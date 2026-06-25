from typing import Any, Literal

from pydantic import BaseModel, Field


class ProfileRequest(BaseModel):
    data_file_id: int
    file_url: str
    file_format: Literal["xlsx", "xls", "csv"]
    sheet_name: str | None = None


class ColumnProfile(BaseModel):
    name: str
    inferred_type: Literal["numerical", "categorical", "identifier"]
    descriptive_statistics: dict[str, Any] = Field(default_factory=dict)
    frequency_table: list[dict[str, Any]] = Field(default_factory=list)


class ProfileResponse(BaseModel):
    row_count: int
    columns: list[ColumnProfile]
