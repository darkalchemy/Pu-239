/* if you have ajax/core.js included - comment next function */
function addEvent(obj, type, fn) {
	if (obj.addEventListener) {
		obj.addEventListener(type, fn, false);
	} else if (obj.attachEvent) {
		obj["e"+type+fn] = fn;
		obj[type+fn] = function() {obj["e"+type+fn](window.event); }
		obj.attachEvent("on"+type, obj[type+fn]);
	}
}
/* comment to here */

function AppendPlusMinus()
{
	var tds = document.getElementsByTagName('td');
	for (x in tds)
	{
		if (tds[x].className == 'tbtr' || tds[x].className == 'tbtr_block')
		{
			a = document.createElement('a');
			a.href = '#';
			a.innerHTML = tds[x].innerHTML;
			tds[x].innerHTML = '';;
			tds[x].appendChild(a);
			el = tds[x];
			addEvent(a, 'click', ShowHide);
			name = tds[x].previousSibling;
			while (name.nodeType == 3)
				name = name.previousSibling;
			name = name.innerHTML.replace(/(<([^>]+)>)/ig,"");
			if (CheckCookie(name))
			{
				el.className = el.className + '_close';
				while (el.nodeName != 'TABLE')
					el = el.parentNode;
				el = el.nextSibling;
				while (el.nodeName != 'TABLE')
					el=el.nextSibling;
				el.style.display='none';
			}
			
		}
	}
}

function CheckCookie(name)
{
	if (!name) return false;
	name = name.replace(/\(\d+\)/, '');
	name = escape(name);
	cookies = document.cookie.split(/;/);
	for (x in cookies)
	{
		cookie = cookies[x].split(/=/);
		if (cookie[0].replace(/ /, '') == 'blocks')
		{
			/* edit this one */
			return eval('cookie[1].match(/(^|,)' + name + '(,|$)/);') != null;
		}
	}
}

function ShowHide(e)
{
	var el;
	if (window.event && window.event.srcElement) { el = window.event.srcElement; }
	if (e && e.target) { el = e.target; }
	if (!el) { return; }
	(e.preventDefault) ? e.preventDefault() : (e.returnValue = false);
	
	while (el.nodeName != 'TD') el=el.parentNode; // go to TD
	name = el.previousSibling;
	while (name.nodeType == 3)
		name = name.previousSibling;
	
	var foldmode = -1;
	
	if (el.className == 'tbtr' || el.className == 'tbtr_block')
	{
		el.className = el.className + '_close';
		el = el.parentNode.parentNode.parentNode.nextSibling;
		while (el.nodeName != 'TABLE')
			el=el.nextSibling;
		el.style.display='none';
		foldmode = 1;
	}
	else
	{
		if (el.className == 'tbtr_close')
			el.className = 'tbtr';
		else
			el.className = 'tbtr_block';
		el = el.parentNode.parentNode.parentNode.nextSibling;
		while (el.nodeName != 'TABLE')
			el=el.nextSibling;
		el.style.display='';
		foldmode = 2;
	}
	
	if (foldmode == -1 || !name)
		return;
	
	name = name.innerHTML.replace(/(<([^>]+)>)/ig,"");
	name = name.replace(/\(\d+\)/, '');

	cookies = document.cookie.split(/;/);
	var found = 0;
	for (x in cookies)
	{
		cookie = cookies[x].split(/=/);
		if (cookie[0].replace(/ /, '') == 'blocks')
		{
			/* edit this one */
			found = 1;

			if (foldmode != 1)
			{
				/* remove */
				cookie[1] = cookie[1].replace(escape(name), '').replace(/(,,)/, ',').replace(/(,$)|(^,)/g, '');
			}
			else
			{
				if (cookie[1].indexOf(escape(name)) != -1)
					return;
				/* add */
				if (cookie[1])
					cookie[1] += ',' + escape(name);
				else
					cookie[1] = escape(name);
					
				cookie[1] = cookie[1].replace(/(,,)/, ',').replace(/(,$)|(^,)/g, '');
			}
			var exdate=new Date()
			exdate.setDate(exdate.getDate()+31)
			document.cookie = 'blocks=' + cookie[1] + ';expires=' + exdate.toGMTString() + ';';
			break;
		}
	}
	if (!found)
	{
		var exdate=new Date()
		exdate.setDate(exdate.getDate()+31)
		document.cookie = 'blocks=' + escape(name) + ';expires=' + exdate.toGMTString() + ';';
	}
	
}

addEvent(window, 'load', AppendPlusMinus);