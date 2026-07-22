stock = {}

try:
    with open("stock.txt") as file:
        for line in file:
            item, qty = line.strip().split(",")
            stock[item] = int(qty)

except FileNotFoundError:
    print("No stck file found.")

def adjest(item, amount):
    stock[item] = stock.get(item, 0) + amount
adjest("paraceptamol", 10)
adjest("vitamin", -5)
adjest("gloves", 15)

print("current Stock")
for items, qty in stock.items():
    print(f"{items} : {qty}")
    
print("\nLow Stock")

low = [item for item, qty in stock.items() if qty < 10]
for item in low:
    print(item)

with open("stock.txt", "w") as file:
    for item,qty in stock.items():
        file.write(f"{item}, {qty}\n")
print("\nStck saved Successfully.")            
