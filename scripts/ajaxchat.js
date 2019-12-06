ajaxChat.view = {
    debounce: false,
    mobileDetectElement: 'submitButtonContainer',
    tinyScreenDetectElement: 'bbCodeContainer',
    bindPopups: function () {
        this.bindButtonToPopup('showChannelsButton', 'logoutChannelInner');
        this.bindButtonToPopup('bbCodeColor', 'colorCodesContainer');
    },
    bindButtonToPopup: function (buttonID, popupID) {
        let buttonElement = document.getElementById(buttonID), popupElement = document.getElementById(popupID);
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
        if (!buttonElement.linkedPopupID) {
            buttonElement.linkedPopupID = popupID;
            ajaxChat.addEvent(buttonElement, 'click', this.toggleButton);
        }
    },
    toggleButton: function (e) {
        e = e || window.event;
        let target = e.target || e.srcElement;
        target.className = target.className === 'button' ? 'button off' : 'button';
        ajaxChat.showHide(target.linkedPopupID);
    },
    renderResize: function () {
        let self = this;
        self.useDebounce(function () {
            self.bindPopups();
            if (self.isMobile()) {
                ajaxChat.updateChatlistView();
            }
        });
    },
    useDebounce: function (callback) {
        let self = this;
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
            for (let i = 0; i < hideContainerIDs.length; i++) {
                ajaxChat.showHide(hideContainerIDs[i], 'none');
            }
        }
        ajaxChat.showHide(containerID);
    }
};

function initialize() {
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
    for (let i = 0; i < document.getElementById("audioBackendSetting").options.length; i++) {
        if (document.getElementById('audioBackendSetting').options[i].value == ajaxChat.getSetting('audioBackend')) {
            document.getElementById('audioBackendSetting').options[i].selected = true;
            break;
        }
    }
    for (let i = 0; i < document.getElementById("audioVolumeSetting").options.length; i++) {
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

function PopMoreSmiles() {
    PopUp('../allsmiles.php', 'More Emoticons', 600, 500, 1, 0);
}
