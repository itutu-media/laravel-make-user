# Create a new user with Artisan

[![Latest Version on Packagist](https://img.shields.io/packagist/v/itutu-media/laravel-make-user.svg?style=flat-square)](https://packagist.org/packages/itutu-media/laravel-make-user)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/itutu-media/laravel-make-user/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/itutu-media/laravel-make-user/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/itutu-media/laravel-make-user/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/itutu-media/laravel-make-user/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/itutu-media/laravel-make-user.svg?style=flat-square)](https://packagist.org/packages/itutu-media/laravel-make-user)

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

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-make-user-views"
```

## Usage

```php
$laravelMakeUser = new ITUTUMedia\LaravelMakeUser();
echo $laravelMakeUser->echoPhrase('Hello, ITUTUMedia!');
```

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
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
