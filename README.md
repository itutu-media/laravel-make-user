This package inspired from [spresnac/laravel-create-user-cli](https://github.com/spresnac/laravel-create-user-cli.git)
# How To Use
## Installation
```
composer require itutu-media/laravel-make-user
```
## Configuration
Add code below to auto hash password on save.
```
protected function password(): Attribute
{
    return Attribute::make(
        set: fn (string $value) => Hash::make($value),
    );
}
```
# Usage
Run artisan command below to create a new user.
```
php artisan make:user
```
This command will prompt user to input data based on User Model's database table.