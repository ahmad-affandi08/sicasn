<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

final readonly class AmbilEventBaru
{
    public function __construct(private EventTerdeteksiRepository $event) {}

    public function handle(): array
    {
        return $this->event->belumDiberitahukan();
    }
}
