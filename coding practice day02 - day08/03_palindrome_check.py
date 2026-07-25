def is_palindrome(s):
    s = s.lower()
    s = s.replace(" ", "")
    if s == s[::-1]:
        return True
    else:
        return False

print(is_palindrome("racecar"))
print(is_palindrome("A man a plan a canal Panama"))
print(is_palindrome("hello"))