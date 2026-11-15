<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Src\DataImport\Application\BknFormasiSource;
use Src\DataImport\Application\EventTerdeteksiRepository;
use Src\DataImport\Application\ImportBatchRepository;
use Src\DataImport\Application\KebijakanJenisPengadaan;
use Src\DataImport\Application\PemicuSinkron;
use Src\DataImport\Application\PengaturanSinkronStore;
use Src\DataImport\Application\PenyimpananPratinjau;
use Src\DataImport\Infrastructure\BerkasPenyimpananPratinjau;
use Src\DataImport\Infrastructure\ConfigKebijakanJenisPengadaan;
use Src\DataImport\Infrastructure\HttpBknFormasiSource;
use Src\DataImport\Infrastructure\Persistence\EloquentEventTerdeteksiRepository;
use Src\DataImport\Infrastructure\Persistence\EloquentImportBatchRepository;
use Src\DataImport\Infrastructure\Queue\AntreanPemicuSinkron;
use Src\DataImport\Infrastructure\SettingsPengaturanSinkronStore;
use Src\Formasi\Application\DetailFormasiRepository;
use Src\Formasi\Application\KatalogFormasi;
use Src\Formasi\Application\KebijakanFormasiAktif;
use Src\Formasi\Application\OpsiFormasiRepository;
use Src\Formasi\Application\PeluangInstansiRepository;
use Src\Formasi\Application\PencarianFormasi;
use Src\Formasi\Application\PenyediaAmbangPersaingan;
use Src\Formasi\Application\ReferensiInstansi;
use Src\Formasi\Application\ReferensiWilayahRepository;
use Src\Formasi\Application\StatistikRepository;
use Src\Formasi\Application\SumberReferensiWilayah;
use Src\Formasi\Infrastructure\BerkasReferensiWilayah;
use Src\Formasi\Infrastructure\ConfigKebijakanFormasiAktif;
use Src\Formasi\Infrastructure\Persistence\EloquentDetailFormasi;
use Src\Formasi\Infrastructure\Persistence\EloquentKatalogFormasi;
use Src\Formasi\Infrastructure\Persistence\EloquentOpsiFormasi;
use Src\Formasi\Infrastructure\Persistence\EloquentPeluangInstansiRepository;
use Src\Formasi\Infrastructure\Persistence\EloquentPencarianFormasi;
use Src\Formasi\Infrastructure\Persistence\EloquentReferensiInstansi;
use Src\Formasi\Infrastructure\Persistence\EloquentReferensiWilayah;
use Src\Formasi\Infrastructure\Persistence\EloquentStatistikRepository;
use Src\Identity\Application\Authenticator;
use Src\Identity\Application\PasswordGenerator;
use Src\Identity\Application\PasswordHasher;
use Src\Identity\Application\PenggunaRepository;
use Src\Identity\Application\PengirimKredensial;
use Src\Identity\Infrastructure\LaravelPasswordHasher;
use Src\Identity\Infrastructure\Persistence\EloquentPenggunaRepository;
use Src\Identity\Infrastructure\Queue\AntreanPengirimKredensial;
use Src\Identity\Infrastructure\SecurePasswordGenerator;
use Src\Identity\Infrastructure\SessionAuthenticator;
use Src\Pantauan\Application\PantauanRepository;
use Src\Pantauan\Application\SumberFormasi;
use Src\Pantauan\Infrastructure\EloquentPantauanRepository;
use Src\Pantauan\Infrastructure\SumberFormasiDariKatalog;
use Src\Pengaturan\Application\PengaturanEmailStore;
use Src\Pengaturan\Application\PengaturanPembayaranStore;
use Src\Pengaturan\Application\PengaturanPersainganStore;
use Src\Pengaturan\Application\PengaturanSitusStore;
use Src\Pengaturan\Application\PengaturanWhatsappStore;
use Src\Pengaturan\Application\SettingsRepository;
use Src\Pengaturan\Infrastructure\Mail\LaravelMailGateway;
use Src\Pengaturan\Infrastructure\Mail\LaravelPembangunMailer;
use Src\Pengaturan\Infrastructure\Mail\PembangunMailer;
use Src\Pengaturan\Infrastructure\Persistence\EloquentSettingsRepository;
use Src\Pengaturan\Infrastructure\SettingsEmailTerkonfigurasi;
use Src\Pengaturan\Infrastructure\SettingsPengaturanEmailStore;
use Src\Pengaturan\Infrastructure\SettingsPengaturanPembayaranStore;
use Src\Pengaturan\Infrastructure\SettingsPengaturanPersainganStore;
use Src\Pengaturan\Infrastructure\SettingsPengaturanSitusStore;
use Src\Pengaturan\Infrastructure\SettingsPengaturanWhatsappStore;
use Src\Shared\Application\AuditLog;
use Src\Shared\Application\Clock;
use Src\Shared\Application\EmailTerkonfigurasi;
use Src\Shared\Application\LogEmail;
use Src\Shared\Application\MailGateway;
use Src\Shared\Infrastructure\EloquentAuditLog;
use Src\Shared\Infrastructure\EloquentLogEmail;
use Src\Shared\Infrastructure\SystemClock;

final class AppServiceProvider extends ServiceProvider
{
    public array $bindings = [
        Clock::class => SystemClock::class,
        AuditLog::class => EloquentAuditLog::class,
        LogEmail::class => EloquentLogEmail::class,
        MailGateway::class => LaravelMailGateway::class,
        EmailTerkonfigurasi::class => SettingsEmailTerkonfigurasi::class,
        PembangunMailer::class => LaravelPembangunMailer::class,
        PenggunaRepository::class => EloquentPenggunaRepository::class,
        PasswordHasher::class => LaravelPasswordHasher::class,
        PasswordGenerator::class => SecurePasswordGenerator::class,
        PengirimKredensial::class => AntreanPengirimKredensial::class,
        Authenticator::class => SessionAuthenticator::class,
        BknFormasiSource::class => HttpBknFormasiSource::class,
        KatalogFormasi::class => EloquentKatalogFormasi::class,
        ImportBatchRepository::class => EloquentImportBatchRepository::class,
        EventTerdeteksiRepository::class => EloquentEventTerdeteksiRepository::class,
        PengaturanSinkronStore::class => SettingsPengaturanSinkronStore::class,
        KebijakanJenisPengadaan::class => ConfigKebijakanJenisPengadaan::class,
        PemicuSinkron::class => AntreanPemicuSinkron::class,
        StatistikRepository::class => EloquentStatistikRepository::class,
        ReferensiInstansi::class => EloquentReferensiInstansi::class,
        PeluangInstansiRepository::class => EloquentPeluangInstansiRepository::class,
        PantauanRepository::class => EloquentPantauanRepository::class,
        SumberFormasi::class => SumberFormasiDariKatalog::class,
        PencarianFormasi::class => EloquentPencarianFormasi::class,
        OpsiFormasiRepository::class => EloquentOpsiFormasi::class,
        DetailFormasiRepository::class => EloquentDetailFormasi::class,
        KebijakanFormasiAktif::class => ConfigKebijakanFormasiAktif::class,
        SumberReferensiWilayah::class => BerkasReferensiWilayah::class,
        ReferensiWilayahRepository::class => EloquentReferensiWilayah::class,
        PenyimpananPratinjau::class => BerkasPenyimpananPratinjau::class,
        PengaturanPersainganStore::class => SettingsPengaturanPersainganStore::class,
        PenyediaAmbangPersaingan::class => SettingsPengaturanPersainganStore::class,
        SettingsRepository::class => EloquentSettingsRepository::class,
        PengaturanEmailStore::class => SettingsPengaturanEmailStore::class,
        PengaturanWhatsappStore::class => SettingsPengaturanWhatsappStore::class,
        PengaturanPembayaranStore::class => SettingsPengaturanPembayaranStore::class,
        PengaturanSitusStore::class => SettingsPengaturanSitusStore::class,
    ];
}
