<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class UserImportController extends Controller
{
    public function import()
    {
        Excel::import(new UsersImport, storage_path('users.csv'));

        return redirect()->back()->with(
            'success',
            'Users imported successfully!'
        );
    }
}
