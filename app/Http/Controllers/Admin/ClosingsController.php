<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Closing;
use App\Models\Enums\ClosingStatus;
use App\Models\Enums\CommissionStatus;
use App\Models\Resources\ClosingCollection;
use App\Models\Resources\ClosingResource;
use App\Repositories\Admin\ClosingRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClosingsController extends Controller
{
    public function index(Request $request, ClosingRepository $repository): Response
    {
        $input = $request->only([
            'q',
            'sortBy',
            'sortOrder'
        ]);

        $closings = $repository->list($input);
        $closingsCollection = new ClosingCollection($closings);


        return Inertia::render('Admin/Closings/Index', [
            'data' => [
                'closings' => $closingsCollection->collection,
                'lastPage' => $closings->lastPage()
            ]
        ]);
    }

    public function show(Closing $closing): Response
    {
        $resourceData = new ClosingResource($closing);

        return Inertia::render('Admin/Closings/Form', [
            'data' => [
                'closing' => $resourceData->resolve(),
            ]
        ]);
    }

    public function update(Closing $closing, Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'status' => ['required', Rule::enum(ClosingStatus::class)],
            'commissionStatus' => ['nullable', Rule::enum(CommissionStatus::class)],
            'notes' => 'nullable|string',
        ]);

        $closing->commissionStatus = $validatedData['commissionStatus'] ?? null;
        $closing->status = $validatedData['status'];
        $closing->notes = $validatedData['notes'];

        $closing->save();

        return Redirect::route('closing.index');
    }

}
