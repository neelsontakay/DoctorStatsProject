from __future__ import annotations

from dataclasses import dataclass, field
from threading import Lock
from typing import Any, Literal


AnalysisStatus = Literal["pending", "processing", "completed", "failed"]


@dataclass
class AnalysisRecord:
    analysis_id: str
    status: AnalysisStatus = "pending"
    progress_percent: int = 0
    current_step: str | None = None
    error: str | None = None
    data_profile: dict[str, Any] = field(default_factory=dict)
    tests: list[dict[str, Any]] = field(default_factory=list)


class AnalysisStore:
    def __init__(self) -> None:
        self._records: dict[str, AnalysisRecord] = {}
        self._lock = Lock()

    def create(self, analysis_id: str) -> AnalysisRecord:
        with self._lock:
            record = AnalysisRecord(analysis_id=analysis_id)
            self._records[analysis_id] = record
            return record

    def get(self, analysis_id: str) -> AnalysisRecord | None:
        with self._lock:
            return self._records.get(analysis_id)

    def update(
        self,
        analysis_id: str,
        *,
        status: AnalysisStatus | None = None,
        progress_percent: int | None = None,
        current_step: str | None = None,
        error: str | None = None,
        data_profile: dict[str, Any] | None = None,
        tests: list[dict[str, Any]] | None = None,
    ) -> AnalysisRecord:
        with self._lock:
            record = self._records.setdefault(analysis_id, AnalysisRecord(analysis_id=analysis_id))

            if status is not None:
                record.status = status
            if progress_percent is not None:
                record.progress_percent = progress_percent
            if current_step is not None:
                record.current_step = current_step
            if error is not None:
                record.error = error
            if data_profile is not None:
                record.data_profile = data_profile
            if tests is not None:
                record.tests = tests

            return record


analysis_store = AnalysisStore()
