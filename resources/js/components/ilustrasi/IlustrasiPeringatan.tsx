import { LencanaStatus } from './LencanaStatus';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiPeringatan(props: PropsIlustrasi) {
    return (
        <LencanaStatus warna="var(--peringatan)" warnaLembut="var(--peringatan-lembut)" {...props}>
            <path d="M80 58 V84" />
            <path d="M80 100 V101" />
        </LencanaStatus>
    );
}
