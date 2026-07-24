class BankConfig:
    __instance = None

    def __new__(cls):
        if cls.__instance is None:
            cls.__instance = super().__new__(cls)
            cls.__instance.interest_rate  = 5
            cls.__instance.overdraft_limit = 3000
        return cls.__instance


config = BankConfig()


# ── Observers ────────────────────────────────
class SMSAlert:
    def update(self, owner, message):
        print(f"SMS Alert  -> {owner}: {message}")


class AuditLog:
    def update(self, owner, message):
        print(f"Audit Log  -> {owner}: {message}")


# ── Base Account (history stack added) ───────
class Account:

    def __init__(self, owner, account_number, balance=0):
        self.owner          = owner
        self.account_number = account_number
        self.__balance      = balance
        self.subscribers    = []
        self._history       = []          # ← stack (list used as stack)

    # ── balance property ──────────────────────
    @property
    def balance(self):
        return self.__balance

    # ── observer helpers ──────────────────────
    def subscribe(self, observer):
        self.subscribers.append(observer)

    def _notify(self, message):
        for observer in self.subscribers:
            observer.update(self.owner, message)

    # ── internal balance mutator (used by undo) ──
    def _apply_balance(self, amount):
        """Directly adjust balance without writing to history (used by undo)."""
        self.__balance += amount

    # ── deposit / withdraw ────────────────────
    def deposit(self, amount):
        if amount <= 0:
            print("Deposit amount must be positive.")
            return
        self.__balance += amount
        self._history.append(("deposit", amount))          # push ← NEW
        print(f"{amount:.2f} ETB deposited.")
        self._notify(f"{amount:.2f} ETB deposited.")

    def withdraw(self, amount):
        if amount <= 0:
            print("Withdrawal amount must be positive.")
            return
        if amount > self.__balance:
            print("Insufficient balance.")
            return
        self.__balance -= amount
        self._history.append(("withdraw", amount))         # push ← NEW
        print(f"{amount:.2f} ETB withdrawn.")
        self._notify(f"{amount:.2f} ETB withdrawn.")

    # ── undo last transaction ─────────────────  ← NEW
    def undo_last(self):
        if not self._history:
            print(f"[{self.account_number}] No transactions to undo.")
            return

        action, amount = self._history.pop()               # pop

        if action == "deposit":
            self._apply_balance(-amount)                   # reverse deposit
            msg = f"Undo deposit of {amount:.2f} ETB."
        else:  # withdraw
            self._apply_balance(+amount)                   # reverse withdraw
            msg = f"Undo withdrawal of {amount:.2f} ETB."

        print(f"[{self.account_number}] {msg} Balance now: {self.balance:.2f} ETB")
        self._notify(msg)

    # ── statement ─────────────────────────────
    def statement(self):
        print("\n------ Account ------")
        print(f"Owner          : {self.owner}")
        print(f"Account Number : {self.account_number}")
        print(f"Balance        : {self.balance:.2f} ETB")
        self._print_history()

    def _print_history(self):
        if not self._history:
            print("History        : (no transactions)")
            return
        print("History        :")
        for i, (action, amount) in enumerate(self._history, 1):
            sign = "+" if action == "deposit" else "-"
            print(f"  {i}. {action.capitalize():10} {sign}{amount:.2f} ETB")


# ── SavingAccount ─────────────────────────────
class SavingAccount(Account):

    def __init__(self, owner, account_number, balance=0):
        super().__init__(owner, account_number, balance)
        self.rate = config.interest_rate

    def add_interest(self):
        interest = self.balance * self.rate / 100
        self.deposit(interest)

    def statement(self):
        print("\n------ Saving Account ------")
        print(f"Owner          : {self.owner}")
        print(f"Account Number : {self.account_number}")
        print(f"Interest Rate  : {self.rate}%")
        print(f"Balance        : {self.balance:.2f} ETB")
        self._print_history()


# ── CurrentAccount ────────────────────────────
class CurrentAccount(Account):

    def __init__(self, owner, account_number, balance=0):
        super().__init__(owner, account_number, balance)
        self.overdraft_limit = config.overdraft_limit

    def withdraw(self, amount):
        if amount <= 0:
            print("Withdrawal amount must be positive.")
            return
        if amount > self.balance + self.overdraft_limit:
            print("Overdraft limit exceeded.")
            return
        self._Account__balance -= amount
        self._history.append(("withdraw", amount))         # push ← NEW
        print(f"{amount:.2f} ETB withdrawn.")
        self._notify(f"{amount:.2f} ETB withdrawn.")

    def statement(self):
        print("\n------ Current Account ------")
        print(f"Owner          : {self.owner}")
        print(f"Account Number : {self.account_number}")
        print(f"Overdraft      : {self.overdraft_limit:.2f} ETB")
        print(f"Balance        : {self.balance:.2f} ETB")
        self._print_history()


# ── Factory ───────────────────────────────────
class AccountFactory:

    @staticmethod
    def create(kind, owner, number, balance=0):
        kind = kind.lower()
        if kind == "saving":
            return SavingAccount(owner, number, balance)
        elif kind == "current":
            return CurrentAccount(owner, number, balance)
        else:
            return Account(owner, number, balance)


# ── Registry (NEW – day07) ────────────────────
class AccountRegistry:
    """
    Stores accounts in a dict keyed by account_number → O(1) add / find.
    list_all() returns accounts sorted by account_number (insertion order
    is fine too; here we sort for a predictable display).
    """

    def __init__(self):
        self._accounts: dict = {}          # { account_number: Account }

    def add(self, account: Account) -> None:
        """Register an account.  Raises if duplicate number."""
        if account.account_number in self._accounts:
            raise ValueError(
                f"Account {account.account_number} already registered."
            )
        self._accounts[account.account_number] = account
        print(f"[Registry] Account {account.account_number} added.")

    def find(self, account_number: str) -> Account:
        """O(1) lookup by account number.  Returns None if not found."""
        account = self._accounts.get(account_number)
        if account is None:
            print(f"[Registry] Account {account_number} not found.")
        return account

    def list_all(self) -> list:
        """Return all accounts sorted by account_number."""
        return sorted(self._accounts.values(),
                      key=lambda a: a.account_number)

    def display_all(self) -> None:
        print("\n====== Registry: All Accounts ======")
        for account in self.list_all():
            account.statement()


# ─────────────────────────────────────────────
# DEMO
# ─────────────────────────────────────────────
if __name__ == "__main__":

    sms   = SMSAlert()
    audit = AuditLog()

    # ── create via factory ────────────────────
    account1 = AccountFactory.create("saving",  "Abel", "1001", 10_000)
    account2 = AccountFactory.create("current", "John", "1002",  5_000)
    account3 = AccountFactory.create("saving",  "Mia",  "1003",  2_000)

    # ── wire observers ────────────────────────
    for acc in (account1, account2, account3):
        acc.subscribe(sms)
        acc.subscribe(audit)

    # ── register ──────────────────────────────
    registry = AccountRegistry()
    registry.add(account1)
    registry.add(account2)
    registry.add(account3)

    # ── transactions (builds history) ─────────
    print("\n── Transactions ──")
    account1.deposit(500)
    account1.deposit(200)
    account1.withdraw(300)

    account2.withdraw(7_000)   # uses overdraft
    account2.deposit(1_000)

    # ── undo demo ─────────────────────────────
    print("\n── Undo Last ──")
    account1.undo_last()       # undoes withdraw 300
    account2.undo_last()       # undoes deposit 1000
    account2.undo_last()       # undoes withdraw 7000
    account2.undo_last()       # nothing left → message

    # ── O(1) find demo ────────────────────────
    print("\n── Find by Number ──")
    found = registry.find("1001")
    if found:
        print(f"Found: {found.owner} — balance {found.balance:.2f} ETB")

    registry.find("9999")      # not found

    # ── list all (sorted) ─────────────────────
    registry.display_all()