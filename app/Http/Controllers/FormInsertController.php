<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormInsertController extends Controller
{
    public function createForm(Request $request)
    {
        $request->validate([
            'formname' => 'required|string|max:255',
            'formdescription' => 'required|string|max:255',
        ]);

        Form::create([
            'formid' => Str::random(16),
            'form_name' => $request->formname,
            'form_description' => $request->formdescription,
            'created_by' => auth()->user()->employeeid,
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Form created successfully!');
    }
}
