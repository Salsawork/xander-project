<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected ?string $search;
    protected int $rowNumber = 0;

    public function __construct(?string $search = null)
    {
        $this->search = $search;
    }

    public function query()
    {
        return Event::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('location', 'like', "%{$this->search}%")
                      ->orWhere('status', 'like', "%{$this->search}%");
            })
            ->orderBy('start_date', 'asc');
    }

    public function map($event): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $event->name,
            $event->location ?? '-',
            optional($event->start_date)->format('Y-m-d'),
            optional($event->end_date)->format('Y-m-d'),
            ucfirst($event->status ?? '-'),
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Event',
            'Lokasi',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header bold
        $sheet->getStyle('1')->getFont()->setBold(true);

        // Kolom auto width
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
