<?php

namespace App\Exports;

use App\Models\AthleteDetail;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AthleteExport implements FromQuery, WithHeadings, WithMapping, WithStyles
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
        $q = AthleteDetail::query()->with('user');

        if (!empty($this->search)) {
            $q->whereHas('user', function ($u) {
                $u->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orWhere('specialty', 'like', "%{$this->search}%")
            ->orWhere('location', 'like', "%{$this->search}%");
        }

        return $q->orderBy('created_at', 'desc');
    }

    public function map($athlete): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber, // Nomor urut otomatis
            $athlete->user?->name ?? '-',
            $athlete->user?->email ?? '-',
            $athlete->specialty ?? '-',
            $athlete->location ?? '-',
            'Rp ' . number_format($athlete->price_per_session ?? 0, 0, ',', '.'),
            $athlete->experience_years ? $athlete->experience_years . ' tahun' : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Email',
            'Spesialisasi',
            'Lokasi',
            'Harga per Sesi',
            'Pengalaman',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Heading (baris pertama) dibuat bold
        $sheet->getStyle('1')->getFont()->setBold(true);

        // Lebar kolom otomatis menyesuaikan isi
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
