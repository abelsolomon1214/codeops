class MinStack:

    def __init__(self):

        self.stack = []
        self.min_stack = []

    def push(self, value):

        self.stack.append(value)

        if len(self.min_stack) == 0 or value <= self.min_stack[-1]:
            self.min_stack.append(value)

    def pop(self):

        if len(self.stack) == 0:
            print("Stack is empty.")
            return None

        value = self.stack.pop()

        if value == self.min_stack[-1]:
            self.min_stack.pop()

        return value

    def top(self):

        if len(self.stack) == 0:
            print("Stack is empty.")
            return None

        return self.stack[-1]

    def get_min(self):

        if len(self.min_stack) == 0:
            print("Stack is empty.")
            return None

        return self.min_stack[-1]

ms = MinStack()

ms.push(8)
ms.push(5)
ms.push(10)
ms.push(2)
ms.push(7)

print("Top:", ms.top())
print("Minimum:", ms.get_min())
print("Removed:", ms.pop())
print("Minimum:", ms.get_min())
print("Removed:", ms.pop())
print("Minimum:", ms.get_min())