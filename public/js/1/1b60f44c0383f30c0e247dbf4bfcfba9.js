(function(factory) {
    if (typeof define === "function" && define.amd) {
        define([ "jquery" ], factory);
    } else {
        factory(jQuery);
    }
})(function($) {
    $.ui = $.ui || {};
    var version = $.ui.version = "1.12.1";
    var widgetUuid = 0;
    var widgetSlice = Array.prototype.slice;
    $.cleanData = function(orig) {
        return function(elems) {
            var events, elem, i;
            for (i = 0; (elem = elems[i]) != null; i++) {
                try {
                    events = $._data(elem, "events");
                    if (events && events.remove) {
                        $(elem).triggerHandler("remove");
                    }
                } catch (e) {}
            }
            orig(elems);
        };
    }($.cleanData);
    $.widget = function(name, base, prototype) {
        var existingConstructor, constructor, basePrototype;
        var proxiedPrototype = {};
        var namespace = name.split(".")[0];
        name = name.split(".")[1];
        var fullName = namespace + "-" + name;
        if (!prototype) {
            prototype = base;
            base = $.Widget;
        }
        if ($.isArray(prototype)) {
            prototype = $.extend.apply(null, [ {} ].concat(prototype));
        }
        $.expr[":"][fullName.toLowerCase()] = function(elem) {
            return !!$.data(elem, fullName);
        };
        $[namespace] = $[namespace] || {};
        existingConstructor = $[namespace][name];
        constructor = $[namespace][name] = function(options, element) {
            if (!this._createWidget) {
                return new constructor(options, element);
            }
            if (arguments.length) {
                this._createWidget(options, element);
            }
        };
        $.extend(constructor, existingConstructor, {
            version: prototype.version,
            _proto: $.extend({}, prototype),
            _childConstructors: []
        });
        basePrototype = new base();
        basePrototype.options = $.widget.extend({}, basePrototype.options);
        $.each(prototype, function(prop, value) {
            if (!$.isFunction(value)) {
                proxiedPrototype[prop] = value;
                return;
            }
            proxiedPrototype[prop] = function() {
                function _super() {
                    return base.prototype[prop].apply(this, arguments);
                }
                function _superApply(args) {
                    return base.prototype[prop].apply(this, args);
                }
                return function() {
                    var __super = this._super;
                    var __superApply = this._superApply;
                    var returnValue;
                    this._super = _super;
                    this._superApply = _superApply;
                    returnValue = value.apply(this, arguments);
                    this._super = __super;
                    this._superApply = __superApply;
                    return returnValue;
                };
            }();
        });
        constructor.prototype = $.widget.extend(basePrototype, {
            widgetEventPrefix: existingConstructor ? basePrototype.widgetEventPrefix || name : name
        }, proxiedPrototype, {
            constructor: constructor,
            namespace: namespace,
            widgetName: name,
            widgetFullName: fullName
        });
        if (existingConstructor) {
            $.each(existingConstructor._childConstructors, function(i, child) {
                var childPrototype = child.prototype;
                $.widget(childPrototype.namespace + "." + childPrototype.widgetName, constructor, child._proto);
            });
            delete existingConstructor._childConstructors;
        } else {
            base._childConstructors.push(constructor);
        }
        $.widget.bridge(name, constructor);
        return constructor;
    };
    $.widget.extend = function(target) {
        var input = widgetSlice.call(arguments, 1);
        var inputIndex = 0;
        var inputLength = input.length;
        var key;
        var value;
        for (;inputIndex < inputLength; inputIndex++) {
            for (key in input[inputIndex]) {
                value = input[inputIndex][key];
                if (input[inputIndex].hasOwnProperty(key) && value !== undefined) {
                    if ($.isPlainObject(value)) {
                        target[key] = $.isPlainObject(target[key]) ? $.widget.extend({}, target[key], value) : $.widget.extend({}, value);
                    } else {
                        target[key] = value;
                    }
                }
            }
        }
        return target;
    };
    $.widget.bridge = function(name, object) {
        var fullName = object.prototype.widgetFullName || name;
        $.fn[name] = function(options) {
            var isMethodCall = typeof options === "string";
            var args = widgetSlice.call(arguments, 1);
            var returnValue = this;
            if (isMethodCall) {
                if (!this.length && options === "instance") {
                    returnValue = undefined;
                } else {
                    this.each(function() {
                        var methodValue;
                        var instance = $.data(this, fullName);
                        if (options === "instance") {
                            returnValue = instance;
                            return false;
                        }
                        if (!instance) {
                            return $.error("cannot call methods on " + name + " prior to initialization; " + "attempted to call method '" + options + "'");
                        }
                        if (!$.isFunction(instance[options]) || options.charAt(0) === "_") {
                            return $.error("no such method '" + options + "' for " + name + " widget instance");
                        }
                        methodValue = instance[options].apply(instance, args);
                        if (methodValue !== instance && methodValue !== undefined) {
                            returnValue = methodValue && methodValue.jquery ? returnValue.pushStack(methodValue.get()) : methodValue;
                            return false;
                        }
                    });
                }
            } else {
                if (args.length) {
                    options = $.widget.extend.apply(null, [ options ].concat(args));
                }
                this.each(function() {
                    var instance = $.data(this, fullName);
                    if (instance) {
                        instance.option(options || {});
                        if (instance._init) {
                            instance._init();
                        }
                    } else {
                        $.data(this, fullName, new object(options, this));
                    }
                });
            }
            return returnValue;
        };
    };
    $.Widget = function() {};
    $.Widget._childConstructors = [];
    $.Widget.prototype = {
        widgetName: "widget",
        widgetEventPrefix: "",
        defaultElement: "<div>",
        options: {
            classes: {},
            disabled: false,
            create: null
        },
        _createWidget: function(options, element) {
            element = $(element || this.defaultElement || this)[0];
            this.element = $(element);
            this.uuid = widgetUuid++;
            this.eventNamespace = "." + this.widgetName + this.uuid;
            this.bindings = $();
            this.hoverable = $();
            this.focusable = $();
            this.classesElementLookup = {};
            if (element !== this) {
                $.data(element, this.widgetFullName, this);
                this._on(true, this.element, {
                    remove: function(event) {
                        if (event.target === element) {
                            this.destroy();
                        }
                    }
                });
                this.document = $(element.style ? element.ownerDocument : element.document || element);
                this.window = $(this.document[0].defaultView || this.document[0].parentWindow);
            }
            this.options = $.widget.extend({}, this.options, this._getCreateOptions(), options);
            this._create();
            if (this.options.disabled) {
                this._setOptionDisabled(this.options.disabled);
            }
            this._trigger("create", null, this._getCreateEventData());
            this._init();
        },
        _getCreateOptions: function() {
            return {};
        },
        _getCreateEventData: $.noop,
        _create: $.noop,
        _init: $.noop,
        destroy: function() {
            var that = this;
            this._destroy();
            $.each(this.classesElementLookup, function(key, value) {
                that._removeClass(value, key);
            });
            this.element.off(this.eventNamespace).removeData(this.widgetFullName);
            this.widget().off(this.eventNamespace).removeAttr("aria-disabled");
            this.bindings.off(this.eventNamespace);
        },
        _destroy: $.noop,
        widget: function() {
            return this.element;
        },
        option: function(key, value) {
            var options = key;
            var parts;
            var curOption;
            var i;
            if (arguments.length === 0) {
                return $.widget.extend({}, this.options);
            }
            if (typeof key === "string") {
                options = {};
                parts = key.split(".");
                key = parts.shift();
                if (parts.length) {
                    curOption = options[key] = $.widget.extend({}, this.options[key]);
                    for (i = 0; i < parts.length - 1; i++) {
                        curOption[parts[i]] = curOption[parts[i]] || {};
                        curOption = curOption[parts[i]];
                    }
                    key = parts.pop();
                    if (arguments.length === 1) {
                        return curOption[key] === undefined ? null : curOption[key];
                    }
                    curOption[key] = value;
                } else {
                    if (arguments.length === 1) {
                        return this.options[key] === undefined ? null : this.options[key];
                    }
                    options[key] = value;
                }
            }
            this._setOptions(options);
            return this;
        },
        _setOptions: function(options) {
            var key;
            for (key in options) {
                this._setOption(key, options[key]);
            }
            return this;
        },
        _setOption: function(key, value) {
            if (key === "classes") {
                this._setOptionClasses(value);
            }
            this.options[key] = value;
            if (key === "disabled") {
                this._setOptionDisabled(value);
            }
            return this;
        },
        _setOptionClasses: function(value) {
            var classKey, elements, currentElements;
            for (classKey in value) {
                currentElements = this.classesElementLookup[classKey];
                if (value[classKey] === this.options.classes[classKey] || !currentElements || !currentElements.length) {
                    continue;
                }
                elements = $(currentElements.get());
                this._removeClass(currentElements, classKey);
                elements.addClass(this._classes({
                    element: elements,
                    keys: classKey,
                    classes: value,
                    add: true
                }));
            }
        },
        _setOptionDisabled: function(value) {
            this._toggleClass(this.widget(), this.widgetFullName + "-disabled", null, !!value);
            if (value) {
                this._removeClass(this.hoverable, null, "ui-state-hover");
                this._removeClass(this.focusable, null, "ui-state-focus");
            }
        },
        enable: function() {
            return this._setOptions({
                disabled: false
            });
        },
        disable: function() {
            return this._setOptions({
                disabled: true
            });
        },
        _classes: function(options) {
            var full = [];
            var that = this;
            options = $.extend({
                element: this.element,
                classes: this.options.classes || {}
            }, options);
            function processClassString(classes, checkOption) {
                var current, i;
                for (i = 0; i < classes.length; i++) {
                    current = that.classesElementLookup[classes[i]] || $();
                    if (options.add) {
                        current = $($.unique(current.get().concat(options.element.get())));
                    } else {
                        current = $(current.not(options.element).get());
                    }
                    that.classesElementLookup[classes[i]] = current;
                    full.push(classes[i]);
                    if (checkOption && options.classes[classes[i]]) {
                        full.push(options.classes[classes[i]]);
                    }
                }
            }
            this._on(options.element, {
                remove: "_untrackClassesElement"
            });
            if (options.keys) {
                processClassString(options.keys.match(/\S+/g) || [], true);
            }
            if (options.extra) {
                processClassString(options.extra.match(/\S+/g) || []);
            }
            return full.join(" ");
        },
        _untrackClassesElement: function(event) {
            var that = this;
            $.each(that.classesElementLookup, function(key, value) {
                if ($.inArray(event.target, value) !== -1) {
                    that.classesElementLookup[key] = $(value.not(event.target).get());
                }
            });
        },
        _removeClass: function(element, keys, extra) {
            return this._toggleClass(element, keys, extra, false);
        },
        _addClass: function(element, keys, extra) {
            return this._toggleClass(element, keys, extra, true);
        },
        _toggleClass: function(element, keys, extra, add) {
            add = typeof add === "boolean" ? add : extra;
            var shift = typeof element === "string" || element === null, options = {
                extra: shift ? keys : extra,
                keys: shift ? element : keys,
                element: shift ? this.element : element,
                add: add
            };
            options.element.toggleClass(this._classes(options), add);
            return this;
        },
        _on: function(suppressDisabledCheck, element, handlers) {
            var delegateElement;
            var instance = this;
            if (typeof suppressDisabledCheck !== "boolean") {
                handlers = element;
                element = suppressDisabledCheck;
                suppressDisabledCheck = false;
            }
            if (!handlers) {
                handlers = element;
                element = this.element;
                delegateElement = this.widget();
            } else {
                element = delegateElement = $(element);
                this.bindings = this.bindings.add(element);
            }
            $.each(handlers, function(event, handler) {
                function handlerProxy() {
                    if (!suppressDisabledCheck && (instance.options.disabled === true || $(this).hasClass("ui-state-disabled"))) {
                        return;
                    }
                    return (typeof handler === "string" ? instance[handler] : handler).apply(instance, arguments);
                }
                if (typeof handler !== "string") {
                    handlerProxy.guid = handler.guid = handler.guid || handlerProxy.guid || $.guid++;
                }
                var match = event.match(/^([\w:-]*)\s*(.*)$/);
                var eventName = match[1] + instance.eventNamespace;
                var selector = match[2];
                if (selector) {
                    delegateElement.on(eventName, selector, handlerProxy);
                } else {
                    element.on(eventName, handlerProxy);
                }
            });
        },
        _off: function(element, eventName) {
            eventName = (eventName || "").split(" ").join(this.eventNamespace + " ") + this.eventNamespace;
            element.off(eventName).off(eventName);
            this.bindings = $(this.bindings.not(element).get());
            this.focusable = $(this.focusable.not(element).get());
            this.hoverable = $(this.hoverable.not(element).get());
        },
        _delay: function(handler, delay) {
            function handlerProxy() {
                return (typeof handler === "string" ? instance[handler] : handler).apply(instance, arguments);
            }
            var instance = this;
            return setTimeout(handlerProxy, delay || 0);
        },
        _hoverable: function(element) {
            this.hoverable = this.hoverable.add(element);
            this._on(element, {
                mouseenter: function(event) {
                    this._addClass($(event.currentTarget), null, "ui-state-hover");
                },
                mouseleave: function(event) {
                    this._removeClass($(event.currentTarget), null, "ui-state-hover");
                }
            });
        },
        _focusable: function(element) {
            this.focusable = this.focusable.add(element);
            this._on(element, {
                focusin: function(event) {
                    this._addClass($(event.currentTarget), null, "ui-state-focus");
                },
                focusout: function(event) {
                    this._removeClass($(event.currentTarget), null, "ui-state-focus");
                }
            });
        },
        _trigger: function(type, event, data) {
            var prop, orig;
            var callback = this.options[type];
            data = data || {};
            event = $.Event(event);
            event.type = (type === this.widgetEventPrefix ? type : this.widgetEventPrefix + type).toLowerCase();
            event.target = this.element[0];
            orig = event.originalEvent;
            if (orig) {
                for (prop in orig) {
                    if (!(prop in event)) {
                        event[prop] = orig[prop];
                    }
                }
            }
            this.element.trigger(event, data);
            return !($.isFunction(callback) && callback.apply(this.element[0], [ event ].concat(data)) === false || event.isDefaultPrevented());
        }
    };
    $.each({
        show: "fadeIn",
        hide: "fadeOut"
    }, function(method, defaultEffect) {
        $.Widget.prototype["_" + method] = function(element, options, callback) {
            if (typeof options === "string") {
                options = {
                    effect: options
                };
            }
            var hasOptions;
            var effectName = !options ? method : options === true || typeof options === "number" ? defaultEffect : options.effect || defaultEffect;
            options = options || {};
            if (typeof options === "number") {
                options = {
                    duration: options
                };
            }
            hasOptions = !$.isEmptyObject(options);
            options.complete = callback;
            if (options.delay) {
                element.delay(options.delay);
            }
            if (hasOptions && $.effects && $.effects.effect[effectName]) {
                element[method](options);
            } else if (effectName !== method && element[effectName]) {
                element[effectName](options.duration, options.easing, callback);
            } else {
                element.queue(function(next) {
                    $(this)[method]();
                    if (callback) {
                        callback.call(element[0]);
                    }
                    next();
                });
            }
        };
    });
    var widget = $.widget;
    (function() {
        var cachedScrollbarWidth, max = Math.max, abs = Math.abs, rhorizontal = /left|center|right/, rvertical = /top|center|bottom/, roffset = /[\+\-]\d+(\.[\d]+)?%?/, rposition = /^\w+/, rpercent = /%$/, _position = $.fn.position;
        function getOffsets(offsets, width, height) {
            return [ parseFloat(offsets[0]) * (rpercent.test(offsets[0]) ? width / 100 : 1), parseFloat(offsets[1]) * (rpercent.test(offsets[1]) ? height / 100 : 1) ];
        }
        function parseCss(element, property) {
            return parseInt($.css(element, property), 10) || 0;
        }
        function getDimensions(elem) {
            var raw = elem[0];
            if (raw.nodeType === 9) {
                return {
                    width: elem.width(),
                    height: elem.height(),
                    offset: {
                        top: 0,
                        left: 0
                    }
                };
            }
            if ($.isWindow(raw)) {
                return {
                    width: elem.width(),
                    height: elem.height(),
                    offset: {
                        top: elem.scrollTop(),
                        left: elem.scrollLeft()
                    }
                };
            }
            if (raw.preventDefault) {
                return {
                    width: 0,
                    height: 0,
                    offset: {
                        top: raw.pageY,
                        left: raw.pageX
                    }
                };
            }
            return {
                width: elem.outerWidth(),
                height: elem.outerHeight(),
                offset: elem.offset()
            };
        }
        $.position = {
            scrollbarWidth: function() {
                if (cachedScrollbarWidth !== undefined) {
                    return cachedScrollbarWidth;
                }
                var w1, w2, div = $("<div " + "style='display:block;position:absolute;width:50px;height:50px;overflow:hidden;'>" + "<div style='height:100px;width:auto;'></div></div>"), innerDiv = div.children()[0];
                $("body").append(div);
                w1 = innerDiv.offsetWidth;
                div.css("overflow", "scroll");
                w2 = innerDiv.offsetWidth;
                if (w1 === w2) {
                    w2 = div[0].clientWidth;
                }
                div.remove();
                return cachedScrollbarWidth = w1 - w2;
            },
            getScrollInfo: function(within) {
                var overflowX = within.isWindow || within.isDocument ? "" : within.element.css("overflow-x"), overflowY = within.isWindow || within.isDocument ? "" : within.element.css("overflow-y"), hasOverflowX = overflowX === "scroll" || overflowX === "auto" && within.width < within.element[0].scrollWidth, hasOverflowY = overflowY === "scroll" || overflowY === "auto" && within.height < within.element[0].scrollHeight;
                return {
                    width: hasOverflowY ? $.position.scrollbarWidth() : 0,
                    height: hasOverflowX ? $.position.scrollbarWidth() : 0
                };
            },
            getWithinInfo: function(element) {
                var withinElement = $(element || window), isWindow = $.isWindow(withinElement[0]), isDocument = !!withinElement[0] && withinElement[0].nodeType === 9, hasOffset = !isWindow && !isDocument;
                return {
                    element: withinElement,
                    isWindow: isWindow,
                    isDocument: isDocument,
                    offset: hasOffset ? $(element).offset() : {
                        left: 0,
                        top: 0
                    },
                    scrollLeft: withinElement.scrollLeft(),
                    scrollTop: withinElement.scrollTop(),
                    width: withinElement.outerWidth(),
                    height: withinElement.outerHeight()
                };
            }
        };
        $.fn.position = function(options) {
            if (!options || !options.of) {
                return _position.apply(this, arguments);
            }
            options = $.extend({}, options);
            var atOffset, targetWidth, targetHeight, targetOffset, basePosition, dimensions, target = $(options.of), within = $.position.getWithinInfo(options.within), scrollInfo = $.position.getScrollInfo(within), collision = (options.collision || "flip").split(" "), offsets = {};
            dimensions = getDimensions(target);
            if (target[0].preventDefault) {
                options.at = "left top";
            }
            targetWidth = dimensions.width;
            targetHeight = dimensions.height;
            targetOffset = dimensions.offset;
            basePosition = $.extend({}, targetOffset);
            $.each([ "my", "at" ], function() {
                var pos = (options[this] || "").split(" "), horizontalOffset, verticalOffset;
                if (pos.length === 1) {
                    pos = rhorizontal.test(pos[0]) ? pos.concat([ "center" ]) : rvertical.test(pos[0]) ? [ "center" ].concat(pos) : [ "center", "center" ];
                }
                pos[0] = rhorizontal.test(pos[0]) ? pos[0] : "center";
                pos[1] = rvertical.test(pos[1]) ? pos[1] : "center";
                horizontalOffset = roffset.exec(pos[0]);
                verticalOffset = roffset.exec(pos[1]);
                offsets[this] = [ horizontalOffset ? horizontalOffset[0] : 0, verticalOffset ? verticalOffset[0] : 0 ];
                options[this] = [ rposition.exec(pos[0])[0], rposition.exec(pos[1])[0] ];
            });
            if (collision.length === 1) {
                collision[1] = collision[0];
            }
            if (options.at[0] === "right") {
                basePosition.left += targetWidth;
            } else if (options.at[0] === "center") {
                basePosition.left += targetWidth / 2;
            }
            if (options.at[1] === "bottom") {
                basePosition.top += targetHeight;
            } else if (options.at[1] === "center") {
                basePosition.top += targetHeight / 2;
            }
            atOffset = getOffsets(offsets.at, targetWidth, targetHeight);
            basePosition.left += atOffset[0];
            basePosition.top += atOffset[1];
            return this.each(function() {
                var collisionPosition, using, elem = $(this), elemWidth = elem.outerWidth(), elemHeight = elem.outerHeight(), marginLeft = parseCss(this, "marginLeft"), marginTop = parseCss(this, "marginTop"), collisionWidth = elemWidth + marginLeft + parseCss(this, "marginRight") + scrollInfo.width, collisionHeight = elemHeight + marginTop + parseCss(this, "marginBottom") + scrollInfo.height, position = $.extend({}, basePosition), myOffset = getOffsets(offsets.my, elem.outerWidth(), elem.outerHeight());
                if (options.my[0] === "right") {
                    position.left -= elemWidth;
                } else if (options.my[0] === "center") {
                    position.left -= elemWidth / 2;
                }
                if (options.my[1] === "bottom") {
                    position.top -= elemHeight;
                } else if (options.my[1] === "center") {
                    position.top -= elemHeight / 2;
                }
                position.left += myOffset[0];
                position.top += myOffset[1];
                collisionPosition = {
                    marginLeft: marginLeft,
                    marginTop: marginTop
                };
                $.each([ "left", "top" ], function(i, dir) {
                    if ($.ui.position[collision[i]]) {
                        $.ui.position[collision[i]][dir](position, {
                            targetWidth: targetWidth,
                            targetHeight: targetHeight,
                            elemWidth: elemWidth,
                            elemHeight: elemHeight,
                            collisionPosition: collisionPosition,
                            collisionWidth: collisionWidth,
                            collisionHeight: collisionHeight,
                            offset: [ atOffset[0] + myOffset[0], atOffset[1] + myOffset[1] ],
                            my: options.my,
                            at: options.at,
                            within: within,
                            elem: elem
                        });
                    }
                });
                if (options.using) {
                    using = function(props) {
                        var left = targetOffset.left - position.left, right = left + targetWidth - elemWidth, top = targetOffset.top - position.top, bottom = top + targetHeight - elemHeight, feedback = {
                            target: {
                                element: target,
                                left: targetOffset.left,
                                top: targetOffset.top,
                                width: targetWidth,
                                height: targetHeight
                            },
                            element: {
                                element: elem,
                                left: position.left,
                                top: position.top,
                                width: elemWidth,
                                height: elemHeight
                            },
                            horizontal: right < 0 ? "left" : left > 0 ? "right" : "center",
                            vertical: bottom < 0 ? "top" : top > 0 ? "bottom" : "middle"
                        };
                        if (targetWidth < elemWidth && abs(left + right) < targetWidth) {
                            feedback.horizontal = "center";
                        }
                        if (targetHeight < elemHeight && abs(top + bottom) < targetHeight) {
                            feedback.vertical = "middle";
                        }
                        if (max(abs(left), abs(right)) > max(abs(top), abs(bottom))) {
                            feedback.important = "horizontal";
                        } else {
                            feedback.important = "vertical";
                        }
                        options.using.call(this, props, feedback);
                    };
                }
                elem.offset($.extend(position, {
                    using: using
                }));
            });
        };
        $.ui.position = {
            fit: {
                left: function(position, data) {
                    var within = data.within, withinOffset = within.isWindow ? within.scrollLeft : within.offset.left, outerWidth = within.width, collisionPosLeft = position.left - data.collisionPosition.marginLeft, overLeft = withinOffset - collisionPosLeft, overRight = collisionPosLeft + data.collisionWidth - outerWidth - withinOffset, newOverRight;
                    if (data.collisionWidth > outerWidth) {
                        if (overLeft > 0 && overRight <= 0) {
                            newOverRight = position.left + overLeft + data.collisionWidth - outerWidth - withinOffset;
                            position.left += overLeft - newOverRight;
                        } else if (overRight > 0 && overLeft <= 0) {
                            position.left = withinOffset;
                        } else {
                            if (overLeft > overRight) {
                                position.left = withinOffset + outerWidth - data.collisionWidth;
                            } else {
                                position.left = withinOffset;
                            }
                        }
                    } else if (overLeft > 0) {
                        position.left += overLeft;
                    } else if (overRight > 0) {
                        position.left -= overRight;
                    } else {
                        position.left = max(position.left - collisionPosLeft, position.left);
                    }
                },
                top: function(position, data) {
                    var within = data.within, withinOffset = within.isWindow ? within.scrollTop : within.offset.top, outerHeight = data.within.height, collisionPosTop = position.top - data.collisionPosition.marginTop, overTop = withinOffset - collisionPosTop, overBottom = collisionPosTop + data.collisionHeight - outerHeight - withinOffset, newOverBottom;
                    if (data.collisionHeight > outerHeight) {
                        if (overTop > 0 && overBottom <= 0) {
                            newOverBottom = position.top + overTop + data.collisionHeight - outerHeight - withinOffset;
                            position.top += overTop - newOverBottom;
                        } else if (overBottom > 0 && overTop <= 0) {
                            position.top = withinOffset;
                        } else {
                            if (overTop > overBottom) {
                                position.top = withinOffset + outerHeight - data.collisionHeight;
                            } else {
                                position.top = withinOffset;
                            }
                        }
                    } else if (overTop > 0) {
                        position.top += overTop;
                    } else if (overBottom > 0) {
                        position.top -= overBottom;
                    } else {
                        position.top = max(position.top - collisionPosTop, position.top);
                    }
                }
            },
            flip: {
                left: function(position, data) {
                    var within = data.within, withinOffset = within.offset.left + within.scrollLeft, outerWidth = within.width, offsetLeft = within.isWindow ? within.scrollLeft : within.offset.left, collisionPosLeft = position.left - data.collisionPosition.marginLeft, overLeft = collisionPosLeft - offsetLeft, overRight = collisionPosLeft + data.collisionWidth - outerWidth - offsetLeft, myOffset = data.my[0] === "left" ? -data.elemWidth : data.my[0] === "right" ? data.elemWidth : 0, atOffset = data.at[0] === "left" ? data.targetWidth : data.at[0] === "right" ? -data.targetWidth : 0, offset = -2 * data.offset[0], newOverRight, newOverLeft;
                    if (overLeft < 0) {
                        newOverRight = position.left + myOffset + atOffset + offset + data.collisionWidth - outerWidth - withinOffset;
                        if (newOverRight < 0 || newOverRight < abs(overLeft)) {
                            position.left += myOffset + atOffset + offset;
                        }
                    } else if (overRight > 0) {
                        newOverLeft = position.left - data.collisionPosition.marginLeft + myOffset + atOffset + offset - offsetLeft;
                        if (newOverLeft > 0 || abs(newOverLeft) < overRight) {
                            position.left += myOffset + atOffset + offset;
                        }
                    }
                },
                top: function(position, data) {
                    var within = data.within, withinOffset = within.offset.top + within.scrollTop, outerHeight = within.height, offsetTop = within.isWindow ? within.scrollTop : within.offset.top, collisionPosTop = position.top - data.collisionPosition.marginTop, overTop = collisionPosTop - offsetTop, overBottom = collisionPosTop + data.collisionHeight - outerHeight - offsetTop, top = data.my[1] === "top", myOffset = top ? -data.elemHeight : data.my[1] === "bottom" ? data.elemHeight : 0, atOffset = data.at[1] === "top" ? data.targetHeight : data.at[1] === "bottom" ? -data.targetHeight : 0, offset = -2 * data.offset[1], newOverTop, newOverBottom;
                    if (overTop < 0) {
                        newOverBottom = position.top + myOffset + atOffset + offset + data.collisionHeight - outerHeight - withinOffset;
                        if (newOverBottom < 0 || newOverBottom < abs(overTop)) {
                            position.top += myOffset + atOffset + offset;
                        }
                    } else if (overBottom > 0) {
                        newOverTop = position.top - data.collisionPosition.marginTop + myOffset + atOffset + offset - offsetTop;
                        if (newOverTop > 0 || abs(newOverTop) < overBottom) {
                            position.top += myOffset + atOffset + offset;
                        }
                    }
                }
            },
            flipfit: {
                left: function() {
                    $.ui.position.flip.left.apply(this, arguments);
                    $.ui.position.fit.left.apply(this, arguments);
                },
                top: function() {
                    $.ui.position.flip.top.apply(this, arguments);
                    $.ui.position.fit.top.apply(this, arguments);
                }
            }
        };
    })();
    var position = $.ui.position;
    var data = $.extend($.expr[":"], {
        data: $.expr.createPseudo ? $.expr.createPseudo(function(dataName) {
            return function(elem) {
                return !!$.data(elem, dataName);
            };
        }) : function(elem, i, match) {
            return !!$.data(elem, match[3]);
        }
    });
    var disableSelection = $.fn.extend({
        disableSelection: function() {
            var eventType = "onselectstart" in document.createElement("div") ? "selectstart" : "mousedown";
            return function() {
                return this.on(eventType + ".ui-disableSelection", function(event) {
                    event.preventDefault();
                });
            };
        }(),
        enableSelection: function() {
            return this.off(".ui-disableSelection");
        }
    });
    var dataSpace = "ui-effects-", dataSpaceStyle = "ui-effects-style", dataSpaceAnimated = "ui-effects-animated", jQuery = $;
    $.effects = {
        effect: {}
    };
    (function(jQuery, undefined) {
        var stepHooks = "backgroundColor borderBottomColor borderLeftColor borderRightColor " + "borderTopColor color columnRuleColor outlineColor textDecorationColor textEmphasisColor", rplusequals = /^([\-+])=\s*(\d+\.?\d*)/, stringParsers = [ {
            re: /rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
            parse: function(execResult) {
                return [ execResult[1], execResult[2], execResult[3], execResult[4] ];
            }
        }, {
            re: /rgba?\(\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
            parse: function(execResult) {
                return [ execResult[1] * 2.55, execResult[2] * 2.55, execResult[3] * 2.55, execResult[4] ];
            }
        }, {
            re: /#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/,
            parse: function(execResult) {
                return [ parseInt(execResult[1], 16), parseInt(execResult[2], 16), parseInt(execResult[3], 16) ];
            }
        }, {
            re: /#([a-f0-9])([a-f0-9])([a-f0-9])/,
            parse: function(execResult) {
                return [ parseInt(execResult[1] + execResult[1], 16), parseInt(execResult[2] + execResult[2], 16), parseInt(execResult[3] + execResult[3], 16) ];
            }
        }, {
            re: /hsla?\(\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
            space: "hsla",
            parse: function(execResult) {
                return [ execResult[1], execResult[2] / 100, execResult[3] / 100, execResult[4] ];
            }
        } ], color = jQuery.Color = function(color, green, blue, alpha) {
            return new jQuery.Color.fn.parse(color, green, blue, alpha);
        }, spaces = {
            rgba: {
                props: {
                    red: {
                        idx: 0,
                        type: "byte"
                    },
                    green: {
                        idx: 1,
                        type: "byte"
                    },
                    blue: {
                        idx: 2,
                        type: "byte"
                    }
                }
            },
            hsla: {
                props: {
                    hue: {
                        idx: 0,
                        type: "degrees"
                    },
                    saturation: {
                        idx: 1,
                        type: "percent"
                    },
                    lightness: {
                        idx: 2,
                        type: "percent"
                    }
                }
            }
        }, propTypes = {
            byte: {
                floor: true,
                max: 255
            },
            percent: {
                max: 1
            },
            degrees: {
                mod: 360,
                floor: true
            }
        }, support = color.support = {}, supportElem = jQuery("<p>")[0], colors, each = jQuery.each;
        supportElem.style.cssText = "background-color:rgba(1,1,1,.5)";
        support.rgba = supportElem.style.backgroundColor.indexOf("rgba") > -1;
        each(spaces, function(spaceName, space) {
            space.cache = "_" + spaceName;
            space.props.alpha = {
                idx: 3,
                type: "percent",
                def: 1
            };
        });
        function clamp(value, prop, allowEmpty) {
            var type = propTypes[prop.type] || {};
            if (value == null) {
                return allowEmpty || !prop.def ? null : prop.def;
            }
            value = type.floor ? ~~value : parseFloat(value);
            if (isNaN(value)) {
                return prop.def;
            }
            if (type.mod) {
                return (value + type.mod) % type.mod;
            }
            return 0 > value ? 0 : type.max < value ? type.max : value;
        }
        function stringParse(string) {
            var inst = color(), rgba = inst._rgba = [];
            string = string.toLowerCase();
            each(stringParsers, function(i, parser) {
                var parsed, match = parser.re.exec(string), values = match && parser.parse(match), spaceName = parser.space || "rgba";
                if (values) {
                    parsed = inst[spaceName](values);
                    inst[spaces[spaceName].cache] = parsed[spaces[spaceName].cache];
                    rgba = inst._rgba = parsed._rgba;
                    return false;
                }
            });
            if (rgba.length) {
                if (rgba.join() === "0,0,0,0") {
                    jQuery.extend(rgba, colors.transparent);
                }
                return inst;
            }
            return colors[string];
        }
        color.fn = jQuery.extend(color.prototype, {
            parse: function(red, green, blue, alpha) {
                if (red === undefined) {
                    this._rgba = [ null, null, null, null ];
                    return this;
                }
                if (red.jquery || red.nodeType) {
                    red = jQuery(red).css(green);
                    green = undefined;
                }
                var inst = this, type = jQuery.type(red), rgba = this._rgba = [];
                if (green !== undefined) {
                    red = [ red, green, blue, alpha ];
                    type = "array";
                }
                if (type === "string") {
                    return this.parse(stringParse(red) || colors._default);
                }
                if (type === "array") {
                    each(spaces.rgba.props, function(key, prop) {
                        rgba[prop.idx] = clamp(red[prop.idx], prop);
                    });
                    return this;
                }
                if (type === "object") {
                    if (red instanceof color) {
                        each(spaces, function(spaceName, space) {
                            if (red[space.cache]) {
                                inst[space.cache] = red[space.cache].slice();
                            }
                        });
                    } else {
                        each(spaces, function(spaceName, space) {
                            var cache = space.cache;
                            each(space.props, function(key, prop) {
                                if (!inst[cache] && space.to) {
                                    if (key === "alpha" || red[key] == null) {
                                        return;
                                    }
                                    inst[cache] = space.to(inst._rgba);
                                }
                                inst[cache][prop.idx] = clamp(red[key], prop, true);
                            });
                            if (inst[cache] && jQuery.inArray(null, inst[cache].slice(0, 3)) < 0) {
                                inst[cache][3] = 1;
                                if (space.from) {
                                    inst._rgba = space.from(inst[cache]);
                                }
                            }
                        });
                    }
                    return this;
                }
            },
            is: function(compare) {
                var is = color(compare), same = true, inst = this;
                each(spaces, function(_, space) {
                    var localCache, isCache = is[space.cache];
                    if (isCache) {
                        localCache = inst[space.cache] || space.to && space.to(inst._rgba) || [];
                        each(space.props, function(_, prop) {
                            if (isCache[prop.idx] != null) {
                                same = isCache[prop.idx] === localCache[prop.idx];
                                return same;
                            }
                        });
                    }
                    return same;
                });
                return same;
            },
            _space: function() {
                var used = [], inst = this;
                each(spaces, function(spaceName, space) {
                    if (inst[space.cache]) {
                        used.push(spaceName);
                    }
                });
                return used.pop();
            },
            transition: function(other, distance) {
                var end = color(other), spaceName = end._space(), space = spaces[spaceName], startColor = this.alpha() === 0 ? color("transparent") : this, start = startColor[space.cache] || space.to(startColor._rgba), result = start.slice();
                end = end[space.cache];
                each(space.props, function(key, prop) {
                    var index = prop.idx, startValue = start[index], endValue = end[index], type = propTypes[prop.type] || {};
                    if (endValue === null) {
                        return;
                    }
                    if (startValue === null) {
                        result[index] = endValue;
                    } else {
                        if (type.mod) {
                            if (endValue - startValue > type.mod / 2) {
                                startValue += type.mod;
                            } else if (startValue - endValue > type.mod / 2) {
                                startValue -= type.mod;
                            }
                        }
                        result[index] = clamp((endValue - startValue) * distance + startValue, prop);
                    }
                });
                return this[spaceName](result);
            },
            blend: function(opaque) {
                if (this._rgba[3] === 1) {
                    return this;
                }
                var rgb = this._rgba.slice(), a = rgb.pop(), blend = color(opaque)._rgba;
                return color(jQuery.map(rgb, function(v, i) {
                    return (1 - a) * blend[i] + a * v;
                }));
            },
            toRgbaString: function() {
                var prefix = "rgba(", rgba = jQuery.map(this._rgba, function(v, i) {
                    return v == null ? i > 2 ? 1 : 0 : v;
                });
                if (rgba[3] === 1) {
                    rgba.pop();
                    prefix = "rgb(";
                }
                return prefix + rgba.join() + ")";
            },
            toHslaString: function() {
                var prefix = "hsla(", hsla = jQuery.map(this.hsla(), function(v, i) {
                    if (v == null) {
                        v = i > 2 ? 1 : 0;
                    }
                    if (i && i < 3) {
                        v = Math.round(v * 100) + "%";
                    }
                    return v;
                });
                if (hsla[3] === 1) {
                    hsla.pop();
                    prefix = "hsl(";
                }
                return prefix + hsla.join() + ")";
            },
            toHexString: function(includeAlpha) {
                var rgba = this._rgba.slice(), alpha = rgba.pop();
                if (includeAlpha) {
                    rgba.push(~~(alpha * 255));
                }
                return "#" + jQuery.map(rgba, function(v) {
                    v = (v || 0).toString(16);
                    return v.length === 1 ? "0" + v : v;
                }).join("");
            },
            toString: function() {
                return this._rgba[3] === 0 ? "transparent" : this.toRgbaString();
            }
        });
        color.fn.parse.prototype = color.fn;
        function hue2rgb(p, q, h) {
            h = (h + 1) % 1;
            if (h * 6 < 1) {
                return p + (q - p) * h * 6;
            }
            if (h * 2 < 1) {
                return q;
            }
            if (h * 3 < 2) {
                return p + (q - p) * (2 / 3 - h) * 6;
            }
            return p;
        }
        spaces.hsla.to = function(rgba) {
            if (rgba[0] == null || rgba[1] == null || rgba[2] == null) {
                return [ null, null, null, rgba[3] ];
            }
            var r = rgba[0] / 255, g = rgba[1] / 255, b = rgba[2] / 255, a = rgba[3], max = Math.max(r, g, b), min = Math.min(r, g, b), diff = max - min, add = max + min, l = add * .5, h, s;
            if (min === max) {
                h = 0;
            } else if (r === max) {
                h = 60 * (g - b) / diff + 360;
            } else if (g === max) {
                h = 60 * (b - r) / diff + 120;
            } else {
                h = 60 * (r - g) / diff + 240;
            }
            if (diff === 0) {
                s = 0;
            } else if (l <= .5) {
                s = diff / add;
            } else {
                s = diff / (2 - add);
            }
            return [ Math.round(h) % 360, s, l, a == null ? 1 : a ];
        };
        spaces.hsla.from = function(hsla) {
            if (hsla[0] == null || hsla[1] == null || hsla[2] == null) {
                return [ null, null, null, hsla[3] ];
            }
            var h = hsla[0] / 360, s = hsla[1], l = hsla[2], a = hsla[3], q = l <= .5 ? l * (1 + s) : l + s - l * s, p = 2 * l - q;
            return [ Math.round(hue2rgb(p, q, h + 1 / 3) * 255), Math.round(hue2rgb(p, q, h) * 255), Math.round(hue2rgb(p, q, h - 1 / 3) * 255), a ];
        };
        each(spaces, function(spaceName, space) {
            var props = space.props, cache = space.cache, to = space.to, from = space.from;
            color.fn[spaceName] = function(value) {
                if (to && !this[cache]) {
                    this[cache] = to(this._rgba);
                }
                if (value === undefined) {
                    return this[cache].slice();
                }
                var ret, type = jQuery.type(value), arr = type === "array" || type === "object" ? value : arguments, local = this[cache].slice();
                each(props, function(key, prop) {
                    var val = arr[type === "object" ? key : prop.idx];
                    if (val == null) {
                        val = local[prop.idx];
                    }
                    local[prop.idx] = clamp(val, prop);
                });
                if (from) {
                    ret = color(from(local));
                    ret[cache] = local;
                    return ret;
                } else {
                    return color(local);
                }
            };
            each(props, function(key, prop) {
                if (color.fn[key]) {
                    return;
                }
                color.fn[key] = function(value) {
                    var vtype = jQuery.type(value), fn = key === "alpha" ? this._hsla ? "hsla" : "rgba" : spaceName, local = this[fn](), cur = local[prop.idx], match;
                    if (vtype === "undefined") {
                        return cur;
                    }
                    if (vtype === "function") {
                        value = value.call(this, cur);
                        vtype = jQuery.type(value);
                    }
                    if (value == null && prop.empty) {
                        return this;
                    }
                    if (vtype === "string") {
                        match = rplusequals.exec(value);
                        if (match) {
                            value = cur + parseFloat(match[2]) * (match[1] === "+" ? 1 : -1);
                        }
                    }
                    local[prop.idx] = value;
                    return this[fn](local);
                };
            });
        });
        color.hook = function(hook) {
            var hooks = hook.split(" ");
            each(hooks, function(i, hook) {
                jQuery.cssHooks[hook] = {
                    set: function(elem, value) {
                        var parsed, curElem, backgroundColor = "";
                        if (value !== "transparent" && (jQuery.type(value) !== "string" || (parsed = stringParse(value)))) {
                            value = color(parsed || value);
                            if (!support.rgba && value._rgba[3] !== 1) {
                                curElem = hook === "backgroundColor" ? elem.parentNode : elem;
                                while ((backgroundColor === "" || backgroundColor === "transparent") && curElem && curElem.style) {
                                    try {
                                        backgroundColor = jQuery.css(curElem, "backgroundColor");
                                        curElem = curElem.parentNode;
                                    } catch (e) {}
                                }
                                value = value.blend(backgroundColor && backgroundColor !== "transparent" ? backgroundColor : "_default");
                            }
                            value = value.toRgbaString();
                        }
                        try {
                            elem.style[hook] = value;
                        } catch (e) {}
                    }
                };
                jQuery.fx.step[hook] = function(fx) {
                    if (!fx.colorInit) {
                        fx.start = color(fx.elem, hook);
                        fx.end = color(fx.end);
                        fx.colorInit = true;
                    }
                    jQuery.cssHooks[hook].set(fx.elem, fx.start.transition(fx.end, fx.pos));
                };
            });
        };
        color.hook(stepHooks);
        jQuery.cssHooks.borderColor = {
            expand: function(value) {
                var expanded = {};
                each([ "Top", "Right", "Bottom", "Left" ], function(i, part) {
                    expanded["border" + part + "Color"] = value;
                });
                return expanded;
            }
        };
        colors = jQuery.Color.names = {
            aqua: "#00ffff",
            black: "#000000",
            blue: "#0000ff",
            fuchsia: "#ff00ff",
            gray: "#808080",
            green: "#008000",
            lime: "#00ff00",
            maroon: "#800000",
            navy: "#000080",
            olive: "#808000",
            purple: "#800080",
            red: "#ff0000",
            silver: "#c0c0c0",
            teal: "#008080",
            white: "#ffffff",
            yellow: "#ffff00",
            transparent: [ null, null, null, 0 ],
            _default: "#ffffff"
        };
    })(jQuery);
    (function() {
        var classAnimationActions = [ "add", "remove", "toggle" ], shorthandStyles = {
            border: 1,
            borderBottom: 1,
            borderColor: 1,
            borderLeft: 1,
            borderRight: 1,
            borderTop: 1,
            borderWidth: 1,
            margin: 1,
            padding: 1
        };
        $.each([ "borderLeftStyle", "borderRightStyle", "borderBottomStyle", "borderTopStyle" ], function(_, prop) {
            $.fx.step[prop] = function(fx) {
                if (fx.end !== "none" && !fx.setAttr || fx.pos === 1 && !fx.setAttr) {
                    jQuery.style(fx.elem, prop, fx.end);
                    fx.setAttr = true;
                }
            };
        });
        function getElementStyles(elem) {
            var key, len, style = elem.ownerDocument.defaultView ? elem.ownerDocument.defaultView.getComputedStyle(elem, null) : elem.currentStyle, styles = {};
            if (style && style.length && style[0] && style[style[0]]) {
                len = style.length;
                while (len--) {
                    key = style[len];
                    if (typeof style[key] === "string") {
                        styles[$.camelCase(key)] = style[key];
                    }
                }
            } else {
                for (key in style) {
                    if (typeof style[key] === "string") {
                        styles[key] = style[key];
                    }
                }
            }
            return styles;
        }
        function styleDifference(oldStyle, newStyle) {
            var diff = {}, name, value;
            for (name in newStyle) {
                value = newStyle[name];
                if (oldStyle[name] !== value) {
                    if (!shorthandStyles[name]) {
                        if ($.fx.step[name] || !isNaN(parseFloat(value))) {
                            diff[name] = value;
                        }
                    }
                }
            }
            return diff;
        }
        if (!$.fn.addBack) {
            $.fn.addBack = function(selector) {
                return this.add(selector == null ? this.prevObject : this.prevObject.filter(selector));
            };
        }
        $.effects.animateClass = function(value, duration, easing, callback) {
            var o = $.speed(duration, easing, callback);
            return this.queue(function() {
                var animated = $(this), baseClass = animated.attr("class") || "", applyClassChange, allAnimations = o.children ? animated.find("*").addBack() : animated;
                allAnimations = allAnimations.map(function() {
                    var el = $(this);
                    return {
                        el: el,
                        start: getElementStyles(this)
                    };
                });
                applyClassChange = function() {
                    $.each(classAnimationActions, function(i, action) {
                        if (value[action]) {
                            animated[action + "Class"](value[action]);
                        }
                    });
                };
                applyClassChange();
                allAnimations = allAnimations.map(function() {
                    this.end = getElementStyles(this.el[0]);
                    this.diff = styleDifference(this.start, this.end);
                    return this;
                });
                animated.attr("class", baseClass);
                allAnimations = allAnimations.map(function() {
                    var styleInfo = this, dfd = $.Deferred(), opts = $.extend({}, o, {
                        queue: false,
                        complete: function() {
                            dfd.resolve(styleInfo);
                        }
                    });
                    this.el.animate(this.diff, opts);
                    return dfd.promise();
                });
                $.when.apply($, allAnimations.get()).done(function() {
                    applyClassChange();
                    $.each(arguments, function() {
                        var el = this.el;
                        $.each(this.diff, function(key) {
                            el.css(key, "");
                        });
                    });
                    o.complete.call(animated[0]);
                });
            });
        };
        $.fn.extend({
            addClass: function(orig) {
                return function(classNames, speed, easing, callback) {
                    return speed ? $.effects.animateClass.call(this, {
                        add: classNames
                    }, speed, easing, callback) : orig.apply(this, arguments);
                };
            }($.fn.addClass),
            removeClass: function(orig) {
                return function(classNames, speed, easing, callback) {
                    return arguments.length > 1 ? $.effects.animateClass.call(this, {
                        remove: classNames
                    }, speed, easing, callback) : orig.apply(this, arguments);
                };
            }($.fn.removeClass),
            toggleClass: function(orig) {
                return function(classNames, force, speed, easing, callback) {
                    if (typeof force === "boolean" || force === undefined) {
                        if (!speed) {
                            return orig.apply(this, arguments);
                        } else {
                            return $.effects.animateClass.call(this, force ? {
                                add: classNames
                            } : {
                                remove: classNames
                            }, speed, easing, callback);
                        }
                    } else {
                        return $.effects.animateClass.call(this, {
                            toggle: classNames
                        }, force, speed, easing);
                    }
                };
            }($.fn.toggleClass),
            switchClass: function(remove, add, speed, easing, callback) {
                return $.effects.animateClass.call(this, {
                    add: add,
                    remove: remove
                }, speed, easing, callback);
            }
        });
    })();
    (function() {
        if ($.expr && $.expr.filters && $.expr.filters.animated) {
            $.expr.filters.animated = function(orig) {
                return function(elem) {
                    return !!$(elem).data(dataSpaceAnimated) || orig(elem);
                };
            }($.expr.filters.animated);
        }
        if ($.uiBackCompat !== false) {
            $.extend($.effects, {
                save: function(element, set) {
                    var i = 0, length = set.length;
                    for (;i < length; i++) {
                        if (set[i] !== null) {
                            element.data(dataSpace + set[i], element[0].style[set[i]]);
                        }
                    }
                },
                restore: function(element, set) {
                    var val, i = 0, length = set.length;
                    for (;i < length; i++) {
                        if (set[i] !== null) {
                            val = element.data(dataSpace + set[i]);
                            element.css(set[i], val);
                        }
                    }
                },
                setMode: function(el, mode) {
                    if (mode === "toggle") {
                        mode = el.is(":hidden") ? "show" : "hide";
                    }
                    return mode;
                },
                createWrapper: function(element) {
                    if (element.parent().is(".ui-effects-wrapper")) {
                        return element.parent();
                    }
                    var props = {
                        width: element.outerWidth(true),
                        height: element.outerHeight(true),
                        float: element.css("float")
                    }, wrapper = $("<div></div>").addClass("ui-effects-wrapper").css({
                        fontSize: "100%",
                        background: "transparent",
                        border: "none",
                        margin: 0,
                        padding: 0
                    }), size = {
                        width: element.width(),
                        height: element.height()
                    }, active = document.activeElement;
                    try {
                        active.id;
                    } catch (e) {
                        active = document.body;
                    }
                    element.wrap(wrapper);
                    if (element[0] === active || $.contains(element[0], active)) {
                        $(active).trigger("focus");
                    }
                    wrapper = element.parent();
                    if (element.css("position") === "static") {
                        wrapper.css({
                            position: "relative"
                        });
                        element.css({
                            position: "relative"
                        });
                    } else {
                        $.extend(props, {
                            position: element.css("position"),
                            zIndex: element.css("z-index")
                        });
                        $.each([ "top", "left", "bottom", "right" ], function(i, pos) {
                            props[pos] = element.css(pos);
                            if (isNaN(parseInt(props[pos], 10))) {
                                props[pos] = "auto";
                            }
                        });
                        element.css({
                            position: "relative",
                            top: 0,
                            left: 0,
                            right: "auto",
                            bottom: "auto"
                        });
                    }
                    element.css(size);
                    return wrapper.css(props).show();
                },
                removeWrapper: function(element) {
                    var active = document.activeElement;
                    if (element.parent().is(".ui-effects-wrapper")) {
                        element.parent().replaceWith(element);
                        if (element[0] === active || $.contains(element[0], active)) {
                            $(active).trigger("focus");
                        }
                    }
                    return element;
                }
            });
        }
        $.extend($.effects, {
            version: "1.12.1",
            define: function(name, mode, effect) {
                if (!effect) {
                    effect = mode;
                    mode = "effect";
                }
                $.effects.effect[name] = effect;
                $.effects.effect[name].mode = mode;
                return effect;
            },
            scaledDimensions: function(element, percent, direction) {
                if (percent === 0) {
                    return {
                        height: 0,
                        width: 0,
                        outerHeight: 0,
                        outerWidth: 0
                    };
                }
                var x = direction !== "horizontal" ? (percent || 100) / 100 : 1, y = direction !== "vertical" ? (percent || 100) / 100 : 1;
                return {
                    height: element.height() * y,
                    width: element.width() * x,
                    outerHeight: element.outerHeight() * y,
                    outerWidth: element.outerWidth() * x
                };
            },
            clipToBox: function(animation) {
                return {
                    width: animation.clip.right - animation.clip.left,
                    height: animation.clip.bottom - animation.clip.top,
                    left: animation.clip.left,
                    top: animation.clip.top
                };
            },
            unshift: function(element, queueLength, count) {
                var queue = element.queue();
                if (queueLength > 1) {
                    queue.splice.apply(queue, [ 1, 0 ].concat(queue.splice(queueLength, count)));
                }
                element.dequeue();
            },
            saveStyle: function(element) {
                element.data(dataSpaceStyle, element[0].style.cssText);
            },
            restoreStyle: function(element) {
                element[0].style.cssText = element.data(dataSpaceStyle) || "";
                element.removeData(dataSpaceStyle);
            },
            mode: function(element, mode) {
                var hidden = element.is(":hidden");
                if (mode === "toggle") {
                    mode = hidden ? "show" : "hide";
                }
                if (hidden ? mode === "hide" : mode === "show") {
                    mode = "none";
                }
                return mode;
            },
            getBaseline: function(origin, original) {
                var y, x;
                switch (origin[0]) {
                  case "top":
                    y = 0;
                    break;

                  case "middle":
                    y = .5;
                    break;

                  case "bottom":
                    y = 1;
                    break;

                  default:
                    y = origin[0] / original.height;
                }
                switch (origin[1]) {
                  case "left":
                    x = 0;
                    break;

                  case "center":
                    x = .5;
                    break;

                  case "right":
                    x = 1;
                    break;

                  default:
                    x = origin[1] / original.width;
                }
                return {
                    x: x,
                    y: y
                };
            },
            createPlaceholder: function(element) {
                var placeholder, cssPosition = element.css("position"), position = element.position();
                element.css({
                    marginTop: element.css("marginTop"),
                    marginBottom: element.css("marginBottom"),
                    marginLeft: element.css("marginLeft"),
                    marginRight: element.css("marginRight")
                }).outerWidth(element.outerWidth()).outerHeight(element.outerHeight());
                if (/^(static|relative)/.test(cssPosition)) {
                    cssPosition = "absolute";
                    placeholder = $("<" + element[0].nodeName + ">").insertAfter(element).css({
                        display: /^(inline|ruby)/.test(element.css("display")) ? "inline-block" : "block",
                        visibility: "hidden",
                        marginTop: element.css("marginTop"),
                        marginBottom: element.css("marginBottom"),
                        marginLeft: element.css("marginLeft"),
                        marginRight: element.css("marginRight"),
                        float: element.css("float")
                    }).outerWidth(element.outerWidth()).outerHeight(element.outerHeight()).addClass("ui-effects-placeholder");
                    element.data(dataSpace + "placeholder", placeholder);
                }
                element.css({
                    position: cssPosition,
                    left: position.left,
                    top: position.top
                });
                return placeholder;
            },
            removePlaceholder: function(element) {
                var dataKey = dataSpace + "placeholder", placeholder = element.data(dataKey);
                if (placeholder) {
                    placeholder.remove();
                    element.removeData(dataKey);
                }
            },
            cleanUp: function(element) {
                $.effects.restoreStyle(element);
                $.effects.removePlaceholder(element);
            },
            setTransition: function(element, list, factor, value) {
                value = value || {};
                $.each(list, function(i, x) {
                    var unit = element.cssUnit(x);
                    if (unit[0] > 0) {
                        value[x] = unit[0] * factor + unit[1];
                    }
                });
                return value;
            }
        });
        function _normalizeArguments(effect, options, speed, callback) {
            if ($.isPlainObject(effect)) {
                options = effect;
                effect = effect.effect;
            }
            effect = {
                effect: effect
            };
            if (options == null) {
                options = {};
            }
            if ($.isFunction(options)) {
                callback = options;
                speed = null;
                options = {};
            }
            if (typeof options === "number" || $.fx.speeds[options]) {
                callback = speed;
                speed = options;
                options = {};
            }
            if ($.isFunction(speed)) {
                callback = speed;
                speed = null;
            }
            if (options) {
                $.extend(effect, options);
            }
            speed = speed || options.duration;
            effect.duration = $.fx.off ? 0 : typeof speed === "number" ? speed : speed in $.fx.speeds ? $.fx.speeds[speed] : $.fx.speeds._default;
            effect.complete = callback || options.complete;
            return effect;
        }
        function standardAnimationOption(option) {
            if (!option || typeof option === "number" || $.fx.speeds[option]) {
                return true;
            }
            if (typeof option === "string" && !$.effects.effect[option]) {
                return true;
            }
            if ($.isFunction(option)) {
                return true;
            }
            if (typeof option === "object" && !option.effect) {
                return true;
            }
            return false;
        }
        $.fn.extend({
            effect: function() {
                var args = _normalizeArguments.apply(this, arguments), effectMethod = $.effects.effect[args.effect], defaultMode = effectMethod.mode, queue = args.queue, queueName = queue || "fx", complete = args.complete, mode = args.mode, modes = [], prefilter = function(next) {
                    var el = $(this), normalizedMode = $.effects.mode(el, mode) || defaultMode;
                    el.data(dataSpaceAnimated, true);
                    modes.push(normalizedMode);
                    if (defaultMode && (normalizedMode === "show" || normalizedMode === defaultMode && normalizedMode === "hide")) {
                        el.show();
                    }
                    if (!defaultMode || normalizedMode !== "none") {
                        $.effects.saveStyle(el);
                    }
                    if ($.isFunction(next)) {
                        next();
                    }
                };
                if ($.fx.off || !effectMethod) {
                    if (mode) {
                        return this[mode](args.duration, complete);
                    } else {
                        return this.each(function() {
                            if (complete) {
                                complete.call(this);
                            }
                        });
                    }
                }
                function run(next) {
                    var elem = $(this);
                    function cleanup() {
                        elem.removeData(dataSpaceAnimated);
                        $.effects.cleanUp(elem);
                        if (args.mode === "hide") {
                            elem.hide();
                        }
                        done();
                    }
                    function done() {
                        if ($.isFunction(complete)) {
                            complete.call(elem[0]);
                        }
                        if ($.isFunction(next)) {
                            next();
                        }
                    }
                    args.mode = modes.shift();
                    if ($.uiBackCompat !== false && !defaultMode) {
                        if (elem.is(":hidden") ? mode === "hide" : mode === "show") {
                            elem[mode]();
                            done();
                        } else {
                            effectMethod.call(elem[0], args, done);
                        }
                    } else {
                        if (args.mode === "none") {
                            elem[mode]();
                            done();
                        } else {
                            effectMethod.call(elem[0], args, cleanup);
                        }
                    }
                }
                return queue === false ? this.each(prefilter).each(run) : this.queue(queueName, prefilter).queue(queueName, run);
            },
            show: function(orig) {
                return function(option) {
                    if (standardAnimationOption(option)) {
                        return orig.apply(this, arguments);
                    } else {
                        var args = _normalizeArguments.apply(this, arguments);
                        args.mode = "show";
                        return this.effect.call(this, args);
                    }
                };
            }($.fn.show),
            hide: function(orig) {
                return function(option) {
                    if (standardAnimationOption(option)) {
                        return orig.apply(this, arguments);
                    } else {
                        var args = _normalizeArguments.apply(this, arguments);
                        args.mode = "hide";
                        return this.effect.call(this, args);
                    }
                };
            }($.fn.hide),
            toggle: function(orig) {
                return function(option) {
                    if (standardAnimationOption(option) || typeof option === "boolean") {
                        return orig.apply(this, arguments);
                    } else {
                        var args = _normalizeArguments.apply(this, arguments);
                        args.mode = "toggle";
                        return this.effect.call(this, args);
                    }
                };
            }($.fn.toggle),
            cssUnit: function(key) {
                var style = this.css(key), val = [];
                $.each([ "em", "px", "%", "pt" ], function(i, unit) {
                    if (style.indexOf(unit) > 0) {
                        val = [ parseFloat(style), unit ];
                    }
                });
                return val;
            },
            cssClip: function(clipObj) {
                if (clipObj) {
                    return this.css("clip", "rect(" + clipObj.top + "px " + clipObj.right + "px " + clipObj.bottom + "px " + clipObj.left + "px)");
                }
                return parseClip(this.css("clip"), this);
            },
            transfer: function(options, done) {
                var element = $(this), target = $(options.to), targetFixed = target.css("position") === "fixed", body = $("body"), fixTop = targetFixed ? body.scrollTop() : 0, fixLeft = targetFixed ? body.scrollLeft() : 0, endPosition = target.offset(), animation = {
                    top: endPosition.top - fixTop,
                    left: endPosition.left - fixLeft,
                    height: target.innerHeight(),
                    width: target.innerWidth()
                }, startPosition = element.offset(), transfer = $("<div class='ui-effects-transfer'></div>").appendTo("body").addClass(options.className).css({
                    top: startPosition.top - fixTop,
                    left: startPosition.left - fixLeft,
                    height: element.innerHeight(),
                    width: element.innerWidth(),
                    position: targetFixed ? "fixed" : "absolute"
                }).animate(animation, options.duration, options.easing, function() {
                    transfer.remove();
                    if ($.isFunction(done)) {
                        done();
                    }
                });
            }
        });
        function parseClip(str, element) {
            var outerWidth = element.outerWidth(), outerHeight = element.outerHeight(), clipRegex = /^rect\((-?\d*\.?\d*px|-?\d+%|auto),?\s*(-?\d*\.?\d*px|-?\d+%|auto),?\s*(-?\d*\.?\d*px|-?\d+%|auto),?\s*(-?\d*\.?\d*px|-?\d+%|auto)\)$/, values = clipRegex.exec(str) || [ "", 0, outerWidth, outerHeight, 0 ];
            return {
                top: parseFloat(values[1]) || 0,
                right: values[2] === "auto" ? outerWidth : parseFloat(values[2]),
                bottom: values[3] === "auto" ? outerHeight : parseFloat(values[3]),
                left: parseFloat(values[4]) || 0
            };
        }
        $.fx.step.clip = function(fx) {
            if (!fx.clipInit) {
                fx.start = $(fx.elem).cssClip();
                if (typeof fx.end === "string") {
                    fx.end = parseClip(fx.end, fx.elem);
                }
                fx.clipInit = true;
            }
            $(fx.elem).cssClip({
                top: fx.pos * (fx.end.top - fx.start.top) + fx.start.top,
                right: fx.pos * (fx.end.right - fx.start.right) + fx.start.right,
                bottom: fx.pos * (fx.end.bottom - fx.start.bottom) + fx.start.bottom,
                left: fx.pos * (fx.end.left - fx.start.left) + fx.start.left
            });
        };
    })();
    (function() {
        var baseEasings = {};
        $.each([ "Quad", "Cubic", "Quart", "Quint", "Expo" ], function(i, name) {
            baseEasings[name] = function(p) {
                return Math.pow(p, i + 2);
            };
        });
        $.extend(baseEasings, {
            Sine: function(p) {
                return 1 - Math.cos(p * Math.PI / 2);
            },
            Circ: function(p) {
                return 1 - Math.sqrt(1 - p * p);
            },
            Elastic: function(p) {
                return p === 0 || p === 1 ? p : -Math.pow(2, 8 * (p - 1)) * Math.sin(((p - 1) * 80 - 7.5) * Math.PI / 15);
            },
            Back: function(p) {
                return p * p * (3 * p - 2);
            },
            Bounce: function(p) {
                var pow2, bounce = 4;
                while (p < ((pow2 = Math.pow(2, --bounce)) - 1) / 11) {}
                return 1 / Math.pow(4, 3 - bounce) - 7.5625 * Math.pow((pow2 * 3 - 2) / 22 - p, 2);
            }
        });
        $.each(baseEasings, function(name, easeIn) {
            $.easing["easeIn" + name] = easeIn;
            $.easing["easeOut" + name] = function(p) {
                return 1 - easeIn(1 - p);
            };
            $.easing["easeInOut" + name] = function(p) {
                return p < .5 ? easeIn(p * 2) / 2 : 1 - easeIn(p * -2 + 2) / 2;
            };
        });
    })();
    var effect = $.effects;
    var effectsEffectBlind = $.effects.define("blind", "hide", function(options, done) {
        var map = {
            up: [ "bottom", "top" ],
            vertical: [ "bottom", "top" ],
            down: [ "top", "bottom" ],
            left: [ "right", "left" ],
            horizontal: [ "right", "left" ],
            right: [ "left", "right" ]
        }, element = $(this), direction = options.direction || "up", start = element.cssClip(), animate = {
            clip: $.extend({}, start)
        }, placeholder = $.effects.createPlaceholder(element);
        animate.clip[map[direction][0]] = animate.clip[map[direction][1]];
        if (options.mode === "show") {
            element.cssClip(animate.clip);
            if (placeholder) {
                placeholder.css($.effects.clipToBox(animate));
            }
            animate.clip = start;
        }
        if (placeholder) {
            placeholder.animate($.effects.clipToBox(animate), options.duration, options.easing);
        }
        element.animate(animate, {
            queue: false,
            duration: options.duration,
            easing: options.easing,
            complete: done
        });
    });
    var effectsEffectBounce = $.effects.define("bounce", function(options, done) {
        var upAnim, downAnim, refValue, element = $(this), mode = options.mode, hide = mode === "hide", show = mode === "show", direction = options.direction || "up", distance = options.distance, times = options.times || 5, anims = times * 2 + (show || hide ? 1 : 0), speed = options.duration / anims, easing = options.easing, ref = direction === "up" || direction === "down" ? "top" : "left", motion = direction === "up" || direction === "left", i = 0, queuelen = element.queue().length;
        $.effects.createPlaceholder(element);
        refValue = element.css(ref);
        if (!distance) {
            distance = element[ref === "top" ? "outerHeight" : "outerWidth"]() / 3;
        }
        if (show) {
            downAnim = {
                opacity: 1
            };
            downAnim[ref] = refValue;
            element.css("opacity", 0).css(ref, motion ? -distance * 2 : distance * 2).animate(downAnim, speed, easing);
        }
        if (hide) {
            distance = distance / Math.pow(2, times - 1);
        }
        downAnim = {};
        downAnim[ref] = refValue;
        for (;i < times; i++) {
            upAnim = {};
            upAnim[ref] = (motion ? "-=" : "+=") + distance;
            element.animate(upAnim, speed, easing).animate(downAnim, speed, easing);
            distance = hide ? distance * 2 : distance / 2;
        }
        if (hide) {
            upAnim = {
                opacity: 0
            };
            upAnim[ref] = (motion ? "-=" : "+=") + distance;
            element.animate(upAnim, speed, easing);
        }
        element.queue(done);
        $.effects.unshift(element, queuelen, anims + 1);
    });
    var effectsEffectClip = $.effects.define("clip", "hide", function(options, done) {
        var start, animate = {}, element = $(this), direction = options.direction || "vertical", both = direction === "both", horizontal = both || direction === "horizontal", vertical = both || direction === "vertical";
        start = element.cssClip();
        animate.clip = {
            top: vertical ? (start.bottom - start.top) / 2 : start.top,
            right: horizontal ? (start.right - start.left) / 2 : start.right,
            bottom: vertical ? (start.bottom - start.top) / 2 : start.bottom,
            left: horizontal ? (start.right - start.left) / 2 : start.left
        };
        $.effects.createPlaceholder(element);
        if (options.mode === "show") {
            element.cssClip(animate.clip);
            animate.clip = start;
        }
        element.animate(animate, {
            queue: false,
            duration: options.duration,
            easing: options.easing,
            complete: done
        });
    });
    var effectsEffectDrop = $.effects.define("drop", "hide", function(options, done) {
        var distance, element = $(this), mode = options.mode, show = mode === "show", direction = options.direction || "left", ref = direction === "up" || direction === "down" ? "top" : "left", motion = direction === "up" || direction === "left" ? "-=" : "+=", oppositeMotion = motion === "+=" ? "-=" : "+=", animation = {
            opacity: 0
        };
        $.effects.createPlaceholder(element);
        distance = options.distance || element[ref === "top" ? "outerHeight" : "outerWidth"](true) / 2;
        animation[ref] = motion + distance;
        if (show) {
            element.css(animation);
            animation[ref] = oppositeMotion + distance;
            animation.opacity = 1;
        }
        element.animate(animation, {
            queue: false,
            duration: options.duration,
            easing: options.easing,
            complete: done
        });
    });
    var effectsEffectExplode = $.effects.define("explode", "hide", function(options, done) {
        var i, j, left, top, mx, my, rows = options.pieces ? Math.round(Math.sqrt(options.pieces)) : 3, cells = rows, element = $(this), mode = options.mode, show = mode === "show", offset = element.show().css("visibility", "hidden").offset(), width = Math.ceil(element.outerWidth() / cells), height = Math.ceil(element.outerHeight() / rows), pieces = [];
        function childComplete() {
            pieces.push(this);
            if (pieces.length === rows * cells) {
                animComplete();
            }
        }
        for (i = 0; i < rows; i++) {
            top = offset.top + i * height;
            my = i - (rows - 1) / 2;
            for (j = 0; j < cells; j++) {
                left = offset.left + j * width;
                mx = j - (cells - 1) / 2;
                element.clone().appendTo("body").wrap("<div></div>").css({
                    position: "absolute",
                    visibility: "visible",
                    left: -j * width,
                    top: -i * height
                }).parent().addClass("ui-effects-explode").css({
                    position: "absolute",
                    overflow: "hidden",
                    width: width,
                    height: height,
                    left: left + (show ? mx * width : 0),
                    top: top + (show ? my * height : 0),
                    opacity: show ? 0 : 1
                }).animate({
                    left: left + (show ? 0 : mx * width),
                    top: top + (show ? 0 : my * height),
                    opacity: show ? 1 : 0
                }, options.duration || 500, options.easing, childComplete);
            }
        }
        function animComplete() {
            element.css({
                visibility: "visible"
            });
            $(pieces).remove();
            done();
        }
    });
    var effectsEffectFade = $.effects.define("fade", "toggle", function(options, done) {
        var show = options.mode === "show";
        $(this).css("opacity", show ? 0 : 1).animate({
            opacity: show ? 1 : 0
        }, {
            queue: false,
            duration: options.duration,
            easing: options.easing,
            complete: done
        });
    });
    var effectsEffectFold = $.effects.define("fold", "hide", function(options, done) {
        var element = $(this), mode = options.mode, show = mode === "show", hide = mode === "hide", size = options.size || 15, percent = /([0-9]+)%/.exec(size), horizFirst = !!options.horizFirst, ref = horizFirst ? [ "right", "bottom" ] : [ "bottom", "right" ], duration = options.duration / 2, placeholder = $.effects.createPlaceholder(element), start = element.cssClip(), animation1 = {
            clip: $.extend({}, start)
        }, animation2 = {
            clip: $.extend({}, start)
        }, distance = [ start[ref[0]], start[ref[1]] ], queuelen = element.queue().length;
        if (percent) {
            size = parseInt(percent[1], 10) / 100 * distance[hide ? 0 : 1];
        }
        animation1.clip[ref[0]] = size;
        animation2.clip[ref[0]] = size;
        animation2.clip[ref[1]] = 0;
        if (show) {
            element.cssClip(animation2.clip);
            if (placeholder) {
                placeholder.css($.effects.clipToBox(animation2));
            }
            animation2.clip = start;
        }
        element.queue(function(next) {
            if (placeholder) {
                placeholder.animate($.effects.clipToBox(animation1), duration, options.easing).animate($.effects.clipToBox(animation2), duration, options.easing);
            }
            next();
        }).animate(animation1, duration, options.easing).animate(animation2, duration, options.easing).queue(done);
        $.effects.unshift(element, queuelen, 4);
    });
    var effectsEffectHighlight = $.effects.define("highlight", "show", function(options, done) {
        var element = $(this), animation = {
            backgroundColor: element.css("backgroundColor")
        };
        if (options.mode === "hide") {
            animation.opacity = 0;
        }
        $.effects.saveStyle(element);
        element.css({
            backgroundImage: "none",
            backgroundColor: options.color || "#ffff99"
        }).animate(animation, {
            queue: false,
            duration: options.duration,
            easing: options.easing,
            complete: done
        });
    });
    var effectsEffectSize = $.effects.define("size", function(options, done) {
        var baseline, factor, temp, element = $(this), cProps = [ "fontSize" ], vProps = [ "borderTopWidth", "borderBottomWidth", "paddingTop", "paddingBottom" ], hProps = [ "borderLeftWidth", "borderRightWidth", "paddingLeft", "paddingRight" ], mode = options.mode, restore = mode !== "effect", scale = options.scale || "both", origin = options.origin || [ "middle", "center" ], position = element.css("position"), pos = element.position(), original = $.effects.scaledDimensions(element), from = options.from || original, to = options.to || $.effects.scaledDimensions(element, 0);
        $.effects.createPlaceholder(element);
        if (mode === "show") {
            temp = from;
            from = to;
            to = temp;
        }
        factor = {
            from: {
                y: from.height / original.height,
                x: from.width / original.width
            },
            to: {
                y: to.height / original.height,
                x: to.width / original.width
            }
        };
        if (scale === "box" || scale === "both") {
            if (factor.from.y !== factor.to.y) {
                from = $.effects.setTransition(element, vProps, factor.from.y, from);
                to = $.effects.setTransition(element, vProps, factor.to.y, to);
            }
            if (factor.from.x !== factor.to.x) {
                from = $.effects.setTransition(element, hProps, factor.from.x, from);
                to = $.effects.setTransition(element, hProps, factor.to.x, to);
            }
        }
        if (scale === "content" || scale === "both") {
            if (factor.from.y !== factor.to.y) {
                from = $.effects.setTransition(element, cProps, factor.from.y, from);
                to = $.effects.setTransition(element, cProps, factor.to.y, to);
            }
        }
        if (origin) {
            baseline = $.effects.getBaseline(origin, original);
            from.top = (original.outerHeight - from.outerHeight) * baseline.y + pos.top;
            from.left = (original.outerWidth - from.outerWidth) * baseline.x + pos.left;
            to.top = (original.outerHeight - to.outerHeight) * baseline.y + pos.top;
            to.left = (original.outerWidth - to.outerWidth) * baseline.x + pos.left;
        }
        element.css(from);
        if (scale === "content" || scale === "both") {
            vProps = vProps.concat([ "marginTop", "marginBottom" ]).concat(cProps);
            hProps = hProps.concat([ "marginLeft", "marginRight" ]);
            element.find("*[width]").each(function() {
                var child = $(this), childOriginal = $.effects.scaledDimensions(child), childFrom = {
                    height: childOriginal.height * factor.from.y,
                    width: childOriginal.width * factor.from.x,
                    outerHeight: childOriginal.outerHeight * factor.from.y,
                    outerWidth: childOriginal.outerWidth * factor.from.x
                }, childTo = {
                    height: childOriginal.height * factor.to.y,
                    width: childOriginal.width * factor.to.x,
                    outerHeight: childOriginal.height * factor.to.y,
                    outerWidth: childOriginal.width * factor.to.x
                };
                if (factor.from.y !== factor.to.y) {
                    childFrom = $.effects.setTransition(child, vProps, factor.from.y, childFrom);
                    childTo = $.effects.setTransition(child, vProps, factor.to.y, childTo);
                }
                if (factor.from.x !== factor.to.x) {
                    childFrom = $.effects.setTransition(child, hProps, factor.from.x, childFrom);
                    childTo = $.effects.setTransition(child, hProps, factor.to.x, childTo);
                }
                if (restore) {
                    $.effects.saveStyle(child);
                }
                child.css(childFrom);
                child.animate(childTo, options.duration, options.easing, function() {
                    if (restore) {
                        $.effects.restoreStyle(child);
                    }
                });
            });
        }
        element.animate(to, {
            queue: false,
            duration: options.duration,
            easing: options.easing,
            complete: function() {
                var offset = element.offset();
                if (to.opacity === 0) {
                    element.css("opacity", from.opacity);
                }
                if (!restore) {
                    element.css("position", position === "static" ? "relative" : position).offset(offset);
                    $.effects.saveStyle(element);
                }
                done();
            }
        });
    });
    var effectsEffectScale = $.effects.define("scale", function(options, done) {
        var el = $(this), mode = options.mode, percent = parseInt(options.percent, 10) || (parseInt(options.percent, 10) === 0 ? 0 : mode !== "effect" ? 0 : 100), newOptions = $.extend(true, {
            from: $.effects.scaledDimensions(el),
            to: $.effects.scaledDimensions(el, percent, options.direction || "both"),
            origin: options.origin || [ "middle", "center" ]
        }, options);
        if (options.fade) {
            newOptions.from.opacity = 1;
            newOptions.to.opacity = 0;
        }
        $.effects.effect.size.call(this, newOptions, done);
    });
    var effectsEffectPuff = $.effects.define("puff", "hide", function(options, done) {
        var newOptions = $.extend(true, {}, options, {
            fade: true,
            percent: parseInt(options.percent, 10) || 150
        });
        $.effects.effect.scale.call(this, newOptions, done);
    });
    var effectsEffectPulsate = $.effects.define("pulsate", "show", function(options, done) {
        var element = $(this), mode = options.mode, show = mode === "show", hide = mode === "hide", showhide = show || hide, anims = (options.times || 5) * 2 + (showhide ? 1 : 0), duration = options.duration / anims, animateTo = 0, i = 1, queuelen = element.queue().length;
        if (show || !element.is(":visible")) {
            element.css("opacity", 0).show();
            animateTo = 1;
        }
        for (;i < anims; i++) {
            element.animate({
                opacity: animateTo
            }, duration, options.easing);
            animateTo = 1 - animateTo;
        }
        element.animate({
            opacity: animateTo
        }, duration, options.easing);
        element.queue(done);
        $.effects.unshift(element, queuelen, anims + 1);
    });
    var effectsEffectShake = $.effects.define("shake", function(options, done) {
        var i = 1, element = $(this), direction = options.direction || "left", distance = options.distance || 20, times = options.times || 3, anims = times * 2 + 1, speed = Math.round(options.duration / anims), ref = direction === "up" || direction === "down" ? "top" : "left", positiveMotion = direction === "up" || direction === "left", animation = {}, animation1 = {}, animation2 = {}, queuelen = element.queue().length;
        $.effects.createPlaceholder(element);
        animation[ref] = (positiveMotion ? "-=" : "+=") + distance;
        animation1[ref] = (positiveMotion ? "+=" : "-=") + distance * 2;
        animation2[ref] = (positiveMotion ? "-=" : "+=") + distance * 2;
        element.animate(animation, speed, options.easing);
        for (;i < times; i++) {
            element.animate(animation1, speed, options.easing).animate(animation2, speed, options.easing);
        }
        element.animate(animation1, speed, options.easing).animate(animation, speed / 2, options.easing).queue(done);
        $.effects.unshift(element, queuelen, anims + 1);
    });
    var effectsEffectSlide = $.effects.define("slide", "show", function(options, done) {
        var startClip, startRef, element = $(this), map = {
            up: [ "bottom", "top" ],
            down: [ "top", "bottom" ],
            left: [ "right", "left" ],
            right: [ "left", "right" ]
        }, mode = options.mode, direction = options.direction || "left", ref = direction === "up" || direction === "down" ? "top" : "left", positiveMotion = direction === "up" || direction === "left", distance = options.distance || element[ref === "top" ? "outerHeight" : "outerWidth"](true), animation = {};
        $.effects.createPlaceholder(element);
        startClip = element.cssClip();
        startRef = element.position()[ref];
        animation[ref] = (positiveMotion ? -1 : 1) * distance + startRef;
        animation.clip = element.cssClip();
        animation.clip[map[direction][1]] = animation.clip[map[direction][0]];
        if (mode === "show") {
            element.cssClip(animation.clip);
            element.css(ref, animation[ref]);
            animation.clip = startClip;
            animation[ref] = startRef;
        }
        element.animate(animation, {
            queue: false,
            duration: options.duration,
            easing: options.easing,
            complete: done
        });
    });
    var effect;
    if ($.uiBackCompat !== false) {
        effect = $.effects.define("transfer", function(options, done) {
            $(this).transfer(options, done);
        });
    }
    var effectsEffectTransfer = effect;
    $.ui.focusable = function(element, hasTabindex) {
        var map, mapName, img, focusableIfVisible, fieldset, nodeName = element.nodeName.toLowerCase();
        if ("area" === nodeName) {
            map = element.parentNode;
            mapName = map.name;
            if (!element.href || !mapName || map.nodeName.toLowerCase() !== "map") {
                return false;
            }
            img = $("img[usemap='#" + mapName + "']");
            return img.length > 0 && img.is(":visible");
        }
        if (/^(input|select|textarea|button|object)$/.test(nodeName)) {
            focusableIfVisible = !element.disabled;
            if (focusableIfVisible) {
                fieldset = $(element).closest("fieldset")[0];
                if (fieldset) {
                    focusableIfVisible = !fieldset.disabled;
                }
            }
        } else if ("a" === nodeName) {
            focusableIfVisible = element.href || hasTabindex;
        } else {
            focusableIfVisible = hasTabindex;
        }
        return focusableIfVisible && $(element).is(":visible") && visible($(element));
    };
    function visible(element) {
        var visibility = element.css("visibility");
        while (visibility === "inherit") {
            element = element.parent();
            visibility = element.css("visibility");
        }
        return visibility !== "hidden";
    }
    $.extend($.expr[":"], {
        focusable: function(element) {
            return $.ui.focusable(element, $.attr(element, "tabindex") != null);
        }
    });
    var focusable = $.ui.focusable;
    var form = $.fn.form = function() {
        return typeof this[0].form === "string" ? this.closest("form") : $(this[0].form);
    };
    var formResetMixin = $.ui.formResetMixin = {
        _formResetHandler: function() {
            var form = $(this);
            setTimeout(function() {
                var instances = form.data("ui-form-reset-instances");
                $.each(instances, function() {
                    this.refresh();
                });
            });
        },
        _bindFormResetHandler: function() {
            this.form = this.element.form();
            if (!this.form.length) {
                return;
            }
            var instances = this.form.data("ui-form-reset-instances") || [];
            if (!instances.length) {
                this.form.on("reset.ui-form-reset", this._formResetHandler);
            }
            instances.push(this);
            this.form.data("ui-form-reset-instances", instances);
        },
        _unbindFormResetHandler: function() {
            if (!this.form.length) {
                return;
            }
            var instances = this.form.data("ui-form-reset-instances");
            instances.splice($.inArray(this, instances), 1);
            if (instances.length) {
                this.form.data("ui-form-reset-instances", instances);
            } else {
                this.form.removeData("ui-form-reset-instances").off("reset.ui-form-reset");
            }
        }
    };
    if ($.fn.jquery.substring(0, 3) === "1.7") {
        $.each([ "Width", "Height" ], function(i, name) {
            var side = name === "Width" ? [ "Left", "Right" ] : [ "Top", "Bottom" ], type = name.toLowerCase(), orig = {
                innerWidth: $.fn.innerWidth,
                innerHeight: $.fn.innerHeight,
                outerWidth: $.fn.outerWidth,
                outerHeight: $.fn.outerHeight
            };
            function reduce(elem, size, border, margin) {
                $.each(side, function() {
                    size -= parseFloat($.css(elem, "padding" + this)) || 0;
                    if (border) {
                        size -= parseFloat($.css(elem, "border" + this + "Width")) || 0;
                    }
                    if (margin) {
                        size -= parseFloat($.css(elem, "margin" + this)) || 0;
                    }
                });
                return size;
            }
            $.fn["inner" + name] = function(size) {
                if (size === undefined) {
                    return orig["inner" + name].call(this);
                }
                return this.each(function() {
                    $(this).css(type, reduce(this, size) + "px");
                });
            };
            $.fn["outer" + name] = function(size, margin) {
                if (typeof size !== "number") {
                    return orig["outer" + name].call(this, size);
                }
                return this.each(function() {
                    $(this).css(type, reduce(this, size, true, margin) + "px");
                });
            };
        });
        $.fn.addBack = function(selector) {
            return this.add(selector == null ? this.prevObject : this.prevObject.filter(selector));
        };
    }
    var keycode = $.ui.keyCode = {
        BACKSPACE: 8,
        COMMA: 188,
        DELETE: 46,
        DOWN: 40,
        END: 35,
        ENTER: 13,
        ESCAPE: 27,
        HOME: 36,
        LEFT: 37,
        PAGE_DOWN: 34,
        PAGE_UP: 33,
        PERIOD: 190,
        RIGHT: 39,
        SPACE: 32,
        TAB: 9,
        UP: 38
    };
    var escapeSelector = $.ui.escapeSelector = function() {
        var selectorEscape = /([!"#$%&'()*+,./:;<=>?@[\]^`{|}~])/g;
        return function(selector) {
            return selector.replace(selectorEscape, "\\$1");
        };
    }();
    var labels = $.fn.labels = function() {
        var ancestor, selector, id, labels, ancestors;
        if (this[0].labels && this[0].labels.length) {
            return this.pushStack(this[0].labels);
        }
        labels = this.eq(0).parents("label");
        id = this.attr("id");
        if (id) {
            ancestor = this.eq(0).parents().last();
            ancestors = ancestor.add(ancestor.length ? ancestor.siblings() : this.siblings());
            selector = "label[for='" + $.ui.escapeSelector(id) + "']";
            labels = labels.add(ancestors.find(selector).addBack(selector));
        }
        return this.pushStack(labels);
    };
    var scrollParent = $.fn.scrollParent = function(includeHidden) {
        var position = this.css("position"), excludeStaticParent = position === "absolute", overflowRegex = includeHidden ? /(auto|scroll|hidden)/ : /(auto|scroll)/, scrollParent = this.parents().filter(function() {
            var parent = $(this);
            if (excludeStaticParent && parent.css("position") === "static") {
                return false;
            }
            return overflowRegex.test(parent.css("overflow") + parent.css("overflow-y") + parent.css("overflow-x"));
        }).eq(0);
        return position === "fixed" || !scrollParent.length ? $(this[0].ownerDocument || document) : scrollParent;
    };
    var tabbable = $.extend($.expr[":"], {
        tabbable: function(element) {
            var tabIndex = $.attr(element, "tabindex"), hasTabindex = tabIndex != null;
            return (!hasTabindex || tabIndex >= 0) && $.ui.focusable(element, hasTabindex);
        }
    });
    var uniqueId = $.fn.extend({
        uniqueId: function() {
            var uuid = 0;
            return function() {
                return this.each(function() {
                    if (!this.id) {
                        this.id = "ui-id-" + ++uuid;
                    }
                });
            };
        }(),
        removeUniqueId: function() {
            return this.each(function() {
                if (/^ui-id-\d+$/.test(this.id)) {
                    $(this).removeAttr("id");
                }
            });
        }
    });
    var widgetsAccordion = $.widget("ui.accordion", {
        version: "1.12.1",
        options: {
            active: 0,
            animate: {},
            classes: {
                "ui-accordion-header": "ui-corner-top",
                "ui-accordion-header-collapsed": "ui-corner-all",
                "ui-accordion-content": "ui-corner-bottom"
            },
            collapsible: false,
            event: "click",
            header: "> li > :first-child, > :not(li):even",
            heightStyle: "auto",
            icons: {
                activeHeader: "ui-icon-triangle-1-s",
                header: "ui-icon-triangle-1-e"
            },
            activate: null,
            beforeActivate: null
        },
        hideProps: {
            borderTopWidth: "hide",
            borderBottomWidth: "hide",
            paddingTop: "hide",
            paddingBottom: "hide",
            height: "hide"
        },
        showProps: {
            borderTopWidth: "show",
            borderBottomWidth: "show",
            paddingTop: "show",
            paddingBottom: "show",
            height: "show"
        },
        _create: function() {
            var options = this.options;
            this.prevShow = this.prevHide = $();
            this._addClass("ui-accordion", "ui-widget ui-helper-reset");
            this.element.attr("role", "tablist");
            if (!options.collapsible && (options.active === false || options.active == null)) {
                options.active = 0;
            }
            this._processPanels();
            if (options.active < 0) {
                options.active += this.headers.length;
            }
            this._refresh();
        },
        _getCreateEventData: function() {
            return {
                header: this.active,
                panel: !this.active.length ? $() : this.active.next()
            };
        },
        _createIcons: function() {
            var icon, children, icons = this.options.icons;
            if (icons) {
                icon = $("<span>");
                this._addClass(icon, "ui-accordion-header-icon", "ui-icon " + icons.header);
                icon.prependTo(this.headers);
                children = this.active.children(".ui-accordion-header-icon");
                this._removeClass(children, icons.header)._addClass(children, null, icons.activeHeader)._addClass(this.headers, "ui-accordion-icons");
            }
        },
        _destroyIcons: function() {
            this._removeClass(this.headers, "ui-accordion-icons");
            this.headers.children(".ui-accordion-header-icon").remove();
        },
        _destroy: function() {
            var contents;
            this.element.removeAttr("role");
            this.headers.removeAttr("role aria-expanded aria-selected aria-controls tabIndex").removeUniqueId();
            this._destroyIcons();
            contents = this.headers.next().css("display", "").removeAttr("role aria-hidden aria-labelledby").removeUniqueId();
            if (this.options.heightStyle !== "content") {
                contents.css("height", "");
            }
        },
        _setOption: function(key, value) {
            if (key === "active") {
                this._activate(value);
                return;
            }
            if (key === "event") {
                if (this.options.event) {
                    this._off(this.headers, this.options.event);
                }
                this._setupEvents(value);
            }
            this._super(key, value);
            if (key === "collapsible" && !value && this.options.active === false) {
                this._activate(0);
            }
            if (key === "icons") {
                this._destroyIcons();
                if (value) {
                    this._createIcons();
                }
            }
        },
        _setOptionDisabled: function(value) {
            this._super(value);
            this.element.attr("aria-disabled", value);
            this._toggleClass(null, "ui-state-disabled", !!value);
            this._toggleClass(this.headers.add(this.headers.next()), null, "ui-state-disabled", !!value);
        },
        _keydown: function(event) {
            if (event.altKey || event.ctrlKey) {
                return;
            }
            var keyCode = $.ui.keyCode, length = this.headers.length, currentIndex = this.headers.index(event.target), toFocus = false;
            switch (event.keyCode) {
              case keyCode.RIGHT:
              case keyCode.DOWN:
                toFocus = this.headers[(currentIndex + 1) % length];
                break;

              case keyCode.LEFT:
              case keyCode.UP:
                toFocus = this.headers[(currentIndex - 1 + length) % length];
                break;

              case keyCode.SPACE:
              case keyCode.ENTER:
                this._eventHandler(event);
                break;

              case keyCode.HOME:
                toFocus = this.headers[0];
                break;

              case keyCode.END:
                toFocus = this.headers[length - 1];
                break;
            }
            if (toFocus) {
                $(event.target).attr("tabIndex", -1);
                $(toFocus).attr("tabIndex", 0);
                $(toFocus).trigger("focus");
                event.preventDefault();
            }
        },
        _panelKeyDown: function(event) {
            if (event.keyCode === $.ui.keyCode.UP && event.ctrlKey) {
                $(event.currentTarget).prev().trigger("focus");
            }
        },
        refresh: function() {
            var options = this.options;
            this._processPanels();
            if (options.active === false && options.collapsible === true || !this.headers.length) {
                options.active = false;
                this.active = $();
            } else if (options.active === false) {
                this._activate(0);
            } else if (this.active.length && !$.contains(this.element[0], this.active[0])) {
                if (this.headers.length === this.headers.find(".ui-state-disabled").length) {
                    options.active = false;
                    this.active = $();
                } else {
                    this._activate(Math.max(0, options.active - 1));
                }
            } else {
                options.active = this.headers.index(this.active);
            }
            this._destroyIcons();
            this._refresh();
        },
        _processPanels: function() {
            var prevHeaders = this.headers, prevPanels = this.panels;
            this.headers = this.element.find(this.options.header);
            this._addClass(this.headers, "ui-accordion-header ui-accordion-header-collapsed", "ui-state-default");
            this.panels = this.headers.next().filter(":not(.ui-accordion-content-active)").hide();
            this._addClass(this.panels, "ui-accordion-content", "ui-helper-reset ui-widget-content");
            if (prevPanels) {
                this._off(prevHeaders.not(this.headers));
                this._off(prevPanels.not(this.panels));
            }
        },
        _refresh: function() {
            var maxHeight, options = this.options, heightStyle = options.heightStyle, parent = this.element.parent();
            this.active = this._findActive(options.active);
            this._addClass(this.active, "ui-accordion-header-active", "ui-state-active")._removeClass(this.active, "ui-accordion-header-collapsed");
            this._addClass(this.active.next(), "ui-accordion-content-active");
            this.active.next().show();
            this.headers.attr("role", "tab").each(function() {
                var header = $(this), headerId = header.uniqueId().attr("id"), panel = header.next(), panelId = panel.uniqueId().attr("id");
                header.attr("aria-controls", panelId);
                panel.attr("aria-labelledby", headerId);
            }).next().attr("role", "tabpanel");
            this.headers.not(this.active).attr({
                "aria-selected": "false",
                "aria-expanded": "false",
                tabIndex: -1
            }).next().attr({
                "aria-hidden": "true"
            }).hide();
            if (!this.active.length) {
                this.headers.eq(0).attr("tabIndex", 0);
            } else {
                this.active.attr({
                    "aria-selected": "true",
                    "aria-expanded": "true",
                    tabIndex: 0
                }).next().attr({
                    "aria-hidden": "false"
                });
            }
            this._createIcons();
            this._setupEvents(options.event);
            if (heightStyle === "fill") {
                maxHeight = parent.height();
                this.element.siblings(":visible").each(function() {
                    var elem = $(this), position = elem.css("position");
                    if (position === "absolute" || position === "fixed") {
                        return;
                    }
                    maxHeight -= elem.outerHeight(true);
                });
                this.headers.each(function() {
                    maxHeight -= $(this).outerHeight(true);
                });
                this.headers.next().each(function() {
                    $(this).height(Math.max(0, maxHeight - $(this).innerHeight() + $(this).height()));
                }).css("overflow", "auto");
            } else if (heightStyle === "auto") {
                maxHeight = 0;
                this.headers.next().each(function() {
                    var isVisible = $(this).is(":visible");
                    if (!isVisible) {
                        $(this).show();
                    }
                    maxHeight = Math.max(maxHeight, $(this).css("height", "").height());
                    if (!isVisible) {
                        $(this).hide();
                    }
                }).height(maxHeight);
            }
        },
        _activate: function(index) {
            var active = this._findActive(index)[0];
            if (active === this.active[0]) {
                return;
            }
            active = active || this.active[0];
            this._eventHandler({
                target: active,
                currentTarget: active,
                preventDefault: $.noop
            });
        },
        _findActive: function(selector) {
            return typeof selector === "number" ? this.headers.eq(selector) : $();
        },
        _setupEvents: function(event) {
            var events = {
                keydown: "_keydown"
            };
            if (event) {
                $.each(event.split(" "), function(index, eventName) {
                    events[eventName] = "_eventHandler";
                });
            }
            this._off(this.headers.add(this.headers.next()));
            this._on(this.headers, events);
            this._on(this.headers.next(), {
                keydown: "_panelKeyDown"
            });
            this._hoverable(this.headers);
            this._focusable(this.headers);
        },
        _eventHandler: function(event) {
            var activeChildren, clickedChildren, options = this.options, active = this.active, clicked = $(event.currentTarget), clickedIsActive = clicked[0] === active[0], collapsing = clickedIsActive && options.collapsible, toShow = collapsing ? $() : clicked.next(), toHide = active.next(), eventData = {
                oldHeader: active,
                oldPanel: toHide,
                newHeader: collapsing ? $() : clicked,
                newPanel: toShow
            };
            event.preventDefault();
            if (clickedIsActive && !options.collapsible || this._trigger("beforeActivate", event, eventData) === false) {
                return;
            }
            options.active = collapsing ? false : this.headers.index(clicked);
            this.active = clickedIsActive ? $() : clicked;
            this._toggle(eventData);
            this._removeClass(active, "ui-accordion-header-active", "ui-state-active");
            if (options.icons) {
                activeChildren = active.children(".ui-accordion-header-icon");
                this._removeClass(activeChildren, null, options.icons.activeHeader)._addClass(activeChildren, null, options.icons.header);
            }
            if (!clickedIsActive) {
                this._removeClass(clicked, "ui-accordion-header-collapsed")._addClass(clicked, "ui-accordion-header-active", "ui-state-active");
                if (options.icons) {
                    clickedChildren = clicked.children(".ui-accordion-header-icon");
                    this._removeClass(clickedChildren, null, options.icons.header)._addClass(clickedChildren, null, options.icons.activeHeader);
                }
                this._addClass(clicked.next(), "ui-accordion-content-active");
            }
        },
        _toggle: function(data) {
            var toShow = data.newPanel, toHide = this.prevShow.length ? this.prevShow : data.oldPanel;
            this.prevShow.add(this.prevHide).stop(true, true);
            this.prevShow = toShow;
            this.prevHide = toHide;
            if (this.options.animate) {
                this._animate(toShow, toHide, data);
            } else {
                toHide.hide();
                toShow.show();
                this._toggleComplete(data);
            }
            toHide.attr({
                "aria-hidden": "true"
            });
            toHide.prev().attr({
                "aria-selected": "false",
                "aria-expanded": "false"
            });
            if (toShow.length && toHide.length) {
                toHide.prev().attr({
                    tabIndex: -1,
                    "aria-expanded": "false"
                });
            } else if (toShow.length) {
                this.headers.filter(function() {
                    return parseInt($(this).attr("tabIndex"), 10) === 0;
                }).attr("tabIndex", -1);
            }
            toShow.attr("aria-hidden", "false").prev().attr({
                "aria-selected": "true",
                "aria-expanded": "true",
                tabIndex: 0
            });
        },
        _animate: function(toShow, toHide, data) {
            var total, easing, duration, that = this, adjust = 0, boxSizing = toShow.css("box-sizing"), down = toShow.length && (!toHide.length || toShow.index() < toHide.index()), animate = this.options.animate || {}, options = down && animate.down || animate, complete = function() {
                that._toggleComplete(data);
            };
            if (typeof options === "number") {
                duration = options;
            }
            if (typeof options === "string") {
                easing = options;
            }
            easing = easing || options.easing || animate.easing;
            duration = duration || options.duration || animate.duration;
            if (!toHide.length) {
                return toShow.animate(this.showProps, duration, easing, complete);
            }
            if (!toShow.length) {
                return toHide.animate(this.hideProps, duration, easing, complete);
            }
            total = toShow.show().outerHeight();
            toHide.animate(this.hideProps, {
                duration: duration,
                easing: easing,
                step: function(now, fx) {
                    fx.now = Math.round(now);
                }
            });
            toShow.hide().animate(this.showProps, {
                duration: duration,
                easing: easing,
                complete: complete,
                step: function(now, fx) {
                    fx.now = Math.round(now);
                    if (fx.prop !== "height") {
                        if (boxSizing === "content-box") {
                            adjust += fx.now;
                        }
                    } else if (that.options.heightStyle !== "content") {
                        fx.now = Math.round(total - toHide.outerHeight() - adjust);
                        adjust = 0;
                    }
                }
            });
        },
        _toggleComplete: function(data) {
            var toHide = data.oldPanel, prev = toHide.prev();
            this._removeClass(toHide, "ui-accordion-content-active");
            this._removeClass(prev, "ui-accordion-header-active")._addClass(prev, "ui-accordion-header-collapsed");
            if (toHide.length) {
                toHide.parent()[0].className = toHide.parent()[0].className;
            }
            this._trigger("activate", null, data);
        }
    });
    var safeActiveElement = $.ui.safeActiveElement = function(document) {
        var activeElement;
        try {
            activeElement = document.activeElement;
        } catch (error) {
            activeElement = document.body;
        }
        if (!activeElement) {
            activeElement = document.body;
        }
        if (!activeElement.nodeName) {
            activeElement = document.body;
        }
        return activeElement;
    };
    var widgetsMenu = $.widget("ui.menu", {
        version: "1.12.1",
        defaultElement: "<ul>",
        delay: 300,
        options: {
            icons: {
                submenu: "ui-icon-caret-1-e"
            },
            items: "> *",
            menus: "ul",
            position: {
                my: "left top",
                at: "right top"
            },
            role: "menu",
            blur: null,
            focus: null,
            select: null
        },
        _create: function() {
            this.activeMenu = this.element;
            this.mouseHandled = false;
            this.element.uniqueId().attr({
                role: this.options.role,
                tabIndex: 0
            });
            this._addClass("ui-menu", "ui-widget ui-widget-content");
            this._on({
                "mousedown .ui-menu-item": function(event) {
                    event.preventDefault();
                },
                "click .ui-menu-item": function(event) {
                    var target = $(event.target);
                    var active = $($.ui.safeActiveElement(this.document[0]));
                    if (!this.mouseHandled && target.not(".ui-state-disabled").length) {
                        this.select(event);
                        if (!event.isPropagationStopped()) {
                            this.mouseHandled = true;
                        }
                        if (target.has(".ui-menu").length) {
                            this.expand(event);
                        } else if (!this.element.is(":focus") && active.closest(".ui-menu").length) {
                            this.element.trigger("focus", [ true ]);
                            if (this.active && this.active.parents(".ui-menu").length === 1) {
                                clearTimeout(this.timer);
                            }
                        }
                    }
                },
                "mouseenter .ui-menu-item": function(event) {
                    if (this.previousFilter) {
                        return;
                    }
                    var actualTarget = $(event.target).closest(".ui-menu-item"), target = $(event.currentTarget);
                    if (actualTarget[0] !== target[0]) {
                        return;
                    }
                    this._removeClass(target.siblings().children(".ui-state-active"), null, "ui-state-active");
                    this.focus(event, target);
                },
                mouseleave: "collapseAll",
                "mouseleave .ui-menu": "collapseAll",
                focus: function(event, keepActiveItem) {
                    var item = this.active || this.element.find(this.options.items).eq(0);
                    if (!keepActiveItem) {
                        this.focus(event, item);
                    }
                },
                blur: function(event) {
                    this._delay(function() {
                        var notContained = !$.contains(this.element[0], $.ui.safeActiveElement(this.document[0]));
                        if (notContained) {
                            this.collapseAll(event);
                        }
                    });
                },
                keydown: "_keydown"
            });
            this.refresh();
            this._on(this.document, {
                click: function(event) {
                    if (this._closeOnDocumentClick(event)) {
                        this.collapseAll(event);
                    }
                    this.mouseHandled = false;
                }
            });
        },
        _destroy: function() {
            var items = this.element.find(".ui-menu-item").removeAttr("role aria-disabled"), submenus = items.children(".ui-menu-item-wrapper").removeUniqueId().removeAttr("tabIndex role aria-haspopup");
            this.element.removeAttr("aria-activedescendant").find(".ui-menu").addBack().removeAttr("role aria-labelledby aria-expanded aria-hidden aria-disabled " + "tabIndex").removeUniqueId().show();
            submenus.children().each(function() {
                var elem = $(this);
                if (elem.data("ui-menu-submenu-caret")) {
                    elem.remove();
                }
            });
        },
        _keydown: function(event) {
            var match, prev, character, skip, preventDefault = true;
            switch (event.keyCode) {
              case $.ui.keyCode.PAGE_UP:
                this.previousPage(event);
                break;

              case $.ui.keyCode.PAGE_DOWN:
                this.nextPage(event);
                break;

              case $.ui.keyCode.HOME:
                this._move("first", "first", event);
                break;

              case $.ui.keyCode.END:
                this._move("last", "last", event);
                break;

              case $.ui.keyCode.UP:
                this.previous(event);
                break;

              case $.ui.keyCode.DOWN:
                this.next(event);
                break;

              case $.ui.keyCode.LEFT:
                this.collapse(event);
                break;

              case $.ui.keyCode.RIGHT:
                if (this.active && !this.active.is(".ui-state-disabled")) {
                    this.expand(event);
                }
                break;

              case $.ui.keyCode.ENTER:
              case $.ui.keyCode.SPACE:
                this._activate(event);
                break;

              case $.ui.keyCode.ESCAPE:
                this.collapse(event);
                break;

              default:
                preventDefault = false;
                prev = this.previousFilter || "";
                skip = false;
                character = event.keyCode >= 96 && event.keyCode <= 105 ? (event.keyCode - 96).toString() : String.fromCharCode(event.keyCode);
                clearTimeout(this.filterTimer);
                if (character === prev) {
                    skip = true;
                } else {
                    character = prev + character;
                }
                match = this._filterMenuItems(character);
                match = skip && match.index(this.active.next()) !== -1 ? this.active.nextAll(".ui-menu-item") : match;
                if (!match.length) {
                    character = String.fromCharCode(event.keyCode);
                    match = this._filterMenuItems(character);
                }
                if (match.length) {
                    this.focus(event, match);
                    this.previousFilter = character;
                    this.filterTimer = this._delay(function() {
                        delete this.previousFilter;
                    }, 1e3);
                } else {
                    delete this.previousFilter;
                }
            }
            if (preventDefault) {
                event.preventDefault();
            }
        },
        _activate: function(event) {
            if (this.active && !this.active.is(".ui-state-disabled")) {
                if (this.active.children("[aria-haspopup='true']").length) {
                    this.expand(event);
                } else {
                    this.select(event);
                }
            }
        },
        refresh: function() {
            var menus, items, newSubmenus, newItems, newWrappers, that = this, icon = this.options.icons.submenu, submenus = this.element.find(this.options.menus);
            this._toggleClass("ui-menu-icons", null, !!this.element.find(".ui-icon").length);
            newSubmenus = submenus.filter(":not(.ui-menu)").hide().attr({
                role: this.options.role,
                "aria-hidden": "true",
                "aria-expanded": "false"
            }).each(function() {
                var menu = $(this), item = menu.prev(), submenuCaret = $("<span>").data("ui-menu-submenu-caret", true);
                that._addClass(submenuCaret, "ui-menu-icon", "ui-icon " + icon);
                item.attr("aria-haspopup", "true").prepend(submenuCaret);
                menu.attr("aria-labelledby", item.attr("id"));
            });
            this._addClass(newSubmenus, "ui-menu", "ui-widget ui-widget-content ui-front");
            menus = submenus.add(this.element);
            items = menus.find(this.options.items);
            items.not(".ui-menu-item").each(function() {
                var item = $(this);
                if (that._isDivider(item)) {
                    that._addClass(item, "ui-menu-divider", "ui-widget-content");
                }
            });
            newItems = items.not(".ui-menu-item, .ui-menu-divider");
            newWrappers = newItems.children().not(".ui-menu").uniqueId().attr({
                tabIndex: -1,
                role: this._itemRole()
            });
            this._addClass(newItems, "ui-menu-item")._addClass(newWrappers, "ui-menu-item-wrapper");
            items.filter(".ui-state-disabled").attr("aria-disabled", "true");
            if (this.active && !$.contains(this.element[0], this.active[0])) {
                this.blur();
            }
        },
        _itemRole: function() {
            return {
                menu: "menuitem",
                listbox: "option"
            }[this.options.role];
        },
        _setOption: function(key, value) {
            if (key === "icons") {
                var icons = this.element.find(".ui-menu-icon");
                this._removeClass(icons, null, this.options.icons.submenu)._addClass(icons, null, value.submenu);
            }
            this._super(key, value);
        },
        _setOptionDisabled: function(value) {
            this._super(value);
            this.element.attr("aria-disabled", String(value));
            this._toggleClass(null, "ui-state-disabled", !!value);
        },
        focus: function(event, item) {
            var nested, focused, activeParent;
            this.blur(event, event && event.type === "focus");
            this._scrollIntoView(item);
            this.active = item.first();
            focused = this.active.children(".ui-menu-item-wrapper");
            this._addClass(focused, null, "ui-state-active");
            if (this.options.role) {
                this.element.attr("aria-activedescendant", focused.attr("id"));
            }
            activeParent = this.active.parent().closest(".ui-menu-item").children(".ui-menu-item-wrapper");
            this._addClass(activeParent, null, "ui-state-active");
            if (event && event.type === "keydown") {
                this._close();
            } else {
                this.timer = this._delay(function() {
                    this._close();
                }, this.delay);
            }
            nested = item.children(".ui-menu");
            if (nested.length && event && /^mouse/.test(event.type)) {
                this._startOpening(nested);
            }
            this.activeMenu = item.parent();
            this._trigger("focus", event, {
                item: item
            });
        },
        _scrollIntoView: function(item) {
            var borderTop, paddingTop, offset, scroll, elementHeight, itemHeight;
            if (this._hasScroll()) {
                borderTop = parseFloat($.css(this.activeMenu[0], "borderTopWidth")) || 0;
                paddingTop = parseFloat($.css(this.activeMenu[0], "paddingTop")) || 0;
                offset = item.offset().top - this.activeMenu.offset().top - borderTop - paddingTop;
                scroll = this.activeMenu.scrollTop();
                elementHeight = this.activeMenu.height();
                itemHeight = item.outerHeight();
                if (offset < 0) {
                    this.activeMenu.scrollTop(scroll + offset);
                } else if (offset + itemHeight > elementHeight) {
                    this.activeMenu.scrollTop(scroll + offset - elementHeight + itemHeight);
                }
            }
        },
        blur: function(event, fromFocus) {
            if (!fromFocus) {
                clearTimeout(this.timer);
            }
            if (!this.active) {
                return;
            }
            this._removeClass(this.active.children(".ui-menu-item-wrapper"), null, "ui-state-active");
            this._trigger("blur", event, {
                item: this.active
            });
            this.active = null;
        },
        _startOpening: function(submenu) {
            clearTimeout(this.timer);
            if (submenu.attr("aria-hidden") !== "true") {
                return;
            }
            this.timer = this._delay(function() {
                this._close();
                this._open(submenu);
            }, this.delay);
        },
        _open: function(submenu) {
            var position = $.extend({
                of: this.active
            }, this.options.position);
            clearTimeout(this.timer);
            this.element.find(".ui-menu").not(submenu.parents(".ui-menu")).hide().attr("aria-hidden", "true");
            submenu.show().removeAttr("aria-hidden").attr("aria-expanded", "true").position(position);
        },
        collapseAll: function(event, all) {
            clearTimeout(this.timer);
            this.timer = this._delay(function() {
                var currentMenu = all ? this.element : $(event && event.target).closest(this.element.find(".ui-menu"));
                if (!currentMenu.length) {
                    currentMenu = this.element;
                }
                this._close(currentMenu);
                this.blur(event);
                this._removeClass(currentMenu.find(".ui-state-active"), null, "ui-state-active");
                this.activeMenu = currentMenu;
            }, this.delay);
        },
        _close: function(startMenu) {
            if (!startMenu) {
                startMenu = this.active ? this.active.parent() : this.element;
            }
            startMenu.find(".ui-menu").hide().attr("aria-hidden", "true").attr("aria-expanded", "false");
        },
        _closeOnDocumentClick: function(event) {
            return !$(event.target).closest(".ui-menu").length;
        },
        _isDivider: function(item) {
            return !/[^\-\u2014\u2013\s]/.test(item.text());
        },
        collapse: function(event) {
            var newItem = this.active && this.active.parent().closest(".ui-menu-item", this.element);
            if (newItem && newItem.length) {
                this._close();
                this.focus(event, newItem);
            }
        },
        expand: function(event) {
            var newItem = this.active && this.active.children(".ui-menu ").find(this.options.items).first();
            if (newItem && newItem.length) {
                this._open(newItem.parent());
                this._delay(function() {
                    this.focus(event, newItem);
                });
            }
        },
        next: function(event) {
            this._move("next", "first", event);
        },
        previous: function(event) {
            this._move("prev", "last", event);
        },
        isFirstItem: function() {
            return this.active && !this.active.prevAll(".ui-menu-item").length;
        },
        isLastItem: function() {
            return this.active && !this.active.nextAll(".ui-menu-item").length;
        },
        _move: function(direction, filter, event) {
            var next;
            if (this.active) {
                if (direction === "first" || direction === "last") {
                    next = this.active[direction === "first" ? "prevAll" : "nextAll"](".ui-menu-item").eq(-1);
                } else {
                    next = this.active[direction + "All"](".ui-menu-item").eq(0);
                }
            }
            if (!next || !next.length || !this.active) {
                next = this.activeMenu.find(this.options.items)[filter]();
            }
            this.focus(event, next);
        },
        nextPage: function(event) {
            var item, base, height;
            if (!this.active) {
                this.next(event);
                return;
            }
            if (this.isLastItem()) {
                return;
            }
            if (this._hasScroll()) {
                base = this.active.offset().top;
                height = this.element.height();
                this.active.nextAll(".ui-menu-item").each(function() {
                    item = $(this);
                    return item.offset().top - base - height < 0;
                });
                this.focus(event, item);
            } else {
                this.focus(event, this.activeMenu.find(this.options.items)[!this.active ? "first" : "last"]());
            }
        },
        previousPage: function(event) {
            var item, base, height;
            if (!this.active) {
                this.next(event);
                return;
            }
            if (this.isFirstItem()) {
                return;
            }
            if (this._hasScroll()) {
                base = this.active.offset().top;
                height = this.element.height();
                this.active.prevAll(".ui-menu-item").each(function() {
                    item = $(this);
                    return item.offset().top - base + height > 0;
                });
                this.focus(event, item);
            } else {
                this.focus(event, this.activeMenu.find(this.options.items).first());
            }
        },
        _hasScroll: function() {
            return this.element.outerHeight() < this.element.prop("scrollHeight");
        },
        select: function(event) {
            this.active = this.active || $(event.target).closest(".ui-menu-item");
            var ui = {
                item: this.active
            };
            if (!this.active.has(".ui-menu").length) {
                this.collapseAll(event, true);
            }
            this._trigger("select", event, ui);
        },
        _filterMenuItems: function(character) {
            var escapedCharacter = character.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&"), regex = new RegExp("^" + escapedCharacter, "i");
            return this.activeMenu.find(this.options.items).filter(".ui-menu-item").filter(function() {
                return regex.test($.trim($(this).children(".ui-menu-item-wrapper").text()));
            });
        }
    });
    $.widget("ui.autocomplete", {
        version: "1.12.1",
        defaultElement: "<input>",
        options: {
            appendTo: null,
            autoFocus: false,
            delay: 300,
            minLength: 1,
            position: {
                my: "left top",
                at: "left bottom",
                collision: "none"
            },
            source: null,
            change: null,
            close: null,
            focus: null,
            open: null,
            response: null,
            search: null,
            select: null
        },
        requestIndex: 0,
        pending: 0,
        _create: function() {
            var suppressKeyPress, suppressKeyPressRepeat, suppressInput, nodeName = this.element[0].nodeName.toLowerCase(), isTextarea = nodeName === "textarea", isInput = nodeName === "input";
            this.isMultiLine = isTextarea || !isInput && this._isContentEditable(this.element);
            this.valueMethod = this.element[isTextarea || isInput ? "val" : "text"];
            this.isNewMenu = true;
            this._addClass("ui-autocomplete-input");
            this.element.attr("autocomplete", "off");
            this._on(this.element, {
                keydown: function(event) {
                    if (this.element.prop("readOnly")) {
                        suppressKeyPress = true;
                        suppressInput = true;
                        suppressKeyPressRepeat = true;
                        return;
                    }
                    suppressKeyPress = false;
                    suppressInput = false;
                    suppressKeyPressRepeat = false;
                    var keyCode = $.ui.keyCode;
                    switch (event.keyCode) {
                      case keyCode.PAGE_UP:
                        suppressKeyPress = true;
                        this._move("previousPage", event);
                        break;

                      case keyCode.PAGE_DOWN:
                        suppressKeyPress = true;
                        this._move("nextPage", event);
                        break;

                      case keyCode.UP:
                        suppressKeyPress = true;
                        this._keyEvent("previous", event);
                        break;

                      case keyCode.DOWN:
                        suppressKeyPress = true;
                        this._keyEvent("next", event);
                        break;

                      case keyCode.ENTER:
                        if (this.menu.active) {
                            suppressKeyPress = true;
                            event.preventDefault();
                            this.menu.select(event);
                        }
                        break;

                      case keyCode.TAB:
                        if (this.menu.active) {
                            this.menu.select(event);
                        }
                        break;

                      case keyCode.ESCAPE:
                        if (this.menu.element.is(":visible")) {
                            if (!this.isMultiLine) {
                                this._value(this.term);
                            }
                            this.close(event);
                            event.preventDefault();
                        }
                        break;

                      default:
                        suppressKeyPressRepeat = true;
                        this._searchTimeout(event);
                        break;
                    }
                },
                keypress: function(event) {
                    if (suppressKeyPress) {
                        suppressKeyPress = false;
                        if (!this.isMultiLine || this.menu.element.is(":visible")) {
                            event.preventDefault();
                        }
                        return;
                    }
                    if (suppressKeyPressRepeat) {
                        return;
                    }
                    var keyCode = $.ui.keyCode;
                    switch (event.keyCode) {
                      case keyCode.PAGE_UP:
                        this._move("previousPage", event);
                        break;

                      case keyCode.PAGE_DOWN:
                        this._move("nextPage", event);
                        break;

                      case keyCode.UP:
                        this._keyEvent("previous", event);
                        break;

                      case keyCode.DOWN:
                        this._keyEvent("next", event);
                        break;
                    }
                },
                input: function(event) {
                    if (suppressInput) {
                        suppressInput = false;
                        event.preventDefault();
                        return;
                    }
                    this._searchTimeout(event);
                },
                focus: function() {
                    this.selectedItem = null;
                    this.previous = this._value();
                },
                blur: function(event) {
                    if (this.cancelBlur) {
                        delete this.cancelBlur;
                        return;
                    }
                    clearTimeout(this.searching);
                    this.close(event);
                    this._change(event);
                }
            });
            this._initSource();
            this.menu = $("<ul>").appendTo(this._appendTo()).menu({
                role: null
            }).hide().menu("instance");
            this._addClass(this.menu.element, "ui-autocomplete", "ui-front");
            this._on(this.menu.element, {
                mousedown: function(event) {
                    event.preventDefault();
                    this.cancelBlur = true;
                    this._delay(function() {
                        delete this.cancelBlur;
                        if (this.element[0] !== $.ui.safeActiveElement(this.document[0])) {
                            this.element.trigger("focus");
                        }
                    });
                },
                menufocus: function(event, ui) {
                    var label, item;
                    if (this.isNewMenu) {
                        this.isNewMenu = false;
                        if (event.originalEvent && /^mouse/.test(event.originalEvent.type)) {
                            this.menu.blur();
                            this.document.one("mousemove", function() {
                                $(event.target).trigger(event.originalEvent);
                            });
                            return;
                        }
                    }
                    item = ui.item.data("ui-autocomplete-item");
                    if (false !== this._trigger("focus", event, {
                        item: item
                    })) {
                        if (event.originalEvent && /^key/.test(event.originalEvent.type)) {
                            this._value(item.value);
                        }
                    }
                    label = ui.item.attr("aria-label") || item.value;
                    if (label && $.trim(label).length) {
                        this.liveRegion.children().hide();
                        $("<div>").text(label).appendTo(this.liveRegion);
                    }
                },
                menuselect: function(event, ui) {
                    var item = ui.item.data("ui-autocomplete-item"), previous = this.previous;
                    if (this.element[0] !== $.ui.safeActiveElement(this.document[0])) {
                        this.element.trigger("focus");
                        this.previous = previous;
                        this._delay(function() {
                            this.previous = previous;
                            this.selectedItem = item;
                        });
                    }
                    if (false !== this._trigger("select", event, {
                        item: item
                    })) {
                        this._value(item.value);
                    }
                    this.term = this._value();
                    this.close(event);
                    this.selectedItem = item;
                }
            });
            this.liveRegion = $("<div>", {
                role: "status",
                "aria-live": "assertive",
                "aria-relevant": "additions"
            }).appendTo(this.document[0].body);
            this._addClass(this.liveRegion, null, "ui-helper-hidden-accessible");
            this._on(this.window, {
                beforeunload: function() {
                    this.element.removeAttr("autocomplete");
                }
            });
        },
        _destroy: function() {
            clearTimeout(this.searching);
            this.element.removeAttr("autocomplete");
            this.menu.element.remove();
            this.liveRegion.remove();
        },
        _setOption: function(key, value) {
            this._super(key, value);
            if (key === "source") {
                this._initSource();
            }
            if (key === "appendTo") {
                this.menu.element.appendTo(this._appendTo());
            }
            if (key === "disabled" && value && this.xhr) {
                this.xhr.abort();
            }
        },
        _isEventTargetInWidget: function(event) {
            var menuElement = this.menu.element[0];
            return event.target === this.element[0] || event.target === menuElement || $.contains(menuElement, event.target);
        },
        _closeOnClickOutside: function(event) {
            if (!this._isEventTargetInWidget(event)) {
                this.close();
            }
        },
        _appendTo: function() {
            var element = this.options.appendTo;
            if (element) {
                element = element.jquery || element.nodeType ? $(element) : this.document.find(element).eq(0);
            }
            if (!element || !element[0]) {
                element = this.element.closest(".ui-front, dialog");
            }
            if (!element.length) {
                element = this.document[0].body;
            }
            return element;
        },
        _initSource: function() {
            var array, url, that = this;
            if ($.isArray(this.options.source)) {
                array = this.options.source;
                this.source = function(request, response) {
                    response($.ui.autocomplete.filter(array, request.term));
                };
            } else if (typeof this.options.source === "string") {
                url = this.options.source;
                this.source = function(request, response) {
                    if (that.xhr) {
                        that.xhr.abort();
                    }
                    that.xhr = $.ajax({
                        url: url,
                        data: request,
                        dataType: "json",
                        success: function(data) {
                            response(data);
                        },
                        error: function() {
                            response([]);
                        }
                    });
                };
            } else {
                this.source = this.options.source;
            }
        },
        _searchTimeout: function(event) {
            clearTimeout(this.searching);
            this.searching = this._delay(function() {
                var equalValues = this.term === this._value(), menuVisible = this.menu.element.is(":visible"), modifierKey = event.altKey || event.ctrlKey || event.metaKey || event.shiftKey;
                if (!equalValues || equalValues && !menuVisible && !modifierKey) {
                    this.selectedItem = null;
                    this.search(null, event);
                }
            }, this.options.delay);
        },
        search: function(value, event) {
            value = value != null ? value : this._value();
            this.term = this._value();
            if (value.length < this.options.minLength) {
                return this.close(event);
            }
            if (this._trigger("search", event) === false) {
                return;
            }
            return this._search(value);
        },
        _search: function(value) {
            this.pending++;
            this._addClass("ui-autocomplete-loading");
            this.cancelSearch = false;
            this.source({
                term: value
            }, this._response());
        },
        _response: function() {
            var index = ++this.requestIndex;
            return $.proxy(function(content) {
                if (index === this.requestIndex) {
                    this.__response(content);
                }
                this.pending--;
                if (!this.pending) {
                    this._removeClass("ui-autocomplete-loading");
                }
            }, this);
        },
        __response: function(content) {
            if (content) {
                content = this._normalize(content);
            }
            this._trigger("response", null, {
                content: content
            });
            if (!this.options.disabled && content && content.length && !this.cancelSearch) {
                this._suggest(content);
                this._trigger("open");
            } else {
                this._close();
            }
        },
        close: function(event) {
            this.cancelSearch = true;
            this._close(event);
        },
        _close: function(event) {
            this._off(this.document, "mousedown");
            if (this.menu.element.is(":visible")) {
                this.menu.element.hide();
                this.menu.blur();
                this.isNewMenu = true;
                this._trigger("close", event);
            }
        },
        _change: function(event) {
            if (this.previous !== this._value()) {
                this._trigger("change", event, {
                    item: this.selectedItem
                });
            }
        },
        _normalize: function(items) {
            if (items.length && items[0].label && items[0].value) {
                return items;
            }
            return $.map(items, function(item) {
                if (typeof item === "string") {
                    return {
                        label: item,
                        value: item
                    };
                }
                return $.extend({}, item, {
                    label: item.label || item.value,
                    value: item.value || item.label
                });
            });
        },
        _suggest: function(items) {
            var ul = this.menu.element.empty();
            this._renderMenu(ul, items);
            this.isNewMenu = true;
            this.menu.refresh();
            ul.show();
            this._resizeMenu();
            ul.position($.extend({
                of: this.element
            }, this.options.position));
            if (this.options.autoFocus) {
                this.menu.next();
            }
            this._on(this.document, {
                mousedown: "_closeOnClickOutside"
            });
        },
        _resizeMenu: function() {
            var ul = this.menu.element;
            ul.outerWidth(Math.max(ul.width("").outerWidth() + 1, this.element.outerWidth()));
        },
        _renderMenu: function(ul, items) {
            var that = this;
            $.each(items, function(index, item) {
                that._renderItemData(ul, item);
            });
        },
        _renderItemData: function(ul, item) {
            return this._renderItem(ul, item).data("ui-autocomplete-item", item);
        },
        _renderItem: function(ul, item) {
            return $("<li>").append($("<div>").text(item.label)).appendTo(ul);
        },
        _move: function(direction, event) {
            if (!this.menu.element.is(":visible")) {
                this.search(null, event);
                return;
            }
            if (this.menu.isFirstItem() && /^previous/.test(direction) || this.menu.isLastItem() && /^next/.test(direction)) {
                if (!this.isMultiLine) {
                    this._value(this.term);
                }
                this.menu.blur();
                return;
            }
            this.menu[direction](event);
        },
        widget: function() {
            return this.menu.element;
        },
        _value: function() {
            return this.valueMethod.apply(this.element, arguments);
        },
        _keyEvent: function(keyEvent, event) {
            if (!this.isMultiLine || this.menu.element.is(":visible")) {
                this._move(keyEvent, event);
                event.preventDefault();
            }
        },
        _isContentEditable: function(element) {
            if (!element.length) {
                return false;
            }
            var editable = element.prop("contentEditable");
            if (editable === "inherit") {
                return this._isContentEditable(element.parent());
            }
            return editable === "true";
        }
    });
    $.extend($.ui.autocomplete, {
        escapeRegex: function(value) {
            return value.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&");
        },
        filter: function(array, term) {
            var matcher = new RegExp($.ui.autocomplete.escapeRegex(term), "i");
            return $.grep(array, function(value) {
                return matcher.test(value.label || value.value || value);
            });
        }
    });
    $.widget("ui.autocomplete", $.ui.autocomplete, {
        options: {
            messages: {
                noResults: "No search results.",
                results: function(amount) {
                    return amount + (amount > 1 ? " results are" : " result is") + " available, use up and down arrow keys to navigate.";
                }
            }
        },
        __response: function(content) {
            var message;
            this._superApply(arguments);
            if (this.options.disabled || this.cancelSearch) {
                return;
            }
            if (content && content.length) {
                message = this.options.messages.results(content.length);
            } else {
                message = this.options.messages.noResults;
            }
            this.liveRegion.children().hide();
            $("<div>").text(message).appendTo(this.liveRegion);
        }
    });
    var widgetsAutocomplete = $.ui.autocomplete;
    var controlgroupCornerRegex = /ui-corner-([a-z]){2,6}/g;
    var widgetsControlgroup = $.widget("ui.controlgroup", {
        version: "1.12.1",
        defaultElement: "<div>",
        options: {
            direction: "horizontal",
            disabled: null,
            onlyVisible: true,
            items: {
                button: "input[type=button], input[type=submit], input[type=reset], button, a",
                controlgroupLabel: ".ui-controlgroup-label",
                checkboxradio: "input[type='checkbox'], input[type='radio']",
                selectmenu: "select",
                spinner: ".ui-spinner-input"
            }
        },
        _create: function() {
            this._enhance();
        },
        _enhance: function() {
            this.element.attr("role", "toolbar");
            this.refresh();
        },
        _destroy: function() {
            this._callChildMethod("destroy");
            this.childWidgets.removeData("ui-controlgroup-data");
            this.element.removeAttr("role");
            if (this.options.items.controlgroupLabel) {
                this.element.find(this.options.items.controlgroupLabel).find(".ui-controlgroup-label-contents").contents().unwrap();
            }
        },
        _initWidgets: function() {
            var that = this, childWidgets = [];
            $.each(this.options.items, function(widget, selector) {
                var labels;
                var options = {};
                if (!selector) {
                    return;
                }
                if (widget === "controlgroupLabel") {
                    labels = that.element.find(selector);
                    labels.each(function() {
                        var element = $(this);
                        if (element.children(".ui-controlgroup-label-contents").length) {
                            return;
                        }
                        element.contents().wrapAll("<span class='ui-controlgroup-label-contents'></span>");
                    });
                    that._addClass(labels, null, "ui-widget ui-widget-content ui-state-default");
                    childWidgets = childWidgets.concat(labels.get());
                    return;
                }
                if (!$.fn[widget]) {
                    return;
                }
                if (that["_" + widget + "Options"]) {
                    options = that["_" + widget + "Options"]("middle");
                } else {
                    options = {
                        classes: {}
                    };
                }
                that.element.find(selector).each(function() {
                    var element = $(this);
                    var instance = element[widget]("instance");
                    var instanceOptions = $.widget.extend({}, options);
                    if (widget === "button" && element.parent(".ui-spinner").length) {
                        return;
                    }
                    if (!instance) {
                        instance = element[widget]()[widget]("instance");
                    }
                    if (instance) {
                        instanceOptions.classes = that._resolveClassesValues(instanceOptions.classes, instance);
                    }
                    element[widget](instanceOptions);
                    var widgetElement = element[widget]("widget");
                    $.data(widgetElement[0], "ui-controlgroup-data", instance ? instance : element[widget]("instance"));
                    childWidgets.push(widgetElement[0]);
                });
            });
            this.childWidgets = $($.unique(childWidgets));
            this._addClass(this.childWidgets, "ui-controlgroup-item");
        },
        _callChildMethod: function(method) {
            this.childWidgets.each(function() {
                var element = $(this), data = element.data("ui-controlgroup-data");
                if (data && data[method]) {
                    data[method]();
                }
            });
        },
        _updateCornerClass: function(element, position) {
            var remove = "ui-corner-top ui-corner-bottom ui-corner-left ui-corner-right ui-corner-all";
            var add = this._buildSimpleOptions(position, "label").classes.label;
            this._removeClass(element, null, remove);
            this._addClass(element, null, add);
        },
        _buildSimpleOptions: function(position, key) {
            var direction = this.options.direction === "vertical";
            var result = {
                classes: {}
            };
            result.classes[key] = {
                middle: "",
                first: "ui-corner-" + (direction ? "top" : "left"),
                last: "ui-corner-" + (direction ? "bottom" : "right"),
                only: "ui-corner-all"
            }[position];
            return result;
        },
        _spinnerOptions: function(position) {
            var options = this._buildSimpleOptions(position, "ui-spinner");
            options.classes["ui-spinner-up"] = "";
            options.classes["ui-spinner-down"] = "";
            return options;
        },
        _buttonOptions: function(position) {
            return this._buildSimpleOptions(position, "ui-button");
        },
        _checkboxradioOptions: function(position) {
            return this._buildSimpleOptions(position, "ui-checkboxradio-label");
        },
        _selectmenuOptions: function(position) {
            var direction = this.options.direction === "vertical";
            return {
                width: direction ? "auto" : false,
                classes: {
                    middle: {
                        "ui-selectmenu-button-open": "",
                        "ui-selectmenu-button-closed": ""
                    },
                    first: {
                        "ui-selectmenu-button-open": "ui-corner-" + (direction ? "top" : "tl"),
                        "ui-selectmenu-button-closed": "ui-corner-" + (direction ? "top" : "left")
                    },
                    last: {
                        "ui-selectmenu-button-open": direction ? "" : "ui-corner-tr",
                        "ui-selectmenu-button-closed": "ui-corner-" + (direction ? "bottom" : "right")
                    },
                    only: {
                        "ui-selectmenu-button-open": "ui-corner-top",
                        "ui-selectmenu-button-closed": "ui-corner-all"
                    }
                }[position]
            };
        },
        _resolveClassesValues: function(classes, instance) {
            var result = {};
            $.each(classes, function(key) {
                var current = instance.options.classes[key] || "";
                current = $.trim(current.replace(controlgroupCornerRegex, ""));
                result[key] = (current + " " + classes[key]).replace(/\s+/g, " ");
            });
            return result;
        },
        _setOption: function(key, value) {
            if (key === "direction") {
                this._removeClass("ui-controlgroup-" + this.options.direction);
            }
            this._super(key, value);
            if (key === "disabled") {
                this._callChildMethod(value ? "disable" : "enable");
                return;
            }
            this.refresh();
        },
        refresh: function() {
            var children, that = this;
            this._addClass("ui-controlgroup ui-controlgroup-" + this.options.direction);
            if (this.options.direction === "horizontal") {
                this._addClass(null, "ui-helper-clearfix");
            }
            this._initWidgets();
            children = this.childWidgets;
            if (this.options.onlyVisible) {
                children = children.filter(":visible");
            }
            if (children.length) {
                $.each([ "first", "last" ], function(index, value) {
                    var instance = children[value]().data("ui-controlgroup-data");
                    if (instance && that["_" + instance.widgetName + "Options"]) {
                        var options = that["_" + instance.widgetName + "Options"](children.length === 1 ? "only" : value);
                        options.classes = that._resolveClassesValues(options.classes, instance);
                        instance.element[instance.widgetName](options);
                    } else {
                        that._updateCornerClass(children[value](), value);
                    }
                });
                this._callChildMethod("refresh");
            }
        }
    });
    $.widget("ui.checkboxradio", [ $.ui.formResetMixin, {
        version: "1.12.1",
        options: {
            disabled: null,
            label: null,
            icon: true,
            classes: {
                "ui-checkboxradio-label": "ui-corner-all",
                "ui-checkboxradio-icon": "ui-corner-all"
            }
        },
        _getCreateOptions: function() {
            var disabled, labels;
            var that = this;
            var options = this._super() || {};
            this._readType();
            labels = this.element.labels();
            this.label = $(labels[labels.length - 1]);
            if (!this.label.length) {
                $.error("No label found for checkboxradio widget");
            }
            this.originalLabel = "";
            this.label.contents().not(this.element[0]).each(function() {
                that.originalLabel += this.nodeType === 3 ? $(this).text() : this.outerHTML;
            });
            if (this.originalLabel) {
                options.label = this.originalLabel;
            }
            disabled = this.element[0].disabled;
            if (disabled != null) {
                options.disabled = disabled;
            }
            return options;
        },
        _create: function() {
            var checked = this.element[0].checked;
            this._bindFormResetHandler();
            if (this.options.disabled == null) {
                this.options.disabled = this.element[0].disabled;
            }
            this._setOption("disabled", this.options.disabled);
            this._addClass("ui-checkboxradio", "ui-helper-hidden-accessible");
            this._addClass(this.label, "ui-checkboxradio-label", "ui-button ui-widget");
            if (this.type === "radio") {
                this._addClass(this.label, "ui-checkboxradio-radio-label");
            }
            if (this.options.label && this.options.label !== this.originalLabel) {
                this._updateLabel();
            } else if (this.originalLabel) {
                this.options.label = this.originalLabel;
            }
            this._enhance();
            if (checked) {
                this._addClass(this.label, "ui-checkboxradio-checked", "ui-state-active");
                if (this.icon) {
                    this._addClass(this.icon, null, "ui-state-hover");
                }
            }
            this._on({
                change: "_toggleClasses",
                focus: function() {
                    this._addClass(this.label, null, "ui-state-focus ui-visual-focus");
                },
                blur: function() {
                    this._removeClass(this.label, null, "ui-state-focus ui-visual-focus");
                }
            });
        },
        _readType: function() {
            var nodeName = this.element[0].nodeName.toLowerCase();
            this.type = this.element[0].type;
            if (nodeName !== "input" || !/radio|checkbox/.test(this.type)) {
                $.error("Can't create checkboxradio on element.nodeName=" + nodeName + " and element.type=" + this.type);
            }
        },
        _enhance: function() {
            this._updateIcon(this.element[0].checked);
        },
        widget: function() {
            return this.label;
        },
        _getRadioGroup: function() {
            var group;
            var name = this.element[0].name;
            var nameSelector = "input[name='" + $.ui.escapeSelector(name) + "']";
            if (!name) {
                return $([]);
            }
            if (this.form.length) {
                group = $(this.form[0].elements).filter(nameSelector);
            } else {
                group = $(nameSelector).filter(function() {
                    return $(this).form().length === 0;
                });
            }
            return group.not(this.element);
        },
        _toggleClasses: function() {
            var checked = this.element[0].checked;
            this._toggleClass(this.label, "ui-checkboxradio-checked", "ui-state-active", checked);
            if (this.options.icon && this.type === "checkbox") {
                this._toggleClass(this.icon, null, "ui-icon-check ui-state-checked", checked)._toggleClass(this.icon, null, "ui-icon-blank", !checked);
            }
            if (this.type === "radio") {
                this._getRadioGroup().each(function() {
                    var instance = $(this).checkboxradio("instance");
                    if (instance) {
                        instance._removeClass(instance.label, "ui-checkboxradio-checked", "ui-state-active");
                    }
                });
            }
        },
        _destroy: function() {
            this._unbindFormResetHandler();
            if (this.icon) {
                this.icon.remove();
                this.iconSpace.remove();
            }
        },
        _setOption: function(key, value) {
            if (key === "label" && !value) {
                return;
            }
            this._super(key, value);
            if (key === "disabled") {
                this._toggleClass(this.label, null, "ui-state-disabled", value);
                this.element[0].disabled = value;
                return;
            }
            this.refresh();
        },
        _updateIcon: function(checked) {
            var toAdd = "ui-icon ui-icon-background ";
            if (this.options.icon) {
                if (!this.icon) {
                    this.icon = $("<span>");
                    this.iconSpace = $("<span> </span>");
                    this._addClass(this.iconSpace, "ui-checkboxradio-icon-space");
                }
                if (this.type === "checkbox") {
                    toAdd += checked ? "ui-icon-check ui-state-checked" : "ui-icon-blank";
                    this._removeClass(this.icon, null, checked ? "ui-icon-blank" : "ui-icon-check");
                } else {
                    toAdd += "ui-icon-blank";
                }
                this._addClass(this.icon, "ui-checkboxradio-icon", toAdd);
                if (!checked) {
                    this._removeClass(this.icon, null, "ui-icon-check ui-state-checked");
                }
                this.icon.prependTo(this.label).after(this.iconSpace);
            } else if (this.icon !== undefined) {
                this.icon.remove();
                this.iconSpace.remove();
                delete this.icon;
            }
        },
        _updateLabel: function() {
            var contents = this.label.contents().not(this.element[0]);
            if (this.icon) {
                contents = contents.not(this.icon[0]);
            }
            if (this.iconSpace) {
                contents = contents.not(this.iconSpace[0]);
            }
            contents.remove();
            this.label.append(this.options.label);
        },
        refresh: function() {
            var checked = this.element[0].checked, isDisabled = this.element[0].disabled;
            this._updateIcon(checked);
            this._toggleClass(this.label, "ui-checkboxradio-checked", "ui-state-active", checked);
            if (this.options.label !== null) {
                this._updateLabel();
            }
            if (isDisabled !== this.options.disabled) {
                this._setOptions({
                    disabled: isDisabled
                });
            }
        }
    } ]);
    var widgetsCheckboxradio = $.ui.checkboxradio;
    $.widget("ui.button", {
        version: "1.12.1",
        defaultElement: "<button>",
        options: {
            classes: {
                "ui-button": "ui-corner-all"
            },
            disabled: null,
            icon: null,
            iconPosition: "beginning",
            label: null,
            showLabel: true
        },
        _getCreateOptions: function() {
            var disabled, options = this._super() || {};
            this.isInput = this.element.is("input");
            disabled = this.element[0].disabled;
            if (disabled != null) {
                options.disabled = disabled;
            }
            this.originalLabel = this.isInput ? this.element.val() : this.element.html();
            if (this.originalLabel) {
                options.label = this.originalLabel;
            }
            return options;
        },
        _create: function() {
            if (!this.option.showLabel & !this.options.icon) {
                this.options.showLabel = true;
            }
            if (this.options.disabled == null) {
                this.options.disabled = this.element[0].disabled || false;
            }
            this.hasTitle = !!this.element.attr("title");
            if (this.options.label && this.options.label !== this.originalLabel) {
                if (this.isInput) {
                    this.element.val(this.options.label);
                } else {
                    this.element.html(this.options.label);
                }
            }
            this._addClass("ui-button", "ui-widget");
            this._setOption("disabled", this.options.disabled);
            this._enhance();
            if (this.element.is("a")) {
                this._on({
                    keyup: function(event) {
                        if (event.keyCode === $.ui.keyCode.SPACE) {
                            event.preventDefault();
                            if (this.element[0].click) {
                                this.element[0].click();
                            } else {
                                this.element.trigger("click");
                            }
                        }
                    }
                });
            }
        },
        _enhance: function() {
            if (!this.element.is("button")) {
                this.element.attr("role", "button");
            }
            if (this.options.icon) {
                this._updateIcon("icon", this.options.icon);
                this._updateTooltip();
            }
        },
        _updateTooltip: function() {
            this.title = this.element.attr("title");
            if (!this.options.showLabel && !this.title) {
                this.element.attr("title", this.options.label);
            }
        },
        _updateIcon: function(option, value) {
            var icon = option !== "iconPosition", position = icon ? this.options.iconPosition : value, displayBlock = position === "top" || position === "bottom";
            if (!this.icon) {
                this.icon = $("<span>");
                this._addClass(this.icon, "ui-button-icon", "ui-icon");
                if (!this.options.showLabel) {
                    this._addClass("ui-button-icon-only");
                }
            } else if (icon) {
                this._removeClass(this.icon, null, this.options.icon);
            }
            if (icon) {
                this._addClass(this.icon, null, value);
            }
            this._attachIcon(position);
            if (displayBlock) {
                this._addClass(this.icon, null, "ui-widget-icon-block");
                if (this.iconSpace) {
                    this.iconSpace.remove();
                }
            } else {
                if (!this.iconSpace) {
                    this.iconSpace = $("<span> </span>");
                    this._addClass(this.iconSpace, "ui-button-icon-space");
                }
                this._removeClass(this.icon, null, "ui-wiget-icon-block");
                this._attachIconSpace(position);
            }
        },
        _destroy: function() {
            this.element.removeAttr("role");
            if (this.icon) {
                this.icon.remove();
            }
            if (this.iconSpace) {
                this.iconSpace.remove();
            }
            if (!this.hasTitle) {
                this.element.removeAttr("title");
            }
        },
        _attachIconSpace: function(iconPosition) {
            this.icon[/^(?:end|bottom)/.test(iconPosition) ? "before" : "after"](this.iconSpace);
        },
        _attachIcon: function(iconPosition) {
            this.element[/^(?:end|bottom)/.test(iconPosition) ? "append" : "prepend"](this.icon);
        },
        _setOptions: function(options) {
            var newShowLabel = options.showLabel === undefined ? this.options.showLabel : options.showLabel, newIcon = options.icon === undefined ? this.options.icon : options.icon;
            if (!newShowLabel && !newIcon) {
                options.showLabel = true;
            }
            this._super(options);
        },
        _setOption: function(key, value) {
            if (key === "icon") {
                if (value) {
                    this._updateIcon(key, value);
                } else if (this.icon) {
                    this.icon.remove();
                    if (this.iconSpace) {
                        this.iconSpace.remove();
                    }
                }
            }
            if (key === "iconPosition") {
                this._updateIcon(key, value);
            }
            if (key === "showLabel") {
                this._toggleClass("ui-button-icon-only", null, !value);
                this._updateTooltip();
            }
            if (key === "label") {
                if (this.isInput) {
                    this.element.val(value);
                } else {
                    this.element.html(value);
                    if (this.icon) {
                        this._attachIcon(this.options.iconPosition);
                        this._attachIconSpace(this.options.iconPosition);
                    }
                }
            }
            this._super(key, value);
            if (key === "disabled") {
                this._toggleClass(null, "ui-state-disabled", value);
                this.element[0].disabled = value;
                if (value) {
                    this.element.blur();
                }
            }
        },
        refresh: function() {
            var isDisabled = this.element.is("input, button") ? this.element[0].disabled : this.element.hasClass("ui-button-disabled");
            if (isDisabled !== this.options.disabled) {
                this._setOptions({
                    disabled: isDisabled
                });
            }
            this._updateTooltip();
        }
    });
    if ($.uiBackCompat !== false) {
        $.widget("ui.button", $.ui.button, {
            options: {
                text: true,
                icons: {
                    primary: null,
                    secondary: null
                }
            },
            _create: function() {
                if (this.options.showLabel && !this.options.text) {
                    this.options.showLabel = this.options.text;
                }
                if (!this.options.showLabel && this.options.text) {
                    this.options.text = this.options.showLabel;
                }
                if (!this.options.icon && (this.options.icons.primary || this.options.icons.secondary)) {
                    if (this.options.icons.primary) {
                        this.options.icon = this.options.icons.primary;
                    } else {
                        this.options.icon = this.options.icons.secondary;
                        this.options.iconPosition = "end";
                    }
                } else if (this.options.icon) {
                    this.options.icons.primary = this.options.icon;
                }
                this._super();
            },
            _setOption: function(key, value) {
                if (key === "text") {
                    this._super("showLabel", value);
                    return;
                }
                if (key === "showLabel") {
                    this.options.text = value;
                }
                if (key === "icon") {
                    this.options.icons.primary = value;
                }
                if (key === "icons") {
                    if (value.primary) {
                        this._super("icon", value.primary);
                        this._super("iconPosition", "beginning");
                    } else if (value.secondary) {
                        this._super("icon", value.secondary);
                        this._super("iconPosition", "end");
                    }
                }
                this._superApply(arguments);
            }
        });
        $.fn.button = function(orig) {
            return function() {
                if (!this.length || this.length && this[0].tagName !== "INPUT" || this.length && this[0].tagName === "INPUT" && (this.attr("type") !== "checkbox" && this.attr("type") !== "radio")) {
                    return orig.apply(this, arguments);
                }
                if (!$.ui.checkboxradio) {
                    $.error("Checkboxradio widget missing");
                }
                if (arguments.length === 0) {
                    return this.checkboxradio({
                        icon: false
                    });
                }
                return this.checkboxradio.apply(this, arguments);
            };
        }($.fn.button);
        $.fn.buttonset = function() {
            if (!$.ui.controlgroup) {
                $.error("Controlgroup widget missing");
            }
            if (arguments[0] === "option" && arguments[1] === "items" && arguments[2]) {
                return this.controlgroup.apply(this, [ arguments[0], "items.button", arguments[2] ]);
            }
            if (arguments[0] === "option" && arguments[1] === "items") {
                return this.controlgroup.apply(this, [ arguments[0], "items.button" ]);
            }
            if (typeof arguments[0] === "object" && arguments[0].items) {
                arguments[0].items = {
                    button: arguments[0].items
                };
            }
            return this.controlgroup.apply(this, arguments);
        };
    }
    var widgetsButton = $.ui.button;
    $.extend($.ui, {
        datepicker: {
            version: "1.12.1"
        }
    });
    var datepicker_instActive;
    function datepicker_getZindex(elem) {
        var position, value;
        while (elem.length && elem[0] !== document) {
            position = elem.css("position");
            if (position === "absolute" || position === "relative" || position === "fixed") {
                value = parseInt(elem.css("zIndex"), 10);
                if (!isNaN(value) && value !== 0) {
                    return value;
                }
            }
            elem = elem.parent();
        }
        return 0;
    }
    function Datepicker() {
        this._curInst = null;
        this._keyEvent = false;
        this._disabledInputs = [];
        this._datepickerShowing = false;
        this._inDialog = false;
        this._mainDivId = "ui-datepicker-div";
        this._inlineClass = "ui-datepicker-inline";
        this._appendClass = "ui-datepicker-append";
        this._triggerClass = "ui-datepicker-trigger";
        this._dialogClass = "ui-datepicker-dialog";
        this._disableClass = "ui-datepicker-disabled";
        this._unselectableClass = "ui-datepicker-unselectable";
        this._currentClass = "ui-datepicker-current-day";
        this._dayOverClass = "ui-datepicker-days-cell-over";
        this.regional = [];
        this.regional[""] = {
            closeText: "Done",
            prevText: "Prev",
            nextText: "Next",
            currentText: "Today",
            monthNames: [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ],
            monthNamesShort: [ "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" ],
            dayNames: [ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ],
            dayNamesShort: [ "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" ],
            dayNamesMin: [ "Su", "Mo", "Tu", "We", "Th", "Fr", "Sa" ],
            weekHeader: "Wk",
            dateFormat: "mm/dd/yy",
            firstDay: 0,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ""
        };
        this._defaults = {
            showOn: "focus",
            showAnim: "fadeIn",
            showOptions: {},
            defaultDate: null,
            appendText: "",
            buttonText: "...",
            buttonImage: "",
            buttonImageOnly: false,
            hideIfNoPrevNext: false,
            navigationAsDateFormat: false,
            gotoCurrent: false,
            changeMonth: false,
            changeYear: false,
            yearRange: "c-10:c+10",
            showOtherMonths: false,
            selectOtherMonths: false,
            showWeek: false,
            calculateWeek: this.iso8601Week,
            shortYearCutoff: "+10",
            minDate: null,
            maxDate: null,
            duration: "fast",
            beforeShowDay: null,
            beforeShow: null,
            onSelect: null,
            onChangeMonthYear: null,
            onClose: null,
            numberOfMonths: 1,
            showCurrentAtPos: 0,
            stepMonths: 1,
            stepBigMonths: 12,
            altField: "",
            altFormat: "",
            constrainInput: true,
            showButtonPanel: false,
            autoSize: false,
            disabled: false
        };
        $.extend(this._defaults, this.regional[""]);
        this.regional.en = $.extend(true, {}, this.regional[""]);
        this.regional["en-US"] = $.extend(true, {}, this.regional.en);
        this.dpDiv = datepicker_bindHover($("<div id='" + this._mainDivId + "' class='ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all'></div>"));
    }
    $.extend(Datepicker.prototype, {
        markerClassName: "hasDatepicker",
        maxRows: 4,
        _widgetDatepicker: function() {
            return this.dpDiv;
        },
        setDefaults: function(settings) {
            datepicker_extendRemove(this._defaults, settings || {});
            return this;
        },
        _attachDatepicker: function(target, settings) {
            var nodeName, inline, inst;
            nodeName = target.nodeName.toLowerCase();
            inline = nodeName === "div" || nodeName === "span";
            if (!target.id) {
                this.uuid += 1;
                target.id = "dp" + this.uuid;
            }
            inst = this._newInst($(target), inline);
            inst.settings = $.extend({}, settings || {});
            if (nodeName === "input") {
                this._connectDatepicker(target, inst);
            } else if (inline) {
                this._inlineDatepicker(target, inst);
            }
        },
        _newInst: function(target, inline) {
            var id = target[0].id.replace(/([^A-Za-z0-9_\-])/g, "\\\\$1");
            return {
                id: id,
                input: target,
                selectedDay: 0,
                selectedMonth: 0,
                selectedYear: 0,
                drawMonth: 0,
                drawYear: 0,
                inline: inline,
                dpDiv: !inline ? this.dpDiv : datepicker_bindHover($("<div class='" + this._inlineClass + " ui-datepicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all'></div>"))
            };
        },
        _connectDatepicker: function(target, inst) {
            var input = $(target);
            inst.append = $([]);
            inst.trigger = $([]);
            if (input.hasClass(this.markerClassName)) {
                return;
            }
            this._attachments(input, inst);
            input.addClass(this.markerClassName).on("keydown", this._doKeyDown).on("keypress", this._doKeyPress).on("keyup", this._doKeyUp);
            this._autoSize(inst);
            $.data(target, "datepicker", inst);
            if (inst.settings.disabled) {
                this._disableDatepicker(target);
            }
        },
        _attachments: function(input, inst) {
            var showOn, buttonText, buttonImage, appendText = this._get(inst, "appendText"), isRTL = this._get(inst, "isRTL");
            if (inst.append) {
                inst.append.remove();
            }
            if (appendText) {
                inst.append = $("<span class='" + this._appendClass + "'>" + appendText + "</span>");
                input[isRTL ? "before" : "after"](inst.append);
            }
            input.off("focus", this._showDatepicker);
            if (inst.trigger) {
                inst.trigger.remove();
            }
            showOn = this._get(inst, "showOn");
            if (showOn === "focus" || showOn === "both") {
                input.on("focus", this._showDatepicker);
            }
            if (showOn === "button" || showOn === "both") {
                buttonText = this._get(inst, "buttonText");
                buttonImage = this._get(inst, "buttonImage");
                inst.trigger = $(this._get(inst, "buttonImageOnly") ? $("<img/>").addClass(this._triggerClass).attr({
                    src: buttonImage,
                    alt: buttonText,
                    title: buttonText
                }) : $("<button type='button'></button>").addClass(this._triggerClass).html(!buttonImage ? buttonText : $("<img/>").attr({
                    src: buttonImage,
                    alt: buttonText,
                    title: buttonText
                })));
                input[isRTL ? "before" : "after"](inst.trigger);
                inst.trigger.on("click", function() {
                    if ($.datepicker._datepickerShowing && $.datepicker._lastInput === input[0]) {
                        $.datepicker._hideDatepicker();
                    } else if ($.datepicker._datepickerShowing && $.datepicker._lastInput !== input[0]) {
                        $.datepicker._hideDatepicker();
                        $.datepicker._showDatepicker(input[0]);
                    } else {
                        $.datepicker._showDatepicker(input[0]);
                    }
                    return false;
                });
            }
        },
        _autoSize: function(inst) {
            if (this._get(inst, "autoSize") && !inst.inline) {
                var findMax, max, maxI, i, date = new Date(2009, 12 - 1, 20), dateFormat = this._get(inst, "dateFormat");
                if (dateFormat.match(/[DM]/)) {
                    findMax = function(names) {
                        max = 0;
                        maxI = 0;
                        for (i = 0; i < names.length; i++) {
                            if (names[i].length > max) {
                                max = names[i].length;
                                maxI = i;
                            }
                        }
                        return maxI;
                    };
                    date.setMonth(findMax(this._get(inst, dateFormat.match(/MM/) ? "monthNames" : "monthNamesShort")));
                    date.setDate(findMax(this._get(inst, dateFormat.match(/DD/) ? "dayNames" : "dayNamesShort")) + 20 - date.getDay());
                }
                inst.input.attr("size", this._formatDate(inst, date).length);
            }
        },
        _inlineDatepicker: function(target, inst) {
            var divSpan = $(target);
            if (divSpan.hasClass(this.markerClassName)) {
                return;
            }
            divSpan.addClass(this.markerClassName).append(inst.dpDiv);
            $.data(target, "datepicker", inst);
            this._setDate(inst, this._getDefaultDate(inst), true);
            this._updateDatepicker(inst);
            this._updateAlternate(inst);
            if (inst.settings.disabled) {
                this._disableDatepicker(target);
            }
            inst.dpDiv.css("display", "block");
        },
        _dialogDatepicker: function(input, date, onSelect, settings, pos) {
            var id, browserWidth, browserHeight, scrollX, scrollY, inst = this._dialogInst;
            if (!inst) {
                this.uuid += 1;
                id = "dp" + this.uuid;
                this._dialogInput = $("<input type='text' id='" + id + "' style='position: absolute; top: -100px; width: 0px;'/>");
                this._dialogInput.on("keydown", this._doKeyDown);
                $("body").append(this._dialogInput);
                inst = this._dialogInst = this._newInst(this._dialogInput, false);
                inst.settings = {};
                $.data(this._dialogInput[0], "datepicker", inst);
            }
            datepicker_extendRemove(inst.settings, settings || {});
            date = date && date.constructor === Date ? this._formatDate(inst, date) : date;
            this._dialogInput.val(date);
            this._pos = pos ? pos.length ? pos : [ pos.pageX, pos.pageY ] : null;
            if (!this._pos) {
                browserWidth = document.documentElement.clientWidth;
                browserHeight = document.documentElement.clientHeight;
                scrollX = document.documentElement.scrollLeft || document.body.scrollLeft;
                scrollY = document.documentElement.scrollTop || document.body.scrollTop;
                this._pos = [ browserWidth / 2 - 100 + scrollX, browserHeight / 2 - 150 + scrollY ];
            }
            this._dialogInput.css("left", this._pos[0] + 20 + "px").css("top", this._pos[1] + "px");
            inst.settings.onSelect = onSelect;
            this._inDialog = true;
            this.dpDiv.addClass(this._dialogClass);
            this._showDatepicker(this._dialogInput[0]);
            if ($.blockUI) {
                $.blockUI(this.dpDiv);
            }
            $.data(this._dialogInput[0], "datepicker", inst);
            return this;
        },
        _destroyDatepicker: function(target) {
            var nodeName, $target = $(target), inst = $.data(target, "datepicker");
            if (!$target.hasClass(this.markerClassName)) {
                return;
            }
            nodeName = target.nodeName.toLowerCase();
            $.removeData(target, "datepicker");
            if (nodeName === "input") {
                inst.append.remove();
                inst.trigger.remove();
                $target.removeClass(this.markerClassName).off("focus", this._showDatepicker).off("keydown", this._doKeyDown).off("keypress", this._doKeyPress).off("keyup", this._doKeyUp);
            } else if (nodeName === "div" || nodeName === "span") {
                $target.removeClass(this.markerClassName).empty();
            }
            if (datepicker_instActive === inst) {
                datepicker_instActive = null;
            }
        },
        _enableDatepicker: function(target) {
            var nodeName, inline, $target = $(target), inst = $.data(target, "datepicker");
            if (!$target.hasClass(this.markerClassName)) {
                return;
            }
            nodeName = target.nodeName.toLowerCase();
            if (nodeName === "input") {
                target.disabled = false;
                inst.trigger.filter("button").each(function() {
                    this.disabled = false;
                }).end().filter("img").css({
                    opacity: "1.0",
                    cursor: ""
                });
            } else if (nodeName === "div" || nodeName === "span") {
                inline = $target.children("." + this._inlineClass);
                inline.children().removeClass("ui-state-disabled");
                inline.find("select.ui-datepicker-month, select.ui-datepicker-year").prop("disabled", false);
            }
            this._disabledInputs = $.map(this._disabledInputs, function(value) {
                return value === target ? null : value;
            });
        },
        _disableDatepicker: function(target) {
            var nodeName, inline, $target = $(target), inst = $.data(target, "datepicker");
            if (!$target.hasClass(this.markerClassName)) {
                return;
            }
            nodeName = target.nodeName.toLowerCase();
            if (nodeName === "input") {
                target.disabled = true;
                inst.trigger.filter("button").each(function() {
                    this.disabled = true;
                }).end().filter("img").css({
                    opacity: "0.5",
                    cursor: "default"
                });
            } else if (nodeName === "div" || nodeName === "span") {
                inline = $target.children("." + this._inlineClass);
                inline.children().addClass("ui-state-disabled");
                inline.find("select.ui-datepicker-month, select.ui-datepicker-year").prop("disabled", true);
            }
            this._disabledInputs = $.map(this._disabledInputs, function(value) {
                return value === target ? null : value;
            });
            this._disabledInputs[this._disabledInputs.length] = target;
        },
        _isDisabledDatepicker: function(target) {
            if (!target) {
                return false;
            }
            for (var i = 0; i < this._disabledInputs.length; i++) {
                if (this._disabledInputs[i] === target) {
                    return true;
                }
            }
            return false;
        },
        _getInst: function(target) {
            try {
                return $.data(target, "datepicker");
            } catch (err) {
                throw "Missing instance data for this datepicker";
            }
        },
        _optionDatepicker: function(target, name, value) {
            var settings, date, minDate, maxDate, inst = this._getInst(target);
            if (arguments.length === 2 && typeof name === "string") {
                return name === "defaults" ? $.extend({}, $.datepicker._defaults) : inst ? name === "all" ? $.extend({}, inst.settings) : this._get(inst, name) : null;
            }
            settings = name || {};
            if (typeof name === "string") {
                settings = {};
                settings[name] = value;
            }
            if (inst) {
                if (this._curInst === inst) {
                    this._hideDatepicker();
                }
                date = this._getDateDatepicker(target, true);
                minDate = this._getMinMaxDate(inst, "min");
                maxDate = this._getMinMaxDate(inst, "max");
                datepicker_extendRemove(inst.settings, settings);
                if (minDate !== null && settings.dateFormat !== undefined && settings.minDate === undefined) {
                    inst.settings.minDate = this._formatDate(inst, minDate);
                }
                if (maxDate !== null && settings.dateFormat !== undefined && settings.maxDate === undefined) {
                    inst.settings.maxDate = this._formatDate(inst, maxDate);
                }
                if ("disabled" in settings) {
                    if (settings.disabled) {
                        this._disableDatepicker(target);
                    } else {
                        this._enableDatepicker(target);
                    }
                }
                this._attachments($(target), inst);
                this._autoSize(inst);
                this._setDate(inst, date);
                this._updateAlternate(inst);
                this._updateDatepicker(inst);
            }
        },
        _changeDatepicker: function(target, name, value) {
            this._optionDatepicker(target, name, value);
        },
        _refreshDatepicker: function(target) {
            var inst = this._getInst(target);
            if (inst) {
                this._updateDatepicker(inst);
            }
        },
        _setDateDatepicker: function(target, date) {
            var inst = this._getInst(target);
            if (inst) {
                this._setDate(inst, date);
                this._updateDatepicker(inst);
                this._updateAlternate(inst);
            }
        },
        _getDateDatepicker: function(target, noDefault) {
            var inst = this._getInst(target);
            if (inst && !inst.inline) {
                this._setDateFromField(inst, noDefault);
            }
            return inst ? this._getDate(inst) : null;
        },
        _doKeyDown: function(event) {
            var onSelect, dateStr, sel, inst = $.datepicker._getInst(event.target), handled = true, isRTL = inst.dpDiv.is(".ui-datepicker-rtl");
            inst._keyEvent = true;
            if ($.datepicker._datepickerShowing) {
                switch (event.keyCode) {
                  case 9:
                    $.datepicker._hideDatepicker();
                    handled = false;
                    break;

                  case 13:
                    sel = $("td." + $.datepicker._dayOverClass + ":not(." + $.datepicker._currentClass + ")", inst.dpDiv);
                    if (sel[0]) {
                        $.datepicker._selectDay(event.target, inst.selectedMonth, inst.selectedYear, sel[0]);
                    }
                    onSelect = $.datepicker._get(inst, "onSelect");
                    if (onSelect) {
                        dateStr = $.datepicker._formatDate(inst);
                        onSelect.apply(inst.input ? inst.input[0] : null, [ dateStr, inst ]);
                    } else {
                        $.datepicker._hideDatepicker();
                    }
                    return false;

                  case 27:
                    $.datepicker._hideDatepicker();
                    break;

                  case 33:
                    $.datepicker._adjustDate(event.target, event.ctrlKey ? -$.datepicker._get(inst, "stepBigMonths") : -$.datepicker._get(inst, "stepMonths"), "M");
                    break;

                  case 34:
                    $.datepicker._adjustDate(event.target, event.ctrlKey ? +$.datepicker._get(inst, "stepBigMonths") : +$.datepicker._get(inst, "stepMonths"), "M");
                    break;

                  case 35:
                    if (event.ctrlKey || event.metaKey) {
                        $.datepicker._clearDate(event.target);
                    }
                    handled = event.ctrlKey || event.metaKey;
                    break;

                  case 36:
                    if (event.ctrlKey || event.metaKey) {
                        $.datepicker._gotoToday(event.target);
                    }
                    handled = event.ctrlKey || event.metaKey;
                    break;

                  case 37:
                    if (event.ctrlKey || event.metaKey) {
                        $.datepicker._adjustDate(event.target, isRTL ? +1 : -1, "D");
                    }
                    handled = event.ctrlKey || event.metaKey;
                    if (event.originalEvent.altKey) {
                        $.datepicker._adjustDate(event.target, event.ctrlKey ? -$.datepicker._get(inst, "stepBigMonths") : -$.datepicker._get(inst, "stepMonths"), "M");
                    }
                    break;

                  case 38:
                    if (event.ctrlKey || event.metaKey) {
                        $.datepicker._adjustDate(event.target, -7, "D");
                    }
                    handled = event.ctrlKey || event.metaKey;
                    break;

                  case 39:
                    if (event.ctrlKey || event.metaKey) {
                        $.datepicker._adjustDate(event.target, isRTL ? -1 : +1, "D");
                    }
                    handled = event.ctrlKey || event.metaKey;
                    if (event.originalEvent.altKey) {
                        $.datepicker._adjustDate(event.target, event.ctrlKey ? +$.datepicker._get(inst, "stepBigMonths") : +$.datepicker._get(inst, "stepMonths"), "M");
                    }
                    break;

                  case 40:
                    if (event.ctrlKey || event.metaKey) {
                        $.datepicker._adjustDate(event.target, +7, "D");
                    }
                    handled = event.ctrlKey || event.metaKey;
                    break;

                  default:
                    handled = false;
                }
            } else if (event.keyCode === 36 && event.ctrlKey) {
                $.datepicker._showDatepicker(this);
            } else {
                handled = false;
            }
            if (handled) {
                event.preventDefault();
                event.stopPropagation();
            }
        },
        _doKeyPress: function(event) {
            var chars, chr, inst = $.datepicker._getInst(event.target);
            if ($.datepicker._get(inst, "constrainInput")) {
                chars = $.datepicker._possibleChars($.datepicker._get(inst, "dateFormat"));
                chr = String.fromCharCode(event.charCode == null ? event.keyCode : event.charCode);
                return event.ctrlKey || event.metaKey || (chr < " " || !chars || chars.indexOf(chr) > -1);
            }
        },
        _doKeyUp: function(event) {
            var date, inst = $.datepicker._getInst(event.target);
            if (inst.input.val() !== inst.lastVal) {
                try {
                    date = $.datepicker.parseDate($.datepicker._get(inst, "dateFormat"), inst.input ? inst.input.val() : null, $.datepicker._getFormatConfig(inst));
                    if (date) {
                        $.datepicker._setDateFromField(inst);
                        $.datepicker._updateAlternate(inst);
                        $.datepicker._updateDatepicker(inst);
                    }
                } catch (err) {}
            }
            return true;
        },
        _showDatepicker: function(input) {
            input = input.target || input;
            if (input.nodeName.toLowerCase() !== "input") {
                input = $("input", input.parentNode)[0];
            }
            if ($.datepicker._isDisabledDatepicker(input) || $.datepicker._lastInput === input) {
                return;
            }
            var inst, beforeShow, beforeShowSettings, isFixed, offset, showAnim, duration;
            inst = $.datepicker._getInst(input);
            if ($.datepicker._curInst && $.datepicker._curInst !== inst) {
                $.datepicker._curInst.dpDiv.stop(true, true);
                if (inst && $.datepicker._datepickerShowing) {
                    $.datepicker._hideDatepicker($.datepicker._curInst.input[0]);
                }
            }
            beforeShow = $.datepicker._get(inst, "beforeShow");
            beforeShowSettings = beforeShow ? beforeShow.apply(input, [ input, inst ]) : {};
            if (beforeShowSettings === false) {
                return;
            }
            datepicker_extendRemove(inst.settings, beforeShowSettings);
            inst.lastVal = null;
            $.datepicker._lastInput = input;
            $.datepicker._setDateFromField(inst);
            if ($.datepicker._inDialog) {
                input.value = "";
            }
            if (!$.datepicker._pos) {
                $.datepicker._pos = $.datepicker._findPos(input);
                $.datepicker._pos[1] += input.offsetHeight;
            }
            isFixed = false;
            $(input).parents().each(function() {
                isFixed |= $(this).css("position") === "fixed";
                return !isFixed;
            });
            offset = {
                left: $.datepicker._pos[0],
                top: $.datepicker._pos[1]
            };
            $.datepicker._pos = null;
            inst.dpDiv.empty();
            inst.dpDiv.css({
                position: "absolute",
                display: "block",
                top: "-1000px"
            });
            $.datepicker._updateDatepicker(inst);
            offset = $.datepicker._checkOffset(inst, offset, isFixed);
            inst.dpDiv.css({
                position: $.datepicker._inDialog && $.blockUI ? "static" : isFixed ? "fixed" : "absolute",
                display: "none",
                left: offset.left + "px",
                top: offset.top + "px"
            });
            if (!inst.inline) {
                showAnim = $.datepicker._get(inst, "showAnim");
                duration = $.datepicker._get(inst, "duration");
                inst.dpDiv.css("z-index", datepicker_getZindex($(input)) + 1);
                $.datepicker._datepickerShowing = true;
                if ($.effects && $.effects.effect[showAnim]) {
                    inst.dpDiv.show(showAnim, $.datepicker._get(inst, "showOptions"), duration);
                } else {
                    inst.dpDiv[showAnim || "show"](showAnim ? duration : null);
                }
                if ($.datepicker._shouldFocusInput(inst)) {
                    inst.input.trigger("focus");
                }
                $.datepicker._curInst = inst;
            }
        },
        _updateDatepicker: function(inst) {
            this.maxRows = 4;
            datepicker_instActive = inst;
            inst.dpDiv.empty().append(this._generateHTML(inst));
            this._attachHandlers(inst);
            var origyearshtml, numMonths = this._getNumberOfMonths(inst), cols = numMonths[1], width = 17, activeCell = inst.dpDiv.find("." + this._dayOverClass + " a");
            if (activeCell.length > 0) {
                datepicker_handleMouseover.apply(activeCell.get(0));
            }
            inst.dpDiv.removeClass("ui-datepicker-multi-2 ui-datepicker-multi-3 ui-datepicker-multi-4").width("");
            if (cols > 1) {
                inst.dpDiv.addClass("ui-datepicker-multi-" + cols).css("width", width * cols + "em");
            }
            inst.dpDiv[(numMonths[0] !== 1 || numMonths[1] !== 1 ? "add" : "remove") + "Class"]("ui-datepicker-multi");
            inst.dpDiv[(this._get(inst, "isRTL") ? "add" : "remove") + "Class"]("ui-datepicker-rtl");
            if (inst === $.datepicker._curInst && $.datepicker._datepickerShowing && $.datepicker._shouldFocusInput(inst)) {
                inst.input.trigger("focus");
            }
            if (inst.yearshtml) {
                origyearshtml = inst.yearshtml;
                setTimeout(function() {
                    if (origyearshtml === inst.yearshtml && inst.yearshtml) {
                        inst.dpDiv.find("select.ui-datepicker-year:first").replaceWith(inst.yearshtml);
                    }
                    origyearshtml = inst.yearshtml = null;
                }, 0);
            }
        },
        _shouldFocusInput: function(inst) {
            return inst.input && inst.input.is(":visible") && !inst.input.is(":disabled") && !inst.input.is(":focus");
        },
        _checkOffset: function(inst, offset, isFixed) {
            var dpWidth = inst.dpDiv.outerWidth(), dpHeight = inst.dpDiv.outerHeight(), inputWidth = inst.input ? inst.input.outerWidth() : 0, inputHeight = inst.input ? inst.input.outerHeight() : 0, viewWidth = document.documentElement.clientWidth + (isFixed ? 0 : $(document).scrollLeft()), viewHeight = document.documentElement.clientHeight + (isFixed ? 0 : $(document).scrollTop());
            offset.left -= this._get(inst, "isRTL") ? dpWidth - inputWidth : 0;
            offset.left -= isFixed && offset.left === inst.input.offset().left ? $(document).scrollLeft() : 0;
            offset.top -= isFixed && offset.top === inst.input.offset().top + inputHeight ? $(document).scrollTop() : 0;
            offset.left -= Math.min(offset.left, offset.left + dpWidth > viewWidth && viewWidth > dpWidth ? Math.abs(offset.left + dpWidth - viewWidth) : 0);
            offset.top -= Math.min(offset.top, offset.top + dpHeight > viewHeight && viewHeight > dpHeight ? Math.abs(dpHeight + inputHeight) : 0);
            return offset;
        },
        _findPos: function(obj) {
            var position, inst = this._getInst(obj), isRTL = this._get(inst, "isRTL");
            while (obj && (obj.type === "hidden" || obj.nodeType !== 1 || $.expr.filters.hidden(obj))) {
                obj = obj[isRTL ? "previousSibling" : "nextSibling"];
            }
            position = $(obj).offset();
            return [ position.left, position.top ];
        },
        _hideDatepicker: function(input) {
            var showAnim, duration, postProcess, onClose, inst = this._curInst;
            if (!inst || input && inst !== $.data(input, "datepicker")) {
                return;
            }
            if (this._datepickerShowing) {
                showAnim = this._get(inst, "showAnim");
                duration = this._get(inst, "duration");
                postProcess = function() {
                    $.datepicker._tidyDialog(inst);
                };
                if ($.effects && ($.effects.effect[showAnim] || $.effects[showAnim])) {
                    inst.dpDiv.hide(showAnim, $.datepicker._get(inst, "showOptions"), duration, postProcess);
                } else {
                    inst.dpDiv[showAnim === "slideDown" ? "slideUp" : showAnim === "fadeIn" ? "fadeOut" : "hide"](showAnim ? duration : null, postProcess);
                }
                if (!showAnim) {
                    postProcess();
                }
                this._datepickerShowing = false;
                onClose = this._get(inst, "onClose");
                if (onClose) {
                    onClose.apply(inst.input ? inst.input[0] : null, [ inst.input ? inst.input.val() : "", inst ]);
                }
                this._lastInput = null;
                if (this._inDialog) {
                    this._dialogInput.css({
                        position: "absolute",
                        left: "0",
                        top: "-100px"
                    });
                    if ($.blockUI) {
                        $.unblockUI();
                        $("body").append(this.dpDiv);
                    }
                }
                this._inDialog = false;
            }
        },
        _tidyDialog: function(inst) {
            inst.dpDiv.removeClass(this._dialogClass).off(".ui-datepicker-calendar");
        },
        _checkExternalClick: function(event) {
            if (!$.datepicker._curInst) {
                return;
            }
            var $target = $(event.target), inst = $.datepicker._getInst($target[0]);
            if ($target[0].id !== $.datepicker._mainDivId && $target.parents("#" + $.datepicker._mainDivId).length === 0 && !$target.hasClass($.datepicker.markerClassName) && !$target.closest("." + $.datepicker._triggerClass).length && $.datepicker._datepickerShowing && !($.datepicker._inDialog && $.blockUI) || $target.hasClass($.datepicker.markerClassName) && $.datepicker._curInst !== inst) {
                $.datepicker._hideDatepicker();
            }
        },
        _adjustDate: function(id, offset, period) {
            var target = $(id), inst = this._getInst(target[0]);
            if (this._isDisabledDatepicker(target[0])) {
                return;
            }
            this._adjustInstDate(inst, offset + (period === "M" ? this._get(inst, "showCurrentAtPos") : 0), period);
            this._updateDatepicker(inst);
        },
        _gotoToday: function(id) {
            var date, target = $(id), inst = this._getInst(target[0]);
            if (this._get(inst, "gotoCurrent") && inst.currentDay) {
                inst.selectedDay = inst.currentDay;
                inst.drawMonth = inst.selectedMonth = inst.currentMonth;
                inst.drawYear = inst.selectedYear = inst.currentYear;
            } else {
                date = new Date();
                inst.selectedDay = date.getDate();
                inst.drawMonth = inst.selectedMonth = date.getMonth();
                inst.drawYear = inst.selectedYear = date.getFullYear();
            }
            this._notifyChange(inst);
            this._adjustDate(target);
        },
        _selectMonthYear: function(id, select, period) {
            var target = $(id), inst = this._getInst(target[0]);
            inst["selected" + (period === "M" ? "Month" : "Year")] = inst["draw" + (period === "M" ? "Month" : "Year")] = parseInt(select.options[select.selectedIndex].value, 10);
            this._notifyChange(inst);
            this._adjustDate(target);
        },
        _selectDay: function(id, month, year, td) {
            var inst, target = $(id);
            if ($(td).hasClass(this._unselectableClass) || this._isDisabledDatepicker(target[0])) {
                return;
            }
            inst = this._getInst(target[0]);
            inst.selectedDay = inst.currentDay = $("a", td).html();
            inst.selectedMonth = inst.currentMonth = month;
            inst.selectedYear = inst.currentYear = year;
            this._selectDate(id, this._formatDate(inst, inst.currentDay, inst.currentMonth, inst.currentYear));
        },
        _clearDate: function(id) {
            var target = $(id);
            this._selectDate(target, "");
        },
        _selectDate: function(id, dateStr) {
            var onSelect, target = $(id), inst = this._getInst(target[0]);
            dateStr = dateStr != null ? dateStr : this._formatDate(inst);
            if (inst.input) {
                inst.input.val(dateStr);
            }
            this._updateAlternate(inst);
            onSelect = this._get(inst, "onSelect");
            if (onSelect) {
                onSelect.apply(inst.input ? inst.input[0] : null, [ dateStr, inst ]);
            } else if (inst.input) {
                inst.input.trigger("change");
            }
            if (inst.inline) {
                this._updateDatepicker(inst);
            } else {
                this._hideDatepicker();
                this._lastInput = inst.input[0];
                if (typeof inst.input[0] !== "object") {
                    inst.input.trigger("focus");
                }
                this._lastInput = null;
            }
        },
        _updateAlternate: function(inst) {
            var altFormat, date, dateStr, altField = this._get(inst, "altField");
            if (altField) {
                altFormat = this._get(inst, "altFormat") || this._get(inst, "dateFormat");
                date = this._getDate(inst);
                dateStr = this.formatDate(altFormat, date, this._getFormatConfig(inst));
                $(altField).val(dateStr);
            }
        },
        noWeekends: function(date) {
            var day = date.getDay();
            return [ day > 0 && day < 6, "" ];
        },
        iso8601Week: function(date) {
            var time, checkDate = new Date(date.getTime());
            checkDate.setDate(checkDate.getDate() + 4 - (checkDate.getDay() || 7));
            time = checkDate.getTime();
            checkDate.setMonth(0);
            checkDate.setDate(1);
            return Math.floor(Math.round((time - checkDate) / 864e5) / 7) + 1;
        },
        parseDate: function(format, value, settings) {
            if (format == null || value == null) {
                throw "Invalid arguments";
            }
            value = typeof value === "object" ? value.toString() : value + "";
            if (value === "") {
                return null;
            }
            var iFormat, dim, extra, iValue = 0, shortYearCutoffTemp = (settings ? settings.shortYearCutoff : null) || this._defaults.shortYearCutoff, shortYearCutoff = typeof shortYearCutoffTemp !== "string" ? shortYearCutoffTemp : new Date().getFullYear() % 100 + parseInt(shortYearCutoffTemp, 10), dayNamesShort = (settings ? settings.dayNamesShort : null) || this._defaults.dayNamesShort, dayNames = (settings ? settings.dayNames : null) || this._defaults.dayNames, monthNamesShort = (settings ? settings.monthNamesShort : null) || this._defaults.monthNamesShort, monthNames = (settings ? settings.monthNames : null) || this._defaults.monthNames, year = -1, month = -1, day = -1, doy = -1, literal = false, date, lookAhead = function(match) {
                var matches = iFormat + 1 < format.length && format.charAt(iFormat + 1) === match;
                if (matches) {
                    iFormat++;
                }
                return matches;
            }, getNumber = function(match) {
                var isDoubled = lookAhead(match), size = match === "@" ? 14 : match === "!" ? 20 : match === "y" && isDoubled ? 4 : match === "o" ? 3 : 2, minSize = match === "y" ? size : 1, digits = new RegExp("^\\d{" + minSize + "," + size + "}"), num = value.substring(iValue).match(digits);
                if (!num) {
                    throw "Missing number at position " + iValue;
                }
                iValue += num[0].length;
                return parseInt(num[0], 10);
            }, getName = function(match, shortNames, longNames) {
                var index = -1, names = $.map(lookAhead(match) ? longNames : shortNames, function(v, k) {
                    return [ [ k, v ] ];
                }).sort(function(a, b) {
                    return -(a[1].length - b[1].length);
                });
                $.each(names, function(i, pair) {
                    var name = pair[1];
                    if (value.substr(iValue, name.length).toLowerCase() === name.toLowerCase()) {
                        index = pair[0];
                        iValue += name.length;
                        return false;
                    }
                });
                if (index !== -1) {
                    return index + 1;
                } else {
                    throw "Unknown name at position " + iValue;
                }
            }, checkLiteral = function() {
                if (value.charAt(iValue) !== format.charAt(iFormat)) {
                    throw "Unexpected literal at position " + iValue;
                }
                iValue++;
            };
            for (iFormat = 0; iFormat < format.length; iFormat++) {
                if (literal) {
                    if (format.charAt(iFormat) === "'" && !lookAhead("'")) {
                        literal = false;
                    } else {
                        checkLiteral();
                    }
                } else {
                    switch (format.charAt(iFormat)) {
                      case "d":
                        day = getNumber("d");
                        break;

                      case "D":
                        getName("D", dayNamesShort, dayNames);
                        break;

                      case "o":
                        doy = getNumber("o");
                        break;

                      case "m":
                        month = getNumber("m");
                        break;

                      case "M":
                        month = getName("M", monthNamesShort, monthNames);
                        break;

                      case "y":
                        year = getNumber("y");
                        break;

                      case "@":
                        date = new Date(getNumber("@"));
                        year = date.getFullYear();
                        month = date.getMonth() + 1;
                        day = date.getDate();
                        break;

                      case "!":
                        date = new Date((getNumber("!") - this._ticksTo1970) / 1e4);
                        year = date.getFullYear();
                        month = date.getMonth() + 1;
                        day = date.getDate();
                        break;

                      case "'":
                        if (lookAhead("'")) {
                            checkLiteral();
                        } else {
                            literal = true;
                        }
                        break;

                      default:
                        checkLiteral();
                    }
                }
            }
            if (iValue < value.length) {
                extra = value.substr(iValue);
                if (!/^\s+/.test(extra)) {
                    throw "Extra/unparsed characters found in date: " + extra;
                }
            }
            if (year === -1) {
                year = new Date().getFullYear();
            } else if (year < 100) {
                year += new Date().getFullYear() - new Date().getFullYear() % 100 + (year <= shortYearCutoff ? 0 : -100);
            }
            if (doy > -1) {
                month = 1;
                day = doy;
                do {
                    dim = this._getDaysInMonth(year, month - 1);
                    if (day <= dim) {
                        break;
                    }
                    month++;
                    day -= dim;
                } while (true);
            }
            date = this._daylightSavingAdjust(new Date(year, month - 1, day));
            if (date.getFullYear() !== year || date.getMonth() + 1 !== month || date.getDate() !== day) {
                throw "Invalid date";
            }
            return date;
        },
        ATOM: "yy-mm-dd",
        COOKIE: "D, dd M yy",
        ISO_8601: "yy-mm-dd",
        RFC_822: "D, d M y",
        RFC_850: "DD, dd-M-y",
        RFC_1036: "D, d M y",
        RFC_1123: "D, d M yy",
        RFC_2822: "D, d M yy",
        RSS: "D, d M y",
        TICKS: "!",
        TIMESTAMP: "@",
        W3C: "yy-mm-dd",
        _ticksTo1970: ((1970 - 1) * 365 + Math.floor(1970 / 4) - Math.floor(1970 / 100) + Math.floor(1970 / 400)) * 24 * 60 * 60 * 1e7,
        formatDate: function(format, date, settings) {
            if (!date) {
                return "";
            }
            var iFormat, dayNamesShort = (settings ? settings.dayNamesShort : null) || this._defaults.dayNamesShort, dayNames = (settings ? settings.dayNames : null) || this._defaults.dayNames, monthNamesShort = (settings ? settings.monthNamesShort : null) || this._defaults.monthNamesShort, monthNames = (settings ? settings.monthNames : null) || this._defaults.monthNames, lookAhead = function(match) {
                var matches = iFormat + 1 < format.length && format.charAt(iFormat + 1) === match;
                if (matches) {
                    iFormat++;
                }
                return matches;
            }, formatNumber = function(match, value, len) {
                var num = "" + value;
                if (lookAhead(match)) {
                    while (num.length < len) {
                        num = "0" + num;
                    }
                }
                return num;
            }, formatName = function(match, value, shortNames, longNames) {
                return lookAhead(match) ? longNames[value] : shortNames[value];
            }, output = "", literal = false;
            if (date) {
                for (iFormat = 0; iFormat < format.length; iFormat++) {
                    if (literal) {
                        if (format.charAt(iFormat) === "'" && !lookAhead("'")) {
                            literal = false;
                        } else {
                            output += format.charAt(iFormat);
                        }
                    } else {
                        switch (format.charAt(iFormat)) {
                          case "d":
                            output += formatNumber("d", date.getDate(), 2);
                            break;

                          case "D":
                            output += formatName("D", date.getDay(), dayNamesShort, dayNames);
                            break;

                          case "o":
                            output += formatNumber("o", Math.round((new Date(date.getFullYear(), date.getMonth(), date.getDate()).getTime() - new Date(date.getFullYear(), 0, 0).getTime()) / 864e5), 3);
                            break;

                          case "m":
                            output += formatNumber("m", date.getMonth() + 1, 2);
                            break;

                          case "M":
                            output += formatName("M", date.getMonth(), monthNamesShort, monthNames);
                            break;

                          case "y":
                            output += lookAhead("y") ? date.getFullYear() : (date.getFullYear() % 100 < 10 ? "0" : "") + date.getFullYear() % 100;
                            break;

                          case "@":
                            output += date.getTime();
                            break;

                          case "!":
                            output += date.getTime() * 1e4 + this._ticksTo1970;
                            break;

                          case "'":
                            if (lookAhead("'")) {
                                output += "'";
                            } else {
                                literal = true;
                            }
                            break;

                          default:
                            output += format.charAt(iFormat);
                        }
                    }
                }
            }
            return output;
        },
        _possibleChars: function(format) {
            var iFormat, chars = "", literal = false, lookAhead = function(match) {
                var matches = iFormat + 1 < format.length && format.charAt(iFormat + 1) === match;
                if (matches) {
                    iFormat++;
                }
                return matches;
            };
            for (iFormat = 0; iFormat < format.length; iFormat++) {
                if (literal) {
                    if (format.charAt(iFormat) === "'" && !lookAhead("'")) {
                        literal = false;
                    } else {
                        chars += format.charAt(iFormat);
                    }
                } else {
                    switch (format.charAt(iFormat)) {
                      case "d":
                      case "m":
                      case "y":
                      case "@":
                        chars += "0123456789";
                        break;

                      case "D":
                      case "M":
                        return null;

                      case "'":
                        if (lookAhead("'")) {
                            chars += "'";
                        } else {
                            literal = true;
                        }
                        break;

                      default:
                        chars += format.charAt(iFormat);
                    }
                }
            }
            return chars;
        },
        _get: function(inst, name) {
            return inst.settings[name] !== undefined ? inst.settings[name] : this._defaults[name];
        },
        _setDateFromField: function(inst, noDefault) {
            if (inst.input.val() === inst.lastVal) {
                return;
            }
            var dateFormat = this._get(inst, "dateFormat"), dates = inst.lastVal = inst.input ? inst.input.val() : null, defaultDate = this._getDefaultDate(inst), date = defaultDate, settings = this._getFormatConfig(inst);
            try {
                date = this.parseDate(dateFormat, dates, settings) || defaultDate;
            } catch (event) {
                dates = noDefault ? "" : dates;
            }
            inst.selectedDay = date.getDate();
            inst.drawMonth = inst.selectedMonth = date.getMonth();
            inst.drawYear = inst.selectedYear = date.getFullYear();
            inst.currentDay = dates ? date.getDate() : 0;
            inst.currentMonth = dates ? date.getMonth() : 0;
            inst.currentYear = dates ? date.getFullYear() : 0;
            this._adjustInstDate(inst);
        },
        _getDefaultDate: function(inst) {
            return this._restrictMinMax(inst, this._determineDate(inst, this._get(inst, "defaultDate"), new Date()));
        },
        _determineDate: function(inst, date, defaultDate) {
            var offsetNumeric = function(offset) {
                var date = new Date();
                date.setDate(date.getDate() + offset);
                return date;
            }, offsetString = function(offset) {
                try {
                    return $.datepicker.parseDate($.datepicker._get(inst, "dateFormat"), offset, $.datepicker._getFormatConfig(inst));
                } catch (e) {}
                var date = (offset.toLowerCase().match(/^c/) ? $.datepicker._getDate(inst) : null) || new Date(), year = date.getFullYear(), month = date.getMonth(), day = date.getDate(), pattern = /([+\-]?[0-9]+)\s*(d|D|w|W|m|M|y|Y)?/g, matches = pattern.exec(offset);
                while (matches) {
                    switch (matches[2] || "d") {
                      case "d":
                      case "D":
                        day += parseInt(matches[1], 10);
                        break;

                      case "w":
                      case "W":
                        day += parseInt(matches[1], 10) * 7;
                        break;

                      case "m":
                      case "M":
                        month += parseInt(matches[1], 10);
                        day = Math.min(day, $.datepicker._getDaysInMonth(year, month));
                        break;

                      case "y":
                      case "Y":
                        year += parseInt(matches[1], 10);
                        day = Math.min(day, $.datepicker._getDaysInMonth(year, month));
                        break;
                    }
                    matches = pattern.exec(offset);
                }
                return new Date(year, month, day);
            }, newDate = date == null || date === "" ? defaultDate : typeof date === "string" ? offsetString(date) : typeof date === "number" ? isNaN(date) ? defaultDate : offsetNumeric(date) : new Date(date.getTime());
            newDate = newDate && newDate.toString() === "Invalid Date" ? defaultDate : newDate;
            if (newDate) {
                newDate.setHours(0);
                newDate.setMinutes(0);
                newDate.setSeconds(0);
                newDate.setMilliseconds(0);
            }
            return this._daylightSavingAdjust(newDate);
        },
        _daylightSavingAdjust: function(date) {
            if (!date) {
                return null;
            }
            date.setHours(date.getHours() > 12 ? date.getHours() + 2 : 0);
            return date;
        },
        _setDate: function(inst, date, noChange) {
            var clear = !date, origMonth = inst.selectedMonth, origYear = inst.selectedYear, newDate = this._restrictMinMax(inst, this._determineDate(inst, date, new Date()));
            inst.selectedDay = inst.currentDay = newDate.getDate();
            inst.drawMonth = inst.selectedMonth = inst.currentMonth = newDate.getMonth();
            inst.drawYear = inst.selectedYear = inst.currentYear = newDate.getFullYear();
            if ((origMonth !== inst.selectedMonth || origYear !== inst.selectedYear) && !noChange) {
                this._notifyChange(inst);
            }
            this._adjustInstDate(inst);
            if (inst.input) {
                inst.input.val(clear ? "" : this._formatDate(inst));
            }
        },
        _getDate: function(inst) {
            var startDate = !inst.currentYear || inst.input && inst.input.val() === "" ? null : this._daylightSavingAdjust(new Date(inst.currentYear, inst.currentMonth, inst.currentDay));
            return startDate;
        },
        _attachHandlers: function(inst) {
            var stepMonths = this._get(inst, "stepMonths"), id = "#" + inst.id.replace(/\\\\/g, "\\");
            inst.dpDiv.find("[data-handler]").map(function() {
                var handler = {
                    prev: function() {
                        $.datepicker._adjustDate(id, -stepMonths, "M");
                    },
                    next: function() {
                        $.datepicker._adjustDate(id, +stepMonths, "M");
                    },
                    hide: function() {
                        $.datepicker._hideDatepicker();
                    },
                    today: function() {
                        $.datepicker._gotoToday(id);
                    },
                    selectDay: function() {
                        $.datepicker._selectDay(id, +this.getAttribute("data-month"), +this.getAttribute("data-year"), this);
                        return false;
                    },
                    selectMonth: function() {
                        $.datepicker._selectMonthYear(id, this, "M");
                        return false;
                    },
                    selectYear: function() {
                        $.datepicker._selectMonthYear(id, this, "Y");
                        return false;
                    }
                };
                $(this).on(this.getAttribute("data-event"), handler[this.getAttribute("data-handler")]);
            });
        },
        _generateHTML: function(inst) {
            var maxDraw, prevText, prev, nextText, next, currentText, gotoDate, controls, buttonPanel, firstDay, showWeek, dayNames, dayNamesMin, monthNames, monthNamesShort, beforeShowDay, showOtherMonths, selectOtherMonths, defaultDate, html, dow, row, group, col, selectedDate, cornerClass, calender, thead, day, daysInMonth, leadDays, curRows, numRows, printDate, dRow, tbody, daySettings, otherMonth, unselectable, tempDate = new Date(), today = this._daylightSavingAdjust(new Date(tempDate.getFullYear(), tempDate.getMonth(), tempDate.getDate())), isRTL = this._get(inst, "isRTL"), showButtonPanel = this._get(inst, "showButtonPanel"), hideIfNoPrevNext = this._get(inst, "hideIfNoPrevNext"), navigationAsDateFormat = this._get(inst, "navigationAsDateFormat"), numMonths = this._getNumberOfMonths(inst), showCurrentAtPos = this._get(inst, "showCurrentAtPos"), stepMonths = this._get(inst, "stepMonths"), isMultiMonth = numMonths[0] !== 1 || numMonths[1] !== 1, currentDate = this._daylightSavingAdjust(!inst.currentDay ? new Date(9999, 9, 9) : new Date(inst.currentYear, inst.currentMonth, inst.currentDay)), minDate = this._getMinMaxDate(inst, "min"), maxDate = this._getMinMaxDate(inst, "max"), drawMonth = inst.drawMonth - showCurrentAtPos, drawYear = inst.drawYear;
            if (drawMonth < 0) {
                drawMonth += 12;
                drawYear--;
            }
            if (maxDate) {
                maxDraw = this._daylightSavingAdjust(new Date(maxDate.getFullYear(), maxDate.getMonth() - numMonths[0] * numMonths[1] + 1, maxDate.getDate()));
                maxDraw = minDate && maxDraw < minDate ? minDate : maxDraw;
                while (this._daylightSavingAdjust(new Date(drawYear, drawMonth, 1)) > maxDraw) {
                    drawMonth--;
                    if (drawMonth < 0) {
                        drawMonth = 11;
                        drawYear--;
                    }
                }
            }
            inst.drawMonth = drawMonth;
            inst.drawYear = drawYear;
            prevText = this._get(inst, "prevText");
            prevText = !navigationAsDateFormat ? prevText : this.formatDate(prevText, this._daylightSavingAdjust(new Date(drawYear, drawMonth - stepMonths, 1)), this._getFormatConfig(inst));
            prev = this._canAdjustMonth(inst, -1, drawYear, drawMonth) ? "<a class='ui-datepicker-prev ui-corner-all' data-handler='prev' data-event='click'" + " title='" + prevText + "'><span class='ui-icon ui-icon-circle-triangle-" + (isRTL ? "e" : "w") + "'>" + prevText + "</span></a>" : hideIfNoPrevNext ? "" : "<a class='ui-datepicker-prev ui-corner-all ui-state-disabled' title='" + prevText + "'><span class='ui-icon ui-icon-circle-triangle-" + (isRTL ? "e" : "w") + "'>" + prevText + "</span></a>";
            nextText = this._get(inst, "nextText");
            nextText = !navigationAsDateFormat ? nextText : this.formatDate(nextText, this._daylightSavingAdjust(new Date(drawYear, drawMonth + stepMonths, 1)), this._getFormatConfig(inst));
            next = this._canAdjustMonth(inst, +1, drawYear, drawMonth) ? "<a class='ui-datepicker-next ui-corner-all' data-handler='next' data-event='click'" + " title='" + nextText + "'><span class='ui-icon ui-icon-circle-triangle-" + (isRTL ? "w" : "e") + "'>" + nextText + "</span></a>" : hideIfNoPrevNext ? "" : "<a class='ui-datepicker-next ui-corner-all ui-state-disabled' title='" + nextText + "'><span class='ui-icon ui-icon-circle-triangle-" + (isRTL ? "w" : "e") + "'>" + nextText + "</span></a>";
            currentText = this._get(inst, "currentText");
            gotoDate = this._get(inst, "gotoCurrent") && inst.currentDay ? currentDate : today;
            currentText = !navigationAsDateFormat ? currentText : this.formatDate(currentText, gotoDate, this._getFormatConfig(inst));
            controls = !inst.inline ? "<button type='button' class='ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all' data-handler='hide' data-event='click'>" + this._get(inst, "closeText") + "</button>" : "";
            buttonPanel = showButtonPanel ? "<div class='ui-datepicker-buttonpane ui-widget-content'>" + (isRTL ? controls : "") + (this._isInRange(inst, gotoDate) ? "<button type='button' class='ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all' data-handler='today' data-event='click'" + ">" + currentText + "</button>" : "") + (isRTL ? "" : controls) + "</div>" : "";
            firstDay = parseInt(this._get(inst, "firstDay"), 10);
            firstDay = isNaN(firstDay) ? 0 : firstDay;
            showWeek = this._get(inst, "showWeek");
            dayNames = this._get(inst, "dayNames");
            dayNamesMin = this._get(inst, "dayNamesMin");
            monthNames = this._get(inst, "monthNames");
            monthNamesShort = this._get(inst, "monthNamesShort");
            beforeShowDay = this._get(inst, "beforeShowDay");
            showOtherMonths = this._get(inst, "showOtherMonths");
            selectOtherMonths = this._get(inst, "selectOtherMonths");
            defaultDate = this._getDefaultDate(inst);
            html = "";
            for (row = 0; row < numMonths[0]; row++) {
                group = "";
                this.maxRows = 4;
                for (col = 0; col < numMonths[1]; col++) {
                    selectedDate = this._daylightSavingAdjust(new Date(drawYear, drawMonth, inst.selectedDay));
                    cornerClass = " ui-corner-all";
                    calender = "";
                    if (isMultiMonth) {
                        calender += "<div class='ui-datepicker-group";
                        if (numMonths[1] > 1) {
                            switch (col) {
                              case 0:
                                calender += " ui-datepicker-group-first";
                                cornerClass = " ui-corner-" + (isRTL ? "right" : "left");
                                break;

                              case numMonths[1] - 1:
                                calender += " ui-datepicker-group-last";
                                cornerClass = " ui-corner-" + (isRTL ? "left" : "right");
                                break;

                              default:
                                calender += " ui-datepicker-group-middle";
                                cornerClass = "";
                                break;
                            }
                        }
                        calender += "'>";
                    }
                    calender += "<div class='ui-datepicker-header ui-widget-header ui-helper-clearfix" + cornerClass + "'>" + (/all|left/.test(cornerClass) && row === 0 ? isRTL ? next : prev : "") + (/all|right/.test(cornerClass) && row === 0 ? isRTL ? prev : next : "") + this._generateMonthYearHeader(inst, drawMonth, drawYear, minDate, maxDate, row > 0 || col > 0, monthNames, monthNamesShort) + "</div><table class='ui-datepicker-calendar'><thead>" + "<tr>";
                    thead = showWeek ? "<th class='ui-datepicker-week-col'>" + this._get(inst, "weekHeader") + "</th>" : "";
                    for (dow = 0; dow < 7; dow++) {
                        day = (dow + firstDay) % 7;
                        thead += "<th scope='col'" + ((dow + firstDay + 6) % 7 >= 5 ? " class='ui-datepicker-week-end'" : "") + ">" + "<span title='" + dayNames[day] + "'>" + dayNamesMin[day] + "</span></th>";
                    }
                    calender += thead + "</tr></thead><tbody>";
                    daysInMonth = this._getDaysInMonth(drawYear, drawMonth);
                    if (drawYear === inst.selectedYear && drawMonth === inst.selectedMonth) {
                        inst.selectedDay = Math.min(inst.selectedDay, daysInMonth);
                    }
                    leadDays = (this._getFirstDayOfMonth(drawYear, drawMonth) - firstDay + 7) % 7;
                    curRows = Math.ceil((leadDays + daysInMonth) / 7);
                    numRows = isMultiMonth ? this.maxRows > curRows ? this.maxRows : curRows : curRows;
                    this.maxRows = numRows;
                    printDate = this._daylightSavingAdjust(new Date(drawYear, drawMonth, 1 - leadDays));
                    for (dRow = 0; dRow < numRows; dRow++) {
                        calender += "<tr>";
                        tbody = !showWeek ? "" : "<td class='ui-datepicker-week-col'>" + this._get(inst, "calculateWeek")(printDate) + "</td>";
                        for (dow = 0; dow < 7; dow++) {
                            daySettings = beforeShowDay ? beforeShowDay.apply(inst.input ? inst.input[0] : null, [ printDate ]) : [ true, "" ];
                            otherMonth = printDate.getMonth() !== drawMonth;
                            unselectable = otherMonth && !selectOtherMonths || !daySettings[0] || minDate && printDate < minDate || maxDate && printDate > maxDate;
                            tbody += "<td class='" + ((dow + firstDay + 6) % 7 >= 5 ? " ui-datepicker-week-end" : "") + (otherMonth ? " ui-datepicker-other-month" : "") + (printDate.getTime() === selectedDate.getTime() && drawMonth === inst.selectedMonth && inst._keyEvent || defaultDate.getTime() === printDate.getTime() && defaultDate.getTime() === selectedDate.getTime() ? " " + this._dayOverClass : "") + (unselectable ? " " + this._unselectableClass + " ui-state-disabled" : "") + (otherMonth && !showOtherMonths ? "" : " " + daySettings[1] + (printDate.getTime() === currentDate.getTime() ? " " + this._currentClass : "") + (printDate.getTime() === today.getTime() ? " ui-datepicker-today" : "")) + "'" + ((!otherMonth || showOtherMonths) && daySettings[2] ? " title='" + daySettings[2].replace(/'/g, "&#39;") + "'" : "") + (unselectable ? "" : " data-handler='selectDay' data-event='click' data-month='" + printDate.getMonth() + "' data-year='" + printDate.getFullYear() + "'") + ">" + (otherMonth && !showOtherMonths ? "&#xa0;" : unselectable ? "<span class='ui-state-default'>" + printDate.getDate() + "</span>" : "<a class='ui-state-default" + (printDate.getTime() === today.getTime() ? " ui-state-highlight" : "") + (printDate.getTime() === currentDate.getTime() ? " ui-state-active" : "") + (otherMonth ? " ui-priority-secondary" : "") + "' href='#'>" + printDate.getDate() + "</a>") + "</td>";
                            printDate.setDate(printDate.getDate() + 1);
                            printDate = this._daylightSavingAdjust(printDate);
                        }
                        calender += tbody + "</tr>";
                    }
                    drawMonth++;
                    if (drawMonth > 11) {
                        drawMonth = 0;
                        drawYear++;
                    }
                    calender += "</tbody></table>" + (isMultiMonth ? "</div>" + (numMonths[0] > 0 && col === numMonths[1] - 1 ? "<div class='ui-datepicker-row-break'></div>" : "") : "");
                    group += calender;
                }
                html += group;
            }
            html += buttonPanel;
            inst._keyEvent = false;
            return html;
        },
        _generateMonthYearHeader: function(inst, drawMonth, drawYear, minDate, maxDate, secondary, monthNames, monthNamesShort) {
            var inMinYear, inMaxYear, month, years, thisYear, determineYear, year, endYear, changeMonth = this._get(inst, "changeMonth"), changeYear = this._get(inst, "changeYear"), showMonthAfterYear = this._get(inst, "showMonthAfterYear"), html = "<div class='ui-datepicker-title'>", monthHtml = "";
            if (secondary || !changeMonth) {
                monthHtml += "<span class='ui-datepicker-month'>" + monthNames[drawMonth] + "</span>";
            } else {
                inMinYear = minDate && minDate.getFullYear() === drawYear;
                inMaxYear = maxDate && maxDate.getFullYear() === drawYear;
                monthHtml += "<select class='ui-datepicker-month' data-handler='selectMonth' data-event='change'>";
                for (month = 0; month < 12; month++) {
                    if ((!inMinYear || month >= minDate.getMonth()) && (!inMaxYear || month <= maxDate.getMonth())) {
                        monthHtml += "<option value='" + month + "'" + (month === drawMonth ? " selected='selected'" : "") + ">" + monthNamesShort[month] + "</option>";
                    }
                }
                monthHtml += "</select>";
            }
            if (!showMonthAfterYear) {
                html += monthHtml + (secondary || !(changeMonth && changeYear) ? "&#xa0;" : "");
            }
            if (!inst.yearshtml) {
                inst.yearshtml = "";
                if (secondary || !changeYear) {
                    html += "<span class='ui-datepicker-year'>" + drawYear + "</span>";
                } else {
                    years = this._get(inst, "yearRange").split(":");
                    thisYear = new Date().getFullYear();
                    determineYear = function(value) {
                        var year = value.match(/c[+\-].*/) ? drawYear + parseInt(value.substring(1), 10) : value.match(/[+\-].*/) ? thisYear + parseInt(value, 10) : parseInt(value, 10);
                        return isNaN(year) ? thisYear : year;
                    };
                    year = determineYear(years[0]);
                    endYear = Math.max(year, determineYear(years[1] || ""));
                    year = minDate ? Math.max(year, minDate.getFullYear()) : year;
                    endYear = maxDate ? Math.min(endYear, maxDate.getFullYear()) : endYear;
                    inst.yearshtml += "<select class='ui-datepicker-year' data-handler='selectYear' data-event='change'>";
                    for (;year <= endYear; year++) {
                        inst.yearshtml += "<option value='" + year + "'" + (year === drawYear ? " selected='selected'" : "") + ">" + year + "</option>";
                    }
                    inst.yearshtml += "</select>";
                    html += inst.yearshtml;
                    inst.yearshtml = null;
                }
            }
            html += this._get(inst, "yearSuffix");
            if (showMonthAfterYear) {
                html += (secondary || !(changeMonth && changeYear) ? "&#xa0;" : "") + monthHtml;
            }
            html += "</div>";
            return html;
        },
        _adjustInstDate: function(inst, offset, period) {
            var year = inst.selectedYear + (period === "Y" ? offset : 0), month = inst.selectedMonth + (period === "M" ? offset : 0), day = Math.min(inst.selectedDay, this._getDaysInMonth(year, month)) + (period === "D" ? offset : 0), date = this._restrictMinMax(inst, this._daylightSavingAdjust(new Date(year, month, day)));
            inst.selectedDay = date.getDate();
            inst.drawMonth = inst.selectedMonth = date.getMonth();
            inst.drawYear = inst.selectedYear = date.getFullYear();
            if (period === "M" || period === "Y") {
                this._notifyChange(inst);
            }
        },
        _restrictMinMax: function(inst, date) {
            var minDate = this._getMinMaxDate(inst, "min"), maxDate = this._getMinMaxDate(inst, "max"), newDate = minDate && date < minDate ? minDate : date;
            return maxDate && newDate > maxDate ? maxDate : newDate;
        },
        _notifyChange: function(inst) {
            var onChange = this._get(inst, "onChangeMonthYear");
            if (onChange) {
                onChange.apply(inst.input ? inst.input[0] : null, [ inst.selectedYear, inst.selectedMonth + 1, inst ]);
            }
        },
        _getNumberOfMonths: function(inst) {
            var numMonths = this._get(inst, "numberOfMonths");
            return numMonths == null ? [ 1, 1 ] : typeof numMonths === "number" ? [ 1, numMonths ] : numMonths;
        },
        _getMinMaxDate: function(inst, minMax) {
            return this._determineDate(inst, this._get(inst, minMax + "Date"), null);
        },
        _getDaysInMonth: function(year, month) {
            return 32 - this._daylightSavingAdjust(new Date(year, month, 32)).getDate();
        },
        _getFirstDayOfMonth: function(year, month) {
            return new Date(year, month, 1).getDay();
        },
        _canAdjustMonth: function(inst, offset, curYear, curMonth) {
            var numMonths = this._getNumberOfMonths(inst), date = this._daylightSavingAdjust(new Date(curYear, curMonth + (offset < 0 ? offset : numMonths[0] * numMonths[1]), 1));
            if (offset < 0) {
                date.setDate(this._getDaysInMonth(date.getFullYear(), date.getMonth()));
            }
            return this._isInRange(inst, date);
        },
        _isInRange: function(inst, date) {
            var yearSplit, currentYear, minDate = this._getMinMaxDate(inst, "min"), maxDate = this._getMinMaxDate(inst, "max"), minYear = null, maxYear = null, years = this._get(inst, "yearRange");
            if (years) {
                yearSplit = years.split(":");
                currentYear = new Date().getFullYear();
                minYear = parseInt(yearSplit[0], 10);
                maxYear = parseInt(yearSplit[1], 10);
                if (yearSplit[0].match(/[+\-].*/)) {
                    minYear += currentYear;
                }
                if (yearSplit[1].match(/[+\-].*/)) {
                    maxYear += currentYear;
                }
            }
            return (!minDate || date.getTime() >= minDate.getTime()) && (!maxDate || date.getTime() <= maxDate.getTime()) && (!minYear || date.getFullYear() >= minYear) && (!maxYear || date.getFullYear() <= maxYear);
        },
        _getFormatConfig: function(inst) {
            var shortYearCutoff = this._get(inst, "shortYearCutoff");
            shortYearCutoff = typeof shortYearCutoff !== "string" ? shortYearCutoff : new Date().getFullYear() % 100 + parseInt(shortYearCutoff, 10);
            return {
                shortYearCutoff: shortYearCutoff,
                dayNamesShort: this._get(inst, "dayNamesShort"),
                dayNames: this._get(inst, "dayNames"),
                monthNamesShort: this._get(inst, "monthNamesShort"),
                monthNames: this._get(inst, "monthNames")
            };
        },
        _formatDate: function(inst, day, month, year) {
            if (!day) {
                inst.currentDay = inst.selectedDay;
                inst.currentMonth = inst.selectedMonth;
                inst.currentYear = inst.selectedYear;
            }
            var date = day ? typeof day === "object" ? day : this._daylightSavingAdjust(new Date(year, month, day)) : this._daylightSavingAdjust(new Date(inst.currentYear, inst.currentMonth, inst.currentDay));
            return this.formatDate(this._get(inst, "dateFormat"), date, this._getFormatConfig(inst));
        }
    });
    function datepicker_bindHover(dpDiv) {
        var selector = "button, .ui-datepicker-prev, .ui-datepicker-next, .ui-datepicker-calendar td a";
        return dpDiv.on("mouseout", selector, function() {
            $(this).removeClass("ui-state-hover");
            if (this.className.indexOf("ui-datepicker-prev") !== -1) {
                $(this).removeClass("ui-datepicker-prev-hover");
            }
            if (this.className.indexOf("ui-datepicker-next") !== -1) {
                $(this).removeClass("ui-datepicker-next-hover");
            }
        }).on("mouseover", selector, datepicker_handleMouseover);
    }
    function datepicker_handleMouseover() {
        if (!$.datepicker._isDisabledDatepicker(datepicker_instActive.inline ? datepicker_instActive.dpDiv.parent()[0] : datepicker_instActive.input[0])) {
            $(this).parents(".ui-datepicker-calendar").find("a").removeClass("ui-state-hover");
            $(this).addClass("ui-state-hover");
            if (this.className.indexOf("ui-datepicker-prev") !== -1) {
                $(this).addClass("ui-datepicker-prev-hover");
            }
            if (this.className.indexOf("ui-datepicker-next") !== -1) {
                $(this).addClass("ui-datepicker-next-hover");
            }
        }
    }
    function datepicker_extendRemove(target, props) {
        $.extend(target, props);
        for (var name in props) {
            if (props[name] == null) {
                target[name] = props[name];
            }
        }
        return target;
    }
    $.fn.datepicker = function(options) {
        if (!this.length) {
            return this;
        }
        if (!$.datepicker.initialized) {
            $(document).on("mousedown", $.datepicker._checkExternalClick);
            $.datepicker.initialized = true;
        }
        if ($("#" + $.datepicker._mainDivId).length === 0) {
            $("body").append($.datepicker.dpDiv);
        }
        var otherArgs = Array.prototype.slice.call(arguments, 1);
        if (typeof options === "string" && (options === "isDisabled" || options === "getDate" || options === "widget")) {
            return $.datepicker["_" + options + "Datepicker"].apply($.datepicker, [ this[0] ].concat(otherArgs));
        }
        if (options === "option" && arguments.length === 2 && typeof arguments[1] === "string") {
            return $.datepicker["_" + options + "Datepicker"].apply($.datepicker, [ this[0] ].concat(otherArgs));
        }
        return this.each(function() {
            typeof options === "string" ? $.datepicker["_" + options + "Datepicker"].apply($.datepicker, [ this ].concat(otherArgs)) : $.datepicker._attachDatepicker(this, options);
        });
    };
    $.datepicker = new Datepicker();
    $.datepicker.initialized = false;
    $.datepicker.uuid = new Date().getTime();
    $.datepicker.version = "1.12.1";
    var widgetsDatepicker = $.datepicker;
    var ie = $.ui.ie = !!/msie [\w.]+/.exec(navigator.userAgent.toLowerCase());
    var mouseHandled = false;
    $(document).on("mouseup", function() {
        mouseHandled = false;
    });
    var widgetsMouse = $.widget("ui.mouse", {
        version: "1.12.1",
        options: {
            cancel: "input, textarea, button, select, option",
            distance: 1,
            delay: 0
        },
        _mouseInit: function() {
            var that = this;
            this.element.on("mousedown." + this.widgetName, function(event) {
                return that._mouseDown(event);
            }).on("click." + this.widgetName, function(event) {
                if (true === $.data(event.target, that.widgetName + ".preventClickEvent")) {
                    $.removeData(event.target, that.widgetName + ".preventClickEvent");
                    event.stopImmediatePropagation();
                    return false;
                }
            });
            this.started = false;
        },
        _mouseDestroy: function() {
            this.element.off("." + this.widgetName);
            if (this._mouseMoveDelegate) {
                this.document.off("mousemove." + this.widgetName, this._mouseMoveDelegate).off("mouseup." + this.widgetName, this._mouseUpDelegate);
            }
        },
        _mouseDown: function(event) {
            if (mouseHandled) {
                return;
            }
            this._mouseMoved = false;
            this._mouseStarted && this._mouseUp(event);
            this._mouseDownEvent = event;
            var that = this, btnIsLeft = event.which === 1, elIsCancel = typeof this.options.cancel === "string" && event.target.nodeName ? $(event.target).closest(this.options.cancel).length : false;
            if (!btnIsLeft || elIsCancel || !this._mouseCapture(event)) {
                return true;
            }
            this.mouseDelayMet = !this.options.delay;
            if (!this.mouseDelayMet) {
                this._mouseDelayTimer = setTimeout(function() {
                    that.mouseDelayMet = true;
                }, this.options.delay);
            }
            if (this._mouseDistanceMet(event) && this._mouseDelayMet(event)) {
                this._mouseStarted = this._mouseStart(event) !== false;
                if (!this._mouseStarted) {
                    event.preventDefault();
                    return true;
                }
            }
            if (true === $.data(event.target, this.widgetName + ".preventClickEvent")) {
                $.removeData(event.target, this.widgetName + ".preventClickEvent");
            }
            this._mouseMoveDelegate = function(event) {
                return that._mouseMove(event);
            };
            this._mouseUpDelegate = function(event) {
                return that._mouseUp(event);
            };
            this.document.on("mousemove." + this.widgetName, this._mouseMoveDelegate).on("mouseup." + this.widgetName, this._mouseUpDelegate);
            event.preventDefault();
            mouseHandled = true;
            return true;
        },
        _mouseMove: function(event) {
            if (this._mouseMoved) {
                if ($.ui.ie && (!document.documentMode || document.documentMode < 9) && !event.button) {
                    return this._mouseUp(event);
                } else if (!event.which) {
                    if (event.originalEvent.altKey || event.originalEvent.ctrlKey || event.originalEvent.metaKey || event.originalEvent.shiftKey) {
                        this.ignoreMissingWhich = true;
                    } else if (!this.ignoreMissingWhich) {
                        return this._mouseUp(event);
                    }
                }
            }
            if (event.which || event.button) {
                this._mouseMoved = true;
            }
            if (this._mouseStarted) {
                this._mouseDrag(event);
                return event.preventDefault();
            }
            if (this._mouseDistanceMet(event) && this._mouseDelayMet(event)) {
                this._mouseStarted = this._mouseStart(this._mouseDownEvent, event) !== false;
                this._mouseStarted ? this._mouseDrag(event) : this._mouseUp(event);
            }
            return !this._mouseStarted;
        },
        _mouseUp: function(event) {
            this.document.off("mousemove." + this.widgetName, this._mouseMoveDelegate).off("mouseup." + this.widgetName, this._mouseUpDelegate);
            if (this._mouseStarted) {
                this._mouseStarted = false;
                if (event.target === this._mouseDownEvent.target) {
                    $.data(event.target, this.widgetName + ".preventClickEvent", true);
                }
                this._mouseStop(event);
            }
            if (this._mouseDelayTimer) {
                clearTimeout(this._mouseDelayTimer);
                delete this._mouseDelayTimer;
            }
            this.ignoreMissingWhich = false;
            mouseHandled = false;
            event.preventDefault();
        },
        _mouseDistanceMet: function(event) {
            return Math.max(Math.abs(this._mouseDownEvent.pageX - event.pageX), Math.abs(this._mouseDownEvent.pageY - event.pageY)) >= this.options.distance;
        },
        _mouseDelayMet: function() {
            return this.mouseDelayMet;
        },
        _mouseStart: function() {},
        _mouseDrag: function() {},
        _mouseStop: function() {},
        _mouseCapture: function() {
            return true;
        }
    });
    var plugin = $.ui.plugin = {
        add: function(module, option, set) {
            var i, proto = $.ui[module].prototype;
            for (i in set) {
                proto.plugins[i] = proto.plugins[i] || [];
                proto.plugins[i].push([ option, set[i] ]);
            }
        },
        call: function(instance, name, args, allowDisconnected) {
            var i, set = instance.plugins[name];
            if (!set) {
                return;
            }
            if (!allowDisconnected && (!instance.element[0].parentNode || instance.element[0].parentNode.nodeType === 11)) {
                return;
            }
            for (i = 0; i < set.length; i++) {
                if (instance.options[set[i][0]]) {
                    set[i][1].apply(instance.element, args);
                }
            }
        }
    };
    var safeBlur = $.ui.safeBlur = function(element) {
        if (element && element.nodeName.toLowerCase() !== "body") {
            $(element).trigger("blur");
        }
    };
    $.widget("ui.draggable", $.ui.mouse, {
        version: "1.12.1",
        widgetEventPrefix: "drag",
        options: {
            addClasses: true,
            appendTo: "parent",
            axis: false,
            connectToSortable: false,
            containment: false,
            cursor: "auto",
            cursorAt: false,
            grid: false,
            handle: false,
            helper: "original",
            iframeFix: false,
            opacity: false,
            refreshPositions: false,
            revert: false,
            revertDuration: 500,
            scope: "default",
            scroll: true,
            scrollSensitivity: 20,
            scrollSpeed: 20,
            snap: false,
            snapMode: "both",
            snapTolerance: 20,
            stack: false,
            zIndex: false,
            drag: null,
            start: null,
            stop: null
        },
        _create: function() {
            if (this.options.helper === "original") {
                this._setPositionRelative();
            }
            if (this.options.addClasses) {
                this._addClass("ui-draggable");
            }
            this._setHandleClassName();
            this._mouseInit();
        },
        _setOption: function(key, value) {
            this._super(key, value);
            if (key === "handle") {
                this._removeHandleClassName();
                this._setHandleClassName();
            }
        },
        _destroy: function() {
            if ((this.helper || this.element).is(".ui-draggable-dragging")) {
                this.destroyOnClear = true;
                return;
            }
            this._removeHandleClassName();
            this._mouseDestroy();
        },
        _mouseCapture: function(event) {
            var o = this.options;
            if (this.helper || o.disabled || $(event.target).closest(".ui-resizable-handle").length > 0) {
                return false;
            }
            this.handle = this._getHandle(event);
            if (!this.handle) {
                return false;
            }
            this._blurActiveElement(event);
            this._blockFrames(o.iframeFix === true ? "iframe" : o.iframeFix);
            return true;
        },
        _blockFrames: function(selector) {
            this.iframeBlocks = this.document.find(selector).map(function() {
                var iframe = $(this);
                return $("<div>").css("position", "absolute").appendTo(iframe.parent()).outerWidth(iframe.outerWidth()).outerHeight(iframe.outerHeight()).offset(iframe.offset())[0];
            });
        },
        _unblockFrames: function() {
            if (this.iframeBlocks) {
                this.iframeBlocks.remove();
                delete this.iframeBlocks;
            }
        },
        _blurActiveElement: function(event) {
            var activeElement = $.ui.safeActiveElement(this.document[0]), target = $(event.target);
            if (target.closest(activeElement).length) {
                return;
            }
            $.ui.safeBlur(activeElement);
        },
        _mouseStart: function(event) {
            var o = this.options;
            this.helper = this._createHelper(event);
            this._addClass(this.helper, "ui-draggable-dragging");
            this._cacheHelperProportions();
            if ($.ui.ddmanager) {
                $.ui.ddmanager.current = this;
            }
            this._cacheMargins();
            this.cssPosition = this.helper.css("position");
            this.scrollParent = this.helper.scrollParent(true);
            this.offsetParent = this.helper.offsetParent();
            this.hasFixedAncestor = this.helper.parents().filter(function() {
                return $(this).css("position") === "fixed";
            }).length > 0;
            this.positionAbs = this.element.offset();
            this._refreshOffsets(event);
            this.originalPosition = this.position = this._generatePosition(event, false);
            this.originalPageX = event.pageX;
            this.originalPageY = event.pageY;
            o.cursorAt && this._adjustOffsetFromHelper(o.cursorAt);
            this._setContainment();
            if (this._trigger("start", event) === false) {
                this._clear();
                return false;
            }
            this._cacheHelperProportions();
            if ($.ui.ddmanager && !o.dropBehaviour) {
                $.ui.ddmanager.prepareOffsets(this, event);
            }
            this._mouseDrag(event, true);
            if ($.ui.ddmanager) {
                $.ui.ddmanager.dragStart(this, event);
            }
            return true;
        },
        _refreshOffsets: function(event) {
            this.offset = {
                top: this.positionAbs.top - this.margins.top,
                left: this.positionAbs.left - this.margins.left,
                scroll: false,
                parent: this._getParentOffset(),
                relative: this._getRelativeOffset()
            };
            this.offset.click = {
                left: event.pageX - this.offset.left,
                top: event.pageY - this.offset.top
            };
        },
        _mouseDrag: function(event, noPropagation) {
            if (this.hasFixedAncestor) {
                this.offset.parent = this._getParentOffset();
            }
            this.position = this._generatePosition(event, true);
            this.positionAbs = this._convertPositionTo("absolute");
            if (!noPropagation) {
                var ui = this._uiHash();
                if (this._trigger("drag", event, ui) === false) {
                    this._mouseUp(new $.Event("mouseup", event));
                    return false;
                }
                this.position = ui.position;
            }
            this.helper[0].style.left = this.position.left + "px";
            this.helper[0].style.top = this.position.top + "px";
            if ($.ui.ddmanager) {
                $.ui.ddmanager.drag(this, event);
            }
            return false;
        },
        _mouseStop: function(event) {
            var that = this, dropped = false;
            if ($.ui.ddmanager && !this.options.dropBehaviour) {
                dropped = $.ui.ddmanager.drop(this, event);
            }
            if (this.dropped) {
                dropped = this.dropped;
                this.dropped = false;
            }
            if (this.options.revert === "invalid" && !dropped || this.options.revert === "valid" && dropped || this.options.revert === true || $.isFunction(this.options.revert) && this.options.revert.call(this.element, dropped)) {
                $(this.helper).animate(this.originalPosition, parseInt(this.options.revertDuration, 10), function() {
                    if (that._trigger("stop", event) !== false) {
                        that._clear();
                    }
                });
            } else {
                if (this._trigger("stop", event) !== false) {
                    this._clear();
                }
            }
            return false;
        },
        _mouseUp: function(event) {
            this._unblockFrames();
            if ($.ui.ddmanager) {
                $.ui.ddmanager.dragStop(this, event);
            }
            if (this.handleElement.is(event.target)) {
                this.element.trigger("focus");
            }
            return $.ui.mouse.prototype._mouseUp.call(this, event);
        },
        cancel: function() {
            if (this.helper.is(".ui-draggable-dragging")) {
                this._mouseUp(new $.Event("mouseup", {
                    target: this.element[0]
                }));
            } else {
                this._clear();
            }
            return this;
        },
        _getHandle: function(event) {
            return this.options.handle ? !!$(event.target).closest(this.element.find(this.options.handle)).length : true;
        },
        _setHandleClassName: function() {
            this.handleElement = this.options.handle ? this.element.find(this.options.handle) : this.element;
            this._addClass(this.handleElement, "ui-draggable-handle");
        },
        _removeHandleClassName: function() {
            this._removeClass(this.handleElement, "ui-draggable-handle");
        },
        _createHelper: function(event) {
            var o = this.options, helperIsFunction = $.isFunction(o.helper), helper = helperIsFunction ? $(o.helper.apply(this.element[0], [ event ])) : o.helper === "clone" ? this.element.clone().removeAttr("id") : this.element;
            if (!helper.parents("body").length) {
                helper.appendTo(o.appendTo === "parent" ? this.element[0].parentNode : o.appendTo);
            }
            if (helperIsFunction && helper[0] === this.element[0]) {
                this._setPositionRelative();
            }
            if (helper[0] !== this.element[0] && !/(fixed|absolute)/.test(helper.css("position"))) {
                helper.css("position", "absolute");
            }
            return helper;
        },
        _setPositionRelative: function() {
            if (!/^(?:r|a|f)/.test(this.element.css("position"))) {
                this.element[0].style.position = "relative";
            }
        },
        _adjustOffsetFromHelper: function(obj) {
            if (typeof obj === "string") {
                obj = obj.split(" ");
            }
            if ($.isArray(obj)) {
                obj = {
                    left: +obj[0],
                    top: +obj[1] || 0
                };
            }
            if ("left" in obj) {
                this.offset.click.left = obj.left + this.margins.left;
            }
            if ("right" in obj) {
                this.offset.click.left = this.helperProportions.width - obj.right + this.margins.left;
            }
            if ("top" in obj) {
                this.offset.click.top = obj.top + this.margins.top;
            }
            if ("bottom" in obj) {
                this.offset.click.top = this.helperProportions.height - obj.bottom + this.margins.top;
            }
        },
        _isRootNode: function(element) {
            return /(html|body)/i.test(element.tagName) || element === this.document[0];
        },
        _getParentOffset: function() {
            var po = this.offsetParent.offset(), document = this.document[0];
            if (this.cssPosition === "absolute" && this.scrollParent[0] !== document && $.contains(this.scrollParent[0], this.offsetParent[0])) {
                po.left += this.scrollParent.scrollLeft();
                po.top += this.scrollParent.scrollTop();
            }
            if (this._isRootNode(this.offsetParent[0])) {
                po = {
                    top: 0,
                    left: 0
                };
            }
            return {
                top: po.top + (parseInt(this.offsetParent.css("borderTopWidth"), 10) || 0),
                left: po.left + (parseInt(this.offsetParent.css("borderLeftWidth"), 10) || 0)
            };
        },
        _getRelativeOffset: function() {
            if (this.cssPosition !== "relative") {
                return {
                    top: 0,
                    left: 0
                };
            }
            var p = this.element.position(), scrollIsRootNode = this._isRootNode(this.scrollParent[0]);
            return {
                top: p.top - (parseInt(this.helper.css("top"), 10) || 0) + (!scrollIsRootNode ? this.scrollParent.scrollTop() : 0),
                left: p.left - (parseInt(this.helper.css("left"), 10) || 0) + (!scrollIsRootNode ? this.scrollParent.scrollLeft() : 0)
            };
        },
        _cacheMargins: function() {
            this.margins = {
                left: parseInt(this.element.css("marginLeft"), 10) || 0,
                top: parseInt(this.element.css("marginTop"), 10) || 0,
                right: parseInt(this.element.css("marginRight"), 10) || 0,
                bottom: parseInt(this.element.css("marginBottom"), 10) || 0
            };
        },
        _cacheHelperProportions: function() {
            this.helperProportions = {
                width: this.helper.outerWidth(),
                height: this.helper.outerHeight()
            };
        },
        _setContainment: function() {
            var isUserScrollable, c, ce, o = this.options, document = this.document[0];
            this.relativeContainer = null;
            if (!o.containment) {
                this.containment = null;
                return;
            }
            if (o.containment === "window") {
                this.containment = [ $(window).scrollLeft() - this.offset.relative.left - this.offset.parent.left, $(window).scrollTop() - this.offset.relative.top - this.offset.parent.top, $(window).scrollLeft() + $(window).width() - this.helperProportions.width - this.margins.left, $(window).scrollTop() + ($(window).height() || document.body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top ];
                return;
            }
            if (o.containment === "document") {
                this.containment = [ 0, 0, $(document).width() - this.helperProportions.width - this.margins.left, ($(document).height() || document.body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top ];
                return;
            }
            if (o.containment.constructor === Array) {
                this.containment = o.containment;
                return;
            }
            if (o.containment === "parent") {
                o.containment = this.helper[0].parentNode;
            }
            c = $(o.containment);
            ce = c[0];
            if (!ce) {
                return;
            }
            isUserScrollable = /(scroll|auto)/.test(c.css("overflow"));
            this.containment = [ (parseInt(c.css("borderLeftWidth"), 10) || 0) + (parseInt(c.css("paddingLeft"), 10) || 0), (parseInt(c.css("borderTopWidth"), 10) || 0) + (parseInt(c.css("paddingTop"), 10) || 0), (isUserScrollable ? Math.max(ce.scrollWidth, ce.offsetWidth) : ce.offsetWidth) - (parseInt(c.css("borderRightWidth"), 10) || 0) - (parseInt(c.css("paddingRight"), 10) || 0) - this.helperProportions.width - this.margins.left - this.margins.right, (isUserScrollable ? Math.max(ce.scrollHeight, ce.offsetHeight) : ce.offsetHeight) - (parseInt(c.css("borderBottomWidth"), 10) || 0) - (parseInt(c.css("paddingBottom"), 10) || 0) - this.helperProportions.height - this.margins.top - this.margins.bottom ];
            this.relativeContainer = c;
        },
        _convertPositionTo: function(d, pos) {
            if (!pos) {
                pos = this.position;
            }
            var mod = d === "absolute" ? 1 : -1, scrollIsRootNode = this._isRootNode(this.scrollParent[0]);
            return {
                top: pos.top + this.offset.relative.top * mod + this.offset.parent.top * mod - (this.cssPosition === "fixed" ? -this.offset.scroll.top : scrollIsRootNode ? 0 : this.offset.scroll.top) * mod,
                left: pos.left + this.offset.relative.left * mod + this.offset.parent.left * mod - (this.cssPosition === "fixed" ? -this.offset.scroll.left : scrollIsRootNode ? 0 : this.offset.scroll.left) * mod
            };
        },
        _generatePosition: function(event, constrainPosition) {
            var containment, co, top, left, o = this.options, scrollIsRootNode = this._isRootNode(this.scrollParent[0]), pageX = event.pageX, pageY = event.pageY;
            if (!scrollIsRootNode || !this.offset.scroll) {
                this.offset.scroll = {
                    top: this.scrollParent.scrollTop(),
                    left: this.scrollParent.scrollLeft()
                };
            }
            if (constrainPosition) {
                if (this.containment) {
                    if (this.relativeContainer) {
                        co = this.relativeContainer.offset();
                        containment = [ this.containment[0] + co.left, this.containment[1] + co.top, this.containment[2] + co.left, this.containment[3] + co.top ];
                    } else {
                        containment = this.containment;
                    }
                    if (event.pageX - this.offset.click.left < containment[0]) {
                        pageX = containment[0] + this.offset.click.left;
                    }
                    if (event.pageY - this.offset.click.top < containment[1]) {
                        pageY = containment[1] + this.offset.click.top;
                    }
                    if (event.pageX - this.offset.click.left > containment[2]) {
                        pageX = containment[2] + this.offset.click.left;
                    }
                    if (event.pageY - this.offset.click.top > containment[3]) {
                        pageY = containment[3] + this.offset.click.top;
                    }
                }
                if (o.grid) {
                    top = o.grid[1] ? this.originalPageY + Math.round((pageY - this.originalPageY) / o.grid[1]) * o.grid[1] : this.originalPageY;
                    pageY = containment ? top - this.offset.click.top >= containment[1] || top - this.offset.click.top > containment[3] ? top : top - this.offset.click.top >= containment[1] ? top - o.grid[1] : top + o.grid[1] : top;
                    left = o.grid[0] ? this.originalPageX + Math.round((pageX - this.originalPageX) / o.grid[0]) * o.grid[0] : this.originalPageX;
                    pageX = containment ? left - this.offset.click.left >= containment[0] || left - this.offset.click.left > containment[2] ? left : left - this.offset.click.left >= containment[0] ? left - o.grid[0] : left + o.grid[0] : left;
                }
                if (o.axis === "y") {
                    pageX = this.originalPageX;
                }
                if (o.axis === "x") {
                    pageY = this.originalPageY;
                }
            }
            return {
                top: pageY - this.offset.click.top - this.offset.relative.top - this.offset.parent.top + (this.cssPosition === "fixed" ? -this.offset.scroll.top : scrollIsRootNode ? 0 : this.offset.scroll.top),
                left: pageX - this.offset.click.left - this.offset.relative.left - this.offset.parent.left + (this.cssPosition === "fixed" ? -this.offset.scroll.left : scrollIsRootNode ? 0 : this.offset.scroll.left)
            };
        },
        _clear: function() {
            this._removeClass(this.helper, "ui-draggable-dragging");
            if (this.helper[0] !== this.element[0] && !this.cancelHelperRemoval) {
                this.helper.remove();
            }
            this.helper = null;
            this.cancelHelperRemoval = false;
            if (this.destroyOnClear) {
                this.destroy();
            }
        },
        _trigger: function(type, event, ui) {
            ui = ui || this._uiHash();
            $.ui.plugin.call(this, type, [ event, ui, this ], true);
            if (/^(drag|start|stop)/.test(type)) {
                this.positionAbs = this._convertPositionTo("absolute");
                ui.offset = this.positionAbs;
            }
            return $.Widget.prototype._trigger.call(this, type, event, ui);
        },
        plugins: {},
        _uiHash: function() {
            return {
                helper: this.helper,
                position: this.position,
                originalPosition: this.originalPosition,
                offset: this.positionAbs
            };
        }
    });
    $.ui.plugin.add("draggable", "connectToSortable", {
        start: function(event, ui, draggable) {
            var uiSortable = $.extend({}, ui, {
                item: draggable.element
            });
            draggable.sortables = [];
            $(draggable.options.connectToSortable).each(function() {
                var sortable = $(this).sortable("instance");
                if (sortable && !sortable.options.disabled) {
                    draggable.sortables.push(sortable);
                    sortable.refreshPositions();
                    sortable._trigger("activate", event, uiSortable);
                }
            });
        },
        stop: function(event, ui, draggable) {
            var uiSortable = $.extend({}, ui, {
                item: draggable.element
            });
            draggable.cancelHelperRemoval = false;
            $.each(draggable.sortables, function() {
                var sortable = this;
                if (sortable.isOver) {
                    sortable.isOver = 0;
                    draggable.cancelHelperRemoval = true;
                    sortable.cancelHelperRemoval = false;
                    sortable._storedCSS = {
                        position: sortable.placeholder.css("position"),
                        top: sortable.placeholder.css("top"),
                        left: sortable.placeholder.css("left")
                    };
                    sortable._mouseStop(event);
                    sortable.options.helper = sortable.options._helper;
                } else {
                    sortable.cancelHelperRemoval = true;
                    sortable._trigger("deactivate", event, uiSortable);
                }
            });
        },
        drag: function(event, ui, draggable) {
            $.each(draggable.sortables, function() {
                var innermostIntersecting = false, sortable = this;
                sortable.positionAbs = draggable.positionAbs;
                sortable.helperProportions = draggable.helperProportions;
                sortable.offset.click = draggable.offset.click;
                if (sortable._intersectsWith(sortable.containerCache)) {
                    innermostIntersecting = true;
                    $.each(draggable.sortables, function() {
                        this.positionAbs = draggable.positionAbs;
                        this.helperProportions = draggable.helperProportions;
                        this.offset.click = draggable.offset.click;
                        if (this !== sortable && this._intersectsWith(this.containerCache) && $.contains(sortable.element[0], this.element[0])) {
                            innermostIntersecting = false;
                        }
                        return innermostIntersecting;
                    });
                }
                if (innermostIntersecting) {
                    if (!sortable.isOver) {
                        sortable.isOver = 1;
                        draggable._parent = ui.helper.parent();
                        sortable.currentItem = ui.helper.appendTo(sortable.element).data("ui-sortable-item", true);
                        sortable.options._helper = sortable.options.helper;
                        sortable.options.helper = function() {
                            return ui.helper[0];
                        };
                        event.target = sortable.currentItem[0];
                        sortable._mouseCapture(event, true);
                        sortable._mouseStart(event, true, true);
                        sortable.offset.click.top = draggable.offset.click.top;
                        sortable.offset.click.left = draggable.offset.click.left;
                        sortable.offset.parent.left -= draggable.offset.parent.left - sortable.offset.parent.left;
                        sortable.offset.parent.top -= draggable.offset.parent.top - sortable.offset.parent.top;
                        draggable._trigger("toSortable", event);
                        draggable.dropped = sortable.element;
                        $.each(draggable.sortables, function() {
                            this.refreshPositions();
                        });
                        draggable.currentItem = draggable.element;
                        sortable.fromOutside = draggable;
                    }
                    if (sortable.currentItem) {
                        sortable._mouseDrag(event);
                        ui.position = sortable.position;
                    }
                } else {
                    if (sortable.isOver) {
                        sortable.isOver = 0;
                        sortable.cancelHelperRemoval = true;
                        sortable.options._revert = sortable.options.revert;
                        sortable.options.revert = false;
                        sortable._trigger("out", event, sortable._uiHash(sortable));
                        sortable._mouseStop(event, true);
                        sortable.options.revert = sortable.options._revert;
                        sortable.options.helper = sortable.options._helper;
                        if (sortable.placeholder) {
                            sortable.placeholder.remove();
                        }
                        ui.helper.appendTo(draggable._parent);
                        draggable._refreshOffsets(event);
                        ui.position = draggable._generatePosition(event, true);
                        draggable._trigger("fromSortable", event);
                        draggable.dropped = false;
                        $.each(draggable.sortables, function() {
                            this.refreshPositions();
                        });
                    }
                }
            });
        }
    });
    $.ui.plugin.add("draggable", "cursor", {
        start: function(event, ui, instance) {
            var t = $("body"), o = instance.options;
            if (t.css("cursor")) {
                o._cursor = t.css("cursor");
            }
            t.css("cursor", o.cursor);
        },
        stop: function(event, ui, instance) {
            var o = instance.options;
            if (o._cursor) {
                $("body").css("cursor", o._cursor);
            }
        }
    });
    $.ui.plugin.add("draggable", "opacity", {
        start: function(event, ui, instance) {
            var t = $(ui.helper), o = instance.options;
            if (t.css("opacity")) {
                o._opacity = t.css("opacity");
            }
            t.css("opacity", o.opacity);
        },
        stop: function(event, ui, instance) {
            var o = instance.options;
            if (o._opacity) {
                $(ui.helper).css("opacity", o._opacity);
            }
        }
    });
    $.ui.plugin.add("draggable", "scroll", {
        start: function(event, ui, i) {
            if (!i.scrollParentNotHidden) {
                i.scrollParentNotHidden = i.helper.scrollParent(false);
            }
            if (i.scrollParentNotHidden[0] !== i.document[0] && i.scrollParentNotHidden[0].tagName !== "HTML") {
                i.overflowOffset = i.scrollParentNotHidden.offset();
            }
        },
        drag: function(event, ui, i) {
            var o = i.options, scrolled = false, scrollParent = i.scrollParentNotHidden[0], document = i.document[0];
            if (scrollParent !== document && scrollParent.tagName !== "HTML") {
                if (!o.axis || o.axis !== "x") {
                    if (i.overflowOffset.top + scrollParent.offsetHeight - event.pageY < o.scrollSensitivity) {
                        scrollParent.scrollTop = scrolled = scrollParent.scrollTop + o.scrollSpeed;
                    } else if (event.pageY - i.overflowOffset.top < o.scrollSensitivity) {
                        scrollParent.scrollTop = scrolled = scrollParent.scrollTop - o.scrollSpeed;
                    }
                }
                if (!o.axis || o.axis !== "y") {
                    if (i.overflowOffset.left + scrollParent.offsetWidth - event.pageX < o.scrollSensitivity) {
                        scrollParent.scrollLeft = scrolled = scrollParent.scrollLeft + o.scrollSpeed;
                    } else if (event.pageX - i.overflowOffset.left < o.scrollSensitivity) {
                        scrollParent.scrollLeft = scrolled = scrollParent.scrollLeft - o.scrollSpeed;
                    }
                }
            } else {
                if (!o.axis || o.axis !== "x") {
                    if (event.pageY - $(document).scrollTop() < o.scrollSensitivity) {
                        scrolled = $(document).scrollTop($(document).scrollTop() - o.scrollSpeed);
                    } else if ($(window).height() - (event.pageY - $(document).scrollTop()) < o.scrollSensitivity) {
                        scrolled = $(document).scrollTop($(document).scrollTop() + o.scrollSpeed);
                    }
                }
                if (!o.axis || o.axis !== "y") {
                    if (event.pageX - $(document).scrollLeft() < o.scrollSensitivity) {
                        scrolled = $(document).scrollLeft($(document).scrollLeft() - o.scrollSpeed);
                    } else if ($(window).width() - (event.pageX - $(document).scrollLeft()) < o.scrollSensitivity) {
                        scrolled = $(document).scrollLeft($(document).scrollLeft() + o.scrollSpeed);
                    }
                }
            }
            if (scrolled !== false && $.ui.ddmanager && !o.dropBehaviour) {
                $.ui.ddmanager.prepareOffsets(i, event);
            }
        }
    });
    $.ui.plugin.add("draggable", "snap", {
        start: function(event, ui, i) {
            var o = i.options;
            i.snapElements = [];
            $(o.snap.constructor !== String ? o.snap.items || ":data(ui-draggable)" : o.snap).each(function() {
                var $t = $(this), $o = $t.offset();
                if (this !== i.element[0]) {
                    i.snapElements.push({
                        item: this,
                        width: $t.outerWidth(),
                        height: $t.outerHeight(),
                        top: $o.top,
                        left: $o.left
                    });
                }
            });
        },
        drag: function(event, ui, inst) {
            var ts, bs, ls, rs, l, r, t, b, i, first, o = inst.options, d = o.snapTolerance, x1 = ui.offset.left, x2 = x1 + inst.helperProportions.width, y1 = ui.offset.top, y2 = y1 + inst.helperProportions.height;
            for (i = inst.snapElements.length - 1; i >= 0; i--) {
                l = inst.snapElements[i].left - inst.margins.left;
                r = l + inst.snapElements[i].width;
                t = inst.snapElements[i].top - inst.margins.top;
                b = t + inst.snapElements[i].height;
                if (x2 < l - d || x1 > r + d || y2 < t - d || y1 > b + d || !$.contains(inst.snapElements[i].item.ownerDocument, inst.snapElements[i].item)) {
                    if (inst.snapElements[i].snapping) {
                        inst.options.snap.release && inst.options.snap.release.call(inst.element, event, $.extend(inst._uiHash(), {
                            snapItem: inst.snapElements[i].item
                        }));
                    }
                    inst.snapElements[i].snapping = false;
                    continue;
                }
                if (o.snapMode !== "inner") {
                    ts = Math.abs(t - y2) <= d;
                    bs = Math.abs(b - y1) <= d;
                    ls = Math.abs(l - x2) <= d;
                    rs = Math.abs(r - x1) <= d;
                    if (ts) {
                        ui.position.top = inst._convertPositionTo("relative", {
                            top: t - inst.helperProportions.height,
                            left: 0
                        }).top;
                    }
                    if (bs) {
                        ui.position.top = inst._convertPositionTo("relative", {
                            top: b,
                            left: 0
                        }).top;
                    }
                    if (ls) {
                        ui.position.left = inst._convertPositionTo("relative", {
                            top: 0,
                            left: l - inst.helperProportions.width
                        }).left;
                    }
                    if (rs) {
                        ui.position.left = inst._convertPositionTo("relative", {
                            top: 0,
                            left: r
                        }).left;
                    }
                }
                first = ts || bs || ls || rs;
                if (o.snapMode !== "outer") {
                    ts = Math.abs(t - y1) <= d;
                    bs = Math.abs(b - y2) <= d;
                    ls = Math.abs(l - x1) <= d;
                    rs = Math.abs(r - x2) <= d;
                    if (ts) {
                        ui.position.top = inst._convertPositionTo("relative", {
                            top: t,
                            left: 0
                        }).top;
                    }
                    if (bs) {
                        ui.position.top = inst._convertPositionTo("relative", {
                            top: b - inst.helperProportions.height,
                            left: 0
                        }).top;
                    }
                    if (ls) {
                        ui.position.left = inst._convertPositionTo("relative", {
                            top: 0,
                            left: l
                        }).left;
                    }
                    if (rs) {
                        ui.position.left = inst._convertPositionTo("relative", {
                            top: 0,
                            left: r - inst.helperProportions.width
                        }).left;
                    }
                }
                if (!inst.snapElements[i].snapping && (ts || bs || ls || rs || first)) {
                    inst.options.snap.snap && inst.options.snap.snap.call(inst.element, event, $.extend(inst._uiHash(), {
                        snapItem: inst.snapElements[i].item
                    }));
                }
                inst.snapElements[i].snapping = ts || bs || ls || rs || first;
            }
        }
    });
    $.ui.plugin.add("draggable", "stack", {
        start: function(event, ui, instance) {
            var min, o = instance.options, group = $.makeArray($(o.stack)).sort(function(a, b) {
                return (parseInt($(a).css("zIndex"), 10) || 0) - (parseInt($(b).css("zIndex"), 10) || 0);
            });
            if (!group.length) {
                return;
            }
            min = parseInt($(group[0]).css("zIndex"), 10) || 0;
            $(group).each(function(i) {
                $(this).css("zIndex", min + i);
            });
            this.css("zIndex", min + group.length);
        }
    });
    $.ui.plugin.add("draggable", "zIndex", {
        start: function(event, ui, instance) {
            var t = $(ui.helper), o = instance.options;
            if (t.css("zIndex")) {
                o._zIndex = t.css("zIndex");
            }
            t.css("zIndex", o.zIndex);
        },
        stop: function(event, ui, instance) {
            var o = instance.options;
            if (o._zIndex) {
                $(ui.helper).css("zIndex", o._zIndex);
            }
        }
    });
    var widgetsDraggable = $.ui.draggable;
    $.widget("ui.resizable", $.ui.mouse, {
        version: "1.12.1",
        widgetEventPrefix: "resize",
        options: {
            alsoResize: false,
            animate: false,
            animateDuration: "slow",
            animateEasing: "swing",
            aspectRatio: false,
            autoHide: false,
            classes: {
                "ui-resizable-se": "ui-icon ui-icon-gripsmall-diagonal-se"
            },
            containment: false,
            ghost: false,
            grid: false,
            handles: "e,s,se",
            helper: false,
            maxHeight: null,
            maxWidth: null,
            minHeight: 10,
            minWidth: 10,
            zIndex: 90,
            resize: null,
            start: null,
            stop: null
        },
        _num: function(value) {
            return parseFloat(value) || 0;
        },
        _isNumber: function(value) {
            return !isNaN(parseFloat(value));
        },
        _hasScroll: function(el, a) {
            if ($(el).css("overflow") === "hidden") {
                return false;
            }
            var scroll = a && a === "left" ? "scrollLeft" : "scrollTop", has = false;
            if (el[scroll] > 0) {
                return true;
            }
            el[scroll] = 1;
            has = el[scroll] > 0;
            el[scroll] = 0;
            return has;
        },
        _create: function() {
            var margins, o = this.options, that = this;
            this._addClass("ui-resizable");
            $.extend(this, {
                _aspectRatio: !!o.aspectRatio,
                aspectRatio: o.aspectRatio,
                originalElement: this.element,
                _proportionallyResizeElements: [],
                _helper: o.helper || o.ghost || o.animate ? o.helper || "ui-resizable-helper" : null
            });
            if (this.element[0].nodeName.match(/^(canvas|textarea|input|select|button|img)$/i)) {
                this.element.wrap($("<div class='ui-wrapper' style='overflow: hidden;'></div>").css({
                    position: this.element.css("position"),
                    width: this.element.outerWidth(),
                    height: this.element.outerHeight(),
                    top: this.element.css("top"),
                    left: this.element.css("left")
                }));
                this.element = this.element.parent().data("ui-resizable", this.element.resizable("instance"));
                this.elementIsWrapper = true;
                margins = {
                    marginTop: this.originalElement.css("marginTop"),
                    marginRight: this.originalElement.css("marginRight"),
                    marginBottom: this.originalElement.css("marginBottom"),
                    marginLeft: this.originalElement.css("marginLeft")
                };
                this.element.css(margins);
                this.originalElement.css("margin", 0);
                this.originalResizeStyle = this.originalElement.css("resize");
                this.originalElement.css("resize", "none");
                this._proportionallyResizeElements.push(this.originalElement.css({
                    position: "static",
                    zoom: 1,
                    display: "block"
                }));
                this.originalElement.css(margins);
                this._proportionallyResize();
            }
            this._setupHandles();
            if (o.autoHide) {
                $(this.element).on("mouseenter", function() {
                    if (o.disabled) {
                        return;
                    }
                    that._removeClass("ui-resizable-autohide");
                    that._handles.show();
                }).on("mouseleave", function() {
                    if (o.disabled) {
                        return;
                    }
                    if (!that.resizing) {
                        that._addClass("ui-resizable-autohide");
                        that._handles.hide();
                    }
                });
            }
            this._mouseInit();
        },
        _destroy: function() {
            this._mouseDestroy();
            var wrapper, _destroy = function(exp) {
                $(exp).removeData("resizable").removeData("ui-resizable").off(".resizable").find(".ui-resizable-handle").remove();
            };
            if (this.elementIsWrapper) {
                _destroy(this.element);
                wrapper = this.element;
                this.originalElement.css({
                    position: wrapper.css("position"),
                    width: wrapper.outerWidth(),
                    height: wrapper.outerHeight(),
                    top: wrapper.css("top"),
                    left: wrapper.css("left")
                }).insertAfter(wrapper);
                wrapper.remove();
            }
            this.originalElement.css("resize", this.originalResizeStyle);
            _destroy(this.originalElement);
            return this;
        },
        _setOption: function(key, value) {
            this._super(key, value);
            switch (key) {
              case "handles":
                this._removeHandles();
                this._setupHandles();
                break;

              default:
                break;
            }
        },
        _setupHandles: function() {
            var o = this.options, handle, i, n, hname, axis, that = this;
            this.handles = o.handles || (!$(".ui-resizable-handle", this.element).length ? "e,s,se" : {
                n: ".ui-resizable-n",
                e: ".ui-resizable-e",
                s: ".ui-resizable-s",
                w: ".ui-resizable-w",
                se: ".ui-resizable-se",
                sw: ".ui-resizable-sw",
                ne: ".ui-resizable-ne",
                nw: ".ui-resizable-nw"
            });
            this._handles = $();
            if (this.handles.constructor === String) {
                if (this.handles === "all") {
                    this.handles = "n,e,s,w,se,sw,ne,nw";
                }
                n = this.handles.split(",");
                this.handles = {};
                for (i = 0; i < n.length; i++) {
                    handle = $.trim(n[i]);
                    hname = "ui-resizable-" + handle;
                    axis = $("<div>");
                    this._addClass(axis, "ui-resizable-handle " + hname);
                    axis.css({
                        zIndex: o.zIndex
                    });
                    this.handles[handle] = ".ui-resizable-" + handle;
                    this.element.append(axis);
                }
            }
            this._renderAxis = function(target) {
                var i, axis, padPos, padWrapper;
                target = target || this.element;
                for (i in this.handles) {
                    if (this.handles[i].constructor === String) {
                        this.handles[i] = this.element.children(this.handles[i]).first().show();
                    } else if (this.handles[i].jquery || this.handles[i].nodeType) {
                        this.handles[i] = $(this.handles[i]);
                        this._on(this.handles[i], {
                            mousedown: that._mouseDown
                        });
                    }
                    if (this.elementIsWrapper && this.originalElement[0].nodeName.match(/^(textarea|input|select|button)$/i)) {
                        axis = $(this.handles[i], this.element);
                        padWrapper = /sw|ne|nw|se|n|s/.test(i) ? axis.outerHeight() : axis.outerWidth();
                        padPos = [ "padding", /ne|nw|n/.test(i) ? "Top" : /se|sw|s/.test(i) ? "Bottom" : /^e$/.test(i) ? "Right" : "Left" ].join("");
                        target.css(padPos, padWrapper);
                        this._proportionallyResize();
                    }
                    this._handles = this._handles.add(this.handles[i]);
                }
            };
            this._renderAxis(this.element);
            this._handles = this._handles.add(this.element.find(".ui-resizable-handle"));
            this._handles.disableSelection();
            this._handles.on("mouseover", function() {
                if (!that.resizing) {
                    if (this.className) {
                        axis = this.className.match(/ui-resizable-(se|sw|ne|nw|n|e|s|w)/i);
                    }
                    that.axis = axis && axis[1] ? axis[1] : "se";
                }
            });
            if (o.autoHide) {
                this._handles.hide();
                this._addClass("ui-resizable-autohide");
            }
        },
        _removeHandles: function() {
            this._handles.remove();
        },
        _mouseCapture: function(event) {
            var i, handle, capture = false;
            for (i in this.handles) {
                handle = $(this.handles[i])[0];
                if (handle === event.target || $.contains(handle, event.target)) {
                    capture = true;
                }
            }
            return !this.options.disabled && capture;
        },
        _mouseStart: function(event) {
            var curleft, curtop, cursor, o = this.options, el = this.element;
            this.resizing = true;
            this._renderProxy();
            curleft = this._num(this.helper.css("left"));
            curtop = this._num(this.helper.css("top"));
            if (o.containment) {
                curleft += $(o.containment).scrollLeft() || 0;
                curtop += $(o.containment).scrollTop() || 0;
            }
            this.offset = this.helper.offset();
            this.position = {
                left: curleft,
                top: curtop
            };
            this.size = this._helper ? {
                width: this.helper.width(),
                height: this.helper.height()
            } : {
                width: el.width(),
                height: el.height()
            };
            this.originalSize = this._helper ? {
                width: el.outerWidth(),
                height: el.outerHeight()
            } : {
                width: el.width(),
                height: el.height()
            };
            this.sizeDiff = {
                width: el.outerWidth() - el.width(),
                height: el.outerHeight() - el.height()
            };
            this.originalPosition = {
                left: curleft,
                top: curtop
            };
            this.originalMousePosition = {
                left: event.pageX,
                top: event.pageY
            };
            this.aspectRatio = typeof o.aspectRatio === "number" ? o.aspectRatio : this.originalSize.width / this.originalSize.height || 1;
            cursor = $(".ui-resizable-" + this.axis).css("cursor");
            $("body").css("cursor", cursor === "auto" ? this.axis + "-resize" : cursor);
            this._addClass("ui-resizable-resizing");
            this._propagate("start", event);
            return true;
        },
        _mouseDrag: function(event) {
            var data, props, smp = this.originalMousePosition, a = this.axis, dx = event.pageX - smp.left || 0, dy = event.pageY - smp.top || 0, trigger = this._change[a];
            this._updatePrevProperties();
            if (!trigger) {
                return false;
            }
            data = trigger.apply(this, [ event, dx, dy ]);
            this._updateVirtualBoundaries(event.shiftKey);
            if (this._aspectRatio || event.shiftKey) {
                data = this._updateRatio(data, event);
            }
            data = this._respectSize(data, event);
            this._updateCache(data);
            this._propagate("resize", event);
            props = this._applyChanges();
            if (!this._helper && this._proportionallyResizeElements.length) {
                this._proportionallyResize();
            }
            if (!$.isEmptyObject(props)) {
                this._updatePrevProperties();
                this._trigger("resize", event, this.ui());
                this._applyChanges();
            }
            return false;
        },
        _mouseStop: function(event) {
            this.resizing = false;
            var pr, ista, soffseth, soffsetw, s, left, top, o = this.options, that = this;
            if (this._helper) {
                pr = this._proportionallyResizeElements;
                ista = pr.length && /textarea/i.test(pr[0].nodeName);
                soffseth = ista && this._hasScroll(pr[0], "left") ? 0 : that.sizeDiff.height;
                soffsetw = ista ? 0 : that.sizeDiff.width;
                s = {
                    width: that.helper.width() - soffsetw,
                    height: that.helper.height() - soffseth
                };
                left = parseFloat(that.element.css("left")) + (that.position.left - that.originalPosition.left) || null;
                top = parseFloat(that.element.css("top")) + (that.position.top - that.originalPosition.top) || null;
                if (!o.animate) {
                    this.element.css($.extend(s, {
                        top: top,
                        left: left
                    }));
                }
                that.helper.height(that.size.height);
                that.helper.width(that.size.width);
                if (this._helper && !o.animate) {
                    this._proportionallyResize();
                }
            }
            $("body").css("cursor", "auto");
            this._removeClass("ui-resizable-resizing");
            this._propagate("stop", event);
            if (this._helper) {
                this.helper.remove();
            }
            return false;
        },
        _updatePrevProperties: function() {
            this.prevPosition = {
                top: this.position.top,
                left: this.position.left
            };
            this.prevSize = {
                width: this.size.width,
                height: this.size.height
            };
        },
        _applyChanges: function() {
            var props = {};
            if (this.position.top !== this.prevPosition.top) {
                props.top = this.position.top + "px";
            }
            if (this.position.left !== this.prevPosition.left) {
                props.left = this.position.left + "px";
            }
            if (this.size.width !== this.prevSize.width) {
                props.width = this.size.width + "px";
            }
            if (this.size.height !== this.prevSize.height) {
                props.height = this.size.height + "px";
            }
            this.helper.css(props);
            return props;
        },
        _updateVirtualBoundaries: function(forceAspectRatio) {
            var pMinWidth, pMaxWidth, pMinHeight, pMaxHeight, b, o = this.options;
            b = {
                minWidth: this._isNumber(o.minWidth) ? o.minWidth : 0,
                maxWidth: this._isNumber(o.maxWidth) ? o.maxWidth : Infinity,
                minHeight: this._isNumber(o.minHeight) ? o.minHeight : 0,
                maxHeight: this._isNumber(o.maxHeight) ? o.maxHeight : Infinity
            };
            if (this._aspectRatio || forceAspectRatio) {
                pMinWidth = b.minHeight * this.aspectRatio;
                pMinHeight = b.minWidth / this.aspectRatio;
                pMaxWidth = b.maxHeight * this.aspectRatio;
                pMaxHeight = b.maxWidth / this.aspectRatio;
                if (pMinWidth > b.minWidth) {
                    b.minWidth = pMinWidth;
                }
                if (pMinHeight > b.minHeight) {
                    b.minHeight = pMinHeight;
                }
                if (pMaxWidth < b.maxWidth) {
                    b.maxWidth = pMaxWidth;
                }
                if (pMaxHeight < b.maxHeight) {
                    b.maxHeight = pMaxHeight;
                }
            }
            this._vBoundaries = b;
        },
        _updateCache: function(data) {
            this.offset = this.helper.offset();
            if (this._isNumber(data.left)) {
                this.position.left = data.left;
            }
            if (this._isNumber(data.top)) {
                this.position.top = data.top;
            }
            if (this._isNumber(data.height)) {
                this.size.height = data.height;
            }
            if (this._isNumber(data.width)) {
                this.size.width = data.width;
            }
        },
        _updateRatio: function(data) {
            var cpos = this.position, csize = this.size, a = this.axis;
            if (this._isNumber(data.height)) {
                data.width = data.height * this.aspectRatio;
            } else if (this._isNumber(data.width)) {
                data.height = data.width / this.aspectRatio;
            }
            if (a === "sw") {
                data.left = cpos.left + (csize.width - data.width);
                data.top = null;
            }
            if (a === "nw") {
                data.top = cpos.top + (csize.height - data.height);
                data.left = cpos.left + (csize.width - data.width);
            }
            return data;
        },
        _respectSize: function(data) {
            var o = this._vBoundaries, a = this.axis, ismaxw = this._isNumber(data.width) && o.maxWidth && o.maxWidth < data.width, ismaxh = this._isNumber(data.height) && o.maxHeight && o.maxHeight < data.height, isminw = this._isNumber(data.width) && o.minWidth && o.minWidth > data.width, isminh = this._isNumber(data.height) && o.minHeight && o.minHeight > data.height, dw = this.originalPosition.left + this.originalSize.width, dh = this.originalPosition.top + this.originalSize.height, cw = /sw|nw|w/.test(a), ch = /nw|ne|n/.test(a);
            if (isminw) {
                data.width = o.minWidth;
            }
            if (isminh) {
                data.height = o.minHeight;
            }
            if (ismaxw) {
                data.width = o.maxWidth;
            }
            if (ismaxh) {
                data.height = o.maxHeight;
            }
            if (isminw && cw) {
                data.left = dw - o.minWidth;
            }
            if (ismaxw && cw) {
                data.left = dw - o.maxWidth;
            }
            if (isminh && ch) {
                data.top = dh - o.minHeight;
            }
            if (ismaxh && ch) {
                data.top = dh - o.maxHeight;
            }
            if (!data.width && !data.height && !data.left && data.top) {
                data.top = null;
            } else if (!data.width && !data.height && !data.top && data.left) {
                data.left = null;
            }
            return data;
        },
        _getPaddingPlusBorderDimensions: function(element) {
            var i = 0, widths = [], borders = [ element.css("borderTopWidth"), element.css("borderRightWidth"), element.css("borderBottomWidth"), element.css("borderLeftWidth") ], paddings = [ element.css("paddingTop"), element.css("paddingRight"), element.css("paddingBottom"), element.css("paddingLeft") ];
            for (;i < 4; i++) {
                widths[i] = parseFloat(borders[i]) || 0;
                widths[i] += parseFloat(paddings[i]) || 0;
            }
            return {
                height: widths[0] + widths[2],
                width: widths[1] + widths[3]
            };
        },
        _proportionallyResize: function() {
            if (!this._proportionallyResizeElements.length) {
                return;
            }
            var prel, i = 0, element = this.helper || this.element;
            for (;i < this._proportionallyResizeElements.length; i++) {
                prel = this._proportionallyResizeElements[i];
                if (!this.outerDimensions) {
                    this.outerDimensions = this._getPaddingPlusBorderDimensions(prel);
                }
                prel.css({
                    height: element.height() - this.outerDimensions.height || 0,
                    width: element.width() - this.outerDimensions.width || 0
                });
            }
        },
        _renderProxy: function() {
            var el = this.element, o = this.options;
            this.elementOffset = el.offset();
            if (this._helper) {
                this.helper = this.helper || $("<div style='overflow:hidden;'></div>");
                this._addClass(this.helper, this._helper);
                this.helper.css({
                    width: this.element.outerWidth(),
                    height: this.element.outerHeight(),
                    position: "absolute",
                    left: this.elementOffset.left + "px",
                    top: this.elementOffset.top + "px",
                    zIndex: ++o.zIndex
                });
                this.helper.appendTo("body").disableSelection();
            } else {
                this.helper = this.element;
            }
        },
        _change: {
            e: function(event, dx) {
                return {
                    width: this.originalSize.width + dx
                };
            },
            w: function(event, dx) {
                var cs = this.originalSize, sp = this.originalPosition;
                return {
                    left: sp.left + dx,
                    width: cs.width - dx
                };
            },
            n: function(event, dx, dy) {
                var cs = this.originalSize, sp = this.originalPosition;
                return {
                    top: sp.top + dy,
                    height: cs.height - dy
                };
            },
            s: function(event, dx, dy) {
                return {
                    height: this.originalSize.height + dy
                };
            },
            se: function(event, dx, dy) {
                return $.extend(this._change.s.apply(this, arguments), this._change.e.apply(this, [ event, dx, dy ]));
            },
            sw: function(event, dx, dy) {
                return $.extend(this._change.s.apply(this, arguments), this._change.w.apply(this, [ event, dx, dy ]));
            },
            ne: function(event, dx, dy) {
                return $.extend(this._change.n.apply(this, arguments), this._change.e.apply(this, [ event, dx, dy ]));
            },
            nw: function(event, dx, dy) {
                return $.extend(this._change.n.apply(this, arguments), this._change.w.apply(this, [ event, dx, dy ]));
            }
        },
        _propagate: function(n, event) {
            $.ui.plugin.call(this, n, [ event, this.ui() ]);
            n !== "resize" && this._trigger(n, event, this.ui());
        },
        plugins: {},
        ui: function() {
            return {
                originalElement: this.originalElement,
                element: this.element,
                helper: this.helper,
                position: this.position,
                size: this.size,
                originalSize: this.originalSize,
                originalPosition: this.originalPosition
            };
        }
    });
    $.ui.plugin.add("resizable", "animate", {
        stop: function(event) {
            var that = $(this).resizable("instance"), o = that.options, pr = that._proportionallyResizeElements, ista = pr.length && /textarea/i.test(pr[0].nodeName), soffseth = ista && that._hasScroll(pr[0], "left") ? 0 : that.sizeDiff.height, soffsetw = ista ? 0 : that.sizeDiff.width, style = {
                width: that.size.width - soffsetw,
                height: that.size.height - soffseth
            }, left = parseFloat(that.element.css("left")) + (that.position.left - that.originalPosition.left) || null, top = parseFloat(that.element.css("top")) + (that.position.top - that.originalPosition.top) || null;
            that.element.animate($.extend(style, top && left ? {
                top: top,
                left: left
            } : {}), {
                duration: o.animateDuration,
                easing: o.animateEasing,
                step: function() {
                    var data = {
                        width: parseFloat(that.element.css("width")),
                        height: parseFloat(that.element.css("height")),
                        top: parseFloat(that.element.css("top")),
                        left: parseFloat(that.element.css("left"))
                    };
                    if (pr && pr.length) {
                        $(pr[0]).css({
                            width: data.width,
                            height: data.height
                        });
                    }
                    that._updateCache(data);
                    that._propagate("resize", event);
                }
            });
        }
    });
    $.ui.plugin.add("resizable", "containment", {
        start: function() {
            var element, p, co, ch, cw, width, height, that = $(this).resizable("instance"), o = that.options, el = that.element, oc = o.containment, ce = oc instanceof $ ? oc.get(0) : /parent/.test(oc) ? el.parent().get(0) : oc;
            if (!ce) {
                return;
            }
            that.containerElement = $(ce);
            if (/document/.test(oc) || oc === document) {
                that.containerOffset = {
                    left: 0,
                    top: 0
                };
                that.containerPosition = {
                    left: 0,
                    top: 0
                };
                that.parentData = {
                    element: $(document),
                    left: 0,
                    top: 0,
                    width: $(document).width(),
                    height: $(document).height() || document.body.parentNode.scrollHeight
                };
            } else {
                element = $(ce);
                p = [];
                $([ "Top", "Right", "Left", "Bottom" ]).each(function(i, name) {
                    p[i] = that._num(element.css("padding" + name));
                });
                that.containerOffset = element.offset();
                that.containerPosition = element.position();
                that.containerSize = {
                    height: element.innerHeight() - p[3],
                    width: element.innerWidth() - p[1]
                };
                co = that.containerOffset;
                ch = that.containerSize.height;
                cw = that.containerSize.width;
                width = that._hasScroll(ce, "left") ? ce.scrollWidth : cw;
                height = that._hasScroll(ce) ? ce.scrollHeight : ch;
                that.parentData = {
                    element: ce,
                    left: co.left,
                    top: co.top,
                    width: width,
                    height: height
                };
            }
        },
        resize: function(event) {
            var woset, hoset, isParent, isOffsetRelative, that = $(this).resizable("instance"), o = that.options, co = that.containerOffset, cp = that.position, pRatio = that._aspectRatio || event.shiftKey, cop = {
                top: 0,
                left: 0
            }, ce = that.containerElement, continueResize = true;
            if (ce[0] !== document && /static/.test(ce.css("position"))) {
                cop = co;
            }
            if (cp.left < (that._helper ? co.left : 0)) {
                that.size.width = that.size.width + (that._helper ? that.position.left - co.left : that.position.left - cop.left);
                if (pRatio) {
                    that.size.height = that.size.width / that.aspectRatio;
                    continueResize = false;
                }
                that.position.left = o.helper ? co.left : 0;
            }
            if (cp.top < (that._helper ? co.top : 0)) {
                that.size.height = that.size.height + (that._helper ? that.position.top - co.top : that.position.top);
                if (pRatio) {
                    that.size.width = that.size.height * that.aspectRatio;
                    continueResize = false;
                }
                that.position.top = that._helper ? co.top : 0;
            }
            isParent = that.containerElement.get(0) === that.element.parent().get(0);
            isOffsetRelative = /relative|absolute/.test(that.containerElement.css("position"));
            if (isParent && isOffsetRelative) {
                that.offset.left = that.parentData.left + that.position.left;
                that.offset.top = that.parentData.top + that.position.top;
            } else {
                that.offset.left = that.element.offset().left;
                that.offset.top = that.element.offset().top;
            }
            woset = Math.abs(that.sizeDiff.width + (that._helper ? that.offset.left - cop.left : that.offset.left - co.left));
            hoset = Math.abs(that.sizeDiff.height + (that._helper ? that.offset.top - cop.top : that.offset.top - co.top));
            if (woset + that.size.width >= that.parentData.width) {
                that.size.width = that.parentData.width - woset;
                if (pRatio) {
                    that.size.height = that.size.width / that.aspectRatio;
                    continueResize = false;
                }
            }
            if (hoset + that.size.height >= that.parentData.height) {
                that.size.height = that.parentData.height - hoset;
                if (pRatio) {
                    that.size.width = that.size.height * that.aspectRatio;
                    continueResize = false;
                }
            }
            if (!continueResize) {
                that.position.left = that.prevPosition.left;
                that.position.top = that.prevPosition.top;
                that.size.width = that.prevSize.width;
                that.size.height = that.prevSize.height;
            }
        },
        stop: function() {
            var that = $(this).resizable("instance"), o = that.options, co = that.containerOffset, cop = that.containerPosition, ce = that.containerElement, helper = $(that.helper), ho = helper.offset(), w = helper.outerWidth() - that.sizeDiff.width, h = helper.outerHeight() - that.sizeDiff.height;
            if (that._helper && !o.animate && /relative/.test(ce.css("position"))) {
                $(this).css({
                    left: ho.left - cop.left - co.left,
                    width: w,
                    height: h
                });
            }
            if (that._helper && !o.animate && /static/.test(ce.css("position"))) {
                $(this).css({
                    left: ho.left - cop.left - co.left,
                    width: w,
                    height: h
                });
            }
        }
    });
    $.ui.plugin.add("resizable", "alsoResize", {
        start: function() {
            var that = $(this).resizable("instance"), o = that.options;
            $(o.alsoResize).each(function() {
                var el = $(this);
                el.data("ui-resizable-alsoresize", {
                    width: parseFloat(el.width()),
                    height: parseFloat(el.height()),
                    left: parseFloat(el.css("left")),
                    top: parseFloat(el.css("top"))
                });
            });
        },
        resize: function(event, ui) {
            var that = $(this).resizable("instance"), o = that.options, os = that.originalSize, op = that.originalPosition, delta = {
                height: that.size.height - os.height || 0,
                width: that.size.width - os.width || 0,
                top: that.position.top - op.top || 0,
                left: that.position.left - op.left || 0
            };
            $(o.alsoResize).each(function() {
                var el = $(this), start = $(this).data("ui-resizable-alsoresize"), style = {}, css = el.parents(ui.originalElement[0]).length ? [ "width", "height" ] : [ "width", "height", "top", "left" ];
                $.each(css, function(i, prop) {
                    var sum = (start[prop] || 0) + (delta[prop] || 0);
                    if (sum && sum >= 0) {
                        style[prop] = sum || null;
                    }
                });
                el.css(style);
            });
        },
        stop: function() {
            $(this).removeData("ui-resizable-alsoresize");
        }
    });
    $.ui.plugin.add("resizable", "ghost", {
        start: function() {
            var that = $(this).resizable("instance"), cs = that.size;
            that.ghost = that.originalElement.clone();
            that.ghost.css({
                opacity: .25,
                display: "block",
                position: "relative",
                height: cs.height,
                width: cs.width,
                margin: 0,
                left: 0,
                top: 0
            });
            that._addClass(that.ghost, "ui-resizable-ghost");
            if ($.uiBackCompat !== false && typeof that.options.ghost === "string") {
                that.ghost.addClass(this.options.ghost);
            }
            that.ghost.appendTo(that.helper);
        },
        resize: function() {
            var that = $(this).resizable("instance");
            if (that.ghost) {
                that.ghost.css({
                    position: "relative",
                    height: that.size.height,
                    width: that.size.width
                });
            }
        },
        stop: function() {
            var that = $(this).resizable("instance");
            if (that.ghost && that.helper) {
                that.helper.get(0).removeChild(that.ghost.get(0));
            }
        }
    });
    $.ui.plugin.add("resizable", "grid", {
        resize: function() {
            var outerDimensions, that = $(this).resizable("instance"), o = that.options, cs = that.size, os = that.originalSize, op = that.originalPosition, a = that.axis, grid = typeof o.grid === "number" ? [ o.grid, o.grid ] : o.grid, gridX = grid[0] || 1, gridY = grid[1] || 1, ox = Math.round((cs.width - os.width) / gridX) * gridX, oy = Math.round((cs.height - os.height) / gridY) * gridY, newWidth = os.width + ox, newHeight = os.height + oy, isMaxWidth = o.maxWidth && o.maxWidth < newWidth, isMaxHeight = o.maxHeight && o.maxHeight < newHeight, isMinWidth = o.minWidth && o.minWidth > newWidth, isMinHeight = o.minHeight && o.minHeight > newHeight;
            o.grid = grid;
            if (isMinWidth) {
                newWidth += gridX;
            }
            if (isMinHeight) {
                newHeight += gridY;
            }
            if (isMaxWidth) {
                newWidth -= gridX;
            }
            if (isMaxHeight) {
                newHeight -= gridY;
            }
            if (/^(se|s|e)$/.test(a)) {
                that.size.width = newWidth;
                that.size.height = newHeight;
            } else if (/^(ne)$/.test(a)) {
                that.size.width = newWidth;
                that.size.height = newHeight;
                that.position.top = op.top - oy;
            } else if (/^(sw)$/.test(a)) {
                that.size.width = newWidth;
                that.size.height = newHeight;
                that.position.left = op.left - ox;
            } else {
                if (newHeight - gridY <= 0 || newWidth - gridX <= 0) {
                    outerDimensions = that._getPaddingPlusBorderDimensions(this);
                }
                if (newHeight - gridY > 0) {
                    that.size.height = newHeight;
                    that.position.top = op.top - oy;
                } else {
                    newHeight = gridY - outerDimensions.height;
                    that.size.height = newHeight;
                    that.position.top = op.top + os.height - newHeight;
                }
                if (newWidth - gridX > 0) {
                    that.size.width = newWidth;
                    that.position.left = op.left - ox;
                } else {
                    newWidth = gridX - outerDimensions.width;
                    that.size.width = newWidth;
                    that.position.left = op.left + os.width - newWidth;
                }
            }
        }
    });
    var widgetsResizable = $.ui.resizable;
    $.widget("ui.dialog", {
        version: "1.12.1",
        options: {
            appendTo: "body",
            autoOpen: true,
            buttons: [],
            classes: {
                "ui-dialog": "ui-corner-all",
                "ui-dialog-titlebar": "ui-corner-all"
            },
            closeOnEscape: true,
            closeText: "Close",
            draggable: true,
            hide: null,
            height: "auto",
            maxHeight: null,
            maxWidth: null,
            minHeight: 150,
            minWidth: 150,
            modal: false,
            position: {
                my: "center",
                at: "center",
                of: window,
                collision: "fit",
                using: function(pos) {
                    var topOffset = $(this).css(pos).offset().top;
                    if (topOffset < 0) {
                        $(this).css("top", pos.top - topOffset);
                    }
                }
            },
            resizable: true,
            show: null,
            title: null,
            width: 300,
            beforeClose: null,
            close: null,
            drag: null,
            dragStart: null,
            dragStop: null,
            focus: null,
            open: null,
            resize: null,
            resizeStart: null,
            resizeStop: null
        },
        sizeRelatedOptions: {
            buttons: true,
            height: true,
            maxHeight: true,
            maxWidth: true,
            minHeight: true,
            minWidth: true,
            width: true
        },
        resizableRelatedOptions: {
            maxHeight: true,
            maxWidth: true,
            minHeight: true,
            minWidth: true
        },
        _create: function() {
            this.originalCss = {
                display: this.element[0].style.display,
                width: this.element[0].style.width,
                minHeight: this.element[0].style.minHeight,
                maxHeight: this.element[0].style.maxHeight,
                height: this.element[0].style.height
            };
            this.originalPosition = {
                parent: this.element.parent(),
                index: this.element.parent().children().index(this.element)
            };
            this.originalTitle = this.element.attr("title");
            if (this.options.title == null && this.originalTitle != null) {
                this.options.title = this.originalTitle;
            }
            if (this.options.disabled) {
                this.options.disabled = false;
            }
            this._createWrapper();
            this.element.show().removeAttr("title").appendTo(this.uiDialog);
            this._addClass("ui-dialog-content", "ui-widget-content");
            this._createTitlebar();
            this._createButtonPane();
            if (this.options.draggable && $.fn.draggable) {
                this._makeDraggable();
            }
            if (this.options.resizable && $.fn.resizable) {
                this._makeResizable();
            }
            this._isOpen = false;
            this._trackFocus();
        },
        _init: function() {
            if (this.options.autoOpen) {
                this.open();
            }
        },
        _appendTo: function() {
            var element = this.options.appendTo;
            if (element && (element.jquery || element.nodeType)) {
                return $(element);
            }
            return this.document.find(element || "body").eq(0);
        },
        _destroy: function() {
            var next, originalPosition = this.originalPosition;
            this._untrackInstance();
            this._destroyOverlay();
            this.element.removeUniqueId().css(this.originalCss).detach();
            this.uiDialog.remove();
            if (this.originalTitle) {
                this.element.attr("title", this.originalTitle);
            }
            next = originalPosition.parent.children().eq(originalPosition.index);
            if (next.length && next[0] !== this.element[0]) {
                next.before(this.element);
            } else {
                originalPosition.parent.append(this.element);
            }
        },
        widget: function() {
            return this.uiDialog;
        },
        disable: $.noop,
        enable: $.noop,
        close: function(event) {
            var that = this;
            if (!this._isOpen || this._trigger("beforeClose", event) === false) {
                return;
            }
            this._isOpen = false;
            this._focusedElement = null;
            this._destroyOverlay();
            this._untrackInstance();
            if (!this.opener.filter(":focusable").trigger("focus").length) {
                $.ui.safeBlur($.ui.safeActiveElement(this.document[0]));
            }
            this._hide(this.uiDialog, this.options.hide, function() {
                that._trigger("close", event);
            });
        },
        isOpen: function() {
            return this._isOpen;
        },
        moveToTop: function() {
            this._moveToTop();
        },
        _moveToTop: function(event, silent) {
            var moved = false, zIndices = this.uiDialog.siblings(".ui-front:visible").map(function() {
                return +$(this).css("z-index");
            }).get(), zIndexMax = Math.max.apply(null, zIndices);
            if (zIndexMax >= +this.uiDialog.css("z-index")) {
                this.uiDialog.css("z-index", zIndexMax + 1);
                moved = true;
            }
            if (moved && !silent) {
                this._trigger("focus", event);
            }
            return moved;
        },
        open: function() {
            var that = this;
            if (this._isOpen) {
                if (this._moveToTop()) {
                    this._focusTabbable();
                }
                return;
            }
            this._isOpen = true;
            this.opener = $($.ui.safeActiveElement(this.document[0]));
            this._size();
            this._position();
            this._createOverlay();
            this._moveToTop(null, true);
            if (this.overlay) {
                this.overlay.css("z-index", this.uiDialog.css("z-index") - 1);
            }
            this._show(this.uiDialog, this.options.show, function() {
                that._focusTabbable();
                that._trigger("focus");
            });
            this._makeFocusTarget();
            this._trigger("open");
        },
        _focusTabbable: function() {
            var hasFocus = this._focusedElement;
            if (!hasFocus) {
                hasFocus = this.element.find("[autofocus]");
            }
            if (!hasFocus.length) {
                hasFocus = this.element.find(":tabbable");
            }
            if (!hasFocus.length) {
                hasFocus = this.uiDialogButtonPane.find(":tabbable");
            }
            if (!hasFocus.length) {
                hasFocus = this.uiDialogTitlebarClose.filter(":tabbable");
            }
            if (!hasFocus.length) {
                hasFocus = this.uiDialog;
            }
            hasFocus.eq(0).trigger("focus");
        },
        _keepFocus: function(event) {
            function checkFocus() {
                var activeElement = $.ui.safeActiveElement(this.document[0]), isActive = this.uiDialog[0] === activeElement || $.contains(this.uiDialog[0], activeElement);
                if (!isActive) {
                    this._focusTabbable();
                }
            }
            event.preventDefault();
            checkFocus.call(this);
            this._delay(checkFocus);
        },
        _createWrapper: function() {
            this.uiDialog = $("<div>").hide().attr({
                tabIndex: -1,
                role: "dialog"
            }).appendTo(this._appendTo());
            this._addClass(this.uiDialog, "ui-dialog", "ui-widget ui-widget-content ui-front");
            this._on(this.uiDialog, {
                keydown: function(event) {
                    if (this.options.closeOnEscape && !event.isDefaultPrevented() && event.keyCode && event.keyCode === $.ui.keyCode.ESCAPE) {
                        event.preventDefault();
                        this.close(event);
                        return;
                    }
                    if (event.keyCode !== $.ui.keyCode.TAB || event.isDefaultPrevented()) {
                        return;
                    }
                    var tabbables = this.uiDialog.find(":tabbable"), first = tabbables.filter(":first"), last = tabbables.filter(":last");
                    if ((event.target === last[0] || event.target === this.uiDialog[0]) && !event.shiftKey) {
                        this._delay(function() {
                            first.trigger("focus");
                        });
                        event.preventDefault();
                    } else if ((event.target === first[0] || event.target === this.uiDialog[0]) && event.shiftKey) {
                        this._delay(function() {
                            last.trigger("focus");
                        });
                        event.preventDefault();
                    }
                },
                mousedown: function(event) {
                    if (this._moveToTop(event)) {
                        this._focusTabbable();
                    }
                }
            });
            if (!this.element.find("[aria-describedby]").length) {
                this.uiDialog.attr({
                    "aria-describedby": this.element.uniqueId().attr("id")
                });
            }
        },
        _createTitlebar: function() {
            var uiDialogTitle;
            this.uiDialogTitlebar = $("<div>");
            this._addClass(this.uiDialogTitlebar, "ui-dialog-titlebar", "ui-widget-header ui-helper-clearfix");
            this._on(this.uiDialogTitlebar, {
                mousedown: function(event) {
                    if (!$(event.target).closest(".ui-dialog-titlebar-close")) {
                        this.uiDialog.trigger("focus");
                    }
                }
            });
            this.uiDialogTitlebarClose = $("<button type='button'></button>").button({
                label: $("<a>").text(this.options.closeText).html(),
                icon: "ui-icon-closethick",
                showLabel: false
            }).appendTo(this.uiDialogTitlebar);
            this._addClass(this.uiDialogTitlebarClose, "ui-dialog-titlebar-close");
            this._on(this.uiDialogTitlebarClose, {
                click: function(event) {
                    event.preventDefault();
                    this.close(event);
                }
            });
            uiDialogTitle = $("<span>").uniqueId().prependTo(this.uiDialogTitlebar);
            this._addClass(uiDialogTitle, "ui-dialog-title");
            this._title(uiDialogTitle);
            this.uiDialogTitlebar.prependTo(this.uiDialog);
            this.uiDialog.attr({
                "aria-labelledby": uiDialogTitle.attr("id")
            });
        },
        _title: function(title) {
            if (this.options.title) {
                title.text(this.options.title);
            } else {
                title.html("&#160;");
            }
        },
        _createButtonPane: function() {
            this.uiDialogButtonPane = $("<div>");
            this._addClass(this.uiDialogButtonPane, "ui-dialog-buttonpane", "ui-widget-content ui-helper-clearfix");
            this.uiButtonSet = $("<div>").appendTo(this.uiDialogButtonPane);
            this._addClass(this.uiButtonSet, "ui-dialog-buttonset");
            this._createButtons();
        },
        _createButtons: function() {
            var that = this, buttons = this.options.buttons;
            this.uiDialogButtonPane.remove();
            this.uiButtonSet.empty();
            if ($.isEmptyObject(buttons) || $.isArray(buttons) && !buttons.length) {
                this._removeClass(this.uiDialog, "ui-dialog-buttons");
                return;
            }
            $.each(buttons, function(name, props) {
                var click, buttonOptions;
                props = $.isFunction(props) ? {
                    click: props,
                    text: name
                } : props;
                props = $.extend({
                    type: "button"
                }, props);
                click = props.click;
                buttonOptions = {
                    icon: props.icon,
                    iconPosition: props.iconPosition,
                    showLabel: props.showLabel,
                    icons: props.icons,
                    text: props.text
                };
                delete props.click;
                delete props.icon;
                delete props.iconPosition;
                delete props.showLabel;
                delete props.icons;
                if (typeof props.text === "boolean") {
                    delete props.text;
                }
                $("<button></button>", props).button(buttonOptions).appendTo(that.uiButtonSet).on("click", function() {
                    click.apply(that.element[0], arguments);
                });
            });
            this._addClass(this.uiDialog, "ui-dialog-buttons");
            this.uiDialogButtonPane.appendTo(this.uiDialog);
        },
        _makeDraggable: function() {
            var that = this, options = this.options;
            function filteredUi(ui) {
                return {
                    position: ui.position,
                    offset: ui.offset
                };
            }
            this.uiDialog.draggable({
                cancel: ".ui-dialog-content, .ui-dialog-titlebar-close",
                handle: ".ui-dialog-titlebar",
                containment: "document",
                start: function(event, ui) {
                    that._addClass($(this), "ui-dialog-dragging");
                    that._blockFrames();
                    that._trigger("dragStart", event, filteredUi(ui));
                },
                drag: function(event, ui) {
                    that._trigger("drag", event, filteredUi(ui));
                },
                stop: function(event, ui) {
                    var left = ui.offset.left - that.document.scrollLeft(), top = ui.offset.top - that.document.scrollTop();
                    options.position = {
                        my: "left top",
                        at: "left" + (left >= 0 ? "+" : "") + left + " " + "top" + (top >= 0 ? "+" : "") + top,
                        of: that.window
                    };
                    that._removeClass($(this), "ui-dialog-dragging");
                    that._unblockFrames();
                    that._trigger("dragStop", event, filteredUi(ui));
                }
            });
        },
        _makeResizable: function() {
            var that = this, options = this.options, handles = options.resizable, position = this.uiDialog.css("position"), resizeHandles = typeof handles === "string" ? handles : "n,e,s,w,se,sw,ne,nw";
            function filteredUi(ui) {
                return {
                    originalPosition: ui.originalPosition,
                    originalSize: ui.originalSize,
                    position: ui.position,
                    size: ui.size
                };
            }
            this.uiDialog.resizable({
                cancel: ".ui-dialog-content",
                containment: "document",
                alsoResize: this.element,
                maxWidth: options.maxWidth,
                maxHeight: options.maxHeight,
                minWidth: options.minWidth,
                minHeight: this._minHeight(),
                handles: resizeHandles,
                start: function(event, ui) {
                    that._addClass($(this), "ui-dialog-resizing");
                    that._blockFrames();
                    that._trigger("resizeStart", event, filteredUi(ui));
                },
                resize: function(event, ui) {
                    that._trigger("resize", event, filteredUi(ui));
                },
                stop: function(event, ui) {
                    var offset = that.uiDialog.offset(), left = offset.left - that.document.scrollLeft(), top = offset.top - that.document.scrollTop();
                    options.height = that.uiDialog.height();
                    options.width = that.uiDialog.width();
                    options.position = {
                        my: "left top",
                        at: "left" + (left >= 0 ? "+" : "") + left + " " + "top" + (top >= 0 ? "+" : "") + top,
                        of: that.window
                    };
                    that._removeClass($(this), "ui-dialog-resizing");
                    that._unblockFrames();
                    that._trigger("resizeStop", event, filteredUi(ui));
                }
            }).css("position", position);
        },
        _trackFocus: function() {
            this._on(this.widget(), {
                focusin: function(event) {
                    this._makeFocusTarget();
                    this._focusedElement = $(event.target);
                }
            });
        },
        _makeFocusTarget: function() {
            this._untrackInstance();
            this._trackingInstances().unshift(this);
        },
        _untrackInstance: function() {
            var instances = this._trackingInstances(), exists = $.inArray(this, instances);
            if (exists !== -1) {
                instances.splice(exists, 1);
            }
        },
        _trackingInstances: function() {
            var instances = this.document.data("ui-dialog-instances");
            if (!instances) {
                instances = [];
                this.document.data("ui-dialog-instances", instances);
            }
            return instances;
        },
        _minHeight: function() {
            var options = this.options;
            return options.height === "auto" ? options.minHeight : Math.min(options.minHeight, options.height);
        },
        _position: function() {
            var isVisible = this.uiDialog.is(":visible");
            if (!isVisible) {
                this.uiDialog.show();
            }
            this.uiDialog.position(this.options.position);
            if (!isVisible) {
                this.uiDialog.hide();
            }
        },
        _setOptions: function(options) {
            var that = this, resize = false, resizableOptions = {};
            $.each(options, function(key, value) {
                that._setOption(key, value);
                if (key in that.sizeRelatedOptions) {
                    resize = true;
                }
                if (key in that.resizableRelatedOptions) {
                    resizableOptions[key] = value;
                }
            });
            if (resize) {
                this._size();
                this._position();
            }
            if (this.uiDialog.is(":data(ui-resizable)")) {
                this.uiDialog.resizable("option", resizableOptions);
            }
        },
        _setOption: function(key, value) {
            var isDraggable, isResizable, uiDialog = this.uiDialog;
            if (key === "disabled") {
                return;
            }
            this._super(key, value);
            if (key === "appendTo") {
                this.uiDialog.appendTo(this._appendTo());
            }
            if (key === "buttons") {
                this._createButtons();
            }
            if (key === "closeText") {
                this.uiDialogTitlebarClose.button({
                    label: $("<a>").text("" + this.options.closeText).html()
                });
            }
            if (key === "draggable") {
                isDraggable = uiDialog.is(":data(ui-draggable)");
                if (isDraggable && !value) {
                    uiDialog.draggable("destroy");
                }
                if (!isDraggable && value) {
                    this._makeDraggable();
                }
            }
            if (key === "position") {
                this._position();
            }
            if (key === "resizable") {
                isResizable = uiDialog.is(":data(ui-resizable)");
                if (isResizable && !value) {
                    uiDialog.resizable("destroy");
                }
                if (isResizable && typeof value === "string") {
                    uiDialog.resizable("option", "handles", value);
                }
                if (!isResizable && value !== false) {
                    this._makeResizable();
                }
            }
            if (key === "title") {
                this._title(this.uiDialogTitlebar.find(".ui-dialog-title"));
            }
        },
        _size: function() {
            var nonContentHeight, minContentHeight, maxContentHeight, options = this.options;
            this.element.show().css({
                width: "auto",
                minHeight: 0,
                maxHeight: "none",
                height: 0
            });
            if (options.minWidth > options.width) {
                options.width = options.minWidth;
            }
            nonContentHeight = this.uiDialog.css({
                height: "auto",
                width: options.width
            }).outerHeight();
            minContentHeight = Math.max(0, options.minHeight - nonContentHeight);
            maxContentHeight = typeof options.maxHeight === "number" ? Math.max(0, options.maxHeight - nonContentHeight) : "none";
            if (options.height === "auto") {
                this.element.css({
                    minHeight: minContentHeight,
                    maxHeight: maxContentHeight,
                    height: "auto"
                });
            } else {
                this.element.height(Math.max(0, options.height - nonContentHeight));
            }
            if (this.uiDialog.is(":data(ui-resizable)")) {
                this.uiDialog.resizable("option", "minHeight", this._minHeight());
            }
        },
        _blockFrames: function() {
            this.iframeBlocks = this.document.find("iframe").map(function() {
                var iframe = $(this);
                return $("<div>").css({
                    position: "absolute",
                    width: iframe.outerWidth(),
                    height: iframe.outerHeight()
                }).appendTo(iframe.parent()).offset(iframe.offset())[0];
            });
        },
        _unblockFrames: function() {
            if (this.iframeBlocks) {
                this.iframeBlocks.remove();
                delete this.iframeBlocks;
            }
        },
        _allowInteraction: function(event) {
            if ($(event.target).closest(".ui-dialog").length) {
                return true;
            }
            return !!$(event.target).closest(".ui-datepicker").length;
        },
        _createOverlay: function() {
            if (!this.options.modal) {
                return;
            }
            var isOpening = true;
            this._delay(function() {
                isOpening = false;
            });
            if (!this.document.data("ui-dialog-overlays")) {
                this._on(this.document, {
                    focusin: function(event) {
                        if (isOpening) {
                            return;
                        }
                        if (!this._allowInteraction(event)) {
                            event.preventDefault();
                            this._trackingInstances()[0]._focusTabbable();
                        }
                    }
                });
            }
            this.overlay = $("<div>").appendTo(this._appendTo());
            this._addClass(this.overlay, null, "ui-widget-overlay ui-front");
            this._on(this.overlay, {
                mousedown: "_keepFocus"
            });
            this.document.data("ui-dialog-overlays", (this.document.data("ui-dialog-overlays") || 0) + 1);
        },
        _destroyOverlay: function() {
            if (!this.options.modal) {
                return;
            }
            if (this.overlay) {
                var overlays = this.document.data("ui-dialog-overlays") - 1;
                if (!overlays) {
                    this._off(this.document, "focusin");
                    this.document.removeData("ui-dialog-overlays");
                } else {
                    this.document.data("ui-dialog-overlays", overlays);
                }
                this.overlay.remove();
                this.overlay = null;
            }
        }
    });
    if ($.uiBackCompat !== false) {
        $.widget("ui.dialog", $.ui.dialog, {
            options: {
                dialogClass: ""
            },
            _createWrapper: function() {
                this._super();
                this.uiDialog.addClass(this.options.dialogClass);
            },
            _setOption: function(key, value) {
                if (key === "dialogClass") {
                    this.uiDialog.removeClass(this.options.dialogClass).addClass(value);
                }
                this._superApply(arguments);
            }
        });
    }
    var widgetsDialog = $.ui.dialog;
    $.widget("ui.droppable", {
        version: "1.12.1",
        widgetEventPrefix: "drop",
        options: {
            accept: "*",
            addClasses: true,
            greedy: false,
            scope: "default",
            tolerance: "intersect",
            activate: null,
            deactivate: null,
            drop: null,
            out: null,
            over: null
        },
        _create: function() {
            var proportions, o = this.options, accept = o.accept;
            this.isover = false;
            this.isout = true;
            this.accept = $.isFunction(accept) ? accept : function(d) {
                return d.is(accept);
            };
            this.proportions = function() {
                if (arguments.length) {
                    proportions = arguments[0];
                } else {
                    return proportions ? proportions : proportions = {
                        width: this.element[0].offsetWidth,
                        height: this.element[0].offsetHeight
                    };
                }
            };
            this._addToManager(o.scope);
            o.addClasses && this._addClass("ui-droppable");
        },
        _addToManager: function(scope) {
            $.ui.ddmanager.droppables[scope] = $.ui.ddmanager.droppables[scope] || [];
            $.ui.ddmanager.droppables[scope].push(this);
        },
        _splice: function(drop) {
            var i = 0;
            for (;i < drop.length; i++) {
                if (drop[i] === this) {
                    drop.splice(i, 1);
                }
            }
        },
        _destroy: function() {
            var drop = $.ui.ddmanager.droppables[this.options.scope];
            this._splice(drop);
        },
        _setOption: function(key, value) {
            if (key === "accept") {
                this.accept = $.isFunction(value) ? value : function(d) {
                    return d.is(value);
                };
            } else if (key === "scope") {
                var drop = $.ui.ddmanager.droppables[this.options.scope];
                this._splice(drop);
                this._addToManager(value);
            }
            this._super(key, value);
        },
        _activate: function(event) {
            var draggable = $.ui.ddmanager.current;
            this._addActiveClass();
            if (draggable) {
                this._trigger("activate", event, this.ui(draggable));
            }
        },
        _deactivate: function(event) {
            var draggable = $.ui.ddmanager.current;
            this._removeActiveClass();
            if (draggable) {
                this._trigger("deactivate", event, this.ui(draggable));
            }
        },
        _over: function(event) {
            var draggable = $.ui.ddmanager.current;
            if (!draggable || (draggable.currentItem || draggable.element)[0] === this.element[0]) {
                return;
            }
            if (this.accept.call(this.element[0], draggable.currentItem || draggable.element)) {
                this._addHoverClass();
                this._trigger("over", event, this.ui(draggable));
            }
        },
        _out: function(event) {
            var draggable = $.ui.ddmanager.current;
            if (!draggable || (draggable.currentItem || draggable.element)[0] === this.element[0]) {
                return;
            }
            if (this.accept.call(this.element[0], draggable.currentItem || draggable.element)) {
                this._removeHoverClass();
                this._trigger("out", event, this.ui(draggable));
            }
        },
        _drop: function(event, custom) {
            var draggable = custom || $.ui.ddmanager.current, childrenIntersection = false;
            if (!draggable || (draggable.currentItem || draggable.element)[0] === this.element[0]) {
                return false;
            }
            this.element.find(":data(ui-droppable)").not(".ui-draggable-dragging").each(function() {
                var inst = $(this).droppable("instance");
                if (inst.options.greedy && !inst.options.disabled && inst.options.scope === draggable.options.scope && inst.accept.call(inst.element[0], draggable.currentItem || draggable.element) && intersect(draggable, $.extend(inst, {
                    offset: inst.element.offset()
                }), inst.options.tolerance, event)) {
                    childrenIntersection = true;
                    return false;
                }
            });
            if (childrenIntersection) {
                return false;
            }
            if (this.accept.call(this.element[0], draggable.currentItem || draggable.element)) {
                this._removeActiveClass();
                this._removeHoverClass();
                this._trigger("drop", event, this.ui(draggable));
                return this.element;
            }
            return false;
        },
        ui: function(c) {
            return {
                draggable: c.currentItem || c.element,
                helper: c.helper,
                position: c.position,
                offset: c.positionAbs
            };
        },
        _addHoverClass: function() {
            this._addClass("ui-droppable-hover");
        },
        _removeHoverClass: function() {
            this._removeClass("ui-droppable-hover");
        },
        _addActiveClass: function() {
            this._addClass("ui-droppable-active");
        },
        _removeActiveClass: function() {
            this._removeClass("ui-droppable-active");
        }
    });
    var intersect = $.ui.intersect = function() {
        function isOverAxis(x, reference, size) {
            return x >= reference && x < reference + size;
        }
        return function(draggable, droppable, toleranceMode, event) {
            if (!droppable.offset) {
                return false;
            }
            var x1 = (draggable.positionAbs || draggable.position.absolute).left + draggable.margins.left, y1 = (draggable.positionAbs || draggable.position.absolute).top + draggable.margins.top, x2 = x1 + draggable.helperProportions.width, y2 = y1 + draggable.helperProportions.height, l = droppable.offset.left, t = droppable.offset.top, r = l + droppable.proportions().width, b = t + droppable.proportions().height;
            switch (toleranceMode) {
              case "fit":
                return l <= x1 && x2 <= r && t <= y1 && y2 <= b;

              case "intersect":
                return l < x1 + draggable.helperProportions.width / 2 && x2 - draggable.helperProportions.width / 2 < r && t < y1 + draggable.helperProportions.height / 2 && y2 - draggable.helperProportions.height / 2 < b;

              case "pointer":
                return isOverAxis(event.pageY, t, droppable.proportions().height) && isOverAxis(event.pageX, l, droppable.proportions().width);

              case "touch":
                return (y1 >= t && y1 <= b || y2 >= t && y2 <= b || y1 < t && y2 > b) && (x1 >= l && x1 <= r || x2 >= l && x2 <= r || x1 < l && x2 > r);

              default:
                return false;
            }
        };
    }();
    $.ui.ddmanager = {
        current: null,
        droppables: {
            default: []
        },
        prepareOffsets: function(t, event) {
            var i, j, m = $.ui.ddmanager.droppables[t.options.scope] || [], type = event ? event.type : null, list = (t.currentItem || t.element).find(":data(ui-droppable)").addBack();
            droppablesLoop: for (i = 0; i < m.length; i++) {
                if (m[i].options.disabled || t && !m[i].accept.call(m[i].element[0], t.currentItem || t.element)) {
                    continue;
                }
                for (j = 0; j < list.length; j++) {
                    if (list[j] === m[i].element[0]) {
                        m[i].proportions().height = 0;
                        continue droppablesLoop;
                    }
                }
                m[i].visible = m[i].element.css("display") !== "none";
                if (!m[i].visible) {
                    continue;
                }
                if (type === "mousedown") {
                    m[i]._activate.call(m[i], event);
                }
                m[i].offset = m[i].element.offset();
                m[i].proportions({
                    width: m[i].element[0].offsetWidth,
                    height: m[i].element[0].offsetHeight
                });
            }
        },
        drop: function(draggable, event) {
            var dropped = false;
            $.each(($.ui.ddmanager.droppables[draggable.options.scope] || []).slice(), function() {
                if (!this.options) {
                    return;
                }
                if (!this.options.disabled && this.visible && intersect(draggable, this, this.options.tolerance, event)) {
                    dropped = this._drop.call(this, event) || dropped;
                }
                if (!this.options.disabled && this.visible && this.accept.call(this.element[0], draggable.currentItem || draggable.element)) {
                    this.isout = true;
                    this.isover = false;
                    this._deactivate.call(this, event);
                }
            });
            return dropped;
        },
        dragStart: function(draggable, event) {
            draggable.element.parentsUntil("body").on("scroll.droppable", function() {
                if (!draggable.options.refreshPositions) {
                    $.ui.ddmanager.prepareOffsets(draggable, event);
                }
            });
        },
        drag: function(draggable, event) {
            if (draggable.options.refreshPositions) {
                $.ui.ddmanager.prepareOffsets(draggable, event);
            }
            $.each($.ui.ddmanager.droppables[draggable.options.scope] || [], function() {
                if (this.options.disabled || this.greedyChild || !this.visible) {
                    return;
                }
                var parentInstance, scope, parent, intersects = intersect(draggable, this, this.options.tolerance, event), c = !intersects && this.isover ? "isout" : intersects && !this.isover ? "isover" : null;
                if (!c) {
                    return;
                }
                if (this.options.greedy) {
                    scope = this.options.scope;
                    parent = this.element.parents(":data(ui-droppable)").filter(function() {
                        return $(this).droppable("instance").options.scope === scope;
                    });
                    if (parent.length) {
                        parentInstance = $(parent[0]).droppable("instance");
                        parentInstance.greedyChild = c === "isover";
                    }
                }
                if (parentInstance && c === "isover") {
                    parentInstance.isover = false;
                    parentInstance.isout = true;
                    parentInstance._out.call(parentInstance, event);
                }
                this[c] = true;
                this[c === "isout" ? "isover" : "isout"] = false;
                this[c === "isover" ? "_over" : "_out"].call(this, event);
                if (parentInstance && c === "isout") {
                    parentInstance.isout = false;
                    parentInstance.isover = true;
                    parentInstance._over.call(parentInstance, event);
                }
            });
        },
        dragStop: function(draggable, event) {
            draggable.element.parentsUntil("body").off("scroll.droppable");
            if (!draggable.options.refreshPositions) {
                $.ui.ddmanager.prepareOffsets(draggable, event);
            }
        }
    };
    if ($.uiBackCompat !== false) {
        $.widget("ui.droppable", $.ui.droppable, {
            options: {
                hoverClass: false,
                activeClass: false
            },
            _addActiveClass: function() {
                this._super();
                if (this.options.activeClass) {
                    this.element.addClass(this.options.activeClass);
                }
            },
            _removeActiveClass: function() {
                this._super();
                if (this.options.activeClass) {
                    this.element.removeClass(this.options.activeClass);
                }
            },
            _addHoverClass: function() {
                this._super();
                if (this.options.hoverClass) {
                    this.element.addClass(this.options.hoverClass);
                }
            },
            _removeHoverClass: function() {
                this._super();
                if (this.options.hoverClass) {
                    this.element.removeClass(this.options.hoverClass);
                }
            }
        });
    }
    var widgetsDroppable = $.ui.droppable;
    var widgetsProgressbar = $.widget("ui.progressbar", {
        version: "1.12.1",
        options: {
            classes: {
                "ui-progressbar": "ui-corner-all",
                "ui-progressbar-value": "ui-corner-left",
                "ui-progressbar-complete": "ui-corner-right"
            },
            max: 100,
            value: 0,
            change: null,
            complete: null
        },
        min: 0,
        _create: function() {
            this.oldValue = this.options.value = this._constrainedValue();
            this.element.attr({
                role: "progressbar",
                "aria-valuemin": this.min
            });
            this._addClass("ui-progressbar", "ui-widget ui-widget-content");
            this.valueDiv = $("<div>").appendTo(this.element);
            this._addClass(this.valueDiv, "ui-progressbar-value", "ui-widget-header");
            this._refreshValue();
        },
        _destroy: function() {
            this.element.removeAttr("role aria-valuemin aria-valuemax aria-valuenow");
            this.valueDiv.remove();
        },
        value: function(newValue) {
            if (newValue === undefined) {
                return this.options.value;
            }
            this.options.value = this._constrainedValue(newValue);
            this._refreshValue();
        },
        _constrainedValue: function(newValue) {
            if (newValue === undefined) {
                newValue = this.options.value;
            }
            this.indeterminate = newValue === false;
            if (typeof newValue !== "number") {
                newValue = 0;
            }
            return this.indeterminate ? false : Math.min(this.options.max, Math.max(this.min, newValue));
        },
        _setOptions: function(options) {
            var value = options.value;
            delete options.value;
            this._super(options);
            this.options.value = this._constrainedValue(value);
            this._refreshValue();
        },
        _setOption: function(key, value) {
            if (key === "max") {
                value = Math.max(this.min, value);
            }
            this._super(key, value);
        },
        _setOptionDisabled: function(value) {
            this._super(value);
            this.element.attr("aria-disabled", value);
            this._toggleClass(null, "ui-state-disabled", !!value);
        },
        _percentage: function() {
            return this.indeterminate ? 100 : 100 * (this.options.value - this.min) / (this.options.max - this.min);
        },
        _refreshValue: function() {
            var value = this.options.value, percentage = this._percentage();
            this.valueDiv.toggle(this.indeterminate || value > this.min).width(percentage.toFixed(0) + "%");
            this._toggleClass(this.valueDiv, "ui-progressbar-complete", null, value === this.options.max)._toggleClass("ui-progressbar-indeterminate", null, this.indeterminate);
            if (this.indeterminate) {
                this.element.removeAttr("aria-valuenow");
                if (!this.overlayDiv) {
                    this.overlayDiv = $("<div>").appendTo(this.valueDiv);
                    this._addClass(this.overlayDiv, "ui-progressbar-overlay");
                }
            } else {
                this.element.attr({
                    "aria-valuemax": this.options.max,
                    "aria-valuenow": value
                });
                if (this.overlayDiv) {
                    this.overlayDiv.remove();
                    this.overlayDiv = null;
                }
            }
            if (this.oldValue !== value) {
                this.oldValue = value;
                this._trigger("change");
            }
            if (value === this.options.max) {
                this._trigger("complete");
            }
        }
    });
    var widgetsSelectable = $.widget("ui.selectable", $.ui.mouse, {
        version: "1.12.1",
        options: {
            appendTo: "body",
            autoRefresh: true,
            distance: 0,
            filter: "*",
            tolerance: "touch",
            selected: null,
            selecting: null,
            start: null,
            stop: null,
            unselected: null,
            unselecting: null
        },
        _create: function() {
            var that = this;
            this._addClass("ui-selectable");
            this.dragged = false;
            this.refresh = function() {
                that.elementPos = $(that.element[0]).offset();
                that.selectees = $(that.options.filter, that.element[0]);
                that._addClass(that.selectees, "ui-selectee");
                that.selectees.each(function() {
                    var $this = $(this), selecteeOffset = $this.offset(), pos = {
                        left: selecteeOffset.left - that.elementPos.left,
                        top: selecteeOffset.top - that.elementPos.top
                    };
                    $.data(this, "selectable-item", {
                        element: this,
                        $element: $this,
                        left: pos.left,
                        top: pos.top,
                        right: pos.left + $this.outerWidth(),
                        bottom: pos.top + $this.outerHeight(),
                        startselected: false,
                        selected: $this.hasClass("ui-selected"),
                        selecting: $this.hasClass("ui-selecting"),
                        unselecting: $this.hasClass("ui-unselecting")
                    });
                });
            };
            this.refresh();
            this._mouseInit();
            this.helper = $("<div>");
            this._addClass(this.helper, "ui-selectable-helper");
        },
        _destroy: function() {
            this.selectees.removeData("selectable-item");
            this._mouseDestroy();
        },
        _mouseStart: function(event) {
            var that = this, options = this.options;
            this.opos = [ event.pageX, event.pageY ];
            this.elementPos = $(this.element[0]).offset();
            if (this.options.disabled) {
                return;
            }
            this.selectees = $(options.filter, this.element[0]);
            this._trigger("start", event);
            $(options.appendTo).append(this.helper);
            this.helper.css({
                left: event.pageX,
                top: event.pageY,
                width: 0,
                height: 0
            });
            if (options.autoRefresh) {
                this.refresh();
            }
            this.selectees.filter(".ui-selected").each(function() {
                var selectee = $.data(this, "selectable-item");
                selectee.startselected = true;
                if (!event.metaKey && !event.ctrlKey) {
                    that._removeClass(selectee.$element, "ui-selected");
                    selectee.selected = false;
                    that._addClass(selectee.$element, "ui-unselecting");
                    selectee.unselecting = true;
                    that._trigger("unselecting", event, {
                        unselecting: selectee.element
                    });
                }
            });
            $(event.target).parents().addBack().each(function() {
                var doSelect, selectee = $.data(this, "selectable-item");
                if (selectee) {
                    doSelect = !event.metaKey && !event.ctrlKey || !selectee.$element.hasClass("ui-selected");
                    that._removeClass(selectee.$element, doSelect ? "ui-unselecting" : "ui-selected")._addClass(selectee.$element, doSelect ? "ui-selecting" : "ui-unselecting");
                    selectee.unselecting = !doSelect;
                    selectee.selecting = doSelect;
                    selectee.selected = doSelect;
                    if (doSelect) {
                        that._trigger("selecting", event, {
                            selecting: selectee.element
                        });
                    } else {
                        that._trigger("unselecting", event, {
                            unselecting: selectee.element
                        });
                    }
                    return false;
                }
            });
        },
        _mouseDrag: function(event) {
            this.dragged = true;
            if (this.options.disabled) {
                return;
            }
            var tmp, that = this, options = this.options, x1 = this.opos[0], y1 = this.opos[1], x2 = event.pageX, y2 = event.pageY;
            if (x1 > x2) {
                tmp = x2;
                x2 = x1;
                x1 = tmp;
            }
            if (y1 > y2) {
                tmp = y2;
                y2 = y1;
                y1 = tmp;
            }
            this.helper.css({
                left: x1,
                top: y1,
                width: x2 - x1,
                height: y2 - y1
            });
            this.selectees.each(function() {
                var selectee = $.data(this, "selectable-item"), hit = false, offset = {};
                if (!selectee || selectee.element === that.element[0]) {
                    return;
                }
                offset.left = selectee.left + that.elementPos.left;
                offset.right = selectee.right + that.elementPos.left;
                offset.top = selectee.top + that.elementPos.top;
                offset.bottom = selectee.bottom + that.elementPos.top;
                if (options.tolerance === "touch") {
                    hit = !(offset.left > x2 || offset.right < x1 || offset.top > y2 || offset.bottom < y1);
                } else if (options.tolerance === "fit") {
                    hit = offset.left > x1 && offset.right < x2 && offset.top > y1 && offset.bottom < y2;
                }
                if (hit) {
                    if (selectee.selected) {
                        that._removeClass(selectee.$element, "ui-selected");
                        selectee.selected = false;
                    }
                    if (selectee.unselecting) {
                        that._removeClass(selectee.$element, "ui-unselecting");
                        selectee.unselecting = false;
                    }
                    if (!selectee.selecting) {
                        that._addClass(selectee.$element, "ui-selecting");
                        selectee.selecting = true;
                        that._trigger("selecting", event, {
                            selecting: selectee.element
                        });
                    }
                } else {
                    if (selectee.selecting) {
                        if ((event.metaKey || event.ctrlKey) && selectee.startselected) {
                            that._removeClass(selectee.$element, "ui-selecting");
                            selectee.selecting = false;
                            that._addClass(selectee.$element, "ui-selected");
                            selectee.selected = true;
                        } else {
                            that._removeClass(selectee.$element, "ui-selecting");
                            selectee.selecting = false;
                            if (selectee.startselected) {
                                that._addClass(selectee.$element, "ui-unselecting");
                                selectee.unselecting = true;
                            }
                            that._trigger("unselecting", event, {
                                unselecting: selectee.element
                            });
                        }
                    }
                    if (selectee.selected) {
                        if (!event.metaKey && !event.ctrlKey && !selectee.startselected) {
                            that._removeClass(selectee.$element, "ui-selected");
                            selectee.selected = false;
                            that._addClass(selectee.$element, "ui-unselecting");
                            selectee.unselecting = true;
                            that._trigger("unselecting", event, {
                                unselecting: selectee.element
                            });
                        }
                    }
                }
            });
            return false;
        },
        _mouseStop: function(event) {
            var that = this;
            this.dragged = false;
            $(".ui-unselecting", this.element[0]).each(function() {
                var selectee = $.data(this, "selectable-item");
                that._removeClass(selectee.$element, "ui-unselecting");
                selectee.unselecting = false;
                selectee.startselected = false;
                that._trigger("unselected", event, {
                    unselected: selectee.element
                });
            });
            $(".ui-selecting", this.element[0]).each(function() {
                var selectee = $.data(this, "selectable-item");
                that._removeClass(selectee.$element, "ui-selecting")._addClass(selectee.$element, "ui-selected");
                selectee.selecting = false;
                selectee.selected = true;
                selectee.startselected = true;
                that._trigger("selected", event, {
                    selected: selectee.element
                });
            });
            this._trigger("stop", event);
            this.helper.remove();
            return false;
        }
    });
    var widgetsSelectmenu = $.widget("ui.selectmenu", [ $.ui.formResetMixin, {
        version: "1.12.1",
        defaultElement: "<select>",
        options: {
            appendTo: null,
            classes: {
                "ui-selectmenu-button-open": "ui-corner-top",
                "ui-selectmenu-button-closed": "ui-corner-all"
            },
            disabled: null,
            icons: {
                button: "ui-icon-triangle-1-s"
            },
            position: {
                my: "left top",
                at: "left bottom",
                collision: "none"
            },
            width: false,
            change: null,
            close: null,
            focus: null,
            open: null,
            select: null
        },
        _create: function() {
            var selectmenuId = this.element.uniqueId().attr("id");
            this.ids = {
                element: selectmenuId,
                button: selectmenuId + "-button",
                menu: selectmenuId + "-menu"
            };
            this._drawButton();
            this._drawMenu();
            this._bindFormResetHandler();
            this._rendered = false;
            this.menuItems = $();
        },
        _drawButton: function() {
            var icon, that = this, item = this._parseOption(this.element.find("option:selected"), this.element[0].selectedIndex);
            this.labels = this.element.labels().attr("for", this.ids.button);
            this._on(this.labels, {
                click: function(event) {
                    this.button.focus();
                    event.preventDefault();
                }
            });
            this.element.hide();
            this.button = $("<span>", {
                tabindex: this.options.disabled ? -1 : 0,
                id: this.ids.button,
                role: "combobox",
                "aria-expanded": "false",
                "aria-autocomplete": "list",
                "aria-owns": this.ids.menu,
                "aria-haspopup": "true",
                title: this.element.attr("title")
            }).insertAfter(this.element);
            this._addClass(this.button, "ui-selectmenu-button ui-selectmenu-button-closed", "ui-button ui-widget");
            icon = $("<span>").appendTo(this.button);
            this._addClass(icon, "ui-selectmenu-icon", "ui-icon " + this.options.icons.button);
            this.buttonItem = this._renderButtonItem(item).appendTo(this.button);
            if (this.options.width !== false) {
                this._resizeButton();
            }
            this._on(this.button, this._buttonEvents);
            this.button.one("focusin", function() {
                if (!that._rendered) {
                    that._refreshMenu();
                }
            });
        },
        _drawMenu: function() {
            var that = this;
            this.menu = $("<ul>", {
                "aria-hidden": "true",
                "aria-labelledby": this.ids.button,
                id: this.ids.menu
            });
            this.menuWrap = $("<div>").append(this.menu);
            this._addClass(this.menuWrap, "ui-selectmenu-menu", "ui-front");
            this.menuWrap.appendTo(this._appendTo());
            this.menuInstance = this.menu.menu({
                classes: {
                    "ui-menu": "ui-corner-bottom"
                },
                role: "listbox",
                select: function(event, ui) {
                    event.preventDefault();
                    that._setSelection();
                    that._select(ui.item.data("ui-selectmenu-item"), event);
                },
                focus: function(event, ui) {
                    var item = ui.item.data("ui-selectmenu-item");
                    if (that.focusIndex != null && item.index !== that.focusIndex) {
                        that._trigger("focus", event, {
                            item: item
                        });
                        if (!that.isOpen) {
                            that._select(item, event);
                        }
                    }
                    that.focusIndex = item.index;
                    that.button.attr("aria-activedescendant", that.menuItems.eq(item.index).attr("id"));
                }
            }).menu("instance");
            this.menuInstance._off(this.menu, "mouseleave");
            this.menuInstance._closeOnDocumentClick = function() {
                return false;
            };
            this.menuInstance._isDivider = function() {
                return false;
            };
        },
        refresh: function() {
            this._refreshMenu();
            this.buttonItem.replaceWith(this.buttonItem = this._renderButtonItem(this._getSelectedItem().data("ui-selectmenu-item") || {}));
            if (this.options.width === null) {
                this._resizeButton();
            }
        },
        _refreshMenu: function() {
            var item, options = this.element.find("option");
            this.menu.empty();
            this._parseOptions(options);
            this._renderMenu(this.menu, this.items);
            this.menuInstance.refresh();
            this.menuItems = this.menu.find("li").not(".ui-selectmenu-optgroup").find(".ui-menu-item-wrapper");
            this._rendered = true;
            if (!options.length) {
                return;
            }
            item = this._getSelectedItem();
            this.menuInstance.focus(null, item);
            this._setAria(item.data("ui-selectmenu-item"));
            this._setOption("disabled", this.element.prop("disabled"));
        },
        open: function(event) {
            if (this.options.disabled) {
                return;
            }
            if (!this._rendered) {
                this._refreshMenu();
            } else {
                this._removeClass(this.menu.find(".ui-state-active"), null, "ui-state-active");
                this.menuInstance.focus(null, this._getSelectedItem());
            }
            if (!this.menuItems.length) {
                return;
            }
            this.isOpen = true;
            this._toggleAttr();
            this._resizeMenu();
            this._position();
            this._on(this.document, this._documentClick);
            this._trigger("open", event);
        },
        _position: function() {
            this.menuWrap.position($.extend({
                of: this.button
            }, this.options.position));
        },
        close: function(event) {
            if (!this.isOpen) {
                return;
            }
            this.isOpen = false;
            this._toggleAttr();
            this.range = null;
            this._off(this.document);
            this._trigger("close", event);
        },
        widget: function() {
            return this.button;
        },
        menuWidget: function() {
            return this.menu;
        },
        _renderButtonItem: function(item) {
            var buttonItem = $("<span>");
            this._setText(buttonItem, item.label);
            this._addClass(buttonItem, "ui-selectmenu-text");
            return buttonItem;
        },
        _renderMenu: function(ul, items) {
            var that = this, currentOptgroup = "";
            $.each(items, function(index, item) {
                var li;
                if (item.optgroup !== currentOptgroup) {
                    li = $("<li>", {
                        text: item.optgroup
                    });
                    that._addClass(li, "ui-selectmenu-optgroup", "ui-menu-divider" + (item.element.parent("optgroup").prop("disabled") ? " ui-state-disabled" : ""));
                    li.appendTo(ul);
                    currentOptgroup = item.optgroup;
                }
                that._renderItemData(ul, item);
            });
        },
        _renderItemData: function(ul, item) {
            return this._renderItem(ul, item).data("ui-selectmenu-item", item);
        },
        _renderItem: function(ul, item) {
            var li = $("<li>"), wrapper = $("<div>", {
                title: item.element.attr("title")
            });
            if (item.disabled) {
                this._addClass(li, null, "ui-state-disabled");
            }
            this._setText(wrapper, item.label);
            return li.append(wrapper).appendTo(ul);
        },
        _setText: function(element, value) {
            if (value) {
                element.text(value);
            } else {
                element.html("&#160;");
            }
        },
        _move: function(direction, event) {
            var item, next, filter = ".ui-menu-item";
            if (this.isOpen) {
                item = this.menuItems.eq(this.focusIndex).parent("li");
            } else {
                item = this.menuItems.eq(this.element[0].selectedIndex).parent("li");
                filter += ":not(.ui-state-disabled)";
            }
            if (direction === "first" || direction === "last") {
                next = item[direction === "first" ? "prevAll" : "nextAll"](filter).eq(-1);
            } else {
                next = item[direction + "All"](filter).eq(0);
            }
            if (next.length) {
                this.menuInstance.focus(event, next);
            }
        },
        _getSelectedItem: function() {
            return this.menuItems.eq(this.element[0].selectedIndex).parent("li");
        },
        _toggle: function(event) {
            this[this.isOpen ? "close" : "open"](event);
        },
        _setSelection: function() {
            var selection;
            if (!this.range) {
                return;
            }
            if (window.getSelection) {
                selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(this.range);
            } else {
                this.range.select();
            }
            this.button.focus();
        },
        _documentClick: {
            mousedown: function(event) {
                if (!this.isOpen) {
                    return;
                }
                if (!$(event.target).closest(".ui-selectmenu-menu, #" + $.ui.escapeSelector(this.ids.button)).length) {
                    this.close(event);
                }
            }
        },
        _buttonEvents: {
            mousedown: function() {
                var selection;
                if (window.getSelection) {
                    selection = window.getSelection();
                    if (selection.rangeCount) {
                        this.range = selection.getRangeAt(0);
                    }
                } else {
                    this.range = document.selection.createRange();
                }
            },
            click: function(event) {
                this._setSelection();
                this._toggle(event);
            },
            keydown: function(event) {
                var preventDefault = true;
                switch (event.keyCode) {
                  case $.ui.keyCode.TAB:
                  case $.ui.keyCode.ESCAPE:
                    this.close(event);
                    preventDefault = false;
                    break;

                  case $.ui.keyCode.ENTER:
                    if (this.isOpen) {
                        this._selectFocusedItem(event);
                    }
                    break;

                  case $.ui.keyCode.UP:
                    if (event.altKey) {
                        this._toggle(event);
                    } else {
                        this._move("prev", event);
                    }
                    break;

                  case $.ui.keyCode.DOWN:
                    if (event.altKey) {
                        this._toggle(event);
                    } else {
                        this._move("next", event);
                    }
                    break;

                  case $.ui.keyCode.SPACE:
                    if (this.isOpen) {
                        this._selectFocusedItem(event);
                    } else {
                        this._toggle(event);
                    }
                    break;

                  case $.ui.keyCode.LEFT:
                    this._move("prev", event);
                    break;

                  case $.ui.keyCode.RIGHT:
                    this._move("next", event);
                    break;

                  case $.ui.keyCode.HOME:
                  case $.ui.keyCode.PAGE_UP:
                    this._move("first", event);
                    break;

                  case $.ui.keyCode.END:
                  case $.ui.keyCode.PAGE_DOWN:
                    this._move("last", event);
                    break;

                  default:
                    this.menu.trigger(event);
                    preventDefault = false;
                }
                if (preventDefault) {
                    event.preventDefault();
                }
            }
        },
        _selectFocusedItem: function(event) {
            var item = this.menuItems.eq(this.focusIndex).parent("li");
            if (!item.hasClass("ui-state-disabled")) {
                this._select(item.data("ui-selectmenu-item"), event);
            }
        },
        _select: function(item, event) {
            var oldIndex = this.element[0].selectedIndex;
            this.element[0].selectedIndex = item.index;
            this.buttonItem.replaceWith(this.buttonItem = this._renderButtonItem(item));
            this._setAria(item);
            this._trigger("select", event, {
                item: item
            });
            if (item.index !== oldIndex) {
                this._trigger("change", event, {
                    item: item
                });
            }
            this.close(event);
        },
        _setAria: function(item) {
            var id = this.menuItems.eq(item.index).attr("id");
            this.button.attr({
                "aria-labelledby": id,
                "aria-activedescendant": id
            });
            this.menu.attr("aria-activedescendant", id);
        },
        _setOption: function(key, value) {
            if (key === "icons") {
                var icon = this.button.find("span.ui-icon");
                this._removeClass(icon, null, this.options.icons.button)._addClass(icon, null, value.button);
            }
            this._super(key, value);
            if (key === "appendTo") {
                this.menuWrap.appendTo(this._appendTo());
            }
            if (key === "width") {
                this._resizeButton();
            }
        },
        _setOptionDisabled: function(value) {
            this._super(value);
            this.menuInstance.option("disabled", value);
            this.button.attr("aria-disabled", value);
            this._toggleClass(this.button, null, "ui-state-disabled", value);
            this.element.prop("disabled", value);
            if (value) {
                this.button.attr("tabindex", -1);
                this.close();
            } else {
                this.button.attr("tabindex", 0);
            }
        },
        _appendTo: function() {
            var element = this.options.appendTo;
            if (element) {
                element = element.jquery || element.nodeType ? $(element) : this.document.find(element).eq(0);
            }
            if (!element || !element[0]) {
                element = this.element.closest(".ui-front, dialog");
            }
            if (!element.length) {
                element = this.document[0].body;
            }
            return element;
        },
        _toggleAttr: function() {
            this.button.attr("aria-expanded", this.isOpen);
            this._removeClass(this.button, "ui-selectmenu-button-" + (this.isOpen ? "closed" : "open"))._addClass(this.button, "ui-selectmenu-button-" + (this.isOpen ? "open" : "closed"))._toggleClass(this.menuWrap, "ui-selectmenu-open", null, this.isOpen);
            this.menu.attr("aria-hidden", !this.isOpen);
        },
        _resizeButton: function() {
            var width = this.options.width;
            if (width === false) {
                this.button.css("width", "");
                return;
            }
            if (width === null) {
                width = this.element.show().outerWidth();
                this.element.hide();
            }
            this.button.outerWidth(width);
        },
        _resizeMenu: function() {
            this.menu.outerWidth(Math.max(this.button.outerWidth(), this.menu.width("").outerWidth() + 1));
        },
        _getCreateOptions: function() {
            var options = this._super();
            options.disabled = this.element.prop("disabled");
            return options;
        },
        _parseOptions: function(options) {
            var that = this, data = [];
            options.each(function(index, item) {
                data.push(that._parseOption($(item), index));
            });
            this.items = data;
        },
        _parseOption: function(option, index) {
            var optgroup = option.parent("optgroup");
            return {
                element: option,
                index: index,
                value: option.val(),
                label: option.text(),
                optgroup: optgroup.attr("label") || "",
                disabled: optgroup.prop("disabled") || option.prop("disabled")
            };
        },
        _destroy: function() {
            this._unbindFormResetHandler();
            this.menuWrap.remove();
            this.button.remove();
            this.element.show();
            this.element.removeUniqueId();
            this.labels.attr("for", this.ids.element);
        }
    } ]);
    var widgetsSlider = $.widget("ui.slider", $.ui.mouse, {
        version: "1.12.1",
        widgetEventPrefix: "slide",
        options: {
            animate: false,
            classes: {
                "ui-slider": "ui-corner-all",
                "ui-slider-handle": "ui-corner-all",
                "ui-slider-range": "ui-corner-all ui-widget-header"
            },
            distance: 0,
            max: 100,
            min: 0,
            orientation: "horizontal",
            range: false,
            step: 1,
            value: 0,
            values: null,
            change: null,
            slide: null,
            start: null,
            stop: null
        },
        numPages: 5,
        _create: function() {
            this._keySliding = false;
            this._mouseSliding = false;
            this._animateOff = true;
            this._handleIndex = null;
            this._detectOrientation();
            this._mouseInit();
            this._calculateNewMax();
            this._addClass("ui-slider ui-slider-" + this.orientation, "ui-widget ui-widget-content");
            this._refresh();
            this._animateOff = false;
        },
        _refresh: function() {
            this._createRange();
            this._createHandles();
            this._setupEvents();
            this._refreshValue();
        },
        _createHandles: function() {
            var i, handleCount, options = this.options, existingHandles = this.element.find(".ui-slider-handle"), handle = "<span tabindex='0'></span>", handles = [];
            handleCount = options.values && options.values.length || 1;
            if (existingHandles.length > handleCount) {
                existingHandles.slice(handleCount).remove();
                existingHandles = existingHandles.slice(0, handleCount);
            }
            for (i = existingHandles.length; i < handleCount; i++) {
                handles.push(handle);
            }
            this.handles = existingHandles.add($(handles.join("")).appendTo(this.element));
            this._addClass(this.handles, "ui-slider-handle", "ui-state-default");
            this.handle = this.handles.eq(0);
            this.handles.each(function(i) {
                $(this).data("ui-slider-handle-index", i).attr("tabIndex", 0);
            });
        },
        _createRange: function() {
            var options = this.options;
            if (options.range) {
                if (options.range === true) {
                    if (!options.values) {
                        options.values = [ this._valueMin(), this._valueMin() ];
                    } else if (options.values.length && options.values.length !== 2) {
                        options.values = [ options.values[0], options.values[0] ];
                    } else if ($.isArray(options.values)) {
                        options.values = options.values.slice(0);
                    }
                }
                if (!this.range || !this.range.length) {
                    this.range = $("<div>").appendTo(this.element);
                    this._addClass(this.range, "ui-slider-range");
                } else {
                    this._removeClass(this.range, "ui-slider-range-min ui-slider-range-max");
                    this.range.css({
                        left: "",
                        bottom: ""
                    });
                }
                if (options.range === "min" || options.range === "max") {
                    this._addClass(this.range, "ui-slider-range-" + options.range);
                }
            } else {
                if (this.range) {
                    this.range.remove();
                }
                this.range = null;
            }
        },
        _setupEvents: function() {
            this._off(this.handles);
            this._on(this.handles, this._handleEvents);
            this._hoverable(this.handles);
            this._focusable(this.handles);
        },
        _destroy: function() {
            this.handles.remove();
            if (this.range) {
                this.range.remove();
            }
            this._mouseDestroy();
        },
        _mouseCapture: function(event) {
            var position, normValue, distance, closestHandle, index, allowed, offset, mouseOverHandle, that = this, o = this.options;
            if (o.disabled) {
                return false;
            }
            this.elementSize = {
                width: this.element.outerWidth(),
                height: this.element.outerHeight()
            };
            this.elementOffset = this.element.offset();
            position = {
                x: event.pageX,
                y: event.pageY
            };
            normValue = this._normValueFromMouse(position);
            distance = this._valueMax() - this._valueMin() + 1;
            this.handles.each(function(i) {
                var thisDistance = Math.abs(normValue - that.values(i));
                if (distance > thisDistance || distance === thisDistance && (i === that._lastChangedValue || that.values(i) === o.min)) {
                    distance = thisDistance;
                    closestHandle = $(this);
                    index = i;
                }
            });
            allowed = this._start(event, index);
            if (allowed === false) {
                return false;
            }
            this._mouseSliding = true;
            this._handleIndex = index;
            this._addClass(closestHandle, null, "ui-state-active");
            closestHandle.trigger("focus");
            offset = closestHandle.offset();
            mouseOverHandle = !$(event.target).parents().addBack().is(".ui-slider-handle");
            this._clickOffset = mouseOverHandle ? {
                left: 0,
                top: 0
            } : {
                left: event.pageX - offset.left - closestHandle.width() / 2,
                top: event.pageY - offset.top - closestHandle.height() / 2 - (parseInt(closestHandle.css("borderTopWidth"), 10) || 0) - (parseInt(closestHandle.css("borderBottomWidth"), 10) || 0) + (parseInt(closestHandle.css("marginTop"), 10) || 0)
            };
            if (!this.handles.hasClass("ui-state-hover")) {
                this._slide(event, index, normValue);
            }
            this._animateOff = true;
            return true;
        },
        _mouseStart: function() {
            return true;
        },
        _mouseDrag: function(event) {
            var position = {
                x: event.pageX,
                y: event.pageY
            }, normValue = this._normValueFromMouse(position);
            this._slide(event, this._handleIndex, normValue);
            return false;
        },
        _mouseStop: function(event) {
            this._removeClass(this.handles, null, "ui-state-active");
            this._mouseSliding = false;
            this._stop(event, this._handleIndex);
            this._change(event, this._handleIndex);
            this._handleIndex = null;
            this._clickOffset = null;
            this._animateOff = false;
            return false;
        },
        _detectOrientation: function() {
            this.orientation = this.options.orientation === "vertical" ? "vertical" : "horizontal";
        },
        _normValueFromMouse: function(position) {
            var pixelTotal, pixelMouse, percentMouse, valueTotal, valueMouse;
            if (this.orientation === "horizontal") {
                pixelTotal = this.elementSize.width;
                pixelMouse = position.x - this.elementOffset.left - (this._clickOffset ? this._clickOffset.left : 0);
            } else {
                pixelTotal = this.elementSize.height;
                pixelMouse = position.y - this.elementOffset.top - (this._clickOffset ? this._clickOffset.top : 0);
            }
            percentMouse = pixelMouse / pixelTotal;
            if (percentMouse > 1) {
                percentMouse = 1;
            }
            if (percentMouse < 0) {
                percentMouse = 0;
            }
            if (this.orientation === "vertical") {
                percentMouse = 1 - percentMouse;
            }
            valueTotal = this._valueMax() - this._valueMin();
            valueMouse = this._valueMin() + percentMouse * valueTotal;
            return this._trimAlignValue(valueMouse);
        },
        _uiHash: function(index, value, values) {
            var uiHash = {
                handle: this.handles[index],
                handleIndex: index,
                value: value !== undefined ? value : this.value()
            };
            if (this._hasMultipleValues()) {
                uiHash.value = value !== undefined ? value : this.values(index);
                uiHash.values = values || this.values();
            }
            return uiHash;
        },
        _hasMultipleValues: function() {
            return this.options.values && this.options.values.length;
        },
        _start: function(event, index) {
            return this._trigger("start", event, this._uiHash(index));
        },
        _slide: function(event, index, newVal) {
            var allowed, otherVal, currentValue = this.value(), newValues = this.values();
            if (this._hasMultipleValues()) {
                otherVal = this.values(index ? 0 : 1);
                currentValue = this.values(index);
                if (this.options.values.length === 2 && this.options.range === true) {
                    newVal = index === 0 ? Math.min(otherVal, newVal) : Math.max(otherVal, newVal);
                }
                newValues[index] = newVal;
            }
            if (newVal === currentValue) {
                return;
            }
            allowed = this._trigger("slide", event, this._uiHash(index, newVal, newValues));
            if (allowed === false) {
                return;
            }
            if (this._hasMultipleValues()) {
                this.values(index, newVal);
            } else {
                this.value(newVal);
            }
        },
        _stop: function(event, index) {
            this._trigger("stop", event, this._uiHash(index));
        },
        _change: function(event, index) {
            if (!this._keySliding && !this._mouseSliding) {
                this._lastChangedValue = index;
                this._trigger("change", event, this._uiHash(index));
            }
        },
        value: function(newValue) {
            if (arguments.length) {
                this.options.value = this._trimAlignValue(newValue);
                this._refreshValue();
                this._change(null, 0);
                return;
            }
            return this._value();
        },
        values: function(index, newValue) {
            var vals, newValues, i;
            if (arguments.length > 1) {
                this.options.values[index] = this._trimAlignValue(newValue);
                this._refreshValue();
                this._change(null, index);
                return;
            }
            if (arguments.length) {
                if ($.isArray(arguments[0])) {
                    vals = this.options.values;
                    newValues = arguments[0];
                    for (i = 0; i < vals.length; i += 1) {
                        vals[i] = this._trimAlignValue(newValues[i]);
                        this._change(null, i);
                    }
                    this._refreshValue();
                } else {
                    if (this._hasMultipleValues()) {
                        return this._values(index);
                    } else {
                        return this.value();
                    }
                }
            } else {
                return this._values();
            }
        },
        _setOption: function(key, value) {
            var i, valsLength = 0;
            if (key === "range" && this.options.range === true) {
                if (value === "min") {
                    this.options.value = this._values(0);
                    this.options.values = null;
                } else if (value === "max") {
                    this.options.value = this._values(this.options.values.length - 1);
                    this.options.values = null;
                }
            }
            if ($.isArray(this.options.values)) {
                valsLength = this.options.values.length;
            }
            this._super(key, value);
            switch (key) {
              case "orientation":
                this._detectOrientation();
                this._removeClass("ui-slider-horizontal ui-slider-vertical")._addClass("ui-slider-" + this.orientation);
                this._refreshValue();
                if (this.options.range) {
                    this._refreshRange(value);
                }
                this.handles.css(value === "horizontal" ? "bottom" : "left", "");
                break;

              case "value":
                this._animateOff = true;
                this._refreshValue();
                this._change(null, 0);
                this._animateOff = false;
                break;

              case "values":
                this._animateOff = true;
                this._refreshValue();
                for (i = valsLength - 1; i >= 0; i--) {
                    this._change(null, i);
                }
                this._animateOff = false;
                break;

              case "step":
              case "min":
              case "max":
                this._animateOff = true;
                this._calculateNewMax();
                this._refreshValue();
                this._animateOff = false;
                break;

              case "range":
                this._animateOff = true;
                this._refresh();
                this._animateOff = false;
                break;
            }
        },
        _setOptionDisabled: function(value) {
            this._super(value);
            this._toggleClass(null, "ui-state-disabled", !!value);
        },
        _value: function() {
            var val = this.options.value;
            val = this._trimAlignValue(val);
            return val;
        },
        _values: function(index) {
            var val, vals, i;
            if (arguments.length) {
                val = this.options.values[index];
                val = this._trimAlignValue(val);
                return val;
            } else if (this._hasMultipleValues()) {
                vals = this.options.values.slice();
                for (i = 0; i < vals.length; i += 1) {
                    vals[i] = this._trimAlignValue(vals[i]);
                }
                return vals;
            } else {
                return [];
            }
        },
        _trimAlignValue: function(val) {
            if (val <= this._valueMin()) {
                return this._valueMin();
            }
            if (val >= this._valueMax()) {
                return this._valueMax();
            }
            var step = this.options.step > 0 ? this.options.step : 1, valModStep = (val - this._valueMin()) % step, alignValue = val - valModStep;
            if (Math.abs(valModStep) * 2 >= step) {
                alignValue += valModStep > 0 ? step : -step;
            }
            return parseFloat(alignValue.toFixed(5));
        },
        _calculateNewMax: function() {
            var max = this.options.max, min = this._valueMin(), step = this.options.step, aboveMin = Math.round((max - min) / step) * step;
            max = aboveMin + min;
            if (max > this.options.max) {
                max -= step;
            }
            this.max = parseFloat(max.toFixed(this._precision()));
        },
        _precision: function() {
            var precision = this._precisionOf(this.options.step);
            if (this.options.min !== null) {
                precision = Math.max(precision, this._precisionOf(this.options.min));
            }
            return precision;
        },
        _precisionOf: function(num) {
            var str = num.toString(), decimal = str.indexOf(".");
            return decimal === -1 ? 0 : str.length - decimal - 1;
        },
        _valueMin: function() {
            return this.options.min;
        },
        _valueMax: function() {
            return this.max;
        },
        _refreshRange: function(orientation) {
            if (orientation === "vertical") {
                this.range.css({
                    width: "",
                    left: ""
                });
            }
            if (orientation === "horizontal") {
                this.range.css({
                    height: "",
                    bottom: ""
                });
            }
        },
        _refreshValue: function() {
            var lastValPercent, valPercent, value, valueMin, valueMax, oRange = this.options.range, o = this.options, that = this, animate = !this._animateOff ? o.animate : false, _set = {};
            if (this._hasMultipleValues()) {
                this.handles.each(function(i) {
                    valPercent = (that.values(i) - that._valueMin()) / (that._valueMax() - that._valueMin()) * 100;
                    _set[that.orientation === "horizontal" ? "left" : "bottom"] = valPercent + "%";
                    $(this).stop(1, 1)[animate ? "animate" : "css"](_set, o.animate);
                    if (that.options.range === true) {
                        if (that.orientation === "horizontal") {
                            if (i === 0) {
                                that.range.stop(1, 1)[animate ? "animate" : "css"]({
                                    left: valPercent + "%"
                                }, o.animate);
                            }
                            if (i === 1) {
                                that.range[animate ? "animate" : "css"]({
                                    width: valPercent - lastValPercent + "%"
                                }, {
                                    queue: false,
                                    duration: o.animate
                                });
                            }
                        } else {
                            if (i === 0) {
                                that.range.stop(1, 1)[animate ? "animate" : "css"]({
                                    bottom: valPercent + "%"
                                }, o.animate);
                            }
                            if (i === 1) {
                                that.range[animate ? "animate" : "css"]({
                                    height: valPercent - lastValPercent + "%"
                                }, {
                                    queue: false,
                                    duration: o.animate
                                });
                            }
                        }
                    }
                    lastValPercent = valPercent;
                });
            } else {
                value = this.value();
                valueMin = this._valueMin();
                valueMax = this._valueMax();
                valPercent = valueMax !== valueMin ? (value - valueMin) / (valueMax - valueMin) * 100 : 0;
                _set[this.orientation === "horizontal" ? "left" : "bottom"] = valPercent + "%";
                this.handle.stop(1, 1)[animate ? "animate" : "css"](_set, o.animate);
                if (oRange === "min" && this.orientation === "horizontal") {
                    this.range.stop(1, 1)[animate ? "animate" : "css"]({
                        width: valPercent + "%"
                    }, o.animate);
                }
                if (oRange === "max" && this.orientation === "horizontal") {
                    this.range.stop(1, 1)[animate ? "animate" : "css"]({
                        width: 100 - valPercent + "%"
                    }, o.animate);
                }
                if (oRange === "min" && this.orientation === "vertical") {
                    this.range.stop(1, 1)[animate ? "animate" : "css"]({
                        height: valPercent + "%"
                    }, o.animate);
                }
                if (oRange === "max" && this.orientation === "vertical") {
                    this.range.stop(1, 1)[animate ? "animate" : "css"]({
                        height: 100 - valPercent + "%"
                    }, o.animate);
                }
            }
        },
        _handleEvents: {
            keydown: function(event) {
                var allowed, curVal, newVal, step, index = $(event.target).data("ui-slider-handle-index");
                switch (event.keyCode) {
                  case $.ui.keyCode.HOME:
                  case $.ui.keyCode.END:
                  case $.ui.keyCode.PAGE_UP:
                  case $.ui.keyCode.PAGE_DOWN:
                  case $.ui.keyCode.UP:
                  case $.ui.keyCode.RIGHT:
                  case $.ui.keyCode.DOWN:
                  case $.ui.keyCode.LEFT:
                    event.preventDefault();
                    if (!this._keySliding) {
                        this._keySliding = true;
                        this._addClass($(event.target), null, "ui-state-active");
                        allowed = this._start(event, index);
                        if (allowed === false) {
                            return;
                        }
                    }
                    break;
                }
                step = this.options.step;
                if (this._hasMultipleValues()) {
                    curVal = newVal = this.values(index);
                } else {
                    curVal = newVal = this.value();
                }
                switch (event.keyCode) {
                  case $.ui.keyCode.HOME:
                    newVal = this._valueMin();
                    break;

                  case $.ui.keyCode.END:
                    newVal = this._valueMax();
                    break;

                  case $.ui.keyCode.PAGE_UP:
                    newVal = this._trimAlignValue(curVal + (this._valueMax() - this._valueMin()) / this.numPages);
                    break;

                  case $.ui.keyCode.PAGE_DOWN:
                    newVal = this._trimAlignValue(curVal - (this._valueMax() - this._valueMin()) / this.numPages);
                    break;

                  case $.ui.keyCode.UP:
                  case $.ui.keyCode.RIGHT:
                    if (curVal === this._valueMax()) {
                        return;
                    }
                    newVal = this._trimAlignValue(curVal + step);
                    break;

                  case $.ui.keyCode.DOWN:
                  case $.ui.keyCode.LEFT:
                    if (curVal === this._valueMin()) {
                        return;
                    }
                    newVal = this._trimAlignValue(curVal - step);
                    break;
                }
                this._slide(event, index, newVal);
            },
            keyup: function(event) {
                var index = $(event.target).data("ui-slider-handle-index");
                if (this._keySliding) {
                    this._keySliding = false;
                    this._stop(event, index);
                    this._change(event, index);
                    this._removeClass($(event.target), null, "ui-state-active");
                }
            }
        }
    });
    var widgetsSortable = $.widget("ui.sortable", $.ui.mouse, {
        version: "1.12.1",
        widgetEventPrefix: "sort",
        ready: false,
        options: {
            appendTo: "parent",
            axis: false,
            connectWith: false,
            containment: false,
            cursor: "auto",
            cursorAt: false,
            dropOnEmpty: true,
            forcePlaceholderSize: false,
            forceHelperSize: false,
            grid: false,
            handle: false,
            helper: "original",
            items: "> *",
            opacity: false,
            placeholder: false,
            revert: false,
            scroll: true,
            scrollSensitivity: 20,
            scrollSpeed: 20,
            scope: "default",
            tolerance: "intersect",
            zIndex: 1e3,
            activate: null,
            beforeStop: null,
            change: null,
            deactivate: null,
            out: null,
            over: null,
            receive: null,
            remove: null,
            sort: null,
            start: null,
            stop: null,
            update: null
        },
        _isOverAxis: function(x, reference, size) {
            return x >= reference && x < reference + size;
        },
        _isFloating: function(item) {
            return /left|right/.test(item.css("float")) || /inline|table-cell/.test(item.css("display"));
        },
        _create: function() {
            this.containerCache = {};
            this._addClass("ui-sortable");
            this.refresh();
            this.offset = this.element.offset();
            this._mouseInit();
            this._setHandleClassName();
            this.ready = true;
        },
        _setOption: function(key, value) {
            this._super(key, value);
            if (key === "handle") {
                this._setHandleClassName();
            }
        },
        _setHandleClassName: function() {
            var that = this;
            this._removeClass(this.element.find(".ui-sortable-handle"), "ui-sortable-handle");
            $.each(this.items, function() {
                that._addClass(this.instance.options.handle ? this.item.find(this.instance.options.handle) : this.item, "ui-sortable-handle");
            });
        },
        _destroy: function() {
            this._mouseDestroy();
            for (var i = this.items.length - 1; i >= 0; i--) {
                this.items[i].item.removeData(this.widgetName + "-item");
            }
            return this;
        },
        _mouseCapture: function(event, overrideHandle) {
            var currentItem = null, validHandle = false, that = this;
            if (this.reverting) {
                return false;
            }
            if (this.options.disabled || this.options.type === "static") {
                return false;
            }
            this._refreshItems(event);
            $(event.target).parents().each(function() {
                if ($.data(this, that.widgetName + "-item") === that) {
                    currentItem = $(this);
                    return false;
                }
            });
            if ($.data(event.target, that.widgetName + "-item") === that) {
                currentItem = $(event.target);
            }
            if (!currentItem) {
                return false;
            }
            if (this.options.handle && !overrideHandle) {
                $(this.options.handle, currentItem).find("*").addBack().each(function() {
                    if (this === event.target) {
                        validHandle = true;
                    }
                });
                if (!validHandle) {
                    return false;
                }
            }
            this.currentItem = currentItem;
            this._removeCurrentsFromItems();
            return true;
        },
        _mouseStart: function(event, overrideHandle, noActivation) {
            var i, body, o = this.options;
            this.currentContainer = this;
            this.refreshPositions();
            this.helper = this._createHelper(event);
            this._cacheHelperProportions();
            this._cacheMargins();
            this.scrollParent = this.helper.scrollParent();
            this.offset = this.currentItem.offset();
            this.offset = {
                top: this.offset.top - this.margins.top,
                left: this.offset.left - this.margins.left
            };
            $.extend(this.offset, {
                click: {
                    left: event.pageX - this.offset.left,
                    top: event.pageY - this.offset.top
                },
                parent: this._getParentOffset(),
                relative: this._getRelativeOffset()
            });
            this.helper.css("position", "absolute");
            this.cssPosition = this.helper.css("position");
            this.originalPosition = this._generatePosition(event);
            this.originalPageX = event.pageX;
            this.originalPageY = event.pageY;
            o.cursorAt && this._adjustOffsetFromHelper(o.cursorAt);
            this.domPosition = {
                prev: this.currentItem.prev()[0],
                parent: this.currentItem.parent()[0]
            };
            if (this.helper[0] !== this.currentItem[0]) {
                this.currentItem.hide();
            }
            this._createPlaceholder();
            if (o.containment) {
                this._setContainment();
            }
            if (o.cursor && o.cursor !== "auto") {
                body = this.document.find("body");
                this.storedCursor = body.css("cursor");
                body.css("cursor", o.cursor);
                this.storedStylesheet = $("<style>*{ cursor: " + o.cursor + " !important; }</style>").appendTo(body);
            }
            if (o.opacity) {
                if (this.helper.css("opacity")) {
                    this._storedOpacity = this.helper.css("opacity");
                }
                this.helper.css("opacity", o.opacity);
            }
            if (o.zIndex) {
                if (this.helper.css("zIndex")) {
                    this._storedZIndex = this.helper.css("zIndex");
                }
                this.helper.css("zIndex", o.zIndex);
            }
            if (this.scrollParent[0] !== this.document[0] && this.scrollParent[0].tagName !== "HTML") {
                this.overflowOffset = this.scrollParent.offset();
            }
            this._trigger("start", event, this._uiHash());
            if (!this._preserveHelperProportions) {
                this._cacheHelperProportions();
            }
            if (!noActivation) {
                for (i = this.containers.length - 1; i >= 0; i--) {
                    this.containers[i]._trigger("activate", event, this._uiHash(this));
                }
            }
            if ($.ui.ddmanager) {
                $.ui.ddmanager.current = this;
            }
            if ($.ui.ddmanager && !o.dropBehaviour) {
                $.ui.ddmanager.prepareOffsets(this, event);
            }
            this.dragging = true;
            this._addClass(this.helper, "ui-sortable-helper");
            this._mouseDrag(event);
            return true;
        },
        _mouseDrag: function(event) {
            var i, item, itemElement, intersection, o = this.options, scrolled = false;
            this.position = this._generatePosition(event);
            this.positionAbs = this._convertPositionTo("absolute");
            if (!this.lastPositionAbs) {
                this.lastPositionAbs = this.positionAbs;
            }
            if (this.options.scroll) {
                if (this.scrollParent[0] !== this.document[0] && this.scrollParent[0].tagName !== "HTML") {
                    if (this.overflowOffset.top + this.scrollParent[0].offsetHeight - event.pageY < o.scrollSensitivity) {
                        this.scrollParent[0].scrollTop = scrolled = this.scrollParent[0].scrollTop + o.scrollSpeed;
                    } else if (event.pageY - this.overflowOffset.top < o.scrollSensitivity) {
                        this.scrollParent[0].scrollTop = scrolled = this.scrollParent[0].scrollTop - o.scrollSpeed;
                    }
                    if (this.overflowOffset.left + this.scrollParent[0].offsetWidth - event.pageX < o.scrollSensitivity) {
                        this.scrollParent[0].scrollLeft = scrolled = this.scrollParent[0].scrollLeft + o.scrollSpeed;
                    } else if (event.pageX - this.overflowOffset.left < o.scrollSensitivity) {
                        this.scrollParent[0].scrollLeft = scrolled = this.scrollParent[0].scrollLeft - o.scrollSpeed;
                    }
                } else {
                    if (event.pageY - this.document.scrollTop() < o.scrollSensitivity) {
                        scrolled = this.document.scrollTop(this.document.scrollTop() - o.scrollSpeed);
                    } else if (this.window.height() - (event.pageY - this.document.scrollTop()) < o.scrollSensitivity) {
                        scrolled = this.document.scrollTop(this.document.scrollTop() + o.scrollSpeed);
                    }
                    if (event.pageX - this.document.scrollLeft() < o.scrollSensitivity) {
                        scrolled = this.document.scrollLeft(this.document.scrollLeft() - o.scrollSpeed);
                    } else if (this.window.width() - (event.pageX - this.document.scrollLeft()) < o.scrollSensitivity) {
                        scrolled = this.document.scrollLeft(this.document.scrollLeft() + o.scrollSpeed);
                    }
                }
                if (scrolled !== false && $.ui.ddmanager && !o.dropBehaviour) {
                    $.ui.ddmanager.prepareOffsets(this, event);
                }
            }
            this.positionAbs = this._convertPositionTo("absolute");
            if (!this.options.axis || this.options.axis !== "y") {
                this.helper[0].style.left = this.position.left + "px";
            }
            if (!this.options.axis || this.options.axis !== "x") {
                this.helper[0].style.top = this.position.top + "px";
            }
            for (i = this.items.length - 1; i >= 0; i--) {
                item = this.items[i];
                itemElement = item.item[0];
                intersection = this._intersectsWithPointer(item);
                if (!intersection) {
                    continue;
                }
                if (item.instance !== this.currentContainer) {
                    continue;
                }
                if (itemElement !== this.currentItem[0] && this.placeholder[intersection === 1 ? "next" : "prev"]()[0] !== itemElement && !$.contains(this.placeholder[0], itemElement) && (this.options.type === "semi-dynamic" ? !$.contains(this.element[0], itemElement) : true)) {
                    this.direction = intersection === 1 ? "down" : "up";
                    if (this.options.tolerance === "pointer" || this._intersectsWithSides(item)) {
                        this._rearrange(event, item);
                    } else {
                        break;
                    }
                    this._trigger("change", event, this._uiHash());
                    break;
                }
            }
            this._contactContainers(event);
            if ($.ui.ddmanager) {
                $.ui.ddmanager.drag(this, event);
            }
            this._trigger("sort", event, this._uiHash());
            this.lastPositionAbs = this.positionAbs;
            return false;
        },
        _mouseStop: function(event, noPropagation) {
            if (!event) {
                return;
            }
            if ($.ui.ddmanager && !this.options.dropBehaviour) {
                $.ui.ddmanager.drop(this, event);
            }
            if (this.options.revert) {
                var that = this, cur = this.placeholder.offset(), axis = this.options.axis, animation = {};
                if (!axis || axis === "x") {
                    animation.left = cur.left - this.offset.parent.left - this.margins.left + (this.offsetParent[0] === this.document[0].body ? 0 : this.offsetParent[0].scrollLeft);
                }
                if (!axis || axis === "y") {
                    animation.top = cur.top - this.offset.parent.top - this.margins.top + (this.offsetParent[0] === this.document[0].body ? 0 : this.offsetParent[0].scrollTop);
                }
                this.reverting = true;
                $(this.helper).animate(animation, parseInt(this.options.revert, 10) || 500, function() {
                    that._clear(event);
                });
            } else {
                this._clear(event, noPropagation);
            }
            return false;
        },
        cancel: function() {
            if (this.dragging) {
                this._mouseUp(new $.Event("mouseup", {
                    target: null
                }));
                if (this.options.helper === "original") {
                    this.currentItem.css(this._storedCSS);
                    this._removeClass(this.currentItem, "ui-sortable-helper");
                } else {
                    this.currentItem.show();
                }
                for (var i = this.containers.length - 1; i >= 0; i--) {
                    this.containers[i]._trigger("deactivate", null, this._uiHash(this));
                    if (this.containers[i].containerCache.over) {
                        this.containers[i]._trigger("out", null, this._uiHash(this));
                        this.containers[i].containerCache.over = 0;
                    }
                }
            }
            if (this.placeholder) {
                if (this.placeholder[0].parentNode) {
                    this.placeholder[0].parentNode.removeChild(this.placeholder[0]);
                }
                if (this.options.helper !== "original" && this.helper && this.helper[0].parentNode) {
                    this.helper.remove();
                }
                $.extend(this, {
                    helper: null,
                    dragging: false,
                    reverting: false,
                    _noFinalSort: null
                });
                if (this.domPosition.prev) {
                    $(this.domPosition.prev).after(this.currentItem);
                } else {
                    $(this.domPosition.parent).prepend(this.currentItem);
                }
            }
            return this;
        },
        serialize: function(o) {
            var items = this._getItemsAsjQuery(o && o.connected), str = [];
            o = o || {};
            $(items).each(function() {
                var res = ($(o.item || this).attr(o.attribute || "id") || "").match(o.expression || /(.+)[\-=_](.+)/);
                if (res) {
                    str.push((o.key || res[1] + "[]") + "=" + (o.key && o.expression ? res[1] : res[2]));
                }
            });
            if (!str.length && o.key) {
                str.push(o.key + "=");
            }
            return str.join("&");
        },
        toArray: function(o) {
            var items = this._getItemsAsjQuery(o && o.connected), ret = [];
            o = o || {};
            items.each(function() {
                ret.push($(o.item || this).attr(o.attribute || "id") || "");
            });
            return ret;
        },
        _intersectsWith: function(item) {
            var x1 = this.positionAbs.left, x2 = x1 + this.helperProportions.width, y1 = this.positionAbs.top, y2 = y1 + this.helperProportions.height, l = item.left, r = l + item.width, t = item.top, b = t + item.height, dyClick = this.offset.click.top, dxClick = this.offset.click.left, isOverElementHeight = this.options.axis === "x" || y1 + dyClick > t && y1 + dyClick < b, isOverElementWidth = this.options.axis === "y" || x1 + dxClick > l && x1 + dxClick < r, isOverElement = isOverElementHeight && isOverElementWidth;
            if (this.options.tolerance === "pointer" || this.options.forcePointerForContainers || this.options.tolerance !== "pointer" && this.helperProportions[this.floating ? "width" : "height"] > item[this.floating ? "width" : "height"]) {
                return isOverElement;
            } else {
                return l < x1 + this.helperProportions.width / 2 && x2 - this.helperProportions.width / 2 < r && t < y1 + this.helperProportions.height / 2 && y2 - this.helperProportions.height / 2 < b;
            }
        },
        _intersectsWithPointer: function(item) {
            var verticalDirection, horizontalDirection, isOverElementHeight = this.options.axis === "x" || this._isOverAxis(this.positionAbs.top + this.offset.click.top, item.top, item.height), isOverElementWidth = this.options.axis === "y" || this._isOverAxis(this.positionAbs.left + this.offset.click.left, item.left, item.width), isOverElement = isOverElementHeight && isOverElementWidth;
            if (!isOverElement) {
                return false;
            }
            verticalDirection = this._getDragVerticalDirection();
            horizontalDirection = this._getDragHorizontalDirection();
            return this.floating ? horizontalDirection === "right" || verticalDirection === "down" ? 2 : 1 : verticalDirection && (verticalDirection === "down" ? 2 : 1);
        },
        _intersectsWithSides: function(item) {
            var isOverBottomHalf = this._isOverAxis(this.positionAbs.top + this.offset.click.top, item.top + item.height / 2, item.height), isOverRightHalf = this._isOverAxis(this.positionAbs.left + this.offset.click.left, item.left + item.width / 2, item.width), verticalDirection = this._getDragVerticalDirection(), horizontalDirection = this._getDragHorizontalDirection();
            if (this.floating && horizontalDirection) {
                return horizontalDirection === "right" && isOverRightHalf || horizontalDirection === "left" && !isOverRightHalf;
            } else {
                return verticalDirection && (verticalDirection === "down" && isOverBottomHalf || verticalDirection === "up" && !isOverBottomHalf);
            }
        },
        _getDragVerticalDirection: function() {
            var delta = this.positionAbs.top - this.lastPositionAbs.top;
            return delta !== 0 && (delta > 0 ? "down" : "up");
        },
        _getDragHorizontalDirection: function() {
            var delta = this.positionAbs.left - this.lastPositionAbs.left;
            return delta !== 0 && (delta > 0 ? "right" : "left");
        },
        refresh: function(event) {
            this._refreshItems(event);
            this._setHandleClassName();
            this.refreshPositions();
            return this;
        },
        _connectWith: function() {
            var options = this.options;
            return options.connectWith.constructor === String ? [ options.connectWith ] : options.connectWith;
        },
        _getItemsAsjQuery: function(connected) {
            var i, j, cur, inst, items = [], queries = [], connectWith = this._connectWith();
            if (connectWith && connected) {
                for (i = connectWith.length - 1; i >= 0; i--) {
                    cur = $(connectWith[i], this.document[0]);
                    for (j = cur.length - 1; j >= 0; j--) {
                        inst = $.data(cur[j], this.widgetFullName);
                        if (inst && inst !== this && !inst.options.disabled) {
                            queries.push([ $.isFunction(inst.options.items) ? inst.options.items.call(inst.element) : $(inst.options.items, inst.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"), inst ]);
                        }
                    }
                }
            }
            queries.push([ $.isFunction(this.options.items) ? this.options.items.call(this.element, null, {
                options: this.options,
                item: this.currentItem
            }) : $(this.options.items, this.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"), this ]);
            function addItems() {
                items.push(this);
            }
            for (i = queries.length - 1; i >= 0; i--) {
                queries[i][0].each(addItems);
            }
            return $(items);
        },
        _removeCurrentsFromItems: function() {
            var list = this.currentItem.find(":data(" + this.widgetName + "-item)");
            this.items = $.grep(this.items, function(item) {
                for (var j = 0; j < list.length; j++) {
                    if (list[j] === item.item[0]) {
                        return false;
                    }
                }
                return true;
            });
        },
        _refreshItems: function(event) {
            this.items = [];
            this.containers = [ this ];
            var i, j, cur, inst, targetData, _queries, item, queriesLength, items = this.items, queries = [ [ $.isFunction(this.options.items) ? this.options.items.call(this.element[0], event, {
                item: this.currentItem
            }) : $(this.options.items, this.element), this ] ], connectWith = this._connectWith();
            if (connectWith && this.ready) {
                for (i = connectWith.length - 1; i >= 0; i--) {
                    cur = $(connectWith[i], this.document[0]);
                    for (j = cur.length - 1; j >= 0; j--) {
                        inst = $.data(cur[j], this.widgetFullName);
                        if (inst && inst !== this && !inst.options.disabled) {
                            queries.push([ $.isFunction(inst.options.items) ? inst.options.items.call(inst.element[0], event, {
                                item: this.currentItem
                            }) : $(inst.options.items, inst.element), inst ]);
                            this.containers.push(inst);
                        }
                    }
                }
            }
            for (i = queries.length - 1; i >= 0; i--) {
                targetData = queries[i][1];
                _queries = queries[i][0];
                for (j = 0, queriesLength = _queries.length; j < queriesLength; j++) {
                    item = $(_queries[j]);
                    item.data(this.widgetName + "-item", targetData);
                    items.push({
                        item: item,
                        instance: targetData,
                        width: 0,
                        height: 0,
                        left: 0,
                        top: 0
                    });
                }
            }
        },
        refreshPositions: function(fast) {
            this.floating = this.items.length ? this.options.axis === "x" || this._isFloating(this.items[0].item) : false;
            if (this.offsetParent && this.helper) {
                this.offset.parent = this._getParentOffset();
            }
            var i, item, t, p;
            for (i = this.items.length - 1; i >= 0; i--) {
                item = this.items[i];
                if (item.instance !== this.currentContainer && this.currentContainer && item.item[0] !== this.currentItem[0]) {
                    continue;
                }
                t = this.options.toleranceElement ? $(this.options.toleranceElement, item.item) : item.item;
                if (!fast) {
                    item.width = t.outerWidth();
                    item.height = t.outerHeight();
                }
                p = t.offset();
                item.left = p.left;
                item.top = p.top;
            }
            if (this.options.custom && this.options.custom.refreshContainers) {
                this.options.custom.refreshContainers.call(this);
            } else {
                for (i = this.containers.length - 1; i >= 0; i--) {
                    p = this.containers[i].element.offset();
                    this.containers[i].containerCache.left = p.left;
                    this.containers[i].containerCache.top = p.top;
                    this.containers[i].containerCache.width = this.containers[i].element.outerWidth();
                    this.containers[i].containerCache.height = this.containers[i].element.outerHeight();
                }
            }
            return this;
        },
        _createPlaceholder: function(that) {
            that = that || this;
            var className, o = that.options;
            if (!o.placeholder || o.placeholder.constructor === String) {
                className = o.placeholder;
                o.placeholder = {
                    element: function() {
                        var nodeName = that.currentItem[0].nodeName.toLowerCase(), element = $("<" + nodeName + ">", that.document[0]);
                        that._addClass(element, "ui-sortable-placeholder", className || that.currentItem[0].className)._removeClass(element, "ui-sortable-helper");
                        if (nodeName === "tbody") {
                            that._createTrPlaceholder(that.currentItem.find("tr").eq(0), $("<tr>", that.document[0]).appendTo(element));
                        } else if (nodeName === "tr") {
                            that._createTrPlaceholder(that.currentItem, element);
                        } else if (nodeName === "img") {
                            element.attr("src", that.currentItem.attr("src"));
                        }
                        if (!className) {
                            element.css("visibility", "hidden");
                        }
                        return element;
                    },
                    update: function(container, p) {
                        if (className && !o.forcePlaceholderSize) {
                            return;
                        }
                        if (!p.height()) {
                            p.height(that.currentItem.innerHeight() - parseInt(that.currentItem.css("paddingTop") || 0, 10) - parseInt(that.currentItem.css("paddingBottom") || 0, 10));
                        }
                        if (!p.width()) {
                            p.width(that.currentItem.innerWidth() - parseInt(that.currentItem.css("paddingLeft") || 0, 10) - parseInt(that.currentItem.css("paddingRight") || 0, 10));
                        }
                    }
                };
            }
            that.placeholder = $(o.placeholder.element.call(that.element, that.currentItem));
            that.currentItem.after(that.placeholder);
            o.placeholder.update(that, that.placeholder);
        },
        _createTrPlaceholder: function(sourceTr, targetTr) {
            var that = this;
            sourceTr.children().each(function() {
                $("<td>&#160;</td>", that.document[0]).attr("colspan", $(this).attr("colspan") || 1).appendTo(targetTr);
            });
        },
        _contactContainers: function(event) {
            var i, j, dist, itemWithLeastDistance, posProperty, sizeProperty, cur, nearBottom, floating, axis, innermostContainer = null, innermostIndex = null;
            for (i = this.containers.length - 1; i >= 0; i--) {
                if ($.contains(this.currentItem[0], this.containers[i].element[0])) {
                    continue;
                }
                if (this._intersectsWith(this.containers[i].containerCache)) {
                    if (innermostContainer && $.contains(this.containers[i].element[0], innermostContainer.element[0])) {
                        continue;
                    }
                    innermostContainer = this.containers[i];
                    innermostIndex = i;
                } else {
                    if (this.containers[i].containerCache.over) {
                        this.containers[i]._trigger("out", event, this._uiHash(this));
                        this.containers[i].containerCache.over = 0;
                    }
                }
            }
            if (!innermostContainer) {
                return;
            }
            if (this.containers.length === 1) {
                if (!this.containers[innermostIndex].containerCache.over) {
                    this.containers[innermostIndex]._trigger("over", event, this._uiHash(this));
                    this.containers[innermostIndex].containerCache.over = 1;
                }
            } else {
                dist = 1e4;
                itemWithLeastDistance = null;
                floating = innermostContainer.floating || this._isFloating(this.currentItem);
                posProperty = floating ? "left" : "top";
                sizeProperty = floating ? "width" : "height";
                axis = floating ? "pageX" : "pageY";
                for (j = this.items.length - 1; j >= 0; j--) {
                    if (!$.contains(this.containers[innermostIndex].element[0], this.items[j].item[0])) {
                        continue;
                    }
                    if (this.items[j].item[0] === this.currentItem[0]) {
                        continue;
                    }
                    cur = this.items[j].item.offset()[posProperty];
                    nearBottom = false;
                    if (event[axis] - cur > this.items[j][sizeProperty] / 2) {
                        nearBottom = true;
                    }
                    if (Math.abs(event[axis] - cur) < dist) {
                        dist = Math.abs(event[axis] - cur);
                        itemWithLeastDistance = this.items[j];
                        this.direction = nearBottom ? "up" : "down";
                    }
                }
                if (!itemWithLeastDistance && !this.options.dropOnEmpty) {
                    return;
                }
                if (this.currentContainer === this.containers[innermostIndex]) {
                    if (!this.currentContainer.containerCache.over) {
                        this.containers[innermostIndex]._trigger("over", event, this._uiHash());
                        this.currentContainer.containerCache.over = 1;
                    }
                    return;
                }
                itemWithLeastDistance ? this._rearrange(event, itemWithLeastDistance, null, true) : this._rearrange(event, null, this.containers[innermostIndex].element, true);
                this._trigger("change", event, this._uiHash());
                this.containers[innermostIndex]._trigger("change", event, this._uiHash(this));
                this.currentContainer = this.containers[innermostIndex];
                this.options.placeholder.update(this.currentContainer, this.placeholder);
                this.containers[innermostIndex]._trigger("over", event, this._uiHash(this));
                this.containers[innermostIndex].containerCache.over = 1;
            }
        },
        _createHelper: function(event) {
            var o = this.options, helper = $.isFunction(o.helper) ? $(o.helper.apply(this.element[0], [ event, this.currentItem ])) : o.helper === "clone" ? this.currentItem.clone() : this.currentItem;
            if (!helper.parents("body").length) {
                $(o.appendTo !== "parent" ? o.appendTo : this.currentItem[0].parentNode)[0].appendChild(helper[0]);
            }
            if (helper[0] === this.currentItem[0]) {
                this._storedCSS = {
                    width: this.currentItem[0].style.width,
                    height: this.currentItem[0].style.height,
                    position: this.currentItem.css("position"),
                    top: this.currentItem.css("top"),
                    left: this.currentItem.css("left")
                };
            }
            if (!helper[0].style.width || o.forceHelperSize) {
                helper.width(this.currentItem.width());
            }
            if (!helper[0].style.height || o.forceHelperSize) {
                helper.height(this.currentItem.height());
            }
            return helper;
        },
        _adjustOffsetFromHelper: function(obj) {
            if (typeof obj === "string") {
                obj = obj.split(" ");
            }
            if ($.isArray(obj)) {
                obj = {
                    left: +obj[0],
                    top: +obj[1] || 0
                };
            }
            if ("left" in obj) {
                this.offset.click.left = obj.left + this.margins.left;
            }
            if ("right" in obj) {
                this.offset.click.left = this.helperProportions.width - obj.right + this.margins.left;
            }
            if ("top" in obj) {
                this.offset.click.top = obj.top + this.margins.top;
            }
            if ("bottom" in obj) {
                this.offset.click.top = this.helperProportions.height - obj.bottom + this.margins.top;
            }
        },
        _getParentOffset: function() {
            this.offsetParent = this.helper.offsetParent();
            var po = this.offsetParent.offset();
            if (this.cssPosition === "absolute" && this.scrollParent[0] !== this.document[0] && $.contains(this.scrollParent[0], this.offsetParent[0])) {
                po.left += this.scrollParent.scrollLeft();
                po.top += this.scrollParent.scrollTop();
            }
            if (this.offsetParent[0] === this.document[0].body || this.offsetParent[0].tagName && this.offsetParent[0].tagName.toLowerCase() === "html" && $.ui.ie) {
                po = {
                    top: 0,
                    left: 0
                };
            }
            return {
                top: po.top + (parseInt(this.offsetParent.css("borderTopWidth"), 10) || 0),
                left: po.left + (parseInt(this.offsetParent.css("borderLeftWidth"), 10) || 0)
            };
        },
        _getRelativeOffset: function() {
            if (this.cssPosition === "relative") {
                var p = this.currentItem.position();
                return {
                    top: p.top - (parseInt(this.helper.css("top"), 10) || 0) + this.scrollParent.scrollTop(),
                    left: p.left - (parseInt(this.helper.css("left"), 10) || 0) + this.scrollParent.scrollLeft()
                };
            } else {
                return {
                    top: 0,
                    left: 0
                };
            }
        },
        _cacheMargins: function() {
            this.margins = {
                left: parseInt(this.currentItem.css("marginLeft"), 10) || 0,
                top: parseInt(this.currentItem.css("marginTop"), 10) || 0
            };
        },
        _cacheHelperProportions: function() {
            this.helperProportions = {
                width: this.helper.outerWidth(),
                height: this.helper.outerHeight()
            };
        },
        _setContainment: function() {
            var ce, co, over, o = this.options;
            if (o.containment === "parent") {
                o.containment = this.helper[0].parentNode;
            }
            if (o.containment === "document" || o.containment === "window") {
                this.containment = [ 0 - this.offset.relative.left - this.offset.parent.left, 0 - this.offset.relative.top - this.offset.parent.top, o.containment === "document" ? this.document.width() : this.window.width() - this.helperProportions.width - this.margins.left, (o.containment === "document" ? this.document.height() || document.body.parentNode.scrollHeight : this.window.height() || this.document[0].body.parentNode.scrollHeight) - this.helperProportions.height - this.margins.top ];
            }
            if (!/^(document|window|parent)$/.test(o.containment)) {
                ce = $(o.containment)[0];
                co = $(o.containment).offset();
                over = $(ce).css("overflow") !== "hidden";
                this.containment = [ co.left + (parseInt($(ce).css("borderLeftWidth"), 10) || 0) + (parseInt($(ce).css("paddingLeft"), 10) || 0) - this.margins.left, co.top + (parseInt($(ce).css("borderTopWidth"), 10) || 0) + (parseInt($(ce).css("paddingTop"), 10) || 0) - this.margins.top, co.left + (over ? Math.max(ce.scrollWidth, ce.offsetWidth) : ce.offsetWidth) - (parseInt($(ce).css("borderLeftWidth"), 10) || 0) - (parseInt($(ce).css("paddingRight"), 10) || 0) - this.helperProportions.width - this.margins.left, co.top + (over ? Math.max(ce.scrollHeight, ce.offsetHeight) : ce.offsetHeight) - (parseInt($(ce).css("borderTopWidth"), 10) || 0) - (parseInt($(ce).css("paddingBottom"), 10) || 0) - this.helperProportions.height - this.margins.top ];
            }
        },
        _convertPositionTo: function(d, pos) {
            if (!pos) {
                pos = this.position;
            }
            var mod = d === "absolute" ? 1 : -1, scroll = this.cssPosition === "absolute" && !(this.scrollParent[0] !== this.document[0] && $.contains(this.scrollParent[0], this.offsetParent[0])) ? this.offsetParent : this.scrollParent, scrollIsRootNode = /(html|body)/i.test(scroll[0].tagName);
            return {
                top: pos.top + this.offset.relative.top * mod + this.offset.parent.top * mod - (this.cssPosition === "fixed" ? -this.scrollParent.scrollTop() : scrollIsRootNode ? 0 : scroll.scrollTop()) * mod,
                left: pos.left + this.offset.relative.left * mod + this.offset.parent.left * mod - (this.cssPosition === "fixed" ? -this.scrollParent.scrollLeft() : scrollIsRootNode ? 0 : scroll.scrollLeft()) * mod
            };
        },
        _generatePosition: function(event) {
            var top, left, o = this.options, pageX = event.pageX, pageY = event.pageY, scroll = this.cssPosition === "absolute" && !(this.scrollParent[0] !== this.document[0] && $.contains(this.scrollParent[0], this.offsetParent[0])) ? this.offsetParent : this.scrollParent, scrollIsRootNode = /(html|body)/i.test(scroll[0].tagName);
            if (this.cssPosition === "relative" && !(this.scrollParent[0] !== this.document[0] && this.scrollParent[0] !== this.offsetParent[0])) {
                this.offset.relative = this._getRelativeOffset();
            }
            if (this.originalPosition) {
                if (this.containment) {
                    if (event.pageX - this.offset.click.left < this.containment[0]) {
                        pageX = this.containment[0] + this.offset.click.left;
                    }
                    if (event.pageY - this.offset.click.top < this.containment[1]) {
                        pageY = this.containment[1] + this.offset.click.top;
                    }
                    if (event.pageX - this.offset.click.left > this.containment[2]) {
                        pageX = this.containment[2] + this.offset.click.left;
                    }
                    if (event.pageY - this.offset.click.top > this.containment[3]) {
                        pageY = this.containment[3] + this.offset.click.top;
                    }
                }
                if (o.grid) {
                    top = this.originalPageY + Math.round((pageY - this.originalPageY) / o.grid[1]) * o.grid[1];
                    pageY = this.containment ? top - this.offset.click.top >= this.containment[1] && top - this.offset.click.top <= this.containment[3] ? top : top - this.offset.click.top >= this.containment[1] ? top - o.grid[1] : top + o.grid[1] : top;
                    left = this.originalPageX + Math.round((pageX - this.originalPageX) / o.grid[0]) * o.grid[0];
                    pageX = this.containment ? left - this.offset.click.left >= this.containment[0] && left - this.offset.click.left <= this.containment[2] ? left : left - this.offset.click.left >= this.containment[0] ? left - o.grid[0] : left + o.grid[0] : left;
                }
            }
            return {
                top: pageY - this.offset.click.top - this.offset.relative.top - this.offset.parent.top + (this.cssPosition === "fixed" ? -this.scrollParent.scrollTop() : scrollIsRootNode ? 0 : scroll.scrollTop()),
                left: pageX - this.offset.click.left - this.offset.relative.left - this.offset.parent.left + (this.cssPosition === "fixed" ? -this.scrollParent.scrollLeft() : scrollIsRootNode ? 0 : scroll.scrollLeft())
            };
        },
        _rearrange: function(event, i, a, hardRefresh) {
            a ? a[0].appendChild(this.placeholder[0]) : i.item[0].parentNode.insertBefore(this.placeholder[0], this.direction === "down" ? i.item[0] : i.item[0].nextSibling);
            this.counter = this.counter ? ++this.counter : 1;
            var counter = this.counter;
            this._delay(function() {
                if (counter === this.counter) {
                    this.refreshPositions(!hardRefresh);
                }
            });
        },
        _clear: function(event, noPropagation) {
            this.reverting = false;
            var i, delayedTriggers = [];
            if (!this._noFinalSort && this.currentItem.parent().length) {
                this.placeholder.before(this.currentItem);
            }
            this._noFinalSort = null;
            if (this.helper[0] === this.currentItem[0]) {
                for (i in this._storedCSS) {
                    if (this._storedCSS[i] === "auto" || this._storedCSS[i] === "static") {
                        this._storedCSS[i] = "";
                    }
                }
                this.currentItem.css(this._storedCSS);
                this._removeClass(this.currentItem, "ui-sortable-helper");
            } else {
                this.currentItem.show();
            }
            if (this.fromOutside && !noPropagation) {
                delayedTriggers.push(function(event) {
                    this._trigger("receive", event, this._uiHash(this.fromOutside));
                });
            }
            if ((this.fromOutside || this.domPosition.prev !== this.currentItem.prev().not(".ui-sortable-helper")[0] || this.domPosition.parent !== this.currentItem.parent()[0]) && !noPropagation) {
                delayedTriggers.push(function(event) {
                    this._trigger("update", event, this._uiHash());
                });
            }
            if (this !== this.currentContainer) {
                if (!noPropagation) {
                    delayedTriggers.push(function(event) {
                        this._trigger("remove", event, this._uiHash());
                    });
                    delayedTriggers.push(function(c) {
                        return function(event) {
                            c._trigger("receive", event, this._uiHash(this));
                        };
                    }.call(this, this.currentContainer));
                    delayedTriggers.push(function(c) {
                        return function(event) {
                            c._trigger("update", event, this._uiHash(this));
                        };
                    }.call(this, this.currentContainer));
                }
            }
            function delayEvent(type, instance, container) {
                return function(event) {
                    container._trigger(type, event, instance._uiHash(instance));
                };
            }
            for (i = this.containers.length - 1; i >= 0; i--) {
                if (!noPropagation) {
                    delayedTriggers.push(delayEvent("deactivate", this, this.containers[i]));
                }
                if (this.containers[i].containerCache.over) {
                    delayedTriggers.push(delayEvent("out", this, this.containers[i]));
                    this.containers[i].containerCache.over = 0;
                }
            }
            if (this.storedCursor) {
                this.document.find("body").css("cursor", this.storedCursor);
                this.storedStylesheet.remove();
            }
            if (this._storedOpacity) {
                this.helper.css("opacity", this._storedOpacity);
            }
            if (this._storedZIndex) {
                this.helper.css("zIndex", this._storedZIndex === "auto" ? "" : this._storedZIndex);
            }
            this.dragging = false;
            if (!noPropagation) {
                this._trigger("beforeStop", event, this._uiHash());
            }
            this.placeholder[0].parentNode.removeChild(this.placeholder[0]);
            if (!this.cancelHelperRemoval) {
                if (this.helper[0] !== this.currentItem[0]) {
                    this.helper.remove();
                }
                this.helper = null;
            }
            if (!noPropagation) {
                for (i = 0; i < delayedTriggers.length; i++) {
                    delayedTriggers[i].call(this, event);
                }
                this._trigger("stop", event, this._uiHash());
            }
            this.fromOutside = false;
            return !this.cancelHelperRemoval;
        },
        _trigger: function() {
            if ($.Widget.prototype._trigger.apply(this, arguments) === false) {
                this.cancel();
            }
        },
        _uiHash: function(_inst) {
            var inst = _inst || this;
            return {
                helper: inst.helper,
                placeholder: inst.placeholder || $([]),
                position: inst.position,
                originalPosition: inst.originalPosition,
                offset: inst.positionAbs,
                item: inst.currentItem,
                sender: _inst ? _inst.element : null
            };
        }
    });
    function spinnerModifer(fn) {
        return function() {
            var previous = this.element.val();
            fn.apply(this, arguments);
            this._refresh();
            if (previous !== this.element.val()) {
                this._trigger("change");
            }
        };
    }
    $.widget("ui.spinner", {
        version: "1.12.1",
        defaultElement: "<input>",
        widgetEventPrefix: "spin",
        options: {
            classes: {
                "ui-spinner": "ui-corner-all",
                "ui-spinner-down": "ui-corner-br",
                "ui-spinner-up": "ui-corner-tr"
            },
            culture: null,
            icons: {
                down: "ui-icon-triangle-1-s",
                up: "ui-icon-triangle-1-n"
            },
            incremental: true,
            max: null,
            min: null,
            numberFormat: null,
            page: 10,
            step: 1,
            change: null,
            spin: null,
            start: null,
            stop: null
        },
        _create: function() {
            this._setOption("max", this.options.max);
            this._setOption("min", this.options.min);
            this._setOption("step", this.options.step);
            if (this.value() !== "") {
                this._value(this.element.val(), true);
            }
            this._draw();
            this._on(this._events);
            this._refresh();
            this._on(this.window, {
                beforeunload: function() {
                    this.element.removeAttr("autocomplete");
                }
            });
        },
        _getCreateOptions: function() {
            var options = this._super();
            var element = this.element;
            $.each([ "min", "max", "step" ], function(i, option) {
                var value = element.attr(option);
                if (value != null && value.length) {
                    options[option] = value;
                }
            });
            return options;
        },
        _events: {
            keydown: function(event) {
                if (this._start(event) && this._keydown(event)) {
                    event.preventDefault();
                }
            },
            keyup: "_stop",
            focus: function() {
                this.previous = this.element.val();
            },
            blur: function(event) {
                if (this.cancelBlur) {
                    delete this.cancelBlur;
                    return;
                }
                this._stop();
                this._refresh();
                if (this.previous !== this.element.val()) {
                    this._trigger("change", event);
                }
            },
            mousewheel: function(event, delta) {
                if (!delta) {
                    return;
                }
                if (!this.spinning && !this._start(event)) {
                    return false;
                }
                this._spin((delta > 0 ? 1 : -1) * this.options.step, event);
                clearTimeout(this.mousewheelTimer);
                this.mousewheelTimer = this._delay(function() {
                    if (this.spinning) {
                        this._stop(event);
                    }
                }, 100);
                event.preventDefault();
            },
            "mousedown .ui-spinner-button": function(event) {
                var previous;
                previous = this.element[0] === $.ui.safeActiveElement(this.document[0]) ? this.previous : this.element.val();
                function checkFocus() {
                    var isActive = this.element[0] === $.ui.safeActiveElement(this.document[0]);
                    if (!isActive) {
                        this.element.trigger("focus");
                        this.previous = previous;
                        this._delay(function() {
                            this.previous = previous;
                        });
                    }
                }
                event.preventDefault();
                checkFocus.call(this);
                this.cancelBlur = true;
                this._delay(function() {
                    delete this.cancelBlur;
                    checkFocus.call(this);
                });
                if (this._start(event) === false) {
                    return;
                }
                this._repeat(null, $(event.currentTarget).hasClass("ui-spinner-up") ? 1 : -1, event);
            },
            "mouseup .ui-spinner-button": "_stop",
            "mouseenter .ui-spinner-button": function(event) {
                if (!$(event.currentTarget).hasClass("ui-state-active")) {
                    return;
                }
                if (this._start(event) === false) {
                    return false;
                }
                this._repeat(null, $(event.currentTarget).hasClass("ui-spinner-up") ? 1 : -1, event);
            },
            "mouseleave .ui-spinner-button": "_stop"
        },
        _enhance: function() {
            this.uiSpinner = this.element.attr("autocomplete", "off").wrap("<span>").parent().append("<a></a><a></a>");
        },
        _draw: function() {
            this._enhance();
            this._addClass(this.uiSpinner, "ui-spinner", "ui-widget ui-widget-content");
            this._addClass("ui-spinner-input");
            this.element.attr("role", "spinbutton");
            this.buttons = this.uiSpinner.children("a").attr("tabIndex", -1).attr("aria-hidden", true).button({
                classes: {
                    "ui-button": ""
                }
            });
            this._removeClass(this.buttons, "ui-corner-all");
            this._addClass(this.buttons.first(), "ui-spinner-button ui-spinner-up");
            this._addClass(this.buttons.last(), "ui-spinner-button ui-spinner-down");
            this.buttons.first().button({
                icon: this.options.icons.up,
                showLabel: false
            });
            this.buttons.last().button({
                icon: this.options.icons.down,
                showLabel: false
            });
            if (this.buttons.height() > Math.ceil(this.uiSpinner.height() * .5) && this.uiSpinner.height() > 0) {
                this.uiSpinner.height(this.uiSpinner.height());
            }
        },
        _keydown: function(event) {
            var options = this.options, keyCode = $.ui.keyCode;
            switch (event.keyCode) {
              case keyCode.UP:
                this._repeat(null, 1, event);
                return true;

              case keyCode.DOWN:
                this._repeat(null, -1, event);
                return true;

              case keyCode.PAGE_UP:
                this._repeat(null, options.page, event);
                return true;

              case keyCode.PAGE_DOWN:
                this._repeat(null, -options.page, event);
                return true;
            }
            return false;
        },
        _start: function(event) {
            if (!this.spinning && this._trigger("start", event) === false) {
                return false;
            }
            if (!this.counter) {
                this.counter = 1;
            }
            this.spinning = true;
            return true;
        },
        _repeat: function(i, steps, event) {
            i = i || 500;
            clearTimeout(this.timer);
            this.timer = this._delay(function() {
                this._repeat(40, steps, event);
            }, i);
            this._spin(steps * this.options.step, event);
        },
        _spin: function(step, event) {
            var value = this.value() || 0;
            if (!this.counter) {
                this.counter = 1;
            }
            value = this._adjustValue(value + step * this._increment(this.counter));
            if (!this.spinning || this._trigger("spin", event, {
                value: value
            }) !== false) {
                this._value(value);
                this.counter++;
            }
        },
        _increment: function(i) {
            var incremental = this.options.incremental;
            if (incremental) {
                return $.isFunction(incremental) ? incremental(i) : Math.floor(i * i * i / 5e4 - i * i / 500 + 17 * i / 200 + 1);
            }
            return 1;
        },
        _precision: function() {
            var precision = this._precisionOf(this.options.step);
            if (this.options.min !== null) {
                precision = Math.max(precision, this._precisionOf(this.options.min));
            }
            return precision;
        },
        _precisionOf: function(num) {
            var str = num.toString(), decimal = str.indexOf(".");
            return decimal === -1 ? 0 : str.length - decimal - 1;
        },
        _adjustValue: function(value) {
            var base, aboveMin, options = this.options;
            base = options.min !== null ? options.min : 0;
            aboveMin = value - base;
            aboveMin = Math.round(aboveMin / options.step) * options.step;
            value = base + aboveMin;
            value = parseFloat(value.toFixed(this._precision()));
            if (options.max !== null && value > options.max) {
                return options.max;
            }
            if (options.min !== null && value < options.min) {
                return options.min;
            }
            return value;
        },
        _stop: function(event) {
            if (!this.spinning) {
                return;
            }
            clearTimeout(this.timer);
            clearTimeout(this.mousewheelTimer);
            this.counter = 0;
            this.spinning = false;
            this._trigger("stop", event);
        },
        _setOption: function(key, value) {
            var prevValue, first, last;
            if (key === "culture" || key === "numberFormat") {
                prevValue = this._parse(this.element.val());
                this.options[key] = value;
                this.element.val(this._format(prevValue));
                return;
            }
            if (key === "max" || key === "min" || key === "step") {
                if (typeof value === "string") {
                    value = this._parse(value);
                }
            }
            if (key === "icons") {
                first = this.buttons.first().find(".ui-icon");
                this._removeClass(first, null, this.options.icons.up);
                this._addClass(first, null, value.up);
                last = this.buttons.last().find(".ui-icon");
                this._removeClass(last, null, this.options.icons.down);
                this._addClass(last, null, value.down);
            }
            this._super(key, value);
        },
        _setOptionDisabled: function(value) {
            this._super(value);
            this._toggleClass(this.uiSpinner, null, "ui-state-disabled", !!value);
            this.element.prop("disabled", !!value);
            this.buttons.button(value ? "disable" : "enable");
        },
        _setOptions: spinnerModifer(function(options) {
            this._super(options);
        }),
        _parse: function(val) {
            if (typeof val === "string" && val !== "") {
                val = window.Globalize && this.options.numberFormat ? Globalize.parseFloat(val, 10, this.options.culture) : +val;
            }
            return val === "" || isNaN(val) ? null : val;
        },
        _format: function(value) {
            if (value === "") {
                return "";
            }
            return window.Globalize && this.options.numberFormat ? Globalize.format(value, this.options.numberFormat, this.options.culture) : value;
        },
        _refresh: function() {
            this.element.attr({
                "aria-valuemin": this.options.min,
                "aria-valuemax": this.options.max,
                "aria-valuenow": this._parse(this.element.val())
            });
        },
        isValid: function() {
            var value = this.value();
            if (value === null) {
                return false;
            }
            return value === this._adjustValue(value);
        },
        _value: function(value, allowAny) {
            var parsed;
            if (value !== "") {
                parsed = this._parse(value);
                if (parsed !== null) {
                    if (!allowAny) {
                        parsed = this._adjustValue(parsed);
                    }
                    value = this._format(parsed);
                }
            }
            this.element.val(value);
            this._refresh();
        },
        _destroy: function() {
            this.element.prop("disabled", false).removeAttr("autocomplete role aria-valuemin aria-valuemax aria-valuenow");
            this.uiSpinner.replaceWith(this.element);
        },
        stepUp: spinnerModifer(function(steps) {
            this._stepUp(steps);
        }),
        _stepUp: function(steps) {
            if (this._start()) {
                this._spin((steps || 1) * this.options.step);
                this._stop();
            }
        },
        stepDown: spinnerModifer(function(steps) {
            this._stepDown(steps);
        }),
        _stepDown: function(steps) {
            if (this._start()) {
                this._spin((steps || 1) * -this.options.step);
                this._stop();
            }
        },
        pageUp: spinnerModifer(function(pages) {
            this._stepUp((pages || 1) * this.options.page);
        }),
        pageDown: spinnerModifer(function(pages) {
            this._stepDown((pages || 1) * this.options.page);
        }),
        value: function(newVal) {
            if (!arguments.length) {
                return this._parse(this.element.val());
            }
            spinnerModifer(this._value).call(this, newVal);
        },
        widget: function() {
            return this.uiSpinner;
        }
    });
    if ($.uiBackCompat !== false) {
        $.widget("ui.spinner", $.ui.spinner, {
            _enhance: function() {
                this.uiSpinner = this.element.attr("autocomplete", "off").wrap(this._uiSpinnerHtml()).parent().append(this._buttonHtml());
            },
            _uiSpinnerHtml: function() {
                return "<span>";
            },
            _buttonHtml: function() {
                return "<a></a><a></a>";
            }
        });
    }
    var widgetsSpinner = $.ui.spinner;
    $.widget("ui.tabs", {
        version: "1.12.1",
        delay: 300,
        options: {
            active: null,
            classes: {
                "ui-tabs": "ui-corner-all",
                "ui-tabs-nav": "ui-corner-all",
                "ui-tabs-panel": "ui-corner-bottom",
                "ui-tabs-tab": "ui-corner-top"
            },
            collapsible: false,
            event: "click",
            heightStyle: "content",
            hide: null,
            show: null,
            activate: null,
            beforeActivate: null,
            beforeLoad: null,
            load: null
        },
        _isLocal: function() {
            var rhash = /#.*$/;
            return function(anchor) {
                var anchorUrl, locationUrl;
                anchorUrl = anchor.href.replace(rhash, "");
                locationUrl = location.href.replace(rhash, "");
                try {
                    anchorUrl = decodeURIComponent(anchorUrl);
                } catch (error) {}
                try {
                    locationUrl = decodeURIComponent(locationUrl);
                } catch (error) {}
                return anchor.hash.length > 1 && anchorUrl === locationUrl;
            };
        }(),
        _create: function() {
            var that = this, options = this.options;
            this.running = false;
            this._addClass("ui-tabs", "ui-widget ui-widget-content");
            this._toggleClass("ui-tabs-collapsible", null, options.collapsible);
            this._processTabs();
            options.active = this._initialActive();
            if ($.isArray(options.disabled)) {
                options.disabled = $.unique(options.disabled.concat($.map(this.tabs.filter(".ui-state-disabled"), function(li) {
                    return that.tabs.index(li);
                }))).sort();
            }
            if (this.options.active !== false && this.anchors.length) {
                this.active = this._findActive(options.active);
            } else {
                this.active = $();
            }
            this._refresh();
            if (this.active.length) {
                this.load(options.active);
            }
        },
        _initialActive: function() {
            var active = this.options.active, collapsible = this.options.collapsible, locationHash = location.hash.substring(1);
            if (active === null) {
                if (locationHash) {
                    this.tabs.each(function(i, tab) {
                        if ($(tab).attr("aria-controls") === locationHash) {
                            active = i;
                            return false;
                        }
                    });
                }
                if (active === null) {
                    active = this.tabs.index(this.tabs.filter(".ui-tabs-active"));
                }
                if (active === null || active === -1) {
                    active = this.tabs.length ? 0 : false;
                }
            }
            if (active !== false) {
                active = this.tabs.index(this.tabs.eq(active));
                if (active === -1) {
                    active = collapsible ? false : 0;
                }
            }
            if (!collapsible && active === false && this.anchors.length) {
                active = 0;
            }
            return active;
        },
        _getCreateEventData: function() {
            return {
                tab: this.active,
                panel: !this.active.length ? $() : this._getPanelForTab(this.active)
            };
        },
        _tabKeydown: function(event) {
            var focusedTab = $($.ui.safeActiveElement(this.document[0])).closest("li"), selectedIndex = this.tabs.index(focusedTab), goingForward = true;
            if (this._handlePageNav(event)) {
                return;
            }
            switch (event.keyCode) {
              case $.ui.keyCode.RIGHT:
              case $.ui.keyCode.DOWN:
                selectedIndex++;
                break;

              case $.ui.keyCode.UP:
              case $.ui.keyCode.LEFT:
                goingForward = false;
                selectedIndex--;
                break;

              case $.ui.keyCode.END:
                selectedIndex = this.anchors.length - 1;
                break;

              case $.ui.keyCode.HOME:
                selectedIndex = 0;
                break;

              case $.ui.keyCode.SPACE:
                event.preventDefault();
                clearTimeout(this.activating);
                this._activate(selectedIndex);
                return;

              case $.ui.keyCode.ENTER:
                event.preventDefault();
                clearTimeout(this.activating);
                this._activate(selectedIndex === this.options.active ? false : selectedIndex);
                return;

              default:
                return;
            }
            event.preventDefault();
            clearTimeout(this.activating);
            selectedIndex = this._focusNextTab(selectedIndex, goingForward);
            if (!event.ctrlKey && !event.metaKey) {
                focusedTab.attr("aria-selected", "false");
                this.tabs.eq(selectedIndex).attr("aria-selected", "true");
                this.activating = this._delay(function() {
                    this.option("active", selectedIndex);
                }, this.delay);
            }
        },
        _panelKeydown: function(event) {
            if (this._handlePageNav(event)) {
                return;
            }
            if (event.ctrlKey && event.keyCode === $.ui.keyCode.UP) {
                event.preventDefault();
                this.active.trigger("focus");
            }
        },
        _handlePageNav: function(event) {
            if (event.altKey && event.keyCode === $.ui.keyCode.PAGE_UP) {
                this._activate(this._focusNextTab(this.options.active - 1, false));
                return true;
            }
            if (event.altKey && event.keyCode === $.ui.keyCode.PAGE_DOWN) {
                this._activate(this._focusNextTab(this.options.active + 1, true));
                return true;
            }
        },
        _findNextTab: function(index, goingForward) {
            var lastTabIndex = this.tabs.length - 1;
            function constrain() {
                if (index > lastTabIndex) {
                    index = 0;
                }
                if (index < 0) {
                    index = lastTabIndex;
                }
                return index;
            }
            while ($.inArray(constrain(), this.options.disabled) !== -1) {
                index = goingForward ? index + 1 : index - 1;
            }
            return index;
        },
        _focusNextTab: function(index, goingForward) {
            index = this._findNextTab(index, goingForward);
            this.tabs.eq(index).trigger("focus");
            return index;
        },
        _setOption: function(key, value) {
            if (key === "active") {
                this._activate(value);
                return;
            }
            this._super(key, value);
            if (key === "collapsible") {
                this._toggleClass("ui-tabs-collapsible", null, value);
                if (!value && this.options.active === false) {
                    this._activate(0);
                }
            }
            if (key === "event") {
                this._setupEvents(value);
            }
            if (key === "heightStyle") {
                this._setupHeightStyle(value);
            }
        },
        _sanitizeSelector: function(hash) {
            return hash ? hash.replace(/[!"$%&'()*+,.\/:;<=>?@\[\]\^`{|}~]/g, "\\$&") : "";
        },
        refresh: function() {
            var options = this.options, lis = this.tablist.children(":has(a[href])");
            options.disabled = $.map(lis.filter(".ui-state-disabled"), function(tab) {
                return lis.index(tab);
            });
            this._processTabs();
            if (options.active === false || !this.anchors.length) {
                options.active = false;
                this.active = $();
            } else if (this.active.length && !$.contains(this.tablist[0], this.active[0])) {
                if (this.tabs.length === options.disabled.length) {
                    options.active = false;
                    this.active = $();
                } else {
                    this._activate(this._findNextTab(Math.max(0, options.active - 1), false));
                }
            } else {
                options.active = this.tabs.index(this.active);
            }
            this._refresh();
        },
        _refresh: function() {
            this._setOptionDisabled(this.options.disabled);
            this._setupEvents(this.options.event);
            this._setupHeightStyle(this.options.heightStyle);
            this.tabs.not(this.active).attr({
                "aria-selected": "false",
                "aria-expanded": "false",
                tabIndex: -1
            });
            this.panels.not(this._getPanelForTab(this.active)).hide().attr({
                "aria-hidden": "true"
            });
            if (!this.active.length) {
                this.tabs.eq(0).attr("tabIndex", 0);
            } else {
                this.active.attr({
                    "aria-selected": "true",
                    "aria-expanded": "true",
                    tabIndex: 0
                });
                this._addClass(this.active, "ui-tabs-active", "ui-state-active");
                this._getPanelForTab(this.active).show().attr({
                    "aria-hidden": "false"
                });
            }
        },
        _processTabs: function() {
            var that = this, prevTabs = this.tabs, prevAnchors = this.anchors, prevPanels = this.panels;
            this.tablist = this._getList().attr("role", "tablist");
            this._addClass(this.tablist, "ui-tabs-nav", "ui-helper-reset ui-helper-clearfix ui-widget-header");
            this.tablist.on("mousedown" + this.eventNamespace, "> li", function(event) {
                if ($(this).is(".ui-state-disabled")) {
                    event.preventDefault();
                }
            }).on("focus" + this.eventNamespace, ".ui-tabs-anchor", function() {
                if ($(this).closest("li").is(".ui-state-disabled")) {
                    this.blur();
                }
            });
            this.tabs = this.tablist.find("> li:has(a[href])").attr({
                role: "tab",
                tabIndex: -1
            });
            this._addClass(this.tabs, "ui-tabs-tab", "ui-state-default");
            this.anchors = this.tabs.map(function() {
                return $("a", this)[0];
            }).attr({
                role: "presentation",
                tabIndex: -1
            });
            this._addClass(this.anchors, "ui-tabs-anchor");
            this.panels = $();
            this.anchors.each(function(i, anchor) {
                var selector, panel, panelId, anchorId = $(anchor).uniqueId().attr("id"), tab = $(anchor).closest("li"), originalAriaControls = tab.attr("aria-controls");
                if (that._isLocal(anchor)) {
                    selector = anchor.hash;
                    panelId = selector.substring(1);
                    panel = that.element.find(that._sanitizeSelector(selector));
                } else {
                    panelId = tab.attr("aria-controls") || $({}).uniqueId()[0].id;
                    selector = "#" + panelId;
                    panel = that.element.find(selector);
                    if (!panel.length) {
                        panel = that._createPanel(panelId);
                        panel.insertAfter(that.panels[i - 1] || that.tablist);
                    }
                    panel.attr("aria-live", "polite");
                }
                if (panel.length) {
                    that.panels = that.panels.add(panel);
                }
                if (originalAriaControls) {
                    tab.data("ui-tabs-aria-controls", originalAriaControls);
                }
                tab.attr({
                    "aria-controls": panelId,
                    "aria-labelledby": anchorId
                });
                panel.attr("aria-labelledby", anchorId);
            });
            this.panels.attr("role", "tabpanel");
            this._addClass(this.panels, "ui-tabs-panel", "ui-widget-content");
            if (prevTabs) {
                this._off(prevTabs.not(this.tabs));
                this._off(prevAnchors.not(this.anchors));
                this._off(prevPanels.not(this.panels));
            }
        },
        _getList: function() {
            return this.tablist || this.element.find("ol, ul").eq(0);
        },
        _createPanel: function(id) {
            return $("<div>").attr("id", id).data("ui-tabs-destroy", true);
        },
        _setOptionDisabled: function(disabled) {
            var currentItem, li, i;
            if ($.isArray(disabled)) {
                if (!disabled.length) {
                    disabled = false;
                } else if (disabled.length === this.anchors.length) {
                    disabled = true;
                }
            }
            for (i = 0; li = this.tabs[i]; i++) {
                currentItem = $(li);
                if (disabled === true || $.inArray(i, disabled) !== -1) {
                    currentItem.attr("aria-disabled", "true");
                    this._addClass(currentItem, null, "ui-state-disabled");
                } else {
                    currentItem.removeAttr("aria-disabled");
                    this._removeClass(currentItem, null, "ui-state-disabled");
                }
            }
            this.options.disabled = disabled;
            this._toggleClass(this.widget(), this.widgetFullName + "-disabled", null, disabled === true);
        },
        _setupEvents: function(event) {
            var events = {};
            if (event) {
                $.each(event.split(" "), function(index, eventName) {
                    events[eventName] = "_eventHandler";
                });
            }
            this._off(this.anchors.add(this.tabs).add(this.panels));
            this._on(true, this.anchors, {
                click: function(event) {
                    event.preventDefault();
                }
            });
            this._on(this.anchors, events);
            this._on(this.tabs, {
                keydown: "_tabKeydown"
            });
            this._on(this.panels, {
                keydown: "_panelKeydown"
            });
            this._focusable(this.tabs);
            this._hoverable(this.tabs);
        },
        _setupHeightStyle: function(heightStyle) {
            var maxHeight, parent = this.element.parent();
            if (heightStyle === "fill") {
                maxHeight = parent.height();
                maxHeight -= this.element.outerHeight() - this.element.height();
                this.element.siblings(":visible").each(function() {
                    var elem = $(this), position = elem.css("position");
                    if (position === "absolute" || position === "fixed") {
                        return;
                    }
                    maxHeight -= elem.outerHeight(true);
                });
                this.element.children().not(this.panels).each(function() {
                    maxHeight -= $(this).outerHeight(true);
                });
                this.panels.each(function() {
                    $(this).height(Math.max(0, maxHeight - $(this).innerHeight() + $(this).height()));
                }).css("overflow", "auto");
            } else if (heightStyle === "auto") {
                maxHeight = 0;
                this.panels.each(function() {
                    maxHeight = Math.max(maxHeight, $(this).height("").height());
                }).height(maxHeight);
            }
        },
        _eventHandler: function(event) {
            var options = this.options, active = this.active, anchor = $(event.currentTarget), tab = anchor.closest("li"), clickedIsActive = tab[0] === active[0], collapsing = clickedIsActive && options.collapsible, toShow = collapsing ? $() : this._getPanelForTab(tab), toHide = !active.length ? $() : this._getPanelForTab(active), eventData = {
                oldTab: active,
                oldPanel: toHide,
                newTab: collapsing ? $() : tab,
                newPanel: toShow
            };
            event.preventDefault();
            if (tab.hasClass("ui-state-disabled") || tab.hasClass("ui-tabs-loading") || this.running || clickedIsActive && !options.collapsible || this._trigger("beforeActivate", event, eventData) === false) {
                return;
            }
            options.active = collapsing ? false : this.tabs.index(tab);
            this.active = clickedIsActive ? $() : tab;
            if (this.xhr) {
                this.xhr.abort();
            }
            if (!toHide.length && !toShow.length) {
                $.error("jQuery UI Tabs: Mismatching fragment identifier.");
            }
            if (toShow.length) {
                this.load(this.tabs.index(tab), event);
            }
            this._toggle(event, eventData);
        },
        _toggle: function(event, eventData) {
            var that = this, toShow = eventData.newPanel, toHide = eventData.oldPanel;
            this.running = true;
            function complete() {
                that.running = false;
                that._trigger("activate", event, eventData);
            }
            function show() {
                that._addClass(eventData.newTab.closest("li"), "ui-tabs-active", "ui-state-active");
                if (toShow.length && that.options.show) {
                    that._show(toShow, that.options.show, complete);
                } else {
                    toShow.show();
                    complete();
                }
            }
            if (toHide.length && this.options.hide) {
                this._hide(toHide, this.options.hide, function() {
                    that._removeClass(eventData.oldTab.closest("li"), "ui-tabs-active", "ui-state-active");
                    show();
                });
            } else {
                this._removeClass(eventData.oldTab.closest("li"), "ui-tabs-active", "ui-state-active");
                toHide.hide();
                show();
            }
            toHide.attr("aria-hidden", "true");
            eventData.oldTab.attr({
                "aria-selected": "false",
                "aria-expanded": "false"
            });
            if (toShow.length && toHide.length) {
                eventData.oldTab.attr("tabIndex", -1);
            } else if (toShow.length) {
                this.tabs.filter(function() {
                    return $(this).attr("tabIndex") === 0;
                }).attr("tabIndex", -1);
            }
            toShow.attr("aria-hidden", "false");
            eventData.newTab.attr({
                "aria-selected": "true",
                "aria-expanded": "true",
                tabIndex: 0
            });
        },
        _activate: function(index) {
            var anchor, active = this._findActive(index);
            if (active[0] === this.active[0]) {
                return;
            }
            if (!active.length) {
                active = this.active;
            }
            anchor = active.find(".ui-tabs-anchor")[0];
            this._eventHandler({
                target: anchor,
                currentTarget: anchor,
                preventDefault: $.noop
            });
        },
        _findActive: function(index) {
            return index === false ? $() : this.tabs.eq(index);
        },
        _getIndex: function(index) {
            if (typeof index === "string") {
                index = this.anchors.index(this.anchors.filter("[href$='" + $.ui.escapeSelector(index) + "']"));
            }
            return index;
        },
        _destroy: function() {
            if (this.xhr) {
                this.xhr.abort();
            }
            this.tablist.removeAttr("role").off(this.eventNamespace);
            this.anchors.removeAttr("role tabIndex").removeUniqueId();
            this.tabs.add(this.panels).each(function() {
                if ($.data(this, "ui-tabs-destroy")) {
                    $(this).remove();
                } else {
                    $(this).removeAttr("role tabIndex " + "aria-live aria-busy aria-selected aria-labelledby aria-hidden aria-expanded");
                }
            });
            this.tabs.each(function() {
                var li = $(this), prev = li.data("ui-tabs-aria-controls");
                if (prev) {
                    li.attr("aria-controls", prev).removeData("ui-tabs-aria-controls");
                } else {
                    li.removeAttr("aria-controls");
                }
            });
            this.panels.show();
            if (this.options.heightStyle !== "content") {
                this.panels.css("height", "");
            }
        },
        enable: function(index) {
            var disabled = this.options.disabled;
            if (disabled === false) {
                return;
            }
            if (index === undefined) {
                disabled = false;
            } else {
                index = this._getIndex(index);
                if ($.isArray(disabled)) {
                    disabled = $.map(disabled, function(num) {
                        return num !== index ? num : null;
                    });
                } else {
                    disabled = $.map(this.tabs, function(li, num) {
                        return num !== index ? num : null;
                    });
                }
            }
            this._setOptionDisabled(disabled);
        },
        disable: function(index) {
            var disabled = this.options.disabled;
            if (disabled === true) {
                return;
            }
            if (index === undefined) {
                disabled = true;
            } else {
                index = this._getIndex(index);
                if ($.inArray(index, disabled) !== -1) {
                    return;
                }
                if ($.isArray(disabled)) {
                    disabled = $.merge([ index ], disabled).sort();
                } else {
                    disabled = [ index ];
                }
            }
            this._setOptionDisabled(disabled);
        },
        load: function(index, event) {
            index = this._getIndex(index);
            var that = this, tab = this.tabs.eq(index), anchor = tab.find(".ui-tabs-anchor"), panel = this._getPanelForTab(tab), eventData = {
                tab: tab,
                panel: panel
            }, complete = function(jqXHR, status) {
                if (status === "abort") {
                    that.panels.stop(false, true);
                }
                that._removeClass(tab, "ui-tabs-loading");
                panel.removeAttr("aria-busy");
                if (jqXHR === that.xhr) {
                    delete that.xhr;
                }
            };
            if (this._isLocal(anchor[0])) {
                return;
            }
            this.xhr = $.ajax(this._ajaxSettings(anchor, event, eventData));
            if (this.xhr && this.xhr.statusText !== "canceled") {
                this._addClass(tab, "ui-tabs-loading");
                panel.attr("aria-busy", "true");
                this.xhr.done(function(response, status, jqXHR) {
                    setTimeout(function() {
                        panel.html(response);
                        that._trigger("load", event, eventData);
                        complete(jqXHR, status);
                    }, 1);
                }).fail(function(jqXHR, status) {
                    setTimeout(function() {
                        complete(jqXHR, status);
                    }, 1);
                });
            }
        },
        _ajaxSettings: function(anchor, event, eventData) {
            var that = this;
            return {
                url: anchor.attr("href").replace(/#.*$/, ""),
                beforeSend: function(jqXHR, settings) {
                    return that._trigger("beforeLoad", event, $.extend({
                        jqXHR: jqXHR,
                        ajaxSettings: settings
                    }, eventData));
                }
            };
        },
        _getPanelForTab: function(tab) {
            var id = $(tab).attr("aria-controls");
            return this.element.find(this._sanitizeSelector("#" + id));
        }
    });
    if ($.uiBackCompat !== false) {
        $.widget("ui.tabs", $.ui.tabs, {
            _processTabs: function() {
                this._superApply(arguments);
                this._addClass(this.tabs, "ui-tab");
            }
        });
    }
    var widgetsTabs = $.ui.tabs;
    $.widget("ui.tooltip", {
        version: "1.12.1",
        options: {
            classes: {
                "ui-tooltip": "ui-corner-all ui-widget-shadow"
            },
            content: function() {
                var title = $(this).attr("title") || "";
                return $("<a>").text(title).html();
            },
            hide: true,
            items: "[title]:not([disabled])",
            position: {
                my: "left top+15",
                at: "left bottom",
                collision: "flipfit flip"
            },
            show: true,
            track: false,
            close: null,
            open: null
        },
        _addDescribedBy: function(elem, id) {
            var describedby = (elem.attr("aria-describedby") || "").split(/\s+/);
            describedby.push(id);
            elem.data("ui-tooltip-id", id).attr("aria-describedby", $.trim(describedby.join(" ")));
        },
        _removeDescribedBy: function(elem) {
            var id = elem.data("ui-tooltip-id"), describedby = (elem.attr("aria-describedby") || "").split(/\s+/), index = $.inArray(id, describedby);
            if (index !== -1) {
                describedby.splice(index, 1);
            }
            elem.removeData("ui-tooltip-id");
            describedby = $.trim(describedby.join(" "));
            if (describedby) {
                elem.attr("aria-describedby", describedby);
            } else {
                elem.removeAttr("aria-describedby");
            }
        },
        _create: function() {
            this._on({
                mouseover: "open",
                focusin: "open"
            });
            this.tooltips = {};
            this.parents = {};
            this.liveRegion = $("<div>").attr({
                role: "log",
                "aria-live": "assertive",
                "aria-relevant": "additions"
            }).appendTo(this.document[0].body);
            this._addClass(this.liveRegion, null, "ui-helper-hidden-accessible");
            this.disabledTitles = $([]);
        },
        _setOption: function(key, value) {
            var that = this;
            this._super(key, value);
            if (key === "content") {
                $.each(this.tooltips, function(id, tooltipData) {
                    that._updateContent(tooltipData.element);
                });
            }
        },
        _setOptionDisabled: function(value) {
            this[value ? "_disable" : "_enable"]();
        },
        _disable: function() {
            var that = this;
            $.each(this.tooltips, function(id, tooltipData) {
                var event = $.Event("blur");
                event.target = event.currentTarget = tooltipData.element[0];
                that.close(event, true);
            });
            this.disabledTitles = this.disabledTitles.add(this.element.find(this.options.items).addBack().filter(function() {
                var element = $(this);
                if (element.is("[title]")) {
                    return element.data("ui-tooltip-title", element.attr("title")).removeAttr("title");
                }
            }));
        },
        _enable: function() {
            this.disabledTitles.each(function() {
                var element = $(this);
                if (element.data("ui-tooltip-title")) {
                    element.attr("title", element.data("ui-tooltip-title"));
                }
            });
            this.disabledTitles = $([]);
        },
        open: function(event) {
            var that = this, target = $(event ? event.target : this.element).closest(this.options.items);
            if (!target.length || target.data("ui-tooltip-id")) {
                return;
            }
            if (target.attr("title")) {
                target.data("ui-tooltip-title", target.attr("title"));
            }
            target.data("ui-tooltip-open", true);
            if (event && event.type === "mouseover") {
                target.parents().each(function() {
                    var parent = $(this), blurEvent;
                    if (parent.data("ui-tooltip-open")) {
                        blurEvent = $.Event("blur");
                        blurEvent.target = blurEvent.currentTarget = this;
                        that.close(blurEvent, true);
                    }
                    if (parent.attr("title")) {
                        parent.uniqueId();
                        that.parents[this.id] = {
                            element: this,
                            title: parent.attr("title")
                        };
                        parent.attr("title", "");
                    }
                });
            }
            this._registerCloseHandlers(event, target);
            this._updateContent(target, event);
        },
        _updateContent: function(target, event) {
            var content, contentOption = this.options.content, that = this, eventType = event ? event.type : null;
            if (typeof contentOption === "string" || contentOption.nodeType || contentOption.jquery) {
                return this._open(event, target, contentOption);
            }
            content = contentOption.call(target[0], function(response) {
                that._delay(function() {
                    if (!target.data("ui-tooltip-open")) {
                        return;
                    }
                    if (event) {
                        event.type = eventType;
                    }
                    this._open(event, target, response);
                });
            });
            if (content) {
                this._open(event, target, content);
            }
        },
        _open: function(event, target, content) {
            var tooltipData, tooltip, delayedShow, a11yContent, positionOption = $.extend({}, this.options.position);
            if (!content) {
                return;
            }
            tooltipData = this._find(target);
            if (tooltipData) {
                tooltipData.tooltip.find(".ui-tooltip-content").html(content);
                return;
            }
            if (target.is("[title]")) {
                if (event && event.type === "mouseover") {
                    target.attr("title", "");
                } else {
                    target.removeAttr("title");
                }
            }
            tooltipData = this._tooltip(target);
            tooltip = tooltipData.tooltip;
            this._addDescribedBy(target, tooltip.attr("id"));
            tooltip.find(".ui-tooltip-content").html(content);
            this.liveRegion.children().hide();
            a11yContent = $("<div>").html(tooltip.find(".ui-tooltip-content").html());
            a11yContent.removeAttr("name").find("[name]").removeAttr("name");
            a11yContent.removeAttr("id").find("[id]").removeAttr("id");
            a11yContent.appendTo(this.liveRegion);
            function position(event) {
                positionOption.of = event;
                if (tooltip.is(":hidden")) {
                    return;
                }
                tooltip.position(positionOption);
            }
            if (this.options.track && event && /^mouse/.test(event.type)) {
                this._on(this.document, {
                    mousemove: position
                });
                position(event);
            } else {
                tooltip.position($.extend({
                    of: target
                }, this.options.position));
            }
            tooltip.hide();
            this._show(tooltip, this.options.show);
            if (this.options.track && this.options.show && this.options.show.delay) {
                delayedShow = this.delayedShow = setInterval(function() {
                    if (tooltip.is(":visible")) {
                        position(positionOption.of);
                        clearInterval(delayedShow);
                    }
                }, $.fx.interval);
            }
            this._trigger("open", event, {
                tooltip: tooltip
            });
        },
        _registerCloseHandlers: function(event, target) {
            var events = {
                keyup: function(event) {
                    if (event.keyCode === $.ui.keyCode.ESCAPE) {
                        var fakeEvent = $.Event(event);
                        fakeEvent.currentTarget = target[0];
                        this.close(fakeEvent, true);
                    }
                }
            };
            if (target[0] !== this.element[0]) {
                events.remove = function() {
                    this._removeTooltip(this._find(target).tooltip);
                };
            }
            if (!event || event.type === "mouseover") {
                events.mouseleave = "close";
            }
            if (!event || event.type === "focusin") {
                events.focusout = "close";
            }
            this._on(true, target, events);
        },
        close: function(event) {
            var tooltip, that = this, target = $(event ? event.currentTarget : this.element), tooltipData = this._find(target);
            if (!tooltipData) {
                target.removeData("ui-tooltip-open");
                return;
            }
            tooltip = tooltipData.tooltip;
            if (tooltipData.closing) {
                return;
            }
            clearInterval(this.delayedShow);
            if (target.data("ui-tooltip-title") && !target.attr("title")) {
                target.attr("title", target.data("ui-tooltip-title"));
            }
            this._removeDescribedBy(target);
            tooltipData.hiding = true;
            tooltip.stop(true);
            this._hide(tooltip, this.options.hide, function() {
                that._removeTooltip($(this));
            });
            target.removeData("ui-tooltip-open");
            this._off(target, "mouseleave focusout keyup");
            if (target[0] !== this.element[0]) {
                this._off(target, "remove");
            }
            this._off(this.document, "mousemove");
            if (event && event.type === "mouseleave") {
                $.each(this.parents, function(id, parent) {
                    $(parent.element).attr("title", parent.title);
                    delete that.parents[id];
                });
            }
            tooltipData.closing = true;
            this._trigger("close", event, {
                tooltip: tooltip
            });
            if (!tooltipData.hiding) {
                tooltipData.closing = false;
            }
        },
        _tooltip: function(element) {
            var tooltip = $("<div>").attr("role", "tooltip"), content = $("<div>").appendTo(tooltip), id = tooltip.uniqueId().attr("id");
            this._addClass(content, "ui-tooltip-content");
            this._addClass(tooltip, "ui-tooltip", "ui-widget ui-widget-content");
            tooltip.appendTo(this._appendTo(element));
            return this.tooltips[id] = {
                element: element,
                tooltip: tooltip
            };
        },
        _find: function(target) {
            var id = target.data("ui-tooltip-id");
            return id ? this.tooltips[id] : null;
        },
        _removeTooltip: function(tooltip) {
            tooltip.remove();
            delete this.tooltips[tooltip.attr("id")];
        },
        _appendTo: function(target) {
            var element = target.closest(".ui-front, dialog");
            if (!element.length) {
                element = this.document[0].body;
            }
            return element;
        },
        _destroy: function() {
            var that = this;
            $.each(this.tooltips, function(id, tooltipData) {
                var event = $.Event("blur"), element = tooltipData.element;
                event.target = event.currentTarget = element[0];
                that.close(event, true);
                $("#" + id).remove();
                if (element.data("ui-tooltip-title")) {
                    if (!element.attr("title")) {
                        element.attr("title", element.data("ui-tooltip-title"));
                    }
                    element.removeData("ui-tooltip-title");
                }
            });
            this.liveRegion.remove();
        }
    });
    if ($.uiBackCompat !== false) {
        $.widget("ui.tooltip", $.ui.tooltip, {
            options: {
                tooltipClass: null
            },
            _tooltip: function() {
                var tooltipData = this._superApply(arguments);
                if (this.options.tooltipClass) {
                    tooltipData.tooltip.addClass(this.options.tooltipClass);
                }
                return tooltipData;
            }
        });
    }
    var widgetsTooltip = $.ui.tooltip;
});

(function($) {
    var ColorPicker = function() {
        var ids = {}, inAction, charMin = 65, visible, tpl = '<div class="colorpicker"><div class="colorpicker_color"><div><div></div></div></div><div class="colorpicker_hue"><div></div></div><div class="colorpicker_new_color"></div><div class="colorpicker_current_color"></div><div class="colorpicker_hex"><input type="text" maxlength="6" size="6" /></div><div class="colorpicker_rgb_r colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_g colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_rgb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_h colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_s colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_hsb_b colorpicker_field"><input type="text" maxlength="3" size="3" /><span></span></div><div class="colorpicker_submit"></div></div>', defaults = {
            eventName: "click",
            onShow: function() {},
            onBeforeShow: function() {},
            onHide: function() {},
            onChange: function() {},
            onSubmit: function() {},
            color: "ff0000",
            livePreview: true,
            flat: false
        }, fillRGBFields = function(hsb, cal) {
            var rgb = HSBToRGB(hsb);
            $(cal).data("colorpicker").fields.eq(1).val(rgb.r).end().eq(2).val(rgb.g).end().eq(3).val(rgb.b).end();
        }, fillHSBFields = function(hsb, cal) {
            $(cal).data("colorpicker").fields.eq(4).val(hsb.h).end().eq(5).val(hsb.s).end().eq(6).val(hsb.b).end();
        }, fillHexFields = function(hsb, cal) {
            $(cal).data("colorpicker").fields.eq(0).val(HSBToHex(hsb)).end();
        }, setSelector = function(hsb, cal) {
            $(cal).data("colorpicker").selector.css("backgroundColor", "#" + HSBToHex({
                h: hsb.h,
                s: 100,
                b: 100
            }));
            $(cal).data("colorpicker").selectorIndic.css({
                left: parseInt(150 * hsb.s / 100, 10),
                top: parseInt(150 * (100 - hsb.b) / 100, 10)
            });
        }, setHue = function(hsb, cal) {
            $(cal).data("colorpicker").hue.css("top", parseInt(150 - 150 * hsb.h / 360, 10));
        }, setCurrentColor = function(hsb, cal) {
            $(cal).data("colorpicker").currentColor.css("backgroundColor", "#" + HSBToHex(hsb));
        }, setNewColor = function(hsb, cal) {
            $(cal).data("colorpicker").newColor.css("backgroundColor", "#" + HSBToHex(hsb));
        }, keyDown = function(ev) {
            var pressedKey = ev.charCode || ev.keyCode || -1;
            if (pressedKey > charMin && pressedKey <= 90 || pressedKey == 32) {
                return false;
            }
            var cal = $(this).parent().parent();
            if (cal.data("colorpicker").livePreview === true) {
                change.apply(this);
            }
        }, change = function(ev) {
            var cal = $(this).parent().parent(), col;
            if (this.parentNode.className.indexOf("_hex") > 0) {
                cal.data("colorpicker").color = col = HexToHSB(fixHex(this.value));
            } else if (this.parentNode.className.indexOf("_hsb") > 0) {
                cal.data("colorpicker").color = col = fixHSB({
                    h: parseInt(cal.data("colorpicker").fields.eq(4).val(), 10),
                    s: parseInt(cal.data("colorpicker").fields.eq(5).val(), 10),
                    b: parseInt(cal.data("colorpicker").fields.eq(6).val(), 10)
                });
            } else {
                cal.data("colorpicker").color = col = RGBToHSB(fixRGB({
                    r: parseInt(cal.data("colorpicker").fields.eq(1).val(), 10),
                    g: parseInt(cal.data("colorpicker").fields.eq(2).val(), 10),
                    b: parseInt(cal.data("colorpicker").fields.eq(3).val(), 10)
                }));
            }
            if (ev) {
                fillRGBFields(col, cal.get(0));
                fillHexFields(col, cal.get(0));
                fillHSBFields(col, cal.get(0));
            }
            setSelector(col, cal.get(0));
            setHue(col, cal.get(0));
            setNewColor(col, cal.get(0));
            cal.data("colorpicker").onChange.apply(cal, [ col, HSBToHex(col), HSBToRGB(col) ]);
        }, blur = function(ev) {
            var cal = $(this).parent().parent();
            cal.data("colorpicker").fields.parent().removeClass("colorpicker_focus");
        }, focus = function() {
            charMin = this.parentNode.className.indexOf("_hex") > 0 ? 70 : 65;
            $(this).parent().parent().data("colorpicker").fields.parent().removeClass("colorpicker_focus");
            $(this).parent().addClass("colorpicker_focus");
        }, downIncrement = function(ev) {
            var field = $(this).parent().find("input").focus();
            var current = {
                el: $(this).parent().addClass("colorpicker_slider"),
                max: this.parentNode.className.indexOf("_hsb_h") > 0 ? 360 : this.parentNode.className.indexOf("_hsb") > 0 ? 100 : 255,
                y: ev.pageY,
                field: field,
                val: parseInt(field.val(), 10),
                preview: $(this).parent().parent().data("colorpicker").livePreview
            };
            $(document).bind("mouseup", current, upIncrement);
            $(document).bind("mousemove", current, moveIncrement);
        }, moveIncrement = function(ev) {
            ev.data.field.val(Math.max(0, Math.min(ev.data.max, parseInt(ev.data.val + ev.pageY - ev.data.y, 10))));
            if (ev.data.preview) {
                change.apply(ev.data.field.get(0), [ true ]);
            }
            return false;
        }, upIncrement = function(ev) {
            change.apply(ev.data.field.get(0), [ true ]);
            ev.data.el.removeClass("colorpicker_slider").find("input").focus();
            $(document).unbind("mouseup", upIncrement);
            $(document).unbind("mousemove", moveIncrement);
            return false;
        }, downHue = function(ev) {
            var current = {
                cal: $(this).parent(),
                y: $(this).offset().top
            };
            current.preview = current.cal.data("colorpicker").livePreview;
            $(document).bind("mouseup", current, upHue);
            $(document).bind("mousemove", current, moveHue);
        }, moveHue = function(ev) {
            change.apply(ev.data.cal.data("colorpicker").fields.eq(4).val(parseInt(360 * (150 - Math.max(0, Math.min(150, ev.pageY - ev.data.y))) / 150, 10)).get(0), [ ev.data.preview ]);
            return false;
        }, upHue = function(ev) {
            fillRGBFields(ev.data.cal.data("colorpicker").color, ev.data.cal.get(0));
            fillHexFields(ev.data.cal.data("colorpicker").color, ev.data.cal.get(0));
            $(document).unbind("mouseup", upHue);
            $(document).unbind("mousemove", moveHue);
            return false;
        }, downSelector = function(ev) {
            var current = {
                cal: $(this).parent(),
                pos: $(this).offset()
            };
            current.preview = current.cal.data("colorpicker").livePreview;
            $(document).bind("mouseup", current, upSelector);
            $(document).bind("mousemove", current, moveSelector);
        }, moveSelector = function(ev) {
            change.apply(ev.data.cal.data("colorpicker").fields.eq(6).val(parseInt(100 * (150 - Math.max(0, Math.min(150, ev.pageY - ev.data.pos.top))) / 150, 10)).end().eq(5).val(parseInt(100 * Math.max(0, Math.min(150, ev.pageX - ev.data.pos.left)) / 150, 10)).get(0), [ ev.data.preview ]);
            return false;
        }, upSelector = function(ev) {
            fillRGBFields(ev.data.cal.data("colorpicker").color, ev.data.cal.get(0));
            fillHexFields(ev.data.cal.data("colorpicker").color, ev.data.cal.get(0));
            $(document).unbind("mouseup", upSelector);
            $(document).unbind("mousemove", moveSelector);
            return false;
        }, enterSubmit = function(ev) {
            $(this).addClass("colorpicker_focus");
        }, leaveSubmit = function(ev) {
            $(this).removeClass("colorpicker_focus");
        }, clickSubmit = function(ev) {
            var cal = $(this).parent();
            var col = cal.data("colorpicker").color;
            cal.data("colorpicker").origColor = col;
            setCurrentColor(col, cal.get(0));
            cal.data("colorpicker").onSubmit(col, HSBToHex(col), HSBToRGB(col), cal.data("colorpicker").el);
        }, show = function(ev) {
            var cal = $("#" + $(this).data("colorpickerId"));
            cal.data("colorpicker").onBeforeShow.apply(this, [ cal.get(0) ]);
            var pos = $(this).offset();
            var viewPort = getViewport();
            var top = ev.clientY;
            var left = pos.left + 40;
            if (top + 176 > viewPort.h) {
                top -= 176;
            }
            if (left + 356 > viewPort.l + viewPort.w) {
                left -= 356;
            }
            cal.css({
                left: left + "px",
                top: top + "px"
            });
            if (cal.data("colorpicker").onShow.apply(this, [ cal.get(0) ]) != false) {
                cal.show();
            }
            $(document).bind("mousedown", {
                cal: cal
            }, hide);
            return false;
        }, hide = function(ev) {
            if (!isChildOf(ev.data.cal.get(0), ev.target, ev.data.cal.get(0))) {
                if (ev.data.cal.data("colorpicker").onHide.apply(this, [ ev.data.cal.get(0) ]) != false) {
                    ev.data.cal.hide();
                }
                $(document).unbind("mousedown", hide);
            }
        }, isChildOf = function(parentEl, el, container) {
            if (parentEl == el) {
                return true;
            }
            if (parentEl.contains) {
                return parentEl.contains(el);
            }
            if (parentEl.compareDocumentPosition) {
                return !!(parentEl.compareDocumentPosition(el) & 16);
            }
            var prEl = el.parentNode;
            while (prEl && prEl != container) {
                if (prEl == parentEl) return true;
                prEl = prEl.parentNode;
            }
            return false;
        }, getViewport = function() {
            var m = document.compatMode == "CSS1Compat";
            return {
                l: window.pageXOffset || (m ? document.documentElement.scrollLeft : document.body.scrollLeft),
                t: window.pageYOffset || (m ? document.documentElement.scrollTop : document.body.scrollTop),
                w: window.innerWidth || (m ? document.documentElement.clientWidth : document.body.clientWidth),
                h: window.innerHeight || (m ? document.documentElement.clientHeight : document.body.clientHeight)
            };
        }, fixHSB = function(hsb) {
            return {
                h: Math.min(360, Math.max(0, hsb.h)),
                s: Math.min(100, Math.max(0, hsb.s)),
                b: Math.min(100, Math.max(0, hsb.b))
            };
        }, fixRGB = function(rgb) {
            return {
                r: Math.min(255, Math.max(0, rgb.r)),
                g: Math.min(255, Math.max(0, rgb.g)),
                b: Math.min(255, Math.max(0, rgb.b))
            };
        }, fixHex = function(hex) {
            var len = 6 - hex.length;
            if (len > 0) {
                var o = [];
                for (var i = 0; i < len; i++) {
                    o.push("0");
                }
                o.push(hex);
                hex = o.join("");
            }
            return hex;
        }, HexToRGB = function(hex) {
            var hex = parseInt(hex.indexOf("#") > -1 ? hex.substring(1) : hex, 16);
            return {
                r: hex >> 16,
                g: (hex & 65280) >> 8,
                b: hex & 255
            };
        }, HexToHSB = function(hex) {
            return RGBToHSB(HexToRGB(hex));
        }, RGBToHSB = function(rgb) {
            var hsb = {
                h: 0,
                s: 0,
                b: 0
            };
            var min = Math.min(rgb.r, rgb.g, rgb.b);
            var max = Math.max(rgb.r, rgb.g, rgb.b);
            var delta = max - min;
            hsb.b = max;
            if (max != 0) {}
            hsb.s = max != 0 ? 255 * delta / max : 0;
            if (hsb.s != 0) {
                if (rgb.r == max) {
                    hsb.h = (rgb.g - rgb.b) / delta;
                } else if (rgb.g == max) {
                    hsb.h = 2 + (rgb.b - rgb.r) / delta;
                } else {
                    hsb.h = 4 + (rgb.r - rgb.g) / delta;
                }
            } else {
                hsb.h = -1;
            }
            hsb.h *= 60;
            if (hsb.h < 0) {
                hsb.h += 360;
            }
            hsb.s *= 100 / 255;
            hsb.b *= 100 / 255;
            return hsb;
        }, HSBToRGB = function(hsb) {
            var rgb = {};
            var h = Math.round(hsb.h);
            var s = Math.round(hsb.s * 255 / 100);
            var v = Math.round(hsb.b * 255 / 100);
            if (s == 0) {
                rgb.r = rgb.g = rgb.b = v;
            } else {
                var t1 = v;
                var t2 = (255 - s) * v / 255;
                var t3 = (t1 - t2) * (h % 60) / 60;
                if (h == 360) h = 0;
                if (h < 60) {
                    rgb.r = t1;
                    rgb.b = t2;
                    rgb.g = t2 + t3;
                } else if (h < 120) {
                    rgb.g = t1;
                    rgb.b = t2;
                    rgb.r = t1 - t3;
                } else if (h < 180) {
                    rgb.g = t1;
                    rgb.r = t2;
                    rgb.b = t2 + t3;
                } else if (h < 240) {
                    rgb.b = t1;
                    rgb.r = t2;
                    rgb.g = t1 - t3;
                } else if (h < 300) {
                    rgb.b = t1;
                    rgb.g = t2;
                    rgb.r = t2 + t3;
                } else if (h < 360) {
                    rgb.r = t1;
                    rgb.g = t2;
                    rgb.b = t1 - t3;
                } else {
                    rgb.r = 0;
                    rgb.g = 0;
                    rgb.b = 0;
                }
            }
            return {
                r: Math.round(rgb.r),
                g: Math.round(rgb.g),
                b: Math.round(rgb.b)
            };
        }, RGBToHex = function(rgb) {
            var hex = [ rgb.r.toString(16), rgb.g.toString(16), rgb.b.toString(16) ];
            $.each(hex, function(nr, val) {
                if (val.length == 1) {
                    hex[nr] = "0" + val;
                }
            });
            return hex.join("");
        }, HSBToHex = function(hsb) {
            return RGBToHex(HSBToRGB(hsb));
        }, restoreOriginal = function() {
            var cal = $(this).parent();
            var col = cal.data("colorpicker").origColor;
            cal.data("colorpicker").color = col;
            fillRGBFields(col, cal.get(0));
            fillHexFields(col, cal.get(0));
            fillHSBFields(col, cal.get(0));
            setSelector(col, cal.get(0));
            setHue(col, cal.get(0));
            setNewColor(col, cal.get(0));
        };
        return {
            init: function(opt) {
                opt = $.extend({}, defaults, opt || {});
                if (typeof opt.color == "string") {
                    opt.color = HexToHSB(opt.color);
                } else if (opt.color.r != undefined && opt.color.g != undefined && opt.color.b != undefined) {
                    opt.color = RGBToHSB(opt.color);
                } else if (opt.color.h != undefined && opt.color.s != undefined && opt.color.b != undefined) {
                    opt.color = fixHSB(opt.color);
                } else {
                    return this;
                }
                return this.each(function() {
                    if (!$(this).data("colorpickerId")) {
                        var options = $.extend({}, opt);
                        options.origColor = opt.color;
                        var id = "collorpicker_" + parseInt(Math.random() * 1e3);
                        $(this).data("colorpickerId", id);
                        var cal = $(tpl).attr("id", id);
                        if (options.flat) {
                            cal.appendTo(this).show();
                        } else {
                            cal.appendTo(document.body);
                        }
                        options.fields = cal.find("input").bind("keyup", keyDown).bind("change", change).bind("blur", blur).bind("focus", focus);
                        cal.find("span").bind("mousedown", downIncrement).end().find(">div.colorpicker_current_color").bind("click", restoreOriginal);
                        options.selector = cal.find("div.colorpicker_color").bind("mousedown", downSelector);
                        options.selectorIndic = options.selector.find("div div");
                        options.el = this;
                        options.hue = cal.find("div.colorpicker_hue div");
                        cal.find("div.colorpicker_hue").bind("mousedown", downHue);
                        options.newColor = cal.find("div.colorpicker_new_color");
                        options.currentColor = cal.find("div.colorpicker_current_color");
                        cal.data("colorpicker", options);
                        cal.find("div.colorpicker_submit").bind("mouseenter", enterSubmit).bind("mouseleave", leaveSubmit).bind("click", clickSubmit);
                        fillRGBFields(options.color, cal.get(0));
                        fillHSBFields(options.color, cal.get(0));
                        fillHexFields(options.color, cal.get(0));
                        setHue(options.color, cal.get(0));
                        setSelector(options.color, cal.get(0));
                        setCurrentColor(options.color, cal.get(0));
                        setNewColor(options.color, cal.get(0));
                        if (options.flat) {
                            cal.css({
                                position: "relative",
                                display: "block"
                            });
                        } else {
                            $(this).bind(options.eventName, show);
                        }
                    }
                });
            },
            showPicker: function() {
                return this.each(function() {
                    if ($(this).data("colorpickerId")) {
                        show.apply(this);
                    }
                });
            },
            hidePicker: function() {
                return this.each(function() {
                    if ($(this).data("colorpickerId")) {
                        $("#" + $(this).data("colorpickerId")).hide();
                    }
                });
            },
            setColor: function(col) {
                if (typeof col == "string") {
                    col = HexToHSB(col);
                } else if (col.r != undefined && col.g != undefined && col.b != undefined) {
                    col = RGBToHSB(col);
                } else if (col.h != undefined && col.s != undefined && col.b != undefined) {
                    col = fixHSB(col);
                } else {
                    return this;
                }
                return this.each(function() {
                    if ($(this).data("colorpickerId")) {
                        var cal = $("#" + $(this).data("colorpickerId"));
                        cal.data("colorpicker").color = col;
                        cal.data("colorpicker").origColor = col;
                        fillRGBFields(col, cal.get(0));
                        fillHSBFields(col, cal.get(0));
                        fillHexFields(col, cal.get(0));
                        setHue(col, cal.get(0));
                        setSelector(col, cal.get(0));
                        setCurrentColor(col, cal.get(0));
                        setNewColor(col, cal.get(0));
                    }
                });
            }
        };
    }();
    $.fn.extend({
        ColorPicker: ColorPicker.init,
        ColorPickerHide: ColorPicker.hidePicker,
        ColorPickerShow: ColorPicker.showPicker,
        ColorPickerSetColor: ColorPicker.setColor
    });
})(jQuery);

var page_config = {
    nav: {
        0: {
            name: "Light",
            className: "skin-1"
        },
        1: {
            name: "Dark",
            className: "skin-2"
        }
    },
    backgrounds: {
        0: {
            name: "Background 1",
            className: "background-1"
        },
        1: {
            name: "Background 2",
            className: "background-2"
        },
        2: {
            name: "Background 3",
            className: "background-3"
        },
        3: {
            name: "Background 4",
            className: "background-4"
        },
        4: {
            name: "Background 5",
            className: "background-5"
        },
        5: {
            name: "Background 6",
            className: "background-6"
        },
        6: {
            name: "Background 7",
            className: "background-7"
        },
        7: {
            name: "Background 8",
            className: "background-8"
        },
        8: {
            name: "Background 9",
            className: "background-9"
        },
        9: {
            name: "Background 10",
            className: "background-10"
        },
        10: {
            name: "Background 11",
            className: "background-11"
        },
        11: {
            name: "Background 12",
            className: "background-12"
        },
        12: {
            name: "Background 13",
            className: "background-13"
        },
        13: {
            name: "Background 14",
            className: "background-14"
        },
        14: {
            name: "Background 15",
            className: "background-15"
        }
    },
    styles: {
        headerStyle: {
            name: "Heading Font",
            id: "heading_style",
            list: {
                0: {
                    name: "Oswald",
                    className: "h-style-1",
                    class: "text-1"
                },
                1: {
                    name: "PT Sans Narrow",
                    className: "h-style-2",
                    class: "text-2"
                },
                2: {
                    name: "Nova Square",
                    className: "h-style-3",
                    class: "text-3"
                },
                3: {
                    name: "Lobster",
                    className: "h-style-4",
                    class: "text-4"
                },
                4: {
                    name: "Open Sans",
                    className: "h-style-5",
                    class: "text-5"
                },
                5: {
                    name: "Encode Sans Condensed",
                    className: "h-style-6",
                    class: "text-6"
                },
                6: {
                    name: "Baloo Bhaijaan",
                    className: "h-style-7",
                    class: "text-7"
                },
                7: {
                    name: "Acme",
                    className: "h-style-8",
                    class: "text-8"
                }
            }
        },
        textStyle: {
            name: "Content Font",
            id: "text_style",
            list: {
                0: {
                    name: "Oswald",
                    className: "text-1",
                    class: "text-1"
                },
                1: {
                    name: "PT Sans Narrow",
                    className: "text-2",
                    class: "text-2"
                },
                2: {
                    name: "Nova Square",
                    className: "text-3",
                    class: "text-3"
                },
                3: {
                    name: "Lobster",
                    className: "text-4",
                    class: "text-4"
                },
                4: {
                    name: "Open Sans",
                    className: "text-5",
                    class: "text-5"
                },
                5: {
                    name: "Encode Sans Condensed",
                    className: "text-6",
                    class: "text-6"
                },
                6: {
                    name: "Baloo Bhaijaan",
                    className: "text-7",
                    class: "text-7"
                },
                7: {
                    name: "Acme",
                    className: "text-8",
                    class: "text-8"
                }
            }
        }
    }
};

$(function() {
    var $body = $("body");
    var $nav = $(".navigation li a");
    var $theme_control_panel = $("#control_panel");
    var a_color = localStorage.getItem("a_color");
    if (a_color != null) {
        $("body").get(0).style.setProperty("--main-color", "#" + a_color);
        $("iframe").contents().find("body").css("--main-color", "#" + a_color);
    }
    function changeBodyClass(className, classesArray) {
        $.each(classesArray, function(idx, val) {
            $body.removeClass(val);
        });
        $body.addClass(className);
        var body_class = localStorage.getItem("theme");
        if (body_class != null) {
            new_class_pattern = className.replace(/\d{1,2}$/, "");
            body_class = body_class.replace(new RegExp(new_class_pattern + "\\d{1,2}"), className);
            localStorage.setItem("theme", body_class);
        } else {
            body_class = $body.attr("class");
            localStorage.setItem("theme", body_class);
        }
    }
    if (typeof page_config != "undefined" && $theme_control_panel) {
        var pattern_classes = new Array();
        var nav = new Array();
        var defaultSettings = {};
        if (page_config.nav) {
            var $bg_block = $("<div/>").attr("id", "nav").addClass("style_block clearfix");
            var $header = $("#header");
            var bg_change_html = "<span>Menu Skin:</span>";
            bg_change_html += "<ul>";
            $.each(page_config.nav, function(idx, val) {
                bg_change_html += '<li><a href="' + val.className + '" title="' + val.name + '" class="' + val.className + '"></a></li>';
                nav.push(val.className);
            });
            bg_change_html += "</ul>";
            $bg_block.html(bg_change_html);
            $theme_control_panel.append($bg_block);
            $bg_block.find("a").click(function() {
                var nextClassName = $(this).attr("href");
                if (!$body.hasClass(nextClassName)) {
                    changeBodyClass(nextClassName, nav);
                    $bg_block.find(".active").removeClass("active");
                    $(this).parent().addClass("active");
                }
                return false;
            });
        }
        if (page_config.backgrounds) {
            var $bg_block = $("<div/>").attr("id", "backgrounds").addClass("style_block");
            var bg_change_html = "<span>Backgrounds:</span>";
            bg_change_html += "<ul>";
            $.each(page_config.backgrounds, function(idx, val) {
                bg_change_html += '<li><a href="' + val.className + '" title="' + val.name + '" class="' + val.className + '"></a></li>';
                pattern_classes.push(val.className);
            });
            bg_change_html += "</ul>";
            $bg_block.html(bg_change_html);
            $theme_control_panel.append($bg_block);
            $bg_block.find("a").click(function() {
                var nextClassName = $(this).attr("href");
                if (!$body.hasClass(nextClassName)) {
                    changeBodyClass(nextClassName, pattern_classes);
                    $bg_block.find(".active").removeClass("active");
                    $(this).parent().addClass("active");
                }
                return false;
            });
        }
        if (page_config.styles) {
            var $style_block;
            var $block_label;
            var $select_element;
            var $links_color;
            var $links_color_wrapper;
            var select_html;
            var header_style_classes = [];
            var text_style_classes = [];
            defaultSettings.style = {};
            $.each(page_config.styles, function(idx, val) {
                $style_block = $("<div/>").addClass("style_block");
                $block_label = $("<span>" + val.name + ":</span>");
                $select_element = $("<select/>").attr({
                    id: val.id
                });
                select_html = "";
                $.each(val.list, function(list_idx, list_val) {
                    if (list_val.class) {
                        var classes = ' class="' + list_val.class + '"';
                    }
                    if ($body.hasClass(list_val.className)) {
                        select_html += '<option value="' + list_val.className + '" ' + (classes || "") + ' selected="selected">' + list_val.name + "</option>";
                    } else {
                        select_html += '<option value="' + list_val.className + '"' + (classes || "") + ">" + list_val.name + "</option>";
                    }
                });
                $select_element.html(select_html);
                $style_block.append($block_label, $select_element);
                $theme_control_panel.append($style_block);
            });
            $.each(page_config.styles.headerStyle.list, function(idx, val) {
                header_style_classes.push(val.className);
            });
            $("#heading_style").change(function() {
                if (!$body.hasClass($(this).val())) {
                    changeBodyClass($(this).val(), header_style_classes);
                }
            });
            $.each(page_config.styles.textStyle.list, function(idx, val) {
                text_style_classes.push(val.className);
            });
            $("#text_style").change(function() {
                if (!$body.hasClass($(this).val())) {
                    changeBodyClass($(this).val(), text_style_classes);
                    $("iframe").contents().find("body").removeClass("text-1 text-2 text-3 text-4 text-5 text-6 text-7 text-8").addClass($(this).val());
                }
            });
            $links_color = $("<div/>").attr({
                id: "linkspicker"
            }).addClass("colorPicker");
            $links_color_wrapper = $("<div/>").addClass("links_color_wrapper clearfix");
            $links_color_wrapper.append("<span>Links Color:</span>", $links_color);
            $theme_control_panel.append($links_color_wrapper);
            var links_picker = $("#linkspicker");
            links_picker.css("background-color", "#0a0b35").ColorPicker({
                color: "#0a0b35",
                onChange: function(hsb, hex, rgb) {
                    links_picker.css("backgroundColor", "#" + hex);
                    $("body").get(0).style.setProperty("--main-color", "#" + hex);
                    $("iframe").contents().find("body").css("--main-color", "#" + hex);
                    localStorage.setItem("a_color", hex);
                }
            });
            var setDefaultsSettings = function() {
                changeBodyClass(page_config.nav[1].className, nav);
                changeBodyClass(page_config.backgrounds[14].className, pattern_classes);
                $theme_control_panel.find("select").val(1);
                changeBodyClass(page_config.styles.headerStyle.list[0].className, header_style_classes);
                changeBodyClass(page_config.styles.textStyle.list[0].className, text_style_classes);
                $("iframe").contents().find("body").removeClass("text-2 text-3 text-4 text-5 text-6 text-7 text-8").addClass("text-1");
                links_picker.css({
                    "background-color": "#008a05"
                }).ColorPickerSetColor("#008a05");
                $("body").get(0).style.setProperty("--main-color", "#9193de");
                $("iframe").contents().find("body").css("--main-color", "#9193de");
                $theme_control_panel.find(".active").removeClass();
                localStorage.removeItem("a_color");
                return false;
            };
            var $restore_button_wrapper = $("<div/>").addClass("restore_button_wrapper");
            var $restore_button = $("<a/>").text("Reset").attr("id", "restore_button").addClass("button small dark").click(setDefaultsSettings);
            $restore_button_wrapper.append($restore_button);
            $theme_control_panel.append($restore_button_wrapper);
        }
        var $theme_control_panel_label = $("#control_label");
        $theme_control_panel_label.click(function() {
            if ($theme_control_panel.hasClass("visible")) {
                $theme_control_panel.animate({
                    left: -210
                }, 400, function() {
                    $theme_control_panel.removeClass("visible");
                });
            } else {
                $theme_control_panel.animate({
                    left: 0
                }, 400, function() {
                    $theme_control_panel.addClass("visible");
                });
            }
            return false;
        });
    }
});

(function(root, factory) {
    if (typeof define === "function" && define.amd) {
        define([ "jquery" ], function(a0) {
            return factory(a0);
        });
    } else if (typeof exports === "object") {
        module.exports = factory(require("jquery"));
    } else {
        factory(jQuery);
    }
})(this, function($) {
    var defaults = {
        animation: "fade",
        animationDuration: 350,
        content: null,
        contentAsHTML: false,
        contentCloning: false,
        debug: true,
        delay: 300,
        delayTouch: [ 300, 500 ],
        functionInit: null,
        functionBefore: null,
        functionReady: null,
        functionAfter: null,
        functionFormat: null,
        IEmin: 6,
        interactive: false,
        multiple: false,
        parent: null,
        plugins: [ "sideTip" ],
        repositionOnScroll: false,
        restoration: "none",
        selfDestruction: true,
        theme: [],
        timer: 0,
        trackerInterval: 500,
        trackOrigin: false,
        trackTooltip: false,
        trigger: "hover",
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
        updateAnimation: "rotate",
        zIndex: 9999999
    }, win = typeof window != "undefined" ? window : null, env = {
        hasTouchCapability: !!(win && ("ontouchstart" in win || win.DocumentTouch && win.document instanceof win.DocumentTouch || win.navigator.maxTouchPoints)),
        hasTransitions: transitionSupport(),
        IE: false,
        semVer: "4.2.5",
        window: win
    }, core = function() {
        this.__$emitterPrivate = $({});
        this.__$emitterPublic = $({});
        this.__instancesLatestArr = [];
        this.__plugins = {};
        this._env = env;
    };
    core.prototype = {
        __bridge: function(constructor, obj, pluginName) {
            if (!obj[pluginName]) {
                var fn = function() {};
                fn.prototype = constructor;
                var pluginInstance = new fn();
                if (pluginInstance.__init) {
                    pluginInstance.__init(obj);
                }
                $.each(constructor, function(methodName, fn) {
                    if (methodName.indexOf("__") != 0) {
                        if (!obj[methodName]) {
                            obj[methodName] = function() {
                                return pluginInstance[methodName].apply(pluginInstance, Array.prototype.slice.apply(arguments));
                            };
                            obj[methodName].bridged = pluginInstance;
                        } else if (defaults.debug) {
                            console.log("The " + methodName + " method of the " + pluginName + " plugin conflicts with another plugin or native methods");
                        }
                    }
                });
                obj[pluginName] = pluginInstance;
            }
            return this;
        },
        __setWindow: function(window) {
            env.window = window;
            return this;
        },
        _getRuler: function($tooltip) {
            return new Ruler($tooltip);
        },
        _off: function() {
            this.__$emitterPrivate.off.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _on: function() {
            this.__$emitterPrivate.on.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _one: function() {
            this.__$emitterPrivate.one.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _plugin: function(plugin) {
            var self = this;
            if (typeof plugin == "string") {
                var pluginName = plugin, p = null;
                if (pluginName.indexOf(".") > 0) {
                    p = self.__plugins[pluginName];
                } else {
                    $.each(self.__plugins, function(i, plugin) {
                        if (plugin.name.substring(plugin.name.length - pluginName.length - 1) == "." + pluginName) {
                            p = plugin;
                            return false;
                        }
                    });
                }
                return p;
            } else {
                if (plugin.name.indexOf(".") < 0) {
                    throw new Error("Plugins must be namespaced");
                }
                self.__plugins[plugin.name] = plugin;
                if (plugin.core) {
                    self.__bridge(plugin.core, self, plugin.name);
                }
                return this;
            }
        },
        _trigger: function() {
            var args = Array.prototype.slice.apply(arguments);
            if (typeof args[0] == "string") {
                args[0] = {
                    type: args[0]
                };
            }
            this.__$emitterPrivate.trigger.apply(this.__$emitterPrivate, args);
            this.__$emitterPublic.trigger.apply(this.__$emitterPublic, args);
            return this;
        },
        instances: function(selector) {
            var instances = [], sel = selector || ".tooltipstered";
            $(sel).each(function() {
                var $this = $(this), ns = $this.data("tooltipster-ns");
                if (ns) {
                    $.each(ns, function(i, namespace) {
                        instances.push($this.data(namespace));
                    });
                }
            });
            return instances;
        },
        instancesLatest: function() {
            return this.__instancesLatestArr;
        },
        off: function() {
            this.__$emitterPublic.off.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            return this;
        },
        on: function() {
            this.__$emitterPublic.on.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            return this;
        },
        one: function() {
            this.__$emitterPublic.one.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            return this;
        },
        origins: function(selector) {
            var sel = selector ? selector + " " : "";
            return $(sel + ".tooltipstered").toArray();
        },
        setDefaults: function(d) {
            $.extend(defaults, d);
            return this;
        },
        triggerHandler: function() {
            this.__$emitterPublic.triggerHandler.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            return this;
        }
    };
    $.tooltipster = new core();
    $.Tooltipster = function(element, options) {
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
        this.__namespace = "tooltipster-" + Math.round(Math.random() * 1e6);
        this.__options;
        this.__$originParents;
        this.__pointerIsOverOrigin = false;
        this.__previousThemes = [];
        this.__state = "closed";
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
        __init: function(origin, options) {
            var self = this;
            self._$origin = $(origin);
            self.__options = $.extend(true, {}, defaults, options);
            self.__optionsFormat();
            if (!env.IE || env.IE >= self.__options.IEmin) {
                var initialTitle = null;
                if (self._$origin.data("tooltipster-initialTitle") === undefined) {
                    initialTitle = self._$origin.attr("title");
                    if (initialTitle === undefined) initialTitle = null;
                    self._$origin.data("tooltipster-initialTitle", initialTitle);
                }
                if (self.__options.content !== null) {
                    self.__contentSet(self.__options.content);
                } else {
                    var selector = self._$origin.attr("data-tooltip-content"), $el;
                    if (selector) {
                        $el = $(selector);
                    }
                    if ($el && $el[0]) {
                        self.__contentSet($el.first());
                    } else {
                        self.__contentSet(initialTitle);
                    }
                }
                self._$origin.removeAttr("title").addClass("tooltipstered");
                self.__prepareOrigin();
                self.__prepareGC();
                $.each(self.__options.plugins, function(i, pluginName) {
                    self._plug(pluginName);
                });
                if (env.hasTouchCapability) {
                    $(env.window.document.body).on("touchmove." + self.__namespace + "-triggerOpen", function(event) {
                        self._touchRecordEvent(event);
                    });
                }
                self._on("created", function() {
                    self.__prepareTooltip();
                })._on("repositioned", function(e) {
                    self.__lastPosition = e.position;
                });
            } else {
                self.__options.disabled = true;
            }
        },
        __contentInsert: function() {
            var self = this, $el = self._$tooltip.find(".tooltipster-content"), formattedContent = self.__Content, format = function(content) {
                formattedContent = content;
            };
            self._trigger({
                type: "format",
                content: self.__Content,
                format: format
            });
            if (self.__options.functionFormat) {
                formattedContent = self.__options.functionFormat.call(self, self, {
                    origin: self._$origin[0]
                }, self.__Content);
            }
            if (typeof formattedContent === "string" && !self.__options.contentAsHTML) {
                $el.text(formattedContent);
            } else {
                $el.empty().append(formattedContent);
            }
            return self;
        },
        __contentSet: function(content) {
            if (content instanceof $ && this.__options.contentCloning) {
                content = content.clone(true);
            }
            this.__Content = content;
            this._trigger({
                type: "updated",
                content: content
            });
            return this;
        },
        __destroyError: function() {
            throw new Error("This tooltip has been destroyed and cannot execute your method call.");
        },
        __geometry: function() {
            var self = this, $target = self._$origin, originIsArea = self._$origin.is("area");
            if (originIsArea) {
                var mapName = self._$origin.parent().attr("name");
                $target = $('img[usemap="#' + mapName + '"]');
            }
            var bcr = $target[0].getBoundingClientRect(), $document = $(env.window.document), $window = $(env.window), $parent = $target, geo = {
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
                var shape = self._$origin.attr("shape"), coords = self._$origin.attr("coords");
                if (coords) {
                    coords = coords.split(",");
                    $.map(coords, function(val, i) {
                        coords[i] = parseInt(val);
                    });
                }
                if (shape != "default") {
                    switch (shape) {
                      case "circle":
                        var circleCenterLeft = coords[0], circleCenterTop = coords[1], circleRadius = coords[2], areaTopOffset = circleCenterTop - circleRadius, areaLeftOffset = circleCenterLeft - circleRadius;
                        geo.origin.size.height = circleRadius * 2;
                        geo.origin.size.width = geo.origin.size.height;
                        geo.origin.windowOffset.left += areaLeftOffset;
                        geo.origin.windowOffset.top += areaTopOffset;
                        break;

                      case "rect":
                        var areaLeft = coords[0], areaTop = coords[1], areaRight = coords[2], areaBottom = coords[3];
                        geo.origin.size.height = areaBottom - areaTop;
                        geo.origin.size.width = areaRight - areaLeft;
                        geo.origin.windowOffset.left += areaLeft;
                        geo.origin.windowOffset.top += areaTop;
                        break;

                      case "poly":
                        var areaSmallestX = 0, areaSmallestY = 0, areaGreatestX = 0, areaGreatestY = 0, arrayAlternate = "even";
                        for (var i = 0; i < coords.length; i++) {
                            var areaNumber = coords[i];
                            if (arrayAlternate == "even") {
                                if (areaNumber > areaGreatestX) {
                                    areaGreatestX = areaNumber;
                                    if (i === 0) {
                                        areaSmallestX = areaGreatestX;
                                    }
                                }
                                if (areaNumber < areaSmallestX) {
                                    areaSmallestX = areaNumber;
                                }
                                arrayAlternate = "odd";
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
                                arrayAlternate = "even";
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
            var edit = function(r) {
                geo.origin.size.height = r.height, geo.origin.windowOffset.left = r.left, geo.origin.windowOffset.top = r.top, 
                geo.origin.size.width = r.width;
            };
            self._trigger({
                type: "geometry",
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
            while ($parent[0].tagName.toLowerCase() != "html") {
                if ($parent.css("position") == "fixed") {
                    geo.origin.fixedLineage = true;
                    break;
                }
                $parent = $parent.parent();
            }
            return geo;
        },
        __optionsFormat: function() {
            if (typeof this.__options.animationDuration == "number") {
                this.__options.animationDuration = [ this.__options.animationDuration, this.__options.animationDuration ];
            }
            if (typeof this.__options.delay == "number") {
                this.__options.delay = [ this.__options.delay, this.__options.delay ];
            }
            if (typeof this.__options.delayTouch == "number") {
                this.__options.delayTouch = [ this.__options.delayTouch, this.__options.delayTouch ];
            }
            if (typeof this.__options.theme == "string") {
                this.__options.theme = [ this.__options.theme ];
            }
            if (this.__options.parent === null) {
                this.__options.parent = $(env.window.document.body);
            } else if (typeof this.__options.parent == "string") {
                this.__options.parent = $(this.__options.parent);
            }
            if (this.__options.trigger == "hover") {
                this.__options.triggerOpen = {
                    mouseenter: true,
                    touchstart: true
                };
                this.__options.triggerClose = {
                    mouseleave: true,
                    originClick: true,
                    touchleave: true
                };
            } else if (this.__options.trigger == "click") {
                this.__options.triggerOpen = {
                    click: true,
                    tap: true
                };
                this.__options.triggerClose = {
                    click: true,
                    tap: true
                };
            }
            this._trigger("options");
            return this;
        },
        __prepareGC: function() {
            var self = this;
            if (self.__options.selfDestruction) {
                self.__garbageCollector = setInterval(function() {
                    var now = new Date().getTime();
                    self.__touchEvents = $.grep(self.__touchEvents, function(event, i) {
                        return now - event.time > 6e4;
                    });
                    if (!bodyContains(self._$origin)) {
                        self.close(function() {
                            self.destroy();
                        });
                    }
                }, 2e4);
            } else {
                clearInterval(self.__garbageCollector);
            }
            return self;
        },
        __prepareOrigin: function() {
            var self = this;
            self._$origin.off("." + self.__namespace + "-triggerOpen");
            if (env.hasTouchCapability) {
                self._$origin.on("touchstart." + self.__namespace + "-triggerOpen " + "touchend." + self.__namespace + "-triggerOpen " + "touchcancel." + self.__namespace + "-triggerOpen", function(event) {
                    self._touchRecordEvent(event);
                });
            }
            if (self.__options.triggerOpen.click || self.__options.triggerOpen.tap && env.hasTouchCapability) {
                var eventNames = "";
                if (self.__options.triggerOpen.click) {
                    eventNames += "click." + self.__namespace + "-triggerOpen ";
                }
                if (self.__options.triggerOpen.tap && env.hasTouchCapability) {
                    eventNames += "touchend." + self.__namespace + "-triggerOpen";
                }
                self._$origin.on(eventNames, function(event) {
                    if (self._touchIsMeaningfulEvent(event)) {
                        self._open(event);
                    }
                });
            }
            if (self.__options.triggerOpen.mouseenter || self.__options.triggerOpen.touchstart && env.hasTouchCapability) {
                var eventNames = "";
                if (self.__options.triggerOpen.mouseenter) {
                    eventNames += "mouseenter." + self.__namespace + "-triggerOpen ";
                }
                if (self.__options.triggerOpen.touchstart && env.hasTouchCapability) {
                    eventNames += "touchstart." + self.__namespace + "-triggerOpen";
                }
                self._$origin.on(eventNames, function(event) {
                    if (self._touchIsTouchEvent(event) || !self._touchIsEmulatedEvent(event)) {
                        self.__pointerIsOverOrigin = true;
                        self._openShortly(event);
                    }
                });
            }
            if (self.__options.triggerClose.mouseleave || self.__options.triggerClose.touchleave && env.hasTouchCapability) {
                var eventNames = "";
                if (self.__options.triggerClose.mouseleave) {
                    eventNames += "mouseleave." + self.__namespace + "-triggerOpen ";
                }
                if (self.__options.triggerClose.touchleave && env.hasTouchCapability) {
                    eventNames += "touchend." + self.__namespace + "-triggerOpen touchcancel." + self.__namespace + "-triggerOpen";
                }
                self._$origin.on(eventNames, function(event) {
                    if (self._touchIsMeaningfulEvent(event)) {
                        self.__pointerIsOverOrigin = false;
                    }
                });
            }
            return self;
        },
        __prepareTooltip: function() {
            var self = this, p = self.__options.interactive ? "auto" : "";
            self._$tooltip.attr("id", self.__namespace).css({
                "pointer-events": p,
                zIndex: self.__options.zIndex
            });
            $.each(self.__previousThemes, function(i, theme) {
                self._$tooltip.removeClass(theme);
            });
            $.each(self.__options.theme, function(i, theme) {
                self._$tooltip.addClass(theme);
            });
            self.__previousThemes = $.merge([], self.__options.theme);
            return self;
        },
        __scrollHandler: function(event) {
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
                        if (self._$origin.css("position") != "fixed") {
                            self.__$originParents.each(function(i, el) {
                                var $el = $(el), overflowX = $el.css("overflow-x"), overflowY = $el.css("overflow-y");
                                if (overflowX != "visible" || overflowY != "visible") {
                                    var bcr = el.getBoundingClientRect();
                                    if (overflowX != "visible") {
                                        if (geo.origin.windowOffset.left < bcr.left || geo.origin.windowOffset.right > bcr.right) {
                                            overflows = true;
                                            return false;
                                        }
                                    }
                                    if (overflowY != "visible") {
                                        if (geo.origin.windowOffset.top < bcr.top || geo.origin.windowOffset.bottom > bcr.bottom) {
                                            overflows = true;
                                            return false;
                                        }
                                    }
                                }
                                if ($el.css("position") == "fixed") {
                                    return false;
                                }
                            });
                        }
                        if (overflows) {
                            self._$tooltip.css("visibility", "hidden");
                        } else {
                            self._$tooltip.css("visibility", "visible");
                            if (self.__options.repositionOnScroll) {
                                self.reposition(event);
                            } else {
                                var offsetLeft = geo.origin.offset.left - self.__Geometry.origin.offset.left, offsetTop = geo.origin.offset.top - self.__Geometry.origin.offset.top;
                                self._$tooltip.css({
                                    left: self.__lastPosition.coord.left + offsetLeft,
                                    top: self.__lastPosition.coord.top + offsetTop
                                });
                            }
                        }
                    }
                    self._trigger({
                        type: "scroll",
                        event: event,
                        geo: geo
                    });
                }
            }
            return self;
        },
        __stateSet: function(state) {
            this.__state = state;
            this._trigger({
                type: "state",
                state: state
            });
            return this;
        },
        __timeoutsClear: function() {
            clearTimeout(this.__timeouts.open);
            this.__timeouts.open = null;
            $.each(this.__timeouts.close, function(i, timeout) {
                clearTimeout(timeout);
            });
            this.__timeouts.close = [];
            return this;
        },
        __trackerStart: function() {
            var self = this, $content = self._$tooltip.find(".tooltipster-content");
            if (self.__options.trackTooltip) {
                self.__contentBcr = $content[0].getBoundingClientRect();
            }
            self.__tracker = setInterval(function() {
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
        _close: function(event, callback, force) {
            var self = this, ok = true;
            self._trigger({
                type: "close",
                event: event,
                stop: function() {
                    ok = false;
                }
            });
            if (ok || force) {
                if (callback) self.__callbacks.close.push(callback);
                self.__callbacks.open = [];
                self.__timeoutsClear();
                var finishCallbacks = function() {
                    $.each(self.__callbacks.close, function(i, c) {
                        c.call(self, self, {
                            event: event,
                            origin: self._$origin[0]
                        });
                    });
                    self.__callbacks.close = [];
                };
                if (self.__state != "closed") {
                    var necessary = true, d = new Date(), now = d.getTime(), newClosingTime = now + self.__options.animationDuration[1];
                    if (self.__state == "disappearing") {
                        if (newClosingTime > self.__closingTime && self.__options.animationDuration[1] > 0) {
                            necessary = false;
                        }
                    }
                    if (necessary) {
                        self.__closingTime = newClosingTime;
                        if (self.__state != "disappearing") {
                            self.__stateSet("disappearing");
                        }
                        var finish = function() {
                            clearInterval(self.__tracker);
                            self._trigger({
                                type: "closing",
                                event: event
                            });
                            self._$tooltip.off("." + self.__namespace + "-triggerClose").removeClass("tooltipster-dying");
                            $(env.window).off("." + self.__namespace + "-triggerClose");
                            self.__$originParents.each(function(i, el) {
                                $(el).off("scroll." + self.__namespace + "-triggerClose");
                            });
                            self.__$originParents = null;
                            $(env.window.document.body).off("." + self.__namespace + "-triggerClose");
                            self._$origin.off("." + self.__namespace + "-triggerClose");
                            self._off("dismissable");
                            self.__stateSet("closed");
                            self._trigger({
                                type: "after",
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
                                "-moz-animation-duration": self.__options.animationDuration[1] + "ms",
                                "-ms-animation-duration": self.__options.animationDuration[1] + "ms",
                                "-o-animation-duration": self.__options.animationDuration[1] + "ms",
                                "-webkit-animation-duration": self.__options.animationDuration[1] + "ms",
                                "animation-duration": self.__options.animationDuration[1] + "ms",
                                "transition-duration": self.__options.animationDuration[1] + "ms"
                            });
                            self._$tooltip.clearQueue().removeClass("tooltipster-show").addClass("tooltipster-dying");
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
        _off: function() {
            this.__$emitterPrivate.off.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _on: function() {
            this.__$emitterPrivate.on.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _one: function() {
            this.__$emitterPrivate.one.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
            return this;
        },
        _open: function(event, callback) {
            var self = this;
            if (!self.__destroying) {
                if (bodyContains(self._$origin) && self.__enabled) {
                    var ok = true;
                    if (self.__state == "closed") {
                        self._trigger({
                            type: "before",
                            event: event,
                            stop: function() {
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
                            var extraTime, finish = function() {
                                if (self.__state != "stable") {
                                    self.__stateSet("stable");
                                }
                                $.each(self.__callbacks.open, function(i, c) {
                                    c.call(self, self, {
                                        origin: self._$origin[0],
                                        tooltip: self._$tooltip[0]
                                    });
                                });
                                self.__callbacks.open = [];
                            };
                            if (self.__state !== "closed") {
                                extraTime = 0;
                                if (self.__state === "disappearing") {
                                    self.__stateSet("appearing");
                                    if (env.hasTransitions) {
                                        self._$tooltip.clearQueue().removeClass("tooltipster-dying").addClass("tooltipster-show");
                                        if (self.__options.animationDuration[0] > 0) {
                                            self._$tooltip.delay(self.__options.animationDuration[0]);
                                        }
                                        self._$tooltip.queue(finish);
                                    } else {
                                        self._$tooltip.stop().fadeIn(finish);
                                    }
                                } else if (self.__state == "stable") {
                                    finish();
                                }
                            } else {
                                self.__stateSet("appearing");
                                extraTime = self.__options.animationDuration[0];
                                self.__contentInsert();
                                self.reposition(event, true);
                                if (env.hasTransitions) {
                                    self._$tooltip.addClass("tooltipster-" + self.__options.animation).addClass("tooltipster-initial").css({
                                        "-moz-animation-duration": self.__options.animationDuration[0] + "ms",
                                        "-ms-animation-duration": self.__options.animationDuration[0] + "ms",
                                        "-o-animation-duration": self.__options.animationDuration[0] + "ms",
                                        "-webkit-animation-duration": self.__options.animationDuration[0] + "ms",
                                        "animation-duration": self.__options.animationDuration[0] + "ms",
                                        "transition-duration": self.__options.animationDuration[0] + "ms"
                                    });
                                    setTimeout(function() {
                                        if (self.__state != "closed") {
                                            self._$tooltip.addClass("tooltipster-show").removeClass("tooltipster-initial");
                                            if (self.__options.animationDuration[0] > 0) {
                                                self._$tooltip.delay(self.__options.animationDuration[0]);
                                            }
                                            self._$tooltip.queue(finish);
                                        }
                                    }, 0);
                                } else {
                                    self._$tooltip.css("display", "none").fadeIn(self.__options.animationDuration[0], finish);
                                }
                                self.__trackerStart();
                                $(env.window).on("resize." + self.__namespace + "-triggerClose", function(e) {
                                    var $ae = $(document.activeElement);
                                    if (!$ae.is("input") && !$ae.is("textarea") || !$.contains(self._$tooltip[0], $ae[0])) {
                                        self.reposition(e);
                                    }
                                }).on("scroll." + self.__namespace + "-triggerClose", function(e) {
                                    self.__scrollHandler(e);
                                });
                                self.__$originParents = self._$origin.parents();
                                self.__$originParents.each(function(i, parent) {
                                    $(parent).on("scroll." + self.__namespace + "-triggerClose", function(e) {
                                        self.__scrollHandler(e);
                                    });
                                });
                                if (self.__options.triggerClose.mouseleave || self.__options.triggerClose.touchleave && env.hasTouchCapability) {
                                    self._on("dismissable", function(event) {
                                        if (event.dismissable) {
                                            if (event.delay) {
                                                timeout = setTimeout(function() {
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
                                    var $elements = self._$origin, eventNamesIn = "", eventNamesOut = "", timeout = null;
                                    if (self.__options.interactive) {
                                        $elements = $elements.add(self._$tooltip);
                                    }
                                    if (self.__options.triggerClose.mouseleave) {
                                        eventNamesIn += "mouseenter." + self.__namespace + "-triggerClose ";
                                        eventNamesOut += "mouseleave." + self.__namespace + "-triggerClose ";
                                    }
                                    if (self.__options.triggerClose.touchleave && env.hasTouchCapability) {
                                        eventNamesIn += "touchstart." + self.__namespace + "-triggerClose";
                                        eventNamesOut += "touchend." + self.__namespace + "-triggerClose touchcancel." + self.__namespace + "-triggerClose";
                                    }
                                    $elements.on(eventNamesOut, function(event) {
                                        if (self._touchIsTouchEvent(event) || !self._touchIsEmulatedEvent(event)) {
                                            var delay = event.type == "mouseleave" ? self.__options.delay : self.__options.delayTouch;
                                            self._trigger({
                                                delay: delay[1],
                                                dismissable: true,
                                                event: event,
                                                type: "dismissable"
                                            });
                                        }
                                    }).on(eventNamesIn, function(event) {
                                        if (self._touchIsTouchEvent(event) || !self._touchIsEmulatedEvent(event)) {
                                            self._trigger({
                                                dismissable: false,
                                                event: event,
                                                type: "dismissable"
                                            });
                                        }
                                    });
                                }
                                if (self.__options.triggerClose.originClick) {
                                    self._$origin.on("click." + self.__namespace + "-triggerClose", function(event) {
                                        if (!self._touchIsTouchEvent(event) && !self._touchIsEmulatedEvent(event)) {
                                            self._close(event);
                                        }
                                    });
                                }
                                if (self.__options.triggerClose.click || self.__options.triggerClose.tap && env.hasTouchCapability) {
                                    setTimeout(function() {
                                        if (self.__state != "closed") {
                                            var eventNames = "", $body = $(env.window.document.body);
                                            if (self.__options.triggerClose.click) {
                                                eventNames += "click." + self.__namespace + "-triggerClose ";
                                            }
                                            if (self.__options.triggerClose.tap && env.hasTouchCapability) {
                                                eventNames += "touchend." + self.__namespace + "-triggerClose";
                                            }
                                            $body.on(eventNames, function(event) {
                                                if (self._touchIsMeaningfulEvent(event)) {
                                                    self._touchRecordEvent(event);
                                                    if (!self.__options.interactive || !$.contains(self._$tooltip[0], event.target)) {
                                                        self._close(event);
                                                    }
                                                }
                                            });
                                            if (self.__options.triggerClose.tap && env.hasTouchCapability) {
                                                $body.on("touchstart." + self.__namespace + "-triggerClose", function(event) {
                                                    self._touchRecordEvent(event);
                                                });
                                            }
                                        }
                                    }, 0);
                                }
                                self._trigger("ready");
                                if (self.__options.functionReady) {
                                    self.__options.functionReady.call(self, self, {
                                        origin: self._$origin[0],
                                        tooltip: self._$tooltip[0]
                                    });
                                }
                            }
                            if (self.__options.timer > 0) {
                                var timeout = setTimeout(function() {
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
        _openShortly: function(event) {
            var self = this, ok = true;
            if (self.__state != "stable" && self.__state != "appearing") {
                if (!self.__timeouts.open) {
                    self._trigger({
                        type: "start",
                        event: event,
                        stop: function() {
                            ok = false;
                        }
                    });
                    if (ok) {
                        var delay = event.type.indexOf("touch") == 0 ? self.__options.delayTouch : self.__options.delay;
                        if (delay[0]) {
                            self.__timeouts.open = setTimeout(function() {
                                self.__timeouts.open = null;
                                if (self.__pointerIsOverOrigin && self._touchIsMeaningfulEvent(event)) {
                                    self._trigger("startend");
                                    self._open(event);
                                } else {
                                    self._trigger("startcancel");
                                }
                            }, delay[0]);
                        } else {
                            self._trigger("startend");
                            self._open(event);
                        }
                    }
                }
            }
            return self;
        },
        _optionsExtract: function(pluginName, defaultOptions) {
            var self = this, options = $.extend(true, {}, defaultOptions);
            var pluginOptions = self.__options[pluginName];
            if (!pluginOptions) {
                pluginOptions = {};
                $.each(defaultOptions, function(optionName, value) {
                    var o = self.__options[optionName];
                    if (o !== undefined) {
                        pluginOptions[optionName] = o;
                    }
                });
            }
            $.each(options, function(optionName, value) {
                if (pluginOptions[optionName] !== undefined) {
                    if (typeof value == "object" && !(value instanceof Array) && value != null && (typeof pluginOptions[optionName] == "object" && !(pluginOptions[optionName] instanceof Array) && pluginOptions[optionName] != null)) {
                        $.extend(options[optionName], pluginOptions[optionName]);
                    } else {
                        options[optionName] = pluginOptions[optionName];
                    }
                }
            });
            return options;
        },
        _plug: function(pluginName) {
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
        _touchIsEmulatedEvent: function(event) {
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
        _touchIsMeaningfulEvent: function(event) {
            return this._touchIsTouchEvent(event) && !this._touchSwiped(event.target) || !this._touchIsTouchEvent(event) && !this._touchIsEmulatedEvent(event);
        },
        _touchIsTouchEvent: function(event) {
            return event.type.indexOf("touch") == 0;
        },
        _touchRecordEvent: function(event) {
            if (this._touchIsTouchEvent(event)) {
                event.time = new Date().getTime();
                this.__touchEvents.push(event);
            }
            return this;
        },
        _touchSwiped: function(target) {
            var swiped = false;
            for (var i = this.__touchEvents.length - 1; i >= 0; i--) {
                var e = this.__touchEvents[i];
                if (e.type == "touchmove") {
                    swiped = true;
                    break;
                } else if (e.type == "touchstart" && target === e.target) {
                    break;
                }
            }
            return swiped;
        },
        _trigger: function() {
            var args = Array.prototype.slice.apply(arguments);
            if (typeof args[0] == "string") {
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
        _unplug: function(pluginName) {
            var self = this;
            if (self[pluginName]) {
                var plugin = $.tooltipster._plugin(pluginName);
                if (plugin.instance) {
                    $.each(plugin.instance, function(methodName, fn) {
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
        close: function(callback) {
            if (!this.__destroyed) {
                this._close(null, callback);
            } else {
                this.__destroyError();
            }
            return this;
        },
        content: function(content) {
            var self = this;
            if (content === undefined) {
                return self.__Content;
            } else {
                if (!self.__destroyed) {
                    self.__contentSet(content);
                    if (self.__Content !== null) {
                        if (self.__state !== "closed") {
                            self.__contentInsert();
                            self.reposition();
                            if (self.__options.updateAnimation) {
                                if (env.hasTransitions) {
                                    var animation = self.__options.updateAnimation;
                                    self._$tooltip.addClass("tooltipster-update-" + animation);
                                    setTimeout(function() {
                                        if (self.__state != "closed") {
                                            self._$tooltip.removeClass("tooltipster-update-" + animation);
                                        }
                                    }, 1e3);
                                } else {
                                    self._$tooltip.fadeTo(200, .5, function() {
                                        if (self.__state != "closed") {
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
        destroy: function() {
            var self = this;
            if (!self.__destroyed) {
                if (self.__state != "closed") {
                    self.option("animationDuration", 0)._close(null, null, true);
                } else {
                    self.__timeoutsClear();
                }
                self._trigger("destroy");
                self.__destroyed = true;
                self._$origin.removeData(self.__namespace).off("." + self.__namespace + "-triggerOpen");
                $(env.window.document.body).off("." + self.__namespace + "-triggerOpen");
                var ns = self._$origin.data("tooltipster-ns");
                if (ns) {
                    if (ns.length === 1) {
                        var title = null;
                        if (self.__options.restoration == "previous") {
                            title = self._$origin.data("tooltipster-initialTitle");
                        } else if (self.__options.restoration == "current") {
                            title = typeof self.__Content == "string" ? self.__Content : $("<div></div>").append(self.__Content).html();
                        }
                        if (title) {
                            self._$origin.attr("title", title);
                        }
                        self._$origin.removeClass("tooltipstered");
                        self._$origin.removeData("tooltipster-ns").removeData("tooltipster-initialTitle");
                    } else {
                        ns = $.grep(ns, function(el, i) {
                            return el !== self.__namespace;
                        });
                        self._$origin.data("tooltipster-ns", ns);
                    }
                }
                self._trigger("destroyed");
                self._off();
                self.off();
                self.__Content = null;
                self.__$emitterPrivate = null;
                self.__$emitterPublic = null;
                self.__options.parent = null;
                self._$origin = null;
                self._$tooltip = null;
                $.tooltipster.__instancesLatestArr = $.grep($.tooltipster.__instancesLatestArr, function(el, i) {
                    return self !== el;
                });
                clearInterval(self.__garbageCollector);
            } else {
                self.__destroyError();
            }
            return self;
        },
        disable: function() {
            if (!this.__destroyed) {
                this._close();
                this.__enabled = false;
                return this;
            } else {
                this.__destroyError();
            }
            return this;
        },
        elementOrigin: function() {
            if (!this.__destroyed) {
                return this._$origin[0];
            } else {
                this.__destroyError();
            }
        },
        elementTooltip: function() {
            return this._$tooltip ? this._$tooltip[0] : null;
        },
        enable: function() {
            this.__enabled = true;
            return this;
        },
        hide: function(callback) {
            return this.close(callback);
        },
        instance: function() {
            return this;
        },
        off: function() {
            if (!this.__destroyed) {
                this.__$emitterPublic.off.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            }
            return this;
        },
        on: function() {
            if (!this.__destroyed) {
                this.__$emitterPublic.on.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            } else {
                this.__destroyError();
            }
            return this;
        },
        one: function() {
            if (!this.__destroyed) {
                this.__$emitterPublic.one.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            } else {
                this.__destroyError();
            }
            return this;
        },
        open: function(callback) {
            if (!this.__destroyed) {
                this._open(null, callback);
            } else {
                this.__destroyError();
            }
            return this;
        },
        option: function(o, val) {
            if (val === undefined) {
                return this.__options[o];
            } else {
                if (!this.__destroyed) {
                    this.__options[o] = val;
                    this.__optionsFormat();
                    if ($.inArray(o, [ "trigger", "triggerClose", "triggerOpen" ]) >= 0) {
                        this.__prepareOrigin();
                    }
                    if (o === "selfDestruction") {
                        this.__prepareGC();
                    }
                } else {
                    this.__destroyError();
                }
                return this;
            }
        },
        reposition: function(event, tooltipIsDetached) {
            var self = this;
            if (!self.__destroyed) {
                if (self.__state != "closed" && bodyContains(self._$origin)) {
                    if (tooltipIsDetached || bodyContains(self._$tooltip)) {
                        if (!tooltipIsDetached) {
                            self._$tooltip.detach();
                        }
                        self.__Geometry = self.__geometry();
                        self._trigger({
                            type: "reposition",
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
        show: function(callback) {
            return this.open(callback);
        },
        status: function() {
            return {
                destroyed: this.__destroyed,
                enabled: this.__enabled,
                open: this.__state !== "closed",
                state: this.__state
            };
        },
        triggerHandler: function() {
            if (!this.__destroyed) {
                this.__$emitterPublic.triggerHandler.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
            } else {
                this.__destroyError();
            }
            return this;
        }
    };
    $.fn.tooltipster = function() {
        var args = Array.prototype.slice.apply(arguments), contentCloningWarning = "You are using a single HTML element as content for several tooltips. You probably want to set the contentCloning option to TRUE.";
        if (this.length === 0) {
            return this;
        } else {
            if (typeof args[0] === "string") {
                var v = "#*$~&";
                this.each(function() {
                    var ns = $(this).data("tooltipster-ns"), self = ns ? $(this).data(ns[0]) : null;
                    if (self) {
                        if (typeof self[args[0]] === "function") {
                            if (this.length > 1 && args[0] == "content" && (args[1] instanceof $ || typeof args[1] == "object" && args[1] != null && args[1].tagName) && !self.__options.contentCloning && self.__options.debug) {
                                console.log(contentCloningWarning);
                            }
                            var resp = self[args[0]](args[1], args[2]);
                        } else {
                            throw new Error('Unknown method "' + args[0] + '"');
                        }
                        if (resp !== self || args[0] === "instance") {
                            v = resp;
                            return false;
                        }
                    } else {
                        throw new Error("You called Tooltipster's \"" + args[0] + '" method on an uninitialized element');
                    }
                });
                return v !== "#*$~&" ? v : this;
            } else {
                $.tooltipster.__instancesLatestArr = [];
                var multipleIsSet = args[0] && args[0].multiple !== undefined, multiple = multipleIsSet && args[0].multiple || !multipleIsSet && defaults.multiple, contentIsSet = args[0] && args[0].content !== undefined, content = contentIsSet && args[0].content || !contentIsSet && defaults.content, contentCloningIsSet = args[0] && args[0].contentCloning !== undefined, contentCloning = contentCloningIsSet && args[0].contentCloning || !contentCloningIsSet && defaults.contentCloning, debugIsSet = args[0] && args[0].debug !== undefined, debug = debugIsSet && args[0].debug || !debugIsSet && defaults.debug;
                if (this.length > 1 && (content instanceof $ || typeof content == "object" && content != null && content.tagName) && !contentCloning && debug) {
                    console.log(contentCloningWarning);
                }
                this.each(function() {
                    var go = false, $this = $(this), ns = $this.data("tooltipster-ns"), obj = null;
                    if (!ns) {
                        go = true;
                    } else if (multiple) {
                        go = true;
                    } else if (debug) {
                        console.log("Tooltipster: one or more tooltips are already attached to the element below. Ignoring.");
                        console.log(this);
                    }
                    if (go) {
                        obj = new $.Tooltipster(this, args[0]);
                        if (!ns) ns = [];
                        ns.push(obj.__namespace);
                        $this.data("tooltipster-ns", ns);
                        $this.data(obj.__namespace, obj);
                        if (obj.__options.functionInit) {
                            obj.__options.functionInit.call(obj, obj, {
                                origin: this
                            });
                        }
                        obj._trigger("init");
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
        __init: function($tooltip) {
            this.__$tooltip = $tooltip;
            this.__$tooltip.css({
                left: 0,
                overflow: "hidden",
                position: "absolute",
                top: 0
            }).find(".tooltipster-content").css("overflow", "auto");
            this.$container = $('<div class="tooltipster-ruler"></div>').append(this.__$tooltip).appendTo(env.window.document.body);
        },
        __forceRedraw: function() {
            var $p = this.__$tooltip.parent();
            this.__$tooltip.detach();
            this.__$tooltip.appendTo($p);
        },
        constrain: function(width, height) {
            this.constraints = {
                width: width,
                height: height
            };
            this.__$tooltip.css({
                display: "block",
                height: "",
                overflow: "auto",
                width: width
            });
            return this;
        },
        destroy: function() {
            this.__$tooltip.detach().find(".tooltipster-content").css({
                display: "",
                overflow: ""
            });
            this.$container.remove();
        },
        free: function() {
            this.constraints = null;
            this.__$tooltip.css({
                display: "",
                height: "",
                overflow: "visible",
                width: ""
            });
            return this;
        },
        measure: function() {
            this.__forceRedraw();
            var tooltipBcr = this.__$tooltip[0].getBoundingClientRect(), result = {
                size: {
                    height: tooltipBcr.height || tooltipBcr.bottom - tooltipBcr.top,
                    width: tooltipBcr.width || tooltipBcr.right - tooltipBcr.left
                }
            };
            if (this.constraints) {
                var $content = this.__$tooltip.find(".tooltipster-content"), height = this.__$tooltip.outerHeight(), contentBcr = $content[0].getBoundingClientRect(), fits = {
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
        $.each(a, function(i, _) {
            if (b[i] === undefined || a[i] !== b[i]) {
                same = false;
                return false;
            }
        });
        return same;
    }
    function bodyContains($obj) {
        var id = $obj.attr("id"), el = id ? env.window.document.getElementById(id) : null;
        return el ? el === $obj[0] : $.contains(env.window.document.body, $obj[0]);
    }
    var uA = navigator.userAgent.toLowerCase();
    if (uA.indexOf("msie") != -1) env.IE = parseInt(uA.split("msie")[1]); else if (uA.toLowerCase().indexOf("trident") !== -1 && uA.indexOf(" rv:11") !== -1) env.IE = 11; else if (uA.toLowerCase().indexOf("edge/") != -1) env.IE = parseInt(uA.toLowerCase().split("edge/")[1]);
    function transitionSupport() {
        if (!win) return false;
        var b = win.document.body || win.document.documentElement, s = b.style, p = "transition", v = [ "Moz", "Webkit", "Khtml", "O", "ms" ];
        if (typeof s[p] == "string") {
            return true;
        }
        p = p.charAt(0).toUpperCase() + p.substr(1);
        for (var i = 0; i < v.length; i++) {
            if (typeof s[v[i] + p] == "string") {
                return true;
            }
        }
        return false;
    }
    var pluginName = "tooltipster.sideTip";
    $.tooltipster._plugin({
        name: pluginName,
        instance: {
            __defaults: function() {
                return {
                    arrow: true,
                    distance: 6,
                    functionPosition: null,
                    maxWidth: null,
                    minIntersection: 16,
                    minWidth: 0,
                    position: null,
                    side: "top",
                    viewportAware: true
                };
            },
            __init: function(instance) {
                var self = this;
                self.__instance = instance;
                self.__namespace = "tooltipster-sideTip-" + Math.round(Math.random() * 1e6);
                self.__previousState = "closed";
                self.__options;
                self.__optionsFormat();
                self.__instance._on("state." + self.__namespace, function(event) {
                    if (event.state == "closed") {
                        self.__close();
                    } else if (event.state == "appearing" && self.__previousState == "closed") {
                        self.__create();
                    }
                    self.__previousState = event.state;
                });
                self.__instance._on("options." + self.__namespace, function() {
                    self.__optionsFormat();
                });
                self.__instance._on("reposition." + self.__namespace, function(e) {
                    self.__reposition(e.event, e.helper);
                });
            },
            __close: function() {
                if (this.__instance.content() instanceof $) {
                    this.__instance.content().detach();
                }
                this.__instance._$tooltip.remove();
                this.__instance._$tooltip = null;
            },
            __create: function() {
                var $html = $('<div class="tooltipster-base tooltipster-sidetip">' + '<div class="tooltipster-box">' + '<div class="tooltipster-content"></div>' + "</div>" + '<div class="tooltipster-arrow">' + '<div class="tooltipster-arrow-uncropped">' + '<div class="tooltipster-arrow-border"></div>' + '<div class="tooltipster-arrow-background"></div>' + "</div>" + "</div>" + "</div>");
                if (!this.__options.arrow) {
                    $html.find(".tooltipster-box").css("margin", 0).end().find(".tooltipster-arrow").hide();
                }
                if (this.__options.minWidth) {
                    $html.css("min-width", this.__options.minWidth + "px");
                }
                if (this.__options.maxWidth) {
                    $html.css("max-width", this.__options.maxWidth + "px");
                }
                this.__instance._$tooltip = $html;
                this.__instance._trigger("created");
            },
            __destroy: function() {
                this.__instance._off("." + self.__namespace);
            },
            __optionsFormat: function() {
                var self = this;
                self.__options = self.__instance._optionsExtract(pluginName, self.__defaults());
                if (self.__options.position) {
                    self.__options.side = self.__options.position;
                }
                if (typeof self.__options.distance != "object") {
                    self.__options.distance = [ self.__options.distance ];
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
                if (typeof self.__options.side == "string") {
                    var opposites = {
                        top: "bottom",
                        right: "left",
                        bottom: "top",
                        left: "right"
                    };
                    self.__options.side = [ self.__options.side, opposites[self.__options.side] ];
                    if (self.__options.side[0] == "left" || self.__options.side[0] == "right") {
                        self.__options.side.push("top", "bottom");
                    } else {
                        self.__options.side.push("right", "left");
                    }
                }
                if ($.tooltipster._env.IE === 6 && self.__options.arrow !== true) {
                    self.__options.arrow = false;
                }
            },
            __reposition: function(event, helper) {
                var self = this, finalResult, targets = self.__targetFind(helper), testResults = [];
                self.__instance._$tooltip.detach();
                var $clone = self.__instance._$tooltip.clone(), ruler = $.tooltipster._getRuler($clone), satisfied = false, animation = self.__instance.option("animation");
                if (animation) {
                    $clone.removeClass("tooltipster-" + animation);
                }
                $.each([ "window", "document" ], function(i, container) {
                    var takeTest = null;
                    self.__instance._trigger({
                        container: container,
                        helper: helper,
                        satisfied: satisfied,
                        takeTest: function(bool) {
                            takeTest = bool;
                        },
                        results: testResults,
                        type: "positionTest"
                    });
                    if (takeTest == true || takeTest != false && satisfied == false && (container != "window" || self.__options.viewportAware)) {
                        for (var i = 0; i < self.__options.side.length; i++) {
                            var distance = {
                                horizontal: 0,
                                vertical: 0
                            }, side = self.__options.side[i];
                            if (side == "top" || side == "bottom") {
                                distance.vertical = self.__options.distance[side];
                            } else {
                                distance.horizontal = self.__options.distance[side];
                            }
                            self.__sideChange($clone, side);
                            $.each([ "natural", "constrained" ], function(i, mode) {
                                takeTest = null;
                                self.__instance._trigger({
                                    container: container,
                                    event: event,
                                    helper: helper,
                                    mode: mode,
                                    results: testResults,
                                    satisfied: satisfied,
                                    side: side,
                                    takeTest: function(bool) {
                                        takeTest = bool;
                                    },
                                    type: "positionTest"
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
                                    var rulerConfigured = mode == "natural" ? ruler.free() : ruler.constrain(helper.geo.available[container][side].width - distance.horizontal, helper.geo.available[container][side].height - distance.vertical), rulerResults = rulerConfigured.measure();
                                    testResult.size = rulerResults.size;
                                    testResult.outerSize = {
                                        height: rulerResults.size.height + distance.vertical,
                                        width: rulerResults.size.width + distance.horizontal
                                    };
                                    if (mode == "natural") {
                                        if (helper.geo.available[container][side].width >= testResult.outerSize.width && helper.geo.available[container][side].height >= testResult.outerSize.height) {
                                            testResult.fits = true;
                                        } else {
                                            testResult.fits = false;
                                        }
                                    } else {
                                        testResult.fits = rulerResults.fits;
                                    }
                                    if (container == "window") {
                                        if (!testResult.fits) {
                                            testResult.whole = false;
                                        } else {
                                            if (side == "top" || side == "bottom") {
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
                                        if (testResult.mode == "natural" && (testResult.fits || testResult.size.width <= helper.geo.available[container][side].width)) {
                                            return false;
                                        }
                                    }
                                }
                            });
                        }
                    }
                });
                self.__instance._trigger({
                    edit: function(r) {
                        testResults = r;
                    },
                    event: event,
                    helper: helper,
                    results: testResults,
                    type: "positionTested"
                });
                testResults.sort(function(a, b) {
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
                            return a.mode == "natural" ? -1 : 1;
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
                                return a.mode == "natural" ? -1 : 1;
                            }
                        } else {
                            if (a.container == "document" && a.side == "bottom" && a.mode == "natural") {
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
                  case "left":
                  case "right":
                    finalResult.coord.top = Math.floor(finalResult.target - finalResult.size.height / 2);
                    break;

                  case "bottom":
                  case "top":
                    finalResult.coord.left = Math.floor(finalResult.target - finalResult.size.width / 2);
                    break;
                }
                switch (finalResult.side) {
                  case "left":
                    finalResult.coord.left = helper.geo.origin.windowOffset.left - finalResult.outerSize.width;
                    break;

                  case "right":
                    finalResult.coord.left = helper.geo.origin.windowOffset.right + finalResult.distance.horizontal;
                    break;

                  case "top":
                    finalResult.coord.top = helper.geo.origin.windowOffset.top - finalResult.outerSize.height;
                    break;

                  case "bottom":
                    finalResult.coord.top = helper.geo.origin.windowOffset.bottom + finalResult.distance.vertical;
                    break;
                }
                if (finalResult.container == "window") {
                    if (finalResult.side == "top" || finalResult.side == "bottom") {
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
                helper.tooltipParent = self.__instance.option("parent").parent[0];
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
                    edit: function(result) {
                        finalResult = result;
                    },
                    event: event,
                    helper: helper,
                    position: finalResultClone,
                    type: "position"
                });
                if (self.__options.functionPosition) {
                    var result = self.__options.functionPosition.call(self, self.__instance, helper, finalResultClone);
                    if (result) finalResult = result;
                }
                ruler.destroy();
                var arrowCoord, maxVal;
                if (finalResult.side == "top" || finalResult.side == "bottom") {
                    arrowCoord = {
                        prop: "left",
                        val: finalResult.target - finalResult.coord.left
                    };
                    maxVal = finalResult.size.width - this.__options.minIntersection;
                } else {
                    arrowCoord = {
                        prop: "top",
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
                    self.__instance._$tooltip.css("position", "fixed");
                } else {
                    self.__instance._$tooltip.css("position", "");
                }
                self.__instance._$tooltip.css({
                    left: finalResult.coord.left,
                    top: finalResult.coord.top,
                    height: finalResult.size.height,
                    width: finalResult.size.width
                }).find(".tooltipster-arrow").css({
                    left: "",
                    top: ""
                }).css(arrowCoord.prop, arrowCoord.val);
                self.__instance._$tooltip.appendTo(self.__instance.option("parent"));
                self.__instance._trigger({
                    type: "repositioned",
                    event: event,
                    position: finalResult
                });
            },
            __sideChange: function($obj, side) {
                $obj.removeClass("tooltipster-bottom").removeClass("tooltipster-left").removeClass("tooltipster-right").removeClass("tooltipster-top").addClass("tooltipster-" + side);
            },
            __targetFind: function(helper) {
                var target = {}, rects = this.__instance._$origin[0].getClientRects();
                if (rects.length > 1) {
                    var opacity = this.__instance._$origin.css("opacity");
                    if (opacity == 1) {
                        this.__instance._$origin.css("opacity", .99);
                        rects = this.__instance._$origin[0].getClientRects();
                        this.__instance._$origin.css("opacity", 1);
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

var animate_duration = 1250;

var animation = "fade";

$(function() {
    $(".tooltipper").tooltipster({
        theme: "tooltipster-borderless",
        animation: animation,
        animationDuration: animate_duration,
        arrow: true,
        contentAsHTML: true,
        maxWidth: 500
    });
    initAll();
});

function initAll() {
    $(".dt-tooltipper.tooltipstered").tooltipster("destroy");
    $(".dt-tooltipper-large").tooltipster({
        theme: "tooltipster-borderless",
        animation: animation,
        animationDuration: animate_duration,
        arrow: true,
        contentAsHTML: true,
        maxWidth: 500
    });
    $(".dt-tooltipper-small").tooltipster({
        theme: "tooltipster-borderless",
        animation: animation,
        animationDuration: animate_duration,
        arrow: true,
        contentAsHTML: true,
        maxWidth: 250
    });
    $(".tooltipper-ajax.tooltipstered").tooltipster("destroy");
    $(".tooltipper-ajax").tooltipster({
        trigger: "custom",
        triggerOpen: {
            mouseenter: true,
            touchstart: true
        },
        triggerClose: {
            mouseleave: true,
            originClick: true,
            click: true,
            scroll: true,
            tap: true,
            touchLeave: true
        },
        theme: [ "tooltipster-borderless", "tooltipster-custom" ],
        contentAsHTML: true,
        interactive: true,
        animation: animation,
        animationDuration: animate_duration,
        updateAnimation: animation,
        arrow: true,
        minWidth: 250,
        content: "patience, grasshopper...",
        functionBefore: function(instance, helper) {
            var $origin = $(helper.origin);
            if ($origin.data("loaded") !== true) {
                $.post("../ajax/ajax_tooltips.php", {
                    csrf_token: csrf_token
                }, function(data) {
                    if (instance.content() === "") return false;
                    instance.content(data);
                    $origin.data("loaded", true);
                });
            }
        }
    });
}

var config = new Object();

var tt_Debug = true;

var tt_Enabled = true;

var TagsToTip = true;

config.Above = false;

config.BgColor = "#E2E7FF";

config.BgImg = "";

config.BorderColor = "#003099";

config.BorderStyle = "solid";

config.BorderWidth = 1;

config.CenterMouse = false;

config.ClickClose = false;

config.ClickSticky = false;

config.CloseBtn = false;

config.CloseBtnColors = [ "#990000", "#FFFFFF", "#DD3333", "#FFFFFF" ];

config.CloseBtnText = "&#160;X&#160;";

config.CopyContent = true;

config.Delay = 400;

config.Duration = 0;

config.Exclusive = false;

config.FadeIn = 100;

config.FadeOut = 100;

config.FadeInterval = 30;

config.Fix = null;

config.FollowMouse = true;

config.FontColor = "#000000";

config.FontFace = "Verdana,Geneva,sans-serif";

config.FontSize = "8pt";

config.FontWeight = "normal";

config.Height = 0;

config.JumpHorz = false;

config.JumpVert = true;

config.Left = false;

config.OffsetX = 14;

config.OffsetY = 8;

config.Opacity = 100;

config.Padding = 3;

config.Shadow = false;

config.ShadowColor = "#C0C0C0";

config.ShadowWidth = 5;

config.Sticky = false;

config.TextAlign = "left";

config.Title = "";

config.TitleAlign = "left";

config.TitleBgColor = "";

config.TitleFontColor = "#FFFFFF";

config.TitleFontFace = "";

config.TitleFontSize = "";

config.TitlePadding = 2;

config.Width = 0;

function Tip() {
    tt_Tip(arguments, null);
}

function TagToTip() {
    var t2t = tt_GetElt(arguments[0]);
    if (t2t) tt_Tip(arguments, t2t);
}

function UnTip() {
    tt_OpReHref();
    if (tt_aV[DURATION] < 0 && tt_iState & 2) tt_tDurt.Timer("tt_HideInit()", -tt_aV[DURATION], true); else if (!(tt_aV[STICKY] && tt_iState & 2)) tt_HideInit();
}

var tt_aElt = new Array(10), tt_aV = new Array(), tt_sContent, tt_t2t, tt_t2tDad, tt_musX, tt_musY, tt_over, tt_x, tt_y, tt_w, tt_h;

function tt_Extension() {
    tt_ExtCmdEnum();
    tt_aExt[tt_aExt.length] = this;
    return this;
}

function tt_SetTipPos(x, y) {
    var css = tt_aElt[0].style;
    tt_x = x;
    tt_y = y;
    css.left = x + "px";
    css.top = y + "px";
    if (tt_ie56) {
        var ifrm = tt_aElt[tt_aElt.length - 1];
        if (ifrm) {
            ifrm.style.left = css.left;
            ifrm.style.top = css.top;
        }
    }
}

function tt_HideInit() {
    if (tt_iState) {
        tt_ExtCallFncs(0, "HideInit");
        tt_iState &= ~(4 | 8);
        if (tt_flagOpa && tt_aV[FADEOUT]) {
            tt_tFade.EndTimer();
            if (tt_opa) {
                var n = Math.round(tt_aV[FADEOUT] / (tt_aV[FADEINTERVAL] * (tt_aV[OPACITY] / tt_opa)));
                tt_Fade(tt_opa, tt_opa, 0, n);
                return;
            }
        }
        tt_tHide.Timer("tt_Hide();", 1, false);
    }
}

function tt_Hide() {
    if (tt_db && tt_iState) {
        tt_OpReHref();
        if (tt_iState & 2) {
            tt_aElt[0].style.visibility = "hidden";
            tt_ExtCallFncs(0, "Hide");
        }
        tt_tShow.EndTimer();
        tt_tHide.EndTimer();
        tt_tDurt.EndTimer();
        tt_tFade.EndTimer();
        if (!tt_op && !tt_ie) {
            tt_tWaitMov.EndTimer();
            tt_bWait = false;
        }
        if (tt_aV[CLICKCLOSE] || tt_aV[CLICKSTICKY]) tt_RemEvtFnc(document, "mouseup", tt_OnLClick);
        tt_ExtCallFncs(0, "Kill");
        if (tt_t2t && !tt_aV[COPYCONTENT]) tt_UnEl2Tip();
        tt_iState = 0;
        tt_over = null;
        tt_ResetMainDiv();
        if (tt_aElt[tt_aElt.length - 1]) tt_aElt[tt_aElt.length - 1].style.display = "none";
    }
}

function tt_GetElt(id) {
    return document.getElementById ? document.getElementById(id) : document.all ? document.all[id] : null;
}

function tt_GetDivW(el) {
    return el ? el.offsetWidth || el.style.pixelWidth || 0 : 0;
}

function tt_GetDivH(el) {
    return el ? el.offsetHeight || el.style.pixelHeight || 0 : 0;
}

function tt_GetScrollX() {
    return window.pageXOffset || (tt_db ? tt_db.scrollLeft || 0 : 0);
}

function tt_GetScrollY() {
    return window.pageYOffset || (tt_db ? tt_db.scrollTop || 0 : 0);
}

function tt_GetClientW() {
    return tt_GetWndCliSiz("Width");
}

function tt_GetClientH() {
    return tt_GetWndCliSiz("Height");
}

function tt_GetEvtX(e) {
    return e ? typeof e.pageX != tt_u ? e.pageX : e.clientX + tt_GetScrollX() : 0;
}

function tt_GetEvtY(e) {
    return e ? typeof e.pageY != tt_u ? e.pageY : e.clientY + tt_GetScrollY() : 0;
}

function tt_AddEvtFnc(el, sEvt, PFnc) {
    if (el) {
        if (el.addEventListener) el.addEventListener(sEvt, PFnc, false); else el.attachEvent("on" + sEvt, PFnc);
    }
}

function tt_RemEvtFnc(el, sEvt, PFnc) {
    if (el) {
        if (el.removeEventListener) el.removeEventListener(sEvt, PFnc, false); else el.detachEvent("on" + sEvt, PFnc);
    }
}

function tt_GetDad(el) {
    return el.parentNode || el.parentElement || el.offsetParent;
}

function tt_MovDomNode(el, dadFrom, dadTo) {
    if (dadFrom) dadFrom.removeChild(el);
    if (dadTo) dadTo.appendChild(el);
}

var tt_aExt = new Array(), tt_db, tt_op, tt_ie, tt_ie56, tt_bBoxOld, tt_body, tt_ovr_, tt_flagOpa, tt_maxPosX, tt_maxPosY, tt_iState = 0, tt_opa, tt_bJmpVert, tt_bJmpHorz, tt_elDeHref, tt_tShow = new Number(0), tt_tHide = new Number(0), tt_tDurt = new Number(0), tt_tFade = new Number(0), tt_tWaitMov = new Number(0), tt_bWait = false, tt_u = "undefined";

function tt_Init() {
    tt_MkCmdEnum();
    if (!tt_Browser() || !tt_MkMainDiv()) return;
    tt_IsW3cBox();
    tt_OpaSupport();
    tt_AddEvtFnc(document, "mousemove", tt_Move);
    if (TagsToTip || tt_Debug) tt_SetOnloadFnc();
    tt_AddEvtFnc(window, "unload", tt_Hide);
}

function tt_MkCmdEnum() {
    var n = 0;
    for (var i in config) eval("window." + i.toString().toUpperCase() + " = " + n++);
    tt_aV.length = n;
}

function tt_Browser() {
    var n, nv, n6, w3c;
    n = navigator.userAgent.toLowerCase(), nv = navigator.appVersion;
    tt_op = document.defaultView && typeof eval("w" + "indow" + "." + "o" + "p" + "er" + "a") != tt_u;
    tt_ie = n.indexOf("msie") != -1 && document.all && !tt_op;
    if (tt_ie) {
        var ieOld = !document.compatMode || document.compatMode == "BackCompat";
        tt_db = !ieOld ? document.documentElement : document.body || null;
        if (tt_db) tt_ie56 = parseFloat(nv.substring(nv.indexOf("MSIE") + 5)) >= 5.5 && typeof document.body.style.maxHeight == tt_u;
    } else {
        tt_db = document.documentElement || document.body || (document.getElementsByTagName ? document.getElementsByTagName("body")[0] : null);
        if (!tt_op) {
            n6 = document.defaultView && typeof document.defaultView.getComputedStyle != tt_u;
            w3c = !n6 && document.getElementById;
        }
    }
    tt_body = document.getElementsByTagName ? document.getElementsByTagName("body")[0] : document.body || null;
    if (tt_ie || n6 || tt_op || w3c) {
        if (tt_body && tt_db) {
            if (document.attachEvent || document.addEventListener) return true;
        } else tt_Err("wz_tooltip.js must be included INSIDE the body section," + " immediately after the opening <body> tag.", false);
    }
    tt_db = null;
    return false;
}

function tt_MkMainDiv() {
    if (tt_body.insertAdjacentHTML) tt_body.insertAdjacentHTML("afterBegin", tt_MkMainDivHtm()); else if (typeof tt_body.innerHTML != tt_u && document.createElement && tt_body.appendChild) tt_body.appendChild(tt_MkMainDivDom());
    if (window.tt_GetMainDivRefs && tt_GetMainDivRefs()) return true;
    tt_db = null;
    return false;
}

function tt_MkMainDivHtm() {
    return '<div id="WzTtDiV"></div>' + (tt_ie56 ? '<iframe id="WzTtIfRm" src="javascript:false" scrolling="no" frameborder="0" style="filter:Alpha(opacity=0);position:absolute;top:0px;left:0px;display:none;"></iframe>' : "");
}

function tt_MkMainDivDom() {
    var el = document.createElement("div");
    if (el) el.id = "WzTtDiV";
    return el;
}

function tt_GetMainDivRefs() {
    tt_aElt[0] = tt_GetElt("WzTtDiV");
    if (tt_ie56 && tt_aElt[0]) {
        tt_aElt[tt_aElt.length - 1] = tt_GetElt("WzTtIfRm");
        if (!tt_aElt[tt_aElt.length - 1]) tt_aElt[0] = null;
    }
    if (tt_aElt[0]) {
        var css = tt_aElt[0].style;
        css.visibility = "hidden";
        css.position = "absolute";
        css.overflow = "hidden";
        return true;
    }
    return false;
}

function tt_ResetMainDiv() {
    tt_SetTipPos(0, 0);
    tt_aElt[0].innerHTML = "";
    tt_aElt[0].style.width = "0px";
    tt_h = 0;
}

function tt_IsW3cBox() {
    var css = tt_aElt[0].style;
    css.padding = "10px";
    css.width = "40px";
    tt_bBoxOld = tt_GetDivW(tt_aElt[0]) == 40;
    css.padding = "0px";
    tt_ResetMainDiv();
}

function tt_OpaSupport() {
    var css = tt_body.style;
    tt_flagOpa = typeof css.KhtmlOpacity != tt_u ? 2 : typeof css.KHTMLOpacity != tt_u ? 3 : typeof css.MozOpacity != tt_u ? 4 : typeof css.opacity != tt_u ? 5 : typeof css.filter != tt_u ? 1 : 0;
}

function tt_SetOnloadFnc() {
    tt_AddEvtFnc(document, "DOMContentLoaded", tt_HideSrcTags);
    tt_AddEvtFnc(window, "load", tt_HideSrcTags);
    if (tt_body.attachEvent) tt_body.attachEvent("onreadystatechange", function() {
        if (tt_body.readyState == "complete") tt_HideSrcTags();
    });
    if (/WebKit|KHTML/i.test(navigator.userAgent)) {
        var t = setInterval(function() {
            if (/loaded|complete/.test(document.readyState)) {
                clearInterval(t);
                tt_HideSrcTags();
            }
        }, 10);
    }
}

function tt_HideSrcTags() {
    if (!window.tt_HideSrcTags || window.tt_HideSrcTags.done) return;
    window.tt_HideSrcTags.done = true;
    if (!tt_HideSrcTagsRecurs(tt_body)) tt_Err("There are HTML elements to be converted to tooltips.\nIf you" + " want these HTML elements to be automatically hidden, you" + " must edit wz_tooltip.js, and set TagsToTip in the global" + " tooltip configuration to true.", true);
}

function tt_HideSrcTagsRecurs(dad) {
    var ovr, asT2t;
    var a = dad.childNodes || dad.children || null;
    for (var i = a ? a.length : 0; i; ) {
        --i;
        if (!tt_HideSrcTagsRecurs(a[i])) return false;
        ovr = a[i].getAttribute ? a[i].getAttribute("onmouseover") || a[i].getAttribute("onclick") : typeof a[i].onmouseover == "function" ? a[i].onmouseover || a[i].onclick : null;
        if (ovr) {
            asT2t = ovr.toString().match(/TagToTip\s*\(\s*'[^'.]+'\s*[\),]/);
            if (asT2t && asT2t.length) {
                if (!tt_HideSrcTag(asT2t[0])) return false;
            }
        }
    }
    return true;
}

function tt_HideSrcTag(sT2t) {
    var id, el;
    id = sT2t.replace(/.+'([^'.]+)'.+/, "$1");
    el = tt_GetElt(id);
    if (el) {
        if (tt_Debug && !TagsToTip) return false; else el.style.display = "none";
    } else tt_Err("Invalid ID\n'" + id + "'\npassed to TagToTip()." + " There exists no HTML element with that ID.", true);
    return true;
}

function tt_Tip(arg, t2t) {
    if (!tt_db || tt_iState & 8) return;
    if (tt_iState) tt_Hide();
    if (!tt_Enabled) return;
    tt_t2t = t2t;
    if (!tt_ReadCmds(arg)) return;
    tt_iState = 1 | 4;
    tt_AdaptConfig1();
    tt_MkTipContent(arg);
    tt_MkTipSubDivs();
    tt_FormatTip();
    tt_bJmpVert = false;
    tt_bJmpHorz = false;
    tt_maxPosX = tt_GetClientW() + tt_GetScrollX() - tt_w - 1;
    tt_maxPosY = tt_GetClientH() + tt_GetScrollY() - tt_h - 1;
    tt_AdaptConfig2();
    tt_OverInit();
    tt_ShowInit();
    tt_Move();
}

function tt_ReadCmds(a) {
    var i;
    i = 0;
    for (var j in config) tt_aV[i++] = config[j];
    if (a.length & 1) {
        for (i = a.length - 1; i > 0; i -= 2) tt_aV[a[i - 1]] = a[i];
        return true;
    }
    tt_Err("Incorrect call of Tip() or TagToTip().\n" + "Each command must be followed by a value.", true);
    return false;
}

function tt_AdaptConfig1() {
    tt_ExtCallFncs(0, "LoadConfig");
    if (!tt_aV[TITLEBGCOLOR].length) tt_aV[TITLEBGCOLOR] = tt_aV[BORDERCOLOR];
    if (!tt_aV[TITLEFONTCOLOR].length) tt_aV[TITLEFONTCOLOR] = tt_aV[BGCOLOR];
    if (!tt_aV[TITLEFONTFACE].length) tt_aV[TITLEFONTFACE] = tt_aV[FONTFACE];
    if (!tt_aV[TITLEFONTSIZE].length) tt_aV[TITLEFONTSIZE] = tt_aV[FONTSIZE];
    if (tt_aV[CLOSEBTN]) {
        if (!tt_aV[CLOSEBTNCOLORS]) tt_aV[CLOSEBTNCOLORS] = new Array("", "", "", "");
        for (var i = 4; i; ) {
            --i;
            if (!tt_aV[CLOSEBTNCOLORS][i].length) tt_aV[CLOSEBTNCOLORS][i] = i & 1 ? tt_aV[TITLEFONTCOLOR] : tt_aV[TITLEBGCOLOR];
        }
        if (!tt_aV[TITLE].length) tt_aV[TITLE] = " ";
    }
    if (tt_aV[OPACITY] == 100 && typeof tt_aElt[0].style.MozOpacity != tt_u && !Array.every) tt_aV[OPACITY] = 99;
    if (tt_aV[FADEIN] && tt_flagOpa && tt_aV[DELAY] > 100) tt_aV[DELAY] = Math.max(tt_aV[DELAY] - tt_aV[FADEIN], 100);
}

function tt_AdaptConfig2() {
    if (tt_aV[CENTERMOUSE]) {
        tt_aV[OFFSETX] -= tt_w - (tt_aV[SHADOW] ? tt_aV[SHADOWWIDTH] : 0) >> 1;
        tt_aV[JUMPHORZ] = false;
    }
}

function tt_MkTipContent(a) {
    if (tt_t2t) {
        if (tt_aV[COPYCONTENT]) tt_sContent = tt_t2t.innerHTML; else tt_sContent = "";
    } else tt_sContent = a[0];
    tt_ExtCallFncs(0, "CreateContentString");
}

function tt_MkTipSubDivs() {
    var sCss = "position:relative;margin:0px;padding:0px;border-width:0px;left:0px;top:0px;line-height:normal;width:auto;", sTbTrTd = ' cellspacing="0" cellpadding="0" border="0" style="' + sCss + '"><tbody style="' + sCss + '"><tr><td ';
    tt_aElt[0].style.width = tt_GetClientW() + "px";
    tt_aElt[0].innerHTML = "" + (tt_aV[TITLE].length ? '<div id="WzTiTl" style="position:relative;z-index:1;">' + '<table id="WzTiTlTb"' + sTbTrTd + 'id="WzTiTlI" style="' + sCss + '">' + tt_aV[TITLE] + "</td>" + (tt_aV[CLOSEBTN] ? '<td align="right" style="' + sCss + 'text-align:right;">' + '<span id="WzClOsE" style="position:relative;left:2px;padding-left:2px;padding-right:2px;' + "cursor:" + (tt_ie ? "hand" : "pointer") + ';" onmouseover="tt_OnCloseBtnOver(1)" onmouseout="tt_OnCloseBtnOver(0)" onclick="tt_HideInit()">' + tt_aV[CLOSEBTNTEXT] + "</span></td>" : "") + "</tr></tbody></table></div>" : "") + '<div id="WzBoDy" style="position:relative;z-index:0;">' + "<table" + sTbTrTd + 'id="WzBoDyI" style="' + sCss + '">' + tt_sContent + "</td></tr></tbody></table></div>" + (tt_aV[SHADOW] ? '<div id="WzTtShDwR" style="position:absolute;overflow:hidden;"></div>' + '<div id="WzTtShDwB" style="position:relative;overflow:hidden;"></div>' : "");
    tt_GetSubDivRefs();
    if (tt_t2t && !tt_aV[COPYCONTENT]) tt_El2Tip();
    tt_ExtCallFncs(0, "SubDivsCreated");
}

function tt_GetSubDivRefs() {
    var aId = new Array("WzTiTl", "WzTiTlTb", "WzTiTlI", "WzClOsE", "WzBoDy", "WzBoDyI", "WzTtShDwB", "WzTtShDwR");
    for (var i = aId.length; i; --i) tt_aElt[i] = tt_GetElt(aId[i - 1]);
}

function tt_FormatTip() {
    var css, w, h, pad = tt_aV[PADDING], padT, wBrd = tt_aV[BORDERWIDTH], iOffY, iOffSh, iAdd = pad + wBrd << 1;
    if (tt_aV[TITLE].length) {
        padT = tt_aV[TITLEPADDING];
        css = tt_aElt[1].style;
        css.background = tt_aV[TITLEBGCOLOR];
        css.paddingTop = css.paddingBottom = padT + "px";
        css.paddingLeft = css.paddingRight = padT + 2 + "px";
        css = tt_aElt[3].style;
        css.color = tt_aV[TITLEFONTCOLOR];
        if (tt_aV[WIDTH] == -1) css.whiteSpace = "nowrap";
        css.fontFamily = tt_aV[TITLEFONTFACE];
        css.fontSize = tt_aV[TITLEFONTSIZE];
        css.fontWeight = "bold";
        css.textAlign = tt_aV[TITLEALIGN];
        if (tt_aElt[4]) {
            css = tt_aElt[4].style;
            css.background = tt_aV[CLOSEBTNCOLORS][0];
            css.color = tt_aV[CLOSEBTNCOLORS][1];
            css.fontFamily = tt_aV[TITLEFONTFACE];
            css.fontSize = tt_aV[TITLEFONTSIZE];
            css.fontWeight = "bold";
        }
        if (tt_aV[WIDTH] > 0) tt_w = tt_aV[WIDTH]; else {
            tt_w = tt_GetDivW(tt_aElt[3]) + tt_GetDivW(tt_aElt[4]);
            if (tt_aElt[4]) tt_w += pad;
            if (tt_aV[WIDTH] < -1 && tt_w > -tt_aV[WIDTH]) tt_w = -tt_aV[WIDTH];
        }
        iOffY = -wBrd;
    } else {
        tt_w = 0;
        iOffY = 0;
    }
    css = tt_aElt[5].style;
    css.top = iOffY + "px";
    if (wBrd) {
        css.borderColor = tt_aV[BORDERCOLOR];
        css.borderStyle = tt_aV[BORDERSTYLE];
        css.borderWidth = wBrd + "px";
    }
    if (tt_aV[BGCOLOR].length) css.background = tt_aV[BGCOLOR];
    if (tt_aV[BGIMG].length) css.backgroundImage = "url(" + tt_aV[BGIMG] + ")";
    css.padding = pad + "px";
    css.textAlign = tt_aV[TEXTALIGN];
    if (tt_aV[HEIGHT]) {
        css.overflow = "auto";
        if (tt_aV[HEIGHT] > 0) css.height = tt_aV[HEIGHT] + iAdd + "px"; else tt_h = iAdd - tt_aV[HEIGHT];
    }
    css = tt_aElt[6].style;
    css.color = tt_aV[FONTCOLOR];
    css.fontFamily = tt_aV[FONTFACE];
    css.fontSize = tt_aV[FONTSIZE];
    css.fontWeight = tt_aV[FONTWEIGHT];
    css.textAlign = tt_aV[TEXTALIGN];
    if (tt_aV[WIDTH] > 0) w = tt_aV[WIDTH]; else if (tt_aV[WIDTH] == -1 && tt_w) w = tt_w; else {
        w = tt_GetDivW(tt_aElt[6]);
        if (tt_aV[WIDTH] < -1 && w > -tt_aV[WIDTH]) w = -tt_aV[WIDTH];
    }
    if (w > tt_w) tt_w = w;
    tt_w += iAdd;
    if (tt_aV[SHADOW]) {
        tt_w += tt_aV[SHADOWWIDTH];
        iOffSh = Math.floor(tt_aV[SHADOWWIDTH] * 4 / 3);
        css = tt_aElt[7].style;
        css.top = iOffY + "px";
        css.left = iOffSh + "px";
        css.width = tt_w - iOffSh - tt_aV[SHADOWWIDTH] + "px";
        css.height = tt_aV[SHADOWWIDTH] + "px";
        css.background = tt_aV[SHADOWCOLOR];
        css = tt_aElt[8].style;
        css.top = iOffSh + "px";
        css.left = tt_w - tt_aV[SHADOWWIDTH] + "px";
        css.width = tt_aV[SHADOWWIDTH] + "px";
        css.background = tt_aV[SHADOWCOLOR];
    } else iOffSh = 0;
    tt_SetTipOpa(tt_aV[FADEIN] ? 0 : tt_aV[OPACITY]);
    tt_FixSize(iOffY, iOffSh);
}

function tt_FixSize(iOffY, iOffSh) {
    var wIn, wOut, h, add, pad = tt_aV[PADDING], wBrd = tt_aV[BORDERWIDTH], i;
    tt_aElt[0].style.width = tt_w + "px";
    tt_aElt[0].style.pixelWidth = tt_w;
    wOut = tt_w - (tt_aV[SHADOW] ? tt_aV[SHADOWWIDTH] : 0);
    wIn = wOut;
    if (!tt_bBoxOld) wIn -= pad + wBrd << 1;
    tt_aElt[5].style.width = wIn + "px";
    if (tt_aElt[1]) {
        wIn = wOut - (tt_aV[TITLEPADDING] + 2 << 1);
        if (!tt_bBoxOld) wOut = wIn;
        tt_aElt[1].style.width = wOut + "px";
        tt_aElt[2].style.width = wIn + "px";
    }
    if (tt_h) {
        h = tt_GetDivH(tt_aElt[5]);
        if (h > tt_h) {
            if (!tt_bBoxOld) tt_h -= pad + wBrd << 1;
            tt_aElt[5].style.height = tt_h + "px";
        }
    }
    tt_h = tt_GetDivH(tt_aElt[0]) + iOffY;
    if (tt_aElt[8]) tt_aElt[8].style.height = tt_h - iOffSh + "px";
    i = tt_aElt.length - 1;
    if (tt_aElt[i]) {
        tt_aElt[i].style.width = tt_w + "px";
        tt_aElt[i].style.height = tt_h + "px";
    }
}

function tt_DeAlt(el) {
    var aKid;
    if (el) {
        if (el.alt) el.alt = "";
        if (el.title) el.title = "";
        aKid = el.childNodes || el.children || null;
        if (aKid) {
            for (var i = aKid.length; i; ) tt_DeAlt(aKid[--i]);
        }
    }
}

function tt_OpDeHref(el) {
    if (!tt_op) return;
    if (tt_elDeHref) tt_OpReHref();
    while (el) {
        if (el.hasAttribute && el.hasAttribute("href")) {
            el.t_href = el.getAttribute("href");
            el.t_stats = window.status;
            el.removeAttribute("href");
            el.style.cursor = "hand";
            tt_AddEvtFnc(el, "mousedown", tt_OpReHref);
            window.status = el.t_href;
            tt_elDeHref = el;
            break;
        }
        el = tt_GetDad(el);
    }
}

function tt_OpReHref() {
    if (tt_elDeHref) {
        tt_elDeHref.setAttribute("href", tt_elDeHref.t_href);
        tt_RemEvtFnc(tt_elDeHref, "mousedown", tt_OpReHref);
        window.status = tt_elDeHref.t_stats;
        tt_elDeHref = null;
    }
}

function tt_El2Tip() {
    var css = tt_t2t.style;
    tt_t2t.t_cp = css.position;
    tt_t2t.t_cl = css.left;
    tt_t2t.t_ct = css.top;
    tt_t2t.t_cd = css.display;
    tt_t2tDad = tt_GetDad(tt_t2t);
    tt_MovDomNode(tt_t2t, tt_t2tDad, tt_aElt[6]);
    css.display = "block";
    css.position = "static";
    css.left = css.top = css.marginLeft = css.marginTop = "0px";
}

function tt_UnEl2Tip() {
    var css = tt_t2t.style;
    css.display = tt_t2t.t_cd;
    tt_MovDomNode(tt_t2t, tt_GetDad(tt_t2t), tt_t2tDad);
    css.position = tt_t2t.t_cp;
    css.left = tt_t2t.t_cl;
    css.top = tt_t2t.t_ct;
    tt_t2tDad = null;
}

function tt_OverInit() {
    if (window.event) tt_over = window.event.target || window.event.srcElement; else tt_over = tt_ovr_;
    tt_DeAlt(tt_over);
    tt_OpDeHref(tt_over);
}

function tt_ShowInit() {
    tt_tShow.Timer("tt_Show()", tt_aV[DELAY], true);
    if (tt_aV[CLICKCLOSE] || tt_aV[CLICKSTICKY]) tt_AddEvtFnc(document, "mouseup", tt_OnLClick);
}

function tt_Show() {
    var css = tt_aElt[0].style;
    css.zIndex = Math.max(window.dd && dd.z ? dd.z + 2 : 0, 1010);
    if (tt_aV[STICKY] || !tt_aV[FOLLOWMOUSE]) tt_iState &= ~4;
    if (tt_aV[EXCLUSIVE]) tt_iState |= 8;
    if (tt_aV[DURATION] > 0) tt_tDurt.Timer("tt_HideInit()", tt_aV[DURATION], true);
    tt_ExtCallFncs(0, "Show");
    css.visibility = "visible";
    tt_iState |= 2;
    if (tt_aV[FADEIN]) tt_Fade(0, 0, tt_aV[OPACITY], Math.round(tt_aV[FADEIN] / tt_aV[FADEINTERVAL]));
    tt_ShowIfrm();
}

function tt_ShowIfrm() {
    if (tt_ie56) {
        var ifrm = tt_aElt[tt_aElt.length - 1];
        if (ifrm) {
            var css = ifrm.style;
            css.zIndex = tt_aElt[0].style.zIndex - 1;
            css.display = "block";
        }
    }
}

function tt_Move(e) {
    if (e) tt_ovr_ = e.target || e.srcElement;
    e = e || window.event;
    if (e) {
        tt_musX = tt_GetEvtX(e);
        tt_musY = tt_GetEvtY(e);
    }
    if (tt_iState & 4) {
        if (!tt_op && !tt_ie) {
            if (tt_bWait) return;
            tt_bWait = true;
            tt_tWaitMov.Timer("tt_bWait = false;", 1, true);
        }
        if (tt_aV[FIX]) {
            tt_iState &= ~4;
            tt_PosFix();
        } else if (!tt_ExtCallFncs(e, "MoveBefore")) tt_SetTipPos(tt_Pos(0), tt_Pos(1));
        tt_ExtCallFncs([ tt_musX, tt_musY ], "MoveAfter");
    }
}

function tt_Pos(iDim) {
    var iX, bJmpMod, cmdAlt, cmdOff, cx, iMax, iScrl, iMus, bJmp;
    if (iDim) {
        bJmpMod = tt_aV[JUMPVERT];
        cmdAlt = ABOVE;
        cmdOff = OFFSETY;
        cx = tt_h;
        iMax = tt_maxPosY;
        iScrl = tt_GetScrollY();
        iMus = tt_musY;
        bJmp = tt_bJmpVert;
    } else {
        bJmpMod = tt_aV[JUMPHORZ];
        cmdAlt = LEFT;
        cmdOff = OFFSETX;
        cx = tt_w;
        iMax = tt_maxPosX;
        iScrl = tt_GetScrollX();
        iMus = tt_musX;
        bJmp = tt_bJmpHorz;
    }
    if (bJmpMod) {
        if (tt_aV[cmdAlt] && (!bJmp || tt_CalcPosAlt(iDim) >= iScrl + 16)) iX = tt_PosAlt(iDim); else if (!tt_aV[cmdAlt] && bJmp && tt_CalcPosDef(iDim) > iMax - 16) iX = tt_PosAlt(iDim); else iX = tt_PosDef(iDim);
    } else {
        iX = iMus;
        if (tt_aV[cmdAlt]) iX -= cx + tt_aV[cmdOff] - (tt_aV[SHADOW] ? tt_aV[SHADOWWIDTH] : 0); else iX += tt_aV[cmdOff];
    }
    if (iX > iMax) iX = bJmpMod ? tt_PosAlt(iDim) : iMax;
    if (iX < iScrl) iX = bJmpMod ? tt_PosDef(iDim) : iScrl;
    return iX;
}

function tt_PosDef(iDim) {
    if (iDim) tt_bJmpVert = tt_aV[ABOVE]; else tt_bJmpHorz = tt_aV[LEFT];
    return tt_CalcPosDef(iDim);
}

function tt_PosAlt(iDim) {
    if (iDim) tt_bJmpVert = !tt_aV[ABOVE]; else tt_bJmpHorz = !tt_aV[LEFT];
    return tt_CalcPosAlt(iDim);
}

function tt_CalcPosDef(iDim) {
    return iDim ? tt_musY + tt_aV[OFFSETY] : tt_musX + tt_aV[OFFSETX];
}

function tt_CalcPosAlt(iDim) {
    var cmdOff = iDim ? OFFSETY : OFFSETX;
    var dx = tt_aV[cmdOff] - (tt_aV[SHADOW] ? tt_aV[SHADOWWIDTH] : 0);
    if (tt_aV[cmdOff] > 0 && dx <= 0) dx = 1;
    return (iDim ? tt_musY - tt_h : tt_musX - tt_w) - dx;
}

function tt_PosFix() {
    var iX, iY;
    if (typeof tt_aV[FIX][0] == "number") {
        iX = tt_aV[FIX][0];
        iY = tt_aV[FIX][1];
    } else {
        if (typeof tt_aV[FIX][0] == "string") el = tt_GetElt(tt_aV[FIX][0]); else el = tt_aV[FIX][0];
        iX = tt_aV[FIX][1];
        iY = tt_aV[FIX][2];
        if (!tt_aV[ABOVE] && el) iY += tt_GetDivH(el);
        for (;el; el = el.offsetParent) {
            iX += el.offsetLeft || 0;
            iY += el.offsetTop || 0;
        }
    }
    if (tt_aV[ABOVE]) iY -= tt_h;
    tt_SetTipPos(iX, iY);
}

function tt_Fade(a, now, z, n) {
    if (n) {
        now += Math.round((z - now) / n);
        if (z > a ? now >= z : now <= z) now = z; else tt_tFade.Timer("tt_Fade(" + a + "," + now + "," + z + "," + (n - 1) + ")", tt_aV[FADEINTERVAL], true);
    }
    now ? tt_SetTipOpa(now) : tt_Hide();
}

function tt_SetTipOpa(opa) {
    tt_SetOpa(tt_aElt[5], opa);
    if (tt_aElt[1]) tt_SetOpa(tt_aElt[1], opa);
    if (tt_aV[SHADOW]) {
        opa = Math.round(opa * .8);
        tt_SetOpa(tt_aElt[7], opa);
        tt_SetOpa(tt_aElt[8], opa);
    }
}

function tt_OnCloseBtnOver(iOver) {
    var css = tt_aElt[4].style;
    iOver <<= 1;
    css.background = tt_aV[CLOSEBTNCOLORS][iOver];
    css.color = tt_aV[CLOSEBTNCOLORS][iOver + 1];
}

function tt_OnLClick(e) {
    e = e || window.event;
    if (!(e.button && e.button & 2 || e.which && e.which == 3)) {
        if (tt_aV[CLICKSTICKY] && tt_iState & 4) {
            tt_aV[STICKY] = true;
            tt_iState &= ~4;
        } else if (tt_aV[CLICKCLOSE]) tt_HideInit();
    }
}

function tt_Int(x) {
    var y;
    return isNaN(y = parseInt(x)) ? 0 : y;
}

Number.prototype.Timer = function(s, iT, bUrge) {
    if (!this.value || bUrge) this.value = window.setTimeout(s, iT);
};

Number.prototype.EndTimer = function() {
    if (this.value) {
        window.clearTimeout(this.value);
        this.value = 0;
    }
};

function tt_GetWndCliSiz(s) {
    var db, y = window["inner" + s], sC = "client" + s, sN = "number";
    if (typeof y == sN) {
        var y2;
        return (db = document.body) && typeof (y2 = db[sC]) == sN && y2 && y2 <= y ? y2 : (db = document.documentElement) && typeof (y2 = db[sC]) == sN && y2 && y2 <= y ? y2 : y;
    }
    return (db = document.documentElement) && (y = db[sC]) ? y : document.body[sC];
}

function tt_SetOpa(el, opa) {
    var css = el.style;
    tt_opa = opa;
    if (tt_flagOpa == 1) {
        if (opa < 100) {
            if (typeof el.filtNo == tt_u) el.filtNo = css.filter;
            var bVis = css.visibility != "hidden";
            css.zoom = "100%";
            if (!bVis) css.visibility = "visible";
            css.filter = "alpha(opacity=" + opa + ")";
            if (!bVis) css.visibility = "hidden";
        } else if (typeof el.filtNo != tt_u) css.filter = el.filtNo;
    } else {
        opa /= 100;
        switch (tt_flagOpa) {
          case 2:
            css.KhtmlOpacity = opa;
            break;

          case 3:
            css.KHTMLOpacity = opa;
            break;

          case 4:
            css.MozOpacity = opa;
            break;

          case 5:
            css.opacity = opa;
            break;
        }
    }
}

function tt_Err(sErr, bIfDebug) {
    if (tt_Debug || !bIfDebug) alert("Tooltip Script Error Message:\n\n" + sErr);
}

function tt_ExtCmdEnum() {
    var s;
    for (var i in config) {
        s = "window." + i.toString().toUpperCase();
        if (eval("typeof(" + s + ") == tt_u")) {
            eval(s + " = " + tt_aV.length);
            tt_aV[tt_aV.length] = null;
        }
    }
}

function tt_ExtCallFncs(arg, sFnc) {
    var b = false;
    for (var i = tt_aExt.length; i; ) {
        --i;
        var fnc = tt_aExt[i]["On" + sFnc];
        if (fnc && fnc(arg)) b = true;
    }
    return b;
}

tt_Init();

function PopUp(url, name, width, height, center, resize, scroll, posleft, postop) {
    showx = "";
    showy = "";
    if (posleft != 0) {
        X = posleft;
    }
    if (postop != 0) {
        Y = postop;
    }
    if (!scroll) {
        scroll = 1;
    }
    if (!resize) {
        resize = 1;
    }
    if (parseInt(navigator.appVersion) >= 4 && center) {
        X = (screen.width - width) / 2;
        Y = (screen.height - height) / 2;
    }
    if (X > 0) {
        showx = ",left=" + X;
    }
    if (Y > 0) {
        showy = ",top=" + Y;
    }
    if (scroll != 0) {
        scroll = 1;
    }
    var Win = window.open(url, name, "width=" + width + ",height=" + height + showx + showy + ",resizable=" + resize + ",scrollbars=" + scroll + ",location=no,directories=no,status=no,menubar=no,toolbar=no");
    event.preventDefault();
}

var offset = 250;

var animate_duration = 1250;

var easing = "swing";

function themes() {
    PopUp("take_theme.php", "My themes", 300, 150, 1, 0);
}

function language_select() {
    PopUp("take_lang.php", "My language", 300, 150, 1, 0);
}

function radio() {
    PopUp("radio_popup.php", "My Radio", 800, 700, 1, 0);
}

function refrClock() {
    var d = new Date();
    var s = d.getSeconds();
    var m = d.getMinutes();
    var h = d.getHours();
    var day = d.getDay();
    var date = d.getDate();
    var month = d.getMonth();
    var year = d.getFullYear();
    var am_pm;
    if (s < 10) {
        s = "0" + s;
    }
    if (m < 10) {
        m = "0" + m;
    }
    if (h > 12) {
        h -= 12;
        am_pm = "pm";
    } else {
        am_pm = "am";
    }
    document.getElementById("clock").innerHTML = h + ":" + m + ":" + s + " " + am_pm;
    setTimeout("refrClock()", 1e3);
}

function togglepic(bu, picid, formid) {
    var pic = document.getElementById(picid);
    var form = document.getElementById(formid);
    if (pic.src == bu + "/images/plus.gif") {
        pic.src = bu + "/images/minus.gif";
        form.value = "minus";
    } else {
        pic.src = bu + "/images/plus.gif";
        form.value = "plus";
    }
}

$(function() {
    if ($("#clock").length) {
        refrClock();
    }
    if ($("#triviabox").length) {
        $("#triviabox").iFrameResize({
            enablePublicMethods: true
        });
    }
    if ($(".password").length) {
        $(".password").pstrength();
    }
    if ($("#help_open").length) {
        $("#help_open").click(function() {
            $("#help").slideToggle(animate_duration, easing, function() {});
        });
    }
    if (typeof Storage !== "undefined") {
        $(".flipper").click(function(e) {
            $(this).next().slideToggle(animate_duration, easing, function() {
                var id = $(this).parent().attr("id");
                if (!$(this).is(":visible")) {
                    localStorage.setItem(id, "closed");
                    $(this).parent().addClass("no-margin no-padding");
                } else {
                    localStorage.setItem(id, "open");
                    $(this).parent().removeClass("no-margin no-padding");
                }
            });
            $(this).parent().find(".fa").toggleClass("fa-angle-up fa-angle-down");
        });
    }
    $(window).scroll(function() {
        if ($(this).scrollTop() > offset) {
            $(".back-to-top").fadeIn(animate_duration);
        } else {
            $(".back-to-top").fadeOut(animate_duration);
        }
    });
    $(".back-to-top").click(function(event) {
        event.preventDefault();
        $("html, body").animate({
            scrollTop: 0
        }, animate_duration, easing);
        $(".back-to-top").blur();
        return false;
    });
    if ($("#request_form").length) {
        $("#request_form").validate();
    }
    if ($("#offer_form").length) {
        $("#offer_form").validate();
    }
    if ($("#upload_form").length) {
        setupDependencies("upload_form");
    }
    if ($("#edit_form").length) {
        setupDependencies("edit_form");
    }
    if ($("#carousel-container").length) {
        $("#icarousel").iCarousel({
            easing: "ease-in-out",
            slides: 10,
            make3D: 1,
            perspective: 590,
            animationSpeed: animate_duration,
            pauseTime: 5e3,
            startSlide: 1,
            directionNav: 1,
            autoPlay: 1,
            keyboardNav: 1,
            touchNav: 1,
            mouseWheel: true,
            pauseOnHover: 1,
            nextLabel: "Next",
            previousLabel: "Previous",
            playLabel: "Play",
            pauseLabel: "Pause",
            randomStart: 1,
            slidesSpace: "200",
            slidesTopSpace: "auto",
            direction: "rtl",
            timer: "360bar",
            timerBg: "#000",
            timerColor: "#0f0",
            timerOpacity: .4,
            timerDiameter: 35,
            timerPadding: 4,
            timerStroke: 3,
            timerBarStroke: 1,
            timerBarStrokeColor: "#FFF",
            timerBarStrokeStyle: "solid",
            timerBarStrokeRadius: 4,
            timerPosition: "top-right",
            timerX: 10,
            timerY: 10
        });
    }
    if ($("#IE_ALERT").length) {
        if (navigator.userAgent.search("MSIE") >= 0) {
            $("#IE_ALERT").slideToggle(animate_duration, easing, function() {});
        }
    }
    $(window).resize(function() {
        var windowWidth = $(window).width();
        if (windowWidth > 768) {
            $("#menuWrapper").show();
        }
    });
    $("#hamburger").click(function(event) {
        event.preventDefault();
        $("#navbar").addClass("showNav");
        var winHeight = $(window).outerHeight();
        $("#menuWrapper").css("height", winHeight + "px");
        $("#menuWrapper").slideToggle(animate_duration, easing, function() {});
    });
    $("#close").click(function(event) {
        event.preventDefault();
        $("#menuWrapper").slideToggle(animate_duration, easing, function() {
            $("#navbar").removeClass("showNav");
            $("#menuWrapper").css("height", "auto");
        });
    });
    $("#menuWrapper ul li").hover(function() {
        var el = $(this).children("ul");
        if (el.hasClass("hov")) {
            $(el).removeClass("hov");
        } else {
            $(el).addClass("hov");
        }
    });
    if ($(".content").length) {
        $(".h1").click(function() {
            $(".content").slideToggle(animate_duration, easing, function() {});
        });
    }
    if ($("#thanks_holder").length) {
        show_thanks(tid);
    }
    if ($("#tz-checkdst").length) {
        if (!$("#tz-checkdst").is(":checked")) {
            $("#tz-checkmanual").show();
        }
    }
    $("#tz-checkdst").click(function() {
        $("#tz-checkmanual").slideToggle(animate_duration, easing, function() {});
    });
    $('li a[href=".' + this.location.pathname + this.location.search + '"]').addClass("is_active");
    if ($("#checkThemAll").length) {
        $("#checkThemAll").change(function() {
            $("input:checkbox").prop("checked", $(this).prop("checked"));
        });
    }
    if ($("#checkAll").length) {
        $("#checkAll").change(function() {
            $("#checkbox_container :checkbox").prop("checked", $(this).prop("checked"));
        });
        if ($("#checkbox_container :checkbox:checked").length == $("#checkbox_container :checkbox").length) {
            $("#checkAll").prop("checked", true);
        }
        $("#checkbox_container :checkbox").click(function() {
            if ($("#checkbox_container :checkbox:checked").length == $("#checkbox_container :checkbox").length) {
                $("#checkAll").prop("checked", true);
            } else {
                $("#checkAll").prop("checked", false);
            }
        });
    }
    if ($("#accordion").length) {
        $("#accordion").accordion({
            collapsible: true,
            active: false,
            animate: animate_duration,
            heightStyle: "content"
        });
    }
    if ($(".alert").length) {
        setTimeout(function() {
            $(".alert").slideUp(animate_duration, function() {
                $(".alert").remove();
            });
        }, 15e3);
    }
    $('a[href*="#"]').not('[href="#"]').not('[href="#0"]').click(function(event) {
        if (location.pathname.replace(/^\//, "") == this.pathname.replace(/^\//, "") && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $("[name=" + this.hash.slice(1) + "]");
            if (target.length) {
                event.preventDefault();
                $("html, body").animate({
                    scrollTop: target.offset().top - 50
                }, animate_duration, function() {
                    var $target = $(target);
                    $target.focus();
                    if ($target.is(":focus")) {
                        return false;
                    } else {
                        $target.attr("tabindex", "-1");
                        $target.focus();
                    }
                });
            }
        }
    });
});