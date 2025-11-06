<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('app');
    }

    public function welcome()
    {
        return view('welcome');
    }

    public function dashboard()
    {
        return view('app');
    }

    public function catchAll()
    {
        return view('app');
    }
}