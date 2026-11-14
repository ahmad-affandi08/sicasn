import { LencanaStatus } from './LencanaStatus';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiKonfirmasi(props: PropsIlustrasi) {
    return (
        <LencanaStatus warna="var(--primer)" warnaLembut="var(--primer-lembut)" {...props}>
            <path d="M65 70 C65 58 95 58 95 72 C95 82 80 83 80 94" />
            <path d="M80 106 V107" />
        </LencanaStatus>
    );
}
