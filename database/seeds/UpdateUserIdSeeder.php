<?php

use App\User;
use Illuminate\Database\Seeder;

class UpdateUserIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'user_id' => 4732,
                'email' => "test@adams.com"
            ],
            [
                'user_id' => 4733,
                'email' => "test1@adams.com"
            ],
            [
                'user_id' => 4734,
                'email' => "test2@adams.com"
            ],
            [
                'user_id' => 4749,
                'email' => "test3@adams.com"
            ],
            [
                'user_id' => 4748,
                'email' => "test4@adams.com"
            ],
            [
                'user_id' => 4735,
                'email' => "test5@adams.com"
            ],
            [
                'user_id' => 4762,
                'email' => "test6@adams.com"
            ],
            [
                'user_id' => 4768,
                'email' => "test7@adams.com"
            ],
            [
                'user_id' => 4769,
                'email' => "test10@adams.com"
            ],
            [
                'user_id' => 4776,
                'email' => "test11@adams.com"
            ],
            [
                'user_id' => 4770,
                'email' => "test15@adams.com"
            ],
            [
                'user_id' => 4771,
                'email' => "test16@adams.com"
            ],
            [
                'user_id' => 4772,
                'email' => "test20@adams.com"
            ],
            [
                'user_id' => 4773,
                'email' => "test21@adams.com"
            ],
            [
                'user_id' => 4774,
                'email' => "test22@adams.com"
            ],
            [
                'user_id' => 4775,
                'email' => "test23@adams.com"
            ],
            [
                'user_id' => 2,
                'email' => "arthur@qstrike.com"
            ],
            [
                'user_id' => 2210,
                'email' => "jared@prolook.com"
            ],
            [
                'user_id' => 81,
                'email' => "dan@prolook.com"
            ],
            [
                'user_id' => 309,
                'email' => "dustin@prolook.com"
            ],
            [
                'user_id' => 1056,
                'email' => "devri@prolook.com"
            ]
        ];

        foreach ($users as $user) {
            $userObj = User::findBy('email', $user['email'])->first();

            if (!is_null($userObj))
            {
                if (is_null($userObj->user_id))
                {
                    $userObj->user_id = $user['user_id'];
                    $userObj->save();
                }
            }
        }
    }
}
