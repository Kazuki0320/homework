#include<stdio.h>
#include<string.h>
#include<stdlib.h>

int isValidInput(char *strYen)
{
	for(int i = 0; strYen[i] != '\0'; i++)
		if(strYen[i] < '0' || '9' < strYen[i])
			return 0;
	return 1;
}

int main(void)
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

	printf("100円:%d枚\n", c100);
	printf("10円:%d枚\n", c10);
	printf("1円:%d枚\n", c1);
}

/*
【課題】
指定した金額を100円玉と10円玉と1円玉だけで、できるだけ少ない枚数で支払いたい。
金額を入力するとそれぞれの枚数を計算して表示するプログラムを作成せよ。
【処理の流れ】
・int型で、c100,c10,c1の方の定義を行い、入力値をchar型yenで受け取る
→受け取った値が、555の場合、始めにyen/100を行い、C100の枚数を計算
→その後、yen%100で、剰余を計算して、余った55をyenに代入
→yen/10の計算を行い、C10の枚数を計算
→同じく、yen%10で、剰余の計算を行い、余った5をyenに代入
→yenに関して、do・while文で受け取った入力値が「数字」か「それ以外か」の継続条件を書いて、判定を行う
・while文によって、数字が返ってきた場合、文字列なのでatoiを使って、文字列から整数型に変換を行う
*/