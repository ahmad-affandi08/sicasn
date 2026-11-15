<?php

declare(strict_types=1);

namespace Src\Formasi\Application;

final readonly class AmbilItemFormasi
{
    public function __construct(
        private PencarianFormasi $pencarian,
        private PerkayaFormasi $perkaya,
    ) {}

    public function handle(array $ids): array
    {
        return $this->perkaya->handle($this->pencarian->ringkas(array_values(array_unique(array_map('intval', $ids)))));
    }
}
