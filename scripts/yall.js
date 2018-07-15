'use strict';

var _extends = Object.assign || function (target) {
    for (var i = 1; i < arguments.length; i++) {
        var source = arguments[i];
        for (var key in source) {
            if (Object.prototype.hasOwnProperty.call(source, key)) {
                target[key] = source[key];
            }
        }
    }
    return target;
};

/**
 * yall.js version 2.0.1
 * Yet Another Lazy loader
 **/

var yallLoad = function yallLoad(element, env) {
    if (element.tagName === 'IMG') {
        var parentElement = element.parentNode;

        if (parentElement.tagName === 'PICTURE') {
            [].slice.call(parentElement.querySelectorAll('source')).forEach(function (source) {
                for (var dataAttribute in source.dataset) {
                    if (env.acceptedDataAttributes.indexOf('data-' + dataAttribute) !== -1) {
                        source.setAttribute(dataAttribute, source.dataset[dataAttribute]);
                        source.removeAttribute('data-' + dataAttribute);
                    }
                }
            });
        }

        var newImageElement = new Image();

        if (typeof element.dataset.srcset !== 'undefined') {
            newImageElement.srcset = element.dataset.srcset;
        }

        newImageElement.src = element.dataset.src;

        if (env.asyncDecodeSupport === true) {
            newImageElement.decode().then(function () {
                for (var i = 0; i < element.attributes.length; i++) {
                    var attrName = element.attributes[i].name;
                    var attrValue = element.attributes[i].value;

                    if (env.ignoredImgAttributes.indexOf(attrName) === -1) {
                        newImageElement.setAttribute(attrName, attrValue);
                    }
                }

                element.replaceWith(newImageElement);
            });
        } else {
            for (var dataAttribute in element.dataset) {
                if (env.acceptedDataAttributes.indexOf('data-' + dataAttribute) !== -1) {
                    element.setAttribute(dataAttribute, element.dataset[dataAttribute]);
                    element.removeAttribute('data-' + dataAttribute);
                }
            }
        }
    }

    if (element.tagName === 'VIDEO') {
        [].slice.call(element.querySelectorAll('source')).forEach(function (source) {
            for (var _dataAttribute in source.dataset) {
                if (env.acceptedDataAttributes.indexOf('data-' + _dataAttribute) !== -1) {
                    source.setAttribute(_dataAttribute, source.dataset[_dataAttribute]);
                    source.removeAttribute('data-' + _dataAttribute);
                }
            }
        });

        element.load();
    }

    if (element.tagName === 'IFRAME') {
        element.src = element.dataset.src;
        element.removeAttribute('data-src');
    }
};

var yall = function yall(userOptions) {
    var env = {
        intersectionObserverSupport: 'IntersectionObserver' in window && 'IntersectionObserverEntry' in window && 'intersectionRatio' in window.IntersectionObserverEntry.prototype,
        mutationObserverSupport: 'MutationObserver' in window,
        idleCallbackSupport: 'requestIdleCallback' in window,
        asyncDecodeSupport: 'decode' in new Image(),
        ignoredImgAttributes: ['data-src', 'data-srcset', 'src', 'srcset'],
        acceptedDataAttributes: ['data-src', 'data-sizes', 'data-media', 'data-srcset'],
        eventsToBind: [[document, 'scroll'], [document, 'touchmove'], [window, 'resize'], [window, 'orientationchange']]
    };

    var options = _extends({
        lazyClass: 'lazy',
        throttleTime: 200,
        idlyLoad: false,
        idleLoadTimeout: 100,
        threshold: 200,
        observeChanges: false,
        observeRootSelector: 'body',
        mutationObserverOptions: {
            childList: true
        }
    }, userOptions);
    var selectorString = 'img.' + options.lazyClass + ',video.' + options.lazyClass + ',iframe.' + options.lazyClass;
    var idleCallbackOptions = {
        timeout: options.idleLoadTimeout
    };

    var lazyElements = [].slice.call(document.querySelectorAll(selectorString));

    if (env.intersectionObserverSupport === true) {
        var intersectionListener = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                var element = entry.target;

                if (entry.isIntersecting === true) {
                    if (options.idlyLoad === true && env.idleCallbackSupport === true) {
                        requestIdleCallback(function () {
                            yallLoad(element, env);
                        }, idleCallbackOptions);
                    } else {
                        yallLoad(element, env);
                    }

                    element.classList.remove(options.lazyClass);
                    observer.unobserve(element);

                    lazyElements = lazyElements.filter(function (lazyElement) {
                        return lazyElement !== element;
                    });
                }
            });
        }, {
            rootMargin: options.threshold + 'px 0%'
        });

        lazyElements.forEach(function (lazyElement) {
            return intersectionListener.observe(lazyElement);
        });
    } else {
        var yallBack = function yallBack() {
            var active = false;

            if (active === false && lazyElements.length > 0) {
                active = true;

                setTimeout(function () {
                    lazyElements.forEach(function (lazyElement) {
                        if (lazyElement.getBoundingClientRect().top <= window.innerHeight + options.threshold && lazyElement.getBoundingClientRect().bottom >= -options.threshold && getComputedStyle(lazyElement).display !== 'none') {
                            if (options.idlyLoad === true && env.idleCallbackSupport === true) {
                                requestIdleCallback(function () {
                                    yallLoad(lazyElement, env);
                                }, idleCallbackOptions);
                            } else {
                                yallLoad(lazyElement, env);
                            }

                            lazyElement.classList.remove(options.lazyClass);

                            lazyElements = lazyElements.filter(function (element) {
                                return element !== lazyElement;
                            });
                        }
                    });

                    active = false;

                    if (lazyElements.length === 0 && options.observeChanges === false) {
                        env.eventsToBind.forEach(function (eventPair) {
                            return eventPair[0].removeEventListener(eventPair[1], yallBack);
                        });
                    }
                }, options.throttleTime);
            }
        };

        env.eventsToBind.forEach(function (eventPair) {
            return eventPair[0].addEventListener(eventPair[1], yallBack);
        });

        yallBack();
    }

    if (env.mutationObserverSupport === true && options.observeChanges === true) {
        var mutationListener = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                [].slice.call(document.querySelectorAll(selectorString)).forEach(function (newElement) {
                    if (lazyElements.indexOf(newElement) === -1) {
                        lazyElements.push(newElement);

                        if (env.intersectionObserverSupport === true) {
                            intersectionListener.observe(newElement);
                        } else {
                            yallBack();
                        }
                    }
                });
            });
        });

        mutationListener.observe(document.querySelector(options.observeRootSelector), options.mutationObserverOptions);
    }
};
