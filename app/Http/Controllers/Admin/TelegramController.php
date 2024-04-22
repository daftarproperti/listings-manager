<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Assert;
use App\Http\Controllers\Controller;
use App\Models\Resources\TelegramAllowlistGroupCollection;
use App\Models\Resources\TelegramAllowlistGroupResource;
use App\Models\TelegramAllowlistGroup;
use App\Repositories\Admin\TelegramAllowlistRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TelegramController extends Controller
{
    public function allowlistIndex(TelegramAllowlistRepository $repository): Response
    {
        $allowlists = $repository->list();
        $allowlistCollection = new TelegramAllowlistGroupCollection($allowlists);

        return Inertia::render('Admin/Allowlists/Index', [
            'data' => [
                'allowlists' => $allowlistCollection->collection,
                'lastPage' => $allowlists->lastPage()
            ]
        ]);
    }

    public function allowlistDetail(TelegramAllowlistGroup $allowlist): Response
    {
        $resourceData = new TelegramAllowlistGroupResource($allowlist);

        return Inertia::render('Admin/Allowlists/Form', [
            'data' => [
                'allowlist' => $resourceData->resolve()
            ]
        ]);
    }

    public function allowlistUpdate(Request $request, TelegramAllowlistGroup $allowlist) : RedirectResponse
    {
        $groupName = Assert::string($request->input('groupName', ''));
        $allowed = Assert::boolean($request->input('allowed', false));

        $allowlist->groupName = $groupName;
        $allowlist->allowed = $allowed;

        $allowlist->save();

        return redirect()->route('telegram.allowlists');
    }
}
