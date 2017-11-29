/**
 * TabControl plugin for jQuery
 * v1.0
 * Implements an tabbed control box
 *
 * By Craig Buckler, Optimalworks.net
 *
 * As featured on SitePoint.com:
 * http://www.sitepoint.com/
 *
 * Please use at your own risk.
 */

/**
 * Usage:
 *
 * From JavaScript, use:
 *     $(<node>).TabControl();
 *     where:
 *       <node> is a list of links to tabbed content
 *
 * Alternatively, in you HTML:
 *     Assign a class of 'tabs' to any <ol> or <ul> tag that contains a list of on-page links.
 */


// jQuery plugin definition
$.fn.TabControl = function () {

    // tab control object
    function TabControl(list) {

        if (list.TabControlInitialized) return;
        else list.TabControlInitialized = true;

        this.List = list;
        this.Tab = {};
        this.Active = null;
        var T = this;

        // find and initialize all tabs
        $('li a[href^=\\#]', list).each(function () {

            var id = T.LinkId(this);
            var content = $('#' + id);
            content = (content.length > 0 ? content[0] : null);

            // link/content object
            T.Tab[id] = {
                link: this,
                content: content
            };

            // set content holder class
            if (content !== null) $(content.parentNode).addClass('active');

            // event delegation
            $("a[href=\\#" + id + "]").click(function (e) {
                T.TabSwitch(e)
            });

            // deactivate tab
            T.Activate(id, false);

            // is tab active?
            if (T.Active === null || '#' + id == location.hash) T.Active = id;

        });

        // show active tab/content
        this.Activate(this.Active);

    }


    // returns linked ID
    TabControl.prototype.LinkId = function (link) {
        return link.href.replace(/.*#(.+)$/, "$1");
    };


    // tab click event handler
    TabControl.prototype.TabSwitch = function (e) {

        e.preventDefault();

        var id = this.LinkId(e.target);
        window.location.hash = '#' + id;

        if (id != this.Active) {

            // hide old tab
            this.Activate(this.Active, false);

            // switch to new tab
            this.Active = id;
            this.Activate(this.Active);

        }

//            // scroll to tab box if required
//            var html = $('html,body');
//            var lt = $(this.List).offset().top, lh = $(this.List).height();
//            var st = Math.max(html.scrollTop(), $('body').scrollTop()), wh = $(window).height();
//            if (lt < st || lt+lh > st+wh) html.animate({scrollTop: lt}, 1250);

        return false;
    };


    // activate or deactivate a tab
    TabControl.prototype.Activate = function (id, show) {

        if (this.Tab[id]) {
            if (show !== false) {
                $(this.Tab[id].link).addClass('active');
                if (this.Tab[id].content) this.Tab[id].content.style.display = 'block';
            }
            else {
                $(this.Tab[id].link).removeClass('active');
                if (this.Tab[id].content) this.Tab[id].content.style.display = 'none';
            }
        }

    };


    // initialize all tab controls
    this.each(function () {
        new TabControl(this);
    });
    return this;
};


// initialize all tab controls
$(function () {
    jQuery('[class=tabs]').TabControl();
});
