/*1-10までを数える実装(下記は配列の要素を出力)
・vue-forディレクティブを使用して実装
・Number型の要素を持つ、配列numbersに数値を適用
・Vueインスタンスのdataでnumbersを定義し、数値を格納
・numbersの各要素を仮変数（エイリアス）numとして取り出し、numをliタグの内部に埋め込み表示を行う
*/
// new Vue({
// 	el: "#app",
// 	data: {
// 		numbers: [
// 			1, 2, 3, 4, 5, 6, 7, 8, 9, 10
// 		]
// 	},
// 	template:
// 	`<div>
// 	<ul>
// 		<li v-for="num in numbers">{{ num }}</li>
// 	</ul>
// 	</div>`
// })

/*[オブジェクトのプロパティを出力]
・v-forディレクティブはオブジェクトに対しても使用することができ、
オブジェクトに使用した場合は、オブジェクトが持つプロパティに対して繰り返し処理が行われる。
*/

// new Vue({
// 	el:"#app",
// 	data: {
// 		objectData: {id: 1, name: 'abc', value:100}
// 	},
// 	template:
// 	`<div>
// 	<ul>
// 		<li v-for="prop in objectData">{{prop}}</li>
// 	</ul>
// 	</div>`
// })

/*[配列からリストを出力]
・配列を繰り返し処理して、リストを出力
*/

// new Vue({
// 	el:"#app",
// 	data:{
// 		items:['アイテム1','アイテム2','アイテム3']
// 	},
// 	template:
// 	`<div>
// 		<ul>
// 			<li v-for="item in items">{{item}}</li>
// 		</ul>
// 	</div>`
// })

/*
[配列のテーブルを出力する]
・配列を繰り返し処理して、テーブルを出力する。
*/

// const items = [
// 	{id: 1, name: 'アイテム1'},
// 	{id: 2, name: 'アイテム2'},
// 	{id: 3, name: 'アイテム3'}
// ];

// new Vue({
// 	el:"#app",
// 	data: {
// 		items: items
// 	},
// 	template:
// 	`<div>
// 	<tr>
// 		<th>ID</th>
// 		<th>名前</th>
// 	</tr>
// 	<tr v-for="item in items">
// 		<td>{{item.id}}</td>
// 		<td>{{item.name}}</td>
// 	</tr>
// 	</div>`
// })

/*
[v-forディレクティブのkey属性の指定]
・vueではコレクションの要素を識別するためにkeyという特別な属性を参照して、
個々の要素を識別している。key属性を付与することで、vueが要素の変更前と変更後の差分を
検出できるようになり、効率の良い処理が可能になる。
・下記は、<div v-for="item in items" v-bind:key="item.id">の略
*/
const items = [
	{
		id: 1,
		name: 'アイテム1'
	},
	{
		id: 2,
		name: 'アイテム2'
	},
	{
		id: 3,
		name: 'アイテム3'
	}
];

new Vue({
	el:"#app",
	data: {
		items: items
	},
	template:
	`<div>
	<tr>
		<th>ID</th>
		<th>名前</th>
	</tr>
	<tr v-for="item in items" :key="item.id">
		<td>{{item.id}}</td>
		<td>{{item.name}}</td>
	</tr>
	</div>`
})
