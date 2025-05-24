//新規作成（自動的にIDを生成したい場合①）
//CollectionReferenceのaddメソッド を利用してドキュメントを追加したい場合
(async () => {
	try {
	  // 省略 
	  // (Cloud Firestoreのインスタンスを初期化してdbにセット)

	const userRef = await db.collection('users').add({
		name: {
		first: 'tarou',
		last: 'yamada',
		},
		score: 80,
		birthday: firebase.firestore.Timestamp.fromDate(new Date(1980, 10, 15)),
		createdAt: firebase.firestore.FieldValue.serverTimestamp(),
		updatedAt: firebase.firestore.FieldValue.serverTimestamp(),
	})

	const userDoc = await userRef.get()
	console.log(userDoc.data())
	// 出力例
	// { birthday: Timestamp { seconds: 343062000, nanoseconds: 0 },
	// createdAt: Timestamp { seconds: 1571747519, nanoseconds: 521000000 },
	// name: { first: 'tarou', last: 'yamada' },
	// score: 80,
	// updatedAt: Timestamp { seconds: 1571747519, nanoseconds: 521000000 } }
	} catch (err) {
	console.log(`Error: ${JSON.stringify(err)}`)
	}
})()

//新規作成（自動的にIDを生成したい場合②）
//DocumentReferenceのsetメソッド を利用してドキュメントを追加することもできる。
//- CollectionReference.doc()
//- DocumentReference.set(docData)
(async () => {
	try {
		// 省略 
		// (Cloud Firestoreのインスタンスを初期化してdbにセット)

		const userRef = db.collection('users').doc()
		await userRef.set({
			name: {
				first: 'tarou',
				last: 'yamada',
			},
			score: 80,
			birthday: firebase.firestore.Timestamp.fromDate(new Date(1980, 10, 15)),
			createdAt: firebase.firestore.FieldValue.serverTimestamp(),
			updatedAt: firebase.firestore.FieldValue.serverTimestamp(),
		})
	} catch (err) {
		console.log(`Error: ${JSON.stringify(err)}`)
	}
})()

//新規作成(ID指定)
//CollectionReferenceのdocメソッド の引数で、ドキュメントIDを指定することもできる。
//- CollectionReference.doc(docId)
//- DocumentReference.set(docData)

(async () => {
	try {
	  // 省略 
	  // (Cloud Firestoreのインスタンスを初期化してdbにセット)

	const userRef = db.collection('users').doc('abcdefg')
	await userRef.set({
		name1: 'xxxxx',
		name2: 'yyyyy',
	})
	} catch (err) {
	console.log(`Error: ${JSON.stringify(err)}`)
	}
})()