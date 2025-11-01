<?php

use App\Models\Order;
use App\Models\User;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test', ['orders:read', 'orders:write'])->plainTextToken;
});

test('can list orders', function () {
    Order::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/orders');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'orders');
});

test('can filter orders by status', function () {
    Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::PLACED,
    ]);
    Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::SHIPPED,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/orders?status=placed');

    $response->assertStatus(200);
    expect($response->json('orders'))->toHaveCount(1);
});

test('can filter orders by amount range', function () {
    Order::factory()->create([
        'user_id' => $this->user->id,
        'amount_decimal' => 50,
    ]);
    Order::factory()->create([
        'user_id' => $this->user->id,
        'amount_decimal' => 200,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/orders?min_amount=100');

    $response->assertStatus(200);
    expect($response->json('orders'))->toHaveCount(1);
});

test('can search orders by code', function () {
    Order::factory()->create([
        'user_id' => $this->user->id,
        'code' => 'ORD-123',
    ]);
    Order::factory()->create([
        'user_id' => $this->user->id,
        'code' => 'ORD-456',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/orders?q=123');

    $response->assertStatus(200);
    expect($response->json('orders'))->toHaveCount(1);
});

test('can filter orders by date range', function () {
    Order::factory()->create([
        'user_id' => $this->user->id,
        'placed_at' => '2025-01-15',
    ]);
    Order::factory()->create([
        'user_id' => $this->user->id,
        'placed_at' => '2025-02-15',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/orders?date_from=2025-02-01&date_to=2025-02-28');

    $response->assertStatus(200);
    expect($response->json('orders'))->toHaveCount(1);
});

test('can sort orders', function () {
    Order::factory()->create([
        'user_id' => $this->user->id,
        'placed_at' => '2025-01-01',
    ]);
    Order::factory()->create([
        'user_id' => $this->user->id,
        'placed_at' => '2025-01-02',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/orders?sort=-placed_at');

    $response->assertStatus(200);
    $orders = $response->json('orders');
    expect($orders[0]['placed_at'])->toBeGreaterThan($orders[1]['placed_at']);
});

test('can create order', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/orders', [
            'code' => 'ORD-999',
            'amount_decimal' => 299.99,
            'status' => 'placed',
            'placed_at' => now(),
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('orders', ['code' => 'ORD-999']);
});

test('updating order status dispatches notification job', function () {
    Queue::fake();

    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status' => OrderStatus::PLACED,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->patchJson("/api/v1/orders/{$order->id}", [
            'status' => 'shipped',
        ]);

    $response->assertStatus(200);
    Queue::assertPushed(\App\Jobs\OrderStatusNotificationJob::class);
});
