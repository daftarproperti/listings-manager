<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resources\UserCollection;
use App\Repositories\Admin\UserRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MembersController extends Controller
{
    public function index(Request $request, UserRepository $repository): Response
    {
        $input = [
            'q' => $request->input('q'),
        ];

        $member = $repository->list($input);
        $memberCollection = new UserCollection($member);

        return Inertia::render('Admin/Members', [
            'data' => [
                'members' => $memberCollection->collection,
                'lastPage' => $member->lastPage(),
            ],
        ]);
    }
}
