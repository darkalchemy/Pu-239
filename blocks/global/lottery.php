<?php
if ($CURUSER) {
    if (($lottery_info = $mc1->get_value('lottery_info_')) === false) {
        $res = sql_query('SELECT * FROM lottery_info') or sqlerr(__FILE__, __LINE__);
        while ($ac = mysqli_fetch_assoc($res)) {
            $lottery_info[$ac['name']] = $ac['value'];
        }
        $mc1->cache_value('lottery_info_', $lottery_info, 86400);
    }

    if ($lottery_info['enable']) {
        $htmlout .= "
    <li>
        <a href='./lottery.php'>
            <b class='btn btn-success btn-small dt-tooltipper-small' data-tooltip-content='#lottery_tooltip'>
                Lottery in Progress
            </b>
            <div class='tooltip_templates'>
                <span id='lottery_tooltip'>
                    <div>
                        <div class='size_4 text-center text-lime bottom10'>Lottery Info</div>
                        <div class='answers-container'>
                            <span>Started at: </span><span>" . get_date($lottery_info['start_date'], 'LONG') . "</span>
                        </div>
                        <div class='answers-container'>
                            <span>Ends at:&#160;&#160;&#160;&#160;&#160;&#160;</span><span>" . get_date($lottery_info['end_date'], 'LONG') . "</span>
                        </div>
                        <div class='answers-container'>
                            <span>Remaining: </span><span>" . mkprettytime($lottery_info['end_date'] - TIME_NOW) . "</span>
                        </div>
                    </div>
                </span>
            </div>
        </a>
    </li>";
    }
}
