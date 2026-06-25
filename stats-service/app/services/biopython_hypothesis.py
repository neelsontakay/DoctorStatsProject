from __future__ import annotations

from typing import Any

import numpy as np
import pandas as pd
from scipy import stats

from app.schemas.analysis import TestResult

try:
    from Bio.codonalign.chisq import chisqprob as _biopython_chisqprob

    BIOPYTHON_AVAILABLE = True
except ImportError:  # pragma: no cover - exercised when Biopython is unavailable locally
    BIOPYTHON_AVAILABLE = False
    _biopython_chisqprob = None


def chi_square_p_value(statistic: float, degrees_of_freedom: int) -> float:
    if BIOPYTHON_AVAILABLE and _biopython_chisqprob is not None:
        return float(_biopython_chisqprob(statistic, degrees_of_freedom))

    return float(stats.chi2.sf(statistic, degrees_of_freedom))


def _hypothesis_library() -> str:
    return "biopython" if BIOPYTHON_AVAILABLE else "scipy"


def chi_square_goodness_of_fit(
    column_name: str,
    observed_counts: np.ndarray,
) -> TestResult | None:
    if len(observed_counts) < 2:
        return None

    total = float(observed_counts.sum())
    expected = np.full(len(observed_counts), total / len(observed_counts))
    if np.any(expected <= 0):
        return None

    statistic = float(np.sum((observed_counts - expected) ** 2 / expected))
    degrees_of_freedom = len(observed_counts) - 1
    p_value = chi_square_p_value(statistic, degrees_of_freedom)

    return TestResult(
        test_name=f"Chi-square goodness-of-fit: {column_name}",
        test_category="hypothesis",
        parameters={
            "column": column_name,
            "library": _hypothesis_library(),
            "test": "chi_square_goodness_of_fit",
            "degrees_of_freedom": degrees_of_freedom,
        },
        test_statistic=round(statistic, 6),
        p_value=round(p_value, 10),
        assumptions_validation={
            "min_expected_frequency": round(float(expected.min()), 6),
            "categories": int(len(observed_counts)),
        },
        raw_output={
            "observed": [int(value) for value in observed_counts],
            "expected_uniform": [round(float(value), 6) for value in expected],
        },
    )


def chi_square_independence(
    first_name: str,
    second_name: str,
    contingency: pd.DataFrame,
) -> TestResult | None:
    if contingency.shape[0] < 2 or contingency.shape[1] < 2:
        return None

    observed = contingency.to_numpy(dtype=float)
    chi2, _, degrees_of_freedom, expected = stats.chi2_contingency(observed)
    if expected.size == 0 or float(expected.min()) <= 0:
        return None

    p_value = chi_square_p_value(float(chi2), int(degrees_of_freedom))

    return TestResult(
        test_name=f"Chi-square independence: {first_name} vs {second_name}",
        test_category="hypothesis",
        parameters={
            "row_variable": first_name,
            "column_variable": second_name,
            "library": _hypothesis_library(),
            "test": "chi_square_independence",
            "degrees_of_freedom": int(degrees_of_freedom),
        },
        test_statistic=round(float(chi2), 6),
        p_value=round(p_value, 10),
        assumptions_validation={
            "min_expected_cell_count": round(float(expected.min()), 6),
        },
        raw_output={
            "contingency_table": contingency.astype(int).to_dict(),
            "expected_frequencies": pd.DataFrame(
                expected,
                index=contingency.index,
                columns=contingency.columns,
            )
            .round(4)
            .to_dict(),
        },
    )


def fisher_exact_test(
    first_name: str,
    second_name: str,
    contingency: pd.DataFrame,
) -> TestResult | None:
    if contingency.shape != (2, 2):
        return None

    observed = contingency.to_numpy(dtype=int)
    odds_ratio, p_value = stats.fisher_exact(observed)

    return TestResult(
        test_name=f"Fisher exact test: {first_name} vs {second_name}",
        test_category="hypothesis",
        parameters={
            "row_variable": first_name,
            "column_variable": second_name,
            "library": "scipy",
            "test": "fisher_exact",
            "reason": "Biopython Fisher exact unavailable",
        },
        test_statistic=round(float(odds_ratio), 6),
        p_value=round(float(p_value), 10),
        effect_sizes={"odds_ratio": round(float(odds_ratio), 6)},
        raw_output={
            "contingency_table": contingency.astype(int).to_dict(),
        },
    )
