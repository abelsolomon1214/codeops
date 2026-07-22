class AlertService:
    def send(self, owner, message):
        print(f"SMS to {owner}: {message}")


class SMSAlert:
    def update(self, owner, message):
        print(f"SMS Alert  {owner}: {message}")


class Account:

    def __init__(self, owner, account_number, balance=0):
        self.owner = owner
        self.account_number = account_number
        self.__balance = balance
        self.subscribers = []

    @property
    def balance(self):
        return self.__balance

    # ---------- Observer ----------
    def subscribe(self, observer):
        self.subscribers.append(observer)

    def _notify(self, message):
        for observer in self.subscribers:
            observer.update(self.owner, message)

    # ------------------------------

    def deposit(self, amount):
        if amount <= 0:
            print("Deposit amount must be positive.")
        else:
            self.__balance += amount
            print(f"{amount:.2f} ETB deposited.")
            self._notify(f"{amount:.2f} ETB deposited. Balance = {self.balance:.2f}")

    def withdraw(self, amount):
        if amount <= 0:
            print("Withdrawal amount must be positive.")
        elif amount > self.__balance:
            print("Insufficient balance.")
        else:
            self.__balance -= amount
            print(f"{amount:.2f} ETB withdrawn.")
            self._notify(f"{amount:.2f} ETB withdrawn. Balance = {self.balance:.2f}")

    def statement(self):
        print("\n------ Account Statement ------")
        print("Account Type : Account")
        print(f"Owner         : {self.owner}")
        print(f"Account No    : {self.account_number}")
        print(f"Balance       : {self.balance:.2f} ETB")


class SavingAccount(Account):

    def __init__(self, owner, account_number, balance, rate):
        super().__init__(owner, account_number, balance)
        self.rate = rate

    def add_interest(self):
        interest = self.balance * self.rate / 100
        self.deposit(interest)
        print(f"Interest Added: {interest:.2f} ETB")

    def statement(self):
        print("\n------ Saving Account ------")
        print(f"Owner         : {self.owner}")
        print(f"Account No    : {self.account_number}")
        print(f"Interest Rate : {self.rate}%")
        print(f"Balance       : {self.balance:.2f} ETB")


class CurrentAccount(Account):

    def __init__(self, owner, account_number, balance, overdraft_limit):
        super().__init__(owner, account_number, balance)
        self.overdraft_limit = overdraft_limit

    def withdraw(self, amount):

        if amount <= 0:
            print("Withdrawal amount must be positive.")

        elif amount > self.balance + self.overdraft_limit:
            print("Overdraft limit exceeded.")

        else:
            self._Account__balance -= amount
            print(f"{amount:.2f} ETB withdrawn.")
            self._notify(f"{amount:.2f} ETB withdrawn. Balance = {self.balance:.2f}")

    def statement(self):
        print("\n------ Current Account ------")
        print(f"Owner              : {self.owner}")
        print(f"Account No         : {self.account_number}")
        print(f"Overdraft Limit    : {self.overdraft_limit:.2f} ETB")
        print(f"Balance            : {self.balance:.2f} ETB")


# ---------- Factory Pattern ----------

class AccountFactory:

    @staticmethod
    def create(kind, owner, account_number, balance, extra):

        if kind.lower() == "saving":
            return SavingAccount(owner, account_number, balance, extra)

        elif kind.lower() == "current":
            return CurrentAccount(owner, account_number, balance, extra)

        else:
            return Account(owner, account_number, balance)


# ---------- Main Program ----------

sms = SMSAlert()

accounts = [

    AccountFactory.create(
        "account",
        "Abel",
        "1001",
        5000,
        0
    ),

    AccountFactory.create(
        "saving",
        "Nardos",
        "1002",
        10000,
        5
    ),

    AccountFactory.create(
        "current",
        "John",
        "1003",
        2000,
        3000
    )

]

for account in accounts:
    account.subscribe(sms)

accounts[0].deposit(500)

accounts[1].add_interest()

accounts[2].withdraw(4000)

print()

for account in accounts:
    account.statement()