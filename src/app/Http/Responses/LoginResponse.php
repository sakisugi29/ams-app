<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.attendance-list');
        }

        return redirect()->route('attendance.index');
    }
}