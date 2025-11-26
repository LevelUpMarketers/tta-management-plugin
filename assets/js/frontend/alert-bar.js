jQuery(function($){
    var data = window.ttaAlertBarData || {};
    var dismissKey = 'ttaPaymentProblemDismissed';

    function isDismissed(){
        try{
            return window.sessionStorage && sessionStorage.getItem(dismissKey) === '1';
        }catch(e){
            return false;
        }
    }

    function setDismissed(){
        try{
            if(window.sessionStorage){
                sessionStorage.setItem(dismissKey,'1');
            }
        }catch(e){}
    }

    function renderPaymentProblem(){
        if(!data.has_payment_problem || isDismissed()){
            return false;
        }
        var $bar = $('<div id="tta-alert-bar" class="tta-alert-bar tta-alert-payment"><span class="tta-alert-message"></span></div>');
        $bar.find('.tta-alert-message').html(data.payment_problem_message||'');
        var label = data.payment_problem_dismiss_label || '';
        if(label){
            var $btn = $('<a class="tta-alert-button" href="#"></a>').text(label);
            $btn.on('click', function(e){
                e.preventDefault();
                setDismissed();
                $bar.remove();
            });
            $bar.append($btn);
        }
        $('body').append($bar);
        return true;
    }

    if(data.is_banned){
        var $bar = $('<div id="tta-alert-bar" class="tta-alert-bar tta-alert-banned"><span class="tta-alert-message"></span></div>');
        $bar.find('.tta-alert-message').html(data.banned_message || '');
        if(data.show_button){
            var $btn = $('<a class="tta-alert-button"></a>').text(data.reentry_label||'').attr('href',data.reentry_url||'#');
            $bar.append($btn);
        }
        $('body').append($bar);
        return;
    }
    var expires = parseInt(data.cart_expires,10) || 0;
    if(!expires){
        renderPaymentProblem();
        return;
    }
    var $bar = $('<div id="tta-alert-bar" class="tta-alert-bar tta-alert-cart"><span class="tta-alert-message">'+(data.cart_message||'')+' <span class="tta-countdown"></span></span><a class="tta-alert-button" href="'+(data.checkout_url||'#')+'">'+(data.checkout_label||'')+'</a></div>');
    $('body').append($bar);
    var $cd = $bar.find('.tta-countdown');
    function update(){
        var diff = expires - Math.floor(Date.now()/1000);
        if(diff <= 0){
            $bar.remove();
            renderPaymentProblem();
            return;
        }
        var m = Math.floor(diff/60);
        var s = diff%60;
        $cd.text(m+':' + (s<10?'0':'') + s);
    }
    update();
    setInterval(update,1000);
});
