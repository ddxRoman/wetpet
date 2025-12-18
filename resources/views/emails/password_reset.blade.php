<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Сброс пароля</title>
    <style>
        body {
            background-color: #eef3ff;
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            text-align: center;
            background-color: #a0c9ffb6;
            padding: 20px;
        }
        .email-header img {
            width: 120px;
        }
        .email-body {
            padding: 30px;
            text-align: center;
            color: #333;
        }
        .email-body h2 {
            color: #333;
            font-size: 20px;
        }
        .email-body p {
            color: #555;
            font-size: 15px;
            line-height: 1.6;
        }
        .reset-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #ec7819b6;
            color: #fff !important;
            font-weight: 600;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        .reset-btn:hover {
            background-color: #ec7819ff;
        }
        .email-footer {
            margin-top: 30px;
            font-size: 13px;
            color: #888;
            text-align: center;
            border-top: 1px solid #eee;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <img
    src="{{ asset('storage/logo/logo_mail.png') }}"
    title="Зверозор, портал о домашних питомцах"
    alt="{{ config('app.name') }}"
    style="max-width:200px; display:block; margin:0 auto;"
>

        </div>

        <div class="email-body">
            <h2>Здравствуйте{{ $user->name ? ', '.$user->name : '' }}!</h2>
            <p>Вы получили это письмо, потому что запросили сброс пароля на сайте <b>{{ config('app.name') }}
</b>.</p>
            <p>Нажмите на кнопку ниже, чтобы установить новый пароль:</p>

            <a href="{{ $resetUrl }}" class="reset-btn">Сбросить пароль</a>

            <p style="margin-top:20px;">Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.</p>
        </div>

        <div class="email-footer">
            © {{ date('Y') }} {{ config('app.name') }}. Все права защищены.
        </div>
    </div>
</body>
</html>
