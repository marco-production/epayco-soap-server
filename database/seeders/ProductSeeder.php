<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::create([
            'name' => 'Mango',
            'price' => 30.0
        ]);

        Product::create([
            'name' => 'Piña',
            'price' => 10.0
        ]);

        Product::create([
            'name' => 'Platano',
            'price' => 25.0
        ]);
    }
}
