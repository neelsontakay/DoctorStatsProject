from __future__ import annotations

from typing import Any

import numpy as np
import pandas as pd
from scipy import stats
import statsmodels.api as sm

from app.schemas.analysis import ColumnSpec, TestResult


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
            frequencies = series.value_counts(dropna=False).to_dict()
            results.append(
                TestResult(
                    test_name=f"Frequency table: {spec.name}",
                    test_category="frequency",
                    parameters={"column": spec.name},
                    raw_output={
                        "frequencies": {str(key): int(value) for key, value in frequencies.items()}
                    },
                )
            )

        if len(categorical) >= 2:
            first, second = categorical[0], categorical[1]
            contingency = pd.crosstab(frame[first.name], frame[second.name])
            if contingency.shape[0] >= 2 and contingency.shape[1] >= 2:
                chi2, p_value, dof, _ = stats.chi2_contingency(contingency)
                results.append(
                    TestResult(
                        test_name=f"Chi-square: {first.name} vs {second.name}",
                        test_category="hypothesis",
                        parameters={
                            "row_variable": first.name,
                            "column_variable": second.name,
                            "degrees_of_freedom": int(dof),
                        },
                        test_statistic=round(float(chi2), 6),
                        p_value=round(float(p_value), 10),
                        raw_output={
                            "contingency_table": contingency.astype(int).to_dict(),
                        },
                    )
                )

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
