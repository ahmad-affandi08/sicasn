import email
import email.policy
import json
import re
import sys
from pathlib import Path

folder = Path(sys.argv[1])
hasil = []
for berkas in sorted(folder.glob("*.eml")):
    pesan = email.message_from_bytes(berkas.read_bytes(), policy=email.policy.default)
    html = pesan.get_body(preferencelist=("html",))
    isi = html.get_content() if html else ""
    cocok = re.search(r"<code>([^<]+)</code>", isi)
    hasil.append({
        "berkas": berkas.name,
        "ke": pesan["To"],
        "dari": pesan["From"],
        "subjek": pesan["Subject"],
        "password": cocok.group(1) if cocok else None,
        "adaTautanLogin": "/masuk" in isi,
        "adaTautanWa": "wa.me/6281234567890" in isi,
    })
print(json.dumps(hasil))
