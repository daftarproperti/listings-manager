<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resources\UserCollection;
use App\Models\Resources\UserResource;
use App\Repositories\Admin\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Redirect;
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

        return Inertia::render('Admin/Members/index', [
            'data' => [
                'members' => $memberCollection->collection,
                'lastPage' => $member->lastPage(),
                'totalMembers' => $member->total(),
            ],
        ]);
    }

    public function show(string $id, UserRepository $repository): Response
    {
        $member = $repository->getById($id);
        if (is_null($member)) {
            abort(404);
        }

        $resourceData = new UserResource($member);

        if (!is_null($member->delegatePhone)) {
            $delegate = $repository->getByPhone($member->delegatePhone);
            $delegateResource = new UserResource($delegate);

            return Inertia::render('Admin/Members/Detail', [
                'data' => [
                    'member' => $resourceData->resolve(),
                    'delegate' => $delegateResource->resolve(),
                ],
            ]);
        }

        $input['delegatePhone'] = $member->phoneNumber;
        $principals = new UserCollection($repository->list($input));

        return Inertia::render('Admin/Members/Detail', [
            'data' => [
                'member' => $resourceData->resolve(),
                'principals' => $principals->collection,
            ],
        ]);
    }

    public function update(string $id, Request $request, UserRepository $repository): RedirectResponse
    {
        $data = $request->only([
            'isDelegateEligible',
        ]);

        $member = $repository->getById($id);
        if (is_null($member)) {
            abort(404);
        }

        $input['delegatePhone'] = $member->phoneNumber;
        $principal = new UserCollection($repository->list($input));
        if ($principal->count()) {
            return Redirect::route('members.show', $member->id)->withErrors(
                ['message' => 'Member sudah terdaftar sebagai delegasi'],
            );
        }

        $data['isDelegateEligible'] = filter_var($data['isDelegateEligible'], FILTER_VALIDATE_BOOLEAN);

        if (!is_null($member->delegatePhone) && $data['isDelegateEligible']) {
            return Redirect::route('members.show', $member->id)->withErrors(
                ['message' => 'Member sudah memiliki delegasi'],
            );
        }

        $member->isDelegateEligible = $data['isDelegateEligible'];
        $member->save();

        return Redirect::route('members.show', $member->id);
    }

    public function search(Request $request, UserRepository $repository): JsonResource
    {
        $input = $request->only(['q']);

        return new UserCollection($repository->list($input));
    }
}
