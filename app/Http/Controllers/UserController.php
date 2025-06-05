<?php

namespace App\Http\Controllers;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function redirectToUser($password, $email)
    {
        $user = DB::table('users')->where('password', $password)->where('email', $email)->first();
        if ($user) {
            Filament::auth()->loginUsingId($user->id);
        }

        return redirect('/user');
    }
}
