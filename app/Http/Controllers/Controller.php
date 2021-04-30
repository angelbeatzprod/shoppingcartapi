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
 *      @OA\SecurityScheme(
 *          securityDefinition="passport",
 *          type="oauth2",
 *          in="header",
 *          name="Authorization"
 *      ),
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
