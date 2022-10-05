// https://jp.vuejs.org/v2/examples/todomvc.html
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
	data: {
		//使用するデータ
		//アプリケーションで使用したデータは、dataに登録していく
		//dataオプションに登録されたデータは、すべて※リアクティヴデータに変換される
	},
	methods: {
		//使用するメソッド
	}
})
/* 
リアクティブデータ:
リアクティブシステムの監視下にあるデータのことで、リアクディブシステムの仕組みを使うことにより、データなどを変更した際に即座にDOMへ反映される。
*/
