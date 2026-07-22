class vehicle:
    def __init__(self, make, model):
        self.make = make
        self.model = model

    def describe(self):
        print(f"vehicle: {self.make} {self.model}")

class car(vehicle):
    pass
class truck(vehicle):
    pass

car = car("Toyota", "Corolla")
truck = truck("Isuzu", "NPR")

car.describe()
truck.describe()