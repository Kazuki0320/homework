#include<stdio.h>
#include<stdbool.h>
#include<string.h>
#include<stdlib.h>

void get_triangle()
{
	int n = 5;

	for(int i = 1;i < n;i++)
	{
		for(int j = 1;j <= n - i;j++)
		{
			printf(" ");
		}
		for(int j = 1;j <= i * 2 - 1;j++)
		{
			printf("#");
		}
		printf("\n");
	}
}

void get_rectangle()
{
	int n = 3;

		for(int i = 1;i <= n;i++)
		{
			for(int j = 1;j <= n;j++)
			{
				printf("#");
			}
			printf("\n");
		}
}

bool judgeTrueOrFalse(char str[10])
{
	for(int i = 0;i < strlen(str); i++)
	{
		if('1' <= str[i] && str[i] <= '2')continue;
		return false;
	}
	return true;
}

int main()
{
	char str[10];

	do
	{
		printf("表記に従って、1or2を入力してください。\n三角形の場合:1\n四角形の場合:2\n数値を入力してください: ");
		scanf("%s", str);
	}
	while(judgeTrueOrFalse(str) == false);

	int OneOrTwo = atoi(str);

		if(OneOrTwo == 1)
		{
			get_triangle();
		}
		else if(OneOrTwo == 2)
		{
			get_rectangle();
		}

}

/*
【課題】
・標準入力の値で、三角形か四角形かを選べる
・期待してない値が入力されたら、再度入力してもらう
<考えるべきこと>
・標準入力で、三角形か四角形か選べる○
・三角形の処理○
 →三角形を出力するには、空白も必要となる○
・四角形の処理を書く○
→２×２から、表示が可能○
・char型で文字を受け取る
→数字・文字を入力した場合、プログラムが終了している
→これを再入力させたい
・while文の中で、関数を呼び出し、その関数がfalseの判定の間はscanfに戻るようにする

<順序>
1.三角形を形成
→３×３のパターンで考えて、１行目は空白をまずは２つハッシュ１を出力する
例）「○○#」
→１行目に空白２つハッシュ１つ空白２つ
例）「○○#○○」
→右側は出力する必要がない
→なぜなら、行数ごとに空白はどんどん出力量が減るので、右側を調整しなくても、段ができるようになる
2.四角形を形成
3.それらを選べるように入力を考える
¬Aを選んだら、三角形。Bを選んだら、四角形となる処理を書く。
つまり、if文を活用する
4.例外処理を行う。
→・期待してない値→負の数、文字などの除外処理

<いっちいの処理から、気づいたこと>
・期待する数字以外の数字と文字が入力されたときは、再度scanfで入力されるような形にする必要がある
→1と2以外の値が入力されたときにtrueとして、それ以外の数字が入力されたときにfalseとする処理を書けばよかった
→while文の中で、収めるのではなく、関数の呼び出しを使って、別の関数の中に判定処理を書けばよかった
・文字列で、入力値を受け取って、それをatoiで、文字列型からint型へ変換することで、1と2を数値として比較することができ、
結果として当初の予定通り、1と2の入力から三角形と四角形を選べるようになる。

*/
