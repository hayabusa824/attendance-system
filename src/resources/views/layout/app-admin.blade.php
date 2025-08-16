<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>勤怠管理(管理者)</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__link">
                <img src="{{ asset('img/image.png') }}"  class="header__link--img"/>
            </a>
            </div>

            <nav class="nav">
                <div class="nav-item">
                    <a href="/admin/attendance/list" class="nav-item__button">勤怠一覧</a>
                </div>
                <div class="nav-item-2">
                    <a href="/admin/staff/list" class="nav-item__button">スタッフ一覧</a>
                </div>
                <div class="nav-item">
                    <a href="/stamp_correction_request/list" class="nav-item__button">申請一覧
                    </a>
                </div>
                <div class="nav-item-3">
                    <form action="/admin/logout" method="POST">
                    @csrf
                    <button class="nav-item__button" >ログアウト</button>
                    </form>
                </div>
            </nav>
        </div>
    </header>

    <main>
    @yield('content')
    </main>
</body>

</html>