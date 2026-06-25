import pandas as pd
import pytest

from app.services.biopython_hypothesis import (
    chi_square_goodness_of_fit,
    chi_square_independence,
    chi_square_p_value,
)
from app.services import upload_profiler as upload_profiler_module


@pytest.fixture
def sample_frame() -> pd.DataFrame:
    return pd.DataFrame(
        {
            "patient_id": [1, 2, 3, 4],
            "age": [45, 52, 49, 60],
            "group": ["A", "B", "A", "B"],
        }
    )


def test_upload_profiler_computes_numerical_and_categorical_stats(
    monkeypatch: pytest.MonkeyPatch,
    sample_frame: pd.DataFrame,
) -> None:
    monkeypatch.setattr(
        upload_profiler_module.data_loader,
        "load",
        lambda file_url, file_format, sheet_name: sample_frame.copy(),
    )

    result = upload_profiler_module.upload_profiler.profile(
        "http://example.test/data.csv",
        "csv",
        None,
    )

    age_column = next(column for column in result["columns"] if column["name"] == "age")
    group_column = next(column for column in result["columns"] if column["name"] == "group")

    assert result["row_count"] == 4
    assert age_column["inferred_type"] == "numerical"
    assert age_column["descriptive_statistics"]["mean"] == 51.5
    assert age_column["descriptive_statistics"]["median"] == 50.5
    assert age_column["descriptive_statistics"]["mode"] is not None

    assert group_column["inferred_type"] == "categorical"
    assert len(group_column["frequency_table"]) == 2
    assert group_column["frequency_table"][0]["percent"] == 50.0
    assert group_column["frequency_table"][1]["cumulative_percent"] == 100.0


def test_chi_square_p_value_uses_biopython() -> None:
    p_value = chi_square_p_value(3.841, 1)
    assert 0.04 < p_value < 0.06


def test_chi_square_goodness_of_fit_returns_hypothesis_result() -> None:
    import numpy as np

    result = chi_square_goodness_of_fit("group", np.array([10, 10, 10, 10]))
    assert result is not None
    assert result.parameters["library"] in {"biopython", "scipy"}
    assert result.p_value is not None


def test_chi_square_independence_returns_hypothesis_result() -> None:
    contingency = pd.DataFrame(
        [[10, 20], [15, 25]],
        index=["A", "B"],
        columns=["X", "Y"],
    )
    result = chi_square_independence("row", "column", contingency)
    assert result is not None
    assert result.parameters["library"] in {"biopython", "scipy"}
    assert result.test_statistic is not None
    assert result.p_value is not None
