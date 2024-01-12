<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>----タイトル----</title>
    @vite([
      'resources/css/user/base.css'
    ])
  </head>
  <body>
    <!-- ▼全体の囲み▼ -->
    <div id="wrapper">

      <!-- メニューを開くボタン -->
      <div id="open">
        <span id="open-icon"></span><span class="open-text">menu</span>
      </div>

      <!-- ▼ヘッダ▼ -->
      <header>
        <div id="header-inner">
          <!-- サイト名 -->
          <h1><span><a href="/home">Golf No.T 予約サイト</a></span></h1>
          @yield('title')
        </div>
      </header><!-- ▲ヘッダ▲ -->

      <!-- ▼メイン▼ -->
      <div id="contents" class="cf">
        <!-- ▼右側▼ -->
        <div id="rightcolumn-wrap">
          @yield('content')
        </div>
        <!-- ▲右側▲ -->


        <!-- ▼左側▼ -->
        <div id="side-bg"></div>
        <div id="leftcolumn-wrap">
          <div id="leftcolumn">
            <!-- ▼メニュー▼ -->
            <h2>Menu</h2>
            <div id="menu">
              <nav>
                <ul>
                  <li><a href="/home">ホーム</a>
                  <li><a href="/reserve">予約カレンダー</a>
                  <li>
                    <span>Other</span>
                    <ul class="sub-menu">
                      <li><a href="#">サブメニュー1</a>
                      <li><a href="#">サブメニュー2</a>
                    </ul>
                  <li>
                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                  </li>
                </ul>
              </nav>
            </div>
            <!-- ▲メニュー▲ -->
            <aside>
              <h2>Update</h2>
                <!-- テキストエリア -->
                <textarea rows="3" cols="20">
                  2024/01/11 up
                </textarea>

              <h2>Link</h2>
                -- ゴルフナンバーティー --<br>
                URL: <a href="https://golf.no-t.jp/">https://golf.no-t.jp</a><br>
                TEL: <a href="tel:042-404-2782">042-404-2782</a><br>
            </aside>
          </div>
        </div>
        <!-- ▲左側▲ -->

        <!-- ▼フッタ▼ -->
        <footer>
        <div id="footer-inner">
          <div id="fl"><span>Copyright &copy; 2024 Labo No.T</span></div>
          </div>
        </footer><!-- ▲フッタ▲ -->

      </div>
      <!-- ▲メイン▲ -->
    </div>
    <!-- ▲全体の囲み▲ -->

    <!-- ページトップに戻る -->
    <a href="#" id="pagetop"><span class="arrow"></span></a>

    <!-- jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="{{ asset('/js/user/jquery.height.js') }}"></script>
    <script src="{{ asset('/js/user/jquery.scroll.js') }}"></script>
    <script src="{{ asset('/js/user/jquery.toggle.js') }}"></script>
  </body>
</html>