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

    def describe(self):
        print(f"{self.make} {self.model} - capacity: {self.capacity} Kg")    
       
truck = truck("MAN", "TGS", 10000)
truck.describe()