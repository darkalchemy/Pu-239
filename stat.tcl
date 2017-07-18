package require http

set url "http://u-232.com/irc_stats.php"
set hash "YXBwemZhbg"
set chan "#09source"
# staff commands (op +o)
bind pub o|o !userinfo userinfo
bind pub o|o !torrentsinfo torrentsinfo
bind pub o|o !irc irc
bind pub o|o !top top
# for users
bind pub  -  !fls fls
bind pub  -  !triggers triggers

proc userinfo {nick uhost hand chan text} {
		set user [getuser $nick $text]
	sendRequest $user "stats" $nick
}
proc torrentsinfo {nick uhost hand chan text} {
		set user [getuser $nick $text]
	sendRequest $user "torrents" $nick
}

proc fls {nick uhost hand chan text} {
	sendRequest $nick "fls" $nick
}
proc irc {nick uhost hand chan text} {
		set user [getuser $nick $text]
	sendRequest $user "irc" $nick
}
proc top {nick uhost hand chan text} {
	set top_type [lindex [split $text] 0]
	if { $top_type == "" } {
		putquick "NOTICE $nick : We have - top uploaders select after upload ammount !top uploaders , top uploaders selected after torrents uploaded !top torrents , top posters selected after number of posts !top posters and top idle !top idle"
	} else {
		sendRequest $nick "top_$top_type" $nick
	}
}
proc triggers { nick uhost hand chan text } {
	set bot [lindex [split $text] 0]
	if { $bot == "" } {
		putquick "PRIVMSG $chan : Triggers for Bert - information about users !userinfo <username>, information about torrents !torrentsinfo <username> , information about irc !irc <username> , First line support !fls, Tops !top"
		putquick "PRIVMSG $chan : Note most of the triggers are only for +o"
	}
}
proc sendRequest { nick do return} {
	global hash url chan
	putquick "NOTICE $return : working ...wait!"
	
	set data [::http::geturl $url?u=$nick&hash=$hash&do=$do]
	foreach line [split [::http::data $data] \n] {
		if { $line != "" } {
			putquick "NOTICE $return : $line "
		}
	}
}

proc getuser { nick nick2 } { 
	set user [lindex [split $nick2] 0]
	if { $user == ""} {
		set user $nick
	}
	return $user
}
##auto triggers 
set time 120
if {[string compare [string index $time 0] "!"] == 0} { set timer [string range $time 1 end] } { set timer [expr $time * 60] }
if {[lsearch -glob [utimers] "* auto_triggers *"] == -1} { utimer $timer auto_triggers }

proc auto_triggers {} {
global chan time timer
	putquick "PRIVMSG $chan : Triggers for Bert - information about users !userinfo <username>, information about torrents !torrentsinfo <username> , information about irc !irc <username> , First line support !fls, Tops !top"
	putquick "PRIVMSG $chan : Note most of the triggers are only for +o"
if {[lsearch -glob [utimers] "* auto_triggers *"] == -1} { utimer $timer auto_triggers }
}
##end auto triggers
putlog "putyn's command script loaded!"
