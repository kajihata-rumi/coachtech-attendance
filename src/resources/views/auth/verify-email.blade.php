<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール認証</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>

<body>
    <header class="auth-header">
        <a class="auth-header__logo" href="/">
            <img class="auth-header__logo-img" src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
        </a>
    </header>

    <main class="verify-email">
        <p class="verify-email__message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <a class="verify-email__button" href="http://localhost:8025" target="_blank">
            認証はこちらから
        </a>

        <form action="/email/verification-notification" method="post">
            @csrf
            <button class="verify-email__resend" type="submit">
                認証メールを再送する
            </button>
        </form>
    </main>
</body>

</html>

