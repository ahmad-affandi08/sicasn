<?php

declare(strict_types=1);

namespace Src\DataImport\Application;

use Src\Shared\Application\Clock;

final readonly class TandaiEventDiberitahukan
{
    public function __construct(
        private EventTerdeteksiRepository $event,
        private Clock $clock,
    ) {}

    public function handle(): void
    {
        $this->event->tandaiDiberitahukan($this->clock->sekarang());
    }
}
