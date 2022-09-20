<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CashRegisterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cash_registers')->insert([
            [
                'title' => 'Main Cash Register',
                'branch_id' => 1,
                'sales_invoice_id' => 2,
                'receiving_invoice_id' => 4,
                'created_by' => 1,
                'created_at' =>now()
            ],

        ]);
    }
}
