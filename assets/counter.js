(function (win, doc) {
    counter = win.counter || {}
    counter.seo = {
        charCountHandler: function(target) {
            $target = $(target);
            let $helpBlock = $target.next('.help-block');
            let min = $target.data('min');
            let max = $target.data('max');
            let count = $target.val().replace(/[{}]/g, "").length;
            $helpBlock.html(`Symbols: <b>${count}</b> of ${min} - ${max} optimal`);
            let $number = $helpBlock.find('b');
            if (count < max && count > min) {
                $number.css({color: 'lime'});
            } else if(count < min) {
              	$number.css({color: 'darkred'});
            } else {
              	$number.css({color: 'coral'});
            }
        }
    } 
    var listeners = [], 
        doc = win.document, 
        MutationObserver = win.MutationObserver || win.WebKitMutationObserver,
        observer;
    
    const ready = (selector, fn) => {
        listeners.push({
            selector: selector,
            fn: fn
        });
        if (!observer) {
            observer = new MutationObserver(check);
            observer.observe(doc.documentElement, {
                childList: true,
                subtree: true
            });
        }
        check();
    }
    
    const check = () => {
        for (var i = 0, len = listeners.length, listener, elements; i < len; i++) {
        listener = listeners[i];
        elements = doc.querySelectorAll(listener.selector);
        for (var j = 0, jLen = elements.length, element; j < jLen; j++) {
            element = elements[j];
            if (!element.ready) {
                element.ready = true;
                listener.fn.call(element, element);
            }
        }
        }
    }
    win.ready = ready;
    win.counter = counter;
    win.ready('[data-counter]', (el) => {
        counter.seo.charCountHandler(el)
        el.oninput = event => counter.seo.charCountHandler(el);
    });
})(window, document);
