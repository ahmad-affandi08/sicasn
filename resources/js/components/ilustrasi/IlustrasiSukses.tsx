import { LencanaStatus } from './LencanaStatus';
import type { PropsIlustrasi } from './tipe';

export function IlustrasiSukses(props: PropsIlustrasi) {
    return (
        <LencanaStatus warna="var(--sukses)" warnaLembut="var(--sukses-lembut)" {...props}>
            <path d="M60 82 L75 97 L102 66" />
        </LencanaStatus>
    );
}
