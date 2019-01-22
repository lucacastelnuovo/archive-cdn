"use strict";

function _extends() {
    return (_extends = Object.assign || function(e) {
            for (var t = 1; t < arguments.length; t++) {
                var r = arguments[t];
                for (var a in r) Object.prototype.hasOwnProperty.call(r, a) && (e[a] = r[a])
            }
            return e
        })
        .apply(this, arguments)
}
window.yall = function(e) {
    var a = function(e) {
            if ("IMG" === e.tagName) {
                var t = e.parentNode;
                "PICTURE" === t.tagName && [].slice.call(t.querySelectorAll("source"))
                    .forEach(function(e) {
                        return r(e)
                    }), r(e)
            }
            "VIDEO" === e.tagName && ([].slice.call(e.querySelectorAll("source"))
                .forEach(function(e) {
                    return r(e)
                }), r(e), !0 === e.autoplay && e.load()), "IFRAME" === e.tagName && (e.src = e.dataset.src, e.removeAttribute("data-src")), e.classList.contains(n.lazyBackgroundClass) && (e.classList.remove(n.lazyBackgroundClass), e.classList.add(n.lazyBackgroundLoaded))
        },
        r = function(e) {
            for (var t in e.dataset) - 1 !== o.acceptedDataAttributes.indexOf("data-" + t) && (e.setAttribute(t, e.dataset[t]), e.removeAttribute("data-" + t))
        },
        t = function yallBack() {
            var e = !1;
            !1 === e && 0 < l.length && (e = !0, setTimeout(function() {
                l.forEach(function(t) {
                    t.getBoundingClientRect()
                        .top <= window.innerHeight + n.threshold && t.getBoundingClientRect()
                        .bottom >= -n.threshold && "none" !== getComputedStyle(t)
                        .display && (!0 === n.idlyLoad && !0 === o.idleCallbackSupport ? requestIdleCallback(function() {
                            a(t)
                        }, i) : a(t), t.classList.remove(n.lazyClass), l = l.filter(function(e) {
                            return e !== t
                        }))
                }), e = !1, 0 === l.length && !1 === n.observeChanges && o.eventsToBind.forEach(function(e) {
                    return e[0].removeEventListener(e[1], yallBack)
                })
            }, n.throttleTime))
        },
        o = {
            intersectionObserverSupport: "IntersectionObserver" in window && "IntersectionObserverEntry" in window && "intersectionRatio" in window.IntersectionObserverEntry.prototype,
            mutationObserverSupport: "MutationObserver" in window,
            idleCallbackSupport: "requestIdleCallback" in window,
            ignoredImgAttributes: ["data-src", "data-sizes", "data-media", "data-srcset", "src", "srcset"],
            acceptedDataAttributes: ["data-src", "data-sizes", "data-media", "data-srcset", "data-poster"],
            eventsToBind: [
                [document, "scroll"],
                [document, "touchmove"],
                [window, "resize"],
                [window, "orientationchange"]
            ]
        },
        n = _extends({
            lazyClass: "lazy",
            lazyBackgroundClass: "lazy-bg",
            lazyBackgroundLoaded: "lazy-bg-loaded",
            throttleTime: 200,
            idlyLoad: !1,
            idleLoadTimeout: 100,
            threshold: 200,
            observeChanges: !1,
            observeRootSelector: "body",
            mutationObserverOptions: {
                childList: !0
            }
        }, e),
        s = "img." + n.lazyClass + ",video." + n.lazyClass + ",iframe." + n.lazyClass + ",." + n.lazyBackgroundClass,
        i = {
            timeout: n.idleLoadTimeout
        },
        l = [].slice.call(document.querySelectorAll(s));
    if (!0 === o.intersectionObserverSupport) {
        var c = new IntersectionObserver(function(e, r) {
            e.forEach(function(e) {
                if (!0 === e.isIntersecting || 0 < e.intersectionRatio) {
                    var t = e.target;
                    !0 === n.idlyLoad && !0 === o.idleCallbackSupport ? requestIdleCallback(function() {
                        return a(t)
                    }, i) : a(t), t.classList.remove(n.lazyClass), r.unobserve(t), l = l.filter(function(e) {
                        return e !== t
                    })
                }
            })
        }, {
            rootMargin: n.threshold + "px 0%"
        });
        l.forEach(function(e) {
            return c.observe(e)
        })
    } else o.eventsToBind.forEach(function(e) {
        return e[0].addEventListener(e[1], t)
    }), t();
    !0 === o.mutationObserverSupport && !0 === n.observeChanges && new MutationObserver(function(e) {
            return e.forEach(function() {
                [].slice.call(document.querySelectorAll(s))
                    .forEach(function(e) {
                        -1 === l.indexOf(e) && (l.push(e), !0 === o.intersectionObserverSupport ? c.observe(e) : t())
                    })
            })
        })
        .observe(document.querySelector(n.observeRootSelector), n.mutationObserverOptions)
};
