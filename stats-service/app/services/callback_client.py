from __future__ import annotations

import logging

import httpx

from app.core.config import settings
from app.schemas.analysis import AnalysisCallbackPayload

logger = logging.getLogger(__name__)


class CallbackClient:
    def send(self, callback_url: str | None, payload: AnalysisCallbackPayload) -> None:
        if not callback_url:
            return

        headers: dict[str, str] = {"Content-Type": "application/json"}
        if settings.service_token:
            headers["X-Service-Token"] = settings.service_token.strip()

        try:
            response = httpx.post(
                callback_url,
                json=payload.model_dump(exclude_none=True),
                headers=headers,
                timeout=settings.callback_timeout_seconds,
            )
            response.raise_for_status()
        except httpx.HTTPError:
            logger.exception(
                "Failed to deliver analysis callback.",
                extra={"job_id": payload.job_id, "status": payload.status},
            )


callback_client = CallbackClient()
