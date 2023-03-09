<?php

namespace ItutuMedia\LaravelMakeUser;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateUser extends Command
{
    private $user;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user';

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

        // Try to save the new user record to the database, displaying a success or error message
        try {
            $this->user->save();
            $this->info('User created successfully');
        } catch (\Exception $e) {
            $this->error('Error creating user: ' . $e->getMessage());
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
        $rules = ['required', 'confirmed'];

        // loop until a valid password value is entered
        do {
            // prompt the user to enter a password value, and check its length
            $password = $this->secret(ucfirst($field));
            $continue = true;
            if (strlen($password) < 8) {
                $this->warn('Password must be at least 8 characters long');
                $continue = strlen($password) == 0 ? false : $this->confirm('Do you want to continue?');
            }
        } while ($continue === false);

        // Loop until the password passes validation rules
        do {
            // Validate the password and confirmation fields
            $validator = $this->_validate([
                $field => $password,
                $field . '_confirmation' => $this->secret('Confirm ' . ucfirst($field))
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
        $rules = ['required'];
        if ($field == 'email')
            $rules[] = 'email';

        // Prompt the user to input a value for the field, and validate the input using the rules above
        do {
            $validator = $this->_validate([
                $field => $this->ask(ucfirst($field))
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
        $validator = Validator::make($data, $rules);

        // If the validator fails, output a warning with the first validation error message
        if ($validator->fails())
            $this->warn('Validation error: ' . $validator->errors()->first());
        return $validator;
    }
}
