from __future__ import annotations

import base64
import io
from typing import Literal

import matplotlib

matplotlib.use("Agg")

import matplotlib.pyplot as plt
import pandas as pd
import seaborn as sns

from app.schemas.analysis import ColumnSpec
from app.schemas.graphs import GenerateGraphsRequest, GraphAsset
from app.services.data_loader import data_loader

GraphType = Literal["bar", "histogram", "box", "scatter", "line", "heatmap"]


class GraphGeneratorService:
    def generate(self, request: GenerateGraphsRequest) -> list[GraphAsset]:
        frame = data_loader.load(request.file_url, request.file_format, request.sheet_name)
        working_frame = data_loader.select_columns(frame, request.columns)

        specs = [column for column in request.columns if column.variable_type != "excluded"]
        numerical = [spec for spec in specs if spec.data_type == "numerical"]
        categorical = [spec for spec in specs if spec.data_type == "categorical"]

        assets: list[GraphAsset] = []

        for graph_type in request.graph_types:
            if graph_type == "histogram" and numerical:
                assets.append(self._histogram(working_frame, numerical[0], request.dpi))
            elif graph_type == "bar" and categorical:
                assets.append(self._bar_chart(working_frame, categorical[0], request.dpi))
            elif graph_type == "box" and numerical and categorical:
                assets.append(self._box_plot(working_frame, numerical[0], categorical[0], request.dpi))
            elif graph_type == "scatter" and len(numerical) >= 2:
                assets.append(self._scatter_plot(working_frame, numerical[0], numerical[1], request.dpi))
            elif graph_type == "heatmap" and len(numerical) >= 2:
                assets.append(self._heatmap(working_frame, numerical, request.dpi))

        return assets

    def _histogram(self, frame: pd.DataFrame, column: ColumnSpec, dpi: int) -> GraphAsset:
        series = pd.to_numeric(frame[column.name], errors="coerce").dropna()
        figure, axis = plt.subplots(figsize=(8, 5))
        axis.hist(series, bins=min(20, max(5, series.nunique())), color="#2563eb", edgecolor="white")
        mean = float(series.mean())
        median = float(series.median())
        mode_result = series.mode()
        mode = float(mode_result.iloc[0]) if not mode_result.empty else None
        axis.axvline(mean, color="#dc2626", linestyle="--", linewidth=1.5, label=f"Mean: {mean:.2f}")
        axis.axvline(median, color="#16a34a", linestyle=":", linewidth=1.5, label=f"Median: {median:.2f}")
        if mode is not None:
            axis.axvline(mode, color="#9333ea", linestyle="-.", linewidth=1.5, label=f"Mode: {mode:.2f}")
        axis.legend(fontsize=8)
        axis.set_title(f"Distribution of {column.name}")
        axis.set_xlabel(column.name)
        axis.set_ylabel("Frequency")
        return self._asset("histogram", f"Distribution of {column.name}", figure, dpi, column.name)

    def _bar_chart(self, frame: pd.DataFrame, column: ColumnSpec, dpi: int) -> GraphAsset:
        counts = frame[column.name].astype(str).value_counts().head(12)
        figure, axis = plt.subplots(figsize=(8, 5))
        counts.plot(kind="bar", ax=axis, color="#0f766e")
        axis.set_title(f"Frequency of {column.name}")
        axis.set_xlabel(column.name)
        axis.set_ylabel("Count")
        figure.autofmt_xdate(rotation=30)
        return self._asset("bar", f"Frequency of {column.name}", figure, dpi, column.name)

    def _box_plot(
        self,
        frame: pd.DataFrame,
        numerical: ColumnSpec,
        categorical: ColumnSpec,
        dpi: int,
    ) -> GraphAsset:
        plot_frame = frame[[categorical.name, numerical.name]].copy()
        plot_frame[numerical.name] = pd.to_numeric(plot_frame[numerical.name], errors="coerce")
        plot_frame = plot_frame.dropna()
        figure, axis = plt.subplots(figsize=(8, 5))
        plot_frame.boxplot(column=numerical.name, by=categorical.name, ax=axis)
        axis.set_title(f"{numerical.name} by {categorical.name}")
        figure.suptitle("")
        return self._asset(
            "box",
            f"Comparison of {numerical.name} across {categorical.name}",
            figure,
            dpi,
            f"{numerical.name}_by_{categorical.name}",
        )

    def _scatter_plot(
        self,
        frame: pd.DataFrame,
        x_column: ColumnSpec,
        y_column: ColumnSpec,
        dpi: int,
    ) -> GraphAsset:
        plot_frame = frame[[x_column.name, y_column.name]].copy()
        plot_frame[x_column.name] = pd.to_numeric(plot_frame[x_column.name], errors="coerce")
        plot_frame[y_column.name] = pd.to_numeric(plot_frame[y_column.name], errors="coerce")
        plot_frame = plot_frame.dropna()
        figure, axis = plt.subplots(figsize=(8, 5))
        axis.scatter(plot_frame[x_column.name], plot_frame[y_column.name], alpha=0.7, color="#7c3aed")
        axis.set_title(f"{y_column.name} vs {x_column.name}")
        axis.set_xlabel(x_column.name)
        axis.set_ylabel(y_column.name)
        return self._asset(
            "scatter",
            f"Relationship between {x_column.name} and {y_column.name}",
            figure,
            dpi,
            f"{x_column.name}_vs_{y_column.name}",
        )

    def _heatmap(self, frame: pd.DataFrame, numerical: list[ColumnSpec], dpi: int) -> GraphAsset:
        columns = [spec.name for spec in numerical[:6]]
        numeric_frame = frame[columns].apply(pd.to_numeric, errors="coerce")
        correlation = numeric_frame.corr(numeric_only=True)
        figure, axis = plt.subplots(figsize=(8, 6))
        sns.heatmap(correlation, annot=True, fmt=".2f", cmap="coolwarm", ax=axis)
        axis.set_title("Correlation matrix")
        return self._asset("heatmap", "Correlation matrix", figure, dpi, "correlation_matrix")

    def _asset(
        self,
        graph_type: str,
        caption: str,
        figure: plt.Figure,
        dpi: int,
        slug: str,
    ) -> GraphAsset:
        buffer = io.BytesIO()
        figure.savefig(buffer, format="png", dpi=dpi, bbox_inches="tight")
        plt.close(figure)
        encoded = base64.b64encode(buffer.getvalue()).decode("ascii")

        return GraphAsset(
            graph_type=graph_type,
            title=slug.replace("_", " ").title(),
            caption=caption,
            image_base64=encoded,
            mime_type="image/png",
        )


graph_generator = GraphGeneratorService()
