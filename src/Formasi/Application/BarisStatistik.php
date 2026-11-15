<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

use Src\Formasi\Domain\Cakupan;
use Src\Formasi\Domain\JenisPengadaan;
use Src\Formasi\Domain\Keyakinan;
use Src\Formasi\Domain\NamaNormal;
use Src\Formasi\Domain\StatusAngka;
use Src\Formasi\Domain\TingkatData;

final readonly class BarisStatistik
{
    public function __construct(
        public int $tahun,
        public JenisPengadaan $jenisPengadaan,
        public Cakupan $cakupan,
        public ?string $namaInstansi,
        public ?string $kodeJabatan,
        public ?string $namaJabatan,
        public ?string $lokasi,
        public ?int $jumlahFormasi,
        public ?int $jumlahPelamar,
        public ?int $jumlahSubmit,
        public ?int $jumlahLolosAdministrasi,
        public string $tanggalSnapshot,
        public StatusAngka $statusAngka,
        public TingkatData $tingkatData,
        public Keyakinan $keyakinan,
        public string $sumberNama,
        public ?string $sumberUrl,
        public ?string $catatan,
    ) {}

    public function kunci(?int $instansiId): string
    {
        $bagian = [$this->tahun, $this->jenisPengadaan->value, $this->cakupan->value, $instansiId ?? '-', $this->tanggalSnapshot, mb_strtolower($this->sumberNama)];

        if ($this->namaJabatan !== null) {
            $bagian[] = NamaNormal::dari($this->namaJabatan);
            $bagian[] = NamaNormal::dari($this->lokasi);
        }

        return sha1(implode('|', $bagian));
    }

    public function keArray(): array
    {
        return [
            'tahun' => $this->tahun,
            'jenis_pengadaan' => $this->jenisPengadaan->value,
            'cakupan' => $this->cakupan->value,
            'instansi' => $this->namaInstansi,
            'kode_jabatan' => $this->kodeJabatan,
            'nama_jabatan' => $this->namaJabatan,
            'lokasi' => $this->lokasi,
            'jumlah_formasi' => $this->jumlahFormasi,
            'jumlah_pelamar' => $this->jumlahPelamar,
            'jumlah_submit' => $this->jumlahSubmit,
            'jumlah_lolos_administrasi' => $this->jumlahLolosAdministrasi,
            'tanggal_snapshot' => $this->tanggalSnapshot,
            'status_angka' => $this->statusAngka->value,
            'tingkat_data' => $this->tingkatData->value,
            'keyakinan' => $this->keyakinan->value,
            'sumber_nama' => $this->sumberNama,
            'sumber_url' => $this->sumberUrl,
            'catatan' => $this->catatan,
        ];
    }

    public static function dariArray(array $data): self
    {
        return new self(
            (int) $data['tahun'],
            JenisPengadaan::from($data['jenis_pengadaan']),
            Cakupan::from($data['cakupan']),
            $data['instansi'],
            $data['kode_jabatan'] ?? null,
            $data['nama_jabatan'] ?? null,
            $data['lokasi'] ?? null,
            $data['jumlah_formasi'],
            $data['jumlah_pelamar'],
            $data['jumlah_submit'],
            $data['jumlah_lolos_administrasi'],
            $data['tanggal_snapshot'],
            StatusAngka::from($data['status_angka']),
            TingkatData::from($data['tingkat_data']),
            Keyakinan::from($data['keyakinan']),
            $data['sumber_nama'],
            $data['sumber_url'],
            $data['catatan'],
        );
    }
}
