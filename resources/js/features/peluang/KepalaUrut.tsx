import { LuArrowUpDown } from 'react-icons/lu';

import { Button } from '@/components/ui/button';
import { TableHead } from '@/components/ui/table';

interface PropsKepalaUrut {
    label: string;
    aktif: boolean;
    onUrut: () => void;
}

export function KepalaUrut({ label, aktif, onUrut }: PropsKepalaUrut) {
    return (
        <TableHead aria-sort={aktif ? 'ascending' : 'none'}>
            <Button variant="ghost" size="sm" className="-ml-3 gap-1.5 text-xs tracking-wide uppercase" onClick={onUrut}>
                {label}
                <LuArrowUpDown aria-hidden className={aktif ? 'h-3.5 w-3.5 text-primer' : 'h-3.5 w-3.5'} />
            </Button>
        </TableHead>
    );
}
