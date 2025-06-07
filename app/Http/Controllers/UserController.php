<?php

namespace App\Http\Controllers;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function redirectToUser($email)
    {
        $user = DB::table('users')->where('password', request('password'))->where('email', $email)->first();
        if ($user) {
            Filament::auth()->loginUsingId($user->id);
        }

        return redirect('/user');
    }
}
