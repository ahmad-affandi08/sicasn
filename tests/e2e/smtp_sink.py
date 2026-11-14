import asyncio
import sys
import time
from pathlib import Path

from aiosmtpd.controller import Controller
from aiosmtpd.smtp import AuthResult, LoginPassword

KELUARAN = Path(sys.argv[1])
KELUARAN.mkdir(parents=True, exist_ok=True)


class Penampung:
    async def handle_DATA(self, server, session, envelope):
        nama = KELUARAN / f"{int(time.time() * 1000)}.eml"
        nama.write_bytes(envelope.content)
        return "250 Pesan diterima"


def autentikasi(server, session, envelope, mechanism, auth_data):
    return AuthResult(success=True)


pengendali = Controller(
    Penampung(),
    hostname="127.0.0.1",
    port=1025,
    authenticator=autentikasi,
    auth_required=False,
    auth_require_tls=False,
)
pengendali.start()
print("SMTP sink siap di 127.0.0.1:1025", flush=True)
try:
    asyncio.get_event_loop().run_forever()
except KeyboardInterrupt:
    pengendali.stop()
