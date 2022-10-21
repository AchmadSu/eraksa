<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoryAssetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\CategoryAssets::create([
            'name' => 'Long Term',
            'description' => 'Ini adalah kategori aset yang memiliki masa peminjaman yang lama',
        ]);
    }
}
