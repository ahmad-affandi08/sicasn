<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

final class AuditLogModel extends Model
{
    protected $table = 'audit_logs';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['rincian' => 'array'];
    }
}
