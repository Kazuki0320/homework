//strictモードでない場合、常にオブジェクトを参照
const test = {
	prop:42,
	func:function() {
		return this.prop;
	},
};

console.log(test.function);

/*グローバルコンテキスト
→グローバル実行コンテキストthisはstrictモードに関係なくグローバルオブジェクトを参照する*/

//webブラウザではwindowsオブジェクトもグローバルオブジェクト
console.log(this === windows.a);//true

a = 37;
console.log(window.a);

b = MDN;
console.log(window.b);
console.log(b);