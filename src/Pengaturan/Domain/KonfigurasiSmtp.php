<?php

declare(strict_types=1);

namespace Src\Pengaturan\Domain;

final readonly class KonfigurasiSmtp
{
    public function __construct(
        public string $host,
        public int $port,
        public Enkripsi $enkripsi,
        public string $username,
        public string $password,
    ) {}

    public static function dariArray(array $data): self
    {
        return new self(
            (string) ($data['host'] ?? ''),
            (int) ($data['port'] ?? 0),
            Enkripsi::tryFrom((string) ($data['enkripsi'] ?? '')) ?? Enkripsi::Tls,
            (string) ($data['username'] ?? ''),
            (string) ($data['password'] ?? ''),
        );
    }

    public function keArray(): array
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'enkripsi' => $this->enkripsi->value,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    public function lengkap(): bool
    {
        return $this->host !== '' && $this->port > 0 && $this->port <= 65535 && $this->username !== '' && $this->password !== '';
    }

    public function denganPassword(string $password): self
    {
        return new self($this->host, $this->port, $this->enkripsi, $this->username, $password);
    }
}
