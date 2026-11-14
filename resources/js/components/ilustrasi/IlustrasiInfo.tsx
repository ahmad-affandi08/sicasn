import { LencanaStatus } from './LencanaStatus';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiInfo(props: PropsIlustrasi) {
    return (
        <LencanaStatus warna="var(--info)" warnaLembut="var(--info-lembut)" {...props}>
            <path d="M80 78 V102" />
            <path d="M80 59 V60" />
        </LencanaStatus>
    );
}
