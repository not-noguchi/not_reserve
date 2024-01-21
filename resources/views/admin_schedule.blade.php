
@extends('layouts.admin_base')
@section('title', 'スケジュール設定')
@section('content')
  <script>
    let masterSchedule = {!! json_encode($masterSchedule) !!};
    let isWeekdays = {!! json_encode(config('const.is_weekdays')) !!};
    var isWeekdaysFlg = 1;
    function changeWeekdays() {
        if (isWeekdaysFlg) {
          isWeekdaysFlg = 0;
        } else {
          isWeekdaysFlg = 1;
        }
        console.log(isWeekdaysFlg);
    }
  </script>

  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!-- script src="{{ asset('/vendor/bootstrap/js/bootstrap.js') }}"></script -->
  @vite([
    'resources/css/admin/schedule.css'
    , 'resources/js/admin/schedule.js'
  ])

  <div id='calendar'></div>

  <div class="reserve-modal" id="modal-template">
    <div class="modal__weekdays">
      <label>平日設定： <input class="is_weekdays" type="checkbox" id="is_weekdays" name="is_weekdays" value="1" onchange="changeWeekdays();"></label>
    </div>
    <div class="modal__add_schedule">
      <label>スケジュール： <input class="schedule" type="text" readonly></label>
    </div>
    <div class="modal__del_schedule">
      <label>スケジュール： <input class="schedule" type="text" readonly></label>
    </div>

    <div class="modal-action-buttons">
      <button class="modal-action-buttons__button save" id="save">登録</button>
      <button class="modal-action-buttons__button delete" id="delete">削除</button>
      <button class="modal-action-buttons close material-icons" id="cancel">cancel</button>
    </div>
  </div>

@endsection
