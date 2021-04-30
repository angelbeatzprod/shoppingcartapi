<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class CartController extends Controller
{
    /**
     * @OA\Get(
     *      path="/cart/user/{user_id}",
     *      summary="Get list of products in the cart",
     *      tags={"Cart"},
     *      @OA\Parameter(
     *          name="user_id",
     *          description="",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="response",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                          property="item_id",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="user_id",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="prod_id",
     *                          type="integer"
     *                      )
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized user",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="array",
     *                  @OA\Items(
     *                  ),
     *              ),
     *         ),
     *      )
     * )
     */

    public function index(Request $request)
    {        
        $response = [];

        foreach (Redis::connection()->keys("cart:{$request['user_id']}:items:*") as $key) {
            $explodedKey = explode("_", $key);
            $response[] = Redis::connection()->hGetAll(end($explodedKey));
        }

        return response()->json(["response" => $response], 200);
    }

    /**
     * @OA\Post(
     *      path="/cart/user/{user_id}",
     *      summary="Add a new product to the cart",
     *      tags={"Cart"},
     *      @OA\Parameter(
     *          name="user_id",
     *          description="",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="prod_id",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="response",
     *                  type="string"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized user",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="array",
     *                  @OA\Items(
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Product or user doesn't exist",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="response",
     *                  type="string"
     *              ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="500",
     *          description="Something went wrong",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="response",
     *                  type="string"
     *              ),
     *          ),
     *      )
     * )
     */

    public function store(Request $request)
    {
        if (! Redis::connection()->exists("product:{$request->get('prod_id')}")) {
            return response()->json(["response" => "The product you want to add doesn't exist"], 404);
        }
        
        if (User::where('id', '=', $request['user_id'])->get()->isEmpty() == true) {
            return response()->json(["response" => "The user to whom you want to add a product doesn't exist"], 404);
        } else {
            $itemId = self::getItemId($request['user_id']);

            if (self::addToCart(['user_id' => $request['user_id'], 'item_id' => $itemId, 'prod_id' => $request->get('prod_id')])) {
                return response()->json(["response" => "Successfully added to the cart"], 200);
            } else {
                return response()->json(["response" => "Something went wrong while adding the product to the cart"], 500);
            }
        }
    }

    /**
     * @OA\Get(
     *      path="/cart/user/{user_id}/item/{item_id}",
     *      summary="Get info about a product from the cart",
     *      tags={"Cart"},
     *      @OA\Parameter(
     *          name="user_id",
     *          description="",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="item_id",
     *          description="",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="response",
     *                  @OA\Property(
     *                      property="item_id",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="user_id",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="prod_id",
     *                      type="integer"
     *                  ),
     *              ),
     *          ),
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized user",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="array",
     *                  @OA\Items(),
     *              ),
     *         ),
     *      )
     * )
     */

    public function show(Request $request)
    {
        // return response()->json(["response" => [$request['id'], $request->get('user_id')]], 200);
        return response()->json(["response" => Redis::connection()->hGetAll("cart:{$request['user_id']}:items:{$request['item_id']}")], 200);
    }

    /**
     * @OA\Delete(
     *      path="/cart/user/{user_id}/item/{item_id}",
     *      summary="Remove a product from the cart",
     *      tags={"Cart"},
     *      @OA\Parameter(
     *          name="user_id",
     *          description="",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="item_id",
     *          description="",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="response",
     *                  type="string"
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Unauthorized user",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="array",
     *                  @OA\Items(
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Item doesn't exist",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="response",
     *                  type="string"
     *              ),
     *          ),
     *      )
     * )
     */

    public function destroy(Request $request)
    {
        if (Redis::connection()->del("cart:{$request['user_id']}:items:{$request['item_id']}")) {
            return response()->json(["response" => "Successfully removed from the cart"], 200);
        } else {
            return response()->json(["response" => "There is no this item in the cart"], 404);
        }
    }

    private static function getItemId($userId) : int
    {
        if (!Redis::connection()->exists("cart:{$userId}:increment")) Redis::connection()->set("cart:{$userId}:increment", 0);

        return Redis::connection()->incr("cart:{$userId}:increment");
    }

    private static function addToCart($data) : bool
    {
        return Redis::connection()->hMSet("cart:{$data['user_id']}:items:{$data['item_id']}", $data);
    }
}
