/*
[sort関数]
・a,bの引数で、numbers配列で指定している要素をそれぞれ要素1,要素2として、a-bを行う。
例えば、1-5=-4となり、返り値は0未満となるので、この場合は入れ替わりがされない。
10と3の場合、10-3=7となり、0より返り値が大きいので、入れ替わる。
*/
const numbers = [1, 5, 10 ,3];
numbers.sort(function (a, b) {
	return a - b;
})

console.log(numbers);//結果[1, 3, 5, 10]

/*
[アロー関数]
・アロー関数を使うと、上記処理と同じ結果を返しつつ、コードの量を減らすことができる。
*/
const numbers1 = [1, 5, 10, 3];
numbers.sort((a, b) => a - b);
console.log(numbers1);//結果[1, 3, 5, 10]