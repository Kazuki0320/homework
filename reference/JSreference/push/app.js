//push:配列の末尾に１つ以上の要素を追加することができる。
const animals = ['pigs', 'goats', 'sheep'];

const count = animals.push('cows');
console.log(count);
//expected output:4
console.log(animals);
//expected output: Array["pigs","goats","sheep","cows"];

animals.push('chickens', 'cats', 'dogs');
console.log(animals);
//expected output: Array ["pigs", "goats", "sheep", "cows", "chickens", "cats", "dogs"]
