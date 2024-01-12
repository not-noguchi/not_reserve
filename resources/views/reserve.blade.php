@extends('layouts.user_base')
@section('title', '予約カレンダー')
@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script>
  let token = "{{ auth()->user()->api_token }}";
  let reserveAvailableTime = "{{ config('const.reserve_available_time') }}";
</script>
<div id="rightcolumn">
  <article>
  <!-- ▼右側の内容▼ -->
    @vite([
      'resources/css/user/reserve.css'
      , 'resources/js/user/reserve.js'
    ])

    <div id='calendar'></div>

  <!-- ▲右側の内容▲ -->
  </article>
</div>
<div id="reservecolumn">
  <div class="item-reserve">
    <label id="select_date">予約日</label>  
  </div>
  <div class="item-reserve">
    <label class="select-time">
        <select id="select_time">
            <option value="0">時間を選択してください</option>
        </select>
    </label>
  </div>
  <div class="item-reserve">
    <a id="reserve" href="#" class="btn-reserve">予約する</a>  
  </div>

</div>
@endsection
