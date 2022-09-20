<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment_types')->insert([

            ['name' => 'Cash', 'type' => 'cash', 'status'=> 'no_round','is_default' => 1, 'created_by' => 1],
            ['name' => 'Credit', 'type' => 'credit', 'status'=> 'no_round','is_default' => 0, 'created_by' => 1]
        ]);
    }
}
