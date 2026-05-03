<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>

<body>
    <header class="auth-header">
        <a class="auth-header__logo" href="/">
            <img class="auth-header__logo-img" src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
        </a>
    </header>

    <main class="auth">
        <h1 class="auth__title">会員登録</h1>

        <form class="auth-form" action="/register" method="post" novalidate>
            @csrf

            <div class="auth-form__group">
                <label class="auth-form__label" for="name">名前</label>
                <input class="auth-form__input" type="text" id="name" name="name" value="{{ old('name') }}">

                @error('name')
                    <p class="auth-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-form__group">
                <label class="auth-form__label" for="email">メールアドレス</label>
                <input class="auth-form__input" type="email" id="email" name="email" value="{{ old('email') }}">

                @error('email')
                    <p class="auth-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-form__group">
                <label class="auth-form__label" for="password">パスワード</label>
                <input class="auth-form__input" type="password" id="password" name="password">

                @error('password')
                    <p class="auth-form__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-form__group">
                <label class="auth-form__label" for="password_confirmation">パスワード確認</label>
                <input class="auth-form__input" type="password" id="password_confirmation" name="password_confirmation">

                @error('password_confirmation')
                    <p class="auth-form__error">{{ $message }}</p>
                @enderror
            </div>

            <button class="auth-form__button" type="submit">登録する</button>
        </form>

        <div class="auth__link">
            <a href="/login">ログインはこちら</a>
        </div>
    </main>
</body>

</html>