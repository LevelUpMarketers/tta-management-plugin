jQuery(function($){
  function mask(el){
    var digits = el.value.replace(/[^0-9]/g,'').slice(0,4);
    if(digits.length>2){
      digits = digits.slice(0,2)+'/'+digits.slice(2);
    }
    el.value = digits;
  }

  $(document).on('input', '.tta-card-exp', function(){
    mask(this);
  });

  $('.tta-card-exp').each(function(){
    mask(this);
  });
});
