import { BidangForm } from '@/components/form/BidangForm';
import { Alert } from '@/components/ui/alert';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { teksAdmin } from '@/lang/admin';

import type { InfoPenyedia, IsianSmtp, OpsiEnkripsi } from './tipe';

interface PropsKonfigurasiSmtp {
    awalan: string;
    penyedia: InfoPenyedia;
    nilai: IsianSmtp;
    enkripsi: OpsiEnkripsi[];
    passwordTersimpan: boolean;
    galat: (kolom: string) => string | undefined;
    onUbah: (kolom: keyof IsianSmtp, nilai: string) => void;
}

export function KonfigurasiSmtp({ awalan, penyedia, nilai, enkripsi, passwordTersimpan, galat, onUbah }: PropsKonfigurasiSmtp) {
    const teks = teksAdmin.pengaturan.email;
    const kunci = (kolom: string) => `${awalan}-${kolom}`;

    return (
        <Card>
            <CardHeader>
                <CardTitle>{teks.konfigurasiUntuk(penyedia.label)}</CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col gap-5">
                <Alert variant="info">{penyedia.preset.petunjuk}</Alert>
                <div className="grid gap-5 sm:grid-cols-2">
                    <BidangForm id={kunci('host')} label={teks.host} error={galat('host')} className="sm:col-span-2">
                        <Input id={kunci('host')} value={nilai.host} autoComplete="off" onChange={(kejadian) => onUbah('host', kejadian.target.value)} />
                    </BidangForm>
                    <BidangForm id={kunci('port')} label={teks.port} error={galat('port')}>
                        <Input id={kunci('port')} type="number" inputMode="numeric" min={1} max={65535} value={nilai.port} onChange={(kejadian) => onUbah('port', kejadian.target.value)} />
                    </BidangForm>
                    <BidangForm id={kunci('enkripsi')} label={teks.enkripsi} error={galat('enkripsi')}>
                        <Select id={kunci('enkripsi')} value={nilai.enkripsi} onChange={(kejadian) => onUbah('enkripsi', kejadian.target.value)}>
                            {enkripsi.map((opsi) => (
                                <option key={opsi.kode} value={opsi.kode}>
                                    {opsi.label}
                                </option>
                            ))}
                        </Select>
                    </BidangForm>
                    <BidangForm id={kunci('username')} label={teks.username} error={galat('username')}>
                        <Input id={kunci('username')} value={nilai.username} autoComplete="off" onChange={(kejadian) => onUbah('username', kejadian.target.value)} />
                    </BidangForm>
                    <BidangForm id={kunci('password')} label={teks.password} error={galat('password')} petunjuk={passwordTersimpan ? teks.passwordTersimpan : undefined}>
                        <Input
                            id={kunci('password')}
                            type="password"
                            autoComplete="new-password"
                            value={nilai.password}
                            placeholder={passwordTersimpan ? '••••••••' : ''}
                            onChange={(kejadian) => onUbah('password', kejadian.target.value)}
                        />
                    </BidangForm>
                </div>
            </CardContent>
        </Card>
    );
}
