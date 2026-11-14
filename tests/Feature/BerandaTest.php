<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

it('menampilkan beranda untuk pengunjung', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Beranda')
            ->where('aplikasi.nama', 'SICASN')
            ->where('whatsappUrl', null));
});

it('menyediakan endpoint kesehatan', function (): void {
    $this->get('/up')->assertOk();
});
