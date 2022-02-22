<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeviceControllerTest extends TestCase
{
    public function testRegisterTestDeviceSuccessfully()
    {
        $params = [
            'uid' => 'uidtest',
            'appId' => 'appIdtest',
            'language' => 'en',
            'operating_system' => 'ios',
        ];

        $response = $this->json('POST', 'api/device/register', $params, ['Accept' => 'application/json']);
        $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'result',
            'message',
            'client-token',
        ]);

        $testString = $response->decodeResponseJson()['message'];
        $substring = 'OK';
        $this->assertEquals($substring, $testString);
    }

    public function testCheckSubscriptionSuccessfully()
    {
        $params = [
            'client_token' => '26fa5420-93cb-11ec-9b2d-23900185a389',
        ];

        $response = $this->json('POST', 'api/device/checksubscription', $params, ['Accept' => 'application/json']);
        $response
        ->assertStatus(200)
        ->assertJsonStructure([
            'result',
            'subscription',
        ]);

        $testString = $response->decodeResponseJson()['result'];
        $this->assertTrue($testString);
    }

    public function testPurchaseSuccessfully()
    {
        $params = [
            'client_token' => '26fa5420-93cb-11ec-9b2d-23900185a389',
            'receipt' => '12422123123',
        ];

        $response = $this->json('POST', 'api/device/purchase', $params, ['Accept' => 'application/json']);
        $response->assertStatus(200);
    }
}
