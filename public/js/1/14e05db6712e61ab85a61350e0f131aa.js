var ajaxChat = {
    settingsInitiated: null,
    styleInitiated: null,
    initializeFunction: null,
    finalizeFunction: null,
    loginChannelID: null,
    loginChannelName: null,
    timerRate: null,
    timerRateReset: null,
    timer: null,
    ajaxURL: null,
    baseURL: null,
    regExpMediaUrl: null,
    dirs: null,
    startChatOnLoad: null,
    chatStarted: null,
    domIDs: null,
    dom: null,
    settings: null,
    nonPersistentSettings: null,
    unusedSettings: null,
    bbCodeTags: null,
    colorCodes: null,
    emoticonCodes: null,
    emoticonFiles: null,
    soundFiles: null,
    sounds: null,
    soundTransform: null,
    siteName: null,
    sessionName: null,
    sessionKeyPrefix: null,
    cookieExpiration: null,
    cookiePath: null,
    cookieDomain: null,
    cookieSecure: null,
    chatBotName: null,
    chatBotID: null,
    chatBotRole: null,
    allowUserMessageDelete: null,
    inactiveTimeout: null,
    privateChannelDiff: null,
    privateMessageDiff: null,
    showChannelMessages: null,
    messageTextMaxLength: null,
    socketServerEnabled: null,
    socketServerHost: null,
    socketServerPort: null,
    socketServerChatID: null,
    socket: null,
    socketIsConnected: null,
    socketTimerRate: null,
    socketReconnectTimer: null,
    socketRegistrationID: null,
    userID: null,
    userName: null,
    userRole: null,
    pmCount: null,
    channelID: null,
    channelName: null,
    channelSwitch: null,
    usersList: null,
    userNamesList: null,
    userMenuCounter: null,
    encodedUserName: null,
    userNodeString: null,
    ignoredUserNames: null,
    lastID: null,
    localID: null,
    lang: null,
    langCode: null,
    baseDirection: null,
    originalDocumentTitle: null,
    blinkInterval: null,
    httpRequest: null,
    retryTimer: null,
    retryTimerDelay: null,
    requestStatus: null,
    DOMbuffering: null,
    DOMbuffer: null,
    DOMbufferRowClass: null,
    inUrlBBCode: null,
    flashSounds: null,
    debug: null,
    init: function(config, lang, initSettings, initStyle, initialize, initializeFunction, finalizeFunction) {
        this.httpRequest = {};
        this.usersList = [];
        this.userNamesList = [];
        this.userMenuCounter = 0;
        this.lastID = 0;
        this.localID = 0;
        this.lang = lang;
        this.requestStatus = "ok";
        this.DOMbufferRowClass = "rowOdd";
        this.inUrlBBCode = false;
        this.pmCount = 0;
        this.timeStamp = new Date();
        this.initConfig(config);
        this.initDirectories();
        if (initSettings) {
            this.initSettings();
        }
        if (initStyle) {
            this.initStyle();
        }
        this.initializeFunction = initializeFunction;
        this.finalizeFunction = finalizeFunction;
        if (initialize) {
            this.setLoadHandler();
        }
    },
    initConfig: function(config) {
        this.token = config["token"];
        this.loginChannelID = config["loginChannelID"];
        this.loginChannelName = config["loginChannelName"];
        this.timerRate = config["timerRate"];
        this.timerRateReset = this.timerRate;
        this.ajaxURL = config["ajaxURL"];
        this.baseURL = config["baseURL"];
        this.regExpMediaUrl = config["regExpMediaUrl"];
        this.startChatOnLoad = config["startChatOnLoad"];
        this.domIDs = config["domIDs"];
        this.settings = config["settings"];
        this.nonPersistentSettings = config["nonPersistentSettings"];
        this.bbCodeTags = config["bbCodeTags"];
        this.colorCodes = config["colorCodes"];
        this.emoticonCodes = config["emoticonCodes"];
        this.emoticonFiles = config["emoticonFiles"];
        this.emoticonDisplay = config["emoticonDisplay"];
        this.soundFiles = config["soundFiles"];
        this.siteName = config["siteName"];
        this.sessionName = config["sessionName"];
        this.sessionKeyPrefix = config["sessionKeyPrefix"];
        this.cookieExpiration = config["cookieExpiration"];
        this.cookiePath = config["cookiePath"];
        this.cookieDomain = config["cookieDomain"];
        this.cookieSecure = config["cookieSecure"];
        this.chatBotName = config["chatBotName"];
        this.chatBotID = config["chatBotID"];
        this.chatBotRole = config["chatBotRole"];
        this.allowUserMessageDelete = config["allowUserMessageDelete"];
        this.inactiveTimeout = Math.max(config["inactiveTimeout"], 2);
        this.privateChannelDiff = config["privateChannelDiff"];
        this.privateMessageDiff = config["privateMessageDiff"];
        this.showChannelMessages = config["showChannelMessages"];
        this.messageTextMaxLength = config["messageTextMaxLength"];
        this.socketServerEnabled = config["socketServerEnabled"];
        this.socketServerHost = config["socketServerHost"];
        this.socketServerPort = config["socketServerPort"];
        this.socketServerChatID = config["socketServerChatID"];
        this.debug = config["debug"];
        this.DOMbuffering = false;
        this.DOMbuffer = "";
        this.anonymizer = config["anonLink"];
        this.retryTimerDelay = (this.inactiveTimeout * 6e3 - this.timerRate) / 4 + this.timerRate;
    },
    initDirectories: function() {
        this.dirs = {};
        this.dirs["emoticons"] = "./images/smilies/";
        this.dirs["sounds"] = "./media/sounds/";
        this.dirs["flash"] = "./media/flash/";
    },
    initSettings: function() {
        var cookie = this.readCookie(this.sessionKeyPrefix + "settings"), i, settingsArray, setting, key, value, number;
        this.settingsInitiated = true;
        this.unusedSettings = {};
        if (cookie) {
            settingsArray = cookie.split("&");
            for (i = 0; i < settingsArray.length; i++) {
                setting = settingsArray[i].split("=");
                if (setting.length === 2) {
                    key = setting[0];
                    value = this.decodeText(setting[1]);
                    switch (value) {
                      case "true":
                        value = true;
                        break;

                      case "false":
                        value = false;
                        break;

                      case "null":
                        value = null;
                        break;

                      default:
                        number = parseFloat(value);
                        if (!isNaN(number)) {
                            if (parseInt(number) === number) {
                                value = parseInt(number);
                            } else {
                                value = number;
                            }
                        }
                    }
                    if (this.inArray(this.nonPersistentSettings, key)) {
                        this.unusedSettings[key] = value;
                    } else {
                        this.settings[key] = value;
                    }
                }
            }
        }
    },
    persistSettings: function() {
        var settingsArray;
        if (this.settingsInitiated) {
            settingsArray = [];
            for (var property in this.settings) {
                if (this.inArray(this.nonPersistentSettings, property)) {
                    if (this.unusedSettings && this.unusedSettings[property]) {
                        this.settings[property] = this.unusedSettings[property];
                    } else {
                        continue;
                    }
                }
                settingsArray.push(property + "=" + this.encodeText(this.settings[property]));
            }
            this.createCookie(this.sessionKeyPrefix + "settings", settingsArray.join("&"), this.cookieExpiration);
        }
    },
    getSettings: function() {
        return this.settings;
    },
    getSetting: function(key) {
        for (var property in this.settings) {
            if (property === key) {
                return this.settings[key];
            }
        }
        return null;
    },
    setSetting: function(key, value) {
        this.settings[key] = value;
    },
    initializeSettings: function() {
        if (this.settings["persistFontColor"] && this.settings["fontColor"]) {
            if (this.dom["inputField"]) {
                this.dom["inputField"].style.color = this.settings["fontColor"];
            }
        }
    },
    initialize: function() {
        this.setUnloadHandler();
        this.initializeDocumentNodes();
        this.loadPageAttributes();
        this.initEmoticons();
        this.initColorCodes();
        this.initializeSettings();
        this.setSelectedStyle();
        this.customInitialize();
        this.setStatus("retrying");
        if (typeof this.initializeFunction === "function") {
            this.initializeFunction();
        }
        if (!this.isCookieEnabled()) {
            this.addChatBotMessageToChatList("/error CookiesRequired");
        } else {
            if (this.startChatOnLoad) {
                this.startChat();
            } else {
                this.setStartChatHandler();
                this.requestTeaserContent();
            }
        }
    },
    requestTeaserContent: function() {
        var params = "&view=teaser";
        params += "&getInfos=" + this.encodeText("userID,userName,userRole");
        if (!isNaN(parseInt(this.loginChannelID))) {
            params += "&channelID=" + this.loginChannelID;
        } else if (this.loginChannelName !== null) {
            params += "&channelName=" + this.encodeText(this.loginChannelName);
        }
        this.updateChat(params);
    },
    setStartChatHandler: function() {
        if (this.dom["inputField"]) {
            this.dom["inputField"].onfocus = function() {
                ajaxChat.startChat();
                ajaxChat.dom["inputField"].onfocus = "";
            };
        }
    },
    startChat: function() {
        this.chatStarted = true;
        if (this.dom["inputField"] && this.settings["autoFocus"]) {
            this.dom["inputField"].focus();
        }
        this.checkFlashSounds();
        if (this.socketServerEnabled || this.flashSounds) {
            this.loadFlashInterface();
        } else {
            this.initializeHTML5Sounds();
        }
        this.startChatUpdate();
    },
    loadPageAttributes: function() {
        var htmlTag = document.getElementsByTagName("html")[0];
        this.langCode = htmlTag.getAttribute("lang") ? htmlTag.getAttribute("lang") : "en";
        this.baseDirection = htmlTag.getAttribute("dir") ? htmlTag.getAttribute("dir") : "ltr";
    },
    setLoadHandler: function() {
        var self = this;
        this.addEvent(window, "load", function() {
            self.initialize();
        });
    },
    setUnloadHandler: function() {
        var onunload = window.onunload;
        if (typeof onunload !== "function") {
            window.onunload = function() {
                ajaxChat.finalize();
            };
        } else {
            window.onunload = function() {
                ajaxChat.finalize();
                onunload();
            };
        }
    },
    updateDOM: function(id, str, prepend, overwrite) {
        var domNode = this.dom[id] ? this.dom[id] : document.getElementById(id);
        if (!domNode) {
            return;
        }
        try {
            domNode.cloneNode(false).innerHTML = str;
            if (overwrite) {
                domNode.innerHTML = str;
            } else if (prepend) {
                if (id == "chatList") {
                    domNode.insertAdjacentHTML("afterbegin", str);
                } else {
                    domNode.innerHTML = str + domNode.innerHTML;
                }
            } else {
                if (id == "chatList") {
                    domNode.insertAdjacentHTML("beforeend", str);
                } else {
                    domNode.innerHTML += str;
                }
            }
        } catch (e) {
            this.DOMbuffer = "";
            this.addChatBotMessageToChatList("/error DOMSyntax " + id);
            this.updateChatlistView();
        }
    },
    initializeDocumentNodes: function() {
        this.dom = {};
        for (var key in this.domIDs) {
            this.dom[key] = document.getElementById(this.domIDs[key]);
        }
    },
    initEmoticons: function() {
        this.DOMbuffer = "";
        for (var i = 0; i < this.emoticonCodes.length; i++) {
            this.emoticonCodes[i] = this.encodeSpecialChars(this.emoticonCodes[i]);
            if (this.emoticonDisplay[i] == 2 || this.emoticonDisplay[i] == 3) {
                if (this.dom["emoticonsContainer"]) {
                    this.DOMbuffer = this.DOMbuffer + "<a href=\"javascript:ajaxChat.insertText('" + this.scriptLinkEncode(this.emoticonCodes[i]) + '\');"><img src="' + this.dirs["emoticons"] + this.emoticonFiles[i] + '" alt="' + this.emoticonCodes[i] + '" title="' + this.emoticonCodes[i] + '"/></a>';
                }
            }
        }
        if (this.dom["emoticonsContainer"]) {
            this.updateDOM("emoticonsContainer", this.DOMbuffer);
        }
        this.DOMbuffer = "";
    },
    initColorCodes: function() {
        if (this.dom["colorCodesContainer"]) {
            this.DOMbuffer = "";
            for (var i = 0; i < this.colorCodes.length; i++) {
                this.DOMbuffer = this.DOMbuffer + "<a href=\"javascript:ajaxChat.setFontColor('" + this.colorCodes[i] + '\');" style="background-color:' + this.colorCodes[i] + ';" title="' + this.colorCodes[i] + '" onclick="this.parentNode.style.display = \'none\';"></a>' + "\n";
            }
            this.updateDOM("colorCodesContainer", this.DOMbuffer);
            this.DOMbuffer = "";
        }
    },
    startChatUpdate: function() {
        var infos = "userID,userName,userRole,channelID,channelName";
        if (this.socketServerEnabled) {
            infos += ",socketRegistrationID";
        }
        var params = "&getInfos=" + this.encodeText(infos);
        if (!isNaN(parseInt(this.loginChannelID))) {
            params += "&channelID=" + this.loginChannelID;
        } else if (this.loginChannelName !== null) {
            params += "&channelName=" + this.encodeText(this.loginChannelName);
        }
        this.updateChat(params);
    },
    updateChat: function(paramString) {
        var requestUrl = this.ajaxURL + "&lastID=" + this.lastID;
        if (paramString) {
            requestUrl += paramString;
        }
        this.makeRequest(requestUrl, "GET", null);
    },
    loadFlashInterface: function() {
        if (!this.dom["flashInterfaceContainer"] || this.dom["flashInterfaceContainer"].flashLoaded) {
            return;
        }
        this.updateDOM("flashInterfaceContainer", '<object id="ajaxChatFlashInterface" style="position:absolute; left:-100px;" ' + 'classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" ' + 'codebase="' + window.location.protocol + '//download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" ' + 'height="1" width="1">' + '<param name="flashvars" value="bridgeName=ajaxChat"/>' + '<param name="src" value="' + this.dirs["flash"] + 'FABridge.swf"/>' + '<embed name="ajaxChatFlashInterface" type="application/x-shockwave-flash" pluginspage="' + window.location.protocol + '//www.macromedia.com/go/getflashplayer" ' + 'src="' + this.dirs["flash"] + 'FABridge.swf" height="1" width="1" flashvars="bridgeName=ajaxChat"/>' + "</object>");
        FABridge.addInitializationCallback("ajaxChat", this.flashInterfaceLoadCompleteHandler);
        this.dom["flashInterfaceContainer"].flashLoaded = true;
    },
    setAudioBackend: function(audioBackend) {
        this.setSetting("audioBackend", audioBackend);
        this.checkFlashSounds();
        if (this.flashSounds) {
            this.loadFlashInterface();
        } else {
            this.initializeHTML5Sounds();
        }
    },
    checkFlashSounds: function() {
        if (this.settings["audioBackend"] < 0) {
            if (navigator.appVersion.indexOf("MSIE") != -1) {
                try {
                    flash = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
                } catch (e) {
                    this.flashSounds = false;
                }
            } else if (navigator.plugins && !navigator.plugins["Shockwave Flash"] || navigator.mimeTypes && !navigator.mimeTypes["application/x-shockwave-flash"]) {
                this.flashSounds = false;
            }
        } else {
            this.flashSounds = this.settings["audioBackend"] == 1;
        }
    },
    flashInterfaceLoadCompleteHandler: function() {
        ajaxChat.initializeFlashInterface();
    },
    initializeFlashInterface: function() {
        if (this.socketServerEnabled) {
            this.socketTimerRate = (this.inactiveTimeout - 1) * 60 * 1e3;
            this.socketConnect();
        }
        this.loadFlashSounds();
        this.initializeCustomFlashInterface();
    },
    socketConnect: function() {
        if (!this.socketIsConnected) {
            try {
                if (!this.socket && FABridge.ajaxChat) {
                    this.socket = FABridge.ajaxChat.create("flash.net.XMLSocket");
                    this.socket.addEventListener("connect", this.socketConnectHandler);
                    this.socket.addEventListener("close", this.socketCloseHandler);
                    this.socket.addEventListener("data", this.socketDataHandler);
                    this.socket.addEventListener("ioError", this.socketIOErrorHandler);
                    this.socket.addEventListener("securityError", this.socketSecurityErrorHandler);
                }
                this.socket.connect(this.socketServerHost, this.socketServerPort);
            } catch (e) {
                this.debugMessage("socketConnect", e);
            }
        }
        clearTimeout(this.socketReconnectTimer);
        this.socketReconnectTimer = null;
    },
    socketConnectHandler: function(event) {
        ajaxChat.socketIsConnected = true;
        setTimeout(ajaxChat.socketRegister, 0);
    },
    socketCloseHandler: function(event) {
        ajaxChat.socketIsConnected = false;
        if (ajaxChat.socket) {
            clearTimeout(ajaxChat.timer);
            ajaxChat.updateChat(null);
        }
    },
    socketDataHandler: function(event) {
        ajaxChat.socketUpdate(event.getData());
    },
    socketIOErrorHandler: function(event) {
        setTimeout(function() {
            ajaxChat.addChatBotMessageToChatList("/error SocketIO");
        }, 0);
        setTimeout(ajaxChat.updateChatlistView, 1);
    },
    socketSecurityErrorHandler: function(event) {
        setTimeout(function() {
            ajaxChat.addChatBotMessageToChatList("/error SocketSecurity");
        }, 0);
        setTimeout(ajaxChat.updateChatlistView, 1);
    },
    socketRegister: function() {
        if (this.socket && this.socketIsConnected) {
            try {
                this.socket.send('<register chatID="' + this.socketServerChatID + '" userID="' + this.userID + '" regID="' + this.socketRegistrationID + '"/>');
            } catch (e) {
                this.debugMessage("socketRegister", e);
            }
        }
    },
    loadXML: function(str) {
        if (!arguments.callee.parser) {
            try {
                arguments.callee.parser = new DOMParser();
            } catch (e) {
                var customDOMParser = function() {};
                if (navigator.appName === "Microsoft Internet Explorer") {
                    customDOMParser.prototype.parseFromString = function(str, contentType) {
                        if (!arguments.callee.XMLDOM) {
                            arguments.callee.XMLDOM = new ActiveXObject("Microsoft.XMLDOM");
                        }
                        arguments.callee.XMLDOM.loadXML(str);
                        return arguments.callee.XMLDOM;
                    };
                } else {
                    customDOMParser.prototype.parseFromString = function(str, contentType) {
                        if (!arguments.callee.httpRequest) {
                            arguments.callee.httpRequest = new XMLHttpRequest();
                        }
                        arguments.callee.httpRequest.open("GET", "data:text/xml;charset=utf-8," + encodeURIComponent(str), false);
                        arguments.callee.httpRequest.send(null);
                        return arguments.callee.httpRequest.responseXML;
                    };
                }
                arguments.callee.parser = new customDOMParser();
            }
        }
        return arguments.callee.parser.parseFromString(str, "text/xml");
    },
    socketUpdate: function(data) {
        var xmlDoc = this.loadXML(data);
        if (xmlDoc) {
            this.handleOnlineUsers(xmlDoc.getElementsByTagName("user"));
            if ((this.showChannelMessages || xmlDoc.firstChild.getAttribute("mode") !== "1") && !this.channelSwitch) {
                var channelID = xmlDoc.firstChild.getAttribute("channelID");
                if (channelID === this.channelID || parseInt(channelID) === parseInt(this.userID) + this.privateMessageDiff) {
                    this.handleChatMessages(xmlDoc.getElementsByTagName("message"));
                }
            }
        }
    },
    setAudioVolume: function(volume) {
        volume = parseFloat(volume);
        if (!isNaN(volume)) {
            if (volume < 0) {
                volume = 0;
            } else if (volume > 1) {
                volume = 1;
            }
            this.settings["audioVolume"] = volume;
            if (this.flashSounds) {
                try {
                    if (!this.soundTransform) {
                        this.soundTransform = FABridge.ajaxChat.create("flash.media.SoundTransform");
                    }
                    this.soundTransform.setVolume(volume);
                } catch (e) {
                    this.debugMessage("setAudioVolumeFlash", e);
                }
            } else {
                try {
                    for (var key in this.soundFiles) {
                        this.sounds[key].volume = volume;
                    }
                } catch (e) {
                    this.debugMessage("setAudioVolume", e);
                }
            }
        }
    },
    initializeHTML5Sounds: function() {
        var audio, mp3, ogg;
        try {
            audio = document.createElement("audio");
            mp3 = !!(audio.canPlayType && audio.canPlayType("audio/mpeg;").replace(/no/, ""));
            ogg = !!(audio.canPlayType && audio.canPlayType('audio/ogg; codecs="vorbis"').replace(/no/, ""));
            this.sounds = [];
            if (mp3) {
                format = ".mp3";
            } else if (ogg) {
                format = ".ogg";
            } else {
                format = ".wav";
            }
            for (var key in this.soundFiles) {
                this.sounds[key] = new Audio(this.dirs["sounds"] + key + format);
            }
            this.setAudioVolume(this.settings["audioVolume"]);
        } catch (e) {
            this.debugMessage("initializeHTML5Sounds", e);
        }
    },
    loadFlashSounds: function() {
        var sound, urlRequest;
        if (this.flashSounds) {
            try {
                this.setAudioVolume(this.settings["audioVolume"]);
                this.sounds = {};
                for (var key in this.soundFiles) {
                    sound = FABridge.ajaxChat.create("flash.media.Sound");
                    sound.addEventListener("complete", this.soundLoadCompleteHandler);
                    sound.addEventListener("ioError", this.soundIOErrorHandler);
                    urlRequest = FABridge.ajaxChat.create("flash.net.URLRequest");
                    urlRequest.setUrl(this.dirs["sounds"] + this.soundFiles[key]);
                    sound.load(urlRequest);
                }
            } catch (e) {
                this.debugMessage("loadFlashSounds", e);
            }
        }
    },
    soundLoadCompleteHandler: function(event) {
        var sound = event.getTarget();
        for (var key in ajaxChat.soundFiles) {
            if (new RegExp(ajaxChat.soundFiles[key]).test(sound.getUrl())) {
                ajaxChat.sounds[key] = sound;
            }
        }
    },
    soundIOErrorHandler: function(event) {
        setTimeout(function() {
            ajaxChat.addChatBotMessageToChatList("/error SoundIO");
        }, 0);
        setTimeout(ajaxChat.updateChatlistView, 1);
    },
    soundPlayCompleteHandler: function(event) {},
    playSound: function(soundID) {
        if (this.sounds && this.sounds[soundID]) {
            if (this.flashSounds) {
                try {
                    return this.sounds[soundID].play(0, 0, this.soundTransform);
                } catch (e) {
                    this.debugMessage("playSound", e);
                }
            } else {
                try {
                    this.sounds[soundID].currentTime = 0;
                    return this.sounds[soundID].play();
                } catch (e) {
                    this.debugMessage("playSound", e);
                }
            }
        }
        return null;
    },
    playSoundOnNewMessage: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
        var messageParts;
        if (this.settings["audio"] && this.sounds && this.lastID && !this.channelSwitch) {
            if (this.customSoundOnNewMessage(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) === false) {
                return;
            }
            messageParts = messageText.split(" ", 1);
            switch (userID) {
              case this.chatBotID:
                switch (messageParts[0]) {
                  case "/kick":
                    this.playSound(this.settings["soundLeave"]);
                    break;

                  case "/error":
                    this.playSound(this.settings["soundError"]);
                    break;

                  default:
                    this.playSound(this.settings["soundChatBot"]);
                }
                break;

              case this.userID:
                switch (messageParts[0]) {
                  case "/privmsgto":
                    this.playSound(this.settings["soundPrivate"]);
                    break;

                  default:
                    this.playSound(this.settings["soundSend"]);
                }
                break;

              default:
                switch (messageParts[0]) {
                  case "/privmsg":
                    this.playSound(this.settings["soundPrivate"]);
                    break;

                  default:
                    this.playSound(this.settings["soundReceive"]);
                }
                break;
            }
        }
    },
    fillSoundSelection: function(selectionID, selectedSound) {
        var selection = document.getElementById(selectionID);
        var i = 1;
        for (var key in this.soundFiles) {
            selection.options[i] = new Option(key, key);
            if (key === selectedSound) {
                selection.options[i].selected = true;
            }
            i++;
        }
    },
    setStatus: function(newStatus) {
        if (!(newStatus === "waiting" && this.requestStatus === "retrying")) {
            this.requestStatus = newStatus;
        }
        if (this.dom["statusIcon"]) {
            this.dom["statusIcon"].className = this.requestStatus;
        }
    },
    forceNewRequest: function() {
        ajaxChat.updateChat(null);
        ajaxChat.setStatus("retrying");
    },
    getHttpRequest: function(identifier) {
        if (!this.httpRequest[identifier]) {
            if (window.XMLHttpRequest) {
                this.httpRequest[identifier] = new XMLHttpRequest();
                if (this.httpRequest[identifier].overrideMimeType) {
                    this.httpRequest[identifier].overrideMimeType("text/xml");
                }
            } else if (window.ActiveXObject) {
                try {
                    this.httpRequest[identifier] = new ActiveXObject("Msxml2.XMLHTTP");
                } catch (e) {
                    try {
                        this.httpRequest[identifier] = new ActiveXObject("Microsoft.XMLHTTP");
                    } catch (e) {}
                }
            }
        }
        return this.httpRequest[identifier];
    },
    makeRequest: function(url, method, data) {
        var self = this, identifier;
        self.setStatus("waiting");
        try {
            if (data) {
                if (!arguments.callee.identifier || arguments.callee.identifier > 50) {
                    arguments.callee.identifier = 1;
                } else {
                    arguments.callee.identifier++;
                }
                identifier = arguments.callee.identifier;
            } else {
                identifier = 0;
            }
            self.retryTimer = setTimeout(ajaxChat.forceNewRequest, ajaxChat.retryTimerDelay);
            self.getHttpRequest(identifier).open(method, url, true);
            self.getHttpRequest(identifier).onreadystatechange = function() {
                try {
                    ajaxChat.handleResponse(identifier);
                } catch (e) {
                    try {
                        clearTimeout(ajaxChat.timer);
                    } catch (e) {
                        self.debugMessage("makeRequest::clearTimeout", e);
                    }
                    try {
                        if (data) {
                            ajaxChat.addChatBotMessageToChatList("/error ConnectionTimeout");
                            ajaxChat.setStatus("retrying");
                            ajaxChat.updateChatlistView();
                        }
                    } catch (e) {
                        self.debugMessage("makeRequest::logRetry", e);
                    }
                    try {
                        ajaxChat.timer = setTimeout(function() {
                            ajaxChat.updateChat(null);
                        }, ajaxChat.timerRate);
                    } catch (e) {
                        self.debugMessage("makeRequest::setTimeout", e);
                    }
                }
            };
            if (method === "POST") {
                self.getHttpRequest(identifier).setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            }
            self.getHttpRequest(identifier).send(data);
        } catch (e) {
            clearTimeout(this.timer);
            if (data) {
                self.addChatBotMessageToChatList("/error ConnectionTimeout");
                ajaxChat.setStatus("retrying");
                self.updateChatlistView();
            }
            self.timer = setTimeout(function() {
                ajaxChat.updateChat(null);
            }, self.timerRate);
        }
    },
    handleResponse: function(identifier) {
        var xmlDoc;
        if (this.getHttpRequest(identifier).readyState === 4) {
            if (this.getHttpRequest(identifier).status === 200) {
                clearTimeout(ajaxChat.retryTimer);
                xmlDoc = this.getHttpRequest(identifier).responseXML;
                ajaxChat.setStatus("ok");
            } else {
                if (this.getHttpRequest(identifier).status === 0) {
                    this.setStatus("waiting");
                    this.updateChatlistView();
                    return false;
                } else {
                    this.addChatBotMessageToChatList("/error ConnectionStatus " + this.getHttpRequest(identifier).status);
                    this.setStatus("retrying");
                    this.updateChatlistView();
                    return false;
                }
            }
        }
        if (!xmlDoc) {
            return false;
        }
        var timediff = new Date() - this.timeStamp;
        if (timediff > 3e5) {
            if (this.timerRate < 3e4) {
                this.timerRate = this.timerRate + 500;
            }
        }
        this.handleXML(xmlDoc);
        return true;
    },
    handleXML: function(xmlDoc) {
        this.handleInfoMessages(xmlDoc.getElementsByTagName("info"));
        this.handleOnlineUsers(xmlDoc.getElementsByTagName("user"));
        this.handleChatMessages(xmlDoc.getElementsByTagName("message"));
        this.channelSwitch = null;
        this.setChatUpdateTimer();
    },
    setChatUpdateTimer: function() {
        clearTimeout(this.timer);
        if (this.chatStarted) {
            var timeout;
            if (this.socketIsConnected) {
                timeout = this.socketTimerRate;
            } else {
                timeout = this.timerRate;
                if (this.socketServerEnabled && !this.socketReconnectTimer) {
                    this.socketReconnectTimer = setTimeout(ajaxChat.socketConnect, 6e4);
                }
            }
            this.timer = setTimeout(function() {
                ajaxChat.updateChat(null);
            }, timeout);
        }
    },
    handleInfoMessages: function(infoNodes) {
        var infoType, infoData;
        for (var i = 0; i < infoNodes.length; i++) {
            infoType = infoNodes[i].getAttribute("type");
            infoData = infoNodes[i].firstChild ? infoNodes[i].firstChild.nodeValue : "";
            this.handleInfoMessage(infoType, infoData);
        }
    },
    handleInfoMessage: function(infoType, infoData) {
        if (this.handleCustomInfoMessage(infoType, infoData) === true) {
            return;
        }
        switch (infoType) {
          case "channelSwitch":
            this.clearChatList();
            this.clearOnlineUsersList();
            this.setSelectedChannel(infoData);
            this.channelName = infoData;
            this.channelSwitch = true;
            break;

          case "channelName":
            this.setSelectedChannel(infoData);
            this.channelName = infoData;
            break;

          case "channelID":
            this.channelID = infoData;
            break;

          case "userID":
            this.userID = infoData;
            break;

          case "userName":
            this.userName = infoData;
            this.encodedUserName = this.scriptLinkEncode(this.userName);
            this.userNodeString = null;
            break;

          case "userRole":
            this.userRole = infoData;
            break;

          case "logout":
            this.handleLogout(infoData);
            return;

          case "socketRegistrationID":
            this.socketRegistrationID = infoData;
            this.socketRegister();

          default:
            return;
        }
    },
    handleOnlineUsers: function(userNodes) {
        if (userNodes.length) {
            var index, userID, userName, userRole, i, onlineUsers = [];
            if (userNodes.length != this.usersList.length) {
                this.clearOnlineUsersList();
            }
            for (i = 0; i < userNodes.length; i++) {
                userID = userNodes[i].getAttribute("userID");
                userName = userNodes[i].firstChild ? userNodes[i].firstChild.nodeValue : "";
                userRole = userNodes[i].getAttribute("userRole");
                onlineUsers.push(userID);
                index = this.arraySearch(userID, this.usersList);
                if (index === -1) {
                    this.addUserToOnlineList(userID, userName, userRole);
                } else if (this.userNamesList[index] !== userName) {
                    this.removeUserFromOnlineList(userID, index);
                    this.addUserToOnlineList(userID, userName, userRole);
                }
                if (userID == this.userID) {
                    pmCount = userNodes[i].getAttribute("pmCount");
                    if (pmCount == 0) {
                        window.parent.document.title = this.siteName + " :: Home";
                        document.title = this.siteName + " :: Chat";
                    } else {
                        window.parent.document.title = this.siteName + " :(" + pmCount + "): Home";
                        document.title = this.siteName + " :(" + pmCount + "): Chat";
                    }
                    var span = document.getElementById("pmcount");
                    while (span.firstChild) {
                        span.removeChild(span.firstChild);
                    }
                    span.appendChild(document.createTextNode(pmCount));
                    this.pmCount = pmCount;
                }
            }
            for (i = 0; i < this.usersList.length; i++) {
                if (!this.inArray(onlineUsers, this.usersList[i])) {
                    this.removeUserFromOnlineList(this.usersList[i], i);
                }
            }
            this.setOnlineListRowClasses();
            document.getElementById("olcount").innerHTML = "(" + this.usersList.length + ")";
        }
    },
    handleChatMessages: function(messageNodes) {
        var userNode, userName, textNode, messageText, i;
        if (messageNodes.length) {
            for (i = 0; i < messageNodes.length; i++) {
                this.DOMbuffering = true;
                userNode = messageNodes[i].getElementsByTagName("username")[0];
                userName = userNode.firstChild ? userNode.firstChild.nodeValue : "";
                textNode = messageNodes[i].getElementsByTagName("text")[0];
                messageText = textNode.firstChild ? textNode.firstChild.nodeValue : "";
                if (i === messageNodes.length - 1) {
                    this.DOMbuffering = false;
                }
                this.addMessageToChatList(new Date(messageNodes[i].getAttribute("dateTime")), messageNodes[i].getAttribute("userID"), userName, messageNodes[i].getAttribute("userRole"), messageNodes[i].getAttribute("id"), messageText, messageNodes[i].getAttribute("channelID"), messageNodes[i].getAttribute("ip"));
                if (messageNodes[i].getAttribute("userID") != this.chatBotID) {
                    this.timerRate = this.timerRateReset;
                    this.timeStamp = new Date();
                }
            }
            this.DOMbuffering = false;
            this.updateChatlistView();
            if (this.settings["postDirection"]) {
                this.lastID = messageNodes[0].getAttribute("id");
            } else {
                this.lastID = messageNodes[messageNodes.length - 1].getAttribute("id");
            }
        }
    },
    setSelectedChannel: function(channel) {
        var channelSelected = false, i, option, text;
        if (this.dom["channelSelection"]) {
            channel = this.decodeSpecialChars(channel);
            for (i = 0; i < this.dom["channelSelection"].options.length; i++) {
                if (this.dom["channelSelection"].options[i].value === channel) {
                    this.dom["channelSelection"].options[i].selected = true;
                    channelSelected = true;
                    break;
                }
            }
            if (!channelSelected) {
                option = document.createElement("option");
                text = document.createTextNode(channel);
                option.appendChild(text);
                option.setAttribute("value", channel);
                option.setAttribute("selected", "selected");
                this.dom["channelSelection"].appendChild(option);
            }
        }
    },
    removeUserFromOnlineList: function(userID, index) {
        this.usersList.splice(index, 1);
        this.userNamesList.splice(index, 1);
        if (this.dom["onlineList"]) {
            this.dom["onlineList"].removeChild(this.getUserNode(userID));
        }
    },
    addUserToOnlineList: function(userID, userName, userRole) {
        this.usersList.push(userID);
        this.userNamesList.push(userName);
        if (this.dom["onlineList"]) {
            this.updateDOM("onlineList", this.getUserNodeString(userID, userName, userRole), this.userID === userID);
        }
    },
    getUserNodeString: function(userID, userName, userRole) {
        var encodedUserName, str;
        if (this.userNodeString && userID === this.userID) {
            return this.userNodeString;
        } else {
            encodedUserName = this.scriptLinkEncode(userName);
            str = '<div id="' + this.getUserDocumentID(userID) + '"><a href="javascript:ajaxChat.toggleUserMenu(\'' + this.getUserMenuDocumentID(userID) + "', '" + encodedUserName + "', " + userID + ');" class="' + this.getRoleClass(userRole) + '" title="' + this.lang["toggleUserMenu"].replace(/%s/, userName) + '">' + userName + "</a>" + '<ul class="userMenu" id="' + this.getUserMenuDocumentID(userID) + '"' + (userID === this.userID ? ">" + this.getUserNodeStringItems(encodedUserName, userID, false) : ' style="display:none;">') + "</ul>" + "</div>";
            if (userID === this.userID) {
                this.userNodeString = str;
            }
            return str;
        }
    },
    toggleUserMenu: function(menuID, userName, userID) {
        var isInline = false;
        if (menuID.indexOf("ium") >= 0) {
            isInline = true;
        }
        if (!document.getElementById(menuID).firstChild) {
            this.updateDOM(menuID, this.getUserNodeStringItems(this.encodeText(this.addSlashes(this.getScriptLinkValue(userName))), userID, isInline), false, true);
        }
        this.showHide(menuID);
        this.dom["chatList"].scrollTop = this.dom["chatList"].scrollHeight;
    },
    getUserNodeStringItems: function(encodedUserName, userID, isInline) {
        var menu;
        if (encodedUserName !== this.encodedUserName) {
            menu = "<li><a href=\"javascript:ajaxChat.insertMessageWrapper('/msg " + encodedUserName + ' \');" title="Private Message this user in AJAX Chat.">' + this.lang["userMenuSendPrivateMessage"] + "</a></li>" + '<li class="disc"><a target="_blank" href="../pm_system.php?action=send_message&amp;receiver=' + userID + '" title="Private Message this user using site messages.">' + "PM User " + "</a></li>" + "<li><a href=\"javascript:ajaxChat.sendMessageWrapper('/query " + encodedUserName + '\');" title="Open a private channel between you and this user.">' + this.lang["userMenuOpenPrivateChannel"] + "</a></li>" + '<li><a href="javascript:ajaxChat.sendMessageWrapper(\'/query\');" title="Close a private channel between you and this user.">' + this.lang["userMenuClosePrivateChannel"] + "</a></li>" + "<li><a href=\"javascript:ajaxChat.sendMessageWrapper('/ignore " + encodedUserName + '\');" title="(un)Ignore this user.">' + this.lang["userMenuIgnore"] + "</a></li>" + "<li><a href=\"javascript:ajaxChat.sendMessageWrapper('/invite " + encodedUserName + '\');" title="Invite this user to your private channel.">' + this.lang["userMenuInvite"] + "</a></li>" + "<li><a href=\"javascript:ajaxChat.sendMessageWrapper('/uninvite " + encodedUserName + '\');" title="Revoke an Invitation to this user to your private channel.">' + this.lang["userMenuUninvite"] + "</a></li>" + '<li class="disc"><a target="_blank" href="../userdetails.php?id=' + userID + '&amp;hit=1" title="Open this users profile.">' + "Users Profile " + "</a></li>" + '<li class="disc"><a target="_blank" href="../browse.php?search=' + encodedUserName + '&amp;searchin=owner&amp;incldead=1&amp;vip=0" title="View this users uploads.">' + "Users Uploads " + "</a></li>" + '<li class="disc"><a href="javascript:ajaxChat.sendMessageWrapper(\'/stats ' + encodedUserName + '\');" title="Display users site stats in chat.">Users Stats' + "</a></li>" + '<li class="disc"><a href="javascript:ajaxChat.insertMessageWrapper(\'/gift ' + encodedUserName + ' \');" title="Give the user a gift of Karma.">' + "Karma Gift" + "</a></li>" + '<li class="disc"><a href="javascript:ajaxChat.insertMessageWrapper(\'/rep ' + encodedUserName + ' \');" title="Give the user som Rep+.">' + "Reputation Gift" + "</a></li>" + '<li class="disc"><a href="javascript:ajaxChat.sendMessageWrapper(\'/seen ' + encodedUserName + ' \');" title="When did this user last talk?">' + "Last Seen" + "</a></li>";
            if (this.userRole >= UC_STAFF) {
                menu += "<li><a href=\"javascript:ajaxChat.insertMessageWrapper('/kick " + encodedUserName + " ');\">" + this.lang["userMenuKick"] + "</a></li>" + "<li><a href=\"javascript:ajaxChat.sendMessageWrapper('/whois " + encodedUserName + "');\">" + this.lang["userMenuWhois"] + "</a></li>";
            }
        } else {
            menu = '<li class="disc"><a target="_blank" href="../pm_system.php" title="How many unread site PMs you have.">' + 'Unread PM (<span id="pmcount">0</span>)</a></li>' + "<li><a href=\"javascript:ajaxChat.sendMessageWrapper('/who');\">" + this.lang["userMenuWho"] + "</a></li>" + '<li class="circle"><a href="#" onclick="return ajaxChat.showHide(\'statslist\');" title="Click here to toggle visibility of stats list">Stats</a><ul style="display: none;" id="statslist">' + '<li class="disc"><a href="javascript:ajaxChat.sendMessageWrapper(\'/stats ' + encodedUserName + '\');" title="Display your stats in chat. [Private Message]">Your Stats' + "</a></li>" + '<li class="disc"><a href="javascript:ajaxChat.sendMessageWrapper(\'/casino \');" title="Show Casino and BlackJack Stats.">' + "Game Stats" + "</a></li>" + '<li class="disc"><a href="' + window.location.protocol + "//" + window.location.host + '/hnrs.php" target="_blank">Hit and Runs</a></li>' + '<li class="disc"><a href="' + window.location.protocol + "//" + window.location.host + '/port_check.php" target="_blank">Port Check</a></li>' + '<li class="disc"><a href="javascript:ajaxChat.sendMessageWrapper(\'/mentions \');" title="Show last 25 posts that mention you by name.">' + "Mentions" + "</a></li>" + "</ul></li>" + "<li><a href=\"javascript:ajaxChat.sendMessageWrapper('/ignore');\">" + this.lang["userMenuIgnoreList"] + "</a></li>" + "<li><a href=\"javascript:ajaxChat.sendMessageWrapper('/list');\">" + this.lang["userMenuList"] + "</a></li>" + "<li><a href=\"javascript:ajaxChat.insertMessageWrapper('/roll ');\">" + this.lang["userMenuRoll"] + "</a></li>" + "<li><a href=\"javascript:ajaxChat.insertMessageWrapper('/nick ');\">" + this.lang["userMenuNick"] + "</a></li>";
            if (this.userRole >= UC_POWER_USER) {
                menu += "<li><a href=\"javascript:ajaxChat.sendMessageWrapper('/join');\">" + this.lang["userMenuEnterPrivateRoom"] + "</a></li>";
                if (this.userRole >= UC_STAFF) {
                    menu += "<li><a href=\"javascript:ajaxChat.sendMessageWrapper('/bans');\">" + this.lang["userMenuBans"] + "</a></li>";
                }
                if (this.userRole >= UC_ADMINISTRATOR) {
                    menu += '<li><a href="../ajaxchat.php?view=logs" title="View AJAX Chat Logs.">' + "View Logs" + "</a></li>";
                }
            }
        }
        menu += this.getCustomUserMenuItems(encodedUserName, userID);
        return menu;
    },
    setOnlineListRowClasses: function() {
        if (this.dom["onlineList"]) {
            var node = this.dom["onlineList"].firstChild;
            var rowEven = false;
            while (node) {
                node.className = rowEven ? "rowEven" : "rowOdd";
                node = node.nextSibling;
                rowEven = !rowEven;
            }
        }
    },
    clearChatList: function() {
        while (this.dom["chatList"].hasChildNodes()) {
            this.dom["chatList"].removeChild(this.dom["chatList"].firstChild);
        }
    },
    clearOnlineUsersList: function() {
        this.usersList = [];
        this.userNamesList = [];
        if (this.dom["onlineList"]) {
            while (this.dom["onlineList"].hasChildNodes()) {
                this.dom["onlineList"].removeChild(this.dom["onlineList"].firstChild);
            }
        }
    },
    getEncodedChatBotName: function() {
        if (typeof arguments.callee.encodedChatBotName === "undefined") {
            arguments.callee.encodedChatBotName = this.encodeSpecialChars(this.chatBotName);
        }
        return arguments.callee.encodedChatBotName;
    },
    addChatBotMessageToChatList: function(messageText) {
        this.addMessageToChatList(new Date(), this.chatBotID, this.getEncodedChatBotName(), this.chatBotRole, null, messageText, null);
    },
    addMessageToChatList: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
        if (this.getMessageNode(messageID)) {
            return;
        }
        if (this.channelName === "Staff" && parseInt(channelID) !== parseInt(this.channelID) || this.channelName === "Sysop" && parseInt(channelID) !== parseInt(this.channelID) || this.channelName === "Support" && parseInt(channelID) !== parseInt(this.channelID) || this.channelName === "Announce" && parseInt(channelID) !== parseInt(this.channelID) || this.channelName === "News" && parseInt(channelID) !== parseInt(this.channelID) || this.channelName === "Git" && parseInt(channelID) !== parseInt(this.channelID)) {
            if (!this.DOMbuffering) {
                this.updateDOM("chatList", this.DOMbuffer, this.settings["postDirection"]);
                this.DOMbuffer = "";
            }
            return;
        }
        if (this.channelName === "Announce" && parseInt(userRole) !== 100 || this.channelName === "News" && parseInt(userRole) !== 100 || this.channelName === "Git" && parseInt(userRole) !== 100) {
            if (!this.DOMbuffering) {
                this.updateDOM("chatList", this.DOMbuffer, this.settings["postDirection"]);
                this.DOMbuffer = "";
            }
            return;
        }
        if (!this.onNewMessage(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip)) {
            if (!this.DOMbuffering) {
                this.updateDOM("chatList", this.DOMbuffer, this.settings["postDirection"]);
                this.DOMbuffer = "";
            }
            return;
        }
        this.DOMbufferRowClass = this.DOMbufferRowClass === "rowEven" ? "rowOdd" : "rowEven";
        this.DOMbuffer = this.DOMbuffer + this.getChatListMessageString(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip);
        if (!this.DOMbuffering) {
            this.updateDOM("chatList", this.DOMbuffer, this.settings["postDirection"]);
            this.DOMbuffer = "";
        }
    },
    getChatListMessageString: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
        var rowClass = this.DOMbufferRowClass, userClass = this.getRoleClass(userRole), colon = ": ";
        if (userRole == 100 && ~messageText.indexOf("Member Since")) {
            rowClass += " monospace";
        }
        if (messageText.indexOf("/action") === 0 || messageText.indexOf("/me") === 0 || messageText.indexOf("/privaction") === 0) {
            userClass += " action";
            colon = " ";
        }
        if (messageText.indexOf("/privmsg") === 0 || messageText.indexOf("/privmsgto") === 0 || messageText.indexOf("/privaction") === 0) {
            rowClass += " private";
        }
        var dateTime = this.settings["dateFormat"] ? '<span class="dateTime" title="' + this.formatDate(this.settings["dateFormatTooltip"], dateObject) + '">' + this.formatDate(this.settings["dateFormat"], dateObject) + "</span> " : "";
        return '<div id="' + this.getMessageDocumentID(messageID) + '" class="' + rowClass + '">' + this.getDeletionLink(messageID, userID, userRole, channelID) + dateTime + '<span class="' + userClass + '" title="Click to use@' + userName + ':"' + this.getChatListUserNameTitle(userID, userName, userRole, ip) + ' dir="' + this.baseDirection + '" onclick="ajaxChat.insertText(\'[' + this.getRoleClass(userRole) + "]@' + this.firstChild.nodeValue + '[/" + this.getRoleClass(userRole) + "] ');\">" + userName + "</span>" + colon + this.replaceText(messageText) + "</div>";
    },
    getChatListUserNameTitle: function(userID, userName, userRole, ip) {
        return ip !== null ? ' title="IP: ' + ip + '"' : "";
    },
    getMessageDocumentID: function(messageID) {
        return messageID === null ? "ajaxChat_lm_" + this.localID++ : "ajaxChat_m_" + messageID;
    },
    getMessageNode: function(messageID) {
        return messageID === null ? null : document.getElementById(this.getMessageDocumentID(messageID));
    },
    getUserDocumentID: function(userID) {
        return "ajaxChat_u_" + userID;
    },
    getUserNode: function(userID) {
        return document.getElementById(this.getUserDocumentID(userID));
    },
    getUserMenuDocumentID: function(userID) {
        return "ajaxChat_um_" + userID;
    },
    getInlineUserMenuDocumentID: function(menuID, index) {
        return "ajaxChat_ium_" + menuID + "_" + index;
    },
    getDeletionLink: function(messageID, userID, userRole, channelID) {
        if (messageID !== null && this.isAllowedToDeleteMessage(messageID, userID, userRole, channelID)) {
            if (!arguments.callee.deleteMessage) {
                arguments.callee.deleteMessage = this.encodeSpecialChars(this.lang["deleteMessage"]);
            }
            return '<a class="delete" title="' + arguments.callee.deleteMessage + '" href="javascript:ajaxChat.deleteMessage(' + messageID + ', false);"> </a>';
        }
        return "";
    },
    isAllowedToDeleteMessage: function(messageID, userID, userRole, channelID) {
        if (this.userRole >= UC_USER && this.allowUserMessageDelete && (userID === this.userID || parseInt(channelID) === parseInt(this.userID) + this.privateMessageDiff || parseInt(channelID) === parseInt(this.userID) + this.privateChannelDiff) || this.userRole >= UC_STAFF && this.allowUserMessageDelete && this.userRole > userRole || this.userRole >= UC_ADMINISTRATOR && (this.userRole > userRole || userRole === ChatBot)) {
            return true;
        }
        return false;
    },
    onNewMessage: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
        if (!this.customOnNewMessage(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip)) {
            return false;
        }
        if (this.ignoreMessage(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip)) {
            return false;
        }
        if (this.parseDeleteMessageCommand(messageText)) {
            return false;
        }
        this.blinkOnNewMessage(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip);
        this.playSoundOnNewMessage(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip);
        return true;
    },
    parseDeleteMessageCommand: function(messageText) {
        if (messageText.indexOf("/delete") === 0) {
            var messageID = messageText.substr(8);
            var messageNode = this.getMessageNode(messageID);
            if (messageNode) {
                var nextSibling = messageNode.nextSibling;
                try {
                    this.dom["chatList"].removeChild(messageNode);
                    if (nextSibling) {
                        this.updateChatListRowClasses(nextSibling);
                    }
                } catch (e) {}
            }
            return true;
        }
        return false;
    },
    blinkOnNewMessage: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
        if (this.settings["blink"] && this.lastID && !this.channelSwitch && userID !== this.userID) {
            clearInterval(this.blinkInterval);
            this.blinkInterval = setInterval("ajaxChat.blinkUpdate('" + this.addSlashes(this.decodeSpecialChars(userName)) + "')", this.settings["blinkInterval"]);
        }
    },
    blinkUpdate: function(blinkStr) {
        if (!this.originalDocumentTitle) {
            this.originalDocumentTitle = document.title;
        }
        if (!arguments.callee.blink) {
            document.title = "[@ ] " + blinkStr + " - " + this.originalDocumentTitle;
            arguments.callee.blink = 1;
        } else if (arguments.callee.blink > this.settings["blinkIntervalNumber"]) {
            clearInterval(this.blinkInterval);
            document.title = this.originalDocumentTitle;
            arguments.callee.blink = 0;
        } else {
            if (arguments.callee.blink % 2 !== 0) {
                document.title = "[@ ] " + blinkStr + " - " + this.originalDocumentTitle;
            } else {
                document.title = "[ @] " + blinkStr + " - " + this.originalDocumentTitle;
            }
            arguments.callee.blink++;
        }
    },
    updateChatlistView: function() {
        if (this.dom["chatList"].childNodes && this.settings["maxMessages"]) {
            while (this.dom["chatList"].childNodes.length > this.settings["maxMessages"]) {
                this.dom["chatList"].removeChild(this.dom["chatList"].firstChild);
            }
        }
        if (this.settings["autoScroll"]) {
            var self = this;
            setTimeout(function() {
                self.scrollChatList();
            }, 250);
        }
    },
    scrollChatList: function() {
        if (this.settings["postDirection"]) {
            this.dom["chatList"].scrollTop = 0;
        } else {
            this.dom["chatList"].scrollTop = this.dom["chatList"].scrollHeight;
        }
    },
    encodeText: function(text) {
        return encodeURIComponent(text);
    },
    decodeText: function(text) {
        return decodeURIComponent(text);
    },
    utf8Encode: function(plainText) {
        var utf8Text = "";
        for (var i = 0; i < plainText.length; i++) {
            var c = plainText.charCodeAt(i);
            if (c < 128) {
                utf8Text += String.fromCharCode(c);
            } else if (c > 127 && c < 2048) {
                utf8Text += String.fromCharCode(c >> 6 | 192);
                utf8Text += String.fromCharCode(c & 63 | 128);
            } else {
                utf8Text += String.fromCharCode(c >> 12 | 224);
                utf8Text += String.fromCharCode(c >> 6 & 63 | 128);
                utf8Text += String.fromCharCode(c & 63 | 128);
            }
        }
        return utf8Text;
    },
    utf8Decode: function(utf8Text) {
        var plainText = "";
        var c, c2, c3;
        var i = 0;
        while (i < utf8Text.length) {
            c = utf8Text.charCodeAt(i);
            if (c < 128) {
                plainText += String.fromCharCode(c);
                i++;
            } else if (c > 191 && c < 224) {
                c2 = utf8Text.charCodeAt(i + 1);
                plainText += String.fromCharCode((c & 31) << 6 | c2 & 63);
                i += 2;
            } else {
                c2 = utf8Text.charCodeAt(i + 1);
                c3 = utf8Text.charCodeAt(i + 2);
                plainText += String.fromCharCode((c & 15) << 12 | (c2 & 63) << 6 | c3 & 63);
                i += 3;
            }
        }
        return plainText;
    },
    encodeSpecialChars: function(text) {
        return text.replace(/[&<>'"]/g, this.encodeSpecialCharsCallback);
    },
    encodeSpecialCharsCallback: function(str) {
        switch (str) {
          case "&":
            return "&amp;";

          case "<":
            return "&lt;";

          case ">":
            return "&gt;";

          case "'":
            return "&#39;";

          case '"':
            return "&quot;";

          default:
            return str;
        }
    },
    decodeSpecialChars: function(text) {
        var regExp = new RegExp("(&amp;)|(&lt;)|(&gt;)|(&#39;)|(&quot;)", "g");
        return text.replace(regExp, this.decodeSpecialCharsCallback);
    },
    decodeSpecialCharsCallback: function(str) {
        switch (str) {
          case "&amp;":
            return "&";

          case "&lt;":
            return "<";

          case "&gt;":
            return ">";

          case "&#39;":
            return "'";

          case "&quot;":
            return '"';

          default:
            return str;
        }
    },
    inArray: function(haystack, needle) {
        var i = haystack.length;
        while (i--) {
            if (haystack[i] === needle) {
                return true;
            }
        }
        return false;
    },
    arraySearch: function(needle, haystack) {
        if (!Array.prototype.indexOf) {
            var i = haystack.length;
            while (i--) {
                if (haystack[i] === needle) {
                    return i;
                }
            }
            return -1;
        } else {
            return haystack.indexOf(needle);
        }
    },
    stripTags: function(str) {
        if (!arguments.callee.regExp) {
            arguments.callee.regExp = new RegExp("<\\/?[^>]+?>", "g");
        }
        return str.replace(arguments.callee.regExp, "");
    },
    stripBBCodeTags: function(str) {
        if (!arguments.callee.regExp) {
            arguments.callee.regExp = new RegExp("\\[\\/?[^\\]]+?\\]", "g");
        }
        return str.replace(arguments.callee.regExp, "");
    },
    escapeRegExp: function(text) {
        if (!arguments.callee.regExp) {
            var specials = [ "^", "$", "*", "+", "?", ".", "|", "/", "(", ")", "[", "]", "{", "}", "\\" ];
            arguments.callee.regExp = new RegExp("(\\" + specials.join("|\\") + ")", "g");
        }
        return text.replace(arguments.callee.regExp, "\\$1");
    },
    addSlashes: function(text) {
        return text.replace(/\\/g, "\\\\").replace(/\'/g, "\\'");
    },
    removeSlashes: function(text) {
        return text.replace(/\\\\/g, "\\").replace(/\\\'/g, "'");
    },
    formatDate: function(format, date) {
        date = date === null ? new date() : date;
        var week = [ "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday" ];
        var month = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
        return format.replace(/%Y/g, date.getFullYear()).replace(/%m/g, this.addLeadingZero(date.getMonth() + 1)).replace(/%F/g, month[date.getMonth()]).replace(/%l/g, week[date.getDay()]).replace(/%d/g, this.addLeadingZero(date.getDate())).replace(/%H/g, this.addLeadingZero(date.getHours())).replace(/%i/g, this.addLeadingZero(date.getMinutes())).replace(/%s/g, this.addLeadingZero(date.getSeconds()));
    },
    addLeadingZero: function(number) {
        number = number.toString();
        if (number.length < 2) {
            number = "0" + number;
        }
        return number;
    },
    getUserIDFromUserName: function(userName) {
        var index = this.arraySearch(userName, this.userNamesList);
        if (index !== -1) {
            return this.usersList[index];
        }
        return null;
    },
    getUserNameFromUserID: function(userID) {
        var index = this.arraySearch(userID, this.usersList);
        if (index !== -1) {
            return this.userNamesList[index];
        }
        return null;
    },
    getRoleClass: function(roleID) {
        switch (parseInt(roleID)) {
          case parseInt(UC_USER):
            return "user";

          case parseInt(UC_POWER_USER):
            return "power_user";

          case parseInt(UC_VIP):
            return "vip";

          case parseInt(UC_UPLOADER):
            return "uploader";

          case parseInt(UC_MODERATOR):
            return "moderator";

          case parseInt(UC_ADMINISTRATOR):
            return "administrator";

          case parseInt(UC_SYSOP):
            return "sysop";

          case parseInt(this.chatBotRole):
            return "chatbot";

          default:
            return "user";
        }
    },
    handleInputFieldKeyDown: function(event) {
        var text, lastWord, i;
        if (event.keyCode === 13 && !event.shiftKey) {
            this.sendMessage();
            try {
                event.preventDefault();
            } catch (e) {
                event.returnValue = false;
            }
            return false;
        } else if (event.keyCode === 9 && !event.shiftKey) {
            text = this.dom["inputField"].value;
            if (text) {
                lastWord = text.match(/\w+/g).slice(-1)[0];
                if (lastWord.length > 2) {
                    for (i = 0; i < this.userNamesList.length; i++) {
                        if (this.userNamesList[i].replace("(", "").toLowerCase().indexOf(lastWord.toLowerCase()) === 0) {
                            this.dom["inputField"].value = text.replace(new RegExp(lastWord + "$"), this.userNamesList[i]);
                            break;
                        }
                    }
                }
            }
            try {
                event.preventDefault();
            } catch (e) {
                event.returnValue = false;
            }
            return false;
        }
        return true;
    },
    handleInputFieldKeyUp: function(event) {
        this.updateMessageLengthCounter();
    },
    updateMessageLengthCounter: function() {
        if (this.dom["messageLengthCounter"]) {
            this.updateDOM("messageLengthCounter", this.dom["inputField"].value.length + "/" + this.messageTextMaxLength, false, true);
        }
    },
    sendMessage: function(text) {
        text = text ? text : this.dom["inputField"].value;
        if (!text) {
            return;
        }
        text = this.parseInputMessage(text);
        if (text) {
            clearTimeout(this.timer);
            var message = "lastID=" + this.lastID + "&text=" + this.encodeText(text);
            this.makeRequest(this.ajaxURL, "POST", message);
        }
        this.dom["inputField"].value = "";
        this.dom["inputField"].focus();
        this.updateMessageLengthCounter();
    },
    parseInputMessage: function(text) {
        var textParts;
        if (text.charAt(0) === "/") {
            textParts = text.split(" ");
            switch (textParts[0]) {
              case "/ignore":
                text = this.parseIgnoreInputCommand(text, textParts);
                break;

              case "/clear":
                this.clearChatList();
                return false;
                break;

              default:
                text = this.parseCustomInputCommand(text, textParts);
            }
            if (text && this.settings["persistFontColor"] && this.settings["fontColor"]) {
                text = this.assignFontColorToCommandMessage(text, textParts);
            }
        } else {
            text = this.parseCustomInputMessage(text);
            if (text && this.settings["persistFontColor"] && this.settings["fontColor"]) {
                text = this.assignFontColorToMessage(text);
            }
        }
        return text;
    },
    assignFontColorToMessage: function(text) {
        return "[color=" + this.settings["fontColor"] + "]" + text + "[/color]";
    },
    assignFontColorToCommandMessage: function(text, textParts) {
        switch (textParts[0]) {
          case "/msg":
          case "/describe":
            if (textParts.length > 2) {
                return textParts[0] + " " + textParts[1] + " " + "[color=" + this.settings["fontColor"] + "]" + textParts.slice(2).join(" ") + "[/color]";
            }
            break;

          case "/me":
          case "/action":
            if (textParts.length > 1) {
                return textParts[0] + " " + "[color=" + this.settings["fontColor"] + "]" + textParts.slice(1).join(" ") + "[/color]";
            }
            break;
        }
        return text;
    },
    parseIgnoreInputCommand: function(text, textParts) {
        var userName, ignoredUserNames = this.getIgnoredUserNames(), i;
        if (textParts.length > 1) {
            userName = this.encodeSpecialChars(textParts[1]);
            if (userName === this.userName || userName === this.getEncodedChatBotName()) {
                return this.parseIgnoreInputCommand(null, new Array("/ignore"));
            }
            if (ignoredUserNames.length > 0) {
                i = ignoredUserNames.length;
                while (i--) {
                    if (ignoredUserNames[i] === userName) {
                        ignoredUserNames.splice(i, 1);
                        this.addChatBotMessageToChatList("/ignoreRemoved " + userName);
                        this.setIgnoredUserNames(ignoredUserNames);
                        this.updateChatlistView();
                        return null;
                    }
                }
            }
            ignoredUserNames.push(userName);
            this.addChatBotMessageToChatList("/ignoreAdded " + userName);
            this.setIgnoredUserNames(ignoredUserNames);
        } else {
            if (ignoredUserNames.length === 0) {
                this.addChatBotMessageToChatList("/ignoreListEmpty -");
            } else {
                this.addChatBotMessageToChatList("/ignoreList " + ignoredUserNames.join(" "));
            }
        }
        this.updateChatlistView();
        return null;
    },
    getIgnoredUserNames: function() {
        var ignoredUserNamesString;
        if (!this.ignoredUserNames) {
            ignoredUserNamesString = this.getSetting("ignoredUserNames");
            if (ignoredUserNamesString) {
                this.ignoredUserNames = ignoredUserNamesString.split(" ");
            } else {
                this.ignoredUserNames = [];
            }
        }
        return this.ignoredUserNames;
    },
    setIgnoredUserNames: function(ignoredUserNames) {
        this.ignoredUserNames = ignoredUserNames;
        this.setSetting("ignoredUserNames", ignoredUserNames.join(" "));
    },
    ignoreMessage: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
        var textParts;
        if (userID === this.chatBotID && messageText.charAt(0) === "/") {
            textParts = messageText.split(" ");
            if (textParts.length > 1) {
                switch (textParts[0]) {
                  case "/invite":
                  case "/uninvite":
                  case "/roll":
                    userName = textParts[1];
                    break;
                }
            }
        }
        if (this.inArray(this.getIgnoredUserNames(), userName)) {
            return true;
        }
        return false;
    },
    deleteMessage: function(messageID, askUser) {
        var messageNode = this.getMessageNode(messageID), originalClass, nextSibling;
        if (messageNode) {
            originalClass = messageNode.className;
            this.addClass(messageNode, "deleteSelected");
            if (askUser === false) {
                var askUser = confirm(this.lang["deleteMessageConfirm"]);
            }
            if (askUser === true) {
                nextSibling = messageNode.nextSibling;
                try {
                    this.dom["chatList"].removeChild(messageNode);
                    if (nextSibling) {
                        this.updateChatListRowClasses(nextSibling);
                    }
                    this.updateChat("&delete=" + messageID);
                } catch (e) {
                    messageNode.className = originalClass;
                }
            } else {
                messageNode.className = originalClass;
            }
        }
    },
    updateChatListRowClasses: function(node) {
        var previousNode, rowEven;
        if (!node) {
            node = this.dom["chatList"].firstChild;
        }
        if (node) {
            previousNode = node.previousSibling;
            rowEven = previousNode && previousNode.className === "rowOdd" ? true : false;
            while (node) {
                node.className = rowEven ? "rowEven" : "rowOdd";
                node = node.nextSibling;
                rowEven = !rowEven;
            }
        }
    },
    addEvent: function(elem, type, eventHandle) {
        if (!elem) return;
        if (elem.addEventListener) {
            elem.addEventListener(type, eventHandle, false);
        } else if (elem.attachEvent) {
            elem.attachEvent("on" + type, eventHandle);
        } else {
            elem["on" + type] = eventHandle;
        }
    },
    addClass: function(node, theClass) {
        if (!this.hasClass(node, theClass)) {
            node.className += " " + theClass;
        }
    },
    removeClass: function(node, theClass) {
        node.className = node.className.replace(new RegExp("(?:^|\\s)" + theClass + "(?!\\S)", "g"), "");
    },
    hasClass: function(node, theClass) {
        return node.className.match(new RegExp("\\b" + theClass + "\\b"));
    },
    scriptLinkEncode: function(text) {
        return this.encodeText(this.addSlashes(this.decodeSpecialChars(text)));
    },
    scriptLinkDecode: function(text) {
        return this.encodeSpecialChars(this.removeSlashes(this.decodeText(text)));
    },
    getScriptLinkValue: function(value) {
        if (typeof arguments.callee.utf8Decode === "undefined") {
            switch (navigator.appName) {
              case "Microsoft Internet Explorer":
              case "Opera":
                arguments.callee.utf8Decode = true;
                return this.utf8Decode(value);

              default:
                arguments.callee.utf8Decode = false;
                return value;
            }
        } else if (arguments.callee.utf8Decode) {
            return this.utf8Decode(value);
        } else {
            return value;
        }
    },
    sendMessageWrapper: function(text) {
        this.sendMessage(this.getScriptLinkValue(text));
    },
    insertMessageWrapper: function(text) {
        this.insertText(this.getScriptLinkValue(text), true);
    },
    switchChannel: function(channel) {
        if (!this.chatStarted) {
            this.clearChatList();
            this.channelSwitch = true;
            this.loginChannelID = null;
            this.loginChannelName = channel;
            this.requestTeaserContent();
            return;
        }
        clearTimeout(this.timer);
        var message = "lastID=" + this.lastID + "&channelName=" + this.encodeText(channel);
        this.makeRequest(this.ajaxURL, "POST", message);
        if (this.dom["inputField"] && this.settings["autoFocus"]) {
            this.dom["inputField"].focus();
        }
    },
    logout: function() {
        clearTimeout(this.timer);
        var message = "";
        this.makeRequest(this.ajaxURL + "&token=" + this.token, "POST", message);
    },
    handleLogout: function(url) {
        window.location.href = url;
    },
    toggleSetting: function(setting, buttonID) {
        this.setSetting(setting, !this.getSetting(setting));
        if (buttonID) {
            this.updateButton(setting, buttonID);
        }
    },
    updateButton: function(setting, buttonID) {
        var node = document.getElementById(buttonID);
        if (node) {
            node.className = this.getSetting(setting) ? "button" : "button off";
        }
    },
    showHide: function(id, styleDisplay, displayInline) {
        var node = document.getElementById(id);
        if (node) {
            if (styleDisplay) {
                node.style.display = styleDisplay;
            } else {
                if (node.style.display === "none") {
                    node.style.display = displayInline ? "inline" : "block";
                } else {
                    node.style.display = "none";
                }
            }
        }
        return false;
    },
    setPersistFontColor: function(bool) {
        this.settings["persistFontColor"] = bool;
        if (!this.settings["persistFontColor"]) {
            this.settings["fontColor"] = null;
            if (this.dom["inputField"]) {
                this.dom["inputField"].style.color = "";
            }
        }
    },
    setFontColor: function(color) {
        if (this.settings["persistFontColor"]) {
            this.settings["fontColor"] = color;
            if (this.dom["inputField"]) {
                this.dom["inputField"].style.color = color;
            }
            if (this.dom["colorCodesContainer"]) {
                this.dom["colorCodesContainer"].style.display = "none";
                if (this.dom["inputField"]) {
                    this.dom["inputField"].focus();
                }
            }
        } else {
            this.insert("[color=" + color + "]", "[/color]");
        }
    },
    insertText: function(text, clearInputField) {
        if (clearInputField) {
            this.dom["inputField"].value = "";
        }
        this.insert(text, "");
    },
    insertBBCode: function(bbCode) {
        switch (bbCode) {
          case "url":
            var url = prompt(this.lang["urlDialog"], "http://");
            if (url) this.insert("[url=" + url + "] ", "[/url]"); else this.dom["inputField"].focus();
            break;

          default:
            this.insert("[" + bbCode + "]", "[/" + bbCode + "]");
        }
    },
    insert: function(startTag, endTag) {
        this.dom["inputField"].focus();
        if (typeof document.selection !== "undefined") {
            var range = document.selection.createRange();
            var insText = range.text;
            range.text = startTag + insText + endTag;
            range = document.selection.createRange();
            if (insText.length === 0) {
                range.move("character", -endTag.length);
            } else {
                range.moveStart("character", startTag.length + insText.length + endTag.length);
            }
            range.select();
        } else if (typeof this.dom["inputField"].selectionStart !== "undefined") {
            var start = this.dom["inputField"].selectionStart;
            var end = this.dom["inputField"].selectionEnd;
            var insText = this.dom["inputField"].value.substring(start, end);
            this.dom["inputField"].value = this.dom["inputField"].value.substr(0, start) + startTag + insText + endTag + this.dom["inputField"].value.substr(end);
            var pos;
            if (insText.length === 0) {
                pos = start + startTag.length;
            } else {
                pos = start + startTag.length + insText.length + endTag.length;
            }
            this.dom["inputField"].selectionStart = pos;
            this.dom["inputField"].selectionEnd = pos;
        } else {
            var pos = this.dom["inputField"].value.length;
            this.dom["inputField"].value = this.dom["inputField"].value.substr(0, pos) + startTag + endTag + this.dom["inputField"].value.substr(pos);
        }
    },
    replaceText: function(text) {
        try {
            text = text.replace(/&amp;#039;/g, "'");
            text = text.replace(/&amp;amp;/g, "&amp;");
            text = this.replaceLineBreaks(text);
            text = this.replaceCustomText(text);
            if (text.charAt(0) === "/") {
                text = this.replaceCommands(text);
            } else {
                text = this.replaceBBCode(text);
                text = this.replaceBBCode(text);
                text = this.replaceHyperLinks(text);
                text = this.replaceEmoticons(text);
            }
            text = this.breakLongWords(text);
            if (text.toLowerCase().indexOf(this.userName.toLowerCase()) !== -1) {
                this.playSound(this.settings["soundPrivate"]);
                text = '<span class="mentioned">' + text + "</span>";
            }
        } catch (e) {
            this.debugMessage("replaceText", e);
        }
        return text;
    },
    replaceCommands: function(text) {
        try {
            if (text.charAt(0) !== "/") {
                return text;
            }
            var textParts = text.split(" ");
            switch (textParts[0]) {
              case "/privmsg":
                return this.replaceCommandPrivMsg(textParts);

              case "/privmsgto":
                return this.replaceCommandPrivMsgTo(textParts);

              case "/privaction":
                return this.replaceCommandPrivAction(textParts);

              case "/privactionto":
                return this.replaceCommandPrivActionTo(textParts);

              case "/me":
              case "/action":
                return this.replaceCommandAction(textParts);

              case "/invite":
                return this.replaceCommandInvite(textParts);

              case "/inviteto":
                return this.replaceCommandInviteTo(textParts);

              case "/uninvite":
                return this.replaceCommandUninvite(textParts);

              case "/uninviteto":
                return this.replaceCommandUninviteTo(textParts);

              case "/queryOpen":
                return this.replaceCommandQueryOpen(textParts);

              case "/queryClose":
                return this.replaceCommandQueryClose(textParts);

              case "/ignoreAdded":
                return this.replaceCommandIgnoreAdded(textParts);

              case "/ignoreRemoved":
                return this.replaceCommandIgnoreRemoved(textParts);

              case "/ignoreList":
                return this.replaceCommandIgnoreList(textParts);

              case "/ignoreListEmpty":
                return this.replaceCommandIgnoreListEmpty(textParts);

              case "/kick":
                return this.replaceCommandKick(textParts);

              case "/who":
                return this.replaceCommandWho(textParts);

              case "/whoChannel":
                return this.replaceCommandWhoChannel(textParts);

              case "/whoEmpty":
                return this.replaceCommandWhoEmpty(textParts);

              case "/list":
                return this.replaceCommandList(textParts);

              case "/bans":
                return this.replaceCommandBans(textParts);

              case "/bansEmpty":
                return this.replaceCommandBansEmpty(textParts);

              case "/unban":
                return this.replaceCommandUnban(textParts);

              case "/whois":
                return this.replaceCommandWhois(textParts);

              case "/whereis":
                return this.replaceCommandWhereis(textParts);

              case "/gift":
                return this.replaceCommandGift(textParts);

              case "/rep":
                return this.replaceCommandRep(textParts);

              case "/seen":
                return this.replaceCommandSeen(textParts);

              case "/mentions":
                return this.replaceCommandMentions(textParts);

              case "/stats":
                return this.replaceCommandStats(textParts);

              case "/casino":
                return this.replaceCommandCasino(textParts);

              case "/roll":
                return this.replaceCommandRoll(textParts);

              case "/nick":
                return this.replaceCommandNick(textParts);

              case "/error":
                return this.replaceCommandError(textParts);

              default:
                return this.replaceCustomCommands(text, textParts);
            }
        } catch (e) {
            this.debugMessage("replaceCommands", e);
        }
        return text;
    },
    replaceCommandPrivMsg: function(textParts) {
        var privMsgText = textParts.slice(1).join(" ");
        privMsgText = this.replaceBBCode(privMsgText);
        privMsgText = this.replaceHyperLinks(privMsgText);
        privMsgText = this.replaceEmoticons(privMsgText);
        return '<span class="privmsg">' + this.lang["privmsg"] + "</span> " + privMsgText;
    },
    replaceCommandPrivMsgTo: function(textParts) {
        var privMsgText = textParts.slice(2).join(" ");
        privMsgText = this.replaceBBCode(privMsgText);
        privMsgText = this.replaceHyperLinks(privMsgText);
        privMsgText = this.replaceEmoticons(privMsgText);
        return '<span class="privmsg">' + this.lang["privmsgto"].replace(/%s/, textParts[1]) + "</span> " + privMsgText;
    },
    replaceCommandPrivAction: function(textParts) {
        var privActionText = textParts.slice(1).join(" ");
        privActionText = this.replaceBBCode(privActionText);
        privActionText = this.replaceHyperLinks(privActionText);
        privActionText = this.replaceEmoticons(privActionText);
        return '<span class="action">' + privActionText + '</span> <span class="privmsg">' + this.lang["privmsg"] + "</span> ";
    },
    replaceCommandPrivActionTo: function(textParts) {
        var privActionText = textParts.slice(2).join(" ");
        privActionText = this.replaceBBCode(privActionText);
        privActionText = this.replaceHyperLinks(privActionText);
        privActionText = this.replaceEmoticons(privActionText);
        return '<span class="action">' + privActionText + '</span> <span class="privmsg">' + this.lang["privmsgto"].replace(/%s/, textParts[1]) + "</span> ";
    },
    replaceCommandAction: function(textParts) {
        var actionText = textParts.slice(1).join(" ");
        actionText = this.replaceBBCode(actionText);
        actionText = this.replaceHyperLinks(actionText);
        actionText = this.replaceEmoticons(actionText);
        return '<span class="action">' + actionText + "</span>";
    },
    replaceCommandInvite: function(textParts) {
        var inviteText = this.lang["invite"].replace(/%s/, textParts[1]).replace(/%s/, "<a href=\"javascript:ajaxChat.sendMessageWrapper('/join " + this.scriptLinkEncode(textParts[2]) + '\');" title="' + this.lang["joinChannel"].replace(/%s/, textParts[2]) + '">' + textParts[2] + "</a>");
        return '<span class="chatBotMessage">' + inviteText + "</span>";
    },
    replaceCommandInviteTo: function(textParts) {
        var inviteText = this.lang["inviteto"].replace(/%s/, textParts[1]).replace(/%s/, textParts[2]);
        return '<span class="chatBotMessage">' + inviteText + "</span>";
    },
    replaceCommandUninvite: function(textParts) {
        var uninviteText = this.lang["uninvite"].replace(/%s/, textParts[1]).replace(/%s/, textParts[2]);
        return '<span class="chatBotMessage">' + uninviteText + "</span>";
    },
    replaceCommandUninviteTo: function(textParts) {
        var uninviteText = this.lang["uninviteto"].replace(/%s/, textParts[1]).replace(/%s/, textParts[2]);
        return '<span class="chatBotMessage">' + uninviteText + "</span>";
    },
    replaceCommandQueryOpen: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["queryOpen"].replace(/%s/, textParts[1]) + "</span>";
    },
    replaceCommandQueryClose: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["queryClose"].replace(/%s/, textParts[1]) + "</span>";
    },
    replaceCommandIgnoreAdded: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["ignoreAdded"].replace(/%s/, textParts[1]) + "</span>";
    },
    replaceCommandIgnoreRemoved: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["ignoreRemoved"].replace(/%s/, textParts[1]) + "</span>";
    },
    replaceCommandIgnoreList: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["ignoreList"] + " " + this.getInlineUserMenu(textParts.slice(1)) + "</span>";
    },
    replaceCommandIgnoreListEmpty: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["ignoreListEmpty"] + "</span>";
    },
    replaceCommandKick: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["logoutKicked"].replace(/%s/, textParts[1]) + "</span>";
    },
    replaceCommandWho: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["who"] + " " + this.getInlineUserMenu(textParts.slice(1)) + "</span>";
    },
    replaceCommandWhoChannel: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["whoChannel"].replace(/%s/, textParts[1]) + " " + this.getInlineUserMenu(textParts.slice(2)) + "</span>";
    },
    replaceCommandWhoEmpty: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["whoEmpty"] + "</span>";
    },
    replaceCommandList: function(textParts) {
        var channels = textParts.slice(1);
        var listChannels = [];
        var channelName;
        for (var i = 0; i < channels.length; i++) {
            channelName = channels[i] === this.channelName ? "<b>" + channels[i] + "</b>" : channels[i];
            listChannels.push("<a href=\"javascript:ajaxChat.sendMessageWrapper('/join " + this.scriptLinkEncode(channels[i]) + '\');" title="' + this.lang["joinChannel"].replace(/%s/, channels[i]) + '">' + channelName + "</a>");
        }
        return '<span class="chatBotMessage">' + this.lang["list"] + " " + listChannels.join(", ") + "</span>";
    },
    replaceCommandBans: function(textParts) {
        var users = textParts.slice(1);
        var listUsers = [];
        for (var i = 0; i < users.length; i++) {
            listUsers.push("<a href=\"javascript:ajaxChat.sendMessageWrapper('/unban " + this.scriptLinkEncode(users[i]) + '\');" title="' + this.lang["unbanUser"].replace(/%s/, users[i]) + '">' + users[i] + "</a>");
        }
        return '<span class="chatBotMessage">' + this.lang["bans"] + " " + listUsers.join(", ") + "</span>";
    },
    replaceCommandBansEmpty: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["bansEmpty"] + "</span>";
    },
    replaceCommandUnban: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["unban"].replace(/%s/, textParts[1]) + "</span>";
    },
    replaceCommandWhois: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["whois"].replace(/%s/, textParts[1]) + " " + '<a title="Geolocation" target="_blank" href="http://www.infosniper.net/index.php?ip_address=' + textParts[2] + '">' + textParts[2] + "</a>" + "</span>";
    },
    replaceCommandStats: function(textParts) {
        return this.replaceBBCode(textParts);
    },
    replaceCommandGift: function(textParts) {
        return this.replaceBBCode(textParts);
    },
    replaceCommandRep: function(textParts) {
        return this.replaceBBCode(textParts);
    },
    replaceCommandSeen: function(textParts) {
        return this.replaceBBCode(textParts);
    },
    replaceCommandMentions: function(textParts) {
        return this.replaceBBCode(textParts);
    },
    replaceCommandCasino: function(textParts) {
        return this.replaceBBCode(textParts);
    },
    replaceCommandWhereis: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["whereis"].replace(/%s/, textParts[1]).replace(/%s/, "<a href=\"javascript:ajaxChat.sendMessageWrapper('/join " + this.scriptLinkEncode(textParts[2]) + '\');" title="' + this.lang["joinChannel"].replace(/%s/, textParts[2]) + '">' + textParts[2] + "</a>") + "</span>";
    },
    replaceCommandRoll: function(textParts) {
        var rollText = this.lang["roll"].replace(/%s/, textParts[1]);
        rollText = rollText.replace(/%s/, textParts[2]);
        rollText = rollText.replace(/%s/, textParts[3]);
        return '<span class="chatBotMessage">' + '<img src="' + ajaxChat.dirs["emoticons"] + 'dice.gif" alt="dice" />' + rollText + "</span>";
    },
    replaceCommandNick: function(textParts) {
        return '<span class="chatBotMessage">' + this.lang["nick"].replace(/%s/, textParts[1]).replace(/%s/, textParts[2]) + "</span>";
    },
    replaceCommandError: function(textParts) {
        var errorMessage = this.lang["error" + textParts[1]];
        if (!errorMessage) {
            errorMessage = "Error: Unknown.";
        } else if (textParts.length > 2) {
            errorMessage = errorMessage.replace(/%s/, textParts.slice(2).join(" "));
        }
        return '<span class="chatBotErrorMessage">' + errorMessage + "</span>";
    },
    getInlineUserMenu: function(users) {
        var menu = "";
        for (var i = 0; i < users.length; i++) {
            if (i > 0) {
                menu += ", ";
            }
            menu += "<a href=\"javascript:ajaxChat.toggleUserMenu('" + this.getInlineUserMenuDocumentID(this.userMenuCounter, i) + "', '" + this.scriptLinkEncode(users[i]) + '\', null);" title="' + this.lang["toggleUserMenu"].replace(/%s/, users[i]) + '" dir="' + this.baseDirection + '">' + (users[i] === this.userName ? "<b>" + users[i] + "</b>" : users[i]) + "</a>" + '<ul class="inlineUserMenu" id="' + this.getInlineUserMenuDocumentID(this.userMenuCounter, i) + '" style="display:none;">' + "</ul>";
        }
        this.userMenuCounter++;
        return menu;
    },
    containsUnclosedTags: function(str) {
        var openTags, closeTags, regExpOpenTags = /<[^>\/]+?>/gm, regExpCloseTags = /<\/[^>]+?>/gm;
        openTags = str.match(regExpOpenTags);
        closeTags = str.match(regExpCloseTags);
        if (!openTags && closeTags || openTags && !closeTags || openTags && closeTags && openTags.length !== closeTags.length) {
            return true;
        }
        return false;
    },
    breakLongWords: function(text) {
        var newText, charCounter, currentChar, withinTag, withinEntity, i;
        if (!this.settings["wordWrap"]) return text;
        newText = "";
        charCounter = 0;
        for (i = 0; i < text.length; i++) {
            currentChar = text.charAt(i);
            if (currentChar === "<") {
                withinTag = true;
                if (i > 5 && text.substr(i - 5, 4) === "<br/") charCounter = 0;
            } else if (withinTag && i > 0 && text.charAt(i - 1) === ">") {
                withinTag = false;
                if (i > 4 && text.substr(i - 5, 4) === "<br/") charCounter = 0;
            }
            if (!withinTag && currentChar === "&") {
                withinEntity = true;
            } else if (withinEntity && i > 0 && text.charAt(i - 1) === ";") {
                withinEntity = false;
                charCounter++;
            }
            if (!withinTag && !withinEntity) {
                if (currentChar === " " || currentChar === "\n" || currentChar === "\t") {
                    charCounter = 0;
                } else {
                    charCounter++;
                }
                if (charCounter > this.settings["maxWordLength"]) {
                    newText += "&#8203;";
                    charCounter = 0;
                }
            }
            newText += currentChar;
        }
        return newText;
    },
    replaceBBCode: function(text) {
        if (!this.settings["bbCode"]) {
            return text.replace(/\[(?:\/)?(\w+)(?:=([^<>]*?))?\]/, "");
        }
        return text.replace(/\[(\w+)(?:=([^<>]*?))?\](.+?)\[\/\1\]/gm, this.replaceBBCodeCallback);
    },
    replaceBBCodeCallback: function(str, p1, p2, p3) {
        if (!ajaxChat.inArray(ajaxChat.bbCodeTags, p1)) {
            return str;
        }
        if (ajaxChat.containsUnclosedTags(p3)) {
            return str;
        }
        switch (p1) {
          case "color":
            return ajaxChat.replaceBBCodeColor(p3, p2);

          case "url":
            return ajaxChat.replaceBBCodeUrl(p3, p2);

          case "img":
            return ajaxChat.replaceBBCodeImage(p3);

          case "quote":
            return ajaxChat.replaceBBCodeQuote(p3, p2);

          case "code":
            return ajaxChat.replaceBBCodeCode(p3);

          case "u":
            return ajaxChat.replaceBBCodeUnderline(p3);

          case "b":
            return ajaxChat.replaceBBCodeBold(p3);

          case "i":
            return ajaxChat.replaceBBCodeItalic(p3);

          default:
            return ajaxChat.replaceCustomBBCode(p1, p2, p3);
        }
    },
    replaceBBCodeColor: function(content, attribute) {
        if (this.settings["bbCodeColors"]) {
            return '<span style="color:' + attribute + ';">' + this.replaceBBCode(content) + "</span>";
        }
        return content;
    },
    replaceBBCodeUrl: function(content, attribute) {
        var url, regExpUrl, link;
        if (attribute) {
            url = attribute.replace(/\s/gm, this.encodeText(" "));
        } else {
            url = this.stripBBCodeTags(content.replace(/\s/gm, this.encodeText(" ")));
        }
        regExpUrl = new RegExp("^(?:(?:http)|(?:https)|(?:ftp)|(?:irc)):\\/\\/", "");
        if (!url || !url.match(regExpUrl)) {
            return content;
        }
        this.inUrlBBCode = true;
        var hostname = new RegExp(window.location.hostname, "g");
        var res = url.match(hostname);
        if (!res) {
            url = this.anonymizer + url;
        }
        link = '<a href="' + url + '" onclick="window.open(this.href); return false;">' + this.replaceBBCode(content) + "</a>";
        this.inUrlBBCode = false;
        return link;
    },
    replaceBBCodeImage: function(url) {
        var regExpUrl, maxWidth, maxHeight, link;
        if (this.settings["bbCodeImages"]) {
            regExpUrl = new RegExp(this.regExpMediaUrl, "");
            if (!url || !url.match(regExpUrl)) {
                return url;
            }
            url = this.stripTags(url.replace(/\s/gm, this.encodeText(" ")));
            maxWidth = this.dom["chatList"].offsetWidth - 50;
            maxHeight = this.dom["chatList"].offsetHeight - 50;
            link = '<img class="bbCodeImage" style="max-width:' + maxWidth + "px; max-height:" + maxHeight + 'px;" src="' + url + '" alt="" onload="ajaxChat.updateChatlistView();"/>';
            if (!this.inUrlBBCode) {
                link = '<a href="' + url + '" onclick="window.open(this.href); return false;">' + link + "</a>";
            }
            return link;
        }
        return url;
    },
    replaceBBCodeQuote: function(content, attribute) {
        if (attribute) return '<span class="quote"><cite>' + this.lang["cite"].replace(/%s/, attribute) + "</cite><q>" + this.replaceBBCode(content) + "</q></span>";
        return '<span class="quote"><q>' + this.replaceBBCode(content) + "</q></span>";
    },
    replaceBBCodeCode: function(content) {
        return "<code>" + this.replaceBBCode(content.replace(/\t|(?:  )/gm, "&#160;&#160;")) + "</code>";
    },
    replaceBBCodeUnderline: function(content) {
        return '<span style="text-decoration:underline;">' + this.replaceBBCode(content) + "</span>";
    },
    replaceBBCodeBold: function(content) {
        return '<span style="font-weight:bold;">' + this.replaceBBCode(content) + "</span>";
    },
    replaceBBCodeItalic: function(content) {
        return '<span style="font-style: italic;">' + this.replaceBBCode(content) + "</span>";
    },
    replaceHyperLinks: function(text) {
        var regExp;
        if (!this.settings["hyperLinks"]) {
            return text;
        }
        regExp = new RegExp("(^|\\s|>)((?:(?:http)|(?:https)|(?:ftp)|(?:irc)):\\/\\/[^\\s<>]+)(<\\/a>)?", "gm");
        return text.replace(regExp, function(str, p1, p2, p3) {
            if (p3) {
                return str;
            }
            var hostname = new RegExp(window.location.hostname, "g");
            var res = text.match(hostname);
            if (res) {
                url = p2;
            } else {
                url = this.anonymizer + p2;
            }
            return p1 + '<a href="' + url + '" onclick="window.open(this.href); return false;">' + p2 + "</a>";
        });
    },
    replaceLineBreaks: function(text) {
        var regExp = new RegExp("\\n", "g");
        if (!this.settings["lineBreaks"]) {
            return text.replace(regExp, " ");
        } else {
            return text.replace(regExp, "<br/>");
        }
    },
    replaceEmoticons: function(text) {
        if (!this.settings["emoticons"]) {
            return text;
        }
        if (!arguments.callee.regExp) {
            var regExpStr = "^(.*)(";
            for (var i = 0; i < this.emoticonCodes.length; i++) {
                if (i !== 0) regExpStr += "|";
                regExpStr += "(?:" + this.escapeRegExp(this.emoticonCodes[i]) + ")";
            }
            regExpStr += ")(.*)$";
            arguments.callee.regExp = new RegExp(regExpStr, "gm");
        }
        return text.replace(arguments.callee.regExp, this.replaceEmoticonsCallback);
    },
    replaceEmoticonsCallback: function(str, p1, p2, p3) {
        if (!arguments.callee.regExp) {
            arguments.callee.regExp = new RegExp('(="[^"]*$)|(&[^;]*$)', "");
        }
        if (p1.match(arguments.callee.regExp)) {
            return str;
        }
        if (p2) {
            var index = ajaxChat.arraySearch(p2, ajaxChat.emoticonCodes);
            return ajaxChat.replaceEmoticons(p1) + '<img src="' + ajaxChat.dirs["emoticons"] + ajaxChat.emoticonFiles[index] + '" alt="' + p2 + '" />' + ajaxChat.replaceEmoticons(p3);
        }
        return str;
    },
    getActiveStyle: function() {
        var cookie = this.readCookie(this.sessionKeyPrefix + "style");
        return cookie ? cookie : this.getPreferredStyleSheet();
    },
    initStyle: function() {
        this.styleInitiated = true;
        this.setActiveStyleSheet(this.getActiveStyle());
    },
    persistStyle: function() {
        if (this.styleInitiated) {
            this.createCookie(this.sessionKeyPrefix + "style", this.getActiveStyleSheet(), this.cookieExpiration);
        }
    },
    setSelectedStyle: function() {
        if (this.dom["styleSelection"]) {
            var style = this.getActiveStyle();
            var styleOptions = this.dom["styleSelection"].getElementsByTagName("option");
            for (var i = 0; i < styleOptions.length; i++) {
                if (styleOptions[i].value == style) {
                    styleOptions[i].selected = true;
                    break;
                }
            }
        }
    },
    getSelectedStyle: function() {
        var styleOptions = this.dom["styleSelection"].getElementsByTagName("option");
        if (this.dom["styleSelection"].selectedIndex === -1) {
            return styleOptions[0].value;
        } else {
            return styleOptions[this.dom["styleSelection"].selectedIndex].value;
        }
    },
    setActiveStyleSheet: function(title) {
        var i, a, titleFound = false;
        for (i = 0; a = document.getElementsByTagName("link")[i]; i++) {
            if (a.getAttribute("rel").indexOf("style") !== -1 && a.getAttribute("title")) {
                a.disabled = true;
                if (a.getAttribute("title") === title) {
                    a.disabled = false;
                    titleFound = true;
                }
            }
        }
        if (!titleFound && title !== null) {
            this.setActiveStyleSheet(this.getPreferredStyleSheet());
        }
    },
    getActiveStyleSheet: function() {
        var i, a;
        for (i = 0; a = document.getElementsByTagName("link")[i]; i++) {
            if (a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title") && !a.disabled) {
                return a.getAttribute("title");
            }
        }
        return null;
    },
    getPreferredStyleSheet: function() {
        var i, a;
        for (i = 0; a = document.getElementsByTagName("link")[i]; i++) {
            if (a.getAttribute("rel").indexOf("style") !== -1 && a.getAttribute("rel").indexOf("alt") === -1 && a.getAttribute("title")) {
                return a.getAttribute("title");
            }
        }
        return null;
    },
    switchLanguage: function(langCode) {
        window.location.search = "?lang=" + langCode;
    },
    createCookie: function(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + days * 24 * 60 * 60 * 1e3);
            expires = "; expires=" + date.toGMTString();
        }
        var path = "; path=" + this.cookiePath;
        var domain = this.cookieDomain ? "; domain=" + this.cookieDomain : "";
        var secure = this.cookieSecure ? "; secure" : "";
        document.cookie = name + "=" + encodeURIComponent(value) + expires + path + domain + secure;
    },
    readCookie: function(name) {
        if (!document.cookie) return null;
        var nameEQ = name + "=";
        var ca = document.cookie.split(";");
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === " ") {
                c = c.substring(1, c.length);
            }
            if (c.indexOf(nameEQ) === 0) {
                return decodeURIComponent(c.substring(nameEQ.length, c.length));
            }
        }
        return null;
    },
    isCookieEnabled: function() {
        this.createCookie(this.sessionKeyPrefix + "cookie_test", true, 1);
        var cookie = this.readCookie(this.sessionKeyPrefix + "cookie_test");
        if (cookie) {
            this.createCookie(this.sessionKeyPrefix + "cookie_test", true, -1);
            return true;
        }
        return false;
    },
    finalize: function() {
        if (typeof this.finalizeFunction === "function") {
            this.finalizeFunction();
        }
        if (this.socket) {
            try {
                this.socket.close();
                this.socket = null;
            } catch (e) {
                this.debugMessage("finalize::closeSocket", e);
            }
        }
        this.persistSettings();
        this.persistStyle();
        this.customFinalize();
    },
    initializeCustomFlashInterface: function() {},
    handleCustomInfoMessage: function(infoType, infoData) {},
    customInitialize: function() {},
    customFinalize: function() {},
    getCustomUserMenuItems: function(encodedUserName, userID) {
        return "";
    },
    parseCustomInputMessage: function(text) {
        return text;
    },
    parseCustomInputCommand: function(text, textParts) {
        return text;
    },
    replaceCustomText: function(text) {
        userClass = this.getRoleClass(this.userRole);
        text = text.replace("bitches", "friends");
        text = text.replace("[username]", "[" + userClass + "]" + this.userName + "[/" + userClass + "]");
        return text;
    },
    replaceCustomCommands: function(text, textParts) {
        return text;
    },
    replaceCustomBBCode: function(tag, attribute, content) {
        return '<span class="' + tag + '">' + this.replaceBBCode(content) + "</span>";
    },
    customOnNewMessage: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
        return true;
    },
    customSoundOnNewMessage: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
        return true;
    },
    debugMessage: function(msg, e) {
        msg = "Ajax chat: " + msg + " exception: ";
        if (this.debug) {
            console.log(msg, e);
            if (this.debug === 2) {
                alert(msg + e);
            }
        }
    }
};

ajaxChat.logsMonitorMode = null;

ajaxChat.logsLastID = null;

ajaxChat.logsCommand = null;

ajaxChat.startChatUpdate = function() {
    var infos = "userID,userName,userRole";
    if (this.socketServerEnabled) {
        infos += ",socketRegistrationID";
    }
    this.updateChat("&getInfos=" + this.encodeText(infos));
};

ajaxChat.updateChat = function(paramString) {
    if (paramString || this.logsMonitorMode || !this.logsLastID || this.lastID != this.logsLastID) {
        this.logsLastID = this.lastID;
        var requestUrl = this.ajaxURL + "&lastID=" + this.lastID;
        if (paramString) {
            requestUrl += paramString;
        }
        requestUrl += "&" + this.getLogsCommand();
        this.makeRequest(requestUrl, "GET", null);
    } else {
        this.logsLastID = null;
    }
};

ajaxChat.sendMessage = function() {
    this.getLogs();
};

ajaxChat.getLogs = function() {
    clearTimeout(this.timer);
    this.clearChatList();
    this.lastID = 0;
    this.logsCommand = null;
    this.makeRequest(this.ajaxURL, "POST", this.getLogsCommand());
};

ajaxChat.getLogsCommand = function() {
    if (!this.logsCommand) {
        if (!this.dom["inputField"].value && parseInt(this.dom["yearSelection"].value) <= 0 && parseInt(this.dom["hourSelection"].value) <= 0) {
            this.logsMonitorMode = true;
        } else {
            this.logsMonitorMode = false;
        }
        this.logsCommand = "command=getLogs" + "&channelID=" + this.dom["channelSelection"].value + "&year=" + this.dom["yearSelection"].value + "&month=" + this.dom["monthSelection"].value + "&day=" + this.dom["daySelection"].value + "&hour=" + this.dom["hourSelection"].value + "&search=" + this.encodeText(this.dom["inputField"].value);
    }
    return this.logsCommand;
};

ajaxChat.onNewMessage = function(dateObject, userID, userName, userRoleClass, messageID, messageText, channelID, ip) {
    if (messageText.indexOf("/delete") == 0) {
        return false;
    }
    if (this.logsMonitorMode) {
        this.blinkOnNewMessage(dateObject, userID, userName, userRoleClass, messageID, messageText, channelID, ip);
        this.playSoundOnNewMessage(dateObject, userID, userName, userRoleClass, messageID, messageText, channelID, ip);
    }
    return true;
};

ajaxChat.logout = function() {
    clearTimeout(this.timer);
};

ajaxChat.switchLanguage = function(langCode) {
    window.location.search = "?view=logs&lang=" + langCode;
};

ajaxChat.setChatUpdateTimer = function() {
    clearTimeout(this.timer);
    var timeout;
    if (this.socketIsConnected && this.logsLastID && this.lastID == this.logsLastID) {
        timeout = this.socketTimerRate;
    } else {
        timeout = this.timerRate;
        if (this.socketServerEnabled && !this.socketReconnectTimer) {
            this.socketReconnectTimer = setTimeout("ajaxChat.socketConnect();", 6e4);
        }
    }
    this.timer = setTimeout("ajaxChat.updateChat(null);", timeout);
};

ajaxChat.socketUpdate = function(data) {
    if (this.logsMonitorMode) {
        var xmlDoc = this.loadXML(data);
        if (xmlDoc) {
            var selectedChannelID = parseInt(this.dom["channelSelection"].value);
            var channelID = parseInt(xmlDoc.firstChild.getAttribute("channelID"));
            if (selectedChannelID == -3 || channelID == selectedChannelID || selectedChannelID == -2 && channelID >= this.privateMessageDiff || selectedChannelID == -1 && channelID >= this.privateChannelDiff && channelID < this.privateMessageDiff) {
                this.handleChatMessages(xmlDoc.getElementsByTagName("message"));
            }
        }
    }
};

var ajaxChatLang = {
    login: "%s logs into the Chat.",
    logout: "%s logs out of the Chat.",
    logoutTimeout: "%s has been logged out (Timeout).",
    logoutIP: "%s has been logged out (Invalid IP address).",
    logoutKicked: "%s has been logged out (Kicked).",
    channelEnter: "%s enters the channel.",
    channelLeave: "%s leaves the channel.",
    privmsg: "(whispers)",
    privmsgto: "(whispers to %s)",
    invite: "%s invites you to join %s.",
    inviteto: "Your invitation to %s to join channel %s has been sent.",
    uninvite: "%s uninvites you from channel %s.",
    uninviteto: "Your uninvitation to %s for channel %s has been sent.",
    queryOpen: "Private channel opened to %s.",
    queryClose: "Private channel to %s closed.",
    ignoreAdded: "Added %s to the ignore list.",
    ignoreRemoved: "Removed %s from the ignore list.",
    ignoreList: "Ignored Users:",
    ignoreListEmpty: "No ignored Users listed.",
    who: "Online Users:",
    whoChannel: "Online Users in channel %s:",
    whoEmpty: "No online users in the given channel.",
    list: "Available channels:",
    bans: "Banned Users:",
    bansEmpty: "No banned Users listed.",
    unban: "Ban of user %s revoked.",
    whois: "User %s - IP address:",
    whereis: "User %s is in channel %s.",
    roll: "%s rolls %s and gets %s.",
    nick: "%s is now known as %s.",
    toggleUserMenu: "Toggle user menu for %s",
    userMenuLogout: "Logout",
    userMenuWho: "List online users",
    userMenuList: "List available channels",
    userMenuAction: "Describe action",
    userMenuRoll: "Roll dice",
    userMenuNick: "Change username",
    userMenuEnterPrivateRoom: "Enter private room",
    userMenuSendPrivateMessage: "Send private message",
    userMenuDescribe: "Send private action",
    userMenuOpenPrivateChannel: "Open private channel",
    userMenuClosePrivateChannel: "Close private channel",
    userMenuInvite: "Invite",
    userMenuUninvite: "Uninvite",
    userMenuIgnore: "Ignore/Accept",
    userMenuIgnoreList: "List ignored users",
    userMenuWhereis: "Display channel",
    userMenuKick: "Kick/Ban",
    userMenuBans: "List banned users",
    userMenuWhois: "Display IP",
    unbanUser: "Revoke ban of user %s",
    joinChannel: "Join channel %s",
    cite: "%s said:",
    urlDialog: "Please enter the address (URL) of the webpage:",
    deleteMessage: "Delete this chat message",
    deleteMessageConfirm: "Really delete the selected chat message?",
    errorCookiesRequired: "Cookies are required for this chat.",
    errorUserNameNotFound: "Error: User %s not found.",
    errorMissingText: "Error: Missing message text.",
    errorMissingUserName: "Error: Missing username.",
    errorInvalidUserName: "Error: Invalid username.",
    errorUserNameInUse: "Error: Username already in use.",
    errorMissingChannelName: "Error: Missing channel name.",
    errorInvalidChannelName: "Error: Invalid channel name: %s",
    errorPrivateMessageNotAllowed: "Error: Private messages are not allowed.",
    errorInviteNotAllowed: "Error: You are not allowed to invite someone to this channel.",
    errorUninviteNotAllowed: "Error: You are not allowed to uninvite someone from this channel.",
    errorNoOpenQuery: "Error: No private channel open.",
    errorKickNotAllowed: "Error: You are not allowed to kick %s.",
    errorCommandNotAllowed: "Error: Command not allowed: %s",
    errorUnknownCommand: "Error: Unknown command: %s",
    errorMaxMessageRate: "Error: You exceeded the maximum number of messages per minute.",
    errorConnectionTimeout: "Error: Connection timeout. Please try again.",
    errorConnectionStatus: "Error: Connection status: %s",
    errorSoundIO: "Error: Failed to load sound file (Flash IO Error).",
    errorSocketIO: "Error: Connection to socket server failed (Flash IO Error).",
    errorSocketSecurity: "Error: Connection to socket server failed (Flash Security Error).",
    errorDOMSyntax: "Error: Invalid DOM Syntax (DOM ID: %s)."
};

var UC_USER = 0;

var UC_POWER_USER = 1;

var UC_VIP = 2;

var UC_UPLOADER = 3;

var UC_MODERATOR = 4;

var UC_STAFF = 4;

var UC_ADMINISTRATOR = 5;

var UC_SYSOP = 6;

var ajaxChatConfig = {
    loginChannelID: null,
    loginChannelName: null,
    timerRate: 2e3,
    ajaxURL: "./ajaxchat.php?ajax=true",
    baseURL: "./chat/",
    regExpMediaUrl: "^((http)|(https)):\\/\\/",
    startChatOnLoad: true,
    domIDs: {
        chatList: "chatList",
        onlineList: "onlineList",
        inputField: "inputField",
        messageLengthCounter: "messageLengthCounter",
        channelSelection: "channelSelection",
        styleSelection: "styleSelection",
        emoticonsContainer: "emoticonsContainer",
        colorCodesContainer: "colorCodesContainer",
        flashInterfaceContainer: "flashInterfaceContainer",
        statusIcon: "statusIconContainer"
    },
    settings: {
        postDirection: false,
        bbCode: true,
        bbCodeImages: true,
        bbCodeColors: true,
        hyperLinks: true,
        lineBreaks: true,
        emoticons: true,
        autoFocus: false,
        autoScroll: true,
        maxMessages: 0,
        wordWrap: true,
        maxWordLength: 32,
        dateFormat: "[%H:%i:%s]",
        dateFormatTooltip: "%l, %F %d, %Y",
        persistFontColor: false,
        fontColor: null,
        audio: true,
        audioBackend: -1,
        audioVolume: .3,
        soundReceive: "sound_1",
        soundSend: "sound_2",
        soundEnter: "sound_3",
        soundLeave: "sound_4",
        soundChatBot: "sound_5",
        soundError: "sound_6",
        soundPrivate: "sound_7",
        blink: true,
        blinkInterval: 500,
        blinkIntervalNumber: 10
    },
    nonPersistentSettings: [],
    bbCodeTags: [ "b", "i", "u", "quote", "code", "color", "url", "img", "chatbot", "user", "power_user", "vip", "uploader", "moderator", "administrator", "sysop" ],
    colorCodes: [ "gray", "silver", "white", "yellow", "orange", "red", "fuchsia", "purple", "navy", "blue", "aqua", "teal", "green", "lime", "olive", "maroon", "black" ],
    emoticonCodes: [ ":)", ":smile:", ":-D", ":lol:", ":w00t:", ":-P", ":blum:", ";-)", ":devil:", ":yawn:", ":-/", ":o)", ":innocent:", ":whistle:", ":unsure:", ":blush:", ":hmm:", ":hmmm:", ":huh:", ":look:", ":rolleyes:", ":kiss:", ":blink:", ":baby:", ":hi2:", ":pmsl:", ":-)", ":smile:", ":-D", ":lol:", ":w00t:", ":-P", ";-)", ":-|", ":-/", ":-(", ":'-(", ":weep:", ":-O", ":o)", "8-)", "|-)", ":innocent:", ":whistle:", ":closedeyes:", ":cool:", ":fun:", ":unsure:", ":thumbsup:", ":thumbsdown:", ":blush:", ":yes:", ":no:", ":love:", ":?:", ":!:", ":idea:", ":arrow:", ":hmm:", ":hmmm:", ":huh:", ":geek:", ":look:", ":rolleyes:", ":kiss:", ":shifty:", ":blink:", ":smartass:", ":sick:", ":crazy:", ":wacko:", ":alien:", ":wizard:", ":wave:", ":wavecry:", ":baby:", ":angry:", ":ras:", ":sly:", ":devil:", ":evil:", ":evilmad:", ":sneaky:", ":axe:", ":slap:", ":wall:", ":rant:", ":jump:", ":yucky:", ":nugget:", ":smart:", ":shutup:", ":shutup2:", ":crockett:", ":zorro:", ":snap:", ":beer:", ":beer2:", ":drunk:", ":strongbench:", ":weakbench:", ":dumbells:", ":music:", ":stupid:", ":dots:", ":offtopic:", ":spam:", ":oops:", ":lttd:", ":please:", ":sorry:", ":hi:", ":yay:", ":cake:", ":hbd:", ":band:", ":punk:", ":rofl:", ":bounce:", ":mbounce:", ":gathering:", ":hang:", ":chop:", ":horny:", ":rip:", ":whip:", ":judge:", ":chair:", ":tease:", ":box:", ":boxing:", ":guns:", ":shoot:", ":shoot2:", ":flowers:", ":wub:", ":lovers:", ":kissing:", ":kissing2:", ":console:", ":group:", ":hump:", ":hooray:", ":happy2:", ":clap:", ":clap2:", ":weirdo:", ":yawn:", ":bow:", ":dawgie:", ":cylon:", ":book:", ":fish:", ":mama:", ":pepsi:", ":medieval:", ":rambo:", ":ninja:", ":hannibal:", ":party:", ":snorkle:", ":evo:", ":king:", ":chef:", ":mario:", ":pope:", ":fez:", ":cap:", ":cowboy:", ":pirate:", ":pirate2:", ":rock:", ":cigar:", ":icecream:", ":oldtimer:", ":trampoline:", ":banana:", ":smurf:", ":yikes:", ":osama:", ":saddam:", ":santa:", ":indian:", ":pimp:", ":nuke:", ":jacko:", ":ike:", ":greedy:", ":super:", ":wolverine:", ":spidey:", ":spider:", ":bandana:", ":construction:", ":sheep:", ":police:", ":detective:", ":bike:", ":fishing:", ":clover:", ":horse:", ":shit:", ":soldiers:", ":)", ":wink:", ":D", ":P", ":(", ":'(", ":|", ":Boozer:", ":deadhorse:", ":spank:", ":yoji:", ":locked:", ":grrr:", "O:-", ":sleeping:", ":clown:", ":mml:", ":rtf:", ":dancer:", ":morepics:", ":rb:", ":rblocked:", ":maxlocked:", ":hslocked:", ":thankyou:", ":congrat:", ":thedevil:", ":drinks2:", ":rose:", ":good:", ":hi2:", ":pardon:", ":rofl2:", ":spite:", ":unknw:", ":cuppa:", ":smoken:", ":slick:", ":sun:", ":fart2:", ":lurker:", ":jawdrop:", ":sob:", ":whip2:", ":geek2:", ":madgrin:", ":byebye:", ":img:", ":alcohol:", ":pmsl:", ":bombie:", ":whoops:", ":banned:", ":faq:", ":iluvff:", ":starwars:", ":mage:", ":respect:", ":utorrent:", ":spliffy:", ":bear:", ":bandit:", ":congrats:", ":smokin:", ":canabis:", ":2gun:", ":bigun:", ":chainsaw:", ":drinks:", ":fight1:", ":fight2:", ":fight3:", ":fight4:", ":first:", ":Gotcha:", ":jumping:", ":yoda:", ":wink1:", ":upyours:", ":taz:", ":spew2:", ":spew:", ":sniper1:", ":smokie2:", ":sick2:", ":scream:", ":rasp2:", ":rasp:", ":party8:", ":party7:", ":party6:", ":party5:", ":party4:", ":party3:", ":party2:", ":party1:", ":oldman:", ":ninja2:", ":madaRse:", ":line:", ":last:", ":kenny:", ":jumping3:", ":jumping2:", ":jumping1:", ":pish:", ":grim:", ":taz2:", ":spiderman:", ":bong:", ":bat:", ":shotgun:", ":eye:", ":tumble:", ":welcome:", ":fart3:", ":caveman:", ":explode:", ":finger:", ":bhong:", ":bye:", ":slip:", ":jerry:", ":schair:", ":raver:", ":ras2:", ":moonie:", ":hides:", ":apache:", ":doobie:", ":acid:", ":angeldevil:", ":madraver:", ":clapper1:", ":high5:", ":shoutkiller:", ":bhong3:", ":bomb:", ":grey:", ":fart:", ":trumpet:", ":lmfao:", ":flmao:", ":googleit:", ":wow:", ":karma:", ":king2:", ":king3:", ":astronomer:", ":bluesbro:", ":Bunny:", ":cookies:", ":wired:", ":elektrik:", ":boom:", ":firebug:", ":fishy:", ":fishy2:", ":graffiti:", ":glue:", ":goodjob:", ":googleb:", ":grasshopper:", ":hint:", ":magnify:", ":mini2:", ":mini3:", ":mini4:", ":moo:", ":muhaha:", ":fubar:", ":nhlfan:", ":oldman2:", ":omg:", ":peacock:", ":pottymouth:", ":salute:", ":scuba2:", ":scythe:", ":shadowpet:", ":sharky:", ":sheesh:", ":smmdi:", ":boader:", ":soapbox1:", ":shappens:", ":swinger:", ":talk2:", ":usd:", ":wanted:", ":clowny:", ":angry_skull:", ":cheesy_skull:", ":cool_skull:", ":cry_skull:", ":embarassed_skull:", ":grin_skull:", ":huh_skull:", ":kiss_skull:", ":laugh_skull:", ":lipsrsealed_skull:", ":rolleyes_skull:", ":sad_skull:", ":shocked_skull:", ":smiley_skull:", ":tongue_skull:", ":undecided_skull:", ":wink_skull:", ":fart4:", ":Boozer:", ":deadhorse:", ":headbang:", ":bump:", ":spank:", ":yoji:", ":grrr:", ":mml:", ":rtf:", ":morepics:", ":rb:", ":rblocked:", ":maxlocked:", ":hslocked:", ":locked:", ":censoredpic:", ":dabunnies:" ],
    emoticonFiles: [ "smile1.gif", "smile2.gif", "grin.gif", "laugh.gif", "w00t.gif", "tongue.gif", "blum.gif", "wink.gif", "devil.gif", "yawn.gif", "confused.gif", "clown.gif", "innocent.gif", "whistle.gif", "unsure.gif", "blush.gif", "hmm.gif", "hmmm.gif", "huh.gif", "look.gif", "rolleyes.gif", "kiss.gif", "blink.gif", "baby.gif", "hi2.gif", "hysterical.gif", "smile1.gif", "smile2.gif", "grin.gif", "laugh.gif", "w00t.gif", "tongue.gif", "wink.gif", "noexpression.gif", "confused.gif", "sad.gif", "cry.gif", "weep.gif", "ohmy.gif", "clown.gif", "cool1.gif", "sleeping.gif", "innocent.gif", "whistle.gif", "closedeyes.gif", "cool2.gif", "fun.gif", "unsure.gif", "thumbsup.gif", "thumbsdown.gif", "blush.gif", "yes.gif", "no.gif", "love.gif", "question.gif", "excl.gif", "idea.gif", "arrow.gif", "hmm.gif", "hmmm.gif", "huh.gif", "geek.gif", "look.gif", "rolleyes.gif", "kiss.gif", "shifty.gif", "blink.gif", "smartass.gif", "sick.gif", "crazy.gif", "wacko.gif", "alien.gif", "wizard.gif", "wave.gif", "wavecry.gif", "baby.gif", "angry.gif", "ras.gif", "sly.gif", "devil.gif", "evil.gif", "evilmad.gif", "sneaky.gif", "axe.gif", "slap.gif", "wall.gif", "rant.gif", "jump.gif", "yucky.gif", "nugget.gif", "smart.gif", "shutup.gif", "shutup2.gif", "crockett.gif", "zorro.gif", "snap.gif", "beer.gif", "beer2.gif", "drunk.gif", "strongbench.gif", "weakbench.gif", "dumbells.gif", "music.gif", "stupid.gif", "dots.gif", "offtopic.gif", "spam.gif", "oops.gif", "lttd.gif", "please.gif", "sorry.gif", "hi.gif", "yay.gif", "cake.gif", "hbd.gif", "band.gif", "punk.gif", "rofl.gif", "bounce.gif", "mbounce.gif", "gathering.gif", "hang.gif", "chop.gif", "horny.gif", "rip.gif", "whip.gif", "judge.gif", "chair.gif", "tease.gif", "box.gif", "boxing.gif", "guns.gif", "shoot.gif", "shoot2.gif", "flowers.gif", "wub.gif", "lovers.gif", "kissing.gif", "kissing2.gif", "console.gif", "group.gif", "hump.gif", "hooray.gif", "happy2.gif", "clap.gif", "clap2.gif", "weirdo.gif", "yawn.gif", "bow.gif", "dawgie.gif", "cylon.gif", "book.gif", "fish.gif", "mama.gif", "pepsi.gif", "medieval.gif", "rambo.gif", "ninja.gif", "hannibal.gif", "party.gif", "snorkle.gif", "evo.gif", "king.gif", "chef.gif", "mario.gif", "pope.gif", "fez.gif", "cap.gif", "cowboy.gif", "pirate.gif", "pirate2.gif", "rock.gif", "cigar.gif", "icecream.gif", "oldtimer.gif", "trampoline.gif", "bananadance.gif", "smurf.gif", "yikes.gif", "osama.gif", "saddam.gif", "santa.gif", "indian.gif", "pimp.gif", "nuke.gif", "jacko.gif", "ike.gif", "greedy.gif", "super.gif", "wolverine.gif", "spidey.gif", "spider.gif", "bandana.gif", "construction.gif", "sheep.gif", "police.gif", "detective.gif", "bike.gif", "fishing.gif", "clover.gif", "horse.gif", "shit.gif", "soldiers.gif", "smile1.gif", "wink.gif", "grin.gif", "tongue.gif", "sad.gif", "cry.gif", "noexpression.gif", "alcoholic.gif", "deadhorse.gif", "spank.gif", "yoji.gif", "locked.gif", "angry.gif", "innocent.gif", "sleeping.gif", "clown.gif", "mml.gif", "rtf.gif", "dancer.gif", "morepics.gif", "rb.gif", "rblocked.gif", "maxlocked.gif", "hslocked.gif", "thankyou.gif", "clapping2.gif", "diablo.gif", "drinks2.gif", "give_rose.gif", "good.gif", "hi2.gif", "pardon.gif", "rofl2.gif", "spiteful.gif", "unknw.gif", "cuppa.gif", "smoke2.gif", "uber.gif", "read.gif", "fart2.gif", "lurker.gif", "jawdrop.gif", "sob.gif", "whip2.gif", "geek2.gif", "mad-grin.gif", "connie_mini_byebye.gif", "img.gif", "alcohol.gif", "hysterical.gif", "bomb_ie.gif", "whoops.gif", "banned.gif", "faq.gif", "iluvff.gif", "starwars.gif", "mage.gif", "respect.gif", "utorrent.gif", "spliffy.gif", "bear.gif", "bandit.gif", "congrats.gif", "smokin.gif", "canabis.gif", "2gun.gif", "biggun.gif", "chainsaw2.gif", "drinks.gif", "fight1.gif", "fight2.gif", "fight3.gif", "fight4.gif", "first.gif", "Gotcha.gif", "jumping.gif", "yoda.gif", "wink1.gif", "upyours.gif", "taz.gif", "spew2.gif", "spew.gif", "sniper1.gif", "smokie2.gif", "sick2.gif", "scream.gif", "rasp2.gif", "rasp.gif", "party8.gif", "party7.gif", "party6.gif", "party5.gif", "party4.gif", "party3.gif", "party2.gif", "party1.gif", "oldman.gif", "ninja2.gif", "madarse.gif", "Line.gif", "last.gif", "kenny.gif", "jumping3.gif", "jumping2.gif", "jumping1.gif", "pish.gif", "grim.gif", "taz2.gif", "spidey.gif", "bong.gif", "bat.gif", "shotgun.gif", "eye.gif", "tumbleweed.gif", "welcome.gif", "fart3.gif", "caveman.gif", "explode.gif", "finger.gif", "bhong.gif", "bye.gif", "slip.gif", "jerry.gif", "schair.gif", "raver.gif", "ras2.gif", "moonie.gif", "hides.gif", "apache.gif", "doobie.gif", "acid.gif", "angeldevil.gif", "madraver.gif", "clapper1.gif", "high5.gif", "shoutkiller.gif", "bhong3.gif", "bomb.gif", "grey.gif", "fart.gif", "trumpet.gif", "lmfao.gif", "lmao2.gif", "googleit.gif", "wow.gif", "karma.gif", "2nd.gif", "3rd.gif", "astronomer1.gif", "bluesbro.gif", "bunnywalk.gif", "cookies.gif", "electrician.gif", "elektrik.gif", "explosion.gif", "firebug.gif", "fishtale.gif", "fishy.gif", "graffiti.gif", "glueb.gif", "goodjob.gif", "googleb.gif", "grasshopper.gif", "hint.gif", "magglass.gif", "mini2.gif", "mini3.gif", "mini4.gif", "moo.gif", "muah.gif", "likefoobar.gif", "nhlfan.gif", "old.gif", "OMG_sign.gif", "peacock.gif", "pottymouth.gif", "salute3.gif", "scuba2.gif", "scythe.gif", "shadowpets.gif", "sharkycircle.gif", "sheesh.gif", "shemademedoit.gif", "skateboardb.gif", "soapbox1.gif", "stuffhappens.gif", "swinger1.gif", "talk2.gif", "usd.gif", "wanted.gif", "mindless.gif", "angry_skull.gif", "cheesy_skull.gif", "cool_skull.gif", "cry_skull.gif", "embarassed_skull.gif", "grin_skull.gif", "huh_skull.gif", "kiss_skull.gif", "laugh_skull.gif", "lipsrsealed_skull.gif", "rolleyes_skull.gif", "sad_skull.gif", "shocked_skull.gif", "smiley_skull.gif", "tongue_skull.gif", "undecided_skull.gif", "wink_skull.gif", "fart4.gif", "alcoholic.gif", "deadhorse.gif", "headbang.gif", "halo.gif", "spank.gif", "yoji.gif", "angry.gif", "mml.gif", "rtf.gif", "morepics.gif", "rb.gif", "rblocked.gif", "maxlocked.gif", "hslocked.gif", "locked.gif", "boucher-censored.jpg", "bunnies3.gif" ],
    emoticonDisplay: [ 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 0 ],
    soundFiles: {
        sound_1: "sound_1.mp3",
        sound_2: "sound_2.mp3",
        sound_3: "sound_3.mp3",
        sound_4: "sound_4.mp3",
        sound_5: "sound_5.mp3",
        sound_6: "sound_6.mp3",
        sound_7: "sound_7.mp3"
    },
    debug: false
};

function FABridge(target, bridgeName) {
    this.target = target;
    this.remoteTypeCache = {};
    this.remoteInstanceCache = {};
    this.remoteFunctionCache = {};
    this.localFunctionCache = {};
    this.bridgeID = FABridge.nextBridgeID++;
    this.name = bridgeName;
    this.nextLocalFuncID = 0;
    FABridge.instances[this.name] = this;
    FABridge.idMap[this.bridgeID] = this;
    return this;
}

FABridge.TYPE_ASINSTANCE = 1;

FABridge.TYPE_ASFUNCTION = 2;

FABridge.TYPE_JSFUNCTION = 3;

FABridge.TYPE_ANONYMOUS = 4;

FABridge.initCallbacks = {};

FABridge.argsToArray = function(args) {
    var result = [];
    for (var i = 0; i < args.length; i++) {
        result[i] = args[i];
    }
    return result;
};

function instanceFactory(objID) {
    this.fb_instance_id = objID;
    return this;
}

function FABridge__invokeJSFunction(args) {
    var funcID = args[0];
    var throughArgs = args.concat();
    throughArgs.shift();
    var bridge = FABridge.extractBridgeFromID(funcID);
    return bridge.invokeLocalFunction(funcID, throughArgs);
}

FABridge.addInitializationCallback = function(bridgeName, callback) {
    var inst = FABridge.instances[bridgeName];
    if (inst != undefined) {
        callback.call(inst);
        return;
    }
    var callbackList = FABridge.initCallbacks[bridgeName];
    if (callbackList == null) {
        FABridge.initCallbacks[bridgeName] = callbackList = [];
    }
    callbackList.push(callback);
};

function FABridge__bridgeInitialized(bridgeName) {
    var objects = document.getElementsByTagName("object");
    var ol = objects.length;
    var activeObjects = [];
    if (ol > 0) {
        for (var i = 0; i < ol; i++) {
            if (typeof objects[i].SetVariable != "undefined") {
                activeObjects[activeObjects.length] = objects[i];
            }
        }
    }
    var embeds = document.getElementsByTagName("embed");
    var el = embeds.length;
    var activeEmbeds = [];
    if (el > 0) {
        for (var j = 0; j < el; j++) {
            if (typeof embeds[j].SetVariable != "undefined") {
                activeEmbeds[activeEmbeds.length] = embeds[j];
            }
        }
    }
    var aol = activeObjects.length;
    var ael = activeEmbeds.length;
    var searchStr = "bridgeName=" + bridgeName;
    if (aol == 1 && !ael || aol == 1 && ael == 1) {
        FABridge.attachBridge(activeObjects[0], bridgeName);
    } else if (ael == 1 && !aol) {
        FABridge.attachBridge(activeEmbeds[0], bridgeName);
    } else {
        var flash_found = false;
        if (aol > 1) {
            for (var k = 0; k < aol; k++) {
                var params = activeObjects[k].childNodes;
                for (var l = 0; l < params.length; l++) {
                    var param = params[l];
                    if (param.nodeType == 1 && param.tagName.toLowerCase() == "param" && param["name"].toLowerCase() == "flashvars" && param["value"].indexOf(searchStr) >= 0) {
                        FABridge.attachBridge(activeObjects[k], bridgeName);
                        flash_found = true;
                        break;
                    }
                }
                if (flash_found) {
                    break;
                }
            }
        }
        if (!flash_found && ael > 1) {
            for (var m = 0; m < ael; m++) {
                var flashVars = activeEmbeds[m].attributes.getNamedItem("flashVars").nodeValue;
                if (flashVars.indexOf(searchStr) >= 0) {
                    FABridge.attachBridge(activeEmbeds[m], bridgeName);
                    break;
                }
            }
        }
    }
    return true;
}

FABridge.nextBridgeID = 0;

FABridge.instances = {};

FABridge.idMap = {};

FABridge.refCount = 0;

FABridge.extractBridgeFromID = function(id) {
    var bridgeID = id >> 16;
    return FABridge.idMap[bridgeID];
};

FABridge.attachBridge = function(instance, bridgeName) {
    var newBridgeInstance = new FABridge(instance, bridgeName);
    FABridge[bridgeName] = newBridgeInstance;
    var callbacks = FABridge.initCallbacks[bridgeName];
    if (callbacks == null) {
        return;
    }
    for (var i = 0; i < callbacks.length; i++) {
        callbacks[i].call(newBridgeInstance);
    }
    delete FABridge.initCallbacks[bridgeName];
};

FABridge.blockedMethods = {
    toString: true,
    get: true,
    set: true,
    call: true
};

FABridge.prototype = {
    root: function() {
        return this.deserialize(this.target.getRoot());
    },
    releaseASObjects: function() {
        return this.target.releaseASObjects();
    },
    releaseNamedASObject: function(value) {
        if (typeof value != "object") {
            return false;
        } else {
            return this.target.releaseNamedASObject(value.fb_instance_id);
        }
    },
    create: function(className) {
        return this.deserialize(this.target.create(className));
    },
    makeID: function(token) {
        return (this.bridgeID << 16) + token;
    },
    getPropertyFromAS: function(objRef, propName) {
        if (FABridge.refCount > 0) {
            throw new Error("You are trying to call recursively into the Flash Player which is not allowed. In most cases the JavaScript setTimeout function, can be used as a workaround.");
        } else {
            FABridge.refCount++;
            retVal = this.target.getPropFromAS(objRef, propName);
            retVal = this.handleError(retVal);
            FABridge.refCount--;
            return retVal;
        }
    },
    setPropertyInAS: function(objRef, propName, value) {
        if (FABridge.refCount > 0) {
            throw new Error("You are trying to call recursively into the Flash Player which is not allowed. In most cases the JavaScript setTimeout function, can be used as a workaround.");
        } else {
            FABridge.refCount++;
            retVal = this.target.setPropInAS(objRef, propName, this.serialize(value));
            retVal = this.handleError(retVal);
            FABridge.refCount--;
            return retVal;
        }
    },
    callASFunction: function(funcID, args) {
        if (FABridge.refCount > 0) {
            throw new Error("You are trying to call recursively into the Flash Player which is not allowed. In most cases the JavaScript setTimeout function, can be used as a workaround.");
        } else {
            FABridge.refCount++;
            retVal = this.target.invokeASFunction(funcID, this.serialize(args));
            retVal = this.handleError(retVal);
            FABridge.refCount--;
            return retVal;
        }
    },
    callASMethod: function(objID, funcName, args) {
        if (FABridge.refCount > 0) {
            throw new Error("You are trying to call recursively into the Flash Player which is not allowed. In most cases the JavaScript setTimeout function, can be used as a workaround.");
        } else {
            FABridge.refCount++;
            args = this.serialize(args);
            retVal = this.target.invokeASMethod(objID, funcName, args);
            retVal = this.handleError(retVal);
            FABridge.refCount--;
            return retVal;
        }
    },
    invokeLocalFunction: function(funcID, args) {
        var result;
        var func = this.localFunctionCache[funcID];
        if (func != undefined) {
            result = this.serialize(func.apply(null, this.deserialize(args)));
        }
        return result;
    },
    getTypeFromName: function(objTypeName) {
        return this.remoteTypeCache[objTypeName];
    },
    createProxy: function(objID, typeName) {
        instanceFactory.prototype = this.getTypeFromName(typeName);
        var instance = new instanceFactory(objID);
        this.remoteInstanceCache[objID] = instance;
        return instance;
    },
    getProxy: function(objID) {
        return this.remoteInstanceCache[objID];
    },
    addTypeDataToCache: function(typeData) {
        newType = new ASProxy(this, typeData.name);
        var accessors = typeData.accessors;
        for (var i = 0; i < accessors.length; i++) {
            this.addPropertyToType(newType, accessors[i]);
        }
        var methods = typeData.methods;
        for (var i = 0; i < methods.length; i++) {
            if (FABridge.blockedMethods[methods[i]] == undefined) {
                this.addMethodToType(newType, methods[i]);
            }
        }
        this.remoteTypeCache[newType.typeName] = newType;
        return newType;
    },
    addPropertyToType: function(ty, propName) {
        var c = propName.charAt(0);
        var setterName;
        var getterName;
        if (c >= "a" && c <= "z") {
            getterName = "get" + c.toUpperCase() + propName.substr(1);
            setterName = "set" + c.toUpperCase() + propName.substr(1);
        } else {
            getterName = "get" + propName;
            setterName = "set" + propName;
        }
        ty[setterName] = function(val) {
            this.bridge.setPropertyInAS(this.fb_instance_id, propName, val);
        };
        ty[getterName] = function() {
            return this.bridge.deserialize(this.bridge.getPropertyFromAS(this.fb_instance_id, propName));
        };
    },
    addMethodToType: function(ty, methodName) {
        ty[methodName] = function() {
            return this.bridge.deserialize(this.bridge.callASMethod(this.fb_instance_id, methodName, FABridge.argsToArray(arguments)));
        };
    },
    getFunctionProxy: function(funcID) {
        var bridge = this;
        if (this.remoteFunctionCache[funcID] == null) {
            this.remoteFunctionCache[funcID] = function() {
                bridge.callASFunction(funcID, FABridge.argsToArray(arguments));
            };
        }
        return this.remoteFunctionCache[funcID];
    },
    getFunctionID: function(func) {
        if (func.__bridge_id__ == undefined) {
            func.__bridge_id__ = this.makeID(this.nextLocalFuncID++);
            this.localFunctionCache[func.__bridge_id__] = func;
        }
        return func.__bridge_id__;
    },
    serialize: function(value) {
        var result = {};
        var t = typeof value;
        if (t == "number" || t == "string" || t == "boolean" || t == null || t == undefined) {
            result = value;
        } else if (value instanceof Array) {
            result = [];
            for (var i = 0; i < value.length; i++) {
                result[i] = this.serialize(value[i]);
            }
        } else if (t == "function") {
            result.type = FABridge.TYPE_JSFUNCTION;
            result.value = this.getFunctionID(value);
        } else if (value instanceof ASProxy) {
            result.type = FABridge.TYPE_ASINSTANCE;
            result.value = value.fb_instance_id;
        } else {
            result.type = FABridge.TYPE_ANONYMOUS;
            result.value = value;
        }
        return result;
    },
    deserialize: function(packedValue) {
        var result;
        var t = typeof packedValue;
        if (t == "number" || t == "string" || t == "boolean" || packedValue == null || packedValue == undefined) {
            result = this.handleError(packedValue);
        } else if (packedValue instanceof Array) {
            result = [];
            for (var i = 0; i < packedValue.length; i++) {
                result[i] = this.deserialize(packedValue[i]);
            }
        } else if (t == "object") {
            for (var i = 0; i < packedValue.newTypes.length; i++) {
                this.addTypeDataToCache(packedValue.newTypes[i]);
            }
            for (var aRefID in packedValue.newRefs) {
                this.createProxy(aRefID, packedValue.newRefs[aRefID]);
            }
            if (packedValue.type == FABridge.TYPE_PRIMITIVE) {
                result = packedValue.value;
            } else if (packedValue.type == FABridge.TYPE_ASFUNCTION) {
                result = this.getFunctionProxy(packedValue.value);
            } else if (packedValue.type == FABridge.TYPE_ASINSTANCE) {
                result = this.getProxy(packedValue.value);
            } else if (packedValue.type == FABridge.TYPE_ANONYMOUS) {
                result = packedValue.value;
            }
        }
        return result;
    },
    addRef: function(obj) {
        this.target.incRef(obj.fb_instance_id);
    },
    release: function(obj) {
        this.target.releaseRef(obj.fb_instance_id);
    },
    handleError: function(value) {
        if (typeof value == "string" && value.indexOf("__FLASHERROR") == 0) {
            var myErrorMessage = value.split("||");
            if (FABridge.refCount > 0) {
                FABridge.refCount--;
            }
            throw new Error(myErrorMessage[1]);
            return value;
        } else {
            return value;
        }
    }
};

ASProxy = function(bridge, typeName) {
    this.bridge = bridge;
    this.typeName = typeName;
    return this;
};

ASProxy.prototype = {
    get: function(propName) {
        return this.bridge.deserialize(this.bridge.getPropertyFromAS(this.fb_instance_id, propName));
    },
    set: function(propName, value) {
        this.bridge.setPropertyInAS(this.fb_instance_id, propName, value);
    },
    call: function(funcName, args) {
        this.bridge.callASMethod(this.fb_instance_id, funcName, args);
    },
    addRef: function() {
        this.bridge.addRef(this);
    },
    release: function() {
        this.bridge.release(this);
    }
};