from fastapi import APIRouter, Depends

from app.api.deps import verify_service_token
from app.schemas.profile import ProfileRequest, ProfileResponse
from app.services.upload_profiler import upload_profiler

router = APIRouter(prefix="/api/v1", tags=["profile"])


@router.post("/profile", response_model=ProfileResponse)
def profile_dataset(
    request: ProfileRequest,
    _: None = Depends(verify_service_token),
) -> ProfileResponse:
    result = upload_profiler.profile(
        request.file_url,
        request.file_format,
        request.sheet_name,
    )
    return ProfileResponse(**result)
