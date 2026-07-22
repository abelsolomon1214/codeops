class product:
    def __init__(self, name, price, quantity):
        self.name = name
        self.price = price
        self.quantity = quantity

    def restock(self, n):
        self.quantity += n

    def sell(self, n):
        self.quantity -= n

product = product("sugar", 120, 50)

print(f"Product quantity: {product.quantity}")

product.sell(10)
print(f"Product sell: {product.quantity}")

product.restock(20)
print(f"Product restock: {product.quantity}")

