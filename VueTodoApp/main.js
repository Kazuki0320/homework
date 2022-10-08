// https://jp.vuejs.org/v2/examples/todomvc.html
//Vue.js 公式サンプル「TodoMVC の例」のコードを使用
var STORAGE_KEY = 'todos-vuejs-demo'
var todoStorage = {
  fetch: function() {
    var todos = JSON.parse(
      localStorage.getItem(STORAGE_KEY) || '[]'
    )
    todos.forEach(function(todo, index) {
      todo.id = index
    })
    todoStorage.uid = todos.length
    return todos
  },
  save: function(todos) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(todos))
  }
}

//アプリケーションを紐づける#appを作成する
const app = new Vue({
	el: '#app',
	//算出プロパティは、データから別の新しいデータを作成する関数型のデータ
	//定義方法は、computed オプションに加工したデータを返すメソッドを登録します。 算出プロパティは、元になったデータに変更があるまで、結果をキャッシュするという性質を持っている
	computed: {
		computedTodos: function() {
		  // データ current が -1 ならすべて
		  // それ以外なら current と state が一致するものだけに絞り込む
		  return this.todos.filter(function(el) {
			return this.current < 0 ? true : this.current === el.state
		  }, this)
		},
		labels() {
			  return this.options.reduce(function(a, b) {
				return Object.assign(a, { [b.value]: b.label })
			  }, {})
			  // キーから見つけやすいように、次のように加工したデータを作成
			  // {0: '作業中', 1: '完了', -1: 'すべて'}
			}
		}
	},
	created() {
		/* インスタンス作成時に自動的に、ローカルストレージに保存されているデータを「自動的」に取得して、Vue.jsのデータとして読み込む
		データの取得には、先に作っておいたtodoStorageオブジェクトのfetchメソッドを使用
		ローカルストレージは Ajax と違い同期的に結果を取得できるため、返り値を代入すればいいだけなので簡単
		*/this.todos = todoStorage.fetch()
	  },
	/*watchオプションのウォッチャ機能を使うことで、ストレージへ自動保存することができる
		ウォッチャはデータの変化に対応して、あらかじめ登録していた処理を自動で行う。
		これで、doAppに変化があれば、自動的にストレージが変更される*/
	watch: {
		// オプションを使う場合はオブジェクト形式にする
		todos: {
		 // 引数はウォッチしているプロパティの変更後の値
		 handler: function(todos) {
			todoStorage.save(todos)
		},
		// deep オプションでネストしているデータも監視できる
		deep: true
		}
	},
	data: {
		//使用するデータ
		todos: [],
		//特定の作業状態のリストのみを表示させる「絞り込み機能」を追加
		//スローガンテキストの下にラジオボタンをリストで表示。 ToDo リストと同じように動的に作成するため、選択肢の options リストを作成
		options: [
			{ value: -1, label: 'すべて' },
			{ value: 0,  label: '作業中' },
			{ value: 1,  label: '完了' }
		  ],
		  // 選択している options の value を記憶するためのデータ
		  // 初期値を「-1」つまり「すべて」にする
		  current: -1
		//データがない時でも、配列として認識されるために宣言をするのと、dataオプション直下のデータは後から追加ができないので、初期化で宣言する必要がある
		//アプリケーションで使用したデータは、dataに登録していく
		//dataオプションに登録されたデータは、すべて※リアクティヴデータに変換される
	},
	methods: {
		//使用するメソッド
		/*このメソッドは、フォームの入力値を取得して新しいTodoの追加処理を行う。
		ルートコンストラクタのmethodsオプションにメソッドを登録する*/
	    // ToDo 追加の処理_
	    doAdd: function(event, value) {
		// ref で名前を付けておいた要素を参照
		//ref属性で名前をつけたタグをメソッド内から呼び出せるようになる
		var comment = this.$refs.comment
		// 入力がなければ何もしないで return
		if (!comment.value.length) {
		  return
		}
		// { 新しいID, コメント, 作業状態 }
		// というオブジェクトを現在の todos リストへ push
		// 作業状態「state」はデフォルト「作業中=0」で作成
		this.todos.push({
		  id: todoStorage.uid++,
		  comment: comment.value,
		  state: 0
		})
		// フォーム要素を空にする
		comment.value = ''
	  },
	  // 状態変更の処理
	  //item.state の値を反転
		doChangeState: function(item) {
			item.state = item.state ? 0 : 1
		  },
		  // 削除の処理
		  //インデックスを取得して配列メソッドの splice を使って削除
		  //どちらも引数として要素の参照を渡している
		  doRemove: function(item) {
			var index = this.todos.indexOf(item)
			this.todos.splice(index, 1)
		  }
  })


/* 
==============【データ構想】==============
・ToDo のリストデータ
→要素の固有ID
→コメント
→今の状態
・作業中・完了・すべて などオプションラベルで使用する名称リスト
・現在絞り込みしている作業状態
==========================================
リアクティブデータ:
リアクティブシステムの監視下にあるデータのことで、リアクディブシステムの仕組みを使うことにより、データなどを変更した際に即座にDOMへ反映される。
*/
