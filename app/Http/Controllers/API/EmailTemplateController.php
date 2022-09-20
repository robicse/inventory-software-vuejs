<?php

namespace App\Http\Controllers\Api;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Lang;

class EmailTemplateController extends Controller
{

    public function index(Request $request)
    {
        $data = EmailTemplate::getEmailTemplate($request);
        $totalCount = EmailTemplate::countData();

        return ['datarows' => $data, 'count' => $totalCount];
    }

    public function show($id)
    {
        $emailTemplate = EmailTemplate::getOne($id);

        if ($emailTemplate->custom_content) {
            $response = [
                'emailSubject' => $emailTemplate->template_subject,
                'content' => $emailTemplate->custom_content,
                'isCustom' => true,
            ];
        } else {
            $response = [
                'emailSubject' => $emailTemplate->template_subject,
                'content' => $emailTemplate->default_content,
                'isCustom' => false,
            ];
        }

        return $response;
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'subject' => 'required',
        ]);

        $success = EmailTemplate::updateData($id, [
            'template_subject' => $request->input('subject'),
            'custom_content' => $request->input('custom_content')
        ]);

        if (!$success) {
            return response()->json([
                'message' => Lang::get('lang.error_during_update')], 404);
        }

        $response = [
            'message' => Lang::get('lang.' . $request->template_name) . ' ' . Lang::get('lang.settings_saved_successfully'),
        ];

        return response()->json($response, 200);
    }
}