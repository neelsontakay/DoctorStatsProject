import io

import pandas as pd
from fastapi.testclient import TestClient

from app.main import app

client = TestClient(app)


def _csv_bytes() -> bytes:
    frame = pd.DataFrame(
        {
            "patient_id": [1, 2, 3, 4],
            "age": [45, 52, 49, 60],
            "group": ["A", "B", "A", "B"],
        }
    )
    buffer = io.StringIO()
    frame.to_csv(buffer, index=False)
    return buffer.getvalue().encode("utf-8")


def test_analyze_accepts_request_and_returns_status(monkeypatch) -> None:
    def fake_load(file_url: str, file_format: str, sheet_name: str | None):
        return pd.read_csv(io.BytesIO(_csv_bytes()))

    monkeypatch.setattr("app.services.data_loader.data_loader.load", fake_load)
    monkeypatch.setattr("app.services.callback_client.callback_client.send", lambda *args, **kwargs: None)

    payload = {
        "job_id": "DS-2026-TESTJOB1",
        "file_url": "http://example.test/data.csv",
        "file_format": "csv",
        "sheet_name": None,
        "objectives": "Compare age across treatment groups.",
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
        "callback_url": None,
    }

    response = client.post("/api/v1/analyze", json=payload)
    assert response.status_code == 202
    assert response.json()["analysis_id"] == "DS-2026-TESTJOB1"

    status_response = client.get("/api/v1/analysis/DS-2026-TESTJOB1/status")
    assert status_response.status_code == 200
    assert status_response.json()["status"] in {"completed", "processing", "pending"}
