$(window).on('load resize', function() {
  var w = window.innerWidth ? window.innerWidth : $(window).width(),
      h = window.innerHeight ? window.innerHeight : $(window).height(),
      header = $('header').outerHeight(true),
      footer = $('footer').outerHeight(true);

  //600より大きい時は#rightcolumnの内容が少なくても最大にする

  if (w > 600) {
    $('#rightcolumn').css('min-height', h - footer + 'px');
    $('#leftcolumn').css('padding-top', header + 'px');
  } else {
    $('#rightcolumn').css('min-height', 'auto');
    $('#leftcolumn').css('padding-top', '');
  }
});


