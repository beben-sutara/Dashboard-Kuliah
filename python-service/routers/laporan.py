import os
import requests
from fastapi import APIRouter
from pydantic import BaseModel
from services.db_service import get_db
from dotenv import load_dotenv

load_dotenv()

router = APIRouter()

THRESHOLD = int(os.getenv('LAPORAN_THRESHOLD', 3))
LARAVEL_NOTIFY_URL = os.getenv('LARAVEL_URL', 'http://127.0.0.1:8000') + '/api/internal/laporan-threshold'


class ThresholdRequest(BaseModel):
    jadwal_id: int
    tanggal: str


@router.post("/api/laporan/threshold")
def check_threshold(req: ThresholdRequest):
    db = get_db()
    try:
        with db.cursor() as cur:
            # Count valid (pending) reports for this jadwal on this date
            cur.execute("""
                SELECT COUNT(*) AS jumlah
                FROM laporan_kehadiran
                WHERE jadwal_id = %s AND tanggal = %s
            """, (req.jadwal_id, req.tanggal))
            row = cur.fetchone()
            count = row["jumlah"] if row else 0

            if count >= THRESHOLD:
                # Get jadwal details for notification
                cur.execute("""
                    SELECT mk.nama AS matakuliah, d.nama AS dosen
                    FROM jadwal_perkuliahan jp
                    JOIN master_matakuliah mk ON mk.id = jp.matakuliah_id
                    JOIN master_dosen d ON d.id = jp.dosen_id
                    WHERE jp.id = %s
                """, (req.jadwal_id,))
                jadwal = cur.fetchone()

                # Mark reports as valid
                cur.execute("""
                    UPDATE laporan_kehadiran
                    SET status_validasi = 'valid'
                    WHERE jadwal_id = %s AND tanggal = %s AND status_validasi = 'pending'
                """, (req.jadwal_id, req.tanggal))

                return {
                    "threshold_reached": True,
                    "jumlah_laporan": count,
                    "matakuliah": jadwal["matakuliah"] if jadwal else "",
                    "dosen": jadwal["dosen"] if jadwal else "",
                    "message": f"Threshold {THRESHOLD} tercapai — notifikasi dikirim ke BAAK.",
                }

        return {"threshold_reached": False, "jumlah_laporan": count}
    finally:
        db.close()
