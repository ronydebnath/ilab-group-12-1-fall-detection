# src/main.py

import fedml
from fedml import FedMLRunner

def run_federated_learning():
    # 1) parse args from fedml_config.yaml
    args = fedml.init()

    # 2) select device (CPU/GPU)
    device = fedml.device.get_device(args)

    # 3) load your Mobifall dataset
    #    you’ll need to implement data loader under fedml.data.load
    dataset, output_dim = fedml.data.load(args)

    # 4) create or wrap your model
    model = fedml.model.create(args, output_dim)

    # 5) start the federated protocol
    FedMLRunner(args, device, dataset, model).run()

# if __name__ == "__main__":
#     run_federated_learning()

# if __name__ == "__main__":
#     fedml.run_simulation()

if __name__ == "__main__":
    args = fedml.init()
    device = fedml.device.get_device(args)
    dataset, output_dim = fedml.data.load(args)
    model = fedml.model.create(args, output_dim)
    FedMLRunner(args, device, dataset, model).run()
