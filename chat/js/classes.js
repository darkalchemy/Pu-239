var UC_USER = 0;
var UC_POWER_USER = 1;
var UC_VIP = 2;
var UC_UPLOADER = 3;
var UC_MODERATOR = 4;
var UC_STAFF = 4;
var UC_ADMINISTRATOR = 5;
var UC_SYSOP = 6;

ajaxChat.getRoleClass = function(roleID) {
    switch (parseInt(roleID)) {
        case parseInt(UC_USER):
            return 'user';
        case parseInt(UC_POWER_USER):
            return 'power_user';
        case parseInt(UC_VIP):
            return 'vip';
        case parseInt(UC_UPLOADER):
            return 'uploader';
        case parseInt(UC_MODERATOR):
            return 'moderator';
        case parseInt(UC_ADMINISTRATOR):
            return 'administrator';
        case parseInt(UC_SYSOP):
            return 'sysop';
        case parseInt(ajaxChat.chatBotRole):
            return 'chatbot';
        default:
            return 'user';
    }
};