<?php

namespace App\Exports;

use App\Models\Opinion;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;   // <-- WAJIB ada
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OpinionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, Responsable
{
    use Exportable; // <-- INI yang menyediakan toResponse() agar bisa return instance langsung

    /** Nama file output saat diunduh */
    public string $fileName;

    /** Header HTTP opsional (biar MIME type-nya pas) */
    public array $headers = [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    /** Filter pencarian opsional */
    protected ?string $search;

    public function __construct(?string $search = null)
    {
        $this->search   = $search;
        $this->fileName = 'opinions_' . now()->format('Ymd_His') . '.xlsx';
    }

    public function collection()
    {
        $q = Opinion::query()->orderBy('created_at', 'desc');

        if ($this->search) {
            $s = $this->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('email', 'like', "%{$s}%")
                   ->orWhere('subject', 'like', "%{$s}%")
                   ->orWhere('description', 'like', "%{$s}%");
            });
        }

        return $q->get();
    }

    public function headings(): array
    {
        return ['#', 'Email', 'Subject', 'Description', 'Tanggal'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->email,
            $row->subject,
            $row->description,
            optional($row->created_at)->format('d M Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold header
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        // Wrap text untuk kolom deskripsi
        $sheet->getStyle('D:D')->getAlignment()->setWrapText(true);

        return [];
    }
}
