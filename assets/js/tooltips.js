(function(){
    document.addEventListener('DOMContentLoaded', function(){
        if (window.TTA_Tooltips) {
            document.querySelectorAll('[data-ttakey]').forEach(function(el){
                var key = el.getAttribute('data-ttakey');
                if (key && window.TTA_Tooltips[key]) {
                    el.setAttribute('data-tooltip', window.TTA_Tooltips[key]);
                }
            });
        }
    });
})();
