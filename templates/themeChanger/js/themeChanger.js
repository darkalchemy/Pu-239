var page_config = {
    nav: {
        0: {
            name: 'Light',
            className: 'skin-1'
        },
        1: {
            name: 'Dark',
            className: 'skin-2'
        }
    },
    backgrounds: {
        0: {
            name: 'Background 1',
            className: 'background-1'
        },
        1: {
            name: 'Background 2',
            className: 'background-2'
        },
        2: {
            name: 'Background 3',
            className: 'background-3'
        },
        3: {
            name: 'Background 4',
            className: 'background-4'
        },
        4: {
            name: 'Background 5',
            className: 'background-5'
        },
        5: {
            name: 'Background 6',
            className: 'background-6'
        },
        6: {
            name: 'Background 7',
            className: 'background-7'
        },
        7: {
            name: 'Background 8',
            className: 'background-8'
        },
        8: {
            name: 'Background 9',
            className: 'background-9'
        },
        9: {
            name: 'Background 10',
            className: 'background-10'
        },
        10: {
            name: 'Background 11',
            className: 'background-11'
        },
        11: {
            name: 'Background 12',
            className: 'background-12'
        },
        12: {
            name: 'Background 13',
            className: 'background-13'
        },
        13: {
            name: 'Background 14',
            className: 'background-14'
        },
        14: {
            name: 'Background 15',
            className: 'background-15'
        },
        15: {
            name: 'Default',
            className: 'background-16'
        }
    },
    styles: {
        headerStyle: {
            name: 'Heading Font',
            id: 'heading_style',
            list: {
                0: {
                    name: 'Oswald',
                    className: 'h-style-1',
                    class: 'text-1'
                },
                1: {
                    name: 'PT Sans Narrow',
                    className: 'h-style-2',
                    class: 'text-2'
                },
                2: {
                    name: 'Nova Square',
                    className: 'h-style-3',
                    class: 'text-3'
                },
                3: {
                    name: 'Lobster',
                    className: 'h-style-4',
                    class: 'text-4'
                },
                4: {
                    name: 'Open Sans',
                    className: 'h-style-5',
                    class: 'text-5'
                },
                5: {
                    name: 'Encode Sans Condensed',
                    className: 'h-style-6',
                    class: 'text-6'
                },
                6: {
                    name: 'Baloo Bhaijaan',
                    className: 'h-style-7',
                    class: 'text-7'
                },
                7: {
                    name: 'Acme',
                    className: 'h-style-8',
                    class: 'text-8'
                },
                8: {
                    name: 'Default',
                    className: 'h-style-9',
                    class: 'text-9'
                }
            }
        },
        textStyle: {
            name: 'Content Font',
            id: 'text_style',
            list: {
                0: {
                    name: 'Oswald',
                    className: 'text-1',
                    class: 'text-1'
                },
                1: {
                    name: 'PT Sans Narrow',
                    className: 'text-2',
                    class: 'text-2'
                },
                2: {
                    name: 'Nova Square',
                    className: 'text-3',
                    class: 'text-3'
                },
                3: {
                    name: 'Lobster',
                    className: 'text-4',
                    class: 'text-4'
                },
                4: {
                    name: 'Open Sans',
                    className: 'text-5',
                    class: 'text-5'
                },
                5: {
                    name: 'Encode Sans Condensed',
                    className: 'text-6',
                    class: 'text-6'
                },
                6: {
                    name: 'Baloo Bhaijaan',
                    className: 'text-7',
                    class: 'text-7'
                },
                7: {
                    name: 'Acme',
                    className: 'text-8',
                    class: 'text-8'
                },
                8: {
                    name: 'Default',
                    className: 'text-9',
                    class: 'text-9'
                }
            }
        }
    }
};

$(function () {
    var $body = $('body');
    var $nav = $('.navigation li a');
    var $theme_control_panel = $('#control_panel');
    var a_color = localStorage.getItem('a_color');
    if (a_color != null) {
        $('body').get(0).style.setProperty('--main-color', '#' + a_color);
        $('iframe').contents().find('body').css('--main-color', '#' + a_color);
    }

    function changeBodyClass(className, classesArray) {
        $.each(classesArray, function (idx, val) {
            $body.removeClass(val);
        });
        $body.addClass(className);
        var body_class = localStorage.getItem('theme');
        if (body_class != null) {
            new_class_pattern = className.replace(/\d{1,2}$/, '');
            body_class = body_class.replace(new RegExp(new_class_pattern + '\\d{1,2}'), className);
            localStorage.setItem('theme', body_class);
        } else {
            body_class = $body.attr('class');
            localStorage.setItem('theme', body_class);
        }
    }

    if (typeof page_config != 'undefined' && $theme_control_panel) {
        var pattern_classes = new Array();
        var nav = new Array();
        var defaultSettings = {};
        if (page_config.nav) {
            var $bg_block = $('<div/>').attr('id', 'nav').addClass('style_block clearfix');
            var $header = $('#header');
            var bg_change_html = '<span>Menu Skin:</span>';
            bg_change_html += '<ul>';
            $.each(page_config.nav, function (idx, val) {
                bg_change_html += '<li><a href="' + val.className + '" title="' + val.name + '" class="tooltipper ' + val.className + '"></a></li>';
                nav.push(val.className);
            });
            bg_change_html += '</ul>';
            $bg_block.html(bg_change_html);
            $theme_control_panel.append($bg_block);
            $bg_block.find('a').click(function () {
                var nextClassName = $(this).attr('href');
                if (!$body.hasClass(nextClassName)) {
                    changeBodyClass(nextClassName, nav);
                    $bg_block.find('.is-active').removeClass('is-active');
                    $(this).parent().addClass('is-active');
                }
                return false;
            });
        }
        if (page_config.backgrounds) {
            var $bg_block = $('<div/>').attr('id', 'backgrounds').addClass('style_block');
            var bg_change_html = '<span>Backgrounds:</span>';
            bg_change_html += '<ul class="limited">';
            $.each(page_config.backgrounds, function (idx, val) {
                bg_change_html += '<li><a href="' + val.className + '" title="' + val.name + '" class="tooltipper ' + val.className + '"></a></li>';
                pattern_classes.push(val.className);
            });
            bg_change_html += '</ul>';
            $bg_block.html(bg_change_html);
            $theme_control_panel.append($bg_block);
            $bg_block.find('a').click(function () {
                var nextClassName = $(this).attr('href');
                if (!$body.hasClass(nextClassName)) {
                    changeBodyClass(nextClassName, pattern_classes);
                    $bg_block.find('.is-active').removeClass('is-active');
                    $(this).parent().addClass('is-active');
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
            $.each(page_config.styles, function (idx, val) {
                $style_block = $('<div/>').addClass('style_block');
                $block_label = $('<span>' + val.name + ':</span>');
                $select_element = $('<select/>').attr({
                    id: val.id
                });
                select_html = '';
                $.each(val.list, function (list_idx, list_val) {
                    if (list_val.class) {
                        var classes = ' class="' + list_val.class + '"';
                    }
                    if ($body.hasClass(list_val.className)) {
                        select_html += '<option value="' + list_val.className + '" ' + (classes || '') + ' selected="selected">' + list_val.name + '</option>';
                    } else {
                        select_html += '<option value="' + list_val.className + '"' + (classes || '') + '>' + list_val.name + '</option>';
                    }
                });
                $select_element.html(select_html);
                $style_block.append($block_label, $select_element);
                $theme_control_panel.append($style_block);
            });
            $.each(page_config.styles.headerStyle.list, function (idx, val) {
                header_style_classes.push(val.className);
            });
            $('#heading_style').change(function () {
                if (!$body.hasClass($(this).val())) {
                    changeBodyClass($(this).val(), header_style_classes);
                }
            });
            $.each(page_config.styles.textStyle.list, function (idx, val) {
                text_style_classes.push(val.className);
            });
            $('#text_style').change(function () {
                if (!$body.hasClass($(this).val())) {
                    changeBodyClass($(this).val(), text_style_classes);
                    $('iframe').contents().find('body').removeClass('text-1 text-2 text-3 text-4 text-5 text-6 text-7 text-8').addClass($(this).val());
                }
            });
            $links_color = $('<div/>').attr({
                id: 'linkspicker'
            }).addClass('colorPicker');
            $links_color_wrapper = $('<div/>').addClass('links_color_wrapper clearfix');
            $links_color_wrapper.append('<span>Links Color:</span>', $links_color);
            $theme_control_panel.append($links_color_wrapper);
            var links_picker = $('#linkspicker');
            links_picker.css('background-color', '#0a0b35').ColorPicker({
                color: '#0a0b35',
                onChange: function (hsb, hex, rgb) {
                    links_picker.css('backgroundColor', '#' + hex);
                    $('body').get(0).style.setProperty('--main-color', '#' + hex);
                    $('iframe').contents().find('body').css('--main-color', '#' + hex);
                    localStorage.setItem('a_color', hex);
                }
            });
            var setDefaultsSettings = function () {
                changeBodyClass(page_config.nav[1].className, nav);
                changeBodyClass(page_config.backgrounds[15].className, pattern_classes);
                $theme_control_panel.find('select').val(1);
                changeBodyClass(page_config.styles.headerStyle.list[8].className, header_style_classes);
                changeBodyClass(page_config.styles.textStyle.list[8].className, text_style_classes);
                $('iframe').contents().find('body').removeClass('text-1 text-2 text-3 text-4 text-5 text-6 text-7 text-8').addClass('text-9');
                links_picker.css({
                    'background-color': '#008a05'
                }).ColorPickerSetColor('#008a05');
                $('body').get(0).style.setProperty('--main-color', '#9193de');
                $('iframe').contents().find('body').css('--main-color', '#9193de');
                $theme_control_panel.find('.is-active').removeClass();
                localStorage.removeItem('a_color');
                return false;
            };
            var $restore_button_wrapper = $('<div/>').addClass('restore_button_wrapper');
            var $restore_button = $('<a/>').text('Reset').attr('id', 'restore_button').addClass('button small dark').click(setDefaultsSettings);
            $restore_button_wrapper.append($restore_button);
            $theme_control_panel.append($restore_button_wrapper);
        }
        var $theme_control_panel_label = $('#control_label');
        $theme_control_panel_label.click(function () {
            if ($theme_control_panel.hasClass('visible')) {
                $theme_control_panel.animate({
                    left: -210
                }, 400, function () {
                    $theme_control_panel.removeClass('visible');
                });
            } else {
                $theme_control_panel.animate({
                    left: 0
                }, 400, function () {
                    $theme_control_panel.addClass('visible');
                });
            }
            return false;
        });
    }
});
