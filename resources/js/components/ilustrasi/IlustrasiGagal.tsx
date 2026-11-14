import { LencanaStatus } from './LencanaStatus';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiGagal(props: PropsIlustrasi) {
    return (
        <LencanaStatus warna="var(--bahaya)" warnaLembut="var(--bahaya-lembut)" {...props}>
            <path d="M64 64 L96 96 M96 64 L64 96" />
        </LencanaStatus>
    );
}
