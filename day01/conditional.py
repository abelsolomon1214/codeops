balance = int(input("enter: "))  # ETB
if balance >= 1000:
    print("Premium customer")
elif balance >= 500:
    print("Standard customer")
else:
    print("Basic customer")
# Prints: Premium customer
