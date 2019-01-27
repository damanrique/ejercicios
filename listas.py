import random

a = []
b = []
c = []

while len(a) <= 39:
    ra = random.randint(1, 50)
    rb = random.randint(1, 50)
    a.append(ra)
    b.append(rb)
# valida los que no estan en a y b y llena la lista c

ch = 1

for ch in range(1, 50):
    if ch not in b and ch not in a:
        c.append(ch)
        ch = ch + 1

print('la lista a es:', a)
print('la lista b es:', b)
print('la lista c es:', c)
