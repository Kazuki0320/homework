/*
<ローカルコンポーネントの使い方>
・new vue内のcomponentsオブジェクトで使えるコンポーネント
・ローカルコンポーネントを使うメリットは、コンポーネントの中で定義したdataやmethodsなどがその指定したVueインスタンスの中だけで有効になること（グローバルに影響しない）
let app = new Vue({
	el:'#app',
	components: {
		'コンポーネント名': {
			template: 'htmlタグ',
			data(){
				return
			},
			methods:{},
			computed:{}
		}
	}
})
*/

let app = new Vue({
	el:'#app',
	components: {
		'local-hello': {
			template: '<p>{{ name }}さん、こんにちは！</p>',
			data() {
				return {
					name:"AAA"
				}
			}
		}
	}
})