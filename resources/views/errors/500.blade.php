<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Ошибка сервера — Зверозор</title>

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f1f1f1;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #212529;
            padding: 24px;
        }
        .error-wrap {
            text-align: center;
            max-width: 640px;
        }
        .error-logo {
            margin-bottom: 28px;
        }
        .error-logo img {
            height: 48px;
            width: auto;
        }
        .error-code {
            font-family: 'Arial Black', sans-serif;
            font-size: 6rem;
            line-height: 1;
            font-weight: 900;
            color: #ff8c00;
            text-shadow: 2px 2px 0px #fff, 4px 4px 0px rgba(0,0,0,0.1);
            margin: 0 0 16px;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 16px;
        }
        .error-text {
            color: #6c757d;
            font-size: 1.05rem;
            line-height: 1.6;
            margin: 0 0 36px;
        }
        .error-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 12px 36px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background-color: #ff8c00;
            color: #fff;
        }
        .btn-primary:hover {
            background-color: #e67e00;
            color: #fff;
        }
        .btn-outline {
            background: transparent;
            color: #6c757d;
            border: 1px solid #ced4da;
        }
        .btn-outline:hover {
            background-color: #e9ecef;
        }
        .error-paw {
            font-size: 2.5rem;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="error-wrap">
        <div class="error-logo">
            <img src="/storage/logo/logo2.png" alt="Зверозор" onerror="this.style.display='none'">
        </div>

        <div class="error-paw">🐾</div>
        <p class="error-code">500</p>
        <h1 class="error-title">Что-то пошло не так на нашей стороне</h1>
        <p class="error-text">
            Технические неполадки на сервере — мы уже знаем о проблеме и разбираемся.<br>
            Попробуйте обновить страницу через пару минут или вернитесь на главную.
        </p>

        <div class="error-actions">
            <a href="/" class="btn btn-primary">На главную</a>
            <button type="button" class="btn btn-outline" onclick="window.location.reload()">Обновить страницу</button>
        </div>
    </div>
</body>
</html>
