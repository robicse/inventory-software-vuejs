<?php

use Illuminate\Database\Seeder;

class ShortcutKeyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shortcut_keys')->insert([
            [
                'user_id' => '1',
                'defaultShortcuts' => 'a:7:{s:13:"productSearch";a:3:{s:11:"action_name";s:13:"productSearch";s:12:"shortcut_key";s:6:"ctrl+s";s:6:"status";b:1;}s:8:"holdCard";a:3:{s:11:"action_name";s:8:"holdCard";s:12:"shortcut_key";s:6:"ctrl+h";s:6:"status";b:1;}s:3:"pay";a:3:{s:11:"action_name";s:3:"pay";s:12:"shortcut_key";s:6:"ctrl+p";s:6:"status";b:1;}s:11:"addCustomer";a:3:{s:11:"action_name";s:11:"addCustomer";s:12:"shortcut_key";s:6:"ctrl+a";s:6:"status";b:1;}s:14:"cancelCarditem";a:3:{s:11:"action_name";s:14:"cancelCarditem";s:12:"shortcut_key";s:6:"ctrl+d";s:6:"status";b:1;}s:13:"loadSalesPage";a:3:{s:11:"action_name";s:13:"loadSalesPage";s:12:"shortcut_key";s:6:"ctrl+l";s:6:"status";b:1;}s:12:"donePayment1";a:3:{s:11:"action_name";s:12:"donePayment1";s:12:"shortcut_key";s:6:"ctrl+m";s:6:"status";b:1;}}',
                'customShortcuts' => Null,
                'created_by'=>1,
                'shortcutsStatus'=>1,

            ],
        ]);
    }
}