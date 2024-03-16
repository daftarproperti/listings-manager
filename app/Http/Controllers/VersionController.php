<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;

class VersionController extends Controller
{
    public function index(): Response
    {
        return response()->json([
            'version' => config('app.version'),
        ]);
    }
}
