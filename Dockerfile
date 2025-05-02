# Use an official Python 3.9 slim image as base
FROM python:3.10-slim

# Set working directory
WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

    # Copy Poetry dependency files first to leverage Docker cache
COPY pyproject.toml poetry.lock* README.md ./

# Install Poetry
RUN pip install --upgrade pip && pip install poetry

# Install Python dependencies using Poetry (no dev dependencies)
RUN poetry config virtualenvs.create false \
    && poetry install --no-root --no-interaction --no-ansi --only main

# Copy the rest of the application
COPY . .

# Create data directory
RUN mkdir -p /app/data

# Set environment variables
ENV PYTHONUNBUFFERED=1
ENV PYTHONPATH=/app

# Expose the port
EXPOSE 5555

# Command to run the application
CMD ["python", "swarm_learning/entrypoint.py"]
