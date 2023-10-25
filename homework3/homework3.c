#include <stdio.h>

void test(void);
int main(void) {
	test();
	return 0;
}

void test(void) {
	int src = 0x01234567;
	char* src_p;

	src_p = &src;
	printf("0x%x\n", src);
    printf("0x%x\n", *src_p);
    printf("0x%x\n", src_p[1]);
}


/*
<アルゴリズム>
[エンディアン判定プログラム作成]
1.intで１６進数を宣言
2.char型ポインタにintのアドレスを格納
3.charのポインタの値を表示
int型（32bit→4byte）をchar型（8bit→1byte）に分けることで1byteがどう格納されているかが見える

<エンディアン判定プログラム実装>
１.int src = 0x01234567;
←int型の１６進数を宣言
２.char* src_p;
←char型のポインタを宣言
３.src_p = &src;
←char型ポインタにint型のアドレスを格納
4.printf("0x%x\n", *src_p);
←char型ポインタの中身を表示
*/