<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Canonical lookup list of client codes, managed via the Client & Carrier
 * Codes admin page (/client-carrier-codes). Read elsewhere by `name` only —
 * ReconFieldController::index() and DefaultFieldApi::index() both pull
 * straight from this table to populate Client Code dropdowns. Renaming or
 * deleting a row here does not touch any existing ticket/audit data, since
 * those tables store the code as a plain string, not a foreign key.
 *
 * Note: the QA Monitoring form's Client Code combo
 * (resources/views/extension/qa.blade.php) is driven by a separate CSV file
 * via ComboController/DropdownService, not this table.
 */
class ClientCode extends Model
{
    protected $table = 'client_codes';

    // Table has a single `timestamp` column (useCurrent()), not the
    // standard created_at/updated_at pair.
    public $timestamps = false;

    protected $fillable = [
        'name',
        'added_by',
    ];
}
