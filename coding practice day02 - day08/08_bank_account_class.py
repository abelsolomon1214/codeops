class BankAccount:

    def __init__(self, owner, initial_balance=0):

        self.owner = owner
        self._balance = initial_balance

    @property
    def balance(self):
        return self._balance

    def deposit(self, amount):

        if amount <= 0:
            raise ValueError("Deposit amount must be greater than zero.")

        self._balance += amount

    def withdraw(self, amount):

        if amount <= 0:
            raise ValueError("Withdrawal amount must be greater than zero.")

        if amount > self._balance:
            raise ValueError("Insufficient funds.")

        self._balance -= amount

    def __str__(self):

        return f"BankAccount(owner={self.owner}, balance={self._balance})"

account = BankAccount("Abel", 8000)

print(account)

account.deposit(2500)
print(account)

account.withdraw(3000)
print(account)

try:
    account.withdraw(10000)

except ValueError as error:
    print(error)