<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;


class ProductsTest extends TestCase
{
    public function test_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->get('api/products');
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                                        'response' => [
                                                '*' => [
                                                     'id',
                                                     'name',
                                                     'price'
                                                ]
                                        ]
                                    ]);
    }

    public function test_show()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->get('api/products/1');
        
        $response
                ->assertStatus(200)
                ->assertJsonStructure([
                                        'response' => [
                                                    'id',
                                                    'name',
                                                    'price'
                                        ]
                                    ]);
    }

    public function test_store()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->post('api/products', ['product_name' => 'tangerine', 'product_price' => '19']);
        
        $response
                ->assertStatus(200)
                ->assertJsonFragment([
                                        'response' => "Successfully added to the store"
                                    ]);
    }

    public function test_put()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->put('api/products/8', ['product_name' => 'orange', 'product_price' => '9']);
        
        $response
                ->assertStatus(200)
                ->assertJsonFragment([
                                        'response' => "Successfully updated in the store"
                                    ]);
    }

    public function test_delete()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->delete('api/products/9');
        
        $response
                ->assertStatus(200)
                ->assertJsonFragment([
                                        'response' => "Successfully removed from the store"
                                    ]);
    }

    public function test_delete_repeat()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')
                         ->delete('api/products/9');
        
        $response
                ->assertStatus(500)
                ->assertJsonFragment([
                                        'response' => "Something went wrong while removing the product from the store"
                                    ]);
    }
}
