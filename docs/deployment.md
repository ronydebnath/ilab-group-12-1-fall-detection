# Deployment Guide

## Prerequisites

### System Requirements
- Docker and Docker Compose installed
- Minimum 4GB RAM per node
- 2 CPU cores per node
- 10GB free disk space
- Network connectivity between nodes

### Software Requirements
- Python 3.9 or compatible environment
- Docker Engine 20.10.0 or later
- Docker Compose 2.0.0 or later

## Deployment Steps

### 1. Environment Setup

#### Clone the Repository
```bash
git clone https://github.com/ronydebnath/ilab-group-12-1-fall-detection.git
cd ilab-group-12-1-fall-detection
```

#### Install Dependencies
```bash
# Using pip
pip install -r requirements.txt

# Using Docker (recommended)
docker-compose build
```

### 2. Configuration

#### Environment Variables
Create a `.env` file in the project root:
```bash
# Node Configuration
NODE_ID=node1
PEERS=node2:5555,node3:5555
DATA_PATH=/app/data/df_filtered_cnn.pkl

# System Configuration
PYTHONUNBUFFERED=1
PYTHONPATH=/app
```

#### Docker Configuration
Review and modify `docker-compose.yml` if needed:
```yaml
services:
  node1:
    build: .
    environment:
      - NODE_ID=node1
      - PEERS=node2:5555,node3:5555
      - DATA_PATH=/app/data/df_filtered_cnn.pkl
    ports:
      - "5555:5555"
    networks:
      - swarm-network
```

### 3. Data Preparation

#### Data Directory Structure
```
data/
├── df_filtered_cnn.pkl    # Preprocessed sensor data
└── models/                # Model checkpoints
```

#### Data Format
- Input data should be in pickle format
- Expected columns: ['acc_x', 'acc_y', 'acc_z', 'gyro_x', 'gyro_y', 'gyro_z', 'label']
- Labels: 0 (non-fall) or 1 (fall)

### 4. Deployment

#### Single Node Deployment
```bash
# Build the image
docker-compose build node1

# Run the container
docker-compose up node1
```

#### Multi-Node Deployment
```bash
# Build all images
docker-compose build

# Start all nodes
docker-compose up
```

### 5. Verification

#### Check Node Status
```bash
# View container logs
docker-compose logs -f

# Check container status
docker-compose ps
```

#### Verify Communication
- Check weight sharing logs
- Monitor model aggregation
- Verify error-free operation

## Monitoring

### Logging
- Node-specific logs in container output
- Weight sharing statistics
- Error messages and warnings

### Metrics
- Model performance
- Communication latency
- Resource utilization

## Maintenance

### Updates
```bash
# Pull latest changes
git pull

# Rebuild containers
docker-compose build

# Restart services
docker-compose up -d
```

### Backup
```bash
# Backup model checkpoints
docker-compose exec node1 tar -czf /app/data/models_backup.tar.gz /app/data/models/

# Backup configuration
docker-compose config > docker-compose.backup.yml
```

## Troubleshooting

### Common Issues
1. **Port Conflicts**
   - Check port availability
   - Update port mappings in docker-compose.yml

2. **Network Issues**
   - Verify network connectivity
   - Check firewall settings
   - Ensure correct peer addresses

3. **Resource Constraints**
   - Monitor system resources
   - Adjust container limits
   - Optimize model parameters

### Recovery Procedures
1. **Container Failure**
   ```bash
   # Restart failed container
   docker-compose restart node1
   ```

2. **Data Corruption**
   ```bash
   # Restore from backup
   docker-compose exec node1 tar -xzf /app/data/models_backup.tar.gz -C /app/data/
   ```

## Scaling

### Horizontal Scaling
1. Add new node to docker-compose.yml
2. Update peer configurations
3. Deploy new node
4. Verify integration

### Vertical Scaling
1. Adjust container resources
2. Optimize model parameters
3. Update system configuration

## Security

### Network Security
- Use internal networks
- Implement firewalls
- Monitor network traffic

### Data Security
- Regular backups
- Access control
- Encryption at rest

## Best Practices

### Deployment
- Use version control
- Maintain documentation
- Regular backups
- Monitor system health

### Operations
- Regular updates
- Performance monitoring
- Security audits
- Capacity planning 