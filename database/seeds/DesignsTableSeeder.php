<?php

use App\Models\Design;
use Illuminate\Database\Seeder;

class DesignsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => "Design 1",
                'image' => "/riddell/img/Football-Picker/Inspiration@2x.png"
            ],
            [
                'name' => "Design 2",
                'image' => "/riddell/img/Football-Picker/download@2x.png"
            ],
            [
                'name' => "Design 3",
                'image' => "/riddell/img/Football-Picker/download%20(1)@2x.png"
            ],
            [
                'name' => "Design 4",
                'image' => "/riddell/img/Football-Picker/download%20(3)@2x.png"
            ],
            [
                'name' => "Design 5",
                'image' => "/riddell/img/Football-Picker/download%20(2)@2x.png"
            ],
            [
                'name' => "Design 6",
                'image' => "/riddell/img/Football-Picker/Inspiration@2x.png"
            ],
            [
                'name' => "Design 7",
                'image' => "/riddell/img/Football-Picker/download@2x.png"
            ],
            [
                'name' => "Design 8",
                'image' => "/riddell/img/Football-Picker/download%20(1)@2x.png"
            ],
            [
                'name' => "Design 9",
                'image' => "/riddell/img/Football-Picker/download%20(3)@2x.png"
            ],
            [
                'name' => "Design 10",
                'image' => "/riddell/img/Football-Picker/download%20(2)@2x.png"
            ],
        ];

        foreach ($data as $datum) {
            Design::create([
                'name' => $datum['name'],
                'image' => $datum['image']
            ]);
        }
    }
}
