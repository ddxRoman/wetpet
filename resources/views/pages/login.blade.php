@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://yastatic.net/s3/passport-sdk/autofill/v1/sdk-suggest-with-polyfills-latest.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body class="body_page">
    <div class="container login-page">
    <div class="row">
        <div class="col-12 logo-block_login-page">
            <img class="page-login_logo" src="{{ Storage::url('logo.png') }}" alt="Зверополис">
        </div>
    </div>    
        <div class="row">
            
            <div class="col-12 login-block">
                <form action="">
                    <input class="login_input" type="phone" placeholder="Телефон"><br>
                    <input class="login_input" type="password" placeholder="Пароль"><br>
                    <button class="login_btn_page">Войти</button>
                </form>
                <hr class="hr_login_page">
                <p>
                   <h3>
                       или авторризуйтесь через:
                   </h3> 
                </p>
                <a href="" class="link_login_page_social">
                    <img class="login_social_icon" src="{{ Storage::url('icon/social/mailru.svg') }}" alt="">
                </a>
                <a href="" class="link_login_page_social">
                    <img class="login_social_icon" src="{{ Storage::url('icon/social/telegram.svg') }}" alt="">
                </a>
                <a href="" class="link_login_page_social">
                    <img class="login_social_icon" src="{{ Storage::url('icon/social/vk.svg') }}" alt="">
                </a>
                <a href="" class="link_login_page_social">
                    <img class="login_social_icon" src="{{ Storage::url('icon/social/yandex.svg') }}" alt="">
                </a>
            </div>
        </div>
    </div>
</body>
</html>