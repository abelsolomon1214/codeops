grocery_items = {
    "Bread" : 12,
    "Engera" : 35,
    "Oil" : 2500,
    "Tomato" : 120,
    "Egg" : 25
}

print("Price Report")

for item,price in grocery_items.items():
    print(f"{item} : {price} ETB")
