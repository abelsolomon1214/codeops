from abc import ABC, abstractmethod

class NotificationChannel(ABC):

    @abstractmethod
    def send(self, message):
        pass

class EmailChannel(NotificationChannel):

    def __init__(self, email):
        self.email = email

    def send(self, message):
        print(f"Email sent to {self.email}")
        print(f"Message: {message}\n")

class SMSChannel(NotificationChannel):

    def __init__(self, phone):
        self.phone = phone

    def send(self, message):
        print(f"SMS sent to {self.phone}")
        print(f"Message:{message}\n")

class PushChannel(NotificationChannel):

    def __init__(self, device):
        self.device = device

    def send(self, message):
        print(f"Push notification sent to {self.device}")
        print(f"Message:{message}\n")

class NotificationService:

    def __init__(self):
        self.channels = []

    def add_channel(self, channel):
        self.channels.append(channel)

    def notify_all(self, message):
        for channel in self.channels:
            channel.send(message)

service = NotificationService()

service.add_channel(EmailChannel("abel@gmail.com"))
service.add_channel(SMSChannel("+251911223344"))
service.add_channel(PushChannel("Samsung Galaxy S24"))
service.notify_all("Your bank transaction was successful.")