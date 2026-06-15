import io

import pandas as pd
from fastapi.testclient import TestClient

from app.main import app
from app.services.analysis_store import analysis_store

client = TestClient(app)


def _seed_completed_analysis() -> str:
    analysis_id = "DS-2026-GRAPH01"
    analysis_store.create(analysis_id)
    analysis_store.update(
        analysis_id,
        status="completed",
        progress_percent=100,
        data_profile={"row_count": 4},
        tests=[],
    )
    return analysis_id


def test_generate_graphs_returns_image_assets(monkeypatch) -> None:
    analysis_id = _seed_completed_analysis()

    def fake_load(file_url: str, file_format: str, sheet_name: str | None):
        return pd.read_csv(
            io.BytesIO(b"patient_id,age,group\n1,45,A\n2,52,B\n3,49,A\n4,60,B\n"),
        )

    monkeypatch.setattr("app.services.data_loader.data_loader.load", fake_load)

    payload = {
        "analysis_id": analysis_id,
        "file_url": "http://example.test/data.csv",
        "file_format": "csv",
        "sheet_name": None,
        "columns": [
            {
                "name": "patient_id",
                "index": 0,
                "data_type": "text",
                "variable_type": "identifier",
            },
            {
                "name": "age",
                "index": 1,
                "data_type": "numerical",
                "variable_type": "dependent",
            },
            {
                "name": "group",
                "index": 2,
                "data_type": "categorical",
                "variable_type": "independent",
            },
        ],
        "graph_types": ["histogram", "bar", "box", "scatter", "heatmap"],
    }

    response = client.post("/api/v1/generate-graphs", json=payload)
    assert response.status_code == 200
    body = response.json()
    assert body["analysis_id"] == analysis_id
    assert len(body["graphs"]) >= 1
    assert body["graphs"][0]["image_base64"]
