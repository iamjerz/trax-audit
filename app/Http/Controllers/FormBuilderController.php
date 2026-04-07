<?php

namespace App\Http\Controllers;
use App\Models\Form;

use Illuminate\Http\Request;

class FormBuilderController extends Controller
{
    public function show($id)
    {
        $form = Form::where('formid', $id)->firstOrFail();
        return view('formbuilder', compact('form'));
    }

}