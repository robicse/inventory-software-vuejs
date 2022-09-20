<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Libraries\Permissions;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use DB;

class ContactController extends Controller
{

    public function permissionCheck()
    {
        $controller = new Permissions;
        return $controller;
    }

    public function index()
    {
        $tabName = '';
        $routeName = '';
        if(isset($_GET['tab_name'])){
            $tabName = $_GET['tab_name'];
        }
        if(isset($_GET['route_name'])){
            $routeName = $_GET['route_name'];
        }
        return view('contacts.ContactsIndex',['tab_name'=>$tabName, 'route_name'=>$routeName]);

    }
}