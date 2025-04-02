# Use an official Python runtime as a base image
FROM python:3.9-slim

# Set the working directory in the container
WORKDIR /app

# Copy the local requirements file to the container
COPY requirements.txt .

# Install any needed packages specified in requirements.txt
RUN pip install --no-cache-dir -r requirements.txt

# Copy the rest of the code into the container
COPY . .

# Expose a port if your node exposes a REST API (e.g., port 5000)
EXPOSE 5000

# Define environment variables (if needed)
ENV NODE_ID=default_node

# Command to run the node (adjust based on your implementation)
CMD ["python", "node.py"]
