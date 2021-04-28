<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class CartTest extends TestCase
{
    public function test_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->get('api/cart/user/3');
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                                        'response' => [
                                                '*' => [
                                                     'user_id',
                                                     'seq_num',
                                                     'prod_id'
                                                ]
                                        ]
                                    ]);
    }

    public function test_show()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->get('api/cart/user/2/item/2');
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                                        'response' => [
                                                    'user_id',
                                                    'seq_num',
                                                    'prod_id'
                                        ]
                                    ]);
    }

    public function test_store()
    {
        $user = User::factory()->create();
        User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->post('api/cart/user/1', ['prod_id' => '10']);
        
        $response
                ->assertStatus(200)
                ->assertJsonFragment([
                                        'response' => "Successfully added to the cart"
                                    ]);
    }

    public function test_store_non_existent_prod()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->post('api/cart/user/1', ['prod_id' => '20']);
        
        $response
                ->assertStatus(500)
                ->assertJsonFragment([
                                        'response' => "The product you want to add doesn't exist"
                                    ]);
    }

    public function test_store_non_existent_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->post('api/cart/user/10', ['prod_id' => '8']);
        
        $response
                ->assertStatus(500)
                ->assertJsonFragment([
                                        'response' => "The user to whom you want to add a product doesn't exist"
                                    ]);
    }

    public function test_delete()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->delete('api/cart/user/1/item/2');
        
        $response
                ->assertStatus(200)
                ->assertJsonFragment([
                                        'response' => "Successfully removed from the cart"
                                    ]);
    }

    public function test_delete_repeat()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->delete('api/cart/user/1/item/2');
        
        $response
                ->assertStatus(500)
                ->assertJsonFragment([
                                        'response' => "Something went wrong while removing the product from the cart"
                                    ]);
    }
}
