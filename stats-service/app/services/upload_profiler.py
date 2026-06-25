from __future__ import annotations

from typing import Any

import numpy as np
import pandas as pd
from scipy import stats

from app.services.data_loader import data_loader


class UploadProfiler:
    NUMERIC_THRESHOLD = 0.8

    def profile(
        self,
        file_url: str,
        file_format: str,
        sheet_name: str | None,
    ) -> dict[str, Any]:
        frame = data_loader.load(file_url, file_format, sheet_name)  # type: ignore[arg-type]
        row_count = int(len(frame))

        columns: list[dict[str, Any]] = []
        for name in frame.columns:
            series = frame[name]
            inferred_type = self._infer_type(series, row_count)

            if inferred_type == "identifier":
                columns.append(
                    {
                        "name": str(name),
                        "inferred_type": inferred_type,
                        "descriptive_statistics": {
                            "count": int(series.count()),
                            "unique_values": int(series.nunique(dropna=True)),
                        },
                        "frequency_table": [],
                    }
                )
                continue

            if inferred_type == "numerical":
                numeric = pd.to_numeric(series, errors="coerce")
                columns.append(
                    {
                        "name": str(name),
                        "inferred_type": inferred_type,
                        "descriptive_statistics": self._numerical_stats(numeric),
                        "frequency_table": [],
                    }
                )
            else:
                columns.append(
                    {
                        "name": str(name),
                        "inferred_type": inferred_type,
                        "descriptive_statistics": self._categorical_descriptive(series),
                        "frequency_table": self._frequency_table(series),
                    }
                )

        return {"row_count": row_count, "columns": columns}

    def _infer_type(self, series: pd.Series, row_count: int) -> str:
        non_null = series.dropna()
        if non_null.empty:
            return "categorical"

        numeric = pd.to_numeric(non_null, errors="coerce")
        numeric_ratio = float(numeric.notna().mean())
        if numeric_ratio >= self.NUMERIC_THRESHOLD:
            return "numerical"

        unique_count = int(non_null.nunique())
        if unique_count == row_count and row_count > 1:
            return "identifier"

        return "categorical"

    def _numerical_stats(self, series: pd.Series) -> dict[str, Any]:
        clean = series.dropna()
        if clean.empty:
            return {"count": 0}

        q1 = clean.quantile(0.25)
        q3 = clean.quantile(0.75)
        mode_result = stats.mode(clean, keepdims=False)

        return {
            "count": int(clean.count()),
            "mean": self._safe_float(clean.mean()),
            "median": self._safe_float(clean.median()),
            "mode": self._safe_float(mode_result.mode),
            "std": self._safe_float(clean.std()),
            "min": self._safe_float(clean.min()),
            "max": self._safe_float(clean.max()),
            "q1": self._safe_float(q1),
            "q3": self._safe_float(q3),
            "iqr": self._safe_float(q3 - q1),
        }

    def _categorical_descriptive(self, series: pd.Series) -> dict[str, Any]:
        clean = series.dropna()
        if clean.empty:
            return {"count": 0, "unique_values": 0}

        mode_value = clean.astype(str).mode()
        return {
            "count": int(clean.count()),
            "unique_values": int(clean.nunique()),
            "mode": str(mode_value.iloc[0]) if not mode_value.empty else None,
        }

    def _frequency_table(self, series: pd.Series, limit: int = 20) -> list[dict[str, Any]]:
        clean = series.dropna().astype(str)
        if clean.empty:
            return []

        counts = clean.value_counts()
        total = int(len(clean))
        cumulative = 0
        rows: list[dict[str, Any]] = []

        for value, count in counts.head(limit).items():
            cumulative += int(count)
            rows.append(
                {
                    "value": str(value),
                    "count": int(count),
                    "percent": round((int(count) / total) * 100, 2),
                    "cumulative_percent": round((cumulative / total) * 100, 2),
                }
            )

        return rows

    def _safe_float(self, value: Any) -> float | None:
        if value is None or pd.isna(value):
            return None
        return round(float(value), 6)


upload_profiler = UploadProfiler()
