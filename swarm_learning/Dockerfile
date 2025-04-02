# Use an official Python slim image
FROM python:3.9-slim

# (Optional) Install build tools if needed for certain dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

# Install Poetry globally
RUN pip install poetry

# Set working directory
WORKDIR /app

# Copy poetry files (pyproject.toml and poetry.lock) to install dependencies
COPY pyproject.toml poetry.lock* /app/

# Configure poetry to install packages into the system environment (not a virtualenv)
RUN poetry config virtualenvs.create false && poetry install --no-interaction --no-ansi

# Copy the entire repository into the container
COPY . /app

# Expose port 5000 (used by the aggregator Flask app)
EXPOSE 5000

# Set default command to run our entrypoint script.
# This script will check the ROLE environment variable and launch aggregator.py or node.py.
CMD ["python", "swarm_learning/entrypoint.py"]
