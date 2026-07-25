def flatten(lst):

    result = []

    for item in lst:

        if isinstance(item, list):
            result.extend(flatten(item))
        else:
            result.append(item)

    return result

numbers = [10, [20, 30], [40, [50, 60]], [70]]

print(flatten(numbers))
print()

data = [[1, 2], [3, [4, [5, 6]]]]
print(flatten(data))