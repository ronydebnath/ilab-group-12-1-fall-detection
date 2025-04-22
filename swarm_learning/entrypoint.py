import os
import subprocess
import time

def main():
    # Get node configuration from environment variables
    node_id = os.environ.get('NODE_ID', 'node_default')
    peers = os.environ.get('PEERS', '').split(',')
    
    print(f"Starting node {node_id} with peers: {peers}")
    
    # Start the node server in the background
    server_process = subprocess.Popen(
        ['python', 'swarm_learning/node_server.py'],
        env=os.environ
    )
    
    # Give the server a moment to start
    time.sleep(2)
    
    try:
        # Start the node training process
        subprocess.run(
            ['python', 'swarm_learning/node.py'],
            env=os.environ,
            check=True
        )
    except KeyboardInterrupt:
        print("Shutting down...")
    finally:
        # Clean up the server process
        server_process.terminate()
        server_process.wait()

if __name__ == "__main__":
    main()
