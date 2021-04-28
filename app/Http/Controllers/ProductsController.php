<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ProductsController extends Controller
{
    public function index()
    {
        $response = [];

        foreach (Redis::connection()->keys("product:*") as $key) {
            $explodedKey = explode("_", $key);
            $response[] = Redis::connection()->hGetAll(end($explodedKey));
        }

        return response()->json(["response" => $response], 200);
    }

    public function store(Request $request)
    {
        $productId = self::getProductId();
        
        if (self::newProduct(['id' => $productId, 'name' => $request->get('product_name'), 'price' => $request->get('product_price')])) {
            return response()->json(["response" => "Successfully added to the store"], 200);
        } else {
            return response()->json(["response" => "Something went wrong while adding the product to the store"], 500);
        }
    }

    public function show(Request $request)
    {
        return response()->json(["response" => Redis::connection()->hGetAll("product:{$request['id']}")], 200);
    }

    public function update(Request $request)
    {
        if (self::updateProduct(['id' => $request['id'], 'name' => $request->get('product_name'), 'price' => $request->get('product_price')])) {
            return response()->json(["response" => "Successfully updated in the store"], 200);
        } else {
            return response()->json(["response" => "Something went wrong while update the product in the store"], 500);
        }
    }

    public function destroy(Request $request)
    {
        if (Redis::connection()->del("product:{$request['id']}")) {
            return response()->json(["response" => "Successfully removed from the store"], 200);
        } else {
            return response()->json(["response" => "Something went wrong while removing the product from the store"], 500);
        }
    }

    private static function getProductId() : int
    {
        if (!Redis::connection()->exists('product_id')) Redis::connection()->set('product_id', 0);

        return Redis::connection()->incr('product_id');
    }

    private static function newProduct($data) : bool
    {
        return Redis::connection()->hMSet("product:{$data['id']}", $data);
    }

    private static function updateProduct($data) : bool
    {
        Redis::connection()->del("product:{$data['id']}");
        return Redis::connection()->hMSet("product:{$data['id']}", $data);
    }

}
