<html>
    <head>
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <script src="{{ asset('/vendor/bootstrap/js/bootstrap.js') }}"></script>
        @vite([
          'resources/css/admin/calendar.css'
          , 'resources/js/admin/calendar.js'
        ])
    </head>
<body>
    <div id='calendar'></div>

    <div class="modal" id="modal-template">
      <div class="modal__no">
        <label>No： <input type="text" id="user_no" placeholder="会員Noを入力"></label>
      </div>
      <div class="modal__name">
        <label>氏名(非会員のみ)： <input type="text" id="user_name" placeholder="ゲスト氏名を入力"></label>
      </div>
      <div class="modal__title">
        <label>対象ユーザ： <input type="text" id="title" readonly></label>
      </div>
<!--
      <div class="modal__color">
        <label>背景色： <input type="color" id="color"></label>
      </div>

      <div class="modal__times">
        <label>開始： <input type="date" id="start"></label>
        <label>終了： <input type="date" id="end"></label>
      </div>
-->          
      <div class="modal-action-buttons">
        <button class="modal-action-buttons__button save" id="save">登録</button>
        <button class="modal-action-buttons__button delete" id="delete">削除</button>
        <button class="modal-action-buttons close material-icons" id="cancel">cancel</button>
      </div>
    </div>
  <style>
div.fc-event-today{
  /*background: none;*/
  background-color: transparent;
}
  </style>
</body>
</html>
