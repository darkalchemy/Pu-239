<?php

global $fluent, $site_config, $cache;

if (XBT_TRACKER) {
    $htmlout .= "
        <li>
        <a class='tooltip' href='index.php#'><span class='button is-success is-small'>XBT TRACKER</span>
        <span class='custom info alert alert-success'><em>XBT TRACKER</em>
      <br>XBT TRACKER running - No crazyhours, happyhours, freeslots active :-(<br><br></span></a></li>";
} else {
    $fpoints = $dpoints = $hpoints = $freeleech_enabled = $double_upload_enabled = $half_down_enabled = '';
    $scheduled_events = $cache->get('freecontribution_datas_alerts_');
    if ($scheduled_events === false || is_null($scheduled_events)) {
        $scheduled_events = $fluent->from('events')
            ->orderBy('startTime DESC')
            ->limit(3)
            ->fetchAll();
        $cache->set('freecontribution_datas_alerts_', $scheduled_events, 3 * 86400);
    }

    if (is_array($scheduled_events)) {
        foreach ($scheduled_events as $scheduled_event) {
            if (is_array($scheduled_event) && array_key_exists('startTime', $scheduled_event) &&
                array_key_exists('endTime', $scheduled_event)) {
                $startTime = 0;
                $endTime = 0;
                $startTime = $scheduled_event['startTime'];
                $endTime = $scheduled_event['endTime'];
                if (TIME_NOW < $endTime && TIME_NOW > $startTime) {
                    if (array_key_exists('freeleechEnabled', $scheduled_event)) {
                        $freeleechEnabled = $scheduled_event['freeleechEnabled'];
                        if ($scheduled_event['freeleechEnabled']) {
                            $freeleech_start_time = $scheduled_event['startTime'];
                            $freeleech_end_time = $scheduled_event['endTime'];
                            $freeleech_enabled = true;
                        }
                    }
                    if (array_key_exists('duploadEnabled', $scheduled_event)) {
                        $duploadEnabled = $scheduled_event['duploadEnabled'];
                        if ($scheduled_event['duploadEnabled']) {
                            $double_upload_start_time = $scheduled_event['startTime'];
                            $double_upload_end_time = $scheduled_event['endTime'];
                            $double_upload_enabled = true;
                        }
                    }
                    if (array_key_exists('hdownEnabled', $scheduled_event)) {
                        $hdownEnabled = $scheduled_event['hdownEnabled'];
                        if ($scheduled_event['hdownEnabled']) {
                            $half_down_start_time = $scheduled_event['startTime'];
                            $half_down_end_time = $scheduled_event['endTime'];
                            $half_down_enabled = true;
                        }
                    }
                }
            }
        }
    }

    $percent_fl = $cache->get('freeleech_counter_alerts_');
    if ($percent_fl === false || is_null($percent_fl)) {
        $res = $fluent->from('bonus')
            ->select(null)
            ->select('pointspool / points * 100 AS percent')
            ->where('id = 11')
            ->fetch();

        $percent_fl = number_format($res['percent'], 2);
        $cache->set('freeleech_counter_alerts_', $percent_fl, 0);
    }

    switch ($percent_fl) {
        case $percent_fl >= 90:
            $font_color_fl = "<span class='has-text-green'> {$percent_fl}%</span>";
            break;
        case $percent_fl >= 80:
            $font_color_fl = "<span class='has-text-lightgreen'> {$percent_fl}%</span>";
            break;
        case $percent_fl >= 70:
            $font_color_fl = "<span class='has-text-jade'> {$percent_fl}%</span>";
            break;
        case $percent_fl >= 50:
            $font_color_fl = "<span class='has-text-turquoise'> {$percent_fl}%</span>";
            break;
        case $percent_fl >= 40:
            $font_color_fl = "<span class='has-text-lghtblue'> {$percent_fl}%</span>";
            break;
        case $percent_fl >= 30:
            $font_color_fl = "<span class='has-text-gold'> {$percent_fl}%</span>";
            break;
        case $percent_fl >= 20:
            $font_color_fl = "<span class='has-text-oragne'> {$percent_fl}%</span>";
            break;
        case $percent_fl < 20:
            $font_color_fl = "<span class='has-text-red'> {$percent_fl}%</span>";
            break;
    }

    $percent_du = $cache->get('doubleupload_counter_alerts_');
    if ($percent_du === false || is_null($percent_du)) {
        $res = $fluent->from('bonus')
            ->select(null)
            ->select('pointspool / points * 100 AS percent')
            ->where('id = 12')
            ->fetch();

        $percent_du = number_format($res['percent'], 2);
        $cache->set('doubleupload_counter_alerts_', $percent_du, 0);
    }

    switch ($percent_du) {
        case $percent_du >= 90:
            $font_color_du = "<span class='has-text-green'> {$percent_du}%</span>";
            break;
        case $percent_du >= 80:
            $font_color_du = "<span class='has-text-lightgreen'> {$percent_du}%</span>";
            break;
        case $percent_du >= 70:
            $font_color_du = "<span class='has-text-jade'> {$percent_du}%</span>";
            break;
        case $percent_du >= 50:
            $font_color_du = "<span class='has-text-turquoise'> {$percent_du}%</span>";
            break;
        case $percent_du >= 40:
            $font_color_du = "<span class='has-text-lghtblue'> {$percent_du}%</span>";
            break;
        case $percent_du >= 30:
            $font_color_du = "<span class='has-text-gold'> {$percent_du}%</span>";
            break;
        case $percent_du >= 20:
            $font_color_du = "<span class='has-text-oragne'> {$percent_du}%</span>";
            break;
        case $percent_du < 20:
            $font_color_du = "<span class='has-text-red'> {$percent_du}%</span>";
            break;
    }

    $percent_hd = $cache->get('halfdownload_counter_alerts_');
    if ($percent_hd === false || is_null($percent_hd)) {
        $res = $fluent->from('bonus')
            ->select(null)
            ->select('pointspool / points * 100 AS percent')
            ->where('id = 13')
            ->fetch();

        $percent_hd = number_format($res['percent'], 2);
        $cache->set('halfdownload_counter_alerts_', $percent_hd, 0);
    }

    switch ($percent_hd) {
        case $percent_hd >= 90:
            $font_color_hd = "<span class='has-text-green'> {$percent_hd}%</span>";
            break;
        case $percent_hd >= 80:
            $font_color_hd = "<span class='has-text-lightgreen'> {$percent_hd}%</span>";
            break;
        case $percent_hd >= 70:
            $font_color_hd = "<span class='has-text-jade'> {$percent_hd}%</span>";
            break;
        case $percent_hd >= 50:
            $font_color_hd = "<span class='has-text-turquoise'> {$percent_hd}%</span>";
            break;
        case $percent_hd >= 40:
            $font_color_hd = "<span class='has-text-lghtblue'> {$percent_hd}%</span>";
            break;
        case $percent_hd >= 30:
            $font_color_hd = "<span class='has-text-gold'> {$percent_hd}%</span>";
            break;
        case $percent_hd >= 20:
            $font_color_hd = "<span class='has-text-oragne'> {$percent_hd}%</span>";
            break;
        case $percent_hd < 20:
            $font_color_hd = "<span class='has-text-red'> {$percent_hd}%</span>";
            break;
    }

    if ($freeleech_enabled) {
        $fstatus = "<span class='has-text-green'> ON </span>";
    } else {
        $fstatus = $font_color_fl . '';
    }
    if ($double_upload_enabled) {
        $dstatus = "<span class='has-text-green'> ON </span>";
    } else {
        $dstatus = $font_color_du . '';
    }
    if ($half_down_enabled) {
        $hstatus = "<span class='has-text-green'> ON </span>";
    } else {
        $hstatus = $font_color_hd . '';
    }
    $htmlout .= "
                <li>
                    <a href='{$site_config['baseurl']}/mybonus.php'>
                        <span class='button tag is-success dt-tooltipper-large' data-tooltip-content='#karma_tooltip'>Karma Contribution's</span>
                        <div class='tooltip_templates'>
                            <span id='karma_tooltip'>
                                <div class='size_4 has-text-centered has-text-success has-text-weight-bold bottom10'>Karma Contribution's</div>
                                <div class='level is-marginless'>
                                    <span>Freeleech</span><span> [ ";
    if ($freeleech_enabled) {
        $htmlout .= "<span class='has-text-success'> ON </span>" . get_date($freeleech_start_time, 'DATE') . ' - ' . get_date($freeleech_end_time, 'DATE');
    } else {
        $htmlout .= $fstatus;
    }
    $htmlout .= ' ]
                                    </span>
                                </div>';

    $htmlout .= "
                                <div class='level is-marginless'>
                                    <span>DoubleUpload</span><span> [ ";
    if ($double_upload_enabled) {
        $htmlout .= "<span class='has-text-success'> ON </span>" . get_date($double_upload_start_time, 'DATE') . ' - ' . get_date($double_upload_end_time, 'DATE');
    } else {
        $htmlout .= $dstatus;
    }
    $htmlout .= ' ]
                                    </span>
                                </div>';

    $htmlout .= "
                                <div class='level is-marginless'>
                                    <span>Half Download</span><span> [ ";
    if ($half_down_enabled) {
        $htmlout .= '<span class="has-text-success"> ON</span> ' . get_date($half_down_start_time, 'DATE') . ' - ' . get_date($half_down_end_time, 'DATE');
    } else {
        $htmlout .= $hstatus;
    }
    $htmlout .= ' ]
                                    </span>
                                </div>
                            </span>
                        </div>
                    </a>
                </li>';
}
//=== karma contribution alert end
// End Class
// End File
