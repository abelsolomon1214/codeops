class Account:
    def __init__(self, owner, account_number, code, balance=0):
        self.owner = owner
        self.account_number = account_number
        self.code = code
        self.__balance = balance

    @property
    def balance(self):
        return self.__balance

    def deposit(self, amount):
        if amount <= 0:
            print("Invalid amount. Deposit must be greater than 0.")
        else:
            self.__balance += amount
            print(f"{amount:.2f} ETB deposited successfully.")

    def withdraw(self, amount):
        if amount <= 0:
            print("Invalid amount. Withdrawal must be greater than 0.")
        elif amount > self.__balance:
            print("Insufficient balance. Withdrawal rejected.")
        else:
            self.__balance -= amount
            print(f"{amount:.2f} ETB withdrawn successfully.")

    def statement(self):
        print("\n====== Account Statement ======")
        print(f"Owner          : {self.owner}")
        print(f"Account Number : {self.account_number}")
        print(f"Balance        : {self.__balance:.2f} ETB")
        print("===============================")


# -------- Bank Database (Already Existing Account) --------

account = Account(
    owner="Abel Solomon",
    account_number="100001",
    code="1234",
    balance=5000
)


# -------- Login --------

print("====== Addis Bank Login ======")

while True:
    entered_account = input("Enter account number: ")
    entered_code = input("Enter account code: ")

    if entered_account == account.account_number and entered_code == account.code:
        print("Login successful!")
        break
    else:
        print("Wrong account number or code. Try again.")


# -------- Account Menu --------

while True:
    print("\n====== Addis Bank ======")
    print("1. Deposit")
    print("2. Withdraw")
    print("3. View Statement")
    print("4. Exit")

    choice = input("Choose option (1-4): ")

    if choice == "1":
        amount = float(input("Enter deposit amount (ETB): "))
        account.deposit(amount)

    elif choice == "2":
        amount = float(input("Enter withdrawal amount (ETB): "))
        account.withdraw(amount)

    elif choice == "3":
        account.statement()

    elif choice == "4":
        print("Thank you for using Addis Bank.")
        break

    else:
        print("Invalid option. Choose between 1-4.")