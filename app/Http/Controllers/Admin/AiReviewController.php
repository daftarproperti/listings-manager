<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ListingHelper;
use App\Http\Controllers\Controller;
use App\Jobs\AiReviewJob;
use App\Models\Enums\AiReviewStatus;
use App\Models\Resources\AiReviewResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class AiReviewController extends Controller
{
    public function doReview(string|int $listingId): RedirectResponse
    {
        $listing = ListingHelper::getListingByIdOrListingId($listingId);

        if (!$listing) {
            abort(404);
        }

        $aiReviewOperation = $listing->aiReview()->exists() ? 'update' : 'create';
        $listing->aiReview()->{$aiReviewOperation}([
            'results' => [],
            'status' => (AiReviewStatus::PROCESSING)->value,
        ]);

        AiReviewJob::dispatch($listing);

        return redirect()->back();
    }

    public function getReview(string|int $listingId): JsonResponse|JsonResource
    {
        $listing = ListingHelper::getListingByIdOrListingId($listingId);

        if (!$listing) {
            return response()->json(['success' => false, 'message' => 'Listing not found.'], 404);
        }

        if (!$listing->aiReview()->exists()) {
            return response()->json(['success' => false, 'message' => 'Review not found.'], 404);
        }

        return new AiReviewResource($listing->aiReview);
    }
}
