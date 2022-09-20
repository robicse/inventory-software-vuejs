<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('taxes')->insert([
            'name' => 'Zero Tax',
            'percentage' => 0,
            'is_default' => 1,
        ]);
    }
}
