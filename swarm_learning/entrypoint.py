import os
import sys

role = os.environ.get("ROLE", "node").lower()
print(f"Container starting with ROLE: {role}")

if role == "node":
    os.system("python swarm_learning/node_server.py &")
    sys.exit(os.system("python swarm_learning/node.py"))
else:
    print("ROLE must be 'node' for P2P swarm setup.")
    sys.exit(1)