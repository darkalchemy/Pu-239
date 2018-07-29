/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */
// Overriding client side functionality:

/*
// Example - Overriding the replaceCustomCommands method:
ajaxChat.replaceCustomCommands = function(text, textParts) {
	return text;
}
 */

ajaxChat.replaceCustomCommands = function (e, a) {
    switch (a[0]) {
        case '/announce':
            e = e.replace('/announce', ' ');
        case '/takeover':
            return e = e.replace('/takeover', ' '), e = this.replaceBBCode(e), e = this.replaceHyperLinks(e), e = this.replaceEmoticons(e), '<span class="chatBotMessage">' + e + '</span>';
        default:
            return e;
    }
};
