jQuery(function($){
  function buildMessage(){
    var title = $('.tta-event-title').text().trim();
    var date  = $('.tta-event-date').text().trim();
    var time  = $('.tta-event-time').text().trim();
    var venue = $('.tta-event-details-icon-after strong:contains("Venue")').next('a').text().trim();
    var msg = 'Check out this upcoming Trying to Adult event I\'m attending! ' + title;
    if(date) msg += ' - ' + date;
    if(time) msg += ' at ' + time;
    if(venue) msg += ' at ' + venue;
    return encodeURIComponent(msg);
  }

  function openShare(platform){
    var url   = encodeURIComponent(window.location.href);
    var message = buildMessage();
    var shareUrl = '';
    if(platform === 'facebook'){
      shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + url + '&quote=' + message;
    } else if(platform === 'instagram'){
      shareUrl = 'https://www.instagram.com/create?caption=' + message + '&url=' + url;
    }
    if(shareUrl){
      window.open(shareUrl, 'ttaShare', 'width=600,height=600');
    }
  }
  $(document).on('click', '.tta-share-link', function(e){
    e.preventDefault();
    openShare($(this).data('platform'));
  });
});
