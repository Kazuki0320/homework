/*
【グローバルコンポーネントとローカルコンポーネントの違い】
<グローバルコンポーネントの使い方>
・アプリ内ならどこでも使えるグローバルコンポーネント

[基本的な書き方]
Vue.component('コンポーネント名', {
	template: 'HTMLのコード',
	data: function() {
		return
	},
	methods:{
	},
	computed:{
	},
})
*/

Vue.component('hello-vue', {
	template: '<p>こんにちは！</p>',
})

/*
<コンポーネントの中で、dataオブジェクトを使用する方法>
dataオブジェクトを使用すれば、vue側に作成したデータをテンプレートで呼び出すことができる。
*dataが関数になるので、data(){return}という形になる
[基本的な書き方]
Vue.component('コンポーネント名',{
	template:'HTMLのコード'
	data() {
		return {
			プロパティ名1: 値1,
			プロパティ名2: 値2
		}
	}
})
*/

Vue.component('hello-vue1', {
	template:'<p>{{ hello }}、{{ message }}',
	data() {
		return {
			hello:"こんにちは",
			message:"初期値のメッセージになります。"
		}
	}
})

/*
<コンポーネントの中で、methodsオブジェクトを使用する>
methodオブジェクトに関しては、returnは不要

Vue.component('コンポーネント名', {
	template:'',
	data(){
		return {
			プロパティ名: 値1,
			プロパティ名: 値2
		}
	},
	methods:{
		メソッド名() {
			処理
		}
	}
})
*/

//methodを使った実例として、要素をクリックするとdataにセットした2つのプロパティを繋げて、画面上に表示する処理を書いていく。
Vue.component('methods-vue', {
	template:'<p @click="sayHi"><button>挨拶: {{ output }}</button></p>',
	data() {
		return {
			name:"AAA",
			hello:"こんにちは",
			output:""
		}
	},
	methods:{
		sayHi(){
			this.output = this.name + this.hello
		}
	}
})

let app = new Vue({
	el:'#app'
})






