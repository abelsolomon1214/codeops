class Report:
    def build(self):
        print("Building report...")

class ReportSaver:
    def save(self):
        print("Saving report...")

class EmailService:
    def email(self):
        print("Sending report by email...")

report = Report()
saver = ReportSaver()                        
email = EmailService()

report.build()
saver.save()
email.email()