class AppSettings:
    __instance = None
    def __new__(cls):
        if cls.__instance is None:
            cls.__instance = super().__new__(cls)
            cls.__instance.currency = "ETB"

        return cls.__instance

settings1 = AppSettings()
settings2 = AppSettings()   

print(settings1.currency)
print(settings1 is settings2)        