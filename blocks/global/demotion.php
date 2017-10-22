<?php
if ($CURUSER['override_class'] != 255 && $CURUSER) {
    $htmlout .= "
    <li>
        <a href='./restoreclass.php'>
            <b class='btn btn-warning btn-small dt-tooltipper-small' data-tooltip-content='#demotion_tooltip'>
                {$lang['gl_temp_demotion']}
            </b>
            <div class='tooltip_templates'>
                <span id='demotion_tooltip'>
                    <em>{$lang['gl_temp_demotion1']}</em><br>
                    {$lang['gl_temp_demotion2']}
                </span>
            </div>
        </a>
    </li>";
}
