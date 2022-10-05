/*1-10までを数える実装
・vue-forディレクティブを使用して実装
・Number型の要素を持つ、配列numbersに数値を適用
・Vueインスタンスのdataでnumbersを定義し、数値を格納
・numbersの各要素を仮変数（エイリアス）numとして取り出し、numをliタグの内部に埋め込み表示を行う
*/
new Vue({
	el: "#app",
	data: {
		numbers: [
			1, 2, 3, 4, 5, 6, 7, 8, 9, 10
		]
	},
	template: `
	<div>
	  <ul>
	  	<li v-for="num in numbers">{{ num }}</li>
	  </ul>
	</div>
	`
})
