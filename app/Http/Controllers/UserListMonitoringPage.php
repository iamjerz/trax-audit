<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;

class UserListMonitoringPage extends Controller
{
    public function getUsersData()
    {
        return [
            'logisticsUsers' => User::select('employeeid', 'first_name', 'last_name')
                ->where('position', 'Logistics Data Analyst')
                ->orderBy('first_name')
                ->get(),

            'supervisors' => User::select('employeeid', 'first_name', 'last_name')
                ->where('position', '!=', 'LDA')
                ->orderBy('first_name')
                ->get(),

            'allusers' => User::select('employeeid', 'first_name', 'last_name')
                ->orderBy('first_name')
                ->get(),
        ];
    }

    public function UserList()
    {
        $data = $this->getUsersData();
        return view('monitoringform', $data);
    }

    public function EvalIndiData()
    {
        $data = $this->getUsersData();
        return view('individualEval', $data);
    }

    public function CoachingTriadData()
    {
        $data = $this->getUsersData();
        return view('coachingtriadform', $data);
    }

    public function CoachingFormData()
    {
        $data = $this->getUsersData();
        return view('coachingform', $data);
    }

    public function SelectionUserList()
    {
        $data = $this->getUsersData();
        return view('extension.selection', $data);
    }

    public function UserPageList()
    {
        $data = $this->getUsersData();
        return view('users', $data);
    }
    
}
