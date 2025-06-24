<?php

namespace App\Filament\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;

class TenantLogin extends BaseLogin
{
    /**
     * This method defines the fields the user sees on the login page.
     * The name of the field here ('email') is just for the form itself.
     */
    protected function getFormSchema(): array
    {
        return [
            TextInput::make('email') // We'll call it 'email' in the form for simplicity
                ->label('Email Address')
                ->email()
                ->required(),
            TextInput::make('password')
                ->label('Password')
                ->password()
                ->required(),
            Checkbox::make('remember')
                ->label('Remember me'),
        ];
    }

    /**
     * THIS IS THE CORRECTED METHOD.
     * This method takes the form data and prepares it for Laravel's Auth system.
     * The key of the returned array MUST match the database column name.
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        // The form data has an 'email' key from getFormSchema().
        // We map it to the 'manager_email' key, which matches the database column.
        return [
            'manager_email' => $data['email'],
            'password'      => $data['password'],
        ];
    }
}
