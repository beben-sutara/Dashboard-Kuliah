from fastapi import APIRouter
from pydantic import BaseModel
from typing import Optional
from services.db_service import get_db

router = APIRouter()


class ClashCheckRequest(BaseModel):
    dosen_id: int
    ruangan_id: int
    hari: str
    waktu_mulai: str    # "HH:MM"
    waktu_selesai: str  # "HH:MM"
    exclude_id: Optional[int] = None


@router.post("/api/clash-check")
def check_clash(req: ClashCheckRequest):
    """
    Check if a new/updated jadwal would clash with an existing one.
    Clash conditions:
      - Same dosen on same hari, overlapping time
      - Same ruangan on same hari, overlapping time
    """
    db = get_db()
    try:
        with db.cursor() as cur:
            # Build exclusion clause
            exclude = f"AND jp.id != {req.exclude_id}" if req.exclude_id else ""

            query = f"""
                SELECT jp.id,
                       mk.nama AS matakuliah,
                       d.nama AS dosen,
                       r.kode AS ruangan,
                       jp.waktu_mulai, jp.waktu_selesai,
                       CASE
                           WHEN jp.dosen_id = %(dosen_id)s THEN 'dosen'
                           ELSE 'ruangan'
                       END AS clash_type
                FROM jadwal_perkuliahan jp
                JOIN master_matakuliah mk ON mk.id = jp.matakuliah_id
                JOIN master_dosen d ON d.id = jp.dosen_id
                JOIN master_ruangan r ON r.id = jp.ruangan_id
                WHERE jp.hari = %(hari)s
                  {exclude}
                  AND jp.waktu_mulai < %(waktu_selesai)s
                  AND jp.waktu_selesai > %(waktu_mulai)s
                  AND (jp.dosen_id = %(dosen_id)s OR jp.ruangan_id = %(ruangan_id)s)
                LIMIT 1
            """

            cur.execute(query, {
                "dosen_id": req.dosen_id,
                "ruangan_id": req.ruangan_id,
                "hari": req.hari,
                "waktu_mulai": req.waktu_mulai + ":00",
                "waktu_selesai": req.waktu_selesai + ":00",
            })
            conflict = cur.fetchone()

        if conflict:
            clash_type = conflict["clash_type"]
            entity = conflict["dosen"] if clash_type == "dosen" else conflict["ruangan"]
            start = str(conflict["waktu_mulai"])[:5]
            end = str(conflict["waktu_selesai"])[:5]
            return {
                "clash": True,
                "message": f"{'Dosen' if clash_type == 'dosen' else 'Ruangan'} '{entity}' sudah memiliki jadwal "
                           f"'{conflict['matakuliah']}' pada {req.hari} pukul {start}–{end}."
            }

        return {"clash": False, "message": ""}

    finally:
        db.close()
