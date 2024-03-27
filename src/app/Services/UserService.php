<?php

namespace App\Services;

class UserService
{
    /**
     * Check if user is authenticated and return it
     */
    public static function getAuthenticatedUser()
    {
        $auth = auth('sanctum');
        if(!$auth->check()){
            return response()->json([
                "message" => "Unauthorized",
            ], 401);
        }

        return $auth->user();
    }
}
