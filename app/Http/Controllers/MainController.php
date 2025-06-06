<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
    //
    public function showDashboard(){
        return view('res.home');
    }

    public function showCreate(){
        return view('res.create', );
    }

    public function showVerify(){
        return view('res.verify');
    }

    public function showFiles(){
        return view('res.files');
    }
}
