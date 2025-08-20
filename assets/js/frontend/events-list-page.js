(function($){
  var headerSelector = '.site-header, .tta-header';
  var extraOffset    = 360;

  function getHeaderHeight(){
    return $(headerSelector).first().outerHeight() || 0;
  }

  $(function(){
    $('.tta-scroll-login').on('click', function(e){
      e.preventDefault();
      var target = $('#loginform');
      if(target.length){
        $('html, body').animate({
          scrollTop: target.offset().top - getHeaderHeight() - extraOffset
        }, 600);
      }
    });

    $('.tta-join-friends .tta-accordion-toggle-image-gallery')
      .each(function(){
        var txt = $(this).text().trim();
        $(this).data('readMore', txt || 'View Gallery');
      })
      .on('click', function(){
        var $btn  = $(this),
            $grid = $btn.siblings('.tta-accordion-content').find('.tta-friend-grid');
        if(!$grid.length){
          $grid = $btn.closest('.tta-join-friends').find('.tta-friend-grid');
        }
        if($grid.hasClass('expanded')){
          $grid.removeClass('expanded');
          $btn.text($btn.data('readMore'));
        } else {
          $grid.addClass('expanded');
          $btn.text('Show less');
        }
      });
  });
})(jQuery);
