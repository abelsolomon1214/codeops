def find_duplicates(numbers):
    count = {}
    duplicates = []

    for number in numbers:
        if number in count:
            count[number] += 1
        else:
            count[number] = 1
    for number in count:
        if count[number] > 1:
            duplicates.append(number)
    return duplicates

user_input = input("Enter numbers separated by spaces: ")
numbers = []

for value in user_input.split():
    numbers.append(int(value))

print("Duplicate numbers:", find_duplicates(numbers))