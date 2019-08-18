<?php

use App\Models\Style;
use Illuminate\Database\Seeder;

class StylesTableSeeder extends Seeder
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
                'name' => "Style 1",
                'image' => "/riddell/img/Football-Picker/Inspiration@2x.png"
            ],
            [
                'name' => "Style 2",
                'image' => "/riddell/img/Football-Picker/download@2x.png"
            ],
            [
                'name' => "Style 3",
                'image' => "/riddell/img/Football-Picker/download%20(1)@2x.png"
            ],
            [
                'name' => "Style 4",
                'image' => "/riddell/img/Football-Picker/download%20(3)@2x.png"
            ],
            [
                'name' => "Style 5",
                'image' => "/riddell/img/Football-Picker/download%20(2)@2x.png"
            ],
            [
                'name' => "Style 6",
                'image' => "/riddell/img/Football-Picker/Inspiration@2x.png"
            ],
            [
                'name' => "Style 7",
                'image' => "/riddell/img/Football-Picker/download@2x.png"
            ],
            [
                'name' => "Style 8",
                'image' => "/riddell/img/Football-Picker/download%20(1)@2x.png"
            ],
            [
                'name' => "Style 9",
                'image' => "/riddell/img/Football-Picker/download%20(3)@2x.png"
            ],
            [
                'name' => "Style 10",
                'image' => "/riddell/img/Football-Picker/download%20(2)@2x.png"
            ],
        ];

        foreach ($data as $datum) {
            Style::create([
                'name' => $datum['name'],
                'image' => $datum['image']
            ]);
        }
    }
}
