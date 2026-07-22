customers = [
 ("Abel", 1300), ("Btseti", 300), ("Nardos", 80),
 ("Natan", 50000), ("John", 740),
]
def tier(balance):
    if balance >= 1000:
        return "Premium"
    elif balance >= 500:
        return "Standard"
    else:
        return "Basic"

premium_count = 0
standard_count = 0
basic_count = 0

print("Telebirr Customer Report")
print("-" * 30)

for name, balance in customers:
    customer_tier = tier(balance)
    print(f"{name}: {customer_tier} ({balance} ETB)")

    if customer_tier == "premium":
        premium_count += 1
    elif customer_tier == "standard":
        standard += 1
    else:
        basic_count += 1

print("Summary")
print("-" * 35)
print(f"Premium Customer: {premium_count}")
print(f"Standard Customer: {standard_count}")
print(f"Basic Customer: {basic_count}")
