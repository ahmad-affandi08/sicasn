<?php

declare(strict_types=1);

namespace Src\Pengaturan\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

final class PengaturanModel extends Model
{
    protected $table = 'pengaturans';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['terenkripsi' => 'boolean'];
    }
}
