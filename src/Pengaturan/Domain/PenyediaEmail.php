<?php

declare(strict_types=1);

namespace Src\Pengaturan\Domain;

enum PenyediaEmail: string
{
    case Hostinger = 'hostinger';
    case SmtpKustom = 'smtp_kustom';
    case Gmail = 'gmail';
    case Brevo = 'brevo';
    case SendGrid = 'sendgrid';
    case Mailgun = 'mailgun';
    case AmazonSes = 'amazon_ses';
    case Resend = 'resend';
    case Postmark = 'postmark';

    public function label(): string
    {
        return match ($this) {
            self::Hostinger => 'Hostinger',
            self::SmtpKustom => 'SMTP Kustom',
            self::Gmail => 'Gmail / Google Workspace',
            self::Brevo => 'Brevo',
            self::SendGrid => 'SendGrid',
            self::Mailgun => 'Mailgun',
            self::AmazonSes => 'Amazon SES',
            self::Resend => 'Resend',
            self::Postmark => 'Postmark',
        };
    }

    public function preset(): PresetSmtp
    {
        return match ($this) {
            self::Hostinger => new PresetSmtp('smtp.hostinger.com', 465, Enkripsi::Ssl, '', 'Username adalah alamat email lengkap yang dibuat di Hostinger, misalnya noreply@domainanda.com.'),
            self::SmtpKustom => new PresetSmtp('', 587, Enkripsi::Tls, '', 'Isi sesuai informasi SMTP dari penyedia email Anda.'),
            self::Gmail => new PresetSmtp('smtp.gmail.com', 587, Enkripsi::Tls, '', 'Wajib memakai sandi aplikasi (app password), bukan sandi akun Google.'),
            self::Brevo => new PresetSmtp('smtp-relay.brevo.com', 587, Enkripsi::Tls, '', 'Gunakan login SMTP dan kunci SMTP dari dashboard Brevo.'),
            self::SendGrid => new PresetSmtp('smtp.sendgrid.net', 587, Enkripsi::Tls, 'apikey', 'Username selalu "apikey"; kata sandi adalah API key SendGrid.'),
            self::Mailgun => new PresetSmtp('smtp.mailgun.org', 587, Enkripsi::Tls, '', 'Untuk region Eropa gunakan smtp.eu.mailgun.org. Username dan sandi dari kredensial SMTP domain.'),
            self::AmazonSes => new PresetSmtp('email-smtp.ap-southeast-1.amazonaws.com', 587, Enkripsi::Tls, '', 'Ganti region pada host sesuai akun Anda. Gunakan kredensial SMTP SES, bukan kunci akses IAM.'),
            self::Resend => new PresetSmtp('smtp.resend.com', 465, Enkripsi::Ssl, 'resend', 'Username selalu "resend"; kata sandi adalah API key Resend.'),
            self::Postmark => new PresetSmtp('smtp.postmarkapp.com', 587, Enkripsi::Tls, '', 'Username dan kata sandi sama-sama server token Postmark.'),
        };
    }
}
