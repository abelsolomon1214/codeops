class vehicle:
    def __init__(self, make, model):
        self.make = make
        self.model = model

    def describe(self):
        print(f"vehicle: {self.make} {self.model}")


class truck(vehicle):
    def __init__(self, make, model, capacity):
        super().__init__(make, model)
        self.capacity = capacity
       
truck = truck("Isuzu", "NPR", 5000)

print("make:", truck.make)
print("Model:", truck.model)
print("capacity:", truck.capacity, "kg")
