<?php

namespace ITUTUMedia\LaravelMakeUser\Commands;

use Illuminate\Console\Command;

class LaravelMakeUserCommand extends Command
{
    private $user;
    protected $password, $password_confirmation;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user {--S|superadmin : Assign the superadmin role to the new user (see the documentation for more information)} {--R|roles : Assign roles to the new user (see the documentation for more information)} {--G|guard= : Guard name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get the User model class based on the current authentication configuration
        $class = config(
            'auth.providers.'
                . config(
                    'auth.guards.'
                        . config('auth.defaults.guard', 'web')
                        . '.provider'
                )
                . '.model'
        );

        // Create a new instance of the User model
        $this->user = new $class();

        if ($this->option('superadmin')) {
            if (!in_array('Spatie\Permission\Traits\HasRoles', class_uses($this->user)))
                return $this->error('The super admin option requires the Spatie\Permission\Traits\HasRoles trait to be added to the User model');

            $role = config('make-user.super_admin_role_name') ?? 'Super Admin';
            $guard = $this->option('guard') ?? $this->user->guard_name;
            $sa = Role::findByName($role, $guard);
        }

        // Get the columns of the User model's database table
        $columns = $this->_getColumns($this->user->getTable());

        // Loop through each column, prompting the user to enter a value for fillable nullable columns
        foreach ($columns as $column) {
            if (in_array($column->Field, $this->user->getFillable()) && $column->Null) {
                if ($column->Field == 'password') {
                    $this->_inputPassword($column->Field);
                } else {
                    $this->_input($column->Field);
                }
            }
        }

        if ($this->option('roles')) {
            if (!in_array('Spatie\Permission\Traits\HasRoles', class_uses($this->user)))
                return $this->error('The roles option requires the Spatie\Permission\Traits\HasRoles trait to be added to the User model');

            $roles = Role::all()->pluck('name')->toArray();
            $roles = $this->choice('Select roles to assign to the user', $roles, null, null, true);
        }

        // Try to save the new user record to the database, displaying a success or error message
        try {
            $this->user->save();
            if ($this->option('superadmin')) {
                $this->user->assignRole($role);
            }
            if ($this->option('roles')) {
                $this->user->assignRole($roles);
            }
            $this->info('User created successfully');
        } catch (\Exception $e) {
            return $this->error('Error creating user: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve the columns of a database table, filtering out auto-incrementing and timestamp columns.
     *
     * @param string $table The name of the database table to retrieve columns for.
     * @return array An array of database column objects.
     */
    private function _getColumns(string $table): array
    {
        // get the database connection type
        $conn = config('database.default');

        // prepare the SQL query based on the connection type
        if ($conn == 'mysql') {
            $query = "
            SHOW COLUMNS FROM {$table}
            WHERE (
                type != 'timestamp' AND
                extra != 'auto_increment'
            )";
        } else if ($conn == 'pgsql') {
            $query = "
            SELECT
                *, data_type AS \"Type\", column_name AS \"Field\", is_nullable AS \"Null\"
            FROM
                information_schema.columns
            WHERE
                table_schema = 'public'
                AND table_name = '{$table}';";
        }

        // execute the query and return the result as an array of objects
        return DB::select($query);
    }

    /**
     * Prompt the user to enter a valid password value for the given field, and validate the input.
     *
     * @param string $field The name of the password field.
     * @return void
     */
    private function _inputPassword(string $field): void
    {
        // password validation rules
        $rules = config('make-user.rules.' . $field, 'nullable');

        // loop until a valid password value is entered
        do {
            // prompt the user to enter a password value, and check its length
            $this->password = $this->secret(ucfirst($field) . ' (password not displayed)');
            if (strlen($this->password) < 8) {
                $this->warn('Password must be at least 8 characters long');
            }
            // Validate the password and confirmation fields
            $validator = $this->_validate([
                $field => $this->password
            ], [
                $field => $rules
            ]);
        } while ($validator->fails());
        
        $this->_inputPasswordConfirmation($field);
    }

    /**
     * Prompt the user to enter a valid password value for the given field, and validate the input.
     *
     * @param string $field The name of the password field.
     * @return void
     */
    private function _inputPasswordConfirmation(string $field): void
    {
        // password validation rules
        $rules = config('make-user.rules.' . $field, 'nullable') . '|confirmed';

        // Loop until the password passes validation rules
        do {
            $this->password_confirmation = $this->secret('Confirm ' . ucfirst($field) . ' (press r to re-enter the password)');
            if ($this->password_confirmation == 'r') {
                $this->_inputPassword($field);
            }
            // Validate the password and confirmation fields
            $validator = $this->_validate([
                $field => $this->password,
                $field . '_confirmation' => $this->password_confirmation
            ], [
                $field => $rules
            ]);
        } while ($validator->fails());

        // Set the validated password on the user model
        $this->user->{$field} = $validator->validated()[$field];
    }

    /**
     * Prompt the user to input a value for a given field.
     *
     * @param string $field The name of the field to prompt for input.
     * @return void
     */
    private function _input(string $field): void
    {
        // Set validation rules for the field
        $rules = config('make-user.rules.' . $field, 'nullable');

        // Prompt the user to input a value for the field, and validate the input using the rules above
        do {
            $validator = $this->_validate([
                $field => $this->ask(str_contains($rules, 'required') ? ucfirst($field) . ' (required)' : ucfirst($field) . ' (optional)')
            ], [
                $field => $rules
            ]);
        } while ($validator->fails());

        // Assign the validated value to the corresponding field on the User model
        $this->user->{$field} = $validator->validated()[$field];
    }

    /**
     * Validate an array of data against a set of validation rules.
     *
     * @param array $data An associative array of data to validate.
     * @param array $rules An associative array of validation rules.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance.
     */
    private function _validate($data, $rules): \Illuminate\Contracts\Validation\Validator
    {
        // Create a validator instance for the given data and rules
        $validator = Validator::make($data, $rules, [
            'password.regex' => 'The :attribute must contain at least one uppercase letter, one lowercase letter, and one number'
        ]);

        // If the validator fails, output a warning with the first validation error message
        if ($validator->fails())
            $this->warn('Validation error: ' . $validator->errors()->first());
        return $validator;
    }
}
