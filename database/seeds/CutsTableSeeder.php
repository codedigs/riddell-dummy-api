<?php

use App\Models\Cut;
use Illuminate\Database\Seeder;

class CutsTableSeeder extends Seeder
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
                'name' => "Cut 1",
                'image' => "/riddell/img/Cuts/cut-7.png"
            ],
            [
                'name' => "Cut 2",
                'image' => "/riddell/img/Cuts/cut-1.png"
            ],
            [
                'name' => "Cut 3",
                'image' => "/riddell/img/Cuts/cut-2.png"
            ],
            [
                'name' => "Cut 4",
                'image' => "/riddell/img/Cuts/cut-3.png"
            ],
            [
                'name' => "Cut 5",
                'image' => "/riddell/img/Cuts/cut-4.png"
            ],
            [
                'name' => "Cut 6",
                'image' => "/riddell/img/Cuts/cut-5.png"
            ],
            [
                'name' => "Cut 7",
                'image' => "/riddell/img/Cuts/cut-6.png"
            ],
            [
                'name' => "Cut 8",
                'image' => "/riddell/img/Cuts/cut-4.png"
            ],
            [
                'name' => "Cut 9",
                'image' => "/riddell/img/Cuts/cut-5.png"
            ],
            [
                'name' => "Cut 10",
                'image' => "/riddell/img/Cuts/cut-6.png"
            ],
        ];

        foreach ($data as $datum) {
            Cut::create([
                'name' => $datum['name'],
                'image' => $datum['image']
            ]);
        }
    }
}
