import time
import threading
import os

class Timer(threading.Thread):
 	def __init__(self, seconds):
 		self.runTime = seconds
 		threading.Thread.__init__(self)

class CountDownTimer(Timer):
 	def run(self):
 		counter = self.runTime
 		for sec in range(self.runTime):
 			print counter
 			time.sleep(1.0)
 			counter -= 1
 		
 		#timer finished
 		os.system(r'rundll32.exe powrprof.dll,SetSuspendState Hibernate')

minutes = int(raw_input())

c = CountDownTimer(minutes * 60)
c.start()
