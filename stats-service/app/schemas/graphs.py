from typing import Any, Literal

from pydantic import BaseModel, Field

from app.schemas.analysis import ColumnSpec


class GenerateGraphsRequest(BaseModel):
    analysis_id: str
    file_url: str
    file_format: Literal["xlsx", "xls", "csv"]
    sheet_name: str | None = None
    columns: list[ColumnSpec]
    graph_types: list[Literal["bar", "histogram", "box", "scatter", "line", "heatmap"]] = Field(
        default_factory=lambda: ["histogram", "bar", "box", "scatter", "heatmap"]
    )
    output_format: Literal["png", "svg"] = "png"
    dpi: int = 300


class GraphAsset(BaseModel):
    graph_type: str
    title: str
    caption: str
    image_base64: str
    mime_type: str = "image/png"


class GenerateGraphsResponse(BaseModel):
    analysis_id: str
    graphs: list[GraphAsset]
