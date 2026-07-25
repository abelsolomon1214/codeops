def read_numbers(filename):

    numbers = []
    try:
        file = open(filename, "r")

        for line in file:
            try:
                numbers.append(int(line.strip()))
            except ValueError:
                print(f"Warning: '{line.strip()}' is not a valid number.")

        file.close()

    except FileNotFoundError:
        print("File not found.")
        return []

    return numbers

result = read_numbers("07_scores.txt")
print(result)