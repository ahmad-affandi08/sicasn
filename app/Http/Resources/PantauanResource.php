<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Src\Pantauan\Application\ItemPantauan;

final class PantauanResource
{
    public static function item(ItemPantauan $pantauan): array
    {
        return FormasiResource::item($pantauan->item) + [
            'status' => $pantauan->item->formasi->status,
            'catatan' => $pantauan->catatan,
            'ditambahkanAt' => $pantauan->ditambahkanAt,
        ];
    }
}
