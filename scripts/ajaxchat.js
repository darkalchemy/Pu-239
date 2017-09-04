ajaxChat.view = {
    debounce: false,

    // Use the visibility of this to guess that the user is on a mobile device
    mobileDetectElement: 'submitButtonContainer',

    // Use the visibility of this to guess that the screen is tiny and hide popups
    tinyScreenDetectElement: 'bbCodeContainer',

    bindPopups: function () {
        this.bindButtonToPopup('showChannelsButton', 'logoutChannelInner');
        this.bindButtonToPopup('bbCodeColor', 'colorCodesContainer');
    },

    bindButtonToPopup: function (buttonID, popupID) {
        var buttonElement = document.getElementById(buttonID),
            popupElement = document.getElementById(popupID);

        if (!buttonElement || !popupElement) {
            return;
        }

        if (this.isVisible(buttonElement) || this.isTinyScreen()) {
            popupElement.style.display = 'none';
            ajaxChat.addClass(popupElement, 'popup');
        } else {
            popupElement.style.display = 'block';
            ajaxChat.removeClass(popupElement, 'popup');
        }

        if (!buttonElement.linkedPopupID) { // For IE<9 we need to avoid re-attaching events.
            buttonElement.linkedPopupID = popupID;
            ajaxChat.addEvent(buttonElement, 'click', this.toggleButton);
        }
    },

    toggleButton: function (e) {
        // Workaround for IE<9.
        // If you don't care about IE8, remove the crud and use "this" to find the target.
        e = e || window.event;
        var target = e.target || e.srcElement;
        target.className = (target.className === 'button' ? 'button off' : 'button');
        ajaxChat.showHide(target.linkedPopupID);
    },

    renderResize: function () {
        var self = this;

        self.useDebounce(function () {
            // to support ie8 we need to apply a height to this container and reapply it on resize
            if (typeof isIElt8 !== 'undefined') {
                var cont = document.getElementById('mainPanelContainer');
                cont.removeAttribute("style");
                cont.style.height = cont.clientHeight;
            }

            // If panels are converted to popups at this size, turn them into popups
            self.bindPopups();

            // Fire the auto-scroll event on a window resize for mobiles (e.g. show/hide onscreen keyboard)
            if (self.isMobile()) {
                ajaxChat.updateChatlistView();
            }

            // Tiny screens have no room for options
            if (self.isTinyScreen()) {
                ajaxChat.showHide('onlineListContainer', 'none');
                ajaxChat.showHide('settingsContainer', 'none');
                ajaxChat.showHide('helpContainer', 'none');
            }
        });
    },

    useDebounce: function (callback) {
        var self = this;
        if (self.debounce === false) {
            self.debounce = true;

            setTimeout(function () {
                callback();
                self.debounce = false;
            }, 100);
        }
    },

    isVisible: function (element) {
        return element.offsetWidth > 0 || element.offsetHeight > 0;
    },

    isMobile: function () {
        return !this.isVisible(document.getElementById(this.mobileDetectElement));
    },

    isTinyScreen: function () {
        return !this.isVisible(document.getElementById(this.tinyScreenDetectElement));
    },

    toggleContainer: function (containerID, hideContainerIDs) {
        if (hideContainerIDs) {
            for (var i = 0; i < hideContainerIDs.length; i++) {
                ajaxChat.showHide(hideContainerIDs[i], 'none');
            }
        }
        ajaxChat.showHide(containerID);
    }
};

function initialize() {
    if (ajaxChat.view.isMobile()) {
        ajaxChat.setSetting('blink', false);
        ajaxChat.view.toggleContainer('onlineListContainer', ['settingsContainer', 'helpContainer']);
    }

    ajaxChat.view.bindPopups();

    ajaxChat.addEvent(window, 'resize', function () {
        ajaxChat.view.renderResize();
    });

    ajaxChat.updateButton('audio', 'audioButton');
    ajaxChat.updateButton('autoScroll', 'autoScrollButton');
    document.getElementById('postDirectionSetting').checked = ajaxChat.getSetting('postDirection');
    document.getElementById('bbCodeSetting').checked = ajaxChat.getSetting('bbCode');
    document.getElementById('bbCodeImagesSetting').checked = ajaxChat.getSetting('bbCodeImages');
    document.getElementById('bbCodeColorsSetting').checked = ajaxChat.getSetting('bbCodeColors');
    document.getElementById('hyperLinksSetting').checked = ajaxChat.getSetting('hyperLinks');
    document.getElementById('lineBreaksSetting').checked = ajaxChat.getSetting('lineBreaks');
    document.getElementById('emoticonsSetting').checked = ajaxChat.getSetting('emoticons');
    document.getElementById('autoFocusSetting').checked = ajaxChat.getSetting('autoFocus');
    document.getElementById('maxMessagesSetting').value = ajaxChat.getSetting('maxMessages');
    document.getElementById('wordWrapSetting').checked = ajaxChat.getSetting('wordWrap');
    document.getElementById('maxWordLengthSetting').value = ajaxChat.getSetting('maxWordLength');
    document.getElementById('dateFormatSetting').value = ajaxChat.getSetting('dateFormat');
    document.getElementById('persistFontColorSetting').checked = ajaxChat.getSetting('persistFontColor');
    for (var i = 0; i < document.getElementById('audioBackendSetting').options.length; i++) {
        if (document.getElementById('audioBackendSetting').options[i].value == ajaxChat.getSetting('audioBackend')) {
            document.getElementById('audioBackendSetting').options[i].selected = true;
            break;
        }
    }
    for (var i = 0; i < document.getElementById('audioVolumeSetting').options.length; i++) {
        if (document.getElementById('audioVolumeSetting').options[i].value == ajaxChat.getSetting('audioVolume')) {
            document.getElementById('audioVolumeSetting').options[i].selected = true;
            break;
        }
    }
    ajaxChat.fillSoundSelection('soundReceiveSetting', ajaxChat.getSetting('soundReceive'));
    ajaxChat.fillSoundSelection('soundSendSetting', ajaxChat.getSetting('soundSend'));
    ajaxChat.fillSoundSelection('soundEnterSetting', ajaxChat.getSetting('soundEnter'));
    ajaxChat.fillSoundSelection('soundLeaveSetting', ajaxChat.getSetting('soundLeave'));
    ajaxChat.fillSoundSelection('soundChatBotSetting', ajaxChat.getSetting('soundChatBot'));
    ajaxChat.fillSoundSelection('soundErrorSetting', ajaxChat.getSetting('soundError'));
    ajaxChat.fillSoundSelection('soundPrivateSetting', ajaxChat.getSetting('soundPrivate'));
    document.getElementById('blinkSetting').checked = ajaxChat.getSetting('blink');
    document.getElementById('blinkIntervalSetting').value = ajaxChat.getSetting('blinkInterval');
    document.getElementById('blinkIntervalNumberSetting').value = ajaxChat.getSetting('blinkIntervalNumber');
}

function PopMoreSmiles(){
    PopUp('../allsmiles.php','More Emoticons',600, 500, 1, 0);
}

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
    event.preventDefault()
}
