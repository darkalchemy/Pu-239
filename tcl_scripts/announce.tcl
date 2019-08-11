package require http
package require tls

set conf(port) 35791
set conf(pass) XZ0jMsqZi2va1ENI
set conf(chan) #announce

listen $conf(port) script idle:listen

proc idle:listen {idx} {
    control $idx idle:process
}

proc idle:process {idx args} {
	global conf

	set args [join $args]
    set password [lindex [split $args] 0]
    set message [join [lrange [split $args] 1 end]]

    if {[string match $password $conf(pass)]} {
        putquick "PRIVMSG $conf(chan) : $message"
    }
}

putlog "Module loaded: SSL Announce"
