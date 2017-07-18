#staff.tcl
# ultimate tbdev eggdrop commands script by pdq 02-10-09

## REQUIRE PACKAGES  ##
package require http

## INSTRUCTIONS ##
#in your eggdrop.conf make sure settings match like below or uncomment these 4 lines
#unbind msg - hello *msg:hello
#bind msg - gimmesomelove *msg:hello
#set learn-users 1
#set default-flags "h"

## SETTINGS ##

set sitename "09Source"
set siteurl "http://u-232.com/ircs.php"
set botpass "adlsadladadll"
set bothash  "adlsadladadll"

## BINDS ##
#comment binds not in use

# master commands (master +m)
bind pub m|m !deluser del_user
bind pub m|m !chattr chattr_global
bind pub m|m !delhost del_host
bind pub m|m !setusername setusername
bind pub m|m !addbonus addbonus
bind pub m|m !addfreeslots addfreeslots
bind pub m|m !addreputation addreputation
bind pub m|m !addinvites addinvites
bind pub m|m !rembonus rembonus
bind pub m|m !remfreeslots remfreeslots
bind pub m|m !remreputation remreputation
bind pub m|m !reminvites reminvites

# staff commands (op +o)
bind pub o|o !addhost add_host
bind pub o|o !addsupport addsupport
bind pub o|o !connectable connectable
bind pub o|o !online online
bind pub o|o !torrents torrents
bind pub o|o !noseeds noseeds
bind pub o|o !includedead includedead
bind pub o|o !onlydead onlydead
bind pub o|o !enabled enabled
bind pub o|o !downloadpos downloadpos
bind pub o|o !uploadpos uploadpos
bind pub o|o !forum_post forum_post
bind pub o|o !chatpost chatpost
bind pub o|o !sendpmpos sendpmpos
bind pub o|o !avatarpos avatarpos
bind pub o|o !invite_rights invite_rights
bind pub o|o !rules ab_rules
bind pub o|o !topirc topirc

# user commands (users +h)
bind pub h|h !user sitestats
bind pub h|h !bonus bonus
bind pub h|h !givebonus givebonus
bind pub h|h !giveinvites giveinvites
bind pub h|h !givefreeslots givefreeslots
bind pub h|h !givereputation givereputation
bind pub h|h !flushtorrents flushtorrents
bind pub h|h !ircbonus ircbonus
bind pub h|h !irctotal irctotal

## end of settings do not touch below ##
## bind msg - signmeup signmeup
## proc signmeup {nick host text} 
## in progress

proc ab_rules {nick host hand chan text} {
  global sitename
  set who [lindex $text 0]
  if {$who == ""} { 

    putmsg $chan "This channel is for $sitename members only. There is NO Cursing, Harrassment, Abusing the bot, Flooding,Clones, Advertising, Impersonating.  Violation of this policy may result in a kick, and/or ban from channel and site."
    return 1
  }
  putmsg $who "This channel is for $sitename members only. There is NO Cursing, Harrassment, Abusing the bot, Flooding, Clones, Advertising, Impersonating.  Violation of this policy may result in a kick, and/or ban from channel and site."
}

proc del_user {n u h c a} {
  global botnick
  if {[lindex $a 0] == ""} {
        putquick "NOTICE $n : !deluser <nick>"
      return 0
  } 
  set newop [lindex $a 0]
  set newhost [maskhost [getchanhost $newop $c]]

  if {[validuser $newop] == 1} {
     if {[lsearch [getuser $newop hosts] $newhost] == "-1"} {
       putserv "NOTICE $n :$newop exists in my database.Deleting The Requested User From Userlist"
         deluser $newop
       return 1
     } {
     putserv "NOTICE $n :$newop is in my database.Deleting Requested User From Userlist."
     deluser $newop
     return 1 }
  }  
  deluser $newop
  save
}

proc chattr_global {n u h c a} {
  global botnick
  set newflags [lindex $a 1]
  if {[lindex $a 0] == ""} {
        putquick "NOTICE $n : !chattr <nick> <flags>"
      return 0
  } 
  if {[lindex $a 1] == ""} {
        putquick "NOTICE $n : !chattr <nick> <flags>"
      return 0
  }
  set newop [lindex $a 0]
  set newhost [maskhost [getchanhost $newop $c]]

  if {[validuser $newop] == 1} {
     if {[lsearch [getuser $newop hosts] $newhost] == "-1"} {
       putserv "NOTICE $n :$newop exists in my database. Upgrading flags of $newop"
       chattr $newop -|$newflags
       return 1
     }
  }  
  
  if {[chattr $newop $newflags] == "*"} {
        putserv "NOTICE $n :Error Upgrading flags to $newop.Because $newop don't exist in my database."
        return 1
  }
}

proc add_host {n u h c a} {
set existuser [lindex $a 0]
set userhost [lindex $a 1]
  if {$existuser == ""} {
        putquick "NOTICE $n : !addhost <nick> <host>"
        return 0
} elseif {$userhost == ""} {
        putquick "NOTICE $n : !addhost <nick> <host>"
        return 0
}

  if {[validuser $existuser] == 1} {
        setuser $existuser HOSTS $userhost
        return 1
  }
}

proc del_host {n u h c a} {
set existuser [lindex $a 0]
set userhost [lindex $a 1]
  if {$existuser == ""} {
        putquick "NOTICE $n : !delhost <nick> <host>"
        return 0
} elseif {$userhost == ""} {
        putquick "NOTICE $n : !delhost <nick> <host>"
        return 0
}
  if {[validuser $existuser] == 1} {
        delhost $existuser $userhost
        return 1
  }
}

proc setusername {nick uhost hand chan text} {
      global siteurl botpass bothash
      set mod $nick
      set whom [lindex $text 0]
      set newname [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?setusername&newname=$newname&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !setusername <oldnick> <newnick>"
      }
    }

proc sitestats {nick uhost hand chan text} {
    global siteurl botpass bothash
    set user [lindex [split $text] 0]
    set line "blue"
if {[isop $nick $chan] == 1} {
         if {$text == ""} {
        set user $nick
    }
    } else {
      set user $nick
    }
    if {$user!=""} {
        set data [::http::geturl $siteurl?func=stats&search=$user&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
    }
}

proc flushtorrents {nick uhost hand chan text} {
    global siteurl botpass bothash
    set user [lindex [split $text] 0]
    set line "blue"
if {[isop $nick $chan] == 1} {
         if {$text == ""} {
        set user $nick
    }
    } else {
      set user $nick
    }
    if {$user!=""} {
        set data [::http::geturl $siteurl?func=flushtorrents&search=$user&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
    }
}

proc bonus {nick uhost hand chan text} {
    global siteurl botpass bothash
    set user [lindex [split $text] 0]
    set line "blue"
if {[isop $nick $chan] == 1} {
         if {$text == ""} {
        set user $nick
    }
    } else {
      set user $nick
    }
    if {$user!=""} {
        set data [::http::geturl $siteurl?func=check&search=$user&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
    }
}

proc connectable {nick uhost hand chan text} {
    global siteurl botpass bothash
    set user [lindex [split $text] 0]
    set line "blue"
  if {[isop $nick $chan] == 1} {
         if {$text == ""} {
        set user $nick
    }
    } else {
      set user $nick
    }
    if {$user!=""} {
        set data [::http::geturl $siteurl?search=$user&func=connectable&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
    }
}

proc online {nick uhost hand chan text} {
    global siteurl botpass bothash
    set user [lindex [split $text] 0]
    set line "blue"
   if {[isop $nick $chan] == 1} {
         if {$text == ""} {
        set user $nick
    }
    } else {
      set user $nick
    }
    if {$user!=""} {
        set data [::http::geturl $siteurl?search=$user&func=online&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
    }
}

proc torrents {nick uhost hand chan text} {
    global siteurl botpass bothash
    set user [lindex [split $text] 0]
    set line "blue"
    if {$text == ""} {
        set user $nick
    }
    if {$user!=""} {
        set data [::http::geturl $siteurl?torrents&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
    }
}

proc noseeds {nick uhost hand chan text} {
    global siteurl botpass bothash
    set user [lindex [split $text] 0]
    set line "blue"
    if {$text == ""} {
        set user $nick
    }
    if {$user!=""} {
        set data [::http::geturl $siteurl?noseeds&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
    }
}

proc includedead {nick uhost hand chan text} {
    global siteurl botpass bothash
    set user [lindex [split $text] 0]
    set line "blue"
    if {$text == ""} {
        set user $nick
    }
    if {$user!=""} {
        set data [::http::geturl $siteurl?includedead&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
    }
}

proc onlydead {nick uhost hand chan text} {
    global siteurl botpass bothash
    set user [lindex [split $text] 0]
    set line "blue"
    if {$text == ""} {
        set user $nick
    }
    if {$user!=""} {
        set data [::http::geturl $siteurl?onlydead&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
    }
}

proc uploadpos {nick uhost hand chan text} {
    global siteurl botpass bothash
set mod $nick
set whom [lindex $text 0]
set toggle [lrange $text 1 end]
if {$whom!=""} {
set data [::http::geturl $siteurl?uploadpos&toggle=$toggle&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
foreach line [split [::http::data $data] \n] {
    if {$line != ""} {
        putquick "PRIVMSG $chan :\0032$line\003"
    }
}
::http::cleanup $data
} else {
putquick "PRIVMSG $chan : format is: !uploadpos <nick> <yes>/<no>"
}
}

proc forum_post {nick uhost hand chan text} {
    global siteurl botpass bothash
set mod $nick
set whom [lindex $text 0]
set toggle [lrange $text 1 end]
if {$whom!=""} {
set data [::http::geturl $siteurl?forum_post&toggle=$toggle&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
foreach line [split [::http::data $data] \n] {
    if {$line != ""} {
        putquick "PRIVMSG $chan :\0032$line\003"
    }
}
::http::cleanup $data
} else {
putquick "PRIVMSG $chan : format is: !forum_post <nick> <yes>/<no>"
}
}    

proc chatpost {nick uhost hand chan text} {
    global siteurl botpass bothash
set mod $nick
set whom [lindex $text 0]
set toggle [lrange $text 1 end]
if {$whom!=""} {
set data [::http::geturl $siteurl?chatpost&toggle=$toggle&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
foreach line [split [::http::data $data] \n] {
    if {$line != ""} {
        putquick "PRIVMSG $chan :\0032$line\003"
    }
}
::http::cleanup $data
} else {
putquick "PRIVMSG $chan : format is: !chatpost <nick> <yes>/<no>"
}
}    

proc sendpmpos {nick uhost hand chan text} {
    global siteurl botpass bothash
set mod $nick
set whom [lindex $text 0]
set toggle [lrange $text 1 end]
if {$whom!=""} {
set data [::http::geturl $siteurl?sendpmpos&toggle=$toggle&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
foreach line [split [::http::data $data] \n] {
    if {$line != ""} {
        putquick "PRIVMSG $chan :\0032$line\003"
    }
}
::http::cleanup $data
} else {
putquick "PRIVMSG $chan : format is: !sendpmpos <nick> <yes>/<no>"
}
}    

proc enabled {nick uhost hand chan text} {
    global siteurl botpass bothash
set mod $nick
set whom [lindex $text 0]
set toggle [lrange $text 1 end]
if {$whom!=""} {
set data [::http::geturl $siteurl?enabled&toggle=$toggle&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
foreach line [split [::http::data $data] \n] {
    if {$line != ""} {
        putquick "PRIVMSG $chan :\0032$line\003"
    }
}
::http::cleanup $data
} else {
putquick "PRIVMSG $chan : format is: !enabled <nick> <yes>/<no>"
}
}    

proc downloadpos {nick uhost hand chan text} {
    global siteurl botpass bothash
set mod $nick
set whom [lindex $text 0]
set toggle [lrange $text 1 end]
if {$whom!=""} {
set data [::http::geturl $siteurl?downloadpos&toggle=$toggle&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
foreach line [split [::http::data $data] \n] {
    if {$line != ""} {
        putquick "PRIVMSG $chan :\0032$line\003"
    }
}
::http::cleanup $data
} else {
putquick "PRIVMSG $chan : format is: !downloadpos <nick> <yes>/<no>"
}
}    

proc avatarpos {nick uhost hand chan text} {
    global siteurl botpass bothash
set mod $nick
set whom [lindex $text 0]
set toggle [lrange $text 1 end]
if {$whom!=""} {
set data [::http::geturl $siteurl?avatarpos&toggle=$toggle&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
foreach line [split [::http::data $data] \n] {
    if {$line != ""} {
        putquick "PRIVMSG $chan :\0032$line\003"
    }
}
::http::cleanup $data
} else {
putquick "PRIVMSG $chan : format is: !avatarpos <nick> <yes>/<no>"
}
}    

proc invite_rights {nick uhost hand chan text} {
    global siteurl botpass bothash
set mod $nick
set whom [lindex $text 0]
set toggle [lrange $text 1 end]
if {$whom!=""} {
set data [::http::geturl $siteurl?invite_rights&toggle=$toggle&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
foreach line [split [::http::data $data] \n] {
    if {$line != ""} {
        putquick "PRIVMSG $chan :\0032$line\003"
    }
}
::http::cleanup $data
} else {
putquick "PRIVMSG $chan : format is: !invite_rights <nick> <yes>/<no>"
}
}

proc addsupport {nick uhost hand chan text} {
    global siteurl botpass bothash
set mod $nick
set whom [lindex $text 0]
set supportfor [lrange $text 1 end]
if {$whom!=""} {
set data [::http::geturl $siteurl?addsupport&supportfor=$supportfor&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
foreach line [split [::http::data $data] \n] {
    if {$line != ""} {
        putquick "PRIVMSG $chan :\0032$line\003"
    }
}
::http::cleanup $data
} else {
putquick "PRIVMSG $chan : format is: !addsupport <nick> <support for>"
}
}

proc addbonus {nick uhost hand chan text} {
    global siteurl botpass bothash
      set mod $nick
      set whom [lindex $text 0]
      set amount [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?func=add&bonus&amount=$amount&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !addbonus <nick> <amount>"
      }
    }

proc addfreeslots {nick uhost hand chan text} {
    global siteurl botpass bothash
      set mod $nick
      set whom [lindex $text 0]
      set amount [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?func=add&freeslots&amount=$amount&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !addfreeslots <nick> <amount>"
      }
    }
    
proc addreputation {nick uhost hand chan text} {
    global siteurl botpass bothash
      set mod $nick
      set whom [lindex $text 0]
      set amount [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?func=add&reputation&amount=$amount&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !addreputation <nick> <amount>"
      }
    }

proc addinvites {nick uhost hand chan text} {
    global siteurl botpass bothash
      set mod $nick
      set whom [lindex $text 0]
      set amount [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?func=add&invites&amount=$amount&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !addinvites <nick> <amount>"
      }
    }

proc rembonus {nick uhost hand chan text} {
    global siteurl botpass bothash
      set mod $nick
      set whom [lindex $text 0]
      set amount [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?func=rem&bonus&amount=$amount&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !rembonus <nick> <amount>"
      }
    }

proc remfreeslots {nick uhost hand chan text} {
    global siteurl botpass bothash
      set mod $nick
      set whom [lindex $text 0]
      set amount [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?func=rem&freeslots&amount=$amount&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !remfreeslots <nick> <amount>"
      }
    }
    
proc remreputation {nick uhost hand chan text} {
    global siteurl botpass bothash
      set mod $nick
      set whom [lindex $text 0]
      set amount [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?func=rem&reputation&amount=$amount&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !remreputation <nick> <amount>"
      }
    }

proc reminvites {nick uhost hand chan text} {
    global siteurl botpass bothash
      set mod $nick
      set whom [lindex $text 0]
      set amount [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?func=rem&invites&amount=$amount&whom=$whom&mod=$mod&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !reminvites <nick> <amount>"
      }
    }

proc givebonus {nick uhost hand chan text} {
    global siteurl botpass bothash
      set whom [lindex $text 0]
      set amount [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?func=give&bonus&amount=$amount&whom=$whom&me=$nick&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !givebonus <nick> <amount>"
      }
    }

proc giveinvites {nick uhost hand chan text} {
    global siteurl botpass bothash
      set whom [lindex $text 0]
      set amount [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?func=give&invites&amount=$amount&whom=$whom&me=$nick&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !giveinvites <nick> <amount>"
      }
    }

proc givefreeslots {nick uhost hand chan text} {
    global siteurl botpass bothash
      set whom [lindex $text 0]
      set amount [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?func=give&freeslots&amount=$amount&whom=$whom&me=$nick&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !givefreeslots <nick> <amount>"
      }
    }
    
    
proc givereputation {nick uhost hand chan text} {
    global siteurl botpass bothash
      set whom [lindex $text 0]
      set amount [lrange $text 1 end]
      if {$whom!=""} {
 set data [::http::geturl $siteurl?func=give&reputation&amount=$amount&whom=$whom&me=$nick&pass=$botpass&hash=$bothash]
        foreach line [split [::http::data $data] \n] {
            if {$line != ""} {
                putquick "PRIVMSG $chan :\0032$line\003"
            }
        }
    ::http::cleanup $data
      } else {
  putquick "PRIVMSG $chan : format is: !givereputation <nick> <amount>"
      }
    }
    
 proc ircbonus {nick uhost hand chan text} {
        global siteurl botpass bothash
        set user [lindex [split $text] 0]
        set line "blue"
if {[isop $nick $chan] == 1} {
                 if {$text == ""} {
                set user $nick
        }
        } else {
          set user $nick
        }
        if {$user!=""} {
                set data [::http::geturl $siteurl?func=ircbonus&search=$user&pass=$botpass&hash=$bothash]
                foreach line [split [::http::data $data] \n] {
                        if {$line != ""} {
                                putquick "PRIVMSG $chan :\0032$line\003"
                        }
                }
        ::http::cleanup $data
        }
}

proc irctotal {nick uhost hand chan text} {
        global siteurl botpass bothash
        set user [lindex [split $text] 0]
        set line "blue"
if {[isop $nick $chan] == 1} {
                 if {$text == ""} {
                set user $nick
        }
        } else {
          set user $nick
        }
        if {$user!=""} {
                set data [::http::geturl $siteurl?func=irctotal&search=$user&pass=$botpass&hash=$bothash]
                foreach line [split [::http::data $data] \n] {
                        if {$line != ""} {
                                putquick "PRIVMSG $chan :\0032$line\003"
                        }
                }
        ::http::cleanup $data
        }
}

proc topirc {nick uhost hand chan text} {
        global siteurl botpass bothash
        set user [lindex [split $text] 0]
        set line "blue"
        if {$text == ""} {
                set user $nick
        }
        if {$user!=""} {
                set data [::http::geturl $siteurl?topirc&pass=$botpass&hash=$bothash]
                foreach line [split [::http::data $data] \n] {
                        if {$line != ""} {
                                putquick "PRIVMSG $chan :\0032$line\003"
                        }
                }
        ::http::cleanup $data
        }
}   

putlog "pdqs ultimate staff loaded!"
