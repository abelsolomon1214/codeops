# method 1
def apply_discount(price, percent=10):
    return price * 0.9
print(apply_discount(100))

# method 2

percent = 0.90

def apply_discount(price):
    return price * percent
print(apply_discount(100))
