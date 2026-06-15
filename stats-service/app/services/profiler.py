from __future__ import annotations

from typing import Any

import numpy as np
import pandas as pd

from app.schemas.analysis import ColumnSpec


class DataProfiler:
    def profile(self, frame: pd.DataFrame, columns: list[ColumnSpec]) -> dict[str, Any]:
        specs_by_name = {column.name: column for column in columns if column.variable_type != "excluded"}

        descriptive_statistics: dict[str, Any] = {}
        missing_data_patterns: dict[str, Any] = {}
        outliers: dict[str, Any] = {}

        for name, spec in specs_by_name.items():
            series = frame[name]
            missing_count = int(series.isna().sum())
            missing_data_patterns[name] = {
                "missing_count": missing_count,
                "missing_percent": round((missing_count / max(len(series), 1)) * 100, 2),
            }

            if spec.data_type == "numerical":
                numeric = pd.to_numeric(series, errors="coerce")
                descriptive_statistics[name] = {
                    "count": int(numeric.count()),
                    "mean": self._safe_float(numeric.mean()),
                    "median": self._safe_float(numeric.median()),
                    "std": self._safe_float(numeric.std()),
                    "min": self._safe_float(numeric.min()),
                    "max": self._safe_float(numeric.max()),
                }
                outliers[name] = self._detect_outliers(numeric)
            else:
                value_counts = series.astype(str).value_counts(dropna=True).head(10)
                descriptive_statistics[name] = {
                    "count": int(series.count()),
                    "unique_values": int(series.nunique(dropna=True)),
                    "top_values": {str(k): int(v) for k, v in value_counts.items()},
                }

        return {
            "row_count": int(len(frame)),
            "descriptive_statistics": descriptive_statistics,
            "missing_data_patterns": missing_data_patterns,
            "outliers": outliers,
        }

    def _detect_outliers(self, series: pd.Series) -> dict[str, Any]:
        clean = series.dropna()
        if clean.empty:
            return {"count": 0, "method": "iqr"}

        q1 = clean.quantile(0.25)
        q3 = clean.quantile(0.75)
        iqr = q3 - q1
        lower = q1 - (1.5 * iqr)
        upper = q3 + (1.5 * iqr)
        mask = (clean < lower) | (clean > upper)

        return {
            "method": "iqr",
            "count": int(mask.sum()),
            "lower_bound": self._safe_float(lower),
            "upper_bound": self._safe_float(upper),
        }

    def _safe_float(self, value: Any) -> float | None:
        if value is None or pd.isna(value):
            return None
        return round(float(value), 6)


profiler = DataProfiler()
