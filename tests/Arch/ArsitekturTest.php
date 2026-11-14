<?php

declare(strict_types=1);

$konteks = ['Formasi', 'DataImport', 'Pantauan', 'Identity', 'Pengaturan'];

foreach ($konteks as $nama) {
    arch("domain {$nama} tidak bergantung pada framework")
        ->expect("Src\\{$nama}\\Domain")
        ->not->toUse(['Illuminate', 'App', 'Laravel']);

    arch("application {$nama} tidak memakai eloquent, http, atau infrastructure")
        ->expect("Src\\{$nama}\\Application")
        ->not->toUse(['Illuminate\\Database\\Eloquent', 'Illuminate\\Http', 'App']);
}

arch('tidak ada fungsi debug')
    ->expect(['dd', 'dump', 'var_dump', 'print_r', 'ray'])
    ->not->toBeUsed();

arch('controller tidak memakai eloquent langsung')
    ->expect('App\\Http')
    ->not->toUse(['Illuminate\\Database\\Eloquent', 'Illuminate\\Support\\Facades\\DB']);

arch('model eloquent hanya di infrastructure')
    ->expect('Illuminate\\Database\\Eloquent\\Model')
    ->toOnlyBeUsedIn('Src\\Identity\\Infrastructure');
