class Student:

    def __init__(self, name):

        self.name = name
        self.grades = {}

    def add_grade(self, subject, score):

        self.grades[subject] = score

    def average(self):

        if len(self.grades) == 0:
            return 0.0

        total = sum(self.grades.values())

        return total / len(self.grades)

    def highest(self):

        if len(self.grades) == 0:
            return None

        return max(self.grades.items(), key=lambda item: item[1])

    def __repr__(self):

        return f"Student(name='{self.name}', grades={len(self.grades)})"

student = Student("Nardos")

student.add_grade("Python", 95)
student.add_grade("Database", 88)
student.add_grade("Networking", 91)
student.add_grade("Linux", 84)

print(student)
print("Average:", student.average())
print("Highest:", student.highest())