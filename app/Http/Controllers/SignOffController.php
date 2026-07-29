<?php

namespace App\Http\Controllers;

class SignOffController extends Controller
{
    public function index()
    {
        return view('signoff');
    }

    public function signOffForms()
    {
        return view('signoffform');
    }
}
