/******************************************************************* 
* File    : © JavaScript-FX.com
* Created : 2001/08/31 
* Author  : Roy Whittle  (Roy@Whittle.com) www.Roy.Whittle.com 
***********************************************************************/

var FadeInStep 	= 18;
var FadeOutStep = 18;

document.write('<STYLE TYPE="text/css">.imgFader{ position:relative; filter:alpha(opacity=0); -moz-opacity:0.0 }</STYLE>');

if(!window.JSFX)
	JSFX=new Object();

JSFX.RolloverObjects=new Array();

JSFX.Rollover = function(name, img)
{
	JSFX.RolloverObjects[name]=new Image();
	JSFX.RolloverObjects[name].img_src = img;	
	if(!JSFX.Rollover.postLoad)
		JSFX.RolloverObjects[name].src = img;
}
JSFX.Rollover.postLoad = false;
JSFX.Rollover.loadImages = function()
{
	var i;
	for(i in JSFX.RolloverObjects)
	{
		r=JSFX.RolloverObjects[i];
		r.src=r.img_src;
	}
}
JSFX.Rollover.error = function(n)
{
		alert("JSFX.Rollover - An Error has been detected\n"
			+ "----------------------------------\n"
			+ "You must define a JSFX.Rollover in your document\n"
			+ "JSFX.Rollover(\""+n+"\",\"your_on_img.gif\")\n"
			+ "(check the spelling of your JSFX.Rollovers)");
}
/*******************************************************************
*
* Function    : getImg
*
* Description : In Netscape 4 images could be in layers so we might
*		    have to recurse the layers to find the image
*
*****************************************************************/
JSFX.getImg = function(n, d) 
{
	var img = d.images[n];
	if(!img && d.layers)  
		for(var i=0 ; !img && i<d.layers.length ; i++)
			img=JSFX.getImg(n,d.layers[i].document);
	return img;
}
/*******************************************************************
*
* Function    : findImg
*
* Description : gets the image from the document and reports an
*		    error if it cannot find it.
*
*****************************************************************/
JSFX.findImg = function(n, d) 
{
	var img = JSFX.getImg(n, d);

	/*** Stop emails because the image was named incorrectly ***/
	if(!img)
	{
		alert("JSFX.findImg - An Error has been detected\n"
			+ "----------------------------------\n"
			+ "You must define an image in your document\n"
			+ "<IMG SRC=\"your_image.ext\" NAME=\""+n+"\">\n"
			+ "(check the NAME= attribute of your images)");

		return(new Image());
	}
	return img;
}

JSFX.ImageFadeRunning=false;
JSFX.ImageFadeInterval=30;

/*******************************************************************
*
* Function    : imgFadeIn
*
* Description : This function is based on the turn_on() function
*		      of animate2.js (animated rollovers from www.roy.whittle.com).
*		      Each image object is given a state. 
*			OnMouseOver the state is switched depending on the current state.
*			Current state -> Switch to
*			===========================
*			null		->	OFF.
*			OFF		->	FADE_IN
*			FADE_OUT	->	FADE_IN
*			FADE_OUT	->	FADE_OUT_IN (if the new image is different)
*			FADE_IN_OUT->	FADE_IN (if the image is the same)
*****************************************************************/
JSFX.imgFadeIn = function(img, imgSrc)
{
	if(img) 
	{
		if(img.state == null) 
		{
			img.state = "OFF";
			img.index = 0;
			img.next_on    = null;
		}

		if(img.state == "OFF")
		{
			/*** Vers 1.7 only load the ON image once ever ***/
			if(img.src.indexOf(imgSrc) == -1)
				img.src=imgSrc;

			img.currSrc = imgSrc;
			img.state = "FADE_IN";
			JSFX.startFading();
		}
		else if( img.state == "FADE_IN_OUT"
			|| img.state == "FADE_OUT_IN"
			|| img.state == "FADE_OUT")
		{
			if(img.currSrc == imgSrc)
				img.state = "FADE_IN";
			else
			{

				img.next_on = imgSrc;
				img.state="FADE_OUT_IN";
			}
		}
	}
}
/*******************************************************************
*
* Function    : imgFadeOut
*
* Description : This function is based on the turn_off function
*		      of animate2.js (animated rollovers from www.roy.whittle.com).
*		      Each image object is given a state. 
*			OnMouseOut the state is switched depending on the current state.
*			Current state -> Switch to
*			===========================
*			ON		->	FADE_OUT.
*			FADE_IN	->	FADE_IN_OUT.
*			FADE_OUT_IN	->	FADE_IN. (after swapping to the next image)
*****************************************************************/
JSFX.imgFadeOut = function(img)
{
	if(img)
	{
		if(img.state=="ON")
		{
			img.state="FADE_OUT";
			JSFX.startFading();
		}
		else if(img.state == "FADE_IN")
		{
			img.state="FADE_IN_OUT";
		}
		else if(img.state=="FADE_OUT_IN")
		{
			img.next_on == null;
			img.state = "FADE_OUT";
		}
	}
}
/*******************************************************************
*
* Function    : startFading
*
* Description : This function is based on the start_animating() function
*	        	of animate2.js (animated rollovers from www.roy.whittle.com).
*			If the timer is not currently running, it is started.
*			Only 1 timer is used for all objects
*****************************************************************/
JSFX.startFading = function()
{
	if(!JSFX.ImageFadeRunning)
		JSFX.ImageFadeAnimation();
}

/*******************************************************************
*
* Function    : ImageFadeAnimation
*
* Description : This function is based on the Animate function
*		    of animate2.js (animated rollovers from www.roy.whittle.com).
*		    Each image object has a state. This function
*		    modifies each object and (possibly) changes its state.
*****************************************************************/
JSFX.ImageFadeAnimation = function()
{
	JSFX.ImageFadeRunning = false;
	for(i=0 ; i<document.images.length ; i++)
	{
		var img = document.images[i];
		if(img.state)
		{
			if(img.state == "FADE_IN")
			{
				img.index+=FadeInStep;

				if(img.index > 100)
					img.index = 100;

				if(img.filters)
					img.filters.alpha.opacity = img.index;
				else
					img.style.MozOpacity = img.index/101;

				if(img.index == 100)
					img.state="ON";
				else
					JSFX.ImageFadeRunning = true;
			}
			else if(img.state == "FADE_IN_OUT")
			{
				img.index+=FadeInStep;
				if(img.index > 100)
					img.index = 100;

				if(img.filters)
					img.filters.alpha.opacity = img.index;
				else 
					img.style.MozOpacity = img.index/101;

	
				if(img.index == 100)
					img.state="FADE_OUT";

				JSFX.ImageFadeRunning = true;
			}
			else if(img.state == "FADE_OUT")
			{
				img.index-=FadeOutStep;
				if(img.index < 0)
					img.index = 0;

				if(img.filters)
					img.filters.alpha.opacity = img.index;
				else
					img.style.MozOpacity = img.index/101;


				if(img.index == 0)
					img.state="OFF";
				else
					JSFX.ImageFadeRunning = true;
			}
			else if(img.state == "FADE_OUT_IN")
			{
				img.index-=FadeOutStep;
				if(img.index < 0)
					img.index = 0;

				if(img.filters)
					img.filters.alpha.opacity = img.index;
				else
					img.style.MozOpacity = img.index/101;

				if(img.index == 0)
				{
					img.src = img.next_on;
					img.currSrc = img.next_on;
					img.state="FADE_IN";
				}
				JSFX.ImageFadeRunning = true;
			}
		}
	}
	/*** Check to see if we need to animate any more frames. ***/
	if(JSFX.ImageFadeRunning)
		setTimeout("JSFX.ImageFadeAnimation()", JSFX.ImageFadeInterval);
}
/*******************************************************************
*
* Function    : hasOpacity
*
* Description : Tests if the browser allows Opacity
*
*****************************************************************/
JSFX.hasOpacity = function(obj)
{
	if(document.layers)
		return false;

	if(window.opera)
		return false;

	if(navigator.userAgent.toLowerCase().indexOf("mac") != -1)
		return false;

	return true;
}
/*******************************************************************
*
* Function    : fadeIn /fadeOut
*
* Description : Detects browser that can do opacity and fades the images
*		    For browsers that do not support opacity it just does an image swap.
*		    (I only know about NS4 but maybe IE on a Mac also ?)
*		    For these functions to work you need to name the image
*			e.g. for an image named "home" you need
*			<IMG .... NAME="home">
*		    and you need 2 images, the on and the off image
*****************************************************************/
JSFX.fadeIn = function(imgName, rollName)
{
	if(rollName == null)
		rollName=imgName;

	/*** Stop emails because the rollover was named incorrectly ***/
	if(!JSFX.RolloverObjects[rollName])
	{
		JSFX.Rollover.error(rollName);
		return;
	}

	var img = JSFX.findImg(imgName, document);
	if(JSFX.hasOpacity(img))
		JSFX.imgFadeIn(img, JSFX.RolloverObjects[rollName].img_src);
	else
	{
		if(img.offSrc==null)
			img.offSrc=img.src;
		img.src=JSFX.RolloverObjects[rollName].img_src;
	}
}
JSFX.fadeOut = function(imgName)
{
	var img = JSFX.findImg(imgName, document);
	if(JSFX.hasOpacity(img))
		JSFX.imgFadeOut(img);
	else
		img.src=img.offSrc;
}
/*******************************************************************
*
* Function    : imgOn /imgOff
*
* Description : Included these functions so you can mix simple and
*		    fading rollovers without having to include 2 ".js" files
*
*****************************************************************/
JSFX.imgOn = function(imgName, rollName)
{
	if(rollName == null)
		rollName=imgName;

	/*** Stop emails because the rollover was named incorrectly ***/
	if(!JSFX.RolloverObjects[rollName])
	{
		JSFX.Rollover.error(rollName);
		return;
	}
	var img = JSFX.findImg(imgName,document);
	if(img.offSrc==null)
		img.offSrc=img.src;
	img.src=JSFX.RolloverObjects[rollName].img_src;
}
JSFX.imgOff = function(imgName)
{
	var img = JSFX.findImg(imgName,document);
	img.src=img.offSrc;
}
