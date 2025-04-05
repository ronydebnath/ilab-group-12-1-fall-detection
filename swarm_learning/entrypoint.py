import os
import sys

role = os.environ.get("ROLE", "node").lower()
print(f"Container starting with ROLE: {role}")

if role == "aggregator":
    sys.exit(os.system("python federated_learning/aggregator.py"))
elif role == "node":
    sys.exit(os.system("python federated_learning/node.py"))
else:
    print(f"Unknown ROLE: {role}")
    sys.exit(1)
