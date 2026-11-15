<?php

declare(strict_types=1);

namespace Src\Formasi\Domain;

enum TipeInstansi: string
{
    case Pusat = 'pusat';
    case Daerah = 'daerah';
    case TidakDiketahui = 'unknown';

    public static function dariNama(string $nama): self
    {
        if (preg_match('/^(?:pemerintah\s+)?(?:provinsi|kabupaten|kota)\b|^(?:pemprov|pemkab|pemkot)\b/iu', trim($nama)) === 1) {
            return self::Daerah;
        }

        if (preg_match('/^(?:kementerian|lembaga|komisi|mahkamah|kejaksaan|dewan|ombudsman|otoritas)\b/iu', trim($nama)) === 1) {
            return self::Pusat;
        }

        return self::TidakDiketahui;
    }
}
