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

const deleteTasks = (deleteButton) => {
	const chosenTask = deleteButton.closest('li');//親要素と自身の要素から一致する要素をかえす
	taskList.removeChild(chosenTask);//一致した要素をremoveChildで削除する処理を書く
};

taskSubmit.addEventListener('click', evt => {//クリックされた時に、submitにもイベントを付与
	evt.preventDefault();
	const task = taskValue.value;//taskにtaskValue.valueを代入することで、addTasks関数が呼び出された時に、文字入力をリセットする
	addTasks(task);
	taskValue.value = '';
});
