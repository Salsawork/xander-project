<?php

namespace App\Exports;

use App\Models\Subscriber;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubscribersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected ?string $search;

    public function __construct(?string $search = null)
    {
        $this->search = $search;
    }

    /**
     * Gunakan query agar hemat memori saat data besar.
     */
    public function query()
    {
        $q = Subscriber::query();

        if (!empty($this->search)) {
            $q->where('email', 'like', '%' . $this->search . '%');
        }

        return $q->orderBy('created_at', 'desc');
    }

    /**
     * Header kolom di Excel.
     */
    public function headings(): array
    {
        return ['ID', 'Email', 'Registered At'];
    }

    /**
     * Mapping tiap baris ke kolom Excel.
     */
    public function map($subscriber): array
    {
        return [
            $subscriber->id,
            $subscriber->email,
            optional($subscriber->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
