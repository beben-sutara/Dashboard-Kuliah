import os
from groq import Groq
from services.db_service import get_db
from dotenv import load_dotenv

load_dotenv()

client = Groq(api_key=os.getenv('GROQ_API_KEY', ''))


def get_jadwal_context() -> str:
    """Fetch today's/all jadwal from DB for AI context."""
    db = get_db()
    try:
        with db.cursor() as cur:
            cur.execute("""
                SELECT jp.hari, jp.waktu_mulai, jp.waktu_selesai,
                       mk.nama AS matakuliah,
                       d.nama AS dosen,
                       r.kode AS ruangan_kode,
                       r.nama AS ruangan_nama,
                       jp.prodi, jp.semester
                FROM jadwal_perkuliahan jp
                JOIN master_matakuliah mk ON mk.id = jp.matakuliah_id
                JOIN master_dosen d ON d.id = jp.dosen_id
                JOIN master_ruangan r ON r.id = jp.ruangan_id
                ORDER BY jp.hari, jp.waktu_mulai
                LIMIT 50
            """)
            rows = cur.fetchall()

        lines = [f"[{r['hari']} {str(r['waktu_mulai'])[:5]}-{str(r['waktu_selesai'])[:5]}] "
                 f"{r['matakuliah']} | Dosen: {r['dosen']} | Ruang: {r['ruangan_kode']} | "
                 f"Prodi: {r['prodi']} | Semester: {r['semester']}"
                 for r in rows]
        return "\n".join(lines) if lines else "Tidak ada jadwal tersedia."
    finally:
        db.close()


HARI_ID = {0: 'Senin', 1: 'Selasa', 2: 'Rabu', 3: 'Kamis', 4: 'Jumat', 5: 'Sabtu', 6: 'Minggu'}


def chat(message: str) -> str:
    from datetime import datetime
    now = datetime.now()
    hari_ini = HARI_ID.get(now.weekday(), '')
    tanggal_str = now.strftime(f"{hari_ini}, %d %B %Y pukul %H:%M WIB")

    jadwal_ctx = get_jadwal_context()

    system_prompt = f"""Kamu adalah INSUN, asisten akademik virtual yang cerdas dan ramah untuk mahasiswa kampus.
Kamu membantu menjawab pertanyaan seputar jadwal perkuliahan, dosen, ruangan, dan informasi akademik kampus.
Jawab dalam bahasa Indonesia yang natural, ringkas, dan informatif.
Jangan gunakan kata 'berdasarkan data' — cukup jawab langsung.

Waktu saat ini: {tanggal_str}

Data jadwal perkuliahan:
{jadwal_ctx}
"""

    chat_response = client.chat.completions.create(
        model="llama-3.3-70b-versatile",
        messages=[
            {"role": "system", "content": system_prompt},
            {"role": "user", "content": message},
        ],
        max_tokens=512,
        temperature=0.7,
    )

    return chat_response.choices[0].message.content
