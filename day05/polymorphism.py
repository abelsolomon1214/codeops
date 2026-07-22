class vehicle:
    def __init__(self, make, model):
        self.make = make
        self.model = model

    def describe(self):
        print(f"{self.make} {self.model}")

class car(vehicle):
    pass

class truck(vehicle):
    def __init__(self, make, model, capacity):
        super().__init__(make, model)
        self.capacity = capacity

    def describe(self):
        print(f"{self.make} {self.model} - capacity: {self.capacity} Kg")

vehicles = [car("Toyota", "corolla"), 
           truck("Isuzu", "NPR", 5000),
            car("Honda", "BMW") ]     
for vehicle in vehicles:
    vehicle.describe()   