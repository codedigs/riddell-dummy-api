<?php

use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class, 10)->create();

        // user added
        $names = ["rodrigo", "mike", "jowin", "johnjay", "elmer"];

        foreach ($names as $name) {
            User::create([
                'name' => $name,
                'email' => $name . "@qstrike.com",
                'password' => password_hash($name, PASSWORD_DEFAULT)
            ]);
        }
    }
}
