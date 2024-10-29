<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resources\UserCollection;
use App\Repositories\Admin\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;

class MembersController extends Controller
{
    public function index(Request $request, UserRepository $repository): Response
    {
        $input = $request->only([
            'q',
            'delegatePhone',
            'isDelegateEligible',
        ]);

        $member = $repository->list($input);
        $memberCollection = new UserCollection($member);

        return Inertia::render('Admin/Members', [
            'data' => [
                'members' => $memberCollection->collection,
                'lastPage' => $member->lastPage(),
            ],
        ]);
    }

    public function search(Request $request, UserRepository $repository): JsonResource
    {
        $input = $request->only(['q']);

        return new UserCollection($repository->list($input));
    }
}
