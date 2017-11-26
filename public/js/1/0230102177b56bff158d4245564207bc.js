function show_thanks(tid) {
    var holder = $("#thanks_holder");
    holder.html("Loading ...").fadeIn("slow");
    $.post("./ajax/thanks.php", {
        action: "list",
        ajax: 1,
        torrentid: tid
    }, function(r) {
        if (r.status) {
            if (!r.hadTh) r.list += "<br><input type='button is-primary is-small' value='Say thanks' onclick=\"say_thanks(" + tid + ")\" id='thanks_button' />";
            holder.empty().html(r.list);
        }
    }, "json");
}

function say_thanks(tid) {
    $("#thanks_button").attr("value", "Please wait...").attr("disabled", "disabled");
    var holder = $("#thanks_holder");
    $.post("./ajax/thanks.php", {
        action: "add",
        ajax: 1,
        torrentid: tid
    }, function(r) {
        if (r.status) holder.empty().html(r.list); else alert(r.err);
    }, "json");
}

function sack(file) {
    this.xmlhttp = null;
    this.resetData = function() {
        this.method = "POST";
        this.queryStringSeparator = "?";
        this.argumentSeparator = "&";
        this.URLString = "";
        this.encodeURIString = true;
        this.execute = false;
        this.element = null;
        this.elementObj = null;
        this.requestFile = file;
        this.vars = new Object();
        this.responseStatus = new Array(2);
    };
    this.resetFunctions = function() {
        this.onLoading = function() {};
        this.onLoaded = function() {};
        this.onInteractive = function() {};
        this.onCompletion = function() {};
        this.onError = function() {};
        this.onFail = function() {};
    };
    this.reset = function() {
        this.resetFunctions();
        this.resetData();
    };
    this.createAJAX = function() {
        try {
            this.xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e1) {
            try {
                this.xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (e2) {
                this.xmlhttp = null;
            }
        }
        if (!this.xmlhttp) {
            if (typeof XMLHttpRequest != "undefined") {
                this.xmlhttp = new XMLHttpRequest();
            } else {
                this.failed = true;
            }
        }
    };
    this.setVar = function(name, value) {
        this.vars[name] = Array(value, false);
    };
    this.encVar = function(name, value, returnvars) {
        if (true == returnvars) {
            return Array(encodeURIComponent(name), encodeURIComponent(value));
        } else {
            this.vars[encodeURIComponent(name)] = Array(encodeURIComponent(value), true);
        }
    };
    this.processURLString = function(string, encode) {
        encoded = encodeURIComponent(this.argumentSeparator);
        regexp = new RegExp(this.argumentSeparator + "|" + encoded);
        varArray = string.split(regexp);
        for (i = 0; i < varArray.length; i++) {
            urlVars = varArray[i].split("=");
            if (true == encode) {
                this.encVar(urlVars[0], urlVars[1]);
            } else {
                this.setVar(urlVars[0], urlVars[1]);
            }
        }
    };
    this.createURLString = function(urlstring) {
        if (this.encodeURIString && this.URLString.length) {
            this.processURLString(this.URLString, true);
        }
        if (urlstring) {
            if (this.URLString.length) {
                this.URLString += this.argumentSeparator + urlstring;
            } else {
                this.URLString = urlstring;
            }
        }
        this.setVar("rndval", new Date().getTime());
        urlstringtemp = new Array();
        for (key in this.vars) {
            if (false == this.vars[key][1] && true == this.encodeURIString) {
                encoded = this.encVar(key, this.vars[key][0], true);
                delete this.vars[key];
                this.vars[encoded[0]] = Array(encoded[1], true);
                key = encoded[0];
            }
            urlstringtemp[urlstringtemp.length] = key + "=" + this.vars[key][0];
        }
        if (urlstring) {
            this.URLString += this.argumentSeparator + urlstringtemp.join(this.argumentSeparator);
        } else {
            this.URLString += urlstringtemp.join(this.argumentSeparator);
        }
    };
    this.runResponse = function() {
        eval(this.response);
    };
    this.runAJAX = function(urlstring) {
        if (this.failed) {
            this.onFail();
        } else {
            this.createURLString(urlstring);
            if (this.element) {
                this.elementObj = document.getElementById(this.element);
            }
            if (this.xmlhttp) {
                var self = this;
                if (this.method == "GET") {
                    totalurlstring = this.requestFile + this.queryStringSeparator + this.URLString;
                    this.xmlhttp.open(this.method, totalurlstring, true);
                } else {
                    this.xmlhttp.open(this.method, this.requestFile, true);
                    try {
                        this.xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    } catch (e) {}
                }
                this.xmlhttp.onreadystatechange = function() {
                    switch (self.xmlhttp.readyState) {
                      case 1:
                        self.onLoading();
                        break;

                      case 2:
                        self.onLoaded();
                        break;

                      case 3:
                        self.onInteractive();
                        break;

                      case 4:
                        self.response = self.xmlhttp.responseText;
                        self.responseXML = self.xmlhttp.responseXML;
                        self.responseStatus[0] = self.xmlhttp.status;
                        self.responseStatus[1] = self.xmlhttp.statusText;
                        if (self.execute) {
                            self.runResponse();
                        }
                        if (self.elementObj) {
                            elemNodeName = self.elementObj.nodeName;
                            elemNodeName.toLowerCase();
                            if (elemNodeName == "input" || elemNodeName == "select" || elemNodeName == "option" || elemNodeName == "textarea") {
                                self.elementObj.value = self.response;
                            } else {
                                self.elementObj.innerHTML = self.response;
                            }
                        }
                        if (self.responseStatus[0] == "200") {
                            self.onCompletion();
                        } else {
                            self.onError();
                        }
                        self.URLString = "";
                        break;
                    }
                };
                this.xmlhttp.send(this.URLString);
            }
        }
    };
    this.reset();
    this.createAJAX();
}

function ThumbsUp(id) {
    var url = "./ajax/thumbsup.php?id=" + escape(id);
    try {
        request = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
        try {
            request = new ActiveXObject("Microsoft.XMLHTTP");
        } catch (e2) {
            request = false;
        }
    }
    if (!request && typeof XMLHttpRequest != "undefined") {
        request = new XMLHttpRequest();
    }
    request.open("GET", url, true);
    global_content = id;
    request.onreadystatechange = gom;
    request.send(null);
}

function gom() {
    if (request.readyState == 4) {
        if (request.status == 200) {
            document.getElementById("thumbsup").innerHTML = request.responseText;
        }
    }
}