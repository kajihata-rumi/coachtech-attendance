<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>メール認証完了</title>
</head>

<body>
    <p>メール認証完了</p>

    <form action="/logout" method="post">
        @csrf
        <button type="submit">ログアウト</button>
    </form>
</body>

</html>
