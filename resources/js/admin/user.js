import axios from 'axios';

  function delUser(id, userNo, userName, row) {
    if (confirm( ( '000' + userNo ).slice( -3 ) + ':' + userName + '、このユーザを削除します。宜しいですか？')) {
    // ユーザー削除処理の呼び出し
    axios.post("/api/admin/user/delete", {
            user_id: id,
        })
        .then((response) => {
            let returnData = response.data;

            if (returnData.result_info.code == 200) {
                alert("削除完了しました。");
                var table = document.getElementById('dataTable');
                table.deleteRow(row);
            } else {
                alert(returnData.result_info.message);
            }
        })
        .catch(() => {
            // バリデーションエラーなど
            if (response.data.result_info.message) {
                alert(response.data.result_info.message);
            } else {
                alert("削除に失敗しました");
            }
        });    
    }
}

window.delUser = delUser;