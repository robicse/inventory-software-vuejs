<?php

namespace App\Http\Controllers\API;

use App\Models\CustomUser;
use App\User;
use App\Models\Setting;
use DateTimeZone;
use Defuse\Crypto\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Libraries\AllSettingFormat;
use App\Libraries\imageHandler;

class ProfileController extends Controller
{
    public function index()
    {
        return response()->json([
            'profile' => Auth::user(),
            'dateformat' => $this->dateFormat(),
        ], 200);
    }

    public function update(Request $request)
    {
        $user_id = Auth::user()->id;
        $avatar = '';

        if ($request->avatar == Auth::user()->avatar) {
            $avatar = Auth::user()->avatar;

            $emailUsed = CustomUser::userEmailExists($user_id, $request->email);
            if ($emailUsed) {
                $userData = ['first_name' => $request->first_name, 'last_name' => $request->last_name,'gender' => $request->gender, 'date_of_birth' => $request->date_of_birth];
                $message=Lang::get('lang.profile').' '.Lang::get('lang.successfully_saved').' '.Lang::get('lang.but_email_already_exists');

            }else{
                $userData = ['first_name' => $request->first_name, 'last_name' => $request->last_name, 'email' => $request->email, 'gender' => $request->gender, 'date_of_birth' => $request->date_of_birth];
                $message=Lang::get('lang.profile').' '.Lang::get('lang.successfully_saved');
            }


            CustomUser::updateData($user_id, $userData);

        } else {
            if ($file = $request->avatar) {
                $imageHandler = new imageHandler;
                $avatar = $imageHandler->imageUpload($request->avatar, 'profile_', 'uploads/profile/');
                $userData['avatar'] = $avatar;

                CustomUser::updateData($user_id, $userData);

                if (Auth::user()->avatar != 'default.jpg') {
                    unlink('uploads/profile/' . Auth::user()->avatar);
                }
            }
            $message=Lang::get('lang.profile').' '.Lang::get('lang.successfully_saved');
        }

        return response()->json(['message' =>$message]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);
        $user = Auth::user();
        $password = $request->password;

        CustomUser::updatePassword($user, $password);

        return response()->json(['message' => Lang::get('lang.password_updated')]);
    }

    public function myindex()
    {
        $user = Auth::user();
        $dataFormat = Setting::getSettingValue('date_format')->setting_value;

         if($dataFormat == "d/m/Y"){
            $user->dateformat = "dd/MM/yyyy";
         }
         if($dataFormat == "m/d/Y"){
            $user->dateformat = "MM/dd/yyyy";
         }
         if($dataFormat == "Y/m/d"){
            $user->dateformat = "yyyy/MM/dd";
         }
         if($dataFormat == "d-m-Y"){
            $user->dateformat = "dd-MM-yyyy";
          }
         if($dataFormat == "m-d-Y"){
            $user->dateformat = "MM-dd-yyyy";
         }
         if($dataFormat == "Y-m-d"){
            $user->dateformat = "yyyy-MM-dd";
         }
         if($dataFormat == "d.m.Y"){
            $user->dateformat = "dd.MM.yyyy";
         }
         if($dataFormat == "m.d.Y"){
            $user->dateformat = "MM.dd.yyyy";
         }
         if($dataFormat == "Y.m.d"){
            $user->dateformat = "yyyy.MM.dd";
         }

        return view('users.profile', ['profile' => $user]);

    }

    public function getTimezone()
    {
        return $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    }

    public function dateFormat()
    {
        $allSettingFormat = new AllSettingFormat;

        return $allSettingFormat->getDateFormat();
    }
}