import os
import sys

role = os.environ.get("ROLE", "node").lower()
print(f"Container starting with ROLE: {role}")

if role == "node":
    sys.exit(os.system("python swarm_learning/node.py"))
else:
    print(f"Unknown ROLE: {role}")
    sys.exit(1)
