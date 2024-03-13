<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resources\TelegramUserCollection;
use App\Repositories\Admin\TelegramUserRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MembersController extends Controller
{
    public function index(Request $request, TelegramUserRepository $repository): Response
    {
        $input = [
            'q' => $request->input('q'),
        ];

        $member = $repository->list($input);
        $memberCollection = new TelegramUserCollection($member);

        return Inertia::render('Admin/Members', [
            'data' => [
                'members' => $memberCollection->collection,
                'lastPage' => $member->lastPage()
            ]
        ]);
    }
}
