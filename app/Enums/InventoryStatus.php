<?php

namespace App\Enums;

enum InventoryStatus: string
{
    case NORMAL = 'normal';
    case CRITICAL = 'critical';
    case OUT_OF_STOCK = 'out_of_stock';

    /**
     * Statuses that require inventory attention.
     *
     * @return array<int, self>
     */
    public static function attention(): array
    {
        return [
            self::CRITICAL,
            self::OUT_OF_STOCK,
        ];
    }

    /**
     * Get the attention statuses as scalar values.
     *
     * @return array<int, string>
     */
    public static function attentionValues(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::attention());
    }
}
