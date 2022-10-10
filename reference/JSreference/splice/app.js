/*spliceはその場で、配列の要素を除いたり、置き換えたり、新しい要素を追加できる
startは配列を変更する要素数を示す
deleteCountはstartの位置から、古い要素を取り除く数を示す
【構文】
splice(start)
splice(start, deleteCount)
splice(start, deleteCount, item1)
splice(start, deleteCount, item1, item2, itemN)
*/
const months = ['Jan', 'March', 'April', 'June'];
months.splice(1, 0, 'Feb');

console.log(months);
//expected output:Array ["Jan", "Feb", "March", "April", "June"]

months.splice(4, 1, 'May');

console.log(months);
//expected output: Array ["Jan", "Feb", "March", "April", "May"]
