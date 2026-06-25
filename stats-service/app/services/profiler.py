from __future__ import annotations

from typing import Any

import numpy as np
import pandas as pd
from scipy import stats

from app.schemas.analysis import ColumnSpec


class DataProfiler:
    def profile(self, frame: pd.DataFrame, columns: list[ColumnSpec]) -> dict[str, Any]:
        specs_by_name = {column.name: column for column in columns if column.variable_type != "excluded"}

        descriptive_statistics: dict[str, Any] = {}
        missing_data_patterns: dict[str, Any] = {}
        outliers: dict[str, Any] = {}
        cross_tabulations: dict[str, Any] = {}

        categorical_specs = [
            spec for spec in specs_by_name.values() if spec.data_type == "categorical"
        ]
        if len(categorical_specs) >= 2:
            first, second = categorical_specs[0], categorical_specs[1]
            cross_tabulations[f"{first.name}_x_{second.name}"] = (
                pd.crosstab(frame[first.name], frame[second.name]).astype(int).to_dict()
            )

        for name, spec in specs_by_name.items():
            series = frame[name]
            missing_count = int(series.isna().sum())
            missing_data_patterns[name] = {
                "missing_count": missing_count,
                "missing_percent": round((missing_count / max(len(series), 1)) * 100, 2),
            }

            if spec.data_type == "numerical":
                numeric = pd.to_numeric(series, errors="coerce")
                descriptive_statistics[name] = self._numerical_stats(numeric)
                outliers[name] = self._detect_outliers(numeric)
            else:
                descriptive_statistics[name] = self._categorical_stats(series)

        return {
            "row_count": int(len(frame)),
            "descriptive_statistics": descriptive_statistics,
            "missing_data_patterns": missing_data_patterns,
            "outliers": outliers,
            "cross_tabulations": cross_tabulations,
        }

    def _numerical_stats(self, series: pd.Series) -> dict[str, Any]:
        clean = series.dropna()
        if clean.empty:
            return {"count": 0}

        q1 = clean.quantile(0.25)
        q3 = clean.quantile(0.75)
        mean = float(clean.mean())
        std = float(clean.std())
        mode_result = stats.mode(clean, keepdims=False)

        return {
            "count": int(clean.count()),
            "mean": self._safe_float(mean),
            "median": self._safe_float(clean.median()),
            "mode": self._safe_float(mode_result.mode),
            "std": self._safe_float(std),
            "min": self._safe_float(clean.min()),
            "max": self._safe_float(clean.max()),
            "q1": self._safe_float(q1),
            "q3": self._safe_float(q3),
            "p90": self._safe_float(clean.quantile(0.9)),
            "iqr": self._safe_float(q3 - q1),
            "skewness": self._safe_float(stats.skew(clean)),
            "kurtosis": self._safe_float(stats.kurtosis(clean)),
            "coefficient_of_variation": self._safe_float(std / mean) if mean else None,
        }

    def _categorical_stats(self, series: pd.Series) -> dict[str, Any]:
        clean = series.dropna()
        if clean.empty:
            return {"count": 0, "unique_values": 0, "frequency_table": []}

        mode_value = clean.astype(str).mode()
        return {
            "count": int(clean.count()),
            "unique_values": int(clean.nunique(dropna=True)),
            "mode": str(mode_value.iloc[0]) if not mode_value.empty else None,
            "frequency_table": self._frequency_table(clean),
        }

    def _frequency_table(self, series: pd.Series, limit: int = 20) -> list[dict[str, Any]]:
        counts = series.astype(str).value_counts()
        total = int(len(series))
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
