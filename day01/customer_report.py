customers = [
 ("Abel", 1000), ("john", 1200), ("hayle", 450),
 ("selam", 70), ("rahel", 840),
]
def tier(balance):
    if balance >= 1000:
        return "Premium"
    elif balance >= 500:
        return "Standard"
    return "Basic"
for name, balance in customers:
 print(f"{name}: {tier(balance)} ({balance} ETB)")
