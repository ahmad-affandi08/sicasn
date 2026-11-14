<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

final class EmailLogModel extends Model
{
    protected $table = 'email_logs';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['dikirim_at' => 'datetime'];
    }
}
