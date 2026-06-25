<?php

namespace App\Enums;

enum PeralatanReviewStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Review Peralatan',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
            self::Approved => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            self::Rejected => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Pending => 'Peralatan menunggu review dari tim reviewer',
            self::Approved => 'Peralatan telah disetujui dan siap digunakan',
            self::Rejected => 'Peralatan ditolak, perlu revisi sebelum diajukan kembali',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->label(),
        ])->toArray();
    }
}
