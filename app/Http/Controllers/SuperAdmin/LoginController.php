<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function loginToUser($id)
    {
        Auth::logout();
        Auth::login(User::find($id));

        return redirect('/');
    }
}
