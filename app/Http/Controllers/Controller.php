<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Swagger(
 *   schemes={"http"},
 *   host="localhost:8000",
 *   basePath="/",
 *   @OA\Info(
 *     title="Shopping cart API",
 *     version="1.0.0"
 *   ),
 * )
 */

 /**
 * @OA\SecurityScheme(
 *     @OA\Flow(
 *         flow="clientCredentials",
 *         tokenUrl="/oauth/token",
 *         scopes={}
 *     ),
 *     securityScheme="oauth2",
 *     in="header",
 *     type="oauth2",
 *     description="Oauth2 security",
 *     name="oauth2",
 *     scheme="http",
 *     bearerFormat="bearer",
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
