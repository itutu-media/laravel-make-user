# Create a new user with Artisan

[![Latest Version on Packagist](https://img.shields.io/packagist/v/itutu-media/laravel-make-user.svg?style=flat-square)](https://packagist.org/packages/itutu-media/laravel-make-user)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/itutu-media/laravel-make-user/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/itutu-media/laravel-make-user/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/itutu-media/laravel-make-user/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/itutu-media/laravel-make-user/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/itutu-media/laravel-make-user.svg?style=flat-square)](https://packagist.org/packages/itutu-media/laravel-make-user)

## Dependencies

- [Spatie Permission package (optional)](https://github.com/spatie/laravel-permission)
If you want to use the `--superuser` or `--roles` options, make sure the Spatie Permission package is installed and configured in your Laravel application.

## Installation

You can install the package via composer:

```bash
composer require itutu-media/laravel-make-user
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-make-user-config"
```

This is the contents of the published config file:

```php
return [
  'super_admin_role_name' => env('SUPER_ADMIN_ROLE_NAME', 'Super Admin'),
  'rules' => [
    'name' => 'required|string|max:255',
    'email' => 'required|string|email|max:255|unique:users',
    'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
  ],
];
```

## Configuration

Open file `config/make-user.php` and change the rules based on your needs for the new user.

## Usage
Once installed, you can use the `make:user` command to create a new user. Here's an example:
```bash
php artisan make:user
```
### Command Options
The `make:user` command supports the following options:
> `--superadmin` (`-S`): Assign the superadmin role to the new user. Requires the `Spatie\Permission\Traits\HasRoles` trait to be added to the User model.
> `--roles` (`-R`): Assign roles to the new user. Requires the `Spatie\Permission\Traits\HasRoles` trait to be added to the User model.
### - Super Admin
To use the `--superadmin` option, you need to set the `super_admin_role_name` value in the `config/make-user.php` file. Here's an example of using the make:user command with the `--superadmin` option:
```bash
php artisan make:user --superuser
```
### - Roles
Here's an example of using the make:user command with the `--roles` options:
```bash
php artisan make:user --role
```
This will prompt you to enter values for fillable nullable columns and select the roles to assign to the new user.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [ITUTU Media](https://github.com/itutu-media)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
