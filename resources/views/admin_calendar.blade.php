
@extends('layouts.admin_base')
@section('title', '予約カレンダー')
@section('content')
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!-- script src="{{ asset('/vendor/bootstrap/js/bootstrap.js') }}"></script -->
  @vite([
    'resources/css/admin/calendar.css'
    , 'resources/js/admin/calendar.js'
  ])

  <div id='calendar'></div>

  <div class="reserve-modal" id="modal-template">
    <div class="modal__no">
      <label>No： <input class="user_no" type="text" placeholder="会員Noを入力"></label>
    </div>
    <div class="modal__name">
      <label>氏名(非会員のみ)： <input class="user_name" type="text" placeholder="ゲスト氏名を入力"></label>
    </div>
    <div class="modal__title">
      <label>対象ユーザ： <input class="title" type="text" readonly></label>
    </div>

    <div class="modal-action-buttons">
      <button class="modal-action-buttons__button save" id="save">登録</button>
      <button class="modal-action-buttons__button delete" id="delete">削除</button>
      <button class="modal-action-buttons close material-icons" id="cancel">cancel</button>
    </div>
  </div>

@endsection
