$.fn.TabControl = function () {
    function TabControl(list) {
        if (list.TabControlInitialized) return; else list.TabControlInitialized = true;
        this.List = list;
        this.Tab = {};
        this.Active = null;
        var T = this;
        $('li a[href^=\\#]', list).each(function () {
            var id = T.LinkId(this);
            var content = $('#' + id);
            content = content.length > 0 ? content[0] : null;
            T.Tab[id] = {
                link: this,
                content: content
            };
            if (content !== null) $(content.parentNode).addClass('active');
            $('a[href=\\#' + id + ']').click(function (e) {
                T.TabSwitch(e);
            });
            T.Activate(id, false);
            if (T.Active === null || '#' + id == location.hash) T.Active = id;
        });
        this.Activate(this.Active);
    }

    TabControl.prototype.LinkId = function (link) {
        return link.href.replace(/.*#(.+)$/, '$1');
    };
    TabControl.prototype.TabSwitch = function (e) {
        e.preventDefault();
        var id = this.LinkId(e.target);
        window.location.hash = '#' + id;
        if (id != this.Active) {
            this.Activate(this.Active, false);
            this.Active = id;
            this.Activate(this.Active);
        }
        return false;
    };
    TabControl.prototype.Activate = function (id, show) {
        if (this.Tab[id]) {
            if (show !== false) {
                $(this.Tab[id].link).addClass('active');
                if (this.Tab[id].content) this.Tab[id].content.style.display = 'block';
            } else {
                $(this.Tab[id].link).removeClass('active');
                if (this.Tab[id].content) this.Tab[id].content.style.display = 'none';
            }
        }
    };
    this.each(function () {
        new TabControl(this);
    });
    return this;
};

$(function () {
    jQuery('[class=tabs]').TabControl();
});
