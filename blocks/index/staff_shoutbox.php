<?php
// === Staff shoutbox 09
if ($CURUSER['opt2'] & user_options_2::SHOW_STAFFSHOUT) {
    $commandbutton = $refreshbutton = $smilebutton = $custombutton = $staffsmiliebutton = '';
    if ($CURUSER['class'] >= UC_STAFF) {
        $staffsmiliebutton.= "
		 <span><a class='btn btn-mini' href=\"javascript:PopStaffSmiles('staff_shbox','staff_shbox_text')\"><i class='icon-wrench'></i>&nbsp;{$lang['index_shoutbox_ssmilies']}</a></span>";
    }
    if (get_smile() != '0') $custombutton.= "
		<span><a class='btn btn-mini' href=\"javascript:PopCustomSmiles('staff_shbox','staff_shbox_text')\"><i class='icon-barcode'></i>&nbsp;{$lang['index_shoutbox_csmilies']}</a></span>";
    if ($CURUSER['class'] >= UC_STAFF) {
        $commandbutton = "
		<span><a class='btn btn-mini' href=\"javascript:popUp('shoutbox_commands.php')\"><i class='icon-cog'></i>&nbsp;{$lang['index_shoutbox_commands']}</a></span>\n";
    }
    $refreshbutton = "
	<span><a class='btn btn-mini' href='staff_shoutbox.php' target='staff_shoutbox'><i class='icon-refresh'></i>&nbsp;{$lang['index_shoutbox_refresh']}</a></span>\n";
    $smilebutton = "
	<span><a class='btn btn-mini' href=\"javascript:PopMoreSmiles('staff_shbox','staff_shbox_text')\"><i class='icon-plus-sign'></i>&nbsp;{$lang['index_shoutbox_smilies']}</a></span>\n";
    $HTMLOUT.= "<form action='staff_shoutbox.php' method='get' target='staff_shoutbox' name='staff_shbox' onsubmit='staff_mysubmit()'>
   <fieldset class='header'><legend>{$lang['index_staff_shoutbox']}</legend>
   <span class='shouthis'>";
    if ($CURUSER['class'] >= UC_STAFF) {
        $HTMLOUT.= "<a class='pull-right' href='{$INSTALLER09['baseurl']}/staffpanel.php?tool=staff_shistory'><b><i class='icon-folder-open'></i>&nbsp;{$lang['index_shoutbox_history']}</b></a>";
    }
    $HTMLOUT.= "</span>
   
   <div class='container-fluid'>
   <iframe src='{$INSTALLER09['baseurl']}/staff_shoutbox.php' width='100%' height='200' name='staff_shoutbox' frameborder='0' marginwidth='0' marginheight='0'></iframe>
   <br />
   <div align='center'>
   <b>{$lang['index_shoutbox_shout']}</b>
   <input type='text' maxlength='680' name='staff_shbox_text' class='input-xxlarge' />
   <input class='btn btn-small' type='submit' value='{$lang['index_shoutbox_send']}' />
   <input type='hidden' name='staff_sent' value='yes' />
   <br /> 
   <a href=\"javascript:SmileIT(':-)','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/smile1.gif' alt='Smile' title='Smile' /></a> 
   <a href=\"javascript:SmileIT(':smile:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/smile2.gif' alt='Smiling' title='Smiling' /></a> 
   <a href=\"javascript:SmileIT(':-D','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/grin.gif' alt='Grin' title='Grin' /></a> 
   <a href=\"javascript:SmileIT(':lol:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/laugh.gif' alt='Laughing' title='Laughing' /></a> 
   <a href=\"javascript:SmileIT(':w00t:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='W00t' title='W00t' /></a> 
   <a href=\"javascript:SmileIT(':blum:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/blum.gif' alt='Rasp' title='Rasp' /></a> 
   <a href=\"javascript:SmileIT(';-)','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/wink.gif' alt='Wink' title='Wink' /></a> 
   <a href=\"javascript:SmileIT(':devil:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/devil.gif' alt='Devil' title='Devil' /></a> 
   <a href=\"javascript:SmileIT(':yawn:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/yawn.gif' alt='Yawn' title='Yawn' /></a> 
   <a href=\"javascript:SmileIT(':-/','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/confused.gif' alt='Confused' title='Confused' /></a> 
   <a href=\"javascript:SmileIT(':o)','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/clown.gif' alt='Clown' title='Clown' /></a> 
   <a href=\"javascript:SmileIT(':innocent:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/innocent.gif' alt='Innocent' title='innocent' /></a> 
   <a href=\"javascript:SmileIT(':whistle:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/whistle.gif' alt='Whistle' title='Whistle' /></a> 
   <a href=\"javascript:SmileIT(':unsure:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/unsure.gif' alt='Unsure' title='Unsure' /></a> 
   <a href=\"javascript:SmileIT(':blush:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/blush.gif' alt='Blush' title='Blush' /></a> 
   <a href=\"javascript:SmileIT(':hmm:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/hmm.gif' alt='Hmm' title='Hmm' /></a> 
   <a href=\"javascript:SmileIT(':hmmm:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/hmmm.gif' alt='Hmmm' title='Hmmm' /></a> 
   <a href=\"javascript:SmileIT(':huh:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/huh.gif' alt='Huh' title='Huh' /></a> 
   <a href=\"javascript:SmileIT(':look:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/look.gif' alt='Look' title='Look' /></a> 
   <a href=\"javascript:SmileIT(':rolleyes:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/rolleyes.gif' alt='Roll Eyes' title='Roll Eyes' /></a> 
   <a href=\"javascript:SmileIT(':kiss:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/kiss.gif' alt='Kiss' title='Kiss' /></a> 
   <a href=\"javascript:SmileIT(':blink:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/blink.gif' alt='Blink' title='Blink' /></a> 
   <a href=\"javascript:SmileIT(':baby:','staff_shbox','staff_shbox_text')\"><img src='{$INSTALLER09['pic_base_url']}smilies/baby.gif' alt='Baby' title='Baby' /></a>
   <div class='shoutextra'><a class='btn btn-mini' href='{$INSTALLER09['baseurl']}/staff_shoutbox.php?show_staffshout=1&amp;show_staff=no'><i class='icon-remove-sign'></i>&nbsp;[&nbsp;{$lang['index_shoutbox_close']}&nbsp;]</a>
   {$staffsmiliebutton}{$smilebutton}{$custombutton}{$refreshbutton}{$commandbutton}</div>
   </div>
   </div>
   </fieldset></form><hr />\n";
}
if (!($CURUSER['opt2'] & user_options_2::SHOW_STAFFSHOUT)) {
    $HTMLOUT.= "<fieldset class='header'><legend><b>{$lang['index_staff_shoutbox']}&nbsp;</b></legend><div class='container'><a class='btn btn-mini' href='{$INSTALLER09['baseurl']}/staff_shoutbox.php?show_staffshout=1&amp;show_staff=yes'><i class='icon-ok'></i>[&nbsp;{$lang['index_shoutbox_open']}&nbsp;]</a></div></fieldset><hr />";
}
//==end 09 Staff shoutbox
// End Class
// End File
