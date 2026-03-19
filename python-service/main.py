from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from dotenv import load_dotenv

load_dotenv()

from routers import clash, laporan, insun

app = FastAPI(
    title="JadwalKuliah Python Service",
    description="Clash detection, laporan threshold, dan INSUN AI untuk Sistem Informasi Penjadwalan",
    version="1.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://127.0.0.1:8000", "http://localhost:8000"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(clash.router, tags=["Clash Detection"])
app.include_router(laporan.router, tags=["Laporan Threshold"])
app.include_router(insun.router, tags=["INSUN AI"])


@app.get("/health")
def health():
    return {"status": "ok", "service": "JadwalKuliah Python Service v1.0"}
