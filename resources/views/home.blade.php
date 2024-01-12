@extends('layouts.user_base')
@section('title', 'ホーム')
@section('content')
<script>
  let token = "{{ auth()->user()->api_token }}";
  let cancellAvailableTime = "{{ config('const.cancell_available_time') }}";
</script>
@vite([
  'resources/js/user/home.js'
  ,'resources/css/user/home.css'
])

  <div id="rightcolumn">
    <article>
    <!-- ▼右側の内容▼ -->
      <h2>会員情報</h2>
      <div class="line">
        <span style="display: inline-block; width:60px;">プラン</span>: <em>{{ config('const.m_plan')[session('plan_id')] }}会員</em><br>
        <span style="display: inline-block; width:60px;">No.</span>: <em>{{ auth()->user()->user_no }}</em><br>
        <span style="display: inline-block; width:60px;">氏名</span>: <em>{{ auth()->user()->name }} 様</em><br>
      </div>
      <h2>お知らせ</h2>
      <div class="line">
        @if(session('plan_id') == 0)
        プランに<span class="marker">未設定会員</span>と表示される場合は予約情報の設定が済んでおりません。システム側の対応をお待ちください。<br />
        @endif
        <span class="marker">テスト運用中です</span><br />
      </div>

      <h2>予約情報</h2>
      <div class="reserve-list">
        @foreach($reserve_list as $reserve)
        <li id="li_{{ $reserve->id }}">
          <label style="display: inline-block; height:30px;">{{ $reserve->use_date }} {{ substr($reserve->start_time,0,5) }}～</label> <a class="btn-cancel" href="#" onclick="cancel({{ $reserve->id }});return false;" class="btn-reserve">キャンセル</a>
          <input type="hidden" id="reserve_date_{{ $reserve->id }}" value="{{ $reserve->use_date }} {{ substr($reserve->start_time,0,5) }}" /><br>
        </li>
        @endforeach
      </div>

    <!-- ▲右側の内容▲ -->
    </article>
    <!-- ページ内リンクのために余白空けています 削除可 -->
    <div style="height:30px"></div>
  </div>
@endsection
