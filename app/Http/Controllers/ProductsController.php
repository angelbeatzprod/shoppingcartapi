<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ProductsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/products",
     *     summary="Get list of products",
     *     tags={"Products"},
     *     @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="response",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                          property="id",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="price",
     *                          type="number"
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
     *                  type="string"
     *              ),
     *          ),
     *      )
     * )
     */

    public function index()
    {
        $response = [];

        foreach (Redis::connection()->keys("product:*") as $key) {
            $explodedKey = explode("_", $key);
            $response[] = Redis::connection()->hGetAll(end($explodedKey));
        }

        return response()->json(["response" => $response], 200);
    }

    /**
     * @OA\Post(
     *      path="/products",
     *      summary="Add a new product",
     *      tags={"Products"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="product_name",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="product_price",
     *                  type="number"
     *              )
     *          )
     *      ),
     *     @OA\Response(
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
     *                  type="string"
     *              ),
     *          ),
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
        $productId = self::getProductId();
        
        if (self::newProduct(['id' => $productId, 'name' => $request->get('product_name'), 'price' => $request->get('product_price')])) {
            return response()->json(["response" => "Successfully added to the store"], 200);
        } else {
            return response()->json(["response" => "Something went wrong while adding the product to the store"], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/products/{id}",
     *      summary="Get info about a product",
     *      tags={"Products"},
     *      @OA\Parameter(
     *          name="id",
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
     *                      property="id",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="number"
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
     *                  type="string"
     *              ),
     *          ),
     *      )
     * )
     */

    public function show(Request $request)
    {
        return response()->json(["response" => Redis::connection()->hGetAll("product:{$request['id']}")], 200);
    }

    /**
     * @OA\Put(
     *      path="/products/{id}",
     *      summary="Get info about a product",
     *      tags={"Products"},
     *      @OA\Parameter(
     *          name="id",
     *          description="",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="product_name",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="product_price",
     *                  type="number"
     *              )
     *          )
     *      ),
     *     @OA\Response(
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
     *         ),
     *     )
     * )
     */

    public function update(Request $request)
    {
        if (self::updateProduct(['id' => $request['id'], 'name' => $request->get('product_name'), 'price' => $request->get('product_price')])) {
            return response()->json(["response" => "Successfully updated in the store"], 200);
        } else {
            return response()->json(["response" => "Something went wrong while update the product in the store"], 500);
        }
    }

    /**
     * @OA\Delete(
     *      path="/products/{id}",
     *      summary="Get info about a product",
     *      tags={"Products"},
     *      @OA\Parameter(
     *          name="id",
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
