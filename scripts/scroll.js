/***********************************************
* Cross browser Marquee II- © Dynamic Drive (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit http://www.dynamicdrive.com/ for this script and 100s more.
***********************************************/

var delayb4scroll=2000 //Specify initial delay before marquee starts to scroll on page (2000=2 seconds)
var marqueespeed=2 //Specify marquee scroll speed (larger is faster 1-10)
var pauseit=1 //Pause marquee onMousever (0=no. 1=yes)?

////NO NEED TO EDIT BELOW THIS LINE////////////

var copyspeed=marqueespeed
var pausespeed=(pauseit==0)? copyspeed: 0
var actualheight=''

function scrollmarquee(){
if (parseInt(cross_marquee.style.right)<(actualwidth*(-1)+4))
cross_marquee.style.right=(parseInt(cross_marquee2.style.right)+actualwidth+4)+"px"
if (parseInt(cross_marquee2.style.right)<(actualwidth*(-1)+4))
cross_marquee2.style.right=(parseInt(cross_marquee.style.right)+actualwidth+4)+"px"
cross_marquee2.style.right=parseInt(cross_marquee2.style.right)-copyspeed+"px"
cross_marquee.style.right=parseInt(cross_marquee.style.right)-copyspeed+"px"
}

function initializemarquee(){
cross_marquee=document.getElementById("vmarquee")
cross_marquee2=document.getElementById("vmarquee2")
cross_marquee.style.right=0
marqueewidth=document.getElementById("marqueecontainer").offsetWidth
actualwidth=cross_marquee.firstChild.offsetWidth
cross_marquee2.style.right=actualwidth+4+'px'
cross_marquee2.innerHTML=cross_marquee.innerHTML
setTimeout('righttime=setInterval("scrollmarquee()",30)', delayb4scroll)
}

if (window.addEventListener)
window.addEventListener("load", initializemarquee, false)
else if (window.attachEvent)
window.attachEvent("onload", initializemarquee)
else if (document.getElementById)
window.onload=initializemarquee