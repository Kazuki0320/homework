/*button-counterと呼ばれる新しいコンポーネントを定義

・button-counterと呼ばれるコンポーネントを定義して、ボタンがクリックされる度にカウントするtemplateメソッドをつかい定義。
作成したbutton-counterをhtmlで、ボタン要素として適用。idタグには、components-demo要素を適用して、vueインスタンスで、エレメントを作成
・コンポーネントのdataは関数でなければならないため、functionで定義して、それをreturnで返すような書き方をしないと、それぞれクリックボタンを作った場合、
連動して、同時にカウントされてしまう。
・コンポーネントは再利用が可能なVueインスタンスなので、data,computed,watch,methods,ライフサイクルフックなどのnew Vueとおなじオプションを受け入れる。
*/
Vue.component('button-counter', {
	data: function() {
		return {
			count: 0
		}
	},
	template: '<button v-on:click="count++">You clicked me {{ count }} times.</button>'
})

new Vue({ el: '#components-demo' })
