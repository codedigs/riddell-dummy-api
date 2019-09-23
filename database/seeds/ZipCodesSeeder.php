<?php

use App\Models\ZipCode;
use Illuminate\Database\Seeder;

class ZipCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $zip_codes = json_decode(file_get_contents(storage_path() . '/dataseed/zipcodes.json'), true);

        foreach ($zip_codes as $zip_code) {
            echo "Zip Code: " . $zip_code['zip'] . PHP_EOL .
                "State Code: " . $zip_code['state'] . PHP_EOL .
                "State: " . $zip_code['fullState'] . PHP_EOL .
                "City: " . $zip_code['city'];

            echo PHP_EOL . PHP_EOL;

            ZipCode::create([
                'zip_code' => $zip_code['zip'],
                'state_code' => $zip_code['state'],
                'state' => $zip_code['fullState'],
                'city' => $zip_code['city']
            ]);
        }
    }
}
