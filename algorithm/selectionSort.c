#include <stdio.h>

int main()
{
	int data[] = {9, 8, 7, 6, 5, 4, 3, 2, 1, 0};
	int n, i, j, min, temp;

	printf("Before: ");
	for(n = 0; data[n] != '\0'; n++)
	{
		printf("%d ", data[n]);
	}

	i = 0;
	while(i < n)
	{
		min = i;
		for(j = i + 1; j < n; j++)
		{
			if(data[j] < data[min])
				min = j;
		}
		temp = data[i];
		data[i] = data[min];
		data[min] = temp;
		i += 1;
	}

	printf("\nAfter : ");
	for(n = 0; data[n] != '\0'; n++)
		printf("%d ", data[n]);
}
/*
【目的】
・大きい数値と小さい数値を比べて、数値を入れ替えて、左から大きい順に並び替える
【処理】
・data[]で、9~1の数値を宣言○
・要素番号の[0]とそれ以降の要素番号を比べて、一番小さい数と[0]の要素数を入れ替える
→i=0からn=9の間、処理を行う
→minはiになる
・入れ替えた後に、次の配列[1]から、また同じような処理を行なっていく
・入れ替えの際に、必要な処理
temp = b;
b = a;
a = b;
*/
// #include <stdio.h>

// int main(void){
//   int data[] = {9, 8, 7, 6, 5, 4, 3, 2, 1, 0};
//   int n, i, j, min, temp;

//   /* 整列前のデータを表示する */
//   printf("---selection sort---\nBefore : ");
//   for(n=0; data[n]!='\0'; n++){
//     printf("%d ",data[n]);
//   }

//   /* 選択法（選択ソート）を用いてデータを整列する */
//   i = 0;
//   while(i < n)
//   {
//     min = i;//i番目はミニマムになるので、minに代入
//     for(j=i+1; j<n; j++){
//       if(data[j]<data[min]){//data[j]は1なのでこの要素番号に入っている数値は8,data[min]は0番目の要素番号になるので、9になる
//         min = j;//jがminより、小さい時、jがミニマムになる
// 		printf("j:%d\n", j);
// 		printf("min:%d\n", min);
//       }
//     }
//     temp = data[i];
// 	printf("data[i]:%d\n", data[i]);
// 	printf("i→temp:%d\n", temp);
//     data[i] = data[min];//上記if文で、minが最終的に8で終わっており、data[min]とすることで、data[8]番目の要素数となるため、data[min]には１が入る
// 	printf("data[min]:%d\n", data[min]);
// 	printf("data[min]→data[i]:%d\n", data[i]);
//     data[min] = temp;
// 	printf("data[min]:%d\n", data[min]);
//     i += 1;
//   }

//   /* 整列後のデータを表示する */
//   printf("\nAfter  : ");
//   for(n=0; data[n]!='\0'; n++){
//     printf("%d ",data[n]);
//   }

//   return 0;
// }