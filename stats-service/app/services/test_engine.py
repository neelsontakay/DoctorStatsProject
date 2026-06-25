from __future__ import annotations

from typing import Any

import numpy as np
import pandas as pd
from scipy import stats
import statsmodels.api as sm

from app.schemas.analysis import ColumnSpec, TestResult
from app.services.biopython_hypothesis import (
    chi_square_goodness_of_fit,
    chi_square_independence,
    fisher_exact_test,
)


class TestEngine:
    def run(self, frame: pd.DataFrame, columns: list[ColumnSpec]) -> list[TestResult]:
        specs = [column for column in columns if column.variable_type != "excluded"]
        results: list[TestResult] = []

        numerical = [spec for spec in specs if spec.data_type == "numerical"]
        categorical = [spec for spec in specs if spec.data_type == "categorical"]
        dependents = [spec for spec in specs if spec.variable_type == "dependent"]
        independents = [spec for spec in specs if spec.variable_type == "independent"]

        for spec in categorical:
            series = frame[spec.name].astype(str)
            frequency_table = self._build_frequency_table(series)
            results.append(
                TestResult(
                    test_name=f"Frequency table: {spec.name}",
                    test_category="frequency",
                    parameters={"column": spec.name},
                    raw_output={"frequency_table": frequency_table},
                )
            )

            observed = series.dropna().value_counts().to_numpy(dtype=int)
            goodness_of_fit = chi_square_goodness_of_fit(spec.name, observed)
            if goodness_of_fit is not None:
                results.append(goodness_of_fit)

        if len(categorical) >= 2:
            first, second = categorical[0], categorical[1]
            contingency = pd.crosstab(frame[first.name], frame[second.name])
            results.append(
                TestResult(
                    test_name=f"Cross-tabulation: {first.name} vs {second.name}",
                    test_category="frequency",
                    parameters={
                        "row_variable": first.name,
                        "column_variable": second.name,
                    },
                    raw_output={
                        "contingency_table": contingency.astype(int).to_dict(),
                    },
                )
            )

            if contingency.shape[0] >= 2 and contingency.shape[1] >= 2:
                _, _, _, expected = stats.chi2_contingency(contingency.to_numpy())
                if contingency.shape == (2, 2) and float(expected.min()) < 5:
                    fisher_result = fisher_exact_test(first.name, second.name, contingency)
                    if fisher_result is not None:
                        results.append(fisher_result)
                else:
                    independence = chi_square_independence(first.name, second.name, contingency)
                    if independence is not None:
                        results.append(independence)

        dependent = dependents[0] if dependents else (numerical[0] if numerical else None)
        independent_numeric = next((spec for spec in independents if spec.data_type == "numerical"), None)
        independent_categorical = next(
            (spec for spec in independents if spec.data_type == "categorical"),
            categorical[0] if categorical else None,
        )

        if dependent and independent_categorical and dependent.data_type == "numerical":
            results.extend(self._group_comparison(frame, dependent, independent_categorical))

        if len(numerical) >= 2:
            first_num, second_num = numerical[0], numerical[1]
            x = pd.to_numeric(frame[first_num.name], errors="coerce")
            y = pd.to_numeric(frame[second_num.name], errors="coerce")
            paired = pd.concat([x, y], axis=1).dropna()
            if len(paired) >= 3:
                coefficient, p_value = stats.pearsonr(paired.iloc[:, 0], paired.iloc[:, 1])
                results.append(
                    TestResult(
                        test_name=f"Pearson correlation: {first_num.name} vs {second_num.name}",
                        test_category="correlation",
                        parameters={
                            "variable_x": first_num.name,
                            "variable_y": second_num.name,
                            "sample_size": int(len(paired)),
                            "library": "scipy",
                            "test": "pearsonr",
                        },
                        test_statistic=round(float(coefficient), 6),
                        p_value=round(float(p_value), 10),
                        effect_sizes={"pearson_r": round(float(coefficient), 6)},
                    )
                )

        if dependent and independent_numeric and dependent.data_type == "numerical":
            result = self._linear_regression(frame, dependent, independent_numeric)
            if result is not None:
                results.append(result)

        return results

    def _build_frequency_table(self, series: pd.Series, limit: int = 20) -> list[dict[str, Any]]:
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

    def _group_comparison(
        self,
        frame: pd.DataFrame,
        dependent: ColumnSpec,
        independent: ColumnSpec,
    ) -> list[TestResult]:
        data = frame[[independent.name, dependent.name]].copy()
        data[dependent.name] = pd.to_numeric(data[dependent.name], errors="coerce")
        data = data.dropna()

        groups = data.groupby(independent.name)[dependent.name].apply(list)
        groups = {key: values for key, values in groups.items() if len(values) >= 2}

        if len(groups) < 2:
            return []

        group_names = list(groups.keys())

        if len(group_names) == 2:
            first = np.array(groups[group_names[0]], dtype=float)
            second = np.array(groups[group_names[1]], dtype=float)

            t_stat, p_value = stats.ttest_ind(first, second, equal_var=False)
            pooled_std = np.sqrt((np.var(first, ddof=1) + np.var(second, ddof=1)) / 2)
            cohens_d = (np.mean(first) - np.mean(second)) / pooled_std if pooled_std else 0.0

            return [
                TestResult(
                    test_name=f"Independent t-test: {dependent.name} by {independent.name}",
                    test_category="hypothesis",
                    parameters={
                        "dependent": dependent.name,
                        "independent": independent.name,
                        "group_a": str(group_names[0]),
                        "group_b": str(group_names[1]),
                        "library": "scipy",
                        "test": "ttest_ind",
                    },
                    test_statistic=round(float(t_stat), 6),
                    p_value=round(float(p_value), 10),
                    effect_sizes={"cohens_d": round(float(cohens_d), 6)},
                    assumptions_validation={
                        "min_group_size": min(len(first), len(second)),
                        "groups_compared": 2,
                    },
                )
            ]

        samples = [np.array(values, dtype=float) for values in groups.values()]
        f_stat, p_value = stats.f_oneway(*samples)

        return [
            TestResult(
                test_name=f"One-way ANOVA: {dependent.name} by {independent.name}",
                test_category="hypothesis",
                parameters={
                    "dependent": dependent.name,
                    "independent": independent.name,
                    "groups": [str(name) for name in group_names],
                    "library": "scipy",
                    "test": "f_oneway",
                },
                test_statistic=round(float(f_stat), 6),
                p_value=round(float(p_value), 10),
                assumptions_validation={
                    "group_count": len(group_names),
                    "min_group_size": min(len(values) for values in groups.values()),
                },
            )
        ]

    def _linear_regression(
        self,
        frame: pd.DataFrame,
        dependent: ColumnSpec,
        independent: ColumnSpec,
    ) -> TestResult | None:
        data = frame[[independent.name, dependent.name]].copy()
        data[independent.name] = pd.to_numeric(data[independent.name], errors="coerce")
        data[dependent.name] = pd.to_numeric(data[dependent.name], errors="coerce")
        data = data.dropna()

        if len(data) < 3:
            return None

        x = sm.add_constant(data[independent.name])
        model = sm.OLS(data[dependent.name], x).fit()

        return TestResult(
            test_name=f"Linear regression: {dependent.name} ~ {independent.name}",
            test_category="regression",
            parameters={
                "dependent": dependent.name,
                "independent": independent.name,
                "sample_size": int(len(data)),
                "library": "statsmodels",
                "test": "ols",
            },
            test_statistic=round(float(model.fvalue), 6) if model.fvalue is not None else None,
            p_value=round(float(model.f_pvalue), 10) if model.f_pvalue is not None else None,
            raw_output={
                "r_squared": round(float(model.rsquared), 6),
                "coefficients": {
                    str(name): round(float(value), 6) for name, value in model.params.items()
                },
            },
            assumptions_validation={"residual_df": int(model.df_resid)},
        )


test_engine = TestEngine()
