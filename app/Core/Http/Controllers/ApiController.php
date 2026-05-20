<?php

namespace App\Core\Http\Controllers;

use App\Core\Http\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Controller base API — sem views (API ONLY).
 */
abstract class ApiController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;
}
