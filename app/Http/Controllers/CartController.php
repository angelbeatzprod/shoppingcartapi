<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class CartController extends Controller
{
    public function index(Request $request)
    {
        Log::info("USER_ID " . $request['user_id']);
        Log::info("QUERY " . User::where('id', '=', $request['user_id'])->get());
        
        $response = [];

        foreach (Redis::connection()->keys("cart:{$request['user_id']}:items:*") as $key) {
            // return response()->json(["response" => $key], 200);
            $explodedKey = explode("_", $key);
            $response[] = Redis::connection()->hGetAll(end($explodedKey));
        }

        return response()->json(["response" => $response], 200);
        // return response()->json(["response" => Redis::connection()->keys("cart:1:items:*")], 200);
    }

    public function store(Request $request)
    {        
        // return response()->json(["response" => User::where('id', '=', $request['user_id'])->get()->isEmpty()], 500);
        Log::info("USER_ID " . $request['user_id']);
        Log::info("QUERY " . User::where('id', '=', $request['user_id'])->get());

        if (! Redis::connection()->exists("product:{$request->get('prod_id')}")) {
            return response()->json(["response" => "The product you want to add doesn't exist"], 500);
        } 
        
        if (User::where('id', '=', $request['user_id'])->get()->isEmpty() == true) {
            return response()->json(["response" => "The user to whom you want to add a product doesn't exist"], 500);
        } else {
            $sequenceNumber = self::getSequenceNumber($request['user_id']);

            if (self::addToCart(['user_id' => $request['user_id'], 'seq_num' => $sequenceNumber, 'prod_id' => $request->get('prod_id')])) {
                return response()->json(["response" => "Successfully added to the cart"], 200);
            } else {
                return response()->json(["response" => "Something went wrong while adding the product to the cart"], 500);
            }
        }
    }

    public function show(Request $request)
    {
        // return response()->json(["response" => [$request['id'], $request->get('user_id')]], 200);
        return response()->json(["response" => Redis::connection()->hGetAll("cart:{$request['user_id']}:items:{$request['item_id']}")], 200);
    }

    public function destroy(Request $request)
    {
        if (Redis::connection()->del("cart:{$request['user_id']}:items:{$request['item_id']}")) {
            return response()->json(["response" => "Successfully removed from the cart"], 200);
        } else {
            return response()->json(["response" => "Something went wrong while removing the product from the cart"], 500);
        }
    }

    private static function getSequenceNumber($userId) : int
    {
        if (!Redis::connection()->exists("cart:{$userId}:increment")) Redis::connection()->set("cart:{$userId}:increment", 0);

        return Redis::connection()->incr("cart:{$userId}:increment");
    }

    private static function addToCart($data) : bool
    {
        return Redis::connection()->hMSet("cart:{$data['user_id']}:items:{$data['seq_num']}", $data);
    }
}
