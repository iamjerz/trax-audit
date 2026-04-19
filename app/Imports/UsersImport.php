<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class UsersImport implements ToModel, WithHeadingRow, WithUpserts
{
    public function model(array $row)
    {

    set_time_limit(300);
        return new User([
            'employeeid'        => $row['employeeid'],
            'first_name'        => $row['first_name'],
            'last_name'         => $row['last_name'],
            'position'          => $row['position'],
            'department'        => $row['department'],
            'supervisor_id'     => $row['supervisor_id'],
            'email'             => strtolower($row['email']),
            'role'              => 'user',
            'status'            => 'active',
            'email_verified_at' => now(), // optional
            'password'          => Hash::make('password123'), // default password
        ]);
    }

    // Prevent duplicate imports
    public function uniqueBy()
    {
        return 'email';
    }
}
