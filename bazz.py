def bazz (num):
    if num%3 == 0 and num%5 != 0:
        print("bazz")

    elif num%5 == 0 and num%3 !=0:
        print("buzz")

    elif num%3 == 0 and num%5 == 0:
        print("bazzbuzz")

for x in range (1, 100) :
    num = x
    print(x)
    bazz(num)
