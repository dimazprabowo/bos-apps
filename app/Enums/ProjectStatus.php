<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Draft',
            self::Active => 'Aktif',
            self::Closed => 'Ditutup',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Draft => 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
            self::Active => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            self::Closed => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::Draft => 'Project masih dalam tahap penyusunan, dapat diedit dan belum diajukan untuk persetujuan',
            self::Active => 'Project telah disetujui dan sedang berjalan',
            self::Closed => 'Project telah ditutup dan tidak dapat diedit atau diajukan kembali',
        };
    }

    /**
     * A project can only be edited while still a draft.
     */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function isFinal(): bool
    {
        return $this === self::Closed;
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
