<?php

use App\Models\User;
use App\Models\DeviceToken;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test', ['devices:write'])->plainTextToken;
});

test('can register device token', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/devices', [
            'token' => 'fcm_token_123',
            'platform' => 'android',
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('device_tokens', [
        'user_id' => $this->user->id,
        'token' => 'fcm_token_123',
    ]);
});

test('can remove device token', function () {
    $deviceToken = DeviceToken::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->deleteJson("/api/v1/devices/{$deviceToken->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('device_tokens', ['id' => $deviceToken->id]);
});
