<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Filament\Facades\Filament;

class LoginController extends Controller
{
    public function loginToUser($id)
    {
        Filament::auth()->logout();

        return to_route('test', ['id' => $id]);
    }
}
