<?php
if ($site_config['bug_alert'] && $CURUSER['class'] >= UC_STAFF) {
    if (($bugs = $mc1->get_value('bug_mess_')) === false) {
        $res1 = sql_query("SELECT COUNT(id) FROM bugs WHERE status = 'na'");
        list($bugs) = mysqli_fetch_row($res1);
        $mc1->cache_value('bug_mess_', $bugs, $site_config['expires']['alerts']);
    }
    if ($bugs > 0) {
        $htmlout .= "
    <li>
        <a href='{$site_config['baseurl']}/bugs.php?action=bugs'>
            <span class='button tag is-danger dt-tooltipper-small' data-tooltip-content='#bugmessage_tooltip'>
                {$lang['gl_bug_alert']}
            </span>
            <div class='tooltip_templates'>
                <span id='bugmessage_tooltip'>
                    <div class='size_4 has-text-centered has-text-danger has-text-weight-bold bottom10'>
                        {$lang['gl_bug_alert1']}
                    </div>
                    {$lang['gl_bug_alert2']} {$CURUSER['username']}!<br> " . sprintf($lang['gl_bugs'], $bugs[0]) . ($bugs[0] > 1 ? "{$lang['gl_bugss']}" : '') . "!
                 </span>
            </div>
        </a>
    </li>";
    }
}
