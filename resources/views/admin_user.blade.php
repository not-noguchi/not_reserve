
@extends('layouts.admin_base')
@section('title', '会員管理')
@section('content')
<script>
</script>

  @vite([
     'resources/js/admin/user.js'
  ])
<style>
table thead tr th {
  color: #0060a4;
  background: #bddbf0;
}
table tfoot tr th {
  color: #0060a4;
  background: #bddbf0;
}
.btn-del{
  border-radius: 100px;
  display: block;
  width: 60px;
  padding: 2px;
  box-sizing: border-box;
  color: #ffffff;
  background: #ff4500;
  text-decoration: none;
  text-align: center;
}
</style>

  <!-- Begin Page Content -->
  <div class="container-fluid">

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">会員一覧</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th>会員No</th>
                <th>氏名</th>
                <th>email</th>
                <th>プラン</th>
                <th>有効期限</th>
                <th>予約期限</th>
                <th>登録日</th>
                <th>編集</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>会員No</th>
                <th>氏名</th>
                <th>email</th>
                <th>プラン</th>
                <th>有効期限</th>
                <th>予約期限</th>
                <th>登録日</th>
                <th>編集</th>
              </tr>
            </tfoot>
            <tbody>
              <!-- {{ $row=1 }}-->
              @foreach($userList as $user)
              <tr>
                <th>{{ str_pad($user->user_no, 3, '0', STR_PAD_LEFT) }}</th>
                <th>{{ $user->name }}</th>
                <th>{{ $user->email }}</th>
                <th>{{ config('const.m_plan')[$user->plan_id] }}</th>
                <th>{{ $user->expire_start }}～<br />{{ $user->expire_end }}</th>
                <th>{{ $user->reserve_start }}～<br />{{ $user->reserve_end }}</th>
                <th>{{ $user->created_at }}</th>
                <th><a class="btn-del" href="#" onclick="delUser({{$user->id}}, '{{ $user->user_no }}', '{{ $user->name }}', {{ $row }});return false;">削除</a></th>
                <!-- {{ $row++ }} -->
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <!-- /.container-fluid -->

@endsection
