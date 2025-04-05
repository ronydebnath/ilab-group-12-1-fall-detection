# Use an official Python 3.10 slim image as base
FROM python:3.10-slim

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    curl \
    libglib2.0-0 libsm6 libxrender1 libxext6 \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy poetry and config files
COPY pyproject.toml poetry.lock* README.md /app/

# Install pip and poetry
RUN pip install --no-cache-dir --upgrade pip \
    && pip install poetry

# Downgrade numpy explicitly to avoid incompatibility with TensorFlow
RUN poetry config virtualenvs.create false \
    && poetry add numpy@^1.26.4 tensorflow@2.16.1

# Copy app source
COPY . /app

# Expose port
EXPOSE 5000

# Entry point
CMD ["python", "swarm_learning/entrypoint.py"]
