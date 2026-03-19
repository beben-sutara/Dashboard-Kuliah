from fastapi import APIRouter, HTTPException
from pydantic import BaseModel
from services.groq_service import chat

router = APIRouter()


class ChatRequest(BaseModel):
    message: str
    user_id: int | None = None


@router.post("/api/insun/chat")
def insun_chat(req: ChatRequest):
    if not req.message.strip():
        raise HTTPException(status_code=400, detail="Message cannot be empty")

    try:
        reply = chat(req.message)
        return {"reply": reply}
    except Exception as e:
        return {"reply": f"Maaf, INSUN sedang tidak dapat merespons. Silakan coba lagi nanti. (Error: {type(e).__name__})"}
