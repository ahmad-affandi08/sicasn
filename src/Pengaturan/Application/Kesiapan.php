<?php

declare(strict_types=1);

namespace Src\Pengaturan\Application;

final readonly class Kesiapan
{
    public function __construct(
        public bool $email,
        public bool $whatsapp,
        public bool $pembayaran,
    ) {}
}
