def max_water(height):

    left = 0
    right = len(height) - 1
    maximum = 0
    
    while left < right:

        width = right - left
        current_height = min(height[left], height[right])
        area = width * current_height

        if area > maximum:
            maximum = area

        if height[left] < height[right]:
            left += 1
        else:
            right -= 1

    return maximum

heights = [2, 5, 4, 8, 3, 7]
print("Maximum Water:", max_water(heights))