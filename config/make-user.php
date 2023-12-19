<?php

// config for ITUTUMedia/LaravelMakeUser
return [
    'super_admin_role_name' => env('SUPER_ADMIN_ROLE_NAME', 'Super Admin'),
    'rules' => [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
    ],
];
