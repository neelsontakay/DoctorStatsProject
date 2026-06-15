from fastapi import APIRouter, Depends, HTTPException, status

from app.api.deps import verify_service_token
from app.schemas.graphs import GenerateGraphsRequest, GenerateGraphsResponse
from app.services.analysis_store import analysis_store
from app.services.graph_generator import graph_generator

router = APIRouter(prefix="/api/v1", tags=["graphs"])


@router.post("/generate-graphs", response_model=GenerateGraphsResponse)
def generate_graphs(
    request: GenerateGraphsRequest,
    _: None = Depends(verify_service_token),
) -> GenerateGraphsResponse:
    record = analysis_store.get(request.analysis_id)
    if record is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Analysis not found.")

    if record.status != "completed":
        raise HTTPException(
            status_code=status.HTTP_409_CONFLICT,
            detail="Graphs can only be generated for completed analyses.",
        )

    graphs = graph_generator.generate(request)

    return GenerateGraphsResponse(analysis_id=request.analysis_id, graphs=graphs)
