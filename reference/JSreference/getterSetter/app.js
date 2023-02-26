/*
[getterとsetter]
*/
//まず初めにgetterとsetterを利用しない方法で、処理を書いていく。
//フルネームを表示させる場合
//①変数の結合を利用する方法
let user = {
	firstName: "John",
	lastName: "Doe",
};

console.log(user.firstName + " " + user.lastName);
console.log(`${user.firstName} ${user.lastName}`); // ES6から利用できるテンプレートリテラル今後はこちらを利用
//結果
//John Doe
//John Doe

//②メソッドを利用した方法
/*
オブジェクトにfullNameメソッドを追加することで、追加したメソッドを実行したい場合はuser.fullNameのカッコをつける必要がある。
*/
let user1 = {
	firstName: "John",
	lastName: "Doe",
	fullName: function () {
		return `${this.firstName} ${this.lastName}`;
	},
};

console.log(user.fullName());//←このかっこがgetterの場合、いらない
//結果
// John Doe

//[getter]
/*
メソッドの場合と異なり実行する際はカッコが必要でなくfirstNameやlastNameのプロパティにアクセスする場合と同じように記述を行うことができる。
このようにGetterを利用することでfirstNameとlastNameを結合し、プロパティのように扱うことができるようになる。
*/
let user2 = {
	firstName: "John",
	lastName: "Doe",

	get fullName() {
		return `${this.firstName} ${this.lastName}`;
	},
};

console.log(user.fullName);
//結果
// John Doe

//↓.(ドット)ではなく[]カッコを利用してもアクセスすることが可能。
console.log(user["fullName"]);
//結果
// John Doe

/*[setter]
今回はSetterを利用して値を設定するのにフルーネームを利用。
フルーネームはファーストネームとラストネームの間に空白を挟んでいるので、
空白を挟んでいるフルネームの値をファーストネームとラストネームの2つに分ける方法を理解しておく必要がある。
setではfullNameに引数valueを設定し、splitメソッドで文字列を空白で分割し、分割代入でfirstNameとlastNameを設定している。この時thisをつける必要がある。
※getとsetで同じfullNameを利用しているが、”=”(イコール)で値を設定する場合はsetterが利用され、値を取得する場合はgetterが利用される。
*/
//[splitで、分けた名前を分割代入を使って取得]
// fullName = "John Doe";
// [firstName, lastName] = fullName.split(" ");
// console.log(firstName);
// console.log(lastName);

let user3 = {
	firstName: "John",
	lastName: "Doe",

	get fullName() {
		return `${this.firstName} ${this.lastName}`;
	},
	set fullName(value) {
		[this.firstName, this.lastName] = value.split(" ");
	},
};

user.fullName = "Kit Harington";

console.log(user.fullName);

// 結果
// Kit Harington

//setterの中で入力した文字列のチェックを行うことも可能。matchメソッドを利用して入力した文字列に空白がないかチェックをして空白がない場合はメッセージを出力。
// let user = {
// 	firstName: "John",
// 	lastName: "Doe",

// 	get fullName() {
// 		return `${this.firstName} ${this.lastName}`;
// 	},
// 	set fullName(value) {
// 		if (!value.match(" ")) {
// 			console.log("入力した文字の名前に空白がありません。");
// 			return;
// 		}
// 		[this.firstName, this.lastName] = value.split(" ");
// 	},
// };

// user.fullName = "KitHarington";

// console.log(user.fullName);
