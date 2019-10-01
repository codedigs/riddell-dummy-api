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
        $emails = [
            "monique@ratke.com",
            "corkery.jadon@yahoo.com",
            "nicolette67@grant.com",
            "rbradtke@hotmail.com",
            "considine.dell@hotmail.com",
            "bmuller@gmail.com",
            "vdach@cruickshank.com",
            "chelsey.kunde@gmail.com",
            "gwilderman@hotmail.com",
            "cthiel@conroy.org",
            "rodrigo@qstrike.com",
            "mike@qstrike.com",
            "jowin@qstrike.com",
            "johnjay@qstrike.com",
            "elmer@qstrike.com",
            "aron@qstrike.com",
            "test3@adams.com",
            "test22@adams.com",
            "test1@adams.com",
            "test4@adams.com",
            "test10@adams.com",
            "test23@adams.com",
            "test21@adams.com",
            "test11@adams.com",
            "test@adams.com",
            "test12@adams.com",
            "test6@adams.com",
            "testuser2@qstrike.com",
            "randomuser1@gmail.com",
            "randomuser5@gmail.com",
            "randomuser6@gmail.com",
            "randomuser7@gmail.com",
            "randomness1@gmai.com",
            "test7@adams.com",
            "test13@adams.com",
            "jayjay@gmail.com",
            "rico@qstrike.com",
            "arthur@prolook.com",
            "jossel@qstrike.com",
            "test25@adams.com",
            "arthur@qstrike.com",
            "test100@adams.com",
            "arthur@adams.com",
            "arthur@gmail.com",
            "jared@prolook.com",
            "arthur@yahoo.com",
            "test@test123.com",
            "shop@prolook.com",
            "art@adams.com",
            "danm@prolook.com",
            "erlyn@qstrike.com",
            "test14@adams.com",
            "snelson@brgsports.com",
            "test9@adams.com",
            "angel@qstrike.com",
            "test15@adams.com",
            "test16@adams.com",
            "test17@adams.com",
            "tes17t@adams.com",
            "tes15t@adams.com",
            "devri@qstrike.com",
            "testtest@adams.com",
            "testuser1@adams.com",
            "testeteset@adams.com",
            "testuser2@adams.com",
            "shawn@qstrike.com",
            "teset7@adams.com",
            "dan@prolook.com",
            "testuser123@adams.com",
            "test1234@adams.com",
            "dan@qstrike.com",
            "doug.carrico@brgsports.com",
            "test14@gmail.com",
            "tuser1@brgsports.com",
            "dbaron@riddellsales.com",
            "tes29t@adams.com",
            "bross@riddellsales.com",
            "rbohlinger@riddellsales.com",
            "bhergert@riddellsales.com",
            "acolangelo@riddellsports.com",
            "dev1@adams.com",
            "dev2@adams.com",
            "test111111@adams.com",
            "test1002@gmail.com",
            "test1003@gmail.com",
            "test1004@gmail.com",
            "price.kyla@lemke.net",
            "laisha.schaefer@ratke.com",
            "abdullah13@gmail.com",
            "dbarrows@gmail.com",
            "qwisozk@yahoo.com",
            "sincere.wilderman@stiedemann.com",
            "garnet78@gmail.com",
            "dkozey@gmail.com",
            "forrest.schumm@hotmail.com",
            "tatyana.hane@yahoo.com",
            "dev03@adams.com",
            "test11111@adams.com",
        ];

        foreach ($emails as $email)
        {
            $userObj = User::findBy('email', $email)->first();

            if (!is_null($userObj))
            {
                if (!is_null($userObj->user_id))
                {
                    $userObj->user_id = null;
                    $userObj->save();
                }
            }
        }
    }
}
