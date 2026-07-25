from abc import ABC, abstractmethod

PI = 3.14159

class Shape(ABC):

    @abstractmethod
    def area(self):
        pass

    def describe(self):
        print(f"The {self.__class__.__name__} with area {self.area():.2f}")

class Circle(Shape):

    def __init__(self, radius):
        self.radius = radius

    def area(self):
        return PI * self.radius * self.radius

class Rectangle(Shape):

    def __init__(self, width, height):
        self.width = width
        self.height = height

    def area(self):
        return self.width * self.height

class Triangle(Shape):

    def __init__(self, base, height):
        self.base = base
        self.height = height

    def area(self):
        return (self.base * self.height) / 2

def total_area(shapes):

    total = 0

    for shape in shapes:
        shape.describe()
        total += shape.area()

    return total

shape1 = Circle(4)
shape2 = Rectangle(6, 8)
shape3 = Triangle(10, 5)

shapes = [shape1, shape2, shape3]

print("\nTotal Area:", total_area(shapes))