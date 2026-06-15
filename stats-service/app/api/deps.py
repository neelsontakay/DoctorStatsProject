from fastapi import Header, HTTPException, status

from app.core.config import settings


def verify_service_token(x_service_token: str | None = Header(default=None)) -> None:
    if settings.debug or not settings.service_token:
        return

    expected = settings.service_token.strip()
    provided = (x_service_token or "").strip()

    if provided != expected:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid service token.",
        )
