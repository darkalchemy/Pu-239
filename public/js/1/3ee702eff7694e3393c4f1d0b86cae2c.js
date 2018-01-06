(function(global, factory) {
    "use strict";
    if (typeof module === "object" && typeof module.exports === "object") {
        module.exports = global.document ? factory(global, true) : function(w) {
            if (!w.document) {
                throw new Error("jQuery requires a window with a document");
            }
            return factory(w);
        };
    } else {
        factory(global);
    }
})(typeof window !== "undefined" ? window : this, function(window, noGlobal) {
    "use strict";
    var arr = [];
    var document = window.document;
    var getProto = Object.getPrototypeOf;
    var slice = arr.slice;
    var concat = arr.concat;
    var push = arr.push;
    var indexOf = arr.indexOf;
    var class2type = {};
    var toString = class2type.toString;
    var hasOwn = class2type.hasOwnProperty;
    var fnToString = hasOwn.toString;
    var ObjectFunctionString = fnToString.call(Object);
    var support = {};
    function DOMEval(code, doc) {
        doc = doc || document;
        var script = doc.createElement("script");
        script.text = code;
        doc.head.appendChild(script).parentNode.removeChild(script);
    }
    var version = "3.2.1", jQuery = function(selector, context) {
        return new jQuery.fn.init(selector, context);
    }, rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, rmsPrefix = /^-ms-/, rdashAlpha = /-([a-z])/g, fcamelCase = function(all, letter) {
        return letter.toUpperCase();
    };
    jQuery.fn = jQuery.prototype = {
        jquery: version,
        constructor: jQuery,
        length: 0,
        toArray: function() {
            return slice.call(this);
        },
        get: function(num) {
            if (num == null) {
                return slice.call(this);
            }
            return num < 0 ? this[num + this.length] : this[num];
        },
        pushStack: function(elems) {
            var ret = jQuery.merge(this.constructor(), elems);
            ret.prevObject = this;
            return ret;
        },
        each: function(callback) {
            return jQuery.each(this, callback);
        },
        map: function(callback) {
            return this.pushStack(jQuery.map(this, function(elem, i) {
                return callback.call(elem, i, elem);
            }));
        },
        slice: function() {
            return this.pushStack(slice.apply(this, arguments));
        },
        first: function() {
            return this.eq(0);
        },
        last: function() {
            return this.eq(-1);
        },
        eq: function(i) {
            var len = this.length, j = +i + (i < 0 ? len : 0);
            return this.pushStack(j >= 0 && j < len ? [ this[j] ] : []);
        },
        end: function() {
            return this.prevObject || this.constructor();
        },
        push: push,
        sort: arr.sort,
        splice: arr.splice
    };
    jQuery.extend = jQuery.fn.extend = function() {
        var options, name, src, copy, copyIsArray, clone, target = arguments[0] || {}, i = 1, length = arguments.length, deep = false;
        if (typeof target === "boolean") {
            deep = target;
            target = arguments[i] || {};
            i++;
        }
        if (typeof target !== "object" && !jQuery.isFunction(target)) {
            target = {};
        }
        if (i === length) {
            target = this;
            i--;
        }
        for (;i < length; i++) {
            if ((options = arguments[i]) != null) {
                for (name in options) {
                    src = target[name];
                    copy = options[name];
                    if (target === copy) {
                        continue;
                    }
                    if (deep && copy && (jQuery.isPlainObject(copy) || (copyIsArray = Array.isArray(copy)))) {
                        if (copyIsArray) {
                            copyIsArray = false;
                            clone = src && Array.isArray(src) ? src : [];
                        } else {
                            clone = src && jQuery.isPlainObject(src) ? src : {};
                        }
                        target[name] = jQuery.extend(deep, clone, copy);
                    } else if (copy !== undefined) {
                        target[name] = copy;
                    }
                }
            }
        }
        return target;
    };
    jQuery.extend({
        expando: "jQuery" + (version + Math.random()).replace(/\D/g, ""),
        isReady: true,
        error: function(msg) {
            throw new Error(msg);
        },
        noop: function() {},
        isFunction: function(obj) {
            return jQuery.type(obj) === "function";
        },
        isWindow: function(obj) {
            return obj != null && obj === obj.window;
        },
        isNumeric: function(obj) {
            var type = jQuery.type(obj);
            return (type === "number" || type === "string") && !isNaN(obj - parseFloat(obj));
        },
        isPlainObject: function(obj) {
            var proto, Ctor;
            if (!obj || toString.call(obj) !== "[object Object]") {
                return false;
            }
            proto = getProto(obj);
            if (!proto) {
                return true;
            }
            Ctor = hasOwn.call(proto, "constructor") && proto.constructor;
            return typeof Ctor === "function" && fnToString.call(Ctor) === ObjectFunctionString;
        },
        isEmptyObject: function(obj) {
            var name;
            for (name in obj) {
                return false;
            }
            return true;
        },
        type: function(obj) {
            if (obj == null) {
                return obj + "";
            }
            return typeof obj === "object" || typeof obj === "function" ? class2type[toString.call(obj)] || "object" : typeof obj;
        },
        globalEval: function(code) {
            DOMEval(code);
        },
        camelCase: function(string) {
            return string.replace(rmsPrefix, "ms-").replace(rdashAlpha, fcamelCase);
        },
        each: function(obj, callback) {
            var length, i = 0;
            if (isArrayLike(obj)) {
                length = obj.length;
                for (;i < length; i++) {
                    if (callback.call(obj[i], i, obj[i]) === false) {
                        break;
                    }
                }
            } else {
                for (i in obj) {
                    if (callback.call(obj[i], i, obj[i]) === false) {
                        break;
                    }
                }
            }
            return obj;
        },
        trim: function(text) {
            return text == null ? "" : (text + "").replace(rtrim, "");
        },
        makeArray: function(arr, results) {
            var ret = results || [];
            if (arr != null) {
                if (isArrayLike(Object(arr))) {
                    jQuery.merge(ret, typeof arr === "string" ? [ arr ] : arr);
                } else {
                    push.call(ret, arr);
                }
            }
            return ret;
        },
        inArray: function(elem, arr, i) {
            return arr == null ? -1 : indexOf.call(arr, elem, i);
        },
        merge: function(first, second) {
            var len = +second.length, j = 0, i = first.length;
            for (;j < len; j++) {
                first[i++] = second[j];
            }
            first.length = i;
            return first;
        },
        grep: function(elems, callback, invert) {
            var callbackInverse, matches = [], i = 0, length = elems.length, callbackExpect = !invert;
            for (;i < length; i++) {
                callbackInverse = !callback(elems[i], i);
                if (callbackInverse !== callbackExpect) {
                    matches.push(elems[i]);
                }
            }
            return matches;
        },
        map: function(elems, callback, arg) {
            var length, value, i = 0, ret = [];
            if (isArrayLike(elems)) {
                length = elems.length;
                for (;i < length; i++) {
                    value = callback(elems[i], i, arg);
                    if (value != null) {
                        ret.push(value);
                    }
                }
            } else {
                for (i in elems) {
                    value = callback(elems[i], i, arg);
                    if (value != null) {
                        ret.push(value);
                    }
                }
            }
            return concat.apply([], ret);
        },
        guid: 1,
        proxy: function(fn, context) {
            var tmp, args, proxy;
            if (typeof context === "string") {
                tmp = fn[context];
                context = fn;
                fn = tmp;
            }
            if (!jQuery.isFunction(fn)) {
                return undefined;
            }
            args = slice.call(arguments, 2);
            proxy = function() {
                return fn.apply(context || this, args.concat(slice.call(arguments)));
            };
            proxy.guid = fn.guid = fn.guid || jQuery.guid++;
            return proxy;
        },
        now: Date.now,
        support: support
    });
    if (typeof Symbol === "function") {
        jQuery.fn[Symbol.iterator] = arr[Symbol.iterator];
    }
    jQuery.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "), function(i, name) {
        class2type["[object " + name + "]"] = name.toLowerCase();
    });
    function isArrayLike(obj) {
        var length = !!obj && "length" in obj && obj.length, type = jQuery.type(obj);
        if (type === "function" || jQuery.isWindow(obj)) {
            return false;
        }
        return type === "array" || length === 0 || typeof length === "number" && length > 0 && length - 1 in obj;
    }
    var Sizzle = function(window) {
        var i, support, Expr, getText, isXML, tokenize, compile, select, outermostContext, sortInput, hasDuplicate, setDocument, document, docElem, documentIsHTML, rbuggyQSA, rbuggyMatches, matches, contains, expando = "sizzle" + 1 * new Date(), preferredDoc = window.document, dirruns = 0, done = 0, classCache = createCache(), tokenCache = createCache(), compilerCache = createCache(), sortOrder = function(a, b) {
            if (a === b) {
                hasDuplicate = true;
            }
            return 0;
        }, hasOwn = {}.hasOwnProperty, arr = [], pop = arr.pop, push_native = arr.push, push = arr.push, slice = arr.slice, indexOf = function(list, elem) {
            var i = 0, len = list.length;
            for (;i < len; i++) {
                if (list[i] === elem) {
                    return i;
                }
            }
            return -1;
        }, booleans = "checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped", whitespace = "[\\x20\\t\\r\\n\\f]", identifier = "(?:\\\\.|[\\w-]|[^\0-\\xa0])+", attributes = "\\[" + whitespace + "*(" + identifier + ")(?:" + whitespace + "*([*^$|!~]?=)" + whitespace + "*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|(" + identifier + "))|)" + whitespace + "*\\]", pseudos = ":(" + identifier + ")(?:\\((" + "('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|" + "((?:\\\\.|[^\\\\()[\\]]|" + attributes + ")*)|" + ".*" + ")\\)|)", rwhitespace = new RegExp(whitespace + "+", "g"), rtrim = new RegExp("^" + whitespace + "+|((?:^|[^\\\\])(?:\\\\.)*)" + whitespace + "+$", "g"), rcomma = new RegExp("^" + whitespace + "*," + whitespace + "*"), rcombinators = new RegExp("^" + whitespace + "*([>+~]|" + whitespace + ")" + whitespace + "*"), rattributeQuotes = new RegExp("=" + whitespace + "*([^\\]'\"]*?)" + whitespace + "*\\]", "g"), rpseudo = new RegExp(pseudos), ridentifier = new RegExp("^" + identifier + "$"), matchExpr = {
            ID: new RegExp("^#(" + identifier + ")"),
            CLASS: new RegExp("^\\.(" + identifier + ")"),
            TAG: new RegExp("^(" + identifier + "|[*])"),
            ATTR: new RegExp("^" + attributes),
            PSEUDO: new RegExp("^" + pseudos),
            CHILD: new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(" + whitespace + "*(even|odd|(([+-]|)(\\d*)n|)" + whitespace + "*(?:([+-]|)" + whitespace + "*(\\d+)|))" + whitespace + "*\\)|)", "i"),
            bool: new RegExp("^(?:" + booleans + ")$", "i"),
            needsContext: new RegExp("^" + whitespace + "*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(" + whitespace + "*((?:-\\d)?\\d*)" + whitespace + "*\\)|)(?=[^-]|$)", "i")
        }, rinputs = /^(?:input|select|textarea|button)$/i, rheader = /^h\d$/i, rnative = /^[^{]+\{\s*\[native \w/, rquickExpr = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/, rsibling = /[+~]/, runescape = new RegExp("\\\\([\\da-f]{1,6}" + whitespace + "?|(" + whitespace + ")|.)", "ig"), funescape = function(_, escaped, escapedWhitespace) {
            var high = "0x" + escaped - 65536;
            return high !== high || escapedWhitespace ? escaped : high < 0 ? String.fromCharCode(high + 65536) : String.fromCharCode(high >> 10 | 55296, high & 1023 | 56320);
        }, rcssescape = /([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g, fcssescape = function(ch, asCodePoint) {
            if (asCodePoint) {
                if (ch === "\0") {
                    return "�";
                }
                return ch.slice(0, -1) + "\\" + ch.charCodeAt(ch.length - 1).toString(16) + " ";
            }
            return "\\" + ch;
        }, unloadHandler = function() {
            setDocument();
        }, disabledAncestor = addCombinator(function(elem) {
            return elem.disabled === true && ("form" in elem || "label" in elem);
        }, {
            dir: "parentNode",
            next: "legend"
        });
        try {
            push.apply(arr = slice.call(preferredDoc.childNodes), preferredDoc.childNodes);
            arr[preferredDoc.childNodes.length].nodeType;
        } catch (e) {
            push = {
                apply: arr.length ? function(target, els) {
                    push_native.apply(target, slice.call(els));
                } : function(target, els) {
                    var j = target.length, i = 0;
                    while (target[j++] = els[i++]) {}
                    target.length = j - 1;
                }
            };
        }
        function Sizzle(selector, context, results, seed) {
            var m, i, elem, nid, match, groups, newSelector, newContext = context && context.ownerDocument, nodeType = context ? context.nodeType : 9;
            results = results || [];
            if (typeof selector !== "string" || !selector || nodeType !== 1 && nodeType !== 9 && nodeType !== 11) {
                return results;
            }
            if (!seed) {
                if ((context ? context.ownerDocument || context : preferredDoc) !== document) {
                    setDocument(context);
                }
                context = context || document;
                if (documentIsHTML) {
                    if (nodeType !== 11 && (match = rquickExpr.exec(selector))) {
                        if (m = match[1]) {
                            if (nodeType === 9) {
                                if (elem = context.getElementById(m)) {
                                    if (elem.id === m) {
                                        results.push(elem);
                                        return results;
                                    }
                                } else {
                                    return results;
                                }
                            } else {
                                if (newContext && (elem = newContext.getElementById(m)) && contains(context, elem) && elem.id === m) {
                                    results.push(elem);
                                    return results;
                                }
                            }
                        } else if (match[2]) {
                            push.apply(results, context.getElementsByTagName(selector));
                            return results;
                        } else if ((m = match[3]) && support.getElementsByClassName && context.getElementsByClassName) {
                            push.apply(results, context.getElementsByClassName(m));
                            return results;
                        }
                    }
                    if (support.qsa && !compilerCache[selector + " "] && (!rbuggyQSA || !rbuggyQSA.test(selector))) {
                        if (nodeType !== 1) {
                            newContext = context;
                            newSelector = selector;
                        } else if (context.nodeName.toLowerCase() !== "object") {
                            if (nid = context.getAttribute("id")) {
                                nid = nid.replace(rcssescape, fcssescape);
                            } else {
                                context.setAttribute("id", nid = expando);
                            }
                            groups = tokenize(selector);
                            i = groups.length;
                            while (i--) {
                                groups[i] = "#" + nid + " " + toSelector(groups[i]);
                            }
                            newSelector = groups.join(",");
                            newContext = rsibling.test(selector) && testContext(context.parentNode) || context;
                        }
                        if (newSelector) {
                            try {
                                push.apply(results, newContext.querySelectorAll(newSelector));
                                return results;
                            } catch (qsaError) {} finally {
                                if (nid === expando) {
                                    context.removeAttribute("id");
                                }
                            }
                        }
                    }
                }
            }
            return select(selector.replace(rtrim, "$1"), context, results, seed);
        }
        function createCache() {
            var keys = [];
            function cache(key, value) {
                if (keys.push(key + " ") > Expr.cacheLength) {
                    delete cache[keys.shift()];
                }
                return cache[key + " "] = value;
            }
            return cache;
        }
        function markFunction(fn) {
            fn[expando] = true;
            return fn;
        }
        function assert(fn) {
            var el = document.createElement("fieldset");
            try {
                return !!fn(el);
            } catch (e) {
                return false;
            } finally {
                if (el.parentNode) {
                    el.parentNode.removeChild(el);
                }
                el = null;
            }
        }
        function addHandle(attrs, handler) {
            var arr = attrs.split("|"), i = arr.length;
            while (i--) {
                Expr.attrHandle[arr[i]] = handler;
            }
        }
        function siblingCheck(a, b) {
            var cur = b && a, diff = cur && a.nodeType === 1 && b.nodeType === 1 && a.sourceIndex - b.sourceIndex;
            if (diff) {
                return diff;
            }
            if (cur) {
                while (cur = cur.nextSibling) {
                    if (cur === b) {
                        return -1;
                    }
                }
            }
            return a ? 1 : -1;
        }
        function createInputPseudo(type) {
            return function(elem) {
                var name = elem.nodeName.toLowerCase();
                return name === "input" && elem.type === type;
            };
        }
        function createButtonPseudo(type) {
            return function(elem) {
                var name = elem.nodeName.toLowerCase();
                return (name === "input" || name === "button") && elem.type === type;
            };
        }
        function createDisabledPseudo(disabled) {
            return function(elem) {
                if ("form" in elem) {
                    if (elem.parentNode && elem.disabled === false) {
                        if ("label" in elem) {
                            if ("label" in elem.parentNode) {
                                return elem.parentNode.disabled === disabled;
                            } else {
                                return elem.disabled === disabled;
                            }
                        }
                        return elem.isDisabled === disabled || elem.isDisabled !== !disabled && disabledAncestor(elem) === disabled;
                    }
                    return elem.disabled === disabled;
                } else if ("label" in elem) {
                    return elem.disabled === disabled;
                }
                return false;
            };
        }
        function createPositionalPseudo(fn) {
            return markFunction(function(argument) {
                argument = +argument;
                return markFunction(function(seed, matches) {
                    var j, matchIndexes = fn([], seed.length, argument), i = matchIndexes.length;
                    while (i--) {
                        if (seed[j = matchIndexes[i]]) {
                            seed[j] = !(matches[j] = seed[j]);
                        }
                    }
                });
            });
        }
        function testContext(context) {
            return context && typeof context.getElementsByTagName !== "undefined" && context;
        }
        support = Sizzle.support = {};
        isXML = Sizzle.isXML = function(elem) {
            var documentElement = elem && (elem.ownerDocument || elem).documentElement;
            return documentElement ? documentElement.nodeName !== "HTML" : false;
        };
        setDocument = Sizzle.setDocument = function(node) {
            var hasCompare, subWindow, doc = node ? node.ownerDocument || node : preferredDoc;
            if (doc === document || doc.nodeType !== 9 || !doc.documentElement) {
                return document;
            }
            document = doc;
            docElem = document.documentElement;
            documentIsHTML = !isXML(document);
            if (preferredDoc !== document && (subWindow = document.defaultView) && subWindow.top !== subWindow) {
                if (subWindow.addEventListener) {
                    subWindow.addEventListener("unload", unloadHandler, false);
                } else if (subWindow.attachEvent) {
                    subWindow.attachEvent("onunload", unloadHandler);
                }
            }
            support.attributes = assert(function(el) {
                el.className = "i";
                return !el.getAttribute("className");
            });
            support.getElementsByTagName = assert(function(el) {
                el.appendChild(document.createComment(""));
                return !el.getElementsByTagName("*").length;
            });
            support.getElementsByClassName = rnative.test(document.getElementsByClassName);
            support.getById = assert(function(el) {
                docElem.appendChild(el).id = expando;
                return !document.getElementsByName || !document.getElementsByName(expando).length;
            });
            if (support.getById) {
                Expr.filter["ID"] = function(id) {
                    var attrId = id.replace(runescape, funescape);
                    return function(elem) {
                        return elem.getAttribute("id") === attrId;
                    };
                };
                Expr.find["ID"] = function(id, context) {
                    if (typeof context.getElementById !== "undefined" && documentIsHTML) {
                        var elem = context.getElementById(id);
                        return elem ? [ elem ] : [];
                    }
                };
            } else {
                Expr.filter["ID"] = function(id) {
                    var attrId = id.replace(runescape, funescape);
                    return function(elem) {
                        var node = typeof elem.getAttributeNode !== "undefined" && elem.getAttributeNode("id");
                        return node && node.value === attrId;
                    };
                };
                Expr.find["ID"] = function(id, context) {
                    if (typeof context.getElementById !== "undefined" && documentIsHTML) {
                        var node, i, elems, elem = context.getElementById(id);
                        if (elem) {
                            node = elem.getAttributeNode("id");
                            if (node && node.value === id) {
                                return [ elem ];
                            }
                            elems = context.getElementsByName(id);
                            i = 0;
                            while (elem = elems[i++]) {
                                node = elem.getAttributeNode("id");
                                if (node && node.value === id) {
                                    return [ elem ];
                                }
                            }
                        }
                        return [];
                    }
                };
            }
            Expr.find["TAG"] = support.getElementsByTagName ? function(tag, context) {
                if (typeof context.getElementsByTagName !== "undefined") {
                    return context.getElementsByTagName(tag);
                } else if (support.qsa) {
                    return context.querySelectorAll(tag);
                }
            } : function(tag, context) {
                var elem, tmp = [], i = 0, results = context.getElementsByTagName(tag);
                if (tag === "*") {
                    while (elem = results[i++]) {
                        if (elem.nodeType === 1) {
                            tmp.push(elem);
                        }
                    }
                    return tmp;
                }
                return results;
            };
            Expr.find["CLASS"] = support.getElementsByClassName && function(className, context) {
                if (typeof context.getElementsByClassName !== "undefined" && documentIsHTML) {
                    return context.getElementsByClassName(className);
                }
            };
            rbuggyMatches = [];
            rbuggyQSA = [];
            if (support.qsa = rnative.test(document.querySelectorAll)) {
                assert(function(el) {
                    docElem.appendChild(el).innerHTML = "<a id='" + expando + "'></a>" + "<select id='" + expando + "-\r\\' msallowcapture=''>" + "<option selected=''></option></select>";
                    if (el.querySelectorAll("[msallowcapture^='']").length) {
                        rbuggyQSA.push("[*^$]=" + whitespace + "*(?:''|\"\")");
                    }
                    if (!el.querySelectorAll("[selected]").length) {
                        rbuggyQSA.push("\\[" + whitespace + "*(?:value|" + booleans + ")");
                    }
                    if (!el.querySelectorAll("[id~=" + expando + "-]").length) {
                        rbuggyQSA.push("~=");
                    }
                    if (!el.querySelectorAll(":checked").length) {
                        rbuggyQSA.push(":checked");
                    }
                    if (!el.querySelectorAll("a#" + expando + "+*").length) {
                        rbuggyQSA.push(".#.+[+~]");
                    }
                });
                assert(function(el) {
                    el.innerHTML = "<a href='' disabled='disabled'></a>" + "<select disabled='disabled'><option/></select>";
                    var input = document.createElement("input");
                    input.setAttribute("type", "hidden");
                    el.appendChild(input).setAttribute("name", "D");
                    if (el.querySelectorAll("[name=d]").length) {
                        rbuggyQSA.push("name" + whitespace + "*[*^$|!~]?=");
                    }
                    if (el.querySelectorAll(":enabled").length !== 2) {
                        rbuggyQSA.push(":enabled", ":disabled");
                    }
                    docElem.appendChild(el).disabled = true;
                    if (el.querySelectorAll(":disabled").length !== 2) {
                        rbuggyQSA.push(":enabled", ":disabled");
                    }
                    el.querySelectorAll("*,:x");
                    rbuggyQSA.push(",.*:");
                });
            }
            if (support.matchesSelector = rnative.test(matches = docElem.matches || docElem.webkitMatchesSelector || docElem.mozMatchesSelector || docElem.oMatchesSelector || docElem.msMatchesSelector)) {
                assert(function(el) {
                    support.disconnectedMatch = matches.call(el, "*");
                    matches.call(el, "[s!='']:x");
                    rbuggyMatches.push("!=", pseudos);
                });
            }
            rbuggyQSA = rbuggyQSA.length && new RegExp(rbuggyQSA.join("|"));
            rbuggyMatches = rbuggyMatches.length && new RegExp(rbuggyMatches.join("|"));
            hasCompare = rnative.test(docElem.compareDocumentPosition);
            contains = hasCompare || rnative.test(docElem.contains) ? function(a, b) {
                var adown = a.nodeType === 9 ? a.documentElement : a, bup = b && b.parentNode;
                return a === bup || !!(bup && bup.nodeType === 1 && (adown.contains ? adown.contains(bup) : a.compareDocumentPosition && a.compareDocumentPosition(bup) & 16));
            } : function(a, b) {
                if (b) {
                    while (b = b.parentNode) {
                        if (b === a) {
                            return true;
                        }
                    }
                }
                return false;
            };
            sortOrder = hasCompare ? function(a, b) {
                if (a === b) {
                    hasDuplicate = true;
                    return 0;
                }
                var compare = !a.compareDocumentPosition - !b.compareDocumentPosition;
                if (compare) {
                    return compare;
                }
                compare = (a.ownerDocument || a) === (b.ownerDocument || b) ? a.compareDocumentPosition(b) : 1;
                if (compare & 1 || !support.sortDetached && b.compareDocumentPosition(a) === compare) {
                    if (a === document || a.ownerDocument === preferredDoc && contains(preferredDoc, a)) {
                        return -1;
                    }
                    if (b === document || b.ownerDocument === preferredDoc && contains(preferredDoc, b)) {
                        return 1;
                    }
                    return sortInput ? indexOf(sortInput, a) - indexOf(sortInput, b) : 0;
                }
                return compare & 4 ? -1 : 1;
            } : function(a, b) {
                if (a === b) {
                    hasDuplicate = true;
                    return 0;
                }
                var cur, i = 0, aup = a.parentNode, bup = b.parentNode, ap = [ a ], bp = [ b ];
                if (!aup || !bup) {
                    return a === document ? -1 : b === document ? 1 : aup ? -1 : bup ? 1 : sortInput ? indexOf(sortInput, a) - indexOf(sortInput, b) : 0;
                } else if (aup === bup) {
                    return siblingCheck(a, b);
                }
                cur = a;
                while (cur = cur.parentNode) {
                    ap.unshift(cur);
                }
                cur = b;
                while (cur = cur.parentNode) {
                    bp.unshift(cur);
                }
                while (ap[i] === bp[i]) {
                    i++;
                }
                return i ? siblingCheck(ap[i], bp[i]) : ap[i] === preferredDoc ? -1 : bp[i] === preferredDoc ? 1 : 0;
            };
            return document;
        };
        Sizzle.matches = function(expr, elements) {
            return Sizzle(expr, null, null, elements);
        };
        Sizzle.matchesSelector = function(elem, expr) {
            if ((elem.ownerDocument || elem) !== document) {
                setDocument(elem);
            }
            expr = expr.replace(rattributeQuotes, "='$1']");
            if (support.matchesSelector && documentIsHTML && !compilerCache[expr + " "] && (!rbuggyMatches || !rbuggyMatches.test(expr)) && (!rbuggyQSA || !rbuggyQSA.test(expr))) {
                try {
                    var ret = matches.call(elem, expr);
                    if (ret || support.disconnectedMatch || elem.document && elem.document.nodeType !== 11) {
                        return ret;
                    }
                } catch (e) {}
            }
            return Sizzle(expr, document, null, [ elem ]).length > 0;
        };
        Sizzle.contains = function(context, elem) {
            if ((context.ownerDocument || context) !== document) {
                setDocument(context);
            }
            return contains(context, elem);
        };
        Sizzle.attr = function(elem, name) {
            if ((elem.ownerDocument || elem) !== document) {
                setDocument(elem);
            }
            var fn = Expr.attrHandle[name.toLowerCase()], val = fn && hasOwn.call(Expr.attrHandle, name.toLowerCase()) ? fn(elem, name, !documentIsHTML) : undefined;
            return val !== undefined ? val : support.attributes || !documentIsHTML ? elem.getAttribute(name) : (val = elem.getAttributeNode(name)) && val.specified ? val.value : null;
        };
        Sizzle.escape = function(sel) {
            return (sel + "").replace(rcssescape, fcssescape);
        };
        Sizzle.error = function(msg) {
            throw new Error("Syntax error, unrecognized expression: " + msg);
        };
        Sizzle.uniqueSort = function(results) {
            var elem, duplicates = [], j = 0, i = 0;
            hasDuplicate = !support.detectDuplicates;
            sortInput = !support.sortStable && results.slice(0);
            results.sort(sortOrder);
            if (hasDuplicate) {
                while (elem = results[i++]) {
                    if (elem === results[i]) {
                        j = duplicates.push(i);
                    }
                }
                while (j--) {
                    results.splice(duplicates[j], 1);
                }
            }
            sortInput = null;
            return results;
        };
        getText = Sizzle.getText = function(elem) {
            var node, ret = "", i = 0, nodeType = elem.nodeType;
            if (!nodeType) {
                while (node = elem[i++]) {
                    ret += getText(node);
                }
            } else if (nodeType === 1 || nodeType === 9 || nodeType === 11) {
                if (typeof elem.textContent === "string") {
                    return elem.textContent;
                } else {
                    for (elem = elem.firstChild; elem; elem = elem.nextSibling) {
                        ret += getText(elem);
                    }
                }
            } else if (nodeType === 3 || nodeType === 4) {
                return elem.nodeValue;
            }
            return ret;
        };
        Expr = Sizzle.selectors = {
            cacheLength: 50,
            createPseudo: markFunction,
            match: matchExpr,
            attrHandle: {},
            find: {},
            relative: {
                ">": {
                    dir: "parentNode",
                    first: true
                },
                " ": {
                    dir: "parentNode"
                },
                "+": {
                    dir: "previousSibling",
                    first: true
                },
                "~": {
                    dir: "previousSibling"
                }
            },
            preFilter: {
                ATTR: function(match) {
                    match[1] = match[1].replace(runescape, funescape);
                    match[3] = (match[3] || match[4] || match[5] || "").replace(runescape, funescape);
                    if (match[2] === "~=") {
                        match[3] = " " + match[3] + " ";
                    }
                    return match.slice(0, 4);
                },
                CHILD: function(match) {
                    match[1] = match[1].toLowerCase();
                    if (match[1].slice(0, 3) === "nth") {
                        if (!match[3]) {
                            Sizzle.error(match[0]);
                        }
                        match[4] = +(match[4] ? match[5] + (match[6] || 1) : 2 * (match[3] === "even" || match[3] === "odd"));
                        match[5] = +(match[7] + match[8] || match[3] === "odd");
                    } else if (match[3]) {
                        Sizzle.error(match[0]);
                    }
                    return match;
                },
                PSEUDO: function(match) {
                    var excess, unquoted = !match[6] && match[2];
                    if (matchExpr["CHILD"].test(match[0])) {
                        return null;
                    }
                    if (match[3]) {
                        match[2] = match[4] || match[5] || "";
                    } else if (unquoted && rpseudo.test(unquoted) && (excess = tokenize(unquoted, true)) && (excess = unquoted.indexOf(")", unquoted.length - excess) - unquoted.length)) {
                        match[0] = match[0].slice(0, excess);
                        match[2] = unquoted.slice(0, excess);
                    }
                    return match.slice(0, 3);
                }
            },
            filter: {
                TAG: function(nodeNameSelector) {
                    var nodeName = nodeNameSelector.replace(runescape, funescape).toLowerCase();
                    return nodeNameSelector === "*" ? function() {
                        return true;
                    } : function(elem) {
                        return elem.nodeName && elem.nodeName.toLowerCase() === nodeName;
                    };
                },
                CLASS: function(className) {
                    var pattern = classCache[className + " "];
                    return pattern || (pattern = new RegExp("(^|" + whitespace + ")" + className + "(" + whitespace + "|$)")) && classCache(className, function(elem) {
                        return pattern.test(typeof elem.className === "string" && elem.className || typeof elem.getAttribute !== "undefined" && elem.getAttribute("class") || "");
                    });
                },
                ATTR: function(name, operator, check) {
                    return function(elem) {
                        var result = Sizzle.attr(elem, name);
                        if (result == null) {
                            return operator === "!=";
                        }
                        if (!operator) {
                            return true;
                        }
                        result += "";
                        return operator === "=" ? result === check : operator === "!=" ? result !== check : operator === "^=" ? check && result.indexOf(check) === 0 : operator === "*=" ? check && result.indexOf(check) > -1 : operator === "$=" ? check && result.slice(-check.length) === check : operator === "~=" ? (" " + result.replace(rwhitespace, " ") + " ").indexOf(check) > -1 : operator === "|=" ? result === check || result.slice(0, check.length + 1) === check + "-" : false;
                    };
                },
                CHILD: function(type, what, argument, first, last) {
                    var simple = type.slice(0, 3) !== "nth", forward = type.slice(-4) !== "last", ofType = what === "of-type";
                    return first === 1 && last === 0 ? function(elem) {
                        return !!elem.parentNode;
                    } : function(elem, context, xml) {
                        var cache, uniqueCache, outerCache, node, nodeIndex, start, dir = simple !== forward ? "nextSibling" : "previousSibling", parent = elem.parentNode, name = ofType && elem.nodeName.toLowerCase(), useCache = !xml && !ofType, diff = false;
                        if (parent) {
                            if (simple) {
                                while (dir) {
                                    node = elem;
                                    while (node = node[dir]) {
                                        if (ofType ? node.nodeName.toLowerCase() === name : node.nodeType === 1) {
                                            return false;
                                        }
                                    }
                                    start = dir = type === "only" && !start && "nextSibling";
                                }
                                return true;
                            }
                            start = [ forward ? parent.firstChild : parent.lastChild ];
                            if (forward && useCache) {
                                node = parent;
                                outerCache = node[expando] || (node[expando] = {});
                                uniqueCache = outerCache[node.uniqueID] || (outerCache[node.uniqueID] = {});
                                cache = uniqueCache[type] || [];
                                nodeIndex = cache[0] === dirruns && cache[1];
                                diff = nodeIndex && cache[2];
                                node = nodeIndex && parent.childNodes[nodeIndex];
                                while (node = ++nodeIndex && node && node[dir] || (diff = nodeIndex = 0) || start.pop()) {
                                    if (node.nodeType === 1 && ++diff && node === elem) {
                                        uniqueCache[type] = [ dirruns, nodeIndex, diff ];
                                        break;
                                    }
                                }
                            } else {
                                if (useCache) {
                                    node = elem;
                                    outerCache = node[expando] || (node[expando] = {});
                                    uniqueCache = outerCache[node.uniqueID] || (outerCache[node.uniqueID] = {});
                                    cache = uniqueCache[type] || [];
                                    nodeIndex = cache[0] === dirruns && cache[1];
                                    diff = nodeIndex;
                                }
                                if (diff === false) {
                                    while (node = ++nodeIndex && node && node[dir] || (diff = nodeIndex = 0) || start.pop()) {
                                        if ((ofType ? node.nodeName.toLowerCase() === name : node.nodeType === 1) && ++diff) {
                                            if (useCache) {
                                                outerCache = node[expando] || (node[expando] = {});
                                                uniqueCache = outerCache[node.uniqueID] || (outerCache[node.uniqueID] = {});
                                                uniqueCache[type] = [ dirruns, diff ];
                                            }
                                            if (node === elem) {
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                            diff -= last;
                            return diff === first || diff % first === 0 && diff / first >= 0;
                        }
                    };
                },
                PSEUDO: function(pseudo, argument) {
                    var args, fn = Expr.pseudos[pseudo] || Expr.setFilters[pseudo.toLowerCase()] || Sizzle.error("unsupported pseudo: " + pseudo);
                    if (fn[expando]) {
                        return fn(argument);
                    }
                    if (fn.length > 1) {
                        args = [ pseudo, pseudo, "", argument ];
                        return Expr.setFilters.hasOwnProperty(pseudo.toLowerCase()) ? markFunction(function(seed, matches) {
                            var idx, matched = fn(seed, argument), i = matched.length;
                            while (i--) {
                                idx = indexOf(seed, matched[i]);
                                seed[idx] = !(matches[idx] = matched[i]);
                            }
                        }) : function(elem) {
                            return fn(elem, 0, args);
                        };
                    }
                    return fn;
                }
            },
            pseudos: {
                not: markFunction(function(selector) {
                    var input = [], results = [], matcher = compile(selector.replace(rtrim, "$1"));
                    return matcher[expando] ? markFunction(function(seed, matches, context, xml) {
                        var elem, unmatched = matcher(seed, null, xml, []), i = seed.length;
                        while (i--) {
                            if (elem = unmatched[i]) {
                                seed[i] = !(matches[i] = elem);
                            }
                        }
                    }) : function(elem, context, xml) {
                        input[0] = elem;
                        matcher(input, null, xml, results);
                        input[0] = null;
                        return !results.pop();
                    };
                }),
                has: markFunction(function(selector) {
                    return function(elem) {
                        return Sizzle(selector, elem).length > 0;
                    };
                }),
                contains: markFunction(function(text) {
                    text = text.replace(runescape, funescape);
                    return function(elem) {
                        return (elem.textContent || elem.innerText || getText(elem)).indexOf(text) > -1;
                    };
                }),
                lang: markFunction(function(lang) {
                    if (!ridentifier.test(lang || "")) {
                        Sizzle.error("unsupported lang: " + lang);
                    }
                    lang = lang.replace(runescape, funescape).toLowerCase();
                    return function(elem) {
                        var elemLang;
                        do {
                            if (elemLang = documentIsHTML ? elem.lang : elem.getAttribute("xml:lang") || elem.getAttribute("lang")) {
                                elemLang = elemLang.toLowerCase();
                                return elemLang === lang || elemLang.indexOf(lang + "-") === 0;
                            }
                        } while ((elem = elem.parentNode) && elem.nodeType === 1);
                        return false;
                    };
                }),
                target: function(elem) {
                    var hash = window.location && window.location.hash;
                    return hash && hash.slice(1) === elem.id;
                },
                root: function(elem) {
                    return elem === docElem;
                },
                focus: function(elem) {
                    return elem === document.activeElement && (!document.hasFocus || document.hasFocus()) && !!(elem.type || elem.href || ~elem.tabIndex);
                },
                enabled: createDisabledPseudo(false),
                disabled: createDisabledPseudo(true),
                checked: function(elem) {
                    var nodeName = elem.nodeName.toLowerCase();
                    return nodeName === "input" && !!elem.checked || nodeName === "option" && !!elem.selected;
                },
                selected: function(elem) {
                    if (elem.parentNode) {
                        elem.parentNode.selectedIndex;
                    }
                    return elem.selected === true;
                },
                empty: function(elem) {
                    for (elem = elem.firstChild; elem; elem = elem.nextSibling) {
                        if (elem.nodeType < 6) {
                            return false;
                        }
                    }
                    return true;
                },
                parent: function(elem) {
                    return !Expr.pseudos["empty"](elem);
                },
                header: function(elem) {
                    return rheader.test(elem.nodeName);
                },
                input: function(elem) {
                    return rinputs.test(elem.nodeName);
                },
                button: function(elem) {
                    var name = elem.nodeName.toLowerCase();
                    return name === "input" && elem.type === "button" || name === "button";
                },
                text: function(elem) {
                    var attr;
                    return elem.nodeName.toLowerCase() === "input" && elem.type === "text" && ((attr = elem.getAttribute("type")) == null || attr.toLowerCase() === "text");
                },
                first: createPositionalPseudo(function() {
                    return [ 0 ];
                }),
                last: createPositionalPseudo(function(matchIndexes, length) {
                    return [ length - 1 ];
                }),
                eq: createPositionalPseudo(function(matchIndexes, length, argument) {
                    return [ argument < 0 ? argument + length : argument ];
                }),
                even: createPositionalPseudo(function(matchIndexes, length) {
                    var i = 0;
                    for (;i < length; i += 2) {
                        matchIndexes.push(i);
                    }
                    return matchIndexes;
                }),
                odd: createPositionalPseudo(function(matchIndexes, length) {
                    var i = 1;
                    for (;i < length; i += 2) {
                        matchIndexes.push(i);
                    }
                    return matchIndexes;
                }),
                lt: createPositionalPseudo(function(matchIndexes, length, argument) {
                    var i = argument < 0 ? argument + length : argument;
                    for (;--i >= 0; ) {
                        matchIndexes.push(i);
                    }
                    return matchIndexes;
                }),
                gt: createPositionalPseudo(function(matchIndexes, length, argument) {
                    var i = argument < 0 ? argument + length : argument;
                    for (;++i < length; ) {
                        matchIndexes.push(i);
                    }
                    return matchIndexes;
                })
            }
        };
        Expr.pseudos["nth"] = Expr.pseudos["eq"];
        for (i in {
            radio: true,
            checkbox: true,
            file: true,
            password: true,
            image: true
        }) {
            Expr.pseudos[i] = createInputPseudo(i);
        }
        for (i in {
            submit: true,
            reset: true
        }) {
            Expr.pseudos[i] = createButtonPseudo(i);
        }
        function setFilters() {}
        setFilters.prototype = Expr.filters = Expr.pseudos;
        Expr.setFilters = new setFilters();
        tokenize = Sizzle.tokenize = function(selector, parseOnly) {
            var matched, match, tokens, type, soFar, groups, preFilters, cached = tokenCache[selector + " "];
            if (cached) {
                return parseOnly ? 0 : cached.slice(0);
            }
            soFar = selector;
            groups = [];
            preFilters = Expr.preFilter;
            while (soFar) {
                if (!matched || (match = rcomma.exec(soFar))) {
                    if (match) {
                        soFar = soFar.slice(match[0].length) || soFar;
                    }
                    groups.push(tokens = []);
                }
                matched = false;
                if (match = rcombinators.exec(soFar)) {
                    matched = match.shift();
                    tokens.push({
                        value: matched,
                        type: match[0].replace(rtrim, " ")
                    });
                    soFar = soFar.slice(matched.length);
                }
                for (type in Expr.filter) {
                    if ((match = matchExpr[type].exec(soFar)) && (!preFilters[type] || (match = preFilters[type](match)))) {
                        matched = match.shift();
                        tokens.push({
                            value: matched,
                            type: type,
                            matches: match
                        });
                        soFar = soFar.slice(matched.length);
                    }
                }
                if (!matched) {
                    break;
                }
            }
            return parseOnly ? soFar.length : soFar ? Sizzle.error(selector) : tokenCache(selector, groups).slice(0);
        };
        function toSelector(tokens) {
            var i = 0, len = tokens.length, selector = "";
            for (;i < len; i++) {
                selector += tokens[i].value;
            }
            return selector;
        }
        function addCombinator(matcher, combinator, base) {
            var dir = combinator.dir, skip = combinator.next, key = skip || dir, checkNonElements = base && key === "parentNode", doneName = done++;
            return combinator.first ? function(elem, context, xml) {
                while (elem = elem[dir]) {
                    if (elem.nodeType === 1 || checkNonElements) {
                        return matcher(elem, context, xml);
                    }
                }
                return false;
            } : function(elem, context, xml) {
                var oldCache, uniqueCache, outerCache, newCache = [ dirruns, doneName ];
                if (xml) {
                    while (elem = elem[dir]) {
                        if (elem.nodeType === 1 || checkNonElements) {
                            if (matcher(elem, context, xml)) {
                                return true;
                            }
                        }
                    }
                } else {
                    while (elem = elem[dir]) {
                        if (elem.nodeType === 1 || checkNonElements) {
                            outerCache = elem[expando] || (elem[expando] = {});
                            uniqueCache = outerCache[elem.uniqueID] || (outerCache[elem.uniqueID] = {});
                            if (skip && skip === elem.nodeName.toLowerCase()) {
                                elem = elem[dir] || elem;
                            } else if ((oldCache = uniqueCache[key]) && oldCache[0] === dirruns && oldCache[1] === doneName) {
                                return newCache[2] = oldCache[2];
                            } else {
                                uniqueCache[key] = newCache;
                                if (newCache[2] = matcher(elem, context, xml)) {
                                    return true;
                                }
                            }
                        }
                    }
                }
                return false;
            };
        }
        function elementMatcher(matchers) {
            return matchers.length > 1 ? function(elem, context, xml) {
                var i = matchers.length;
                while (i--) {
                    if (!matchers[i](elem, context, xml)) {
                        return false;
                    }
                }
                return true;
            } : matchers[0];
        }
        function multipleContexts(selector, contexts, results) {
            var i = 0, len = contexts.length;
            for (;i < len; i++) {
                Sizzle(selector, contexts[i], results);
            }
            return results;
        }
        function condense(unmatched, map, filter, context, xml) {
            var elem, newUnmatched = [], i = 0, len = unmatched.length, mapped = map != null;
            for (;i < len; i++) {
                if (elem = unmatched[i]) {
                    if (!filter || filter(elem, context, xml)) {
                        newUnmatched.push(elem);
                        if (mapped) {
                            map.push(i);
                        }
                    }
                }
            }
            return newUnmatched;
        }
        function setMatcher(preFilter, selector, matcher, postFilter, postFinder, postSelector) {
            if (postFilter && !postFilter[expando]) {
                postFilter = setMatcher(postFilter);
            }
            if (postFinder && !postFinder[expando]) {
                postFinder = setMatcher(postFinder, postSelector);
            }
            return markFunction(function(seed, results, context, xml) {
                var temp, i, elem, preMap = [], postMap = [], preexisting = results.length, elems = seed || multipleContexts(selector || "*", context.nodeType ? [ context ] : context, []), matcherIn = preFilter && (seed || !selector) ? condense(elems, preMap, preFilter, context, xml) : elems, matcherOut = matcher ? postFinder || (seed ? preFilter : preexisting || postFilter) ? [] : results : matcherIn;
                if (matcher) {
                    matcher(matcherIn, matcherOut, context, xml);
                }
                if (postFilter) {
                    temp = condense(matcherOut, postMap);
                    postFilter(temp, [], context, xml);
                    i = temp.length;
                    while (i--) {
                        if (elem = temp[i]) {
                            matcherOut[postMap[i]] = !(matcherIn[postMap[i]] = elem);
                        }
                    }
                }
                if (seed) {
                    if (postFinder || preFilter) {
                        if (postFinder) {
                            temp = [];
                            i = matcherOut.length;
                            while (i--) {
                                if (elem = matcherOut[i]) {
                                    temp.push(matcherIn[i] = elem);
                                }
                            }
                            postFinder(null, matcherOut = [], temp, xml);
                        }
                        i = matcherOut.length;
                        while (i--) {
                            if ((elem = matcherOut[i]) && (temp = postFinder ? indexOf(seed, elem) : preMap[i]) > -1) {
                                seed[temp] = !(results[temp] = elem);
                            }
                        }
                    }
                } else {
                    matcherOut = condense(matcherOut === results ? matcherOut.splice(preexisting, matcherOut.length) : matcherOut);
                    if (postFinder) {
                        postFinder(null, results, matcherOut, xml);
                    } else {
                        push.apply(results, matcherOut);
                    }
                }
            });
        }
        function matcherFromTokens(tokens) {
            var checkContext, matcher, j, len = tokens.length, leadingRelative = Expr.relative[tokens[0].type], implicitRelative = leadingRelative || Expr.relative[" "], i = leadingRelative ? 1 : 0, matchContext = addCombinator(function(elem) {
                return elem === checkContext;
            }, implicitRelative, true), matchAnyContext = addCombinator(function(elem) {
                return indexOf(checkContext, elem) > -1;
            }, implicitRelative, true), matchers = [ function(elem, context, xml) {
                var ret = !leadingRelative && (xml || context !== outermostContext) || ((checkContext = context).nodeType ? matchContext(elem, context, xml) : matchAnyContext(elem, context, xml));
                checkContext = null;
                return ret;
            } ];
            for (;i < len; i++) {
                if (matcher = Expr.relative[tokens[i].type]) {
                    matchers = [ addCombinator(elementMatcher(matchers), matcher) ];
                } else {
                    matcher = Expr.filter[tokens[i].type].apply(null, tokens[i].matches);
                    if (matcher[expando]) {
                        j = ++i;
                        for (;j < len; j++) {
                            if (Expr.relative[tokens[j].type]) {
                                break;
                            }
                        }
                        return setMatcher(i > 1 && elementMatcher(matchers), i > 1 && toSelector(tokens.slice(0, i - 1).concat({
                            value: tokens[i - 2].type === " " ? "*" : ""
                        })).replace(rtrim, "$1"), matcher, i < j && matcherFromTokens(tokens.slice(i, j)), j < len && matcherFromTokens(tokens = tokens.slice(j)), j < len && toSelector(tokens));
                    }
                    matchers.push(matcher);
                }
            }
            return elementMatcher(matchers);
        }
        function matcherFromGroupMatchers(elementMatchers, setMatchers) {
            var bySet = setMatchers.length > 0, byElement = elementMatchers.length > 0, superMatcher = function(seed, context, xml, results, outermost) {
                var elem, j, matcher, matchedCount = 0, i = "0", unmatched = seed && [], setMatched = [], contextBackup = outermostContext, elems = seed || byElement && Expr.find["TAG"]("*", outermost), dirrunsUnique = dirruns += contextBackup == null ? 1 : Math.random() || .1, len = elems.length;
                if (outermost) {
                    outermostContext = context === document || context || outermost;
                }
                for (;i !== len && (elem = elems[i]) != null; i++) {
                    if (byElement && elem) {
                        j = 0;
                        if (!context && elem.ownerDocument !== document) {
                            setDocument(elem);
                            xml = !documentIsHTML;
                        }
                        while (matcher = elementMatchers[j++]) {
                            if (matcher(elem, context || document, xml)) {
                                results.push(elem);
                                break;
                            }
                        }
                        if (outermost) {
                            dirruns = dirrunsUnique;
                        }
                    }
                    if (bySet) {
                        if (elem = !matcher && elem) {
                            matchedCount--;
                        }
                        if (seed) {
                            unmatched.push(elem);
                        }
                    }
                }
                matchedCount += i;
                if (bySet && i !== matchedCount) {
                    j = 0;
                    while (matcher = setMatchers[j++]) {
                        matcher(unmatched, setMatched, context, xml);
                    }
                    if (seed) {
                        if (matchedCount > 0) {
                            while (i--) {
                                if (!(unmatched[i] || setMatched[i])) {
                                    setMatched[i] = pop.call(results);
                                }
                            }
                        }
                        setMatched = condense(setMatched);
                    }
                    push.apply(results, setMatched);
                    if (outermost && !seed && setMatched.length > 0 && matchedCount + setMatchers.length > 1) {
                        Sizzle.uniqueSort(results);
                    }
                }
                if (outermost) {
                    dirruns = dirrunsUnique;
                    outermostContext = contextBackup;
                }
                return unmatched;
            };
            return bySet ? markFunction(superMatcher) : superMatcher;
        }
        compile = Sizzle.compile = function(selector, match) {
            var i, setMatchers = [], elementMatchers = [], cached = compilerCache[selector + " "];
            if (!cached) {
                if (!match) {
                    match = tokenize(selector);
                }
                i = match.length;
                while (i--) {
                    cached = matcherFromTokens(match[i]);
                    if (cached[expando]) {
                        setMatchers.push(cached);
                    } else {
                        elementMatchers.push(cached);
                    }
                }
                cached = compilerCache(selector, matcherFromGroupMatchers(elementMatchers, setMatchers));
                cached.selector = selector;
            }
            return cached;
        };
        select = Sizzle.select = function(selector, context, results, seed) {
            var i, tokens, token, type, find, compiled = typeof selector === "function" && selector, match = !seed && tokenize(selector = compiled.selector || selector);
            results = results || [];
            if (match.length === 1) {
                tokens = match[0] = match[0].slice(0);
                if (tokens.length > 2 && (token = tokens[0]).type === "ID" && context.nodeType === 9 && documentIsHTML && Expr.relative[tokens[1].type]) {
                    context = (Expr.find["ID"](token.matches[0].replace(runescape, funescape), context) || [])[0];
                    if (!context) {
                        return results;
                    } else if (compiled) {
                        context = context.parentNode;
                    }
                    selector = selector.slice(tokens.shift().value.length);
                }
                i = matchExpr["needsContext"].test(selector) ? 0 : tokens.length;
                while (i--) {
                    token = tokens[i];
                    if (Expr.relative[type = token.type]) {
                        break;
                    }
                    if (find = Expr.find[type]) {
                        if (seed = find(token.matches[0].replace(runescape, funescape), rsibling.test(tokens[0].type) && testContext(context.parentNode) || context)) {
                            tokens.splice(i, 1);
                            selector = seed.length && toSelector(tokens);
                            if (!selector) {
                                push.apply(results, seed);
                                return results;
                            }
                            break;
                        }
                    }
                }
            }
            (compiled || compile(selector, match))(seed, context, !documentIsHTML, results, !context || rsibling.test(selector) && testContext(context.parentNode) || context);
            return results;
        };
        support.sortStable = expando.split("").sort(sortOrder).join("") === expando;
        support.detectDuplicates = !!hasDuplicate;
        setDocument();
        support.sortDetached = assert(function(el) {
            return el.compareDocumentPosition(document.createElement("fieldset")) & 1;
        });
        if (!assert(function(el) {
            el.innerHTML = "<a href='#'></a>";
            return el.firstChild.getAttribute("href") === "#";
        })) {
            addHandle("type|href|height|width", function(elem, name, isXML) {
                if (!isXML) {
                    return elem.getAttribute(name, name.toLowerCase() === "type" ? 1 : 2);
                }
            });
        }
        if (!support.attributes || !assert(function(el) {
            el.innerHTML = "<input/>";
            el.firstChild.setAttribute("value", "");
            return el.firstChild.getAttribute("value") === "";
        })) {
            addHandle("value", function(elem, name, isXML) {
                if (!isXML && elem.nodeName.toLowerCase() === "input") {
                    return elem.defaultValue;
                }
            });
        }
        if (!assert(function(el) {
            return el.getAttribute("disabled") == null;
        })) {
            addHandle(booleans, function(elem, name, isXML) {
                var val;
                if (!isXML) {
                    return elem[name] === true ? name.toLowerCase() : (val = elem.getAttributeNode(name)) && val.specified ? val.value : null;
                }
            });
        }
        return Sizzle;
    }(window);
    jQuery.find = Sizzle;
    jQuery.expr = Sizzle.selectors;
    jQuery.expr[":"] = jQuery.expr.pseudos;
    jQuery.uniqueSort = jQuery.unique = Sizzle.uniqueSort;
    jQuery.text = Sizzle.getText;
    jQuery.isXMLDoc = Sizzle.isXML;
    jQuery.contains = Sizzle.contains;
    jQuery.escapeSelector = Sizzle.escape;
    var dir = function(elem, dir, until) {
        var matched = [], truncate = until !== undefined;
        while ((elem = elem[dir]) && elem.nodeType !== 9) {
            if (elem.nodeType === 1) {
                if (truncate && jQuery(elem).is(until)) {
                    break;
                }
                matched.push(elem);
            }
        }
        return matched;
    };
    var siblings = function(n, elem) {
        var matched = [];
        for (;n; n = n.nextSibling) {
            if (n.nodeType === 1 && n !== elem) {
                matched.push(n);
            }
        }
        return matched;
    };
    var rneedsContext = jQuery.expr.match.needsContext;
    function nodeName(elem, name) {
        return elem.nodeName && elem.nodeName.toLowerCase() === name.toLowerCase();
    }
    var rsingleTag = /^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;
    var risSimple = /^.[^:#\[\.,]*$/;
    function winnow(elements, qualifier, not) {
        if (jQuery.isFunction(qualifier)) {
            return jQuery.grep(elements, function(elem, i) {
                return !!qualifier.call(elem, i, elem) !== not;
            });
        }
        if (qualifier.nodeType) {
            return jQuery.grep(elements, function(elem) {
                return elem === qualifier !== not;
            });
        }
        if (typeof qualifier !== "string") {
            return jQuery.grep(elements, function(elem) {
                return indexOf.call(qualifier, elem) > -1 !== not;
            });
        }
        if (risSimple.test(qualifier)) {
            return jQuery.filter(qualifier, elements, not);
        }
        qualifier = jQuery.filter(qualifier, elements);
        return jQuery.grep(elements, function(elem) {
            return indexOf.call(qualifier, elem) > -1 !== not && elem.nodeType === 1;
        });
    }
    jQuery.filter = function(expr, elems, not) {
        var elem = elems[0];
        if (not) {
            expr = ":not(" + expr + ")";
        }
        if (elems.length === 1 && elem.nodeType === 1) {
            return jQuery.find.matchesSelector(elem, expr) ? [ elem ] : [];
        }
        return jQuery.find.matches(expr, jQuery.grep(elems, function(elem) {
            return elem.nodeType === 1;
        }));
    };
    jQuery.fn.extend({
        find: function(selector) {
            var i, ret, len = this.length, self = this;
            if (typeof selector !== "string") {
                return this.pushStack(jQuery(selector).filter(function() {
                    for (i = 0; i < len; i++) {
                        if (jQuery.contains(self[i], this)) {
                            return true;
                        }
                    }
                }));
            }
            ret = this.pushStack([]);
            for (i = 0; i < len; i++) {
                jQuery.find(selector, self[i], ret);
            }
            return len > 1 ? jQuery.uniqueSort(ret) : ret;
        },
        filter: function(selector) {
            return this.pushStack(winnow(this, selector || [], false));
        },
        not: function(selector) {
            return this.pushStack(winnow(this, selector || [], true));
        },
        is: function(selector) {
            return !!winnow(this, typeof selector === "string" && rneedsContext.test(selector) ? jQuery(selector) : selector || [], false).length;
        }
    });
    var rootjQuery, rquickExpr = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/, init = jQuery.fn.init = function(selector, context, root) {
        var match, elem;
        if (!selector) {
            return this;
        }
        root = root || rootjQuery;
        if (typeof selector === "string") {
            if (selector[0] === "<" && selector[selector.length - 1] === ">" && selector.length >= 3) {
                match = [ null, selector, null ];
            } else {
                match = rquickExpr.exec(selector);
            }
            if (match && (match[1] || !context)) {
                if (match[1]) {
                    context = context instanceof jQuery ? context[0] : context;
                    jQuery.merge(this, jQuery.parseHTML(match[1], context && context.nodeType ? context.ownerDocument || context : document, true));
                    if (rsingleTag.test(match[1]) && jQuery.isPlainObject(context)) {
                        for (match in context) {
                            if (jQuery.isFunction(this[match])) {
                                this[match](context[match]);
                            } else {
                                this.attr(match, context[match]);
                            }
                        }
                    }
                    return this;
                } else {
                    elem = document.getElementById(match[2]);
                    if (elem) {
                        this[0] = elem;
                        this.length = 1;
                    }
                    return this;
                }
            } else if (!context || context.jquery) {
                return (context || root).find(selector);
            } else {
                return this.constructor(context).find(selector);
            }
        } else if (selector.nodeType) {
            this[0] = selector;
            this.length = 1;
            return this;
        } else if (jQuery.isFunction(selector)) {
            return root.ready !== undefined ? root.ready(selector) : selector(jQuery);
        }
        return jQuery.makeArray(selector, this);
    };
    init.prototype = jQuery.fn;
    rootjQuery = jQuery(document);
    var rparentsprev = /^(?:parents|prev(?:Until|All))/, guaranteedUnique = {
        children: true,
        contents: true,
        next: true,
        prev: true
    };
    jQuery.fn.extend({
        has: function(target) {
            var targets = jQuery(target, this), l = targets.length;
            return this.filter(function() {
                var i = 0;
                for (;i < l; i++) {
                    if (jQuery.contains(this, targets[i])) {
                        return true;
                    }
                }
            });
        },
        closest: function(selectors, context) {
            var cur, i = 0, l = this.length, matched = [], targets = typeof selectors !== "string" && jQuery(selectors);
            if (!rneedsContext.test(selectors)) {
                for (;i < l; i++) {
                    for (cur = this[i]; cur && cur !== context; cur = cur.parentNode) {
                        if (cur.nodeType < 11 && (targets ? targets.index(cur) > -1 : cur.nodeType === 1 && jQuery.find.matchesSelector(cur, selectors))) {
                            matched.push(cur);
                            break;
                        }
                    }
                }
            }
            return this.pushStack(matched.length > 1 ? jQuery.uniqueSort(matched) : matched);
        },
        index: function(elem) {
            if (!elem) {
                return this[0] && this[0].parentNode ? this.first().prevAll().length : -1;
            }
            if (typeof elem === "string") {
                return indexOf.call(jQuery(elem), this[0]);
            }
            return indexOf.call(this, elem.jquery ? elem[0] : elem);
        },
        add: function(selector, context) {
            return this.pushStack(jQuery.uniqueSort(jQuery.merge(this.get(), jQuery(selector, context))));
        },
        addBack: function(selector) {
            return this.add(selector == null ? this.prevObject : this.prevObject.filter(selector));
        }
    });
    function sibling(cur, dir) {
        while ((cur = cur[dir]) && cur.nodeType !== 1) {}
        return cur;
    }
    jQuery.each({
        parent: function(elem) {
            var parent = elem.parentNode;
            return parent && parent.nodeType !== 11 ? parent : null;
        },
        parents: function(elem) {
            return dir(elem, "parentNode");
        },
        parentsUntil: function(elem, i, until) {
            return dir(elem, "parentNode", until);
        },
        next: function(elem) {
            return sibling(elem, "nextSibling");
        },
        prev: function(elem) {
            return sibling(elem, "previousSibling");
        },
        nextAll: function(elem) {
            return dir(elem, "nextSibling");
        },
        prevAll: function(elem) {
            return dir(elem, "previousSibling");
        },
        nextUntil: function(elem, i, until) {
            return dir(elem, "nextSibling", until);
        },
        prevUntil: function(elem, i, until) {
            return dir(elem, "previousSibling", until);
        },
        siblings: function(elem) {
            return siblings((elem.parentNode || {}).firstChild, elem);
        },
        children: function(elem) {
            return siblings(elem.firstChild);
        },
        contents: function(elem) {
            if (nodeName(elem, "iframe")) {
                return elem.contentDocument;
            }
            if (nodeName(elem, "template")) {
                elem = elem.content || elem;
            }
            return jQuery.merge([], elem.childNodes);
        }
    }, function(name, fn) {
        jQuery.fn[name] = function(until, selector) {
            var matched = jQuery.map(this, fn, until);
            if (name.slice(-5) !== "Until") {
                selector = until;
            }
            if (selector && typeof selector === "string") {
                matched = jQuery.filter(selector, matched);
            }
            if (this.length > 1) {
                if (!guaranteedUnique[name]) {
                    jQuery.uniqueSort(matched);
                }
                if (rparentsprev.test(name)) {
                    matched.reverse();
                }
            }
            return this.pushStack(matched);
        };
    });
    var rnothtmlwhite = /[^\x20\t\r\n\f]+/g;
    function createOptions(options) {
        var object = {};
        jQuery.each(options.match(rnothtmlwhite) || [], function(_, flag) {
            object[flag] = true;
        });
        return object;
    }
    jQuery.Callbacks = function(options) {
        options = typeof options === "string" ? createOptions(options) : jQuery.extend({}, options);
        var firing, memory, fired, locked, list = [], queue = [], firingIndex = -1, fire = function() {
            locked = locked || options.once;
            fired = firing = true;
            for (;queue.length; firingIndex = -1) {
                memory = queue.shift();
                while (++firingIndex < list.length) {
                    if (list[firingIndex].apply(memory[0], memory[1]) === false && options.stopOnFalse) {
                        firingIndex = list.length;
                        memory = false;
                    }
                }
            }
            if (!options.memory) {
                memory = false;
            }
            firing = false;
            if (locked) {
                if (memory) {
                    list = [];
                } else {
                    list = "";
                }
            }
        }, self = {
            add: function() {
                if (list) {
                    if (memory && !firing) {
                        firingIndex = list.length - 1;
                        queue.push(memory);
                    }
                    (function add(args) {
                        jQuery.each(args, function(_, arg) {
                            if (jQuery.isFunction(arg)) {
                                if (!options.unique || !self.has(arg)) {
                                    list.push(arg);
                                }
                            } else if (arg && arg.length && jQuery.type(arg) !== "string") {
                                add(arg);
                            }
                        });
                    })(arguments);
                    if (memory && !firing) {
                        fire();
                    }
                }
                return this;
            },
            remove: function() {
                jQuery.each(arguments, function(_, arg) {
                    var index;
                    while ((index = jQuery.inArray(arg, list, index)) > -1) {
                        list.splice(index, 1);
                        if (index <= firingIndex) {
                            firingIndex--;
                        }
                    }
                });
                return this;
            },
            has: function(fn) {
                return fn ? jQuery.inArray(fn, list) > -1 : list.length > 0;
            },
            empty: function() {
                if (list) {
                    list = [];
                }
                return this;
            },
            disable: function() {
                locked = queue = [];
                list = memory = "";
                return this;
            },
            disabled: function() {
                return !list;
            },
            lock: function() {
                locked = queue = [];
                if (!memory && !firing) {
                    list = memory = "";
                }
                return this;
            },
            locked: function() {
                return !!locked;
            },
            fireWith: function(context, args) {
                if (!locked) {
                    args = args || [];
                    args = [ context, args.slice ? args.slice() : args ];
                    queue.push(args);
                    if (!firing) {
                        fire();
                    }
                }
                return this;
            },
            fire: function() {
                self.fireWith(this, arguments);
                return this;
            },
            fired: function() {
                return !!fired;
            }
        };
        return self;
    };
    function Identity(v) {
        return v;
    }
    function Thrower(ex) {
        throw ex;
    }
    function adoptValue(value, resolve, reject, noValue) {
        var method;
        try {
            if (value && jQuery.isFunction(method = value.promise)) {
                method.call(value).done(resolve).fail(reject);
            } else if (value && jQuery.isFunction(method = value.then)) {
                method.call(value, resolve, reject);
            } else {
                resolve.apply(undefined, [ value ].slice(noValue));
            }
        } catch (value) {
            reject.apply(undefined, [ value ]);
        }
    }
    jQuery.extend({
        Deferred: function(func) {
            var tuples = [ [ "notify", "progress", jQuery.Callbacks("memory"), jQuery.Callbacks("memory"), 2 ], [ "resolve", "done", jQuery.Callbacks("once memory"), jQuery.Callbacks("once memory"), 0, "resolved" ], [ "reject", "fail", jQuery.Callbacks("once memory"), jQuery.Callbacks("once memory"), 1, "rejected" ] ], state = "pending", promise = {
                state: function() {
                    return state;
                },
                always: function() {
                    deferred.done(arguments).fail(arguments);
                    return this;
                },
                catch: function(fn) {
                    return promise.then(null, fn);
                },
                pipe: function() {
                    var fns = arguments;
                    return jQuery.Deferred(function(newDefer) {
                        jQuery.each(tuples, function(i, tuple) {
                            var fn = jQuery.isFunction(fns[tuple[4]]) && fns[tuple[4]];
                            deferred[tuple[1]](function() {
                                var returned = fn && fn.apply(this, arguments);
                                if (returned && jQuery.isFunction(returned.promise)) {
                                    returned.promise().progress(newDefer.notify).done(newDefer.resolve).fail(newDefer.reject);
                                } else {
                                    newDefer[tuple[0] + "With"](this, fn ? [ returned ] : arguments);
                                }
                            });
                        });
                        fns = null;
                    }).promise();
                },
                then: function(onFulfilled, onRejected, onProgress) {
                    var maxDepth = 0;
                    function resolve(depth, deferred, handler, special) {
                        return function() {
                            var that = this, args = arguments, mightThrow = function() {
                                var returned, then;
                                if (depth < maxDepth) {
                                    return;
                                }
                                returned = handler.apply(that, args);
                                if (returned === deferred.promise()) {
                                    throw new TypeError("Thenable self-resolution");
                                }
                                then = returned && (typeof returned === "object" || typeof returned === "function") && returned.then;
                                if (jQuery.isFunction(then)) {
                                    if (special) {
                                        then.call(returned, resolve(maxDepth, deferred, Identity, special), resolve(maxDepth, deferred, Thrower, special));
                                    } else {
                                        maxDepth++;
                                        then.call(returned, resolve(maxDepth, deferred, Identity, special), resolve(maxDepth, deferred, Thrower, special), resolve(maxDepth, deferred, Identity, deferred.notifyWith));
                                    }
                                } else {
                                    if (handler !== Identity) {
                                        that = undefined;
                                        args = [ returned ];
                                    }
                                    (special || deferred.resolveWith)(that, args);
                                }
                            }, process = special ? mightThrow : function() {
                                try {
                                    mightThrow();
                                } catch (e) {
                                    if (jQuery.Deferred.exceptionHook) {
                                        jQuery.Deferred.exceptionHook(e, process.stackTrace);
                                    }
                                    if (depth + 1 >= maxDepth) {
                                        if (handler !== Thrower) {
                                            that = undefined;
                                            args = [ e ];
                                        }
                                        deferred.rejectWith(that, args);
                                    }
                                }
                            };
                            if (depth) {
                                process();
                            } else {
                                if (jQuery.Deferred.getStackHook) {
                                    process.stackTrace = jQuery.Deferred.getStackHook();
                                }
                                window.setTimeout(process);
                            }
                        };
                    }
                    return jQuery.Deferred(function(newDefer) {
                        tuples[0][3].add(resolve(0, newDefer, jQuery.isFunction(onProgress) ? onProgress : Identity, newDefer.notifyWith));
                        tuples[1][3].add(resolve(0, newDefer, jQuery.isFunction(onFulfilled) ? onFulfilled : Identity));
                        tuples[2][3].add(resolve(0, newDefer, jQuery.isFunction(onRejected) ? onRejected : Thrower));
                    }).promise();
                },
                promise: function(obj) {
                    return obj != null ? jQuery.extend(obj, promise) : promise;
                }
            }, deferred = {};
            jQuery.each(tuples, function(i, tuple) {
                var list = tuple[2], stateString = tuple[5];
                promise[tuple[1]] = list.add;
                if (stateString) {
                    list.add(function() {
                        state = stateString;
                    }, tuples[3 - i][2].disable, tuples[0][2].lock);
                }
                list.add(tuple[3].fire);
                deferred[tuple[0]] = function() {
                    deferred[tuple[0] + "With"](this === deferred ? undefined : this, arguments);
                    return this;
                };
                deferred[tuple[0] + "With"] = list.fireWith;
            });
            promise.promise(deferred);
            if (func) {
                func.call(deferred, deferred);
            }
            return deferred;
        },
        when: function(singleValue) {
            var remaining = arguments.length, i = remaining, resolveContexts = Array(i), resolveValues = slice.call(arguments), master = jQuery.Deferred(), updateFunc = function(i) {
                return function(value) {
                    resolveContexts[i] = this;
                    resolveValues[i] = arguments.length > 1 ? slice.call(arguments) : value;
                    if (!--remaining) {
                        master.resolveWith(resolveContexts, resolveValues);
                    }
                };
            };
            if (remaining <= 1) {
                adoptValue(singleValue, master.done(updateFunc(i)).resolve, master.reject, !remaining);
                if (master.state() === "pending" || jQuery.isFunction(resolveValues[i] && resolveValues[i].then)) {
                    return master.then();
                }
            }
            while (i--) {
                adoptValue(resolveValues[i], updateFunc(i), master.reject);
            }
            return master.promise();
        }
    });
    var rerrorNames = /^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;
    jQuery.Deferred.exceptionHook = function(error, stack) {
        if (window.console && window.console.warn && error && rerrorNames.test(error.name)) {
            window.console.warn("jQuery.Deferred exception: " + error.message, error.stack, stack);
        }
    };
    jQuery.readyException = function(error) {
        window.setTimeout(function() {
            throw error;
        });
    };
    var readyList = jQuery.Deferred();
    jQuery.fn.ready = function(fn) {
        readyList.then(fn).catch(function(error) {
            jQuery.readyException(error);
        });
        return this;
    };
    jQuery.extend({
        isReady: false,
        readyWait: 1,
        ready: function(wait) {
            if (wait === true ? --jQuery.readyWait : jQuery.isReady) {
                return;
            }
            jQuery.isReady = true;
            if (wait !== true && --jQuery.readyWait > 0) {
                return;
            }
            readyList.resolveWith(document, [ jQuery ]);
        }
    });
    jQuery.ready.then = readyList.then;
    function completed() {
        document.removeEventListener("DOMContentLoaded", completed);
        window.removeEventListener("load", completed);
        jQuery.ready();
    }
    if (document.readyState === "complete" || document.readyState !== "loading" && !document.documentElement.doScroll) {
        window.setTimeout(jQuery.ready);
    } else {
        document.addEventListener("DOMContentLoaded", completed);
        window.addEventListener("load", completed);
    }
    var access = function(elems, fn, key, value, chainable, emptyGet, raw) {
        var i = 0, len = elems.length, bulk = key == null;
        if (jQuery.type(key) === "object") {
            chainable = true;
            for (i in key) {
                access(elems, fn, i, key[i], true, emptyGet, raw);
            }
        } else if (value !== undefined) {
            chainable = true;
            if (!jQuery.isFunction(value)) {
                raw = true;
            }
            if (bulk) {
                if (raw) {
                    fn.call(elems, value);
                    fn = null;
                } else {
                    bulk = fn;
                    fn = function(elem, key, value) {
                        return bulk.call(jQuery(elem), value);
                    };
                }
            }
            if (fn) {
                for (;i < len; i++) {
                    fn(elems[i], key, raw ? value : value.call(elems[i], i, fn(elems[i], key)));
                }
            }
        }
        if (chainable) {
            return elems;
        }
        if (bulk) {
            return fn.call(elems);
        }
        return len ? fn(elems[0], key) : emptyGet;
    };
    var acceptData = function(owner) {
        return owner.nodeType === 1 || owner.nodeType === 9 || !+owner.nodeType;
    };
    function Data() {
        this.expando = jQuery.expando + Data.uid++;
    }
    Data.uid = 1;
    Data.prototype = {
        cache: function(owner) {
            var value = owner[this.expando];
            if (!value) {
                value = {};
                if (acceptData(owner)) {
                    if (owner.nodeType) {
                        owner[this.expando] = value;
                    } else {
                        Object.defineProperty(owner, this.expando, {
                            value: value,
                            configurable: true
                        });
                    }
                }
            }
            return value;
        },
        set: function(owner, data, value) {
            var prop, cache = this.cache(owner);
            if (typeof data === "string") {
                cache[jQuery.camelCase(data)] = value;
            } else {
                for (prop in data) {
                    cache[jQuery.camelCase(prop)] = data[prop];
                }
            }
            return cache;
        },
        get: function(owner, key) {
            return key === undefined ? this.cache(owner) : owner[this.expando] && owner[this.expando][jQuery.camelCase(key)];
        },
        access: function(owner, key, value) {
            if (key === undefined || key && typeof key === "string" && value === undefined) {
                return this.get(owner, key);
            }
            this.set(owner, key, value);
            return value !== undefined ? value : key;
        },
        remove: function(owner, key) {
            var i, cache = owner[this.expando];
            if (cache === undefined) {
                return;
            }
            if (key !== undefined) {
                if (Array.isArray(key)) {
                    key = key.map(jQuery.camelCase);
                } else {
                    key = jQuery.camelCase(key);
                    key = key in cache ? [ key ] : key.match(rnothtmlwhite) || [];
                }
                i = key.length;
                while (i--) {
                    delete cache[key[i]];
                }
            }
            if (key === undefined || jQuery.isEmptyObject(cache)) {
                if (owner.nodeType) {
                    owner[this.expando] = undefined;
                } else {
                    delete owner[this.expando];
                }
            }
        },
        hasData: function(owner) {
            var cache = owner[this.expando];
            return cache !== undefined && !jQuery.isEmptyObject(cache);
        }
    };
    var dataPriv = new Data();
    var dataUser = new Data();
    var rbrace = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/, rmultiDash = /[A-Z]/g;
    function getData(data) {
        if (data === "true") {
            return true;
        }
        if (data === "false") {
            return false;
        }
        if (data === "null") {
            return null;
        }
        if (data === +data + "") {
            return +data;
        }
        if (rbrace.test(data)) {
            return JSON.parse(data);
        }
        return data;
    }
    function dataAttr(elem, key, data) {
        var name;
        if (data === undefined && elem.nodeType === 1) {
            name = "data-" + key.replace(rmultiDash, "-$&").toLowerCase();
            data = elem.getAttribute(name);
            if (typeof data === "string") {
                try {
                    data = getData(data);
                } catch (e) {}
                dataUser.set(elem, key, data);
            } else {
                data = undefined;
            }
        }
        return data;
    }
    jQuery.extend({
        hasData: function(elem) {
            return dataUser.hasData(elem) || dataPriv.hasData(elem);
        },
        data: function(elem, name, data) {
            return dataUser.access(elem, name, data);
        },
        removeData: function(elem, name) {
            dataUser.remove(elem, name);
        },
        _data: function(elem, name, data) {
            return dataPriv.access(elem, name, data);
        },
        _removeData: function(elem, name) {
            dataPriv.remove(elem, name);
        }
    });
    jQuery.fn.extend({
        data: function(key, value) {
            var i, name, data, elem = this[0], attrs = elem && elem.attributes;
            if (key === undefined) {
                if (this.length) {
                    data = dataUser.get(elem);
                    if (elem.nodeType === 1 && !dataPriv.get(elem, "hasDataAttrs")) {
                        i = attrs.length;
                        while (i--) {
                            if (attrs[i]) {
                                name = attrs[i].name;
                                if (name.indexOf("data-") === 0) {
                                    name = jQuery.camelCase(name.slice(5));
                                    dataAttr(elem, name, data[name]);
                                }
                            }
                        }
                        dataPriv.set(elem, "hasDataAttrs", true);
                    }
                }
                return data;
            }
            if (typeof key === "object") {
                return this.each(function() {
                    dataUser.set(this, key);
                });
            }
            return access(this, function(value) {
                var data;
                if (elem && value === undefined) {
                    data = dataUser.get(elem, key);
                    if (data !== undefined) {
                        return data;
                    }
                    data = dataAttr(elem, key);
                    if (data !== undefined) {
                        return data;
                    }
                    return;
                }
                this.each(function() {
                    dataUser.set(this, key, value);
                });
            }, null, value, arguments.length > 1, null, true);
        },
        removeData: function(key) {
            return this.each(function() {
                dataUser.remove(this, key);
            });
        }
    });
    jQuery.extend({
        queue: function(elem, type, data) {
            var queue;
            if (elem) {
                type = (type || "fx") + "queue";
                queue = dataPriv.get(elem, type);
                if (data) {
                    if (!queue || Array.isArray(data)) {
                        queue = dataPriv.access(elem, type, jQuery.makeArray(data));
                    } else {
                        queue.push(data);
                    }
                }
                return queue || [];
            }
        },
        dequeue: function(elem, type) {
            type = type || "fx";
            var queue = jQuery.queue(elem, type), startLength = queue.length, fn = queue.shift(), hooks = jQuery._queueHooks(elem, type), next = function() {
                jQuery.dequeue(elem, type);
            };
            if (fn === "inprogress") {
                fn = queue.shift();
                startLength--;
            }
            if (fn) {
                if (type === "fx") {
                    queue.unshift("inprogress");
                }
                delete hooks.stop;
                fn.call(elem, next, hooks);
            }
            if (!startLength && hooks) {
                hooks.empty.fire();
            }
        },
        _queueHooks: function(elem, type) {
            var key = type + "queueHooks";
            return dataPriv.get(elem, key) || dataPriv.access(elem, key, {
                empty: jQuery.Callbacks("once memory").add(function() {
                    dataPriv.remove(elem, [ type + "queue", key ]);
                })
            });
        }
    });
    jQuery.fn.extend({
        queue: function(type, data) {
            var setter = 2;
            if (typeof type !== "string") {
                data = type;
                type = "fx";
                setter--;
            }
            if (arguments.length < setter) {
                return jQuery.queue(this[0], type);
            }
            return data === undefined ? this : this.each(function() {
                var queue = jQuery.queue(this, type, data);
                jQuery._queueHooks(this, type);
                if (type === "fx" && queue[0] !== "inprogress") {
                    jQuery.dequeue(this, type);
                }
            });
        },
        dequeue: function(type) {
            return this.each(function() {
                jQuery.dequeue(this, type);
            });
        },
        clearQueue: function(type) {
            return this.queue(type || "fx", []);
        },
        promise: function(type, obj) {
            var tmp, count = 1, defer = jQuery.Deferred(), elements = this, i = this.length, resolve = function() {
                if (!--count) {
                    defer.resolveWith(elements, [ elements ]);
                }
            };
            if (typeof type !== "string") {
                obj = type;
                type = undefined;
            }
            type = type || "fx";
            while (i--) {
                tmp = dataPriv.get(elements[i], type + "queueHooks");
                if (tmp && tmp.empty) {
                    count++;
                    tmp.empty.add(resolve);
                }
            }
            resolve();
            return defer.promise(obj);
        }
    });
    var pnum = /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source;
    var rcssNum = new RegExp("^(?:([+-])=|)(" + pnum + ")([a-z%]*)$", "i");
    var cssExpand = [ "Top", "Right", "Bottom", "Left" ];
    var isHiddenWithinTree = function(elem, el) {
        elem = el || elem;
        return elem.style.display === "none" || elem.style.display === "" && jQuery.contains(elem.ownerDocument, elem) && jQuery.css(elem, "display") === "none";
    };
    var swap = function(elem, options, callback, args) {
        var ret, name, old = {};
        for (name in options) {
            old[name] = elem.style[name];
            elem.style[name] = options[name];
        }
        ret = callback.apply(elem, args || []);
        for (name in options) {
            elem.style[name] = old[name];
        }
        return ret;
    };
    function adjustCSS(elem, prop, valueParts, tween) {
        var adjusted, scale = 1, maxIterations = 20, currentValue = tween ? function() {
            return tween.cur();
        } : function() {
            return jQuery.css(elem, prop, "");
        }, initial = currentValue(), unit = valueParts && valueParts[3] || (jQuery.cssNumber[prop] ? "" : "px"), initialInUnit = (jQuery.cssNumber[prop] || unit !== "px" && +initial) && rcssNum.exec(jQuery.css(elem, prop));
        if (initialInUnit && initialInUnit[3] !== unit) {
            unit = unit || initialInUnit[3];
            valueParts = valueParts || [];
            initialInUnit = +initial || 1;
            do {
                scale = scale || ".5";
                initialInUnit = initialInUnit / scale;
                jQuery.style(elem, prop, initialInUnit + unit);
            } while (scale !== (scale = currentValue() / initial) && scale !== 1 && --maxIterations);
        }
        if (valueParts) {
            initialInUnit = +initialInUnit || +initial || 0;
            adjusted = valueParts[1] ? initialInUnit + (valueParts[1] + 1) * valueParts[2] : +valueParts[2];
            if (tween) {
                tween.unit = unit;
                tween.start = initialInUnit;
                tween.end = adjusted;
            }
        }
        return adjusted;
    }
    var defaultDisplayMap = {};
    function getDefaultDisplay(elem) {
        var temp, doc = elem.ownerDocument, nodeName = elem.nodeName, display = defaultDisplayMap[nodeName];
        if (display) {
            return display;
        }
        temp = doc.body.appendChild(doc.createElement(nodeName));
        display = jQuery.css(temp, "display");
        temp.parentNode.removeChild(temp);
        if (display === "none") {
            display = "block";
        }
        defaultDisplayMap[nodeName] = display;
        return display;
    }
    function showHide(elements, show) {
        var display, elem, values = [], index = 0, length = elements.length;
        for (;index < length; index++) {
            elem = elements[index];
            if (!elem.style) {
                continue;
            }
            display = elem.style.display;
            if (show) {
                if (display === "none") {
                    values[index] = dataPriv.get(elem, "display") || null;
                    if (!values[index]) {
                        elem.style.display = "";
                    }
                }
                if (elem.style.display === "" && isHiddenWithinTree(elem)) {
                    values[index] = getDefaultDisplay(elem);
                }
            } else {
                if (display !== "none") {
                    values[index] = "none";
                    dataPriv.set(elem, "display", display);
                }
            }
        }
        for (index = 0; index < length; index++) {
            if (values[index] != null) {
                elements[index].style.display = values[index];
            }
        }
        return elements;
    }
    jQuery.fn.extend({
        show: function() {
            return showHide(this, true);
        },
        hide: function() {
            return showHide(this);
        },
        toggle: function(state) {
            if (typeof state === "boolean") {
                return state ? this.show() : this.hide();
            }
            return this.each(function() {
                if (isHiddenWithinTree(this)) {
                    jQuery(this).show();
                } else {
                    jQuery(this).hide();
                }
            });
        }
    });
    var rcheckableType = /^(?:checkbox|radio)$/i;
    var rtagName = /<([a-z][^\/\0>\x20\t\r\n\f]+)/i;
    var rscriptType = /^$|\/(?:java|ecma)script/i;
    var wrapMap = {
        option: [ 1, "<select multiple='multiple'>", "</select>" ],
        thead: [ 1, "<table>", "</table>" ],
        col: [ 2, "<table><colgroup>", "</colgroup></table>" ],
        tr: [ 2, "<table><tbody>", "</tbody></table>" ],
        td: [ 3, "<table><tbody><tr>", "</tr></tbody></table>" ],
        _default: [ 0, "", "" ]
    };
    wrapMap.optgroup = wrapMap.option;
    wrapMap.tbody = wrapMap.tfoot = wrapMap.colgroup = wrapMap.caption = wrapMap.thead;
    wrapMap.th = wrapMap.td;
    function getAll(context, tag) {
        var ret;
        if (typeof context.getElementsByTagName !== "undefined") {
            ret = context.getElementsByTagName(tag || "*");
        } else if (typeof context.querySelectorAll !== "undefined") {
            ret = context.querySelectorAll(tag || "*");
        } else {
            ret = [];
        }
        if (tag === undefined || tag && nodeName(context, tag)) {
            return jQuery.merge([ context ], ret);
        }
        return ret;
    }
    function setGlobalEval(elems, refElements) {
        var i = 0, l = elems.length;
        for (;i < l; i++) {
            dataPriv.set(elems[i], "globalEval", !refElements || dataPriv.get(refElements[i], "globalEval"));
        }
    }
    var rhtml = /<|&#?\w+;/;
    function buildFragment(elems, context, scripts, selection, ignored) {
        var elem, tmp, tag, wrap, contains, j, fragment = context.createDocumentFragment(), nodes = [], i = 0, l = elems.length;
        for (;i < l; i++) {
            elem = elems[i];
            if (elem || elem === 0) {
                if (jQuery.type(elem) === "object") {
                    jQuery.merge(nodes, elem.nodeType ? [ elem ] : elem);
                } else if (!rhtml.test(elem)) {
                    nodes.push(context.createTextNode(elem));
                } else {
                    tmp = tmp || fragment.appendChild(context.createElement("div"));
                    tag = (rtagName.exec(elem) || [ "", "" ])[1].toLowerCase();
                    wrap = wrapMap[tag] || wrapMap._default;
                    tmp.innerHTML = wrap[1] + jQuery.htmlPrefilter(elem) + wrap[2];
                    j = wrap[0];
                    while (j--) {
                        tmp = tmp.lastChild;
                    }
                    jQuery.merge(nodes, tmp.childNodes);
                    tmp = fragment.firstChild;
                    tmp.textContent = "";
                }
            }
        }
        fragment.textContent = "";
        i = 0;
        while (elem = nodes[i++]) {
            if (selection && jQuery.inArray(elem, selection) > -1) {
                if (ignored) {
                    ignored.push(elem);
                }
                continue;
            }
            contains = jQuery.contains(elem.ownerDocument, elem);
            tmp = getAll(fragment.appendChild(elem), "script");
            if (contains) {
                setGlobalEval(tmp);
            }
            if (scripts) {
                j = 0;
                while (elem = tmp[j++]) {
                    if (rscriptType.test(elem.type || "")) {
                        scripts.push(elem);
                    }
                }
            }
        }
        return fragment;
    }
    (function() {
        var fragment = document.createDocumentFragment(), div = fragment.appendChild(document.createElement("div")), input = document.createElement("input");
        input.setAttribute("type", "radio");
        input.setAttribute("checked", "checked");
        input.setAttribute("name", "t");
        div.appendChild(input);
        support.checkClone = div.cloneNode(true).cloneNode(true).lastChild.checked;
        div.innerHTML = "<textarea>x</textarea>";
        support.noCloneChecked = !!div.cloneNode(true).lastChild.defaultValue;
    })();
    var documentElement = document.documentElement;
    var rkeyEvent = /^key/, rmouseEvent = /^(?:mouse|pointer|contextmenu|drag|drop)|click/, rtypenamespace = /^([^.]*)(?:\.(.+)|)/;
    function returnTrue() {
        return true;
    }
    function returnFalse() {
        return false;
    }
    function safeActiveElement() {
        try {
            return document.activeElement;
        } catch (err) {}
    }
    function on(elem, types, selector, data, fn, one) {
        var origFn, type;
        if (typeof types === "object") {
            if (typeof selector !== "string") {
                data = data || selector;
                selector = undefined;
            }
            for (type in types) {
                on(elem, type, selector, data, types[type], one);
            }
            return elem;
        }
        if (data == null && fn == null) {
            fn = selector;
            data = selector = undefined;
        } else if (fn == null) {
            if (typeof selector === "string") {
                fn = data;
                data = undefined;
            } else {
                fn = data;
                data = selector;
                selector = undefined;
            }
        }
        if (fn === false) {
            fn = returnFalse;
        } else if (!fn) {
            return elem;
        }
        if (one === 1) {
            origFn = fn;
            fn = function(event) {
                jQuery().off(event);
                return origFn.apply(this, arguments);
            };
            fn.guid = origFn.guid || (origFn.guid = jQuery.guid++);
        }
        return elem.each(function() {
            jQuery.event.add(this, types, fn, data, selector);
        });
    }
    jQuery.event = {
        global: {},
        add: function(elem, types, handler, data, selector) {
            var handleObjIn, eventHandle, tmp, events, t, handleObj, special, handlers, type, namespaces, origType, elemData = dataPriv.get(elem);
            if (!elemData) {
                return;
            }
            if (handler.handler) {
                handleObjIn = handler;
                handler = handleObjIn.handler;
                selector = handleObjIn.selector;
            }
            if (selector) {
                jQuery.find.matchesSelector(documentElement, selector);
            }
            if (!handler.guid) {
                handler.guid = jQuery.guid++;
            }
            if (!(events = elemData.events)) {
                events = elemData.events = {};
            }
            if (!(eventHandle = elemData.handle)) {
                eventHandle = elemData.handle = function(e) {
                    return typeof jQuery !== "undefined" && jQuery.event.triggered !== e.type ? jQuery.event.dispatch.apply(elem, arguments) : undefined;
                };
            }
            types = (types || "").match(rnothtmlwhite) || [ "" ];
            t = types.length;
            while (t--) {
                tmp = rtypenamespace.exec(types[t]) || [];
                type = origType = tmp[1];
                namespaces = (tmp[2] || "").split(".").sort();
                if (!type) {
                    continue;
                }
                special = jQuery.event.special[type] || {};
                type = (selector ? special.delegateType : special.bindType) || type;
                special = jQuery.event.special[type] || {};
                handleObj = jQuery.extend({
                    type: type,
                    origType: origType,
                    data: data,
                    handler: handler,
                    guid: handler.guid,
                    selector: selector,
                    needsContext: selector && jQuery.expr.match.needsContext.test(selector),
                    namespace: namespaces.join(".")
                }, handleObjIn);
                if (!(handlers = events[type])) {
                    handlers = events[type] = [];
                    handlers.delegateCount = 0;
                    if (!special.setup || special.setup.call(elem, data, namespaces, eventHandle) === false) {
                        if (elem.addEventListener) {
                            elem.addEventListener(type, eventHandle);
                        }
                    }
                }
                if (special.add) {
                    special.add.call(elem, handleObj);
                    if (!handleObj.handler.guid) {
                        handleObj.handler.guid = handler.guid;
                    }
                }
                if (selector) {
                    handlers.splice(handlers.delegateCount++, 0, handleObj);
                } else {
                    handlers.push(handleObj);
                }
                jQuery.event.global[type] = true;
            }
        },
        remove: function(elem, types, handler, selector, mappedTypes) {
            var j, origCount, tmp, events, t, handleObj, special, handlers, type, namespaces, origType, elemData = dataPriv.hasData(elem) && dataPriv.get(elem);
            if (!elemData || !(events = elemData.events)) {
                return;
            }
            types = (types || "").match(rnothtmlwhite) || [ "" ];
            t = types.length;
            while (t--) {
                tmp = rtypenamespace.exec(types[t]) || [];
                type = origType = tmp[1];
                namespaces = (tmp[2] || "").split(".").sort();
                if (!type) {
                    for (type in events) {
                        jQuery.event.remove(elem, type + types[t], handler, selector, true);
                    }
                    continue;
                }
                special = jQuery.event.special[type] || {};
                type = (selector ? special.delegateType : special.bindType) || type;
                handlers = events[type] || [];
                tmp = tmp[2] && new RegExp("(^|\\.)" + namespaces.join("\\.(?:.*\\.|)") + "(\\.|$)");
                origCount = j = handlers.length;
                while (j--) {
                    handleObj = handlers[j];
                    if ((mappedTypes || origType === handleObj.origType) && (!handler || handler.guid === handleObj.guid) && (!tmp || tmp.test(handleObj.namespace)) && (!selector || selector === handleObj.selector || selector === "**" && handleObj.selector)) {
                        handlers.splice(j, 1);
                        if (handleObj.selector) {
                            handlers.delegateCount--;
                        }
                        if (special.remove) {
                            special.remove.call(elem, handleObj);
                        }
                    }
                }
                if (origCount && !handlers.length) {
                    if (!special.teardown || special.teardown.call(elem, namespaces, elemData.handle) === false) {
                        jQuery.removeEvent(elem, type, elemData.handle);
                    }
                    delete events[type];
                }
            }
            if (jQuery.isEmptyObject(events)) {
                dataPriv.remove(elem, "handle events");
            }
        },
        dispatch: function(nativeEvent) {
            var event = jQuery.event.fix(nativeEvent);
            var i, j, ret, matched, handleObj, handlerQueue, args = new Array(arguments.length), handlers = (dataPriv.get(this, "events") || {})[event.type] || [], special = jQuery.event.special[event.type] || {};
            args[0] = event;
            for (i = 1; i < arguments.length; i++) {
                args[i] = arguments[i];
            }
            event.delegateTarget = this;
            if (special.preDispatch && special.preDispatch.call(this, event) === false) {
                return;
            }
            handlerQueue = jQuery.event.handlers.call(this, event, handlers);
            i = 0;
            while ((matched = handlerQueue[i++]) && !event.isPropagationStopped()) {
                event.currentTarget = matched.elem;
                j = 0;
                while ((handleObj = matched.handlers[j++]) && !event.isImmediatePropagationStopped()) {
                    if (!event.rnamespace || event.rnamespace.test(handleObj.namespace)) {
                        event.handleObj = handleObj;
                        event.data = handleObj.data;
                        ret = ((jQuery.event.special[handleObj.origType] || {}).handle || handleObj.handler).apply(matched.elem, args);
                        if (ret !== undefined) {
                            if ((event.result = ret) === false) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                        }
                    }
                }
            }
            if (special.postDispatch) {
                special.postDispatch.call(this, event);
            }
            return event.result;
        },
        handlers: function(event, handlers) {
            var i, handleObj, sel, matchedHandlers, matchedSelectors, handlerQueue = [], delegateCount = handlers.delegateCount, cur = event.target;
            if (delegateCount && cur.nodeType && !(event.type === "click" && event.button >= 1)) {
                for (;cur !== this; cur = cur.parentNode || this) {
                    if (cur.nodeType === 1 && !(event.type === "click" && cur.disabled === true)) {
                        matchedHandlers = [];
                        matchedSelectors = {};
                        for (i = 0; i < delegateCount; i++) {
                            handleObj = handlers[i];
                            sel = handleObj.selector + " ";
                            if (matchedSelectors[sel] === undefined) {
                                matchedSelectors[sel] = handleObj.needsContext ? jQuery(sel, this).index(cur) > -1 : jQuery.find(sel, this, null, [ cur ]).length;
                            }
                            if (matchedSelectors[sel]) {
                                matchedHandlers.push(handleObj);
                            }
                        }
                        if (matchedHandlers.length) {
                            handlerQueue.push({
                                elem: cur,
                                handlers: matchedHandlers
                            });
                        }
                    }
                }
            }
            cur = this;
            if (delegateCount < handlers.length) {
                handlerQueue.push({
                    elem: cur,
                    handlers: handlers.slice(delegateCount)
                });
            }
            return handlerQueue;
        },
        addProp: function(name, hook) {
            Object.defineProperty(jQuery.Event.prototype, name, {
                enumerable: true,
                configurable: true,
                get: jQuery.isFunction(hook) ? function() {
                    if (this.originalEvent) {
                        return hook(this.originalEvent);
                    }
                } : function() {
                    if (this.originalEvent) {
                        return this.originalEvent[name];
                    }
                },
                set: function(value) {
                    Object.defineProperty(this, name, {
                        enumerable: true,
                        configurable: true,
                        writable: true,
                        value: value
                    });
                }
            });
        },
        fix: function(originalEvent) {
            return originalEvent[jQuery.expando] ? originalEvent : new jQuery.Event(originalEvent);
        },
        special: {
            load: {
                noBubble: true
            },
            focus: {
                trigger: function() {
                    if (this !== safeActiveElement() && this.focus) {
                        this.focus();
                        return false;
                    }
                },
                delegateType: "focusin"
            },
            blur: {
                trigger: function() {
                    if (this === safeActiveElement() && this.blur) {
                        this.blur();
                        return false;
                    }
                },
                delegateType: "focusout"
            },
            click: {
                trigger: function() {
                    if (this.type === "checkbox" && this.click && nodeName(this, "input")) {
                        this.click();
                        return false;
                    }
                },
                _default: function(event) {
                    return nodeName(event.target, "a");
                }
            },
            beforeunload: {
                postDispatch: function(event) {
                    if (event.result !== undefined && event.originalEvent) {
                        event.originalEvent.returnValue = event.result;
                    }
                }
            }
        }
    };
    jQuery.removeEvent = function(elem, type, handle) {
        if (elem.removeEventListener) {
            elem.removeEventListener(type, handle);
        }
    };
    jQuery.Event = function(src, props) {
        if (!(this instanceof jQuery.Event)) {
            return new jQuery.Event(src, props);
        }
        if (src && src.type) {
            this.originalEvent = src;
            this.type = src.type;
            this.isDefaultPrevented = src.defaultPrevented || src.defaultPrevented === undefined && src.returnValue === false ? returnTrue : returnFalse;
            this.target = src.target && src.target.nodeType === 3 ? src.target.parentNode : src.target;
            this.currentTarget = src.currentTarget;
            this.relatedTarget = src.relatedTarget;
        } else {
            this.type = src;
        }
        if (props) {
            jQuery.extend(this, props);
        }
        this.timeStamp = src && src.timeStamp || jQuery.now();
        this[jQuery.expando] = true;
    };
    jQuery.Event.prototype = {
        constructor: jQuery.Event,
        isDefaultPrevented: returnFalse,
        isPropagationStopped: returnFalse,
        isImmediatePropagationStopped: returnFalse,
        isSimulated: false,
        preventDefault: function() {
            var e = this.originalEvent;
            this.isDefaultPrevented = returnTrue;
            if (e && !this.isSimulated) {
                e.preventDefault();
            }
        },
        stopPropagation: function() {
            var e = this.originalEvent;
            this.isPropagationStopped = returnTrue;
            if (e && !this.isSimulated) {
                e.stopPropagation();
            }
        },
        stopImmediatePropagation: function() {
            var e = this.originalEvent;
            this.isImmediatePropagationStopped = returnTrue;
            if (e && !this.isSimulated) {
                e.stopImmediatePropagation();
            }
            this.stopPropagation();
        }
    };
    jQuery.each({
        altKey: true,
        bubbles: true,
        cancelable: true,
        changedTouches: true,
        ctrlKey: true,
        detail: true,
        eventPhase: true,
        metaKey: true,
        pageX: true,
        pageY: true,
        shiftKey: true,
        view: true,
        char: true,
        charCode: true,
        key: true,
        keyCode: true,
        button: true,
        buttons: true,
        clientX: true,
        clientY: true,
        offsetX: true,
        offsetY: true,
        pointerId: true,
        pointerType: true,
        screenX: true,
        screenY: true,
        targetTouches: true,
        toElement: true,
        touches: true,
        which: function(event) {
            var button = event.button;
            if (event.which == null && rkeyEvent.test(event.type)) {
                return event.charCode != null ? event.charCode : event.keyCode;
            }
            if (!event.which && button !== undefined && rmouseEvent.test(event.type)) {
                if (button & 1) {
                    return 1;
                }
                if (button & 2) {
                    return 3;
                }
                if (button & 4) {
                    return 2;
                }
                return 0;
            }
            return event.which;
        }
    }, jQuery.event.addProp);
    jQuery.each({
        mouseenter: "mouseover",
        mouseleave: "mouseout",
        pointerenter: "pointerover",
        pointerleave: "pointerout"
    }, function(orig, fix) {
        jQuery.event.special[orig] = {
            delegateType: fix,
            bindType: fix,
            handle: function(event) {
                var ret, target = this, related = event.relatedTarget, handleObj = event.handleObj;
                if (!related || related !== target && !jQuery.contains(target, related)) {
                    event.type = handleObj.origType;
                    ret = handleObj.handler.apply(this, arguments);
                    event.type = fix;
                }
                return ret;
            }
        };
    });
    jQuery.fn.extend({
        on: function(types, selector, data, fn) {
            return on(this, types, selector, data, fn);
        },
        one: function(types, selector, data, fn) {
            return on(this, types, selector, data, fn, 1);
        },
        off: function(types, selector, fn) {
            var handleObj, type;
            if (types && types.preventDefault && types.handleObj) {
                handleObj = types.handleObj;
                jQuery(types.delegateTarget).off(handleObj.namespace ? handleObj.origType + "." + handleObj.namespace : handleObj.origType, handleObj.selector, handleObj.handler);
                return this;
            }
            if (typeof types === "object") {
                for (type in types) {
                    this.off(type, selector, types[type]);
                }
                return this;
            }
            if (selector === false || typeof selector === "function") {
                fn = selector;
                selector = undefined;
            }
            if (fn === false) {
                fn = returnFalse;
            }
            return this.each(function() {
                jQuery.event.remove(this, types, fn, selector);
            });
        }
    });
    var rxhtmlTag = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([a-z][^\/\0>\x20\t\r\n\f]*)[^>]*)\/>/gi, rnoInnerhtml = /<script|<style|<link/i, rchecked = /checked\s*(?:[^=]|=\s*.checked.)/i, rscriptTypeMasked = /^true\/(.*)/, rcleanScript = /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g;
    function manipulationTarget(elem, content) {
        if (nodeName(elem, "table") && nodeName(content.nodeType !== 11 ? content : content.firstChild, "tr")) {
            return jQuery(">tbody", elem)[0] || elem;
        }
        return elem;
    }
    function disableScript(elem) {
        elem.type = (elem.getAttribute("type") !== null) + "/" + elem.type;
        return elem;
    }
    function restoreScript(elem) {
        var match = rscriptTypeMasked.exec(elem.type);
        if (match) {
            elem.type = match[1];
        } else {
            elem.removeAttribute("type");
        }
        return elem;
    }
    function cloneCopyEvent(src, dest) {
        var i, l, type, pdataOld, pdataCur, udataOld, udataCur, events;
        if (dest.nodeType !== 1) {
            return;
        }
        if (dataPriv.hasData(src)) {
            pdataOld = dataPriv.access(src);
            pdataCur = dataPriv.set(dest, pdataOld);
            events = pdataOld.events;
            if (events) {
                delete pdataCur.handle;
                pdataCur.events = {};
                for (type in events) {
                    for (i = 0, l = events[type].length; i < l; i++) {
                        jQuery.event.add(dest, type, events[type][i]);
                    }
                }
            }
        }
        if (dataUser.hasData(src)) {
            udataOld = dataUser.access(src);
            udataCur = jQuery.extend({}, udataOld);
            dataUser.set(dest, udataCur);
        }
    }
    function fixInput(src, dest) {
        var nodeName = dest.nodeName.toLowerCase();
        if (nodeName === "input" && rcheckableType.test(src.type)) {
            dest.checked = src.checked;
        } else if (nodeName === "input" || nodeName === "textarea") {
            dest.defaultValue = src.defaultValue;
        }
    }
    function domManip(collection, args, callback, ignored) {
        args = concat.apply([], args);
        var fragment, first, scripts, hasScripts, node, doc, i = 0, l = collection.length, iNoClone = l - 1, value = args[0], isFunction = jQuery.isFunction(value);
        if (isFunction || l > 1 && typeof value === "string" && !support.checkClone && rchecked.test(value)) {
            return collection.each(function(index) {
                var self = collection.eq(index);
                if (isFunction) {
                    args[0] = value.call(this, index, self.html());
                }
                domManip(self, args, callback, ignored);
            });
        }
        if (l) {
            fragment = buildFragment(args, collection[0].ownerDocument, false, collection, ignored);
            first = fragment.firstChild;
            if (fragment.childNodes.length === 1) {
                fragment = first;
            }
            if (first || ignored) {
                scripts = jQuery.map(getAll(fragment, "script"), disableScript);
                hasScripts = scripts.length;
                for (;i < l; i++) {
                    node = fragment;
                    if (i !== iNoClone) {
                        node = jQuery.clone(node, true, true);
                        if (hasScripts) {
                            jQuery.merge(scripts, getAll(node, "script"));
                        }
                    }
                    callback.call(collection[i], node, i);
                }
                if (hasScripts) {
                    doc = scripts[scripts.length - 1].ownerDocument;
                    jQuery.map(scripts, restoreScript);
                    for (i = 0; i < hasScripts; i++) {
                        node = scripts[i];
                        if (rscriptType.test(node.type || "") && !dataPriv.access(node, "globalEval") && jQuery.contains(doc, node)) {
                            if (node.src) {
                                if (jQuery._evalUrl) {
                                    jQuery._evalUrl(node.src);
                                }
                            } else {
                                DOMEval(node.textContent.replace(rcleanScript, ""), doc);
                            }
                        }
                    }
                }
            }
        }
        return collection;
    }
    function remove(elem, selector, keepData) {
        var node, nodes = selector ? jQuery.filter(selector, elem) : elem, i = 0;
        for (;(node = nodes[i]) != null; i++) {
            if (!keepData && node.nodeType === 1) {
                jQuery.cleanData(getAll(node));
            }
            if (node.parentNode) {
                if (keepData && jQuery.contains(node.ownerDocument, node)) {
                    setGlobalEval(getAll(node, "script"));
                }
                node.parentNode.removeChild(node);
            }
        }
        return elem;
    }
    jQuery.extend({
        htmlPrefilter: function(html) {
            return html.replace(rxhtmlTag, "<$1></$2>");
        },
        clone: function(elem, dataAndEvents, deepDataAndEvents) {
            var i, l, srcElements, destElements, clone = elem.cloneNode(true), inPage = jQuery.contains(elem.ownerDocument, elem);
            if (!support.noCloneChecked && (elem.nodeType === 1 || elem.nodeType === 11) && !jQuery.isXMLDoc(elem)) {
                destElements = getAll(clone);
                srcElements = getAll(elem);
                for (i = 0, l = srcElements.length; i < l; i++) {
                    fixInput(srcElements[i], destElements[i]);
                }
            }
            if (dataAndEvents) {
                if (deepDataAndEvents) {
                    srcElements = srcElements || getAll(elem);
                    destElements = destElements || getAll(clone);
                    for (i = 0, l = srcElements.length; i < l; i++) {
                        cloneCopyEvent(srcElements[i], destElements[i]);
                    }
                } else {
                    cloneCopyEvent(elem, clone);
                }
            }
            destElements = getAll(clone, "script");
            if (destElements.length > 0) {
                setGlobalEval(destElements, !inPage && getAll(elem, "script"));
            }
            return clone;
        },
        cleanData: function(elems) {
            var data, elem, type, special = jQuery.event.special, i = 0;
            for (;(elem = elems[i]) !== undefined; i++) {
                if (acceptData(elem)) {
                    if (data = elem[dataPriv.expando]) {
                        if (data.events) {
                            for (type in data.events) {
                                if (special[type]) {
                                    jQuery.event.remove(elem, type);
                                } else {
                                    jQuery.removeEvent(elem, type, data.handle);
                                }
                            }
                        }
                        elem[dataPriv.expando] = undefined;
                    }
                    if (elem[dataUser.expando]) {
                        elem[dataUser.expando] = undefined;
                    }
                }
            }
        }
    });
    jQuery.fn.extend({
        detach: function(selector) {
            return remove(this, selector, true);
        },
        remove: function(selector) {
            return remove(this, selector);
        },
        text: function(value) {
            return access(this, function(value) {
                return value === undefined ? jQuery.text(this) : this.empty().each(function() {
                    if (this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9) {
                        this.textContent = value;
                    }
                });
            }, null, value, arguments.length);
        },
        append: function() {
            return domManip(this, arguments, function(elem) {
                if (this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9) {
                    var target = manipulationTarget(this, elem);
                    target.appendChild(elem);
                }
            });
        },
        prepend: function() {
            return domManip(this, arguments, function(elem) {
                if (this.nodeType === 1 || this.nodeType === 11 || this.nodeType === 9) {
                    var target = manipulationTarget(this, elem);
                    target.insertBefore(elem, target.firstChild);
                }
            });
        },
        before: function() {
            return domManip(this, arguments, function(elem) {
                if (this.parentNode) {
                    this.parentNode.insertBefore(elem, this);
                }
            });
        },
        after: function() {
            return domManip(this, arguments, function(elem) {
                if (this.parentNode) {
                    this.parentNode.insertBefore(elem, this.nextSibling);
                }
            });
        },
        empty: function() {
            var elem, i = 0;
            for (;(elem = this[i]) != null; i++) {
                if (elem.nodeType === 1) {
                    jQuery.cleanData(getAll(elem, false));
                    elem.textContent = "";
                }
            }
            return this;
        },
        clone: function(dataAndEvents, deepDataAndEvents) {
            dataAndEvents = dataAndEvents == null ? false : dataAndEvents;
            deepDataAndEvents = deepDataAndEvents == null ? dataAndEvents : deepDataAndEvents;
            return this.map(function() {
                return jQuery.clone(this, dataAndEvents, deepDataAndEvents);
            });
        },
        html: function(value) {
            return access(this, function(value) {
                var elem = this[0] || {}, i = 0, l = this.length;
                if (value === undefined && elem.nodeType === 1) {
                    return elem.innerHTML;
                }
                if (typeof value === "string" && !rnoInnerhtml.test(value) && !wrapMap[(rtagName.exec(value) || [ "", "" ])[1].toLowerCase()]) {
                    value = jQuery.htmlPrefilter(value);
                    try {
                        for (;i < l; i++) {
                            elem = this[i] || {};
                            if (elem.nodeType === 1) {
                                jQuery.cleanData(getAll(elem, false));
                                elem.innerHTML = value;
                            }
                        }
                        elem = 0;
                    } catch (e) {}
                }
                if (elem) {
                    this.empty().append(value);
                }
            }, null, value, arguments.length);
        },
        replaceWith: function() {
            var ignored = [];
            return domManip(this, arguments, function(elem) {
                var parent = this.parentNode;
                if (jQuery.inArray(this, ignored) < 0) {
                    jQuery.cleanData(getAll(this));
                    if (parent) {
                        parent.replaceChild(elem, this);
                    }
                }
            }, ignored);
        }
    });
    jQuery.each({
        appendTo: "append",
        prependTo: "prepend",
        insertBefore: "before",
        insertAfter: "after",
        replaceAll: "replaceWith"
    }, function(name, original) {
        jQuery.fn[name] = function(selector) {
            var elems, ret = [], insert = jQuery(selector), last = insert.length - 1, i = 0;
            for (;i <= last; i++) {
                elems = i === last ? this : this.clone(true);
                jQuery(insert[i])[original](elems);
                push.apply(ret, elems.get());
            }
            return this.pushStack(ret);
        };
    });
    var rmargin = /^margin/;
    var rnumnonpx = new RegExp("^(" + pnum + ")(?!px)[a-z%]+$", "i");
    var getStyles = function(elem) {
        var view = elem.ownerDocument.defaultView;
        if (!view || !view.opener) {
            view = window;
        }
        return view.getComputedStyle(elem);
    };
    (function() {
        function computeStyleTests() {
            if (!div) {
                return;
            }
            div.style.cssText = "box-sizing:border-box;" + "position:relative;display:block;" + "margin:auto;border:1px;padding:1px;" + "top:1%;width:50%";
            div.innerHTML = "";
            documentElement.appendChild(container);
            var divStyle = window.getComputedStyle(div);
            pixelPositionVal = divStyle.top !== "1%";
            reliableMarginLeftVal = divStyle.marginLeft === "2px";
            boxSizingReliableVal = divStyle.width === "4px";
            div.style.marginRight = "50%";
            pixelMarginRightVal = divStyle.marginRight === "4px";
            documentElement.removeChild(container);
            div = null;
        }
        var pixelPositionVal, boxSizingReliableVal, pixelMarginRightVal, reliableMarginLeftVal, container = document.createElement("div"), div = document.createElement("div");
        if (!div.style) {
            return;
        }
        div.style.backgroundClip = "content-box";
        div.cloneNode(true).style.backgroundClip = "";
        support.clearCloneStyle = div.style.backgroundClip === "content-box";
        container.style.cssText = "border:0;width:8px;height:0;top:0;left:-9999px;" + "padding:0;margin-top:1px;position:absolute";
        container.appendChild(div);
        jQuery.extend(support, {
            pixelPosition: function() {
                computeStyleTests();
                return pixelPositionVal;
            },
            boxSizingReliable: function() {
                computeStyleTests();
                return boxSizingReliableVal;
            },
            pixelMarginRight: function() {
                computeStyleTests();
                return pixelMarginRightVal;
            },
            reliableMarginLeft: function() {
                computeStyleTests();
                return reliableMarginLeftVal;
            }
        });
    })();
    function curCSS(elem, name, computed) {
        var width, minWidth, maxWidth, ret, style = elem.style;
        computed = computed || getStyles(elem);
        if (computed) {
            ret = computed.getPropertyValue(name) || computed[name];
            if (ret === "" && !jQuery.contains(elem.ownerDocument, elem)) {
                ret = jQuery.style(elem, name);
            }
            if (!support.pixelMarginRight() && rnumnonpx.test(ret) && rmargin.test(name)) {
                width = style.width;
                minWidth = style.minWidth;
                maxWidth = style.maxWidth;
                style.minWidth = style.maxWidth = style.width = ret;
                ret = computed.width;
                style.width = width;
                style.minWidth = minWidth;
                style.maxWidth = maxWidth;
            }
        }
        return ret !== undefined ? ret + "" : ret;
    }
    function addGetHookIf(conditionFn, hookFn) {
        return {
            get: function() {
                if (conditionFn()) {
                    delete this.get;
                    return;
                }
                return (this.get = hookFn).apply(this, arguments);
            }
        };
    }
    var rdisplayswap = /^(none|table(?!-c[ea]).+)/, rcustomProp = /^--/, cssShow = {
        position: "absolute",
        visibility: "hidden",
        display: "block"
    }, cssNormalTransform = {
        letterSpacing: "0",
        fontWeight: "400"
    }, cssPrefixes = [ "Webkit", "Moz", "ms" ], emptyStyle = document.createElement("div").style;
    function vendorPropName(name) {
        if (name in emptyStyle) {
            return name;
        }
        var capName = name[0].toUpperCase() + name.slice(1), i = cssPrefixes.length;
        while (i--) {
            name = cssPrefixes[i] + capName;
            if (name in emptyStyle) {
                return name;
            }
        }
    }
    function finalPropName(name) {
        var ret = jQuery.cssProps[name];
        if (!ret) {
            ret = jQuery.cssProps[name] = vendorPropName(name) || name;
        }
        return ret;
    }
    function setPositiveNumber(elem, value, subtract) {
        var matches = rcssNum.exec(value);
        return matches ? Math.max(0, matches[2] - (subtract || 0)) + (matches[3] || "px") : value;
    }
    function augmentWidthOrHeight(elem, name, extra, isBorderBox, styles) {
        var i, val = 0;
        if (extra === (isBorderBox ? "border" : "content")) {
            i = 4;
        } else {
            i = name === "width" ? 1 : 0;
        }
        for (;i < 4; i += 2) {
            if (extra === "margin") {
                val += jQuery.css(elem, extra + cssExpand[i], true, styles);
            }
            if (isBorderBox) {
                if (extra === "content") {
                    val -= jQuery.css(elem, "padding" + cssExpand[i], true, styles);
                }
                if (extra !== "margin") {
                    val -= jQuery.css(elem, "border" + cssExpand[i] + "Width", true, styles);
                }
            } else {
                val += jQuery.css(elem, "padding" + cssExpand[i], true, styles);
                if (extra !== "padding") {
                    val += jQuery.css(elem, "border" + cssExpand[i] + "Width", true, styles);
                }
            }
        }
        return val;
    }
    function getWidthOrHeight(elem, name, extra) {
        var valueIsBorderBox, styles = getStyles(elem), val = curCSS(elem, name, styles), isBorderBox = jQuery.css(elem, "boxSizing", false, styles) === "border-box";
        if (rnumnonpx.test(val)) {
            return val;
        }
        valueIsBorderBox = isBorderBox && (support.boxSizingReliable() || val === elem.style[name]);
        if (val === "auto") {
            val = elem["offset" + name[0].toUpperCase() + name.slice(1)];
        }
        val = parseFloat(val) || 0;
        return val + augmentWidthOrHeight(elem, name, extra || (isBorderBox ? "border" : "content"), valueIsBorderBox, styles) + "px";
    }
    jQuery.extend({
        cssHooks: {
            opacity: {
                get: function(elem, computed) {
                    if (computed) {
                        var ret = curCSS(elem, "opacity");
                        return ret === "" ? "1" : ret;
                    }
                }
            }
        },
        cssNumber: {
            animationIterationCount: true,
            columnCount: true,
            fillOpacity: true,
            flexGrow: true,
            flexShrink: true,
            fontWeight: true,
            lineHeight: true,
            opacity: true,
            order: true,
            orphans: true,
            widows: true,
            zIndex: true,
            zoom: true
        },
        cssProps: {
            float: "cssFloat"
        },
        style: function(elem, name, value, extra) {
            if (!elem || elem.nodeType === 3 || elem.nodeType === 8 || !elem.style) {
                return;
            }
            var ret, type, hooks, origName = jQuery.camelCase(name), isCustomProp = rcustomProp.test(name), style = elem.style;
            if (!isCustomProp) {
                name = finalPropName(origName);
            }
            hooks = jQuery.cssHooks[name] || jQuery.cssHooks[origName];
            if (value !== undefined) {
                type = typeof value;
                if (type === "string" && (ret = rcssNum.exec(value)) && ret[1]) {
                    value = adjustCSS(elem, name, ret);
                    type = "number";
                }
                if (value == null || value !== value) {
                    return;
                }
                if (type === "number") {
                    value += ret && ret[3] || (jQuery.cssNumber[origName] ? "" : "px");
                }
                if (!support.clearCloneStyle && value === "" && name.indexOf("background") === 0) {
                    style[name] = "inherit";
                }
                if (!hooks || !("set" in hooks) || (value = hooks.set(elem, value, extra)) !== undefined) {
                    if (isCustomProp) {
                        style.setProperty(name, value);
                    } else {
                        style[name] = value;
                    }
                }
            } else {
                if (hooks && "get" in hooks && (ret = hooks.get(elem, false, extra)) !== undefined) {
                    return ret;
                }
                return style[name];
            }
        },
        css: function(elem, name, extra, styles) {
            var val, num, hooks, origName = jQuery.camelCase(name), isCustomProp = rcustomProp.test(name);
            if (!isCustomProp) {
                name = finalPropName(origName);
            }
            hooks = jQuery.cssHooks[name] || jQuery.cssHooks[origName];
            if (hooks && "get" in hooks) {
                val = hooks.get(elem, true, extra);
            }
            if (val === undefined) {
                val = curCSS(elem, name, styles);
            }
            if (val === "normal" && name in cssNormalTransform) {
                val = cssNormalTransform[name];
            }
            if (extra === "" || extra) {
                num = parseFloat(val);
                return extra === true || isFinite(num) ? num || 0 : val;
            }
            return val;
        }
    });
    jQuery.each([ "height", "width" ], function(i, name) {
        jQuery.cssHooks[name] = {
            get: function(elem, computed, extra) {
                if (computed) {
                    return rdisplayswap.test(jQuery.css(elem, "display")) && (!elem.getClientRects().length || !elem.getBoundingClientRect().width) ? swap(elem, cssShow, function() {
                        return getWidthOrHeight(elem, name, extra);
                    }) : getWidthOrHeight(elem, name, extra);
                }
            },
            set: function(elem, value, extra) {
                var matches, styles = extra && getStyles(elem), subtract = extra && augmentWidthOrHeight(elem, name, extra, jQuery.css(elem, "boxSizing", false, styles) === "border-box", styles);
                if (subtract && (matches = rcssNum.exec(value)) && (matches[3] || "px") !== "px") {
                    elem.style[name] = value;
                    value = jQuery.css(elem, name);
                }
                return setPositiveNumber(elem, value, subtract);
            }
        };
    });
    jQuery.cssHooks.marginLeft = addGetHookIf(support.reliableMarginLeft, function(elem, computed) {
        if (computed) {
            return (parseFloat(curCSS(elem, "marginLeft")) || elem.getBoundingClientRect().left - swap(elem, {
                marginLeft: 0
            }, function() {
                return elem.getBoundingClientRect().left;
            })) + "px";
        }
    });
    jQuery.each({
        margin: "",
        padding: "",
        border: "Width"
    }, function(prefix, suffix) {
        jQuery.cssHooks[prefix + suffix] = {
            expand: function(value) {
                var i = 0, expanded = {}, parts = typeof value === "string" ? value.split(" ") : [ value ];
                for (;i < 4; i++) {
                    expanded[prefix + cssExpand[i] + suffix] = parts[i] || parts[i - 2] || parts[0];
                }
                return expanded;
            }
        };
        if (!rmargin.test(prefix)) {
            jQuery.cssHooks[prefix + suffix].set = setPositiveNumber;
        }
    });
    jQuery.fn.extend({
        css: function(name, value) {
            return access(this, function(elem, name, value) {
                var styles, len, map = {}, i = 0;
                if (Array.isArray(name)) {
                    styles = getStyles(elem);
                    len = name.length;
                    for (;i < len; i++) {
                        map[name[i]] = jQuery.css(elem, name[i], false, styles);
                    }
                    return map;
                }
                return value !== undefined ? jQuery.style(elem, name, value) : jQuery.css(elem, name);
            }, name, value, arguments.length > 1);
        }
    });
    function Tween(elem, options, prop, end, easing) {
        return new Tween.prototype.init(elem, options, prop, end, easing);
    }
    jQuery.Tween = Tween;
    Tween.prototype = {
        constructor: Tween,
        init: function(elem, options, prop, end, easing, unit) {
            this.elem = elem;
            this.prop = prop;
            this.easing = easing || jQuery.easing._default;
            this.options = options;
            this.start = this.now = this.cur();
            this.end = end;
            this.unit = unit || (jQuery.cssNumber[prop] ? "" : "px");
        },
        cur: function() {
            var hooks = Tween.propHooks[this.prop];
            return hooks && hooks.get ? hooks.get(this) : Tween.propHooks._default.get(this);
        },
        run: function(percent) {
            var eased, hooks = Tween.propHooks[this.prop];
            if (this.options.duration) {
                this.pos = eased = jQuery.easing[this.easing](percent, this.options.duration * percent, 0, 1, this.options.duration);
            } else {
                this.pos = eased = percent;
            }
            this.now = (this.end - this.start) * eased + this.start;
            if (this.options.step) {
                this.options.step.call(this.elem, this.now, this);
            }
            if (hooks && hooks.set) {
                hooks.set(this);
            } else {
                Tween.propHooks._default.set(this);
            }
            return this;
        }
    };
    Tween.prototype.init.prototype = Tween.prototype;
    Tween.propHooks = {
        _default: {
            get: function(tween) {
                var result;
                if (tween.elem.nodeType !== 1 || tween.elem[tween.prop] != null && tween.elem.style[tween.prop] == null) {
                    return tween.elem[tween.prop];
                }
                result = jQuery.css(tween.elem, tween.prop, "");
                return !result || result === "auto" ? 0 : result;
            },
            set: function(tween) {
                if (jQuery.fx.step[tween.prop]) {
                    jQuery.fx.step[tween.prop](tween);
                } else if (tween.elem.nodeType === 1 && (tween.elem.style[jQuery.cssProps[tween.prop]] != null || jQuery.cssHooks[tween.prop])) {
                    jQuery.style(tween.elem, tween.prop, tween.now + tween.unit);
                } else {
                    tween.elem[tween.prop] = tween.now;
                }
            }
        }
    };
    Tween.propHooks.scrollTop = Tween.propHooks.scrollLeft = {
        set: function(tween) {
            if (tween.elem.nodeType && tween.elem.parentNode) {
                tween.elem[tween.prop] = tween.now;
            }
        }
    };
    jQuery.easing = {
        linear: function(p) {
            return p;
        },
        swing: function(p) {
            return .5 - Math.cos(p * Math.PI) / 2;
        },
        _default: "swing"
    };
    jQuery.fx = Tween.prototype.init;
    jQuery.fx.step = {};
    var fxNow, inProgress, rfxtypes = /^(?:toggle|show|hide)$/, rrun = /queueHooks$/;
    function schedule() {
        if (inProgress) {
            if (document.hidden === false && window.requestAnimationFrame) {
                window.requestAnimationFrame(schedule);
            } else {
                window.setTimeout(schedule, jQuery.fx.interval);
            }
            jQuery.fx.tick();
        }
    }
    function createFxNow() {
        window.setTimeout(function() {
            fxNow = undefined;
        });
        return fxNow = jQuery.now();
    }
    function genFx(type, includeWidth) {
        var which, i = 0, attrs = {
            height: type
        };
        includeWidth = includeWidth ? 1 : 0;
        for (;i < 4; i += 2 - includeWidth) {
            which = cssExpand[i];
            attrs["margin" + which] = attrs["padding" + which] = type;
        }
        if (includeWidth) {
            attrs.opacity = attrs.width = type;
        }
        return attrs;
    }
    function createTween(value, prop, animation) {
        var tween, collection = (Animation.tweeners[prop] || []).concat(Animation.tweeners["*"]), index = 0, length = collection.length;
        for (;index < length; index++) {
            if (tween = collection[index].call(animation, prop, value)) {
                return tween;
            }
        }
    }
    function defaultPrefilter(elem, props, opts) {
        var prop, value, toggle, hooks, oldfire, propTween, restoreDisplay, display, isBox = "width" in props || "height" in props, anim = this, orig = {}, style = elem.style, hidden = elem.nodeType && isHiddenWithinTree(elem), dataShow = dataPriv.get(elem, "fxshow");
        if (!opts.queue) {
            hooks = jQuery._queueHooks(elem, "fx");
            if (hooks.unqueued == null) {
                hooks.unqueued = 0;
                oldfire = hooks.empty.fire;
                hooks.empty.fire = function() {
                    if (!hooks.unqueued) {
                        oldfire();
                    }
                };
            }
            hooks.unqueued++;
            anim.always(function() {
                anim.always(function() {
                    hooks.unqueued--;
                    if (!jQuery.queue(elem, "fx").length) {
                        hooks.empty.fire();
                    }
                });
            });
        }
        for (prop in props) {
            value = props[prop];
            if (rfxtypes.test(value)) {
                delete props[prop];
                toggle = toggle || value === "toggle";
                if (value === (hidden ? "hide" : "show")) {
                    if (value === "show" && dataShow && dataShow[prop] !== undefined) {
                        hidden = true;
                    } else {
                        continue;
                    }
                }
                orig[prop] = dataShow && dataShow[prop] || jQuery.style(elem, prop);
            }
        }
        propTween = !jQuery.isEmptyObject(props);
        if (!propTween && jQuery.isEmptyObject(orig)) {
            return;
        }
        if (isBox && elem.nodeType === 1) {
            opts.overflow = [ style.overflow, style.overflowX, style.overflowY ];
            restoreDisplay = dataShow && dataShow.display;
            if (restoreDisplay == null) {
                restoreDisplay = dataPriv.get(elem, "display");
            }
            display = jQuery.css(elem, "display");
            if (display === "none") {
                if (restoreDisplay) {
                    display = restoreDisplay;
                } else {
                    showHide([ elem ], true);
                    restoreDisplay = elem.style.display || restoreDisplay;
                    display = jQuery.css(elem, "display");
                    showHide([ elem ]);
                }
            }
            if (display === "inline" || display === "inline-block" && restoreDisplay != null) {
                if (jQuery.css(elem, "float") === "none") {
                    if (!propTween) {
                        anim.done(function() {
                            style.display = restoreDisplay;
                        });
                        if (restoreDisplay == null) {
                            display = style.display;
                            restoreDisplay = display === "none" ? "" : display;
                        }
                    }
                    style.display = "inline-block";
                }
            }
        }
        if (opts.overflow) {
            style.overflow = "hidden";
            anim.always(function() {
                style.overflow = opts.overflow[0];
                style.overflowX = opts.overflow[1];
                style.overflowY = opts.overflow[2];
            });
        }
        propTween = false;
        for (prop in orig) {
            if (!propTween) {
                if (dataShow) {
                    if ("hidden" in dataShow) {
                        hidden = dataShow.hidden;
                    }
                } else {
                    dataShow = dataPriv.access(elem, "fxshow", {
                        display: restoreDisplay
                    });
                }
                if (toggle) {
                    dataShow.hidden = !hidden;
                }
                if (hidden) {
                    showHide([ elem ], true);
                }
                anim.done(function() {
                    if (!hidden) {
                        showHide([ elem ]);
                    }
                    dataPriv.remove(elem, "fxshow");
                    for (prop in orig) {
                        jQuery.style(elem, prop, orig[prop]);
                    }
                });
            }
            propTween = createTween(hidden ? dataShow[prop] : 0, prop, anim);
            if (!(prop in dataShow)) {
                dataShow[prop] = propTween.start;
                if (hidden) {
                    propTween.end = propTween.start;
                    propTween.start = 0;
                }
            }
        }
    }
    function propFilter(props, specialEasing) {
        var index, name, easing, value, hooks;
        for (index in props) {
            name = jQuery.camelCase(index);
            easing = specialEasing[name];
            value = props[index];
            if (Array.isArray(value)) {
                easing = value[1];
                value = props[index] = value[0];
            }
            if (index !== name) {
                props[name] = value;
                delete props[index];
            }
            hooks = jQuery.cssHooks[name];
            if (hooks && "expand" in hooks) {
                value = hooks.expand(value);
                delete props[name];
                for (index in value) {
                    if (!(index in props)) {
                        props[index] = value[index];
                        specialEasing[index] = easing;
                    }
                }
            } else {
                specialEasing[name] = easing;
            }
        }
    }
    function Animation(elem, properties, options) {
        var result, stopped, index = 0, length = Animation.prefilters.length, deferred = jQuery.Deferred().always(function() {
            delete tick.elem;
        }), tick = function() {
            if (stopped) {
                return false;
            }
            var currentTime = fxNow || createFxNow(), remaining = Math.max(0, animation.startTime + animation.duration - currentTime), temp = remaining / animation.duration || 0, percent = 1 - temp, index = 0, length = animation.tweens.length;
            for (;index < length; index++) {
                animation.tweens[index].run(percent);
            }
            deferred.notifyWith(elem, [ animation, percent, remaining ]);
            if (percent < 1 && length) {
                return remaining;
            }
            if (!length) {
                deferred.notifyWith(elem, [ animation, 1, 0 ]);
            }
            deferred.resolveWith(elem, [ animation ]);
            return false;
        }, animation = deferred.promise({
            elem: elem,
            props: jQuery.extend({}, properties),
            opts: jQuery.extend(true, {
                specialEasing: {},
                easing: jQuery.easing._default
            }, options),
            originalProperties: properties,
            originalOptions: options,
            startTime: fxNow || createFxNow(),
            duration: options.duration,
            tweens: [],
            createTween: function(prop, end) {
                var tween = jQuery.Tween(elem, animation.opts, prop, end, animation.opts.specialEasing[prop] || animation.opts.easing);
                animation.tweens.push(tween);
                return tween;
            },
            stop: function(gotoEnd) {
                var index = 0, length = gotoEnd ? animation.tweens.length : 0;
                if (stopped) {
                    return this;
                }
                stopped = true;
                for (;index < length; index++) {
                    animation.tweens[index].run(1);
                }
                if (gotoEnd) {
                    deferred.notifyWith(elem, [ animation, 1, 0 ]);
                    deferred.resolveWith(elem, [ animation, gotoEnd ]);
                } else {
                    deferred.rejectWith(elem, [ animation, gotoEnd ]);
                }
                return this;
            }
        }), props = animation.props;
        propFilter(props, animation.opts.specialEasing);
        for (;index < length; index++) {
            result = Animation.prefilters[index].call(animation, elem, props, animation.opts);
            if (result) {
                if (jQuery.isFunction(result.stop)) {
                    jQuery._queueHooks(animation.elem, animation.opts.queue).stop = jQuery.proxy(result.stop, result);
                }
                return result;
            }
        }
        jQuery.map(props, createTween, animation);
        if (jQuery.isFunction(animation.opts.start)) {
            animation.opts.start.call(elem, animation);
        }
        animation.progress(animation.opts.progress).done(animation.opts.done, animation.opts.complete).fail(animation.opts.fail).always(animation.opts.always);
        jQuery.fx.timer(jQuery.extend(tick, {
            elem: elem,
            anim: animation,
            queue: animation.opts.queue
        }));
        return animation;
    }
    jQuery.Animation = jQuery.extend(Animation, {
        tweeners: {
            "*": [ function(prop, value) {
                var tween = this.createTween(prop, value);
                adjustCSS(tween.elem, prop, rcssNum.exec(value), tween);
                return tween;
            } ]
        },
        tweener: function(props, callback) {
            if (jQuery.isFunction(props)) {
                callback = props;
                props = [ "*" ];
            } else {
                props = props.match(rnothtmlwhite);
            }
            var prop, index = 0, length = props.length;
            for (;index < length; index++) {
                prop = props[index];
                Animation.tweeners[prop] = Animation.tweeners[prop] || [];
                Animation.tweeners[prop].unshift(callback);
            }
        },
        prefilters: [ defaultPrefilter ],
        prefilter: function(callback, prepend) {
            if (prepend) {
                Animation.prefilters.unshift(callback);
            } else {
                Animation.prefilters.push(callback);
            }
        }
    });
    jQuery.speed = function(speed, easing, fn) {
        var opt = speed && typeof speed === "object" ? jQuery.extend({}, speed) : {
            complete: fn || !fn && easing || jQuery.isFunction(speed) && speed,
            duration: speed,
            easing: fn && easing || easing && !jQuery.isFunction(easing) && easing
        };
        if (jQuery.fx.off) {
            opt.duration = 0;
        } else {
            if (typeof opt.duration !== "number") {
                if (opt.duration in jQuery.fx.speeds) {
                    opt.duration = jQuery.fx.speeds[opt.duration];
                } else {
                    opt.duration = jQuery.fx.speeds._default;
                }
            }
        }
        if (opt.queue == null || opt.queue === true) {
            opt.queue = "fx";
        }
        opt.old = opt.complete;
        opt.complete = function() {
            if (jQuery.isFunction(opt.old)) {
                opt.old.call(this);
            }
            if (opt.queue) {
                jQuery.dequeue(this, opt.queue);
            }
        };
        return opt;
    };
    jQuery.fn.extend({
        fadeTo: function(speed, to, easing, callback) {
            return this.filter(isHiddenWithinTree).css("opacity", 0).show().end().animate({
                opacity: to
            }, speed, easing, callback);
        },
        animate: function(prop, speed, easing, callback) {
            var empty = jQuery.isEmptyObject(prop), optall = jQuery.speed(speed, easing, callback), doAnimation = function() {
                var anim = Animation(this, jQuery.extend({}, prop), optall);
                if (empty || dataPriv.get(this, "finish")) {
                    anim.stop(true);
                }
            };
            doAnimation.finish = doAnimation;
            return empty || optall.queue === false ? this.each(doAnimation) : this.queue(optall.queue, doAnimation);
        },
        stop: function(type, clearQueue, gotoEnd) {
            var stopQueue = function(hooks) {
                var stop = hooks.stop;
                delete hooks.stop;
                stop(gotoEnd);
            };
            if (typeof type !== "string") {
                gotoEnd = clearQueue;
                clearQueue = type;
                type = undefined;
            }
            if (clearQueue && type !== false) {
                this.queue(type || "fx", []);
            }
            return this.each(function() {
                var dequeue = true, index = type != null && type + "queueHooks", timers = jQuery.timers, data = dataPriv.get(this);
                if (index) {
                    if (data[index] && data[index].stop) {
                        stopQueue(data[index]);
                    }
                } else {
                    for (index in data) {
                        if (data[index] && data[index].stop && rrun.test(index)) {
                            stopQueue(data[index]);
                        }
                    }
                }
                for (index = timers.length; index--; ) {
                    if (timers[index].elem === this && (type == null || timers[index].queue === type)) {
                        timers[index].anim.stop(gotoEnd);
                        dequeue = false;
                        timers.splice(index, 1);
                    }
                }
                if (dequeue || !gotoEnd) {
                    jQuery.dequeue(this, type);
                }
            });
        },
        finish: function(type) {
            if (type !== false) {
                type = type || "fx";
            }
            return this.each(function() {
                var index, data = dataPriv.get(this), queue = data[type + "queue"], hooks = data[type + "queueHooks"], timers = jQuery.timers, length = queue ? queue.length : 0;
                data.finish = true;
                jQuery.queue(this, type, []);
                if (hooks && hooks.stop) {
                    hooks.stop.call(this, true);
                }
                for (index = timers.length; index--; ) {
                    if (timers[index].elem === this && timers[index].queue === type) {
                        timers[index].anim.stop(true);
                        timers.splice(index, 1);
                    }
                }
                for (index = 0; index < length; index++) {
                    if (queue[index] && queue[index].finish) {
                        queue[index].finish.call(this);
                    }
                }
                delete data.finish;
            });
        }
    });
    jQuery.each([ "toggle", "show", "hide" ], function(i, name) {
        var cssFn = jQuery.fn[name];
        jQuery.fn[name] = function(speed, easing, callback) {
            return speed == null || typeof speed === "boolean" ? cssFn.apply(this, arguments) : this.animate(genFx(name, true), speed, easing, callback);
        };
    });
    jQuery.each({
        slideDown: genFx("show"),
        slideUp: genFx("hide"),
        slideToggle: genFx("toggle"),
        fadeIn: {
            opacity: "show"
        },
        fadeOut: {
            opacity: "hide"
        },
        fadeToggle: {
            opacity: "toggle"
        }
    }, function(name, props) {
        jQuery.fn[name] = function(speed, easing, callback) {
            return this.animate(props, speed, easing, callback);
        };
    });
    jQuery.timers = [];
    jQuery.fx.tick = function() {
        var timer, i = 0, timers = jQuery.timers;
        fxNow = jQuery.now();
        for (;i < timers.length; i++) {
            timer = timers[i];
            if (!timer() && timers[i] === timer) {
                timers.splice(i--, 1);
            }
        }
        if (!timers.length) {
            jQuery.fx.stop();
        }
        fxNow = undefined;
    };
    jQuery.fx.timer = function(timer) {
        jQuery.timers.push(timer);
        jQuery.fx.start();
    };
    jQuery.fx.interval = 13;
    jQuery.fx.start = function() {
        if (inProgress) {
            return;
        }
        inProgress = true;
        schedule();
    };
    jQuery.fx.stop = function() {
        inProgress = null;
    };
    jQuery.fx.speeds = {
        slow: 600,
        fast: 200,
        _default: 400
    };
    jQuery.fn.delay = function(time, type) {
        time = jQuery.fx ? jQuery.fx.speeds[time] || time : time;
        type = type || "fx";
        return this.queue(type, function(next, hooks) {
            var timeout = window.setTimeout(next, time);
            hooks.stop = function() {
                window.clearTimeout(timeout);
            };
        });
    };
    (function() {
        var input = document.createElement("input"), select = document.createElement("select"), opt = select.appendChild(document.createElement("option"));
        input.type = "checkbox";
        support.checkOn = input.value !== "";
        support.optSelected = opt.selected;
        input = document.createElement("input");
        input.value = "t";
        input.type = "radio";
        support.radioValue = input.value === "t";
    })();
    var boolHook, attrHandle = jQuery.expr.attrHandle;
    jQuery.fn.extend({
        attr: function(name, value) {
            return access(this, jQuery.attr, name, value, arguments.length > 1);
        },
        removeAttr: function(name) {
            return this.each(function() {
                jQuery.removeAttr(this, name);
            });
        }
    });
    jQuery.extend({
        attr: function(elem, name, value) {
            var ret, hooks, nType = elem.nodeType;
            if (nType === 3 || nType === 8 || nType === 2) {
                return;
            }
            if (typeof elem.getAttribute === "undefined") {
                return jQuery.prop(elem, name, value);
            }
            if (nType !== 1 || !jQuery.isXMLDoc(elem)) {
                hooks = jQuery.attrHooks[name.toLowerCase()] || (jQuery.expr.match.bool.test(name) ? boolHook : undefined);
            }
            if (value !== undefined) {
                if (value === null) {
                    jQuery.removeAttr(elem, name);
                    return;
                }
                if (hooks && "set" in hooks && (ret = hooks.set(elem, value, name)) !== undefined) {
                    return ret;
                }
                elem.setAttribute(name, value + "");
                return value;
            }
            if (hooks && "get" in hooks && (ret = hooks.get(elem, name)) !== null) {
                return ret;
            }
            ret = jQuery.find.attr(elem, name);
            return ret == null ? undefined : ret;
        },
        attrHooks: {
            type: {
                set: function(elem, value) {
                    if (!support.radioValue && value === "radio" && nodeName(elem, "input")) {
                        var val = elem.value;
                        elem.setAttribute("type", value);
                        if (val) {
                            elem.value = val;
                        }
                        return value;
                    }
                }
            }
        },
        removeAttr: function(elem, value) {
            var name, i = 0, attrNames = value && value.match(rnothtmlwhite);
            if (attrNames && elem.nodeType === 1) {
                while (name = attrNames[i++]) {
                    elem.removeAttribute(name);
                }
            }
        }
    });
    boolHook = {
        set: function(elem, value, name) {
            if (value === false) {
                jQuery.removeAttr(elem, name);
            } else {
                elem.setAttribute(name, name);
            }
            return name;
        }
    };
    jQuery.each(jQuery.expr.match.bool.source.match(/\w+/g), function(i, name) {
        var getter = attrHandle[name] || jQuery.find.attr;
        attrHandle[name] = function(elem, name, isXML) {
            var ret, handle, lowercaseName = name.toLowerCase();
            if (!isXML) {
                handle = attrHandle[lowercaseName];
                attrHandle[lowercaseName] = ret;
                ret = getter(elem, name, isXML) != null ? lowercaseName : null;
                attrHandle[lowercaseName] = handle;
            }
            return ret;
        };
    });
    var rfocusable = /^(?:input|select|textarea|button)$/i, rclickable = /^(?:a|area)$/i;
    jQuery.fn.extend({
        prop: function(name, value) {
            return access(this, jQuery.prop, name, value, arguments.length > 1);
        },
        removeProp: function(name) {
            return this.each(function() {
                delete this[jQuery.propFix[name] || name];
            });
        }
    });
    jQuery.extend({
        prop: function(elem, name, value) {
            var ret, hooks, nType = elem.nodeType;
            if (nType === 3 || nType === 8 || nType === 2) {
                return;
            }
            if (nType !== 1 || !jQuery.isXMLDoc(elem)) {
                name = jQuery.propFix[name] || name;
                hooks = jQuery.propHooks[name];
            }
            if (value !== undefined) {
                if (hooks && "set" in hooks && (ret = hooks.set(elem, value, name)) !== undefined) {
                    return ret;
                }
                return elem[name] = value;
            }
            if (hooks && "get" in hooks && (ret = hooks.get(elem, name)) !== null) {
                return ret;
            }
            return elem[name];
        },
        propHooks: {
            tabIndex: {
                get: function(elem) {
                    var tabindex = jQuery.find.attr(elem, "tabindex");
                    if (tabindex) {
                        return parseInt(tabindex, 10);
                    }
                    if (rfocusable.test(elem.nodeName) || rclickable.test(elem.nodeName) && elem.href) {
                        return 0;
                    }
                    return -1;
                }
            }
        },
        propFix: {
            for: "htmlFor",
            class: "className"
        }
    });
    if (!support.optSelected) {
        jQuery.propHooks.selected = {
            get: function(elem) {
                var parent = elem.parentNode;
                if (parent && parent.parentNode) {
                    parent.parentNode.selectedIndex;
                }
                return null;
            },
            set: function(elem) {
                var parent = elem.parentNode;
                if (parent) {
                    parent.selectedIndex;
                    if (parent.parentNode) {
                        parent.parentNode.selectedIndex;
                    }
                }
            }
        };
    }
    jQuery.each([ "tabIndex", "readOnly", "maxLength", "cellSpacing", "cellPadding", "rowSpan", "colSpan", "useMap", "frameBorder", "contentEditable" ], function() {
        jQuery.propFix[this.toLowerCase()] = this;
    });
    function stripAndCollapse(value) {
        var tokens = value.match(rnothtmlwhite) || [];
        return tokens.join(" ");
    }
    function getClass(elem) {
        return elem.getAttribute && elem.getAttribute("class") || "";
    }
    jQuery.fn.extend({
        addClass: function(value) {
            var classes, elem, cur, curValue, clazz, j, finalValue, i = 0;
            if (jQuery.isFunction(value)) {
                return this.each(function(j) {
                    jQuery(this).addClass(value.call(this, j, getClass(this)));
                });
            }
            if (typeof value === "string" && value) {
                classes = value.match(rnothtmlwhite) || [];
                while (elem = this[i++]) {
                    curValue = getClass(elem);
                    cur = elem.nodeType === 1 && " " + stripAndCollapse(curValue) + " ";
                    if (cur) {
                        j = 0;
                        while (clazz = classes[j++]) {
                            if (cur.indexOf(" " + clazz + " ") < 0) {
                                cur += clazz + " ";
                            }
                        }
                        finalValue = stripAndCollapse(cur);
                        if (curValue !== finalValue) {
                            elem.setAttribute("class", finalValue);
                        }
                    }
                }
            }
            return this;
        },
        removeClass: function(value) {
            var classes, elem, cur, curValue, clazz, j, finalValue, i = 0;
            if (jQuery.isFunction(value)) {
                return this.each(function(j) {
                    jQuery(this).removeClass(value.call(this, j, getClass(this)));
                });
            }
            if (!arguments.length) {
                return this.attr("class", "");
            }
            if (typeof value === "string" && value) {
                classes = value.match(rnothtmlwhite) || [];
                while (elem = this[i++]) {
                    curValue = getClass(elem);
                    cur = elem.nodeType === 1 && " " + stripAndCollapse(curValue) + " ";
                    if (cur) {
                        j = 0;
                        while (clazz = classes[j++]) {
                            while (cur.indexOf(" " + clazz + " ") > -1) {
                                cur = cur.replace(" " + clazz + " ", " ");
                            }
                        }
                        finalValue = stripAndCollapse(cur);
                        if (curValue !== finalValue) {
                            elem.setAttribute("class", finalValue);
                        }
                    }
                }
            }
            return this;
        },
        toggleClass: function(value, stateVal) {
            var type = typeof value;
            if (typeof stateVal === "boolean" && type === "string") {
                return stateVal ? this.addClass(value) : this.removeClass(value);
            }
            if (jQuery.isFunction(value)) {
                return this.each(function(i) {
                    jQuery(this).toggleClass(value.call(this, i, getClass(this), stateVal), stateVal);
                });
            }
            return this.each(function() {
                var className, i, self, classNames;
                if (type === "string") {
                    i = 0;
                    self = jQuery(this);
                    classNames = value.match(rnothtmlwhite) || [];
                    while (className = classNames[i++]) {
                        if (self.hasClass(className)) {
                            self.removeClass(className);
                        } else {
                            self.addClass(className);
                        }
                    }
                } else if (value === undefined || type === "boolean") {
                    className = getClass(this);
                    if (className) {
                        dataPriv.set(this, "__className__", className);
                    }
                    if (this.setAttribute) {
                        this.setAttribute("class", className || value === false ? "" : dataPriv.get(this, "__className__") || "");
                    }
                }
            });
        },
        hasClass: function(selector) {
            var className, elem, i = 0;
            className = " " + selector + " ";
            while (elem = this[i++]) {
                if (elem.nodeType === 1 && (" " + stripAndCollapse(getClass(elem)) + " ").indexOf(className) > -1) {
                    return true;
                }
            }
            return false;
        }
    });
    var rreturn = /\r/g;
    jQuery.fn.extend({
        val: function(value) {
            var hooks, ret, isFunction, elem = this[0];
            if (!arguments.length) {
                if (elem) {
                    hooks = jQuery.valHooks[elem.type] || jQuery.valHooks[elem.nodeName.toLowerCase()];
                    if (hooks && "get" in hooks && (ret = hooks.get(elem, "value")) !== undefined) {
                        return ret;
                    }
                    ret = elem.value;
                    if (typeof ret === "string") {
                        return ret.replace(rreturn, "");
                    }
                    return ret == null ? "" : ret;
                }
                return;
            }
            isFunction = jQuery.isFunction(value);
            return this.each(function(i) {
                var val;
                if (this.nodeType !== 1) {
                    return;
                }
                if (isFunction) {
                    val = value.call(this, i, jQuery(this).val());
                } else {
                    val = value;
                }
                if (val == null) {
                    val = "";
                } else if (typeof val === "number") {
                    val += "";
                } else if (Array.isArray(val)) {
                    val = jQuery.map(val, function(value) {
                        return value == null ? "" : value + "";
                    });
                }
                hooks = jQuery.valHooks[this.type] || jQuery.valHooks[this.nodeName.toLowerCase()];
                if (!hooks || !("set" in hooks) || hooks.set(this, val, "value") === undefined) {
                    this.value = val;
                }
            });
        }
    });
    jQuery.extend({
        valHooks: {
            option: {
                get: function(elem) {
                    var val = jQuery.find.attr(elem, "value");
                    return val != null ? val : stripAndCollapse(jQuery.text(elem));
                }
            },
            select: {
                get: function(elem) {
                    var value, option, i, options = elem.options, index = elem.selectedIndex, one = elem.type === "select-one", values = one ? null : [], max = one ? index + 1 : options.length;
                    if (index < 0) {
                        i = max;
                    } else {
                        i = one ? index : 0;
                    }
                    for (;i < max; i++) {
                        option = options[i];
                        if ((option.selected || i === index) && !option.disabled && (!option.parentNode.disabled || !nodeName(option.parentNode, "optgroup"))) {
                            value = jQuery(option).val();
                            if (one) {
                                return value;
                            }
                            values.push(value);
                        }
                    }
                    return values;
                },
                set: function(elem, value) {
                    var optionSet, option, options = elem.options, values = jQuery.makeArray(value), i = options.length;
                    while (i--) {
                        option = options[i];
                        if (option.selected = jQuery.inArray(jQuery.valHooks.option.get(option), values) > -1) {
                            optionSet = true;
                        }
                    }
                    if (!optionSet) {
                        elem.selectedIndex = -1;
                    }
                    return values;
                }
            }
        }
    });
    jQuery.each([ "radio", "checkbox" ], function() {
        jQuery.valHooks[this] = {
            set: function(elem, value) {
                if (Array.isArray(value)) {
                    return elem.checked = jQuery.inArray(jQuery(elem).val(), value) > -1;
                }
            }
        };
        if (!support.checkOn) {
            jQuery.valHooks[this].get = function(elem) {
                return elem.getAttribute("value") === null ? "on" : elem.value;
            };
        }
    });
    var rfocusMorph = /^(?:focusinfocus|focusoutblur)$/;
    jQuery.extend(jQuery.event, {
        trigger: function(event, data, elem, onlyHandlers) {
            var i, cur, tmp, bubbleType, ontype, handle, special, eventPath = [ elem || document ], type = hasOwn.call(event, "type") ? event.type : event, namespaces = hasOwn.call(event, "namespace") ? event.namespace.split(".") : [];
            cur = tmp = elem = elem || document;
            if (elem.nodeType === 3 || elem.nodeType === 8) {
                return;
            }
            if (rfocusMorph.test(type + jQuery.event.triggered)) {
                return;
            }
            if (type.indexOf(".") > -1) {
                namespaces = type.split(".");
                type = namespaces.shift();
                namespaces.sort();
            }
            ontype = type.indexOf(":") < 0 && "on" + type;
            event = event[jQuery.expando] ? event : new jQuery.Event(type, typeof event === "object" && event);
            event.isTrigger = onlyHandlers ? 2 : 3;
            event.namespace = namespaces.join(".");
            event.rnamespace = event.namespace ? new RegExp("(^|\\.)" + namespaces.join("\\.(?:.*\\.|)") + "(\\.|$)") : null;
            event.result = undefined;
            if (!event.target) {
                event.target = elem;
            }
            data = data == null ? [ event ] : jQuery.makeArray(data, [ event ]);
            special = jQuery.event.special[type] || {};
            if (!onlyHandlers && special.trigger && special.trigger.apply(elem, data) === false) {
                return;
            }
            if (!onlyHandlers && !special.noBubble && !jQuery.isWindow(elem)) {
                bubbleType = special.delegateType || type;
                if (!rfocusMorph.test(bubbleType + type)) {
                    cur = cur.parentNode;
                }
                for (;cur; cur = cur.parentNode) {
                    eventPath.push(cur);
                    tmp = cur;
                }
                if (tmp === (elem.ownerDocument || document)) {
                    eventPath.push(tmp.defaultView || tmp.parentWindow || window);
                }
            }
            i = 0;
            while ((cur = eventPath[i++]) && !event.isPropagationStopped()) {
                event.type = i > 1 ? bubbleType : special.bindType || type;
                handle = (dataPriv.get(cur, "events") || {})[event.type] && dataPriv.get(cur, "handle");
                if (handle) {
                    handle.apply(cur, data);
                }
                handle = ontype && cur[ontype];
                if (handle && handle.apply && acceptData(cur)) {
                    event.result = handle.apply(cur, data);
                    if (event.result === false) {
                        event.preventDefault();
                    }
                }
            }
            event.type = type;
            if (!onlyHandlers && !event.isDefaultPrevented()) {
                if ((!special._default || special._default.apply(eventPath.pop(), data) === false) && acceptData(elem)) {
                    if (ontype && jQuery.isFunction(elem[type]) && !jQuery.isWindow(elem)) {
                        tmp = elem[ontype];
                        if (tmp) {
                            elem[ontype] = null;
                        }
                        jQuery.event.triggered = type;
                        elem[type]();
                        jQuery.event.triggered = undefined;
                        if (tmp) {
                            elem[ontype] = tmp;
                        }
                    }
                }
            }
            return event.result;
        },
        simulate: function(type, elem, event) {
            var e = jQuery.extend(new jQuery.Event(), event, {
                type: type,
                isSimulated: true
            });
            jQuery.event.trigger(e, null, elem);
        }
    });
    jQuery.fn.extend({
        trigger: function(type, data) {
            return this.each(function() {
                jQuery.event.trigger(type, data, this);
            });
        },
        triggerHandler: function(type, data) {
            var elem = this[0];
            if (elem) {
                return jQuery.event.trigger(type, data, elem, true);
            }
        }
    });
    jQuery.each(("blur focus focusin focusout resize scroll click dblclick " + "mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave " + "change select submit keydown keypress keyup contextmenu").split(" "), function(i, name) {
        jQuery.fn[name] = function(data, fn) {
            return arguments.length > 0 ? this.on(name, null, data, fn) : this.trigger(name);
        };
    });
    jQuery.fn.extend({
        hover: function(fnOver, fnOut) {
            return this.mouseenter(fnOver).mouseleave(fnOut || fnOver);
        }
    });
    support.focusin = "onfocusin" in window;
    if (!support.focusin) {
        jQuery.each({
            focus: "focusin",
            blur: "focusout"
        }, function(orig, fix) {
            var handler = function(event) {
                jQuery.event.simulate(fix, event.target, jQuery.event.fix(event));
            };
            jQuery.event.special[fix] = {
                setup: function() {
                    var doc = this.ownerDocument || this, attaches = dataPriv.access(doc, fix);
                    if (!attaches) {
                        doc.addEventListener(orig, handler, true);
                    }
                    dataPriv.access(doc, fix, (attaches || 0) + 1);
                },
                teardown: function() {
                    var doc = this.ownerDocument || this, attaches = dataPriv.access(doc, fix) - 1;
                    if (!attaches) {
                        doc.removeEventListener(orig, handler, true);
                        dataPriv.remove(doc, fix);
                    } else {
                        dataPriv.access(doc, fix, attaches);
                    }
                }
            };
        });
    }
    var location = window.location;
    var nonce = jQuery.now();
    var rquery = /\?/;
    jQuery.parseXML = function(data) {
        var xml;
        if (!data || typeof data !== "string") {
            return null;
        }
        try {
            xml = new window.DOMParser().parseFromString(data, "text/xml");
        } catch (e) {
            xml = undefined;
        }
        if (!xml || xml.getElementsByTagName("parsererror").length) {
            jQuery.error("Invalid XML: " + data);
        }
        return xml;
    };
    var rbracket = /\[\]$/, rCRLF = /\r?\n/g, rsubmitterTypes = /^(?:submit|button|image|reset|file)$/i, rsubmittable = /^(?:input|select|textarea|keygen)/i;
    function buildParams(prefix, obj, traditional, add) {
        var name;
        if (Array.isArray(obj)) {
            jQuery.each(obj, function(i, v) {
                if (traditional || rbracket.test(prefix)) {
                    add(prefix, v);
                } else {
                    buildParams(prefix + "[" + (typeof v === "object" && v != null ? i : "") + "]", v, traditional, add);
                }
            });
        } else if (!traditional && jQuery.type(obj) === "object") {
            for (name in obj) {
                buildParams(prefix + "[" + name + "]", obj[name], traditional, add);
            }
        } else {
            add(prefix, obj);
        }
    }
    jQuery.param = function(a, traditional) {
        var prefix, s = [], add = function(key, valueOrFunction) {
            var value = jQuery.isFunction(valueOrFunction) ? valueOrFunction() : valueOrFunction;
            s[s.length] = encodeURIComponent(key) + "=" + encodeURIComponent(value == null ? "" : value);
        };
        if (Array.isArray(a) || a.jquery && !jQuery.isPlainObject(a)) {
            jQuery.each(a, function() {
                add(this.name, this.value);
            });
        } else {
            for (prefix in a) {
                buildParams(prefix, a[prefix], traditional, add);
            }
        }
        return s.join("&");
    };
    jQuery.fn.extend({
        serialize: function() {
            return jQuery.param(this.serializeArray());
        },
        serializeArray: function() {
            return this.map(function() {
                var elements = jQuery.prop(this, "elements");
                return elements ? jQuery.makeArray(elements) : this;
            }).filter(function() {
                var type = this.type;
                return this.name && !jQuery(this).is(":disabled") && rsubmittable.test(this.nodeName) && !rsubmitterTypes.test(type) && (this.checked || !rcheckableType.test(type));
            }).map(function(i, elem) {
                var val = jQuery(this).val();
                if (val == null) {
                    return null;
                }
                if (Array.isArray(val)) {
                    return jQuery.map(val, function(val) {
                        return {
                            name: elem.name,
                            value: val.replace(rCRLF, "\r\n")
                        };
                    });
                }
                return {
                    name: elem.name,
                    value: val.replace(rCRLF, "\r\n")
                };
            }).get();
        }
    });
    var r20 = /%20/g, rhash = /#.*$/, rantiCache = /([?&])_=[^&]*/, rheaders = /^(.*?):[ \t]*([^\r\n]*)$/gm, rlocalProtocol = /^(?:about|app|app-storage|.+-extension|file|res|widget):$/, rnoContent = /^(?:GET|HEAD)$/, rprotocol = /^\/\//, prefilters = {}, transports = {}, allTypes = "*/".concat("*"), originAnchor = document.createElement("a");
    originAnchor.href = location.href;
    function addToPrefiltersOrTransports(structure) {
        return function(dataTypeExpression, func) {
            if (typeof dataTypeExpression !== "string") {
                func = dataTypeExpression;
                dataTypeExpression = "*";
            }
            var dataType, i = 0, dataTypes = dataTypeExpression.toLowerCase().match(rnothtmlwhite) || [];
            if (jQuery.isFunction(func)) {
                while (dataType = dataTypes[i++]) {
                    if (dataType[0] === "+") {
                        dataType = dataType.slice(1) || "*";
                        (structure[dataType] = structure[dataType] || []).unshift(func);
                    } else {
                        (structure[dataType] = structure[dataType] || []).push(func);
                    }
                }
            }
        };
    }
    function inspectPrefiltersOrTransports(structure, options, originalOptions, jqXHR) {
        var inspected = {}, seekingTransport = structure === transports;
        function inspect(dataType) {
            var selected;
            inspected[dataType] = true;
            jQuery.each(structure[dataType] || [], function(_, prefilterOrFactory) {
                var dataTypeOrTransport = prefilterOrFactory(options, originalOptions, jqXHR);
                if (typeof dataTypeOrTransport === "string" && !seekingTransport && !inspected[dataTypeOrTransport]) {
                    options.dataTypes.unshift(dataTypeOrTransport);
                    inspect(dataTypeOrTransport);
                    return false;
                } else if (seekingTransport) {
                    return !(selected = dataTypeOrTransport);
                }
            });
            return selected;
        }
        return inspect(options.dataTypes[0]) || !inspected["*"] && inspect("*");
    }
    function ajaxExtend(target, src) {
        var key, deep, flatOptions = jQuery.ajaxSettings.flatOptions || {};
        for (key in src) {
            if (src[key] !== undefined) {
                (flatOptions[key] ? target : deep || (deep = {}))[key] = src[key];
            }
        }
        if (deep) {
            jQuery.extend(true, target, deep);
        }
        return target;
    }
    function ajaxHandleResponses(s, jqXHR, responses) {
        var ct, type, finalDataType, firstDataType, contents = s.contents, dataTypes = s.dataTypes;
        while (dataTypes[0] === "*") {
            dataTypes.shift();
            if (ct === undefined) {
                ct = s.mimeType || jqXHR.getResponseHeader("Content-Type");
            }
        }
        if (ct) {
            for (type in contents) {
                if (contents[type] && contents[type].test(ct)) {
                    dataTypes.unshift(type);
                    break;
                }
            }
        }
        if (dataTypes[0] in responses) {
            finalDataType = dataTypes[0];
        } else {
            for (type in responses) {
                if (!dataTypes[0] || s.converters[type + " " + dataTypes[0]]) {
                    finalDataType = type;
                    break;
                }
                if (!firstDataType) {
                    firstDataType = type;
                }
            }
            finalDataType = finalDataType || firstDataType;
        }
        if (finalDataType) {
            if (finalDataType !== dataTypes[0]) {
                dataTypes.unshift(finalDataType);
            }
            return responses[finalDataType];
        }
    }
    function ajaxConvert(s, response, jqXHR, isSuccess) {
        var conv2, current, conv, tmp, prev, converters = {}, dataTypes = s.dataTypes.slice();
        if (dataTypes[1]) {
            for (conv in s.converters) {
                converters[conv.toLowerCase()] = s.converters[conv];
            }
        }
        current = dataTypes.shift();
        while (current) {
            if (s.responseFields[current]) {
                jqXHR[s.responseFields[current]] = response;
            }
            if (!prev && isSuccess && s.dataFilter) {
                response = s.dataFilter(response, s.dataType);
            }
            prev = current;
            current = dataTypes.shift();
            if (current) {
                if (current === "*") {
                    current = prev;
                } else if (prev !== "*" && prev !== current) {
                    conv = converters[prev + " " + current] || converters["* " + current];
                    if (!conv) {
                        for (conv2 in converters) {
                            tmp = conv2.split(" ");
                            if (tmp[1] === current) {
                                conv = converters[prev + " " + tmp[0]] || converters["* " + tmp[0]];
                                if (conv) {
                                    if (conv === true) {
                                        conv = converters[conv2];
                                    } else if (converters[conv2] !== true) {
                                        current = tmp[0];
                                        dataTypes.unshift(tmp[1]);
                                    }
                                    break;
                                }
                            }
                        }
                    }
                    if (conv !== true) {
                        if (conv && s.throws) {
                            response = conv(response);
                        } else {
                            try {
                                response = conv(response);
                            } catch (e) {
                                return {
                                    state: "parsererror",
                                    error: conv ? e : "No conversion from " + prev + " to " + current
                                };
                            }
                        }
                    }
                }
            }
        }
        return {
            state: "success",
            data: response
        };
    }
    jQuery.extend({
        active: 0,
        lastModified: {},
        etag: {},
        ajaxSettings: {
            url: location.href,
            type: "GET",
            isLocal: rlocalProtocol.test(location.protocol),
            global: true,
            processData: true,
            async: true,
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            accepts: {
                "*": allTypes,
                text: "text/plain",
                html: "text/html",
                xml: "application/xml, text/xml",
                json: "application/json, text/javascript"
            },
            contents: {
                xml: /\bxml\b/,
                html: /\bhtml/,
                json: /\bjson\b/
            },
            responseFields: {
                xml: "responseXML",
                text: "responseText",
                json: "responseJSON"
            },
            converters: {
                "* text": String,
                "text html": true,
                "text json": JSON.parse,
                "text xml": jQuery.parseXML
            },
            flatOptions: {
                url: true,
                context: true
            }
        },
        ajaxSetup: function(target, settings) {
            return settings ? ajaxExtend(ajaxExtend(target, jQuery.ajaxSettings), settings) : ajaxExtend(jQuery.ajaxSettings, target);
        },
        ajaxPrefilter: addToPrefiltersOrTransports(prefilters),
        ajaxTransport: addToPrefiltersOrTransports(transports),
        ajax: function(url, options) {
            if (typeof url === "object") {
                options = url;
                url = undefined;
            }
            options = options || {};
            var transport, cacheURL, responseHeadersString, responseHeaders, timeoutTimer, urlAnchor, completed, fireGlobals, i, uncached, s = jQuery.ajaxSetup({}, options), callbackContext = s.context || s, globalEventContext = s.context && (callbackContext.nodeType || callbackContext.jquery) ? jQuery(callbackContext) : jQuery.event, deferred = jQuery.Deferred(), completeDeferred = jQuery.Callbacks("once memory"), statusCode = s.statusCode || {}, requestHeaders = {}, requestHeadersNames = {}, strAbort = "canceled", jqXHR = {
                readyState: 0,
                getResponseHeader: function(key) {
                    var match;
                    if (completed) {
                        if (!responseHeaders) {
                            responseHeaders = {};
                            while (match = rheaders.exec(responseHeadersString)) {
                                responseHeaders[match[1].toLowerCase()] = match[2];
                            }
                        }
                        match = responseHeaders[key.toLowerCase()];
                    }
                    return match == null ? null : match;
                },
                getAllResponseHeaders: function() {
                    return completed ? responseHeadersString : null;
                },
                setRequestHeader: function(name, value) {
                    if (completed == null) {
                        name = requestHeadersNames[name.toLowerCase()] = requestHeadersNames[name.toLowerCase()] || name;
                        requestHeaders[name] = value;
                    }
                    return this;
                },
                overrideMimeType: function(type) {
                    if (completed == null) {
                        s.mimeType = type;
                    }
                    return this;
                },
                statusCode: function(map) {
                    var code;
                    if (map) {
                        if (completed) {
                            jqXHR.always(map[jqXHR.status]);
                        } else {
                            for (code in map) {
                                statusCode[code] = [ statusCode[code], map[code] ];
                            }
                        }
                    }
                    return this;
                },
                abort: function(statusText) {
                    var finalText = statusText || strAbort;
                    if (transport) {
                        transport.abort(finalText);
                    }
                    done(0, finalText);
                    return this;
                }
            };
            deferred.promise(jqXHR);
            s.url = ((url || s.url || location.href) + "").replace(rprotocol, location.protocol + "//");
            s.type = options.method || options.type || s.method || s.type;
            s.dataTypes = (s.dataType || "*").toLowerCase().match(rnothtmlwhite) || [ "" ];
            if (s.crossDomain == null) {
                urlAnchor = document.createElement("a");
                try {
                    urlAnchor.href = s.url;
                    urlAnchor.href = urlAnchor.href;
                    s.crossDomain = originAnchor.protocol + "//" + originAnchor.host !== urlAnchor.protocol + "//" + urlAnchor.host;
                } catch (e) {
                    s.crossDomain = true;
                }
            }
            if (s.data && s.processData && typeof s.data !== "string") {
                s.data = jQuery.param(s.data, s.traditional);
            }
            inspectPrefiltersOrTransports(prefilters, s, options, jqXHR);
            if (completed) {
                return jqXHR;
            }
            fireGlobals = jQuery.event && s.global;
            if (fireGlobals && jQuery.active++ === 0) {
                jQuery.event.trigger("ajaxStart");
            }
            s.type = s.type.toUpperCase();
            s.hasContent = !rnoContent.test(s.type);
            cacheURL = s.url.replace(rhash, "");
            if (!s.hasContent) {
                uncached = s.url.slice(cacheURL.length);
                if (s.data) {
                    cacheURL += (rquery.test(cacheURL) ? "&" : "?") + s.data;
                    delete s.data;
                }
                if (s.cache === false) {
                    cacheURL = cacheURL.replace(rantiCache, "$1");
                    uncached = (rquery.test(cacheURL) ? "&" : "?") + "_=" + nonce++ + uncached;
                }
                s.url = cacheURL + uncached;
            } else if (s.data && s.processData && (s.contentType || "").indexOf("application/x-www-form-urlencoded") === 0) {
                s.data = s.data.replace(r20, "+");
            }
            if (s.ifModified) {
                if (jQuery.lastModified[cacheURL]) {
                    jqXHR.setRequestHeader("If-Modified-Since", jQuery.lastModified[cacheURL]);
                }
                if (jQuery.etag[cacheURL]) {
                    jqXHR.setRequestHeader("If-None-Match", jQuery.etag[cacheURL]);
                }
            }
            if (s.data && s.hasContent && s.contentType !== false || options.contentType) {
                jqXHR.setRequestHeader("Content-Type", s.contentType);
            }
            jqXHR.setRequestHeader("Accept", s.dataTypes[0] && s.accepts[s.dataTypes[0]] ? s.accepts[s.dataTypes[0]] + (s.dataTypes[0] !== "*" ? ", " + allTypes + "; q=0.01" : "") : s.accepts["*"]);
            for (i in s.headers) {
                jqXHR.setRequestHeader(i, s.headers[i]);
            }
            if (s.beforeSend && (s.beforeSend.call(callbackContext, jqXHR, s) === false || completed)) {
                return jqXHR.abort();
            }
            strAbort = "abort";
            completeDeferred.add(s.complete);
            jqXHR.done(s.success);
            jqXHR.fail(s.error);
            transport = inspectPrefiltersOrTransports(transports, s, options, jqXHR);
            if (!transport) {
                done(-1, "No Transport");
            } else {
                jqXHR.readyState = 1;
                if (fireGlobals) {
                    globalEventContext.trigger("ajaxSend", [ jqXHR, s ]);
                }
                if (completed) {
                    return jqXHR;
                }
                if (s.async && s.timeout > 0) {
                    timeoutTimer = window.setTimeout(function() {
                        jqXHR.abort("timeout");
                    }, s.timeout);
                }
                try {
                    completed = false;
                    transport.send(requestHeaders, done);
                } catch (e) {
                    if (completed) {
                        throw e;
                    }
                    done(-1, e);
                }
            }
            function done(status, nativeStatusText, responses, headers) {
                var isSuccess, success, error, response, modified, statusText = nativeStatusText;
                if (completed) {
                    return;
                }
                completed = true;
                if (timeoutTimer) {
                    window.clearTimeout(timeoutTimer);
                }
                transport = undefined;
                responseHeadersString = headers || "";
                jqXHR.readyState = status > 0 ? 4 : 0;
                isSuccess = status >= 200 && status < 300 || status === 304;
                if (responses) {
                    response = ajaxHandleResponses(s, jqXHR, responses);
                }
                response = ajaxConvert(s, response, jqXHR, isSuccess);
                if (isSuccess) {
                    if (s.ifModified) {
                        modified = jqXHR.getResponseHeader("Last-Modified");
                        if (modified) {
                            jQuery.lastModified[cacheURL] = modified;
                        }
                        modified = jqXHR.getResponseHeader("etag");
                        if (modified) {
                            jQuery.etag[cacheURL] = modified;
                        }
                    }
                    if (status === 204 || s.type === "HEAD") {
                        statusText = "nocontent";
                    } else if (status === 304) {
                        statusText = "notmodified";
                    } else {
                        statusText = response.state;
                        success = response.data;
                        error = response.error;
                        isSuccess = !error;
                    }
                } else {
                    error = statusText;
                    if (status || !statusText) {
                        statusText = "error";
                        if (status < 0) {
                            status = 0;
                        }
                    }
                }
                jqXHR.status = status;
                jqXHR.statusText = (nativeStatusText || statusText) + "";
                if (isSuccess) {
                    deferred.resolveWith(callbackContext, [ success, statusText, jqXHR ]);
                } else {
                    deferred.rejectWith(callbackContext, [ jqXHR, statusText, error ]);
                }
                jqXHR.statusCode(statusCode);
                statusCode = undefined;
                if (fireGlobals) {
                    globalEventContext.trigger(isSuccess ? "ajaxSuccess" : "ajaxError", [ jqXHR, s, isSuccess ? success : error ]);
                }
                completeDeferred.fireWith(callbackContext, [ jqXHR, statusText ]);
                if (fireGlobals) {
                    globalEventContext.trigger("ajaxComplete", [ jqXHR, s ]);
                    if (!--jQuery.active) {
                        jQuery.event.trigger("ajaxStop");
                    }
                }
            }
            return jqXHR;
        },
        getJSON: function(url, data, callback) {
            return jQuery.get(url, data, callback, "json");
        },
        getScript: function(url, callback) {
            return jQuery.get(url, undefined, callback, "script");
        }
    });
    jQuery.each([ "get", "post" ], function(i, method) {
        jQuery[method] = function(url, data, callback, type) {
            if (jQuery.isFunction(data)) {
                type = type || callback;
                callback = data;
                data = undefined;
            }
            return jQuery.ajax(jQuery.extend({
                url: url,
                type: method,
                dataType: type,
                data: data,
                success: callback
            }, jQuery.isPlainObject(url) && url));
        };
    });
    jQuery._evalUrl = function(url) {
        return jQuery.ajax({
            url: url,
            type: "GET",
            dataType: "script",
            cache: true,
            async: false,
            global: false,
            throws: true
        });
    };
    jQuery.fn.extend({
        wrapAll: function(html) {
            var wrap;
            if (this[0]) {
                if (jQuery.isFunction(html)) {
                    html = html.call(this[0]);
                }
                wrap = jQuery(html, this[0].ownerDocument).eq(0).clone(true);
                if (this[0].parentNode) {
                    wrap.insertBefore(this[0]);
                }
                wrap.map(function() {
                    var elem = this;
                    while (elem.firstElementChild) {
                        elem = elem.firstElementChild;
                    }
                    return elem;
                }).append(this);
            }
            return this;
        },
        wrapInner: function(html) {
            if (jQuery.isFunction(html)) {
                return this.each(function(i) {
                    jQuery(this).wrapInner(html.call(this, i));
                });
            }
            return this.each(function() {
                var self = jQuery(this), contents = self.contents();
                if (contents.length) {
                    contents.wrapAll(html);
                } else {
                    self.append(html);
                }
            });
        },
        wrap: function(html) {
            var isFunction = jQuery.isFunction(html);
            return this.each(function(i) {
                jQuery(this).wrapAll(isFunction ? html.call(this, i) : html);
            });
        },
        unwrap: function(selector) {
            this.parent(selector).not("body").each(function() {
                jQuery(this).replaceWith(this.childNodes);
            });
            return this;
        }
    });
    jQuery.expr.pseudos.hidden = function(elem) {
        return !jQuery.expr.pseudos.visible(elem);
    };
    jQuery.expr.pseudos.visible = function(elem) {
        return !!(elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length);
    };
    jQuery.ajaxSettings.xhr = function() {
        try {
            return new window.XMLHttpRequest();
        } catch (e) {}
    };
    var xhrSuccessStatus = {
        0: 200,
        1223: 204
    }, xhrSupported = jQuery.ajaxSettings.xhr();
    support.cors = !!xhrSupported && "withCredentials" in xhrSupported;
    support.ajax = xhrSupported = !!xhrSupported;
    jQuery.ajaxTransport(function(options) {
        var callback, errorCallback;
        if (support.cors || xhrSupported && !options.crossDomain) {
            return {
                send: function(headers, complete) {
                    var i, xhr = options.xhr();
                    xhr.open(options.type, options.url, options.async, options.username, options.password);
                    if (options.xhrFields) {
                        for (i in options.xhrFields) {
                            xhr[i] = options.xhrFields[i];
                        }
                    }
                    if (options.mimeType && xhr.overrideMimeType) {
                        xhr.overrideMimeType(options.mimeType);
                    }
                    if (!options.crossDomain && !headers["X-Requested-With"]) {
                        headers["X-Requested-With"] = "XMLHttpRequest";
                    }
                    for (i in headers) {
                        xhr.setRequestHeader(i, headers[i]);
                    }
                    callback = function(type) {
                        return function() {
                            if (callback) {
                                callback = errorCallback = xhr.onload = xhr.onerror = xhr.onabort = xhr.onreadystatechange = null;
                                if (type === "abort") {
                                    xhr.abort();
                                } else if (type === "error") {
                                    if (typeof xhr.status !== "number") {
                                        complete(0, "error");
                                    } else {
                                        complete(xhr.status, xhr.statusText);
                                    }
                                } else {
                                    complete(xhrSuccessStatus[xhr.status] || xhr.status, xhr.statusText, (xhr.responseType || "text") !== "text" || typeof xhr.responseText !== "string" ? {
                                        binary: xhr.response
                                    } : {
                                        text: xhr.responseText
                                    }, xhr.getAllResponseHeaders());
                                }
                            }
                        };
                    };
                    xhr.onload = callback();
                    errorCallback = xhr.onerror = callback("error");
                    if (xhr.onabort !== undefined) {
                        xhr.onabort = errorCallback;
                    } else {
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4) {
                                window.setTimeout(function() {
                                    if (callback) {
                                        errorCallback();
                                    }
                                });
                            }
                        };
                    }
                    callback = callback("abort");
                    try {
                        xhr.send(options.hasContent && options.data || null);
                    } catch (e) {
                        if (callback) {
                            throw e;
                        }
                    }
                },
                abort: function() {
                    if (callback) {
                        callback();
                    }
                }
            };
        }
    });
    jQuery.ajaxPrefilter(function(s) {
        if (s.crossDomain) {
            s.contents.script = false;
        }
    });
    jQuery.ajaxSetup({
        accepts: {
            script: "text/javascript, application/javascript, " + "application/ecmascript, application/x-ecmascript"
        },
        contents: {
            script: /\b(?:java|ecma)script\b/
        },
        converters: {
            "text script": function(text) {
                jQuery.globalEval(text);
                return text;
            }
        }
    });
    jQuery.ajaxPrefilter("script", function(s) {
        if (s.cache === undefined) {
            s.cache = false;
        }
        if (s.crossDomain) {
            s.type = "GET";
        }
    });
    jQuery.ajaxTransport("script", function(s) {
        if (s.crossDomain) {
            var script, callback;
            return {
                send: function(_, complete) {
                    script = jQuery("<script>").prop({
                        charset: s.scriptCharset,
                        src: s.url
                    }).on("load error", callback = function(evt) {
                        script.remove();
                        callback = null;
                        if (evt) {
                            complete(evt.type === "error" ? 404 : 200, evt.type);
                        }
                    });
                    document.head.appendChild(script[0]);
                },
                abort: function() {
                    if (callback) {
                        callback();
                    }
                }
            };
        }
    });
    var oldCallbacks = [], rjsonp = /(=)\?(?=&|$)|\?\?/;
    jQuery.ajaxSetup({
        jsonp: "callback",
        jsonpCallback: function() {
            var callback = oldCallbacks.pop() || jQuery.expando + "_" + nonce++;
            this[callback] = true;
            return callback;
        }
    });
    jQuery.ajaxPrefilter("json jsonp", function(s, originalSettings, jqXHR) {
        var callbackName, overwritten, responseContainer, jsonProp = s.jsonp !== false && (rjsonp.test(s.url) ? "url" : typeof s.data === "string" && (s.contentType || "").indexOf("application/x-www-form-urlencoded") === 0 && rjsonp.test(s.data) && "data");
        if (jsonProp || s.dataTypes[0] === "jsonp") {
            callbackName = s.jsonpCallback = jQuery.isFunction(s.jsonpCallback) ? s.jsonpCallback() : s.jsonpCallback;
            if (jsonProp) {
                s[jsonProp] = s[jsonProp].replace(rjsonp, "$1" + callbackName);
            } else if (s.jsonp !== false) {
                s.url += (rquery.test(s.url) ? "&" : "?") + s.jsonp + "=" + callbackName;
            }
            s.converters["script json"] = function() {
                if (!responseContainer) {
                    jQuery.error(callbackName + " was not called");
                }
                return responseContainer[0];
            };
            s.dataTypes[0] = "json";
            overwritten = window[callbackName];
            window[callbackName] = function() {
                responseContainer = arguments;
            };
            jqXHR.always(function() {
                if (overwritten === undefined) {
                    jQuery(window).removeProp(callbackName);
                } else {
                    window[callbackName] = overwritten;
                }
                if (s[callbackName]) {
                    s.jsonpCallback = originalSettings.jsonpCallback;
                    oldCallbacks.push(callbackName);
                }
                if (responseContainer && jQuery.isFunction(overwritten)) {
                    overwritten(responseContainer[0]);
                }
                responseContainer = overwritten = undefined;
            });
            return "script";
        }
    });
    support.createHTMLDocument = function() {
        var body = document.implementation.createHTMLDocument("").body;
        body.innerHTML = "<form></form><form></form>";
        return body.childNodes.length === 2;
    }();
    jQuery.parseHTML = function(data, context, keepScripts) {
        if (typeof data !== "string") {
            return [];
        }
        if (typeof context === "boolean") {
            keepScripts = context;
            context = false;
        }
        var base, parsed, scripts;
        if (!context) {
            if (support.createHTMLDocument) {
                context = document.implementation.createHTMLDocument("");
                base = context.createElement("base");
                base.href = document.location.href;
                context.head.appendChild(base);
            } else {
                context = document;
            }
        }
        parsed = rsingleTag.exec(data);
        scripts = !keepScripts && [];
        if (parsed) {
            return [ context.createElement(parsed[1]) ];
        }
        parsed = buildFragment([ data ], context, scripts);
        if (scripts && scripts.length) {
            jQuery(scripts).remove();
        }
        return jQuery.merge([], parsed.childNodes);
    };
    jQuery.fn.load = function(url, params, callback) {
        var selector, type, response, self = this, off = url.indexOf(" ");
        if (off > -1) {
            selector = stripAndCollapse(url.slice(off));
            url = url.slice(0, off);
        }
        if (jQuery.isFunction(params)) {
            callback = params;
            params = undefined;
        } else if (params && typeof params === "object") {
            type = "POST";
        }
        if (self.length > 0) {
            jQuery.ajax({
                url: url,
                type: type || "GET",
                dataType: "html",
                data: params
            }).done(function(responseText) {
                response = arguments;
                self.html(selector ? jQuery("<div>").append(jQuery.parseHTML(responseText)).find(selector) : responseText);
            }).always(callback && function(jqXHR, status) {
                self.each(function() {
                    callback.apply(this, response || [ jqXHR.responseText, status, jqXHR ]);
                });
            });
        }
        return this;
    };
    jQuery.each([ "ajaxStart", "ajaxStop", "ajaxComplete", "ajaxError", "ajaxSuccess", "ajaxSend" ], function(i, type) {
        jQuery.fn[type] = function(fn) {
            return this.on(type, fn);
        };
    });
    jQuery.expr.pseudos.animated = function(elem) {
        return jQuery.grep(jQuery.timers, function(fn) {
            return elem === fn.elem;
        }).length;
    };
    jQuery.offset = {
        setOffset: function(elem, options, i) {
            var curPosition, curLeft, curCSSTop, curTop, curOffset, curCSSLeft, calculatePosition, position = jQuery.css(elem, "position"), curElem = jQuery(elem), props = {};
            if (position === "static") {
                elem.style.position = "relative";
            }
            curOffset = curElem.offset();
            curCSSTop = jQuery.css(elem, "top");
            curCSSLeft = jQuery.css(elem, "left");
            calculatePosition = (position === "absolute" || position === "fixed") && (curCSSTop + curCSSLeft).indexOf("auto") > -1;
            if (calculatePosition) {
                curPosition = curElem.position();
                curTop = curPosition.top;
                curLeft = curPosition.left;
            } else {
                curTop = parseFloat(curCSSTop) || 0;
                curLeft = parseFloat(curCSSLeft) || 0;
            }
            if (jQuery.isFunction(options)) {
                options = options.call(elem, i, jQuery.extend({}, curOffset));
            }
            if (options.top != null) {
                props.top = options.top - curOffset.top + curTop;
            }
            if (options.left != null) {
                props.left = options.left - curOffset.left + curLeft;
            }
            if ("using" in options) {
                options.using.call(elem, props);
            } else {
                curElem.css(props);
            }
        }
    };
    jQuery.fn.extend({
        offset: function(options) {
            if (arguments.length) {
                return options === undefined ? this : this.each(function(i) {
                    jQuery.offset.setOffset(this, options, i);
                });
            }
            var doc, docElem, rect, win, elem = this[0];
            if (!elem) {
                return;
            }
            if (!elem.getClientRects().length) {
                return {
                    top: 0,
                    left: 0
                };
            }
            rect = elem.getBoundingClientRect();
            doc = elem.ownerDocument;
            docElem = doc.documentElement;
            win = doc.defaultView;
            return {
                top: rect.top + win.pageYOffset - docElem.clientTop,
                left: rect.left + win.pageXOffset - docElem.clientLeft
            };
        },
        position: function() {
            if (!this[0]) {
                return;
            }
            var offsetParent, offset, elem = this[0], parentOffset = {
                top: 0,
                left: 0
            };
            if (jQuery.css(elem, "position") === "fixed") {
                offset = elem.getBoundingClientRect();
            } else {
                offsetParent = this.offsetParent();
                offset = this.offset();
                if (!nodeName(offsetParent[0], "html")) {
                    parentOffset = offsetParent.offset();
                }
                parentOffset = {
                    top: parentOffset.top + jQuery.css(offsetParent[0], "borderTopWidth", true),
                    left: parentOffset.left + jQuery.css(offsetParent[0], "borderLeftWidth", true)
                };
            }
            return {
                top: offset.top - parentOffset.top - jQuery.css(elem, "marginTop", true),
                left: offset.left - parentOffset.left - jQuery.css(elem, "marginLeft", true)
            };
        },
        offsetParent: function() {
            return this.map(function() {
                var offsetParent = this.offsetParent;
                while (offsetParent && jQuery.css(offsetParent, "position") === "static") {
                    offsetParent = offsetParent.offsetParent;
                }
                return offsetParent || documentElement;
            });
        }
    });
    jQuery.each({
        scrollLeft: "pageXOffset",
        scrollTop: "pageYOffset"
    }, function(method, prop) {
        var top = "pageYOffset" === prop;
        jQuery.fn[method] = function(val) {
            return access(this, function(elem, method, val) {
                var win;
                if (jQuery.isWindow(elem)) {
                    win = elem;
                } else if (elem.nodeType === 9) {
                    win = elem.defaultView;
                }
                if (val === undefined) {
                    return win ? win[prop] : elem[method];
                }
                if (win) {
                    win.scrollTo(!top ? val : win.pageXOffset, top ? val : win.pageYOffset);
                } else {
                    elem[method] = val;
                }
            }, method, val, arguments.length);
        };
    });
    jQuery.each([ "top", "left" ], function(i, prop) {
        jQuery.cssHooks[prop] = addGetHookIf(support.pixelPosition, function(elem, computed) {
            if (computed) {
                computed = curCSS(elem, prop);
                return rnumnonpx.test(computed) ? jQuery(elem).position()[prop] + "px" : computed;
            }
        });
    });
    jQuery.each({
        Height: "height",
        Width: "width"
    }, function(name, type) {
        jQuery.each({
            padding: "inner" + name,
            content: type,
            "": "outer" + name
        }, function(defaultExtra, funcName) {
            jQuery.fn[funcName] = function(margin, value) {
                var chainable = arguments.length && (defaultExtra || typeof margin !== "boolean"), extra = defaultExtra || (margin === true || value === true ? "margin" : "border");
                return access(this, function(elem, type, value) {
                    var doc;
                    if (jQuery.isWindow(elem)) {
                        return funcName.indexOf("outer") === 0 ? elem["inner" + name] : elem.document.documentElement["client" + name];
                    }
                    if (elem.nodeType === 9) {
                        doc = elem.documentElement;
                        return Math.max(elem.body["scroll" + name], doc["scroll" + name], elem.body["offset" + name], doc["offset" + name], doc["client" + name]);
                    }
                    return value === undefined ? jQuery.css(elem, type, extra) : jQuery.style(elem, type, value, extra);
                }, type, chainable ? margin : undefined, chainable);
            };
        });
    });
    jQuery.fn.extend({
        bind: function(types, data, fn) {
            return this.on(types, null, data, fn);
        },
        unbind: function(types, fn) {
            return this.off(types, null, fn);
        },
        delegate: function(selector, types, data, fn) {
            return this.on(types, selector, data, fn);
        },
        undelegate: function(selector, types, fn) {
            return arguments.length === 1 ? this.off(selector, "**") : this.off(types, selector || "**", fn);
        }
    });
    jQuery.holdReady = function(hold) {
        if (hold) {
            jQuery.readyWait++;
        } else {
            jQuery.ready(true);
        }
    };
    jQuery.isArray = Array.isArray;
    jQuery.parseJSON = JSON.parse;
    jQuery.nodeName = nodeName;
    if (typeof define === "function" && define.amd) {
        define("jquery", [], function() {
            return jQuery;
        });
    }
    var _jQuery = window.jQuery, _$ = window.$;
    jQuery.noConflict = function(deep) {
        if (window.$ === jQuery) {
            window.$ = _$;
        }
        if (deep && window.jQuery === jQuery) {
            window.jQuery = _jQuery;
        }
        return jQuery;
    };
    if (!noGlobal) {
        window.jQuery = window.$ = jQuery;
    }
    return jQuery;
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
        },
        15: {
            name: "Default",
            className: "background-16"
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
                },
                8: {
                    name: "Default",
                    className: "h-style-9",
                    class: "text-9"
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
                },
                8: {
                    name: "Default",
                    className: "text-9",
                    class: "text-9"
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
                bg_change_html += '<li><a href="' + val.className + '" title="' + val.name + '" class="tooltipper ' + val.className + '"></a></li>';
                nav.push(val.className);
            });
            bg_change_html += "</ul>";
            $bg_block.html(bg_change_html);
            $theme_control_panel.append($bg_block);
            $bg_block.find("a").click(function() {
                var nextClassName = $(this).attr("href");
                if (!$body.hasClass(nextClassName)) {
                    changeBodyClass(nextClassName, nav);
                    $bg_block.find(".is-active").removeClass("is-active");
                    $(this).parent().addClass("is-active");
                }
                return false;
            });
        }
        if (page_config.backgrounds) {
            var $bg_block = $("<div/>").attr("id", "backgrounds").addClass("style_block");
            var bg_change_html = "<span>Backgrounds:</span>";
            bg_change_html += '<ul class="limited">';
            $.each(page_config.backgrounds, function(idx, val) {
                bg_change_html += '<li><a href="' + val.className + '" title="' + val.name + '" class="tooltipper ' + val.className + '"></a></li>';
                pattern_classes.push(val.className);
            });
            bg_change_html += "</ul>";
            $bg_block.html(bg_change_html);
            $theme_control_panel.append($bg_block);
            $bg_block.find("a").click(function() {
                var nextClassName = $(this).attr("href");
                if (!$body.hasClass(nextClassName)) {
                    changeBodyClass(nextClassName, pattern_classes);
                    $bg_block.find(".is-active").removeClass("is-active");
                    $(this).parent().addClass("is-active");
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
                changeBodyClass(page_config.backgrounds[15].className, pattern_classes);
                $theme_control_panel.find("select").val(1);
                changeBodyClass(page_config.styles.headerStyle.list[8].className, header_style_classes);
                changeBodyClass(page_config.styles.textStyle.list[8].className, text_style_classes);
                $("iframe").contents().find("body").removeClass("text-1 text-2 text-3 text-4 text-5 text-6 text-7 text-8").addClass("text-9");
                links_picker.css({
                    "background-color": "#008a05"
                }).ColorPickerSetColor("#008a05");
                $("body").get(0).style.setProperty("--main-color", "#9193de");
                $("iframe").contents().find("body").css("--main-color", "#9193de");
                $theme_control_panel.find(".is-active").removeClass();
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

(function($) {
    $.fn.markItUp = function(settings, extraSettings) {
        var method, params, options, ctrlKey, shiftKey, altKey;
        ctrlKey = shiftKey = altKey = false;
        if (typeof settings == "string") {
            method = settings;
            params = extraSettings;
        }
        options = {
            id: "",
            nameSpace: "",
            root: "",
            previewHandler: false,
            previewInWindow: "",
            previewInElement: "",
            previewAutoRefresh: true,
            previewPosition: "after",
            previewTemplatePath: "~/templates/preview.html",
            previewParser: false,
            previewParserPath: "",
            previewParserVar: "data",
            previewParserAjaxType: "POST",
            resizeHandle: true,
            beforeInsert: "",
            afterInsert: "",
            onEnter: {},
            onShiftEnter: {},
            onCtrlEnter: {},
            onTab: {},
            markupSet: [ {} ]
        };
        $.extend(options, settings, extraSettings);
        if (!options.root) {
            $("script").each(function(a, tag) {
                miuScript = $(tag).get(0).src.match(/(.*)jquery\.markitup(\.pack)?\.js$/);
                if (miuScript !== null) {
                    options.root = miuScript[1];
                }
            });
        }
        var uaMatch = function(ua) {
            ua = ua.toLowerCase();
            var match = /(chrome)[ \/]([\w.]+)/.exec(ua) || /(webkit)[ \/]([\w.]+)/.exec(ua) || /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) || /(msie) ([\w.]+)/.exec(ua) || ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) || [];
            return {
                browser: match[1] || "",
                version: match[2] || "0"
            };
        };
        var matched = uaMatch(navigator.userAgent);
        var browser = {};
        if (matched.browser) {
            browser[matched.browser] = true;
            browser.version = matched.version;
        }
        if (browser.chrome) {
            browser.webkit = true;
        } else if (browser.webkit) {
            browser.safari = true;
        }
        return this.each(function() {
            var $$, textarea, levels, scrollPosition, caretPosition, caretOffset, clicked, hash, header, footer, previewWindow, template, iFrame, abort;
            $$ = $(this);
            textarea = this;
            levels = [];
            abort = false;
            scrollPosition = caretPosition = 0;
            caretOffset = -1;
            options.previewParserPath = localize(options.previewParserPath);
            options.previewTemplatePath = localize(options.previewTemplatePath);
            if (method) {
                switch (method) {
                  case "remove":
                    remove();
                    break;

                  case "insert":
                    markup(params);
                    break;

                  default:
                    $.error("Method " + method + " does not exist on jQuery.markItUp");
                }
                return;
            }
            function localize(data, inText) {
                if (inText) {
                    return data.replace(/("|')~\//g, "$1" + options.root);
                }
                return data.replace(/^~\//, options.root);
            }
            function init() {
                id = "";
                nameSpace = "";
                if (options.id) {
                    id = 'id="' + options.id + '"';
                } else if ($$.attr("id")) {
                    id = 'id="markItUp' + $$.attr("id").substr(0, 1).toUpperCase() + $$.attr("id").substr(1) + '"';
                }
                if (options.nameSpace) {
                    nameSpace = 'id="' + options.nameSpace + '" class="' + options.nameSpace + '"';
                }
                $$.wrap("<div " + nameSpace + "></div>");
                $$.wrap("<div " + id + ' class="markItUp"></div>');
                $$.wrap('<div class="markItUpContainer"></div>');
                $$.addClass("markItUpEditor");
                header = $('<div class="markItUpHeader"></div>').insertBefore($$);
                $(dropMenus(options.markupSet)).appendTo(header);
                footer = $('<div class="markItUpFooter"></div>').insertAfter($$);
                if (options.resizeHandle === true && browser.safari !== true) {
                    resizeHandle = $('<div class="markItUpResizeHandle"></div>').insertAfter($$).bind("mousedown.markItUp", function(e) {
                        var h = $$.height(), y = e.clientY, mouseMove, mouseUp;
                        mouseMove = function(e) {
                            $$.css("height", Math.max(20, e.clientY + h - y) + "px");
                            return false;
                        };
                        mouseUp = function(e) {
                            $("html").unbind("mousemove.markItUp", mouseMove).unbind("mouseup.markItUp", mouseUp);
                            return false;
                        };
                        $("html").bind("mousemove.markItUp", mouseMove).bind("mouseup.markItUp", mouseUp);
                    });
                    footer.append(resizeHandle);
                }
                $$.bind("keydown.markItUp", keyPressed).bind("keyup", keyPressed);
                $$.bind("insertion.markItUp", function(e, settings) {
                    if (settings.target !== false) {
                        get();
                    }
                    if (textarea === $.markItUp.focused) {
                        markup(settings);
                    }
                });
                $$.bind("focus.markItUp", function() {
                    $.markItUp.focused = this;
                });
                if (options.previewInElement) {
                    refreshPreview();
                }
            }
            function dropMenus(markupSet) {
                var ul = $("<ul></ul>"), i = 0;
                $("li:hover > ul", ul).css("display", "block");
                $.each(markupSet, function() {
                    var button = this, t = "", title, li, j;
                    button.title ? title = button.key ? (button.title || "") + " [Ctrl+" + button.key + "]" : button.title || "" : title = button.key ? (button.name || "") + " [Ctrl+" + button.key + "]" : button.name || "";
                    key = button.key ? 'accesskey="' + button.key + '"' : "";
                    if (button.separator) {
                        li = $('<li class="markItUpSeparator">' + (button.separator || "") + "</li>").appendTo(ul);
                    } else {
                        i++;
                        for (j = levels.length - 1; j >= 0; j--) {
                            t += levels[j] + "-";
                        }
                        var setTitle = ' title="' + title + '"';
                        var addClass = "";
                        if (typeof button.className !== "undefined") {
                            var str = button.className;
                            if (str.includes("font_") || str.includes("text-") || str.includes("size") || str.includes("colors")) {
                                setTitle = "";
                            }
                            if (str.includes("text-")) {
                                addClass = " " + str;
                            }
                        }
                        li = $('<li class="tooltipper markItUpButton markItUpButton' + t + i + " " + (button.className || "") + '"' + setTitle + '><a href="#" ' + key + ">" + (button.showName || "") + "</a></li>").bind("contextmenu.markItUp", function() {
                            return false;
                        }).bind("click.markItUp", function(e) {
                            e.preventDefault();
                        }).bind("focusin.markItUp", function() {
                            $$.focus();
                        }).bind("mouseup", function(e) {
                            if (button.call) {
                                eval(button.call)(e);
                            }
                            setTimeout(function() {
                                markup(button);
                            }, 1);
                            return false;
                        }).bind("mouseenter.markItUp", function() {
                            $("> ul", this).show();
                            $(document).one("click", function() {
                                $("ul ul", header).hide();
                            });
                        }).bind("mouseleave.markItUp", function() {
                            $("> ul", this).hide();
                        }).appendTo(ul);
                        if (button.dropMenu) {
                            levels.push(i);
                            $(li).addClass("markItUpDropMenu").append(dropMenus(button.dropMenu));
                        }
                    }
                });
                levels.pop();
                return ul;
            }
            function magicMarkups(string) {
                if (string) {
                    string = string.toString();
                    string = string.replace(/\(\!\(([\s\S]*?)\)\!\)/g, function(x, a) {
                        var b = a.split("|!|");
                        if (altKey === true) {
                            return b[1] !== undefined ? b[1] : b[0];
                        } else {
                            return b[1] === undefined ? "" : b[0];
                        }
                    });
                    string = string.replace(/\[\!\[([\s\S]*?)\]\!\]/g, function(x, a) {
                        var b = a.split(":!:");
                        if (abort === true) {
                            return false;
                        }
                        value = prompt(b[0], b[1] ? b[1] : "");
                        if (value === null) {
                            abort = true;
                        }
                        return value;
                    });
                    return string;
                }
                return "";
            }
            function prepare(action) {
                if ($.isFunction(action)) {
                    action = action(hash);
                }
                return magicMarkups(action);
            }
            function build(string) {
                var openWith = prepare(clicked.openWith);
                var placeHolder = prepare(clicked.placeHolder);
                var replaceWith = prepare(clicked.replaceWith);
                var closeWith = prepare(clicked.closeWith);
                var openBlockWith = prepare(clicked.openBlockWith);
                var closeBlockWith = prepare(clicked.closeBlockWith);
                var multiline = clicked.multiline;
                if (replaceWith !== "") {
                    block = openWith + replaceWith + closeWith;
                } else if (selection === "" && placeHolder !== "") {
                    block = openWith + placeHolder + closeWith;
                } else {
                    string = string || selection;
                    var lines = [ string ], blocks = [];
                    if (multiline === true) {
                        lines = string.split(/\r?\n/);
                    }
                    for (var l = 0; l < lines.length; l++) {
                        line = lines[l];
                        var trailingSpaces;
                        if (trailingSpaces = line.match(/ *$/)) {
                            blocks.push(openWith + line.replace(/ *$/g, "") + closeWith + trailingSpaces);
                        } else {
                            blocks.push(openWith + line + closeWith);
                        }
                    }
                    block = blocks.join("\n");
                }
                block = openBlockWith + block + closeBlockWith;
                return {
                    block: block,
                    openBlockWith: openBlockWith,
                    openWith: openWith,
                    replaceWith: replaceWith,
                    placeHolder: placeHolder,
                    closeWith: closeWith,
                    closeBlockWith: closeBlockWith
                };
            }
            function markup(button) {
                var len, j, n, i;
                hash = clicked = button;
                get();
                $.extend(hash, {
                    line: "",
                    root: options.root,
                    textarea: textarea,
                    selection: selection || "",
                    caretPosition: caretPosition,
                    ctrlKey: ctrlKey,
                    shiftKey: shiftKey,
                    altKey: altKey
                });
                prepare(options.beforeInsert);
                prepare(clicked.beforeInsert);
                if (ctrlKey === true && shiftKey === true || button.multiline === true) {
                    prepare(clicked.beforeMultiInsert);
                }
                $.extend(hash, {
                    line: 1
                });
                if (ctrlKey === true && shiftKey === true) {
                    lines = selection.split(/\r?\n/);
                    for (j = 0, n = lines.length, i = 0; i < n; i++) {
                        if ($.trim(lines[i]) !== "") {
                            $.extend(hash, {
                                line: ++j,
                                selection: lines[i]
                            });
                            lines[i] = build(lines[i]).block;
                        } else {
                            lines[i] = "";
                        }
                    }
                    string = {
                        block: lines.join("\n")
                    };
                    start = caretPosition;
                    len = string.block.length + (browser.opera ? n - 1 : 0);
                } else if (ctrlKey === true) {
                    string = build(selection);
                    start = caretPosition + string.openWith.length;
                    len = string.block.length - string.openWith.length - string.closeWith.length;
                    len = len - (string.block.match(/ $/) ? 1 : 0);
                    len -= fixIeBug(string.block);
                } else if (shiftKey === true) {
                    string = build(selection);
                    start = caretPosition;
                    len = string.block.length;
                    len -= fixIeBug(string.block);
                } else {
                    string = build(selection);
                    start = caretPosition + string.block.length;
                    len = 0;
                    start -= fixIeBug(string.block);
                }
                if (selection === "" && string.replaceWith === "") {
                    caretOffset += fixOperaBug(string.block);
                    start = caretPosition + string.openBlockWith.length + string.openWith.length;
                    len = string.block.length - string.openBlockWith.length - string.openWith.length - string.closeWith.length - string.closeBlockWith.length;
                    caretOffset = $$.val().substring(caretPosition, $$.val().length).length;
                    caretOffset -= fixOperaBug($$.val().substring(0, caretPosition));
                }
                $.extend(hash, {
                    caretPosition: caretPosition,
                    scrollPosition: scrollPosition
                });
                if (string.block !== selection && abort === false) {
                    insert(string.block);
                    set(start, len);
                } else {
                    caretOffset = -1;
                }
                get();
                $.extend(hash, {
                    line: "",
                    selection: selection
                });
                if (ctrlKey === true && shiftKey === true || button.multiline === true) {
                    prepare(clicked.afterMultiInsert);
                }
                prepare(clicked.afterInsert);
                prepare(options.afterInsert);
                if (previewWindow && options.previewAutoRefresh) {
                    refreshPreview();
                }
                shiftKey = altKey = ctrlKey = abort = false;
            }
            function fixOperaBug(string) {
                if (browser.opera) {
                    return string.length - string.replace(/\n*/g, "").length;
                }
                return 0;
            }
            function fixIeBug(string) {
                if (browser.msie) {
                    return string.length - string.replace(/\r*/g, "").length;
                }
                return 0;
            }
            function insert(block) {
                if (document.selection) {
                    var newSelection = document.selection.createRange();
                    newSelection.text = block;
                } else {
                    textarea.value = textarea.value.substring(0, caretPosition) + block + textarea.value.substring(caretPosition + selection.length, textarea.value.length);
                }
            }
            function set(start, len) {
                if (textarea.createTextRange) {
                    if (browser.opera && browser.version >= 9.5 && len == 0) {
                        return false;
                    }
                    range = textarea.createTextRange();
                    range.collapse(true);
                    range.moveStart("character", start);
                    range.moveEnd("character", len);
                    range.select();
                } else if (textarea.setSelectionRange) {
                    textarea.setSelectionRange(start, start + len);
                }
                textarea.scrollTop = scrollPosition;
                textarea.focus();
            }
            function get() {
                textarea.focus();
                scrollPosition = textarea.scrollTop;
                if (document.selection) {
                    selection = document.selection.createRange().text;
                    if (browser.msie) {
                        var range = document.selection.createRange(), rangeCopy = range.duplicate();
                        rangeCopy.moveToElementText(textarea);
                        caretPosition = -1;
                        while (rangeCopy.inRange(range)) {
                            rangeCopy.moveStart("character");
                            caretPosition++;
                        }
                    } else {
                        caretPosition = textarea.selectionStart;
                    }
                } else {
                    caretPosition = textarea.selectionStart;
                    selection = textarea.value.substring(caretPosition, textarea.selectionEnd);
                }
                return selection;
            }
            function preview() {
                if (typeof options.previewHandler === "function") {
                    previewWindow = true;
                } else if (options.previewInElement) {
                    previewWindow = $(options.previewInElement);
                    var parent = $("#" + options.previewInElement).parent().parent().attr("id");
                    $("#" + parent).slideToggle(1250);
                } else if (!previewWindow || previewWindow.closed) {
                    if (options.previewInWindow) {
                        previewWindow = window.open("", "preview", options.previewInWindow);
                        $(window).unload(function() {
                            previewWindow.close();
                        });
                    } else {
                        iFrame = $('<iframe class="markItUpPreviewFrame"></iframe>');
                        if (options.previewPosition == "after") {
                            iFrame.insertAfter(footer);
                        } else {
                            iFrame.insertBefore(header);
                        }
                        previewWindow = iFrame[iFrame.length - 1].contentWindow || frame[iFrame.length - 1];
                    }
                } else if (altKey === true) {
                    if (iFrame) {
                        iFrame.remove();
                    } else {
                        previewWindow.close();
                    }
                    previewWindow = iFrame = false;
                }
                if (!options.previewAutoRefresh) {
                    refreshPreview();
                }
                if (options.previewInWindow) {
                    previewWindow.focus();
                }
            }
            function refreshPreview() {
                renderPreview();
            }
            function renderPreview() {
                var parsedData = $$.val();
                if (options.previewParser && typeof options.previewParser === "function") {
                    parsedData = options.previewParser(parsedData);
                }
                if (parsedData.length > 1) {
                    if (options.previewInElement != "") {
                        var parent = $("#" + options.previewInElement).parent().parent().attr("id");
                        if ($("#" + parent).is(":visible")) {
                            getAjax(parsedData);
                        }
                    } else {
                        getAjax(parsedData);
                    }
                } else {
                    return false;
                }
            }
            function getAjax(parsedData) {
                if (options.previewHandler && typeof options.previewHandler === "function") {
                    options.previewHandler(parsedData);
                } else if (options.previewParserPath !== "") {
                    $.ajax({
                        type: options.previewParserAjaxType,
                        dataType: "text",
                        global: false,
                        url: options.previewParserPath,
                        data: options.previewParserVar + "=" + encodeURIComponent(parsedData),
                        success: function(data) {
                            writeInPreview(localize(data, 1));
                        }
                    });
                } else {
                    if (!template) {
                        $.ajax({
                            url: options.previewTemplatePath,
                            dataType: "text",
                            global: false,
                            success: function(data) {
                                writeInPreview(localize(data, 1).replace(/<!-- content -->/g, parsedData));
                            }
                        });
                    }
                }
                return false;
            }
            function writeInPreview(data) {
                if (options.previewInElement) {
                    $("#" + options.previewInElement).html(data);
                } else if (previewWindow && previewWindow.document) {
                    try {
                        sp = previewWindow.document.documentElement.scrollTop;
                    } catch (e) {
                        sp = 0;
                    }
                    previewWindow.document.open();
                    previewWindow.document.write(data);
                    previewWindow.document.close();
                    previewWindow.document.documentElement.scrollTop = sp;
                }
            }
            function keyPressed(e) {
                shiftKey = e.shiftKey;
                altKey = e.altKey;
                ctrlKey = !(e.altKey && e.ctrlKey) ? e.ctrlKey || e.metaKey : false;
                if (e.type === "keydown") {
                    if (ctrlKey === true) {
                        li = $('a[accesskey="' + (e.keyCode == 13 ? "\\n" : String.fromCharCode(e.keyCode)) + '"]', header).parent("li");
                        if (li.length !== 0) {
                            ctrlKey = false;
                            setTimeout(function() {
                                li.triggerHandler("mouseup");
                            }, 1);
                            return false;
                        }
                    }
                    if (e.keyCode === 13 || e.keyCode === 10) {
                        if (ctrlKey === true) {
                            ctrlKey = false;
                            markup(options.onCtrlEnter);
                            return options.onCtrlEnter.keepDefault;
                        } else if (shiftKey === true) {
                            shiftKey = false;
                            markup(options.onShiftEnter);
                            return options.onShiftEnter.keepDefault;
                        } else {
                            markup(options.onEnter);
                            return options.onEnter.keepDefault;
                        }
                    }
                    if (e.keyCode === 9) {
                        if (shiftKey == true || ctrlKey == true || altKey == true) {
                            return false;
                        }
                        if (caretOffset !== -1) {
                            get();
                            caretOffset = $$.val().length - caretOffset;
                            set(caretOffset, 0);
                            caretOffset = -1;
                            return false;
                        } else {
                            markup(options.onTab);
                            return options.onTab.keepDefault;
                        }
                    }
                }
            }
            function remove() {
                $$.unbind(".markItUp").removeClass("markItUpEditor");
                $$.parent("div").parent("div.markItUp").parent("div").replaceWith($$);
                var relativeRef = $$.parent("div").parent("div.markItUp").parent("div");
                if (relativeRef.length) {
                    relativeRef.replaceWith($$);
                }
                $$.data("markItUp", null);
            }
            init();
        });
    };
    $.fn.markItUpRemove = function() {
        return this.each(function() {
            $(this).markItUp("remove");
        });
    };
    $.markItUp = function(settings) {
        var options = {
            target: false
        };
        $.extend(options, settings);
        if (options.target) {
            return $(options.target).each(function() {
                $(this).focus();
                $(this).trigger("insertion", [ options ]);
            });
        } else {
            $("textarea").trigger("insertion", [ options ]);
        }
    };
})(jQuery);

var myBbcodeSettings = {
    nameSpace: "bbcode",
    previewParserPath: "./ajax/bbcode_parser.php",
    previewInElement: "preview-window",
    markupSet: [ {
        name: "Bold",
        key: "B",
        openWith: "[b]",
        closeWith: "[/b]",
        className: "boldbutton"
    }, {
        name: "Italic",
        key: "I",
        openWith: "[i]",
        closeWith: "[/i]",
        className: "italicbutton"
    }, {
        name: "Underline",
        key: "U",
        openWith: "[u]",
        closeWith: "[/u]",
        className: "underlinebutton"
    }, {
        name: "Strike through",
        key: "S",
        openWith: "[s]",
        closeWith: "[/s]",
        className: "strikebutton"
    }, {
        name: "Subscript",
        openWith: "[sub]",
        closeWith: "[/sub]",
        className: "subscriptbutton"
    }, {
        name: "Superscript",
        openWith: "[sup]",
        closeWith: "[/sup]",
        className: "superscriptbutton"
    }, {
        name: "Horizontal line",
        openWith: "[hr] ",
        className: "Horizontal_line"
    }, {
        separator: " "
    }, {
        name: "Picture",
        key: "P",
        replaceWith: "[img][![Url]!][/img]",
        className: "picture"
    }, {
        name: "Link",
        key: "L",
        openWith: "[url=[![Url]!]]",
        closeWith: "[/url]",
        className: "linkbutton",
        placeHolder: "Your text to link here..."
    }, {
        name: "Youtube / Google Video",
        openWith: "[video=[![Enter URL to Google Or Yahoo Video Here]!]]",
        className: "youtubebutton"
    }, {
        name: "MP3 / Audio",
        openWith: "[audio][![Enter URL to Audio File Here]!]",
        closeWith: "[/audio]",
        className: "audiobutton"
    }, {
        name: "Email",
        openWith: "[email][![Enter Email Address Here]!]",
        closeWith: "[/email]",
        className: "emailbutton"
    }, {
        separator: " "
    }, {
        name: "Fonts",
        className: "fontsbutton",
        dropMenu: [ {
            name: "Oswald",
            showName: "Oswald",
            openWith: "[font01]",
            closeWith: "[/font01]",
            className: "text-1"
        }, {
            name: "PT Sans Narrow",
            showName: "PT Sans Narrow",
            openWith: "[font02]",
            closeWith: "[/font02]",
            className: "text-2"
        }, {
            name: "Nova Square",
            showName: "Nova Square",
            openWith: "[font03]",
            closeWith: "[/font03]",
            className: "text-3"
        }, {
            name: "Lobster",
            showName: "Lobster",
            openWith: "[font04]",
            closeWith: "[/font04]",
            className: "text-4"
        }, {
            name: "Open Sans",
            showName: "Open Sans",
            openWith: "[font05]",
            closeWith: "[/font05]",
            className: "text-5"
        }, {
            name: "Encode Sans Condensed",
            showName: "Encode Sans Condensed",
            openWith: "[font06]",
            closeWith: "[/font06]",
            className: "text-6"
        }, {
            name: "Baloo Bhaijaan",
            showName: "Baloo Bhaijaan",
            openWith: "[font07]",
            closeWith: "[/font07]",
            className: "text-7"
        }, {
            name: "Acme",
            showName: "Acme",
            openWith: "[font08]",
            closeWith: "[/font08]",
            className: "text-8"
        }, {
            name: "Arial",
            showName: "Arial",
            openWith: "[font=Arial]",
            closeWith: "[/font]",
            className: "font_7"
        }, {
            name: "Arial Black",
            showName: "Arial Black",
            openWith: "[font=Arial Black]",
            closeWith: "[/font]",
            className: "font_Arial"
        }, {
            name: "Comic Sans MS",
            showName: "Comic Sans MS",
            openWith: "[font=Comic Sans MS]",
            closeWith: "[/font]",
            className: "font_Comic_Sans_MS"
        }, {
            name: "Courier New",
            showName: "Courier New",
            openWith: "[font=Courier New]",
            closeWith: "[/font]",
            className: "font_Courier_New"
        }, {
            name: "Georgia",
            showName: "Georgia",
            openWith: "[font=Georgia]",
            closeWith: "[/font]",
            className: "font_Georgia"
        }, {
            name: "Impact",
            showName: "Impact",
            openWith: "[font=Impact]",
            closeWith: "[/font]",
            className: "font_Impact"
        }, {
            name: "Times New Roman",
            showName: "Times New Roman",
            openWith: "[font=Times New Roman]",
            closeWith: "[/font]",
            className: "font_Times_New_Roman"
        }, {
            name: "Trebuchet MS",
            showName: "Trebuchet MS",
            openWith: "[font=Trebuchet MS]",
            closeWith: "[/font]",
            className: "font_Trebuchet_MS"
        }, {
            name: "Verdana",
            showName: "Verdana",
            openWith: "[font=Verdana]",
            closeWith: "[/font]",
            className: "font_Verdana"
        }, {
            name: "Courier",
            showName: "Courier",
            openWith: "[font=Courier]",
            closeWith: "[/font]",
            className: "font_Courier"
        }, {
            name: "Helvetica",
            showName: "Helvetica",
            openWith: "[font=Helvetica]",
            closeWith: "[/font]",
            className: "font_Helvetica"
        }, {
            name: "Times",
            showName: "Times",
            openWith: "[font=Times]",
            closeWith: "[/font]",
            className: "font_Times"
        }, {
            name: "Andale Mono",
            showName: "Andale Mono",
            openWith: "[font=Andale Mono]",
            closeWith: "[/font]",
            className: "font_Andale_Mono"
        }, {
            name: "Bitstream Vera Sans",
            showName: "Bitstream Vera Sans",
            openWith: "[font=Bitstream Vera Sans]",
            closeWith: "[/font]",
            className: "font_Bitstream_Vera_Sans"
        }, {
            name: "Mono",
            showName: "Mono",
            openWith: "[font=Mono]",
            closeWith: "[/font]",
            className: "font_Mono"
        } ]
    }, {
        name: "Colors",
        className: "palette",
        openWith: "[color=[![Enter Hex or web-safe color, ie: #FF33FF or purple]!]]",
        closeWith: "[/color]",
        dropMenu: [ {
            name: "#330000",
            openWith: "[color=#330000]",
            closeWith: "[/color]",
            className: "col1-1"
        }, {
            name: "#333300",
            openWith: "[color=#333300]",
            closeWith: "[/color]",
            className: "col1-2"
        }, {
            name: "#336600",
            openWith: "[color=#336600]",
            closeWith: "[/color]",
            className: "col1-3"
        }, {
            name: "#339900",
            openWith: "[color=#339900]",
            closeWith: "[/color]",
            className: "col1-4"
        }, {
            name: "#33CC00",
            openWith: "[color=#33CC00]",
            closeWith: "[/color]",
            className: "col1-5"
        }, {
            name: "#33FF00",
            openWith: "[color=#33FF00]",
            closeWith: "[/color]",
            className: "col1-6"
        }, {
            name: "#66FF00",
            openWith: "[color=#66FF00]",
            closeWith: "[/color]",
            className: "col1-7"
        }, {
            name: "#66CC00",
            openWith: "[color=#66CC00]",
            closeWith: "[/color]",
            className: "col1-8"
        }, {
            name: "#669900",
            openWith: "[color=#669900]",
            closeWith: "[/color]",
            className: "col1-9"
        }, {
            name: "#666600",
            openWith: "[color=#666600]",
            closeWith: "[/color]",
            className: "col1-10"
        }, {
            name: "#663300",
            openWith: "[color=#663300]",
            closeWith: "[/color]",
            className: "col1-11"
        }, {
            name: "#660000",
            openWith: "[color=#660000]",
            closeWith: "[/color]",
            className: "col1-12"
        }, {
            name: "#FF0000",
            openWith: "[color=#FF0000]",
            closeWith: "[/color]",
            className: "col1-13"
        }, {
            name: "#FF3300",
            openWith: "[color=#FF3300]",
            closeWith: "[/color]",
            className: "col1-14"
        }, {
            name: "#FF6600",
            openWith: "[color=#FF6600]",
            closeWith: "[/color]",
            className: "col1-15"
        }, {
            name: "#FF9900",
            openWith: "[color=#FF9900]",
            closeWith: "[/color]",
            className: "col1-16"
        }, {
            name: "#FFCC00",
            openWith: "[color=#FFCC00]",
            closeWith: "[/color]",
            className: "col1-17"
        }, {
            name: "#FFFF00",
            openWith: "[color=#FFFF00]",
            closeWith: "[/color]",
            className: "col1-18"
        }, {
            name: "#330033",
            openWith: "[color=#330033]",
            closeWith: "[/color]",
            className: "col2-1"
        }, {
            name: "#333333",
            openWith: "[color=#333333]",
            closeWith: "[/color]",
            className: "col2-2"
        }, {
            name: "#336633",
            openWith: "[color=#336633]",
            closeWith: "[/color]",
            className: "col2-3"
        }, {
            name: "#339933",
            openWith: "[color=#339933]",
            closeWith: "[/color]",
            className: "col2-4"
        }, {
            name: "#33CC33",
            openWith: "[color=#33CC33]",
            closeWith: "[/color]",
            className: "col2-5"
        }, {
            name: "#33FF33",
            openWith: "[color=#33FF33]",
            closeWith: "[/color]",
            className: "col2-6"
        }, {
            name: "#66FF33",
            openWith: "[color=#66FF33]",
            closeWith: "[/color]",
            className: "col2-7"
        }, {
            name: "#66CC33",
            openWith: "[color=#66CC33]",
            closeWith: "[/color]",
            className: "col2-8"
        }, {
            name: "#669933",
            openWith: "[color=#669933]",
            closeWith: "[/color]",
            className: "col2-9"
        }, {
            name: "#666633",
            openWith: "[color=#666633]",
            closeWith: "[/color]",
            className: "col2-10"
        }, {
            name: "#663333",
            openWith: "[color=#663333]",
            closeWith: "[/color]",
            className: "col2-11"
        }, {
            name: "#660033",
            openWith: "[color=#660033]",
            closeWith: "[/color]",
            className: "col2-12"
        }, {
            name: "#FF0033",
            openWith: "[color=#FF0033]",
            closeWith: "[/color]",
            className: "col2-13"
        }, {
            name: "#FF3333",
            openWith: "[color=#FF3333]",
            closeWith: "[/color]",
            className: "col2-14"
        }, {
            name: "#FF6633",
            openWith: "[color=#FF6633]",
            closeWith: "[/color]",
            className: "col2-15"
        }, {
            name: "#FF9933",
            openWith: "[color=#FF9933]",
            closeWith: "[/color]",
            className: "col2-16"
        }, {
            name: "#FFCC33",
            openWith: "[color=#FFCC33]",
            closeWith: "[/color]",
            className: "col2-17"
        }, {
            name: "#FFFF33",
            openWith: "[color=#FFFF33]",
            closeWith: "[/color]",
            className: "col2-18"
        }, {
            name: "#330066",
            openWith: "[color=#330066]",
            closeWith: "[/color]",
            className: "col3-1"
        }, {
            name: "#333366",
            openWith: "[color=#333366]",
            closeWith: "[/color]",
            className: "col3-2"
        }, {
            name: "#336666",
            openWith: "[color=#336666]",
            closeWith: "[/color]",
            className: "col3-3"
        }, {
            name: "#339966",
            openWith: "[color=#339966]",
            closeWith: "[/color]",
            className: "col3-4"
        }, {
            name: "#33CC66",
            openWith: "[color=#33CC66]",
            closeWith: "[/color]",
            className: "col3-5"
        }, {
            name: "#33FF66",
            openWith: "[color=#33FF66]",
            closeWith: "[/color]",
            className: "col3-6"
        }, {
            name: "#66FF66",
            openWith: "[color=#66FF66]",
            closeWith: "[/color]",
            className: "col3-7"
        }, {
            name: "#66CC66",
            openWith: "[color=#66CC66]",
            closeWith: "[/color]",
            className: "col3-8"
        }, {
            name: "#669966",
            openWith: "[color=#669966]",
            closeWith: "[/color]",
            className: "col3-9"
        }, {
            name: "#666666",
            openWith: "[color=#666666]",
            closeWith: "[/color]",
            className: "col3-10"
        }, {
            name: "#663366",
            openWith: "[color=#663366]",
            closeWith: "[/color]",
            className: "col3-11"
        }, {
            name: "#660066",
            openWith: "[color=#660066]",
            closeWith: "[/color]",
            className: "col3-12"
        }, {
            name: "#FF0066",
            openWith: "[color=#FF0066]",
            closeWith: "[/color]",
            className: "col3-13"
        }, {
            name: "#FF3366",
            openWith: "[color=#FF3366]",
            closeWith: "[/color]",
            className: "col3-14"
        }, {
            name: "#FF6666",
            openWith: "[color=#FF6666]",
            closeWith: "[/color]",
            className: "col3-15"
        }, {
            name: "#FF9966",
            openWith: "[color=#FF9966]",
            closeWith: "[/color]",
            className: "col3-16"
        }, {
            name: "#FFCC66",
            openWith: "[color=#FFCC66]",
            closeWith: "[/color]",
            className: "col3-17"
        }, {
            name: "#FFFF66",
            openWith: "[color=#FFFF66]",
            closeWith: "[/color]",
            className: "col3-18"
        }, {
            name: "#330099",
            openWith: "[color=#330099]",
            closeWith: "[/color]",
            className: "col4-1"
        }, {
            name: "#333399",
            openWith: "[color=#333399]",
            closeWith: "[/color]",
            className: "col4-2"
        }, {
            name: "#336699",
            openWith: "[color=#336699]",
            closeWith: "[/color]",
            className: "col4-3"
        }, {
            name: "#339999",
            openWith: "[color=#339999]",
            closeWith: "[/color]",
            className: "col4-4"
        }, {
            name: "#33CC99",
            openWith: "[color=#33CC99]",
            closeWith: "[/color]",
            className: "col4-5"
        }, {
            name: "#33FF99",
            openWith: "[color=#33FF99]",
            closeWith: "[/color]",
            className: "col4-6"
        }, {
            name: "#66FF99",
            openWith: "[color=#66FF99]",
            closeWith: "[/color]",
            className: "col4-7"
        }, {
            name: "#66CC99",
            openWith: "[color=#66CC99]",
            closeWith: "[/color]",
            className: "col4-8"
        }, {
            name: "#669999",
            openWith: "[color=#669999]",
            closeWith: "[/color]",
            className: "col4-9"
        }, {
            name: "#666699",
            openWith: "[color=#666699]",
            closeWith: "[/color]",
            className: "col4-10"
        }, {
            name: "#663399",
            openWith: "[color=#663399]",
            closeWith: "[/color]",
            className: "col4-11"
        }, {
            name: "#660099",
            openWith: "[color=#660099]",
            closeWith: "[/color]",
            className: "col4-12"
        }, {
            name: "#FF0099",
            openWith: "[color=#FF0099]",
            closeWith: "[/color]",
            className: "col4-13"
        }, {
            name: "#FF3399",
            openWith: "[color=#FF3399]",
            closeWith: "[/color]",
            className: "col4-14"
        }, {
            name: "#FF6699",
            openWith: "[color=#FF6699]",
            closeWith: "[/color]",
            className: "col4-15"
        }, {
            name: "#FF9999",
            openWith: "[color=#FF9999]",
            closeWith: "[/color]",
            className: "col4-16"
        }, {
            name: "#FFCC99",
            openWith: "[color=#FFCC99]",
            closeWith: "[/color]",
            className: "col4-17"
        }, {
            name: "#FFFF99",
            openWith: "[color=#FFFF99]",
            closeWith: "[/color]",
            className: "col4-18"
        }, {
            name: "#3300CC",
            openWith: "[color=#3300CC]",
            closeWith: "[/color]",
            className: "col5-1"
        }, {
            name: "#3333CC",
            openWith: "[color=#3333CC]",
            closeWith: "[/color]",
            className: "col5-2"
        }, {
            name: "#3366CC",
            openWith: "[color=#3366CC]",
            closeWith: "[/color]",
            className: "col5-3"
        }, {
            name: "#3399CC",
            openWith: "[color=#3399CC]",
            closeWith: "[/color]",
            className: "col5-4"
        }, {
            name: "#33CCCC",
            openWith: "[color=#33CCCC]",
            closeWith: "[/color]",
            className: "col5-5"
        }, {
            name: "#33FFCC",
            openWith: "[color=#33FFCC]",
            closeWith: "[/color]",
            className: "col5-6"
        }, {
            name: "#66FFCC",
            openWith: "[color=#66FFCC]",
            closeWith: "[/color]",
            className: "col5-7"
        }, {
            name: "#66CCCC",
            openWith: "[color=#66CCCC]",
            closeWith: "[/color]",
            className: "col5-8"
        }, {
            name: "#6699CC",
            openWith: "[color=#6699CC]",
            closeWith: "[/color]",
            className: "col5-9"
        }, {
            name: "#6666CC",
            openWith: "[color=#6666CC]",
            closeWith: "[/color]",
            className: "col5-10"
        }, {
            name: "#6633CC",
            openWith: "[color=#6633CC]",
            closeWith: "[/color]",
            className: "col5-11"
        }, {
            name: "#6600CC",
            openWith: "[color=#6600CC]",
            closeWith: "[/color]",
            className: "col5-12"
        }, {
            name: "#FF00CC",
            openWith: "[color=#FF00CC]",
            closeWith: "[/color]",
            className: "col5-13"
        }, {
            name: "#FF33CC",
            openWith: "[color=#FF33CC]",
            closeWith: "[/color]",
            className: "col5-14"
        }, {
            name: "#FF66CC",
            openWith: "[color=#FF66CC]",
            closeWith: "[/color]",
            className: "col5-15"
        }, {
            name: "#FF99CC",
            openWith: "[color=#FF99CC]",
            closeWith: "[/color]",
            className: "col5-16"
        }, {
            name: "#FFCCCC",
            openWith: "[color=#FFCCCC]",
            closeWith: "[/color]",
            className: "col5-17"
        }, {
            name: "#FFFFCC",
            openWith: "[color=#FFFFCC]",
            closeWith: "[/color]",
            className: "col5-18"
        }, {
            name: "#3300FF",
            openWith: "[color=#3300FF]",
            closeWith: "[/color]",
            className: "col6-1"
        }, {
            name: "#3333FF",
            openWith: "[color=#3333FF]",
            closeWith: "[/color]",
            className: "col6-2"
        }, {
            name: "#3366FF",
            openWith: "[color=#3366FF]",
            closeWith: "[/color]",
            className: "col6-3"
        }, {
            name: "#3399FF",
            openWith: "[color=#3399FF]",
            closeWith: "[/color]",
            className: "col6-4"
        }, {
            name: "#33CCFF",
            openWith: "[color=#33CCFF]",
            closeWith: "[/color]",
            className: "col6-5"
        }, {
            name: "#33FFFF",
            openWith: "[color=#33FFFF]",
            closeWith: "[/color]",
            className: "col6-6"
        }, {
            name: "#66FFFF",
            openWith: "[color=#66FFFF]",
            closeWith: "[/color]",
            className: "col6-7"
        }, {
            name: "#66CCFF",
            openWith: "[color=#66CCFF]",
            closeWith: "[/color]",
            className: "col6-8"
        }, {
            name: "#6699FF",
            openWith: "[color=#6699FF]",
            closeWith: "[/color]",
            className: "col6-9"
        }, {
            name: "#6666FF",
            openWith: "[color=#6666FF]",
            closeWith: "[/color]",
            className: "col6-10"
        }, {
            name: "#6633FF",
            openWith: "[color=#6633FF]",
            closeWith: "[/color]",
            className: "col6-11"
        }, {
            name: "#6600FF",
            openWith: "[color=#6600FF]",
            closeWith: "[/color]",
            className: "col6-12"
        }, {
            name: "#FF00FF",
            openWith: "[color=#FF00FF]",
            closeWith: "[/color]",
            className: "col6-13"
        }, {
            name: "#FF33FF",
            openWith: "[color=#FF33FF]",
            closeWith: "[/color]",
            className: "col6-14"
        }, {
            name: "#FF66FF",
            openWith: "[color=#FF66FF]",
            closeWith: "[/color]",
            className: "col6-15"
        }, {
            name: "#FF99FF",
            openWith: "[color=#FF99FF]",
            closeWith: "[/color]",
            className: "col6-16"
        }, {
            name: "#FFCCFF",
            openWith: "[color=#FFCCFF]",
            closeWith: "[/color]",
            className: "col6-17"
        }, {
            name: "#FFFFFF",
            openWith: "[color=#FFFFFF]",
            closeWith: "[/color]",
            className: "col6-18"
        }, {
            name: "#0000FF",
            openWith: "[color=#0000FF]",
            closeWith: "[/color]",
            className: "col7-1"
        }, {
            name: "#0033FF",
            openWith: "[color=#0033FF]",
            closeWith: "[/color]",
            className: "col7-2"
        }, {
            name: "#0066FF",
            openWith: "[color=#0066FF]",
            closeWith: "[/color]",
            className: "col7-3"
        }, {
            name: "#0099FF",
            openWith: "[color=#0099FF]",
            closeWith: "[/color]",
            className: "col7-4"
        }, {
            name: "#00CCFF",
            openWith: "[color=#00CCFF]",
            closeWith: "[/color]",
            className: "col7-5"
        }, {
            name: "#00FFFF",
            openWith: "[color=#00FFFF]",
            closeWith: "[/color]",
            className: "col7-6"
        }, {
            name: "#99FFFF",
            openWith: "[color=#99FFFF]",
            closeWith: "[/color]",
            className: "col7-7"
        }, {
            name: "#99CCFF",
            openWith: "[color=#99CCFF]",
            closeWith: "[/color]",
            className: "col7-8"
        }, {
            name: "#9999FF",
            openWith: "[color=#9999FF]",
            closeWith: "[/color]",
            className: "col7-9"
        }, {
            name: "#9966FF",
            openWith: "[color=#9966FF]",
            closeWith: "[/color]",
            className: "col7-10"
        }, {
            name: "#9933FF",
            openWith: "[color=#9933FF]",
            closeWith: "[/color]",
            className: "col7-11"
        }, {
            name: "#9900FF",
            openWith: "[color=#9900FF]",
            closeWith: "[/color]",
            className: "col7-12"
        }, {
            name: "#CC00FF",
            openWith: "[color=#CC00FF]",
            closeWith: "[/color]",
            className: "col7-13"
        }, {
            name: "#CC33FF",
            openWith: "[color=#CC33FF]",
            closeWith: "[/color]",
            className: "col7-14"
        }, {
            name: "#CC66FF",
            openWith: "[color=#CC66FF]",
            closeWith: "[/color]",
            className: "col7-15"
        }, {
            name: "#CC99FF",
            openWith: "[color=#CC99FF]",
            closeWith: "[/color]",
            className: "col7-16"
        }, {
            name: "#CCCCFF",
            openWith: "[color=#CCCCFF]",
            closeWith: "[/color]",
            className: "col7-17"
        }, {
            name: "#CCFFFF",
            openWith: "[color=#CCFFFF]",
            closeWith: "[/color]",
            className: "col7-18"
        }, {
            name: "#0000CC",
            openWith: "[color=#0000CC]",
            closeWith: "[/color]",
            className: "col8-1"
        }, {
            name: "#0033CC",
            openWith: "[color=#0033CC]",
            closeWith: "[/color]",
            className: "col8-2"
        }, {
            name: "#0066CC",
            openWith: "[color=#0066CC]",
            closeWith: "[/color]",
            className: "col8-3"
        }, {
            name: "#0099CC",
            openWith: "[color=#0099CC]",
            closeWith: "[/color]",
            className: "col8-4"
        }, {
            name: "#00CCCC",
            openWith: "[color=#00CCCC]",
            closeWith: "[/color]",
            className: "col8-5"
        }, {
            name: "#00FFCC",
            openWith: "[color=#00FFCC]",
            closeWith: "[/color]",
            className: "col8-6"
        }, {
            name: "#99FFCC",
            openWith: "[color=#99FFCC]",
            closeWith: "[/color]",
            className: "col8-7"
        }, {
            name: "#99CCCC",
            openWith: "[color=#99CCCC]",
            closeWith: "[/color]",
            className: "col8-8"
        }, {
            name: "#9999CC",
            openWith: "[color=#9999CC]",
            closeWith: "[/color]",
            className: "col8-9"
        }, {
            name: "#9966CC",
            openWith: "[color=#9966CC]",
            closeWith: "[/color]",
            className: "col8-10"
        }, {
            name: "#9933CC",
            openWith: "[color=#9933CC]",
            closeWith: "[/color]",
            className: "col8-11"
        }, {
            name: "#9900CC",
            openWith: "[color=#9900CC]",
            closeWith: "[/color]",
            className: "col8-12"
        }, {
            name: "#CC00CC",
            openWith: "[color=#CC00CC]",
            closeWith: "[/color]",
            className: "col8-13"
        }, {
            name: "#CC33CC",
            openWith: "[color=#CC33CC]",
            closeWith: "[/color]",
            className: "col8-14"
        }, {
            name: "#CC66CC",
            openWith: "[color=#CC66CC]",
            closeWith: "[/color]",
            className: "col8-15"
        }, {
            name: "#CC99CC",
            openWith: "[color=#CC99CC]",
            closeWith: "[/color]",
            className: "col8-16"
        }, {
            name: "#CCCCCC",
            openWith: "[color=#CCCCCC]",
            closeWith: "[/color]",
            className: "col8-17"
        }, {
            name: "#CCFFCC",
            openWith: "[color=#CCFFCC]",
            closeWith: "[/color]",
            className: "col8-18"
        }, {
            name: "#000099",
            openWith: "[color=#000099]",
            closeWith: "[/color]",
            className: "col9-1"
        }, {
            name: "#003399",
            openWith: "[color=#003399]",
            closeWith: "[/color]",
            className: "col9-2"
        }, {
            name: "#006699",
            openWith: "[color=#006699]",
            closeWith: "[/color]",
            className: "col9-3"
        }, {
            name: "#009999",
            openWith: "[color=#009999]",
            closeWith: "[/color]",
            className: "col9-4"
        }, {
            name: "#00CC99",
            openWith: "[color=#00CC99]",
            closeWith: "[/color]",
            className: "col9-5"
        }, {
            name: "#00FF99",
            openWith: "[color=#00FF99]",
            closeWith: "[/color]",
            className: "col9-6"
        }, {
            name: "#99FF99",
            openWith: "[color=#99FF99]",
            closeWith: "[/color]",
            className: "col9-7"
        }, {
            name: "#99CC99",
            openWith: "[color=#99CC99]",
            closeWith: "[/color]",
            className: "col9-8"
        }, {
            name: "#999999",
            openWith: "[color=#999999]",
            closeWith: "[/color]",
            className: "col9-9"
        }, {
            name: "#996699",
            openWith: "[color=#996699]",
            closeWith: "[/color]",
            className: "col9-10"
        }, {
            name: "#993399",
            openWith: "[color=#993399]",
            closeWith: "[/color]",
            className: "col9-11"
        }, {
            name: "#990099",
            openWith: "[color=#990099]",
            closeWith: "[/color]",
            className: "col9-12"
        }, {
            name: "#CC0099",
            openWith: "[color=#CC0099]",
            closeWith: "[/color]",
            className: "col9-13"
        }, {
            name: "#CC3399",
            openWith: "[color=#CC3399]",
            closeWith: "[/color]",
            className: "col9-14"
        }, {
            name: "#CC6699",
            openWith: "[color=#CC6699]",
            closeWith: "[/color]",
            className: "col9-15"
        }, {
            name: "#CC9999",
            openWith: "[color=#CC9999]",
            closeWith: "[/color]",
            className: "col9-16"
        }, {
            name: "#CCCC99",
            openWith: "[color=#CCCC99]",
            closeWith: "[/color]",
            className: "col9-17"
        }, {
            name: "#CCFF99",
            openWith: "[color=#CCFF99]",
            closeWith: "[/color]",
            className: "col9-18"
        }, {
            name: "#000066",
            openWith: "[color=#000066]",
            closeWith: "[/color]",
            className: "col10-1"
        }, {
            name: "#003366",
            openWith: "[color=#003366]",
            closeWith: "[/color]",
            className: "col10-2"
        }, {
            name: "#006666",
            openWith: "[color=#006666]",
            closeWith: "[/color]",
            className: "col10-3"
        }, {
            name: "#009966",
            openWith: "[color=#009966]",
            closeWith: "[/color]",
            className: "col10-4"
        }, {
            name: "#00CC66",
            openWith: "[color=#00CC66]",
            closeWith: "[/color]",
            className: "col10-5"
        }, {
            name: "#00FF66",
            openWith: "[color=#00FF66]",
            closeWith: "[/color]",
            className: "col10-6"
        }, {
            name: "#99FF66",
            openWith: "[color=#99FF66]",
            closeWith: "[/color]",
            className: "col10-7"
        }, {
            name: "#99CC66",
            openWith: "[color=#99CC66]",
            closeWith: "[/color]",
            className: "col10-8"
        }, {
            name: "#999966",
            openWith: "[color=#999966]",
            closeWith: "[/color]",
            className: "col10-9"
        }, {
            name: "#996666",
            openWith: "[color=#996666]",
            closeWith: "[/color]",
            className: "col10-10"
        }, {
            name: "#993366",
            openWith: "[color=#993366]",
            closeWith: "[/color]",
            className: "col10-11"
        }, {
            name: "#990066",
            openWith: "[color=#990066]",
            closeWith: "[/color]",
            className: "col10-12"
        }, {
            name: "#CC0066",
            openWith: "[color=#CC0066]",
            closeWith: "[/color]",
            className: "col10-13"
        }, {
            name: "#CC3366",
            openWith: "[color=#CC336]",
            closeWith: "[/color]",
            className: "col10-14"
        }, {
            name: "#CC6666",
            openWith: "[color=#CC6666]",
            closeWith: "[/color]",
            className: "col10-15"
        }, {
            name: "#CC9966",
            openWith: "[color=#CC9966]",
            closeWith: "[/color]",
            className: "col10-16"
        }, {
            name: "#CCCC66",
            openWith: "[color=#CCCC66]",
            closeWith: "[/color]",
            className: "col10-17"
        }, {
            name: "#CCFF66",
            openWith: "[color=#CCFF66]",
            closeWith: "[/color]",
            className: "col10-18"
        }, {
            name: "#000033",
            openWith: "[color=#000033]",
            closeWith: "[/color]",
            className: "col11-1"
        }, {
            name: "#003333",
            openWith: "[color=#003333]",
            closeWith: "[/color]",
            className: "col11-2"
        }, {
            name: "#006633",
            openWith: "[color=#006633]",
            closeWith: "[/color]",
            className: "col11-3"
        }, {
            name: "#009933",
            openWith: "[color=#009933]",
            closeWith: "[/color]",
            className: "col11-4"
        }, {
            name: "#00CC33",
            openWith: "[color=#00CC33]",
            closeWith: "[/color]",
            className: "col11-5"
        }, {
            name: "#00FF33",
            openWith: "[color=#00FF33]",
            closeWith: "[/color]",
            className: "col11-6"
        }, {
            name: "#99FF33",
            openWith: "[color=#99FF33]",
            closeWith: "[/color]",
            className: "col11-7"
        }, {
            name: "#99CC33",
            openWith: "[color=#99CC33]",
            closeWith: "[/color]",
            className: "col11-8"
        }, {
            name: "#999933",
            openWith: "[color=#999933]",
            closeWith: "[/color]",
            className: "col11-9"
        }, {
            name: "#996633",
            openWith: "[color=#996633]",
            closeWith: "[/color]",
            className: "col11-10"
        }, {
            name: "#993333",
            openWith: "[color=#993333]",
            closeWith: "[/color]",
            className: "col11-11"
        }, {
            name: "#990033",
            openWith: "[color=#990033]",
            closeWith: "[/color]",
            className: "col11-12"
        }, {
            name: "#CC0033",
            openWith: "[color=#CC0033]",
            closeWith: "[/color]",
            className: "col11-13"
        }, {
            name: "#CC3333",
            openWith: "[color=#CC3333]",
            closeWith: "[/color]",
            className: "col11-14"
        }, {
            name: "#CC6633",
            openWith: "[color=#CC6633]",
            closeWith: "[/color]",
            className: "col11-15"
        }, {
            name: "#CC9933",
            openWith: "[color=#CC9933]",
            closeWith: "[/color]",
            className: "col11-16"
        }, {
            name: "#CCCC33",
            openWith: "[color=#CCCC33]",
            closeWith: "[/color]",
            className: "col11-17"
        }, {
            name: "#CCFF33",
            openWith: "[color=#CCFF33]",
            closeWith: "[/color]",
            className: "col11-18"
        }, {
            name: "#000000",
            openWith: "[color=#000000]",
            closeWith: "[/color]",
            className: "col12-1"
        }, {
            name: "#003300",
            openWith: "[color=#003300]",
            closeWith: "[/color]",
            className: "col12-2"
        }, {
            name: "#006600",
            openWith: "[color=#006600]",
            closeWith: "[/color]",
            className: "col12-3"
        }, {
            name: "#009900",
            openWith: "[color=#009900]",
            closeWith: "[/color]",
            className: "col12-4"
        }, {
            name: "#00CC00",
            openWith: "[color=#00CC00]",
            closeWith: "[/color]",
            className: "col12-5"
        }, {
            name: "#00FF00",
            openWith: "[color=#00FF00]",
            closeWith: "[/color]",
            className: "col12-6"
        }, {
            name: "#99FF00",
            openWith: "[color=#99FF00]",
            closeWith: "[/color]",
            className: "col12-7"
        }, {
            name: "#99CC00",
            openWith: "[color=#99CC00]",
            closeWith: "[/color]",
            className: "col12-8"
        }, {
            name: "#999900",
            openWith: "[color=#999900]",
            closeWith: "[/color]",
            className: "col12-9"
        }, {
            name: "#996600",
            openWith: "[color=#996600]",
            closeWith: "[/color]",
            className: "col12-10"
        }, {
            name: "#993300",
            openWith: "[color=#993300]",
            closeWith: "[/color]",
            className: "col12-11"
        }, {
            name: "#990000",
            openWith: "[color=#990000]",
            closeWith: "[/color]",
            className: "col12-12"
        }, {
            name: "#CC0000",
            openWith: "[color=#CC0000]",
            closeWith: "[/color]",
            className: "col12-13"
        }, {
            name: "#CC3300",
            openWith: "[color=#CC3300]",
            closeWith: "[/color]",
            className: "col12-14"
        }, {
            name: "#CC6600",
            openWith: "[color=#CC6600]",
            closeWith: "[/color]",
            className: "col12-15"
        }, {
            name: "#CC9900",
            openWith: "[color=#CC9900]",
            closeWith: "[/color]",
            className: "col12-16"
        }, {
            name: "#CCCC00",
            openWith: "[color=#CCCC00]",
            closeWith: "[/color]",
            className: "col12-17"
        }, {
            name: "#CCFF00",
            openWith: "[color=#CCFF00]",
            closeWith: "[/color]",
            className: "col12-18"
        }, {
            name: "#000000",
            openWith: "[color=#000000]",
            closeWith: "[/color]",
            className: "col13-1"
        }, {
            name: "#000000",
            openWith: "[color=#000000]",
            closeWith: "[/color]",
            className: "col13-1"
        }, {
            name: "#000000",
            openWith: "[color=#000000]",
            closeWith: "[/color]",
            className: "col13-1"
        }, {
            name: "#111111",
            openWith: "[color=#111111]",
            closeWith: "[/color]",
            className: "col13-2"
        }, {
            name: "#222222",
            openWith: "[color=#222222]",
            closeWith: "[/color]",
            className: "col13-3"
        }, {
            name: "#333333",
            openWith: "[color=#333333]",
            closeWith: "[/color]",
            className: "col13-4"
        }, {
            name: "#444444",
            openWith: "[color=#444444]",
            closeWith: "[/color]",
            className: "col13-5"
        }, {
            name: "#555555",
            openWith: "[color=#555555]",
            closeWith: "[/color]",
            className: "col13-6"
        }, {
            name: "#666666",
            openWith: "[color=#666666]",
            closeWith: "[/color]",
            className: "col13-7"
        }, {
            name: "#777777",
            openWith: "[color=#777777]",
            closeWith: "[/color]",
            className: "col13-8"
        }, {
            name: "#888888",
            openWith: "[color=#888888]",
            closeWith: "[/color]",
            className: "col13-9"
        }, {
            name: "#999999",
            openWith: "[color=#999999]",
            closeWith: "[/color]",
            className: "col13-10"
        }, {
            name: "#AAAAAA",
            openWith: "[color=#AAAAAA]",
            closeWith: "[/color]",
            className: "col13-11"
        }, {
            name: "#BBBBBB",
            openWith: "[color=#BBBBBB]",
            closeWith: "[/color]",
            className: "col13-12"
        }, {
            name: "#CCCCCC",
            openWith: "[color=#CCCCCC]",
            closeWith: "[/color]",
            className: "col13-13"
        }, {
            name: "#DDDDDD",
            openWith: "[color=#DDDDDD]",
            closeWith: "[/color]",
            className: "col13-14"
        }, {
            name: "#EEEEEE",
            openWith: "[color=#EEEEEE]",
            closeWith: "[/color]",
            className: "col13-15"
        }, {
            name: "#FFFFFF",
            openWith: "[color=#FFFFFF]",
            closeWith: "[/color]",
            className: "col13-16"
        } ]
    }, {
        name: "Size",
        key: "S",
        openWith: "[size=[![Text size]!]]",
        closeWith: "[/size]",
        className: "sizebutton",
        dropMenu: [ {
            name: "xx-large",
            showName: "xx-large",
            openWith: "[size=7]",
            closeWith: "[/size]",
            className: "size_7"
        }, {
            name: "x-large",
            showName: "x-large",
            openWith: "[size=6]",
            closeWith: "[/size]",
            className: "size_6"
        }, {
            name: "large",
            showName: "large",
            openWith: "[size=5]",
            closeWith: "[/size]",
            className: "size_5"
        }, {
            name: "medium",
            showName: "medium",
            openWith: "[size=4]",
            closeWith: "[/size]",
            className: "size_4"
        }, {
            name: "small",
            showName: "small",
            openWith: "[size=3]",
            closeWith: "[/size]",
            className: "size_3"
        }, {
            name: "x-small",
            showName: "x-small",
            openWith: "[size=2]",
            closeWith: "[/size]",
            className: "size_2"
        }, {
            name: "xx-small",
            showName: "xx-small",
            openWith: "[size=1]",
            closeWith: "[/size]",
            className: "size_1"
        } ]
    }, {
        separator: " "
    }, {
        name: "Unordered list",
        openWith: "[list]\n",
        closeWith: "[/list]",
        className: "list_bullet"
    }, {
        name: "Ordered list",
        openWith: "[list=[![Starting number]!]]\n",
        closeWith: "\n[/list]",
        className: "list_numeric"
    }, {
        name: "List item",
        openWith: "[*] ",
        className: "list_item"
    }, {
        separator: " "
    }, {
        name: "Align Left",
        openWith: "[left]",
        closeWith: "[/left]",
        className: "align-left"
    }, {
        name: "Align Center",
        openWith: "[center]",
        closeWith: "[/center]",
        className: "align-center"
    }, {
        name: "Align Right",
        openWith: "[right]",
        closeWith: "[/right]",
        className: "align-right"
    }, {
        name: "Justify",
        openWith: "[justify]",
        closeWith: "[/justify]",
        className: "align-justify"
    }, {
        separator: " "
    }, {
        name: "Blockquote",
        openWith: "[blockquote]",
        closeWith: "[/blockquote]",
        className: "blockquotebutton"
    }, {
        name: "Quotes",
        key: "Q",
        openWith: "[quote]",
        closeWith: "[/quote]",
        className: "quotebutton"
    }, {
        name: "Code",
        key: "K",
        openWith: "[code]",
        closeWith: "[/code]",
        className: "codebutton"
    }, {
        name: "Marquee",
        openWith: "[marquee]",
        closeWith: "[/marquee]",
        className: "marqueebutton"
    }, {
        name: "Spoiler",
        openWith: "[spoiler]",
        closeWith: "[/spoiler]",
        className: "spoilerbutton"
    }, {
        separator: " "
    }, {
        name: "Table generator",
        className: "tablegenerator",
        placeholder: "Your text here...",
        replaceWith: function(h) {
            var cols = prompt("How many cols?"), rows = prompt("How many rows?"), thead = prompt("Is first row a table header? (yes or no)"), html = "[table]\n";
            if (thead == "yes") {
                for (var c = 0; c < cols; c++) {
                    html += "\t[th] [![TH" + (c + 1) + " text:]!][/th]\n";
                }
            }
            for (var r = 0; r < rows; r++) {
                html += "\t[tr]\n";
                for (var c = 0; c < cols; c++) {
                    html += "\t\t[td]" + (h.placeholder || "") + "[/td]\n";
                }
                html += "\t[/tr]\n";
            }
            html += "[/table]";
            return html;
        }
    }, {
        separator: " "
    }, {
        name: "Remove Formatting from Selected Text",
        className: "clean",
        replaceWith: function(h) {
            return h.selection.replace(/\[(.*?)\]/g, "");
        }
    }, {
        name: "Preview",
        key: "!",
        className: "preview",
        call: "preview"
    } ]
};

$(document).ready(function() {
    $("#box_1").hide();
    $("#box_2").hide();
    $("#box_3").hide();
    $("#box_4").hide();
    $("#box_1").fadeIn("slow");
    $("a#smilies").click(function() {
        $("#box_1").show("slow");
        $("#box_2").hide();
        $("#box_3").hide();
        $("#box_4").hide();
    });
    $("a#custom").click(function() {
        $("#box_1").hide();
        $("#box_2").show("slow");
        $("#box_3").hide();
        $("#box_4").hide();
    });
    $("a#staff").click(function() {
        $("#box_1").hide();
        $("#box_2").hide();
        $("#box_3").show("slow");
        $("#box_4").hide();
    });
    if ($("#bbcode_editor").length) {
        $("#bbcode_editor").markItUp(myBbcodeSettings);
    }
    $(".emoticons a").click(function() {
        emoticon = $(this).attr("alt");
        $.markItUp({
            openWith: emoticon
        });
        return false;
    });
    $("#tool_open").click(function() {
        $("#tools").slideToggle("slow", function() {});
        $("#tool_open").hide();
        $("#tool_close").show();
    });
    $("#tool_close").click(function() {
        $("#tools").slideToggle("slow", function() {});
        $("#tool_close").hide();
        $("#tool_open").show();
    });
    $("#more").click(function() {
        $("#attach_more").slideToggle("slow", function() {});
    });
});

(function(root, factory) {
    if (typeof define === "function" && define.amd) {
        define([ "jquery" ], factory);
    } else if (typeof exports === "object") {
        module.exports = factory(require("jquery"));
    } else {
        root.lightbox = factory(root.jQuery);
    }
})(this, function($) {
    function Lightbox(options) {
        this.album = [];
        this.currentImageIndex = void 0;
        this.init();
        this.options = $.extend({}, this.constructor.defaults);
        this.option(options);
    }
    Lightbox.defaults = {
        albumLabel: "Image %1 of %2",
        alwaysShowNavOnTouchDevices: false,
        fadeDuration: 600,
        fitImagesInViewport: true,
        imageFadeDuration: 600,
        positionFromTop: 50,
        resizeDuration: 700,
        showImageNumberLabel: true,
        wrapAround: false,
        disableScrolling: false,
        sanitizeTitle: false
    };
    Lightbox.prototype.option = function(options) {
        $.extend(this.options, options);
    };
    Lightbox.prototype.imageCountLabel = function(currentImageNum, totalImages) {
        return this.options.albumLabel.replace(/%1/g, currentImageNum).replace(/%2/g, totalImages);
    };
    Lightbox.prototype.init = function() {
        var self = this;
        $(document).ready(function() {
            self.enable();
            self.build();
        });
    };
    Lightbox.prototype.enable = function() {
        var self = this;
        $("body").on("click", "a[rel^=lightbox], area[rel^=lightbox], a[data-lightbox], area[data-lightbox]", function(event) {
            self.start($(event.currentTarget));
            return false;
        });
    };
    Lightbox.prototype.build = function() {
        if ($("#lightbox").length > 0) {
            return;
        }
        var self = this;
        $('<div id="lightboxOverlay" class="lightboxOverlay"></div><div id="lightbox" class="lightbox"><div class="lb-outerContainer"><div class="lb-container"><img class="lb-image" src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" /><div class="lb-nav"><a class="lb-prev" href="" ></a><a class="lb-next" href="" ></a></div><div class="lb-loader"><a class="lb-cancel"></a></div></div></div><div class="lb-dataContainer"><div class="lb-data"><div class="lb-details"><span class="lb-caption"></span><span class="lb-number"></span></div><div class="lb-closeContainer"><a class="lb-close"></a></div></div></div></div>').appendTo($("body"));
        this.$lightbox = $("#lightbox");
        this.$overlay = $("#lightboxOverlay");
        this.$outerContainer = this.$lightbox.find(".lb-outerContainer");
        this.$container = this.$lightbox.find(".lb-container");
        this.$image = this.$lightbox.find(".lb-image");
        this.$nav = this.$lightbox.find(".lb-nav");
        this.containerPadding = {
            top: parseInt(this.$container.css("padding-top"), 10),
            right: parseInt(this.$container.css("padding-right"), 10),
            bottom: parseInt(this.$container.css("padding-bottom"), 10),
            left: parseInt(this.$container.css("padding-left"), 10)
        };
        this.imageBorderWidth = {
            top: parseInt(this.$image.css("border-top-width"), 10),
            right: parseInt(this.$image.css("border-right-width"), 10),
            bottom: parseInt(this.$image.css("border-bottom-width"), 10),
            left: parseInt(this.$image.css("border-left-width"), 10)
        };
        this.$overlay.hide().on("click", function() {
            self.end();
            return false;
        });
        this.$lightbox.hide().on("click", function(event) {
            if ($(event.target).attr("id") === "lightbox") {
                self.end();
            }
            return false;
        });
        this.$outerContainer.on("click", function(event) {
            if ($(event.target).attr("id") === "lightbox") {
                self.end();
            }
            return false;
        });
        this.$lightbox.find(".lb-prev").on("click", function() {
            if (self.currentImageIndex === 0) {
                self.changeImage(self.album.length - 1);
            } else {
                self.changeImage(self.currentImageIndex - 1);
            }
            return false;
        });
        this.$lightbox.find(".lb-next").on("click", function() {
            if (self.currentImageIndex === self.album.length - 1) {
                self.changeImage(0);
            } else {
                self.changeImage(self.currentImageIndex + 1);
            }
            return false;
        });
        this.$nav.on("mousedown", function(event) {
            if (event.which === 3) {
                self.$nav.css("pointer-events", "none");
                self.$lightbox.one("contextmenu", function() {
                    setTimeout(function() {
                        this.$nav.css("pointer-events", "auto");
                    }.bind(self), 0);
                });
            }
        });
        this.$lightbox.find(".lb-loader, .lb-close").on("click", function() {
            self.end();
            return false;
        });
    };
    Lightbox.prototype.start = function($link) {
        var self = this;
        var $window = $(window);
        $window.on("resize", $.proxy(this.sizeOverlay, this));
        $("select, object, embed").css({
            visibility: "hidden"
        });
        this.sizeOverlay();
        this.album = [];
        var imageNumber = 0;
        function addToAlbum($link) {
            self.album.push({
                alt: $link.attr("data-alt"),
                link: $link.attr("href"),
                title: $link.attr("data-title") || $link.attr("title")
            });
        }
        var dataLightboxValue = $link.attr("data-lightbox");
        var $links;
        if (dataLightboxValue) {
            $links = $($link.prop("tagName") + '[data-lightbox="' + dataLightboxValue + '"]');
            for (var i = 0; i < $links.length; i = ++i) {
                addToAlbum($($links[i]));
                if ($links[i] === $link[0]) {
                    imageNumber = i;
                }
            }
        } else {
            if ($link.attr("rel") === "lightbox") {
                addToAlbum($link);
            } else {
                $links = $($link.prop("tagName") + '[rel="' + $link.attr("rel") + '"]');
                for (var j = 0; j < $links.length; j = ++j) {
                    addToAlbum($($links[j]));
                    if ($links[j] === $link[0]) {
                        imageNumber = j;
                    }
                }
            }
        }
        var top = $window.scrollTop() + this.options.positionFromTop;
        var left = $window.scrollLeft();
        this.$lightbox.css({
            top: top + "px",
            left: left + "px"
        }).fadeIn(this.options.fadeDuration);
        if (this.options.disableScrolling) {
            $("html").addClass("lb-disable-scrolling");
        }
        this.changeImage(imageNumber);
    };
    Lightbox.prototype.changeImage = function(imageNumber) {
        var self = this;
        this.disableKeyboardNav();
        var $image = this.$lightbox.find(".lb-image");
        this.$overlay.fadeIn(this.options.fadeDuration);
        $(".lb-loader").fadeIn("slow");
        this.$lightbox.find(".lb-image, .lb-nav, .lb-prev, .lb-next, .lb-dataContainer, .lb-numbers, .lb-caption").hide();
        this.$outerContainer.addClass("animating");
        var preloader = new Image();
        preloader.onload = function() {
            var $preloader;
            var imageHeight;
            var imageWidth;
            var maxImageHeight;
            var maxImageWidth;
            var windowHeight;
            var windowWidth;
            $image.attr({
                alt: self.album[imageNumber].alt,
                src: self.album[imageNumber].link
            });
            $preloader = $(preloader);
            $image.width(preloader.width);
            $image.height(preloader.height);
            if (self.options.fitImagesInViewport) {
                windowWidth = $(window).width();
                windowHeight = $(window).height();
                maxImageWidth = windowWidth - self.containerPadding.left - self.containerPadding.right - self.imageBorderWidth.left - self.imageBorderWidth.right - 20;
                maxImageHeight = windowHeight - self.containerPadding.top - self.containerPadding.bottom - self.imageBorderWidth.top - self.imageBorderWidth.bottom - 120;
                if (self.options.maxWidth && self.options.maxWidth < maxImageWidth) {
                    maxImageWidth = self.options.maxWidth;
                }
                if (self.options.maxHeight && self.options.maxHeight < maxImageWidth) {
                    maxImageHeight = self.options.maxHeight;
                }
                if (preloader.width > maxImageWidth || preloader.height > maxImageHeight) {
                    if (preloader.width / maxImageWidth > preloader.height / maxImageHeight) {
                        imageWidth = maxImageWidth;
                        imageHeight = parseInt(preloader.height / (preloader.width / imageWidth), 10);
                        $image.width(imageWidth);
                        $image.height(imageHeight);
                    } else {
                        imageHeight = maxImageHeight;
                        imageWidth = parseInt(preloader.width / (preloader.height / imageHeight), 10);
                        $image.width(imageWidth);
                        $image.height(imageHeight);
                    }
                }
            }
            self.sizeContainer($image.width(), $image.height());
        };
        preloader.src = this.album[imageNumber].link;
        this.currentImageIndex = imageNumber;
    };
    Lightbox.prototype.sizeOverlay = function() {
        this.$overlay.width($(document).width()).height($(document).height());
    };
    Lightbox.prototype.sizeContainer = function(imageWidth, imageHeight) {
        var self = this;
        var oldWidth = this.$outerContainer.outerWidth();
        var oldHeight = this.$outerContainer.outerHeight();
        var newWidth = imageWidth + this.containerPadding.left + this.containerPadding.right + this.imageBorderWidth.left + this.imageBorderWidth.right;
        var newHeight = imageHeight + this.containerPadding.top + this.containerPadding.bottom + this.imageBorderWidth.top + this.imageBorderWidth.bottom;
        function postResize() {
            self.$lightbox.find(".lb-dataContainer").width(newWidth);
            self.$lightbox.find(".lb-prevLink").height(newHeight);
            self.$lightbox.find(".lb-nextLink").height(newHeight);
            self.showImage();
        }
        if (oldWidth !== newWidth || oldHeight !== newHeight) {
            this.$outerContainer.animate({
                width: newWidth,
                height: newHeight
            }, this.options.resizeDuration, "swing", function() {
                postResize();
            });
        } else {
            postResize();
        }
    };
    Lightbox.prototype.showImage = function() {
        this.$lightbox.find(".lb-loader").stop(true).hide();
        this.$lightbox.find(".lb-image").fadeIn(this.options.imageFadeDuration);
        this.updateNav();
        this.updateDetails();
        this.preloadNeighboringImages();
        this.enableKeyboardNav();
    };
    Lightbox.prototype.updateNav = function() {
        var alwaysShowNav = false;
        try {
            document.createEvent("TouchEvent");
            alwaysShowNav = this.options.alwaysShowNavOnTouchDevices ? true : false;
        } catch (e) {}
        this.$lightbox.find(".lb-nav").show();
        if (this.album.length > 1) {
            if (this.options.wrapAround) {
                if (alwaysShowNav) {
                    this.$lightbox.find(".lb-prev, .lb-next").css("opacity", "1");
                }
                this.$lightbox.find(".lb-prev, .lb-next").show();
            } else {
                if (this.currentImageIndex > 0) {
                    this.$lightbox.find(".lb-prev").show();
                    if (alwaysShowNav) {
                        this.$lightbox.find(".lb-prev").css("opacity", "1");
                    }
                }
                if (this.currentImageIndex < this.album.length - 1) {
                    this.$lightbox.find(".lb-next").show();
                    if (alwaysShowNav) {
                        this.$lightbox.find(".lb-next").css("opacity", "1");
                    }
                }
            }
        }
    };
    Lightbox.prototype.updateDetails = function() {
        var self = this;
        if (typeof this.album[this.currentImageIndex].title !== "undefined" && this.album[this.currentImageIndex].title !== "") {
            var $caption = this.$lightbox.find(".lb-caption");
            if (this.options.sanitizeTitle) {
                $caption.text(this.album[this.currentImageIndex].title);
            } else {
                $caption.html(this.album[this.currentImageIndex].title);
            }
            $caption.fadeIn("fast").find("a").on("click", function(event) {
                if ($(this).attr("target") !== undefined) {
                    window.open($(this).attr("href"), $(this).attr("target"));
                } else {
                    location.href = $(this).attr("href");
                }
            });
        }
        if (this.album.length > 1 && this.options.showImageNumberLabel) {
            var labelText = this.imageCountLabel(this.currentImageIndex + 1, this.album.length);
            this.$lightbox.find(".lb-number").text(labelText).fadeIn("fast");
        } else {
            this.$lightbox.find(".lb-number").hide();
        }
        this.$outerContainer.removeClass("animating");
        this.$lightbox.find(".lb-dataContainer").fadeIn(this.options.resizeDuration, function() {
            return self.sizeOverlay();
        });
    };
    Lightbox.prototype.preloadNeighboringImages = function() {
        if (this.album.length > this.currentImageIndex + 1) {
            var preloadNext = new Image();
            preloadNext.src = this.album[this.currentImageIndex + 1].link;
        }
        if (this.currentImageIndex > 0) {
            var preloadPrev = new Image();
            preloadPrev.src = this.album[this.currentImageIndex - 1].link;
        }
    };
    Lightbox.prototype.enableKeyboardNav = function() {
        $(document).on("keyup.keyboard", $.proxy(this.keyboardAction, this));
    };
    Lightbox.prototype.disableKeyboardNav = function() {
        $(document).off(".keyboard");
    };
    Lightbox.prototype.keyboardAction = function(event) {
        var KEYCODE_ESC = 27;
        var KEYCODE_LEFTARROW = 37;
        var KEYCODE_RIGHTARROW = 39;
        var keycode = event.keyCode;
        var key = String.fromCharCode(keycode).toLowerCase();
        if (keycode === KEYCODE_ESC || key.match(/x|o|c/)) {
            this.end();
        } else if (key === "p" || keycode === KEYCODE_LEFTARROW) {
            if (this.currentImageIndex !== 0) {
                this.changeImage(this.currentImageIndex - 1);
            } else if (this.options.wrapAround && this.album.length > 1) {
                this.changeImage(this.album.length - 1);
            }
        } else if (key === "n" || keycode === KEYCODE_RIGHTARROW) {
            if (this.currentImageIndex !== this.album.length - 1) {
                this.changeImage(this.currentImageIndex + 1);
            } else if (this.options.wrapAround && this.album.length > 1) {
                this.changeImage(0);
            }
        }
    };
    Lightbox.prototype.end = function() {
        this.disableKeyboardNav();
        $(window).off("resize", this.sizeOverlay);
        this.$lightbox.fadeOut(this.options.fadeDuration);
        this.$overlay.fadeOut(this.options.fadeDuration);
        $("select, object, embed").css({
            visibility: "visible"
        });
        if (this.options.disableScrolling) {
            $("html").removeClass("lb-disable-scrolling");
        }
    };
    return new Lightbox();
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

var animate_duration = 1e3;

var animation = "fade";

$(function() {
    $(".tooltipper").tooltipster({
        theme: "tooltipster-borderless",
        side: "top",
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
        side: "top",
        interactive: true,
        animation: animation,
        animationDuration: animate_duration,
        arrow: true,
        contentAsHTML: true,
        maxWidth: 500,
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
        }
    });
    $(".dt-tooltipper-small").tooltipster({
        theme: "tooltipster-borderless",
        side: "top",
        interactive: false,
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

var v_offset = 250;

var animate_duration = 1e3;

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

$(".delete").on("click", function() {
    $(this).parent().slideUp(animate_duration, function() {
        $(this).remove();
    });
});

function SmileIT(smile, form, text) {
    document.forms[form].elements[text].value = document.forms[form].elements[text].value + " " + smile + " ";
    document.forms[form].elements[text].focus();
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
        if ($(this).scrollTop() > v_offset) {
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
    if ($(".notification").length) {
        setTimeout(function() {
            $(".notification").slideUp(animate_duration, function() {
                $(".notification").remove();
            });
        }, 15e3);
    }
    if ($("#accordion").length) {
        $("#accordion").find(".accordion-toggle").click(function() {
            $(this).next().slideToggle(animate_duration);
            $(".accordion-content").not($(this).next()).slideUp(animate_duration);
        });
    }
    $("a[href^=\\#]:not([href=\\#])").click(function(e) {
        if (location.pathname.replace(/^\//, "") == this.pathname.replace(/^\//, "") || location.hostname == this.hostname) {
            e.preventDefault();
            var headerHeight = $("#navbar").outerHeight() + 10;
            var target = $(this).attr("href");
            var scrollToPosition = $(target).offset().top - headerHeight;
            $("html, body").animate({
                scrollTop: scrollToPosition
            }, animate_duration, function() {
                window.location.hash = "" + target;
                $("html, body").animate({
                    scrollTop: scrollToPosition
                }, 0);
            });
        }
    });
    if (window.location.hash) {
        var headerHeight = $("#navbar").outerHeight() + 10;
        var target = $(window.location.hash);
        var scrollToPosition = $(target).offset().top - headerHeight;
        $("html, body").animate({
            scrollTop: scrollToPosition
        }, animate_duration, "swing");
    }
});