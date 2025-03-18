<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function store(Request $request): JsonResponse {
        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:clients,email',
            'websites' => 'required|array|max:10', // Max 10 websites per scalability requirement
            'websites.*' => 'required|url|distinct', // Each website must be a valid URL
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create client
        $client = Client::create(['email' => $request->email]);

        // Create websites
        foreach ($request->websites as $url) {
            Website::create([
                'client_id' => $client->id,
                'url' => $url,
            ]);
        }

        return response()->json([
            'message' => 'Client and websites added successfully',
            'client' => $client->load('websites'),
        ], 201);
    }
}
