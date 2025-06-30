jQuery(function($){
  var $cal = $('.tta-calendar');
  if(!$cal.length) return;

  function renderGrid(data){
    var $grid = $cal.find('.tta-cal-grid').empty();
    var labels = ['Su','Mo','Tu','We','Th','Fr','Sa'];
    $.each(labels, function(i, lab){
      $grid.append('<div class="tta-cal-label">'+lab+'</div>');
    });
    for(var i=0; i<data.first_wday; i++){
      $grid.append('<div class="tta-cal-day empty"></div>');
    }
    for(var d=1; d<=data.days_in_month; d++){
      var has = data.event_days.indexOf(d)>=0;
      var cls = has ? 'tta-cal-day has-event' : 'tta-cal-day';
      var out = d;
      if(has && data.permalinks[d]){
        out = '<a href="'+data.permalinks[d]+'">'+d+'</a>';
      }
      $grid.append('<div class="'+cls+'">'+out+'</div>');
    }
  }

  function updateNav(data){
    $cal.data('year', data.year);
    $cal.data('month', data.month);
    $cal.find('.tta-cal-current').text(data.month_name+' '+data.year);
    $cal.find('.tta-cal-prev').toggleClass('disabled', !data.prev_allowed);
    $cal.find('.tta-cal-next').toggleClass('disabled', !data.next_allowed);
  }

  function fetchMonth(year, month){
    $.post(ttaCal.ajax_url, {
      action: 'tta_get_calendar_month',
      nonce: ttaCal.nonce,
      year: year,
      month: month
    }, function(resp){
      if(resp && resp.success){
        renderGrid(resp.data);
        updateNav(resp.data);
      }
    });
  }

  $cal.on('click', '.tta-cal-prev:not(.disabled)', function(e){
    e.preventDefault();
    var year = parseInt($cal.data('year')); var month = parseInt($cal.data('month')) - 1;
    if(month<1){ month=12; year--; }
    fetchMonth(year, month);
  });

  $cal.on('click', '.tta-cal-next:not(.disabled)', function(e){
    e.preventDefault();
    var year = parseInt($cal.data('year')); var month = parseInt($cal.data('month')) + 1;
    if(month>12){ month=1; year++; }
    fetchMonth(year, month);
  });
});
