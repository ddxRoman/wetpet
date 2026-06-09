<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты — Зверозор</title>
    <style>
        :root {
            --primary-color: #4f46e5; /* Фирменный индиго, можно заменить на цвет Зверозора */
            --text-main: #1f2937;
            --text-muted: #4b5563;
            --bg-body: #f9fafb;
            --bg-card: #ffffff;
            --border-color: #e5e7eb;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            margin: 0;
            padding: 40px 20px;
        }

        .contacts-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--text-main);
        }

        .header p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }

        @media (max-width: 640px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .card h2 {
            font-size: 1.25rem;
            margin-top: 0;
            margin-bottom: 16px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card p {
            margin: 8px 0;
            color: var(--text-muted);
        }

        .card a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .card a:hover {
            text-decoration: underline;
        }

        .requisites {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 30px;
        }

        .requisites h2 {
            font-size: 1.4rem;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
        }

        .req-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .req-item label {
            display: block;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .req-item span {
            font-weight: 500;
            color: var(--text-main);
        }
    </style>
</head>
<body>

<div class="contacts-container">
    <div class="header">
        <h1>Контакты</h1>
        <p>Мы всегда на связи. Выберите удобный способ для связи с проектом «Зверозор»</p>
    </div>

    <!-- Сетка основных контактов -->
    <div class="grid">
        <!-- Электронная почта -->
        <div class="card">
            <h2>
                <!-- Простая SVG иконка почты -->
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                Электронная почта
            </h2>
            <p>По вопросам поддержки, сотрудничества и общих предложений пишите нам на:</p>
            <p><a href="mailto:{{ config('company.email') }}">{{ config('company.email') }}</a></p>
        </div>

        <!-- Обратная связь / Время работы -->
        <div class="card">
            <h2>
                <!-- Простая SVG иконка часов -->
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                Режим работы
            </h2>
            <p>Прием электронных обращений — круглосуточно.</p>
            <p>Ответы на запросы осуществляются в будние дни с 10:00 до 19:00.</p>
        </div>
    </div>

    <!-- Блок юридических реквизитов -->
    <div class="requisites">
        <h2>Юридическая информация</h2>
        
        <div class="req-grid">
            <div class="req-item">
                <label>Наименование / ФИО</label>
                <span>{{ config('company.fio') }}</span>
            </div>

            <div class="req-item">
                <label>ИНН</label>
                <span>{{ config('company.inn') }}</span>
            </div>

            <div class="req-item" style="grid-column: 1 / -1;">
                <label>Адрес регистрации</label>
                <span>{{ config('company.address') }}</span>
            </div>
        </div>
    </div>
</div>

</body>
</html>