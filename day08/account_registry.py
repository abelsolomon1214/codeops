# day08/account_registry.py
# Extends day07 with: top_by_balance(), binary_search(), recursive total_transactions()

# ── Singleton ────────────────────────────────
class BankConfig:
    __instance = None
    def __new__(cls):
        if cls.__instance is None:
            cls.__instance = super().__new__(cls)
            cls.__instance.interest_rate   = 5
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

# ── Base Account ─────────────────────────────
class Account:

    def __init__(self, owner, account_number, balance=0):
        self.owner          = owner
        self.account_number = account_number
        self.__balance      = balance
        self.subscribers    = []
        self._history       = []

    @property
    def balance(self):
        return self.__balance

    def subscribe(self, observer):
        self.subscribers.append(observer)

    def _notify(self, message):
        for obs in self.subscribers:
            obs.update(self.owner, message)

    def _set_balance(self, amount):
        self.__balance += amount

    def deposit(self, amount):
        if amount <= 0:
            print("Deposit amount must be positive.")
            return
        self.__balance += amount
        self._history.append(("deposit", amount))
        print(f"  {amount:.2f} ETB deposited. Balance: {self.__balance:.2f}")
        self._notify(f"{amount:.2f} ETB deposited.")

    def withdraw(self, amount):
        if amount <= 0:
            print("Withdrawal amount must be positive.")
            return
        if amount > self.__balance:
            print("Insufficient balance.")
            return
        self.__balance -= amount
        self._history.append(("withdraw", amount))
        print(f"  {amount:.2f} ETB withdrawn. Balance: {self.__balance:.2f}")
        self._notify(f"{amount:.2f} ETB withdrawn.")

    def undo_last(self):
        if not self._history:
            print(f"  [{self.account_number}] No transactions to undo.")
            return
        action, amount = self._history.pop()
        if action == "deposit":
            self._set_balance(-amount)
        else:
            self._set_balance(+amount)
        print(f"  [{self.account_number}] UNDO {action} of {amount:.2f} ETB."
              f" Balance now: {self.balance:.2f} ETB")
        self._notify(f"UNDO {action} of {amount:.2f} ETB.")

    def statement(self):
        print("\n------ Account ------")
        print(f"  Owner          : {self.owner}")
        print(f"  Account Number : {self.account_number}")
        print(f"  Balance        : {self.balance:.2f} ETB")
        self._show_history()

    def _show_history(self):
        if not self._history:
            print("  History        : no transactions yet")
            return
        print("  History        :")
        for i, (action, amount) in enumerate(self._history, 1):
            sign = "+" if action == "deposit" else "-"
            print(f"    {i}. {action.capitalize():<10} {sign}{amount:.2f} ETB")

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
        print(f"  Owner          : {self.owner}")
        print(f"  Account Number : {self.account_number}")
        print(f"  Interest Rate  : {self.rate}%")
        print(f"  Balance        : {self.balance:.2f} ETB")
        self._show_history()

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
        self._history.append(("withdraw", amount))
        print(f"  {amount:.2f} ETB withdrawn. Balance: {self.balance:.2f}")
        self._notify(f"{amount:.2f} ETB withdrawn.")

    def statement(self):
        print("\n------ Current Account ------")
        print(f"  Owner          : {self.owner}")
        print(f"  Account Number : {self.account_number}")
        print(f"  Overdraft      : {self.overdraft_limit:.2f} ETB")
        print(f"  Balance        : {self.balance:.2f} ETB")
        self._show_history()

# ── Factory ───────────────────────────────────
class AccountFactory:

    @staticmethod
    def create(kind, owner, number, balance=0):
        k = kind.lower()
        if k == "saving":
            return SavingAccount(owner, number, balance)
        elif k == "current":
            return CurrentAccount(owner, number, balance)
        return Account(owner, number, balance)


# ── AccountRegistry (extended) ────────────────
class AccountRegistry:

    def __init__(self):
        self._store: dict = {}   # { account_number: Account }

    # ── day07: add / find / list ──────────────
    def add(self, account: Account):
        if account.account_number in self._store:
            raise ValueError(f"Account {account.account_number} already exists.")
        self._store[account.account_number] = account
        print(f"  [Registry] Added → {account.account_number} ({account.owner})")

    def find(self, number: str) -> Account:
        acc = self._store.get(number)
        if acc is None:
            print(f"  [Registry] '{number}' not found.")
        return acc

    def list_all(self) -> list:
        return sorted(self._store.values(), key=lambda a: a.account_number)

    def display_all(self):
        print("\n====== All Accounts ======")
        for acc in self.list_all():
            acc.statement()

    # ── day08 NEW #1: Balance Leaderboard ─────
    def top_by_balance(self, n: int) -> list:
        """
        Returns top-n accounts sorted by balance descending.
        Uses sorted() with key=lambda → O(n log n)

        Example:
            top 2 accounts → [acc with 15000, acc with 10000]
        """
        ranked = sorted(
            self._store.values(),
            key=lambda acc: acc.balance,   # sort by balance
            reverse=True                   # highest first
        )
        return ranked[:n]                  # slice top n

    def display_leaderboard(self, n: int):
        print(f"\n🏆 Top {n} Accounts by Balance")
        print("─" * 35)
        for rank, acc in enumerate(self.top_by_balance(n), 1):
            print(f"  #{rank}  {acc.owner:<12} "
                  f"[{acc.account_number}]  {acc.balance:>10.2f} ETB")

    # ── day08 NEW #2: Binary Search ───────────
    def _sorted_accounts(self) -> list:
        """Returns accounts sorted by account_number (needed for binary search)."""
        return sorted(self._store.values(), key=lambda a: a.account_number)

    def binary_search(self, number: str) -> Account:
        """
        Manual binary search by account_number → O(log n)

        How it works:
            accounts sorted:  [1001, 1002, 1003, 1004, 1005]
            looking for 1004:
              mid = 1003 → too small → search RIGHT half
              mid = 1004 → FOUND ✅

        Use this when you have a large registry and need fast lookup
        on a sorted list. (find() is O(1) via dict — this shows the algorithm)
        """
        accounts = self._sorted_accounts()
        low, high = 0, len(accounts) - 1

        while low <= high:
            mid = (low + high) // 2
            mid_number = accounts[mid].account_number

            if mid_number == number:
                return accounts[mid]          # found
            elif mid_number < number:
                low = mid + 1                 # search right half
            else:
                high = mid - 1               # search left half

        return None                           # not found

    def find_by_number(self, number: str) -> Account:
        """
        Public interface for binary search with friendly output.
        """
        result = self.binary_search(number)
        if result:
            print(f"  [BinarySearch] Found → {result.owner} "
                  f"[{result.account_number}] {result.balance:.2f} ETB")
        else:
            print(f"  [BinarySearch] Account '{number}' not found.")
        return result

    # ── day08 NEW #3: Recursive Total ─────────
    def total_transactions(self, account: Account, index: int = 0) -> float:
        """
        Recursively sums all transaction AMOUNTS in an account's history.

        How recursion works here:
            history = [("deposit",500), ("withdraw",200), ("deposit",300)]

            call 0: index=0 → 500 + call(index=1)
            call 1: index=1 → 200 + call(index=2)
            call 2: index=2 → 300 + call(index=3)
            call 3: index=3 → BASE CASE (index == len) → return 0

            result: 500 + 200 + 300 = 1000

        This counts total money MOVED (deposits + withdrawals combined).
        """
        history = account._history

        # base case: reached end of history
        if index == len(history):
            return 0.0

        # recursive case: current amount + rest of history
        _, amount = history[index]
        return amount + self.total_transactions(account, index + 1)

    def display_total_transactions(self, account: Account):
        total = self.total_transactions(account)
        print(f"\n  [{account.account_number}] {account.owner} — "
              f"Total money moved: {total:.2f} ETB "
              f"across {len(account._history)} transactions")

# ─────────────────────────────────────────────
# DEMO
# ─────────────────────────────────────────────
if __name__ == "__main__":

    sms   = SMSAlert()
    audit = AuditLog()

    # create accounts
    acc1 = AccountFactory.create("saving",  "Abel",   "1001", 10_000)
    acc2 = AccountFactory.create("current", "John",   "1002",  5_000)
    acc3 = AccountFactory.create("saving",  "Mia",    "1003",  2_000)
    acc4 = AccountFactory.create("saving",  "Sara",   "1004", 15_000)
    acc5 = AccountFactory.create("current", "Daniel", "1005",  8_000)

    for acc in (acc1, acc2, acc3, acc4, acc5):
        acc.subscribe(sms)
        acc.subscribe(audit)

    # register
    registry = AccountRegistry()
    print("\n── Registering Accounts ──")
    for acc in (acc1, acc2, acc3, acc4, acc5):
        registry.add(acc)

    # transactions
    print("\n── Transactions ──")
    acc1.deposit(500)
    acc1.deposit(200)
    acc1.withdraw(300)
    acc2.withdraw(2_000)
    acc2.deposit(1_000)
    acc3.deposit(800)
    acc3.withdraw(500)
    acc4.deposit(3_000)
    acc5.withdraw(1_500)

    # ── test 1: leaderboard ───────────────────
    print("\n── Test 1: Top 3 by Balance ──")
    registry.display_leaderboard(3)

    # ── test 2: binary search ─────────────────
    print("\n── Test 2: Binary Search ──")
    registry.find_by_number("1003")    # found
    registry.find_by_number("9999")    # not found

    # ── test 3: recursive total ───────────────
    print("\n── Test 3: Recursive Total Transactions ──")
    registry.display_total_transactions(acc1)
    registry.display_total_transactions(acc2)
    registry.display_total_transactions(acc3)