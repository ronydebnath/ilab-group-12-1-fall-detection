FROM python:3.10-slim

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential curl libglib2.0-0 libsm6 libxrender1 libxext6 \
  && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Install Poetry via official installer (v1.7+)
RUN curl -sSL https://install.python-poetry.org | python3 - \
 && ln -s /root/.local/bin/poetry /usr/local/bin/poetry

# Disable venv creation & install dependencies
COPY pyproject.toml poetry.lock README.md /app/
RUN poetry config virtualenvs.create false \
 && poetry add fedml@^0.9.6 torch@2.6.0 \
 && poetry install --no-root --no-interaction --no-ansi

COPY . /app
CMD ["python", "src/main.py"] 