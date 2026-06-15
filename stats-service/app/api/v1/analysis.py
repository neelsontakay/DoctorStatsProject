from fastapi import APIRouter, BackgroundTasks, Depends, HTTPException, status

from app.api.deps import verify_service_token
from app.schemas.analysis import (
    AnalysisResultsResponse,
    AnalysisStatusResponse,
    AnalyzeAcceptedResponse,
    AnalyzeRequest,
)
from app.services.analysis_orchestrator import analysis_orchestrator
from app.services.analysis_store import analysis_store

router = APIRouter(prefix="/api/v1", tags=["analysis"])


@router.post(
    "/analyze",
    response_model=AnalyzeAcceptedResponse,
    status_code=status.HTTP_202_ACCEPTED,
)
def analyze(
    request: AnalyzeRequest,
    background_tasks: BackgroundTasks,
    _: None = Depends(verify_service_token),
) -> AnalyzeAcceptedResponse:
    if not request.columns:
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_CONTENT,
            detail="At least one column specification is required.",
        )

    analysis_store.create(request.job_id)
    background_tasks.add_task(analysis_orchestrator.run, request)

    return AnalyzeAcceptedResponse(analysis_id=request.job_id)


@router.get("/analysis/{analysis_id}/status", response_model=AnalysisStatusResponse)
def analysis_status(
    analysis_id: str,
    _: None = Depends(verify_service_token),
) -> AnalysisStatusResponse:
    record = analysis_store.get(analysis_id)
    if record is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Analysis not found.")

    return AnalysisStatusResponse(
        analysis_id=record.analysis_id,
        status=record.status,
        progress_percent=record.progress_percent,
        current_step=record.current_step,
        error=record.error,
    )


@router.get("/analysis/{analysis_id}/results", response_model=AnalysisResultsResponse)
def analysis_results(
    analysis_id: str,
    _: None = Depends(verify_service_token),
) -> AnalysisResultsResponse:
    record = analysis_store.get(analysis_id)
    if record is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Analysis not found.")

    if record.status != "completed":
        raise HTTPException(
            status_code=status.HTTP_409_CONFLICT,
            detail="Analysis results are not ready yet.",
        )

    from app.schemas.analysis import TestResult

    return AnalysisResultsResponse(
        analysis_id=record.analysis_id,
        data_profile=record.data_profile,
        tests=[TestResult(**test) for test in record.tests if test.get("test_category") != "profile"],
    )
