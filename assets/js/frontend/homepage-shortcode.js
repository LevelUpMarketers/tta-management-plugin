(function($){
    function animateCounter($el){
        var target = parseInt($el.data('target'),10);
        var duration = 5000;
        var startTime = null;
        function step(timestamp){
            if(!startTime){ startTime = timestamp; }
            var progress = Math.min((timestamp - startTime)/duration,1);
            var eased = 1 - Math.pow(1 - progress, 3); // ease out
            var value = Math.floor(eased * target);
            $el.text(value.toLocaleString());
            if(progress < 1){
                window.requestAnimationFrame(step);
            } else {
                $el.text(target.toLocaleString());
            }
        }
        window.requestAnimationFrame(step);
    }
    $(function(){
        $('.tta-counter').each(function(){
            animateCounter($(this));
        });

        var $carousel = $('.tta-intro-img');
        var $imgs = $carousel.find('img');
        if ($imgs.length > 1) {
            var idx = 0;
            function cycle(){
                var $current = $imgs.eq(idx);
                idx = (idx + 1) % $imgs.length;
                var $next = $imgs.eq(idx);
                $current.removeClass('active').addClass('exit');
                $next.addClass('active');
                setTimeout(function(){
                    $current.removeClass('exit');
                }, 1000);
                setTimeout(cycle, 5000);
            }
            setTimeout(cycle, 5000);
        }

        var $cd = $('.tta-next-event__countdown .tta-countdown');
        if ($cd.length) {
            var target = parseInt($cd.data('time'), 10) * 1000;
            function plural(v, s, p){ return v + ' ' + (v === 1 ? s : p); }
            function update(){
                var now = Date.now();
                var diff = Math.max(0, target - now);
                var days = Math.floor(diff / 86400000);
                var hours = Math.floor((diff % 86400000) / 3600000);
                var mins = Math.floor((diff % 3600000) / 60000);
                var secs = Math.floor((diff % 60000) / 1000);
                $cd.text(plural(days,'day','days') + ', ' + plural(hours,'hour','hours') + ', ' + plural(mins,'minute','minutes') + ', ' + plural(secs,'second','seconds'));
            }
            update();
            setInterval(update, 1000);
        }
    });
})(jQuery);
