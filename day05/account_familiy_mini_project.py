class Account:
    def __init__(self, owner, account_number, balance=0):
        self.owner = owner
        self.account_number = account_number
        self.__balance = balance

    @property
    def balance(self):
        return self.__balance

    def deposite(self, amount):
        if amount <= 0: 
            print("Deposite amount be positive")
        else:
            self.__balance += amount
            print(f"{amount:.2f} ETP deposited.")

    def withdraw(self, amount):
        if amount <= 0:
            print("Withdraw amount must be positive.")
        elif amount > self.__balance:
            print("Insufficient balance.")
        else:
            self.__balance -= amount
            print(f"{amount:.2f} ETP withdrawn.")

    def statement(self):
        print("\n------Account Statment-------")
        print(f"Account Type     : Account")
        print(f"Owner            : {self.owner}")
        print(f"Account Number   : {self.account_number}")
        print(f"Balance          : {self.balance:.2f} ETP")

class SavingAccount(Account):
    def __init__(self, owner, account_number, balance, rate):
        super().__init__(owner, account_number, balance)
        self.rate = rate

    def add_interest(self):
        interest = self.balance * self.rate / 100
        self.deposite(interest)
        print(f"Interest added: {interest:.2f} ETB")

    def statement(self):
        print("\n------Saving Account------")
        print(f"Account Type     : Account")
        print(f"Owner            : {self.owner}")
        print(f"Account Number   : {self.account_number}")
        print(f"Interest Rate    : {self.rate}%")
        print(f"Balance          : {self.balance:.2f} ETP")


class CurrentAccount(Account):

    def __init__(self, owner, account_number, balance, overdraft_limit):
        super().__init__(owner, account_number, balance)
        self.overdraft_limit = overdraft_limit

    def withdraw(self, amount):
        if amount <= 0:
            print("Withdraw amount must be positive")
        elif amount > self.balance + self.overdraft_limit:
            print("Overdraft limit exceeded")
        else:
            self._Account__balance -= amount
            print(f"{amount:.2f} ETB withdrawn")

    def statement(self):
        print("\n------Current Account------")
        print(f"Account Type     : Current Account")
        print(f"Owner            : {self.owner}")
        print(f"Account Number   : {self.account_number}")
        print(f"Overdraft Limit   : {self.overdraft_limit:.2f} ETB")
        print(f"Balance          : {self.balance:.2f} ETB")

accounts = [Account("abel", "1001", 5000),
           SavingAccount("Nardos","1002",10000000,5),
           CurrentAccount("John","1003",2000,3000)]

accounts[1].add_interest()

for account in accounts:
    account.statement()
                      
