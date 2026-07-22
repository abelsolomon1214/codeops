from abc import ABC, abstractmethod

class vehicle(ABC):
    def __init__(self, make, model):
        self.make = make
        self.model = model

    def describe(self):
        print(f"{self.make} {self.model}")

    @abstractmethod
    def wheels(self):
        pass

class car(vehicle):
    def wheels(self):
        return 4

class truck(vehicle):
    def __init__(self, make, model, capacity):
        super().__init__(make, model)
        self.capacity = capacity

    def wheels(self):
        return 6

car = car("Toyota", "corolla")
truck = truck("Isuzu", "NPR", 5000)

car.describe()
print("wheels:", car.wheels())

truck.describe()
print("wheels:", truck.wheels())