<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can register', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Ahmed Ali',
        'email' => 'ahmed@test.com',
        'password' => 'password123',
        'device_name' => 'mobile',
        'abilities' => ['orders:read', 'orders:write'],
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['user', 'token', 'abilities']);

    $this->assertDatabaseHas('users', ['email' => 'ahmed@test.com']);
});

test('user can login', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
        'device_name' => 'postman',
        'abilities' => ['orders:read'],
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['user', 'token']);
});

test('user can get their profile', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test', ['orders:read'])->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJsonPath('user.email', $user->email);
});

test('user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(200);
});
