class NewAgency:
    def __init__(self):
        self.subscribe = []

    def subscribe(self, subscribe):
        self.subscribe.append(subscribe)

    def notify(self, news):
        for subscribe in self.subscribers:
            subscribe.update(news)

class TVChannel:
    def update(self, news):
        print("TV:", news)

class Radio:
    def update(self, news):
        print("Radio:", news)    

agency = NewAgency()
tv = TVChannel()
radio = Radio()

agency.subscribe(tv)
agency.subscribe(radio)

agency.notify("Breaking News!")

