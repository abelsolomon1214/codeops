class Book:
    def __init__(self, title, author, pages):
        self.title = title
        self.author = author
        self.pages = pages

    def describe(self):
        print(f"'{self.title}' by {self.author} has {self.pages} pages.")

book1 = Book("crime & punishment", "Feyedor Destoviski", 397 )
book2 = Book("Hadis", "Hadis Alemayehu", 350)
book3 = Book("Lelasew", "Mihretu Debebe", 390)

book1.describe()
book2.describe()
book3.describe()

