const taskValue = document.getElementsByClassName('task_value')[0];
const taskSubmit = document.getElementsByClassName('task_submit')[0];
const taskList = document.getElementsByClassName('task_list')[0];

const addTasks = (task) => {
	const listItem = document.createElement('li');
	const showItem = taskList.appendChild(listItem);
	showItem.innerHTML = task;

	const deleteButton = document.createElement('button');
	deleteButton.innerHTML = 'Delete';
	listItem.appendChild(deleteButton);

	deleteButton.addEventListener('click', evt => {
		evt.preventDefault();
		deleteTasks(deleteButton);
	});
};

const deleteTasks = (deleteButton) => {
	const chosenTask = deleteButton.closest('li');
	taskList.removeChild(chosenTask);
};

taskSubmit.addEventListener('click', evt => {
	evt.preventDefault();
	const task = taskValue.value;
	addTasks(task);
	taskValue.value = '';
});
