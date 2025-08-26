jQuery(function($){
  function buildDefaultMessage(){
    var title = $('.tta-event-title').text().trim();
    var date  = $('.tta-event-date').text().trim();
    var time  = $('.tta-event-time').text().trim();
    var msg   = 'Check out this upcoming Trying To Adult event - ' + title;
    if ( date ) {
      msg += ', on ' + date;
    }
    if ( time ) {
      msg += ', at ' + time;
    }
    return msg;
  }

  function openShare(el){
    var $el      = $(el);
    var platform = $el.data('platform');
    var url      = encodeURIComponent($el.data('share-url') || window.location.href);
    var message  = encodeURIComponent($el.data('share-message') || buildDefaultMessage());
    var shareUrl = '';

    if ( platform === 'facebook' ) {
      shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + url + '&quote=' + message;
    } else if ( platform === 'instagram' ) {
      shareUrl = 'https://www.instagram.com/create?caption=' + message + '&url=' + url;
    }

    if ( shareUrl ) {
      window.open(shareUrl, 'ttaShare', 'width=600,height=600');
    }
  }

  $(document).on('click', '.tta-share-link', function(e){
    e.preventDefault();
    openShare(this);
  });
});
