<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class DPAuth
{
    /**
     * A type-safe version of Laravel's Auth::user().
     *
     * DP logged in user may be of type User or Admin, so annotate the typing as such.
     * This codebase should use this helper instead of directly calling Auth::user() to ensure type safety.
     *
     * TODO: Add lint to warn direct call of Auth::user().
     */
    public static function user(): User|Admin|null
    {
        return Auth::user();
    }

    /**
     * A type-safe version of Laravel's Auth::user().
     *
     * Same as DPAuth::user() but also asserts that the user is not null.
     * Convenient in places where we know there must be an authenticated user.
     */
    public static function userNotNull(): User|Admin
    {
        return type(Auth::user())->not()->null();
    }

    /**
     * A type-safe version of Laravel's Auth::user().
     *
     * Same as DPAuth::user() but also asserts that the user is of type Admin.
     */
    public static function userAsAdmin(): Admin
    {
        return type(Auth::user())->as(Admin::class);
    }

    /**
     * A type-safe version of Laravel's Auth::user().
     *
     * Same as DPAuth::user() but also asserts that the user is of type User (app user, non-admin).
     */
    public static function appUser(): User
    {
        return type(Auth::user())->as(User::class);
    }
}
