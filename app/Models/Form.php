<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory, Auditable;

    protected $table = 'form_list'; // your table name

    protected $fillable = [
        'formid',
        'form_name',
        'form_description',
        'created_by',
        'last_modified_by',
        'status'
    ];
}
