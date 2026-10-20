<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Column contract only — read via Excel::toCollection() in
 * UserBulkImportController, which hands the raw rows to
 * UserBulkEditService::validateRows() to make every create/update/error
 * decision itself (so a full preview can be shown before anything touches
 * the database, instead of Maatwebsite's own ToModel/WithUpserts magic
 * silently writing on the way in). See UsersBulkEditExport for the exact
 * columns this expects back.
 */
class UsersBulkEditImport implements WithHeadingRow, SkipsEmptyRows
{
}
