pal1 = ((input(' ingrese palabra A: ')))
pal1 = str(pal1)
pal2 = ((input(' ingrese palabra B: ')))
pal2 = str(pal2)

pal1inicial = pal1

pal2 = list(pal2)


def comparar(a, pal2):
    s = ""
    for i in range(len(a)):
        if a[i] in pal2:
            s += str(a[i])
    return s


pal3 = comparar(pal1, pal2)

for char in pal2:
    pal1 = pal1.replace(char, "")

print(pal1inicial + " - " + pal3 + " = " + pal1)
