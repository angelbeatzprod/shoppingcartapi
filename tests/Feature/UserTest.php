<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserTest extends TestCase
{
    public function test_login()
    {
        \Artisan::call('passport:install');
        
        $user = User::factory()->create([
            'email' => "hans@gmail.com",
            'password' => bcrypt("qwerty234"),
            'name' => "Hans Zimmerman"
        ]);

        $credentials = [
            'email' => "hans@gmail.com",
            'password' => "qwerty234",
            'password_confirmation' => "qwerty234"
        ];
        
        $response = $this->post('api/user/login', $credentials);
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                                        'user_id',
                                        'token'
                                    ]);
    }

    public function test_signup()
    {
        $credentials = [            
            'email' => "paul@gmail.com",
            'password' => "qwerty234",
            'password_confirmation' => "qwerty234",
            'name' => "Paul Zimmerman"
        ];

        $response = $this->post('api/user/signup', $credentials);
        
        $response
                ->assertStatus(200)
                ->assertJsonFragment([
                                        'response' => "You have successfully signed up"
                                    ]);
    }
}
