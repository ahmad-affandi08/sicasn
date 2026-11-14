<?php

declare(strict_types=1);

namespace Src\Shared\Application;

interface MailGateway
{
    public function kirim(PesanEmail $pesan): HasilKirim;
}
