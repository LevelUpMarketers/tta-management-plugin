jQuery(function($){
  var $exp = $('#tta-card-exp');

  function formatExp() {
    var digits = $exp.val().replace(/[^0-9]/g, '').slice(0,4);
    if ( digits.length > 2 ) {
      digits = digits.slice(0,2) + '/' + digits.slice(2);
    }
    $exp.val(digits);
  }

  if ( $exp.length ) {
    formatExp();
    $exp.on('input', formatExp);
  }
});
