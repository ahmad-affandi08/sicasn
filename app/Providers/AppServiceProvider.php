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
use Src\DataImport\Infrastructure\ConfigKebijakanJenisPengadaan;
use Src\DataImport\Infrastructure\HttpBknFormasiSource;
use Src\DataImport\Infrastructure\Persistence\EloquentEventTerdeteksiRepository;
use Src\DataImport\Infrastructure\Persistence\EloquentImportBatchRepository;
use Src\DataImport\Infrastructure\Queue\AntreanPemicuSinkron;
use Src\DataImport\Infrastructure\SettingsPengaturanSinkronStore;
use Src\Formasi\Application\KatalogFormasi;
use Src\Formasi\Infrastructure\Persistence\EloquentKatalogFormasi;
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
use Src\Pengaturan\Application\PengaturanEmailStore;
use Src\Pengaturan\Application\PengaturanPembayaranStore;
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
        SettingsRepository::class => EloquentSettingsRepository::class,
        PengaturanEmailStore::class => SettingsPengaturanEmailStore::class,
        PengaturanWhatsappStore::class => SettingsPengaturanWhatsappStore::class,
        PengaturanPembayaranStore::class => SettingsPengaturanPembayaranStore::class,
        PengaturanSitusStore::class => SettingsPengaturanSitusStore::class,
    ];
}
