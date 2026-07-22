class Circle:
    def area(self):
        return 3.14 * 5 * 5
    
class Square:
    def area(self):
        return 5 * 5

class Triangle:
    def area(self):
        return 0.5 * 5 * 6

shapes = [Circle(), Square(), Triangle()]

for shape in shapes:
    print(f"Area of {shape.__class__.__name__}: {shape.area()}")