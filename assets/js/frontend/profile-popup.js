jQuery(function($){
  function openPopup(url, alt){
    var overlay = $('<div class="tta-popup-overlay"/>');
    var wrap = $('<div class="tta-popup-wrap"/>');
    var img = $('<img/>').attr('src', url).attr('alt', alt);
    wrap.append(img);
    if (alt) {
      wrap.append($('<p class="tta-popup-caption"/>').text(alt));
    }
    overlay.append(wrap);
    $('body').append(overlay);
    overlay.on('click', function(){ overlay.remove(); });
  }

  $(document).on('click', '.tta-popup-img', function(e){
    if (window.innerWidth <= 480) {
      return; // disable on small screens
    }
    e.preventDefault();
    var src = $(this).data('full') || $(this).attr('src');
    var alt = $(this).attr('alt') || '';
    openPopup(src, alt);
  });
});

