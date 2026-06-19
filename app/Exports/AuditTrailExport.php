<?php

namespace App\Exports;

use App\Models\AuditTrail;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AuditTrailExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(private array $filters = []) {}

    public function headings(): array
    {
        return ['When', 'Actor', 'Employee ID', 'Event', 'Description', 'IP Address'];
    }

    public function array(): array
    {
        $query = AuditTrail::query()->orderByDesc('id');

        $f = $this->filters;
        if (!empty($f['search'])) {
            $s = $f['search'];
            $query->where(function ($q) use ($s) {
                $q->where('actor_name', 'like', "%{$s}%")
                  ->orWhere('employeeid', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('auditable_type', 'like', "%{$s}%");
            });
        }
        if (!empty($f['event']))     $query->where('event', $f['event']);
        if (!empty($f['date_from'])) $query->whereDate('created_at', '>=', $f['date_from']);
        if (!empty($f['date_to']))   $query->whereDate('created_at', '<=', $f['date_to']);

        return $query->get()->map(fn ($log) => [
            optional($log->created_at)->format('Y-m-d H:i:s'),
            $log->actor_name,
            $log->employeeid,
            $log->event,
            $log->description,
            $log->ip_address,
        ])->toArray();
    }
}
