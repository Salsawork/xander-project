<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;   // WAJIB
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlayersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, Responsable
{
    use Exportable; // -> ini yang menyediakan toResponse() utk Responsable

    /** Nama file output saat diunduh */
    public string $fileName;

    /** Header HTTP opsional */
    public array $headers = [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    /** Filter pencarian opsional */
    protected ?string $search;

    public function __construct(?string $search = null)
    {
        $this->search   = $search;
        $this->fileName = 'players_' . now()->format('Ymd_His') . '.xlsx';
    }

    public function collection()
    {
        $q = User::query()
            ->where('roles', 'player')
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $s = $this->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                   ->orWhere('email', 'like', "%{$s}%");
            });
        }

        return $q->get(['name', 'email', 'status_player', 'created_at']);
    }

    public function headings(): array
    {
        return ['#', 'Name', 'Email', 'Status', 'Registered At'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->name,
            $row->email,
            (int)$row->status_player === 1 ? 'Terverifikasi' : 'Menunggu Verifikasi',
            optional($row->created_at)->format('d M Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Bold header
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        return [];
    }
}
