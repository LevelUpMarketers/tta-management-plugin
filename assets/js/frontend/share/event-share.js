jQuery(function($){
  function openShare(platform){
    var url   = encodeURIComponent(window.location.href);
    var title = encodeURIComponent($('.tta-event-title').text());
    var shareUrl = '';
    if(platform === 'facebook'){
      shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + url + '&t=' + title;
    } else if(platform === 'instagram'){
      shareUrl = 'https://www.instagram.com/?url=' + url;
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
