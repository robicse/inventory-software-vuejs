<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Email Header
        $emailHeader = '<html>
                            <div style="max-width: 60%; color: #000000 !important; font-family: Helvetica; margin:auto; padding-bottom: 10px;">
                                <div style="text-align:center; padding-top: 10px; padding-bottom: 10px;">
                                    <h1>{app_name}</h1>
                                </div>
                                <div style="padding: 35px;padding-left:20px; font-size:17px; border-bottom: 1px solid #cccccc; border-top: 1px solid #cccccc;">';
        $emailFooter = '        </div>
                            </div>
                        </html>';

        DB::table("email_templates")->insert([
            'template_type' => 'user_invitation',
            'template_subject' => 'You are invited',
            'default_content' =>
                $emailHeader.'Hi,<br>
                {invited_by} invited you to join with the team on {app_name}.<br>
                Please click the link below to accept the invitation!<br>
                {verification_link}'.$emailFooter
        ]);

        DB::table("email_templates")->insert([
            'template_type' => 'account_verification',
            'template_subject' => 'Account verification',
            'default_content' =>
                $emailHeader.'Hi {first_name},<br>
                        Your account has been created.<br>
                        Please click the link below to verify your email.<br>
                        {verification_link}'.$emailFooter
        ]);


        DB::table("email_templates")->insert([
            'template_type' => 'reset_password',
            'template_subject' => 'Reset password',
            'default_content' =>
                $emailHeader.'Hi,<br>
                        You have requested to change your password.<br>
                        Please click the link below to change your password!<br>
                        {reset_password_link}'.$emailFooter
        ]);

        DB::table("email_templates")->insert([
            'template_type' => 'pos_invoice',
            'template_subject' => 'Invoice',
            'default_content' =>
                $emailHeader.'Hi {first_name},<br>
                        Thanks for shopping with us.<br>
                        Please find the attachment for your purchase ({invoice_id}) details.'.$emailFooter
        ]);

    }
}
