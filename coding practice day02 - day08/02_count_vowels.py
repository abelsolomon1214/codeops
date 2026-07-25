def count_vowels(text):
    count = 0
    vowels = "aeiou"
    text = text.lower()
    for letter in text:
        if letter in vowels:
            count += 1
    return count

word = input("Enter a word: ")
print("Number of vowels:", count_vowels(word))