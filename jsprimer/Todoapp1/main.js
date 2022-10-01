const taskValue = document.getElementsByClassName('task_value')[0];
const taskSubmit = document.getElementsByClassName('task_submit')[0];
const taskList = document.getElementsByClassName('task_list')[0];

const addTasks = (task) => {
	const listItem = document.createElement('li');//createElementで引数の要素を作成
	const showItem = taskList.appendChild(listItem);//appendChildで、親要素に子要素を追加する
	showItem.innerHTML = task;//入力したタスクを表示する

	const deleteButton = document.createElement('button');
	deleteButton.innerHTML = 'Delete';//createElementで作成したbuttonの表示を'Delete'とする
	listItem.appendChild(deleteButton);//listItemに子要素として、deleteButtonを追加

	deleteButton.addEventListener('click', evt => {//deleteButtonが呼び出された時に、イベントが発火するようにする
		evt.preventDefault();
		deleteTasks(deleteButton);//deleteTasksに,イベントの処理を書いていく
	});
};

// // 削除ボタンにタスクを消す機能を付与
// const deleteTasks = (deleteButton) => {
//   const chosenTask = deleteButton.closest('li');
//   taskList.removeChild(chosenTask);
// };

// // 追加ボタンをクリックし、イベントを発動（タスクが追加）
// taskSubmit.addEventListener('click', evt => {
//   evt.preventDefault();
//   const task = taskValue.value;
//   addTasks(task);
//   taskValue.value = '';
// });