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
            self.__quantity -= n
        else:
            print("Not enough stock") 

product = product("cofee", 650, 20)

print("Current quantity:", product.quantity)
product.sell(10)
print("After selling 10:", product.quantity)

product.sell(15)
print("Final quantity:", product.quantity)
        