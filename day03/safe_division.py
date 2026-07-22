try:
    number = int(input("Enter a number: "))
    result = 1000/number
    print(f"Result: {result}")

except ValueError:
    print("Plase enter number only.")   
     
except ZeroDivisionError:
    print("Number can't be zero.")
