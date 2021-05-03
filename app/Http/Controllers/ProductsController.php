<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ProductsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get list of products",
     *     tags={"Products"},
     *      security={{"oauth2": {}}},
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
     *                  type="array",
     *                  @OA\Items(
     *                  ),
     *              ),
     *         ),
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
     *      path="/api/products",
     *      summary="Add a new product",
     *      tags={"Products"},
     *      security={{"oauth2": {}}},
     *      @OA\Parameter(
     *          name="product_name",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="product_price",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="number"
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
     *                  type="array",
     *                  @OA\Items(
     *                  ),
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
        $productId = self::getProductId();
        
        if (self::newProduct(['id' => $productId, 'name' => $request->get('product_name'), 'price' => $request->get('product_price')])) {
            return response()->json(["response" => "Successfully added to the store"], 200);
        } else {
            return response()->json(["response" => "Something went wrong while adding the product to the store"], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/products/{id}",
     *      summary="Get info about a product",
     *      tags={"Products"},
     *      security={{"oauth2": {}}},
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
     *                  type="array",
     *                  @OA\Items(
     *                  ),
     *              ),
     *         ),
     *      )
     * )
     */

    public function show(Request $request)
    {
        return response()->json(["response" => Redis::connection()->hGetAll("product:{$request['id']}")], 200);
    }

    /**
     * @OA\Put(
     *      path="/api/products/{id}",
     *      summary="Update info of a product",
     *      tags={"Products"},
     *      security={{"oauth2": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="product_name",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="product_price",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="number"
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
     *          description="Product doesn't exist",
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
            return response()->json(["response" => "The product you want to update doesn't exist"], 404);
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/products/{id}",
     *      summary="Remove a product",
     *      tags={"Products"},
     *      security={{"oauth2": {}}},
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
     *                  type="array",
     *                  @OA\Items(
     *                  ),
     *              ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Product doesn't exist",
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
            return response()->json(["response" => "The product doesn't exist"], 404);
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
        if (Redis::connection()->del("product:{$data['id']}")) {
            return Redis::connection()->hMSet("product:{$data['id']}", $data);
        } else {
            return false;
        }
    }

}
