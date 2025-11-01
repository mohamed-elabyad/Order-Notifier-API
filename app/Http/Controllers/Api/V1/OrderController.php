<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Pipeline\StatusFilter;
use App\Pipeline\AmountRangeFilter;
use App\Pipeline\SearchFilter;
use App\Pipeline\DateRangeFilter;
use App\Pipeline\SortPipe;
use App\Jobs\OrderStatusNotificationJob;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Validation\Rule;

class OrderController extends ApiBaseController
{
    use AuthorizesRequests;
    /**
     * @OA\Get(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="List orders with filters",
     *     description="Get all orders for authenticated user with dynamic filters using Pipeline pattern. Requires orders:read ability.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status (comma-separated)",
     *         required=false,
     *         @OA\Schema(type="string", example="placed,processing")
     *     ),
     *     @OA\Parameter(
     *         name="min_amount",
     *         in="query",
     *         description="Minimum amount filter",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=100.00)
     *     ),
     *     @OA\Parameter(
     *         name="max_amount",
     *         in="query",
     *         description="Maximum amount filter",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=500.00)
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search in order code",
     *         required=false,
     *         @OA\Schema(type="string", example="ORD-1001")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Filter orders from date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-10-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Filter orders until date",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-10-31")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort by field (prefix with - for desc)",
     *         required=false,
     *         @OA\Schema(type="string", example="-placed_at")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="orders", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code", type="string", example="ORD-1001"),
     *                 @OA\Property(property="amount_decimal", type="number", example=199.99),
     *                 @OA\Property(property="status", type="string", example="placed"),
     *                 @OA\Property(property="placed_at", type="string", format="datetime")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - missing orders:read ability")
     * )
     */
    public function index(Request $request)
    {
        $query = Order::query()->where('user_id', $request->user()->id);

        $pipes = [
            new StatusFilter($request->status),
            new AmountRangeFilter($request->min_amount, $request->max_amount),
            new SearchFilter($request->q),
            new DateRangeFilter($request->date_from, $request->date_to),
            new SortPipe($request->sort),
        ];

        $orders = Pipeline::send($query)
            ->through($pipes)
            ->thenReturn()
            ->get();

        return response()->json(['orders' => OrderResource::collection($orders)]);
    }

    /**
     * @OA\Post(
     *     path="/orders",
     *     tags={"Orders"},
     *     summary="Create new order",
     *     description="Create a new order for authenticated user. Requires orders:write ability.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code","amount_decimal","status","placed_at"},
     *             @OA\Property(property="code", type="string", example="ORD-1001"),
     *             @OA\Property(property="amount_decimal", type="number", format="float", example=199.99),
     *             @OA\Property(property="status", type="string", enum={"placed","processing","shipped","delivered","cancelled"}, example="placed"),
     *             @OA\Property(property="placed_at", type="string", format="datetime", example="2025-10-29 10:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="order", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - missing orders:write ability"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(OrderRequest $request)
    {
        $validated = $request->validated();

        $order = Order::create([
            'user_id' => $request->user()->id,
            'code' => $validated['code'],
            'amount_decimal' => $validated['amount_decimal'],
            'status' => $validated['status'],
            'placed_at' => $validated['placed_at'],
        ]);

        return response()->json(['order' => new OrderResource($order)], 201);
    }

    /**
     * @OA\Get(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Get order details",
     *     description="Get single order by ID. User can only access their own orders. Requires orders:read ability.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="order", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - not your order or missing orders:read ability"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return response()->json(['order' => new OrderResource($order)]);
    }

    /**
     * @OA\Patch(
     *     path="/orders/{id}",
     *     tags={"Orders"},
     *     summary="Update order status",
     *     description="Update order status. Triggers FCM push notification on status change. Requires orders:write ability.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"placed","processing","shipped","delivered","cancelled"}, example="shipped")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated and notification queued",
     *         @OA\JsonContent(
     *             @OA\Property(property="order", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden - not your order or missing orders:write ability"),
     *     @OA\Response(response=404, description="Order not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $oldStatus = $order->status;
        $order->update($validated);

        if ($oldStatus !== $validated['status']) {
            OrderStatusNotificationJob::dispatch($order->id);
        }

        return response()->json(['order' => new OrderResource($order)]);
    }
}
