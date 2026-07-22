class BankConfig:

    __instance = None
    def __new__(cls):

        if cls.__instance is None:
            cls.__instance = super().__new__(cls)
            cls.__instance.interest_rate = 5
            cls.__instance.overdraft_limit = 3000
        return cls.__instance
    
config = BankConfig()

class SMSAlert:

    def update(self, owner, message):
        print(f"SMS Alert -> {owner}: {message}")

class AuditLog:

    def update(self, owner, message):
        print(f"Audit Log -> {owner}: {message}")

class Account:

    def __init__(self, owner, account_number, balance=0):

        self.owner = owner
        self.account_number = account_number
        self.__balance = balance
        self.subscribers = []

    @property
    def balance(self):
        return self.__balance

    def subscribe(self, observer):
        self.subscribers.append(observer)

    def _notify(self, message):

        for observer in self.subscribers:
            observer.update(self.owner, message)

    def deposit(self, amount):

        if amount <= 0:
            print("Deposit amount must be positive.")
        else:
            self.__balance += amount
            print(f"{amount:.2f} ETB deposited.")
            self._notify(f"{amount:.2f} ETB deposited.")

    def withdraw(self, amount):

        if amount <= 0:
            print("Withdrawal amount must be positive.")
        elif amount > self.__balance:
            print("Insufficient balance.")
        else:
            self.__balance -= amount
            print(f"{amount:.2f} ETB withdrawn.")
            self._notify(f"{amount:.2f} ETB withdrawn.")

    def statement(self):

        print("\n------ Account ------")
        print(f"Owner : {self.owner}")
        print(f"Account Number : {self.account_number}")
        print(f"Balance : {self.balance:.2f} ETB")

class SavingAccount(Account):

    def __init__(self, owner, account_number, balance=0):
        super().__init__(owner, account_number, balance)
        self.rate = config.interest_rate

    def add_interest(self):
        interest = self.balance * self.rate / 100
        self.deposit(interest)

    def statement(self):

        print("\n------ Saving Account ------")
        print(f"Owner : {self.owner}")
        print(f"Account Number : {self.account_number}")
        print(f"Interest Rate : {self.rate}%")
        print(f"Balance : {self.balance:.2f} ETB")

class CurrentAccount(Account):

    def __init__(self, owner, account_number, balance=0):

        super().__init__(owner, account_number, balance)
        self.overdraft_limit = config.overdraft_limit

    def withdraw(self, amount):

        if amount <= 0:
            print("Withdrawal amount must be positive.")
        elif amount > self.balance + self.overdraft_limit:
            print("Overdraft limit exceeded.")
        else:
            self._Account__balance -= amount
            print(f"{amount:.2f} ETB withdrawn.")
            self._notify(f"{amount:.2f} ETB withdrawn.")

    def statement(self):

        print("\n------ Current Account ------")
        print(f"Owner : {self.owner}")
        print(f"Account Number : {self.account_number}")
        print(f"Overdraft : {self.overdraft_limit}")
        print(f"Balance : {self.balance:.2f} ETB")

class AccountFactory:

    @staticmethod
    def create(kind, owner, number, balance=0):

        if kind.lower() == "saving":
            return SavingAccount(owner, number, balance)
        elif kind.lower() == "current":
            return CurrentAccount(owner, number, balance)
        else:
            return Account(owner, number, balance)

sms = SMSAlert()

audit = AuditLog()

account1 = AccountFactory.create(
    "saving",
    "Abel",
    "1001",
    10000
)
account2 = AccountFactory.create(
    "current",
    "John",
    "1002",
    5000
)

account1.subscribe(sms)
account1.subscribe(audit)

account2.subscribe(sms)
account2.subscribe(audit)

account1.deposit(500)
account2.withdraw(7000)

account1.statement()
account2.statement()