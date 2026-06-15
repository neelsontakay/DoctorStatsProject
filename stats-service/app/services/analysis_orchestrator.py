from __future__ import annotations

import logging

from app.schemas.analysis import AnalysisCallbackPayload, AnalyzeRequest, TestResult
from app.services.analysis_store import analysis_store
from app.services.callback_client import callback_client
from app.services.data_loader import data_loader
from app.services.profiler import profiler
from app.services.test_engine import test_engine

logger = logging.getLogger(__name__)


class AnalysisOrchestrator:
    def run(self, request: AnalyzeRequest) -> None:
        analysis_id = request.job_id

        try:
            self._notify(request, status="processing", progress_percent=5, current_step="Loading dataset")
            frame = data_loader.load(request.file_url, request.file_format, request.sheet_name)
            working_frame = data_loader.select_columns(frame, request.columns)

            self._notify(
                request,
                status="processing",
                progress_percent=35,
                current_step="Profiling dataset",
            )
            data_profile = profiler.profile(working_frame, request.columns)

            self._notify(
                request,
                status="processing",
                progress_percent=65,
                current_step="Running statistical tests",
            )
            tests = test_engine.run(working_frame, request.columns)
            test_payloads = [test.model_dump() for test in tests]

            profile_result = TestResult(
                test_name="Data profile",
                test_category="profile",
                raw_output=data_profile,
            )
            all_tests = [profile_result, *tests]
            all_test_payloads = [test.model_dump() for test in all_tests]

            analysis_store.update(
                analysis_id,
                status="completed",
                progress_percent=100,
                current_step="Completed",
                data_profile=data_profile,
                tests=all_test_payloads,
            )

            self._notify(
                request,
                status="completed",
                progress_percent=100,
                current_step="Completed",
                data_profile=data_profile,
                tests=all_tests,
            )
        except Exception as exc:
            logger.exception("Analysis failed.", extra={"job_id": analysis_id})
            analysis_store.update(
                analysis_id,
                status="failed",
                progress_percent=100,
                current_step="Failed",
                error=str(exc),
            )
            self._notify(
                request,
                status="failed",
                progress_percent=100,
                current_step="Failed",
                error=str(exc),
            )

    def _notify(
        self,
        request: AnalyzeRequest,
        *,
        status: str,
        progress_percent: int,
        current_step: str | None,
        error: str | None = None,
        data_profile: dict | None = None,
        tests: list[TestResult] | None = None,
    ) -> None:
        analysis_store.update(
            request.job_id,
            status=status,  # type: ignore[arg-type]
            progress_percent=progress_percent,
            current_step=current_step,
            error=error,
            data_profile=data_profile,
            tests=[test.model_dump() for test in tests] if tests else None,
        )

        callback_client.send(
            request.callback_url,
            AnalysisCallbackPayload(
                job_id=request.job_id,
                status=status,  # type: ignore[arg-type]
                progress_percent=progress_percent,
                current_step=current_step,
                error=error,
                data_profile=data_profile,
                tests=tests,
            ),
        )


analysis_orchestrator = AnalysisOrchestrator()
