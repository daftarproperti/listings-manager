<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VersionController extends Controller
{
    public function index()
    {
        return response()->json([
            'version' => config('app.version'),
        ]);
    }
}
