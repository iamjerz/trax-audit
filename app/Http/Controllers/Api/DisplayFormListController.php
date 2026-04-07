<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;

class DisplayFormListController extends Controller
{
    public function displayFormList(Request $request)
    {

        // Ensure the request expects a JSON response
        if (!$request->expectsJson()) {
            abort(403);
        }

        $forms = Form::select('formid', 'form_name', 'created_by', 'status')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($forms);
    }
}
