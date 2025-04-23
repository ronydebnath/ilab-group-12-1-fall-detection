# Troubleshooting Guide

## Common Issues and Solutions

### 1. Node Communication Issues

#### Symptoms
- Nodes unable to connect to peers
- Weight sharing failures
- Timeout errors in logs

#### Solutions
1. **Check Network Configuration**
   ```bash
   # Verify port availability
   netstat -tuln | grep 5555
   
   # Test network connectivity
   ping node2
   ```

2. **Verify Environment Variables**
   ```bash
   # Check node configuration
   docker-compose exec node1 env | grep PEERS
   ```

3. **Inspect ZeroMQ Logs**
   ```bash
   # View detailed logs
   docker-compose logs -f node1
   ```

### 2. Docker Container Issues

#### Symptoms
- Container startup failures
- Resource constraints
- Permission errors

#### Solutions
1. **Container Startup**
   ```bash
   # Check container status
   docker-compose ps
   
   # View container logs
   docker-compose logs node1
   ```

2. **Resource Management**
   ```bash
   # Check system resources
   docker stats
   
   # Adjust container limits
   docker-compose up -d --scale node1=1 --no-recreate
   ```

3. **Permission Issues**
   ```bash
   # Fix volume permissions
   docker-compose exec node1 chown -R app:app /app/data
   ```

### 3. Model Training Issues

#### Symptoms
- Training failures
- Poor model performance
- Memory errors

#### Solutions
1. **Data Validation**
   ```bash
   # Check data format
   docker-compose exec node1 python -c "import pandas as pd; print(pd.read_pickle('/app/data/df_filtered_cnn.pkl').head())"
   ```

2. **Memory Management**
   ```bash
   # Monitor memory usage
   docker stats
   
   # Adjust batch size
   export BATCH_SIZE=32
   ```

3. **Model Configuration**
   ```bash
   # Verify model architecture
   docker-compose exec node1 python -c "from swarm_learning.node import create_model; print(create_model.__doc__)"
   ```

### 4. Weight Sharing Issues

#### Symptoms
- Failed weight aggregation
- Inconsistent model updates
- Communication timeouts

#### Solutions
1. **Check Weight Format**
   ```bash
   # Verify weight serialization
   docker-compose exec node1 python -c "from swarm_learning.node import serialize_weights; print(serialize_weights.__doc__)"
   ```

2. **Monitor Aggregation**
   ```bash
   # View aggregation logs
   docker-compose logs -f node1 | grep "aggregation"
   ```

3. **Verify Peer Configuration**
   ```bash
   # Check peer list
   docker-compose exec node1 env | grep PEERS
   ```

## Log Analysis

### Common Log Messages

1. **Connection Issues**
   ```
   ERROR: Connection refused
   Solution: Check peer availability and network configuration
   ```

2. **Weight Sharing**
   ```
   WARNING: Timeout while sending weights
   Solution: Increase timeout value or check network latency
   ```

3. **Model Training**
   ```
   ERROR: Out of memory
   Solution: Reduce batch size or increase container memory
   ```

### Log Location
- Container logs: `docker-compose logs`
- Application logs: `/app/logs/` inside container
- System logs: Host system logs

## Performance Tuning

### 1. Resource Optimization
```bash
# Adjust container resources
docker-compose up -d --scale node1=1 --no-recreate --memory=4G --cpus=2
```

### 2. Network Optimization
```bash
# Configure network settings
docker network create --driver=bridge --subnet=172.20.0.0/16 swarm-network
```

### 3. Model Optimization
```python
# Adjust model parameters
model.compile(
    optimizer='adam',
    loss='sparse_categorical_crossentropy',
    metrics=['accuracy'],
    run_eagerly=False
)
```

## Recovery Procedures

### 1. Node Recovery
```bash
# Restart failed node
docker-compose restart node1

# Verify recovery
docker-compose logs -f node1
```

### 2. Data Recovery
```bash
# Restore from backup
docker-compose exec node1 tar -xzf /app/data/backup.tar.gz -C /app/data/

# Verify data integrity
docker-compose exec node1 python -c "import pandas as pd; print(pd.read_pickle('/app/data/df_filtered_cnn.pkl').info())"
```

### 3. System Recovery
```bash
# Stop all containers
docker-compose down

# Remove volumes
docker-compose down -v

# Rebuild and start
docker-compose up --build
```

## Monitoring Tools

### 1. System Monitoring
```bash
# Container stats
docker stats

# Resource usage
docker-compose top
```

### 2. Network Monitoring
```bash
# Network connections
netstat -tuln

# Network traffic
docker-compose exec node1 tcpdump -i eth0
```

### 3. Application Monitoring
```bash
# Log monitoring
docker-compose logs -f

# Performance metrics
docker-compose exec node1 python -c "import psutil; print(psutil.cpu_percent())"
```

## Best Practices

### 1. Regular Maintenance
- Monitor system logs
- Check resource usage
- Verify data integrity
- Update dependencies

### 2. Backup Strategy
- Regular data backups
- Configuration backups
- Model checkpointing
- Log archiving

### 3. Security Measures
- Regular security audits
- Access control
- Network monitoring
- Data encryption 