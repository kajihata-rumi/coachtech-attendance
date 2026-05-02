<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
</head>

<body>
    <h1>{{ $title }}</h1>
    <p>仮ページです。</p>

    <form action="/logout" method="post">
        @csrf
        <button type="submit">ログアウト</button>
    </form>
</body>

</html>