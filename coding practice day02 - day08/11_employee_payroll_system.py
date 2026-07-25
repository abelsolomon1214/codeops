from abc import ABC, abstractmethod

class Employee(ABC):

    def __init__(self, name, base_salary):

        self.name = name
        self.base_salary = base_salary

    @abstractmethod
    def pay(self):
        pass

class FullTimeEmployee(Employee):

    def pay(self):
        return self.base_salary

class PartTimeEmployee(Employee):

    def __init__(self, name, hourly_rate, hours_worked):

        self.name = name
        self.hourly_rate = hourly_rate
        self.hours_worked = hours_worked

    def pay(self):
        return self.hourly_rate * self.hours_worked

class ContractEmployee(Employee):

    def __init__(self, name, base_salary, bonus):

        super().__init__(name, base_salary)
        self.bonus = bonus

    def pay(self):
        return self.base_salary + self.bonus

def print_payroll(employees):

    print("------ Payroll ------")

    for employee in employees:
        print(f"{employee.name} : {employee.pay():.2f} ETB")

employee1 = FullTimeEmployee("Abel", 18000)
employee2 = PartTimeEmployee("Nardos", 250, 80)
employee3 = ContractEmployee("John", 12000, 3000)
employees = [employee1, employee2, employee3]

print_payroll(employees)