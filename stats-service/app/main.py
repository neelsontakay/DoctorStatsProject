"""DoctorStats statistical analysis service.

FastAPI microservice consumed by the Laravel web application.
Endpoint contract: docs/api/laravel-python-contract.md (repository root).
"""

from fastapi import FastAPI

from app.api.v1.analysis import router as analysis_router
from app.api.v1.graphs import router as graphs_router
from app.core.config import settings


def create_app() -> FastAPI:
    application = FastAPI(
        title="DoctorStats Statistical Analysis Service",
        version="0.1.0",
        debug=settings.debug,
    )

    @application.get("/health", tags=["health"])
    def health() -> dict[str, str]:
        return {"status": "ok", "service": settings.service_name}

    application.include_router(analysis_router)
    application.include_router(graphs_router)

    return application


app = create_app()
