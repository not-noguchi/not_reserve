import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";

import axios from 'axios';

var calendarEl = document.getElementById("calendar");
let today = new Date();
const dateRanges = showPrevDateRange(today);
const backgroundParams = backgroundColorParams(today, dateRanges[0]);
var reserveInfo = [];
var businessInfo = [];

let calendar = new Calendar(calendarEl, {
    plugins: [interactionPlugin, dayGridPlugin],
    initialView: "dayGridMonth",
    contentHeight: 'auto',
    nowIndicator: true,
    locale: "ja",
//    timeZone: "Asia/Tokyo",
    initialDate: new Date(),
    navLinks: false,
    editable: true,
    dayMaxEvents: true,
    firstDay:0, // 週の始まり
    fixedWeekCount: false,
    dayCellContent: function(arg) {
        return arg.date.getDate();
    },
    headerToolbar: {
        left: 'prev,next,today',
        center: 'title',
        right: 'dayGridMonth'
    },
    buttonText: {
        today: '今月',
        month: '月',
        list: 'リスト'
    },
    validRange: {
        start: dateRanges[0],
        end: dateRanges[1],
    },
    views: {
        dayGridMonth: {
          titleFormat: function (date) {
            return `${date.date.year}年 ${date.date.month + 1}月`;
          },
          dayHeaderContent: function (date) {
            let weekList = ['日', '月', '火', '水', '木', '金', '土'];
            return weekList[date.dow];
          },
        },
    },
    events: function (info, successCallback, failureCallback) {

        // カレンダー情報取得処理の呼び出し
        axios.post("/api/user/calendar/fetch", {
                start_date: info.start.valueOf(),
                end_date: info.end.valueOf(),
                api_token: token,
            })
            .then((response) => {
                let returnData = response.data;
                if (returnData.schedule_info) {
                    let closed = returnData.schedule_info.closed;
                    for(let key in closed) {
                        // 休業日をグレーアウト
                        var findParam = 'td[data-date=\'' + closed[key]['date'] + '\']';
                        document.querySelector(findParam).firstElementChild.style.background = '#808080';
                    }
                    //successCallback(returnData.schedule_info.closed);
                    reserveInfo = returnData.reserve_info;
                    businessInfo = returnData.schedule_info.business
                }
            })
            .catch(() => {
                // バリデーションエラーなど
                alert("スケジュール取得に失敗しました");
            });
    },
    dateClick: function (date, allDay, jsEvent, view) {

        if (date.dayEl.classList.contains("fc-day-past")) {
            alert("選択できません。");
            return;
        }
        // 時間選択切り替え
        changeTimeSelect(date.dateStr);

    },

});
calendar.render();

//events.push(backgroundParams);

function changeTimeSelect(dateStr) {

    let tmpBusinessInfo = businessInfo[dateStr];
    if (tmpBusinessInfo == null) {
        return;
    }
    let selectDate = document.getElementById('select_date');
    let selectTime = document.getElementById('select_time');
    // 選択日付色変更（リセット）
    if (selectDate.textContent != '予約日') {
       var findParam = 'td[data-date=\'' + selectDate.textContent + '\']';
       document.querySelector(findParam).firstElementChild.style.background = '#ffffff';
    }
    // 選択日付色変更
    var findParam = 'td[data-date=\'' + dateStr + '\']';
    document.querySelector(findParam).firstElementChild.style.background = '#99ccff';
    // 日付設定
    selectDate.textContent = dateStr;

    // option要素を削除
    while (0 < selectTime.childNodes.length) {
        selectTime.removeChild(selectTime.childNodes[0]);
    }
    // option要素を生成
    var option = document.createElement('option');
    var text = document.createTextNode('時間を選択してください');
    option.appendChild(text);
    // option要素を追加
    option.value = 0;
    selectTime.appendChild(option);

    let tmpReserveInfo = reserveInfo[dateStr];
    var now = new Date();
    now.setHours(Number(now.getHours()) + Number(reserveAvailableTime));
    for(let key in tmpBusinessInfo) {
        var reserveDate = new Date(dateStr + ' ' + key);
        if (reserveDate.getTime() < now.getTime()) {
            // 1時間前を過ぎていたら表示しない
            continue;
        }
        var option = document.createElement('option');
        var rest = 4;
        var isSelf = false;
        if (tmpReserveInfo != null && tmpReserveInfo[key] != null) {
            rest = rest - tmpReserveInfo[key]['reserve_cnt'];
            isSelf = tmpReserveInfo[key]['is_self'];
        }
        var text = '';
        if (isSelf) {
            text = document.createTextNode(tmpBusinessInfo[key]['startDisp'] + '～ 残：' + rest + ' 予約済');
        } else {
            text = document.createTextNode(tmpBusinessInfo[key]['startDisp'] + '～ 残：' + rest);   
        }
        option.appendChild(text);
        // option要素を追加
        if (rest && !isSelf) {
            option.value = dateStr + ' ' + key;
        } else {
            option.value = 1;
        }
        selectTime.appendChild(option);
    }
}

// 予約実行
document.getElementById("reserve").onclick = function () {

    let selectTime = document.getElementById('select_time').value;
    if (selectTime == 0) {
        // 未選択
        alert('予約時間を選択してください。');
        return;
    } else if (selectTime == 9) {
        // 予約不可
        alert('予約出来ません。日付と時間をご確認ください。');
        return;
    }
    // 時間チェック
    var now = new Date();
    now.setHours(Number(now.getHours()) + Number(reserveAvailableTime));
    var reserveDate = new Date(selectTime);
    if (reserveDate.getTime() < now.getTime()) {
        // 1時間前を過ぎていたらエラー
        alert('予約出来ません。日付と時間をご確認ください。');
        return;
    }

    if (confirm( selectTime + '、この日時で予約します。宜しいですか？')) {
        // 予約登録(カレンダー用)処理の呼び出し
        axios.post("/api/user/calendar/add_reserve", {
                start_date: selectTime,
                api_token: token,
            })
            .then((response) => {
                let returnData = response.data;

                if (returnData.result_info.code == 200) {
                    alert("予約登録完了しました。");
                } else {
                    alert(returnData.result_info.message);
                }
            })
            .catch(() => {
                // バリデーションエラーなど
                if (response.data.result_info.message) {
                    alert(response.data.result_info.message);
                } else {
                    alert("予約登録に失敗しました");
                }
            });    
    }
}

function showPrevDateRange(today) {
    const year = today.getFullYear();
    const month = today.getMonth() + 1;
    const isLastYear = month - 1 === 0 ? true : false;
    const isNextYear = month + 3 > 12 ? true : false;

    return [
        // 2か月前まで
        (isLastYear ? year - 1 : year) + "-" + dateZeroPadding(isLastYear ? 12 : month - 2) + "-01",
        // 1か月先まで表示
        (isNextYear ? year + 1 : year) + "-" + dateZeroPadding(isNextYear ? month - 12 + 2 : month + 2) + "-01",
    ];
}

function dateZeroPadding(date) {
    return ("0" + date).slice(-2);
}

function backgroundColorParams(today, start) {
    return {
        start: start,
        end: today.getFullYear() + "-" + dateZeroPadding(today.getMonth() + 1) + "-" + dateZeroPadding(today.getDate()),
        display: "background",
        color: "#d7d7d7"
    };
}