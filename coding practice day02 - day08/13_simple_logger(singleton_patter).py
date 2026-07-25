class Logger:

    __instance = None
    def __new__(cls):

        if cls.__instance is None:
            cls.__instance = super().__new__(cls)
            cls.__instance.logs = []

        return cls.__instance

    def log(self, level, message):
        self.logs.append(f"[{level}] {message}")

    def get_logs(self):
        return self.logs

logger1 = Logger()
logger2 = Logger()

print(logger1 is logger2)

logger1.log("INFO", "Application started")
logger2.log("WARNING", "Low disk space")
logger1.log("ERROR", "Database connection failed")

print()

for log in logger1.get_logs():
    print(log)