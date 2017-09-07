var myBbcodeSettings = {
    nameSpace:          'bbcode',
    previewParserPath: './ajax/bbcode_parser.php',
    previewInElement: 'preview-window',
    markupSet: [
        {name:'Bold', key:'B', openWith:'[b]', closeWith:'[/b]'},
        {name:'Italic', key:'I', openWith:'[i]', closeWith:'[/i]'},
        {name:'Underline', key:'U', openWith:'[u]', closeWith:'[/u]'},
        {name:'Strike through', key:'S', openWith:'[s]', closeWith:'[/s]' },
        {separator:'---------------' },
        {name:'Picture', key:'P', replaceWith:'[img][![Url]!][/img]'},
        {name:'Link', key:'L', openWith:'[url=[![Url]!]]', closeWith:'[/url]', placeHolder:'Your text to link here...'},
        {separator:'---------------' },
        {name:'Colors', openWith:'[color=[![Color]!]]', closeWith:'[/color]', dropMenu: [
                {name:'Yellow', openWith:'[color=yellow]', closeWith:'[/color]', className:'col1-1' },
                {name:'Orange', openWith:'[color=orange]', closeWith:'[/color]', className:'col1-2' },
                {name:'Red', openWith:'[color=red]', closeWith:'[/color]', className:'col1-3' },
                {name:'Blue', openWith:'[color=blue]', closeWith:'[/color]', className:'col2-1' },
                {name:'Purple', openWith:'[color=purple]', closeWith:'[/color]', className:'col2-2' },
                {name:'Green', openWith:'[color=green]', closeWith:'[/color]', className:'col2-3' },
                {name:'White', openWith:'[color=white]', closeWith:'[/color]', className:'col3-1' },
                {name:'Gray', openWith:'[color=gray]', closeWith:'[/color]', className:'col3-2' },
                {name:'Black', openWith:'[color=black]', closeWith:'[/color]', className:'col3-3' }
            ]
        },
        {name:'Size', key:'S', openWith:'[size=[![Text size]!]]', closeWith:'[/size]', dropMenu :[
                {name:'xx-large', openWith:'[size=7]', closeWith:'[/size]' },
                {name:'x-large', openWith:'[size=6]', closeWith:'[/size]' },
                {name:'large', openWith:'[size=5]', closeWith:'[/size]' },
                {name:'medium', openWith:'[size=4]', closeWith:'[/size]' },
                {name:'small', openWith:'[size=3]', closeWith:'[/size]' },
                {name:'x-small', openWith:'[size=2]', closeWith:'[/size]' },
                {name:'xx-small', openWith:'[size=1]', closeWith:'[/size]' },
            ]
        },
        {separator:'---------------' },
        {name:'Unordered list', openWith:'[list]\n', closeWith:'[/list]'},
        {name:'Ordered list', openWith:'[list=[![Starting number]!]]\n', closeWith:'\n[/list]'},
        {name:'List item', openWith:'[*] '},
        {separator:'---------------' },
        {name:'Align Left', openWith:'[left]', closeWith:'[/left]'},
        {name:'Align Center', openWith:'[center]', closeWith:'[/center]'},
        {name:'Align Right', openWith:'[right]', closeWith:'[/right]'},
        {separator:'---------------' },
        {name:'Quotes', key:'Q', openWith:'[quote]', closeWith:'[/quote]'},
        {name:'Code', key:'K', openWith:'[code]', closeWith:'[/code]'},
        {separator:'---------------' },
        {name:'Table generator',
            className:'tablegenerator',
            placeholder:'Your text here...',
            replaceWith:function(h) {
                var cols = prompt('How many cols?'),
                    rows = prompt('How many rows?'),
                    thead = prompt('Is first row a table header? (yes or no)'),
                    html = '[table]\n';
                if (thead == 'yes') {
                    for (var c = 0; c < cols; c++) {
                        html += '\t[th] [![TH'+(c+1)+' text:]!][/th]\n';
                    }
                }
                for (var r = 0; r < rows; r++) {
                    html+= '\t[tr]\n';
                    for (var c = 0; c < cols; c++) {
                        html += '\t\t[td]'+(h.placeholder||'')+'[/td]\n';
                    }
                    html+= '\t[/tr]\n';
                }
                html += '[/table]';
                return html;
            }
        },
        {separator:'---------------' },
        {name:'Clean', className:'clean', replaceWith:function(h) { return h.selection.replace(/\[(.*?)\]/g, '') } },
        {name:'Preview', key:'!', className:'preview', call:'preview' }
    ]
}
