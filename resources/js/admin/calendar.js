import { Calendar } from "@fullcalendar/core";
import interactionPlugin from "@fullcalendar/interaction";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from '@fullcalendar/timegrid';
import axios from 'axios';

var calendarEl = document.getElementById("calendar");

let calendar = new Calendar(calendarEl, {
    plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin],
    initialView: "timeGridWeek",
//    noEventsContent: 'スケジュールはありません',
    contentHeight: 'auto',
    nowIndicator: true,
    locale: "ja",
    initialDate: new Date(),
    navLinks: true,
    editable: true,
    dayMaxEvents: true,
    firstDay:0, // 週の始まり
    fixedWeekCount: false,
    allDaySlot: false,
    slotDuration: '00:15', // 15分ごとのslot
    slotLabelInterval: '01:00', // 1時間ごとにラベルを表示
//    html: true,
//    googleCalendarApiKey: 'AIzaSyBQMuWSWslRooXDj9tRzOerlWQTArOfuCA',
    eventSources: [
        {
            googleCalendarApiKey: 'AIzaSyBQMuWSWslRooXDj9tRzOerlWQTArOfuCA',
//            googleCalendarId: 'japanese__ja@holiday.calendar.google.com',
            googleCalendarId: 'ja.japanese#holiday@group.v.calendar.google.com',
            className: 'ja-holidays',
            textColor: 'red',
            rendering: 'background',
            color:"#ffd0d0"
        }
    ],
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
//     select: function (info) {
//         alert("selected " + info.startStr + " to " + info.endStr);

//         // 入力ダイアログ
//         const eventName = prompt("イベントを入力してください");
        
//         if (eventName) {
//             // イベントの追加
//             calendar.addEvent({
//                 title: eventName,// + "aaa\nbb",
//                 start: info.start,
//                 end: info.end,
// //                backgroundColor: color.value,
//                 allDay: false,
// //                html: true,
//             });
//         }
//     },
    select: arg => {
        console.log( arg );
        // 新規
        console.log( arg );
        initEditModal( arg );
    },
    eventClick: arg => {
        // 変更
        console.log( arg );
        initEditModal( arg );
    },
    events: function (info, successCallback, failureCallback) {
        // カレンダー情報取得処理の呼び出し
        axios.post("/api/admin/calendar/fetch", {
                start_date: info.start.valueOf(),
                end_date: info.end.valueOf(),
            })
            .then((response) => {
                let returnData = response.data;
                // 追加したイベントを削除
       //         calendar.removeAllEvents();
                // カレンダーに読み込み
//console.log(returnData.reserve_info);
                if (returnData.reserve_info) {
//                    console.log( returnData.reserve_info );
                    successCallback(returnData.reserve_info);
                }
            })
            .catch(() => {
                // バリデーションエラーなど
                alert("スケジュール取得に失敗しました");
            });
    },
    // dateClick: function (info) {
    //     console.log( '@@info' );
    //     console.log( info );

    //     if (info.dayEl.classList.contains("fc-day-past")) {
    //         alert("選択できません。");
    //         return;
    //     }
    //     //initEditModal( info );
    // },


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
    document.querySelector( '#modal .modal__title' ).remove();
  } else {
    // イベントが取得出来た場合、保存ボタン非表示
    document.querySelector( '#modal .modal__no' ).remove();
    document.querySelector( '#modal .modal__name' ).remove();
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

//alert(arg.start);
//alert(arg.end);

//    const start = modal.querySelector( '#start' );
//    const end = modal.querySelector( '#end' );

    const start = arg.start;
    const end = arg.end;

    const userNo = modal.querySelector( '.user_no' );
    const userName = modal.querySelector( '.user_name' );
    const title = modal.querySelector( '.title' );
//    const color = modal.querySelector( '#color' );
  
    // 保存
    const saveButton = modal.querySelector( '#save' );
    if ( saveButton ) {
        saveButton.addEventListener( 'click', e => {
            e.preventDefault();

      
            if ( arg.event !== undefined ) {
                // 変更時
                //const endStrings = end.value && start.value !== end.value ? end.value.split( '-' ) : start.value.split( '-' );
                //const endDate = new Date( endStrings[0], parseInt( endStrings[1] ) - 1, endStrings[2], 23, 59, 59 );
alert(start);
                arg.event.setStart( start );
                arg.event.setEnd( end );
                arg.event.setProp( 'title', title.value );
            } else {
                // 新規作成時

                // 入力チェック
            　　if (start.valueOf() == '') {
                    alert('開始日時取得エラー');
                    return false;
                }
            　　if (userNo.value == '' && userName.value == '') {
                    alert('No 氏名はいずれか入力必須です');
                    return false;
                }

                // 予約登録(カレンダー用)処理の呼び出し
                axios.post("/api/admin/calendar/add_reserve", {
                        start_date: start.valueOf(),
                        end_date: end.valueOf(),
                        user_no: userNo.value,
                        user_name: userName.value
                    })
                    .then((response) => {
                        let returnData = response.data;

                        if (returnData.result_info.code == 200) {
                            // カレンダーにイベント追加
                            calendar.addEvent( {
                              start: start,
                              end: end,
                              title: returnData.user_info.user_no + ' ' + returnData.user_info.name,
                              user_no: returnData.user_info.user_no,
                              reserve_id: returnData.user_info.reserve_id,
                              backgroundColor: '#4169e1'//color.value
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
                            alert("予約登録に失敗しました");
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

            alert(arg.event.extendedProps.user_no);
            alert(arg.event.extendedProps.reserve_id);

            // 予約キャンセル(カレンダー用)処理の呼び出し
            axios.post("/api/admin/calendar/cancel_reserve", {
                    user_no: arg.event.extendedProps.user_no,
                    reserve_id: arg.event.extendedProps.reserve_id
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
//    const start = modal.querySelector( '#start' );
//    const end = modal.querySelector( '#end' );
  const title = modal.querySelector( '.title' );
//    const color = modal.querySelector( '#color' );
  
  console.log( data );
  if ( data.event !== undefined ) {
//    start.value = /T/.test( data.event.startStr ) ? data.event.startStr.split( 'T' )[0] : data.event.startStr;
//    end.value = /T/.test( data.event.endStr ) ? data.event.endStr.split( 'T' )[0] : data.event.endStr;
    title.value = data.event.title;
//    color.value = data.event.backgroundColor;
  } else {
//    start.value = data.startStr;
    
//    const diffTime = Math.abs( data.end - data.start );
//    const diffDays = Math.ceil( diffTime / ( 1000 * 60 * 60 * 24 ) );
//    if ( 1 < diffDays ) {
    
//      const endDate = data.end;
//      endDate.setDate( endDate.getDate() - 1 );
//      end.value = formatDate( endDate );
//    }
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
