def apply_discount(price, percent=10):
    discount = price * (percent/100)
    return price - discount

print(apply_discount(100))
print(apply_discount(100, 20))
