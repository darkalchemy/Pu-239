(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], function (a0) {
            return factory(a0);
        });
    } else if (typeof exports === 'object') {
        module.exports = factory(require('jquery'));
    } else {
        factory(jQuery);
    }
})(this, function ($) {
    var defaults = {
        animation: 'fade',
        animationDuration: 350,
        content: null,
        contentAsHTML: false,
        contentCloning: false,
        debug: true,
        delay: 300,
        delayTouch: [300, 500],
        functionInit: null,
        functionBefore: null,
        functionReady: null,
        functionAfter: null,
        functionFormat: null,
        IEmin: 6,
        interactive: false,
        multiple: false,
        parent: null,
        plugins: ['sideTip'],
        repositionOnScroll: false,
        restoration: 'none',
        selfDestruction: true,
        theme: [],
        timer: 0,
        trackerInterval: 500,
        trackOrigin: false,
        trackTooltip: false,
        trigger: 'hover',
        triggerClose: {
            click: false,
            mouseleave: false,
            originClick: false,
            scroll: false,
            tap: false,
            touchleave: false
        },
        triggerOpen: {
            click: false,
            mouseenter: false,
            tap: false,
            touchstart: false
        },
        updateAnimation: 'rotate',
        zIndex: 9999999
    }, win = typeof window != 'undefined' ? window : null, env = {
        hasTouchCapability: !!(win && ('ontouchstart' in win || win.DocumentTouch && win.document instanceof win.DocumentTouch || win.navigator.maxTouchPoints)),
        hasTransitions: transitionSupport(),
        IE: false,
        semVer: '4.2.5',
        window: win
    }, core = function () {
        this.__$emitterPrivate = $({});
        this.__$emitterPublic = $({});
        this.__instancesLatestArr = [];
        this.__plugins = {};
        this._env = env;
    };
    core.prototype = {
        __bridge: function (constructor, obj, pluginName) {
            if (!obj[pluginName]) {
                var fn = function () {
                };
                fn.prototype = constructor;
                var pluginInstance = new fn();
                if (pluginInstance.__init) {
                    pluginInstance.__init(obj);
                }
                $.each(constructor, function (methodName, fn) {
                    if (methodName.indexOf('__') != 0) {
                        if (!obj[methodName]) {
                            obj[methodName] = function () {
                                return pluginInstance[methodName].apply(pluginInstance, Array.prototype.slice.apply(arguments));
                            };
                            obj[methodName].bridged = pluginInstance;
                        } else if (defaults.debug) {
                            console.log('The ' + methodName + ' method of the ' + pluginName + ' plugin conflicts with another plugin or native methods');
                        }
                    }
                });
                obj[pluginName] = pluginInstance;
            }
            return this;
        },
        __setWindow: function (window) {
            env.window = window;
            return this;
        },
        _getRuler: function ($tooltip) {
            return new Ruler($tooltip);
        },
        _off: function () {
            this.__$emitterPrivate.off.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _on: function () {
            this.__$emitterPrivate.on.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _one: function () {
            this.__$emitterPrivate.one.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _plugin: function (plugin) {
            var self = this;
            if (typeof plugin == 'string') {
                var pluginName = plugin, p = null;
                if (pluginName.indexOf('.') > 0) {
                    p = self.__plugins[pluginName];
                } else {
                    $.each(self.__plugins, function (i, plugin) {
                        if (plugin.name.substring(plugin.name.length - pluginName.length - 1) == '.' + pluginName) {
                            p = plugin;
                            return false;
                        }
                    });
                }
                return p;
            } else {
                if (plugin.name.indexOf('.') < 0) {
                    throw new Error('Plugins must be namespaced');
                }
                self.__plugins[plugin.name] = plugin;
                if (plugin.core) {
                    self.__bridge(plugin.core, self, plugin.name);
                }
                return this;
            }
        },
        _trigger: function () {
            var args = Array.prototype.slice.apply(arguments);
            if (typeof args[0] == 'string') {
                args[0] = {
                    type: args[0]
                };
            }
            this.__$emitterPrivate.trigger.apply(this.__$emitterPrivate, args);
            this.__$emitterPublic.trigger.apply(this.__$emitterPublic, args);
            return this;
        },
        instances: function (selector) {
            var instances = [], sel = selector || '.tooltipstered';
            $(sel).each(function () {
                var $this = $(this), ns = $this.data('tooltipster-ns');
                if (ns) {
                    $.each(ns, function (i, namespace) {
                        instances.push($this.data(namespace));
                    });
                }
            });
            return instances;
        },
        instancesLatest: function () {
            return this.__instancesLatestArr;
        },
        off: function () {
            this.__$emitterPublic.off.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            return this;
        },
        on: function () {
            this.__$emitterPublic.on.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            return this;
        },
        one: function () {
            this.__$emitterPublic.one.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            return this;
        },
        origins: function (selector) {
            var sel = selector ? selector + ' ' : '';
            return $(sel + '.tooltipstered').toArray();
        },
        setDefaults: function (d) {
            $.extend(defaults, d);
            return this;
        },
        triggerHandler: function () {
            this.__$emitterPublic.triggerHandler.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            return this;
        }
    };
    $.tooltipster = new core();
    $.Tooltipster = function (element, options) {
        this.__callbacks = {
            close: [],
            open: []
        };
        this.__closingTime;
        this.__Content;
        this.__contentBcr;
        this.__destroyed = false;
        this.__$emitterPrivate = $({});
        this.__$emitterPublic = $({});
        this.__enabled = true;
        this.__garbageCollector;
        this.__Geometry;
        this.__lastPosition;
        this.__namespace = 'tooltipster-' + Math.round(Math.random() * 1e6);
        this.__options;
        this.__$originParents;
        this.__pointerIsOverOrigin = false;
        this.__previousThemes = [];
        this.__state = 'closed';
        this.__timeouts = {
            close: [],
            open: null
        };
        this.__touchEvents = [];
        this.__tracker = null;
        this._$origin;
        this._$tooltip;
        this.__init(element, options);
    };
    $.Tooltipster.prototype = {
        __init: function (origin, options) {
            var self = this;
            self._$origin = $(origin);
            self.__options = $.extend(true, {}, defaults, options);
            self.__optionsFormat();
            if (!env.IE || env.IE >= self.__options.IEmin) {
                var initialTitle = null;
                if (self._$origin.data('tooltipster-initialTitle') === undefined) {
                    initialTitle = self._$origin.attr('title');
                    if (initialTitle === undefined) initialTitle = null;
                    self._$origin.data('tooltipster-initialTitle', initialTitle);
                }
                if (self.__options.content !== null) {
                    self.__contentSet(self.__options.content);
                } else {
                    var selector = self._$origin.attr('data-tooltip-content'), $el;
                    if (selector) {
                        $el = $(selector);
                    }
                    if ($el && $el[0]) {
                        self.__contentSet($el.first());
                    } else {
                        self.__contentSet(initialTitle);
                    }
                }
                self._$origin.removeAttr('title').addClass('tooltipstered');
                self.__prepareOrigin();
                self.__prepareGC();
                $.each(self.__options.plugins, function (i, pluginName) {
                    self._plug(pluginName);
                });
                if (env.hasTouchCapability) {
                    $(env.window.document.body).on('touchmove.' + self.__namespace + '-triggerOpen', function (event) {
                        self._touchRecordEvent(event);
                    });
                }
                self._on('created', function () {
                    self.__prepareTooltip();
                })._on('repositioned', function (e) {
                    self.__lastPosition = e.position;
                });
            } else {
                self.__options.disabled = true;
            }
        },
        __contentInsert: function () {
            var self = this, $el = self._$tooltip.find('.tooltipster-content'), formattedContent = self.__Content,
                format = function (content) {
                    formattedContent = content;
                };
            self._trigger({
                type: 'format',
                content: self.__Content,
                format: format
            });
            if (self.__options.functionFormat) {
                formattedContent = self.__options.functionFormat.call(self, self, {
                    origin: self._$origin[0]
                }, self.__Content);
            }
            if (typeof formattedContent === 'string' && !self.__options.contentAsHTML) {
                $el.text(formattedContent);
            } else {
                $el.empty().append(formattedContent);
            }
            return self;
        },
        __contentSet: function (content) {
            if (content instanceof $ && this.__options.contentCloning) {
                content = content.clone(true);
            }
            this.__Content = content;
            this._trigger({
                type: 'updated',
                content: content
            });
            return this;
        },
        __destroyError: function () {
            throw new Error('This tooltip has been destroyed and cannot execute your method call.');
        },
        __geometry: function () {
            var self = this, $target = self._$origin, originIsArea = self._$origin.is('area');
            if (originIsArea) {
                var mapName = self._$origin.parent().attr('name');
                $target = $('img[usemap="#' + mapName + '"]');
            }
            var bcr = $target[0].getBoundingClientRect(), $document = $(env.window.document), $window = $(env.window),
                $parent = $target, geo = {
                    available: {
                        document: null,
                        window: null
                    },
                    document: {
                        size: {
                            height: $document.height(),
                            width: $document.width()
                        }
                    },
                    window: {
                        scroll: {
                            left: env.window.scrollX || env.window.document.documentElement.scrollLeft,
                            top: env.window.scrollY || env.window.document.documentElement.scrollTop
                        },
                        size: {
                            height: $window.height(),
                            width: $window.width()
                        }
                    },
                    origin: {
                        fixedLineage: false,
                        offset: {},
                        size: {
                            height: bcr.bottom - bcr.top,
                            width: bcr.right - bcr.left
                        },
                        usemapImage: originIsArea ? $target[0] : null,
                        windowOffset: {
                            bottom: bcr.bottom,
                            left: bcr.left,
                            right: bcr.right,
                            top: bcr.top
                        }
                    }
                }, geoFixed = false;
            if (originIsArea) {
                var shape = self._$origin.attr('shape'), coords = self._$origin.attr('coords');
                if (coords) {
                    coords = coords.split(',');
                    $.map(coords, function (val, i) {
                        coords[i] = parseInt(val);
                    });
                }
                if (shape != 'default') {
                    switch (shape) {
                        case 'circle':
                            var circleCenterLeft = coords[0], circleCenterTop = coords[1], circleRadius = coords[2],
                                areaTopOffset = circleCenterTop - circleRadius,
                                areaLeftOffset = circleCenterLeft - circleRadius;
                            geo.origin.size.height = circleRadius * 2;
                            geo.origin.size.width = geo.origin.size.height;
                            geo.origin.windowOffset.left += areaLeftOffset;
                            geo.origin.windowOffset.top += areaTopOffset;
                            break;

                        case 'rect':
                            var areaLeft = coords[0], areaTop = coords[1], areaRight = coords[2],
                                areaBottom = coords[3];
                            geo.origin.size.height = areaBottom - areaTop;
                            geo.origin.size.width = areaRight - areaLeft;
                            geo.origin.windowOffset.left += areaLeft;
                            geo.origin.windowOffset.top += areaTop;
                            break;

                        case 'poly':
                            var areaSmallestX = 0, areaSmallestY = 0, areaGreatestX = 0, areaGreatestY = 0,
                                arrayAlternate = 'even';
                            for (var i = 0; i < coords.length; i++) {
                                var areaNumber = coords[i];
                                if (arrayAlternate == 'even') {
                                    if (areaNumber > areaGreatestX) {
                                        areaGreatestX = areaNumber;
                                        if (i === 0) {
                                            areaSmallestX = areaGreatestX;
                                        }
                                    }
                                    if (areaNumber < areaSmallestX) {
                                        areaSmallestX = areaNumber;
                                    }
                                    arrayAlternate = 'odd';
                                } else {
                                    if (areaNumber > areaGreatestY) {
                                        areaGreatestY = areaNumber;
                                        if (i == 1) {
                                            areaSmallestY = areaGreatestY;
                                        }
                                    }
                                    if (areaNumber < areaSmallestY) {
                                        areaSmallestY = areaNumber;
                                    }
                                    arrayAlternate = 'even';
                                }
                            }
                            geo.origin.size.height = areaGreatestY - areaSmallestY;
                            geo.origin.size.width = areaGreatestX - areaSmallestX;
                            geo.origin.windowOffset.left += areaSmallestX;
                            geo.origin.windowOffset.top += areaSmallestY;
                            break;
                    }
                }
            }
            var edit = function (r) {
                geo.origin.size.height = r.height, geo.origin.windowOffset.left = r.left, geo.origin.windowOffset.top = r.top,
                    geo.origin.size.width = r.width;
            };
            self._trigger({
                type: 'geometry',
                edit: edit,
                geometry: {
                    height: geo.origin.size.height,
                    left: geo.origin.windowOffset.left,
                    top: geo.origin.windowOffset.top,
                    width: geo.origin.size.width
                }
            });
            geo.origin.windowOffset.right = geo.origin.windowOffset.left + geo.origin.size.width;
            geo.origin.windowOffset.bottom = geo.origin.windowOffset.top + geo.origin.size.height;
            geo.origin.offset.left = geo.origin.windowOffset.left + geo.window.scroll.left;
            geo.origin.offset.top = geo.origin.windowOffset.top + geo.window.scroll.top;
            geo.origin.offset.bottom = geo.origin.offset.top + geo.origin.size.height;
            geo.origin.offset.right = geo.origin.offset.left + geo.origin.size.width;
            geo.available.document = {
                bottom: {
                    height: geo.document.size.height - geo.origin.offset.bottom,
                    width: geo.document.size.width
                },
                left: {
                    height: geo.document.size.height,
                    width: geo.origin.offset.left
                },
                right: {
                    height: geo.document.size.height,
                    width: geo.document.size.width - geo.origin.offset.right
                },
                top: {
                    height: geo.origin.offset.top,
                    width: geo.document.size.width
                }
            };
            geo.available.window = {
                bottom: {
                    height: Math.max(geo.window.size.height - Math.max(geo.origin.windowOffset.bottom, 0), 0),
                    width: geo.window.size.width
                },
                left: {
                    height: geo.window.size.height,
                    width: Math.max(geo.origin.windowOffset.left, 0)
                },
                right: {
                    height: geo.window.size.height,
                    width: Math.max(geo.window.size.width - Math.max(geo.origin.windowOffset.right, 0), 0)
                },
                top: {
                    height: Math.max(geo.origin.windowOffset.top, 0),
                    width: geo.window.size.width
                }
            };
            while ($parent[0].tagName.toLowerCase() != 'html') {
                if ($parent.css('position') == 'fixed') {
                    geo.origin.fixedLineage = true;
                    break;
                }
                $parent = $parent.parent();
            }
            return geo;
        },
        __optionsFormat: function () {
            if (typeof this.__options.animationDuration == 'number') {
                this.__options.animationDuration = [this.__options.animationDuration, this.__options.animationDuration];
            }
            if (typeof this.__options.delay == 'number') {
                this.__options.delay = [this.__options.delay, this.__options.delay];
            }
            if (typeof this.__options.delayTouch == 'number') {
                this.__options.delayTouch = [this.__options.delayTouch, this.__options.delayTouch];
            }
            if (typeof this.__options.theme == 'string') {
                this.__options.theme = [this.__options.theme];
            }
            if (this.__options.parent === null) {
                this.__options.parent = $(env.window.document.body);
            } else if (typeof this.__options.parent == 'string') {
                this.__options.parent = $(this.__options.parent);
            }
            if (this.__options.trigger == 'hover') {
                this.__options.triggerOpen = {
                    mouseenter: true,
                    touchstart: true
                };
                this.__options.triggerClose = {
                    mouseleave: true,
                    originClick: true,
                    touchleave: true
                };
            } else if (this.__options.trigger == 'click') {
                this.__options.triggerOpen = {
                    click: true,
                    tap: true
                };
                this.__options.triggerClose = {
                    click: true,
                    tap: true
                };
            }
            this._trigger('options');
            return this;
        },
        __prepareGC: function () {
            var self = this;
            if (self.__options.selfDestruction) {
                self.__garbageCollector = setInterval(function () {
                    var now = new Date().getTime();
                    self.__touchEvents = $.grep(self.__touchEvents, function (event, i) {
                        return now - event.time > 6e4;
                    });
                    if (!bodyContains(self._$origin)) {
                        self.close(function () {
                            self.destroy();
                        });
                    }
                }, 2e4);
            } else {
                clearInterval(self.__garbageCollector);
            }
            return self;
        },
        __prepareOrigin: function () {
            var self = this;
            self._$origin.off('.' + self.__namespace + '-triggerOpen');
            if (env.hasTouchCapability) {
                self._$origin.on('touchstart.' + self.__namespace + '-triggerOpen ' + 'touchend.' + self.__namespace + '-triggerOpen ' + 'touchcancel.' + self.__namespace + '-triggerOpen', function (event) {
                    self._touchRecordEvent(event);
                });
            }
            if (self.__options.triggerOpen.click || self.__options.triggerOpen.tap && env.hasTouchCapability) {
                var eventNames = '';
                if (self.__options.triggerOpen.click) {
                    eventNames += 'click.' + self.__namespace + '-triggerOpen ';
                }
                if (self.__options.triggerOpen.tap && env.hasTouchCapability) {
                    eventNames += 'touchend.' + self.__namespace + '-triggerOpen';
                }
                self._$origin.on(eventNames, function (event) {
                    if (self._touchIsMeaningfulEvent(event)) {
                        self._open(event);
                    }
                });
            }
            if (self.__options.triggerOpen.mouseenter || self.__options.triggerOpen.touchstart && env.hasTouchCapability) {
                var eventNames = '';
                if (self.__options.triggerOpen.mouseenter) {
                    eventNames += 'mouseenter.' + self.__namespace + '-triggerOpen ';
                }
                if (self.__options.triggerOpen.touchstart && env.hasTouchCapability) {
                    eventNames += 'touchstart.' + self.__namespace + '-triggerOpen';
                }
                self._$origin.on(eventNames, function (event) {
                    if (self._touchIsTouchEvent(event) || !self._touchIsEmulatedEvent(event)) {
                        self.__pointerIsOverOrigin = true;
                        self._openShortly(event);
                    }
                });
            }
            if (self.__options.triggerClose.mouseleave || self.__options.triggerClose.touchleave && env.hasTouchCapability) {
                var eventNames = '';
                if (self.__options.triggerClose.mouseleave) {
                    eventNames += 'mouseleave.' + self.__namespace + '-triggerOpen ';
                }
                if (self.__options.triggerClose.touchleave && env.hasTouchCapability) {
                    eventNames += 'touchend.' + self.__namespace + '-triggerOpen touchcancel.' + self.__namespace + '-triggerOpen';
                }
                self._$origin.on(eventNames, function (event) {
                    if (self._touchIsMeaningfulEvent(event)) {
                        self.__pointerIsOverOrigin = false;
                    }
                });
            }
            return self;
        },
        __prepareTooltip: function () {
            var self = this, p = self.__options.interactive ? 'auto' : '';
            self._$tooltip.attr('id', self.__namespace).css({
                'pointer-events': p,
                zIndex: self.__options.zIndex
            });
            $.each(self.__previousThemes, function (i, theme) {
                self._$tooltip.removeClass(theme);
            });
            $.each(self.__options.theme, function (i, theme) {
                self._$tooltip.addClass(theme);
            });
            self.__previousThemes = $.merge([], self.__options.theme);
            return self;
        },
        __scrollHandler: function (event) {
            var self = this;
            if (self.__options.triggerClose.scroll) {
                self._close(event);
            } else {
                if (bodyContains(self._$origin) && bodyContains(self._$tooltip)) {
                    var geo = null;
                    if (event.target === env.window.document) {
                        if (!self.__Geometry.origin.fixedLineage) {
                            if (self.__options.repositionOnScroll) {
                                self.reposition(event);
                            }
                        }
                    } else {
                        geo = self.__geometry();
                        var overflows = false;
                        if (self._$origin.css('position') != 'fixed') {
                            self.__$originParents.each(function (i, el) {
                                var $el = $(el), overflowX = $el.css('overflow-x'), overflowY = $el.css('overflow-y');
                                if (overflowX != 'visible' || overflowY != 'visible') {
                                    var bcr = el.getBoundingClientRect();
                                    if (overflowX != 'visible') {
                                        if (geo.origin.windowOffset.left < bcr.left || geo.origin.windowOffset.right > bcr.right) {
                                            overflows = true;
                                            return false;
                                        }
                                    }
                                    if (overflowY != 'visible') {
                                        if (geo.origin.windowOffset.top < bcr.top || geo.origin.windowOffset.bottom > bcr.bottom) {
                                            overflows = true;
                                            return false;
                                        }
                                    }
                                }
                                if ($el.css('position') == 'fixed') {
                                    return false;
                                }
                            });
                        }
                        if (overflows) {
                            self._$tooltip.css('visibility', 'hidden');
                        } else {
                            self._$tooltip.css('visibility', 'visible');
                            if (self.__options.repositionOnScroll) {
                                self.reposition(event);
                            } else {
                                var offsetLeft = geo.origin.offset.left - self.__Geometry.origin.offset.left,
                                    offsetTop = geo.origin.offset.top - self.__Geometry.origin.offset.top;
                                self._$tooltip.css({
                                    left: self.__lastPosition.coord.left + offsetLeft,
                                    top: self.__lastPosition.coord.top + offsetTop
                                });
                            }
                        }
                    }
                    self._trigger({
                        type: 'scroll',
                        event: event,
                        geo: geo
                    });
                }
            }
            return self;
        },
        __stateSet: function (state) {
            this.__state = state;
            this._trigger({
                type: 'state',
                state: state
            });
            return this;
        },
        __timeoutsClear: function () {
            clearTimeout(this.__timeouts.open);
            this.__timeouts.open = null;
            $.each(this.__timeouts.close, function (i, timeout) {
                clearTimeout(timeout);
            });
            this.__timeouts.close = [];
            return this;
        },
        __trackerStart: function () {
            var self = this, $content = self._$tooltip.find('.tooltipster-content');
            if (self.__options.trackTooltip) {
                self.__contentBcr = $content[0].getBoundingClientRect();
            }
            self.__tracker = setInterval(function () {
                if (!bodyContains(self._$origin) || !bodyContains(self._$tooltip)) {
                    self._close();
                } else {
                    if (self.__options.trackOrigin) {
                        var g = self.__geometry(), identical = false;
                        if (areEqual(g.origin.size, self.__Geometry.origin.size)) {
                            if (self.__Geometry.origin.fixedLineage) {
                                if (areEqual(g.origin.windowOffset, self.__Geometry.origin.windowOffset)) {
                                    identical = true;
                                }
                            } else {
                                if (areEqual(g.origin.offset, self.__Geometry.origin.offset)) {
                                    identical = true;
                                }
                            }
                        }
                        if (!identical) {
                            if (self.__options.triggerClose.mouseleave) {
                                self._close();
                            } else {
                                self.reposition();
                            }
                        }
                    }
                    if (self.__options.trackTooltip) {
                        var currentBcr = $content[0].getBoundingClientRect();
                        if (currentBcr.height !== self.__contentBcr.height || currentBcr.width !== self.__contentBcr.width) {
                            self.reposition();
                            self.__contentBcr = currentBcr;
                        }
                    }
                }
            }, self.__options.trackerInterval);
            return self;
        },
        _close: function (event, callback, force) {
            var self = this, ok = true;
            self._trigger({
                type: 'close',
                event: event,
                stop: function () {
                    ok = false;
                }
            });
            if (ok || force) {
                if (callback) self.__callbacks.close.push(callback);
                self.__callbacks.open = [];
                self.__timeoutsClear();
                var finishCallbacks = function () {
                    $.each(self.__callbacks.close, function (i, c) {
                        c.call(self, self, {
                            event: event,
                            origin: self._$origin[0]
                        });
                    });
                    self.__callbacks.close = [];
                };
                if (self.__state != 'closed') {
                    var necessary = true, d = new Date(), now = d.getTime(),
                        newClosingTime = now + self.__options.animationDuration[1];
                    if (self.__state == 'disappearing') {
                        if (newClosingTime > self.__closingTime && self.__options.animationDuration[1] > 0) {
                            necessary = false;
                        }
                    }
                    if (necessary) {
                        self.__closingTime = newClosingTime;
                        if (self.__state != 'disappearing') {
                            self.__stateSet('disappearing');
                        }
                        var finish = function () {
                            clearInterval(self.__tracker);
                            self._trigger({
                                type: 'closing',
                                event: event
                            });
                            self._$tooltip.off('.' + self.__namespace + '-triggerClose').removeClass('tooltipster-dying');
                            $(env.window).off('.' + self.__namespace + '-triggerClose');
                            self.__$originParents.each(function (i, el) {
                                $(el).off('scroll.' + self.__namespace + '-triggerClose');
                            });
                            self.__$originParents = null;
                            $(env.window.document.body).off('.' + self.__namespace + '-triggerClose');
                            self._$origin.off('.' + self.__namespace + '-triggerClose');
                            self._off('dismissable');
                            self.__stateSet('closed');
                            self._trigger({
                                type: 'after',
                                event: event
                            });
                            if (self.__options.functionAfter) {
                                self.__options.functionAfter.call(self, self, {
                                    event: event,
                                    origin: self._$origin[0]
                                });
                            }
                            finishCallbacks();
                        };
                        if (env.hasTransitions) {
                            self._$tooltip.css({
                                '-moz-animation-duration': self.__options.animationDuration[1] + 'ms',
                                '-ms-animation-duration': self.__options.animationDuration[1] + 'ms',
                                '-o-animation-duration': self.__options.animationDuration[1] + 'ms',
                                '-webkit-animation-duration': self.__options.animationDuration[1] + 'ms',
                                'animation-duration': self.__options.animationDuration[1] + 'ms',
                                'transition-duration': self.__options.animationDuration[1] + 'ms'
                            });
                            self._$tooltip.clearQueue().removeClass('tooltipster-show').addClass('tooltipster-dying');
                            if (self.__options.animationDuration[1] > 0) {
                                self._$tooltip.delay(self.__options.animationDuration[1]);
                            }
                            self._$tooltip.queue(finish);
                        } else {
                            self._$tooltip.stop().fadeOut(self.__options.animationDuration[1], finish);
                        }
                    }
                } else {
                    finishCallbacks();
                }
            }
            return self;
        },
        _off: function () {
            this.__$emitterPrivate.off.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _on: function () {
            this.__$emitterPrivate.on.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _one: function () {
            this.__$emitterPrivate.one.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _open: function (event, callback) {
            var self = this;
            if (!self.__destroying) {
                if (bodyContains(self._$origin) && self.__enabled) {
                    var ok = true;
                    if (self.__state == 'closed') {
                        self._trigger({
                            type: 'before',
                            event: event,
                            stop: function () {
                                ok = false;
                            }
                        });
                        if (ok && self.__options.functionBefore) {
                            ok = self.__options.functionBefore.call(self, self, {
                                event: event,
                                origin: self._$origin[0]
                            });
                        }
                    }
                    if (ok !== false) {
                        if (self.__Content !== null) {
                            if (callback) {
                                self.__callbacks.open.push(callback);
                            }
                            self.__callbacks.close = [];
                            self.__timeoutsClear();
                            var extraTime, finish = function () {
                                if (self.__state != 'stable') {
                                    self.__stateSet('stable');
                                }
                                $.each(self.__callbacks.open, function (i, c) {
                                    c.call(self, self, {
                                        origin: self._$origin[0],
                                        tooltip: self._$tooltip[0]
                                    });
                                });
                                self.__callbacks.open = [];
                            };
                            if (self.__state !== 'closed') {
                                extraTime = 0;
                                if (self.__state === 'disappearing') {
                                    self.__stateSet('appearing');
                                    if (env.hasTransitions) {
                                        self._$tooltip.clearQueue().removeClass('tooltipster-dying').addClass('tooltipster-show');
                                        if (self.__options.animationDuration[0] > 0) {
                                            self._$tooltip.delay(self.__options.animationDuration[0]);
                                        }
                                        self._$tooltip.queue(finish);
                                    } else {
                                        self._$tooltip.stop().fadeIn(finish);
                                    }
                                } else if (self.__state == 'stable') {
                                    finish();
                                }
                            } else {
                                self.__stateSet('appearing');
                                extraTime = self.__options.animationDuration[0];
                                self.__contentInsert();
                                self.reposition(event, true);
                                if (env.hasTransitions) {
                                    self._$tooltip.addClass('tooltipster-' + self.__options.animation).addClass('tooltipster-initial').css({
                                        '-moz-animation-duration': self.__options.animationDuration[0] + 'ms',
                                        '-ms-animation-duration': self.__options.animationDuration[0] + 'ms',
                                        '-o-animation-duration': self.__options.animationDuration[0] + 'ms',
                                        '-webkit-animation-duration': self.__options.animationDuration[0] + 'ms',
                                        'animation-duration': self.__options.animationDuration[0] + 'ms',
                                        'transition-duration': self.__options.animationDuration[0] + 'ms'
                                    });
                                    setTimeout(function () {
                                        if (self.__state != 'closed') {
                                            self._$tooltip.addClass('tooltipster-show').removeClass('tooltipster-initial');
                                            if (self.__options.animationDuration[0] > 0) {
                                                self._$tooltip.delay(self.__options.animationDuration[0]);
                                            }
                                            self._$tooltip.queue(finish);
                                        }
                                    }, 0);
                                } else {
                                    self._$tooltip.css('display', 'none').fadeIn(self.__options.animationDuration[0], finish);
                                }
                                self.__trackerStart();
                                $(env.window).on('resize.' + self.__namespace + '-triggerClose', function (e) {
                                    var $ae = $(document.activeElement);
                                    if (!$ae.is('input') && !$ae.is('textarea') || !$.contains(self._$tooltip[0], $ae[0])) {
                                        self.reposition(e);
                                    }
                                }).on('scroll.' + self.__namespace + '-triggerClose', function (e) {
                                    self.__scrollHandler(e);
                                });
                                self.__$originParents = self._$origin.parents();
                                self.__$originParents.each(function (i, parent) {
                                    $(parent).on('scroll.' + self.__namespace + '-triggerClose', function (e) {
                                        self.__scrollHandler(e);
                                    });
                                });
                                if (self.__options.triggerClose.mouseleave || self.__options.triggerClose.touchleave && env.hasTouchCapability) {
                                    self._on('dismissable', function (event) {
                                        if (event.dismissable) {
                                            if (event.delay) {
                                                timeout = setTimeout(function () {
                                                    self._close(event.event);
                                                }, event.delay);
                                                self.__timeouts.close.push(timeout);
                                            } else {
                                                self._close(event);
                                            }
                                        } else {
                                            clearTimeout(timeout);
                                        }
                                    });
                                    var $elements = self._$origin, eventNamesIn = '', eventNamesOut = '',
                                        timeout = null;
                                    if (self.__options.interactive) {
                                        $elements = $elements.add(self._$tooltip);
                                    }
                                    if (self.__options.triggerClose.mouseleave) {
                                        eventNamesIn += 'mouseenter.' + self.__namespace + '-triggerClose ';
                                        eventNamesOut += 'mouseleave.' + self.__namespace + '-triggerClose ';
                                    }
                                    if (self.__options.triggerClose.touchleave && env.hasTouchCapability) {
                                        eventNamesIn += 'touchstart.' + self.__namespace + '-triggerClose';
                                        eventNamesOut += 'touchend.' + self.__namespace + '-triggerClose touchcancel.' + self.__namespace + '-triggerClose';
                                    }
                                    $elements.on(eventNamesOut, function (event) {
                                        if (self._touchIsTouchEvent(event) || !self._touchIsEmulatedEvent(event)) {
                                            var delay = event.type == 'mouseleave' ? self.__options.delay : self.__options.delayTouch;
                                            self._trigger({
                                                delay: delay[1],
                                                dismissable: true,
                                                event: event,
                                                type: 'dismissable'
                                            });
                                        }
                                    }).on(eventNamesIn, function (event) {
                                        if (self._touchIsTouchEvent(event) || !self._touchIsEmulatedEvent(event)) {
                                            self._trigger({
                                                dismissable: false,
                                                event: event,
                                                type: 'dismissable'
                                            });
                                        }
                                    });
                                }
                                if (self.__options.triggerClose.originClick) {
                                    self._$origin.on('click.' + self.__namespace + '-triggerClose', function (event) {
                                        if (!self._touchIsTouchEvent(event) && !self._touchIsEmulatedEvent(event)) {
                                            self._close(event);
                                        }
                                    });
                                }
                                if (self.__options.triggerClose.click || self.__options.triggerClose.tap && env.hasTouchCapability) {
                                    setTimeout(function () {
                                        if (self.__state != 'closed') {
                                            var eventNames = '', $body = $(env.window.document.body);
                                            if (self.__options.triggerClose.click) {
                                                eventNames += 'click.' + self.__namespace + '-triggerClose ';
                                            }
                                            if (self.__options.triggerClose.tap && env.hasTouchCapability) {
                                                eventNames += 'touchend.' + self.__namespace + '-triggerClose';
                                            }
                                            $body.on(eventNames, function (event) {
                                                if (self._touchIsMeaningfulEvent(event)) {
                                                    self._touchRecordEvent(event);
                                                    if (!self.__options.interactive || !$.contains(self._$tooltip[0], event.target)) {
                                                        self._close(event);
                                                    }
                                                }
                                            });
                                            if (self.__options.triggerClose.tap && env.hasTouchCapability) {
                                                $body.on('touchstart.' + self.__namespace + '-triggerClose', function (event) {
                                                    self._touchRecordEvent(event);
                                                });
                                            }
                                        }
                                    }, 0);
                                }
                                self._trigger('ready');
                                if (self.__options.functionReady) {
                                    self.__options.functionReady.call(self, self, {
                                        origin: self._$origin[0],
                                        tooltip: self._$tooltip[0]
                                    });
                                }
                            }
                            if (self.__options.timer > 0) {
                                var timeout = setTimeout(function () {
                                    self._close();
                                }, self.__options.timer + extraTime);
                                self.__timeouts.close.push(timeout);
                            }
                        }
                    }
                }
            }
            return self;
        },
        _openShortly: function (event) {
            var self = this, ok = true;
            if (self.__state != 'stable' && self.__state != 'appearing') {
                if (!self.__timeouts.open) {
                    self._trigger({
                        type: 'start',
                        event: event,
                        stop: function () {
                            ok = false;
                        }
                    });
                    if (ok) {
                        var delay = event.type.indexOf('touch') == 0 ? self.__options.delayTouch : self.__options.delay;
                        if (delay[0]) {
                            self.__timeouts.open = setTimeout(function () {
                                self.__timeouts.open = null;
                                if (self.__pointerIsOverOrigin && self._touchIsMeaningfulEvent(event)) {
                                    self._trigger('startend');
                                    self._open(event);
                                } else {
                                    self._trigger('startcancel');
                                }
                            }, delay[0]);
                        } else {
                            self._trigger('startend');
                            self._open(event);
                        }
                    }
                }
            }
            return self;
        },
        _optionsExtract: function (pluginName, defaultOptions) {
            var self = this, options = $.extend(true, {}, defaultOptions);
            var pluginOptions = self.__options[pluginName];
            if (!pluginOptions) {
                pluginOptions = {};
                $.each(defaultOptions, function (optionName, value) {
                    var o = self.__options[optionName];
                    if (o !== undefined) {
                        pluginOptions[optionName] = o;
                    }
                });
            }
            $.each(options, function (optionName, value) {
                if (pluginOptions[optionName] !== undefined) {
                    if (typeof value == 'object' && !(value instanceof Array) && value != null && (typeof pluginOptions[optionName] == 'object' && !(pluginOptions[optionName] instanceof Array) && pluginOptions[optionName] != null)) {
                        $.extend(options[optionName], pluginOptions[optionName]);
                    } else {
                        options[optionName] = pluginOptions[optionName];
                    }
                }
            });
            return options;
        },
        _plug: function (pluginName) {
            var plugin = $.tooltipster._plugin(pluginName);
            if (plugin) {
                if (plugin.instance) {
                    $.tooltipster.__bridge(plugin.instance, this, plugin.name);
                }
            } else {
                throw new Error('The "' + pluginName + '" plugin is not defined');
            }
            return this;
        },
        _touchIsEmulatedEvent: function (event) {
            var isEmulated = false, now = new Date().getTime();
            for (var i = this.__touchEvents.length - 1; i >= 0; i--) {
                var e = this.__touchEvents[i];
                if (now - e.time < 500) {
                    if (e.target === event.target) {
                        isEmulated = true;
                    }
                } else {
                    break;
                }
            }
            return isEmulated;
        },
        _touchIsMeaningfulEvent: function (event) {
            return this._touchIsTouchEvent(event) && !this._touchSwiped(event.target) || !this._touchIsTouchEvent(event) && !this._touchIsEmulatedEvent(event);
        },
        _touchIsTouchEvent: function (event) {
            return event.type.indexOf('touch') == 0;
        },
        _touchRecordEvent: function (event) {
            if (this._touchIsTouchEvent(event)) {
                event.time = new Date().getTime();
                this.__touchEvents.push(event);
            }
            return this;
        },
        _touchSwiped: function (target) {
            var swiped = false;
            for (var i = this.__touchEvents.length - 1; i >= 0; i--) {
                var e = this.__touchEvents[i];
                if (e.type == 'touchmove') {
                    swiped = true;
                    break;
                } else if (e.type == 'touchstart' && target === e.target) {
                    break;
                }
            }
            return swiped;
        },
        _trigger: function () {
            var args = Array.prototype.slice.apply(arguments);
            if (typeof args[0] == 'string') {
                args[0] = {
                    type: args[0]
                };
            }
            args[0].instance = this;
            args[0].origin = this._$origin ? this._$origin[0] : null;
            args[0].tooltip = this._$tooltip ? this._$tooltip[0] : null;
            this.__$emitterPrivate.trigger.apply(this.__$emitterPrivate, args);
            $.tooltipster._trigger.apply($.tooltipster, args);
            this.__$emitterPublic.trigger.apply(this.__$emitterPublic, args);
            return this;
        },
        _unplug: function (pluginName) {
            var self = this;
            if (self[pluginName]) {
                var plugin = $.tooltipster._plugin(pluginName);
                if (plugin.instance) {
                    $.each(plugin.instance, function (methodName, fn) {
                        if (self[methodName] && self[methodName].bridged === self[pluginName]) {
                            delete self[methodName];
                        }
                    });
                }
                if (self[pluginName].__destroy) {
                    self[pluginName].__destroy();
                }
                delete self[pluginName];
            }
            return self;
        },
        close: function (callback) {
            if (!this.__destroyed) {
                this._close(null, callback);
            } else {
                this.__destroyError();
            }
            return this;
        },
        content: function (content) {
            var self = this;
            if (content === undefined) {
                return self.__Content;
            } else {
                if (!self.__destroyed) {
                    self.__contentSet(content);
                    if (self.__Content !== null) {
                        if (self.__state !== 'closed') {
                            self.__contentInsert();
                            self.reposition();
                            if (self.__options.updateAnimation) {
                                if (env.hasTransitions) {
                                    var animation = self.__options.updateAnimation;
                                    self._$tooltip.addClass('tooltipster-update-' + animation);
                                    setTimeout(function () {
                                        if (self.__state != 'closed') {
                                            self._$tooltip.removeClass('tooltipster-update-' + animation);
                                        }
                                    }, 1e3);
                                } else {
                                    self._$tooltip.fadeTo(200, .5, function () {
                                        if (self.__state != 'closed') {
                                            self._$tooltip.fadeTo(200, 1);
                                        }
                                    });
                                }
                            }
                        }
                    } else {
                        self._close();
                    }
                } else {
                    self.__destroyError();
                }
                return self;
            }
        },
        destroy: function () {
            var self = this;
            if (!self.__destroyed) {
                if (self.__state != 'closed') {
                    self.option('animationDuration', 0)._close(null, null, true);
                } else {
                    self.__timeoutsClear();
                }
                self._trigger('destroy');
                self.__destroyed = true;
                self._$origin.removeData(self.__namespace).off('.' + self.__namespace + '-triggerOpen');
                $(env.window.document.body).off('.' + self.__namespace + '-triggerOpen');
                var ns = self._$origin.data('tooltipster-ns');
                if (ns) {
                    if (ns.length === 1) {
                        var title = null;
                        if (self.__options.restoration == 'previous') {
                            title = self._$origin.data('tooltipster-initialTitle');
                        } else if (self.__options.restoration == 'current') {
                            title = typeof self.__Content == 'string' ? self.__Content : $('<div></div>').append(self.__Content).html();
                        }
                        if (title) {
                            self._$origin.attr('title', title);
                        }
                        self._$origin.removeClass('tooltipstered');
                        self._$origin.removeData('tooltipster-ns').removeData('tooltipster-initialTitle');
                    } else {
                        ns = $.grep(ns, function (el, i) {
                            return el !== self.__namespace;
                        });
                        self._$origin.data('tooltipster-ns', ns);
                    }
                }
                self._trigger('destroyed');
                self._off();
                self.off();
                self.__Content = null;
                self.__$emitterPrivate = null;
                self.__$emitterPublic = null;
                self.__options.parent = null;
                self._$origin = null;
                self._$tooltip = null;
                $.tooltipster.__instancesLatestArr = $.grep($.tooltipster.__instancesLatestArr, function (el, i) {
                    return self !== el;
                });
                clearInterval(self.__garbageCollector);
            } else {
                self.__destroyError();
            }
            return self;
        },
        disable: function () {
            if (!this.__destroyed) {
                this._close();
                this.__enabled = false;
                return this;
            } else {
                this.__destroyError();
            }
            return this;
        },
        elementOrigin: function () {
            if (!this.__destroyed) {
                return this._$origin[0];
            } else {
                this.__destroyError();
            }
        },
        elementTooltip: function () {
            return this._$tooltip ? this._$tooltip[0] : null;
        },
        enable: function () {
            this.__enabled = true;
            return this;
        },
        hide: function (callback) {
            return this.close(callback);
        },
        instance: function () {
            return this;
        },
        off: function () {
            if (!this.__destroyed) {
                this.__$emitterPublic.off.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            }
            return this;
        },
        on: function () {
            if (!this.__destroyed) {
                this.__$emitterPublic.on.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            } else {
                this.__destroyError();
            }
            return this;
        },
        one: function () {
            if (!this.__destroyed) {
                this.__$emitterPublic.one.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            } else {
                this.__destroyError();
            }
            return this;
        },
        open: function (callback) {
            if (!this.__destroyed) {
                this._open(null, callback);
            } else {
                this.__destroyError();
            }
            return this;
        },
        option: function (o, val) {
            if (val === undefined) {
                return this.__options[o];
            } else {
                if (!this.__destroyed) {
                    this.__options[o] = val;
                    this.__optionsFormat();
                    if ($.inArray(o, ['trigger', 'triggerClose', 'triggerOpen']) >= 0) {
                        this.__prepareOrigin();
                    }
                    if (o === 'selfDestruction') {
                        this.__prepareGC();
                    }
                } else {
                    this.__destroyError();
                }
                return this;
            }
        },
        reposition: function (event, tooltipIsDetached) {
            var self = this;
            if (!self.__destroyed) {
                if (self.__state != 'closed' && bodyContains(self._$origin)) {
                    if (tooltipIsDetached || bodyContains(self._$tooltip)) {
                        if (!tooltipIsDetached) {
                            self._$tooltip.detach();
                        }
                        self.__Geometry = self.__geometry();
                        self._trigger({
                            type: 'reposition',
                            event: event,
                            helper: {
                                geo: self.__Geometry
                            }
                        });
                    }
                }
            } else {
                self.__destroyError();
            }
            return self;
        },
        show: function (callback) {
            return this.open(callback);
        },
        status: function () {
            return {
                destroyed: this.__destroyed,
                enabled: this.__enabled,
                open: this.__state !== 'closed',
                state: this.__state
            };
        },
        triggerHandler: function () {
            if (!this.__destroyed) {
                this.__$emitterPublic.triggerHandler.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            } else {
                this.__destroyError();
            }
            return this;
        }
    };
    $.fn.tooltipster = function () {
        var args = Array.prototype.slice.apply(arguments),
            contentCloningWarning = 'You are using a single HTML element as content for several tooltips. You probably want to set the contentCloning option to TRUE.';
        if (this.length === 0) {
            return this;
        } else {
            if (typeof args[0] === 'string') {
                var v = '#*$~&';
                this.each(function () {
                    var ns = $(this).data('tooltipster-ns'), self = ns ? $(this).data(ns[0]) : null;
                    if (self) {
                        if (typeof self[args[0]] === 'function') {
                            if (this.length > 1 && args[0] == 'content' && (args[1] instanceof $ || typeof args[1] == 'object' && args[1] != null && args[1].tagName) && !self.__options.contentCloning && self.__options.debug) {
                                console.log(contentCloningWarning);
                            }
                            var resp = self[args[0]](args[1], args[2]);
                        } else {
                            throw new Error('Unknown method "' + args[0] + '"');
                        }
                        if (resp !== self || args[0] === 'instance') {
                            v = resp;
                            return false;
                        }
                    } else {
                        throw new Error('You called Tooltipster\'s "' + args[0] + '" method on an uninitialized element');
                    }
                });
                return v !== '#*$~&' ? v : this;
            } else {
                $.tooltipster.__instancesLatestArr = [];
                var multipleIsSet = args[0] && args[0].multiple !== undefined,
                    multiple = multipleIsSet && args[0].multiple || !multipleIsSet && defaults.multiple,
                    contentIsSet = args[0] && args[0].content !== undefined,
                    content = contentIsSet && args[0].content || !contentIsSet && defaults.content,
                    contentCloningIsSet = args[0] && args[0].contentCloning !== undefined,
                    contentCloning = contentCloningIsSet && args[0].contentCloning || !contentCloningIsSet && defaults.contentCloning,
                    debugIsSet = args[0] && args[0].debug !== undefined,
                    debug = debugIsSet && args[0].debug || !debugIsSet && defaults.debug;
                if (this.length > 1 && (content instanceof $ || typeof content == 'object' && content != null && content.tagName) && !contentCloning && debug) {
                    console.log(contentCloningWarning);
                }
                this.each(function () {
                    var go = false, $this = $(this), ns = $this.data('tooltipster-ns'), obj = null;
                    if (!ns) {
                        go = true;
                    } else if (multiple) {
                        go = true;
                    } else if (debug) {
                        console.log('Tooltipster: one or more tooltips are already attached to the element below. Ignoring.');
                        console.log(this);
                    }
                    if (go) {
                        obj = new $.Tooltipster(this, args[0]);
                        if (!ns) ns = [];
                        ns.push(obj.__namespace);
                        $this.data('tooltipster-ns', ns);
                        $this.data(obj.__namespace, obj);
                        if (obj.__options.functionInit) {
                            obj.__options.functionInit.call(obj, obj, {
                                origin: this
                            });
                        }
                        obj._trigger('init');
                    }
                    $.tooltipster.__instancesLatestArr.push(obj);
                });
                return this;
            }
        }
    };

    function Ruler($tooltip) {
        this.$container;
        this.constraints = null;
        this.__$tooltip;
        this.__init($tooltip);
    }

    Ruler.prototype = {
        __init: function ($tooltip) {
            this.__$tooltip = $tooltip;
            this.__$tooltip.css({
                left: 0,
                overflow: 'hidden',
                position: 'absolute',
                top: 0
            }).find('.tooltipster-content').css('overflow', 'auto');
            this.$container = $('<div class="tooltipster-ruler"></div>').append(this.__$tooltip).appendTo(env.window.document.body);
        },
        __forceRedraw: function () {
            var $p = this.__$tooltip.parent();
            this.__$tooltip.detach();
            this.__$tooltip.appendTo($p);
        },
        constrain: function (width, height) {
            this.constraints = {
                width: width,
                height: height
            };
            this.__$tooltip.css({
                display: 'block',
                height: '',
                overflow: 'auto',
                width: width
            });
            return this;
        },
        destroy: function () {
            this.__$tooltip.detach().find('.tooltipster-content').css({
                display: '',
                overflow: ''
            });
            this.$container.remove();
        },
        free: function () {
            this.constraints = null;
            this.__$tooltip.css({
                display: '',
                height: '',
                overflow: 'visible',
                width: ''
            });
            return this;
        },
        measure: function () {
            this.__forceRedraw();
            var tooltipBcr = this.__$tooltip[0].getBoundingClientRect(), result = {
                size: {
                    height: tooltipBcr.height || tooltipBcr.bottom - tooltipBcr.top,
                    width: tooltipBcr.width || tooltipBcr.right - tooltipBcr.left
                }
            };
            if (this.constraints) {
                var $content = this.__$tooltip.find('.tooltipster-content'), height = this.__$tooltip.outerHeight(),
                    contentBcr = $content[0].getBoundingClientRect(), fits = {
                        height: height <= this.constraints.height,
                        width: tooltipBcr.width <= this.constraints.width && contentBcr.width >= $content[0].scrollWidth - 1
                    };
                result.fits = fits.height && fits.width;
            }
            if (env.IE && env.IE <= 11 && result.size.width !== env.window.document.documentElement.clientWidth) {
                result.size.width = Math.ceil(result.size.width) + 1;
            }
            return result;
        }
    };

    function areEqual(a, b) {
        var same = true;
        $.each(a, function (i, _) {
            if (b[i] === undefined || a[i] !== b[i]) {
                same = false;
                return false;
            }
        });
        return same;
    }

    function bodyContains($obj) {
        var id = $obj.attr('id'), el = id ? env.window.document.getElementById(id) : null;
        return el ? el === $obj[0] : $.contains(env.window.document.body, $obj[0]);
    }

    var uA = navigator.userAgent.toLowerCase();
    if (uA.indexOf('msie') != -1) env.IE = parseInt(uA.split('msie')[1]); else if (uA.toLowerCase().indexOf('trident') !== -1 && uA.indexOf(' rv:11') !== -1) env.IE = 11; else if (uA.toLowerCase().indexOf('edge/') != -1) env.IE = parseInt(uA.toLowerCase().split('edge/')[1]);

    function transitionSupport() {
        if (!win) return false;
        var b = win.document.body || win.document.documentElement, s = b.style, p = 'transition',
            v = ['Moz', 'Webkit', 'Khtml', 'O', 'ms'];
        if (typeof s[p] == 'string') {
            return true;
        }
        p = p.charAt(0).toUpperCase() + p.substr(1);
        for (var i = 0; i < v.length; i++) {
            if (typeof s[v[i] + p] == 'string') {
                return true;
            }
        }
        return false;
    }

    var pluginName = 'tooltipster.sideTip';
    $.tooltipster._plugin({
        name: pluginName,
        instance: {
            __defaults: function () {
                return {
                    arrow: true,
                    distance: 6,
                    functionPosition: null,
                    maxWidth: null,
                    minIntersection: 16,
                    minWidth: 0,
                    position: null,
                    side: 'top',
                    viewportAware: true
                };
            },
            __init: function (instance) {
                var self = this;
                self.__instance = instance;
                self.__namespace = 'tooltipster-sideTip-' + Math.round(Math.random() * 1e6);
                self.__previousState = 'closed';
                self.__options;
                self.__optionsFormat();
                self.__instance._on('state.' + self.__namespace, function (event) {
                    if (event.state == 'closed') {
                        self.__close();
                    } else if (event.state == 'appearing' && self.__previousState == 'closed') {
                        self.__create();
                    }
                    self.__previousState = event.state;
                });
                self.__instance._on('options.' + self.__namespace, function () {
                    self.__optionsFormat();
                });
                self.__instance._on('reposition.' + self.__namespace, function (e) {
                    self.__reposition(e.event, e.helper);
                });
            },
            __close: function () {
                if (this.__instance.content() instanceof $) {
                    this.__instance.content().detach();
                }
                this.__instance._$tooltip.remove();
                this.__instance._$tooltip = null;
            },
            __create: function () {
                var $html = $('<div class="tooltipster-base tooltipster-sidetip">' + '<div class="tooltipster-box">' + '<div class="tooltipster-content"></div>' + '</div>' + '<div class="tooltipster-arrow">' + '<div class="tooltipster-arrow-uncropped">' + '<div class="tooltipster-arrow-border"></div>' + '<div class="tooltipster-arrow-background"></div>' + '</div>' + '</div>' + '</div>');
                if (!this.__options.arrow) {
                    $html.find('.tooltipster-box').css('margin', 0).end().find('.tooltipster-arrow').hide();
                }
                if (this.__options.minWidth) {
                    $html.css('min-width', this.__options.minWidth + 'px');
                }
                if (this.__options.maxWidth) {
                    $html.css('max-width', this.__options.maxWidth + 'px');
                }
                this.__instance._$tooltip = $html;
                this.__instance._trigger('created');
            },
            __destroy: function () {
                this.__instance._off('.' + self.__namespace);
            },
            __optionsFormat: function () {
                var self = this;
                self.__options = self.__instance._optionsExtract(pluginName, self.__defaults());
                if (self.__options.position) {
                    self.__options.side = self.__options.position;
                }
                if (typeof self.__options.distance != 'object') {
                    self.__options.distance = [self.__options.distance];
                }
                if (self.__options.distance.length < 4) {
                    if (self.__options.distance[1] === undefined) self.__options.distance[1] = self.__options.distance[0];
                    if (self.__options.distance[2] === undefined) self.__options.distance[2] = self.__options.distance[0];
                    if (self.__options.distance[3] === undefined) self.__options.distance[3] = self.__options.distance[1];
                    self.__options.distance = {
                        top: self.__options.distance[0],
                        right: self.__options.distance[1],
                        bottom: self.__options.distance[2],
                        left: self.__options.distance[3]
                    };
                }
                if (typeof self.__options.side == 'string') {
                    var opposites = {
                        top: 'bottom',
                        right: 'left',
                        bottom: 'top',
                        left: 'right'
                    };
                    self.__options.side = [self.__options.side, opposites[self.__options.side]];
                    if (self.__options.side[0] == 'left' || self.__options.side[0] == 'right') {
                        self.__options.side.push('top', 'bottom');
                    } else {
                        self.__options.side.push('right', 'left');
                    }
                }
                if ($.tooltipster._env.IE === 6 && self.__options.arrow !== true) {
                    self.__options.arrow = false;
                }
            },
            __reposition: function (event, helper) {
                var self = this, finalResult, targets = self.__targetFind(helper), testResults = [];
                self.__instance._$tooltip.detach();
                var $clone = self.__instance._$tooltip.clone(), ruler = $.tooltipster._getRuler($clone),
                    satisfied = false, animation = self.__instance.option('animation');
                if (animation) {
                    $clone.removeClass('tooltipster-' + animation);
                }
                $.each(['window', 'document'], function (i, container) {
                    var takeTest = null;
                    self.__instance._trigger({
                        container: container,
                        helper: helper,
                        satisfied: satisfied,
                        takeTest: function (bool) {
                            takeTest = bool;
                        },
                        results: testResults,
                        type: 'positionTest'
                    });
                    if (takeTest == true || takeTest != false && satisfied == false && (container != 'window' || self.__options.viewportAware)) {
                        for (var i = 0; i < self.__options.side.length; i++) {
                            var distance = {
                                horizontal: 0,
                                vertical: 0
                            }, side = self.__options.side[i];
                            if (side == 'top' || side == 'bottom') {
                                distance.vertical = self.__options.distance[side];
                            } else {
                                distance.horizontal = self.__options.distance[side];
                            }
                            self.__sideChange($clone, side);
                            $.each(['natural', 'constrained'], function (i, mode) {
                                takeTest = null;
                                self.__instance._trigger({
                                    container: container,
                                    event: event,
                                    helper: helper,
                                    mode: mode,
                                    results: testResults,
                                    satisfied: satisfied,
                                    side: side,
                                    takeTest: function (bool) {
                                        takeTest = bool;
                                    },
                                    type: 'positionTest'
                                });
                                if (takeTest == true || takeTest != false && satisfied == false) {
                                    var testResult = {
                                        container: container,
                                        distance: distance,
                                        fits: null,
                                        mode: mode,
                                        outerSize: null,
                                        side: side,
                                        size: null,
                                        target: targets[side],
                                        whole: null
                                    };
                                    var rulerConfigured = mode == 'natural' ? ruler.free() : ruler.constrain(helper.geo.available[container][side].width - distance.horizontal, helper.geo.available[container][side].height - distance.vertical),
                                        rulerResults = rulerConfigured.measure();
                                    testResult.size = rulerResults.size;
                                    testResult.outerSize = {
                                        height: rulerResults.size.height + distance.vertical,
                                        width: rulerResults.size.width + distance.horizontal
                                    };
                                    if (mode == 'natural') {
                                        if (helper.geo.available[container][side].width >= testResult.outerSize.width && helper.geo.available[container][side].height >= testResult.outerSize.height) {
                                            testResult.fits = true;
                                        } else {
                                            testResult.fits = false;
                                        }
                                    } else {
                                        testResult.fits = rulerResults.fits;
                                    }
                                    if (container == 'window') {
                                        if (!testResult.fits) {
                                            testResult.whole = false;
                                        } else {
                                            if (side == 'top' || side == 'bottom') {
                                                testResult.whole = helper.geo.origin.windowOffset.right >= self.__options.minIntersection && helper.geo.window.size.width - helper.geo.origin.windowOffset.left >= self.__options.minIntersection;
                                            } else {
                                                testResult.whole = helper.geo.origin.windowOffset.bottom >= self.__options.minIntersection && helper.geo.window.size.height - helper.geo.origin.windowOffset.top >= self.__options.minIntersection;
                                            }
                                        }
                                    }
                                    testResults.push(testResult);
                                    if (testResult.whole) {
                                        satisfied = true;
                                    } else {
                                        if (testResult.mode == 'natural' && (testResult.fits || testResult.size.width <= helper.geo.available[container][side].width)) {
                                            return false;
                                        }
                                    }
                                }
                            });
                        }
                    }
                });
                self.__instance._trigger({
                    edit: function (r) {
                        testResults = r;
                    },
                    event: event,
                    helper: helper,
                    results: testResults,
                    type: 'positionTested'
                });
                testResults.sort(function (a, b) {
                    if (a.whole && !b.whole) {
                        return -1;
                    } else if (!a.whole && b.whole) {
                        return 1;
                    } else if (a.whole && b.whole) {
                        var ai = self.__options.side.indexOf(a.side), bi = self.__options.side.indexOf(b.side);
                        if (ai < bi) {
                            return -1;
                        } else if (ai > bi) {
                            return 1;
                        } else {
                            return a.mode == 'natural' ? -1 : 1;
                        }
                    } else {
                        if (a.fits && !b.fits) {
                            return -1;
                        } else if (!a.fits && b.fits) {
                            return 1;
                        } else if (a.fits && b.fits) {
                            var ai = self.__options.side.indexOf(a.side), bi = self.__options.side.indexOf(b.side);
                            if (ai < bi) {
                                return -1;
                            } else if (ai > bi) {
                                return 1;
                            } else {
                                return a.mode == 'natural' ? -1 : 1;
                            }
                        } else {
                            if (a.container == 'document' && a.side == 'bottom' && a.mode == 'natural') {
                                return -1;
                            } else {
                                return 1;
                            }
                        }
                    }
                });
                finalResult = testResults[0];
                finalResult.coord = {};
                switch (finalResult.side) {
                    case 'left':
                    case 'right':
                        finalResult.coord.top = Math.floor(finalResult.target - finalResult.size.height / 2);
                        break;

                    case 'bottom':
                    case 'top':
                        finalResult.coord.left = Math.floor(finalResult.target - finalResult.size.width / 2);
                        break;
                }
                switch (finalResult.side) {
                    case 'left':
                        finalResult.coord.left = helper.geo.origin.windowOffset.left - finalResult.outerSize.width;
                        break;

                    case 'right':
                        finalResult.coord.left = helper.geo.origin.windowOffset.right + finalResult.distance.horizontal;
                        break;

                    case 'top':
                        finalResult.coord.top = helper.geo.origin.windowOffset.top - finalResult.outerSize.height;
                        break;

                    case 'bottom':
                        finalResult.coord.top = helper.geo.origin.windowOffset.bottom + finalResult.distance.vertical;
                        break;
                }
                if (finalResult.container == 'window') {
                    if (finalResult.side == 'top' || finalResult.side == 'bottom') {
                        if (finalResult.coord.left < 0) {
                            if (helper.geo.origin.windowOffset.right - this.__options.minIntersection >= 0) {
                                finalResult.coord.left = 0;
                            } else {
                                finalResult.coord.left = helper.geo.origin.windowOffset.right - this.__options.minIntersection - 1;
                            }
                        } else if (finalResult.coord.left > helper.geo.window.size.width - finalResult.size.width) {
                            if (helper.geo.origin.windowOffset.left + this.__options.minIntersection <= helper.geo.window.size.width) {
                                finalResult.coord.left = helper.geo.window.size.width - finalResult.size.width;
                            } else {
                                finalResult.coord.left = helper.geo.origin.windowOffset.left + this.__options.minIntersection + 1 - finalResult.size.width;
                            }
                        }
                    } else {
                        if (finalResult.coord.top < 0) {
                            if (helper.geo.origin.windowOffset.bottom - this.__options.minIntersection >= 0) {
                                finalResult.coord.top = 0;
                            } else {
                                finalResult.coord.top = helper.geo.origin.windowOffset.bottom - this.__options.minIntersection - 1;
                            }
                        } else if (finalResult.coord.top > helper.geo.window.size.height - finalResult.size.height) {
                            if (helper.geo.origin.windowOffset.top + this.__options.minIntersection <= helper.geo.window.size.height) {
                                finalResult.coord.top = helper.geo.window.size.height - finalResult.size.height;
                            } else {
                                finalResult.coord.top = helper.geo.origin.windowOffset.top + this.__options.minIntersection + 1 - finalResult.size.height;
                            }
                        }
                    }
                } else {
                    if (finalResult.coord.left > helper.geo.window.size.width - finalResult.size.width) {
                        finalResult.coord.left = helper.geo.window.size.width - finalResult.size.width;
                    }
                    if (finalResult.coord.left < 0) {
                        finalResult.coord.left = 0;
                    }
                }
                self.__sideChange($clone, finalResult.side);
                helper.tooltipClone = $clone[0];
                helper.tooltipParent = self.__instance.option('parent').parent[0];
                helper.mode = finalResult.mode;
                helper.whole = finalResult.whole;
                helper.origin = self.__instance._$origin[0];
                helper.tooltip = self.__instance._$tooltip[0];
                delete finalResult.container;
                delete finalResult.fits;
                delete finalResult.mode;
                delete finalResult.outerSize;
                delete finalResult.whole;
                finalResult.distance = finalResult.distance.horizontal || finalResult.distance.vertical;
                var finalResultClone = $.extend(true, {}, finalResult);
                self.__instance._trigger({
                    edit: function (result) {
                        finalResult = result;
                    },
                    event: event,
                    helper: helper,
                    position: finalResultClone,
                    type: 'position'
                });
                if (self.__options.functionPosition) {
                    var result = self.__options.functionPosition.call(self, self.__instance, helper, finalResultClone);
                    if (result) finalResult = result;
                }
                ruler.destroy();
                var arrowCoord, maxVal;
                if (finalResult.side == 'top' || finalResult.side == 'bottom') {
                    arrowCoord = {
                        prop: 'left',
                        val: finalResult.target - finalResult.coord.left
                    };
                    maxVal = finalResult.size.width - this.__options.minIntersection;
                } else {
                    arrowCoord = {
                        prop: 'top',
                        val: finalResult.target - finalResult.coord.top
                    };
                    maxVal = finalResult.size.height - this.__options.minIntersection;
                }
                if (arrowCoord.val < this.__options.minIntersection) {
                    arrowCoord.val = this.__options.minIntersection;
                } else if (arrowCoord.val > maxVal) {
                    arrowCoord.val = maxVal;
                }
                var originParentOffset;
                if (helper.geo.origin.fixedLineage) {
                    originParentOffset = helper.geo.origin.windowOffset;
                } else {
                    originParentOffset = {
                        left: helper.geo.origin.windowOffset.left + helper.geo.window.scroll.left,
                        top: helper.geo.origin.windowOffset.top + helper.geo.window.scroll.top
                    };
                }
                finalResult.coord = {
                    left: originParentOffset.left + (finalResult.coord.left - helper.geo.origin.windowOffset.left),
                    top: originParentOffset.top + (finalResult.coord.top - helper.geo.origin.windowOffset.top)
                };
                self.__sideChange(self.__instance._$tooltip, finalResult.side);
                if (helper.geo.origin.fixedLineage) {
                    self.__instance._$tooltip.css('position', 'fixed');
                } else {
                    self.__instance._$tooltip.css('position', '');
                }
                self.__instance._$tooltip.css({
                    left: finalResult.coord.left,
                    top: finalResult.coord.top,
                    height: finalResult.size.height,
                    width: finalResult.size.width
                }).find('.tooltipster-arrow').css({
                    left: '',
                    top: ''
                }).css(arrowCoord.prop, arrowCoord.val);
                self.__instance._$tooltip.appendTo(self.__instance.option('parent'));
                self.__instance._trigger({
                    type: 'repositioned',
                    event: event,
                    position: finalResult
                });
            },
            __sideChange: function ($obj, side) {
                $obj.removeClass('tooltipster-bottom').removeClass('tooltipster-left').removeClass('tooltipster-right').removeClass('tooltipster-top').addClass('tooltipster-' + side);
            },
            __targetFind: function (helper) {
                var target = {}, rects = this.__instance._$origin[0].getClientRects();
                if (rects.length > 1) {
                    var opacity = this.__instance._$origin.css('opacity');
                    if (opacity == 1) {
                        this.__instance._$origin.css('opacity', .99);
                        rects = this.__instance._$origin[0].getClientRects();
                        this.__instance._$origin.css('opacity', 1);
                    }
                }
                if (rects.length < 2) {
                    target.top = Math.floor(helper.geo.origin.windowOffset.left + helper.geo.origin.size.width / 2);
                    target.bottom = target.top;
                    target.left = Math.floor(helper.geo.origin.windowOffset.top + helper.geo.origin.size.height / 2);
                    target.right = target.left;
                } else {
                    var targetRect = rects[0];
                    target.top = Math.floor(targetRect.left + (targetRect.right - targetRect.left) / 2);
                    if (rects.length > 2) {
                        targetRect = rects[Math.ceil(rects.length / 2) - 1];
                    } else {
                        targetRect = rects[0];
                    }
                    target.right = Math.floor(targetRect.top + (targetRect.bottom - targetRect.top) / 2);
                    targetRect = rects[rects.length - 1];
                    target.bottom = Math.floor(targetRect.left + (targetRect.right - targetRect.left) / 2);
                    if (rects.length > 2) {
                        targetRect = rects[Math.ceil((rects.length + 1) / 2) - 1];
                    } else {
                        targetRect = rects[rects.length - 1];
                    }
                    target.left = Math.floor(targetRect.top + (targetRect.bottom - targetRect.top) / 2);
                }
                return target;
            }
        }
    });
    return $;
});
