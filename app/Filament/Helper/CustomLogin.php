<?php

namespace App\Filament\Helper;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login;

class CustomLogin extends Login
{
    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('email')
                ->email()
                ->required()
                // ->default('demo@gmail.com')
                ->placeholder('Email')
                ->maxLength(255),
            TextInput::make('password')
                ->password()
                ->visible()
                ->placeholder('password')
                ->revealable(true)
                // ->default('password')
                ->maxLength(255),
        ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }
}
