<x-mail::message>
# Halo, {{ $nama }}

Akun SICASN Anda sudah siap. Gunakan data berikut untuk masuk.

<x-mail::panel>
**Email login:** {{ $email }}

**Kata sandi sementara:** `{!! $password !!}`
</x-mail::panel>

<x-mail::button :url="$tautanLogin">
Masuk ke SICASN
</x-mail::button>

Kata sandi sementara ini berlaku {{ $masaBerlakuHari }} hari dan **wajib diganti** saat Anda pertama kali masuk. Jangan bagikan kata sandi ini kepada siapa pun.

@if ($tautanWhatsapp)
Butuh bantuan? [Hubungi admin lewat WhatsApp]({{ $tautanWhatsapp }}).
@endif

Salam,<br>
Tim SICASN
</x-mail::message>
