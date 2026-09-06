// Projects shuffle animation (GSAP Flip with a no-GSAP fallback) + show-more.
// initShuffle() is re-run after smooth language switches; the shuffleId guard
// stops any loop from a previous run still holding old DOM nodes.
(function() {
    window.initShuffle = function initShuffle() {
        var grid = document.getElementById('projects-grid');
        if (!grid) return;
        window.__shuffleId = (window.__shuffleId || 0) + 1;
        var shuffleId = window.__shuffleId;
        var cards = [];
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var hasGsap = !!(window.gsap && window.Flip);
        var shuffleMobile = !!(window.SITE && window.SITE.shuffleMobile);
        if (window.__shuffleMqBound !== true) {
            window.__shuffleMqBound = true;
            var mq = window.matchMedia('(max-width: 768px)');
            window.__shuffleIsMobile = mq.matches;
            if (mq.addEventListener) {
                mq.addEventListener('change', function(e) {
                    window.__shuffleIsMobile = e.matches;
                    if (!e.matches && window.__shuffleWake) window.__shuffleWake();
                });
            } else if (mq.addListener) {
                mq.addListener(function(e) {
                    window.__shuffleIsMobile = e.matches;
                    if (!e.matches && window.__shuffleWake) window.__shuffleWake();
                });
            }
        }
        var timer = null;
        var busy = false;
        var shuffleOn = true;

        function visible() {
            return cards.filter(function(c) { return !c.classList.contains('hidden'); });
        }
        function swapNodes(a, b) {
            var ra = document.createTextNode('');
            var rb = document.createTextNode('');
            a.replaceWith(ra);
            b.replaceWith(rb);
            ra.replaceWith(b);
            rb.replaceWith(a);
        }
        function cleanup(involved) {
            involved.forEach(function(c) {
                c.style.transition = '';
                c.style.transform = '';
                c.style.filter = '';
                c.style.willChange = '';
                c.style.zIndex = '';
            });
        }
        function motionBlur(tween, involved) {
            tween.eventCallback('onUpdate', function() {
                var p = tween.progress();
                var blur = Math.round(Math.sin(p * Math.PI) * 3);
                for (var i = 0; i < involved.length; i++) {
                    involved[i].style.willChange = 'filter';
                    involved[i].style.filter = 'blur(' + blur + 'px)';
                }
            });
        }
        function run() {
            if (shuffleId !== window.__shuffleId) return;
            if (window.__shuffleIsMobile === true && !shuffleMobile) { schedule(); return; }
            cards = Array.prototype.slice.call(grid.querySelectorAll('.project-card'));
            if (busy) { schedule(); return; }
            var items = visible();
            if (items.length < 2) { schedule(); return; }

            var n = items.length;
            var allowed = [4, 3, 2].filter(function(g) { return g < n; });
            if (allowed.length === 0) { schedule(); return; }
            var gap = allowed[Math.floor(Math.random() * allowed.length)];
            var start = Math.floor(Math.random() * (n - gap));
            var a = items[start];
            var b = items[start + gap];
            var involved = [a, b];

            if (reduceMotion) {
                swapNodes(a, b);
                schedule();
                return;
            }

            if (hasGsap) {
                gsap.killTweensOf(cards);
                cards.forEach(function(c) { gsap.set(c, { clearProps: 'transform,filter,zIndex,willChange,transition' }); });
                busy = true;
                involved.forEach(function(c) { c.style.transition = 'none'; c.style.zIndex = '10'; });
                var state = Flip.getState(involved);
                swapNodes(a, b);
                var flight = Flip.from(state, {
                    duration: 2.4,
                    ease: 'power3.inOut',
                    onComplete: function() {
                        involved.forEach(function(c) { gsap.set(c, { clearProps: 'transform,filter,zIndex,willChange,transition' }); });
                        busy = false;
                    }
                });
                motionBlur(flight, involved);
            } else {
                var ra = a.getBoundingClientRect();
                var rb = b.getBoundingClientRect();
                var dax = ra.left - rb.left;
                var day = ra.top - rb.top;
                busy = true;
                swapNodes(a, b);
                involved.forEach(function(c, k) {
                    c.style.transition = 'none';
                    c.style.transform = 'translate(' + (k === 0 ? dax : -dax) + 'px,' + (k === 0 ? day : -day) + 'px)';
                    c.style.filter = 'blur(3px)';
                    c.style.zIndex = '10';
                });
                void a.offsetWidth;
                involved.forEach(function(c) {
                    c.style.transition = 'transform 1.4s cubic-bezier(0.6, 0, 0.4, 1), filter 1.4s ease';
                    c.style.transform = 'translate(0,0)';
                    c.style.filter = 'blur(0)';
                });
                setTimeout(function() {
                    cleanup(involved);
                    busy = false;
                }, 1500);
            }
            schedule();
        }
        function schedule() {
            if (timer) clearTimeout(timer);
            timer = ((window.__shuffleIsMobile === true && !shuffleMobile) || !shuffleOn) ? null : setTimeout(run, 3500 + Math.random() * 4000);
        }
        window.__shuffleWake = function() { schedule(); };

        var showMoreBtn = document.getElementById('show-more-btn');
        if (showMoreBtn) {
            showMoreBtn.addEventListener('click', function() {
                var hidden = grid.querySelectorAll('.project-card.hidden');
                Array.prototype.forEach.call(hidden, function(c) { c.classList.remove('hidden'); });
                if (showMoreBtn.parentNode) showMoreBtn.parentNode.removeChild(showMoreBtn);
                setTimeout(function() {
                    cards = Array.prototype.slice.call(grid.querySelectorAll('.project-card'));
                    schedule();
                }, 350);
            });
        }

        var shuffleCheck = document.getElementById('shuffleCheck');
        if (shuffleCheck) {
            shuffleCheck.addEventListener('change', function() {
                shuffleOn = shuffleCheck.checked;
                if (shuffleOn) {
                    schedule();
                } else {
                    if (timer) { clearTimeout(timer); timer = null; }
                }
            });
        }

        schedule();
    };
})();
