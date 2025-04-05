# Use an official Python 3.9 slim image as base
FROM python:3.10-slim

# Install build-essential if needed for some dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install Poetry globally
RUN pip install poetry

# Set working directory in container
WORKDIR /app

# Copy poetry configuration files and README.md from the repository root into /app/
COPY pyproject.toml poetry.lock* README.md /app/

# Configure Poetry to install packages in the system environment and do not install the current project (--no-root)
RUN poetry config virtualenvs.create false && poetry install --no-root --no-interaction --no-ansi

# Copy the entire repository into the container
COPY . /app

# Expose port 5000 (for the aggregator's Flask server)
EXPOSE 5000

# Set the entrypoint to run the swarm learning entrypoint script.
CMD ["python", "federated_learning/entrypoint.py"]
