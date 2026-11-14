import { describe, expect, it } from 'vitest';

import { isianAwal, isianAwalPenyedia, keMuatan } from './isian';
import type { InfoPenyedia, PengaturanEmailTampil } from './tipe';

const penyedia: InfoPenyedia[] = [
    { kode: 'hostinger', label: 'Hostinger', preset: { host: 'smtp.hostinger.com', port: 465, enkripsi: 'ssl', username: '', petunjuk: 'x' } },
    { kode: 'sendgrid', label: 'SendGrid', preset: { host: 'smtp.sendgrid.net', port: 587, enkripsi: 'tls', username: 'apikey', petunjuk: 'y' } },
];

const kosong: PengaturanEmailTampil = { aktif: null, cadangan: null, pengirimNama: 'SICASN', pengirimAlamat: '', alamatBalas: '', konfigurasi: {}, siap: false };

describe('isian pengaturan email', () => {
    it('mengisi otomatis dari preset penyedia yang belum pernah disimpan', () => {
        expect(isianAwalPenyedia('hostinger', penyedia, {})).toEqual({ host: 'smtp.hostinger.com', port: '465', enkripsi: 'ssl', username: '', password: '' });
        expect(isianAwalPenyedia('sendgrid', penyedia, {}).username).toBe('apikey');
    });

    it('memakai nilai tersimpan dan mengosongkan kata sandi', () => {
        const tersimpan = { hostinger: { host: 'smtp.kustom.id', port: 587, enkripsi: 'tls', username: 'a@b.id', passwordTersimpan: true } };

        expect(isianAwalPenyedia('hostinger', penyedia, tersimpan)).toEqual({ host: 'smtp.kustom.id', port: '587', enkripsi: 'tls', username: 'a@b.id', password: '' });
    });

    it('memilih penyedia pertama sebagai aktif bila belum ada pengaturan', () => {
        const isian = isianAwal(kosong, penyedia);

        expect(isian.aktif).toBe('hostinger');
        expect(Object.keys(isian.konfigurasi)).toEqual(['hostinger']);
    });

    it('hanya mengirim konfigurasi penyedia aktif dan cadangan dengan port numerik', () => {
        const isian = isianAwal({ ...kosong, aktif: 'hostinger', cadangan: 'sendgrid' }, penyedia);
        isian.konfigurasi['lain'] = { host: 'x', port: '1', enkripsi: 'tls', username: 'u', password: 'p' };

        const muatan = keMuatan(isian);

        expect(Object.keys(muatan.konfigurasi).sort()).toEqual(['hostinger', 'sendgrid']);
        expect(muatan.konfigurasi.hostinger?.port).toBe(465);
    });

    it('mengubah kolom kosong menjadi null untuk cadangan dan alamat balas', () => {
        const muatan = keMuatan(isianAwal(kosong, penyedia));

        expect(muatan.cadangan).toBeNull();
        expect(muatan.alamat_balas).toBeNull();
    });
});
