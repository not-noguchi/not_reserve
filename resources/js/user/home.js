import axios from 'axios';

function cancel(id) {
    let strReserveDate = document.getElementById('reserve_date_' + id).value;

    var now = new Date();
    now.setHours(Number(now.getHours()) + Number(cancellAvailableTime));
    var reserveDate = new Date(strReserveDate + ':00');
    if (reserveDate.getTime() < now.getTime()) {
        // 1時間前を過ぎていたらエラー
        alert('キャンセル出来ません。店舗にご連絡ください。');
        return;
    }

    if (confirm( strReserveDate + '～、この予約をキャンセルします。宜しいですか？')) {
        // 予約キャンセル(カレンダー用)処理の呼び出し
        axios.post("/api/user/calendar/cancel_reserve", {
                reserve_id: id,
                api_token: token,
            })
            .then((response) => {
                let returnData = response.data;

                if (returnData.result_info.code == 200) {
                    alert("予約キャンセル完了しました。");
                    let list_element = document.getElementById('li_' + id);
                    list_element.remove();
                } else {
                    alert(returnData.result_info.message);
                }
            })
            .catch(() => {
                // バリデーションエラーなど
                if (response.data.result_info.message) {
                    alert(response.data.result_info.message);
                } else {
                    alert("予約キャンセルに失敗しました");
                }
            });    
    } else {
        
    }
}

window.cancel = cancel;