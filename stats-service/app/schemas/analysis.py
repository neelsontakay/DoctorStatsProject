from typing import Any, Literal

from pydantic import BaseModel, Field


class ColumnSpec(BaseModel):
    name: str
    index: int
    data_type: Literal["categorical", "numerical", "date", "text"]
    description: str | None = None
    unit: str | None = None
    variable_type: Literal[
        "independent", "dependent", "control", "identifier", "excluded"
    ]


class AnalyzeRequest(BaseModel):
    job_id: str
    file_url: str
    file_format: Literal["xlsx", "xls", "csv"]
    sheet_name: str | None = None
    objectives: str
    columns: list[ColumnSpec]
    callback_url: str | None = None


class AnalyzeAcceptedResponse(BaseModel):
    analysis_id: str
    status: Literal["pending"] = "pending"


class AnalysisStatusResponse(BaseModel):
    analysis_id: str
    status: Literal["pending", "processing", "completed", "failed"]
    progress_percent: int = 0
    current_step: str | None = None
    error: str | None = None


class TestResult(BaseModel):
    test_name: str
    test_category: Literal[
        "frequency",
        "hypothesis",
        "non_parametric",
        "correlation",
        "regression",
        "advanced",
        "profile",
    ]
    parameters: dict[str, Any] = Field(default_factory=dict)
    test_statistic: float | None = None
    p_value: float | None = None
    confidence_intervals: dict[str, Any] = Field(default_factory=dict)
    effect_sizes: dict[str, Any] = Field(default_factory=dict)
    assumptions_validation: dict[str, Any] = Field(default_factory=dict)
    raw_output: dict[str, Any] = Field(default_factory=dict)


class AnalysisResultsResponse(BaseModel):
    analysis_id: str
    data_profile: dict[str, Any]
    tests: list[TestResult]


class AnalysisCallbackPayload(BaseModel):
    job_id: str
    status: Literal["processing", "completed", "failed"]
    progress_percent: int = 0
    current_step: str | None = None
    error: str | None = None
    data_profile: dict[str, Any] | None = None
    tests: list[TestResult] | None = None
