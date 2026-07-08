<?php

namespace App\Controllers;

class MyDashboardDev extends BaseController
{
    public function index(){
        if (!session('__xsys_myuserzicas_is_logged__')) {
            return view('MyLogin');
        }

        return view('MyDashboardDev');
    }

}
