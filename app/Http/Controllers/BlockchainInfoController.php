<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Shows the current blockchain configuration.
 */
class BlockchainInfoController extends Controller
{
    public function index(): JsonResponse
    {
        $info = [
            'contractAddress' => env('ETH_LISTINGS_CONTRACT_ADDRESS'),
            'contractVersion' => env('ETH_LISTINGS_CONTRACT_VERSION', '0'),
        ];
        return response()->json($info);
    }
}
