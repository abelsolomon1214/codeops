def longest_unique(s):

    seen = set()
    left = 0
    maximum = 0

    for right in range(len(s)):

        while s[right] in seen:

            seen.remove(s[left])
            left += 1
        seen.add(s[right])
        current_length = right - left + 1

        if current_length > maximum:
            maximum = current_length

    return maximum

text = "programming"
print("Length:", longest_unique(text))