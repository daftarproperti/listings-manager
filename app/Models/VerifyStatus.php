<?php

namespace App\Models;

/**
 * @OA\Schema(
 *     schema="VerifyStatus",
 *     type="string",
 *     description="Verification status",
 *     enum={"on_review", "approved", "rejected"},
 *     example="approved"
 * )
 */
enum VerifyStatus: string
{
    case ON_REVIEW = 'on_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
