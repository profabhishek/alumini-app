<?php

namespace App\Exports;

use App\Models\NewsletterSubscriber;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NewsletterSubscribersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        return NewsletterSubscriber::query()->orderBy('email');
    }

    public function headings(): array
    {
        return ['Email', 'Status', 'Subscribed At', 'Unsubscribed At'];
    }

    /**
     * @param NewsletterSubscriber $subscriber
     */
    public function map($subscriber): array
    {
        return [
            $subscriber->email,
            ucfirst($subscriber->status),
            $subscriber->subscribed_at?->format('Y-m-d H:i') ?? '',
            $subscriber->unsubscribed_at?->format('Y-m-d H:i') ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}