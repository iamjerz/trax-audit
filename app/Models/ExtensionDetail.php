<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class ExtensionDetail extends Model
{
    use Auditable;

    // Maps to the existing extension_details table.
    protected $fillable = [
        'version',
        'item_id',
        'status',
        'created_by',
    ];
}
