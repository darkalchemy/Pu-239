    /*
    This script is for use non comercial
     */
     
    function ISEObject()                        // Define super class
    {
    }
     
     
    ISEObject.prototype.Render = function (container, element)        // Define Method
    {
        $(container).append(element.Definition);
    }
     
     
    //proto
    function Element()                        // Define super class
    {
        this.Definition = null;
    }
     
    Element.prototype.AddChild = function (element)        // Define Method
    {
        this.Definition.append(element.Definition);
    }
     
    Element.prototype.AddAttributesWithValue = function (atr, value)        // Define Method
    {
        this.Definition.attr(atr, value);
    }
     
    Element.prototype.AddText = function (value)        // Define Method
    {
        this.Definition.text(value);
    }
     
    Element.prototype.AddHtml = function (value)        // Define Method
    {
        this.Definition.html(value);
    }
     
     
    Element.prototype.AddInlineStyle = function (name, value)        // Define Method
    {
        this.Definition.css(name, value);
    }
     
     
     
    //Define DIV Element
    Div.prototype = new Element;
    Div.prototype.constructor = Div;
    function Div(Id) {
        this.Definition = $("<div></div>");
        if (Id != 'undefined');
        this.Definition.attr("Id", Id);
    //Element.call(this); // Call super-class constructor (if desired)
    }
     
     
    //Define UL element
    Ul.prototype = new Element;
    Ul.prototype.constructor = Ul;
    function Ul(Id) {
        this.Definition = $("<ul></ul>");
        if (this.Id != 'undefined');
        this.Definition.attr("Id", this.Id);
     
    }
     
     
    Li.prototype = new Element;
    Li.prototype.constructor = Li;
    function Li(Id) {
        this.Definition = $("<li></li>");
        if (this.Id != 'undefined');
        this.Definition.attr("Id", this.Id);
    }
     
    Img.prototype = new Element;
    Img.prototype.constructor = Img;
    function Img(Id) {
        this.Definition = $("<Img></Img>");
        if (this.Id != 'undefined');
        this.Definition.attr("Id", this.Id);
    }
     
    A.prototype = new Element;
    A.prototype.constructor = A;
    function A(Id) {
        this.Definition = $("<a></a>");
        if (this.Id != 'undefined');
        this.Definition.attr("Id", this.Id);
    }
     
    (function ($) {
        $.fn.ISEAccordion = function (options) {
            var opts = $.extend({}, $.fn.ISEAccordion.defaults, options);
            var onLoadMessage = "Can not load configuration XML, please check the path.";
            var mainElem=this.selector;
            var expandSlideWidth = 0;
            var autoPlayMouseOver=false;
           
            jQuery.extend(jQuery.easing,
            {
                //###### Animation definition #####
                //d - animation time
                //t - animation time in miliseconds (czas ktory uplynol)
                //x - current time in miliseconds divided by duration
                //c - is always 1
                //b - is always 0
                easeOutBounce: function (x, t, b, c, d) {
                    if ((t /= d) < (1 / 2.75)) {
                        return c * (7.5625 * t * t) + b;
                    } else if (t < (2 / 2.75)) {
                        return c * (7.5625 * (t -= (1.5 / 2.75)) * t + .75) + b;
                    } else if (t < (2.5 / 2.75)) {
                        return c * (7.5625 * (t -= (2.25 / 2.75)) * t + .9375) + b;
                    } else {
                        return c * (7.5625 * (t -= (2.625 / 2.75)) * t + .984375) + b;
                    }
                },
     
                easeInOutBounce: function (x, t, b, c, d) {
                    var s = 1.70158;
                    var p = 0;
                    var a = c;
                    if (t == 0) return b;
                    if ((t /= d / 2) == 2) return b + c;
                    if (!p) p = d * (.3 * 1.5);
                    if (a < Math.abs(c)) {
                        a = c;
                        var s = p / 4;
                    }
                    else s = p / (2 * Math.PI) * Math.asin(c / a);
                    if (t < 1) return -.5 * (a * Math.pow(2, 10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p)) + b;
                    return a * Math.pow(2, -10 * (t -= 1)) * Math.sin((t * d - s) * (2 * Math.PI) / p) * .5 + c + b;
                }
            })
     
     
            //Create Accordion object
            accordion = new Div("container");
            accordion.AddInlineStyle("position", "relative");
            ul = new Ul();
            accordion.AddChild(ul);
     
            var configData =null;
            var i = 0;
            var itemsNumber;
            if(opts.htmlConfig==true) {
                configData = $(mainElem + ' ul');
               
                itemsNumber = $(configData).find('li').length;
               
                ParseConfigData(configData);
            }else {
                //##### Load XML Configuration ####
                $.ajax({
                    type: "GET",
                    url: opts.assetsPath + "/data.xml",
                    dataType: "xml",
                    success: function (xml) {
                        itemsNumber = $(xml).find('li').length;
                        ParseConfigData($(xml));
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(onLoadMessage);
                    }
                });
     
            }
     
            function ParseConfigData(xml)
            {
                xml.find('li').each(function () {
                    var path;
                    var leftText;
                    var bottomText;
                    var url;
                    var urlTarget;
       
                    path = jQuery.trim($(this).find('img').attr("src"));
     
                    if(opts.htmlConfig) {
                        leftText = $.trim($(this).find('p[pos="left"]').html());
                        bottomText = $.trim($(this).find('p[pos="bottom"]').html());
                        url = $.trim($(this).find('a[name="link"]').html());
                    }
                    else{
                        leftText = $(this).find('p[pos="left"]').text();
                        bottomText = $(this).find('p[pos="bottom"]').text();
                        url = $(this).find('a[name="link"]').text();
                    }
                    urlTarget = jQuery.trim($(this).find('a[name="link"]').attr("target"));
     
                    li = new Li();
                    ul.AddChild(li);
     
                    divTitle = new Div();
                    divTitle.AddAttributesWithValue("class", "title");
                    divTitle.AddHtml(leftText);
                    if(opts.showLeftText ==true)
                        li.AddChild(divTitle);
     
                    divItem = new Div();
                    divItem.AddAttributesWithValue("class", "item");
                    li.AddChild(divItem);
     
     
                    divBottomText = new Div();
                    divBottomText.AddAttributesWithValue("class", "bottom-text");
                    divBottomText.AddHtml(bottomText);
                    if(opts.showBottomText ==true)
                        divItem.AddChild(divBottomText);
     
                    image = new Img();
                    image.AddAttributesWithValue("src", path);
                    if (url != "") {
                        var linkAccordion = new A();
                        linkAccordion.AddAttributesWithValue("href", url);
                        if (urlTarget != 'undefined' && urlTarget != '')
                            linkAccordion.AddAttributesWithValue("target", urlTarget);
     
                        divItem.AddChild(linkAccordion);
                        linkAccordion.AddChild(image);
                    //linkAccordion.
                    }
                    else {
                        divItem.AddChild(image);
                    }
     
                    if (i == itemsNumber - 1) {
                        li.AddAttributesWithValue("class", "last");
                        li.AddInlineStyle("border-right", "none");
                    }
                    i++;
                });
               
                CreateGallery(itemsNumber);
            }      
           
            function CreateGallery(itemsNumber) {
                var slideWidth = 0;
                slideWidth = (opts.galleryWidth / itemsNumber) - 1;
                expandSlideWidth = (opts.galleryWidth - opts.pictureWidth) / (itemsNumber - 1) - 1
             
                var cssString = '<style type="text/css">'+mainElem+' .title{width:' + opts.galleryHeight + 'px;height:30px;background-color:' + opts.backgroundColor + ';-moz-transform-origin:0 0;-webkit-transform-origin:0 0;-webkit-transform:rotate(-90deg);-moz-transform:rotate(-90deg);color:#fff;font-size:13px;text-align:center;vertical-align:middle;position:absolute;bottom:-30px;left:0;line-height:30px;font-family:Arial}</style> <!--[if IE]><style type="text/css"> .title {top:0;filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);}</style><![endif]--> <style type="text/css"> '+mainElem+'{position:relative;width:' + opts.galleryWidth + 'px;height:' + opts.galleryHeight + 'px;background-color:#000;font-size:18px;overflow:hidden}'+mainElem+' ul{width:' + opts.galleryWidth + 'px;height:' + opts.galleryHeight + 'px;margin:0;padding:0}'+mainElem+' li{float:left;height:' + opts.galleryHeight + 'px;width:' + slideWidth + 'px;list-style:none;border-right:solid 1px ' + opts.borderColor + ';position:relative;overflow:hidden}'+mainElem+' li .item{height:' + opts.galleryHeight + 'px;width:' + opts.pictureWidth + 'px;position:absolute}'+mainElem+' li img{height:' + opts.galleryHeight + 'px;width:' + opts.pictureWidth + 'px; border-width:0;}.last{overflow:visible!important;overflow-x:visible!important;overflow-y:visible!important}'+mainElem+' .bottom-text{background-color:#000;width:'+(opts.pictureWidth-20)+'px;padding:5px 10px 5px 10px; text-align:justify;font-family:Arial;color:#fff;font-weight:400;font-size:12px;position:absolute;bottom:-100px}</style>';
           
                $("head").append(cssString);
                if(configData!=null)
                    configData.remove();
     
                fl = new ISEObject();
                fl.Render(mainElem, accordion);
               
             
                //$(mainElem).append("<div style='position:absolute; top:10px; left:10px; font-family:Arial; font-size:12px;'><a style='color:white; text-decoration:none;' href='http://flcomponents.net/Components/Item/JQuery_Accordion_Gallery'>Buy this plugin<a/></div>");
                //##### Aply the animations #####
                $(mainElem+' .item').fadeTo(0, opts.imagesOpacity, function () {
                    // Animation complete.
                    });
     
                $(mainElem+' .bottom-text').fadeTo(0, opts.bottomTextOpacity, function () {
                    // Animation complete.
                    });
     
                if(!opts.autoPlay){
                    if(opts.eventType=='over')
                    {
                        $(mainElem+'  li').mouseenter(function () {
                            ChangeSlide(mainElem, this);
                        });
                    }
     
                    if(opts.eventType=='click')
                    {
                        $(mainElem+'  li').click(function () {
                            ChangeSlide(mainElem, this);
                        });
                    }
     
                    $(mainElem+'  .item').mouseenter(function () {
                        EnterEffect(this);
                    });
     
                    $(mainElem+' ').mouseleave(function () {
                        $(mainElem+'  li').animate({
                            width: slideWidth
                        }, {
                            duration: opts.animationTime,
                            queue: false,
                            easing: "swing",
                            complete: function () {
     
                            }
                        });
     
                    })
     
                    $(mainElem+'  .item').mouseleave(function () {
                        OutEffect(this);
                    })
                }
                else
                {
                    $(mainElem+' ').mouseenter(function () {
                        autoPlayMouseOver=true;
                    });
                    $(mainElem+' ').mouseleave(function () {
                        autoPlayMouseOver=false;
                    });
                }
               
            }
     
            function ChangeSlide(mainElem, object)
            {
                var t = object;
                $(mainElem+'  li').animate({
                    width: expandSlideWidth
                }, {
                    duration: opts.animationTime,
                    queue: false,
                    easing: "swing",
                    complete: function () {
                    }
                });
     
     
                $(t).find(".bottom-text").animate({
                    bottom: 10
                }, {
                    duration: 1200,
                    queue: false,
                    easing: "easeInOutBounce"
                });
                $(t).animate({
                    width: opts.pictureWidth
                }, {
                    duration: opts.animationTime,
                    queue: false,
                    easing: "swing",
                    complete: function () {
                    }
                });
            }
     
            function EnterEffect(obj)
            {
                var t = obj;
                $(t).fadeTo(opts.animationTime, 1, function () {
                    $(t).clearQueue();
                });
            }
     
            function OutEffect(obj)
            {
                var t = obj;
                $(t).find(".bottom-text").animate({
                    bottom: -$(t).find(".bottom-text").height() - 15
                }, {
                    duration: 1200,
                    queue: false,
                    easing: "easeInOutBounce"
                });
                $(t).fadeTo(opts.animationTime, opts.imagesOpacity, function () {
                    $(t).clearQueue();
                });
            }
     
            //Auto switching funcionality
            var autoSlideNumber=0;
            var liList = $(mainElem+'  li');
            var itemList = $(mainElem+'  .item');
            function display() {
                if(!autoPlayMouseOver)
                {
                    if(opts.switchOrder=='ltr') {
                        if(autoSlideNumber!=0)
                            OutEffect(itemList[autoSlideNumber-1]);
                        else
                            OutEffect(itemList[itemsNumber-1]);
                        ChangeSlide(mainElem, liList[autoSlideNumber]);
                        EnterEffect(itemList[autoSlideNumber]);
                        autoSlideNumber++;
                        if(autoSlideNumber==itemsNumber)
                            autoSlideNumber=0;
                    }
                    if(opts.switchOrder=='rtl') {
                        autoSlideNumber++;
                        if(autoSlideNumber!=1)
                            OutEffect(itemList[itemsNumber-autoSlideNumber+1]);
                        else
                            OutEffect(itemList[0]);
                       
                        ChangeSlide(mainElem, liList[itemsNumber-autoSlideNumber]);
                        EnterEffect(itemList[itemsNumber-autoSlideNumber]);
                        if(autoSlideNumber==itemsNumber)
                            autoSlideNumber=0;                    
                    }
                }
                setTimeout(display, opts.switchTime*1000);
            }
           
            if(opts.autoPlay)
                display();
           
        };
     
     
        $.fn.ISEAccordion.defaults = {
            galleryWidth: 600,
            galleryHeight: 400,
            pictureWidth: 300,
            backgroundColor: '#222',
            borderColor: '#9999aa',
            imagesOpacity: 0.5,
            bottomTextOpacity: 0.6,
            eventType:'over',
            showLeftText: true,
            showBottomText:true,
            animationTime:600,
            assetsPath: 'assets',
            htmlConfig: true,
            autoPlay: false,
            switchTime: 2,
            switchOrder:'ltr'
        };
    })(jQuery);
