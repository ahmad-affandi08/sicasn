import { LencanaStatus } from './LencanaStatus';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiGalat(props: PropsIlustrasi) {
    return (
        <LencanaStatus warna="var(--bahaya)" warnaLembut="var(--bahaya-lembut)" {...props}>
            <path d="M80 56 V86" />
            <path d="M80 102 V103" />
        </LencanaStatus>
    );
}
