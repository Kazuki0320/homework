/*
computed:キャッシュされる
→算出プロパティのキャッシュの再構築のトリガーとなるのは、リアクティブなデータのみ。
→Math.random() はリアクティブではないので、複数回呼んでもキャッシュされたデータが使われて同じ値が返される。
methods:キャッシュされない
*/
var app = new Vue ({
	el: '#app',
	computed: {
		computedNumber: function() {
			console.log('computed!');

			return Math.random()
		}
	},

	methods: {
		methodsNumber: function (){
			console.log('methods!');

			return Math.random()
		}
	}
})