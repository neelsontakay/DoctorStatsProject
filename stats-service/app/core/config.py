from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    """Environment-driven configuration (prefix: STATS_)."""

    model_config = SettingsConfigDict(
        env_prefix="STATS_",
        env_file=".env",
        env_file_encoding="utf-8",
        extra="ignore",
    )

    service_name: str = "doctorstats-stats-service"
    debug: bool = False
    service_token: str = ""
    callback_timeout_seconds: int = 30


settings = Settings()
