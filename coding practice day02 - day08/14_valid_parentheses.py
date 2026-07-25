def is_valid(s):

    stack = []
    pairs = {
        ")": "(",
        "]": "[",
        "}": "{"
    }

    for character in s:
        if character in "([{":
            stack.append(character)
        else:
            if len(stack) == 0:
                return False
            top = stack.pop()
            if top != pairs[character]:
                return False

    return len(stack) == 0

print(is_valid("()"))
print(is_valid("([])"))
print(is_valid("{[]}"))
print(is_valid("([)]"))
print(is_valid("((("))