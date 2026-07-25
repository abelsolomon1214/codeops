def two_sum(nums, target):
    seen = {}

    for index in range(len(nums)):
        current = nums[index]
        needed = target - current

        if needed in seen:
            return [seen[needed], index]

        seen[current] = index

numbers = [8, 1, 6, 4, 9]
target = 10
print("indices =",two_sum(numbers, target))