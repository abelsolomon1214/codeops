with open("names.txt", "w") as file:
    file.write("Abel\n")
    file.write("John\n")
    file.write("Btseti\n")
    file.write("Natan\n")
    file.write("Nardos\n")

print("Reading File\n")

with open("names.txt") as file:
    for name in file:
        print(name.strip())    