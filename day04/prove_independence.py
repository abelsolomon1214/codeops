class product:
    def __init__(self, name, price, quantity):
        self.name = name
        self.price = price
        self.__quantity = quantity

    @property
    def quantity(self):
        return self.__quantity

    def restock(self, n):
        self.__quantity += n

    def sell(self, n):
        if n <= self.__quantity:
            self.__quantity -=n
        else:
            print("Not enough stck")

p1 = product("Rice", 80, 50)
p2 = product("Sugar", 120, 40)
p3 = product("oil", 300, 25)  

p1.sell(20)

print(p1.name, p1.quantity)
print(p2.name, p2.quantity)
print(p3.name, p3.quantity)

