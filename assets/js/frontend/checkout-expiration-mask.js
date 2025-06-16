jQuery(function($){
  var $exp = $('#tta-card-exp');
  if ( $exp.length ) {
    $exp.on('input', function(){
      var digits = $exp.val().replace(/[^0-9]/g, '');
      if ( digits.length > 2 ) {
        digits = digits.slice(0,2) + '/' + digits.slice(2,4);
      }
      $exp.val(digits);
    });
  }
});
