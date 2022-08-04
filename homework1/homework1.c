#include<stdio.h>
#include<string.h>
#include<stdlib.h>
#include<stdbool.h>

bool isValidInput(char *yen) {
	for(int i = 0; i < strlen(yen); i++)
		if(yen[i] < '0' || '9' < yen[i])
			return false;
	return true;
}

int main()
{
	int c100, c10, c1, yen;
	char strYen[256];

	do {
		printf("金額を入力してください: ");
		scanf("%s", strYen);
	} while(!isValidInput(strYen));

	yen  = atoi(strYen);
	c100 = yen / 100;
	yen  = yen % 100;
	c10  = yen / 10;
	yen  = yen % 10;
	c1   = yen / 1;

	printf("100円:%d枚\n",c100);
	printf("10円:%d枚\n",c10);
	printf("1円:%d枚\n",c1);
}

/*
【課題】
指定した金額を100円玉と10円玉と1円玉だけで、できるだけ少ない枚数で支払いたい。
金額を入力するとそれぞれの枚数を計算して表示するプログラムを作成せよ。
【考えるべき事】
・まずは１の位から、考えてみる
→合計金額（10円）が入力されたと仮定して、その数値が入力された時に正しい数値が出力されるか試す
→10/1=10枚となる○
→入力された文字を文字列として受け取り、ヌル文字まで「数字であるか」「アルファベットであるか」を区別する
【処理の流れ（１の位のみのパターン）】
1.入力値を受け取る○
2.1で割り算をする
3.割り算によって、割った数を出力
・*/