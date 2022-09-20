<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            EmailTemplateTableSeeder::class,
            TaxTableSeeder::class,
            SettingsTableSeeder::class,
            PaymentMethodsTableSeeder::class,
            ShortcutKeyTableSeeder::class,
            BranchesTableSeeder::class,
            CashRegisterTableSeeder::class,
            InvoiceTemplateTableSeeder::class,

        ]);
    }
}
