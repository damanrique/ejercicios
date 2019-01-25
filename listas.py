import random

a=[]
b=[]
c=[]

while len(a)<=39:
    ra = random.randint(1, 50)
    rb = random.randint(1, 50)
    a.append(ra)
    b.append(rb)

if ra not in b and rb not in a:
    c.append(ra)


print(a)
print(b)
print(c)