import { LuMoon, LuSun } from 'react-icons/lu';

import { Button } from '@/components/ui/button';
import { useTema } from '@/hooks/useTema';
import { teks } from '@/lang/id';

export function TombolTema() {
    const { gelap, ganti } = useTema();

    return (
        <Button variant="ghost" size="icon" onClick={ganti} aria-label={gelap ? teks.nav.modeTerang : teks.nav.modeGelap}>
            {gelap ? <LuSun aria-hidden className="h-5 w-5" /> : <LuMoon aria-hidden className="h-5 w-5" />}
        </Button>
    );
}
