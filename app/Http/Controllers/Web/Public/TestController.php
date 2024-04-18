<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request): View
    {
        $phase1 = env('PHASE1', false);
        
        if ($phase1 == 'true') {
            return view('/pages/test');
        } else {
            abort(404, 'Page not found');
        }
    }
}
