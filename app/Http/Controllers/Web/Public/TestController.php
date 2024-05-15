<?php

namespace App\Http\Controllers\Web\Public;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(): View
    {
        return view('pages/test-list', [
            'pages' => [
                'home',
                'contact',
            ],
        ]);
    }

    public function page(string $page, Request $request): View
    {
        if (method_exists($this, $page)) {
            return $this->$page($request);
        }

        abort(404, 'Page not found');
    }

    public function home(): View
    {
        return view('home');
    }

    public function contact(): View
    {
        return view('contact');
    }
}
