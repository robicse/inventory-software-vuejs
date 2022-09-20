<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BranchesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('branches')->insert([
         [
            'name' => 'Main Branch',
            'taxable'=>1,
            'is_default' => 1,
            'tax_id' => 1,
            'is_cash_register'=>1,
            'created_by' =>1,
            'created_at' =>now()
        ],
      ]);
    }
}
