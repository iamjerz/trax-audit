<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReconExport extends DefaultValueBinder implements FromArray, WithHeadings, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder
{
    // Columns that must stay as text (long IDs Excel would turn into 1.78E+12).
    // A = Submission ID, I = Jira Ticket
    private const TEXT_COLUMNS = ['A', 'I'];

    private array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function headings(): array
    {
        // Matches the columns shown on the /recon-ticket list.
        return [
            'Submission ID', 'Name', 'Recon Date', 'Client Code', 'Carrier Code',
            'Region', 'Action Item Summary', 'Action Item Details', 'Jira Ticket',
            'Status', 'Created At',
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), self::TEXT_COLUMNS, true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function array(): array
    {
        $search       = $this->filters['search'] ?? null;
        $f_name       = $this->filters['name'] ?? null;
        $f_client     = $this->filters['client_code'] ?? null;
        $f_carrier    = $this->filters['carrier_code'] ?? null;
        $f_status     = $this->filters['status'] ?? null;
        $f_date_from  = $this->filters['date_from'] ?? null;
        $f_date_to    = $this->filters['date_to'] ?? null;

        $query = DB::table('recon_action_items')
            ->leftJoin('users', 'recon_action_items.lda_email', '=', 'users.email')
            ->select(
                'recon_action_items.*',
                DB::raw("users.first_name || ' ' || users.last_name as full_name")
            );

        // 🔍 Global search (same as the list endpoint)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('recon_action_items.submission_id', 'ilike', "%{$search}%")
                  ->orWhere('recon_action_items.client_code', 'ilike', "%{$search}%")
                  ->orWhere('recon_action_items.carrier_code', 'ilike', "%{$search}%")
                  ->orWhere('recon_action_items.region', 'ilike', "%{$search}%")
                  ->orWhere('recon_action_items.status', 'ilike', "%{$search}%")
                  ->orWhere('users.first_name', 'ilike', "%{$search}%")
                  ->orWhere('users.last_name', 'ilike', "%{$search}%")
                  ->orWhere(DB::raw("users.first_name || ' ' || users.last_name"), 'ilike', "%{$search}%");
            });
        }

        // Name filter
        if ($f_name) {
            $query->where(function ($q) use ($f_name) {
                $q->where('users.first_name', 'ilike', "%{$f_name}%")
                  ->orWhere('users.last_name', 'ilike', "%{$f_name}%")
                  ->orWhere(DB::raw("users.first_name || ' ' || users.last_name"), 'ilike', "%{$f_name}%");
            });
        }

        if ($f_client)  $query->where('recon_action_items.client_code', $f_client);
        if ($f_carrier) $query->where('recon_action_items.carrier_code', $f_carrier);
        if ($f_status)  $query->where('recon_action_items.status', $f_status);

        if ($f_date_from) $query->whereDate('recon_action_items.recon_call_date', '>=', $f_date_from);
        if ($f_date_to)   $query->whereDate('recon_action_items.recon_call_date', '<=', $f_date_to);

        // 👤 Role filter — LDAs only see their own tickets (mirrors displayTicket)
        $user = auth()->user();
        if ($user && $user->position === 'LDA') {
            $query->where(function ($q) use ($user) {
                $q->where('recon_action_items.lda_email', $user->email)
                  ->orWhere('recon_action_items.assigned_to', $user->employeeid);
            });
        }

        return $query
            ->orderByDesc('recon_action_items.id')
            ->get()
            ->map(function ($r) {
                return [
                    $r->submission_id,
                    $r->full_name,
                    $r->recon_call_date,
                    $r->client_code,
                    $r->carrier_code,
                    $r->region,
                    $r->action_item_summary,
                    $r->action_item_details,
                    $r->jira_ticket,
                    $r->status,
                    $r->created_at,
                ];
            })
            ->toArray();
    }
}
