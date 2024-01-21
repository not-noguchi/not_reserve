import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from '@fullcalendar/timegrid';
import axios from 'axios';

var calendarEl = document.getElementById("calendar");

let calendar = new Calendar(calendarEl, {
    plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin],
    initialView: "timeGridWeek",
    contentHeight: 'auto',
    nowIndicator: true,
    locale: "ja",
//    timeZone: "Asia/Tokyo",
    initialDate: new Date(),
    navLinks: true,
    editable: true,
    dayMaxEvents: true,
    firstDay:0, // 週の始まり
    fixedWeekCount: false,
    allDaySlot: false,
    slotDuration: '01:00', // 15分ごとのslot
    slotLabelInterval: '01:00', // 1時間ごとにラベルを表示

    headerToolbar: {
        left: 'prev,next,today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek'
    },
    buttonText: {
        today: '今日',
        month: '月',
        week: '週',
        list: 'リスト'
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
        timeGridWeek: {
            slotMinTime: '08:00:00',
            slotMaxTime: '22:00:00',
          slotLabelFormat: { hour: 'numeric', minute: '2-digit',hour12: false },
          titleFormat: function (date) {
            const startMonth = date.start.month + 1;
            const endMonth = date.end.month + 1;
            if (startMonth === endMonth) {
              return `${date.date.year}年 ${startMonth}月`;
            } else {
              return `${date.date.year}年 ${startMonth}月～ ${endMonth}月`; 
            }
          },
          dayHeaderFormat: function (date) {
            const day = date.date.day;
            const weekNum = date.date.marker.getDay();
            const week = ['(日)', '(月)', '(火)', '(水)', '(木)', '(金)', '(土)'][weekNum];
            return day + ' ' + week;
          },
        },
    },
    // 日付をクリック、または範囲を選択したイベント
    selectable: true,
    select: arg => {
        // 新規
        var argDate = new Date(arg.start);
        let events = calendar.getEvents();
        for (var i=0; i<events.length; i++) {
            var tmpDate = new Date(events[i].start);
            if (tmpDate.getTime() == argDate.getTime()) {
                // スケジュール設定済み
                return false;
            }
        }
        let element = document.getElementById('is_weekdays');
        element.checked = true;
        isWeekdaysFlg = 1;

        if (isWeekdays[argDate.getDay()] == 1) {
            // 平日
            if (masterSchedule[1][( '00' + argDate.getHours() ).slice( -2 )] == null
                && masterSchedule[0][( '00' + argDate.getHours() ).slice( -2 )] == null) {
                alert('登録出来ない時間です。');
                return false;
            }
        } else {
            // 土日
            if (masterSchedule[0][( '00' + argDate.getHours() ).slice( -2 )] == null) {
                alert('登録出来ない時間です。');
                return false;
            }
            element.checked = false;
            isWeekdaysFlg = 0;
        }

        initEditModal( arg );
    },
    eventClick: arg => {
        // 変更
        if (arg.event.title.substr(1, 1) != '日') {
            // 予約イベントは拾わない
            return false;
        }
        initEditModal( arg );
    },
    events: function (info, successCallback, failureCallback) {
        // スケジュール情報取得処理の呼び出し
        axios.post("/api/admin/schedule/fetch", {
                start_date: info.start.valueOf(),
                end_date: info.end.valueOf(),
            })
            .then((response) => {
                let returnData = response.data;
                // カレンダーに読み込み
                if (returnData.reserve_info) {
                    successCallback(returnData.reserve_info);
                }
            })
            .catch(() => {
                // バリデーションエラーなど
                alert("スケジュール取得に失敗しました");
            });
    },

});
calendar.render();

const initEditModal = data  => {
    removeAlreadyModal();
    const defModal = document.getElementById( 'modal-template' );
    const modal = defModal.cloneNode( true );
    modal.id = 'modal';

    // modal.childNodes[1].childNodes[1].childNodes[1].id = 'user_no';
    // modal.childNodes[3].childNodes[1].childNodes[1].id = 'user_name';


    setupModalPosition( modal, data.jsEvent );
    document.body.appendChild( modal );
    if ( data.event === undefined ) {
        // イベントが取得出来ない(新規の)場合title、削除ボタン非表示
        document.querySelector( '#modal .delete' ).remove();
        document.querySelector( '#modal .modal__del_schedule' ).remove();
    } else {
        // イベントが取得出来た場合、保存ボタン非表示
        document.querySelector( '#modal .modal__weekdays' ).remove();
        document.querySelector( '#modal .modal__add_schedule' ).remove();
        document.querySelector( '#modal .save' ).remove();
    }
  
    setupModalData( modal, data );

    registerEditModalEvent( modal, data );
};

const setupModalPosition = ( modal, e ) => {
    modal.style.display = 'flex';
    modal.style.position = 'absolute';
    modal.style.zIndex = 9999;

    const position = calcModalPosition( e );
    modal.style.left = `${position.x}px`;
    modal.style.top = `${position.y}px`;
};

const calcModalPosition = e => {
    const windowWidth = window.outerWidth;

    const y = e.pageY + 16;
    let x = e.pageX;

    if ( e.pageX <= 125 ) {
        x = e.pageX;
    } else if (  e.pageX > 125 && windowWidth - e.pageX > 125 ) {
        x = e.pageX - 125;
    } else if ( windowWidth - e.pageX <= 125 ) {
        x = e.pageX - 250;
    }

    return {
        x: x,
        y: y
    };
};

const removeAlreadyModal = () => {
    const modal = document.getElementById( 'modal' );
    if ( modal ) {
        modal.remove();
    }
};

// モーダル登録処理
const registerEditModalEvent = ( modal, arg ) => {

    const start = arg.start;
    const end = arg.end;

    const userNo = modal.querySelector( '.user_no' );
    const userName = modal.querySelector( '.user_name' );
    const title = modal.querySelector( '.title' );
  
    // 保存
    const saveButton = modal.querySelector( '#save' );
    if ( saveButton ) {
        saveButton.addEventListener( 'click', e => {
            e.preventDefault();

      
            if ( arg.event !== undefined ) {
                // 変更時
                arg.event.setStart( start );
                arg.event.setEnd( end );
                arg.event.setProp( 'title', title.value );
            } else {
                // 新規作成時
                // 入力チェック
                var startDate = new Date(start);
                if (isWeekdays[startDate.getDay()] == 0 && isWeekdaysFlg == 1) {
                    alert('平日設定エラー(土日には設定出来ません)');
                    return false;
                }
                var m_schedule_id = null;
                if (masterSchedule[isWeekdaysFlg][( '00' + startDate.getHours() ).slice( -2 )] == null) {
                    alert('登録出来ない時間です');
                    return false;
                } else {
                    // masterScheduleのID取得
                    m_schedule_id = masterSchedule[isWeekdaysFlg][( '00' + startDate.getHours() ).slice( -2 )]['id'];
                }
                var $title = '';
                var $color = '';
                if (isWeekdaysFlg == 1) {
                    // 平日
                    $title = '平日:';
                    $color = '#ff8c00';
                } else {
                    // 休日
                    $title = '休日:';     
                    $color = '#3cb371';               
                }
                $title +=  ( '00' + startDate.getHours() ).slice( -2 ) + '～';

                // 予約登録(カレンダー用)処理の呼び出し
                axios.post("/api/admin/schedule/add", {
                        m_schedule_id: m_schedule_id,
                        use_date: start.valueOf(),
                    })
                    .then((response) => {
                        let returnData = response.data;

                        if (returnData.result_info.code == 200) {
                            // カレンダーにイベント追加
                            calendar.addEvent( {
                              start: start,
                              end: end,
                              title: $title,
                              schedule_id: returnData.schedule_info.schedule_id,
                              backgroundColor: $color
                            } );
                        } else {
                            alert(returnData.result_info.message);
                        }
                    })
                    .catch(() => {
                        // バリデーションエラーなど
                        if (response.data.result_info.message) {
                            alert(response.data.result_info.message);
                        } else {
                            alert("スケジュール登録に失敗しました");
                        }
                    });


            }

            calendar.unselect();
            modal.remove();
        } );
    }

    // キャンセル
    const cancelButton = modal.querySelector( '#cancel' );
    cancelButton.addEventListener( 'click', e => {
        e.preventDefault();

        calendar.unselect();
        modal.remove();
    } );

    // 削除
    const deleteButton = modal.querySelector( '#delete' );
    if ( deleteButton ) {

        deleteButton.addEventListener( 'click', e => {

            // スケジュール削除処理の呼び出し
            axios.post("/api/admin/schedule/delete", {
                    schedule_id: arg.event.extendedProps.schedule_id
                })
                .then((response) => {
                    let returnData = response.data;

                    if (returnData.result_info.code == 200) {
                        // カレンダーイベント削除
                        e.preventDefault();
                        arg.event.remove();
                        calendar.unselect();
                        modal.remove();

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
        } );
    }
};

// モダールに既存イベントを設定
const setupModalData = ( modal, data ) => {

  const title = modal.querySelector( '.schedule' );

  if ( data.event !== undefined ) {
    // 削除時(イベント変更)
    title.value = formatDateTime(data.event.start);
  } else {
    // 登録時(イベント新規)
    title.value = formatDateTime(data.start);
  }
};

// DateObject to YYYY-MM-DD
function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [year, month, day].join('-');
}

// DateObject to YYYY-MM-DD 00:00~
function formatDateTime(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear(),
        hour = d.getHours();

    if (month.length < 2) {
        month = '0' + month;
    }
    if (day.length < 2) {
        day = '0' + day;
    }
    if (hour.length < 2) {
        hour = '0' + hour;
    }

    return [year, month, day].join('-') + ' ' + hour + ':00~';
}

