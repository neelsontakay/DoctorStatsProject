from __future__ import annotations

import io
from typing import Literal

import httpx
import pandas as pd

from app.core.config import settings
from app.schemas.analysis import ColumnSpec

FileFormat = Literal["xlsx", "xls", "csv"]


class DataLoader:
    def load(self, file_url: str, file_format: FileFormat, sheet_name: str | None) -> pd.DataFrame:
        headers: dict[str, str] = {}
        if settings.service_token:
            headers["X-Service-Token"] = settings.service_token.strip()

        response = httpx.get(file_url, timeout=120, follow_redirects=True, headers=headers)
        response.raise_for_status()

        content = response.content

        if file_format == "csv":
            frame = pd.read_csv(io.BytesIO(content))
        else:
            frame = pd.read_excel(io.BytesIO(content), sheet_name=sheet_name or 0)

        frame.columns = [str(column).strip() for column in frame.columns]
        return frame

    def select_columns(self, frame: pd.DataFrame, columns: list[ColumnSpec]) -> pd.DataFrame:
        selected: dict[str, pd.Series] = {}

        for spec in columns:
            if spec.variable_type == "excluded":
                continue

            if spec.index < len(frame.columns):
                source_name = frame.columns[spec.index]
            elif spec.name in frame.columns:
                source_name = spec.name
            else:
                raise ValueError(f"Column '{spec.name}' was not found in the uploaded dataset.")

            selected[spec.name] = frame[source_name]

        if not selected:
            raise ValueError("No analyzable columns were found in the dataset.")

        return pd.DataFrame(selected)


data_loader = DataLoader()
