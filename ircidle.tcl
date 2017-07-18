package require http

#set channel where script should work
set conf(channel) "#09source"
set conf(chan) "#09source"
set conf(site) "09source"
set conf(key)  "VGhlIE1vemlsbGEgZmFtaWx5IGFwcG" 
set conf(site_url) "http://u-232.com/irc_idle.php"

#set the file where greet message is
set gfile "text/greet.txt"

#bind process
bind join - "#09source *" bind_join
bind part - "#09source *" bind_part
bind sign - "#09source *" bind_sign
bind nick - "#09source *" bind_nick

#bind join - "$channel *" bind_join
#bind part - "$channel *" bind_part
#bind sign - "$channel *" bind_sign
#bind nick - "$channel *" bind_nick

proc bind_join {nick host handle chan} {
  set check [user_check $nick]
	if { $check == 1 } {
		irc_idle $nick 1
		#hello $nick
	}
}
proc bind_part {nick host handle chan msg} {
	irc_idle $nick 0
}
proc bind_nick {nick host handle chan newnick} {
  set check [user_check $nick]
	if { $check == 1 } {
		irc_idle $newnick 1
		#hello $nick
	}
}
proc bind_sign {nick host handle chan msg} {
	irc_idle $nick 0
}

proc irc_idle { user irc_idle } {
	global conf
	
  set  token [::http::geturl $conf(site_url)?key=$conf(key)&do=idle&username=$user&ircidle=$irc_idle]
	set result [::http::data $token]
	
	if { $result == 1 && $irc_idle == 1 } {
		putquick "notice $user : <+> Irc bonus enabled"
	} elseif { $result == 1 && $irc_idle == 0 } {
		putquick "PRIVMSG $conf(chan) : \0032<*> Irc bonus disabled for $user\003"
	} else {
		putlog "There was an error while changing irc status($irc_idle) for user $user"
	}
}
proc user_check { user } {
	global db conf 
	
  if { $user == "c00kie" } {
    return 0
  } 
  set   token [::http::geturl $conf(site_url)?key=$conf(key)&do=check&username=$user] 
	set is_user [::http::data $token]

	if { $is_user == 1 } {
		putserv "mode $conf(chan) +v $user" 
	} else {
		putquick "PRIVMSG $user : This is only for $conf(site) members! Use your site username!"
		putserv "kick $conf(chan) $user : Not a $conf(site) member ! Use your site username!"
	}
	return $is_user
}
proc hello { nick } {
	global gfile conf
	
	set h [open "$gfile" "r"]
	set greet [read $h]

	regsub -all "%nick" $greet "$nick" greet
	regsub -all "%chan" $greet "$conf(chan)" greet
	
    foreach line [split $greet "|"] {
        putquick "NOTICE $nick : $line" 
    }
}
putlog "putyn's idle,greet,usercheck scripts loaded!"
