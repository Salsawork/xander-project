<?php

namespace App\Exports;

use App\Models\Venue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VenuesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected ?string $search;
    protected int $rowNumber = 0;

    public function __construct(?string $search = null)
    {
        $this->search = $search;
    }

    /**
     * Query utama untuk export data.
     */
    public function query()
    {
        $query = Venue::with('user');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('address', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function ($u) {
                      $u->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Mapping tiap baris ke kolom Excel.
     */
    public function map($venue): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $venue->name,
            $venue->user ? $venue->user->name . ' (' . $venue->user->email . ')' : '-',
            $venue->address ?? '-',
            $venue->phone ?? '-',
            $venue->operating_hours ?? '-',
            number_format($venue->rating ?? 0, 1),
        ];
    }

    /**
     * Header kolom di Excel.
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Venue',
            'Pemilik',
            'Alamat',
            'Kontak',
            'Jam Operasional',
            'Rating',
        ];
    }

    /**
     * Styling: Bold header + auto width kolom.
     */
    public function styles(Worksheet $sheet)
    {
        // Header tebal
        $sheet->getStyle('1')->getFont()->setBold(true);

        // Auto size untuk semua kolom
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
