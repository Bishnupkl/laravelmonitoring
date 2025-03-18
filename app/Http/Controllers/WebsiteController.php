<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebsiteController extends Controller
{
    public function getClients(): JsonResponse
    {
        return response()->json(
            Cache::remember('clients', 60, fn() => Client::with('websites')->get())
        );
    }
}
