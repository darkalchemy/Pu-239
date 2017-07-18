/**
 * glossy.js 1.51 (21-Mar-2009)
 * (c) by Christian Effenberger 
 * All Rights Reserved
 * Source: glossy.netzgesta.de
 * Distributed under Netzgestade Software License Agreement
 * http://www.netzgesta.de/cvi/LICENSE.txt
 * License permits free of charge
 * use on non-commercial and 
 * private web sites only 
**/

var tmp = navigator.appName == 'Microsoft Internet Explorer' && navigator.userAgent.indexOf('Opera') < 1 ? 1 : 0;
if(tmp) var isIE = document.namespaces ? 1 : 0;

if(isIE) {
	if(document.namespaces['v']==null) {
		var e=["shape","shapetype","group","background","path","formulas","handles","fill","stroke","shadow","textbox","textpath","imagedata","line","polyline","curve","roundrect","oval","rect","arc","image"],s=document.createStyleSheet(); 
		for(var i=0; i<e.length; i++) {s.addRule("v\\:"+e[i],"behavior: url(#default#VML);");} document.namespaces.add("v","urn:schemas-microsoft-com:vml");
	} 
}

function getImages(className){
	var children = document.getElementsByTagName('img'); 
	var elements = new Array(); var i = 0;
	var child; var classNames; var j = 0;
	for (i=0;i<children.length;i++) {
		child = children[i];
		classNames = child.className.split(' ');
		for (var j = 0; j < classNames.length; j++) {
			if (classNames[j] == className) {
				elements.push(child);
				break;
			}
		}
	}
	return elements;
}

function getClasses(classes,string){
	var temp = '';
	for (var j=0;j<classes.length;j++) {
		if (classes[j] != string) {
			if (temp) {
				temp += ' '
			}
			temp += classes[j];
		}
	}
	return temp;
}

function getClassValue(classes,string){
	var temp = 0; var pos = string.length;
	for (var j=0;j<classes.length;j++) {
		if (classes[j].indexOf(string) == 0) {
			temp = Math.min(classes[j].substring(pos),100);
			break;
		}
	}
	return Math.max(0,temp);
}

function getClassColor(classes,string){
	var temp = 0; var str = ''; var pos = string.length;
	for (var j=0;j<classes.length;j++) {
		if (classes[j].indexOf(string) == 0) {
			temp = classes[j].substring(pos);
			str = '#' + temp.toLowerCase();
			break;
		}
	}
	if(str.match(/^#[0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f]$/i)) {
		return str;
	}else {
		return 0;
	}
}

function getClassAttribute(classes,string){
	var temp = 0; var pos = string.length;
	for (var j=0;j<classes.length;j++) {
		if (classes[j].indexOf(string) == 0) {
			temp = 1; break;
		}
	}
	return temp;
}

function roundedRect(ctx,x,y,width,height,radius,nopath){
	if (!nopath) ctx.beginPath();
	ctx.moveTo(x,y+radius);
	ctx.lineTo(x,y+height-radius);
	ctx.quadraticCurveTo(x,y+height,x+radius,y+height);
	ctx.lineTo(x+width-radius,y+height);
	ctx.quadraticCurveTo(x+width,y+height,x+width,y+height-radius);
	ctx.lineTo(x+width,y+radius);
	ctx.quadraticCurveTo(x+width,y,x+width-radius,y);
	ctx.lineTo(x+radius,y);
	ctx.quadraticCurveTo(x,y,x,y+radius);
	if (!nopath) ctx.closePath();
}

function addRadialStyle(ctx,x1,y1,r1,x2,y2,r2,opacity) {
	var tmp = ctx.createRadialGradient(x1,y1,r1,x2,y2,r2);
	var opt = Math.min(parseFloat(opacity+0.1),1.0);
	tmp.addColorStop(0,'rgba(0,0,0,'+opt+')');
	tmp.addColorStop(0.25,'rgba(0,0,0,'+opacity+')');
	tmp.addColorStop(1,'rgba(0,0,0,0)');
	return tmp;
}

function addLinearStyle(ctx,x,y,w,h,opacity) {
	var tmp = ctx.createLinearGradient(x,y,w,h);
	var opt = Math.min(parseFloat(opacity+0.1),1.0);
	tmp.addColorStop(0,'rgba(0,0,0,'+opt+')');
	tmp.addColorStop(0.25,'rgba(0,0,0,'+opacity+')');
	tmp.addColorStop(1,'rgba(0,0,0,0)');
	return tmp;
}

function addBright(ctx,x,y,width,height,radius,opacity) {
	var style = ctx.createLinearGradient(0,y,0,y+height);
	style.addColorStop(0,'rgba(254,254,254,'+opacity+')');
	style.addColorStop(1,'rgba(254,254,254,0.1)');
	ctx.beginPath();
	ctx.moveTo(x,y+radius);
	ctx.lineTo(x,y+height-radius);
	ctx.quadraticCurveTo(x,y+height,x+radius,y+height);
	ctx.lineTo(x+width-radius,y+height);
	ctx.quadraticCurveTo(x+width,y+height,x+width,y+height-radius);
	ctx.lineTo(x+width,y+radius);
	ctx.quadraticCurveTo(x+width,y,x+width-radius,y);
	ctx.lineTo(x+radius,y);
	ctx.quadraticCurveTo(x,y,x,y+radius);
	ctx.closePath();
	ctx.fillStyle = style;
	ctx.fill();
}

function addDark(ctx,x,y,width,height,radius,opacity) {
	var style = ctx.createLinearGradient(0,y,0,y+height);
	style.addColorStop(0,'rgba(0,0,0,0)');
	style.addColorStop(1,'rgba(0,0,0,'+opacity+')');
	ctx.beginPath();
	ctx.moveTo(x,y);
	ctx.lineTo(x,y+height-radius);
	ctx.quadraticCurveTo(x,y+height,x+radius,y+height);
	ctx.lineTo(x+width-radius,y+height);
	ctx.quadraticCurveTo(x+width,y+height,x+width,y+height-radius);
	ctx.lineTo(x+width,y);
	ctx.lineTo(x,y);
	ctx.closePath();
	ctx.fillStyle = style;
	ctx.fill();
}

function addFrame(ctx,x,y,width,height,radius,opacity) {
	roundedRect(ctx,x,y,width,height,radius);
	var style = ctx.createLinearGradient(0,0,0,height);
	style.addColorStop(0,'rgba(254,254,254,'+opacity+')');
	style.addColorStop(1,'rgba(0,0,0,'+opacity+')');
	ctx.lineWidth = (radius+x)/2;
	ctx.strokeStyle = style;
	ctx.stroke();
}

function glossyShadow(ctx,x,y,width,height,radius,opacity){
	var style; var os = radius/2;
	ctx.beginPath();
	ctx.rect(x+radius,y,width-(radius*2),y+os);
	ctx.closePath();
	style = addLinearStyle(ctx,x+radius,y+os,x+radius,y,opacity);
	ctx.fillStyle = style;
	ctx.fill();
	ctx.beginPath();
	ctx.rect(x,y,radius,radius);
	ctx.closePath();
	style = addRadialStyle(ctx,x+radius,y+radius,radius-os,x+radius,y+radius,radius,opacity);
	ctx.fillStyle = style;
	ctx.fill();
	ctx.beginPath();
	ctx.rect(x,y+radius,os,height-(radius*2));
	ctx.closePath();
	style = addLinearStyle(ctx,x+os,y+radius,x,y+radius,opacity);
	ctx.fillStyle = style;
	ctx.fill();
	ctx.beginPath();
	ctx.rect(x,y+height-radius,radius,radius);
	ctx.closePath();
	style = addRadialStyle(ctx,x+radius,y+height-radius,radius-os,x+radius,y+height-radius,radius,opacity);
	ctx.fillStyle = style;
	ctx.fill();
	ctx.beginPath();
	ctx.rect(x+radius,y+height-os,width-(radius*2),os);
	ctx.closePath();
	style = addLinearStyle(ctx,x+radius,y+height-os,x+radius,y+height,opacity);
	ctx.fillStyle = style;
	ctx.fill();
	ctx.beginPath(); 
	ctx.rect(x+width-radius,y+height-radius,radius,radius);
	ctx.closePath();
	style = addRadialStyle(ctx,x+width-radius,y+height-radius,radius-os,x+width-radius,y+height-radius,radius,opacity);
	ctx.fillStyle = style;
	ctx.fill();
	ctx.beginPath();
	ctx.rect(x+width-os,y+radius,os,height-(radius*2));
	ctx.closePath();
	style = addLinearStyle(ctx,x+width-os,y+radius,x+width,y+radius,opacity);
	ctx.fillStyle = style;
	ctx.fill();
	ctx.beginPath();
	ctx.rect(x+width-radius,y,radius,radius);
	ctx.closePath();
	style = addRadialStyle(ctx,x+width-radius,y+radius,radius-os,x+width-radius,y+radius,radius,opacity);
	ctx.fillStyle = style;
	ctx.fill();
}

function addIEGlossy() {
	var theimages = getImages('glossy');
	var image; var object; var canvas; var context; var i;
	var iradius = null; var sradius = null; var noshadow = 0;
	var ibgcolor = null; var igradient = null; var horizontal = 0;
	var factor = 0.25; var classes = ''; var newClasses = ''; 
	var maxdim = null; var inset = 0; var offset = 0; var style = '';
	var width = 0; var height = 0; var vml = null; var flt = null;
	var display = null; var xradius = null; var angle;
	var head; var foot; var fill; var shade; var tmp;
	for(i=0;i<theimages.length;i++) {	
		image = theimages[i]; object = image.parentNode; 
		head = ''; foot = ''; fill = ''; shade = ''; tmp = '';
		if(image.width>=16 && image.height>=16) {
			classes = image.className.split(' '); 
			horizontal = 0; igradient = 0; factor = 0.25;
			noshadow = 0; iradius = 0; ibgcolor = 0;
			iradius = getClassValue(classes,"iradius");
			ibgcolor = getClassColor(classes,"ibgcolor");
			igradient = getClassColor(classes,"igradient");
			noshadow = getClassAttribute(classes,"noshadow");
			horizontal = getClassAttribute(classes,"horizontal");
			newClasses = getClasses(classes,"glossy");
			width = image.width; height = image.height;
			maxdim = Math.min(width,height)/2; angle = 0;
			factor = iradius>0?Math.min(Math.max(iradius,20),50)/100:factor;
			iradius = Math.round(45*factor);
			xradius = Math.round(Math.max(Math.round(maxdim*factor),4)/4)*4;
			if(noshadow<1) {
				offset = xradius/4; sradius = iradius*0.75;
				inset = offset; radius = sradius; sradius = radius*0.75;
				shade = '<v:roundrect arcsize="' + radius + '%" strokeweight="0" filled="t" stroked="f" fillcolor="#000000" style="filter:Alpha(opacity=60), progid:dxImageTransform.Microsoft.Blur(PixelRadius=' + inset + ', MakeShadow=false); zoom:1;margin:-1px 0 0 -1px;padding: 0;display:block;position:absolute;top:' + inset + 'px;left:0px;width:' + (width-(2*inset)) + 'px;height:' + (height-(3*inset)) + 'px;"><v:fill color="#000000" opacity="1" /></v:roundrect>';
				tmp = '<v:rect strokeweight="0" filled="t" stroked="f" fillcolor="#ffffff" style="zoom:1;margin:-1px 0 0 -1px;padding: 0;display:block;position:absolute;top:0px;left:0px;width:' + width + 'px;height:' + height + 'px;"><v:fill color="#ffffff" opacity="0.0" /></v:rect>';
			}else {
				radius = iradius; inset = 0; 
				offset = xradius/4; sradius = iradius*0.75;
			}
			if(isNaN(ibgcolor)) {
				fill = '<v:roundrect arcsize="' + radius + '%" strokeweight="0" filled="t" stroked="f" fillcolor="#ffffff" style="zoom:1;margin:-1px 0 0 -1px;padding: 0;display:block;position:absolute;top:0px;left:' + inset + 'px;width:' + (width-(2*inset)) + 'px;height:' + (height-(2*inset)) + 'px;">';
				if(isNaN(igradient)) {
					if(horizontal>0) angle = 90;
					fill = fill + '<v:fill method="sigma" type="gradient" angle="' + angle + '" color="' + igradient + '" color2="' + ibgcolor + '" /></v:roundrect>';
				}else {
					fill = fill + '<v:fill color="' + ibgcolor + '" /></v:roundrect>';
				}
			}
			display = (image.currentStyle.display.toLowerCase()=='block')?'block':'inline-block';
			vml = document.createElement(['<var style="zoom:1;overflow:hidden;display:' + display + ';width:' + width + 'px;height:' + height + 'px;padding:0;">'].join(''));
			flt = image.currentStyle.styleFloat.toLowerCase();
			display = (flt=='left'||flt=='right')?'inline':display;
			head = '<v:group style="zoom:1; display:' + display + '; margin:-1px 0 0 -1px; padding:0; position:relative; width:' + width + 'px;height:' + height + 'px;" coordsize="' + width + ',' + height + '">' + tmp;
			foot = '<v:roundrect arcsize="' + radius + '%" strokeweight="0" filled="t" stroked="f" fillcolor="#ffffff" style="zoom:1;margin:-1px 0 0 -1px;padding: 0;display:block;position:absolute;top:0px;left:' + inset + 'px;width:' + (width-(2*inset)) + 'px;height:' + (height-(2*inset)) + 'px;"><v:fill src="' + image.src + '" type="frame" /></v:roundrect><v:roundrect arcsize="' + (sradius*2) + '%" strokeweight="0" filled="t" stroked="f" fillcolor="#ffffff" style="zoom:1;margin:-1px 0 0 -1px;padding: 0;display: block;position:absolute;top:' + offset + 'px;left:' + (offset+inset) + 'px;width:' + (width-(2*offset)-(2*inset)) + 'px;height:' + ((height/2)-offset-inset) + 'px;"><v:fill method="linear" type="gradient" angle="0" color="#ffffff" opacity="0.1" color2="#ffffff" o:opacity2="0.75" /></v:roundrect><v:roundrect arcsize="' + (radius*2) + '%" strokeweight="0" filled="t" stroked="f" fillcolor="#000000" style="zoom:1;margin:-1px 0 0 -1px;padding: 0;display: block;position:absolute;top:' + ((height/2)-inset) + 'px;left:' + inset + 'px;width:' + (width-(2*inset)) + 'px;height:' + ((height/2)-inset) + 'px;"><v:fill method="sigma" type="gradient" angle="180" color="#000000" opacity="0.0" color2="#000000" o:opacity2="0.5" /></v:roundrect></v:group>';
			vml.innerHTML = head + shade + fill + foot;
			vml.className = newClasses;
			vml.style.cssText = image.style.cssText;
			vml.style.visibility = 'visible';
			vml.src = image.src; vml.alt = image.alt;
			vml.width = image.width; vml.height = image.height;
			if(image.id!='') vml.id = image.id;
			if(image.title!='') vml.title = image.title;
			if(image.getAttribute('onclick')!='') vml.setAttribute('onclick',image.getAttribute('onclick'));
			object.replaceChild(vml,image);
		}
	}
}

function addGlossy() {
	var theimages = getImages('glossy');
	var image; var object; var canvas; var context; var i, radius;
	var iradius = null; var sradius = null; var noshadow = 0;
	var ibgcolor = null; var igradient = null; var horizontal = 0;
	var factor = 0.25; var classes = ''; var newClasses = ''; 
	var maxdim = null; var inset = 0; var offset = 0; var style = '';
	for(i=0;i<theimages.length;i++) {	
		image = theimages[i]; object = image.parentNode; 
		canvas = document.createElement('canvas');
		if(canvas.getContext && image.width>=16 && image.height>=16) {
			classes = image.className.split(' '); 
			horizontal = 0; igradient = 0; factor = 0.25;
			noshadow = 0; iradius = 0; ibgcolor = 0;
			iradius = getClassValue(classes,"iradius");
			ibgcolor = getClassColor(classes,"ibgcolor");
			igradient = getClassColor(classes,"igradient");
			noshadow = getClassAttribute(classes,"noshadow");
			horizontal = getClassAttribute(classes,"horizontal");
			newClasses = getClasses(classes,"glossy");
			canvas.className = newClasses;
			canvas.style.cssText = image.style.cssText;
			canvas.style.height = image.height+'px';
			canvas.style.width = image.width+'px';
			canvas.height = image.height;
			canvas.width = image.width;
			canvas.src = image.src; canvas.alt = image.alt;
			if(image.id!='') canvas.id = image.id;
			if(image.title!='') canvas.title = image.title;
			if(image.getAttribute('onclick')!='') canvas.setAttribute('onclick',image.getAttribute('onclick'));
			maxdim = Math.min(canvas.width,canvas.height)/2;
			factor = iradius>0?Math.min(Math.max(iradius,20),50)/100:factor;
			iradius = Math.max(Math.round(maxdim*factor),4);
			if(noshadow<1) {
				iradius = Math.round(iradius/4)*4;
				offset = iradius/4; sradius = iradius*0.75;
				inset = offset; radius = sradius; sradius = radius*0.75;
			}else {
				radius = iradius; inset = 0;
				offset = iradius/4; sradius = iradius*0.75;
			}
			context = canvas.getContext("2d");
			object.replaceChild(canvas,image);
			context.clearRect(0,0,canvas.width,canvas.height);
			if(noshadow<1) glossyShadow(context,0,0,canvas.width,canvas.height,iradius,0.5);
			context.save();
			if(!isNaN(ibgcolor)&&window.opera) {
				context.globalCompositeOperation = "destination-out";
				context.save();
				roundedRect(context,inset,0,canvas.width-(inset*2),canvas.height-(inset*2),radius);
				context.fillStyle='rgba(0,0,0,1)'; context.fill(); context.clip(); 
				context.clearRect(0,0,canvas.width,canvas.height);
				context.restore();
				roundedRect(context,inset,0,canvas.width-(inset*2),canvas.height-(inset*2),radius);
				context.clip(); context.globalCompositeOperation = "source-over";
			}else {
				roundedRect(context,inset,0,canvas.width-(inset*2),canvas.height-(inset*2),radius);
				context.clip();
			}
			if(isNaN(ibgcolor)) {
				if(isNaN(igradient)) {
					if(horizontal>0) {
						style = context.createLinearGradient(0,0,canvas.width,0);
					}else {
						style = context.createLinearGradient(0,0,0,canvas.height-(inset*2));
					}
					style.addColorStop(0,ibgcolor); 
					style.addColorStop(1,igradient);
					context.beginPath();
					context.rect(0,0,canvas.width,canvas.height-(inset*2));
					context.closePath();
					context.fillStyle = style;
					context.fill();
				}else {
					context.fillStyle = ibgcolor;
					context.fillRect(0,0,canvas.width,canvas.height-(inset*2));
				}
			}else {
				context.clearRect(0,0,canvas.width,canvas.height);
			}
			context.drawImage(image,inset,0,canvas.width-(inset*2),canvas.height-(inset*2));
			addBright(context,offset+inset,offset,canvas.width-(2*(offset+inset)),(canvas.height/2)-offset,sradius,0.75);
			addDark(context,inset,(canvas.height/2)-inset,canvas.width-(2*inset),(canvas.height/2)-inset,sradius,0.5);
			addFrame(context,inset,0,canvas.width-(inset*2),canvas.height-(inset*2),radius,0.25)
			canvas.style.visibility = 'visible';
		}
	}
}

var glossyOnload = window.onload;
window.onload = function () { if(glossyOnload) glossyOnload(); if(isIE){addIEGlossy(); }else {addGlossy();}}