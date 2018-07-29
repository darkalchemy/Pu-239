<?php

/** Adminer - Compact database management
 *
 * @link      https://www.adminer.org/
 * @author    Jakub Vrana, https://www.vrana.cz/
 * @copyright 2007 Jakub Vrana
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 * @version   4.6.3
 */
error_reporting(6135);
$tc = !preg_match('~^(unsafe_raw)?$~', ini_get("filter.default"));
if ($tc || ini_get("filter.default_flags")) {
    foreach ([
                 '_GET',
                 '_POST',
                 '_COOKIE',
                 '_SERVER',
             ] as $X) {
        $Yg = filter_input_array(constant("INPUT$X"), FILTER_UNSAFE_RAW);
        if ($Yg) {
            $$X = $Yg;
        }
    }
}
if (function_exists("mb_internal_encoding")) {
    mb_internal_encoding("8bit");
}
function connection()
{
    global $g;
    return $g;
}

function adminer()
{
    global $c;
    return $c;
}

function version()
{
    global $fa;
    return $fa;
}

function idf_unescape($w)
{
    $rd = substr($w, -1);
    return str_replace($rd . $rd, $rd, substr($w, 1, -1));
}

function escape_string($X)
{
    return substr(q($X), 1, -1);
}

function number($X)
{
    return preg_replace('~[^0-9]+~', '', $X);
}

function number_type()
{
    return '((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';
}

function remove_slashes($ef, $tc = false)
{
    if (get_magic_quotes_gpc()) {
        while (list($_, $X) = each($ef)) {
            foreach ($X as $jd => $W) {
                unset($ef[$_][$jd]);
                if (is_array($W)) {
                    $ef[$_][stripslashes($jd)] = $W;
                    $ef[] =& $ef[$_][stripslashes($jd)];
                } else {
                    $ef[$_][stripslashes($jd)] = ($tc ? $W : stripslashes($W));
                }
            }
        }
    }
}

function bracket_escape($w, $_a = false)
{
    static $Lg = [
        ':' => ':1',
        ']' => ':2',
        '[' => ':3',
        '"' => ':4',
    ];
    return strtr($w, ($_a ? array_flip($Lg) : $Lg));
}

function min_version($mh, $Dd = "", $h = null)
{
    global $g;
    if (!$h) {
        $h = $g;
    }
    $Mf = $h->server_info;
    if ($Dd && preg_match('~([\d.]+)-MariaDB~', $Mf, $D)) {
        $Mf = $D[1];
        $mh = $Dd;
    }
    return (version_compare($Mf, $mh) >= 0);
}

function charset($g)
{
    return (min_version("5.5.3", 0, $g) ? "utf8mb4" : "utf8");
}

function script($Uf, $Kg = "\n")
{
    return "<script" . nonce() . ">$Uf</script>$Kg";
}

function script_src($dh)
{
    return "<script src='" . h($dh) . "'" . nonce() . "></script>\n";
}

function nonce()
{
    return ' nonce="' . get_nonce() . '"';
}

function target_blank()
{
    return ' target="_blank" rel="noreferrer noopener"';
}

function h($eg)
{
    return str_replace("\0", "&#0;", htmlspecialchars($eg, ENT_QUOTES, 'utf-8'));
}

function nl_br($eg)
{
    return str_replace("\n", "<br>", $eg);
}

function checkbox($F, $Y, $Na, $nd = "", $ne = "", $Ra = "", $od = "")
{
    $K = "<input type='checkbox' name='$F' value='" . h($Y) . "'" . ($Na ? " checked" : "") . ($od ? " aria-labelledby='$od'" : "") . ">" . ($ne ? script("qsl('input').onclick = function () { $ne };", "") : "");
    return ($nd != "" || $Ra ? "<label" . ($Ra ? " class='$Ra'" : "") . ">$K" . h($nd) . "</label>" : $K);
}

function optionlist($re, $Hf = null, $gh = false)
{
    $K = "";
    foreach ($re as $jd => $W) {
        $se = [$jd => $W];
        if (is_array($W)) {
            $K .= '<optgroup label="' . h($jd) . '">';
            $se = $W;
        }
        foreach ($se as $_ => $X) {
            $K .= '<option' . ($gh || is_string($_) ? ' value="' . h($_) . '"' : '') . (($gh || is_string($_) ? (string) $_ : $X) === $Hf ? ' selected' : '') . '>' . h($X);
        }
        if (is_array($W)) {
            $K .= '</optgroup>';
        }
    }
    return $K;
}

function html_select($F, $re, $Y = "", $me = true, $od = "")
{
    if ($me) {
        return "<select name='" . h($F) . "'" . ($od ? " aria-labelledby='$od'" : "") . ">" . optionlist($re, $Y) . "</select>" . (is_string($me) ? script("qsl('select').onchange = function () { $me };", "") : "");
    }
    $K = "";
    foreach ($re as $_ => $X) {
        $K .= "<label><input type='radio' name='" . h($F) . "' value='" . h($_) . "'" . ($_ == $Y ? " checked" : "") . ">" . h($X) . "</label>";
    }
    return $K;
}

function select_input($wa, $re, $Y = "", $me = "", $Re = "")
{
    $tg = ($re ? "select" : "input");
    return "<$tg$wa" . ($re ? "><option value=''>$Re" . optionlist($re, $Y, true) . "</select>" : " size='10' value='" . h($Y) . "' placeholder='$Re'>") . ($me ? script("qsl('$tg').onchange = $me;", "") : "");
}

function confirm($E = "", $If = "qsl('input')")
{
    return script("$If.onclick = function () { return confirm('" . ($E ? js_escape($E) : lang(0)) . "'); };", "");
}

function print_fieldset($v, $wd, $ph = false)
{
    echo "<fieldset><legend>", "<a href='#fieldset-$v'>$wd</a>", script("qsl('a').onclick = partial(toggle, 'fieldset-$v');", ""), "</legend>", "<div id='fieldset-$v'" . ($ph ? "" : " class='hidden'") . ">\n";
}

function bold($Ga, $Ra = "")
{
    return ($Ga ? " class='active $Ra'" : ($Ra ? " class='$Ra'" : ""));
}

function odd($K = ' class="odd"')
{
    static $u = 0;
    if (!$K) {
        $u = -1;
    }
    return ($u++ % 2 ? $K : '');
}

function js_escape($eg)
{
    return addcslashes($eg, "\r\n'\\/");
}

function json_row($_, $X = null)
{
    static $uc = true;
    if ($uc) {
        echo "{";
    }
    if ($_ != "") {
        echo ($uc ? "" : ",") . "\n\t\"" . addcslashes($_, "\r\n\t\"\\/") . '": ' . ($X !== null ? '"' . addcslashes($X, "\r\n\"\\/") . '"' : 'null');
        $uc = false;
    } else {
        echo "\n}\n";
        $uc = true;
    }
}

function ini_bool($Xc)
{
    $X = ini_get($Xc);
    return (preg_match('~^(on|true|yes)$~i', $X) || (int) $X);
}

function sid()
{
    static $K;
    if ($K === null) {
        $K = (SID && !($_COOKIE && ini_bool("session.use_cookies")));
    }
    return $K;
}

function set_password($lh, $O, $V, $Ne)
{
    $_SESSION["pwds"][$lh][$O][$V] = ($_COOKIE["adminer_key"] && is_string($Ne) ? [encrypt_string($Ne, $_COOKIE["adminer_key"])] : $Ne);
}

function get_password()
{
    $K = get_session("pwds");
    if (is_array($K)) {
        $K = ($_COOKIE["adminer_key"] ? decrypt_string($K[0], $_COOKIE["adminer_key"]) : false);
    }
    return $K;
}

function q($eg)
{
    global $g;
    return $g->quote($eg);
}

function get_vals($I, $d = 0)
{
    global $g;
    $K = [];
    $J = $g->query($I);
    if (is_object($J)) {
        while ($L = $J->fetch_row()) {
            $K[] = $L[$d];
        }
    }
    return $K;
}

function get_key_vals($I, $h = null, $Pf = true)
{
    global $g;
    if (!is_object($h)) {
        $h = $g;
    }
    $K = [];
    $J = $h->query($I);
    if (is_object($J)) {
        while ($L = $J->fetch_row()) {
            if ($Pf) {
                $K[$L[0]] = $L[1];
            } else {
                $K[] = $L[0];
            }
        }
    }
    return $K;
}

function get_rows($I, $h = null, $m = "<p class='error'>")
{
    global $g;
    $eb = (is_object($h) ? $h : $g);
    $K = [];
    $J = $eb->query($I);
    if (is_object($J)) {
        while ($L = $J->fetch_assoc()) {
            $K[] = $L;
        }
    } elseif (!$J && !is_object($h) && $m && defined("PAGE_HEADER")) {
        echo $m . error() . "\n";
    }
    return $K;
}

function unique_array($L, $y)
{
    foreach ($y as $x) {
        if (preg_match("~PRIMARY|UNIQUE~", $x["type"])) {
            $K = [];
            foreach ($x["columns"] as $_) {
                if (!isset($L[$_])) {
                    continue
                    2;
                }
                $K[$_] = $L[$_];
            }
            return $K;
        }
    }
}

function escape_key($_)
{
    if (preg_match('(^([\w(]+)(' . str_replace("_", ".*", preg_quote(idf_escape("_"))) . ')([ \w)]+)$)', $_, $D)) {
        return $D[1] . idf_escape(idf_unescape($D[2])) . $D[3];
    }
    return idf_escape($_);
}

function where($Z, $o = [])
{
    global $g, $z;
    $K = [];
    foreach ((array) $Z["where"] as $_ => $X) {
        $_ = bracket_escape($_, 1);
        $d = escape_key($_);
        $K[] = $d . ($z == "sql" && preg_match('~^[0-9]*\.[0-9]*$~', $X) ? " LIKE " . q(addcslashes($X, "%_\\")) : ($z == "mssql" ? " LIKE " . q(preg_replace('~[_%[]~', '[\0]', $X)) : " = " . unconvert_field($o[$_], q($X))));
        if ($z == "sql" && preg_match('~char|text~', $o[$_]["type"]) && preg_match("~[^ -@]~", $X)) {
            $K[] = "$d = " . q($X) . " COLLATE " . charset($g) . "_bin";
        }
    }
    foreach ((array) $Z["null"] as $_) {
        $K[] = escape_key($_) . " IS NULL";
    }
    return implode(" AND ", $K);
}

function where_check($X, $o = [])
{
    parse_str($X, $Ma);
    remove_slashes([&$Ma]);
    return where($Ma, $o);
}

function where_link($u, $d, $Y, $oe = "=")
{
    return "&where%5B$u%5D%5Bcol%5D=" . urlencode($d) . "&where%5B$u%5D%5Bop%5D=" . urlencode(($Y !== null ? $oe : "IS NULL")) . "&where%5B$u%5D%5Bval%5D=" . urlencode($Y);
}

function convert_fields($e, $o, $N = [])
{
    $K = "";
    foreach ($e as $_ => $X) {
        if ($N && !in_array(idf_escape($_), $N)) {
            continue;
        }
        $ua = convert_field($o[$_]);
        if ($ua) {
            $K .= ", $ua AS " . idf_escape($_);
        }
    }
    return $K;
}

function cookie($F, $Y, $zd = 2592000)
{
    global $ba;
    return header("Set-Cookie: $F=" . urlencode($Y) . ($zd ? "; expires=" . gmdate("D, d M Y H:i:s", time() + $zd) . " GMT" : "") . "; path=" . preg_replace('~\?.*~', '', $_SERVER["REQUEST_URI"]) . ($ba ? "; secure" : "") . "; HttpOnly; SameSite=lax", false);
}

function restart_session()
{
    if (!ini_bool("session.use_cookies")) {
        session_start();
    }
}

function stop_session($wc = false)
{
    if (!ini_bool("session.use_cookies") || ($wc && @ini_set("session.use_cookies", false) !== false)) {
        session_write_close();
    }
}

function&get_session($_)
{
    return $_SESSION[$_][DRIVER][SERVER][$_GET["username"]];
}

function set_session($_, $X)
{
    $_SESSION[$_][DRIVER][SERVER][$_GET["username"]] = $X;
}

function auth_url($lh, $O, $V, $k = null)
{
    global $Ib;
    preg_match('~([^?]*)\??(.*)~', remove_from_uri(implode("|", array_keys($Ib)) . "|username|" . ($k !== null ? "db|" : "") . session_name()), $D);
    return "$D[1]?" . (sid() ? SID . "&" : "") . ($lh != "server" || $O != "" ? urlencode($lh) . "=" . urlencode($O) . "&" : "") . "username=" . urlencode($V) . ($k != "" ? "&db=" . urlencode($k) : "") . ($D[2] ? "&$D[2]" : "");
}

function is_ajax()
{
    return ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest");
}

function redirect($C, $E = null)
{
    if ($E !== null) {
        restart_session();
        $_SESSION["messages"][preg_replace('~^[^?]*~', '', ($C !== null ? $C : $_SERVER["REQUEST_URI"]))][] = $E;
    }
    if ($C !== null) {
        if ($C == "") {
            $C = ".";
        }
        header("Location: $C");
        exit;
    }
}

function query_redirect($I, $C, $E, $mf = true, $gc = true, $nc = false, $_g = "")
{
    global $g, $m, $c;
    if ($gc) {
        $ag = microtime(true);
        $nc = !$g->query($I);
        $_g = format_time($ag);
    }
    $Wf = "";
    if ($I) {
        $Wf = $c->messageQuery($I, $_g, $nc);
    }
    if ($nc) {
        $m = error() . $Wf . script("messagesPrint();");
        return false;
    }
    if ($mf) {
        redirect($C, $E . $Wf);
    }
    return true;
}

function queries($I)
{
    global $g;
    static $hf = [];
    static $ag;
    if (!$ag) {
        $ag = microtime(true);
    }
    if ($I === null) {
        return [
            implode("\n", $hf),
            format_time($ag),
        ];
    }
    $hf[] = (preg_match('~;$~', $I) ? "DELIMITER ;;\n$I;\nDELIMITER " : $I) . ";";
    return $g->query($I);
}

function apply_queries($I, $S, $cc = 'table')
{
    foreach ($S as $Q) {
        if (!queries("$I " . $cc($Q))) {
            return false;
        }
    }
    return true;
}

function queries_redirect($C, $E, $mf)
{
    list($hf, $_g) = queries(null);
    return query_redirect($hf, $C, $E, $mf, false, !$mf, $_g);
}

function format_time($ag)
{
    return lang(1, max(0, microtime(true) - $ag));
}

function remove_from_uri($Fe = "")
{
    return substr(preg_replace("~(?<=[?&])($Fe" . (SID ? "" : "|" . session_name()) . ")=[^&]*&~", '', "$_SERVER[REQUEST_URI]&"), 0, -1);
}

function pagination($G, $pb)
{
    return " " . ($G == $pb ? $G + 1 : '<a href="' . h(remove_from_uri("page") . ($G ? "&page=$G" . ($_GET["next"] ? "&next=" . urlencode($_GET["next"]) : "") : "")) . '">' . ($G + 1) . "</a>");
}

function get_file($_, $xb = false)
{
    $rc = $_FILES[$_];
    if (!$rc) {
        return null;
    }
    foreach ($rc as $_ => $X) {
        $rc[$_] = (array) $X;
    }
    $K = '';
    foreach ($rc["error"] as $_ => $m) {
        if ($m) {
            return $m;
        }
        $F = $rc["name"][$_];
        $Hg = $rc["tmp_name"][$_];
        $fb = file_get_contents($xb && preg_match('~\.gz$~', $F) ? "compress.zlib://$Hg" : $Hg);
        if ($xb) {
            $ag = substr($fb, 0, 3);
            if (function_exists("iconv") && preg_match("~^\xFE\xFF|^\xFF\xFE~", $ag, $sf)) {
                $fb = iconv("utf-16", "utf-8", $fb);
            } elseif ($ag == "\xEF\xBB\xBF") {
                $fb = substr($fb, 3);
            }
            $K .= $fb . "\n\n";
        } else {
            $K .= $fb;
        }
    }
    return $K;
}

function upload_error($m)
{
    $Jd = ($m == UPLOAD_ERR_INI_SIZE ? ini_get("upload_max_filesize") : 0);
    return ($m ? lang(2) . ($Jd ? " " . lang(3, $Jd) : "") : lang(4));
}

function repeat_pattern($Pe, $xd)
{
    return str_repeat("$Pe{0,65535}", $xd / 65535) . "$Pe{0," . ($xd % 65535) . "}";
}

function is_utf8($X)
{
    return (preg_match('~~u', $X) && !preg_match('~[\0-\x8\xB\xC\xE-\x1F]~', $X));
}

function shorten_utf8($eg, $xd = 80, $ig = "")
{
    if (!preg_match("(^(" . repeat_pattern("[\t\r\n -\x{10FFFF}]", $xd) . ")($)?)u", $eg, $D)) {
        preg_match("(^(" . repeat_pattern("[\t\r\n -~]", $xd) . ")($)?)", $eg, $D);
    }
    return h($D[1]) . $ig . (isset($D[2]) ? "" : "<i>...</i>");
}

function format_number($X)
{
    return strtr(number_format($X, 0, ".", lang(5)), preg_split('~~u', lang(6), -1, PREG_SPLIT_NO_EMPTY));
}

function friendly_url($X)
{
    return preg_replace('~[^a-z0-9_]~i', '-', $X);
}

function hidden_fields($ef, $Uc = [])
{
    $K = false;
    while (list($_, $X) = each($ef)) {
        if (!in_array($_, $Uc)) {
            if (is_array($X)) {
                foreach ($X as $jd => $W) {
                    $ef[$_ . "[$jd]"] = $W;
                }
            } else {
                $K = true;
                echo '<input type="hidden" name="' . h($_) . '" value="' . h($X) . '">';
            }
        }
    }
    return $K;
}

function hidden_fields_get()
{
    echo(sid() ? '<input type="hidden" name="' . session_name() . '" value="' . h(session_id()) . '">' : ''), (SERVER !== null ? '<input type="hidden" name="' . DRIVER . '" value="' . h(SERVER) . '">' : ""), '<input type="hidden" name="username" value="' . h($_GET["username"]) . '">';
}

function table_status1($Q, $oc = false)
{
    $K = table_status($Q, $oc);
    return ($K ? $K : ["Name" => $Q]);
}

function column_foreign_keys($Q)
{
    global $c;
    $K = [];
    foreach ($c->foreignKeys($Q) as $p) {
        foreach ($p["source"] as $X) {
            $K[$X][] = $p;
        }
    }
    return $K;
}

function enum_input($U, $wa, $n, $Y, $Wb = null)
{
    global $c;
    preg_match_all("~'((?:[^']|'')*)'~", $n["length"], $Ed);
    $K = ($Wb !== null ? "<label><input type='$U'$wa value='$Wb'" . ((is_array($Y) ? in_array($Wb, $Y) : $Y === 0) ? " checked" : "") . "><i>" . lang(7) . "</i></label>" : "");
    foreach ($Ed[1] as $u => $X) {
        $X = stripcslashes(str_replace("''", "'", $X));
        $Na = (is_int($Y) ? $Y == $u + 1 : (is_array($Y) ? in_array($u + 1, $Y) : $Y === $X));
        $K .= " <label><input type='$U'$wa value='" . ($u + 1) . "'" . ($Na ? ' checked' : '') . '>' . h($c->editVal($X, $n)) . '</label>';
    }
    return $K;
}

function input($n, $Y, $s)
{
    global $Tg, $c, $z;
    $F = h(bracket_escape($n["field"]));
    echo "<td class='function'>";
    if (is_array($Y) && !$s) {
        $ta = [$Y];
        if (version_compare(PHP_VERSION, 5.4) >= 0) {
            $ta[] = JSON_PRETTY_PRINT;
        }
        $Y = call_user_func_array('json_encode', $ta);
        $s = "json";
    }
    $uf = ($z == "mssql" && $n["auto_increment"]);
    if ($uf && !$_POST["save"]) {
        $s = null;
    }
    $Cc = (isset($_GET["select"]) || $uf ? ["orig" => lang(8)] : []) + $c->editFunctions($n);
    $wa = " name='fields[$F]'";
    if ($n["type"] == "enum") {
        echo h($Cc[""]) . "<td>" . $c->editInput($_GET["edit"], $n, $wa, $Y);
    } else {
        $Lc = (in_array($s, $Cc) || isset($Cc[$s]));
        echo (count($Cc) > 1 ? "<select name='function[$F]'>" . optionlist($Cc, $s === null || $Lc ? $s : "") . "</select>" . on_help("getTarget(event).value.replace(/^SQL\$/, '')", 1) . script("qsl('select').onchange = functionChange;", "") : h(reset($Cc))) . '<td>';
        $Zc = $c->editInput($_GET["edit"], $n, $wa, $Y);
        if ($Zc != "") {
            echo $Zc;
        } elseif (preg_match('~bool~', $n["type"])) {
            echo "<input type='hidden'$wa value='0'>" . "<input type='checkbox'" . (preg_match('~^(1|t|true|y|yes|on)$~i', $Y) ? " checked='checked'" : "") . "$wa value='1'>";
        } elseif ($n["type"] == "set") {
            preg_match_all("~'((?:[^']|'')*)'~", $n["length"], $Ed);
            foreach ($Ed[1] as $u => $X) {
                $X = stripcslashes(str_replace("''", "'", $X));
                $Na = (is_int($Y) ? ($Y >> $u) & 1 : in_array($X, explode(",", $Y), true));
                echo " <label><input type='checkbox' name='fields[$F][$u]' value='" . (1 << $u) . "'" . ($Na ? ' checked' : '') . ">" . h($c->editVal($X, $n)) . '</label>';
            }
        } elseif (preg_match('~blob|bytea|raw|file~', $n["type"]) && ini_bool("file_uploads")) {
            echo "<input type='file' name='fields-$F'>";
        } elseif (($yg = preg_match('~text|lob~', $n["type"])) || preg_match("~\n~", $Y)) {
            if ($yg && $z != "sqlite") {
                $wa .= " cols='50' rows='12'";
            } else {
                $M = min(12, substr_count($Y, "\n") + 1);
                $wa .= " cols='30' rows='$M'" . ($M == 1 ? " style='height: 1.2em;'" : "");
            }
            echo "<textarea$wa>" . h($Y) . '</textarea>';
        } elseif ($s == "json" || preg_match('~^jsonb?$~', $n["type"])) {
            echo "<textarea$wa cols='50' rows='12' class='jush-js'>" . h($Y) . '</textarea>';
        } else {
            $Ld = (!preg_match('~int~', $n["type"]) && preg_match('~^(\d+)(,(\d+))?$~', $n["length"], $D) ? ((preg_match("~binary~", $n["type"]) ? 2 : 1) * $D[1] + ($D[3] ? 1 : 0) + ($D[2] && !$n["unsigned"] ? 1 : 0)) : ($Tg[$n["type"]] ? $Tg[$n["type"]] + ($n["unsigned"] ? 0 : 1) : 0));
            if ($z == 'sql' && min_version(5.6) && preg_match('~time~', $n["type"])) {
                $Ld += 7;
            }
            echo "<input" . ((!$Lc || $s === "") && preg_match('~(?<!o)int(?!er)~', $n["type"]) && !preg_match('~\[\]~', $n["full_type"]) ? " type='number'" : "") . " value='" . h($Y) . "'" . ($Ld ? " data-maxlength='$Ld'" : "") . (preg_match('~char|binary~', $n["type"]) && $Ld > 20 ? " size='40'" : "") . "$wa>";
        }
        echo $c->editHint($_GET["edit"], $n, $Y);
        $uc = 0;
        foreach ($Cc as $_ => $X) {
            if ($_ === "" || !$X) {
                break;
            }
            $uc++;
        }
        if ($uc) {
            echo script("mixin(qsl('td'), {onchange: partial(skipOriginal, $uc), oninput: function () { this.onchange(); }});");
        }
    }
}

function process_input($n)
{
    global $c, $l;
    $w = bracket_escape($n["field"]);
    $s = $_POST["function"][$w];
    $Y = $_POST["fields"][$w];
    if ($n["type"] == "enum") {
        if ($Y == -1) {
            return false;
        }
        if ($Y == "") {
            return "NULL";
        }
        return +$Y;
    }
    if ($n["auto_increment"] && $Y == "") {
        return null;
    }
    if ($s == "orig") {
        return ($n["on_update"] == "CURRENT_TIMESTAMP" ? idf_escape($n["field"]) : false);
    }
    if ($s == "NULL") {
        return "NULL";
    }
    if ($n["type"] == "set") {
        return array_sum((array) $Y);
    }
    if ($s == "json") {
        $s = "";
        $Y = json_decode($Y, true);
        if (!is_array($Y)) {
            return false;
        }
        return $Y;
    }
    if (preg_match('~blob|bytea|raw|file~', $n["type"]) && ini_bool("file_uploads")) {
        $rc = get_file("fields-$w");
        if (!is_string($rc)) {
            return false;
        }
        return $l->quoteBinary($rc);
    }
    return $c->processInput($n, $Y, $s);
}

function fields_from_edit()
{
    global $l;
    $K = [];
    foreach ((array) $_POST["field_keys"] as $_ => $X) {
        if ($X != "") {
            $X = bracket_escape($X);
            $_POST["function"][$X] = $_POST["field_funs"][$_];
            $_POST["fields"][$X] = $_POST["field_vals"][$_];
        }
    }
    foreach ((array) $_POST["fields"] as $_ => $X) {
        $F = bracket_escape($_, 1);
        $K[$F] = [
            "field"          => $F,
            "privileges"     => [
                "insert" => 1,
                "update" => 1,
            ],
            "null"           => 1,
            "auto_increment" => ($_ == $l->primary),
        ];
    }
    return $K;
}

function search_tables()
{
    global $c, $g;
    $_GET["where"][0]["val"] = $_POST["query"];
    $Kf = "<ul>\n";
    foreach (table_status('', true) as $Q => $R) {
        $F = $c->tableName($R);
        if (isset($R["Engine"]) && $F != "" && (!$_POST["tables"] || in_array($Q, $_POST["tables"]))) {
            $J = $g->query("SELECT" . limit("1 FROM " . table($Q), " WHERE " . implode(" AND ", $c->selectSearchProcess(fields($Q), [])), 1));
            if (!$J || $J->fetch_row()) {
                $af = "<a href='" . h(ME . "select=" . urlencode($Q) . "&where[0][op]=" . urlencode($_GET["where"][0]["op"]) . "&where[0][val]=" . urlencode($_GET["where"][0]["val"])) . "'>$F</a>";
                echo "$Kf<li>" . ($J ? $af : "<p class='error'>$af: " . error()) . "\n";
                $Kf = "";
            }
        }
    }
    echo ($Kf ? "<p class='message'>" . lang(9) : "</ul>") . "\n";
}

function dump_headers($Tc, $Sd = false)
{
    global $c;
    $K = $c->dumpHeaders($Tc, $Sd);
    $Ce = $_POST["output"];
    if ($Ce != "text") {
        header("Content-Disposition: attachment; filename=" . $c->dumpFilename($Tc) . ".$K" . ($Ce != "file" && !preg_match('~[^0-9a-z]~', $Ce) ? ".$Ce" : ""));
    }
    session_write_close();
    ob_flush();
    flush();
    return $K;
}

function dump_csv($L)
{
    foreach ($L as $_ => $X) {
        if (preg_match("~[\"\n,;\t]~", $X) || $X === "") {
            $L[$_] = '"' . str_replace('"', '""', $X) . '"';
        }
    }
    echo implode(($_POST["format"] == "csv" ? "," : ($_POST["format"] == "tsv" ? "\t" : ";")), $L) . "\r\n";
}

function apply_sql_function($s, $d)
{
    return ($s ? ($s == "unixepoch" ? "DATETIME($d, '$s')" : ($s == "count distinct" ? "COUNT(DISTINCT " : strtoupper("$s(")) . "$d)") : $d);
}

function get_temp_dir()
{
    $K = ini_get("upload_tmp_dir");
    if (!$K) {
        if (function_exists('sys_get_temp_dir')) {
            $K = sys_get_temp_dir();
        } else {
            $sc = @tempnam("", "");
            if (!$sc) {
                return false;
            }
            $K = dirname($sc);
            unlink($sc);
        }
    }
    return $K;
}

function file_open_lock($sc)
{
    $r = @fopen($sc, "r+");
    if (!$r) {
        $r = @fopen($sc, "w");
        if (!$r) {
            return;
        }
        chmod($sc, 0660);
    }
    flock($r, LOCK_EX);
    return $r;
}

function file_write_unlock($r, $rb)
{
    rewind($r);
    fwrite($r, $rb);
    ftruncate($r, strlen($rb));
    flock($r, LOCK_UN);
    fclose($r);
}

function password_file($i)
{
    $sc = get_temp_dir() . "/adminer.key";
    $K = @file_get_contents($sc);
    if ($K || !$i) {
        return $K;
    }
    $r = @fopen($sc, "w");
    if ($r) {
        chmod($sc, 0660);
        $K = rand_string();
        fwrite($r, $K);
        fclose($r);
    }
    return $K;
}

function rand_string()
{
    return md5(uniqid(mt_rand(), true));
}

function select_value($X, $B, $n, $zg)
{
    global $c;
    if (is_array($X)) {
        $K = "";
        foreach ($X as $jd => $W) {
            $K .= "<tr>" . ($X != array_values($X) ? "<th>" . h($jd) : "") . "<td>" . select_value($W, $B, $n, $zg);
        }
        return "<table cellspacing='0'>$K</table>";
    }
    if (!$B) {
        $B = $c->selectLink($X, $n);
    }
    if ($B === null) {
        if (is_mail($X)) {
            $B = "mailto:$X";
        }
        if (is_url($X)) {
            $B = $X;
        }
    }
    $K = $c->editVal($X, $n);
    if ($K !== null) {
        if (!is_utf8($K)) {
            $K = "\0";
        } elseif ($zg != "" && is_shortable($n)) {
            $K = shorten_utf8($K, max(0, +$zg));
        } else {
            $K = h($K);
        }
    }
    return $c->selectVal($K, $B, $n, $X);
}

function is_mail($Tb)
{
    $va = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]';
    $Hb = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';
    $Pe = "$va+(\\.$va+)*@($Hb?\\.)+$Hb";
    return is_string($Tb) && preg_match("(^$Pe(,\\s*$Pe)*\$)i", $Tb);
}

function is_url($eg)
{
    $Hb = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';
    return preg_match("~^(https?)://($Hb?\\.)+$Hb(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i", $eg);
}

function is_shortable($n)
{
    return preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea~', $n["type"]);
}

function count_rows($Q, $Z, $fd, $t)
{
    global $z;
    $I = " FROM " . table($Q) . ($Z ? " WHERE " . implode(" AND ", $Z) : "");
    return ($fd && ($z == "sql" || count($t) == 1) ? "SELECT COUNT(DISTINCT " . implode(", ", $t) . ")$I" : "SELECT COUNT(*)" . ($fd ? " FROM (SELECT 1$I GROUP BY " . implode(", ", $t) . ") x" : $I));
}

function slow_query($I)
{
    global $c, $T, $l;
    $k = $c->database();
    $Ag = $c->queryTimeout();
    $Sf = $l->slowQuery($I, $Ag);
    if (!$Sf && support("kill") && is_object($h = connect()) && ($k == "" || $h->select_db($k))) {
        $ld = $h->result(connection_id());
        echo '<script', nonce(), '>
var timeout = setTimeout(function () {
	ajax(\'', js_escape(ME), 'script=kill\', function () {
	}, \'kill=', $ld, '&token=', $T, '\');
}, ', 1000 * $Ag, ');
</script>
';
    } else {
        $h = null;
    }
    ob_flush();
    flush();
    $K = @get_key_vals(($Sf ? $Sf : $I), $h, false);
    if ($h) {
        echo script("clearTimeout(timeout);");
        ob_flush();
        flush();
    }
    return $K;
}

function get_token()
{
    $kf = rand(1, 1e6);
    return ($kf ^ $_SESSION["token"]) . ":$kf";
}

function verify_token()
{
    list($T, $kf) = explode(":", $_POST["token"]);
    return ($kf ^ $_SESSION["token"]) == $T;
}

function lzw_decompress($Da)
{
    $Db = 256;
    $Ea = 8;
    $Ta = [];
    $vf = 0;
    $wf = 0;
    for ($u = 0; $u < strlen($Da); $u++) {
        $vf = ($vf << 8) + ord($Da[$u]);
        $wf += 8;
        if ($wf >= $Ea) {
            $wf -= $Ea;
            $Ta[] = $vf >> $wf;
            $vf &= (1 << $wf) - 1;
            $Db++;
            if ($Db >> $Ea) {
                $Ea++;
            }
        }
    }
    $Cb = range("\0", "\xFF");
    $K = "";
    foreach ($Ta as $u => $Sa) {
        $Sb = $Cb[$Sa];
        if (!isset($Sb)) {
            $Sb = $vh . $vh[0];
        }
        $K .= $Sb;
        if ($u) {
            $Cb[] = $vh . $Sb[0];
        }
        $vh = $Sb;
    }
    return $K;
}

function on_help($Za, $Qf = 0)
{
    return script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $Za, $Qf) }, onmouseout: helpMouseout});", "");
}

function edit_form($b, $o, $L, $bh)
{
    global $c, $z, $T, $m;
    $ng = $c->tableName(table_status1($b, true));
    page_header(($bh ? lang(10) : lang(11)), $m, [
        "select" => [
            $b,
            $ng,
        ],
    ], $ng);
    if ($L === false) {
        echo "<p class='error'>" . lang(12) . "\n";
    }
    echo '<form action="" method="post" enctype="multipart/form-data" id="form">
';
    if (!$o) {
        echo "<p class='error'>" . lang(13) . "\n";
    } else {
        echo "<table cellspacing='0'>" . script("qsl('table').onkeydown = editingKeydown;");
        foreach ($o as $F => $n) {
            echo "<tr><th>" . $c->fieldName($n);
            $yb = $_GET["set"][bracket_escape($F)];
            if ($yb === null) {
                $yb = $n["default"];
                if ($n["type"] == "bit" && preg_match("~^b'([01]*)'\$~", $yb, $sf)) {
                    $yb = $sf[1];
                }
            }
            $Y = ($L !== null ? ($L[$F] != "" && $z == "sql" && preg_match("~enum|set~", $n["type"]) ? (is_array($L[$F]) ? array_sum($L[$F]) : +$L[$F]) : $L[$F]) : (!$bh && $n["auto_increment"] ? "" : (isset($_GET["select"]) ? false : $yb)));
            if (!$_POST["save"] && is_string($Y)) {
                $Y = $c->editVal($Y, $n);
            }
            $s = ($_POST["save"] ? (string) $_POST["function"][$F] : ($bh && $n["on_update"] == "CURRENT_TIMESTAMP" ? "now" : ($Y === false ? null : ($Y !== null ? '' : 'NULL'))));
            if (preg_match("~time~", $n["type"]) && $Y == "CURRENT_TIMESTAMP") {
                $Y = "";
                $s = "now";
            }
            input($n, $Y, $s);
            echo "\n";
        }
        if (!support("table")) {
            echo "<tr>" . "<th><input name='field_keys[]'>" . script("qsl('input').oninput = fieldChange;") . "<td class='function'>" . html_select("field_funs[]", $c->editFunctions(["null" => isset($_GET["select"])])) . "<td><input name='field_vals[]'>" . "\n";
        }
        echo "</table>\n";
    }
    echo "<p>\n";
    if ($o) {
        echo "<input type='submit' value='" . lang(14) . "'>\n";
        if (!isset($_GET["select"])) {
            echo "<input type='submit' name='insert' value='" . ($bh ? lang(15) : lang(16)) . "' title='Ctrl+Shift+Enter'>\n", ($bh ? script("qsl('input').onclick = function () { return !ajaxForm(this.form, '" . lang(17) . "...', this); };") : "");
        }
    }
    echo($bh ? "<input type='submit' name='delete' value='" . lang(18) . "'>" . confirm() . "\n" : ($_POST || !$o ? "" : script("focus(qsa('td', qs('#form'))[1].firstChild);")));
    if (isset($_GET["select"])) {
        hidden_fields([
            "check" => (array) $_POST["check"],
            "clone" => $_POST["clone"],
            "all"   => $_POST["all"],
        ]);
    }
    echo '<input type="hidden" name="referer" value="', h(isset($_POST["referer"]) ? $_POST["referer"] : $_SERVER["HTTP_REFERER"]), '">
<input type="hidden" name="save" value="1">
<input type="hidden" name="token" value="', $T, '">
</form>
';
}

if (isset($_GET["file"])) {
    if ($_SERVER["HTTP_IF_MODIFIED_SINCE"]) {
        header("HTTP/1.1 304 Not Modified");
        exit;
    }
    header("Expires: " . gmdate("D, d M Y H:i:s", time() + 365 * 24 * 60 * 60) . " GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: immutable");
    if ($_GET["file"] == "favicon.ico") {
        header("Content-Type: image/x-icon");
        echo lzw_decompress("\0\0\0` \0�\0\n @\0�C��\"\0`E�Q����?�tvM'�Jd�d\\�b0\0�\"��fӈ��s5����A�XPaJ�0���8�#R�T��z`�#.��c�X��Ȁ?�-\0�Im?�.�M��\0ȯ(̉��/(%�\0");
    } elseif ($_GET["file"] == "default.css") {
        header("Content-Type: text/css; charset=utf-8");
        echo lzw_decompress("\n1̇�ٌ�l7��B1�4vb0��fs���n2B�ѱ٘�n:�#(�b.\rDc)��a7E����l�ñ��i1̎s���-4��f�	��i7������Fé�vt2���!�r0���t~�U�'3M��W�B�'c�P�:6T\rc�A�zr_�WK�\r-�VNFS%~�c���&�\\^�r����u�ŎÞ�ً4'7k����Q��h�'g\rFB\ryT7SS�P�1=ǤcI��:�d��m>�S8L�J��t.M���	ϋ`'C����889�� �Q����2�#8А����6m����j��h�<�����9/��:�J�)ʂ�\0d>!\0Z��v�n��o(���k�7��s��>��!�R\"*nS�\0@P\"��(�#[���@g�o���zn�9k�8�n���1�I*��=�n������0�c(�;�à��!���*c��>Ύ�E7D�LJ��1����`�8(��3M��\"�39�?E�e=Ҭ�~������Ӹ7;�C����E\rd!)�a*�5ajo\0�#`�38�\0��]�e���2�	mk��e]���AZs�StZ�Z!)BR�G+�#Jv2(���c�4<�#sB�0���6YL\r�=���[�73��<�:��bx��J=	m_ ���f�l��t��I��H�3�x*���6`t6��%�U�L�eق�<�\0�AQ<P<:�#u/�:T\\>��-�xJ�͍QH\nj�L+j�z��7���`����\nk��'�N�vX>�C-T˩�����4*L�%Cj>7ߨ�ި���`���;y���q�r�3#��} :#n�\r�^�=C�Aܸ�Ǝ�s&8��K&��*0��t�S���=�[��:�\\]�E݌�/O�>^]�ø�<����gZ�V��q����� ��x\\������޺��\"J�\\î��##���D��x6��5x�������\rH�l ����b��r�7��6���j|����ۖ*�FAquvyO��WeM����D.F��:R�\$-����T!�DS`�8D�~��A`(�em�����T@O1@��X��\nLp�P�����m�yf��)	���GSEI���xC(s(a�?\$`tE�n��,�� \$a��U>,�В\$Z�kDm,G\0��\\��i��%ʹ� n��������g���b	y`��Ԇ�W� 䗗�_C��T\ni��H%�da��i�7�At�,��J�X4n����0o͹�9g\nzm�M%`�'I���О-���7:p�3p��Q�rED������b2]�PF����>e���3j\n�߰t!�?4f�tK;��\rΞи�!�o�u�?���Ph���0uIC}'~��2�v�Q���8)���7�DI�=��y&��ea�s*hɕjlA�(�\"�\\��m^i��M)��^�	|~�l��#!Y�f81RS����!���62P�C��l&���xd!�|��9�`�_OY�=��G�[E�-eL�CvT� )�@�j-5���pSg�.�G=���ZE��\$\0�цKj�U��\$���G'I�P��~�ځ� ;��hNێG%*�Rj�X[�XPf^��|��T!�*N��І�\rU��^q1V!��Uz,�I|7�7�r,���7���ľB���;�+���ߕ�A�p����^���~ؼW!3P�I8]��v�J��f�q�|,���9W�f`\0�q�Z�p}[Jdhy��N�Y|�Cy,�<s A�{e�Q���hd���Ǉ �B4;ks&�������a�������;˹}�S��J���)�=d��|���Nd��I�*8���dl�ѓ�E6~Ϩ�F����X`�M\rʞ/�%B/V�I�N&;���0�UC cT&.E+��������@�0`;���G�5��ަj'������Ɛ�Y�+��QZ-i���yv��I�5��,O|�P�]Fۏ�����\0���2�49͢���n/χ]س&��I^�=�l��qfI��= �]x1GR�&�e�7��)��'��:B�B�>a�z�-���2.����bz���#�����Uᓍ�L7-�w�t�3ɵ��e���D��\$�#���j�@�G�8� �7p���R�YC��~��:�@��EU�J��;67v]�J'���q1ϳ�El�QІi�����/��{k<��֡M�po�}��r��q�؞�c�ä�_m�w��^�u������������ln���	��_�~�G�n����{kܞ�w���\rj~�K�\0�����-����B�;����b`}�CC,���-��L��8\r,��kl�ǌ�n}-5����3u�gm��Ÿ�*�/������׏�`�`�#x�+B?#�ۏN;OR\r����\$�����k��ϙ\01\0k�\0�8��a��/t���#(&�l&���p��삅���i�M�{�zp*�-g���v��6�k�	���d�؋����A`6�lX)+d ��7 �\r�� �ځcj6��\rp�\r��\r\"oP�7�\r��\0�\0�y��P���\rQ7���Z��4Q���ڍp/�y\r��##D�;����<�g�\0fi2�)f�\\	m�Gh\r�#�n����@[ �G�\"Sqm��\r���#�(Aj��qѣ%���̑3qE��\0r�����0��я����.��Q7шW���u����� �@�H��q'vs�0�\n�+0����SG�p�O`�\r)c�#�����R=\$�ƐR\r�Gы\$R?%2C�[\0؍�~�!�\\��p�#@���O(rg%�?ra\$��)r](��&�?&�#&R�',\rqV3�\"H�m+���l�Q\"\0�4��\$r�,�=����&2;.�H@`���a����\$�_*RIS&��q��_�1�1+1������3)2�V7��2l�ڄ!1g-�2f`���,Q�7��0qg�]!q��m6����_�M7 ���7�o6Q����kp�3�g9��s� 3�6�\r�:S�9ӏ;� �\r9�-\0�Yӧ0Q�<b#<Ӂ�w/�G��>r�\r��=3��^&Q;ѣ?q�0\"�0HЙ�|���ʖS��i��@*�T�2�T#�� �\0�C��07]?��&���E��D�;:/�3�E�5��EQ�e��T\"�m����5�E;��#=4�8��*���LS�5Hr�JE TO\rԅJ��J��J���eG)8B�8�,&�G����	��+M���ɲ��^*���G��14�6�\$.\"拢�I4w!\$L �8b�A2�L�'M?MF�\$�,����Nr��/4�BJ�¨");
    } elseif ($_GET["file"] == "functions.js") {
        header("Content-Type: text/javascript; charset=utf-8");
        echo lzw_decompress("f:��gCI��\n8��3)��7���81��x:\nOg#)��r7\n\"��`�|2�gSi�H)N�S��\r��\"0��@�)�`(\$s6O!��V/=��' T4�=��iS��6IO��er�x�9�*ź��n3�\rщv�C��`���2G%�Y�����1��f���Ȃl��1�\ny�*pC\r\$�n�T��3=\\�r9O\"�	��l<�\r�\\��I,�s\nA��eh+M�!�q0��f�`(�N{c��+w���Y��p٧3�3��+I��j�����k��n�q���zi#^r�����3���[��o;��(��6�#�Ґ��\":cz>ߣC2v�CX�<�P��c*5\n���/�P97�|F��c0�����!���!���!��\nZ%�ć#CH�!��r8�\$���,�Rܔ2���^0��@�2��(�88P/��݄�\\�\$La\\�;c�H��HX���\nʃt���8A<�sZ�*�;I��3��@�2<���!A8G<�j�-K�({*\r��a1���N4Tc\"\\�!=1^���M9O�:�;j��\r�X��L#H�7�#Tݪ/-���p�;�B \n�2!���t]apΎ��\0R�C�v�M�I,\r���\0Hv��?kT�4����uٱ�;&���+&���\r�X���bu4ݡi88�2B�/⃖4���N8A�A)52������2��s�8�5���p�WC@�:�t�㾴�e��h\"#8_��cp^��I]OH��:zd�3g�(���Ök��\\6����2�ږ��i��7���]\r�xO�n�p�<��p�Q�U�n��|@���#G3��8bA��6�2�67%#�\\8\r��2�c\r�ݟk��.(�	��-�J;��� ��L�� ���W��㧓ѥɤ����n��ҧ���M��9ZНs]�z����y^[��4-�U\0ta��62^��.`���.C�j�[ᄠ% Q\0`d�M8�����\$O0`4���\n\0a\rA�<�@����\r!�:�BA�9�?h>�Ǻ��~̌�6Ȉh�=�-�A7X��և\\�\r��Q<蚧q�'!XΓ2�T �!�D\r��,K�\"�%�H�qR\r�̠��C =�������<c�\n#<�5�M� �E��y�������o\"�cJKL2�&��eR��W�AΐTw�ё;�J���\\`)5��ޜB�qhT3��R	�'\r+\":�8��tV�A�+]��S72��Y�F��Z85�c,���J��/+S�nBpoW�d��\"�Q��a�ZKp�ާy\$�����4�I�@L'@�xC�df�~}Q*�ҺA��Q�\"B�*2\0�.��kF�\"\r��� �o�\\�Ԣ���VijY��M��O�\$��2�ThH����0XH�5~kL���T*:~P��2�t���B\0�Y������j�vD�s.�9�s��̤�P�*x���b�o����P�\$�W/�*��z';��\$�*����d�m�Ã�'b\r�n%��47W�-�������K���@<�g�èbB��[7�\\�|�VdR��6leQ�`(Ԣ,�d��8\r�]S:?�1�`��Y�`�A�ғ%��ZkQ�sM�*���{`�J*�w��ӊ>�վ�D���>�eӾ�\"�t+po������W\$����Q�@��3t`����-k7g��]��l��E��^dW>nv�t�lzPH��FvW�V\n�h;��B�D�س/�:J��\\�+ %�����]��ъ��wa�ݫ���=��X��N�/��w�J�_[�t)5���QR2l�-:�Y9�&l R;�u#S	� ht�k�E!l���>SH��X<,��O�YyЃ%L�]\0�	��^�dw�3�,Sc�Qt�e=�M:4���2]��P�T�s��n:��u>�/�d�� ��a�'%����qҨ&@֐���H�G�@w8p����΁�Z\n��{�[�t2���a��>	�w�J�^+u~�o��µXkզBZk˱�X=��0>�t��lŃ)Wb�ܦ��'�A�,��m�Y�,�A���e��#V��+�n1I����E�+[����[��-R�mK9��~���L�-3O���`_0s���L;�����]�6��|��h�V�T:��ޞerM��a�\$~e�9�>����Д�\r��\\���J1Ú���%�=0{�	����|ޗtڼ�=���Q�|\0?��[g@u?ɝ|��4�*��c-7�4\ri'^���n;�������(���{K�h�nf���Zϝ}l�����]\r��pJ>�,gp{�;�\0��u)��s�N�'����H��C9M5��*��`�k�㬎����AhY��*����jJ�ǅPN+^� D�*��À���D��P���LQ`O&��\0�}�\$���6�Zn>��0� �e��\n��	�trp!�hV�'Py�^�*|r%|\nr\r#���@w����T.Rv�8�j�\nmB���p�� �Y0�Ϣ�m\0�@P\r8�Y\rG��d�	�QG�P%E�/@]\r���{\0�Q����bR M\rF��|��%0SDr�����f/����\":�mo�ރ�%�@�3H�x\0�l\0���	��W����\n�8\r\0}�@�D��`#�t��.�jEoDrǢlb����t�f4�0���%�0���k�z2\r� �W@�%\r\n~1��X����D2!��O�*���{0<E��k*m�0ı���|\r\n�^i��� ��!.�r � ��������f��Ĭ��+:��ŋJ�B5\$L���P���LĂ�� Z@����`^P�L%5%jp�H�W��on��kA#&���8��<K6�/����̏������XWe+&�%���c&rj��'%�x�����nK�2�2ֶ�l��*�.�r��΢���*�\r+jp�Bg�{ ���0�%1(���Z�`Q#�Ԏ�n*h��v�B����\\F\n�W�r f\$�93�G4%d�b�:JZ!�,��_��f%2��6s*F���Һ�EQ�q~��`ts�Ҁ���(�`�\r���#�R����R�r��X��:R�)�A*3�\$l�*ν:\"Xl��tbK�-��O>R�-�d��=��\$S�\$�2��}7Sf��[�}\"@�]�[6S|SE_>�q-�@z`�;�0��ƻ��C�*��[���{D��jC\nf�s�P�6'���ȕ QE���N\\%r�o�7o�G+dW4A*��#TqE�f��%�D�Z�3��2.��Rk��z@��@�E�D�`C�V!C��ŕ\0���I�)38��M3�@�3L��ZB�1F@L�h~G�1M���6��4�Xє�}ƞf�ˢIN��34��X�Btd�8\nbtN��Qb;�ܑD��L�\0��\"\n����V��6��]U�cVf���D`�M�6�O4�4sJ��55�5�\\x	�<5[F�ߵy7m�)@SV��Ğ#�x��8 ոы��`�\\`�-�v2���p���+v���U��L�xY.����\0005(�@��ⰵ[U@#�VJuX4�u_�\"JO(Dt�_	5s�^���������5�^�^V�I��\rg&]��\r\"ZCI�6��#��\r��ܓ��]7���q�0��6}o���`u��ab(�X�D�f�M�N)�V�UUF�о��=jSWi�\"\\B1Ğ�E0� �amP��&<�O_�L���.c�1Z*��R\$�h���mv�[v>ݭ�p����(��0����cP�om\0R��p�&�w+KQ�s6�}5[s�J���2��/���O �V*)�R�.Du33�F\r�;��v4���H�	_!��2��k����+��%�:�_,�eo��F��AJ�O�\"%�\n�k5`z %|�%�Ϋg|��}l�v2n7�~\0�	�YRH��@��r��xN-Jp\0���f#��@ˀmv�x��\r���2WMO/�\nD��7�}2���VW�W��wɀ7����H�k���]�\$�Mz\\�e�.f�RZ�a�B���Qd�KZ��vt���w4�\0�Z@�	��Bc;�b��>�B�	3m�n\n�o��J3��k�(܍���\"�yG\$:\r�ņ�ݎ��G6�ɲJ��y��Q�\\Q��if�����(�m)/r�\$�J�/�H�]*���g�ZOD�Ѭ��]1�g22������f�=HT��]N�&���M\0�[8x�ȮE��8&L�Vm�v����j�ט�F��\\��	���&s�@Q� \\\"�b��	��\rBs�Iw�	�Yɜ�N �7�C/&٫`�\n\n��[k���*A���T�V*UZtz{�.��y�S���#�3�ipzW@yC\nKT��1@|�z#���_CJz(B�,V�(K�_��dO���P�@X��t�Ѕ��c;�WZzW�_٠�\0ފ�CF�xR �	�\n������P�A��&������,�pfV|@N�\"�\$�[�i����������Z�\0Zd\\\"�|�W`��]��tz�o\$�\0[����u�e���ə�bhU-��,�r �Lk8��֫�V&�al����d��2;	�'-��Jyu��a���\0����a��{s�[9V\0��F��R �VB0S;D�>L4�&�ZHO1�\0�wg��S�tK��R�z���i��+�3�w��z�X�]�(G\$����D+�tչ�(#����oc�:	��Y6�\0��&��	@�	���)��!����w���# t�x�ND�����)��C��FZ�p��a��*F�b�	��ͼ����ģ�����Si/S�!��z�UH*�4����0�K�-�/���-k`�n�Li�J�~�w�Jn��\"�`�=��V�3Oį8t�>��vo��E.��Rz`��p�P���E\\��ɧ�3L�l�ѥs]T���oV��\n��	*�\r�@7)��D�m�0W�5Ӏ��ǰ�w��b���|	��JV����\"�ur\r�&N0N�B�d��d�8�D��_ͫ�^T��H#]�d�+�v�~�U,�PR%�����x���fA��C��m����͸����c��yŜD)���uH���p�p�^u\0�����}�{ѡ�\rg�s�QM�Y�2j�\r�|0\0X��@q���I`��5F�6�N��V@ӔsE�p���#\r�P�T��DeW�ؼ񛭁��z!û�:�DMV(��~X���9�\0�@���40N�ܽ~�Q�[T���e�qSv\"�\"h�\0R-�hZ�d����F5�P��`�9�D&xs9W֗5Er@o�wkb�1��PO-O�OxlH�D6/ֿ�m�ޠ��3�7T��K�~54�	�p#�I�>YIN\\5���NӃ����M��pr&�G�xM�sq����.F���8�Cs�� h�e5������*�b�)Sڪ��̭�e�0�-X� {�5|�i�֢a��ȕ6z�޽��/Y���ێM� ƃ� �\nR*8r o� @7�8Bf�z�K�r���A\$˰	p�\0?���d�k�|45}�A����ɶ�W��J�2k Gi\0\"����d���8�\0�>m��� `8�w�7�o4�cGh��Q�(퀨�8@\$<\0p��0���L�eX+�Ja�{�B��h��8�Cy���P2��Ӯ�*�EH�2���DqS�ۘ�p�0�I���k�`��S�\n�:��B�7����{-����`����6�A�W�ܖ\r�p�W#���?���{\0������cD��[<����f�--�pԌ�*B�]�nW��^��R70\r�+N�GN�\$(\0�#+y�@�@iD(8@\r�h��H�He����zz�{1���h��W1F�Who&aɜ�d6���jw�������`h�{v`RE�\nj���`�ܷ����*���ʸ}�Y��	\rY�H�6�#\0�廆��a�� Q�HEl4�d���p��#�������o�br+_)\r`��!�|dQ�>��=Qʡ��ζ�EOB'�>�P��Ӷ� A\rnK�i�� 	�����	�%<	�o;�S�@�!	�x��:���A�+\\1d\$�jO��7�%�	�/����gu�z*�G�H�5\"8��,�]raq���/�h��#����\$ /tn��8y��-�O���H�b���<�Z�!���1��`�.(uo����|`GːS��BaM	ڂ9ƞ�D@���1�B�tD��ʡ@?o�(H��qC��8E�TcncR��6�N%�rHj��2G\0�a��q �r��z9b>(P��x��<��)�x#�8�誹t���h�2v��Wo2U���t��+=�l#���j�D�	0����&R�c�\$�*̑-Z`��\r��;�|A�p�=1�	1����ƈ�bEv(^�X�P2=\0}�W���G�<���G�����R�#P�Hܮr9	��Y��!�LB���4�NC�Z��IC���MLm��,�f@eY�x�BS(�+��<4Y�)-�\r�z?\$���\"\"�� 6�E�\r)z���@ȑ��r����*��J�윋��%\$�e�J���\0A�\$ڰ/5��B0S���x��I�Q)�<��4YS�&�{��b�+IG=>�\r�PY`Z�D�`��U����F1���4d8X(����C%�`�㜭0�I\$�7W�pǁ,��Ac���&Ԍ�p\$�:�>]�.�VY��\$p� ��]��`�;��e�\0�0�\n��K+�@DL�S��r(on�M\0@9��%�\"�WS�\"���� 䥙�ٍ�ػj�_J-��rʜ���5�\\�2�5>Ze\"0��%9y��^�WMax&a)D�L���2Q����t?�=,�/o�f�3I�J�\$\r;���7�}�\r�W�@�Ұ�M|\r�Y���]5���\\*s:��FV!���kن�R���L3L�	��52�M�sb�\$����7�\0l�y���&� 9�|m!��0J��4��TSd���G���nK�V:l�D'/��:Zs��\n��y�%��i����,@ҲL��j1<��3Ĩ�D2/;��'Pݻ����`���qKȰ�f�I�L� Dݬ�4�3 ��OH�J�	q�&�����X��!��r)F�Xx���^QwOP��h��՞-_�>�a����(	��x%��K�b�<�E�j7�������hHt�`�.r�P���x��\"{\0006CVQE�&��>�ޅ�w����e'?B�9x�>:\"�73���xT\0e����j	��[t�Ҝ\"�(\\K�e�z�r����e> ���\0002�hʇ��X�a<�JtU�z`�達?��#�����2-��4hFY|C��\"M�yƔKd ���E�7���+(U�ʖX�� /D���)�\"����بމjoh�Fz4�t���D׌�G��RZ�ć�ȿ\0�FV4Q�6v�b�i=G�;Ϭ�k�d+\n>�E��\0�2f{����!J��Q�J�ؘ9��(2�#\\Z��,��Qܥ�3?8`�	bwR6��\n*�㋀�ƒ�(t��L*�S�d�\0x�)�(�*�wH]7O�N�v(Гdg�q	\nLp��L�N��H@�1����M �		n��z���e4!!	��'槝-t���AQP���L,����7��\\�i����^�\$�,�|�Z��(S9���\n* +��T�D�z?(T�>��L��æ��R����\$�zдi̼W�ͨ�Ds�{)�@�����	v�P��g�qIVҨ����\n )�!�8|\$pZ�*�!7A����N��j�NW����U���Q���)�eF�UA�S�x\0[N���2���X :S�T�~�S*T4	�3��]9�F���]:�KUg;��*Ay�a��1j|8Ϋ����I�MR��Vh7uU���r,�h�%<q�R@N9�ާ�k�	�B|�����8��r������DР@\"�ɋ�z\r������O�_���Q�\0\0���|�]�f�\nz�����UeH�Ą/k+�TF?��*03�!�\0��I���t	f\0(S�U��ZA�F��1\0��k�]��WZN�Q��܂���%��x1���'��!-,�Ƕvzg��#�Gh�;f�PH�9Bj�u�\n�A�VR����1K+�MN!��Sμ��Y��vdZ\\,���g٨�����\"}W��Yɵ�t�P��g�,�����	\0b�-�hB/@�̎�/�M��J���Y\0����)\n��I�?v�	��Ȕ1��\$�(�w\r+�n ��s�s�QfQ�O�P�.D���bV\0-�J<�i;[���=#���n,j?)�\"���lYL.����A::������BxOF7����`���d��}�}=�i)@к��\$ q˷(y%��huzb2�3Ƨ��.�-h�oO����\0`���VZ��&y�t9C���鋭Z��ґ�Z!�X�U����.k��V#8�G�}�Q���u8cΫt�bE>�v��{@{QP]<�ary��j\\��\$j�x�nc6k�;qs�T���K�����jJ���n\\C��{���`g�6�5�Rk�t����s�|@�_0΅5:B�3����rѡ�&�㴸�\0����&�׈�����ԡ��SXʕ�G�m�ʶWr,j�q\0\$޺sW�P�.A\n4�9(u�.���l�V�Ju�Ԍ�+�A�uC�>hl6��2���G�e���N��n�=�'���~��Þ��PҀ�%0z�u��r�\0��9uE�s\"���\\�ט���^���(3ՑS%<+�9��Ծ����\0���~'̞�֓<+�,i�:��@��N���\$�o������� �]������Z�!��]�n,��x��>_�f��W\0006��%�}I�\nh߀w�����ǃ -��H@_�Vi�����{���R��^�۔}5�b,!5���H��p/��k<��<�jh|i��k��hLv݄\n�`�[���WC6��z\n�g��r��u=��!zCţ���e#��nj��\0`^;=E�*@�y�% ��LQe���2�A�1,��C�ix�t����G�]q�O(����\n�V9dr�D'5@x\$�r6��;\"ǣ���7�\0M0ņH_#�c�pn>��<aa�q@g�2��lm-��������8��?8��7p����>��ji���N�\$#E/�0��s\n�B\r�*��z��oyn[Ι�� 6�a����g8�qC��⼜�I�rNF�ȫ�1��70�����/i(�B�0����Z��(��+S�J�,��91/Y+jxӱF���A��k�f�Jee\r�Cͳrz�m���h@9�O�� ؝��GK�Ad���OH���=���<&`��K�PA�!WO;-�X�L��m�Kz�7-e[u��p�q���o/�`�C����KX�f�i��Y7=�M�/�F�R�۔T�d��Y\"=`�1�k�1Տh�\r���f@N��z�(@����	h�\0�����I�}PJKr���pR`x������fo���(A��[��19�(&jo<��I@p	@�����,y�	nIs�^Ўѫ:Y��vc���؏9q.C��8�bW��V?��҅�9�\$u�@5#S(4Y���K���6�!��N6<��|v1��3ʊ:��!����`��M��l����f`�Z��J=��GX�Y)_l�А�T�)P��`�%��:�!Z\"lYS�Uؤ(��Y1Z�니rv)F`�K~=Y>���S���c����!l���D����BrF\$��RA:�\\�P�4�V�R6<�O�S�_BCS+����'V��2T#Lc�F�NBD%�G�W�nR�S����I��\n'k�0���O��Ў����8rݯAS�?��xm��yv���a�b��Ͱ�,��ЅA������]pJ\\\\�Xi���Eu��B)���Z@Ώ \"��gg0{��n��'APR��٨v�~�0R�w쀱\"�������H�J���Ζ�\\�\r}i?�Ғ:��2���g��{I�3)��B��͙Z�s��`.�#2�vt�X�IGU>`)�%���(|�f<Κ_�ޯ���_G�<��_ ˟������[:�6G8��l�#J(��JC���`���wF�w\"b�!,��!�r�@�K(���\n@AsV��S�ֹ�4�_\ns٠eڋj��)&�3�{��k���Q���G�c��X^�L{�C\n�m����A��D��1O?(��(�����2\"UL��+#o��@���X�\0�٭���^n_p�eQ˙X}%��*��e�m�{�GN��Xl�q�]R\\Z�v!�) ���xd΀,�cK��鮇�m���I~����K�{+��Gݥ�=@Q��,1!aEOc��#6<u��rB�\n�Ȳ��dH�t����	�{C�<x3���H��1��K�wB�\0��u����'ӆQ�^���򕥂�i�rRv�Vɷ�lS�.O)����[��xS�t���c)���k�B��+��v���B��w�.�wC���2���2d�.H��p+a\\H��[�\$}nNN7��H�.�S\r�ȒT���w�	*H�g\\��\$�,�:KBOx��>����5����Ӷ����u2��n��`��Yq�D���xwMB�n�2>���G�ڄ����YaK�w(2`����w����1m�-:�&LD8�U��8l��\\<���	��z�a����:,��K'�%7:���M����U[���*;K���j�;/wG���\n���^�eV'��,��;��B6�G�1��OKW����(i�X\np�Cکc6�^��㷀=�^ûcQ��Rp`\$	�D(\0D�>{�ET�c��I\r{���\$o�R	�ZZ�4*��??�+j���n��Q`����X�3�	\$���M�\n׉w�\"d�W���~@�'�I�᭫�0+-��w����y�6�vȽ'�Ԇ:Y)Y0\0�*)?'��Ǟv����fI�\n��z�9�.�b��!�c�E�[��F麙ks�}��Bv�g�5�V���,)J\$��j�Z�J�\$�Y��ח9�\0�\n����.^J��ڋ�b��mI0:g��������˗ATP�I�]~!��;D�����	�z��<P�Q>�m���`��?%Y��T\n\0D\0�\0'���H@0`�<׭�10�(�m�-��ɞ7A\0�~�~ꁡĤ?t�hє.w�%)0	#c����\"�c����jfW��\0\0p��C���kC��8��85+i:��[�8�b��l�[\"����5S�y\0�����*�Q�6V�s�9��7!�;\"��c�)�O�Q,��Ա��\r�7�,*�0�aQ�u?�_C|�������R(o(��<j(��Tv��\r|_\"�3��m��S7D�!׸�h�|���(�&�@:�	\"-ގ��&Mu;�,�bк=p�>A6ɭ���7���- WW9�O,�o'�v2�<�3\0���h��@`� 3TX�Ϛ|�\"FC_��~x����`��'f�Q-4�����/�`'���=A�\$>��`P��_G(���E���&/J�I�v�'�m餧zpޞFo�	�/[��i�؋�G*���y�(�<��7q�Y�.�眪��B���\r�l�r\nUnƧ��T>�������	�Q���_�|����K��8�ډ�e��_��xz�x�L���p14��d����U#4t�K���\$�!����p�w����Zx��_����i5T?}��C�{�����h/Gzj\$.B�Ҩ�=#�Ϗ|��*����I��w/��a�x`*��*���]����>a?'}FJS���ԖA0��'�����ʟ�0:63���л��n'��U/�r�|=slb0�\0W�rB�ʤ���@T��~\$����H�����	��D\\���-���(��ᩖB�M���z+�%�(��i��㹃�I���5/�.y/���\$�{Q}p�ܻdI�\\�Վ�B�\0V0�B�9�{T\$n�8\$Z�e�Pĳ���%9�&���V��b�x}g\"%h���*ٸvOw�˾�/�o�L,���=��V��5Bg� ϶�3��>�~�`\nxi�\"��v@�����nף�ϳyac�G�'%[��4`n��47!5�ހr����ɉ��>z�(Y�t��0���V���P�ZXT`2�~Cl���[o�n�t8jB\0d�\0000��V��g�����@V!�h\0006d<��=[�W�����f�@pb��a��ټ�s;���G<�~a�?�N�L����\"(���?�%�x#�7�|S��O�Ɠ)�B4��+��*�!��)6#�+?'���(X�����JO\0��");
    } elseif ($_GET["file"] == "jush.js") {
        header("Content-Type: text/javascript; charset=utf-8");
        echo lzw_decompress("v0��F����==��FS	��_6MƳ���r:�E�CI��o:�C��Xc��\r�؄J(:=�E���a28�x�?�'�i�SANN���xs�NB��Vl0���S	��Ul�(D|҄��P��>�E�㩶yHch��-3Eb�� �b��pE�p�9.����~\n�?Kb�iw|�`��d.�x8EN��!��2��3���\r���Y���y6GFmY�8o7\n\r�0��\0�Dbc�!�Q7Шd8���~��N)�Eг`�Ns��`�S)�O���/�<�x�9�o�����3n��2�!r�:;�+�9�CȨ���\n<�`��b�\\�?�`�4\r#`�<�Be�B#�N ��\r.D`��j�4���p�ar��㢺�>�8�\$�c��1�c���c����{n7����A�N�RLi\r1���!�(�j´�+��62�X�8+����.\r����!x���h�'��6S�\0R����O�\n��1(W0���7q��:N�E:68n+��մ5_(�s�\r��/m�6P�@�EQ���9\n�V-���\"�.:�J��8we�q�|؇�X�]��Y X�e�zW�� �7��Z1��hQf��u�j�4Z{p\\AU�J<��k��@�ɍ��@�}&���L7U�wuYh��2��@�u� P�7�A�h����3Û��XEͅZ�]�l�@Mplv�)� ��HW���y>�Y�-�Y��/�������hC�[*��F�#~�!�`�\r#0P�C˝�f������\\���^�%B<�\\�f�ޱ�����&/�O��L\\jF��jZ�1�\\:ƴ>�N��XaF�A�������f�h{\"s\n�64������?�8�^p�\"띰�ȸ\\�e(�P�N��q[g��r�&�}Ph���W��*��r_s�P�h���\n���om������#���.�\0@�pdW �\$Һ�Q۽Tl0� ��HdH�)��ۏ��)P���H�g��U����B�e\r�t:��\0)\"�t�,�����[�(D�O\nR8!�Ƭ֚��lA�V��4�h��Sq<��@}���gK�]���]�=90��'����wA<����a�~��W��D|A���2�X�U2��yŊ��=�p)�\0P	�s��n�3�r�f\0�F���v��G��I@�%���+��_I`����\r.��N���KI�[�ʖSJ���aUf�Sz���M��%��\"Q|9��Bc�a�q\0�8�#�<a��:z1Uf��>�Z�l������e5#U@iUG��n�%Ұs���;gxL�pP�?B��Q�\\�b��龒Q�=7�:��ݡQ�\r:�t�:y(� �\n�d)���\n�X;����CaA�\r���P�GH�!���@�9\n\nAl~H���V\ns��ի�Ư�bBr���������3�\r�P�%�ф\r}b/�Α\$�5�P�C�\"w�B_��U�gAt��夅�^Q��U���j���Bvh졄4�)��+�)<�j^�<L��4U*���Bg�����*n�ʖ�-����	9O\$��طzyM�3�\\9���.o�����E(i������7	tߚ�-&�\nj!\r��y�y�D1g���]��yR�7\"������~����)TZ0E9M�YZtXe!�f�@�{Ȭyl	8�;���R{��8�Į�e�+UL�'�F�1���8PE5-	�_!�7��[2�J��;�HR��ǹ�8p痲݇@��0,ծpsK0\r�4��\$sJ���4�DZ��I��'\$cL�R��MpY&����i�z3G�zҚJ%��P�-��[�/x�T�{p��z�C�v���:�V'�\\��KJa��M�&���Ӿ\"�e�o^Q+h^��iT��1�OR�l�,5[ݘ\$��)��jLƁU`�S�`Z^�|��r�=��n登��TU	1Hyk��t+\0v�D�\r	<��ƙ��jG���t�*3%k�YܲT*�|\"C��lhE�(�\r�8r��{��0����D�_��.6и�;����rBj�O'ۜ���>\$��`^6��9�#����4X��mh8:��c��0��;�/ԉ����;�\\'(��t�'+�����̷�^�]��N�v��#�,�v���O�i�ϖ�>��<S�A\\�\\��!�3*tl`�u�\0p'�7�P�9�bs�{�v�{��7�\"{��r�a�(�^��E����g��/���U�9g���/��`�\nL\n�)���(A�a�\" ���	�&�P��@O\n師0�(M&�FJ'�! �0�<�H�������*�|��*�OZ�m*n/b�/�������.��o\0��dn�)����i�:R���P2�m�\0/v�OX���Fʳψ���\"�����0�0�����0b��gj��\$�n�0}�	�@�=MƂ0n�P�/p�ot������.�̽�g\0�)o�\n0���\rF����b�i��o}\n�̯�	NQ�'�x�Fa�J���L������\r��\r����0��'��d	oep��4D��ʐ�q(~�� �\r�E��pr�QVFH�l��Kj���N&�j!�H`�_bh\r1���n!�Ɏ�z�����\\��\r���`V_k��\"\\ׂ'V��\0ʾ`AC������V�`\r%�����\r����k@N����B�횙� �!�\n�\0Z�6�\$d��,%�%la�H�\n�#�S\$!\$@��2���I\$r�{!��J�2H�ZM\\��hb,�'||cj~g�r�`�ļ�\$���+�A1�E���� <�L��\$�Y%-FD��d�L焳��\n@�bVf�;2_(��L�п��<%@ڜ,\"�d��N�er�\0�`��Z��4�'ld9-�#`��Ŗ����j6�ƣ�v���N�͐f��@܆�&�B\$�(�Z&���278I ��P\rk\\���2`�\rdLb@E��2`P( B'�����0�&��{���:��dB�1�^؉*\r\0c<K�|�5sZ�`���O3�5=@�5�C>@�W*	=\0N<g�6s67Sm7u?	{<&L�.3~D��\rŚ�x��),r�in�/��O\0o{0k�]3>m��1\0�I@�9T34+ԙ@e�GFMC�\rE3�Etm!�#1�D @�H(��n ��<g,V`R]@����3Cr7s~�GI�i@\0v��5\rV�'������P��\r�\$<b�%(�Dd��PW����b�fO �x\0�} ��lb�&�vj4�LS��ִԶ5&dsF M�4��\".H�M0�1uL�\"��/J`�{�����xǐYu*\"U.I53Q�3Q��J��g��5�s���&jь��u�٭ЪGQMTmGB�tl-c�*��\r��Z7���*hs/RUV����B�Nˈ�����Ԋ�i�Lk�.���t�龩�rYi���-S��3�\\�T�OM^�G>�ZQj���\"���i��MsS�S\$Ib	f���u����:�SB|i��Y¦��8	v�#�D�4`��.��^�H�M�_ռ�u��U�z`Z�J	e��@Ce��a�\"m�b�6ԯJR���T�?ԣXMZ��І��p����Qv�j�jV�{���C�\r��7�Tʞ� ��5{P��]�\r�?Q�AA������2񾠓V)Ji��-N99f�l Jm��;u�@�<F�Ѡ�e�j��Ħ�I�<+CW@�����Z�l�1�<2�iF�7`KG�~L&+N��YtWH飑w	����l��s'g��q+L�zbiz���Ţ�.Њ�zW�� �zd�W����(�y)v�E4,\0�\"d��\$B�{��!)1U�5bp#�}m=��@�w�	P\0�\r�����`O|���	�ɍ����Y��JՂ�E��Ou�_�\n`F`�}M�.#1��f�*�ա��  �z�uc���� xf�8kZR�s2ʂ-���Z2�+�ʷ�(�sU�cD�ѷ���X!��u�&-vP�ر\0'L�X �L����o	��>�Վ�\r@�P�\rxF��E��ȭ�%����=5N֜��?�7�N�Å�w�`�hX�98 �����q��z��d%6̂t�/������L��l��,�Ka�N~�����,�'�ǀM\rf9�w��!x��x[�ϑ�G�8;�xA��-I�&5\$�D\$���%��xѬ���´���]����&o�-3�9�L��z���y6�;u�zZ ��8�_�ɐx\0D?�X7����y�OY.#3�8��ǀ�e�Q�=؀*��G�wm ���Y�����]YOY�F���)�z#\$e��)�/�z?�z;����^��F�Zg�����������`^�e����#�������?��e��M��3u�偃0�>�\"?��@חXv�\"������*Ԣ\r6v~��OV~�&ר�^g���đٞ�'��f6:-Z~��O6;zx��;&!�+{9M�ٳd� \r,9���W��ݭ:�\r�ٜ��@睂+��]��-�[g��ۇ[s�[i��i�q��y��x�+�|7�{7�|w�}����E��W��Wk�|J؁��xm��q xwyj���#��e��(�������ߞþ��� {��ڏ�y���M���@��ɂ��Y�(g͚-��������J(���@�;�y�#S���Y��p@�%�s��o�9;�������+��	�;����ZNٯº��� k�V��u�[�x��|q��ON?���	�`u��6�|�|X����س|O�x!�:���ϗY]�����c���\r�h�9n�������8'������\rS.1��USȸ��X��+��z]ɵ��?����C�\r��\\����\$�`��)U�|ˤ|Ѩx'՜����<�̙e�|�ͳ����L���M�y�(ۧ�l�к�O]{Ѿ�FD���}�yu��Ē�,XL\\�x��;U��Wt�v��\\OxWJ9Ȓ�R5�WiMi[�K��f(\0�dĚ�迩�\r�M����7�;��������6�KʦI�\r���xv\r�V3���ɱ.��R������|��^2�^0߾\$�Q��[�D��ܣ�>1'^X~t�1\"6L���+��A��e�����I��~����@����pM>�m<��SK��-H���T76�SMfg�=��GPʰ�P�\r��>�����2Sb\$�C[���(�)��%Q#G`u��Gwp\rk�Ke�zhj��zi(��rO�������T=�7���~�4\"ef�~�d���V�Z���U�-�b'V�J�Z7���)T��8.<�RM�\$�����'�by�\n5����_��w����U�`ei޿J�b�g�u�S��?��`���+��� M�g�7`���\0�_�-���_��?�F�\0����X���[��J�8&~D#��{P���4ܗ��\"�\0��������@ғ��\0F ?*��^��w�О:���u��3xK�^�w���߯�y[Ԟ(���#�/zr_�g��?�\0?�1wMR&M���?�St�T]ݴG�:I����)��B�� v����1�<�t��6�:�W{���x:=��ޚ��:�!!\0x�����q&��0}z\"]��o�z���j�w�����6��J�P۞[\\ }��`S�\0�qHM�/7B��P���]FT��8S5�/I�\r�\n ��O�0aQ\n�>�2�j�;=ڬ�dA=�p�VL)X�\n¦`e\$�TƦQJ����lJ����y�I�	�:����B�bP���Z��n����U;>_�\n	�����`��uM򌂂�֍m����Lw�B\0\\b8�M��[z��&�1�\0�	�\r�T������+\\�3�Plb4-)%Wd#\n��r��MX\"ϡ�(Ei11(b`@f����S���j�D��bf�}�r����D�R1���b��A��Iy\"�Wv��gC�I�J8z\"P\\i�\\m~ZR��v�1ZB5I��i@x����-�uM\njK�U�h\$o��JϤ!�L\"#p7\0� P�\0�D�\$	�GK4e��\$�\nG�?�3�EAJF4�Ip\0��F�4��<f@� %q�<k�w��	�LOp\0�x��(	�G>�@�����9\0T����GB7�-�����G:<Q��#���Ǵ�1�&tz��0*J=�'�J>���8q��Х���	�O��X�F��Q�,����\"9��p�*�66A'�,y��IF�R��T���\"��H�R�!�j#kyF���e��z�����G\0�p��aJ`C�i�@�T�|\n�Ix�K\"��*��Tk\$c��ƔaAh��!�\"�E\0O�d�Sx�\0T	�\0���!F�\n�U�|�#S&		IvL\"����\$h���EA�N\$�%%�/\nP�1���{��) <���L���-R1��6���<�@O*\0J@q��Ԫ#�@ǵ0\$t�|�]�`��ĊA]���Pᑀ�C�p\\pҤ\0���7���@9�b�m�r�o�C+�]�Jr�f��\r�)d�����^h�I\\�. g��>���8���'�H�f�rJ�[r�o���.�v���#�#yR�+�y��^����F\0᱁�]!ɕ�ޔ++�_�,�\0<@�M-�2W���R,c���e2�*@\0�P ��c�a0�\\P���O���`I_2Qs\$�w��=:�z\0)�`�h�������\nJ@@ʫ�\0�� 6qT��4J%�N-�m����.ɋ%*cn��N�6\"\r͑�����f�A���p�MۀI7\0�M�>lO�4�S	7�c���\"�ߧ\0�6�ps�����y.��	���RK��PAo1F�tI�b*��<���@�7�˂p,�0N��:��N�m�,�xO%�!��v����gz(�M���I��	��~y���h\0U:��OZyA8�<2����us�~l���E�O�0��0]'�>��ɍ�:���;�/��w�����'~3GΖ~ӭ����c.	���vT\0c�t'�;P�\$�\$����-�s��e|�!�@d�Obw��c��'�@`P\"x����0O�5�/|�U{:b�R\"�0�шk���`BD�\nk�P��c��4�^ p6S`��\$�f;�7�?ls��߆gD�'4Xja	A��E%�	86b�:qr\r�]C8�c�F\n'ьf_9�%(��*�~��iS����@(85�T��[��Jڍ4�I�l=��Q�\$d��h�@D	-��!�_]��H�Ɗ�k6:���\\M-����\r�FJ>\n.��q�eG�5QZ����' ɢ���ہ0��zP��#������r���t����ˎ��<Q��T��3�D\\����pOE�%)77�Wt�[��@����\$F)�5qG0�-�W�v�`�*)Rr��=9qE*K\$g	��A!�PjBT:�K���!��H� R0?�6�yA)B@:Q�8B+J�5U]`�Ҭ��:���*%Ip9�̀�`KcQ�Q.B��Ltb��yJ�E�T��7���Am�䢕Ku:��Sji� 5.q%LiF��Tr��i��K�Ҩz�55T%U��U�IՂ���Y\"\nS�m���x��Ch�NZ�UZ���( B��\$Y�V��u@蔻����|	�\$\0�\0�oZw2Ҁx2���k\$�*I6I�n�����I,��QU4�\n��).�Q���aI�]����L�h\"�f���>�:Z�>L�`n�ض��7�VLZu��e��X����B���B�����Z`;���J�]�����S8��f \nڶ�#\$�jM(��ޡ����a�G��+A�!�xL/\0)	C�\n�W@�4�����۩� ��RZ����=���8�`�8~�h��P ��\r�	���D-FyX�+�f�QSj+X�|��9-��s�x�����+�V�cbp쿔o6H�q�����@.��l�8g�YM��WMP��U��YL�3Pa�H2�9��:�a�`��d\0�&�Y��Y0٘��S�-��%;/�T�BS�P�%f������@�F�(�֍*�q +[�Z:�QY\0޴�JUY֓/���pkzȈ�,�𪇃j�ꀥW�״e�J�F��VBI�\r��pF�Nقֶ�*ը�3k�0�D�{����`q��ҲBq�e�D�c���V�E���n����FG�E�>j�����0g�a|�Sh�7u�݄�\$���;a��7&��R[WX���(q�#���P���ז�c8!�H���VX�Ď�j��Z������Q,DUaQ�X0��ը���Gb��l�B�t9-oZ���L���­�pˇ�x6&��My��sҐ����\"�̀�R�IWU`c���}l<|�~�w\"��vI%r+��R�\n\\����][��6�&���ȭ�a�Ӻ��j�(ړ�Tѓ��C'��� '%de,�\n�FC�эe9C�N�Ѝ�-6�Ueȵ��CX��V������+�R+�����3B��ڌJ�虜��T2�]�\0P�a�t29��(i�#�aƮ1\"S�:�����oF)k�f���Ъ\0�ӿ��,��w�J@��V򄎵�q.e}KmZ����XnZ{G-���ZQ���}��׶�6ɸ���_�؁Չ�\n�@7�` �C\0]_ ��ʵ����}�G�WW: fCYk+��b۶���2S,	ڋ�9�\0﯁+�W�Z!�e��2������k.Oc��(v̮8�DeG`ۇ�L���,�d�\"C���B-�İ(����p���p�=����!�k������}(���B�kr�_R�ܼ0�8a%ۘL	\0���b������@�\"��r,�0T�rV>����Q��\"�r��P�&3b�P��-�x���uW~�\"�*舞�N�h�%7���K�Y��^A����C����p����\0�..`c��+ϊ�GJ���H���E����l@|I#Ac��D��|+<[c2�+*WS<�r��g���}��>i�݀�!`f8�(c����Q�=f�\n�2�c�h4�+q���8\na�R�B�|�R����m��\\q��gX����ώ0�X�`n�F���O p��H�C��jd�f��EuDV��bJɦ��:��\\�!mɱ?,TIa���aT.L�]�,J��?�?��FMct!a٧R�F�G�!�A���rr�-p�X��\r��C^�7���&�R�\0��f�*�A\n�՛H��y�Y=���l�<��A�_��	+��tA�\0B�<Ay�(fy�1�c�O;p���ᦝ`�4СM��*��f�� 5fvy {?���:y��^c��u�'���8\0��ӱ?��g��� 8B��&p9�O\"z���rs�0��B�!u�3�f{�\0�:�\n@\0����p���6�v.;�����b�ƫ:J>˂��-�B�hkR`-����aw�xEj����r�8�\0\\����\\�Uhm� �(m�H3̴�S����q\0��NVh�Hy�	��5�M͎e\\g�\n�IP:Sj�ۡٶ�<���x�&�L��;nfͶc�q��\$f�&l���i�����0%yΞ�t�/��gU̳�d�\0e:��h�Z	�^�@��1��m#�N��w@��O��zG�\$�m6�6}��ҋ�X'�I�i\\Q�Y���4k-.�:yz���H��]��x�G��3��M\0��@z7���6�-DO34�ދ\0Κ��ΰt\"�\"vC\"Jf�Rʞ��ku3�M��~����5V ��j/3���@gG�}D���B�Nq��=]\$�I��Ӟ�3�x=_j�X٨�fk(C]^j�M��F��ա��ϣCz��V��=]&�\r�A<	������6�Ԯ�״�`jk7:g��4ծ��YZq�ftu�|�h�Z��6��i〰0�?��骭{-7_:��ސtѯ�ck�`Y��&���I�lP`:�� j�{h�=�f	��[by��ʀoЋB�RS���B6��^@'�4��1U�Dq}��N�(X�6j}�c�{@8���,�	�PFC���B�\$mv���P�\"��L��CS�]����E���lU��f�wh{o�(��)�\0@*a1G� (��D4-c��P8��N|R���VM���n8G`e}�!}���p�����@_���nCt�9��\0]�u��s���~�r��#Cn�p;�%�>wu���n�w��ݞ�.���[��hT�{��值	�ˁ��J���ƗiJ�6�O�=������E��ٴ��Im���V'��@�&�{��������;�op;^��6Ŷ@2�l���N��M��r�_ܰ�Í�` �( y�6�7�����ǂ��7/�p�e>|��	�=�]�oc����&�xNm���烻��o�G�N	p����x��ý���y\\3����'�I`r�G�]ľ�7�\\7�49�]�^p�{<Z��q4�u�|��Qۙ��p���i\$�@ox�_<���9pBU\"\0005�� i�ׂ��C�p�\n�i@�[��4�jЁ�6b�P�\0�&F2~������U&�}����ɘ	��Da<��zx�k���=���r3��(l_���FeF���4�1�K	\\ӎld�	�1�H\r���p!�%bG�Xf��'\0���	'6��ps_��\$?0\0�~p(�H\n�1�W:9�͢��`��:h�B��g�B�k��p�Ɓ�t��EBI@<�%����` �y�d\\Y@D�P?�|+!��W��.:�Le�v,�>q�A���:���bY�@8�d>r/)�B�4���(���`|�:t�!����?<�@���/��S��P\0��>\\�� |�3�:V�uw���x�(����4��ZjD^���L�'���C[�'�����jº[�E�� u�{KZ[s���6��S1��z%1�c��B4�B\n3M`0�;����3�.�&?��!YA�I,)��l�W['��ITj���>F���S���BбP�ca�ǌu�N����H�	LS��0��Y`���\"il�\r�B���/����%P���N�G��0J�X\n?a�!�3@M�F&ó����,�\"���lb�:KJ\r�`k_�b��A��į��1�I,�����;B,�:���Y%�J���#v��'�{������	wx:\ni����}c��eN���`!w��\0�BRU#�S�!�<`��&v�<�&�qO�+Σ�sfL9�Q�Bʇ����b��_+�*�Su>%0�����8@l�?�L1po.�C&��ɠB��qh�����z\0�`1�_9�\"���!�\$���~~-�.�*3r?�ò�d�s\0����>z\n�\0�0�1�~���J����|Sޜ��k7g�\0��KԠd��a��Pg�%�w�D��zm�����)����j�����`k���Q�^��1���+��>/wb�GwOk���_�'��-CJ��7&����E�\0L\r>�!�q́���7����o��`9O`�����+!}�P~E�N�c��Q�)��#��#�����������J��z_u{��K%�\0=��O�X�߶C�>\n���|w�?�F�����a�ϩU����b	N�Y��h����/��)�G��2���K|�y/�\0��Z�{��P�YG�;�?Z}T!�0��=mN����f�\"%4�a�\"!�ޟ����\0���}��[��ܾ��bU}�ڕm��2�����/t���%#�.�ؖ��se�B�p&}[˟��7�<a�K���8��P\0��g��?��,�\0�߈r,�>���W����/��[�q��k~�CӋ4��G��:��X��G�r\0������L%VFLUc��䑢��H�ybP��'#��	\0п���`9�9�~���_��0q�5K-�E0�b�ϭ�����t`lm����b��Ƙ; ,=��'S�.b��S���Cc����ʍAR,����X�@�'��8Z0�&�Xnc<<ȣ�3\0(�+*�3��@&\r�+�@h, ��\$O���\0Œ��t+>����b��ʰ�\r�><]#�%�;N�s�Ŏ����*��c�0-@��L� >�Y�p#�-�f0��ʱa�,>��`����P�:9��o���ov�R)e\0ڢ\\����\nr{îX����:A*��.�D��7�����#,�N�\r�E���hQK2�ݩ��z�>P@���	T<��=�:���X�GJ<�GAf�&�A^p�`���{��0`�:���);U !�e\0����c�p\r�����:(��@�%2	S�\$Y��3�hC��:O�#��L��/����k,��K�oo7�BD0{���j��j&X2��{�}�R�x��v���أ�9A����0�;0�����-�5��/�<�� �N�8E����	+�Ѕ�Pd��;���*n��&�8/jX�\r��>	PϐW>K��O��V�/��U\n<��\0�\nI�k@��㦃[��Ϧ²�#�?���%���.\0001\0��k�`1T� ����ɐl�������p���������< .�>��5��\0��	O�>k@Bn��<\"i%�>��z��������3�P�!�\r�\"��\r �>�ad���U?�ǔ3P��j3�䰑>;���>�t6�2�[��޾M\r�>��\0��P���B�Oe*R�n���y;� 8\0���o�0���i���3ʀ2@����?x�[����L�a����w\ns����A��x\r[�a�6�clc=�ʼX0�z/>+����W[�o2���)e�2�HQP�DY�zG4#YD����p)	�H�p���&�4*@�/:�	�T�	���aH5���h.�A>��`;.���Y��a	���t/ =3��BnhD?(\n�!�B�s�\0��D�&D�J��)\0�j�Q�y��hDh(�K�/!�>�h,=�����tJ�+�S��,\"M�Ŀ�N�1�[;�Т��+��#<��I�Zğ�P�)��LJ�D��P1\$����Q�>dO��v�#�/mh8881N:��Z0Z���T �B�C�q3%��@�\0��\"�XD	�3\0�!\\�8#�h�v�ib��T�!d�����V\\2��S��Œ\nA+ͽp�x�iD(�(�<*��+��E��T���B�S�CȿT���� e�A�\"�|�u�v8�T\0002�@8D^oo�����|�N������J8[��3����J�z׳WL\0�\0��Ȇ8�:y,�6&@�� �E�ʯݑh;�!f��.B�;:���[Z3������n���ȑ��A���qP4,��Xc8^��`׃��l.����S�hޔ���O+�%P#Ρ\n?��IB��eˑ�O\\]��6�#��۽؁(!c)�N����?E��B##D �Ddo��P�A�\0�:�n�Ɵ�`  ��Q��>!\r6�\0��V%cb�HF�)�m&\0B�2I�5��#]���D>��3<\n:ML��9C���0��\0���(ᏩH\n����M�\"GR\n@���`[���\ni*\0��)������u�)��Hp\0�N�	�\"��N:9q�.\r!���J��{,�'����4�B���lq���Xc��4��N1ɨ5�Wm��3\n��F��`�'��Ҋx��&>z>N�\$4?����(\n쀨>�	�ϵP�!Cq͌��p�qGLqq�G�y�H.�^��\0z�\$�AT9Fs�Ѕ�D{�a��cc_�G�z�)� �}Q��h��HBָ�<�y!L����!\\�����'�H(��-�\"�in]Ğ���\\�!�`M�H,gȎ�*�Kf�*\0�>6���6��2�hJ�7�{nq�8����H�#c�H�#�\r�:��7�8�܀Z��ZrD��߲`rG\0�l\n�I��i\0<����\0Lg�~���E��\$��P�\$�@�PƼT03�HGH�l�Q%*\"N?�%��	��\n�CrW�C\$��p�%�uR`��%��R\$�<�`�Ifx���\$/\$�����\$���O�(���\0��\0�RY�*�/	�\rܜC9��&hh�=I�'\$�RRI�'\\�a=E����u·'̙wI�'T���������K9%�d����!��������j�����&���v̟�\\=<,�E��`�Y��\\����*b0>�r��,d�pd���0DD ̖`�,T �1�% P���/�\r�b�(���J����T0�``ƾ����J�t���ʟ((d�ʪ�h+ <Ɉ+H%i�����#�`� ���'��B>t��J�Z\\�`<J�+hR���8�hR�,J]g�I��0\n%J�*�Y���JwD��&ʖD�������R�K\"�1Q�� ��AJKC,�mV�������-���KI*�r��\0�L�\"�Kb(����J:qKr�d�ʟ-)��ˆ#Ը�޸[�A�@�.[�Ҩʼ�4���.�1�J�.̮�u#J���g\0��򑧣<�&���K�+�	M?�/d��%'/��2Y��>�\$��l�\0��+����}-t��ͅ*�R�\$ߔ��K�.����JH�ʉ�2\r��B���(P���6\"��nf�\0#Ї ��%\$��[�\n�no�LJ�����e'<����1K��y�Y1��s�0�&zLf#�Ƴ/%y-�ˣ3-��K��L�΁��0����[,��̵,������0���(�.D��@��2�L+.|�����2�(�L�*��S:\0�3����G3l��aːl�@L�3z4�ǽ%̒�L�3����!0�33=L�4|ȗ��+\"���4���7�,\$�SPM�\\��?J�Y�̡��+(�a=K��4���C̤<Ё�=\$�,��UJ]5h�W�&t�I%��5�ҳ\\M38g�́5H�N?W1H��^��Ը�Y͗ؠ�͏.�N3M�4Å�`��i/P�7�dM>�d�/�LR���=K�60>�I\0[��\0��\r2���Z@�1��2��7�9�FG+�Ҝ�\r)�hQtL}8\$�BeC#��r*H�۫�-�H�/���6��\$�RC9�ب!���7�k/P�0Xr5��3D���<T�Ԓq�K���n�H�<�F�:1SL�r�%(��u)�Xr�1��nJ�I��S�\$\$�.·9��IΟ�3 �L�l���Ι9��C�N�#ԡ�\$�/��s��9�@6�t���N�9���N�:����7�Ӭ�:D���M)<#���M}+�2�N��O&��JNy*���ٸ[;���O\"m����M�<c�´���8�K�,���N�=07s�JE=T��O<����J�=D��:�C<���ˉ=���K�ʻ̳�L3�����LTЀ3�S,�.���q-��s�7�>�?�7O;ܠ`�OA9���ϻ\$���O�;��`9�n�I�A�xp��E=O�<��5����2�O�?d�����`N�iO�>��3�P	?���O�m��S�M�ˬ��=�(�d�Aȭ9���\0�#��@��9D����&���?����i9�\n�/��A���ȭA��S�Po?kuN5�~4���6���=򖌓*@(�N\0\\۔dG��p#��>�0��\$2�4z )�`�W���+\0��80�菦������z\"T��0�:\0�\ne \$��rM�=�r\n�N�P�Cmt80�� #��J=�&��3\0*��B�6�\"������#��>�	�(Q\n���8�1C\rt2�EC�\n`(�x?j8N�\0��[��QN>���'\0�x	c���\n�3��Ch�`&\0���8�\0�\n���O`/����A`#��Xc���D �tR\n>���d�B�D�L��������Dt4���j�p�GAoQoG8,-s����K#�);�E5�TQ�G�4Ao\0�>�tM�D8yRG@'P�C�	�<P�C�\"�K\0��x��~\0�ei9���v))ѵGb6���H\r48�@�M�:��F�tQ�!H��{R} �URp���O\0�I�t8������[D4F�D�#��+D�'�M����>RgI���Q�J���U�)Em���TZ�E�'��iE����qFzA��>�)T�Q3H�#TL�qIjNT���&C��h�X\nT���K\0000�5���JH�\0�FE@'љFp�hS5F�\"�oѮ�e%aoS E)� ��DU��Q�Fm�ѣM��Ѳe(tn� �U1ܣ~>�\$��ǂ��(h�ǑG�y`�\0��	��G��3�5Sp(��P�G�\$��#��	���N�\n�V\$��]ԜP�=\"RӨ?Lzt��1L\$\0��G~��,�KN�=���GM����NS�)��O]:ԊS}�81�RGe@C�\0�OP�S�N�1��T!P�@��S����S�G`\n�:��P�j�7R� @3��\n� �������DӠ��L�����	��\0�Q5���CP��SMP�v4��?h	h�T�D0��֏��>&�ITx�O�?�@U��R8@%Ԗ��K���N�K��RyE�E#�� @����%L�Q�Q����?N5\0�R\0�ԁT�F�ԔR�S�!oTE�C(�����ĵ\0�?3i�SS@U�QeM��	K�\n4P�CeS��\0�NC�P��O�!�\"RT�����S�N���U5OU>UiI�PU#UnKP��UYT�*�C��U�/\0+���)��:ReA�\$\0���x��WD�3���`����U5�IHUY��:�P	�e\0�MJi�����Q�>�@�T�C{��u��?�^�v\0WR�]U}C��1-5+U�?�\r�W<�?5�JU-SX��L�� \\t�?�sM�b�ՃV܁t�T�>�MU+�	E�c���9Nm\rRǃC�8�S�X�'R��XjCI#G|�!Q�Gh�t�Q��� )<�Y�*��RmX0����M���OQ�Y�h���du���Z(�Ao#�NlyN�V�Z9I���M��V�ZuOՅT�T�EՇַS�e����\n�X��S�QER����[MF�V�O=/����>�gչT�V�oU�T�Z�N�*T\\*����S-p�S��V�q��M(�Q=\\�-UUUV�C���Z�\nu�V\$?M@U�WJ\r\rU��\\�'U�W]�W��W8�N�'#h=oC���F(��:9�Yu����V-U�9�]�C�:U�\\�\n�qW���(TT?5P�\$ R3�⺟C}`>\0�E]�#R��	��#R�)�W���:`#�G�)4�R��;��ViD%8�)Ǔ^�Q��#�h	�HX	��\$N�x��#i x�ԒXR��'�9`m\\���\nE��Q�`�bu@��N�dT�#YY����GV�]j5#?L�xt/#���#酽O�P��Q��6����^� �������M\\R5t�Ӛp�*��X�V\"W�D�	oRALm\rdG�N	����6�p\$�P废E5����Tx\n�+��C[��V�����8U�Du}ػF\$.��Q-;4Ȁ�NX\n�.X�b͐�\0�b�)�#�N�G4K��ZS�^״M�8��d�\"C��>��dHe\n�Y8���.� ���ҏF�D��W1cZ6��Q�KH�@*\0�^���\\Q�F�4U3Y|�=�Ӥ�E��ۤ�?-�47Y�Pm�hYw_\r�VeױM���ُe(0��F�\r�!�PUI�u�7Q�C�ю?0����gu\rqधY-Q�����=g\0�\0M#�U�S5Zt�֟ae^�\$>�ArV�_\r;t���HW�Z�@H��hzD��\0�S2J� HI�O�'ǁe�g�6�[�R�<�?� /��KM����\n>��H�Z!i����TX6���i�C !ӛg�� �G }Q6��4>�w�!ڙC}�VB�>�UQڑj�8c�U�T���'<�>����HC]�V��7jj3v���`0���23����x�@U�k�\n�:Si5��#Y�-w����M?c��MQ�GQ�уb`��\0�@��ҧ\0M��)ZrKX�֟�Wl������l�TM�D\r4�QsS�40�sQ́�mY�h�d��C`{�V�gE�\n��XkՁ�'��,4���^�6�#<4��NXnM):��OM_6d�������[\"KU�n��?l�x\0&\0�R56�T~>��ո?�Jn��� ��Z/i�6���glͦ�U��F}�.����JL�CTbM�4��cL�TjSD�}Jt���Z����:�L���d:�Ez�ʤ�>��V\$2>����[�p�6��R�9u�W.?�1��RHu���R�?58Ԯ��D��u���p�c�Z�?�r׻ Eaf��}5wY���ϒ���W�wT[Sp7'�_aEk�\"[/i��#�\$;m�fأWO����F�\r%\$�ju-t#<�!�\n:�KEA����]�\nU�Q�KE��#��X��5[�>�`/��D��֭VEp�)��I%�q���n�x):��le���[e�\\�eV[j�����7 -+��G�WEwt�WkE�~u�Q/m�#ԐW�`�yu�ǣD�A�'ױ\r��ՙO�D )ZM^��u-|v8]�g��h���L��W\0���6�X��=Y�d�Q�7ϓ��9����r <�֏�D��B`c�9���`�D�=wx�I%�,ᄬ�����j[њ����O��� ``��|�����������.�	AO���	��@�@ 0h2�\\�ЀM{e�9^>���@7\0��˂W���\$,��Ś�@؀����w^fm�,\0�yD,ם^X�.�ֆ�7����2��f;��6�\n����^�zC�קmz��n�^���&LFF�,��[��e��aXy9h�!:z�9c�Q9b� !���Gw_W�g�9���S+t���p�tɃ\nm+����_�	��\\���k5���]�4�_h�9 ��N����]%|��7�֜�];��|���X��9�|����G���[��\0�}U���MC�I:�qO�Vԃa\0\r�R�6π�\0�@H��P+r�S�W���p7�I~�p/��H�^������E�-%��̻�&.��+�Jђ;:���!���N�	�~����/�W��!�B�L+�\$��q�=��+�`/Ƅe�\\���x�pE�lpS�JS�ݢ��6��_�(ů���b\\O��&�\\�59�\0�9n���D�{�\$���K��v2	d]�v�C�����?�tf|W�:���p&��Ln��賞�{;���G�R9��T.y���I8���\rl� �	T��n�3���T.�9��3����Z�s����G����:	0���z��.�]��ģQ�?�gT�%��x�Ռ.����n<�-�8B˳,B��rgQ�����Ɏ`��2�:{�g��s��g�Z��� ׌<��w{���bU9�	`5`4�\0BxMp�8qnah�@ؼ�-�(�>S|0�����3�8h\0���C�zLQ�@�\n?��`A��>2��,���N�&��x�l8sah1�|�B�ɇD�xB�#V��V�׊`W�a'@���	X_?\n�  �_�. �P�r2�bUar�I�~��S���\0ׅ\"�2����>b;�vPh{[�7a`�\0�˲j�o�~���v��|fv�4[�\$��{�P\rv�BKGbp������O�5ݠ2\0j�لL���)�m��V�ejBB.'R{C��V'`؂ ��%�ǀ�\$�O��\0�`����4 �N�>;4���/�π��*��\\5���!��`X*�%��N�3S�AM���Ɣ,�1����\\��caϧ ��@��˃�B/����0`�v2��`hD�JO\$�@p!9�!�\n1�7pB,>8F4��f�π:��7���3��3����T8�=+~�n���\\�e�<br����Fز� ��C�N�:c�:�l�<\r��\\3�>���6�ONn��!;��@�tw�^F�L�;���,^a��\ra\"��ڮ'�:�v�Je4�א;��_d\r4\r�:����S�����2��[c��X�ʦPl�\$�ޣ�i�w�d#�B��b��������`:���~ <\0�2����R���P�\r�J8D�t@�E��\0\r͜6����7����Y���\"����\r�����3��.�+�z3�;_ʟvL����wJ�94�I�Ja,A����;�s?�N\nR��!��ݐ�Om�s�_��-zۭw���zܭ7���z���M����o����\0��a��ݹ4�8�Pf�Y�?��i��eB�S�1\0�jDTeK��UYS�?66R	�c�6Ry[c���5�]B͔�R�_eA)&�[凕XYRW�6VYaeU�fYe�w��U�b�w�E�ʆ;z�^W�9��ק�ݖ��\0<ޘ�e�9S���da�	�_-��L�8ǅ�Q��TH[!<p\0��Py5�|�#��P�	�9v��2�|Ǹ��fao��,j8�\$A@k����a���b�c��f4!4���cr,;�����b�=��;\0��ź���cd��X�b�x�a�Rx0A�h�+w�xN[��B��p���w�T�8T%��M�l2�������}��s.kY��0\$/�fU�=��s�gK���M� �?���`4c.��!�&�分g��f�/�f1�=��V AE<#̹�f\n�)���Np��`.\"\"�A�����q��X��٬:a�8��f��Vs�G��r�:�V��c�g�Vl��g=��`��W���y�gU��˙�Ẽ�eT=�����x 0� M�@����%κb���w��f��O�筘�*0���|t�%��P��p��gK���?p�@J�<Bٟ#�`1��9�2�g�!3~����nl��f��Vh���.����aC���?���-�1�68>A��a�\r��y�0��i�J�}�������z:\r�)�S���@��h@���Y���mCEg�cyφ��<���h@�@�zh<W��`��:zO���\r��W���V08�f7�(Gy���`St#��f�#����C(9���؀d���8T:���0�� q���79��phAg�6�.��7Fr�b� �j��A5��a1��h�ZCh:�%��gU��D9��Ɉ�׹��0~vTi;�VvS��w��\r΃?��f�����n�ϛiY��a��3�·9�,\n��r��,/,@.:�Y>&��F�)�����}�b���iO�i��:d�A�n��c=�L9O�h{�� 8hY.������������\r��և�����1Q�U	�C�h��e�O���+2o����N�����zp�(�]�h��Z|�O�c�zD���;�T\0j�\0�8#�>Ύ�=bZ8Fj���;�޺T酡w��)���N`���ÅB{��z\r�c���|dTG�i�/��!i��0���'`Z:�CH�(8�`V������\0�ꧩ��W��Ǫ��zgG������-[��	i��N\rq��n���o	ƥfEJ��apb��}6���=o���,t�Y+��EC\r�Px4=����@���.��F��[�zq���X6:FG��#��\$@&�ab��hE:����`�S�1�1g1���2uhY��_:Bߡdc�*���\0�ƗFYF�:���n���=ۨH*Z�Mhk�/�냡�zٹ]��h@����1\0��ZK�������^+�,vf�s��>���O�|���s�\0֜5�X��ѯF��n�A�r]|�Ii4�� ��C� h@ع����cߥ�6smO������gX�V2�6g?~��Y�Ѱ�s�cl \\R�\0��c��A+�1������\n(����^368cz:=z��(�� ;裨�s�F�@`;�,>yT��&��d�Lן��%��-�CHL8\r��b�����Mj]4�Ym9����Z�B��P}<���X���̥�+g�^�M� + B_Fd�X���l�w�~�\r⽋�\":��qA1X������3�ΓE�h�4�ZZ��&����1~!N�f��o���\nMe�଄��XI΄�G@V*X��;�Y5{V�\n���T�z\rF�3}m��p1�[�>�t�e�w����@V�z#��2��	i���{�9��p̝�gh���+[elU���A�ٶӼi1�!��omm�*K���}��!�Ƴ��{me�f`��m��C�z=�n�:}g� T�mLu1F��}=8�Z���O��mFFMf��OO����������/����ޓ���V�oqj���n!+����Z��I�.�9!nG�\\��3a�~�O+��::�K@�\n�@���Hph��\\B��dm�fvC���P�\" ��.nW&��n��HY�+\r���z�i>Mfqۤ��Qc�[�H+��o��*�1'��#āEw�D_X�)>�s��-~\rT=�������- �y�m����{�h��j�M�)�^����'@V�+i�������;F��D[�b!����B	��:MP���ۭoC�vAE?�C�IiY��#�p�P\$k�J�q�.�07���x�l�sC|���bo�2�X�>M�\rl&��:2�~��cQ����o��d�-��U�Ro�Y�nM;�n�#��\0�P�f��Po׿(C�v<���[�o۸����fѿ���;�ẖ�[�Y�.o�Up���pU���.���B!'\0���<T�:1�������<���n��F���I�ǔ��V0�ǁRO8�w��,aF��ɥ�[�Ο��YO����/\0��ox���Q�?��:ً���`h@:�����/M�m�x:۰c1������v�;���^���@��@�����\n{�����;���B��8�� g坒�\\*g�yC)��E�^�O�h	���A�u>���@�D��Y�����`o�<>��p���ķ�q,Y1Q��߸��/qg�\0+\0���D���?�� ����k:�\$����ץ6~I��=@���!��v�zO񁚲�+���9�i����a������g������?��0Gn�q�]{Ҹ,F���O���� <_>f+��,��	���&�����·�y�ǩO�:�U¯�L�\n�úI:2��-;_Ģ�|%�崿!��f�\$���Xr\"Kni����\$8#�g�t-��r@L�圏�@S�<�rN\n�D/rLdQk࣓�����e����Э��\n=4)�B���ך�");
    } else {
        header("Content-Type: image/gif");
        switch ($_GET["file"]) {
            case"plus.gif":
                echo "GIF89a\0\0�\0001���\0\0����\0\0\0!�\0\0\0,\0\0\0\0\0\0!�����M��*)�o��) q��e���#��L�\0;";
                break;
            case"cross.gif":
                echo "GIF89a\0\0�\0001���\0\0����\0\0\0!�\0\0\0,\0\0\0\0\0\0#�����#\na�Fo~y�.�_wa��1�J�G�L�6]\0\0;";
                break;
            case"up.gif":
                echo "GIF89a\0\0�\0001���\0\0����\0\0\0!�\0\0\0,\0\0\0\0\0\0 �����MQN\n�}��a8�y�aŶ�\0��\0;";
                break;
            case"down.gif":
                echo "GIF89a\0\0�\0001���\0\0����\0\0\0!�\0\0\0,\0\0\0\0\0\0 �����M��*)�[W�\\��L&ٜƶ�\0��\0;";
                break;
            case"arrow.gif":
                echo "GIF89a\0\n\0�\0\0������!�\0\0\0,\0\0\0\0\0\n\0\0�i������Ӳ޻\0\0;";
                break;
        }
    }
    exit;
}
if ($_GET["script"] == "version") {
    $r = file_open_lock(get_temp_dir() . "/adminer.version");
    if ($r) {
        file_write_unlock($r, serialize([
            "signature" => $_POST["signature"],
            "version"   => $_POST["version"],
        ]));
    }
    exit;
}
global $c, $g, $l, $Ib, $Pb, $Zb, $m, $Cc, $Hc, $ba, $Yc, $z, $a, $qd, $le, $Qe, $fg, $Mc, $T, $Ng, $Tg, $ah, $fa;
if (!$_SERVER["REQUEST_URI"]) {
    $_SERVER["REQUEST_URI"] = $_SERVER["ORIG_PATH_INFO"];
}
if (!strpos($_SERVER["REQUEST_URI"], '?') && $_SERVER["QUERY_STRING"] != "") {
    $_SERVER["REQUEST_URI"] .= "?$_SERVER[QUERY_STRING]";
}
if ($_SERVER["HTTP_X_FORWARDED_PREFIX"]) {
    $_SERVER["REQUEST_URI"] = $_SERVER["HTTP_X_FORWARDED_PREFIX"] . $_SERVER["REQUEST_URI"];
}
$ba = ($_SERVER["HTTPS"] && strcasecmp($_SERVER["HTTPS"], "off")) || ini_bool("session.cookie_secure");
@ini_set("session.use_trans_sid", false);
if (!defined("SID")) {
    session_cache_limiter("");
    session_name("adminer_sid");
    $Ge = [
        0,
        preg_replace('~\?.*~', '', $_SERVER["REQUEST_URI"]),
        "",
        $ba,
    ];
    if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
        $Ge[] = true;
    }
    call_user_func_array('session_set_cookie_params', $Ge);
    session_start();
}
remove_slashes([
    &$_GET,
    &$_POST,
    &$_COOKIE,
], $tc);
if (get_magic_quotes_runtime()) {
    set_magic_quotes_runtime(false);
}
@set_time_limit(0);
@ini_set("zend.ze1_compatibility_mode", false);
@ini_set("precision", 15);
$qd = [
    'en'    => 'English',
    'ar'    => 'العربية',
    'bg'    => 'Български',
    'bn'    => 'বাংলা',
    'bs'    => 'Bosanski',
    'ca'    => 'Català',
    'cs'    => 'Čeština',
    'da'    => 'Dansk',
    'de'    => 'Deutsch',
    'el'    => 'Ελληνικά',
    'es'    => 'Español',
    'et'    => 'Eesti',
    'fa'    => 'فارسی',
    'fi'    => 'Suomi',
    'fr'    => 'Français',
    'gl'    => 'Galego',
    'he'    => 'עברית',
    'hu'    => 'Magyar',
    'id'    => 'Bahasa Indonesia',
    'it'    => 'Italiano',
    'ja'    => '日本語',
    'ko'    => '한국어',
    'lt'    => 'Lietuvių',
    'ms'    => 'Bahasa Melayu',
    'nl'    => 'Nederlands',
    'no'    => 'Norsk',
    'pl'    => 'Polski',
    'pt'    => 'Português',
    'pt-br' => 'Português (Brazil)',
    'ro'    => 'Limba Română',
    'ru'    => 'Русский',
    'sk'    => 'Slovenčina',
    'sl'    => 'Slovenski',
    'sr'    => 'Српски',
    'ta'    => 'த‌மிழ்',
    'th'    => 'ภาษาไทย',
    'tr'    => 'Türkçe',
    'uk'    => 'Українська',
    'vi'    => 'Tiếng Việt',
    'zh'    => '简体中文',
    'zh-tw' => '繁體中文',
];
function get_lang()
{
    global $a;
    return $a;
}

function lang($w, $ce = null)
{
    if (is_string($w)) {
        $Te = array_search($w, get_translations("en"));
        if ($Te !== false) {
            $w = $Te;
        }
    }
    global $a, $Ng;
    $Mg = ($Ng[$w] ? $Ng[$w] : $w);
    if (is_array($Mg)) {
        $Te = ($ce == 1 ? 0 : ($a == 'cs' || $a == 'sk' ? ($ce && $ce < 5 ? 1 : 2) : ($a == 'fr' ? (!$ce ? 0 : 1) : ($a == 'pl' ? ($ce % 10 > 1 && $ce % 10 < 5 && $ce / 10 % 10 != 1 ? 1 : 2) : ($a == 'sl' ? ($ce % 100 == 1 ? 0 : ($ce % 100 == 2 ? 1 : ($ce % 100 == 3 || $ce % 100 == 4 ? 2 : 3))) : ($a == 'lt' ? ($ce % 10 == 1 && $ce % 100 != 11 ? 0 : ($ce % 10 > 1 && $ce / 10 % 10 != 1 ? 1 : 2)) : ($a == 'bs' || $a == 'ru' || $a == 'sr' || $a == 'uk' ? ($ce % 10 == 1 && $ce % 100 != 11 ? 0 : ($ce % 10 > 1 && $ce % 10 < 5 && $ce / 10 % 10 != 1 ? 1 : 2)) : 1)))))));
        $Mg = $Mg[$Te];
    }
    $ta = func_get_args();
    array_shift($ta);
    $zc = str_replace("%d", "%s", $Mg);
    if ($zc != $Mg) {
        $ta[0] = format_number($ce);
    }
    return vsprintf($zc, $ta);
}

function switch_lang()
{
    global $a, $qd;
    echo "<form action='' method='post'>\n<div id='lang'>", lang(19) . ": " . html_select("lang", $qd, $a, "this.form.submit();"), " <input type='submit' value='" . lang(20) . "' class='hidden'>\n", "<input type='hidden' name='token' value='" . get_token() . "'>\n";
    echo "</div>\n</form>\n";
}

if (isset($_POST["lang"]) && verify_token()) {
    cookie("adminer_lang", $_POST["lang"]);
    $_SESSION["lang"] = $_POST["lang"];
    $_SESSION["translations"] = [];
    redirect(remove_from_uri());
}
$a = "en";
if (isset($qd[$_COOKIE["adminer_lang"]])) {
    cookie("adminer_lang", $_COOKIE["adminer_lang"]);
    $a = $_COOKIE["adminer_lang"];
} elseif (isset($qd[$_SESSION["lang"]])) {
    $a = $_SESSION["lang"];
} else {
    $ka = [];
    preg_match_all('~([-a-z]+)(;q=([0-9.]+))?~', str_replace("_", "-", strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"])), $Ed, PREG_SET_ORDER);
    foreach ($Ed as $D) {
        $ka[$D[1]] = (isset($D[3]) ? $D[3] : 1);
    }
    arsort($ka);
    foreach ($ka as $_ => $H) {
        if (isset($qd[$_])) {
            $a = $_;
            break;
        }
        $_ = preg_replace('~-.*~', '', $_);
        if (!isset($ka[$_]) && isset($qd[$_])) {
            $a = $_;
            break;
        }
    }
}
$Ng = $_SESSION["translations"];
if ($_SESSION["translations_version"] != 2138479313) {
    $Ng = [];
    $_SESSION["translations_version"] = 2138479313;
}
function get_translations($pd)
{
    switch ($pd) {
        case"en":
            $f = "A9D�y�@s:�G�(�ff�����	��:�S���a2\"1�..L'�I��m�#�s,�K��OP#I�@%9��i4�o2ύ���,9�%�P�b2��a��r\n2�NC�(�r4��1C`(�:Eb�9A�i:�&㙔�y��F��Y��\r�\n� 8Z�S=\$A����`�=�܌���0�\n��dF�	��n:Zΰ)��Q���mw����O��mfpQ�΂��q��a�į�#q��w7S�X3���=�O��ztR-�<����i��gKG4�n����r&r�\$-��Ӊ�����KX�9,�8�7�o��)�*���/�h��/Ȥ\n�9��8�Ⳉ�E\r�P�/�k��)��\\# ڵ����)jj8:�0�c�9�i}�QX@;�B#�I�\0x����C@�:�t���\$�~��8^�ㄵ�C ^(�ڳ��p̳�M�^�|�8�(Ʀ�k�Q+�;�:�hKN ����2c(�T1����0@�B�78o�J��C�:��rξ��6%�x�<�\r=�6�m�p:��ƀ٫ˌ3#�CR6#N)�4�#�u&�/���3�#;9tCX�4N`�;���#C\"�%5����£�\"�h�z7;_q�CcB����\n\"`@�Y��d��MTTR}W���y�#!�/�+|�QFN��yl@�2�J��_�(�\"��~b��h��(e �/���P�lB\r�Cx�3\r��P&E��*\r��d7(��NIQ�makw.�Iܵ���{9Z\r�l׶ԄI2^߉Fۛ/n��om���/c��4�\"�)̸�5��pAp5���Qjׯ�6��p��P*1n�}C�c�����K�s�Tr�1L�\0D(�b�єu!�\nv�4�#\$������pܔ%P�G=Ds�B���k�x��1̳<�5ͳ|�N���i�5��@���E֫�ǈ!��\\�U�5d�&΍�L5�\"\$h�i�<��2;7N�Q�J��_(*�!!���F��;qE�M*�bȐ{(�4��``E�ߞ� ���h�t��\0����E���\nI\$NX�\"P��q�\\����dȹ�\$1L��LI��/A��5�[̈́E\r���\\Lq�G�j<BjII������j��\0P\"�H��7��\n�\"�rC��af���fTI!�&��C���8�d�=Wf�U�i�!<)�FL]R&�p�dRz��f2�Ӟ��INpr]�l���cICVcg0̑�@���\nlfH�&H�0T�W�z^%� �d�5ZNˠB�(�)���xNT(@�(\n� �\"P�h�/Xk�>�f�G�y����c]��s�JCy�#���s��R�1��>����K��i����T~�r>0i=.��*�h�9��p�`�@�(+ ��yϹ�3���5��af:�p�&b�Y=�(��[�j��(3��-��Vt���po�b��S�R�I�-\\��J�´�T�P�G���d��ϳ�AC�J���2��D_��d&��3��YB�h]g�ؠteP�PF(��U��D���/!D�p�����4��Rў�HwV�@��@ ���H��o�d�q�\"����/!����`�\"�\$&��l�aORM����U��\$MmIE@\\o�~m81�@A��E�0��)8�`X�g�ny�`3:J���v�\n�L.�~)�������,�h���}�!8#������#��ݑxJ���Hhp��������%˖�A��㸥�&d\r�N�#�1g,�M���>�m	&t��}R��9�=���mI#�WBg�ӟ�I�7e�XF)�4J\r�3'��/�4��TQ�O�0jL�?�t��B�sQ��~kem��_c���!t��|Yu�x�h��׫����>����Sq�UK�r������h��\r�d0�NIn�Ɯ��]��F��k''�TT�5=I 4�v�Ϥbُ[��-��6�zy��y���&D�߅3�6\r}�\$)]���{D�\r\\�p�O���\n�7N�ӓ��[^Mm�EI�K�[Kl�xƪҶ����rhmaM����aQ�i�sˬ����b�Є��-�3\$>�W�Q��y�*b_K�G�RG�޲偗��+W�J�v�F������ʮ��iU�5�wV�݄����sT�{0�J��\0���<*�~:����r���}d�^��/���УLñ|q��J�K{r��)3����9Hd�q��n/�9��%��yu��\$\\�9��\"�PdSލ�6��`�S2��G�̠�BN�d���#�������=;����d��O�l>�H���\r�BZ4�4/ �7���l�\0o��\0��Q�M��`	D_\$N��b�Yɔ�>4�z�d�� �\$�.���2\n�Ic,�\r��4��<7��o2.\"�	pM���. �PPW�`\r����S�B���\r�V�\0�`��\"F��0l��.��m\"�(b���`�F�\n���Z�5��9�P�\"%��.;��0�H��\"c�3����Uf�	��\r0������6�g�Т3җe�[�X���N̮�@`DB: �DB�\n�'\n�[B�԰Z��RSe�QQM�MH���R�i\"Պ����0vqi�2�d��\nfXf�^��.�'��*�����+��h��@	�L�\r�ڱ�aE�\0� #N����*%�S�vG�1�e�\nOG�l`��c����=H�Tn�߱��@�BfX�F�\"�-K�2������d\$�\"�\"�2#\"i��\"�\\";
            break;
        case"ar":
            $f = "�C�P���l*�\r�,&\n�A���(J.��0Se\\�\r��b�@�0�,\nQ,l)���µ���A��j_1�C�M��e��S�\ng@�Og���X�DM�)��0��cA��n8�e*y#au4�� �Ir*;rS�U�dJ	}���*z�U�@��X;ai1l(n������[�y�d�u'c(��oF����e3�Nb���p2N�S��ӳ:LZ�z�P�\\b�u�.�[�Q`u	!��Jy��&2��(gT��SњM�x�5g5�K�K�¦����0ʀ(�7\rm8�7(�9\r�f\"7�^��pL\n7A�*�BP��<7cp�4���Y�+dHB&���O��̤��\\�<i���H��2�lk4�����ﲠƗ\ns W��HBƯ��(�z �>����%�t�\$(�R�\n�v�-��������R���0ӣ�et�@2�� ��k� ��4�x荶�I�#��C�X@0ѭӄ0�m(�4���0�ԃ����`@T@�2���D4���9�Ax^;؁p�D�pT3��(��m^9�xD��lҽC46�Q\0��|��%��[F��ڏ���t�wk��j�P���Ӭ� ��m~�s���Pi�����n�E���9\r�PΎ�\$ؠ#�����r��8#��:�Yc���(r�\"W�6Rc��6�+�)/w�I(J���'	j?��ɩ�U�H��E*�߂]Z\r�~�F�d�i�	�[�r�(�}���B6n66��61�#s�-��p@)�\"bԇ����d��l�1\\��]�����1K���ű�\"�J\\�n����S_7k����!��ٖN;�^��qj��Z��1̃Ň�W4O=7x�\" ��&��B9�`�4�J7��0�E��µɺ��ț�B���\\p����MS�6n\r�x��u��9}c�OP �,d(��M�(`���r,�\0C\naH#B��#\rO�9E�N\nS�-�����L��il]I��B���F0��9��\0�Q�Y��Ɨ��)�@�o'اC8 Q+ ƈP�dQ��Ыur�Ø\"�9\nF,�1Ow��C��PRH��\\C:5�K�/Ee��'Xn�\n�&a5R���C��V<\0ҭ�\$���\\+�x���X��c,�܁�r�Y�=\r��>�V��	!�8�ڳ��`�B�URƍ�!�n)0���L�]ԈRR~ܚLoGP�(HBI�T\r�L��B8ln���p�e;!�?)G� _�p~��*szm&Y2�9*K;e<����[|*���æ@�'�,E�(8�����0d5�Ar�?J�YMD�!�.��&���`XҖz�ߩ14�xw�=㺕�LӡĔ�Bl*I�0e��XB��ie���2�Qϑ�Gt�ϲ�QΡ'��0�F�ɟQ���u&�Ct�7��[�zԘfA�%IeoVt�~��tRU�li�\rd���`�T!dE���Ljj;K\r4���PQ!6i.R�е��㈆��\$�B��ס���5\$�Н�@��@\$;V��6��c��@�LIo�0j<\rj��P()��\0�\"��U����iQ�8�Ī���NC�II�{�6��p \n�@\"�@U�\"���yۼ4O��+�bˉ�0��(6JbM�^G([R��K.̎��|\\�U{\$tCY��˚\$/��[*(4���\ngi���v,�_f�4st����D���ĳ���`7v�]�9�cLJ���:����QC���n��ŧ'��K�A�JI���XJ�9rĝ��ܣ@�� ���5��a�w(4���LNsk�b\r@�5��M@��L4��}Ee\$g�L2��1H\"%\0*>G����35��;�����	Id4R;�R�X6����}T�����P@��02ac�]b�vqJ\r}��=pN�9؉L�;U�-�\",P�)�X�\n)>�\\�>6��R�*y�A��*2��sG�-䤕8T\n�!��AY�0iRZ�)@�H�܉#�8K�B(��O��\"Tx \"�k�7t^�{#%'�XBg��K�VP�O����~c�� �S�&^Y|:e��B�y8�U���\\6G���ᙃ���H�HD>&��{izo:����L�@e���\0��TR�A܎#������ĩ1�I���01�PBW��'�?����ڗ;��%����(q�\r�*]��Y��Z�U(.�敱VA�9B��\niQ	��%\rϐ�T���p'U�Gx��!�-'��ư�c�=f���0�{��Dݬ;�>����#��|�13�{���G��������{��`�,�x�����\\;^��-� \n�\"���t�W��ǔ�écO���k��4H�O��O�_�Z�B��mL�,*+\"V�MX�\"���@\0P:�� @RJ�I��O��J]�H^LV��D\$)�qd�c�>��H�0DuJ�̀�PAxy	�\0f\n�'�d��Ķ�'|����D�\"���VNJ]�P��>L��b��N�ݭ|�C�װ�+K�#�q\0�t'���0�����	p���\rc\r�R��o��	��-d1>#��0���_#��8�fl�HVB�q\n!c��H�\n�#�����侌�Imf����pd��T����(a�q�+�ڢ@x��Bj����O�l�T?/:֍l;����qp\$�Bc����\"��y��`P�w�������60��Ѥ�\"&p��\\.M����O��d�O����s���E�\"aG�:m��E�;FH�ڌq�2/��������·'f ��r	�{�!\0���q�#��������#�I\"�Dɰ�dpf@��0��\\�\$��i�!�]�p+���&��5'f�(q�\$�'�Ŀ\$nr�i��(Km�!�u\0���Ļ\"��L�+��.GlR:��Nj�fT�����f�w�P��M&l~e,\r��G��&�(i&Hs��\r<\"nL�c ��VJ�Z)�c*(`�`�{`�\rd0@�@gFx7��\r��\r �}eP&`�����ж�@��\n���pBh�4����b���mb:c�\$o>���aϠ��P	�I4��aNq\$��nJ�@�2\r�\0E!L<��AK/ġ�f	�޶��V�B8.�=�t=��%*n�+N-��!�/�Xj��&Brxg-��\$�&l\"-0C	�J/�AT#����B �B�f4CI6\n�\r�����)�L2��Cg��]\"���&�.�+E�D!�l��ϼ\$&�hAT)�/\0�74v{�~qq܅s�8 \n�2��\r���2�y�xE�&�j9C��\$�@����(̅-���P�b�tK�'0\n ��d4pOdp&\r�d �	\0t	��@�\n`";
            break;
        case"bg":
            $f = "�P�\r�E�@4�!Awh�Z(&��~\n��fa��N�`���D��4���\"�]4\r;Ae2��a�������.a���rp��@ד�|.W.X4��FP�����\$�hR�s���}@�Зp�Д�B�4�sE�΢7f�&E�,��i�X\nFC1��l7c��MEo)_G����_<�Gӭ}���,k놊qPX�}F�+9���7i��Z贚i�Q��_a���Z��*�n^���S��9���Y�V��~�]�X\\R�6���}�j�}	�l�4�v��=��3	�\0�@D|�¤���[�����^]#�s.�3d\0*��X�7��p@2�C��9(� �9�#�2�pA��tcƣ�n9G�8�:�p�4��3����Jn��<���(�5\n��Kz\0��+��+0�KX��e�>I�J���L�H��/sP�9����K�<h�T �<p(�h���.J*��p�!��S4�&�\n��<�����J��6�#tP�x�Dc�::��WY#�W��p�5`�:F#��H�4\r�p0�;�c X��9�0z\r��8a�^��H\\0��LPEc8_\"����iڡxD��lWU#4V6�r@��|��.Jb�BN���]0�Pl�8���M�'��l�<��8�ݴ�N�<���+Œد�z��B��9\r�HΏ�\"�-(�������J�䧍�_N��ݝK(B>H;h���L��|A�M\\��Ԑ�1�\n���IbU�9%��\r�M�݆���ڊ��#���|ՌL\"��\$ۛ\0��S�H�m��4�G��:ں|̙MS�\"��#�����D�)��+���� r�>�)��I��-�+�e�N���☢&!��Ɣ�L���2���LvT����P���Kb����Ƚ�y��=q��-�,�*%�����s��M|�eJ�v.�͹�C&��:1�	�\$��!�8�,��9:<	eB�SZL��HBϞ>����RlD�������'�\0��ۉ\n.(i�7��V#(lƘ��VNI\n\$�T�&�rO�>Ќ��%6�V�^�-9C�c����F��2FV�p	P ���\n�F/1%0Dǋ��:��+)ȳ4\\;�/�H�-#\r,D*3��hV!�b�`��X!�/�D���h�k�%�5���)%*	�;�uB_hn����Pv����hZI=Àj\"9z �(����@aD(��\$\0�U��U\n�9-pƒR8dUkd-����p\"ʵF��Qq %U�	)�z�7�<Ȧ�i\"�7�EN.�\").	QqM������!\$ʏ������4p\\�6d�PMɼ���\\��t.��4�z�^a�y�4���J�_�5��~����Jh��!���U@���43o��(�8q࣍�w��;Qႆ���\nWѷ*t���������\$,��\0bF��L��Z0a� 0ꮕ�f��6�Ϊk*�\r\0�e�Da[�\0c[�h7N`���8p��f���4 oJ����jB~,�Ⱥ@P>�N�<g0���(-�����y�EA\r\$���9���\r�@9��Q�g�A[��^���蒸\$� S�w����LX%�\n&1M�<R�Q��I8�}4�F�&E�xg��bnk*轔@�(����M�\$Qt�������R��%��j�+�gX�8��� �K�jӃQ.J����]-�J̌,2�4�Z�J��l@��#����*i�d�Pc9ZW�qĀ,P�^��@�.\0I���l`�a��j�,��mt��v��)�����],[��1u9���=~]Y48�d#KT�3]��*�Heq��n���'Ҡ�6,5����f�s�{�O�³�Ϭ�B/j���g�Yi��O>Jm�\nq�JA6(��\"ڨ�Ҩ���o�	��}�<Ӡ�&�L?������U6������ �JXSzp�	#vX�\"R�K��#���9@���)���v���J]��;�����N,P���`���t�n�H�D�h��y�͚���C���J��GwP�+�F�*\nN�H����6��YY,q�I�-�Q�1�.�X��܇q�G��,�§y(5�)�t vO*&1��c��P�3Hnf���t�D��82��&ÞB�L�h��:K'6\\�����ާ%����>FI�bI�S.r045�쏅���v5��כ�Dʲ(�ܢ3Կ[ww�v����t�'����+��\\��]���W�H+�x)�\$d'z�W���Ⱥ�z�]��1��8ޟ��C	\0�8�TPUz�[�@7�*�U��Mx��0�����i�0�0ю0�iaB=Q�|p��+v�&J�HNJ��i�g�aJ�Ww\\i��c���ǂд����X~��6G`/D+��,<�m�9��7��!m�������*(���o���\"�IJ�϶�r���'��t�P%�BJ6%�ĳ����N�&��j��-rQ(���l����Pt-P��R��h.!</\r%�O�[	��gI\\-\"G�Q�L%��\n�vN��o�3� ���E\0z�VHǨ�bp̘�����2;MB3���n��n�.R�6��h�g6@Bjс�B��M��e\n����B��^k� ��,�N�1�B�m��P+�n|.�M��:��1:�g�u=1`��?q8*����Nv맖�¹�Ј.'@��:y�f�J���G�m�nE��(���\n�P����Tz\"�\0�q��br'[����԰c��M�n�3q��p�J~��P�у�!�7B��,i&�0j�+��B+�\"A`1�J\$�q �f�Ql����\$p1\$�J�q���χ00g�\\`�rd���͹���H�Rc\$�j����_��p3����Q)��9,���@����bd��V?0�&o�\0��E%*�++F��	+ʐc�/������ͼ�\"��r�%#�X�S\0�G�&r�Ҟ�	11�)Q���*2��&�\"� �NlN�B��'�\r\n�2��|���:�<u�@s�l�rZ�NP��ȍ2�?\"gj��8x�-�2��G7\\��x�s2��c2�2E vq��`B&r���|N����S|��:R���0r�kr��PP�G_3��%b�ݢ����%-(ft�R��\"\$-=��~��6���)�o>I�?���{1i�R�H-B��?%-3)u;.��N@�t3�����L�+2ڢf&��jf�:arS/�C�S�-\r�|6Jp;d��ͨ��oR���8��/��+�KI�<�ؓ2����2�I)�C�K2�'(�@�]'�#��4���:�H��R[���(6��q?h-Lq.4�N1�+��\"���+�*)e	I���5�)m+B��RB_QikBq�K��4EM���O@��N��N5&��3Q�cNt4w�dP�hQ�S�i.��V�����Q�w=MW��u��u��tS33���}t�I\"?	i� �F���	i�W�q�++�A-�s�H�0?<��\0r\r2S]Dp.&��+�C��l���U腏�f�U��]�/fs&�6ܧ�\0��-�(�����b�:bbئl��rrD +h��l�W5/J��.\"�\n�6��)\n��`�\n���p��@�na�,���WSrϬ�b���pe6%b��l���^������G�����~pz|�S��B� jR�4�?�X�r.{)'@Emtж\0E+M�ʪ��N��K�k��>�o��qo�pm�m����R\n�<�0c�Β��0igP�7:Qb���^2t��4�|��A�ҟ�O^T�hh���uq�tn� �Ƴ��jwj�Qw_t���R�j��t4����|h�@�ė%?���Wm:�Q4��Ho�8�\$�b��X���N��O.\"�s#��&0��}� n�\"���%\n��e�Pf(�.k#H����bZ�����l@��v4�I�\\��y�}�yt�{��vL?N�mC*���x����G*�mw��ǂ�M�o�%H4>�Ȏ0U`wԭ;Qj�";
            break;
        case"bn":
            $f = "�S)\nt]\0_� 	XD)L��@�4l5���BQp�� 9��\n��\0��,��h�SE�0�b�a%�. �H�\0��.b��2n��D�e*�D��M���,OJÐ��v����х\$:IK��g5U4�L�	Nd!u>�&������a\\�@'Jx��S���4�P�D�����z�.S��E<�OS���kb�O�af�hb�\0�B���r��)����Q��W��E�{K��PP~�9\\��l*�_W	��7��ɼ� 4N�Q�� 8�'cI��g2��O9��d0�<�CA��:#ܺ�%3��5�!n�nJ�mk����,q���@ᭋ�(n+L�9�x���k�I��2�L\0I��#Vܦ�#`�������B��4��:�� �,X���2����,(_)��7*�\n�p���p@2�C��9.�#�\0�ˋ�7�ct��.A�>����7cH�B@����G�CwF0;IF���~�#�5@��RS�z+	,��;1�O#(��w0��cG�l-�ъ����v���MYL/q���)jب�hmb0�\n�P��z��-����L��ѥ*�Sђ\n^S[�̐ ��l�6 ����x�>Ä�{�#��вh@0�/�0�o �4�����a��7��`@`�@�2���D4���9�Ax^;�p�v���3��(���&9�xD��l��I�4�6�40��}D�w)c���8�\"�ej}�PF�5�S4�|��4��/�_B�V���@�����U3��+ڳp�Aw%9Z�� +�#��&�J2!�˵�<#T�z��@�ˣs�O3�R{{F�r�Q��]�PM����.� �\n��B&80��e�;#`�2��V�����P�-�:'�sh;�k��?�U����&��6�R���/��\\N*�C�V����UW�]���},���@�mܐ1��h�U�}�+^��3�\r��=�\0�CrI\n!0�\$����lG�\0ћ4N��S݀B�\n>L�*�C�|�7R�� *#9�U��cwv��UFu�nu��D� :\\�%�-5�[�F-j6?�PQ\"Ynf���p�y�,-I̔�6��,j�\nا����|�L�Ģe�,Y-�(\"'�F#c�D�=� wN��<��3`ػ�J� �S,(�y�h��<�\0���`���\0��:LlX:)JC8aI��]�e����������<Q��!�0����5������1+jk��������hSI�=P�n��3���b���xS1�hA�S0�d�M�X1�u\n�<m�������OA��4���Lnzo�ۗҾ[�{x!�M��r��M���ZMͲlC�\$�m�\r(�R�XA����9�&(���p\r,l2�.�hcd��2fP�S,\rɰ2�b��\n�P��3��C��\r��:Q`|��ca�@�V����	�Q�J0mޜ�z��>����ET̥ͨ�I������/�(��\r�L1J���cr�3S��*�쬂r�X����\$���\0�Q+ ix��D�&��YQ��*����I�a�@\$=_3�Q唀[��n���0\0����8�S�r,w&c��a��0*�zOY�=��/����^��Ɇ��{I�J-�v,�n��DZ��c�Ԣh\n\"�s]X%e���z�%��(�T��D��c�<-&�IJk�����@0-4 v�#0�a���׵�vCq}��(\$���w��ir�{���[��ca��5��pm�Ix�ek�{	�7ɚ��҉@S���\0�¡�h��x_ \"�@�\n(�Rl)�����8�R�:�]*�[�Q�v���lq\"T���bSV��� A��865���`�q�Ð\r5�}�̟�Il<i��!�e�#WJņi�#�����8�Q���sQD�?ck��h�� �~���F!�����Д�#cd\nR��ו\"��\nGy�O#�F�.Il���#9)��\"�g�ݼ�\"���Ν�[}����E��8M�o�<:CaN�HrP �k��OI�4k���En' c#P��5��L96l��<̘��,\r0Q�R+j�ڪ'&�W��=�`����3���J��.@�;1d�q.5Ͱ����Uy�K�;`�FϗF#Ek�9ȴ��3Oݯ�g?8斅[�\0���:\\�FI!�=<��X&�\na��^K���J�{�S�ul���r�2�vƬLu6��������Dz�Ӥ-�8� od��!N#���p���[^�G���E8S�Nw.��FX�\r��Y!n��<9zW���r(���y)�����c���]0��Ԣ��yJ�^	o騢���\\�����h��l�k �\n�� �	\0@Ʉ�\r%�K�_\0��������֏g\n)��K�r�^6r\n��~r�L���l�VvBb�2�F��F�k��Ģ���V�ZYHW���Ԅ�H(=/��p����.F(�x:�P{����`����Ў��\"�l�Wn3�`��`��\"M�/#��9	p1	�h@��D���8�n�Pq6(�T胰�P��o�#�n��p�����\n���oϲk ��f��BJ��-���B�,.���[�Zn�v&NT����T���~+��<�((�Y�4��}hD��&�d�ֱ����/NF�D����1�Fi�p�e\\�`���2l-��5Nj�����l������8���e\0db&��Т�7%NVY�r��0�o�%D��p�)��/IR��͆6�����	Q�pn�Q\0� ��-�A�\"�6;o�ﭕ!�\"�c5G&ĥ\"�sq��B;1��.k�2��-!\"�	(rq)�t��;'D��H�����/Em\r'ܐ���#O~��������\rڭ�������ލ����+�R�GL��[�����s��o2G\$Rk*�L����w�V��\0��@\n�ː�D�)Yb���悿'��\"O)(3-)��5�f[3�R\r�B�D+)rI)����7(�p��H�(S/6�G��%����9�k8��O�%��y*�i\$��.0V���E1.��Ѐ�0�#0�9��	D1%a<�<S+<�w<ɢ7S�Q�C1������)�u+2\$�QA9�_&�;(�8�,��M�2:�\n{�/A�KT7B4;9P�?�O?�0hֹ� 7!@-Q�X-�Bt\n>�k�:,��\"�B���o��LE�����trn/)�1�F�Ӣ;%�QFd:��t!	�&h�ر�݆���R��I��)87+S��T4�'�H/E/lHة�*\r�/H�G�V�s>Y����O>Q��)fۓ�E�J��ݒyB�'B�[C/S):泗E4YA��;UER#\r4�G����5+*t?D��-��G3C�=2L�2�sU4W�e,�U*��U-X�;,�:�#)Lr~��k�ȝ�U��)�HJ/�F2�A�����-�+\\�������AM.��F�B����/��V�#'�qY�oR��`�J��)5)8GX�;�0��BUO8ut�4�zF� U�ZҪ�U�A�Q�`Q��_�Z�dg�c��T�W ��{�O8�U\0�Y��;�`�(؞�%����W^�'�'<u@�P�Q֒��%\nS�b�Y�/j(��vyU�1Y�5bV���\r� �'g�iIj�[T��m�Z��ZF��	:��#a�a���cv��ďm�C��R���/�&V�b�MlS�;G�b�6K-O��u�h3�y�KU�5nt*=��@�2)GZw);#���Nr6�qu��u�/?\"vt�k\r+.�qn�b��v,%�t/�N8��W��r�s(�S�V �t{��,��&�ij\r�Vi.`�O��`��x���@����Ja���D\r����4h�\n���Z��I��{T�@c�w¾s�o�F5gq�7w#��@���.9l�\"�ң7��\"A�0(���	�� �]���m4@�aL���\\����aP�����E[Xj�Q�2(�.��L5�.J	�.r|cf\n@\0��Vq��Ʒ���P�t�6�o�DA%��B�UJ��(��?b�8��;XOY��o��\r��1�v��ExQ�2vj�KY�V�l�+�]1�'V�;`�ǒb�\n��>C�<x���\n(]�4��v{�����F�Up\\�XT�LF|b\0|�`]A<(��s��hN�g�����)M�]�d�V���W4�VR��y2�p��%d\n����\r�8�\\��z�(��-�[P�]�d�{���`)��/��\r�L�^�3��Y64ِ�9���s����vl��]�3\r�UvJ5dE]U��\"���	\0t	��@�\n`";
            break;
        case"bs":
            $f = "D0�\r����e��L�S���?	E�34S6MƨA��t7��p�tp@u9���x�N0���V\"d7����dp���؈�L�A�H�a)̅.�RL��	�p7���L�X\nFC1��l7AG���n7���(U�l�����b��eēѴ�>4����)�y��FY��\n,�΢A�f �-�����e3�Nw�|��H�\r�]�ŧ��43�X�ݣw��A!�D��6e�o7�Y>9���q�\$���iM�pV�tb�q\$�٤�\n%���LIT�k���)�乪\r��ӄ\nh@����n�@�D2�8�9�#|&�\n�������:����#�`&>n���!���2��`�(�R6���f9>��(c[Z4��br����܀�\n@�\$��,\n�hԣ4cS=�##�J8��4	\n\n:�\n��:�1�P��6����0�h@�4�L��&O��`@ #C&3��:����x�K������r�3��p^8P4�2��\r����˘ڏ£px�!�=/��	&��(��	�_;1��5��`�6:4���3��%�i.��l���p�� ���\$��\n���\"2b:!-�y\rK��{�wk!\r�*\r#�z�\r��x ��\0Zѭ�J��0�:��c-��%z�B0���l;�'�	�4�Xl�f0����5�8ɖ\nq�H�+�H�\rC�j��j1Ƣ �c���4�Z^K-\"�[&�h�4�6�\r;�׭:.(����#ː��	L���%��j�C�7`/�N㹸�H�6��5ejo��g�����'I\"\"r��B�v=<��r��+c���6~�&q�\"!CMx�d�x̳wR7��2�%�~o-ʃ{[Y���O	��|�3c���t4g�f\n��w�A/�(P9�)p�2��;��b��#l�x\\J*˶�O�r������%ªR2�*7���3��տbN��8 K�|��`ƅ���L* �(�SA>'��5n�IpVX���(��&E!���d�rH9~K������:aT��A(C���2��\n(�*�Tz�RjUK�u2���J�Q!\$���Ua%�v �>@���k�OU鰕?r4s��.	�̏�5ZBɪ|W�<9��f��2����}� Oa�6/�_��^�3C����ȑ��3�4�l��h!�0��@̙�a!���2\\^��8r�'2b<~�:M�������@PLR��2d�A�A%�\r`�`I 4E�ҚsR�Pt5f�2��üC%�c�G\\�<���ŷ��I��kB��6��S�'�:_-fT��D&��/4+�)&h����\\by\r���M�&C��M�����	�9Ҝ�'�C7!�%�E���A�O\naQk9\n!� �\0�C��)Z�̚�`@��p Ϥ7bCr�#�	fJK���#F��)����HSl����J@B0T�M���������9#�r��,h%\r���[C�4�����P�*P@\n\rkh9�5����a *�\0�B`E��>Ȟ�c=���d�=G��CiVs��;%�)LimM)�00���rM\0���V1d���Ϋ�I�W�519XX��K�6�z��ėM]1�E��L@�f�Q�3��:��Ks�pm����B�A���i���)m�'���r��WrGv�-�Ƶ�i53aB`�5�@Jb�X�\0쐂��qM��U�,�/`,�9�Q��Tt&c\\{.d��b�y�A��Ժ|^�\r(e�%lLX`��hi�-���ݞLu�#��i��{[���Bш%zhB6��pLN]��3↕�F&j��h�U�AI���v���<+�\$�2R^��\n>!Ǿ�@��@ �\n�2;)���Mo�M�\n�I�r|Y�X��d����{UJ��b�\nl\$Ģ ������}p��C=iS\nd6Nh���]c� >�Ӧ��\"9ݯW�����a��v>����P��s��.�2��dNV�\\%y�l�L�H����%�Y4W��������boMc��K&�;�wm}��0�֜n덝�ᩄ��%d�ú�#:�\$�ʴwhG|Вc��\nA!*f\\�WDv�H�*��RTp��q�F��O�;̖xZb� ��3Mi�g2q��,B�ݲ��U���iՄ��KA��3�bL%Ҟؓ�?\n��ꔄ���n��>勛���L�8*l=���N�e§{�����໼#��x�Z�ز�[Ѕ��],UY�!��G�ݟ��ȃ9����	��I��o2<�`�s��ރ\nɎyC(���O����f��*�������߶���n����R!�_ݱ%H���wy���bv���z�\$�mm���?��r�Pݱ?N*������D�M��+��/�\$��.���e�\$���m����l�z�o��R��&�B���I{�����-�s&�*èd�{F\0`BU��3ff>��+I�`����(�^�\"G�Oz��� \"٢�(�(� �4�eh��F�Py��\nj��0*���	�R������Y�rȥ���p'�VZ��ԙL�o�����\r��f&v8�����O��\r,�0N�L�LQ\0�G9�%�~g*�o#�J�.2��N��v�`@W�1���o��0��\\Ϗ����qYq� Z��d�Y�v8�ؿ�(Y��~�#���0ūd����\$F#��>�	��80��L���'��̱��s���q����͑���2D�%fz>.��q�\${c���9�\\HR\0���%��ӧ.n.���C��r�g���l�\r(��R0��\$�6a������Yb�a�jiF���Yd�4%�1Ff7�菛\0F�J\\�̔m�'���U'B	�솠�F`�hY ,���7����\\��z�PM�\0z�\n���pA(�c��m�\r������Z2�\0�����2�S.�{�bZ�6�GTc��t�Y��'�\n��1/�N����*���6\\o0�\"�>����̔�R\r�ҾĞF��4�G���}ʒj��F\rPn�2�O���<X�r�sr%sv���ۆ�w�8P`SI.3�Ű���Su\0 ��0����\\q'9\$	��1*Xe�\n�0�Vp\$�Kx��ȼ+v���(���0\r��q�B����j�@��@���H�T��'���<}��#�@g0�Χ<-��2k� Ô(��!k�2����6�.��d�ML�\n���*���b�`�";
            break;
        case"ca":
            $f = "E9�j���e3�NC�P�\\33A�D�i��s9�LF�(��d5M�C	�@e6Ɠ���r����d�`g�I�hp��L�9��Q*�K��5L� ��S,�W-��\r��<�e4�&\"�P�b2��a��r\n1e��y��g4��&�Q:�h4�\rC�� �M���Xa����+�����\\>R��LK&��v������3��é�pt��0Y\$l�1\"P� ���d��\$�Ě`o9>U��^y�==��\n)�n�+Oo���M|���*��u���Nr9]x�&�����:��*!���p�\r#{\$��h�����h��nx8���	�c��C\"� P�2�(�2�F2�\"�^���*�8�9��@!��x��� !H�Ꞝ(�Ȓ7\r#Қ1h2���e��-�2�V��#s�:BțL�4r�+c�ڢÔ�0�c�7��y\r�#��`��N�\\�9����h42�0z\r��8a�^��\\�͉x\\���{��]9�xD���jꎯ#2�=�px�!�c#���O�&��0@6�^:�c��Y�rV���\\��}�*�	�Ų�*QL�P��ʓ�2��\0�<�\0M�{_��6�j�\n�H��qjG!Jc(�\$h��:=�1��(�0�S�콎�,�b��s #\$Y+%4����0��^I�� ��8�7�#`�7�}`�2��7(�p�a����&A�ŭz��KqM64�e�@��3\n7Z����&.��E(�7�,�H<y'BPͲ4�rŢ9�� !���D��Ҁp)��n�����B�Zס�&��� \"��=��5�s����YB �3�0\r�xѴ�*:7��4E38\n�L֫ *\r��}\$�	�<�c3g���%HE��<3�+ˌ�_sf&2��R��[�b��#{��pA�VBh�5�*NU�يE9�0ܙ��bxg�2�g�`ϑWD���@��(rR��bL���R�D�r^�b|W���4�F��+��4�\$6���{�U+� Z��iz&!5#��L�H¯^�&G�T�\"S\niN)�@��!yTʡU�T���A��Z@�G�t�`���T\\�׫&���d�P��L�@���a	01��\n�\n��� ��8GS½>��\"; �B^��3Dd����g�U�(StbA(����Ε��|0�%�ļ_��0�Ǒ�-#\n� �@\$Ӫ�J �R��J��<H\nS�r5���\0ʽ]?�h7�v|ü�B�7%b�8�s��G\0Nf�[<%��r!f�N�<\r�<YkL�Ш��DCɤ��8���*'�p\$��\"�&7O�1���n��60��%dxS\n�.�b|F	�-L�)M�BXV}-o(����܁�2�VnR;�Y	��Y1P�oD�͌Ἇ���]�Kk)\r���\0�&2e^���Gj:z�r5�!���*xdT0�f^��xNT(@�+e�A\"���b�OK/\r�0# _�UQAȁ�\$^ȑ	%{�ᲇT�Bf!T��s�s�c�O��e�\"h��)�g��\$����Yy�O��Oh ������FE��]=V\$\reV��:���b\rԷ�Y�GS�Y�OD`����� �4b_-��:���,��bb)�c�3g��_�z��4H��UD��<�=2,���#��|�\"zeVZ%��\nM�@k�(w��}�a 0�kHA-�q7����+L�A��d��HL����Z�WP	��ĩ�0�S�9V���}��M�N|73��0�:c�5���n���AI�Ť2MA#C!�:GL�RB��P �0��	v6N�r'\$��s��\0��'#X�_`����M\0e_Z]��2���+�5���]jY\$�*A}�>Mq�}�����_�.z��`8�YPӆ~ϐUm���B&�֢~VPN�\$��}��E�u�\$װ/T�H����v��lk�e�0i*��R�\"{>��w���!�U�Q2�zN�bS���\r��L�����:�ܛ���RJ��FM ����C���\$ҵ��1�AAN�8��4Э�������`(\$\"1�,���x�\"���T9�MN{�MnRэ��I\"�vZ�ut׾d�e�ꉞ�tC5��zrK.�a�]a��d���.l\\LlĽ��+f)ǯ;?�y��ڥ�Ȃ!�0�D�cd�S�<��h3��*IX�²\$u,��\"���uC=y2�|laS�(�\"��V\n��p'�E�/��C?O��:�@R�z�`i�A�\\���L�c������sZ�(\0�8^�v�M���{Y{˫�Qv��rV���m/Z�a�\";���֟q�}�!�aHa���N�_��6O��W+%1̘�Ȅ�����&�j�\0/��� ���\0�	��g�^6B�.��0\$� AZ4��)�&n�RW��=�PR\r���&��DzZG�//X��:�k���T2���k���=��pɧ��˜ZL�B)�E,����,b��� [�H�L<�d�\nˋ'J��gK	и�0�� ��-g\0Mc\n��\rO���Z0��o��P�Hk�p��]�;L�ȣ\nPD��������Ќ�\\\$�̹/��N�p����P������p�nbhzM�kf�o��5����>9��	�D���g�1��`��#(��Q���,��#�\r��Z�#�Τ��Q<ȋ��u�N<\n�hS�?/��p�G�.�)��p��NhH�R��U\0Ч�v}N�Ce�NE���������Kb.Bd�H�>�(\$��2	�����bF�n����\"�����k�dp��E��jJJ��p�>�N��J�~&.l �G��Ǿ�`��ZxcO����; ���24��NҐ���C\ns�`?�d����������bPe�G<=c����L�1l�G��1�%��\$�£f</q�j\n�V��6�@���;�1f'����~g/�����r6q9.�6E��n������2�#3�hi3�y4+�21��3N1S0�| �5��֙�&�*~�>q'ų\\�LNFm>fb��m4����&F�����YF��f���T�Y7�Z0&|\r\"dRjB���f��r�\$�/DE- ���o���  '3́3�%g�v�B���B��?S�no4�hC��M(���G�LM\0�	\0t	��@�\n`";
            break;
        case"cs":
            $f = "O8�'c!�~\n��fa�N2�\r�C2i6�Q��h90�'Hi��b7����i��i6ȍ���A;͆Y��@v2�\r&�y�Hs�JGQ�8%9��e:L�:e2���Zt�@\nFC1��l7AP��4T�ت�;j\nb�dWeH��a1M��̬���N���e���^/J��-{�J�p�lP���D��le2b��c��u:F���\r��bʻ�P��77��LDn�[?j1F��7�����I61T7r���{�F�E3i����Ǔ^0�b�b���p@c4{�2�ф֊�â�9��C�����<@Cp���Ҡ�����:4���2�F!��c`��h�6���0���#h�CJz94�P�2��l.9\r0�<��R6�c(�N{��@C`\$��5��\n��4;��ގp�%�.��8K�D�'���2\r�����C\"\$��ɻ.V�c�@5��f��!\0��D��\0xߤ(��C@�:�t��D3��%#8^1�ax�c�R2��ɬ6F�2R�i�x�!�V+4�CDb���<� 襍mz�\nx�6��sz�L\rE�m[�+zٰCXꇵo\n\$�?�`�9]�r��P�5�M�}_���|�W�蹼h��8�*Y P�����L�B`�	#p�9���Ŋ�z�[I����z��YLX�:��\\7���\0��C�E�CCX�2���\$��+#2�-6	��\"\"H�A�@��K���_0�Կ0Lf)�\"d�L����e�(�?�l���vݺ�ك�ܶ��H�+�:'2�4p���H���-�HB���Ȓ6�lX�<s�?���+jre@P�d�oD&�J3<3��2�bx�7LL�����\r�hЍ\"WP湄d�0�\r5\"=y�Sb>�Z����76\r�ᦾ2}��[��z�/�z���죞ߺ;{��č���|���<���uy�趴��\nq��=�4����_/���\"���4�����@R��;��v��\nW��6�&.�k�w��A\"n��Lh;.eQ+j���=�~D���b��9�4�T��Q��K�`���lx��8E�V�#�Q���TҪc\rḟ���T�LTΑ�~QI�(��(BZQ�j\"4D��(�Bu#�pDP�-)X����T\n�;�EL��@rUJ����r�V�l8�Dk�<i�!�2�T��\$��4�E	;��TG�\\D\neM�^�BpN���%mm]���C#�\01u\r!�.�1@��M4~v��'X���X\"�wM�(�J2fUz��MH�!��2S^&A�/s =�JN4\"?\0�(��'���t���\nd\"^\"A�zΕ@CHv#�3�M9	K��Ι�N�l���dNX�<Md��{��hCckh���Bc�K��3eT�8�2%Sfq:&��-:󴂈����9�H�CS�/�V�J@�\"x(���=�AU8aR%�B��Q��5��L�z��(ix�\0���TP�f�2\$���b{�z>���ˊaD\r��4�p�p�l�5,�jҙv��aA\rl�6��R	9V\r����h�H	Y\r���`�E��0s��>�F�\\W%a4��Tw��M-tBE�D�^�oF��-��CO-�^��'%����[7����u�Q��j����t���P�L읱[y�(sf���疵�U�f�B�����J����5}\"RYo��ET�vD�\0as&�a�<��Q�Y �L_����E��r/D7���������U�*\0�T�	Eh���q�9��)��7�̬�9xWp���ׁ£�0�ġB�x긍&��ń�aSK������Z��Ji����>�v?a���ֆ0\n�od�h�f�\"�\r@(!����K���bmvi�a�\0003�t��u�Zv�W\$��k�#\"m��2e�2��9��O.��ff�p�BeA�r%e��ћآd�AC�����Aa �֒MC��.�Zp��S�AK�z]k�����k`fY�D����d\rl\"�6��D�`9K|.�#�*M�s0i��<Atx����xg�ؔ6K�\$�mz��X�pS���I���E�I�\n�B\\��#�>�ZFmm�o(G_h��[!�1tJ�y��!�/��6�P�<��pޫ��nzj!��Dm�\"�9���RI�!#��[����ws\$Y�t�bR�)�S5sB���8�n��*��9dٶ�)(�H��\$��ÄL�U&�Ҍbfu�?��塎j�u���\0%0�Q�JA=�ׇC\"a�*\$���5�o���w�|���\$7���� �I~2���C��pO>6i6�'����R��:3��sK�����CQύ|�|E]\r��yW�+%d�/S2�>�fw��7�� �[@�4��� �'l��ǊFoྡྷ'\0�]`�e�\\Fm+)v�L�#Jņ PM*+/.aF`@\r����\"�İ����P�2�ͤ�n�j�\rFD�C�8��^��\n#ЋЏ�	�> bt=\0��͌ O�\n��#���H�_�V��3\n� �����A��\n.R�T��[��DD���^�����[�t����^�k����!䏼υ����УQ�1��	Bx?�2\r�H�DR�b3��v���eBb��@Iض�]�G\0\0�R\"<l'���#��1Z&ɘ:�,F�JW����R�c̥�|�1XeB����\rOmTӧ�^���->�#�C�&�-�Ĭ��r G3����tq>tLj[�\\@��ɂ�ϫ����\r�2��O���!��j�\n��M� �\r\0�\"��?���.�bgva��@\r��e�«D�T<�PJ\r�Cp� /�Đ����7��2s\rr1=\nM@�`@�Fj9��(��&KN]m�\"I)l�R�ک�'���c���b\$�ք��-W,O�/,�[,p�\"\n8?��/�Xֲ0��g-��C�Xٲ7D���,�k `�0�J1�CK1\r�0Q.�?�>���&0�De�A\"?�fuC�P��V�L�а��4N�4����)���SX#S\\4sLe�)6H%`�#(r=�l@�d#�4�#5��:�R�p��15lM9f��q	:h�45s�s���e�\r�V;i��e�B�R%�IN�?6AR(��󑄙fL�eaf���e@�\n���p%s��Jc3�ǎ35�wA@�6.�q\nFT;Exs��w�x��O\"*\"�2��^f1�]%�(��d��/��F�#\"	b8���HǶ��[\0���Ҍ���j�4�BF��4����'��ȫ^\"\$D#PG �(+hW!{K\r��o��E'.-�V%o�l�H�h��G�\0��T�\"�j^�+Nt�M�NQO���l<�����<g p����R\\\rFf|��K#��s>?\0a4��F�\"�(r\0h�Ͳ��a\0�'I��`/03X/h(�\" �˘c\"<�=\0��E�'Uzr�@f�-�D�,&:�4�	�u'V�,e�`���O5z���1�&�DT��[3�+��";
            break;
        case"da":
            $f = "E9�Q��k5�NC�P�\\33AAD����eA�\"���o0�#cI�\\\n&�Mpci�� :IM���Js:0�#���s�B�S�\nNF��M�,��8�P�FY8�0��cA��n8����h(�r4��&�	�I7�S	�|l�I�FS%�o7l51�r������(�6�n7���13�/�)��@a:0��\n��]���t��e�����8��g:`�	���h���B\r�g�Л����)�0�3��h\n!��pQT�k7���WX�'\"Sω�z�O��x�����Ԝ�:'���	�s�91�\0��6���	�zkK[	5� �\0\r P�<�(������K`�7\"czD���#@��* �px��2(��У�TX ��j֡�x��<-掎\r�>1�rZ���f1F���4��@�:�#@8F����\0y3\r	���CC.8a�^���\\��Ȼγ��z������\r�:0���\"����^0��8��\r����B������:�A�C4���4���W�-J}-`��B��9\r�X�9�� @1W�(�Vbkd	cz>�@b��8@v������ ̐Z�1��\"�0�:��춎�>ST P���cK��6��w�+�)�N��;,���'�p���bD��p���\n�jp64c:D	��6X���e��|�c%\n\"`Z5���[���X�V�����yl�W09�,�'�����0N�.鍆�(-��/�H�(�P�\"�{#\r�2��ݢƑ��!T�xx���ϴ�x�3e�N&8��*\r�\\z<����*J�5�H+X�6�`�3�+[���T�2��R���8�--�)�B0Z��*XZ5�3�YT�����\n#�c�:\$���%m�ΎJ�@�Sh�� �7���:(�}\$����MS���G�b�d�����#k��E���(�I�IPL�T`Ԋ�'O�&�����v��='���:�JC�S�R��nQ�=��`R����0����H�q>�TϚ�N�#R[�X�B�Ԣ�aJ	I��@��Kd��גf���)i|լ�1��|3�	�' ����r�M0�a�#�ͣ��[C�'. �EfH���e��g���:2�l\0�k�����PQK�&��d���\ng���7̃PY�\r-x2�%�ڏr�I�|՞\"��S�.NQ�G�7�\0��\\g�	�����VFz%M0޵'n�b9JD���\"\"[�}YR��Kd©��q^�P3b^���g.a���Ω�Ar��4(��-�S\$�L�6�{ iT���B����L�ܡM	N��y)a���@�I:�4���̑#Ă2p�5���+4���PA�e��8rR�*f��*����s�!���\0��HS)	�8P�T�>�@�-RE�]LdXjBh����iܬ�P@r�1���^�u�]I|_A���bo�SG�(ɫ�҂@CxD�8�W��U�Kiu��B�,\nk��*�6Ps�Í�\$�2�X��ʄ ��R�!�Lv\r1�wDn˓���86��l�Q� � )���sM�HTeƬ�����ctj�����	A��%��E�f�[��F��\"V_�I�G,x���J��h������J��j6���T*��KE�*7�ߒa�M��G���B��Q{I���/�Υ��d�,���B�a�ljA��`��C	\0�qT����܈u 2p���R�,8C��VH �Yh�BVz�1B�� ��[VS���Є�r.K1���+0�B^@	��Z:�6�2�X�Y�c�K��r@E-�-�L��	�̰�L�ZA�2�8ʡ,d�A��d�����p�K7��d�<GH�!_U�LRI%g��R�%9S�H_¸eT�R��m�q;6ĕ0�4]�7-ʘ��*m��\$BE���BtYh~�71���.��\r�Cv8��]�o\r����EϡB�6��K�k4��Co's�:%�U��t0�m�-�71���)H�4�2Î�\\����{He`l�� �Tt�ɌR/�+����׸r� �^ij7z���u�E��V��#\0��D\nP%^j��wX�Զ��������@o�L95ֲ�'7g���|Ϟ�,:e�nP�/��缩��6L#y�8n����:+Au��BH\\\r9��k�PXN��[����{�U������彌����X2�s�A��8���?�ٌ��	ϡ\\�n&״��4HƔ�޷����\r�ņ��q[�,��v�z{��3�y	I���C/��aK�}p l�w��r������C���s�2��;k����]n��	vFu_���������r\"O�X��?V��W��2\"4!��#n\0�P�lL��e*X��.�'o����A�����\0����n�ꯨ�n���\$;�h%r:�8/�Z@o�6��0C�� bz�\$6U�(��7���ϖ�`���p(���4�8�D0�� ��l8�L�D�`)p\$����0�İ�nt��j3C\r���FL�)&�Ar1i&\r��.L��^xK<L�����QB��\"&Z�,fZ-����0̊W�ˮ��-\r��ر��@\0�`�e\0�ˁJ�����ty���cL5�6�����#*�\n��f���EŌ\$�͉\$�&�ۭn�(B��������l��EB#�ސ4iPT&n\$Az�C�įX7�Zp1.��?�B[6�b��FN9oB**�����<�d{#�	|#^^LCm򡅈������p�\r�ݱ�KH�cl�\r��k� \"� f�g\0�V��݃T2�@�0e�\"����Bb2+?<j�02�f�%�a\n��QQ�%\0���f�D�F0%rhdnܢ���\"B��tĞ;��#�\0�-��\0��� /d�'�1�0�殯bd��)*�Z�/��0B<f`��@@-I��d��Z";
            break;
        case"de":
            $f = "S4����@s4��S��%��pQ �\n6L�Sp��o��'C)�@f2�\r�s)�0a����i��i6�M�dd�b�\$RCI���[0��cI�� ��S:�y7�a��t\$�t��C��f4����(�e���*,t\n%�M�b���e6[�@���r��d��Qfa�&7���n9�ԇCіg/���* )aRA`��m+G;�=DY��:�֎Q���K\n�c\n|j�']�C�������\\�<,�:�\r٨U;Iz�d���g#��7%�_,�a�a#�\\��\n�p�7\r�:�Cx�)��ިa�\r�r��N�02�Z�i��0��C\nT��m{���lP&)�Є��C�#��x�2����2�� ���6�h`츰�s���B��9�c�:H�9#@Q��3� T�,KC��9��� ��j�6#zZ@�X�8�v1�ij7��b��Һ;�C@��PÄ�,�C#Z-�3��:�t��L#S���C8^����J���\r�R�7�Rr:\r)\0x�!�/#��,�Q[� �������������3H�/��on��	�(�:2�F=B��Ѓ���C�H��������Ip#��G�/���0��˂�ZѺSRN���{&˄�b�\$\0P��\n�7��0�3�yS�:�eĭJ*�9�X�<ֺ�e�ssB\\�;n��fS���@:B�8�#�b���xD�2\r��������.�s\0�r\\�S�����)����6�d�#�ir��MKW!�#l�58OX�<p���,�����/� �dOX� �j���cx�3\r��f �Q�؍���t;+\\��^�c`��dƀ����!apA��0��<z:�N�\n������@�Rx��#`\\�H�j�!����w���7x>��y\n�7����z(��z����h{a��0�FP7�c����(���dA�2��e�,�x}�@!D&:�Z`!�����f\rB*�ꬲ�S��!��1�\0�܁�SA�N+`���`B�B,5�g�SG��2,��_�a>%�ֳ����!����%�\"MO0r��J1�jn�YYQm�D Ц��T*�R�uR��`rU�mW��8��r�V�8�(�CtN��At ��#f�'s���+)���jL�	�)�F)s\"uB�H\n\0�)�&T�Cj�B&�`;B�A��/!i�`�Z�~#�C1� ��@�����}s:@)�A�r?���N�	�N`�\"�#�b�a�4���RV�Q{6)��ʠ�dL���2�v��<��(* �F�>����Ā��8��C�o�e��1��@�j)�\0��dG�D�H�\0��xeb\n�_3AB#�'\$����|P5/H���\0��o2%�97���I�ǂ�]���RY�\n�q��h.�)xf>�����HGà��-�h��\\jĨ��Hh*������\0� -d\n��B��S�50�=I	)W�G(�\$��(������~�ϲ;V��zQ���鱃4���W\0�5����X&�2ӠF\n�@��2�z��̐�KP�f(k�Kr@��f&�l2)`�0sjK�NT(@�-�(A&[��\\�\\(�t^���ұ�G��ĆÄ�g� ����d�q��O����?\\Q�>l�����V\0gX�@��>G\$�9p�ڍ���4�a�@���\n\$Υ�Ry�PV0�,�F��weH@It�S�-��Nx�D � eu�n���E��\n�8!�]b3~��ĶH�W`��k�rA�,�Y��!*`a��MN�4u&t����V�Y\\E�1e�s�\r8����S^Y�Aiڀ��!k&��F�Bv/�u��@�2��4�,:�a�i�\"�W]�I&�K]^�fP7��c����YL<~��T�����BH�T�3vp�\$�O�n�2bR[�I�Nں��L�����޿�3Fq!���A�Ps%��;�C[�VB�3�Y  �Nq\0N���yDC#�w���;���Ă飤�\rt6���5R޵[t1m�Z�Cy+��kx�>����ۍo�2>AȎ����z�����3&o����g[շ'72H˖�9Blƒ,���3b�'����]�ܨ�rM�y��0��������1�9i�˶\r�chDU���}E�~=aq�s�:\n]�G[\0R�������[���+�x�7\0��gîEq�gɛN���J/�ְ��\n�/�~�l7*�ɞ�Q�gøscVl}i\r�H&0V�1()�y� ���u�X����a�[2�w�}��b�R��?�rY�9G�C��*0[�PA@L����������~�	����M}qH��������.��L(��J����.l�M4=`��	��.J��hMN�O�b����&@��/Knp�ǧ	�\rm��+��K�#\r*0\0�����%\r(��'o��ð^\r�b%Pg�K\0O`@�]c��у\0M��#��b:#�`|�H\$%��\n�5��\$�9餸���\$�H�C��F��:	,Sg������\n�x���+'���'P����j��Hm�#��B�-61�����O/R�-���l;�/	*���B[O\\�#����Q<�Q@&qX(�]�ñf׊F�0�\rw��q~�p�>��MC�#`	J>Hl�H�a���T�Ъ��B�Qx�q\rsЁQ��y�%FP�M\n\\�V\r���d���q�1������6B �2 �\rf`/�2\$�����Q_�6�\$��4�2�ë��!�N��\"rDbT�1��^ĢJr	2&rJݒ+\0�32T?rY��\0bf	�!`�d�\$J~=`����q�POL)��O���\0A(�)*3)�f	gtg����ؽd����;G�'4�w� ���H �r���BE�\n;\0�d\"�A�,���B �\n���p4�ނ�46�&m� �Ά��Q)���H��;1�q�����bP%J�%�'��1�R��N�;.�&N����զ�7#a..��\r�6d�.�Z&`�%#�d~=`�#D��f-�Hdx�#��\rd�QU�:�\"�c@PZ\0�j�H/Up�n�����,�:\rr�8OE<��8\0P��;Ӥo3�)e�NQ6n��3�0K�E�\ng�Ws;<�D�rP�z2�ˢ��@�B+(Hp^8G�q��PT�#�����̊^'D�̌V2(�p �RD�� �:�I; �<3���b.�nR�\0�ֳ�g�<M0��`��̒>���vN�%�`#�I#�/b";
            break;
        case"el":
            $f = "�J����=�Z� �&r͜�g�Y�{=;	E�30��\ng%!��F��3�,�̙i��`��d�L��I�s��9e'�A��='���\nH|�x�V�e�H56�@TБ:�hΧ�g;B�=\\EPTD\r�d�.g2�MF2A�V2i�q+��Nd*S:�d�[h�ڲ�G%����..YJ�#!��j6�2�>h\n�QQ34d�%Y_���\\Rk�_��U�[\n��OW�x�:�X� +�\\�g��+�[J��y��\"���Eb�w1uXK;r���h���s3�D6%������`�Y�J�F((zlܦ&s�/�����2��/%�A�[�7���[��JX�	�đ�Kں��m늕!iBdABpT20�:�%�#���q\\�5)��*@I����\$Ф���6�>�r��ϼ�gfy�/.J��?�*��X�7��p@2�C��9)B �9�#�2�A9��t�=ϣ��9P�x�:�p�4��s\nM)����ҧ��z@K��T���L]ɒ���h�����`���3NgI\r�ذ�B@Q��m_\r�R�K>�{�����`g&��g6h�ʪ�Fq4�V��iX�Đ\\�;�5F���{_�)K���q8���H�Xmܫ���6�#t��x�CMc�<:���#ǃ��p�8 �:O#�>�H�4\r� ��;�c X���9�0z\r��8a�^��\\0���Nc8_F��H��xD��l�>`#4�6�t���|߲K�v��\"\\���MЕ\$�������u���o���\\8Ծ)���&��¼�+-�V����'�s��KЮ0�Cv3��(�C���GU�ݖl�)���g�:���M������� ��X�B�'��q>̑��z��ph=�- /f���dt�21ZP����q��v/�Ͻ��Iڪ��Z��WL�\r�fqL���E9��֩�H�4�@������!9EԮ��p�vg��8p^L�m5h���X��b� ����@L\$�i'�	�J=����ߜk�F˄���@N:R��^�\\�R��*D���^(�p[��s\\Q�8W�YQ,})X�=�Vp�a�J�T�@(�^�!A�\$�.5�O[iezk�@�H\r�Yy�q-���\0�:�-(��_��\"ȁ}�����o�N���p\n�;X��:A�eT�+FD�gEH)Y���I8�׃�L����e\$���Vy.����5����RJU,�,����S,�a[\"R�M�r!.L����RL	A0�Y�4�a̢�	�q	�\r�iqXaR�ދZ���P�C\naH#G�~�b]?h��e�E&�p�J4Cв\r=-�P�	k�r.)AP4ҡ�҈�U���\0���/jEG�F�A3f��� |�Tttm��Q.�,�<�T����Y{�J��.%�0'R��c:��3\n\"&��E\$��-���a&[g�n����Bf��3�|�D��������Z�Q�E�5HV��Y�@E�k�����N,Ť���T�)�� �hU��^�<��R�pP�1�Jؖ#�T���.Q�顲L��|�*�r�[�TI�SS)x�R�П�JR �������pN�6�WNC2�Q�:�&(���\r��3��ȃ@ di�<]�@٥>\r�(0���aM��U�6T5�?��SY�P���N�(:9����w����1�v�`b�2�k\n (\0P`�Kk<\"=#�jJ�I�//\\[����Xc�LZ��P�Hv\r)�3��P��2w%�]d�w�!K\$����LH�+e��Ef��JaE)�;�P���hEó��M'�m�S�>�l��� �t)��hě��DqD�'������*�D@�6w�%�c�@̝V%�Fp�4@%�4jrPV�c4��WL�p��	l��������@�`@xS\n���ө��^wTE�޻��l������`<T���4�������P�0����M}X����\$�m�oQ2C!Н���*[�|1�X��\0�1-\\G;�J#�@Y�\$9�[���а�q��f%lf}�!rV��S���T� R�D�;�LS\0�I�9V��8��}e0H��S��/�\$\n���J�H���;8=9��NrA+ԋ Qj9Okv�#��۔?/0��f�CR2���8HW��� �`�CCi�6��I1\n�vԀQ1�2�lD�������/��DH�3����bR?pW6d�zYT�x^�I�ʋi�~��H���\$�Q�9,����8�,��I�[���W��2[˚DҶ����*�'�Ob�d�#Ħ	v\\�A�y	:���>���S��^�����S��F!hH3\n�ąڣZ��+�s<:e�������w�nn��un�6��h�e�\\�P��o�~�E:퇌]�&oL�H4T�ܡ�(�v��(��\\��BAΪ�b��ɸ�-FD��w���b�v��0��D�1%�dX���g��!�_)��6I�  �_�ԁ\" -���Nl�B7O�W�@G�l�m�Ўo.�¤��م�@�\n�� �	\0@ �N\0�`�0f��\r���a�]��ݎx���EEU�u�^2t�;C&u�;�;g*�K�s	2\$f�ލ�7�L�!4���(���	,�i�\$t\"����H�\r..b{{��C�QH�B��a��O�X;\"���*1��(B-ó(Zn�D�p�)� H���/gZ��!QRCB[I���n��t���_��4�P	i�[(4�b*��̝�Z��T�DpÎ����\$b�,6�P<�E����m�,���M!bN]J:�0Z���Yk�S %��@J�RN���Ăq��\$��bX�\n+-�5��@q���*��vg�r�l,��}��]2�G/�H��D�ʰz�2�,�V���,ϧ� /жR�#�T�v��^�4J�o+�(�D0���G�9.�s�.B�y/в���/#�(��Ƣ@��\0dX���ң0�'2�\0�A�r>�22�3����^mh��G�\r4�tZN�r3>��(�T�Ply�҆�z�ªz�Z��\n��T��H�����N(㢞�-��m�L)��9�|㳈\\�	�+sD��/4���l>s1<��k���L,�D-@R�h�`��X�/e�7����\0T([?��@�11�Z�W4�� ��	42+=T-3�W')���o����<�!d1�O\"GA�y��j̚�J��NrTSq��yE�&ͱQH�u�)C\$Px��m��h.1�_@/�/S76T�8T��{J*��R2�Ȁ�s�B�pL-��+N!��5���a=�2d>�F����,�-�#E_\"���+1��FH���J!�j(bfXC`�.�P�I��S*A\0��O���d.��P��I�eSq�DB���j�T����UcM�gMԤ>ԩ\0So5s�U\\Y�8l��H2o���'���e�D��p�bړ�C�*��c@�>r�\0�&3\0��[��լ���Bp�N���]�5]U�}şI��!Ҫ�ԬJ�5\\����5���8�4=[�_V��]��5��D�aS����MB�Nc]���Bη �E��b\"��[%ZA�V�Iv!\\��(��b�H65L���+����D«G��@t�]r��Uu�c�C�ei0�E%^vCj�b����x�\"�V(E�qr*!��_�J�b�lP�\\�,U��|�2�䎶B��\n[�Ymv�¦Y���,4��=�=I��JM135�^6<\"cq��i��4�C�,�CWm�(:73=��c(���es�c�-j��Pt���aUp��v<BM�\n���h�sOwd�wt��ts:Z��)7j��b��`j�m6�[��h�\nl���ϢdwD���� ��v�~�E�=H�wF�K\"��%Q�)��+����/C-�Z��&{h#tħI\n��b='}B��ҮΗE�FQ�/�1P�/��cE��¥�^��.Q<������`�\r��`֟+4�3�?L.\"�ߍ�2-�H?#�M\"�%uR�`�j�\"r[���Vʉ�|\n���Z���F�g1��26��\r�F��1�GoJ�V1�Z%�>���ꮘ@D}3��r�9[P?}3�B�g�\$�i-�n�v����:�C��PIړИS�;K���M�M�V�4k)�\n&�x��\$L��f�n��bM��5j֥�.G�	�\$�ArW��Ն�ETGD�ԃjݲ4�+	Rt}Sǎ��8U,e�5��K�O[5/�y�x�r��v'c�9�F��*��x�٨���w�q�	Y���6��Ç\r3�,U�b���3��ԃS,��Yg^�Z���}��x�w�]UH�Cq���Y�p3t%/�3�E˖	�.����\0�6[:B\\�h�tF��n��G�Z�����˂���F�݄�[*�ԃ�X�#W¦���1����Z:�g;	�'�I�Y���2��ӧ �k�Sxy���r���ϗ�Z�<��q��!Gt-��X�k�Rf��";
            break;
        case"es":
            $f = "�_�NgF�@s2�Χ#x�%��pQ8� 2��y��b6D�lp�t0�����h4����QY(6�Xk��\nx�E̒)t�e�	Nd)�\n�r��b�蹖�2�\0���d3\rF�q��n4��U@Q��i3�L&ȭV�t2�����4&�̆�1��)L�(N\"-��DˌM�Q��v�U#v�Bg����S���x��#W�Ўu��@���R <�f�q�Ӹ�pr�q�߼�n�3t\"O��B�7��(������%�vI��� ���U7�{є�9M��t�D�r07/�A\0@P��:�K��c\n�\"�t6���#�x��3�p�	��P9�B�7�+�2����V�l�(a\0Ŀ\$Q�]���ҹ����E��ǉ�F!G�|��B`޸�΃|�8n(�&�1�2\r�K�)\r�J�: �bM6#ƌ��R[)5�,�;�#������9��p��>41�0z\r��8a�^���]	L�s�-�8^���B�C ^)A�ڷ\$KH̷'.3��|�\n��p�M��\r.p����3���Ƭ�7�*h�l+�6��:��8����`+�+B��\$t<�\0M�w�D�6�l(*\r(�%C*S	#p��`1�Z:���B�8`P�2���6M���pX��݈î\rS�C�BPԔ��I�Y�.s��!�T�,B�9�yc�2ď+�+-S��wG+���3�]�Cx�o�(;,����b��U�Kv��X�j%R�)G��P���ڐ8�X��YC��2�h���ԣ)�\0P��4�\$4\$��rP݈����n�+n�Q���CB �2�,5�7l�8��Cx�3<��h!���T�#�|�*\r����C��9�c�͋�d���tDb��#8´��=�N�(P9�)�p5�B�)Π삼�p\\\n�\0ٍN��J����~��ef9\r�����Ξ^�*XI��@0�I@F�h�4��\0uN��&5:}�B]#��(�:�TyJ@���®�4HN՘\$\$�`\"�\$����#��z;M�6�zhW20���L�UB�*��4������b�=H�5*���wSi�3�B��r�ie�EV�\\`p\$,&�F��~0F���H�`p>��OS�C�Yf��#L�`!��i�2B3\n\"�d��>�Y H��~�aw�SjrN��<d�%�h6,�0�����i.f`Ǚも�#�\"���!�s\"̂���\0�8orf����\nJA �9����y�S�I;0���\r9�#n8��@��l�\r���D���\0/\"���^-'���PSC�s0f���V�q�s�'�_#�H�z	�b���5@LY���0H�y3iIb�pޛA�63��7�\r`m��qO-��d��\$I�s*cPM�O\naQ<��NH�)-�7θ� uF���ZN��atv��C\$�b9K��%����y4tи�ǌ������t&CGx �RdT�F�I<x����5>�穩��\"��k;�15� �0�BL	!h �+&O\nAJΗP��c����\nkOjQ�P%\$8�f�\\VxdA<8Vs�FLI�1����TJa�Ѹĵ���M�*7m��W�>�u_��?&ڐ	]ё0�2��q\r��p/�{7�����UD�4ц���j�\0�����,/E(F�����v\\ �l�f4�)H�+�\n�p�~�k�3�x�}��AQ��C�@`����^-��S� &���,��4��;��z��V\$/�(a��X�2�nH�#�n\\�Ye���b=Ç��`�d(~�)e8zಂU52_H��7YED��?;��b�0����!���4M�s����A;�y�!P*YP��o5.��R���i5>�l�PԇXb�A�\\������t&X7�%ݪы*�la��A�\n��K�V��?�H.,��kB1���5��W��bF���9�Ъ�:Gڮ��.>s�,^��M�Δ��·�����]��Gv�g쌿\rӶ�V���jV��5	�D��6��#K�7퐙\0�u�-G<1�3�\ny&�����d�[�IP(�	�\$:*J�`b��׀m��ț-�vH>HF4��c�ϛCڸ�U�(	����#~w;�9n\\�!��eu���o�t�EV���=Wg�\0����\n�\"�k��%�\r�v��+ܷh�֢��W0�ѹ��/3��#�gs�08F�̘�\$7���o�E��4��%��D��\r�՜��C)3��8��1V!��|<f�nAOs1�q��5yor�Np�©k�o�����|w�^W�A3�b��5�-�/H���/�Vv��zΣo�q�>�P2��iQk�,Z���;�lbU������[���^�}oTr-̠/C�gc�4B��\"�:�ꎣ�U��_��Q\$B�DȚ�\"���\r��\$I��o���Y\0��D��.�Q��/-�9�ؓ* p.'	�\0�u��o��.�G̦-���Cv�'�K��B�~����@�W���o�	/��0�Ч,%E�ԭ\0�Рꐶ�O��P��&`����m\0A�r�\r��`�Z9��-%���%�0�D`7��A���K�9L�p�g�p�0d���Q�q	��η\r&�xfF�Q0�q%���P�7�4�&��ɿq\ni�H��4���Ԭ��,�����H��Z��L�@Q,�:%Q��l��Q��e�o���w����Ѥ�1z0�����	�\r�a��_㈠Q�17e�)�m��|�\\���� ��^c\$F�P.�'؉H����M� ���͠���g^F �i�0ɒ�pO��0�`�x�i��*^��\n���Z8c-Bh�oğK!���\$����5\"8#�!̷ˤ��1 ��D���&�=��<�檢����LPD����X\$����Ĭ�^��\$<' ��Dd,c�덊�M�\$�Bi��ޥ�#H �*��I�����b/I�/n��0B�1m���1�x?��ˉ2I�8���(�&��n�F��80�����F����Ħޤ�@��il^h�L��4L�Ǌ#�f��X����`�3�Ch9q�\$�Gcv\\ql�Sƀ޹d>��sO�Ћ�\$����5�D`�>�XU��L�e 	\0�@�	�t\n`�";
            break;
        case"et":
            $f = "K0���a�� 5�M�C)�~\n��fa�F0�M��\ry9�&!��\n2�IIن��cf�p(�a5��3#t����ΧS��%9�����p���N�S\$�X\nFC1��l7AGH��\n7��&xT��\n*LP�|� ���j��\n)�NfS����9��f\\U}:���Rɼ� 4Nғq�Uj;F��| ��:�/�II�����R��7���a�ýa�����t��p���Aߚ�'#<�{�Л��]���a��	��U7�sp��r9Zf�L�\n �@�^�w�R��/�2�\r`ܝ\r�:j*���4��P�:��Ԡ���88#(��!jD0�`P��A�����#�#��x���R� �q�đ�Ch�7��p���qr\0�0��ܓ,�[����G�0޶\"�	Nx� ��B��?c �ҳ��*ԥc��0�c�;A~ծH\nR;�CC-9�H�;�# X���9�0z\r��8a�^���\\�:�x\\���x�7�\rDC ^)�}HP̴����x�&��F�1���	8*�~¨�Z��,�j�߲I �7��\"��J��7��Y�����Q3�\r#��2�B�[%�H�J��j�{��\n���#����FQ���E�+�Xl�7(J%OB%\"0���@�\r����H���D]J�B	�J��\r�T�0KX���[2���(\r7j�A���4�cZ��4p��#c�cL�\"��\n\"`Z(:hS�7Y-�-�0kR,9���~�����=G#,v��6�+��}�&G�ݛ�L���\"�[�6�F*���Ȓ6�)(\"�<���5\n6����,���\"�d��\\ʲ�jR7��26������c|�p5��<�:�:��6:�J�P�Eƾ\0�3�/j�L(S�2��R�\r�b���)�]U���[e4��q��_]���I��P���ܞ��4��� V��6 @��rQa���~�i�R\nIX0D�Q�A�i^��h��J?=�%=6NU12d� �>҆n�\"Z�ԛ�SޡLJ�.�ᥩ��tRjUK��6�C��L��9*3���B@�!TAP|�ÁaA���qV�� �ⓂRT�Q�wʜ:A�YY��B�xuGe������j�\r�X�Ɛ�\0љ!������K�j����i��(�8}I2d�\"��^��T����+>��%�N���T@\$	ReY�7`����	'��\r�sLg���4F�93�`�mOR7�r\r)�k-*Ag9�K�7'\$�6�>�\r�OA���\na�仅G�������A\$��,	)�F�ɉ����M�����ṮT�vJ:,h5\n<)�@Z薹)W*T���7(X�����N�H�g��Bw��ũ�%A�8���h\nu�t�<�㱿{�8)��h���*K劭\\�:V�N��Y_X/%�З��\n O����Y)��t�'\0� A\n��\0�B`E�l�t�I�-�hеM����cN\"���E��\0�:���y=�!+g�3�c~L�� �\\2���_��'Jr�,gMR���Դ�J�Ǭ�ߗ%���P�ҳ� W=*���a��5v���g��a�M{\$�ʃp:@J,Ť��.���G�6^��`!�,*ER�`�ȧ��+���@�z^%�=y\\Afb;���yLN\n �2�v\0��9r<a\$�δ\"���Ħ\r��!�R��J�\"���.��/��)�i�#R�i����dI�� T\nA\$#�p���`��ن���CU�\"��a�;�9�F#����@��(N\n�Mo\\2������� �BHE�W]6Ro	���~Z�Ӟ0������x \"�[��{�7eE��_�g��ӡ�����CY�*�蚯̛�&q\\��7��hg�T��96\"����׺�eGù�'\$���r,S�#��V_��)�����QqQ\r�p~zK��+6�mW�g�@Ӱ\r�o-�B:��rx���E�Fp���X��2Z�é�7؄��Y����^Bk�\\���sQ� ��Wz\0=�'^�볰1\r�����]�1٥sD�q����EÜ�;��d�o����Y�xG�\0���.rH�:w��C�I�ȩ^���P{2�L�����e��V8=o6�<VT������B����!�f�����O|�\r-��e���vp��e�m�KߒG�S���+\r�ߘ���ގǺOK�v�.F��փ��<m�q���w����4O	��b���:j]O��֫կ��Z�-~���[\nn���~}n��'�1��zr����/+�zL8*T2��@N���=��9�R�:�<W(�����jP,�x`��Qo���:��j��H~�̜K�!O E��BB��h�����v-Er��x����@����>���嬘��[�R��~/�����%%�[e���M�XPg�mO�/l�(k��i�\nl�Jg<��Z�/�/p��أ�=��V�p��n���p�8��n�Y	�=\rн\n��� �ΦB�o���<\0�zL%��F\$�#y�ж�k���}Mz�ךּ�x�Oc.V��r�-�Q&]��P�oG�e�≣q(o�	�d�ʎ\0ܺ,�3�`]�z�0�\n`�~�\$�1-p��N���u��.�����c��0����ɱ�i��X��g=ќi���l����D��Ƚ�}P��Q�ϋ_1>�o�bjH��	N^�`��\"̕\0P	f���,��\\G�� ����10���0���{`��Q밍�#�6߯�	n� �\r�զ�%Bl��:!%�.�����΀9� �F��`�&e�DB)j�ܐ��\n���Z���J;Bj�*B8_�:\$�.�&D����.I\"l�@����ޜ�����P&냊8�A��<��/�ˠ�v��L\"�%\$�J�HlDL�j��>;p6�l�:�f:��깮�s�����\r����,�P��yF�����[4p�\r��g��5Iu5�D�o+����@34%\"�slA�N�Ch��	6Be�Ѧ`fBJ�¸DP�g(�H�WK�p��5�T�&��2�iS̳d��B��\nD �>b�q���`B�Gd�ta>,^�0(� �,�N�X��L)�����e�`/�_6��#|1fJG	P#|f��<`�	\0t	��@�\n`";
            break;
        case"fa":
            $f = "�B����6P텛aT�F6��(J.��0Se�SěaQ\n��\$6�Ma+X�!(A������t�^.�2�[\"S��-�\\�J���)Cfh��!(i�2o	D6��\n�sRXĨ\0Sm`ۘ��k6�Ѷ�m��kv�ᶹ6�	�C!Z�Q�dJɊ�X��+<NCiW�Q�Mb\"����*�5o#�d�v\\��%�ZA���#��g+���>m�c���[��P�vr��s��\r�ZU��s��/��H�r���%�)�NƓq�GXU�+)6\r��*��<�7\rcp�;��\0�9Cx��H�0�C`ʡa\rЄ%\nBÔ82���7cH�9KIh�*�YN�<̳^�&	�\\�\n���O��4,����R���nz����\nҤl�b���!\n)MrT��jRn�o*M)#�򺖰�d���Ԣ��Ō���H4� ��k�� �2°荎���Pc�1�+�3��:B�	��H�4\r���;�C X���9�0z\r��8a�^���\\0�3��|F�#�GR���\r�T&��P�I��px�!�ƌBTN�\\�*6N�J��,T�=�Z��ܬ�4�3��J��i�Q'ru��,Ȯ0�Cs�3��(��^�P�a���8q�ɰb½\"%k�>��z�HR�.����Є��2������u��3�%iV3u�h2�ɬ���e�����\"�u��0�ʊ�BH�\n�!�s��i��>�+��6��VY��FM�������\nH)�\"c�\$%���l.��笗�]33�B�5\\\\���W:Wu]�ސ�'�Li����<\"!�%\n��+6�^C�2l�)���\nC��l��ç|�����,��q�\"Y����C��66\r�JQ*ɺ���\$*d��+��v-T�!G��Ψe.�%77L�\$Db����lAt%>�\$�����=��2����JU|=�'�g͠�}M�1��ߋ�)ȱ��U�����A)� ��o\rh��C�� ��!��:6�S	\r\$ɴ����`!_����3x�I�\n\n��0�*�P�uQ��'���:�h��D��A�U���� |���t`�]l�����k,7h���M�}O8��xZXazr:n��sb��\nQ 0(������Ei��\$�X�ױC\$�uT*�X����V��\\+�y�8.X7,\$B��*'Y+-���]1���,���\\��z1p����AS�Nv��\"�;�o%�4�\"DRi�&�����fTB\rMEF\$N�l\r��1!0���@m�,0�dD���uQj43Y�xg=��M�A6��a�@�1�Ȭ�a\r�̭�d��L�Q|	�%2��QK:�]�BhU�-+��(���\\�z�%�C�\n\n)����.��l/6)V �4ObQ��P �zАg�j�D!T��Hu��(� �IJ��\0�HN}E��j�,��H���&efW�2��#ի�ȸY���x���F� �I#A�N����ꊗ\r�-M��XC��Pa��ؚ�b�Sp�1υ/?�z��U��Q���]R�2����\n�C=���Z���+��ԍ����\"I\$�����1��zy2�~��t�^�8Gv����@�e�&�rJQ�N� F\n���4b&����i�`II�+�i/6J�������-qP��PO	��*�\0�B�E�8\"P�pKQ|9�5\n\$@#S��k�)Ȗ�~T�vB���D�Ye�:`���J]Ȃ���b�����7���t�]峑�]�#D�C]�i!0��t�|}.H�l��Fs�`���I)z�l�q2�p�4��\n�<e�]\$�dű�bED���Q\$��zgȪ��ZRHE�B�l�crJ._�aGy䡷yb�o��]w�\0;���Wk=�et�}�߮��'�\n�{GX�S��p��ϧ�e\$���v�V�\"Z�Lt�Κ!���\\2,JԱEG���#�����7��j3#?\r.�{f��٦�?(c�!5v�''�'[f-?�kS&8�l`��BHA��\0�eP\r�x�eUw	8ǊZ�1M���bF��gJix����J�mݮϭ�]���Nյ3)\$1��y�82P�'4�k����\n�]��!��X�5�U�a�@�������U�Ύ2�1��PS���8��UUiCq>ڒWT�5���\$=	�vMc�_���ObC��`)�N4ښwҜ��0X��r����c.�}��067.��9�QĶd�!i�D������,^H�/���1�����W��4�]7�G/R�̕�kSeM�QfS�������33��C��8�������4�2���s|��������ֺ��z�2�<��c٤}h��yx���\$@1�Y~;��e�Ȍ�tz���3��)�����f8#��P�P�g�+n�j)J>�Z0.����]'R��\$�x�ƾ%j����&H�PH��L�G�r☂n���r�#��o��OlPx`.�CS�saR�p��v��p �T2è:�?'nI��{��?'���l�o����p�	��ك\\�oV��~?��]nP3��ڧZ�\\�P�������r�0�ծ��\n����7�<؇E/T���Q%�F�\r�����12�1:tGh�����_a�GL�#n���IATK�p8�Z?a�	��#'�l�?�Q9n�pO��eA|��F���.�FR��q�<�O�)��J1�ID���H��1��\$���9/�Q+������������4C�6F��L�B�Kq)r��Q���\"2rP��\r<�oK�4IN�\$#���:~�t,�j ���H�n���\"�����k����HԮ�&~5��1\"Col���į)r��8��&�3�xej�@�k:\r �\rn�D�ƾ&g�� �\n���p���.*�K��H����1��'#�@��D�0_,��`/P<g)��� |� ���Kj�2FF3����W#�*�h����BX��6����\$.�\$ø��Q~�̯��0#&�r��D6��h��,�6v��k���|t)\rS��[�[6��2l�Ӕ�����%:�K����;��93��m<7�o�l�' s�:O�%����F�F�Vzl�\\*�>ov,/zkoN���O���r�U7pN�&�p�\ns1�n�.�'B�4.F�rl�c3O4����\np�NvsbPu�Jʬj8�:s�S\0,�?� SE��;�h��jDt*/���r>�n�l�1k��r";
            break;
        case"fi":
            $f = "O6N��x��a9L#�P�\\33`����d7�Ά���i��&H��\$:GNa��l4�e�p(�u:��&蔲`t:DH�b4o�A����B��b��v?K������d3\rF�q��t<�\rL5 *Xk:��+d��nd����j0�I�ZA��a\r';e�� �K�jI�Nw}�G��\r,�k2�h����@Ʃ(vå��a��p1I��݈*mM�qza��M�C^�m��v���;��c�㞄凃�����P�F����K�u�ҩ��n7��3���5\"b�&,�:�9#ͻ�2����h��:.�Ҧl��#R�7��P�:�O�2(4�L�,�&�6C\0P���)Ӹ��(ޙ��%-���2�Ix��\n	b\\�/AH�=l�ܘ�)�X0�cn�\"��79O\$|���\$%��x8#���\rcL������##��@Ā>�\$����0�c�\r�8@��ܩ�8�7�TX@��c����`@#�@�2���D4(���x�W��<ϰ���}1MS�xD��k�'c3�(�`x�!�j+%�;�Q������@݌�S�#�r�5�2����K^ر��(r�R\n�D�D�a(�׎è}_���m[���<���%�锸ӁBE���:1� Wz;\r�U����P�8�vL2 ��=F3�|32[�3?6��P�0�M<Wn���ʃ�R���7(ע��:p�������/��0�aC[Ӈ����r6� �BR�6�EҎ���+%;rqu8�K��q,�r�ÿcl�C��\"�	�\nȶ� ��Ÿ�[�\"@R�[�ds��3��3�@���52���\0�0��2č#L�X\\<8-�d��N-�:Kc�7u��5'KB4�S�J>Χ������תּ���K�'���2��'|��-\$ŵ><��1cϛ4�~��������Jj�{F����͛�A�2�6.S\nA�BR�P�.0�@Ű�Q�v.�����MB�,i������\0i*!�+4�@���'j):�0䧃\$e�O�F�U�s@��r��VZ�&�%�%�\nA�tK��輄�P��l.�	* ��R��J�X͙��jI	1J�V\r*\0@��\"�U�U*�\\��v>��\\�p��j��\$5�\"�>A\0���H�<A��崥��Pv\\h:)�R6PSiK'�p��u.jNal�hB,�x�N` H�(pL��aљ?�U�� E���)�\n'��5F�Xy,B&>et~n�y�	����4RPNI\n (�L�\r�uis���\nL�X@�y%��P)\r2��@(!����z�#j\\Ȇ�Ⱥ�PH\$ŧ�0��a�\"�=7@�uS�,y0���0�MBYwb�ܞ��|P��1\\�-s#�\\I�*0h%�B���A�%�C2��*xǨՊE̥�:\\b�C�:T�s/������I��v�h��\0�T�6��B�e\0Pq���(���[�>8v����Apf\r!���&�J1uk<�'�L�S�9��M�:��U\n` �P(�xa��n�*X��,%d���^Jh�sb�,���2Ac1d!<'\0� A\n���ЈB`E�l\r��\nX��@\nH�]#\$��.�N]�F�O*@�)�:����,��x �L��p�zR�<�,7\$����\r��ٕ'*s�g<3�������p,�`��b��:�f�_E�PL��� ��䃃x:A�vN�<ZY3��M�R��r���0�\$�و�s\"Mɵ��nN6�x�B��Z�dm9�6�p��:65���ƹLpN��!��CsU�he��w����10u��Pћ[}���� ��mK��%f.�g%�2��{QI4��Dj�B\"�ȉ�40�B�'l@ܒ�P�\0\n��\\g?�U�\n�!��@QI:Od!'���T�P.}��^�N�\r_`�k��M2�_t4��l̪w��f��H\r'o�c]���~͒���=��a<�l��7��}�:*�񬚰ɵ�L0��wlt�j��u�D��<��б�#�6���T�M�\$��ڴ�k�e��3~n�Uf��5�&M�U�P)�\"Bd�q�UUS��l�[:��+�E��A���J�9�GA�t.�ח�i͛\$�kD�	���5���Q���N�7�o�%�q5yf����y��V�*+�^]�Ŷ�]E'���NѸMɱ�䢠�TM�~#�d�.��;�\n0�.��tV#d��1Ԫ���8 �� �6��3�\$W�J!�\"���)���w|��d�`�%(�2�� (aEw��5v/��x����}�����X����z��n���*����O{�!�]����4�L����Z{��nm��\\nu���U�N>���:���bv]N��o��O��g��O��,�t�~o������#鰜�@��#��!F�p�az)���>AǸ(0:+2��cj�S�,Rbx�bR�<=c�`�d�\"-\0PI0*q�LL�1K�'��%ȭ�W�AI\nc\\�.���]��0�o\\r�,�-�*-.!N��\rp�0����-,yO���̢S-0D��F\"E��\"`���J�0�l.��'�bXy`���3o���kM&�/X��@�P��#��=�Tn����2\"�pGM:��PY1j%JʺD�N4�fv���P���DJQ|���q��P�0QR��G�a,�/\0�D�i<�I�]��Q̟����F��Q�j5����h�2�\$�	���vأ�\r�`u,(�Qͅ&A�K!���X)���lB��12<p��\0r�R���&���_rH����jG �cnUC`�J8F�n�R��\$�	4Z���|\n�(	(��2��|���C�!�5B�*��.��&@i���\\9BnU�C�0�2IC�5��8���vB��'c'��5�\\��¯bJ~���\$��/,_�4v�db�k\r�(x��sʦ��-Cl�O\0��;��2�\n�0(aL�2���1����R�2�XF���3�&���f`�@��]b�����M��J�i�x\"oN�3�i�E�F\$�2����@1\0�V\$F\"6�s�7.h��K�2�-�ܽ\"61hL^/�pD�&�j�����dLrN�-�QdLJ��@�-�";
            break;
        case"fr":
            $f = "�E�1i��u9�fS���i7\n��\0�%���(�m8�g3I��e��I�cI��i��D��i6L��İ�22@�sY�2:JeS�\ntL�M&Ӄ��� �Ps��Le�C��f4����(�i���Ɠ<B�\n �LgSt�g�M�CL�7�j��?�7Y3���:N��xI�Na;OB��'��,f��&Bu��L�K������^�\rf�Έ����9�g!uz�c7�����'���z\\ή�����k��n��M<����3�0����3��P�퍏�*��X�7������P���\n��+�t**�1���ȍ.��c@�a��*:'\r�h�ʣ� :�\0�2�*v��H脿\r1�#�q�&�'\0P�<��P�I�cR�@P\$(�KR����p�MrQ0���ɠl\0�:Gn����+���,�N��X�(l+�# ڈ&J��,��������h��I%1��3�h4 �z֤c�\\2�\0x�����CCx8a�^���\\0��C���|�ԃ�L9�xD��j\\�\"2\\��#px�!�t �*b`�%3T؎ۊ�v���������1�r��%�xNv�zä�T`:�#`@ɍ���:B��9\rԲ:���Ɓ�N!�b��7��T|*#�}���:ʲ6T����Σ�+(��ׅ�,��7�� ˉ��+�#;:L��X�>��s��{L�R��a� P�9+�P���C{�9�/���6�����R:��\n�hπ�1쪒}P�J}\n�Zvda�Q��(����:3���1��䘧�94\\EL��+��P9��0�yZ`�#�Y���GE�oܴǽM#t��#�����@�6���\"���͗����We3����\"@TƓ�`S>�hF©U\0�ׯ�*t\"l��kcx�;�C;!;@:�uJ�-Vp[\0���F�BX��\rɼ�\0�����0��Ȱ1RM�;�+Č0��Vo�50L�Xw	:\n��5��@Rǜ��R�uB�<(�ՙP�A���++L�2rЛ��e ����I	ZK̒�@�`QU/Ē����ҮVP�j�O��[\$�)���W�*EM��+÷�A��O)���E\0éW%9/���J	�!�T�X��C*�V*�Z�ur�ڽh@�`, ܰ�z\$�ee,ǂ�Ybz A�5�(jKT	��hfS2���C(�*�:(/����S�JcR��#�\$�T�;�^�(�n��� EtΆ5(�1�&!����<�	��l��R���:':R�'��X��2�Щ��(�P	@�UK���\0��CLW�`s\$,4�:F<�\"�t\r�ݔ\0��؋U!'���DPP`e�m7�������H*J����(��:�����O9��T�S'&<e�����H ��Ӛ�V��@2d(ݣ�*��R֒E�|�J9.�J�Tpk]��zD\$(�	�c}�%���N�߬��շ�0���v�(�Ժ�aI�5m����y^�I�\0M1�P �h�!�,���1�x���I\$E,��	]�R�iEY�l%d���ғX�!2La�+��*��,M�3�\$�XH`c]\$�2�p�p \n�@\"�n���&[�����'�a��+�O�#p5�nX��zN�a�K<8-U�p�dB����\$dÂ�?�H�9�u�=E�D�c��P���?�)2�71b��4(��#���͋��(��V�q�%��63��Х{�v���:g��ܾ*v鵳���m�k�!\0)�5�*GB��g�����(�!%ܞۦ�(�}��I�=���V�q;@����U��h�����Õ�LWVU5������(�63�R��C���\r����;�ꄙ@ӈ̓�Ɉ*8�RC��1k�d}@��t��~��-�&� ��J���6�r>}\\���n/�|yU�'�~I��dA�@�ЧBkDو���GF��H°tk�C��i��%�l`�T!\$fú�\nS%\n2��r_Jΐr2����{W���\"�D�L_(eb�E�t\\�C-`G��&�ؤ�Hc��y)5R��k�Y��3�P�WJȎ3����3���F��n3��2Z�o����1~\0�9W,�桟�:�\\I�E+�>ZBzg���TO'��{gP��pr�o5��\\�t&[��gk&@�LPӶ��ĭ��1Ls�v���ۂ�eSϰ`.�tB���̷�b	�~�1Gh�w�=�3Z��;\0��^�n?f>��a�ב;��J�1��N���{IS�������v�|��ҧ�L��������Y�������a2�_/��O�N��g��Oܲ�'�х�o��~�M��	>��5����^��\n,��`�`�b6���Զ/�N̘f�H��B�np)�dD%\"V\r�Nck�y`P���7\r�/��l��L����\rk�b�\"y�<�J�j���c�Jr�Jv�N�o���R��^�L?�h�/�䨎%�N?���Ǘ���G4�n�����^��\npR�.��ЮC�iNm�s�h�-l��sϨ�P��iP�Mn��P�0�	0�!P�-q�i���\\)K�c�R#�R%�M�/�bM.��ΐ�/\n�!T���d �U\"�\\#h\0����b �\n\nԱ~Q��³��\\x�FV����R�g��ˍk�mr���.�2G�\\�\r`�xc�=e��\\�-'\n�#Ѷ�Q�����Q�mOf-�\r��Ш�0X������B����E�V����C22q��zy2����v(m���\0W�HEORcc\"��fJ�!Db�/qp�Q��1��q�\$p�0�%0��0��2oŤ�Ū�NH_L�!R�&Q�~&f�'2� �=�e(z��D�+\r�׭�\$�ui'd�l�Gm��Z�ƾ׍�+»\n��+2u�ح},�-�-���਺�hM�!����@��K����ҳ/ĵ0&�,�0�O/��+�Bt	�u�Rڭ�0����i���=��PP�F3g\n��4��{�[R�8E�1�.��4!�\n�3T�n���2��3�;D.�rY1��b�\r�V�@�ͣV3���M�6��'��'2��O���G\n�-�1c8C+�\n���p��%rbj����Ӓ����rx\"D\$��L\r2��'��иori�ҿ�;��@mʰ�@�\\T KDߔ ����P��TG~=`�{Њ\\r*ag~���w�!�+C<42;n�)T?1,C�pgp�BG�=9Rc,l0�H��Ŕ���	I��ԟGυ0�}I��J�* ��1}öP�GZtQ��B��:iƢ�%�ߧ=H��K��mc��K|)Qrw�Ok&o�.����OV<� �\$\rL�ċ���иc\0C@�J\n��ԇ�\r�:'�\r��:-wIl²�e�{�O��&x��1��%ȍ/A!e�e��l�D\r�";
            break;
        case"gl":
            $f = "E9�j��g:����P�\\33AAD�y�@�T���l2�\r&����a9\r�1��h2�aB�Q<A'6�XkY�x��̒l�c\n�NF�I��d��1\0��B�M��	���h,�@\nFC1��l7AF#��\n7��4u�&e7B\rƃ�b7�f�S%6P\n\$��ף���]E�FS���'�M\"�c�r5z;d�jQ�0�·[���(��p�% �\n#���	ˇ)�A`�Y��'7T8N6�Bi�R��hGcK��z&�Q\n�rǓ;��T�*��u�Z�\n9M��|~B�%IK\0000�ʨ�\0���ҲCJ*9��¡��s06�H�\"):�\r�~�7C���%p,�|0:FZߊo�J��B��Ԫ���EB+(��6<�*B�8c�5!\r�+dǊ\nRs(�jP@1���@�#\"�(�*�L���(�8\$�Kc,�r0�0�l	%����s]8�����\n43c0z\r��8a�^���]	�jP\\���{\0�(�@��xD��j���2�Ȩx�!�i\$�/�,;\r5S� #���!-��7��+pԷ@U�f���x�\"cx알�07I�P��\r�\\L��\0�<��M�u]��!\r��ھ�B�ҍ�qs\0��O#\"1�v��:O�r�K�P���(�\"�����\\JU�*ǈè�]�e�\$#;63�pЄ:�c���0�߉�4ʨyk\0��(&FJc�&\"�gt�	��p�5�Ӑ��R�J)\\��\$;��7�M�+�\"��&P#(e�+i�6rR!Oem�sr8��,p!�n��oM��'*�B�9;��\n\rCT�A�0��/8�<M�~�2��>��Ir^�\r�@R\r\\�W�>ʴzT.J*�J�{p�#������L�_�j��r�	�\\\n�����]��i�z�w����\$>'e�x��O�m��]>�|��[\0b��#\$Cp쁍�x�/쌝�[D��72�J�qK3ȥ��D��I�w\r=�%��F4\r\n�� xa�	�L�%��C%*(�U�>*��f�P�X�:�C��_%!�0��R��+[*����e�z1u4�a]�ؖ��\\��(�ʢ�b�R\nIJ)e0vҜS��O �\0GH��UL8-��a�>%@��\n���P�=�ح�V��_啒 x&�9CO��0@�\rQ�8hA|��l��y5Q^I���P��!�Ҝ����f3��6��v%t\r�O�0���<)��N:���d@j!8���T�,ȍ�P�\0��\r�fM�\0PU�I�7�L��vG�\"�b�6q �&!G~��Bt\r��;Ĕ#0L�'�o2Ā�|<��(���v��/`Ň��cZ�^B�f>��Ô@� ����&����0c�'�>��4�`�䩅�2j8c�HI\\\$�	p5��:���8��Tp�Z���Lx���\nQ\$:�rU\$`5\n+�E��f�\0vN�t���z*��(,�LgC�cİ�؂e ��0��vJߺ3*M���	3��P��4x^W��ƙ������Ķ|���J�L\n��C���BlBT\n�&��B��	�8P�T�� �`�R���@�-µ�R_��R���Ԁ!@�4^B��E)�ԡ�P�\\Y�e���z���Z%�_7ˡ{<���3�K���P��ԯ�� d(�D�\$�#��n��xn0I�=���ㄑ2\$C�Rx����yz@���ÊT�@�������M`�H6�I�6J�����*�i�j�f3l�ǅ�EH-!�����T�Cu�\n�y\"��\nN���.���V0ӝ��\$h�ʅdlaU��6����޾r�\nb4����V�)FY�8R�� '`�hHԌ\n9���� �S')�G���i\$˦OB=gᥔ��I��G&�:�e^��PRO��ٷ���ns����H��	\0���LJ`oM&u69Y�ԃ�H/�8Ι�~��x ]˧l���KC`a�u�p�d��P�bJI�	����b�L���������n⫷�TѕxM�\"�aNE��A]տ	A4]�|����.�Jt�{q4�i�߮���Ew[�=M�o%d\$�[(��zU9�]�!���3�Oќ��(9Ys*��3�Y����3��<���{�z*\$Xo������	a�(h\$#fB^�kp.�a����/�	+철��ɦ���%nwt�M��F]�=�V��}���}�Vcm%�쐛��?�o���wݩ���Iٵ�d�OC,���a\r�����gW��l��WhJ)�[7T��\"]5w�^Aϔ����f58E�F�����z�s\n\n%�nhא2�5����΂�a�9V���F�8�&����]X~h8� �\"׍����Br2����� `@X�j����\$��n����	\0�P��\0ܰ���1n��M(\$+:���O4Kk5��1�J���&��䏪��@/\"���b�p�&\"�&���3�T&��\r��PLF��\"ϤK'ʚ+���#�j[����ZQ\"��*�{�vAP�?+2��L���Z���*ь�\r��1�:���_D�\$�4�o��sp���0Y���O����������Q\nx0S�����p� �\rv�*2/~<��j\$\$Π�j�\\��R7����ёĪ.oQd��!QV!QZ���N��Q��KH�f<�zn�Yg��[/�scq�����j@�IjL��[c>�0��nG�������B������� @?q��+1�S�2��oE���QU\n���(�� &� �&��H�`�b�e�JZ2��[m]��bX\r�(b�4-�#cZ�\"�\$\nNB/�1��}�\$�wqQ%��%nއ��ox\$&侓��F\0�`� ƛ@�3h��\"ᣜ1�j��r)	�p\n�%\$���vz\n���p���0�F���n\\\$Njॲ\"�2#b:@�N��h���YO\"m�b[�ldh�1�|��Ԭ{Ϙ���UR�X�J�\$Fң�B����S\$�gh�c~3>(2���f�<��(����.�8Ylp��\"@�X�@�i�C6+6L����\r�S6/A+�)��n�8,Zo��GXC��D�VɅ|��]7k��k��K�I�A;o�;�.�g�\0T �(I�\r��?��\r�I.�1���6�\$�?b.�,�2�N&C5��%D��o�\n��3�6����B�VF�;�e +��";
            break;
        case"he":
            $f = "�J5�\rt��U@ ��a��k���(�ff�P��������<=�R��\rt�]S�F�Rd�~�k�T-t�^q ��`�z�\0�2nI&�A�-yZV\r%��S��`(`1ƃQ��p9��'����K�&cu4���Q��� ��K*�u\r��u�I�Ќ4� MH㖩|���Bjs���=5��.��-���uF�}��D 3�~G=��`1:�F�9�k�)\\���N5�������%�(�n5���sp��r9�B�Q�s0���ZQ�A���>�o���2��Sq��7��#��\"\r:����������4�'�� ��Ģ�ħ�Z���iZ��K[,ס�d,ׯ�6��QZ��.�\\��n3_�	�&�!	3���K��1p�!C��`S5���# �4���@2\r�+�����8�0�c��\r�8@0����#��;�#��7��@8N#����`@M�@�2���D4���9�Ax^;ҁp�)J��\\��{�σ��@��\r��*��7?�px�!��9�RW'�j�� m+^�%q:_b��L��&v3a4j\"7�d�榥H+�#��*��J2!q�|���k�vc��\nf����L�9(j�\r�-���ű����u�Yi��ɯ&'�>'�TN��8����� '\nɮOƆ�k% .����k��8,��!�B<�\$rw\$��9z��=���JD)�\"f!5��]d5��y^G���'ijq�mb\r�����Fs�-z������@���z��{&n8z�gn�s�i�M|\")��rC�����[��cI2!�H;���RnD�G��Υ��wa%ij_��H<=̡WEԥ\\��7\r�I�8���s��rH����h���:\n���#�2JM� 2b@���=yu�n�z�!am/)ʯ�M�18�3B5E)a|�!,Y;Yְ���:p� 9-Тt�N�R�x����c2f9;D��,��T:�Qj5G�&�^ڙ<jqO�<~���@\n�S�Jb�;o�b���C#����L�Z�&���Xq�CjY.X��\"N�{ \n���&@�At\r��#���A�2�@����a�2�p�b�l\r�*E�v=��.\0ơ�!���64]\"�F��?HR5� \n (\0PR�L7c�|4\0�COM1�7���C�i=a�.���{�8 K��)������(P��^LȃjK�t;�@��Y�%��Λ3pC�.\\�0����/��\$E,�?B�4�K�v�fÀZ�^�V#�&��Z�!�;�T�V?\r��k&���/2L�aӘ0si�\0�¤�#�����(<'��� d��A�TAˤRFf��G�=�����b/\$�#II���G\0��b�H���E� �\$�:i�cR|L*��rG���f	ǰYz��[95�Џ��R̒�'����t@ت-l8��)d���4�����tH\rc�k�x�����۹�O�[��؈\n�oԊ���!Q��ao�ɨ������}wM���2��y�	s�aPI_��L�S�)%,�28k!�L���d��|��ݣ#F���!N�-=#kD���ֽ��'�\r�6\rNL�7K(�7q\$�X�&	+v�Q9S%d՘.u��nӮA6U���K��#\nj5�B��_oj�A�������Wj\n'� �	��*�\"����b�R�V-s�t`t�{2��� ��Aa!{NG@��Q�m�#��dH���Mq�VYW1v�+��\$�*A�)0����,G�fdnk�2p�|��\\Μ2ԓ�E�m����]R�y����)YC;��i#QՄ��B�&%��B�	�__M\"��ʋ�`4d���S�39|n�V��\n�OVfX/W�7p^��vd��NJ�R�1f؀����j<��9� Ŭꜹ���CT�L�|LA�d�2��y3�3���_��[v.\ra�J�k밂\n��E���5��l������D�s��t`=â�vxy;�����\rỗ��@2��Vf%0{����hB�E������{^r�s������G0�\n���W�\n�<���k�v1�\$�3`�:�u�X9��<�_I}�5�\$#%N8]�0\$���~o\0�Mm����q��a˅뢧n9���Q�-f~ڌ4�Q_D�<.>V�-�C�\n�i����W^ѽ5�u;�f5C����=��������x�	韽U��H����m�Y�e�����v��W��Y�PI�I���������7s���o�y\rm�C�����������X`RB�ޒ��d9P�[ޫ��hU���I\$��|M�|w%�k?�l��w�4�6驺���&!�k��S�=�۾1i3����u55g6�lMih9Ϥ���X����*���������dJ-k��'�1��NvF,2�*��0{&�k-n���:(�L\"�0aN5���b�k\$-m�~��'fj5�Z:TȨ���J`��PV�E��\$�Nf׌Ύ��H�hd�F�#H�dE*Z�#l4��vB���8n�C���\$vO ��q�\\�f�nf`Ͱ�t.-F0)���d�FK����\$E)�u\"%�vB@��:ΐJ-Ct!�<1\"j/�'c�\\�7-��W\"Lr02Ex�p�&6�/���]�#H�j�aQ\\g�F�kַ�,��j����&4�k����1'����,�&��9�5���j��j�n�#q'JXy\"��a� �̦jO����0NG�N����P���;�( �#�<H��!1�!'쒂a�\$���r�";
            break;
        case"hu":
            $f = "B4�����e7���P�\\33\r�5	��d8NF0Q8�m�C|��e6kiL � 0��CT�\\\n Č'�LMBl4�fj�MRr2�X)\no9��D����:OF�\\�@\nFC1��l7AL5� �\n�L��Lt�n1�eJ��7)��F�)�\n!aOL5���x��L�sT��V�\r�*DAq2Q�Ǚ�d�u'c-L� 8�'cI�'���Χ!��!4Pd&�nM�J�6�A����p�<W>do6N����\n���\"a�}�c1�=]��\n*J�Un\\t�(;�1�(6B��5��x�73��7�I��������������`A\n�C(�Ø�7�,[5�{�\r�P��\$I�4���&(.������#��*��;�z:H����(�X��CT���f	IC\r+'<�P�lBP���\"���=A\0�K�j�	#q�C�v8A�P�1�l,D7���8��Z;�,�O?6��;�� X��Ф��D4���9�Ax^;�p���pl3��@^8RT��2��\r�cZ���`��Dcpx�!�n*#��6\$�P�:C�֕1�����JR&Y���0��ς(��6��q����M\rI\n�����7=�xJ2 ɠ��w��2��:B{\rh1Z8�c&ʌ����#�a���\"��mc跈�(�0��H@;#`�2�B[f����ì1�2�֜�:�3ʨ�b��O��9\rťI��7.x�޼�c[7F�\\�8DW2mJ�<)c�)9�R68n(@9�c�i\n\"e\"9n������2�}/�h��u�7m���|U��]���)�	��j�k�p�D��i6(6M��3�#�{��#l�gh�x�<vxC�/�6�s�uW��y �\ry��܀RR�4�E�֍�0̠!I�d�L���7��FgS�A�O|7��\r/j)��0����Cv42��RM��Aث�5�B\0C\naH#\0���`���\"�<���|�\n|�\0�4�@�^��Yf��\$*�Op H��)pƉsJaM)�<S>\$�]��E��N;:\ra����m�R=�\\ʛc���i<��57Î\n�;V��I�S>�IH�QL��|��\n�T��T��h����VHy\"\$H�U۱�&�H���)-5~2ta�J#!����D�TL!\$2�I:�X��r�U��Z5���7��1�3d�1��H`lnjI�(\\��f�-�:�x��es�~�0�Zw�� q�䆖ZɸN.����4eL1�1�pɿ�Q��t3��P���@� \n (M��8�˔2R2>M�\$!R���blͩ�_�䂜\$�L%�.P@AS��xd���tO	�@2%hΥ���T�Y�L-��\$R�+Rt���&M�I&�:�C\\�HD�8\$�^�PfAd��ٜ�П�\0�+IL��cp\"��C\0�¡0H���!l֎Hs_��6ѷQ�J�R챊�\n_Z�I?�YƑXF���nϒA��V��iP�M��*�� �Rz�e�O]̑&U-���5N��A�����A�d�Eq8Ӗ׌q*������xp*a��:u��t!V�\$6p��C�<g�Ő���:��Un9(��N}�&�霔�@�v�o�0��3X�\"`(%=/<S?xbe������\\�q���'~�C{�\\����o�{?\n�����f����P���\n���V��1K�0���y�{��3�}kgEb��������S ,�'�����.Wp�#�o���(	~@����m��t\0�������@<9'��AL�!ʃ�����	6�Z@�7.��tP�44|3l��wD�f��\r��k�	���6�2�>F ()1��S<�4A�г���O�?J͙6gP�~ T!\$\n��M�ӧ�8;�&~�KQP��5�unV��n���E��\0_��]74vuiGj��B�(�Bs�����/�Ю�.�eP��\$��s�b���U�Q�١�g뽢Q��I�{_l�-a�!.�A%�l�鳊^�{H�n��6��ެ��f����!��x��Y��\n�b�p�dnOTjn@ż#F�u��9l��q�dJ9	�BL�1�S|֑��dh9��C��p쨷��93�T'q�g�Taqjp�3�W֩\"�p6����O��9Q9s[6FZĴd0GK�K�����^��ْ��k\$�j%n�.>ᅗ�X�d-��`NK���������#)�����R\"��ګ���*Oʽ f8 ��J�a�[�+W���\"�E0���Y�\$v�}6�|��M�����3�pم���H ���\\�ğp����Gr�^L=�����((� :���	�m������_�\r����F���w����8���|��n �x��&��2`�3�Ш��0���\r��m�߭�0~�0܍���6P �p&�P\0��C����6�F��ʾ��R�HO���0,���%o�:�\"�J �ƕ6��@���@��3 \\Au	'|��6\re.&0�&� ��a�~̨�	>6�+\n�.<n�e���jPOĎB�sb��	X�KAPf���˰����L'�;f��ҋ���@'/.[�j��dS�̾/��w����q2��d��\r�q,�����)�J�Q[o\$���J.~/��#��?q7e�	b��\0�`	��!�\$�*j�`�#:B��ū\0�+�/�(�!mAh�frgo}�x���\$�v���OuB�xq��\r���Q�t\0����;��:\"^���g�\0�\$�/\nZ��Q]\0\"\$�j�͒,�q���#q�s��;��F�|�j<��M\"*�!�T���%rZ�o��R���N#n?`�3q���P�OC�j� CV�F�`%�;,��ʯn�/�{*,c��*�2�r����+�~����\0�%�\n��V�N�A��2�a�F�ߐN���2���ײ��9/.�Z��j��*���be�\\5�W��\r �~%&����\0@\n���Z,��&P�o��vD�?��5K15�5�I6'i6o�#�@\$BH\$�f��n�^&/l	�/2�\nD�/tNt��(X?��:#�<c�\rL�;�R0D!Rꑼ�\"(s�n	�_�z)GS�Uc�C�7�\0cC�\\D�n�:oʍM[έ#��d0�������3BQ).�A��A�|\"��A�_B�`Yd0 �T�b�rc��ȃ\0s/(/�*<q0+&��\r��f�n��l���\$<l��t&� f���1\0O��uˑD�Z #���?)Q�ѧ)@�5e����A�Kl#B	Rj�\"�����f�-aLR���*��a8+ �)C�~bH�\$h3�d-af�MJ@�\r�";
            break;
        case"id":
            $f = "A7\"Ʉ�i7�BQp�� 9�����A8N�i��g:���@��e9�'1p(�e9�NRiD��0���I�*70#d�@%9����L�@t�A�P)l�`1ƃQ��p9��3||+6bU�t0�͒Ҝ��f)�Nf������S+Դ�o:�\r��@n7�#I��l2������:c����>㘺M��p*���4Sq�����7hA�]��l�7���c'������'�D�\$��H�4�U7�z��o9KH��>:� �#��<���2�4&�ݖX���̀�R\$������:�P�0�ˀ�! #��z;\0�K��Ѝ�rP���=��r�:�#d�BjV:�q�n�	@ڜ��P�2\r�BP���� ��l���#c�1��t���V��KF�J,�V9��@��4C(��C@�:�t���(r(ܔ�@���z29̓0^)���1�@��G���|���Ғ� P�O�H�B������V˻�Z��.@P�7D	2e���ޢ!(ȓK�h�7���%#��c�0�\$�3m���!\0�:C�՜\"M��6#c��6�(N�#@#\$#:�!�jGy�p��l��r�5���ۯ��끵�����	��)�(ֈ�h��Ӹ��Z�[0��C�֔!�J)�\"`1Gj��`5euT5�J9�c,~��.q�9��s�m-B(2��09�BKV�V؜��Y�7�\r�]���\" ���rB�;�1�x�3-3�Z%��.*\r��<�	�)ʣ5�Y#:9��0�h@A�XH�ی�@����r��b��#)�b ��\0�4��n���&9\r�H��Z��7Beʱo\no��2�S!��D�1�Ȥ�51Sl�Fa|2,��?LSK�al�c�4vx�9�@Җ���C�#����3h���d�8NS��<OC��\"H�M�r�?Dx����pp3E���G*_XkqﳗF��H\n��#��lZa{1J웠�����JJX���؂�9�%���4(hI�{J�!�1���j^U���:�#�As������\"*\0�5��P	@��� �: �بU\0PC,��1�wS��2�d2�6�	��Ie\n���IhAbfn)�\"b�u D��=�@CiE\$��#�&j��]u+잓�r�N�r:f<0���P�-	\$<<��v���L %<�t�WJ#uG��<�-�HY-2ZR2f�Q a@'�0��C�E]И��D��B�K%��2I�;M������0i\$��\r+��3�\$��%��Y@LuQ�b��\\'+�%/.��D�g�ƶV�f,Ey���P�*PE�\0D�0\"���D�k*�4B3(��(]l9Q��Z��)fM0��#�I�*9\n����uIs[Fe��Hr�R���vF\\�����b�h�re3�J`٥]BhT�#�@PV0�Ŏ�5R�(����DVlX�W�Qj.�t�H�L0n\$TLH�i�X�R�VƲ^C��F��\"c1c�F�����Oc��\r!�;�u^m:�Z�ى��dB-a.���骬Ue�fG(7P��EhF��B��ni�s��}�3�]�P�\0��m�B�1��	�F��Q�\"E�p*��d�m��ޓrP�n摲�JA�d�-a��Aє\"!-t�Bh��lM4��)�xl@u����d��3yW����hP	k��X�-����9�.����)<�܈�Q�A6dd}x�7s�ɱ;R��O�#	B��(%�ԧ4̒���b��+ʌrJΌKȅ5d@@����9���ʌ�.�iA�4���uW��:Ӛ���%vuV���q��&'s}]�5m��I��UQ}�B����r��'>Uw+c���_a�Vi}%��8ie�h����c�QJԝX-����D<�������\"`�4k}rZ�2b��`jզ@kΙ�zoN���M�]yY�\"���X���)���i���b���&��\r��Is���0���`���M[wm�ͨ	ޜt̑+[u˻�Z\r8,Q�\r4��+�'�-�w۟J�V�C��t�3}�}D]O�z�i��L�m�G G��\"�b�7#���p@�}K�N}�BI����sIL�m���|遛iQ�����Fߝ\r\"�\\�B�Ē�jV�n+a6�S�a>ÿ�&��֛�}�ut)m[2v�Zn�i�ݯ�LE���}(<����l|��^#a�Ą��)l�0�N��D�����ܱ�1�\$Z���]������%����^�I[�^~ɖ8���S�^+������к�W��3�ZN�*�=�>��tni�W��	�=|2�>��}P�g\"�X�/�U����f��˼0F��}���@�\n���a7�\0��B�L��`��f.��.�	;��l��`l&d\r�Vb�b,,��\\��4F�\r����K�Z��u�t�'\n���Z䷰H7�B�#&�D��Ҿ���4�j	�oˬc��D��b�;e��&���\"x#���b,7\"@fE~(��W�Z	�ޞ�M��TP�_b�:B�_��4�.���F�N��P'b�,�@Ȱ��&�b��M0�Bbm�<�F��S��_`��G���f�h\"��bU��,��k�tưF��c\nh'K�9�6� �+[�@%���,�\$�� ��.\\�� >8�F9\"c���\n�+`\$�P�����1�5����Zʀ�AƲa�A�1(p?d �.p�`";
            break;
        case"it":
            $f = "S4�Χ#x�%���(�a9@L&�)��o����l2�\r��p�\"u9��1qp(�a��b�㙦I!6�NsY�f7��Xj�\0��B��c���H 2�NgC,�Z0��cA��n8���S|\\o���&��N�&(܂ZM7�\r1��I�b2�M��s:�\$Ɠ9�ZY7�D�	�C#\"'j	�� ���!���4Nz��S����fʠ 1�����c0���x-T�E%�� �����\n\"�&V��3��Nw⩸�#;�pPC������h�EB�b�����)�4�M%�>W8�2��(��B#L�=����*�P��@�8�7���g��^�2Ó�����t9��@���u\0#�@�O�\0&\r�RJ80I�܊���6�l27���4c��#�#�ù�`ҮQS��X��Ɍ�G�C X���9�0z\r��8a�^��\\0��ʴ�z*��L�J0|6��3-	�v��x�%��T޺C��)��-,�-�M4�*c�\\: k��/��8��K���5���6/�r�;#�3\r�P��\r�r��\0�<��M�eY��7��\"�\n�L�i�����+X�4[��4�#��#�C`�\0\nu�b�/�3yؠP�3��C|@����8��P�0��R�����-�ph�Č��F�*6�\0^սj��#�nd�\"0)�\"`0�L+���5ei*.qXU�k�1��Ї4T�2����q+@�6ΰ�H�%K��9ꚶ�2���iyЈ!NA|/�\\<�2H�B7��3���+	l\r��t<��D�Ì�PAj�Ü��o���e� \r�p�aJZ*\r�Z*b��#)�-�4�Ap@)�[8�W^�4�s��.J��2���jܤ�������(�5t`��&�p�G܃1���5�̬��5P��DeKcw�R4��(&���3����j��U߃��J��@�s���L�3�L�5��ޜS�u��<�����>�ܚ�E���p\$y���jЪ[Y&�������rGH��Bp���\r\$�^�`�	s#��U�ҐcQ�,�\"�fL�\"�\$0�g��;p\$q�8ĲA���L��0�č�mmԩ�X*�]	�M�P�a�JS.����hd�H\n	 85�zAF\$Ŭ@��	-kX2�\$�hY��\$yd��Vj�rLV�;���Ȫ1T1�ё�.A#�3/�L4�`���1�Q)I#���I&D����C���	��1�:Aḱ�\n,9�ƵR�X�\n�Jp�*Hʝ�<�0Rt�\r48ٲn�d�[��֗�Л�I�/F���ΪX�M��2cE�zxJK(�X\nM��*G�R�Z�K��\nL\0�dQ�9�B2�\$v�ʷ,|'��@B�D!P\"��L(L����H������y!S���SSl(8/�\n��S�g\$�4��A���֤���Uم@�U�EB�QE\\J�l��Z��9�S����I��N�\$��Ơ��ɨ\n\n�@4ã�kˑf\n�\n3�����}��QG���Ӓ���Л�(��H&�x�H�D�¡s�f�Q0(�h��Q�ʪ��T!�/ؐ���_0��[+����*gj�V�ۀ@L(SO��\0��\rڼ�lقY��GTI�\$�h<���g���yP����K�Xk���\\ʬ��Y�X!F�*��	�2Ѷ��V�L,�=\n�P �0�&)3��_�������G�˵��Õ�Y�u�ZC\n���0��yg��X\\�7������t�Kv���`�]�\n.#c*+��zKcy;13�n��0ih�e���,�}�r��)��üE�0�X������C���YN�\$�A�\r!�FU�\n�k�u\"�`�_��,Ep��{��(bШw;�ט󽀚� �a5p�*��\0�3�-*y.I΋(.��aY��U	�`��ʁ�rɺ����Qh{٫[g��p�&���Z�k2���+QS����b��.QZ���5\0�kT�ƨ��KY+�R��dD�Uj�`+s���Mv���m���q�6�g�u˭�0Z�\r�Z�����PC*�;�\0U��5���\\9��G�����G������n�ti\r+����\$@��!בN��<�\$S_Ts��&/!�Hq���-������m~�dd3ݰ��9�]\r��ލ���D�P�zp>Q�`��6�h��Ly\rU�;��M�¨�\r�6��j9�IK����N8\"S�}���;�|�z��7J#wкgN�����<.�\r� �^\"��i�zA�è�Gd�ݮ��^�����ү��V�;z�v�o�\$��K5{�1���V��������7�����Q���y�{E����yG�ټ�w�o���q�|&|�|j2�{�\$�Ȫ~3\\#���{5]���u\n��\0�G�1���0��HOJ֐�ǎ���溻���+ʼAB\"կ�\n�@��~p8�P�PBbo]@�'C�D�ģ�^�K�0�PW�Y��\r�bm,Ռq��f���,�!�/Şq�..B�0��/�80e԰/~\$��4�mz%�)C1Ⲱ�C��e|[���`�9@�i� +�1��bږhRE��B��PH�q��\n���p=�r/G�%�d1�\\�&���-(�&H��+TǌVk�V\$bJ\$�hf����PW\r�'f�Ȩ�0��-����*b0]���Y%��p%�z9��qF\n�d/М0B1\n��X.�H\$�0VNb�,��%�CH_�܏��}-��1�����{��0<��|��@5c(���si�7d���\0005�/�()�BU�`@�g�vV���\"\\c\n��q�\"�Sk��+�pi8���j�I��@F���`�-�2�i\"��K��Cg&���ު������'f�q�݊�X�Z_F8�/�J�v,��	\0t	��@�\n`";
            break;
        case"ja":
            $f = "�W'�\nc���/�ɘ2-޼O���ᙘ@�S��N4UƂP�ԑ�\\}%QGq�B\r[^G0e<	�&��0S�8�r�&����#A�PKY}t ��Q�\$��I�+ܪ�Õ8��B0��<���h5\r��S�R�9P�:�aKI �T\n\n>��Ygn4\n�T:Shi�1zR��xL&���g`�ɼ� 4N�Q�� 8�'cI��g2��My��d0�5�CA�tt0����S�~���9�����s��=��O�\\�������F�q��E:S*Lҡ\0�U'�����(TB��5�ø����7�N`��9-���A�@����A\n�C(��\rØ�7�.a�K��.r��zJ�RzK��12�#�R>\\��B�H*�AU#dp��DBA���Oj���E�8�i�\\��A\\t�/�>�K(� �ҡlr�j�H�h�^��dL�*J��-*�^A\n�f��øs�D\"��������2\r�d�{r֍�@9�ÄO#���Oh@0��0�m`�4��5H�V���`@U�@�2���D4���9�Ax^;�p�JR�l3��(���v9�xD���l�=�46�1��|�#���*��9t�B8I��,�I(\$I�M������	]���I�P�96W �q�^��13���7=�8��̫��d]'�(�f�Iy��_��JȂ%�0��e<;#`�2�	psO��KG4a�2sd|s���ZNiv]��!\"���|���)����V]��\$�n���J�d�#h#cwS��7=\"���#��\"�g��lns��^�į*G�R`�D�Υ�)Ob�At��N�=�}ߩ�PT�S=��4��S���w����a�Q%����{KS���B\"�t�Zc��M#MU�p��3\r���F�	B?4����݃p���SU6�\r�x�{�������O` � ���P�N(`��Ap#G0�dU0��2=J��A�39D3i�uܡ\r��6�8��y)O�CR����O�T�p@��r	W��\" ȥ��Xkq�0D����x/%� ��*#��A�6fÉ�w(�)�\"\nca��wd�C� SH����U�kt9��x��p\r+2�~�b��Y)f,�֒�\r�\$-u���� DH�q.@�C��\r�h:E�|���V���K��B�%��ȿD+Q�3b|\"O2<\$�ǈb a��#��C�X�b�\$\n�68 �k%�r�R���� ���T��8'���:�8��4��eX r2V���0�#�^Q�*H�l�ΘsY��P	A��jA(5(����Cɝ#�\0����0Id4���?P�F�\$�qmUKSblͩ���Q �n�2�T�t7�zw`��b\$Å^Ep���T�Q+��D��\"8G�t�1�#L:��I��f�� ��&���%3c�{��E�H�y{�4��\\���n���,0��Ú��,6�y4��\"ڟO�X�J��M��\n<)�G2�ҵ(\$�@�qC�1\$28<�4R�h�U�XW��^�`�rbE��*i\0UʯZٞX��|�P��w����Zj� �������h�0T\n\n��@�P��U���}��^E �\"\r�:��!E;VmC�]60��\0U\n �@��8 �&\\^QjlM�XA5\$b�Y�vo���4y1��(拄�|�!AضA�9iދ�^�����+���ܲ\"#�R�:O���4���{�sS\$c�vI�c*��?������ⲋ�\"����D�����*�!�k6°AD���t�Бz����eD�\$�< �db�l�� \"0aO ����E0�A�0�m��;=����� ������P�U\0�`IgI��]Z�o-lQk�2�XIl��\"���,�7���_�>�k>(vP�!���6�:[\\���a�Sztq���]���	y���	��/u�lf	�\"�JBXf��*��Ֆ��X4*��A!�M�#Ya�Hp��\nF�W�X6�ea��\n�`�\0/���n8�X\0 D\"Gz�c`` 3w1��i��H1�\\�ב�& ���fԆ���\\Y����{�B�8<말@J�S�,;�z�QJ�ʤ��.'D��ʲ�a�`A�P �+�胜BP12�`�H���\$5��GU�(+�PĘӧ~��\n��<a�s��rqǸ�ѫ�� X�k�[�m�0 E��R��,Q��a#�[��#���e� d��b�_ �BG��s8b�[�	&\"��ȭ�H9�/�1����M�0�\$�2��yZ���?9��\r��:��OS�o'\r;�ќ��>�Q\0>��A`�B`~�'L/�b8��u��m�(K��0\"������׍|�g@b/�\"08��a��.���n餤�M�0�̪�GV��\$�hxf�A�\"o�B�n���mD��<�\$��O�p������A�!x��,\"�Q�\\����p� �����.g\nfd�k��/����0���X�0?\rp��P�\r�	�p�L�'���>�ޭfE�<�fh��N\nV/��C@,� ��|J���m��i���pf�B�+����u�>0�2������M���҂�̆#\r�v%�mb�d�s�k1�`0���ю`q-a���,��o��������	�\"a�_B���t2����6?B\0���|0�Vrã��g	q�yм#�摂ސ��� �\"j*����.��0\"KL�f\"�\"��3�R}�#B9#��c̼P4��N����`ےQ��Rj`m�<�I��&/2�rv�2RF�'���Dz���ϊ��KR�\rR,}����	`��� \r����v�s\$!v,�lw3	��,���E�c\$l��������&���'LPf���*�/2�/���'<��,�2r�@��`�`�|��\rd6@�Xh��8g�\r���V����\r��+�]\n���Z,��=��\rXs�vgzt��10�g��Ͼɧ�k��F	�H\r3M\"#�1����&Э�xA%:�\"2b�ή\r�\\��8�<e�d���l�'�/�/OV� �<o�{9�s�~�8Q�8��?���v��@o�@�a(/�i8�yQ�&s&\" �Ct5#W5�4\r���e*�h�t���c��y,s1�ד¸O�/�F}mp�!+G��kZ3B��\n�J��\r������dX��y��B��A^:f���T\08&�E��p��0��f�#ob�Ao4B-�xa\">��ҡ��R�\$lDX�5T�B0@";
            break;
        case"ko":
            $f = "�E��dH�ڕL@����؊Z��h�R�?	E�30�شD���c�:��!#�t+�B�u�Ӑd��<�LJ����N\$�H��iBvr�Z��2X�\\,S�\n�%�ɖ��\n�؞VA�*zc�*��D���0��cA��n8ȡ�R`�M�i��XZ:�	J���>��]��ñN������,�	�v%�qU�Y7�D�	�� 7����i6L�S���:�����h4�N���P +�[�G�bu,�ݔ#������^�hA?�IR���(�X E=i��g̫z	��[*K��XvEH*��[b;��\0�9Cx䠈�K�ܪm�%\rл^��@2�(�9�#|N��ec*O\rvZ�H/�ZX�Q�U)q:����O��ă�|F�\n��BZ�!�\$�J��B&�zvP�GYM�e�u�2�v�ğ(Ȳ��+Ȳ�|��E�*N��a0@�E�P'a8^%ɝ#@�s��2\r���{x�\r�@9�#�%Q#��E�@0ӎ#�0�mx�4��MP�փ��	�`@V@�2���D4���9�Ax^;ځp�LSP�\$3��(���~9�xD��l\$׾�4\$6��H��}J��Q0BXGři\$��\0��4�x.Ya(9[�/9NF&%\$�\n��7>�8挌�9`�O\$U\nK�3��v���T�nT��YL��1:�>B%�0��eD;#`�2��!@v�rTF��,H��2�dL|U	�@꒧Y@V/��D?��̈́ű|c�\$�ʡA�h\n��(��C��0�Ϙ�&<�RZP;Lf�<s��=���-x6���iRe9�sr�=�tOk��ߔQ�߅�����\\#��4����}�6�1Q)�c�w�w��*Jܪ�ˁB\"�/����M;SW���3\r��Y@PK3�M�`P�7�W��<��N:�U`͢�`�ϰsXA�9?@��	�(�U2�����������!�0��0�i�X��@HS1.�v\n2P\"��:P�?���%_[�\n����������K����*E�M�CV��J�`�)�:\n�^�<BQ\nN]b2�ty�B�,D��fPBj�8�p�5|�px�c�HM�2�YK1g-����[��嶷V�\$Dȡ.e�Chp7!�oH��� ��咛�DCZ�TA��);\"�	��2&aS�\n��4�SP V᱿�#_+�?���0�h��«����\0�U��7��Q�8}�����3\$&R��	\$�\nS@h�yk2 ((��A�A�\0����G� ���LA���X��y�i�*���,*�Lm\r��7A���`�\r��T\n�b��G �Pf\rƈl�[#u�i�EA*L�AJ)�7�\"oAE(�#Ā��B�!�(du��_SR�0���GC������D�Ct�9�c���fB!�\"H�6q�����m�%�n�P	�L*w�-��b��¶;D�4�F�'�\0�\niO*56��HwR(�)TA�@���K�Χ�QMT�l�5���ӂ�����4�0���\0f�@�۫\0�'��d��L�xZkZ�A�	��2.X��n}�6f��p \n�@\"�@U�\"���w�qH��1�*��X�G�u����hYy�h�A��V�糊UK�OOE,9s9b�ͱ�->=7AS�!��u��<\$��c���U�5;.-MUrO=�`�0���������*��JA)\r̝\"qP?��+�@J����vϙ�U!��\0����K�Yڠb��v�LY�\r!�Q3Z`S��Q��~�Tq�s���FJm�(w`,\r�<F%/��\$�B<�b�]#/���F����a�\$v\\��t���l��Z{4�4�Q��i�(e���A)��[@ur]GT���X� L����J7y��]�BH���>Ǎ}^�X�_���������A\0/��M����oDT��R�{%:\$^��{��~�\$�5ZZ�SǾm�\\��G��6��f��B=����\$sˈ�쌑��*&/��ϥ���ZKɊ;,�^��cCR8D�K�g���^\n�1PhA�%B�t�o2�K����Ʉ B�e���8^01r\$cr:m'�2�����&�Lʙr���FXhNq�d��h�6��hq�ķ0�Ddޱ&)ڌ؞��|e��ڻg�4������XG��~0���IX�/ð��<k�bS[i1�~Y1f.��z�D8!�1�u�Q��_؎�g�_�(������tn�r�;�1%\r4�����AJD�	�=*�xv?|a?�������`�'�/����]���[��&0y^kwm{����k�w�69��mf֭n�d�2�Z���d�����ޯ���d�ˤ �>�,�︹ˡ�N�m'm+�0�'��O����0'2w���)aؿ�V+�:)�tg�Nv�rd�K��@̨�*H.��!��<k��P=k����A3�	�u�,+�+���F��,��\0��(e�H>G2τ����pޝ�0�o�.b�&ʄ��0\$�!n*Q\0yn����p�o���dY�|�Q�A<3�BZ�q*td\$-*;�\"ihr�mh�·�7�	�)Q\\�g�xa*j�p�P�G(�:-�/Qx�NciuQ`w��f�2'-bf�eMD�p0!k7mB�ѭPQ��ⲫ�>1�Ӣ��q�q��-t>��-vP���\r��N����F���і`\0�V�z��bvjn<�4&�Ge���э��fR,l�\n�>��b=�Xc !1F��#�;e�B�I���� ��o\nh\r�Vg�`�D\$V�iC�~ ޫ`�~�p( ��`��жe�A��\n���pC���c�(-�3��>'n#�21�2F\r�Ħ�ߊ~1���2�\r2�_�h`�fG��j�*�AX �)0@�\r�b]G�8c�SZ�B\$A�D�\r�D��Bk�~�#�P�ay�*eT���pc�R�4��\\!�Q.�:���rXL��4����'�L\n�<7�X5Ұ� ��S,�9�;�E7������OI�F�k�B�o��¤��q\nM<F�@a8lf�@�U ���/�:�-d|�HjI�P�V_��SR8��'�Ю�/���go�2V���%D/��:*��vLdtK��t#�";
            break;
        case"lt":
            $f = "T4��FH�%���(�e8NǓY�@�W�̦á�@f�\r��Q4�k9�M�a���Ō��!�^-	Nd)!Ba����S9�lt:��F �0��cA��n8��Ui0���#I��n�P!�D�@l2����Kg\$)L�=&:\nb+�u����l�F0j���o:�\r#(��8Yƛ���/:E����@t4M���HI��'S9���P춛h��b&Nq���|�J��PV�u��o���^<k4�9`��\$�g,�#H(�,1XI�3&�U7��sp��r9X�I�������5��t@P8�<.crR7�� �2���)�h\"��<� ��؂C(h��h \"�(�2��:l�(�6�\"��(�*V�>�jȆ���д*\\M���_\r�\")1�ܻH��B��4�C����\nB;%�2�L̕���6��@���l�4c��:�1��K�@���X�2���42\0�5(��`@RcC�3��:����x�U���:�Ar�3��^��t�0�I�|6�l��3,iZ;�x�\$���n �*�1��(��e�:�&)V9;k�����\0�C%��܎\"�#n\n��N�R���0ܳ��hJ2K(\$,9�7����.\0��+���\r��膠���0�8��@\$���+�Xʐ��̖�(gZ��1\rc�7�#;�3�S�\$���*��c��9B�4��*W'��RT��8��BbT�P�*�3�4�2�#��fc`����`�0���&��5�ir��+���K�rٺ-ľi���+�x�L��#��c�;b������.6�r�1�q�b_�G��4�l�n��#l�#�B*Q��n�7#��z�6^V�G,KR��!P�b�C��̨�3�d�f��L�1���ދ%cp��íB��J�7��u5g�nB���4�7c�(P9�)\"\\�a�(\0�!�0��8o#E�9��@��3;���g&G�+8qN��7�@�R9�\$�)o�>��ql4��������2gė�C~K�� NO����Ƅ	HdN�p�)�BN�>=�������� S3�3ǬR�Ta������A�;+�e�H�*fN��]&\0�Á�/P:\")�N�U�T�U�uZ���Vj�7+T\\P|�W��;�\\\$O�\0#�P�`L�\$�	@1 ��X�Q�a�,F@C�p4��%�q���U�Ϫ�mId���l�\0a��ߴgή_Kk\$O�(�Piܹ(�2\"��K\0l�����9\n�Hi\r�p�\"<fˋ`���#t�@PMO\0K����AA:�(!���hg�)�֔B����/5F]I6���FI~��Sy2qa�m�c|a�%d�����Ry`\r�`Ზ\$����B��/���Y���a\$��� ���zP̸�Pj�0qe������v5�j�*�4\rQ3Đ('b�m\0P	�L*A��cA�i���F:�\\i�iB�a@�#ҥ	�5�F�����YV�f��\r?����I�@:����h��Q��֖�G��3jL#I�Q�O�@j�C	�5�p��\"qI:1R�<�ز�C�h�H�#�Wygn�)�\n_8���V�.�i��L������؈q.B��T��V����;'n��r7,z��0Y���F��I��<��W	ag0�N\0����(�?n-��%�y-\r,�C�J^]���=��8G�gq��%�ޡ�,E��З~��rj@(!BR�XP:B{A�II=Xg���������%�d4�k��u����&[Td��c^8!�]?]��H��~��3l����'@wI3��W��9��!�T��i�ΈQ�ъ(	d�b������q�}���:�@։�<E����x%�3ˀW���G�a�/v�P��&CB2�QD7(@�`Y���L�\"�D�����#�b�v�T\n�!��AR��yz5	>qDB\\�/mջ���R�s�9��H(J\n^�}��ө��pUڦ��=hLе5#E�e��N���v)21��)L�2l����\"���m�wM���.���m�cg\"���Ʒm�������o��w�\"�{\\��`׼!���|����I�'ɍm@��	��\re��6r|g��b_��7,�����!N&�O�zp]O��>s��� 3�f���G��Ã�K;��2@{Y��4���vn8lpr�R]����7�H`|	�]G�n@��6v�o8;�إ3(\$�W)�OS�R����oC�]5ɺw1���� k+��)H�e��y��I���~C�\0 �`�5�V�a�;�9��U�>b��7+'���@}���y��Qn��~{%\rS\"�{���\0(l\$��ߩuV��oy|�<�+�������oL�L\0��.�8��o���L�����>/K��^�\r���į<rIh-[E��,�N���n�_.�/��m�aB���%� �\"\"�/\n�mFPp�(�P8��IʑGNwn���FF�7�:%�5JZb/�)�K)^#�<\"\":O\":O��Ћ\\������E�Wi\\c��C�)iT,���~�\"⣐���(���%�k��ǐ\nw̦��P��\r%�Ӭ�]0���l��\r��.��0�g4���]�9�֊�ܑ9�R\"�V���	oK���\rh#\rtw�[��U�RuEa�-Qh���M'9��P'K��?-]0�p!�Yh\$���<}#�c�\"lZ*-�@\\�mw�jL����W�i�R���b�&�qW�� -*�1,�rHp�q�CN,�7���4>�x	�I\rg�HĐ)N����@�����/��rNI1?	����Ҵ��-Q�'t��e���/�30\"6B�c�(/�R��^�6q��&)�>K�)1D���`�h��	ґ*D�!00�����=������0��2�1n0e�\r�Vg�`�A��FX2�<Xj��;\"z�)*�Ix~�@\n���Z����I�K�)��-K_4�*�33L��#�F�&yJp]�D�@�J2j&�\"��Ì\\m	��-��N��]�8ϰ'@(��ڧ3�\\c�Xp,d��F�\r�\$X�Q\$-;k�<\0�%ĘFep���c�\0�FR�b���%����On��L>�R���4/)6�j*�\$���\n�kq7?�?��2dZ���\r�klΊ',u/N��j:E�ͤTh�\\��t�>�\\H��og'�g`�M�D+�Fr4&��%��@�\0�\\`���@�Cd�>�\"�zԝ,�\$��L�bg\0\n����Ƚ��#��kEpô��&/AM���J�-D0�\"��,r.\n4�9`";
            break;
        case"ms":
            $f = "A7\"���t4��BQp�� 9���S	�@n0�Mb4d� 3�d&�p(�=G#�i��s4�N����n3����0r5����h	Nd))W�F��SQ��%���h5\r��Q��s7�Pca�T4� f�\$RH\n*���(1��A7[�0!��i9�`J��Xe6��鱤@k2�!�)��Bɝ/���Bk4���C%�A�4�Js.g��@��	�œ��oF�6�sB�������e9NyCJ|y�`J#h(�G�uH�>�T�k7������r��1��I9�=�	����?C�\0002�xܘ-,JL:0�P�7��z�0��Z��%�\nL��H˼�p�2�s��(�2l����8'�8��BZ*���b(�&�:��7h�ꉃzr��T�%���1!�B�6�.�t7���ҋ9C������1˩�p��Q��9���:\rx�2��0�;�� X��9�0z\r��8a�^��\\�Ks�=�8^��(�=ϡxD��k���#3ޖ�Hx�!�J(\r+l/�c\n\n�(H;�5�C����5�oa��X�BK��0è+Rp���#\n<��M�m�舖7��蔟1�J��o�4�3��	ժ2G��i[B3��Eq�EB\$2;!� Rw�jZ�\$Γ&3�p��\"B�����(Nz_*��p��<-�i�)X�6J��С\nb��7��7\n�d��^���B�9�	k��LK�)���q!莭��&,�>����:B*_�lAe.�x��-p\"[]j4��d*�(��'#x�3-��K'��j)a\n��z:���l�ƃ���kwĕ�H�^��)��(�&�_	,����oҳ�J*\r��v!�b��1��棅�g��ct�O|���l��3�2w.�GУ\n�.��^�&(��)�:�4����Jԫ�?�,����G@�C�AJ���[W�-��e�yF)��G>���Ps.��J��;�z&�;�'p�VLN���?%�R�Q�AI)D�|ʛ\r�m��\n�sǌ���ug�К6*�*C0P�;�HM�p&�8�vK�@f0�4t̫M�r\r�|���䪃��[A�3�r�:i�ԏ�ĔjArN��*��8�#�xE�6 ��\\���d���(��H\n�も�\nIш�ɭ���U��א�\0���\$bkkp�;��Td�='�M�D�M*�h\$��\"Hx��`�H3��17C,��ӛ�(r)��B`t9�(����P�̯1���8��R9B6	�Â�	Ȅ�N��8?���y�>(�D� �rqoMݟ\0@�C�9����2ʗ	ڟ�4^)��M0���6GU��f҄����c�Y��RE��i(%�/�Y\"�o̜�(i�3��ƹ\n4XA<'\0� A\n���P�B`E�k�e�Q	lLB4)�E�ǒ\n�_m�c�I\n�fp��H�\0�F�R�VH�F���<^���H����ؑl���Y��\\HY\$� �+*�Hm�\r�4V��,p��l��p�{�S�a�4T�EN�\n\n�B%��t�)\n>2���I�w\"dYJy�(ѧ�HCM��AL2��K�mA�P�Z@PJm,�����CJ-�@873�p��X��a1SXe��6�)]���HA\",�����3'���U�y�({���cSKdA>M���k�u��'n\n���ڑa�z/}~��*��w5,Ðp�g�PDW�Tn��u:��T�4��;-��P��ȱ0�� �B��/���P�q%z���b^M�q7O�|����)t��~�'�h�J���֓��i�o^C�9������ve:�imd�\"Dȭ\r#D�Z�*F3�#���Y�7i(aE����^s]ٞMv��\$C����Њ��eL�;Ln�9��Kc6]��Oq��duVq���\nH�#{+5I4�H\\�f�l	.�)Z����3+.�e��ZW��_���5��H�ٛp�\$�i��Ԗ\\�	��F��42�t6<�d(���o��?#I�\\�je�Q�!�n^�~va;��t��xY�\\���� �MH:���X�پCN���\\����I�XzD`��^-ak̹�DW�p�/�7�y��̎0Z+�y��H5(�7֖r�%+]��wߗﾃ����D���E���Q�KHa���ULy�2fUeo��E#�aO���jؚs5q�\\���ud��;;:%T�Ir��z�-[+a�Xt`B�~B��bTbg�^�E8u����1��g��4:K6�An�k��s���0��,4򗱅���OR�6up�^��أ\0�5/H��!\n��z0�^���Q��2��lp�>V|�w�<^�_?�yBKy��-�bǖp�kV��k\\�{��ؾ�5G�)�.v����ޫ���?�o�w�\0�/&����2�l�el��4�f�'�|���\0����Id���/���l Ȫ���\rD��vf�����&!���[��htp��Gâx/l'�Ǆ��M��̠�BJd\r�Vb�g �T(�,��63�* B�%c8���(�heh�,�0\n���ZJɎj��>k�2Kl(M����N���8KLK,�����@���}О2�\"�'�I\"�_\0ʗ�nh�'b�cP�X�\\\r�c�\n:I:K�\r���>�hF��-���o/ �ͅ�Hwg��V��!�b�\0�6&iXjMC\\Cjo��a	����u-�l ��N*c�r�F��ќ\r���z� �P��;�j�D��	�k�r;�Đf^1�L��4-��'�Ep��g\njNL�	�	���M#�r0���-G-����`";
            break;
        case"nl":
            $f = "W2�N�������)�~\n��fa�O7M�s)��j5�FS���n2�X!��o0���p(�a<M�Sl��e�2�t�I&���#y��+Nb)̅5!Q��q�;�9��`1ƃQ��p9 &pQ��i3�M�`(��ɤf˔�Y;�M`����@�߰���\n,�ঃ	�Xn7�s�����4'S���,:*R�	��5'�t)<_u�������FĜ������'5����>2��v�t+CN��6D�Ͼ��G#��U7�~	ʘr��*[[�R��	���*���9�+暊�ZJ�\$�#\"\"(i����P�������#H�#�f�/�xځ.�(0C�1�6�B��2O[چC��0ǂ��1�������ѐ�7%�;�ã�R(���^6�P�2\r���'�@��m`� rXƒA�@�Ѭn<m�5:�Q��'���x�8��Rh��Ax^;�rc4�o��3��^8P�@��J�|�D��3.�j����^0�ɪ�\rʜn�i\\N�1�*:=��:�@P����ORq��ڣ���jZ�P����ҕ�.��0��*R1)Xu\$WjH	cz_\n���qt^7\$Τ�:�A\0ܞE����0�:���0���d%�Ȱ�:��2�)أ\"-'�Z��b��膲\"̗�iC2�nS	 l(Ε���獰��l�cz)�\"d֎R\\���,�������L�\")ɑۮ�C��뵐AYdѤ�?�=d\nC,��BH�9�V\"\"���k�v���ϻ\\d\"@P׏�6k2���`�3e�Rj*�r̷b��8�W���;ڣ6 K+������3Ī*��%4�2��R�L(�ȼ�)���:Yn:���v�Mz��2�<�2��aP��\$ �>*���O#8A3ӈk�1��K�Qh5H*�|2,U�­�×Z(�j��T�#0,����C<�޺�UĨ�9�.M�	�[��\0��L_�Q��H��(���wSI�5)��	Aj�T*��K��3�\0��@�@o%,���U�{`�@RH���D{��%�@�g�i)lA��B����<m��\"�Qy)!�aWD���\r����5	T_i �4NL�\0ib�<�(d���(�ߑ�@@P���'�6�{��8ࠪ���Cq{c�邚8�i��)o�L٦��ay�|D]ަF�˽W����vOS8n��4N{���yQ���jH�y3GI�0b�EI�C6EM�,IA\0f)���B��p�/O��٪B8MB�O\naP�X�L)�(�'fL�1�/�j�g'0Ô�}T�^^b���ǂ�QMa/&)�<�҃4�8r`�\$��Qn��#N�6��&��R�QHb9O��+2�E�p \n�@\"�j\0 �&Z���.H��(H����&l����²�;t\0��~��xm�D\\6��x�#,9�bFKw&Q��Ju�'�F�q��j��a`7���ad�@�:D��±ԐF\r��g��`A\rA��C�^@䄒���T�I�������g�LI�_����Ű���i!R}n�H�r%i��W��M�ywb��R0��rж�\r��0�dR�2����dDS`D'�� �;�s)����2��N��!wKVh�_\"8q��A@�ĘuK��\\f=fޕdP�ݍ�1y�S<��jH'��&��b�hT\n�!��A7�kOj�W'�ߡ3PpO �D	H/����JL��\n,[E�X	��v��5E6�b�H	�d5Ϟ��\"��^䢒1�+�pv%��E4���[�����u�\\�qtd���<��(.��#�ݚÞm��>�E���e9�}4�2�ȱY\$����	\0��Yc��G#g��\"L�	������\rD\0001j�Q��)��(�����������)�[*�ۘ�dK���̩j2��B\n*d��`��(E��-�h&8�&N�Gh��-�Ѯ;L�����ܫgs�p׺��Y�i�X��4JC��B�08�U[��V�ɥ߳����\"U��u4}�A+kku�R�b�D���tJ�J�9���%]��zj���w`k�&ݧ?w������9���ޝ�&�y�2e,q�o�4�<��t-1�:.Cʙ�b��{�~�r���k�)����u�طv�W����GC__�@�&&Q^y\n\r���O`�ƈI\r�E5��d������3��Xo!T�Hc���#��,���%�ge�����;�`��oi��a��vёY�?�s���:�(�:uhv���֎d�:(���N����ƫQ����`���M~�u��U���ɂ�&o:ũ�]��ٞ�Xl���T�I��<���s�zg�k?���U�f��cf:��kC��΄����8gϾ����#,09��y���%������p<�k���-pA\0.k,�e�\n���,d��'Dd�,&Hd���D=L(H�k�}PJw��W@�3�� ��\nEރ!bf�K�Yd��4o�*cP�L�,��\$��� �� �N�D^f5F/��jfbƅ�Gp�����*nq��Y\0�`�#�B��&�. rBz&B�\r�1C.�D'i���f\n���Z6~~L\"j�mj�����Vo\"�,��o�8B9�QB0#B�#�B��*~�K�	��\r ����(NQfІ=�u\0E��D\r`��8�`%��lC�&���ȠU�\nC��J�� �.#��BY��.I2�Hb��#�\n��\0�b�(	f�kqN�!�]n�a����i� �+�E �����@b�3�\$c��t��fk��2\$�˜�\0d��vB�����ކ�u\n�g�\$hL&y&`�2��#�dIe�G��'K�F�D#�\r�s+x`���	4%b�	� 9�� ��M�4OoX��bF�U+�ت��D�b�7��W�TC��	\0t	��@�\n`";
            break;
        case"no":
            $f = "E9�Q��k5�NC�P�\\33AAD����eA�\"a��t����l��\\�u6��x��A%���k����l9�!B)̅)#I̦��Zi�¨q�,�@\nFC1��l7AGCy�o9L�q��\n\$�������?6B�%#)��\n̳h�Z�r��&K�(�6�nW��mj4`�q���e>�䶁\rKM7'�*\\^�w6^MҒa��>mv�>��t��4�	����j���	�L��w;i��y�`N-1�B9{�Sq��o;�!G+D��P�^h�-%/���4��)�@7 �|\0��c�@�Br`6� ²?M�f27*�@�Ka�S78ʲ�kK<�+39���!Kh�7B�<ΎP�:.���ܹm��\nS\"���p�孀P�2\r�b�2\r�+D�Øꑭp�1�r��\n�*@;�#��7���@8Fc��2�\0y1\r	���CBl8a�^��(\\�ɨ��-8^����9�Q�^(��ڴ#`̴2)��|����z2L�P�� �3�:���Եc��2��Un�#�`���ˈŁB��9\r�`�9�� @1)\0�V�Ah	c|��Gb��8Gv��H�[\0 ͣz�5��@���0�:��p���R6�P����T�\nc\rΥ�å��0)ۼ4�C:6�*�)�,��1اx2HH*)��d3��P���e��_c^�����0\"���k,�(M0���H�w_W�YaGZe���cP�ȁBzF�J���0�� �z��(-5��H�8c��[�7�ζ����i�,v\"Ur�E02�����	���3d���6d����A6��x�Hv2++K���|#�D:��3l0��*�iQ3h�aJR*���ؿL�)�Hߐh@�5.~��2,23�͘*��8ε�Kb<�R*\r+EO�#����tJ:�p� 3�A<޳�҂�L˒�TĤa���s�hl'a��<�\n���H�i����t%\$��� Z����(p&����|O��@(%��BNQj5G���Z��`9�����!P�@`�y�7��%�7�N�Y�9ː9\$2��,��:�lFi��lնS��p4ό̦ƨ�bh6+L�EGЃ�a�\\��WJ��;6{N�4&�eC�\n#���p�[��).%̦&�tc\n;�e�0�cX�b� �A@\$\0[%�L�(&�PRSH�z�,����\"�g'>v�Kj��4��D��a6'����z���?{Oqg��@G�3�%e<��\$�#�f'�:N��ފC�f/�J�\"G�(�\rg�I���ҙ2�i&�`O��������9ti3O�D��H4�|�7�����k��PH\r��07'R�Pؔ�'��L���i�+@@D�3�Y����>����̑#�ܑB�ESg���3et��0T\nD����Ӌ�LT�t���c�nbFP#D.��xNT(@�+Y�A\"���\\�ޗG���\"��.F2�����l#�&D�@��d?D��Y\".�3VY�^g�d��\n	�lH��>R���g`�E����An�m�Y���OaARl��e�#�CG\n�h����:��<JIM*%Q�Z1�(*��QEA��ZT�(%���0�]�9�\na�=#�r�ݲ����\"�(J�9n���+�x���Xf��\0��Yl�����vZM1o��ġ+A�abD\$��B��ʑ��xY� bԂU2Q\\��L_��A�\nk�0��a��p�]����oC�!'Z��@��@ �\$)]�%�cCzUJ��Q��cI�JQ��޶\0/*���E���)�	e��&��pO	i�2�,)U�r!0h�盳� �Y�4��K��{�F �4���8z\0�K|�P3�t���8��]���ͺk@�\"R�TPB�4���\nJG\$dt��=cQu�\08�e�سb���F1D�N��C\\!U2�]�Q	�o@�����I��lf�k�𐅋B�d�옶W��(� 9��-z�\r�o�wRIadÃO!Qcz���{�ĺ=�������,�͖���r��c`�R�&Š���R��\$�/�6�2���P�%\r�NPL�C:ܿ�?.0�Q��:�����R�v��a�q�Z��\0(ef0��ˏ[�L��Nԥ[YOQ�Q~�,��c	:��V���'���Ylr���ر��~7����M9�<i4�%�\0�䓸f�7��4�s�=�Xd��)�;����O�x�C�[�0�-���t��#S��U#�qmo��@p�)aH2�tEȝb��4�\"���Nw�^���D�r�>b�z�\"|h�W�)�>�缾%XE��r1��?+�����nU��o�k�a�(~����d�������R�h��6��\0H�Dn�H���kfk���\nYf6\r��^`���RZI\r�6���.��:��H���M��H�.�PG\0�i,&\$��\$a#�\r��Gf��t�����o0w�V�l\\,>8����	����0�4%�,L����\n�Re΀'�dEl� ��;�O9\r�X�д��^c2��PG`��)��F�:�<)��qF�d����L �p*��\$�(i;�	e�Z)|��1��ή���M@��d\r�V\rfd!�.�D�'�4�O\0cXz�b.�R��ȣ �\n���cʉ%�%1D��Ҫ�~�m?O4 1�ʑ�\"j�*��`���P�֎V:Ê�P�q�8�Z�b1�JH�j�æ��%Z6�:0)RGb:@��/��+�0fOJ�PM\$�ƃ.Xn|�r0�,�.+!B1\"Ϊ�`��R&�b��С!2(5\"�\$Bf2+y&F��P�*��%�\njRo�2f�:���.8�1���S(��Wkm��H��kR^�L\"ڲ,\nf�!�N�IR K+*0��2PHo��G`��#�[�B0���x\n�!Ń���";
            break;
        case"pl":
            $f = "C=D�)��eb��)��e7�BQp�� 9���s�����\r&����yb������ob�\$Gs(�M0��g�i��n0�!�Sa�`�b!�29)�V%9���	�Y 4���I��0��cA��n8��X1�b2���i�<\n!Gj�C\r��6\"�'C��D7�8k��@r2юFF��6�Վ���Z�B��.�j4� �U��i�'\n���v7v;=��SF7&�A�<�؉����r���Z��p��k'��z\n*�κ\0Q+�5Ə&(y���7�����r7���J���2�\n�@���\0���#�9A.8���Ø�7�)��ȠϢ�'�h�99#�ܷ�\n���0�\"b��/J�9D`P�2����9.�P���m`�0� P�������j3<��BDX���Ĉ��M��47c`�3�Г��+���5��\n5LbȺ�pcF���x�3c��;�#Ƃ�Cp�K2�@p�4\r���Ń�����`@(#C 3��:����x�S���C�s�3��^8R4�&�J�|��\r��3?)��	���^0�ʘ�5�)�D�-v:�l\":֯̀���\r\n9he��Lv��[\n\$�'>� ����FC:2��3:7��58W��!���	cx��\0P�<�Dr�/�p ��X�7l�<��C����-r�i�µYvixë�ӭ�\n82���	#V�� ��b��s�\n'���B�r�\\���:R:��>J��L �8o�HC�I�r��G��orf>n�>���˚���\0�(��T�;���V�=�5�}N]�-K�5�9�itL��f�#��#sQ7�K.L�*����.��^I��>5��P�6�Y\"�]��*�\n��Nd��}!-[p�6�+�\r��ʂ��L3�F�\n�̽00͓Eեih���{k*1���4��9}n4������Ns��K�����W�G��o��7\"�����5�������0@G��D\n��}��8 � [�B�U\0Au��C�6	�=Ґ�ȕ�)���\0�F��)�Rk!�s%!���\0�C�l^e�\n:w���#r%i�6��~C��C9d��E���d@	�8!��c�xz;�8���� SA�N+����/�(L�e�M�8s\rE��� �z+{�4���K^�.lE ���\$\0g%,���'�C�1�>��B��*�2�6��^��b�A�A	!Ep���iv����@�!��B�a�7<�@��K�*\nX��\"�_s% ����e� ��e,�7����ku5����H\nX4 ��СB\r!�6d\0��r_!�3tx���E���e>�Y���a2�:iX�v\r������zN���Qv��s��.��Q/&\$̻4��S�)y��� �&iSÙЀ��\n`,� ̘�	��8F(��t���2��3�3��4�KH%K%��90�)8a�e�����h��\r~M�����\\�'\$���\n�CJ*RXʗ�@�ș'EȨ`�Bᯩ0�IĲRBQ�#�\n\"�aRȬ6� �k\nr>A��R9G�Xz\$���W\"�+g>t&�2����},-�G)+b��\"�\0�S���X�5q�\\�*����ɞL,���r&�ʚ��I��l��\0�)��_(�a;�JC�i6	l.�N���!A�;�乔`e��m *�2B�]`F	�lAU`��̮8cG�9\"ۚa�ÑȠ�B:���uj�	�5��Ã40�\$��ʚv\r�PmǉtN���S:�ǌ���(,�I�ǂV�\nO���%o\r�@��f&Uq��;�S5�L���D�T r�Y�T*���t�m��?�7b�r5ZH���?\"�ڣA�.gI�T�Y����t�@PO��l��̤�o��b\rq����6=\"���H��#SÑ\r��C�h4��K�s��8GwK�#��^�/̭��D0n�q�F[���.aEmJ��kϓAӹ����M�V\$�T��s����\n��o�'�zB�q�%�e����t4��\"�����1M���u��q��u�=f7r���\0A\n�P �0�F&q-���1N�qBA���T��y^_˽J0#dT�y�ܰ��sg�I98B����-�[�~��`d��7���<�S\"�-�D?�4�y���<������9�P��K�L���z�`����b���sY\\����u�:��p|����1�:o��>����ߙ?c��w��!\"�;0�L4�~��H�!���3���\$uf��xs�5l�Da\$F�.UPm�����b���iW�\$���{0V���1S�3\0��-Y���?�`��/?(�PR]����燒���^ro�U�n9�&߉J~L� �����Z�����\"�ߗ�(B�B(/�����ķ�Ra��ɿ\0N�X��B�\0L��Tr����L:n�B6\0�\rh�4h�d+��FL�\r\\�'�d��\"@�^c�4�W�bdC�glF��McQ�Gd\r͘'p���u\n�B� WЊ�Fa`ڜ�P��Zl`Rɰ:Jǈ����0b�ʋ��0��p��D����V�5\r���+\rK�\r�l\n�lQ������e�\r0��s�	N����\r�O�����������.cp(��2(6���C,�M���=���΃f���\re������b��/�:&͞�(p(#��D��Ɗa�����Nkfp%`梦�8B�R��!�Z\r�|+Bd��C	�d����5��\nb@?\$����q*�E�Zd��@6C� �g���q�(���ź�����-|\\l=����*r&��\0��L�\n6�r;�3#,�w��\\\rt\\���H����%p��h��j���*�0	c�'e�i�'͒��%�Rr��2�;�0EG�k�\$%��=C�8Ƣɢ@Y�ĉ\0�)�J�fܠ���U1	p	p/���QE\$���M�-��(��&Ҏ��֐�]�/\0�Lgx͂�012U0�\\��M��;��6�e&�Ǻ�Ur��.'��p�\$�s/ŸǠ�4�1Ҙ��`k�V#2`���\n����u�Q#�;�{7�G4R{5d�u�|����Lt2�)<enB�-QD<kdz���3c;;�V�D����21@�m���*o�=��>��|�c3o:0�����[�=�,����U��������hA����Zk�v\r�V\rb�#�L\\��N i�9�;���C��)��BBL&J��LX�\0���\n���p&�V�����hOo��o�o޳�¾��;G�j2�fT�dJ���@J�0��B;��\r�\$Sɍ��fwDƗ7�X5��\$��4�R��xkg;��lg���ПC�O�X�@�O�H�8a�*�>=L�s6 /�;�\$A @ވ�b��R�[���Z��S�� PCS\$'I�(�>����S�CU��ˀ�8F���X\ng:j�L5%�\\-~;�t{��gȐQ/�Rg�x\r�K��I!��G����Lu�6��	�0��%�\rG -�\\�B�B�����aWO�g�\"�\$U�H�j��uHj�54�=@jzzʬFDFHL��3�6bChid���V��	�\r�S6�(�";
            break;
        case"pt":
            $f = "T2�D��r:OF�(J.��0Q9��7�j���s9�էc)�@e7�&��2f4��SI��.&�	��6��'�I�2d��fsX�l@%9��jT�l 7E�&Z!�8���h5\r��Q��z4��F��i7M�ZԞ�	�&))��8&�̆���X\n\$��py��1~4נ\"���^��&��a�V#'��ٞ2��H���d0�vf�����β�����K\$�Sy��x��`�\\[\rOZ��x���N�-�&�����gM�[�<��7�ES�<�n5���st��I��̷�*��.�:�15�:\\����.,�p!�#\"h0���ڃ��P�ܺm2�	���K��B8����V1-�[\r\rG��\nh:T�8�thG�����rCȔ4�T|�ɒ3��p�ǉ�\n�4�n�'*C��6�<�7�-P艶����h2@�rdH1G�\0�4����>�0�;�� X� �Ό��D4���9�Ax^;�t36\r�8\\��zP�)9�xD��3:/2�9�hx�!�q\"��*�HQ�K�kb�Iì�1Lbb�%J�8ılk�g�V��%�Ȥ�EK���\r�:(��\0�<� M�y^��!��`꼧#J=}Ǝt^��p����r2 �ϊ��k��2���6Nku�2�v-�����a����4��J((&��ǎ.ٚ��`��/b}`�1��ؠ�vA͈Jr�����٫�� ������3@Û7`��ܤ��&L����j��l� KR�n��p�>B�o�c��,Ǵ�-��h�6#k�B\$������,���Z[���U,q{��!L�>�\"��Ѵ�d7��3�R�\0�R9L�@�\n�z���!�9���b9���A�.��x��0���{Ԓp�aOr7�i@@!�b����֤���9I}w����T�a����̹	wg�����s&��ӟ�d��hui�5*B�تCD�H�e(���\"� �������Xo,����MP�aQ��1�ʳ��tp,&�hGTR�^0���G�T�R�aM)�<���Mi�\0��T�PxBpL�%h����� �>�LQ�xo�\\'0�A�(�� �'|HYrY\$�	g��rKq���0�5zTY^H7�( �׈a��@��I���Pjݵ�v��9Ĭ6H\",e�ABBD���`���<O�ɨ��P�B�H\n	�͛�d\n\n�)\$�zU��G�i\$\r�\\�Y�\r�qƼ3�B:]��7��9��t�1��G=o5R2^h�}ᄜ�(�(%��Dxc��#��B��_�1�Ho�( �l�X��@�@��\$M\"L���3�3voL�8+�ԛ(+\$��l�,(Sz�f�8\n<)�D�`��<�ilt���+�\\�:��\\o�N�4&9HJ\$gNynqd�6Կ9�9��-}�FކS�b.͕��W��� �R]���Q\\�m���3�\"H�W٧\$��f٫�k1�怠��\0U\n �@�_�\0D�0\"�b*��-z_�q%!�	`�Q�񓪂\\�xpe��Ŭc�r�`�e�-J�����7(�b�Hx.K��V��⛬���N�~�Bɖ���K�Vȭ;r�	��9Ӕ��K�H�m8�X��Z!\n��C2zV�HAX���2T�7)}���ee��/��]��]��(����͓#�_��s4�1�L��~9W��C,�:�*��}`��kĕP�z��L�9Ќ1�Ɋ�[�X��E��~.�T6�%�^�3n-NҲ7�\\�2��YLw5C�ev�����A�PP��9<���&�᫫�r\\���1o�.FE�o�E'���aB�Aa 3��]�y��N���y9�I��\"�Aya^�t�e�\r_�1�0�Rt1Q�Mt��[�'M��:_��7�K�WY��M\"���`*�j�f��\$��/x3���{\$�sa��R\$�F�{��DÎ����Ymv*�M\n'ga�f����\"��l�R��!0�ty�ʴ�CG֤�FN�G.�\\��Ɵ�g�	������In�)�g`��za�>I�R���B��6\"�!Z���o��1���ֳ�5�[���wVP��~Û��e9�:��O���p�L��0�o�>ʥ�}��QFGW��D�k�5���Bl9\r� ��d��;e�\"�U?�s�m�gFt�b�p�zf4q��q�4+{�\n�Z(+>\nlz����~����ٟ�t�!�1v,�ad����l�\n���ӓÍ�tn�ۚ���^o�5�7՛W��g�����&�Ik�C\\MFI���&yc�Ѯ7��3�d�a���N�9#�Sz�l}���1������n�{ G�6w�ze��U��I\\ED��,n����\"6O�����9b2�	�R�%�����\"�&�.�����`�	��hL�/�1���\n���.t���EN���1k��/V�`@[-�ܚ\"K�w����C�\"3Hy\"[�_pi	F�2\"D��Ǥ0̆`6\$��Њ>(��#��*(�~�˚�p�:��aϱ.w��p�1��FLްA\"Z���͐�s�k�����f0����c�G�T�',�Y�.�1#\ro�������\0@��2�F�1?\"r &	��\n��J\$p�'Xq��A0P��d�Kb[ 	\r8\r�LC�6�,���8��K�j썦^1�\$	�Pʐ@�gL���T�C/n�1��P�6��shڕ�p�e�<@�jF\r&q�N(En#1-�\$�Z ZgB��D�0��� ��Zb������W	��g��τ��#/r���)�<Oi�L�ʤ��GB�@�� �N�	��f�����/H؋����VĴ���(C��\"�z\$8atc���cb�&��P�jRX�dZ�)+�!'�jce+-��v��0ΐk�d��|�*����gA+HjD��r���_-�.#�\r��9��Mp��,�i�Xe�bf�Gg+\r�1���k<i��F�����b�:�2E �3�x]\"(�fd�*�0�8��/1v8��.��\\�l1�_�j���I�b�˨YL<��^)�lr�L�\$5C��D�";
            break;
        case"pt-br":
            $f = "V7��j���m̧(1��?	E�30��\n'0�f�\rR 8�g6��e6�㱤�rG%����o��i��h�Xj���2L�SI�p�6�N��Lv>%9��\$\\�n 7F��Z)�\r9���h5\r��Q��z4��F��i7M�����&)A��9\"�*R�Q\$�s��NXH��f��F[���\"��M�Q��'�S���f��s���!�\r4g฽�䧂�f���L�o7T��Y|�%�7RA\\�i�A��_f�������DIA��\$���QT�*��f�y�ܕM8䜈����+	�`����A��ȃ2��.��c�0��څ�O[|0��\0�0�Bc>�\"�\0���Ў2�or�\nqZ!ij�;ì`��i[\\Ls�\r�\rꒋ���N͉�z����z7%h0 �����)-�b:\"��B�ƅ\$oL�&�c�ꒀ:� ��c��2�\0y\r\r��C@�:�t��,CS/�8^����GAC ^+�ѻ�p̾'����|�=�,�����<��n�σ�O/��4�%�\"7dYVMb��pޯ�M\$V�\n�x����(�C��W%�ہB�6�\nt4�7lj��k�,1�p���3�桪c����dٌ�2ȭ�t�2�5�a��kvLN1�]��N1�̢h�&�X@6 ,'԰c7\rߍ���R�/'rځ&��0�:/B?g��bR�M�,1�״���b��1o�����d�n���h��hl0X甾�o�m�@�����˱�r\\5�I��6#��B\$��[����m�ra�1�T�I��.\"Z�s]�vK6�5�{�7��0���0��'Cz��!�9��{n9��^W+<+�،#?V��u�1O�(P9�)Ȩ7�iX@!�b��V��N��������_-aiz�خ���������S\$���s��|}��9L��X2Cd�C���\n�\"�\$�� έ��\$f�4��\n�M`���r`�;���>���@PM1�(�(H�ਗ਼����UTZ�Q�EI�U.�]BS�|7)��J���T��j���>B ��B���\$T/D濾�����7e�ߢC1�ٸCf�a�S\\���{��4�ܬ��yiO���Ƈ�aб��p��O1X����I�CgD��g�h(f\nD��F0un��	Ds.�\r�P	A4��HAX\$��I�\$�\n�\$���q�\$Ϳ�e����?����fNO�]\r!�4��G�/l0���u\$�N;��dH���\$Q�-\"�:�#��<!P�\n}H\"K��*MZM*�����pM��e%�r�b�?ፖ6H�p��g�@'�0��S@'��\0Ή�˩d2RLN>#�}Bt�d�S.͖\0�HB��54��v��s��5P�&*�%��#IXO�Fp�v��:\n�:]����#LO��\"���,��p \n�@\"�k� �&Z�F�t9��y-i ��/b�*�fzO�/~4��\$��	0���xR��Rn��~����C�z[%�����[��%�h�MԾ�er�p7���F`¸bNr�Z����kb�͙���9��� .��g�P�9�q<�b���XQL�\"w�*cR\$Bq �ʩXVJV����;�C��t�8ͮ��f�:1f4�����s�y,D2�f����K*J����he�(+�ŀ�e�CF�����\$��82��F�i�Xf�▽e� C��y���ZV8T��h9v;f��/��O��+0��\n�z�2�7֨7�#�	:�r��v�����_\n�P �0�I}LΊ�;@��{�@\$p���.��Q\\`����FiO(o]�C��B���ɽA���0�t2�4��3���N�v�H�iSotƌ�Z\$�H-<���N�\$\"�U��K���%��t�GH[Y),���0d��ޠקC_��,v�d��gljrO!9��ZY*b�h������2���Q�dΤ��ScQ�8��j�ZC�M#��ľWJn�����p�����g�M�n�Az����B�2����9�s�HEcY���jє2;|�0�J�Y����\r�]NN0��W��}~����+g����Ui��؝�I�	F�0/������n6�uk���.��>��;D3wY�3p�� r��Ӈ\"D,N��kw��egd�]��n�����	��b�����YZ:�y�ˊ�]���L�M�C������\r��w�\\y��\r�C��\\�w�;�랗�?�{�T�D��,�`5�����s9���	��{R��Q�4n�պw��~��͆=����R�}���oz�����GZ����*���?`F�l�V�8�\$/Ǥo\"�Ɔ�\$�<.C��(�\n^��P�����)D#�Z��TJH���600\0Cp\n?��#���/�ӯbWJKc=\0���x�����r�f?�>2I�j6������:���o��0�����p�3�p�kl�\"`Y�>����(�	�T�����ε�O��NpW�x���d�/P���2���&��]�0K�8�����4��zk�V������o����0����r�ll&r,/m��I�}P���2�0��f(��\n� l���ʧ\"�N^�L�eQ	����ȑD��l��(�npk�&tɐ�b��#6\n�^��_�rH%���'._�BF1z�Q���|�\rBr	\r\0DP��!c*p�N3e��Gk|YD����k��|D�*��e�2�Cb7#0n�2��*CC	���\"��y D�\"�<��j(\r&R\"��EX#�ZB��1N1@Ze�rʢ\r�ꡃU��\n���qf2L~׍T记�M�{���O /#(���23�<\$Div�������Q\r��1�1�\0\"�Z?��gF�C�:�&�Mp���\r�|��%*�J��Uf��2�(�\n�J�PatY\"��Cn��>qh��7�)�E�j	H+s �k�2R�G�y�7�1��`�,� �3.�3��s.�k4C^;�H?���*,.2�Gpm3c�	k*Rk.kw�3L�W���Jd�Lȱ�~��0�˶O�0#�T�G,.�\"�d�De�r�0~0+�2��/�z���%c(�S*2�~���&\$��/x��/>��]��,\0��C�E�DˡR\nE�/��";
            break;
        case"ro":
            $f = "S:���VBl� 9�L�S������BQp����	�@p:�\$\"��c���f���L�L�#��>e�L��1p(�/���i��i�L��I�@-	Nd���e9�%�	��@n��h��|�X\nFC1��l7AFsy�o9B�&�\rن�7F԰�82`u���Z:LFSa�zE2`xHx(�n9�̹�g��I�f;���=,��f��o��NƜ��� :n�N,�h��2YY�N�;���΁� �A�f����2�r'-K��� �!�{��:<�ٸ�\nd& g-�(��0`P�ތ�P�7\rcp�;�)��'�\"��\n�@�*�12���B��\r.�枿#Jh��8@��C�����ڔ�B#�;.��.�����H��/c��(�6���Z�)���'I�M(E��B�\r,+�%�R�0�B�1T\n��L�7��Rp8&j(�\r�肥�i�Z7��R����FJ�愾��[�m@;�CCeF#�\r;� X�`����D4���9�Ax^;ցr��Oc�\\��|4���PC ^*A�ڼ'�̼(��J7�x�9�����c>�J�i��@�7�)rP�<���=O���t\r7S�Ȳcbj/�X��S�Ҋ�Pܽ��&2B�����`�n �H!��x�73�(�����:��\"a%�\nC'�L�2��Pح����vո��Ǌ����N�&.��3��;�E�L;V�5h|��)�����CF�DI����2�bm|C�^6�\n\"`@8���jC��o;�s�#M��Mr�&��\\��:�X�2��-��7w Ί{� �0w�8�(��7�.��	#m9\\\0P�<uc�\$�9W��͜<\n\"@SB��oH��m�7;B�0�6P)蒂&:0�7��� ,p�Gc2�6N��G)z�꽄F\"�;�P9�)�)�B3�7�p���\r�H�op \nID����ÑE*�U��4��;�+�*DS�C�R�'�pL��D��*P@�ق�*@�C�b�B�Z�Q�3BBHi 䰱Tb�����c��ّ��d��\"zT��S�}P��`���СT��Z�Պ�V��@��x��r����Ŏ�]Hp2�@�C |��\"QSL0�����J�x�,m>-�p���R������z�rw/��GG��R��2n�BPa��3E'���6|\rt����p\$�}K�����_Q9�C)+F�e.���6����î��7�\r�pڂ�\0��?@�ܙR1�w�v�c�)�%C�Ԃ��[[N/��Ć�X(<6C�x��r�'�ؔ�2�Q��.\$��֢�2�S����,�G���1@5��\n��\"���\"���')�i���F�0Ij#�z2����8�%`��Vj(a@'�0�.�Z�k��@�L� Ǵ��*���eE����<��(p���b�C+��d��z2�2/cob91�F� �Sn%m��X��P#����JRy9D�]1wA�`rO`(��V+ϑ�%�-��\n��?f��2��S�/c2�#i�V�RlLX&5Ƽ��(il�ćP�u��q%��2XPM����\n*͛@����c��Hnf�02T�a'[��N��NL�	U@�������k���!!r����/\0(+\$���N��@�Z�[\$�K\n�:���ƹ�-�\0P	� (^ґ��X��T��CVrl��k ���J���:�B�����3�ܷ�Ǆ���T�K��4���K��0���1g��Q{�y��t����0ҧ�Um�S�&R����J��&)�4l�m��y�ri�|��c6Ѫy���.��������N��L�z�;�\$��K�I���!8��|�l�L9�%��C	\rͺ�\"��)	�v3�#�a2h�1�ԝ�0�@���mM�1����AE��rĶ�HtTo�`PRqeY+�@'b=�6A��EG_����6��M��Ӑ+Eӟ'm�z�����&(k�I��n�ݛ\$�`K�˯�)ɬ���R�F���_��%Kȭ����\n��Y����S��K),��b���ł���lnӯ�J\nKg(^�]�A=A�&�o2���W9��Q���-ع!�Ι����_�*�Ȥ���3aˏ���ȡ���P4f��St�Uy!n�Cρ3��S >�w�������Q�����w޳-7�9CA��5���W�ZL~��(^�2e�=#�\"%����g6���?	a���&TcjI��i��\"�۹����`)�<��<�cYzi���׃sH��}]{�v�~��}%uۘ�]X����y�����Ek�}��z�g��W��\$�eN�A�[�mgdU�21�{v�0���r�B�/��/�������cR��\\#�6�0�`���x��:���CF닭������7o��0H�O��FOol+�����8\r �B�����m�\0�|c��<�1o�E�P5���F:�z +��bn�O-\0����>��������,�q/ʋh���:�E�dF���\0A'���x�FR�O���� ��p��>PN��]��Ǧ��4k������2�#BD&����u�2�q(��i��p`�\$�rG&C1X�N#bB:@�aDC�vB~[f�:c�:�^��h�	Db����n�#�\\���m�����1��[1R��|b�沯�Хʽ��b@�/����c��(c�\$P2b�bʎ!K9q��Ox�Q����) oʉ���1�KQ��Id�uQA-&@Ť��9�9�0L	�&�P�� �#-6Lr9! 	<q�W�4��O*��C��\"�@O�?ep�G��ޠ�.�'Ē1r����~G���_��2\"lb/y&��\0�q\$��ٍ��҉(�+�*��x5��J@�j`p@�\n�p�l��\"&�\$z�J'�~C�ڸ����\n���Z2\$��>��a��f��Xލ�1�L�#��\"��t#�>\$\"Fw�0��}.̎n�ètC�3F���#�4��Ȏx�u%I:eb�� @AW\0\"Av'��)O\0��M����L�XMjZG\"zn\rS�1E����on��	� �w;�����+R��C-��=X\r�|���2���1,�3�2��6�l2g�>S�L)�s':��'{���1�b��eI���JH��b�6ZS��--C3\\3g��~&#�X&*#g�2d�_��/+�F@��	��@e��\"��c�c�.�6�D�3��&n�4��\$+�0\"��G;D�q�u�1>g�І�#��03�H <�%��@�	�t\n`�";
            break;
        case"ru":
            $f = "�I4Qb�\r��h-Z(KA{���ᙘ@s4��\$h�X4m�E�FyAg�����\nQBKW2)R�A@�apz\0]NKWRi�Ay-]�!�&��	���p�CE#���yl��\n@N'R)��\0�	Nd*;AEJ�K����F���\$�V�&�'AA�0�@\nFC1��l7c+�&\"I�Iз��>Ĺ���K,q��ϴ�.��u�9�꠆��L���,&��NsD�M�����e!_��Z��G*�r�;i��9X��p�d����'ˌ6ky�}�V��\n�P����ػN�3\0\$�,�:)�f�(nB>�\$e�\n��mz������!0<=�����S<��lP�*�E�i�䦖�;�(P1�W�j�t�E��B��5��x�7(�9\r㒎\"\r#��1\r�*�9���7Kr�0�S8�<�(�9�#|���n;���%;�����(�?IQp�C%�G�N�C;���&�:±Æ�~��hk��ή�hO�i�9�\0G�BЌ�\nu�/*��=��*4�?@NՒ2��)�56d+R�C��<�%�N����=�jtB ��h�7JA\0�7���:\"��8J� �1�w�7�\0�o#��0�r��4��@�:�A\0�|c��2�\0yy���3��:����x�\r�m�At�3��p_��x.�K�|6ʲ��3J�m�8���^0�˪\"���wR��S��N����-X�,�dO!��ifE�dn�G&�Z�!�6��\r۴Ci��=@Z.�-j:b��9\r��Ό�#V�&�N󽯯���l����u�B�)���M/*~���*������3�I!J	t������0�p�����D.�_#��(h�P\"hGH�.��\"b�)d2�F�)t2Y�2i]/4]LY%J���iU8�k�B`��.L����2����M��{�G7�sp��q]�6eE��I�B�E��B�����ُ�AL(��Zۏ:\$d�����DZH)���s�ך��E� �2Tp��6�=�5��`��P��6���a�\r)��C;	\n�Xe�b���[s�w\ny���IZh�#\"��Ȟ�љ26�����!��X'�VEQ#:��rH��B(�\ni�P��	3��N*\"7�DD'w���K�v����\0��,RЩ���i	\0.%Q���A��(1\$�G@�`Z�Ї�3� �p	T�zB�9S�I{���-�Tm]���2VK�)3&�̝wҁ�9HO�Z<;���>�+����2�A�W\"��!z�h�^H��0#���e�K�����Г��O�Y�S&y��2���R�+I�u:�i�?�\nCU��*�)� �O�D>S�e��\"�N��'1AWBb��D�d+1�����W \$�tr��Ǣ�hV�3(�P4���i��x�����	i�8E���\ra�D� |�� tn��'D�;H�3ȵW��PO��Z�د��5�)���,����z̆D���Ď�\\;')^��aY��)�1&(Ř�c�z��F�Cs%M��8'&Xˆ��;��������>�0�%��(������ƌ��䊀K0����U��0���2؅�1���8�:7�4K\r3\r	yu�\$��l\r��1%�����m��0�dښC�u]��3[�xg[wA~�@�R�Z�@�1����a\r�΁#�D`�*keV�h%+�y�05�cK�h��>C(���H0H�Y(hX�!��M�(.@���4�R^�7� �C�iK���%Ҙ� \\k��\$�N�:��D�d}3m��\$�+W<�|yE5��\"��)�>�2�8y�H_��*��X��A�5��/Jz�����&'fH��wIP�(��ϒ��\\���)���D\$ml�9b/���gy`�\$���\n|�*fCi�P�Q���+H&�t��	v��5��FHC�J���P�\0�¤��x�=] ս�*z�\0�3٬��N���\r)K�2�͹�`���KT�%Y������iM9�W��N.�K��p�-i�,��ע�W�6&���8h���O F\n�AAi����W�.�52l��^!����w!sS���DGD��p�ʃnͬL�\"3��g�w��Hb�Y�u�'�԰_B8�J��ζ��(\r�O�@��ok�:O,�<J��2�E[����'i���%��P�!�[���p���}i�eY�Zė��`\$������\r��;�ڭ�wo������K\"�X�V{�l�W,�W#��v/۲��:�uF�Ž�=m]��-�h� t���P	B�:�#�02K��G��R���.K+�6�\\Fl�i�L�EtWɡ�#%���6��ɭS�\"q���5Rmw���4+�x�?{Ch��d�U5���B''��W������[<�<��ֿR���jN�����U�h�l�T�L��k�lG���:���@*����g,/G��o������b�y�B!h�����Ȇ��\"�����J*�d��k�H@\"�bj#!J�L�GL\$��jƘ N\nߧ�������N�S���Pz���̖*L�|Enȅ �\n��`�����,��\\��2�BY�:&�N��Ym�5�pZM\"8�L�\$o@^1���J�\$�G¥qI�u�Z��mδ�������&uC\0�P��\"N��u���Q/\r�2+���(��,)����Qaq�+Qm�7�BF�nv�E��!�r�\r ͈v,h�er���l((�1�֎�F�#��(N0\$�c���%�q���ò���	qTCѤ�q�/q�H�������ʯ�B%� �X�m�-av=E�U\"� Ǖ!\"e���F=� �L�!ql�\$a�\"\"����4L���g|(2\$�R(3�\\�H^�ª\$E�䩞�p���\\>��qb�Չ��p�W>rϔ�RJ���8C�ጜ�C2fn���n�n0dP�[\$���.K��Q����u����\n�ń|�]+�9,2���x��/B�0#-G���/\"�0�F�,Nz&Nrk��1H����/.����u��v(�Gj)�nw&�w��J��K�\"�%����E��[6�@#n|�<= Ϗ�Wm�\$.������0h��T>N�q�6ғ�;��8gt�PCG��S2bY2�����-�����.�~h��γXQ�C2c�=��<�����1����2.�=Ȇ�Ɂ>#�9х?2�\0��#?��S�20�򰘕°a1��BS\r���T#.&*�*@����t4\$9q��#tC9��D�@дET/E��E�aF2C��F�uAQ��,���r�Ȏ:�k/������N7Jt���#t��K��w�1�\n.��\$4�k�	��1����c�	*�Sޗ��z�P���t���Q�g��\n�Ӗ*��\\#�� NP4޷�h0i]P�\r+#�Lm]Kt��p�uէ�!.8u0dl��0P�~T���}����F���cI	IQ��`82���,�B���=q�Y0��r��5���@���A�D�u�Y�:���Y��Z��պ�mc\\�]]4oAu�]���MH�]�!V�_@�R��/3 ޵�`-�k�x�k8�2r\r�(A\ra�ZBƯn������\\�.@MH|u|@4��D�Z�3Y0�YT�I1�2I��VOf5�1�`��_��la	\n'�;G�h\\�Yf`�0�`�oh�*wu�A�i�X�<\$�0�B��(�'P?B�M5�J�.��/B�K��LP<�v�m�w[V|��iCP�v���jOm�V��\0P��\n�uv2�iP��J£K[(��Y��VV�f�sɎs�>�V�Mp��:��	e�1EΉq�*�uo,�D�\$}D��;Ѿ�V���wyF�1w�U�	xj��4u.	w�F�����C�=��r6jD�s�� 2`�I�\"�Z���XU��f�x���W�:C��7�Y��Iw�Fc�;�{�#��� �a�0REJ{HT+�Z�L&SF�!���'���L�Tb3%Rs!�6B��-��J4�|+�#��\n��Z�3U���kF�,IdU���|.y���f�x��u���Q1�,��lV#	*NR��ҵe03Ao�\$��*��<jE�\$7�E�@��>�-5��bݪ��.��P0�����?�V�xAh\"PAw,b[!c!n�*C@��\"�!D��ABS�t�'cC�*a)�\$�bSP\"VC�s3�I��U�/�3�NS�S��E9�#M/�h��͏#��c:2�Vym~x�~�}��j�O�R���ى�EN6���Y���tիX6Xy�3��P�/���:x���xH��!yO�\r*}�\"��n���FDD!����{j\"GPPT��� �x��8�9�+�x���e��ă�JV�yV�0��ǁ**���v���누��yI]��*�\$��3�N���!,��|c�<R�(3|53��חR�:�j";
            break;
        case"sk":
            $f = "N0��FP�%���(��]��(a�@n2�\r�C	��l7��&�����������P�\r�h���l2������5��rxdB\$r:�\rFQ\0��B���18���-9���H�0��cA��n8��)���D�&sL�b\nb�M&}0�a1g�̤�k0��2pQZ@�_bԷ���0 �_0��ɾ�h��\r�Y�83�Nb���p�/ƃN��b�a��aWw�M\r�+o;I���Cv��\0��!����F\"<�lb�Xj�v&�g��0��<���zn5������9\"iH�ڰ	ժ��\n�)�����9�#|&��C*N�c(b��6 P��+Ck�8�\n- I��<�B�K��2��h�:3(p�eHڇ?���\n� �-�~	\rRA-�����6&��9Ģ����H@���\nr4���6���@2\r�R.7��c^�S��1ã�(7�[b�E�`�4��C=AMqp�;�c X��H2���D4���9�Ax^;Ձr�:#�\\���zr��09�xD��j&�.�2&������|�����9S�Q����<2\0�5�������s��\r	��rM�#n�(�'9	�4ݍq(����B��\0Ă�N�`��\r��cSZ;!á�](�\n��%ǩ��P�b�քH�1�C-�:D�\0�:����:�֍�V̌`�:��#>R3�+��t���\rc ʠ����H���C҄����R6&�_-d\"�h^}�c`��Ah`�0��p�&Mka[|�K��#�f`�7���v�tXĶ�Rh�r���\"������S'#^B�6����\0�Ƃz֘����#m���^���w�w�-��;ZV���l꒎��x�3\r��R'��iC12b�ސ�cp�g���B5C�͘	�	�r�0��\n�}�=a���@��\"r3��zk9)� ���:��HŌ��`d\0�=3��ތi�����*_\$!�5�#4IHT4�������JrV�M�4,�qS��O�~�>O�d�������YD�i#�w�\n�M�h�V�P�*�� 0��VVS��P�퀕p�YHd���%H��B�U��W'4ꬕ��\r��<!\$(����u���2���8 G�D��	CZ�O�V7F=T4K�˔�&�*��b%��rr�aHh)�\rd�`@��s�����C1�\"�y�(g���3�Q�D�J�*��(�87,���Iy/e��#�x�J�C�98���\nɯ����b��(dPq�)�����'����\0PCB�`1�U�\r)������VmSڂm��;̷�����h�.3�D�\\F�ݼ��\0q�;�=d����	i�9�v��J�lG@^��:��Y@�\$���g��i`&�>����T�e!Ś�@�*�Q�b� �H �š\n�Ԓ��x�i�'���\$P@xS\n�XP��d�O\r������%)�6��n����1s�F�P�PYJ�Jl�ʀ���\$��\"��w��|��,��P85�:�&�T�O�\$1\$��RèdSt5��!HQ9��͠�p \n�@\"�m���&[���i�!�I�7;�(Z�\$W\$�E�V�.�\"%\n����\$�G���;ǀ�D�j��(�I��{�N��&��-�����.��;��R�Z�l�<�\n�l.�T;��]��X<�S�����FT0����+h��q\"�cO��\"�8H�;:�H�Hx^ٳ7Ju�\r/J�7�\$~5�!<1Af��P�u\rџ�C\0�!\r!�O�\\�A8��.���L1�%r轼ܸ�LPe�07��ו�	�u���G�NYui��?D\0��pJy�'ޅ��z���_[q��g5!�%ڄ�Z��?�0�=�<��{|1�p�\0�:(���e�\$hH�XY(�k�G\0��v��F�\"[�C	\0����j�E	O��������N���!���3)�5��14�&��e�U��{(� RM��B`�ê���Qr���t��F@�����a\r޻����ӿ	̧ML��Dg�86���˅B^���!q�F���([Q�Ț&Vj:}���3���*RR�Q�B��#rd�_���9�sI������\\���K�6��Z虘ƾ��\0�4�NjQJ���H�a9�N{\rқSz�bq'IJ0����9Ҍ��_2��H�\"�7��Ԭ����./�*R银!�\r�jٔ@���y�F� ڮf�V���`Ò_rñ�G�:/�BM��y=d�U�Cxy\$8+�:���;	����7��w)�)���ܑb7�sG��Y�z�u���c/��ؘ�~�׻-��e� oß�\n���fS�oO��\" \0���0R M:���4��̦���F���(H#p\r��	\n��`d���n���HRZG�π�s��b�ƞ�\0��/�m\0��\$��Ϟ��&�0R�B��6'/��Pbtl�ǀ�j�0uo����#�K�h����, n��B�J9�P�PĮ0@;	�t\$��\n`.Ш�Ь�а�LJ0��k𜂐�D���.D߮���X@��\"�klC��,�^���Q\0ұ�	�{-\$�@ҍ-�zp\nB�-\r��^��\"�,'�@�f&:�O/i(��&`��b^����Sbf`1F�hN�L��J��>��D(���(,���B�Eb6o��MP���,��X*�:��OQ�����/���r�����,Ό�a�������Z�L)1�]Q�ތ;Q��2���C��\"�(�����p;�9p��!��!`����#�9〶2(c&r�~(�=\"�/DK#�cd,N.	bLИ��΍���@'Q( o�J-g������	R���(���%��(��g���Q������\$R��xmR� l���GP\$��l���.m7��!q���N��R�2��r��q�*go/�G+���-6C�E�#��\$�K*�.Q\r�l���s\$'��I�0�M�3����.�4�2)���o�=) Kc�g�'\n�\rɠ.���B�s\rvր�\r��5�p�)�`�N�0�cbA#`�:\r��H�7�9�n2&���'S9�е�\r�V\rg�?*��E�<c�(%6\\&�äC���\$% ���T��Ak�\n���p?�N#c���8%�^��\r.,�lBKO.L�e9�CEB��P�����L����:#�Fx&pi`�A��F���J\$dh5�Lѩ�\\��6F\$/⨇�#SB�Q��|<Ѷ@�\r�(XFRt\"�JT�j�b1�_+rDi���\0�z���܏����x-ЋMq��R{��dr4���� �f��SE/�!2���AcV4z�HO�N��*'>L��0�V]�G\0���j脇p��C&�2������\$\n��!�6�g,��|�d�5CZ@��pP`���t�\"�˲i`�&pX�\nj�HB9#t�~1�l�T�H�K�k\rK�Ӵ�+�%�C&\n\$n���f�9\r�E�	\0�@�	�t\n`�";
            break;
        case"sl":
            $f = "S:D��ib#L&�H�%���(�6�����l7�WƓ��@d0�\r�Y�]0���XI�� ��\r&�y��'��̲��%9���J�nn��S鉆^ #!��j6� �!��n7��F�9�<l�I����/*�L��QZ�v���c���c��M�Q��3���g#N\0�e3�Nb	P��p�@s��Nn�b���f��.������Pl5MB�z67Q�����fn�_�T9�n3��'�Q�������(�p�]/�Sq��w�NG(�/Ktˈ)Ѐ��Q�_�����Ø�7�){�F)@������8�!#\n*)�h�ھKp�9!�P�2��h�:HLB)���� �5��Z1!��x���4�B�\n�l�\"�(*5�R<ɍ2< ��ڠ9\$�{4ȧ�?'��1�P�3�	�B�B��\r\\�Ø��`@&�`�3��:����x�E�ʹ�������x�:����J@|���8̍\r�L7�x�%��� c{B��B��5�)L=�h�1-\"�2�͓�3��#�aث��-\"p�;2c,��B�>�L�J2b:6��q�7-�q\rI-�sݶ���\r���1�cH�	q+Nr22�s\$�&hH�;!j4?�#�؟�`�%U�R�#�(�(�B��9���:�J�5�Òx�8��K&���b7�@P�4�k�7��Ԟ�*�{��c�`��>�1�n�pފb����89��u����5�=X6f\r\"�*��ea�mN&�R��ԕ\"��#�;\r�C��A`�Yˬ���� �\r.�4bx�C��3'J�^'��:L9�B���T�p��@#��2�ؐ@�-��t��0����+�P9�06�H�9[���)�pA[:��H�Tc�ۉC���>�[Z:%�,�Ǧ��{:��^*1�+7��4�*Q��1��	,O�j\n��s@�2 EL��J�5L��ebKaVy���x@v-	%(9�K�j )aK�4��x��<�����P�D���R�nR\nIJ�(Ϻ@�qO�����\$#6���q\\uR�BH@�S�_�Y#bn����ʀ��\"HI�(o��F�r���Jid��җ	^���e�C4#\"��2BgZ�݁�M�DѴ��\0c/K�4���K�7�Y�%�]NB�V-d9PЖd�Ԍ<\$\0@\n\n@)#��'��p�xC@�B9�����Z��R��J�KJ\r��k<\"r�ʁ�o\$��\"h#`q�A���%�lQ�d�<��@��	��L����֜�\\g�V�0�b�8i\\�.�x�i�,\\�\0�{��Ib(�`�H���\\̾\nNI�VD��J�nxS\n�8-:P�u��3nn�	S2I3�K��㔃\r�7)&|5�g��M+���(4� F\0��`�+�Z�g��ٓ��RHr&a����L��ue@(�B���:�tl�4p�Y��=���f���P:RAK�ؠkY�A\$dx�B/�A�g���e�pL�D#a��h��R*Ĳ���8O���xf�Ǥ�&3N2Q��N�4t�uq,ͭ����l�\"��L7�U䰛\0nzA�Z\n�_OQ�&�8D媎����~V��j��|�9�:\\Ґ�xs&j���lXpTY�\\�8ԕ.�;�IH`��5�yLxrA��Rtݗ���Zƥ�_/S�A�\$70@ր���l�:`�C�����LL��ܐ��a��3/�����{�lFj�,�����r4:qHI�\$\\��FT�����^+�J�\$�Ufx�3����Aa P64�ܬ�K��0L�`���8V.4������yX\\��5ߝ	+��<�\\��p.lZˊ@����A���_���O���>r�U��ДGC>p��L��ѤQ�i>\\ϖҬ|i�ߦ�� ZO�B�n���pZ�RN5Y.���B�==�\r��Z��R�\0�)���Ψ�V�����ͫ\0�l}m�qB�G_�\ra�[f��eR�ݸ�2���+7n���̕�ی��\r���ֻ�Yn�B������1*��_�=ǳ,���?�\n*%�D��j��Jk(�D���A/7��O،���iC)�\$\"~JEG-Ff2r/�9��6й���msA|V��]Id��hd1����U���0������#�5�9��-�?b<�����ָt>p8;����\\\r V�9Z�߂K�zԗpw��؟3�C�V�N]�FK\r�\\ʷcDa��\$�<��C���L�+�!\$&u�=F]�RJ\n�����\\��٭������Pq\0h�1��:Ѓ)&p;\$�ػ�~��sVy:�c���Scx���>߂�g��t�uX�Y:�݊34��J��k���Io݁�t����\"~�����ol��Ҍ�\r-Z�O������\0/�\rz\"�%\0��/�!o�����-xl-����0�7lH�A���N�j�!.��m#�SM:�����06Chc�p6L�^���G�N\$�Z�*6�:6BJ�	�.bl\$��</~9�j�,6�.p�f>��h*B�\n�L���2G�=C�EP���m!L5\"n��\"æ���\\������h�wE��Z�\0܋�\\rm�pGV1g�(q�)l���f��=.E/�����>(c��\"L��H�%��@��(n�V���\rc\r�D%l���p\\Ȱ��Q���1�\r \$�ZcĢl��	���(��c��c����Q�/�Z�p�_LN�\$f�Ed�\n���\$ld'qC\0q�Q�~0u�Y��g�� �;���-e��ʱ�\$Qb�\"q���;�#o�QP��x/cb-1��X���s��n�7�M���-�!D�\0��=�\n�2r8��ܲ|3P%�&p�&����%��@��À��.7e�&D+*���M�/H�ղ����\nu'��.r���s(��(����G-��ZirE\0�j�	j��z'�V�~��^ʕ��#�\0�\n���p>���L��o��+ͤ�-.\n�1�O,���2�3�>�}��3?S54pL\"�0#E��g�p� ��d\r � \nO@�ɳZ��\"g�7��b&kX�nh#b�h����0�^	�ިE@ D�gS�2��F����8\$b�Ǣ�����(s�I�P����Ř�mz���x�cb����ssQ?�.3c2�x̎\$�6�Tnd@chX�eBaIF��Zi�\r@��'Bx��\\���\n�aD�~�H����x�°�m\0\n���\$�\0�B*`\"�H&h�*��L�5�\08��&*PË2��dZ�F��n�P�k+�\0&#�D�J�l#�*K�R.E�";
            break;
        case"sr":
            $f = "�J4��4P-Ak	@��6�\r��h/`��P�\\33`���h���E����C��\\f�LJⰦ��e_���D�eh��RƂ���hQ�	��jQ����*�1a1�CV�9��%9��P	u6cc�U�P��/�A�B�P�b2��a��s\$_��T���I0�.\"u�Z�H��-�0ՃAcYXZ�5�V\$Q�4�Y�iq���c9m:��M�Q��v2�\r����i;M�S9�� :q�!���:\r<��˵ɫ�x�b���x�>D�q�M��|];ٴRT�R�Ҕ=�q0�!/kV֠�N�)\nS�)��H�3��<��Ӛ�ƨ2E�H�2	��׊�p���p@2�C��9(B#��9a�Fqx�81�{��î7cH�\$-ed]!Hc.�&Bد�O)y*,R�դ�T2�?ƃ0�*�R4��d�@��\"���Ʒ�O�X�(��F�Nh�\\������!�\n��M\$�31j���)�l�Ů)!?N�2HQ1O;�13�rζ�P�2\r��`�{��\r�D��l0�c�\$�a\0�X:���9�#���uۋc�c�f2�\0ya���3��:����x�s��\rYWF�����p^8Z��2��\r���	ј��ICpx�!�D�3������ښL��#G�(�O,�,��*�KƂZ�Ҍ��d��M������\n#l�㏭\n��7BC:F��#>�N��(��a�h�����ƄH��ʵ>�����ȺHH'ixZ�ӈ¾Dl/@�m�#��[��:����a�y�R<�ԠC&�3���k�+��5/!�'G�쒀�y~+@)��Ǯ��,�'prHI�T	G��.5F�sĠ�Q�fh��N��u�%)�i���\\����\nb���xtC:�R�zb�C\0Rx񼭺q��Y>�Ζ�IE�y�2hy/�\r&E�hRs�,3�@�����Ԍate/�L\"H@JqP*O-��ޠR��ŪVt}ً �ѣ���Ĕ����!C\$����na�ܛ�����f��W�<ɔ�\n���00�A\0uI�^���܁\0l\r�	5��@!�0� A�Z�\r��낀�\nKYD,f݌��R�A��ŋ�e�d�A��H�~�T���\"O�b+���\"�\r�*9�9D����H�j�4��C\"�Z��m-���>V(�:4�K[�~Ĉ��\"��f>�q`�[B���@%��qB	�����֊�E��8��#t�[+mn��¸�*��u��`�r�^I#����W�I\r�����%8>GG>w,ƊtA\rk�[a�yk�qK�F�S��LDŔHFD��IRR��Kk!�A�W��%��` G);�(a�[�Y��J���e��FIa���1���[�s-fa��@�_YD�̽�E+���cOL��P@@P��2�?�,�y0.生=2��\\�r�`σ�rQ��\\� �s�ڴWt�7�z�I�dcF�U�\"S�Yk�	|�!�2Bk�qU�s\\ђ��&1�BƠ�.27\r\nb�[{������P�I!0�2��pղ�\r�����[�sV���#7Uy�^4�%,�o^W�ͱL�>�tB�B�O\naR��im:���1Y'�	����C�j��#M��u����#cY1��P���e�5d앏�\$�<��7��0���R¤A�H<מ�� 9+#J��Z(i�j�+�;�H���F��ȶ�l|R��'en1��B䅕�=�8'��@B�D!P\"�\0Q.H��P���˫T�� +�\0�B`Eș\$9b\$�R�P\$����T��-`�&R��3S�rI�P�w\\e|71.伦XW�enh���>��9� �\r̢W6������hP1|,i�\$*�G\0(����C�Jn�!Q�A�\"�,T���^��/�|��?DJ�����j��I���q\n�̸I]��p�f��eI[ !Dt畆��:�q�ғ6���Z�p��\\H���T�`����gE��-�Od�d��f.�\$��=�4s\r+(<4���Z'\\\\o!L2���\\���Nhl��9�P!�h�\$2�u4��΃�o�2����I�U� ��Iip'��=�\0I17<�9�w�Qubr1v]��\",!��d2N�������N&�M\0)���B��d���Iq\$\r��g�PJ%Q9o����s�����i�\\��T!\$��J�g��+p�]N��t�B�3��K�tdm,�:�_sP6O��z'ܢ�L\r�w<�-�D�)�w�FV�K}޶;h��UAp	�D��g��=�����ۋ�s�/�8!~_Q{��g�7�5>���a ~V���;�#��������b�l*.K�D��A�Ŏh{:q�8�{����������o������2i�X��\0g�\0������0�ib'P�\"�p&�/����(4���E�d��\$�^x\"耂�I���Q\nt����̌-�f��p\$��)�͠ %/-VGg� ��*)�Q��v�|bdvFh�P��>͘b��0��b�0c\rv=��?/*�rD�`K\r�P��!��B�V��O�Xf\"�ݯ��N4DΖԭ��W�NQX?��<0�QМ�\r(����'���y\rcq:��A1\"@�(*ě�]\"�w�d~�H���7�@�q\"3�x��*rMwC�Ͱ����D��d	�#����^��sH'#V��^L�%B졆��Nt���qT4�q�\$��n�F�͂���=�^>L�M�3�Rn���`�q�Q*���ت��K�d�%逌�Y!\rC!MI!�c!�S��!2!qP1����\n�f��Q�+����-����F6�B�����2�1��2W r\\��b��f�����l&q��\$�{#d�a&ON�2j����,�J��F�rB��F��#r����+�%s,l\\�c+�M6u&���&��ς�.l���\r�է*��f>�1� E�w���/��ܥBi������i_#b�2��#X�1O0�AGD�Bڽ�G\r/h!2�Ͳ�,��+���3�t��k\"8���r�f���'�-G-rveS� �����U2�&���:ޏz�NV-���9R��r�;�&���8����;���3�#S���҇3=����K7>��{�vP�X}DF�#����\$��4�0u�>�C��v�x�%��-��RB��Ts�-1W�+(X����g��#��v��iEoHe\r�|#�~n�E�E��pSwTt��7�,&\r��C8P�B��k����C�8Ԩ\"N�='=T��T�=�]E�F�l����{Gm�MT�M���\\L�?��k�F��uK�8�IOl�K��9�HxuD4�r�\$�J�4�Mn�C��<��΢e2Nޏ?b���\0&d�s��L�I��Q�<�,�+Tԏ1��)�Xp�������c���F�5F{�tv����*�\$����t�s�*ﰢ�MY��*��ZC��\r�V,���B��IbA�7\$F�b��2\r��JD_�\\\n���Z|��jBo���i%�����Ϣ��̵bưu^�O�*����+\0.��\"mZRϖ�J�S@��F	�޶��.����o�ST�P3	bMw]�]t����\\����Q/\r�&�2d��g���41��G�Q�,ed��m��?Y�Zӎ�c5\n����FW쐱�е��ZAuV@��a��m�y�T�m���v�)�&�1��5�H�Q�=l�;.T��!+H�5\r�Qsm.^zBH�cY��oq� �v�\"�qq�>�0�\\Qc>bP%\"�\nQ�@�<\n�x��\r�3(�\r��v¬��@��#�{3�kc��h��3x'���	c2]c��M�#�Ay��g&�5#J�B�P�\rd�Q]RP�ol��f�";
            break;
        case"ta":
            $f = "�W* �i��F�\\Hd_�����+�BQp�� 9���t\\U�����@�W��(<�\\��@1	|�@(:�\r��	�S.WA��ht�]�R&����\\�����I`�D�J�\$��:��TϠX��`�*���rj1k�,�Յz@%9���5|�Ud�ߠj䦸��C��f4����~�L��g�����p:E5�e&���@.�����qu����W[��\"�+@�m��\0��,-��һ[�׋&��a;D�x��r4��&�)��s<�!���:\r?����8\nRl�������[zR.�<���\n��8N\"��0���AN�*�Åq`��	�&�B��%0dB���Bʳ�(B�ֶnK��*���9Q�āB��4��:�����Nr\$��Ţ��)2��0�\n*��[�;��\0�9Cx�����/��3\r�{����2���9�#|�\0�*�L��c��\$�h�7\r�/�iB��&�r̤ʲp�����I��G��:�.�z���X�.����p{��s^�8�7��-�EyqVP�\0�<�o��F��h�*r�M�����V�6����(��ѰP*�s=�I�\$�H������D�l\"�D,m�JY�D�J�f�茙еEθ*5&ܡםEK# �\$L�\0�7���:\$\n�5d��1���8���7h@;�/˹��٨�;�C X���9�0z\r��8a�^���\\��ct�MC8^2��x�h���L\0|6�O�3MCk�@���^0��\\�����LD�/�R�����^6fY�)JV��h�]H�K|%(b��0��R��1d;Na�u\"/sf��U�o�)��uM\n�����W��zr2�CV��P�0�Ct�3�!(�v�x�z��^�C�]J�X���x��\"�A�=�*�����e)�_�rկ��H�Cc\$��6Pʥ��7�����0u\r��:7BBr�AV|����;H��A-E0����eI0�ѫ|'��F��;�y&�\"X�+�Y����ֈXK�~i`�@���s�`..1V����l\r��;\0�CrE\n!0�=�������PLQR�_n�+��\0�Nc�Jq�:7X+�i0\n�̿t0���4��>�d� ]��C0�H��\"�sH�^�g6qc�!{ϙ|/�\"^���4r&I�P�\$��/*X�Ett��Kރ`����d#󉥾Ah�ɴ�B����O�I�eQ����3c�u��ؑH�ݢ\\:�iIԟ(%4Ǝ��Gxl�� B�����/��D�9\0��w^a�\r�3�8p��U�	P����+r\r��A �Y[-�\00Β�CX����AI��Y��0RW�S\nA�3\r'�h-N|��|��h.FJ���L���0�.b9�n��\$�s�L�P�#M\n{�&=�gR(K�-�rd�8]/�r�+�P����e��1����cPJ\00021֊w�CJm!��>�`�In��\0�q\\-ʲ5F)y�H��f˿a�l_��7&q}�F���Ȃ��:`�%r�{N#J�Mf�޸�0���\nf�4���P��Gi--�����ڨwj�i�&����\n|O�A6��4J�l�Ń��+:zg���ۙ pL�J����d\r�)����,T��|[EĲ4_	�Lu־����ʠ��f�-�#�z��pX�L0�kh�hͷ�qn�Rfp~O�\$�b�\0�o��i��Ϋ0��n�`p�IUQ��>1��\0�/��DW���u�^\$�XiKO��\nj	�AG�|O)�='���W��Ðt>�2��þ^P�\"�Yw�#,��ΩONι�7�ƝM�\"�=9�O�g[�4�7��T�#\r+b�-��G)�n*�ڏU(�_�5����6�\"[��qH(�'�^��M���\\��V���W��>N�\r/L�2E���>\r 8�e�2i\r���5��~[&(l���F�{�,�§����)Q\n<)�D)�򿪻�Q/��&{��:�ok'[����5]0Xb\r (�A�3��C�\$L��ކ\\�xrk��)¹㵰b�e(%��\0@�p \nq�he�@z��F\n�0=0�4�\r'd�Eb�����(O\"��i���B�p�h�#d�PO	��*�\0�B�E�=L\"P�z��(�>s�8t�;%n%t��[*�@xe���2�{=�6C8�B�NH�6�_N�6�3�dHQ�?Ǥ=t���|�g�RF�i�%\$%X/�bT�\"����Q�	FP����Rp5[P��8o^k����)����v���U�)*�p�w�|6P�<y�=\\\$��0>�G�����Y���9f�ר�����j�;W��sj���e��F �+<�T?��Z���B`�S\0o�p�?���\\#c�x�~. t���M ���|�bO��N%T��\"a�*��.0xx���(p'�i&�`�]�����fG\r\n�,`��\r ��4�V��=�(�����8�d��T�Њzc���gB�*�����/�R!Ϗ�͎�r��`���(����\r+YPگ���'R�d�&*��+�}����T���]��T��s�v�Ϻ ���z�Ob��֞q.�Dr�\"�:�i�6qp�o�HdD�0��5++4I�P+�,k.��^Ѕ���>Ũ�D,�����x�o�\0���g4�OZ��z�PJ\\�N����i��Ί���8ΰ\n��`�\r�L��d*̦J�>�~�(aO��̄��B�bS\$�8ǰ��6zp�qF7'� Q���x�1��i�v����\$���Mq�2�F�{!C�u.w)�#0�D��`!�i\"�\"KZ���\"ʎ�2L�r6��#���@���\$Gq�n�������o��u&��F����I��r�Q))��1ó 1E%��1��)L�&rq)���+���% ] ������	v�k�,�-D��%*�#)�\"h�\"��AOLw`���r��.�m�\n�pN�n��b�(ѓ2P`�P֕�,��'�4(�R4du*�R����\"�6���0Jx4y!\"�,�x�g�1\$��\$`�����ڌ���)]�	2qb��*q	5̐�GFb\0�b���s-����w��q�|\n�*��B�.�S�5��㰾���W�F��_'Z\0�=��Z�ZF�&��+g8iГ1�9O���R�2�0ҍ.��s>���/�t�\r2RT#)�5-�X8	Ȫ2���:\0�ZL���r�t?;����r�\$C�1���TO\$��'TkHXW�t���Ӥ_G�qDTh�4f��AT-��Cԗ6C%GJo pȐ��/)�+�T��:fxSf�L\\�1���P����)��b�2�@�M �N\"5Hr-�'S�P�t�,�s�!Ez���t���)O\"���+P��2|���(4U\r��\0002��tcH�EHu<��\n�	��	�T�@\n��P��S��G<\\K��,�P�)l��n��z{ҋFj�5[0�J�fVR̞�OI({5��64�B��%��YJwD�β�aK5�Ku�K��.���ʝIIF3HL��b���G�%<�v5��Y�SI�d)����x�\\�3+>�@�m�6i*u�DrVxq���lϳ��O�`����\$1M�\"#�A+5)�5[�q4��v�1?6U0/t�)�\$���^��y]u�BU�1*�g��.rC^��_�5���S1i��]�B�i�I�s:c�GnƜ�!b�A�x�U���{��S�aH6j�vMI�&�����Q`�L��e�����ɧ��C�<A�!�����vQ+mI�\0��V4wl��\"V���,�v�I�9l��hv�RR����a��gNn�5j ���ƶ@~�7������qW-g��n�\rR6!KԝJS�s/\"��gh�uQvV�u�:�]6�+p�4�oz�oV��W�f�)W��B��Uw�^��J�}Jֹh�AUU�AP�.��J��Y��*s\0�R%\"[��< ���Y��9U�\\O�\0�#��y�ɞ\"�p�bH����7x;�A�%��8h֥]֩iF������n�x'���y�wX<)X_���Xd �X�D?]�W�x��'�Z�|/��A�3V-�sS���,�-?'+s�s��1�c�)p�d6OI֫c����w}'k��a珵���f�8�8ko4��(�(&�\$�j�0rb)�`��b8�t@����X�Ay�5����f��ѕM�P��~w�0��AQ?cכe�ɏ1��t�l��w+���G��%^�'ah7�!y^|�c�W�k��8Y��1�qI{��{����{rYmŔ�\r��T��3Áx|p��-^F��ѱ8���_�v%��\n��or�����}9#�F��\nKpy�Q������J����'K��f���X둉im�c�&w����L�3���V`R=rcx:��+��/��Q�rm�65���aYn�{rZ�w�#��?.�82�	�[��P@�Z�`B�	4��>�y���Gd�4wzo�5�Sy�IHl�V.�4i.Y�:߇�EfQ�eQ��~�Lަ\r�Vj`�O\$�f(��� ��̢�r+������H��NL��\n���pN��ℒ+ڍsק[����֚q��n��R����ʃ�u����lI�TU1���E��)�u�9���Y�w�r	�@��'\r&�A�;A�ɚlt}����Ӳ�*��V����PAD,��y��RS�33�ғ�h{�H54p�)3MQ������sK\r�y �3k�m�d?���y��V-��v��T����۸C��i���Jm�8/�xrB`PQH\$������{���y75�e�Xuf�/H�K���[b�u4���\"z�!��mz'f�-����t\n��>�<.N��\r�Q1��U��A�)��\rȜ�G2�+WGG-7\"��6E:եX�sb��ʼG��^pDsL�M�MO���c�L��v�u�\\�n\0��i�@�f`@Ɛ��]�\nr�`�����\r�+Y�]Q�'��̦7�rx�#x���p�ʦ�9'他|2�C\\�κSŰ\n��ƣ�.D7�sWa^]X��	����]8�Մ�ǯO8�]�a�dA���]\0}�ZA�l�b��MmE,	\0�@�	�t\n`�";
            break;
        case"th":
            $f = "�\\! �M��@�0tD\0�� \nX:&\0��*�\n8�\0�	E�30�/\0ZB�(^\0�A�K�2\0���&��b�8�KG�n����	I�?J\\�)��b�.��)�\\�S��\"��s\0C�WJ��_6\\+eV�6r�Jé5k���]�8��@%9��9��4��fv2� #!��j6�5��:�i\\�(�zʳy�W e�j�\0MLrS��{q\0�ק�|\\Iq	�n�[�R�|��馛��7;Z��4	=j����.����Y7�D�	�� 7����i6L�S�������0��x�4\r/��0�O�ڶ�p��\0@�-�p�BP�,�JQpXD1���jCb�2�α;�󤅗\$3��\$\r�6��мJ���+��.�6��Q󄟨1���`P���#pά����P.�JV�!��\0�0@P�7\ro��7(�9\r㒰\"A0c�ÿ���7N�{OS��<@�p�4��4�È���r�|��2DA4��h��1#R��-t��I1��R� �-QaT8n󄙠΃����\$!- �i�S��#�������3\0\\�+�b��p����qf�V��U�J�T�E��^R��m,�s7(��\\1圔�خm��]���]�N�*��� ��l�7 ��>x�p�8�c�1��<�8l	#��;�0;ӌ�y(�;�# X��9�0z\r��8a�^��(\\0�8\\�8��x�7�]�C ^1��8���8��%7�x�8�l��Ŏ��r��t��Jd�\\�i�~��V+h��\n4`\\;.�KM�|�G%6p��R����\r<1���I{�����B��9\rҨ�9�#\"L�CIu��&qd�'q�c�|i(��Qj{\$�>�\\V\"���7��'6���RŐ�`���߬�B&r0��f&;#`�2�[�)Ћ��*Sw��t4���\n��6*��G��%^�U�\n�����l�\"�\0(���IHq߻C�OIڥ'�8��㾇�+-�{,��J��_\0(#>���a�7?�\0��D���)���ձTC*h�!T/ˑ��T�S.� \r��\"�'����%�C��[	Yo����h�R�c�턓+(MaނȵsƢQD�vhJ����1�m���ʍ�[�tB��EUb�|��!>�:��S�@(��N{�xf�����X�W��;k�\r��ϓa\r��UX�τ��Ҩsfa�9K���\nUH�������<�VQ�<2U\$\0�Fu�T�\$�^v͂�-ԜH��<��0�s��\"�v�ѷZr���{,����!X��J��,x�q���{A�k��^���D�M��1��c5=l�5����O���6V�V�lq�r<�X���H^:Ђ�.�,X+잯��BW�}��,��j�͡�A5��Z8ԃ�/f)�<\0�� .f�ږ3�z��Ch�ݣ��ܛ�sLi�AA�U�Z�Y	!�8���å6��XvT���!���0���ՈQtD����ҫ&\n\"S�E�; t�D�P!�b=�\\9Q��f��R`1��0�ƙ	�� s�jC�a���1֋_(s8����5=Lz�F�65ė�c]U�v햢~�I�%�a@\$\0@\n@)��R)��\0(2N�Ex�r��WA\rD�{V���>�����똸r���3lxw��>L�b�u|�IS�q �4���jE��<�ԙ�aq�d��XWKav8F�0�R,�N�0����T��;��]<��IC��a���&Ƀu�A���ؘfN��Wf��}̘̚��V�~�土)f����4���)�r��K�6���GRg�KU��%פ9u�j\"n���0R�ls����~ P#����]��ϔ��l@3�2\0@�M��0l�!�R�C�\\l#�y�1�D�{��>p�L��i�2*��f*3��%�[�<'\0� A\n�m�@(L���,�q7ݲw\$ݐ�8Ty��:(�ӂ�7��Q�*u(����m�?#���y\"��s'q'iap�vcِR{��v���HDP4�8Eb�síı���g�r�R]pż凜\"T�e��Cb)��\n�������E捑_��.W����IQ��@�hU�t\nNH(�)\n�RM�H���܈\\�:�ko��<�U��`��^�#rn�WC�^���:�ƕH��v��1�h�p~&��iO�XI��)���x=,���uЕ�M�p���Is�Cǖ�V�VA]&2+l�\r��#��ع�8k\"�O��ӥ�H�����F��{X�rBF��.�I��H�n.\0�ri����p�h��g?r�8����b;����\$E�L~w<2��+^(Ì��<E��+�`x�Zڥb ��΢��B�L`Y��9%�Le���\n��`���& ��,����\$\n�-�ӧ����:Nt�(�U�\0^3jD���or��Qĳ��&pX�P\\�G8���0�\\��;hX��^.pk�海P�-P����%tpE�'p� �+r�p��p���搧{\n��	~A�Ī[��n@��R��X��VzO�pn,2��b~z�\n+g��c�M�'����.Zc����\0W�XgBw`���d���\rj��\0���\$\$5�&}��ޱT��nЪ��bU��'m�.E������-F�5�6��'�<9��9��q%��) �`&h��%�ޣ�9~E��KG�v��b�qz��pnz*��F��ţ���%����+#�O��0���,��l�'7�:Wg1 f��.8��B�+rk;\"E�A���La#1�5\r\0002\r�#��ݱ��� ��bЍ&�Nh�p�-�[����<G�{�����J��Y8F��n�0 ����B��SRT���������n5����&��ty�9%�m%-0��v~Od�M�I��*+ޛ�����S*��[�0��Y\r����pr�.m�*Κ\\S��-Io�=1�l�n��L�1�\"r�.Ɗ��3�n%0�A'*�T��q�[�G\0��+��Z�s`��	B_	�^;pw0�M\n(\\Јb<d&j�Ϧ�J�H���5R:�S�٢g:\r�\r�܀c�����HqS�%)�;'�;m�I�:�?CQ2�K3��%	F��@���-ϧ.g����č�<#�+����\"\\�nӚXH77���rx����,�zss�4�A�\$�Ѓ�@jDGq@��S���73���<�M���Z�Eώ��D�Ƌg���wE'Eo�;�n����'�-<�It%;�v7��1�q�>\"�S�!���g�����.`���\0���9ԛJT�t���⡐���N��	>T���!4OqO���T��4�2����x���F�z-C�6��[Qv+\$)Q�jV���#���)T.[G�ef �0Է4j�5o8�AO�J�<�w:�z���W��TIP��Q/o<��<\0@�����45�OnF>d�c���qRTIZ�}[H{Y��\\\0�\\U�}�?e�K��E����0��H�<\n���u��u2�\0�6X��X��X�S`��a�P�,8S�3�at�4u��AbO�\\��dD�V�TUR��N�0��]�7l`���Z3QU�XC��d�01�b�'͟foy�kb�	J\r��+����Z�Bz��5�K/� \r*��4�9-:�(y�Bٖ�m1BLafu��@)Ȯ���m���8!k��Ps�9�l�3i�*�̙��`ƕ��\rm)�>��K\"Ôo.J+\0��������M��\n���pOjJ�U\r�8ӇK�+���ʕLn]P��k�mI>�.b�i�*�`�u��..�v[G�9� 	��*Y�PL�6��ԍ�8(H\"�rm���-��g6+��p�	��զ�gDA`~�uG�����qP�FTg�h΢X1�72�%�5\"64�ޙ���	���[b��J'��oQD6��\$S/0350w�RGb��@\n��?��=�D� �\nhaOH���)�v2peuJ�x�a�L\"��0ݴ&4&nUA��/��(F�؛D�yc��\"e,�%�r��?K��G�v@�c@���Hr�P�C�q�(�(�^�z�Rߩ�Et��p�4�U��1D��L;e~:n�^r������JC��4�jF.ђ�A)�am�cn\$�yǂ+�yR3��e6�.���w��	\0t	��@�\n`";
            break;
        case"tr":
            $f = "E6�M�	�i=�BQp�� 9������ 3����!��i6`'�y�\\\nb,P!�= 2�̑H���o<�N�X�bn���)̅'��b��)��:GX���@\nFC1��l7ASv*|%4��F`(�a1\r�	!���^�2Q�|%�O3���v��K��s��fSd��kXjya��t5��XlF�:�ډi��x���\\�F�a6�3���]7��F	�Ӻ��AE=�� 4�\\�K�K:�L&�QT�k7��8��KH4���(�K�7z�?q��<&0n	��=�S���#`�����ք�p�Bc��\$.�RЍ�H#��z�:#���\r�X�7�{T���b1��P���0+%��1;q��4��+���@�:(1��2 #r<���+�𰣘�8	+\n0�l��\r�8@���:�0�mp�4��@ި\"��9��(��.4C(��C@�:�t��2b��(��!|�/Σ���J(|6��r3\$�l�4�!�^0��<p��+6#��@��m���492+�ڼ6ʘҲ���Ƨ	⤪YP�\"[�;�����Xț0C�����ԉq���/�����(�:C�;0 �RAb��;�E�)?^�u�N�փ\$���%�L�D�_43E8� .��:�+f, ��l\"4�-H�ϥ�������Ym���lc�Sq��(���<��P�Y��;wW���z��v}�O�.��O\$V�c�jz���/p�:�����p@��9�c��m�z��qȂ5�H�|�����k�Ųj�0�VLb\"@T�Y��\0a��j>6���>�m�p������rd;��=���x�l�L�I�b�V���̖!u�o�� �k8.�\rn����D�Û��4a@�)�B0R\rL��:��9\r�X����3���{7ao����n[�\$�\\�'�qc��\n�>s�d͒���Xk]�莑�F��|O�\0�� |� �,��8*er�eX\"�7*�X�s�k�)rf�êx\r��:bbC�#	����O�k*��>�b\n��}v�2>\0@`Ca>F\rm��Id�I�@�5\n��J�Q��)%(���?j�4)���iL&q5B`|�_�.\rС��:�1�4�*��|�	31fd�<&�hCj3l6r`Gߙ�~}���E�|)7-S��x���8d`�7S��H�!2m� p���r�4�I)<�B��P	@�K���H�\0����0f�ڰ)�8�5 ��C��rX���-�G�T e�g-��\\��H	�Cl�=E��\$�6\$ZdrR�Ml����Ժ�.Ȣ;ZmɌ��b|�Bd4�����=22c_%��x��,�J<9y�FH�\r�GE4�cF���u*�k'%��+�E�<��0xS\n���;�rcd���.{L駤l� �qèy蝱 s��������`�1	#c��ڞS��}��A&e*���xrL��c��+\"���H5�����{z�B}1���0\nZ�ʆ�ŐPU0\n	�8P�T�,\$���9�0��\"R��!�za�٫8(L���Z`�j\"{Z0�L�kgmWIr j5�6vg��cw�t=\"v��\\�I�\$阢9*_�Ƙ�(,�[&P�e�A�A�poU�-�DS�RQ/z<Eʸ����P�2#���8��ڜ{i�姸6ʅ���\$@���\r/1e\re�^�H�BϤHX�6���l��cTS2_j5��bK\$E��,�eP5�_ve��+���U�=A�0X\n����A5#���Bp ���\\�-�nXK-�38�Ú��u�̆�,\r�y>7bґ�͆-{^�jƆ}�a�\n����	x��QE�|0�9Č]�_F&,\"Uަ�ő	�O�tBv�.�x9;BC	\09U������&3�D�5\$S,L�x���w�H��ju��'�nNֳ772�wd�-��ZM�3�`���}�j��~*��l��hp�ڻ��bav�0�n�C��X�4�3���R�I���[�N?-�)����O�a�t���&���'|c�)���H�!&R��I5����/��k(8�ęZ⪉���B�;�P�D:)�Y(WGM� ��k�d�ͩ1�*.��'��f�WJ�D�B��uz	aB3^^p�q�xd�NŻ̄\$2fWn�kU��n��y��vN�s}��;{�4j�!L0\$���6�@���kL�,�|��\n��X�ӱm�!|Dk�I{���ºN.�Qv�,3>mI�JX�Js8xGK����@�@u���:�0�mRZ7m\0\\^���SP'�d9���}wf�*6<vbni�S�������N��p�����D�Ƴwo	/��	�����Ϻw_�[s��Ϭ�\"�����c���t���\0��\0����L��d\r|hK�����B���p��H#��T�O,��0~��#��\n`l`��:�K���}�ɦ)�\n��)���Τ��\"���YK�8-����OX�O�z���D���	̺ԏ�%|��\rJ�M9\0L�60P\np�\rP���\r�>���\r ��Pjɥִ��A�L;��T�\"A�u�m����������\nL�\$�_F;�*���&�\"4�\"D��<YQ2ac�	��p�@#dI��V@#hB���\rY#1����''�ϱ-Y)�kC1c�D;��EmB�O��܏1��KbN�[q�@�@�~�c�h� @U�֐k��n2��2ǋ��'|�3)7+��w�l,F��ɏ�\\��J�e��M���ظO��N�F8�H�.�2�3�6^I�@d@\r�V_B�kJp��:\n���ph@�@���F���##V���o���zC�(��e�V,�bc�\"RLnFbC,4����+Z*dsG�|�F%��6#f\\\r;e�s������\$��.��Y��9%h��#�@gRq��\n��{�fȰ4,�8r1��4�'6'�w1G�ߒ�*Y�5� }(n�*� d�\$)Y�l�3��7�Q	�2W�,��̦�\"J�E`����>h�`#�e7�x��\r�6i���\"_���4��C\\�Rr�4E�/�ZIӘ�Cpc��8��r#��ϒ�3\$F\"޽E�3\$l-����CF���\$�]��X��";
            break;
        case"uk":
            $f = "�I4�ɠ�h-`��&�K�BQp�� 9��	�r�h-��-}[��Z����H`R������db��rb�h�d��Z��G��H�����\r�Ms6@Se+ȃE6�J�Td�Jsh\$g�\$�G��f�j>���C��f4����j��SdR�B�\rh��SE�6\rV�G!TI��V�����{Z�L����ʔi%Q�B���vUXh���Z<,�΢A��e�����v4��s)�@t�NC	Ӑt4z�C	��kK�4\\L+U0\\F�>�kC�5�A��2@�\$M��4�TA��J\\G�OR����	�.�%\nK���B��4��;\\��\r�'��T��SX5���5�C�����7�I���<����G��� �8A\"�C(��\rØ�7�-*b�E�N��I!`���<��̔`@�E\n.��hL%� h'L�6K#D��#�a�+�a�56d\nhͶ�Jb��s�b��d,��(3�@#D� �Щ{V�F:4O�j�@���#E�1- h�F�G\n7��iR%e�Nܦ�����2�GB�6��@2\r7��ô8G���1�n���\r����K��Z�e�9�����4C(��C@�:�t��6=�ǃ8^2��x�uݣ���K8|6ǎD@3G�k�)���^0��Z��1|0���F���ZS_?4�@5j��g�7�|�>�r����6-Hٴv#j�������t(+�#����J2 �ė�;ʜ׻N�l��|YS*jH�!��4Q\$���>!�s=@O�!\n&hٲK�3���A�Dp(|\"^��6Z#��6�,G�eO�4R5{ɢѮ��5õJ������^��5����六&�g�Y�Mi:�%ur�E���!Hl0EP\n�X3�r��C���������0���&C)Z#S�|�11<ޔ����mK@)/鵳\"�R�y3V0�5~��)|\"��g�����L�A����W&�����F�U��=��hb��T�T�J�ז��zi��n!&���X�jM�B��@PDOԒ�����S�@(��6�rxf��d�f�kB�o9lx7�@R�u[h39�@xgD�x@��g(�G�Բpu;��9���f��sV�	H'@@R�t�W��!y�u�b��a`��p��BP#2��4�\"V�A�trh��b��I���*Tfu��r��l3�� ��iQ)E���\"�^�T9� |�8tl���԰T��ȒT�TDb�E����I�P���S�*D���Ae�\\P�R�\"��<	���L&�b�G�8��\$��^��|/�������r�Ka�9(%\$���I\r����&�>H`��.v�v)�a\rlih�s)|�A�h�s􊊛�Bn��E�Q��4��6�� \\���#�O����͐0�j\n��]���8G\$n�Oת�`0��@�}/\r.|9�sl��W�f���4o��I.M\\\0�m�����@\n8)�R������*9O¢#�S�e�!� �\\I�CJ�x1ͥ�Ni�:'L2�E����<K=kU����~!G�'�EQT�A-Bi��ҞZ�\r�h�̻+�Q!�������oZ1H7��{���k����9�)0ZM[��&�3��U:��srN�{J�OI'	e���r���\r���e�]sZ!��ٍGVQ�a��8.:�p�y�=&M��`C�}�*��v�E�	�L*<��/�p.L��hz��T �|�B'!zr&�����A,#�*Fʙ���M�*eN�K��ѹڎn�\0�f(S}��3[�@t�F\n�An�8�J֬}�xt�#���PkT%���h��ѭ �e�.��RVA�#�r̼�	�zI�.#�N\r\r<@52�p�lhCR9��ʬTؚ5C�h����%�\"�^��{�j�h���u�]��f!�LBYz��n����\rw#�T�Jp �\\T�����H��! ���DFgto��-j��B<~![)�i�����T�b.��wO����<E�h��>�y6������si-�p�ŵSF�R�D���跱�>Ȫk�2ΚAy(��2l��������Q�v:6F�5�祈^�AN�h7̘��\nx\na�=:+eJ��\na��[[{�w��T�׸��[!���r�n��{&-8�t�\\CJa\n{�fq�	{��S�%��E�\r	��~7|^F�5���9T�u�(�E�����LM3�)Go{���eԩ��:�Ne3Gnm��bA��x{�S�7!N��%��=j+غN\np�y2����3,P�C	\0�#�ҳ���ZA�ߝ� ����ЀYN'槓�j��(��q�����fX���~Hߌ�{d4��(ф4�	��4\$��g\\0&/�PN���(N��i��.0f�K	wk\"_��ذ�=�?�\"�l���K�������c�h�A'�Ԉ�a��	HyB�O.��/	�hG	0g\n�C�#�m���F�Pmp��W\0�5��\r%�Z\$v�3-.e�b@-�,\$�\$d�'*AiD,F�\rp�4/\r�4�П-�R͂-��l�ج^!FR(Q2R�]\n�q:>CV~o �K���F0��h�X�b�Np�h|����*�BM�44fl�JE�ʗz#CL�d�g���`-�A	^h��,W\0�oC0Nr���\$��q�B���N�������c�4-n�AΠ����E+pv{q��h���6�*�K\n%J�ޭfW�\0��P����A��2Nd7'�B<Cm%�J�ax�\$v�\"A�4�@%'|r(z�q�+I�D�c\"���h�8=2?n�)/{�����\r��B���)�P�8��T!�4iBV!m�%@S'�v�����U����D�cp�UN,�l�R���Ng����������!�y�0R�/�ɲ��R���2�bf#h2��K2�6Ŏ,�(\\�m�����4K��Q�13�Q0�2�<�!4)��a4��4�\n�gy��5�3Sdݓ?6�O4k���S�!7�81��o-��3(�C#51�.3��\"�pH�M�s�1���!0�:��V��;�0��\"�!m���h��?�����*��W&�{��1e\"�GX�#,?\$*�Σ?�c@1�eqh19)\"�j��L�l\"S!�zBc���;�@��,\$�0+���:��3��S�#Or+�D�Ơ��/B)�&��k?�+��h��53�1\$h��7s�.�?&OJ�W>�v���+S��	b��V�j�T�߳�J��Mq�=Q� �N\"�Ns�)S\rLS#H��Q�mNN=L�5I����IatS0v�%R4\$)�����K�E\n4g)\$.L��(!�\"��A9fx���	��4���l��5JS�8R��5aUgN����oPn&�R��jjU��6�So#U\n��Xb�X�����W3EU(8o���l��:�KJ��:�/>+�����.���/V�W2!E]��W��0��V��f�����\$�W��*u�S�2nCO�RU��hfL���Ro�S�P��5��]�#av(��Umh���0JA�)D5TӕZ�6قR��y_KP%U ���GS�Rm��4\"���c	�S�g��g� V�5ӀB�Lw%W�Z�H7��n��x�Q	��P�h����5j�Ciӧ_�7q�h3�h�UN4�ޖ��g�*�mѲ��b���tt7�+<�����2r@�s���kot� �t�R���\r���L�cDn\n���Z��`�l��),!u0�|#�Rx�T�����W�\\��cIeA�miƟyu�avVQ0	\$�d�(-\0ȝԃ\"�X��\r�N��A�dWct%1�>�J@��M��N�'?d�Al�/V�O]	�l���rU&�BC2�Tx	��͆0^��<��\nl<�.8oC!/^Di��#�q�(�z�.�!R�v�2\$6iOJ��@V@Mo.%�^1#i΍��V�C!x55�YU��\"HT+1�\$�>����Ht�(?��B��_t�)��szG@O�\"�D�!V�ڲ8.��������X�����]U�48��x�\nŮ��\r��\"�Nx%|g_)��f�F!G�_�K�i����B��R\"��?Ҫ%�N��\n�E��#���\$߅��c'S8y�]A�b����a~";
            break;
        case"vi":
            $f = "Bp��&������ *�(J.��0Q,��Z���)v��@Tf�\n�pj�p�*�V���C`�]��rY<�#\$b\$L2��@%9���I�����Γ���4˅����d3\rF�q��t9N1�Q�E3ڡ�h�j[�J;���o��\n�(�Ub��da���I¾Ri��D�\0\0�A)�X�8@q:�g!�C�_#y�̸�6:����ڋ�.���K;�.���}F��ͼS0��6�������\\��v����N5��n5���x!��r7���CI��1\r�*�9��@2������2��9�#x�9���:�����d����@3��:�ܙ�n�d	�F\r����\r�	B()�2	\njh�-��C&I�N�%h\"4�'�H�2JV�����-ȆcG�I>����2���A��QtV�\0P����8�i@�!K�쪒Ep ��k��=cx�>R��:���.�#�G��2#��0�p�4��x�L�H9�����4C(��C@�:�t�㽌4M�?#8_�p�XVAxD��k�;c3�6�0���|�+��2�dRC�\"Eނh	J�-t��NR������V\r����;�1B��9\r��Ί�\"�<�A@��B\0�G��:��I�a��ڤ�2#!-�%t0��d�;#`�2�WK!�HJpT�cvT�'��s����c[�_�K�K.ޥ�S�er�EzP<:��P�]h	O����6�NHG�,� P\$����/x(����va�\n#��T�.�@�-��3�6X��\r�o)�\"`<]@P��acM �d�H!�b'4��\\J�i��©�މ�W;{_����PµE�X�MJ>�3��/NS{Z���r`�2\"i��vMI3r\"\\�;�@P�U|7��5�7�X��#�?.jD�	\$���B_\r;�G轺9F���h�A�R���4(�X82D���a%���\"p Ιh(n�)h\0`�6DȽ>�r�^QH�3I�]\n��K�j6&��.��,߲.\rho ��HڈQO�9+@ƅ��dQ��+�t�\n@\"��.4�1.\n�4��(��)���\$�/ä1�l����J�X�im.��t�Z�7��u���j��ʻW��`�5���LS>��f��ܳЊ\\KYls�A�	2��4�\\H9�yh���^�`z�ND��%�&��@Jd:. @��`l/(8h�C+!�	 �&���ff��7�����4@G�hUs�t�g(`ŚB�B �O��3��[\r ����n�B�H\nѩ��<%�A8F��۔�2���c���:;p��I�@!�h��.��\r�bd�9�R�#Ѐ�\0�f��d�),]6�9N����Dn���ivX����SȨ\"v���P�M\"����<��]P��|\nIpI\"A��@�Ŕ��\r�X��rjz]����%�8��l\rꖂ E]G�i]�d���HJ�`/\n<)�Hw�9���=�r��Y93#���P���'a�>�W�A����XQBI\nE`f�w�r?ae��'� �%����9����n��+�M���\$2�`5�#L�ז����\$����D����\$��}(�gV]	�9(�6�#�2d!&���L9�:�	:��S	�%�y	E���>A��-dl2Y�>wo#�	���ʐA��Kb,^1B�H����գ�o��b.F��52�� h3����BT��Սq��R���\$��A� (+�׃��Q�DP&S�<�{�q�`Q�,-��PK�\$��֞+Q{#'ݞ��h�O�ig!�=QӴC��`&̋�Kf�P�8(��vPﮃ�z�_\"������.�]�������\r�k%�Kld�	\$Z@�jdэ�PRUTMG�[	�3�	w��j(]�S&�+�4�T\n�!���T{�W4�I)E,��d��!ʋ�<�\"9wk�DC�*x !��&O���'v�\\�؎�y3&�O�d�p��ݤkfcK����x�5�Iv� ۏn�Rgh[q��]œ2T_�,��X���O�w\"��ܔ��4Zh���@.\\Q/7�Xr�\nP�~�5M\$�V7#��Ѕ�O)��n���O�\$\n\nr�XNYv)�l�m�=��ރޛ����eQ����/�Uڽ4˻�D\r#B��K��!S��C���1	�����H�:#���:b]>|B(5Q�<MJ1�	W!�\"RsKA�Nѵ�7�'�te�o4�C�J\n@?Q���H�l�-��QK@@�Dn���.�Df�쇦}�Eh_U䄏�9��i���N1\rc�9.��V�۸ic�<�1�I�|.�;�������؟/��[��n��|�C��I���UeV��u��������t�/\"� �h0�/&�o*{��%�,�M1.uj��l��D��9�~k*�k�b&i����伫�i�x������-�1�IT�g����&��:bd��t�J*�\\+0M2���GX��C��1+μ6`F	O������	mZ�L?\r���0��O�)lLz���R����<��������vY',���91	�q�'��(�\rʶa^K���r��P&H!��C�>�\$��#�_eh��_!YoP\n֑\r�\0�fQq{o�C�^���p�N�\$���n����/����ÏB/c�5qiP�o�:G���B��Q�*�1�� C5�� Q.�B����͈D��`��1,�.�ح�q��E��0�!Q�����������j��K�^�P5�5�̨\$&\n��:�^�..�dn=��ð��N����w&\"������ǚ^�bЙK�j����P�1���N��HG)��CJCd� ��\n���p�N����˖]�{�Nå�-'�^\r&p�]#aQ�E��6e�RQ�t�A/���#h]����	CrM�\\6d|N�T{Lq��\rmR5\rV�%�v�pC����K\r0N�hp��¢v��/4�\rB|\$�p%�b\rgK�`�JXj&�m+�װ�4���:{��>7�<�_#K6�k;	-{;)U;f�'m-�s���L�d@2�f,ַ�&���@tx*�\$	hr� �0���<�\nʲ0��.-��x��\$����1��4iR��\r�~:��5�Չ����L�3�lJ��.��h-J�4�uBC2";
            break;
        case"zh":
            $f = "�^��s�\\�r����|%��:�\$\nr.���2�r/d�Ȼ[8� S�8�r�!T�\\�s���I4�b�r��ЀJs!Kd�u�e�V���D�X,#!��j6� �:�t\nr���U:.Z�Pˑ.�\rVWd^%�䌵�r�T�Լ�*�s#U�`Qd�u'c(��oF����e3�Nb�`�p2N�S��ӣ:LY�ta~��&6ۊ��r�s���k��{��6�������c(��2��f�q�ЈP:S*@S�^�t*���ΔT���^\\�nNG#y�j\"5�>�4o��7@L��@�X�<5cp�4��j��9XS#%�Z��!J��1.[\$�h���rDa�_�g)[-9@��)6_��D�eۂ�%�yP������1[�\$j�W��9@@��Cr�D��L�r	*ݜ�as̓0 ��k�� ��2�x�m8���c�1�P��3�T�#�*;�-k@�4h�;�� X�H�9�0z\r��8a�^��h\\0�3�����x�7�-LC ^(�����������T(7�x�6�I\0D��YRs0�I\\����RN	&s�#lWġrt�4|_\\�Mֺ�E��]�VĽm���7;�8懌�0tIdlK����O-�1fT\$9��Q�E)DOb�x\"T#�6Oc��6�(iZ��r^�Yy7wBZH�9i!����B(e�s����G0�D�)�s�Fq�V�%�a�n����HF��6T �#d�cIσ�7;\"��r�(�v��I&r��[��7NU�u_V��1pW��TI��EF����\\,�0��1t���N�z��^:=�C����]E��\r0�jNaj� VP91�}PA�0�6Nm�%��X�7�H�<��\$:�t(͓�`�3���6:^`�3�.�A��,�7�XP9�>l\0�)�B0@���9F*ةI��+H��Q�#D��xc��&�\"���\n�H�5��j���8>s���Ba�:)�\"��`�'d\n�Y��)�U�����Fb�d+Ż��	�2��]�j�5pa]5.�O�x�P@\\��(Tj�S��V�C��V!���h���B(M\n���Chp3�[�Hd�  ��I��X�CZ�Oa��<��\r!�`��B��B.�@�d\$W�\$���)H�����P^:0P��Z|z\n'=6�����R��I��c� qV:��V�p.M�T�Qb��4��PJ\n�Қ�� \n (	�1�菒�����CI�hV28`���3�x�V\n�Ðt4f�<�<ü�}�D:qZ��`����`L�	5&��B!��+���d%X�%F>��1�\$���tK2��G��k��,�9���l�i�֫yt��t����СqHƅ!Ú!@'�0�5�Ɋ*ΜG�\"�@�/B\0B��=F#�����F�)a�2\r��w��@ A�\r�6�4�uD�`�5[{\r1�@>:]L��2���\nA-P�I��\\����\0U\n �@�c��D�0\"�b�+D��T���� ���0\nf^��u�:gT�Þ`(���t\"��d���7��%DEPp�0rÔlxO�L&1٭k���S��\$�#169�@�an�r��0�K �9�. ��rD�!�jo����jP�aBk�t��(�J	H[�2�wż\0%�(r��ʽ�iL�q��ˡ���Ӹ���b\\�xM�����*�]�='�����J�g��t\$ �]�C��KE�eB���q(��`���E&���>J�Y4�	�tA���b���2���Ft�:ω�x��0:������W	����*@��@ �g�4���ei����bZ'�)�Ǝ�	�x ���C�^(�ȧ���\n�+�( �b<K�b9D���S(D���\$E^���3P���4U�P9��Q���.��`���^Ԉ ?�݁�G��)D��۽��;���N�2�X�LI�(+�PŸ^M�Ģ��1!mw[bтPL�al9D�µ��\n����ѧ�W����z��F�2�^��b�'\\��qm���W2�a6- �e�][s�ȅqG����T]���.�+�g^]�u���I��^.��6��@�F��}w��Ç+郗�u>�κ)h�<����E��I���UkЦ��H4qN,:�,��F�ܾI_�^J�_�����ŗ1��t�3.gL?Xx_7/�����Sj�U�9i�ȶ#��.|�G�#8z���HFq�v!�T��ɔB���ƅ�	���~�\"��S�sc�G�/�����~��M���(P˕�)�*�2�X�s��o1-_!����Wb�G��~�����*�|��:�܏������y�>��P�W\0R.�c\0��\0at�f�o��-��s���l�o�r�.��aZ����o�^B�L��_N�F�1�bt�h0�|]a~��0X�f���prFo�a<��-�t�L��l���	l�I�V��ɐ�\0�<^P�\n̜���l���REp	0>�d�	�s\ro\np�1��Q�0�f�ik�t�A>��:���k�ߋ\n)�n-\n\"l\"������#���ԡ^�z��R�G�\r�Vg�`�A��Q&Te�Xy ޤ\0�y�\"6���@��Z�E�?\0�\n���p@�B��6�\$�0��H0P`#B8��#��%�	�Z\r1_Af��.!࣌���#�#��L��Fۯ��J�x1��\r��Xgx5#b���C���j!:Hj!.|o!\0.P\"���,�X.�Vz�z���˜�\0�h�O�\"�\n�4c\$2�p���`NLHxGo�D�/X����D\\#)�g�f���.y ����Z,�\\�2��,��E\$<�h\n���\r�0���,����r��&\$��a!��\"kzF��������\"����<�\r(o�M/` ��E@	\0�@�	�t\n`�";
            break;
        case"zh-tw":
            $f = "�^��%ӕ\\�r�����|%��u:H�B(\\�4��p�r��neRQ̡D8� S�\n�t*.t�I&�G�N��AʤS�V�:	t%9��Sy:\"<�r�ST�,#!��j6�1uL\0�����U:.��I9���B��K&]\nD�X�[��}-,�r����������&��a;D�x��r4��&�)��s3�S���t�\r�A��b���E�E1��ԣ�g:�x�]#0,'}üb1Q�\\y\0�V��E<���g��S� )ЪOLP\0��Δ�MƼ��� 2��F���6� @���7@,�	@�(��\rØ�7�-ҢK�Œ�J���K���>s\$�Ko	.���#�t��\0F���|��2�')tU��vs����^K��L��)pY��r��2�.���h�2]��*�X!rB����# ��?!\0�7����:�X8?���1�m<\$��׶����Kd�O��9�����4C(��C@�:�t��T3t����8^2��x�Iң���J@|6��Ӽ3?�k_	���^0����@�1&C�1�t�%y�RR! s-�a~Wġr�GALKE��sZ����\$��PM�\\\n��7;�8懌�\0�<�@��V�BO��ܘ�gANQ��9Tr�d���1@P�O#��<��:����i�i^���M��) D)d�8�-�!v]��!����sĔ|G��ʠCAF>s��'�8*A���J���L��8�0���3��0��آ&��{�<�1I��q��7�u�\\�1��^��[�]ۥY�Ŝ���ˇ1<[rJYX���iW�g/Nr���[���0�GR�.��U�h�`VN92L�N��0�6N\r��0u������p����@PC6L\r�x��t��9zc�0����x�:�8_���BhB�)�H�ա~Xt�qtSH�F��Q,+����E	���ё\n�\\���������6���BA�8��,�T��`�'4\n�Y��)�%�(�K<G\"\"# �����q\n)L�MQF�+p����4����ʛ�\n�Q*EL�PwU��7�\\�U��l*�	!Ex��Hm�6�@���5�G�ST4\0}I�8����>B�\0_�U�\"@�c�J�c��A�O��I�����������\nC4TO/]@�w�����>�4��B�C}�/G��ʃ��#BLZ�\n9�@�H��!Ѕ�8\\��s�h�H\nJf�Y�4f� ��\"Фmp�BHs>hM��> �j\r�vOҜ7�y�2����H��[;�Y��X@��\\L	��B�WVj. )D&��@�Y��^��E\n�dBHf��@��L�xQa�C3P�C�,i�3��	#bq6J�a��/�\n�4��(���l��&�\rq�f	.�\0!i]-�@���ZA�E(�&Lʷ3�k�P�4�0���\0f� ��(`������='�`j2y?&d���H%� �äF�R�(	BF�<'\0� A\n�Z@@(L��3�h9Dx����s�%��3.���!<u��;D���.]�\"�]�B.���&�q��&��<G�Ȼ�aw�{�r��.a2%G8�J E��Ċݠ��*��q 0�-� s��\\�aH��8g̙�BiW�R�������\"Y�0b%5�kAh��<]�L4��[<#��e���O9�xO�<�J*Lw���)��ܥ��Һ�k�*ÜZ\n!�/��\\n&d�{,��B@����&\\`�����ٛ7gO0�\$r����\\�j͙�s&D�%�\0�[ǘ^!כ���Oj���������ؤ���,{Oy���*@��@ �'�4�U�f�̳��E�'�(�\"���>:]9�<���@�y-��ǎmQ��O	q��p%�(�#�O���\\��{b��:�včA�b����R9v���\r��Q\0\"����{gp��JoO�;�2�NH�!y���H����NLɱ'4���\$\$�ےŶ�#���Ė*7���Z�^n.2ְ̅���r�q�+E-rYl��C�EP�&7�@�\\� 5��k[s`�@��A�mU��Ȯ�\\�c�˶�0��E{������꣝}R�#�xM<�*�V4Y����=��vx��\\?l�P4H�z��x�ZB9k�![�1h��8H ~xYȍ�=��!�O]����x[���ʽ�������ES���Qt)�i-��F;}�ر�W�pz7�mw���v��]޿�;W���G@����/�B雷bC�6V��޾��f,՜�˸.o���׹�w�2��:ds6Z'�#d	#���B=���J�r\\-�\\C�@�]������\\���t��,.c��ˢɮ�8b<���*ڽ���}\0m���γ�\n���,�1G8��.FO2\\�]E�/�r��H]`E�ĽPl�d���pbI���hAl��[�,�	P��/�0��êM�g-��r!:!���\0b�\$'�|�8#~����\r\r	�*!!\r�	�T�澆��Zu�hfТ��	\n����t�E�'¥�Zf \$P\0�P��l�-I��p�,�q,����19��\n[l�I���:΄JD�S\nq&�M >Ь�2\\�f�A�s�v� �QIB�/M)a��Fu�D��i\0@\n����N*��D�NN�!���az\"�p�\\-Ȉ\r�\"�k�P`� �e����1�٩�|`�`�y\0�\rd\n>�e&V6'�\r�lǦQ�t�\r��fXC�\n���Z�*l;�,�'V���O:~�8�Ŧ��J	�\r2Z�.���|9�#��`�Aj�\n#\"	�ޯE�x�\\6��(r:<�\"��K�)��,\0.g\"���pM��~A�����%���	�PV�A&(E����4A#P2�3\"j�\r�����t�G4br���Ѐ]����gƀ%���oZ�����<�N���,�N\"�zf��O����)�\0 fuA,�<˅��%��|\"�+�:u��,���\\�k�7k���f�Q\0a���HD�P@�	\0t	��@�\n`";
            break;
    }
    $Ng = [];
    foreach (explode("\n", lzw_decompress($f)) as $X) {
        $Ng[] = (strpos($X, "\t") ? explode("\t", $X) : $X);
    }
    return $Ng;
}

if (!$Ng) {
    $Ng = get_translations($a);
    $_SESSION["translations"] = $Ng;
}
if (extension_loaded('pdo')) {
    class
    Min_PDO extends PDO
    {
        var $_result, $server_info, $affected_rows, $errno, $error;

        function __construct()
        {
            global $c;
            $Te = array_search("SQL", $c->operators);
            if ($Te !== false) {
                unset($c->operators[$Te]);
            }
        }

        function dsn($Mb, $V, $Ne, $re = [])
        {
            try {
                parent::__construct($Mb, $V, $Ne, $re);
            } catch (Exception$ec) {
                auth_error(h($ec->getMessage()));
            }
            $this->setAttribute(13, ['Min_PDOStatement']);
            $this->server_info = @$this->getAttribute(4);
        }

        function query($I, $Ug = false)
        {
            $J = parent::query($I);
            $this->error = "";
            if (!$J) {
                list(, $this->errno, $this->error) = $this->errorInfo();
                if (!$this->error) {
                    $this->error = lang(21);
                }
                return false;
            }
            $this->store_result($J);
            return $J;
        }

        function multi_query($I)
        {
            return $this->_result = $this->query($I);
        }

        function store_result($J = null)
        {
            if (!$J) {
                $J = $this->_result;
                if (!$J) {
                    return false;
                }
            }
            if ($J->columnCount()) {
                $J->num_rows = $J->rowCount();
                return $J;
            }
            $this->affected_rows = $J->rowCount();
            return true;
        }

        function next_result()
        {
            if (!$this->_result) {
                return false;
            }
            $this->_result->_offset = 0;
            return @$this->_result->nextRowset();
        }

        function result($I, $n = 0)
        {
            $J = $this->query($I);
            if (!$J) {
                return false;
            }
            $L = $J->fetch();
            return $L[$n];
        }
    }

    class
    Min_PDOStatement extends PDOStatement
    {
        var $_offset = 0, $num_rows;

        function fetch_assoc()
        {
            return $this->fetch(2);
        }

        function fetch_row()
        {
            return $this->fetch(3);
        }

        function fetch_field()
        {
            $L = (object) $this->getColumnMeta($this->_offset++);
            $L->orgtable = $L->table;
            $L->orgname = $L->name;
            $L->charsetnr = (in_array("blob", (array) $L->flags) ? 63 : 0);
            return $L;
        }
    }
}
$Ib = [];

class
Min_SQL
{
    var $_conn;

    function __construct($g)
    {
        $this->_conn = $g;
    }

    function select($Q, $N, $Z, $t, $te = [], $A = 1, $G = 0, $af = false)
    {
        global $c, $z;
        $fd = (count($t) < count($N));
        $I = $c->selectQueryBuild($N, $Z, $t, $te, $A, $G);
        if (!$I) {
            $I = "SELECT" . limit(($_GET["page"] != "last" && $A != "" && $t && $fd && $z == "sql" ? "SQL_CALC_FOUND_ROWS " : "") . implode(", ", $N) . "\nFROM " . table($Q), ($Z ? "\nWHERE " . implode(" AND ", $Z) : "") . ($t && $fd ? "\nGROUP BY " . implode(", ", $t) : "") . ($te ? "\nORDER BY " . implode(", ", $te) : ""), ($A != "" ? +$A : null), ($G ? $A * $G : 0), "\n");
        }
        $ag = microtime(true);
        $K = $this->_conn->query($I);
        if ($af) {
            echo $c->selectQuery($I, $ag, !$K);
        }
        return $K;
    }

    function delete($Q, $if, $A = 0)
    {
        $I = "FROM " . table($Q);
        return queries("DELETE" . ($A ? limit1($Q, $I, $if) : " $I$if"));
    }

    function update($Q, $P, $if, $A = 0, $Lf = "\n")
    {
        $jh = [];
        foreach ($P as $_ => $X) {
            $jh[] = "$_ = $X";
        }
        $I = table($Q) . " SET$Lf" . implode(",$Lf", $jh);
        return queries("UPDATE" . ($A ? limit1($Q, $I, $if, $Lf) : " $I$if"));
    }

    function insert($Q, $P)
    {
        return queries("INSERT INTO " . table($Q) . ($P ? " (" . implode(", ", array_keys($P)) . ")\nVALUES (" . implode(", ", $P) . ")" : " DEFAULT VALUES"));
    }

    function insertUpdate($Q, $M, $Ze)
    {
        return false;
    }

    function begin()
    {
        return queries("BEGIN");
    }

    function commit()
    {
        return queries("COMMIT");
    }

    function rollback()
    {
        return queries("ROLLBACK");
    }

    function slowQuery($I, $Ag)
    {
    }

    function convertSearch($w, $X, $n)
    {
        return $w;
    }

    function value($X, $n)
    {
        return (method_exists($this->_conn, 'value') ? $this->_conn->value($X, $n) : (is_resource($X) ? stream_get_contents($X) : $X));
    }

    function quoteBinary($Cf)
    {
        return q($Cf);
    }

    function warnings()
    {
        return '';
    }

    function tableHelp($F)
    {
    }
}

$Ib = ["server" => "MySQL"] + $Ib;
if (!defined("DRIVER")) {
    $We = [
        "MySQLi",
        "MySQL",
        "PDO_MySQL",
    ];
    define("DRIVER", "server");
    if (extension_loaded("mysqli")) {
        class
        Min_DB extends MySQLi
        {
            var $extension = "MySQLi";

            function __construct()
            {
                parent::init();
            }

            function connect($O = "", $V = "", $Ne = "", $tb = null, $Se = null, $Tf = null)
            {
                global $c;
                mysqli_report(MYSQLI_REPORT_OFF);
                list($Rc, $Se) = explode(":", $O, 2);
                $Zf = $c->connectSsl();
                if ($Zf) {
                    $this->ssl_set($Zf['key'], $Zf['cert'], $Zf['ca'], '', '');
                }
                $K = @$this->real_connect(($O != "" ? $Rc : ini_get("mysqli.default_host")), ($O . $V != "" ? $V : ini_get("mysqli.default_user")), ($O . $V . $Ne != "" ? $Ne : ini_get("mysqli.default_pw")), $tb, (is_numeric($Se) ? $Se : ini_get("mysqli.default_port")), (!is_numeric($Se) ? $Se : $Tf), ($Zf ? 64 : 0));
                $this->options(MYSQLI_OPT_LOCAL_INFILE, false);
                return $K;
            }

            function set_charset($La)
            {
                if (parent::set_charset($La)) {
                    return true;
                }
                parent::set_charset('utf8');
                return $this->query("SET NAMES $La");
            }

            function result($I, $n = 0)
            {
                $J = $this->query($I);
                if (!$J) {
                    return false;
                }
                $L = $J->fetch_array();
                return $L[$n];
            }

            function quote($eg)
            {
                return "'" . $this->escape_string($eg) . "'";
            }
        }
    } elseif (extension_loaded("mysql") && !((ini_bool("sql.safe_mode") || ini_bool("mysql.allow_local_infile")) && extension_loaded("pdo_mysql"))) {
        class
        Min_DB
        {
            var $extension = "MySQL", $server_info, $affected_rows, $errno, $error, $_link, $_result;

            function connect($O, $V, $Ne)
            {
                if (ini_bool("mysql.allow_local_infile")) {
                    $this->error = lang(22, "'mysql.allow_local_infile'", "MySQLi", "PDO_MySQL");
                    return false;
                }
                $this->_link = @mysql_connect(($O != "" ? $O : ini_get("mysql.default_host")), ("$O$V" != "" ? $V : ini_get("mysql.default_user")), ("$O$V$Ne" != "" ? $Ne : ini_get("mysql.default_password")), true, 131072);
                if ($this->_link) {
                    $this->server_info = mysql_get_server_info($this->_link);
                } else {
                    $this->error = mysql_error();
                }
                return (bool) $this->_link;
            }

            function set_charset($La)
            {
                if (function_exists('mysql_set_charset')) {
                    if (mysql_set_charset($La, $this->_link)) {
                        return true;
                    }
                    mysql_set_charset('utf8', $this->_link);
                }
                return $this->query("SET NAMES $La");
            }

            function quote($eg)
            {
                return "'" . mysql_real_escape_string($eg, $this->_link) . "'";
            }

            function select_db($tb)
            {
                return mysql_select_db($tb, $this->_link);
            }

            function query($I, $Ug = false)
            {
                $J = @($Ug ? mysql_unbuffered_query($I, $this->_link) : mysql_query($I, $this->_link));
                $this->error = "";
                if (!$J) {
                    $this->errno = mysql_errno($this->_link);
                    $this->error = mysql_error($this->_link);
                    return false;
                }
                if ($J === true) {
                    $this->affected_rows = mysql_affected_rows($this->_link);
                    $this->info = mysql_info($this->_link);
                    return true;
                }
                return new
                Min_Result($J);
            }

            function multi_query($I)
            {
                return $this->_result = $this->query($I);
            }

            function store_result()
            {
                return $this->_result;
            }

            function next_result()
            {
                return false;
            }

            function result($I, $n = 0)
            {
                $J = $this->query($I);
                if (!$J || !$J->num_rows) {
                    return false;
                }
                return mysql_result($J->_result, 0, $n);
            }
        }

        class
        Min_Result
        {
            var $num_rows, $_result, $_offset = 0;

            function __construct($J)
            {
                $this->_result = $J;
                $this->num_rows = mysql_num_rows($J);
            }

            function fetch_assoc()
            {
                return mysql_fetch_assoc($this->_result);
            }

            function fetch_row()
            {
                return mysql_fetch_row($this->_result);
            }

            function fetch_field()
            {
                $K = mysql_fetch_field($this->_result, $this->_offset++);
                $K->orgtable = $K->table;
                $K->orgname = $K->name;
                $K->charsetnr = ($K->blob ? 63 : 0);
                return $K;
            }

            function __destruct()
            {
                mysql_free_result($this->_result);
            }
        }
    } elseif (extension_loaded("pdo_mysql")) {
        class
        Min_DB extends Min_PDO
        {
            var $extension = "PDO_MySQL";

            function connect($O, $V, $Ne)
            {
                global $c;
                $re = [PDO::MYSQL_ATTR_LOCAL_INFILE => false];
                $Zf = $c->connectSsl();
                if ($Zf) {
                    $re += [
                        PDO::MYSQL_ATTR_SSL_KEY  => $Zf['key'],
                        PDO::MYSQL_ATTR_SSL_CERT => $Zf['cert'],
                        PDO::MYSQL_ATTR_SSL_CA   => $Zf['ca'],
                    ];
                }
                $this->dsn("mysql:charset=utf8;host=" . str_replace(":", ";unix_socket=", preg_replace('~:(\d)~', ';port=\1', $O)), $V, $Ne, $re);
                return true;
            }

            function set_charset($La)
            {
                $this->query("SET NAMES $La");
            }

            function select_db($tb)
            {
                return $this->query("USE " . idf_escape($tb));
            }

            function query($I, $Ug = false)
            {
                $this->setAttribute(1000, !$Ug);
                return parent::query($I, $Ug);
            }
        }
    }

    class
    Min_Driver extends Min_SQL
    {
        function insert($Q, $P)
        {
            return ($P ? parent::insert($Q, $P) : queries("INSERT INTO " . table($Q) . " ()\nVALUES ()"));
        }

        function insertUpdate($Q, $M, $Ze)
        {
            $e = array_keys(reset($M));
            $Xe = "INSERT INTO " . table($Q) . " (" . implode(", ", $e) . ") VALUES\n";
            $jh = [];
            foreach ($e as $_) {
                $jh[$_] = "$_ = VALUES($_)";
            }
            $ig = "\nON DUPLICATE KEY UPDATE " . implode(", ", $jh);
            $jh = [];
            $xd = 0;
            foreach ($M as $P) {
                $Y = "(" . implode(", ", $P) . ")";
                if ($jh && (strlen($Xe) + $xd + strlen($Y) + strlen($ig) > 1e6)) {
                    if (!queries($Xe . implode(",\n", $jh) . $ig)) {
                        return false;
                    }
                    $jh = [];
                    $xd = 0;
                }
                $jh[] = $Y;
                $xd += strlen($Y) + 2;
            }
            return queries($Xe . implode(",\n", $jh) . $ig);
        }

        function slowQuery($I, $Ag)
        {
            if (min_version('5.7.8', '10.1.2')) {
                if (preg_match('~MariaDB~', $this->_conn->server_info)) {
                    return "SET STATEMENT max_statement_time=$Ag FOR $I";
                } elseif (preg_match('~^(SELECT\b)(.+)~is', $I, $D)) {
                    return "$D[1] /*+ MAX_EXECUTION_TIME(" . ($Ag * 1000) . ") */ $D[2]";
                }
            }
        }

        function convertSearch($w, $X, $n)
        {
            return (preg_match('~char|text|enum|set~', $n["type"]) && !preg_match("~^utf8~", $n["collation"]) && preg_match('~[\x80-\xFF]~', $X['val']) ? "CONVERT($w USING " . charset($this->_conn) . ")" : $w);
        }

        function warnings()
        {
            $J = $this->_conn->query("SHOW WARNINGS");
            if ($J && $J->num_rows) {
                ob_start();
                select($J);
                return ob_get_clean();
            }
        }

        function tableHelp($F)
        {
            $Cd = preg_match('~MariaDB~', $this->_conn->server_info);
            if (information_schema(DB)) {
                return strtolower(($Cd ? "information-schema-$F-table/" : str_replace("_", "-", $F) . "-table.html"));
            }
            if (DB == "mysql") {
                return ($Cd ? "mysql$F-table/" : "system-database.html");
            }
        }
    }

    function idf_escape($w)
    {
        return "`" . str_replace("`", "``", $w) . "`";
    }

    function table($w)
    {
        return idf_escape($w);
    }

    function connect()
    {
        global $c, $Tg, $fg;
        $g = new
        Min_DB;
        $mb = $c->credentials();
        if ($g->connect($mb[0], $mb[1], $mb[2])) {
            $g->set_charset(charset($g));
            $g->query("SET sql_quote_show_create = 1, autocommit = 1");
            if (min_version('5.7.8', 10.2, $g)) {
                $fg[lang(23)][] = "json";
                $Tg["json"] = 4294967295;
            }
            return $g;
        }
        $K = $g->error;
        if (function_exists('iconv') && !is_utf8($K) && strlen($Cf = iconv("windows-1250", "utf-8", $K)) > strlen($K)) {
            $K = $Cf;
        }
        return $K;
    }

    function get_databases($vc)
    {
        $K = get_session("dbs");
        if ($K === null) {
            $I = (min_version(5) ? "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME" : "SHOW DATABASES");
            $K = ($vc ? slow_query($I) : get_vals($I));
            restart_session();
            set_session("dbs", $K);
            stop_session();
        }
        return $K;
    }

    function limit($I, $Z, $A, $ee = 0, $Lf = " ")
    {
        return " $I$Z" . ($A !== null ? $Lf . "LIMIT $A" . ($ee ? " OFFSET $ee" : "") : "");
    }

    function limit1($Q, $I, $Z, $Lf = "\n")
    {
        return limit($I, $Z, 1, 0, $Lf);
    }

    function db_collation($k, $Xa)
    {
        global $g;
        $K = null;
        $i = $g->result("SHOW CREATE DATABASE " . idf_escape($k), 1);
        if (preg_match('~ COLLATE ([^ ]+)~', $i, $D)) {
            $K = $D[1];
        } elseif (preg_match('~ CHARACTER SET ([^ ]+)~', $i, $D)) {
            $K = $Xa[$D[1]][-1];
        }
        return $K;
    }

    function engines()
    {
        $K = [];
        foreach (get_rows("SHOW ENGINES") as $L) {
            if (preg_match("~YES|DEFAULT~", $L["Support"])) {
                $K[] = $L["Engine"];
            }
        }
        return $K;
    }

    function logged_user()
    {
        global $g;
        return $g->result("SELECT USER()");
    }

    function tables_list()
    {
        return get_key_vals(min_version(5) ? "SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME" : "SHOW TABLES");
    }

    function count_tables($j)
    {
        $K = [];
        foreach ($j as $k) {
            $K[$k] = count(get_vals("SHOW TABLES IN " . idf_escape($k)));
        }
        return $K;
    }

    function table_status($F = "", $oc = false)
    {
        $K = [];
        foreach (get_rows($oc && min_version(5) ? "SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() " . ($F != "" ? "AND TABLE_NAME = " . q($F) : "ORDER BY Name") : "SHOW TABLE STATUS" . ($F != "" ? " LIKE " . q(addcslashes($F, "%_\\")) : "")) as $L) {
            if ($L["Engine"] == "InnoDB") {
                $L["Comment"] = preg_replace('~(?:(.+); )?InnoDB free: .*~', '\1', $L["Comment"]);
            }
            if (!isset($L["Engine"])) {
                $L["Comment"] = "";
            }
            if ($F != "") {
                return $L;
            }
            $K[$L["Name"]] = $L;
        }
        return $K;
    }

    function is_view($R)
    {
        return $R["Engine"] === null;
    }

    function fk_support($R)
    {
        return preg_match('~InnoDB|IBMDB2I~i', $R["Engine"]) || (preg_match('~NDB~i', $R["Engine"]) && min_version(5.6));
    }

    function fields($Q)
    {
        $K = [];
        foreach (get_rows("SHOW FULL COLUMNS FROM " . table($Q)) as $L) {
            preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~', $L["Type"], $D);
            $K[$L["Field"]] = [
                "field"          => $L["Field"],
                "full_type"      => $L["Type"],
                "type"           => $D[1],
                "length"         => $D[2],
                "unsigned"       => ltrim($D[3] . $D[4]),
                "default"        => ($L["Default"] != "" || preg_match("~char|set~", $D[1]) ? $L["Default"] : null),
                "null"           => ($L["Null"] == "YES"),
                "auto_increment" => ($L["Extra"] == "auto_increment"),
                "on_update"      => (preg_match('~^on update (.+)~i', $L["Extra"], $D) ? $D[1] : ""),
                "collation"      => $L["Collation"],
                "privileges"     => array_flip(preg_split('~, *~', $L["Privileges"])),
                "comment"        => $L["Comment"],
                "primary"        => ($L["Key"] == "PRI"),
            ];
        }
        return $K;
    }

    function indexes($Q, $h = null)
    {
        $K = [];
        foreach (get_rows("SHOW INDEX FROM " . table($Q), $h) as $L) {
            $F = $L["Key_name"];
            $K[$F]["type"] = ($F == "PRIMARY" ? "PRIMARY" : ($L["Index_type"] == "FULLTEXT" ? "FULLTEXT" : ($L["Non_unique"] ? ($L["Index_type"] == "SPATIAL" ? "SPATIAL" : "INDEX") : "UNIQUE")));
            $K[$F]["columns"][] = $L["Column_name"];
            $K[$F]["lengths"][] = ($L["Index_type"] == "SPATIAL" ? null : $L["Sub_part"]);
            $K[$F]["descs"][] = null;
        }
        return $K;
    }

    function foreign_keys($Q)
    {
        global $g, $le;
        static $Pe = '`(?:[^`]|``)+`';
        $K = [];
        $kb = $g->result("SHOW CREATE TABLE " . table($Q), 1);
        if ($kb) {
            preg_match_all("~CONSTRAINT ($Pe) FOREIGN KEY ?\\(((?:$Pe,? ?)+)\\) REFERENCES ($Pe)(?:\\.($Pe))? \\(((?:$Pe,? ?)+)\\)(?: ON DELETE ($le))?(?: ON UPDATE ($le))?~", $kb, $Ed, PREG_SET_ORDER);
            foreach ($Ed as $D) {
                preg_match_all("~$Pe~", $D[2], $Uf);
                preg_match_all("~$Pe~", $D[5], $ug);
                $K[idf_unescape($D[1])] = [
                    "db"        => idf_unescape($D[4] != "" ? $D[3] : $D[4]),
                    "table"     => idf_unescape($D[4] != "" ? $D[4] : $D[3]),
                    "source"    => array_map('idf_unescape', $Uf[0]),
                    "target"    => array_map('idf_unescape', $ug[0]),
                    "on_delete" => ($D[6] ? $D[6] : "RESTRICT"),
                    "on_update" => ($D[7] ? $D[7] : "RESTRICT"),
                ];
            }
        }
        return $K;
    }

    function view($F)
    {
        global $g;
        return ["select" => preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU', '', $g->result("SHOW CREATE VIEW " . table($F), 1))];
    }

    function collations()
    {
        $K = [];
        foreach (get_rows("SHOW COLLATION") as $L) {
            if ($L["Default"]) {
                $K[$L["Charset"]][-1] = $L["Collation"];
            } else {
                $K[$L["Charset"]][] = $L["Collation"];
            }
        }
        ksort($K);
        foreach ($K as $_ => $X) {
            asort($K[$_]);
        }
        return $K;
    }

    function information_schema($k)
    {
        return (min_version(5) && $k == "information_schema") || (min_version(5.5) && $k == "performance_schema");
    }

    function error()
    {
        global $g;
        return h(preg_replace('~^You have an error.*syntax to use~U', "Syntax error", $g->error));
    }

    function create_database($k, $Wa)
    {
        return queries("CREATE DATABASE " . idf_escape($k) . ($Wa ? " COLLATE " . q($Wa) : ""));
    }

    function drop_databases($j)
    {
        $K = apply_queries("DROP DATABASE", $j, 'idf_escape');
        restart_session();
        set_session("dbs", null);
        return $K;
    }

    function rename_database($F, $Wa)
    {
        $K = false;
        if (create_database($F, $Wa)) {
            $tf = [];
            foreach (tables_list() as $Q => $U) {
                $tf[] = table($Q) . " TO " . idf_escape($F) . "." . table($Q);
            }
            $K = (!$tf || queries("RENAME TABLE " . implode(", ", $tf)));
            if ($K) {
                queries("DROP DATABASE " . idf_escape(DB));
            }
            restart_session();
            set_session("dbs", null);
        }
        return $K;
    }

    function auto_increment()
    {
        $za = " PRIMARY KEY";
        if ($_GET["create"] != "" && $_POST["auto_increment_col"]) {
            foreach (indexes($_GET["create"]) as $x) {
                if (in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"], $x["columns"], true)) {
                    $za = "";
                    break;
                }
                if ($x["type"] == "PRIMARY") {
                    $za = " UNIQUE";
                }
            }
        }
        return " AUTO_INCREMENT$za";
    }

    function alter_table($Q, $F, $o, $xc, $bb, $Xb, $Wa, $ya, $Ke)
    {
        $sa = [];
        foreach ($o as $n) {
            $sa[] = ($n[1] ? ($Q != "" ? ($n[0] != "" ? "CHANGE " . idf_escape($n[0]) : "ADD") : " ") . " " . implode($n[1]) . ($Q != "" ? $n[2] : "") : "DROP " . idf_escape($n[0]));
        }
        $sa = array_merge($sa, $xc);
        $bg = ($bb !== null ? " COMMENT=" . q($bb) : "") . ($Xb ? " ENGINE=" . q($Xb) : "") . ($Wa ? " COLLATE " . q($Wa) : "") . ($ya != "" ? " AUTO_INCREMENT=$ya" : "");
        if ($Q == "") {
            return queries("CREATE TABLE " . table($F) . " (\n" . implode(",\n", $sa) . "\n)$bg$Ke");
        }
        if ($Q != $F) {
            $sa[] = "RENAME TO " . table($F);
        }
        if ($bg) {
            $sa[] = ltrim($bg);
        }
        return ($sa || $Ke ? queries("ALTER TABLE " . table($Q) . "\n" . implode(",\n", $sa) . $Ke) : true);
    }

    function alter_indexes($Q, $sa)
    {
        foreach ($sa as $_ => $X) {
            $sa[$_] = ($X[2] == "DROP" ? "\nDROP INDEX " . idf_escape($X[1]) : "\nADD $X[0] " . ($X[0] == "PRIMARY" ? "KEY " : "") . ($X[1] != "" ? idf_escape($X[1]) . " " : "") . "(" . implode(", ", $X[2]) . ")");
        }
        return queries("ALTER TABLE " . table($Q) . implode(",", $sa));
    }

    function truncate_tables($S)
    {
        return apply_queries("TRUNCATE TABLE", $S);
    }

    function drop_views($oh)
    {
        return queries("DROP VIEW " . implode(", ", array_map('table', $oh)));
    }

    function drop_tables($S)
    {
        return queries("DROP TABLE " . implode(", ", array_map('table', $S)));
    }

    function move_tables($S, $oh, $ug)
    {
        $tf = [];
        foreach (array_merge($S, $oh) as $Q) {
            $tf[] = table($Q) . " TO " . idf_escape($ug) . "." . table($Q);
        }
        return queries("RENAME TABLE " . implode(", ", $tf));
    }

    function copy_tables($S, $oh, $ug)
    {
        queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
        foreach ($S as $Q) {
            $F = ($ug == DB ? table("copy_$Q") : idf_escape($ug) . "." . table($Q));
            if (!queries("\nDROP TABLE IF EXISTS $F") || !queries("CREATE TABLE $F LIKE " . table($Q)) || !queries("INSERT INTO $F SELECT * FROM " . table($Q))) {
                return false;
            }
            foreach (get_rows("SHOW TRIGGERS LIKE " . q(addcslashes($Q, "%_\\"))) as $L) {
                $Og = $L["Trigger"];
                if (!queries("CREATE TRIGGER " . ($ug == DB ? idf_escape("copy_$Og") : idf_escape($ug) . "." . idf_escape($Og)) . " $L[Timing] $L[Event] ON $F FOR EACH ROW\n$L[Statement];")) {
                    return false;
                }
            }
        }
        foreach ($oh as $Q) {
            $F = ($ug == DB ? table("copy_$Q") : idf_escape($ug) . "." . table($Q));
            $nh = view($Q);
            if (!queries("DROP VIEW IF EXISTS $F") || !queries("CREATE VIEW $F AS $nh[select]")) {
                return false;
            }
        }
        return true;
    }

    function trigger($F)
    {
        if ($F == "") {
            return [];
        }
        $M = get_rows("SHOW TRIGGERS WHERE `Trigger` = " . q($F));
        return reset($M);
    }

    function triggers($Q)
    {
        $K = [];
        foreach (get_rows("SHOW TRIGGERS LIKE " . q(addcslashes($Q, "%_\\"))) as $L) {
            $K[$L["Trigger"]] = [
                $L["Timing"],
                $L["Event"],
            ];
        }
        return $K;
    }

    function trigger_options()
    {
        return [
            "Timing" => [
                "BEFORE",
                "AFTER",
            ],
            "Event"  => [
                "INSERT",
                "UPDATE",
                "DELETE",
            ],
            "Type"   => ["FOR EACH ROW"],
        ];
    }

    function routine($F, $U)
    {
        global $g, $Zb, $Yc, $Tg;
        $qa = [
            "bool",
            "boolean",
            "integer",
            "double precision",
            "real",
            "dec",
            "numeric",
            "fixed",
            "national char",
            "national varchar",
        ];
        $Vf = "(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";
        $Sg = "((" . implode("|", array_merge(array_keys($Tg), $qa)) . ")\\b(?:\\s*\\(((?:[^'\")]|$Zb)++)\\))?\\s*(zerofill\\s*)?(unsigned(?:\\s+zerofill)?)?)(?:\\s*(?:CHARSET|CHARACTER\\s+SET)\\s*['\"]?([^'\"\\s,]+)['\"]?)?";
        $Pe = "$Vf*(" . ($U == "FUNCTION" ? "" : $Yc) . ")?\\s*(?:`((?:[^`]|``)*)`\\s*|\\b(\\S+)\\s+)$Sg";
        $i = $g->result("SHOW CREATE $U " . idf_escape($F), 2);
        preg_match("~\\(((?:$Pe\\s*,?)*)\\)\\s*" . ($U == "FUNCTION" ? "RETURNS\\s+$Sg\\s+" : "") . "(.*)~is", $i, $D);
        $o = [];
        preg_match_all("~$Pe\\s*,?~is", $D[1], $Ed, PREG_SET_ORDER);
        foreach ($Ed as $Fe) {
            $F = str_replace("``", "`", $Fe[2]) . $Fe[3];
            $o[] = [
                "field"     => $F,
                "type"      => strtolower($Fe[5]),
                "length"    => preg_replace_callback("~$Zb~s", 'normalize_enum', $Fe[6]),
                "unsigned"  => strtolower(preg_replace('~\s+~', ' ', trim("$Fe[8] $Fe[7]"))),
                "null"      => 1,
                "full_type" => $Fe[4],
                "inout"     => strtoupper($Fe[1]),
                "collation" => strtolower($Fe[9]),
            ];
        }
        if ($U != "FUNCTION") {
            return [
                "fields"     => $o,
                "definition" => $D[11],
            ];
        }
        return [
            "fields"     => $o,
            "returns"    => [
                "type"      => $D[12],
                "length"    => $D[13],
                "unsigned"  => $D[15],
                "collation" => $D[16],
            ],
            "definition" => $D[17],
            "language"   => "SQL",
        ];
    }

    function routines()
    {
        return get_rows("SELECT ROUTINE_NAME AS SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = " . q(DB));
    }

    function routine_languages()
    {
        return [];
    }

    function routine_id($F, $L)
    {
        return idf_escape($F);
    }

    function last_id()
    {
        global $g;
        return $g->result("SELECT LAST_INSERT_ID()");
    }

    function explain($g, $I)
    {
        return $g->query("EXPLAIN " . (min_version(5.1) ? "PARTITIONS " : "") . $I);
    }

    function found_rows($R, $Z)
    {
        return ($Z || $R["Engine"] != "InnoDB" ? null : $R["Rows"]);
    }

    function types()
    {
        return [];
    }

    function schemas()
    {
        return [];
    }

    function get_schema()
    {
        return "";
    }

    function set_schema($Ef)
    {
        return true;
    }

    function create_sql($Q, $ya, $gg)
    {
        global $g;
        $K = $g->result("SHOW CREATE TABLE " . table($Q), 1);
        if (!$ya) {
            $K = preg_replace('~ AUTO_INCREMENT=\d+~', '', $K);
        }
        return $K;
    }

    function truncate_sql($Q)
    {
        return "TRUNCATE " . table($Q);
    }

    function use_sql($tb)
    {
        return "USE " . idf_escape($tb);
    }

    function trigger_sql($Q)
    {
        $K = "";
        foreach (get_rows("SHOW TRIGGERS LIKE " . q(addcslashes($Q, "%_\\")), null, "-- ") as $L) {
            $K .= "\nCREATE TRIGGER " . idf_escape($L["Trigger"]) . " $L[Timing] $L[Event] ON " . table($L["Table"]) . " FOR EACH ROW\n$L[Statement];;\n";
        }
        return $K;
    }

    function show_variables()
    {
        return get_key_vals("SHOW VARIABLES");
    }

    function process_list()
    {
        return get_rows("SHOW FULL PROCESSLIST");
    }

    function show_status()
    {
        return get_key_vals("SHOW STATUS");
    }

    function convert_field($n)
    {
        if (preg_match("~binary~", $n["type"])) {
            return "HEX(" . idf_escape($n["field"]) . ")";
        }
        if ($n["type"] == "bit") {
            return "BIN(" . idf_escape($n["field"]) . " + 0)";
        }
        if (preg_match("~geometry|point|linestring|polygon~", $n["type"])) {
            return (min_version(8) ? "ST_" : "") . "AsWKT(" . idf_escape($n["field"]) . ")";
        }
    }

    function unconvert_field($n, $K)
    {
        if (preg_match("~binary~", $n["type"])) {
            $K = "UNHEX($K)";
        }
        if ($n["type"] == "bit") {
            $K = "CONV($K, 2, 10) + 0";
        }
        if (preg_match("~geometry|point|linestring|polygon~", $n["type"])) {
            $K = (min_version(8) ? "ST_" : "") . "GeomFromText($K)";
        }
        return $K;
    }

    function support($pc)
    {
        return !preg_match("~scheme|sequence|type|view_trigger|materializedview" . (min_version(5.1) ? "" : "|event|partitioning" . (min_version(5) ? "" : "|routine|trigger|view")) . "~", $pc);
    }

    function kill_process($X)
    {
        return queries("KILL " . number($X));
    }

    function connection_id()
    {
        return "SELECT CONNECTION_ID()";
    }

    function max_connections()
    {
        global $g;
        return $g->result("SELECT @@max_connections");
    }

    $z = "sql";
    $Tg = [];
    $fg = [];
    foreach ([
                 lang(24) => [
                     "tinyint"   => 3,
                     "smallint"  => 5,
                     "mediumint" => 8,
                     "int"       => 10,
                     "bigint"    => 20,
                     "decimal"   => 66,
                     "float"     => 12,
                     "double"    => 21,
                 ],
                 lang(25) => [
                     "date"      => 10,
                     "datetime"  => 19,
                     "timestamp" => 19,
                     "time"      => 10,
                     "year"      => 4,
                 ],
                 lang(23) => [
                     "char"       => 255,
                     "varchar"    => 65535,
                     "tinytext"   => 255,
                     "text"       => 65535,
                     "mediumtext" => 16777215,
                     "longtext"   => 4294967295,
                 ],
                 lang(26) => [
                     "enum" => 65535,
                     "set"  => 64,
                 ],
                 lang(27) => [
                     "bit"        => 20,
                     "binary"     => 255,
                     "varbinary"  => 65535,
                     "tinyblob"   => 255,
                     "blob"       => 65535,
                     "mediumblob" => 16777215,
                     "longblob"   => 4294967295,
                 ],
                 lang(28) => [
                     "geometry"           => 0,
                     "point"              => 0,
                     "linestring"         => 0,
                     "polygon"            => 0,
                     "multipoint"         => 0,
                     "multilinestring"    => 0,
                     "multipolygon"       => 0,
                     "geometrycollection" => 0,
                 ],
             ] as $_ => $X) {
        $Tg += $X;
        $fg[$_] = array_keys($X);
    }
    $ah = [
        "unsigned",
        "zerofill",
        "unsigned zerofill",
    ];
    $pe = [
        "=",
        "<",
        ">",
        "<=",
        ">=",
        "!=",
        "LIKE",
        "LIKE %%",
        "REGEXP",
        "IN",
        "FIND_IN_SET",
        "IS NULL",
        "NOT LIKE",
        "NOT REGEXP",
        "NOT IN",
        "IS NOT NULL",
        "SQL",
    ];
    $Cc = [
        "char_length",
        "date",
        "from_unixtime",
        "lower",
        "round",
        "floor",
        "ceil",
        "sec_to_time",
        "time_to_sec",
        "upper",
    ];
    $Hc = [
        "avg",
        "count",
        "count distinct",
        "group_concat",
        "max",
        "min",
        "sum",
    ];
    $Pb = [
        [
            "char"      => "md5/sha1/password/encrypt/uuid",
            "binary"    => "md5/sha1",
            "date|time" => "now",
        ],
        [
            number_type() => "+/-",
            "date"        => "+ interval/- interval",
            "time"        => "addtime/subtime",
            "char|text"   => "concat",
        ],
    ];
}
define("SERVER", $_GET[DRIVER]);
define("DB", $_GET["db"]);
define("ME", preg_replace('~^[^?]*/([^?]*).*~', '\1', $_SERVER["REQUEST_URI"]) . '?' . (sid() ? SID . '&' : '') . (SERVER !== null ? DRIVER . "=" . urlencode(SERVER) . '&' : '') . (isset($_GET["username"]) ? "username=" . urlencode($_GET["username"]) . '&' : '') . (DB != "" ? 'db=' . urlencode(DB) . '&' . (isset($_GET["ns"]) ? "ns=" . urlencode($_GET["ns"]) . "&" : "") : ''));
$fa = "4.6.3";

class
Adminer
{
    var $operators;

    function name()
    {
        return "<a href='https://www.adminer.org/'" . target_blank() . " id='h1'>Adminer</a>";
    }

    function credentials()
    {
        return [
            SERVER,
            $_GET["username"],
            get_password(),
        ];
    }

    function connectSsl()
    {
    }

    function permanentLogin($i = false)
    {
        return password_file($i);
    }

    function bruteForceKey()
    {
        return $_SERVER["REMOTE_ADDR"];
    }

    function serverName($O)
    {
        return h($O);
    }

    function database()
    {
        return DB;
    }

    function databases($vc = true)
    {
        return get_databases($vc);
    }

    function schemas()
    {
        return schemas();
    }

    function queryTimeout()
    {
        return 2;
    }

    function headers()
    {
    }

    function csp()
    {
        return csp();
    }

    function head()
    {
        return true;
    }

    function css()
    {
        $K = [];
        $sc = "adminer.css";
        if (file_exists($sc)) {
            $K[] = $sc;
        }
        return $K;
    }

    function loginForm()
    {
        global $Ib;
        echo "<table cellspacing='0'>\n", $this->loginFormField('driver', '<tr><th>' . lang(29) . '<td>', html_select("auth[driver]", $Ib, DRIVER) . "\n"), $this->loginFormField('server', '<tr><th>' . lang(30) . '<td>', '<input name="auth[server]" value="' . h(SERVER) . '" title="hostname[:port]" placeholder="localhost" autocapitalize="off">' . "\n"), $this->loginFormField('username', '<tr><th>' . lang(31) . '<td>', '<input name="auth[username]" id="username" value="' . h($_GET["username"]) . '" autocapitalize="off">' . script("focus(qs('#username'));")), $this->loginFormField('password', '<tr><th>' . lang(32) . '<td>', '<input type="password" name="auth[password]">' . "\n"), $this->loginFormField('db', '<tr><th>' . lang(33) . '<td>', '<input name="auth[db]" value="' . h($_GET["db"]) . '" autocapitalize="off">' . "\n"), "</table>\n", "<p><input type='submit' value='" . lang(34) . "'>\n", checkbox("auth[permanent]", 1, $_COOKIE["adminer_permanent"], lang(35)) . "\n";
    }

    function loginFormField($F, $Oc, $Y)
    {
        return $Oc . $Y;
    }

    function login($Ad, $Ne)
    {
        if ($Ne == "") {
            return lang(36, target_blank());
        }
        return true;
    }

    function tableName($mg)
    {
        return h($mg["Name"]);
    }

    function fieldName($n, $te = 0)
    {
        return '<span title="' . h($n["full_type"]) . '">' . h($n["field"]) . '</span>';
    }

    function selectLinks($mg, $P = "")
    {
        global $z, $l;
        echo '<p class="links">';
        $_d = ["select" => lang(37)];
        if (support("table") || support("indexes")) {
            $_d["table"] = lang(38);
        }
        if (support("table")) {
            if (is_view($mg)) {
                $_d["view"] = lang(39);
            } else {
                $_d["create"] = lang(40);
            }
        }
        if ($P !== null) {
            $_d["edit"] = lang(41);
        }
        $F = $mg["Name"];
        foreach ($_d as $_ => $X) {
            echo " <a href='" . h(ME) . "$_=" . urlencode($F) . ($_ == "edit" ? $P : "") . "'" . bold(isset($_GET[$_])) . ">$X</a>";
        }
        echo doc_link([$z => $l->tableHelp($F)], "?"), "\n";
    }

    function foreignKeys($Q)
    {
        return foreign_keys($Q);
    }

    function backwardKeys($Q, $lg)
    {
        return [];
    }

    function backwardKeysPrint($Aa, $L)
    {
    }

    function selectQuery($I, $ag, $nc = false)
    {
        global $z, $l;
        $K = "</p>\n";
        if (!$nc && ($rh = $l->warnings())) {
            $v = "warnings";
            $K = ", <a href='#$v'>" . lang(42) . "</a>" . script("qsl('a').onclick = partial(toggle, '$v');", "") . "$K<div id='$v' class='hidden'>\n$rh</div>\n";
        }
        return "<p><code class='jush-$z'>" . h(str_replace("\n", " ", $I)) . "</code> <span class='time'>(" . format_time($ag) . ")</span>" . (support("sql") ? " <a href='" . h(ME) . "sql=" . urlencode($I) . "'>" . lang(10) . "</a>" : "") . $K;
    }

    function sqlCommandQuery($I)
    {
        return shorten_utf8(trim($I), 1000);
    }

    function rowDescription($Q)
    {
        return "";
    }

    function rowDescriptions($M, $yc)
    {
        return $M;
    }

    function selectLink($X, $n)
    {
    }

    function selectVal($X, $B, $n, $Ae)
    {
        $K = ($X === null ? "<i>NULL</i>" : (preg_match("~char|binary|boolean~", $n["type"]) && !preg_match("~var~", $n["type"]) ? "<code>$X</code>" : $X));
        if (preg_match('~blob|bytea|raw|file~', $n["type"]) && !is_utf8($X)) {
            $K = "<i>" . lang(43, strlen($Ae)) . "</i>";
        }
        if (preg_match('~json~', $n["type"])) {
            $K = "<code class='jush-js'>$K</code>";
        }
        return ($B ? "<a href='" . h($B) . "'" . (is_url($B) ? target_blank() : "") . ">$K</a>" : $K);
    }

    function editVal($X, $n)
    {
        return $X;
    }

    function tableStructurePrint($o)
    {
        echo "<table cellspacing='0' class='nowrap'>\n", "<thead><tr><th>" . lang(44) . "<td>" . lang(45) . (support("comment") ? "<td>" . lang(46) : "") . "</thead>\n";
        foreach ($o as $n) {
            echo "<tr" . odd() . "><th>" . h($n["field"]), "<td><span title='" . h($n["collation"]) . "'>" . h($n["full_type"]) . "</span>", ($n["null"] ? " <i>NULL</i>" : ""), ($n["auto_increment"] ? " <i>" . lang(47) . "</i>" : ""), (isset($n["default"]) ? " <span title='" . lang(48) . "'>[<b>" . h($n["default"]) . "</b>]</span>" : ""), (support("comment") ? "<td>" . h($n["comment"]) : ""), "\n";
        }
        echo "</table>\n";
    }

    function tableIndexesPrint($y)
    {
        echo "<table cellspacing='0'>\n";
        foreach ($y as $F => $x) {
            ksort($x["columns"]);
            $af = [];
            foreach ($x["columns"] as $_ => $X) {
                $af[] = "<i>" . h($X) . "</i>" . ($x["lengths"][$_] ? "(" . $x["lengths"][$_] . ")" : "") . ($x["descs"][$_] ? " DESC" : "");
            }
            echo "<tr title='" . h($F) . "'><th>$x[type]<td>" . implode(", ", $af) . "\n";
        }
        echo "</table>\n";
    }

    function selectColumnsPrint($N, $e)
    {
        global $Cc, $Hc;
        print_fieldset("select", lang(49), $N);
        $u = 0;
        $N[""] = [];
        foreach ($N as $_ => $X) {
            $X = $_GET["columns"][$_];
            $d = select_input(" name='columns[$u][col]'", $e, $X["col"], ($_ !== "" ? "selectFieldChange" : "selectAddRow"));
            echo "<div>" . ($Cc || $Hc ? "<select name='columns[$u][fun]'>" . optionlist([-1 => ""] + array_filter([
                            lang(50) => $Cc,
                            lang(51) => $Hc,
                        ]), $X["fun"]) . "</select>" . on_help("getTarget(event).value && getTarget(event).value.replace(/ |\$/, '(') + ')'", 1) . script("qsl('select').onchange = function () { helpClose();" . ($_ !== "" ? "" : " qsl('select, input', this.parentNode).onchange();") . " };", "") . "($d)" : $d) . "</div>\n";
            $u++;
        }
        echo "</div></fieldset>\n";
    }

    function selectSearchPrint($Z, $e, $y)
    {
        print_fieldset("search", lang(52), $Z);
        foreach ($y as $u => $x) {
            if ($x["type"] == "FULLTEXT") {
                echo "<div>(<i>" . implode("</i>, <i>", array_map('h', $x["columns"])) . "</i>) AGAINST", " <input type='search' name='fulltext[$u]' value='" . h($_GET["fulltext"][$u]) . "'>", script("qsl('input').oninput = selectFieldChange;", ""), checkbox("boolean[$u]", 1, isset($_GET["boolean"][$u]), "BOOL"), "</div>\n";
            }
        }
        $Ka = "this.parentNode.firstChild.onchange();";
        foreach (array_merge((array) $_GET["where"], [[]]) as $u => $X) {
            if (!$X || ("$X[col]$X[val]" != "" && in_array($X["op"], $this->operators))) {
                echo "<div>" . select_input(" name='where[$u][col]'", $e, $X["col"], ($X ? "selectFieldChange" : "selectAddRow"), "(" . lang(53) . ")"), html_select("where[$u][op]", $this->operators, $X["op"], $Ka), "<input type='search' name='where[$u][val]' value='" . h($X["val"]) . "'>", script("mixin(qsl('input'), {oninput: function () { $Ka }, onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});", ""), "</div>\n";
            }
        }
        echo "</div></fieldset>\n";
    }

    function selectOrderPrint($te, $e, $y)
    {
        print_fieldset("sort", lang(54), $te);
        $u = 0;
        foreach ((array) $_GET["order"] as $_ => $X) {
            if ($X != "") {
                echo "<div>" . select_input(" name='order[$u]'", $e, $X, "selectFieldChange"), checkbox("desc[$u]", 1, isset($_GET["desc"][$_]), lang(55)) . "</div>\n";
                $u++;
            }
        }
        echo "<div>" . select_input(" name='order[$u]'", $e, "", "selectAddRow"), checkbox("desc[$u]", 1, false, lang(55)) . "</div>\n", "</div></fieldset>\n";
    }

    function selectLimitPrint($A)
    {
        echo "<fieldset><legend>" . lang(56) . "</legend><div>";
        echo "<input type='number' name='limit' class='size' value='" . h($A) . "'>", script("qsl('input').oninput = selectFieldChange;", ""), "</div></fieldset>\n";
    }

    function selectLengthPrint($zg)
    {
        if ($zg !== null) {
            echo "<fieldset><legend>" . lang(57) . "</legend><div>", "<input type='number' name='text_length' class='size' value='" . h($zg) . "'>", "</div></fieldset>\n";
        }
    }

    function selectActionPrint($y)
    {
        echo "<fieldset><legend>" . lang(58) . "</legend><div>", "<input type='submit' value='" . lang(49) . "'>", " <span id='noindex' title='" . lang(59) . "'></span>", "<script" . nonce() . ">\n", "var indexColumns = ";
        $e = [];
        foreach ($y as $x) {
            $qb = reset($x["columns"]);
            if ($x["type"] != "FULLTEXT" && $qb) {
                $e[$qb] = 1;
            }
        }
        $e[""] = 1;
        foreach ($e as $_ => $X) {
            json_row($_);
        }
        echo ";\n", "selectFieldChange.call(qs('#form')['select']);\n", "</script>\n", "</div></fieldset>\n";
    }

    function selectCommandPrint()
    {
        return !information_schema(DB);
    }

    function selectImportPrint()
    {
        return !information_schema(DB);
    }

    function selectEmailPrint($Ub, $e)
    {
    }

    function selectColumnsProcess($e, $y)
    {
        global $Cc, $Hc;
        $N = [];
        $t = [];
        foreach ((array) $_GET["columns"] as $_ => $X) {
            if ($X["fun"] == "count" || ($X["col"] != "" && (!$X["fun"] || in_array($X["fun"], $Cc) || in_array($X["fun"], $Hc)))) {
                $N[$_] = apply_sql_function($X["fun"], ($X["col"] != "" ? idf_escape($X["col"]) : "*"));
                if (!in_array($X["fun"], $Hc)) {
                    $t[] = $N[$_];
                }
            }
        }
        return [
            $N,
            $t,
        ];
    }

    function selectSearchProcess($o, $y)
    {
        global $g, $l;
        $K = [];
        foreach ($y as $u => $x) {
            if ($x["type"] == "FULLTEXT" && $_GET["fulltext"][$u] != "") {
                $K[] = "MATCH (" . implode(", ", array_map('idf_escape', $x["columns"])) . ") AGAINST (" . q($_GET["fulltext"][$u]) . (isset($_GET["boolean"][$u]) ? " IN BOOLEAN MODE" : "") . ")";
            }
        }
        foreach ((array) $_GET["where"] as $_ => $X) {
            if ("$X[col]$X[val]" != "" && in_array($X["op"], $this->operators)) {
                $Xe = "";
                $db = " $X[op]";
                if (preg_match('~IN$~', $X["op"])) {
                    $Vc = process_length($X["val"]);
                    $db .= " " . ($Vc != "" ? $Vc : "(NULL)");
                } elseif ($X["op"] == "SQL") {
                    $db = " $X[val]";
                } elseif ($X["op"] == "LIKE %%") {
                    $db = " LIKE " . $this->processInput($o[$X["col"]], "%$X[val]%");
                } elseif ($X["op"] == "ILIKE %%") {
                    $db = " ILIKE " . $this->processInput($o[$X["col"]], "%$X[val]%");
                } elseif ($X["op"] == "FIND_IN_SET") {
                    $Xe = "$X[op](" . q($X["val"]) . ", ";
                    $db = ")";
                } elseif (!preg_match('~NULL$~', $X["op"])) {
                    $db .= " " . $this->processInput($o[$X["col"]], $X["val"]);
                }
                if ($X["col"] != "") {
                    $K[] = $Xe . $l->convertSearch(idf_escape($X["col"]), $X, $o[$X["col"]]) . $db;
                } else {
                    $Ya = [];
                    foreach ($o as $F => $n) {
                        if ((preg_match('~^[-\d.' . (preg_match('~IN$~', $X["op"]) ? ',' : '') . ']+$~', $X["val"]) || !preg_match('~' . number_type() . '|bit~', $n["type"])) && (!preg_match("~[\x80-\xFF]~", $X["val"]) || preg_match('~char|text|enum|set~', $n["type"]))) {
                            $Ya[] = $Xe . $l->convertSearch(idf_escape($F), $X, $n) . $db;
                        }
                    }
                    $K[] = ($Ya ? "(" . implode(" OR ", $Ya) . ")" : "1 = 0");
                }
            }
        }
        return $K;
    }

    function selectOrderProcess($o, $y)
    {
        $K = [];
        foreach ((array) $_GET["order"] as $_ => $X) {
            if ($X != "") {
                $K[] = (preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~', $X) ? $X : idf_escape($X)) . (isset($_GET["desc"][$_]) ? " DESC" : "");
            }
        }
        return $K;
    }

    function selectLimitProcess()
    {
        return (isset($_GET["limit"]) ? $_GET["limit"] : "50");
    }

    function selectLengthProcess()
    {
        return (isset($_GET["text_length"]) ? $_GET["text_length"] : "100");
    }

    function selectEmailProcess($Z, $yc)
    {
        return false;
    }

    function selectQueryBuild($N, $Z, $t, $te, $A, $G)
    {
        return "";
    }

    function messageQuery($I, $_g, $nc = false)
    {
        global $z, $l;
        restart_session();
        $Pc =& get_session("queries");
        if (!$Pc[$_GET["db"]]) {
            $Pc[$_GET["db"]] = [];
        }
        if (strlen($I) > 1e6) {
            $I = preg_replace('~[\x80-\xFF]+$~', '', substr($I, 0, 1e6)) . "\n...";
        }
        $Pc[$_GET["db"]][] = [
            $I,
            time(),
            $_g,
        ];
        $Yf = "sql-" . count($Pc[$_GET["db"]]);
        $K = "<a href='#$Yf' class='toggle'>" . lang(60) . "</a>\n";
        if (!$nc && ($rh = $l->warnings())) {
            $v = "warnings-" . count($Pc[$_GET["db"]]);
            $K = "<a href='#$v' class='toggle'>" . lang(42) . "</a>, $K<div id='$v' class='hidden'>\n$rh</div>\n";
        }
        return " <span class='time'>" . @date("H:i:s") . "</span>" . " $K<div id='$Yf' class='hidden'><pre><code class='jush-$z'>" . shorten_utf8($I, 1000) . "</code></pre>" . ($_g ? " <span class='time'>($_g)</span>" : '') . (support("sql") ? '<p><a href="' . h(str_replace("db=" . urlencode(DB), "db=" . urlencode($_GET["db"]), ME) . 'sql=&history=' . (count($Pc[$_GET["db"]]) - 1)) . '">' . lang(10) . '</a>' : '') . '</div>';
    }

    function editFunctions($n)
    {
        global $Pb;
        $K = ($n["null"] ? "NULL/" : "");
        foreach ($Pb as $_ => $Cc) {
            if (!$_ || (!isset($_GET["call"]) && (isset($_GET["select"]) || where($_GET)))) {
                foreach ($Cc as $Pe => $X) {
                    if (!$Pe || preg_match("~$Pe~", $n["type"])) {
                        $K .= "/$X";
                    }
                }
                if ($_ && !preg_match('~set|blob|bytea|raw|file~', $n["type"])) {
                    $K .= "/SQL";
                }
            }
        }
        if ($n["auto_increment"] && !isset($_GET["select"]) && !where($_GET)) {
            $K = lang(47);
        }
        return explode("/", $K);
    }

    function editInput($Q, $n, $wa, $Y)
    {
        if ($n["type"] == "enum") {
            return (isset($_GET["select"]) ? "<label><input type='radio'$wa value='-1' checked><i>" . lang(8) . "</i></label> " : "") . ($n["null"] ? "<label><input type='radio'$wa value=''" . ($Y !== null || isset($_GET["select"]) ? "" : " checked") . "><i>NULL</i></label> " : "") . enum_input("radio", $wa, $n, $Y, 0);
        }
        return "";
    }

    function editHint($Q, $n, $Y)
    {
        return "";
    }

    function processInput($n, $Y, $s = "")
    {
        if ($s == "SQL") {
            return $Y;
        }
        $F = $n["field"];
        $K = q($Y);
        if (preg_match('~^(now|getdate|uuid)$~', $s)) {
            $K = "$s()";
        } elseif (preg_match('~^current_(date|timestamp)$~', $s)) {
            $K = $s;
        } elseif (preg_match('~^([+-]|\|\|)$~', $s)) {
            $K = idf_escape($F) . " $s $K";
        } elseif (preg_match('~^[+-] interval$~', $s)) {
            $K = idf_escape($F) . " $s " . (preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i", $Y) ? $Y : $K);
        } elseif (preg_match('~^(addtime|subtime|concat)$~', $s)) {
            $K = "$s(" . idf_escape($F) . ", $K)";
        } elseif (preg_match('~^(md5|sha1|password|encrypt)$~', $s)) {
            $K = "$s($K)";
        }
        return unconvert_field($n, $K);
    }

    function dumpOutput()
    {
        $K = [
            'text' => lang(61),
            'file' => lang(62),
        ];
        if (function_exists('gzencode')) {
            $K['gz'] = 'gzip';
        }
        return $K;
    }

    function dumpFormat()
    {
        return [
            'sql'  => 'SQL',
            'csv'  => 'CSV,',
            'csv;' => 'CSV;',
            'tsv'  => 'TSV',
        ];
    }

    function dumpDatabase($k)
    {
    }

    function dumpTable($Q, $gg, $hd = 0)
    {
        if ($_POST["format"] != "sql") {
            echo "\xef\xbb\xbf";
            if ($gg) {
                dump_csv(array_keys(fields($Q)));
            }
        } else {
            if ($hd == 2) {
                $o = [];
                foreach (fields($Q) as $F => $n) {
                    $o[] = idf_escape($F) . " $n[full_type]";
                }
                $i = "CREATE TABLE " . table($Q) . " (" . implode(", ", $o) . ")";
            } else {
                $i = create_sql($Q, $_POST["auto_increment"], $gg);
            }
            set_utf8mb4($i);
            if ($gg && $i) {
                if ($gg == "DROP+CREATE" || $hd == 1) {
                    echo "DROP " . ($hd == 2 ? "VIEW" : "TABLE") . " IF EXISTS " . table($Q) . ";\n";
                }
                if ($hd == 1) {
                    $i = remove_definer($i);
                }
                echo "$i;\n\n";
            }
        }
    }

    function dumpData($Q, $gg, $I)
    {
        global $g, $z;
        $Gd = ($z == "sqlite" ? 0 : 1048576);
        if ($gg) {
            if ($_POST["format"] == "sql") {
                if ($gg == "TRUNCATE+INSERT") {
                    echo truncate_sql($Q) . ";\n";
                }
                $o = fields($Q);
            }
            $J = $g->query($I, 1);
            if ($J) {
                $ad = "";
                $Ia = "";
                $kd = [];
                $ig = "";
                $qc = ($Q != '' ? 'fetch_assoc' : 'fetch_row');
                while ($L = $J->$qc()) {
                    if (!$kd) {
                        $jh = [];
                        foreach ($L as $X) {
                            $n = $J->fetch_field();
                            $kd[] = $n->name;
                            $_ = idf_escape($n->name);
                            $jh[] = "$_ = VALUES($_)";
                        }
                        $ig = ($gg == "INSERT+UPDATE" ? "\nON DUPLICATE KEY UPDATE " . implode(", ", $jh) : "") . ";\n";
                    }
                    if ($_POST["format"] != "sql") {
                        if ($gg == "table") {
                            dump_csv($kd);
                            $gg = "INSERT";
                        }
                        dump_csv($L);
                    } else {
                        if (!$ad) {
                            $ad = "INSERT INTO " . table($Q) . " (" . implode(", ", array_map('idf_escape', $kd)) . ") VALUES";
                        }
                        foreach ($L as $_ => $X) {
                            $n = $o[$_];
                            $L[$_] = ($X !== null ? unconvert_field($n, preg_match(number_type(), $n["type"]) && $X != '' ? $X : q(($X === false ? 0 : $X))) : "NULL");
                        }
                        $Cf = ($Gd ? "\n" : " ") . "(" . implode(",\t", $L) . ")";
                        if (!$Ia) {
                            $Ia = $ad . $Cf;
                        } elseif (strlen($Ia) + 4 + strlen($Cf) + strlen($ig) < $Gd) {
                            $Ia .= ",$Cf";
                        } else {
                            echo $Ia . $ig;
                            $Ia = $ad . $Cf;
                        }
                    }
                }
                if ($Ia) {
                    echo $Ia . $ig;
                }
            } elseif ($_POST["format"] == "sql") {
                echo "-- " . str_replace("\n", " ", $g->error) . "\n";
            }
        }
    }

    function dumpFilename($Tc)
    {
        return friendly_url($Tc != "" ? $Tc : (SERVER != "" ? SERVER : "localhost"));
    }

    function dumpHeaders($Tc, $Sd = false)
    {
        $Ce = $_POST["output"];
        $kc = (preg_match('~sql~', $_POST["format"]) ? "sql" : ($Sd ? "tar" : "csv"));
        header("Content-Type: " . ($Ce == "gz" ? "application/x-gzip" : ($kc == "tar" ? "application/x-tar" : ($kc == "sql" || $Ce != "file" ? "text/plain" : "text/csv") . "; charset=utf-8")));
        if ($Ce == "gz") {
            ob_start('ob_gzencode', 1e6);
        }
        return $kc;
    }

    function importServerPath()
    {
        return "adminer.sql";
    }

    function homepage()
    {
        echo '<p class="links">' . ($_GET["ns"] == "" && support("database") ? '<a href="' . h(ME) . 'database=">' . lang(63) . "</a>\n" : ""), (support("scheme") ? "<a href='" . h(ME) . "scheme='>" . ($_GET["ns"] != "" ? lang(64) : lang(65)) . "</a>\n" : ""), ($_GET["ns"] !== "" ? '<a href="' . h(ME) . 'schema=">' . lang(66) . "</a>\n" : ""), (support("privileges") ? "<a href='" . h(ME) . "privileges='>" . lang(67) . "</a>\n" : "");
        return true;
    }

    function navigation($Rd)
    {
        global $fa, $z, $Ib, $g;
        echo '<h1>
', $this->name(), ' <span class="version">', $fa, '</span>
<a href="https://www.adminer.org/#download"', target_blank(), ' id="version">', (version_compare($fa, $_COOKIE["adminer_version"]) < 0 ? h($_COOKIE["adminer_version"]) : ""), '</a>
</h1>
';
        if ($Rd == "auth") {
            $uc = true;
            foreach ((array) $_SESSION["pwds"] as $lh => $Nf) {
                foreach ($Nf as $O => $hh) {
                    foreach ($hh as $V => $Ne) {
                        if ($Ne !== null) {
                            if ($uc) {
                                echo "<p id='logins'>" . script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");
                                $uc = false;
                            }
                            $wb = $_SESSION["db"][$lh][$O][$V];
                            foreach (($wb ? array_keys($wb) : [""]) as $k) {
                                echo "<a href='" . h(auth_url($lh, $O, $V, $k)) . "'>($Ib[$lh]) " . h($V . ($O != "" ? "@" . $this->serverName($O) : "") . ($k != "" ? " - $k" : "")) . "</a><br>\n";
                            }
                        }
                    }
                }
            }
        } else {
            if ($_GET["ns"] !== "" && !$Rd && DB != "") {
                $g->select_db(DB);
                $S = table_status('', true);
            }
            echo script_src(preg_replace("~\\?.*~", "", ME) . "?file=jush.js&version=4.6.3");
            if (support("sql")) {
                echo '<script', nonce(), '>
';
                if ($S) {
                    $_d = [];
                    foreach ($S as $Q => $U) {
                        $_d[] = preg_quote($Q, '/');
                    }
                    echo "var jushLinks = { $z: [ '" . js_escape(ME) . (support("table") ? "table=" : "select=") . "\$&', /\\b(" . implode("|", $_d) . ")\\b/g ] };\n";
                    foreach ([
                                 "bac",
                                 "bra",
                                 "sqlite_quo",
                                 "mssql_bra",
                             ] as $X) {
                        echo "jushLinks.$X = jushLinks.$z;\n";
                    }
                }
                $Mf = $g->server_info;
                echo 'bodyLoad(\'', (is_object($g) ? preg_replace('~^(\d\.?\d).*~s', '\1', $Mf) : ""), '\'', (preg_match('~MariaDB~', $Mf) ? ", true" : ""), ');
</script>
';
            }
            $this->databasesPrint($Rd);
            if (DB == "" || !$Rd) {
                echo "<p class='links'>" . (support("sql") ? "<a href='" . h(ME) . "sql='" . bold(isset($_GET["sql"]) && !isset($_GET["import"])) . ">" . lang(60) . "</a>\n<a href='" . h(ME) . "import='" . bold(isset($_GET["import"])) . ">" . lang(68) . "</a>\n" : "") . "";
                if (support("dump")) {
                    echo "<a href='" . h(ME) . "dump=" . urlencode(isset($_GET["table"]) ? $_GET["table"] : $_GET["select"]) . "' id='dump'" . bold(isset($_GET["dump"])) . ">" . lang(69) . "</a>\n";
                }
            }
            if ($_GET["ns"] !== "" && !$Rd && DB != "") {
                echo '<a href="' . h(ME) . 'create="' . bold($_GET["create"] === "") . ">" . lang(70) . "</a>\n";
                if (!$S) {
                    echo "<p class='message'>" . lang(9) . "\n";
                } else {
                    $this->tablesPrint($S);
                }
            }
        }
    }

    function databasesPrint($Rd)
    {
        global $c, $g;
        $j = $this->databases();
        if ($j && !in_array(DB, $j)) {
            array_unshift($j, DB);
        }
        echo '<form action="">
<p id="dbs">
';
        hidden_fields_get();
        $ub = script("mixin(qsl('select'), {onmousedown: dbMouseDown, onchange: dbChange});");
        echo "<span title='" . lang(71) . "'>" . lang(72) . "</span>: " . ($j ? "<select name='db'>" . optionlist(["" => ""] + $j, DB) . "</select>$ub" : "<input name='db' value='" . h(DB) . "' autocapitalize='off'>\n"), "<input type='submit' value='" . lang(20) . "'" . ($j ? " class='hidden'" : "") . ">\n";
        if ($Rd != "db" && DB != "" && $g->select_db(DB)) {
        }
        foreach ([
                     "import",
                     "sql",
                     "schema",
                     "dump",
                     "privileges",
                 ] as $X) {
            if (isset($_GET[$X])) {
                echo "<input type='hidden' name='$X' value=''>";
                break;
            }
        }
        echo "</p></form>\n";
    }

    function tablesPrint($S)
    {
        echo "<ul id='tables'>" . script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");
        foreach ($S as $Q => $bg) {
            $F = $this->tableName($bg);
            if ($F != "") {
                echo '<li><a href="' . h(ME) . 'select=' . urlencode($Q) . '"' . bold($_GET["select"] == $Q || $_GET["edit"] == $Q, "select") . ">" . lang(73) . "</a> ", (support("table") || support("indexes") ? '<a href="' . h(ME) . 'table=' . urlencode($Q) . '"' . bold(in_array($Q, [
                            $_GET["table"],
                            $_GET["create"],
                            $_GET["indexes"],
                            $_GET["foreign"],
                            $_GET["trigger"],
                        ]), (is_view($bg) ? "view" : "structure")) . " title='" . lang(38) . "'>$F</a>" : "<span>$F</span>") . "\n";
            }
        }
        echo "</ul>\n";
    }
}

$c = (function_exists('adminer_object') ? adminer_object() : new
Adminer);
if ($c->operators === null) {
    $c->operators = $pe;
}
function page_header($Cg, $m = "", $Ha = [], $Dg = "")
{
    global $a, $fa, $c, $Ib, $z;
    page_headers();
    if (is_ajax() && $m) {
        page_messages($m);
        exit;
    }
    $Eg = $Cg . ($Dg != "" ? ": $Dg" : "");
    $Fg = strip_tags($Eg . (SERVER != "" && SERVER != "localhost" ? h(" - " . SERVER) : "") . " - " . $c->name());
    echo '<!DOCTYPE html>
<html lang="', $a, '" dir="', lang(74), '">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<title>', $Fg, '</title>
<link rel="stylesheet" type="text/css" href="', h(preg_replace("~\\?.*~", "", ME) . "?file=default.css&version=4.6.3"), '">
', script_src(preg_replace("~\\?.*~", "", ME) . "?file=functions.js&version=4.6.3");
    if ($c->head()) {
        echo '<link rel="shortcut icon" type="image/x-icon" href="', h(preg_replace("~\\?.*~", "", ME) . "?file=favicon.ico&version=4.6.3"), '">
<link rel="apple-touch-icon" href="', h(preg_replace("~\\?.*~", "", ME) . "?file=favicon.ico&version=4.6.3"), '">
';
        foreach ($c->css() as $ob) {
            echo '<link rel="stylesheet" type="text/css" href="', h($ob), '">
';
        }
    }
    echo '
<body class="', lang(74), ' nojs">
';
    $sc = get_temp_dir() . "/adminer.version";
    if (!$_COOKIE["adminer_version"] && function_exists('openssl_verify') && file_exists($sc) && filemtime($sc) + 86400 > time()) {
        $mh = unserialize(file_get_contents($sc));
        $gf = "-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwqWOVuF5uw7/+Z70djoK
RlHIZFZPO0uYRezq90+7Amk+FDNd7KkL5eDve+vHRJBLAszF/7XKXe11xwliIsFs
DFWQlsABVZB3oisKCBEuI71J4kPH8dKGEWR9jDHFw3cWmoH3PmqImX6FISWbG3B8
h7FIx3jEaw5ckVPVTeo5JRm/1DZzJxjyDenXvBQ/6o9DgZKeNDgxwKzH+sw9/YCO
jHnq1cFpOIISzARlrHMa/43YfeNRAm/tsBXjSxembBPo7aQZLAWHmaj5+K19H10B
nCpz9Y++cipkVEiKRGih4ZEvjoFysEOdRLj6WiD/uUNky4xGeA6LaJqh5XpkFkcQ
fQIDAQAB
-----END PUBLIC KEY-----
";
        if (openssl_verify($mh["version"], base64_decode($mh["signature"]), $gf) == 1) {
            $_COOKIE["adminer_version"] = $mh["version"];
        }
    }
    echo '<script', nonce(), '>
mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick', (isset($_COOKIE["adminer_version"]) ? "" : ", onload: partial(verifyVersion, '$fa', '" . js_escape(ME) . "', '" . get_token() . "')"); ?>});
    document.body.className = document.body.className.replace(/ nojs/, ' js');
    var offlineMessage = '<?php echo js_escape(lang(75)), '\';
var thousandsSeparator = \'', js_escape(lang(5)), '\';
</script>

<div id="help" class="jush-', $z, ' jsonly hidden"></div>
', script("mixin(qs('#help'), {onmouseover: function () { helpOpen = 1; }, onmouseout: helpMouseout});"), '
<div id="content">
';
    if ($Ha !== null) {
        $B = substr(preg_replace('~\b(username|db|ns)=[^&]*&~', '', ME), 0, -1);
        echo '<p id="breadcrumb"><a href="' . h($B ? $B : ".") . '">' . $Ib[DRIVER] . '</a> &raquo; ';
        $B = substr(preg_replace('~\b(db|ns)=[^&]*&~', '', ME), 0, -1);
        $O = $c->serverName(SERVER);
        $O = ($O != "" ? $O : lang(30));
        if ($Ha === false) {
            echo "$O\n";
        } else {
            echo "<a href='" . ($B ? h($B) : ".") . "' accesskey='1' title='Alt+Shift+1'>$O</a> &raquo; ";
            if ($_GET["ns"] != "" || (DB != "" && is_array($Ha))) {
                echo '<a href="' . h($B . "&db=" . urlencode(DB) . (support("scheme") ? "&ns=" : "")) . '">' . h(DB) . '</a> &raquo; ';
            }
            if (is_array($Ha)) {
                if ($_GET["ns"] != "") {
                    echo '<a href="' . h(substr(ME, 0, -1)) . '">' . h($_GET["ns"]) . '</a> &raquo; ';
                }
                foreach ($Ha as $_ => $X) {
                    $Ab = (is_array($X) ? $X[1] : h($X));
                    if ($Ab != "") {
                        echo "<a href='" . h(ME . "$_=") . urlencode(is_array($X) ? $X[0] : $X) . "'>$Ab</a> &raquo; ";
                    }
                }
            }
            echo "$Cg\n";
        }
    }
    echo "<h2>$Eg</h2>\n", "<div id='ajaxstatus' class='jsonly hidden'></div>\n";
    restart_session();
    page_messages($m);
    $j =& get_session("dbs");
    if (DB != "" && $j && !in_array(DB, $j, true)) {
        $j = null;
    }
    stop_session();
    define("PAGE_HEADER", 1);
}

function page_headers()
{
    global $c;
    header("Content-Type: text/html; charset=utf-8");
    header("Cache-Control: no-cache");
    header("X-Frame-Options: deny");
    header("X-XSS-Protection: 0");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: origin-when-cross-origin");
    foreach ($c->csp() as $nb) {
        $Nc = [];
        foreach ($nb as $_ => $X) {
            $Nc[] = "$_ $X";
        }
        header("Content-Security-Policy: " . implode("; ", $Nc));
    }
    $c->headers();
}

function csp()
{
    return [
        [
            "script-src"  => "'self' 'unsafe-inline' 'nonce-" . get_nonce() . "' 'strict-dynamic'",
            "connect-src" => "'self'",
            "frame-src"   => "https://www.adminer.org",
            "object-src"  => "'none'",
            "base-uri"    => "'none'",
            "form-action" => "'self'",
        ],
    ];
}

function get_nonce()
{
    static $ae;
    if (!$ae) {
        $ae = base64_encode(rand_string());
    }
    return $ae;
}

function page_messages($m)
{
    $ch = preg_replace('~^[^?]*~', '', $_SERVER["REQUEST_URI"]);
    $Pd = $_SESSION["messages"][$ch];
    if ($Pd) {
        echo "<div class='message'>" . implode("</div>\n<div class='message'>", $Pd) . "</div>" . script("messagesPrint();");
        unset($_SESSION["messages"][$ch]);
    }
    if ($m) {
        echo "<div class='error'>$m</div>\n";
    }
}

function page_footer($Rd = "")
{
    global $c, $T;
    echo '</div>

';
    switch_lang();
    if ($Rd != "auth") {
        echo '<form action="" method="post">
<p class="logout">
<input type="submit" name="logout" value="', lang(76), '" id="logout">
<input type="hidden" name="token" value="', $T, '">
</p>
</form>
';
    }
    echo '<div id="menu">
';
    $c->navigation($Rd);
    echo '</div>
', script("setupSubmitHighlight(document);");
}

function int32($Ud)
{
    while ($Ud >= 2147483648) {
        $Ud -= 4294967296;
    }
    while ($Ud <= -2147483649) {
        $Ud += 4294967296;
    }
    return (int) $Ud;
}

function long2str($W, $qh)
{
    $Cf = '';
    foreach ($W as $X) {
        $Cf .= pack('V', $X);
    }
    if ($qh) {
        return substr($Cf, 0, end($W));
    }
    return $Cf;
}

function str2long($Cf, $qh)
{
    $W = array_values(unpack('V*', str_pad($Cf, 4 * ceil(strlen($Cf) / 4), "\0")));
    if ($qh) {
        $W[] = strlen($Cf);
    }
    return $W;
}

function xxtea_mx($xh, $wh, $jg, $jd)
{
    return int32((($xh >> 5 & 0x7FFFFFF) ^ $wh << 2) + (($wh >> 3 & 0x1FFFFFFF) ^ $xh << 4)) ^ int32(($jg ^ $wh) + ($jd ^ $xh));
}

function encrypt_string($dg, $_)
{
    if ($dg == "") {
        return "";
    }
    $_ = array_values(unpack("V*", pack("H*", md5($_))));
    $W = str2long($dg, true);
    $Ud = count($W) - 1;
    $xh = $W[$Ud];
    $wh = $W[0];
    $H = floor(6 + 52 / ($Ud + 1));
    $jg = 0;
    while ($H-- > 0) {
        $jg = int32($jg + 0x9E3779B9);
        $Ob = $jg >> 2 & 3;
        for ($De = 0; $De < $Ud; $De++) {
            $wh = $W[$De + 1];
            $Td = xxtea_mx($xh, $wh, $jg, $_[$De & 3 ^ $Ob]);
            $xh = int32($W[$De] + $Td);
            $W[$De] = $xh;
        }
        $wh = $W[0];
        $Td = xxtea_mx($xh, $wh, $jg, $_[$De & 3 ^ $Ob]);
        $xh = int32($W[$Ud] + $Td);
        $W[$Ud] = $xh;
    }
    return long2str($W, false);
}

function decrypt_string($dg, $_)
{
    if ($dg == "") {
        return "";
    }
    if (!$_) {
        return false;
    }
    $_ = array_values(unpack("V*", pack("H*", md5($_))));
    $W = str2long($dg, false);
    $Ud = count($W) - 1;
    $xh = $W[$Ud];
    $wh = $W[0];
    $H = floor(6 + 52 / ($Ud + 1));
    $jg = int32($H * 0x9E3779B9);
    while ($jg) {
        $Ob = $jg >> 2 & 3;
        for ($De = $Ud; $De > 0; $De--) {
            $xh = $W[$De - 1];
            $Td = xxtea_mx($xh, $wh, $jg, $_[$De & 3 ^ $Ob]);
            $wh = int32($W[$De] - $Td);
            $W[$De] = $wh;
        }
        $xh = $W[$Ud];
        $Td = xxtea_mx($xh, $wh, $jg, $_[$De & 3 ^ $Ob]);
        $wh = int32($W[0] - $Td);
        $W[0] = $wh;
        $jg = int32($jg - 0x9E3779B9);
    }
    return long2str($W, true);
}

$g = '';
$Mc = $_SESSION["token"];
if (!$Mc) {
    $_SESSION["token"] = rand(1, 1e6);
}
$T = get_token();
$Qe = [];
if ($_COOKIE["adminer_permanent"]) {
    foreach (explode(" ", $_COOKIE["adminer_permanent"]) as $X) {
        list($_) = explode(":", $X);
        $Qe[$_] = $X;
    }
}
function add_invalid_login()
{
    global $c;
    $r = file_open_lock(get_temp_dir() . "/adminer.invalid");
    if (!$r) {
        return;
    }
    $dd = unserialize(stream_get_contents($r));
    $_g = time();
    if ($dd) {
        foreach ($dd as $ed => $X) {
            if ($X[0] < $_g) {
                unset($dd[$ed]);
            }
        }
    }
    $cd =& $dd[$c->bruteForceKey()];
    if (!$cd) {
        $cd = [
            $_g + 30 * 60,
            0,
        ];
    }
    $cd[1]++;
    file_write_unlock($r, serialize($dd));
}

function check_invalid_login()
{
    global $c;
    $dd = unserialize(@file_get_contents(get_temp_dir() . "/adminer.invalid"));
    $cd = $dd[$c->bruteForceKey()];
    $Zd = ($cd[1] > 29 ? $cd[0] - time() : 0);
    if ($Zd > 0) {
        auth_error(lang(77, ceil($Zd / 60)));
    }
}

$xa = $_POST["auth"];
if ($xa) {
    session_regenerate_id();
    $lh = $xa["driver"];
    $O = $xa["server"];
    $V = $xa["username"];
    $Ne = (string) $xa["password"];
    $k = $xa["db"];
    set_password($lh, $O, $V, $Ne);
    $_SESSION["db"][$lh][$O][$V][$k] = true;
    if ($xa["permanent"]) {
        $_ = base64_encode($lh) . "-" . base64_encode($O) . "-" . base64_encode($V) . "-" . base64_encode($k);
        $bf = $c->permanentLogin(true);
        $Qe[$_] = "$_:" . base64_encode($bf ? encrypt_string($Ne, $bf) : "");
        cookie("adminer_permanent", implode(" ", $Qe));
    }
    if (count($_POST) == 1 || DRIVER != $lh || SERVER != $O || $_GET["username"] !== $V || DB != $k) {
        redirect(auth_url($lh, $O, $V, $k));
    }
} elseif ($_POST["logout"]) {
    if ($Mc && !verify_token()) {
        page_header(lang(76), lang(78));
        page_footer("db");
        exit;
    } else {
        foreach ([
                     "pwds",
                     "db",
                     "dbs",
                     "queries",
                 ] as $_) {
            set_session($_, null);
        }
        unset_permanent();
        redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~', '', ME), 0, -1), lang(79) . ' ' . lang(80, 'https://sourceforge.net/donate/index.php?group_id=264133'));
    }
} elseif ($Qe && !$_SESSION["pwds"]) {
    session_regenerate_id();
    $bf = $c->permanentLogin();
    foreach ($Qe as $_ => $X) {
        list(, $Qa) = explode(":", $X);
        list($lh, $O, $V, $k) = array_map('base64_decode', explode("-", $_));
        set_password($lh, $O, $V, decrypt_string(base64_decode($Qa), $bf));
        $_SESSION["db"][$lh][$O][$V][$k] = true;
    }
}
function unset_permanent()
{
    global $Qe;
    foreach ($Qe as $_ => $X) {
        list($lh, $O, $V, $k) = array_map('base64_decode', explode("-", $_));
        if ($lh == DRIVER && $O == SERVER && $V == $_GET["username"] && $k == DB) {
            unset($Qe[$_]);
        }
    }
    cookie("adminer_permanent", implode(" ", $Qe));
}

function auth_error($m)
{
    global $c, $Mc;
    $Of = session_name();
    if (isset($_GET["username"])) {
        header("HTTP/1.1 403 Forbidden");
        if (($_COOKIE[$Of] || $_GET[$Of]) && !$Mc) {
            $m = lang(81);
        } else {
            restart_session();
            add_invalid_login();
            $Ne = get_password();
            if ($Ne !== null) {
                if ($Ne === false) {
                    $m .= '<br>' . lang(82, target_blank(), '<code>permanentLogin()</code>');
                }
                set_password(DRIVER, SERVER, $_GET["username"], null);
            }
            unset_permanent();
        }
    }
    if (!$_COOKIE[$Of] && $_GET[$Of] && ini_bool("session.use_only_cookies")) {
        $m = lang(83);
    }
    $Ge = session_get_cookie_params();
    cookie("adminer_key", ($_COOKIE["adminer_key"] ? $_COOKIE["adminer_key"] : rand_string()), $Ge["lifetime"]);
    page_header(lang(34), $m, null);
    echo "<form action='' method='post'>\n", "<div>";
    if (hidden_fields($_POST, ["auth"])) {
        echo "<p class='message'>" . lang(84) . "\n";
    }
    echo "</div>\n";
    $c->loginForm();
    echo "</form>\n";
    page_footer("auth");
    exit;
}

if (isset($_GET["username"]) && !class_exists("Min_DB")) {
    unset($_SESSION["pwds"][DRIVER]);
    unset_permanent();
    page_header(lang(85), lang(86, implode(", ", $We)), false);
    page_footer("auth");
    exit;
}
stop_session(true);
if (isset($_GET["username"])) {
    list($Rc, $Se) = explode(":", SERVER, 2);
    if (is_numeric($Se) && $Se < 1024) {
        auth_error(lang(87));
    }
    check_invalid_login();
    $g = connect();
    $l = new
    Min_Driver($g);
}
$Ad = null;
if (!is_object($g) || ($Ad = $c->login($_GET["username"], get_password())) !== true) {
    auth_error((is_string($g) ? h($g) : (is_string($Ad) ? $Ad : lang(88))));
}
if ($xa && $_POST["token"]) {
    $_POST["token"] = $T;
}
$m = '';
if ($_POST) {
    if (!verify_token()) {
        $Xc = "max_input_vars";
        $Kd = ini_get($Xc);
        if (extension_loaded("suhosin")) {
            foreach ([
                         "suhosin.request.max_vars",
                         "suhosin.post.max_vars",
                     ] as $_) {
                $X = ini_get($_);
                if ($X && (!$Kd || $X < $Kd)) {
                    $Xc = $_;
                    $Kd = $X;
                }
            }
        }
        $m = (!$_POST["token"] && $Kd ? lang(89, "'$Xc'") : lang(78) . ' ' . lang(90));
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $m = lang(91, "'post_max_size'");
    if (isset($_GET["sql"])) {
        $m .= ' ' . lang(92);
    }
}
function select($J, $h = null, $we = [], $A = 0)
{
    global $z;
    $_d = [];
    $y = [];
    $e = [];
    $Fa = [];
    $Tg = [];
    $K = [];
    odd('');
    for ($u = 0; (!$A || $u < $A) && ($L = $J->fetch_row()); $u++) {
        if (!$u) {
            echo "<table cellspacing='0' class='nowrap'>\n", "<thead><tr>";
            for ($id = 0; $id < count($L); $id++) {
                $n = $J->fetch_field();
                $F = $n->name;
                $ve = $n->orgtable;
                $ue = $n->orgname;
                $K[$n->table] = $ve;
                if ($we && $z == "sql") {
                    $_d[$id] = ($F == "table" ? "table=" : ($F == "possible_keys" ? "indexes=" : null));
                } elseif ($ve != "") {
                    if (!isset($y[$ve])) {
                        $y[$ve] = [];
                        foreach (indexes($ve, $h) as $x) {
                            if ($x["type"] == "PRIMARY") {
                                $y[$ve] = array_flip($x["columns"]);
                                break;
                            }
                        }
                        $e[$ve] = $y[$ve];
                    }
                    if (isset($e[$ve][$ue])) {
                        unset($e[$ve][$ue]);
                        $y[$ve][$ue] = $id;
                        $_d[$id] = $ve;
                    }
                }
                if ($n->charsetnr == 63) {
                    $Fa[$id] = true;
                }
                $Tg[$id] = $n->type;
                echo "<th" . ($ve != "" || $n->name != $ue ? " title='" . h(($ve != "" ? "$ve." : "") . $ue) . "'" : "") . ">" . h($F) . ($we ? doc_link([
                        'sql'     => "explain-output.html#explain_" . strtolower($F),
                        'mariadb' => "explain/#the-columns-in-explain-select",
                    ]) : "");
            }
            echo "</thead>\n";
        }
        echo "<tr" . odd() . ">";
        foreach ($L as $_ => $X) {
            if ($X === null) {
                $X = "<i>NULL</i>";
            } elseif ($Fa[$_] && !is_utf8($X)) {
                $X = "<i>" . lang(43, strlen($X)) . "</i>";
            } else {
                $X = h($X);
                if ($Tg[$_] == 254) {
                    $X = "<code>$X</code>";
                }
            }
            if (isset($_d[$_]) && !$e[$_d[$_]]) {
                if ($we && $z == "sql") {
                    $Q = $L[array_search("table=", $_d)];
                    $B = $_d[$_] . urlencode($we[$Q] != "" ? $we[$Q] : $Q);
                } else {
                    $B = "edit=" . urlencode($_d[$_]);
                    foreach ($y[$_d[$_]] as $Ua => $id) {
                        $B .= "&where" . urlencode("[" . bracket_escape($Ua) . "]") . "=" . urlencode($L[$id]);
                    }
                }
                $X = "<a href='" . h(ME . $B) . "'>$X</a>";
            }
            echo "<td>$X";
        }
    }
    echo ($u ? "</table>" : "<p class='message'>" . lang(12)) . "\n";
    return $K;
}

function referencable_primary($Jf)
{
    $K = [];
    foreach (table_status('', true) as $ng => $Q) {
        if ($ng != $Jf && fk_support($Q)) {
            foreach (fields($ng) as $n) {
                if ($n["primary"]) {
                    if ($K[$ng]) {
                        unset($K[$ng]);
                        break;
                    }
                    $K[$ng] = $n;
                }
            }
        }
    }
    return $K;
}

function textarea($F, $Y, $M = 10, $Ya = 80)
{
    global $z;
    echo "<textarea name='$F' rows='$M' cols='$Ya' class='sqlarea jush-$z' spellcheck='false' wrap='off'>";
    if (is_array($Y)) {
        foreach ($Y as $X) {
            echo h($X[0]) . "\n\n\n";
        }
    } else {
        echo h($Y);
    }
    echo "</textarea>";
}

function edit_type($_, $n, $Xa, $q = [], $mc = [])
{
    global $fg, $Tg, $ah, $le;
    $U = $n["type"];
    echo '<td><select name="', h($_), '[type]" class="type" aria-labelledby="label-type">';
    if ($U && !isset($Tg[$U]) && !isset($q[$U]) && !in_array($U, $mc)) {
        $mc[] = $U;
    }
    if ($q) {
        $fg[lang(93)] = $q;
    }
    echo optionlist(array_merge($mc, $fg), $U), '</select>
', on_help("getTarget(event).value", 1), script("mixin(qsl('select'), {onfocus: function () { lastType = selectValue(this); }, onchange: editingTypeChange});", ""), '<td><input name="', h($_), '[length]" value="', h($n["length"]), '" size="3"', (!$n["length"] && preg_match('~var(char|binary)$~', $U) ? " class='required'" : "");
    echo ' aria-labelledby="label-length">', script("mixin(qsl('input'), {onfocus: editingLengthFocus, oninput: editingLengthChange});", ""), '<td class="options">', "<select name='" . h($_) . "[collation]'" . (preg_match('~(char|text|enum|set)$~', $U) ? "" : " class='hidden'") . '><option value="">(' . lang(94) . ')' . optionlist($Xa, $n["collation"]) . '</select>', ($ah ? "<select name='" . h($_) . "[unsigned]'" . (!$U || preg_match(number_type(), $U) ? "" : " class='hidden'") . '><option>' . optionlist($ah, $n["unsigned"]) . '</select>' : ''), (isset($n['on_update']) ? "<select name='" . h($_) . "[on_update]'" . (preg_match('~timestamp|datetime~', $U) ? "" : " class='hidden'") . '>' . optionlist([
            "" => "(" . lang(95) . ")",
            "CURRENT_TIMESTAMP",
        ], $n["on_update"]) . '</select>' : ''), ($q ? "<select name='" . h($_) . "[on_delete]'" . (preg_match("~`~", $U) ? "" : " class='hidden'") . "><option value=''>(" . lang(96) . ")" . optionlist(explode("|", $le), $n["on_delete"]) . "</select> " : " ");
}

function process_length($xd)
{
    global $Zb;
    return (preg_match("~^\\s*\\(?\\s*$Zb(?:\\s*,\\s*$Zb)*+\\s*\\)?\\s*\$~", $xd) && preg_match_all("~$Zb~", $xd, $Ed) ? "(" . implode(",", $Ed[0]) . ")" : preg_replace('~^[0-9].*~', '(\0)', preg_replace('~[^-0-9,+()[\]]~', '', $xd)));
}

function process_type($n, $Va = "COLLATE")
{
    global $ah;
    return " $n[type]" . process_length($n["length"]) . (preg_match(number_type(), $n["type"]) && in_array($n["unsigned"], $ah) ? " $n[unsigned]" : "") . (preg_match('~char|text|enum|set~', $n["type"]) && $n["collation"] ? " $Va " . q($n["collation"]) : "");
}

function process_field($n, $Rg)
{
    return [
        idf_escape(trim($n["field"])),
        process_type($Rg),
        ($n["null"] ? " NULL" : " NOT NULL"),
        default_value($n),
        (preg_match('~timestamp|datetime~', $n["type"]) && $n["on_update"] ? " ON UPDATE $n[on_update]" : ""),
        (support("comment") && $n["comment"] != "" ? " COMMENT " . q($n["comment"]) : ""),
        ($n["auto_increment"] ? auto_increment() : null),
    ];
}

function default_value($n)
{
    $yb = $n["default"];
    return ($yb === null ? "" : " DEFAULT " . (preg_match('~char|binary|text|enum|set~', $n["type"]) || preg_match('~^(?![a-z])~i', $yb) ? q($yb) : $yb));
}

function type_class($U)
{
    foreach ([
                 'char'   => 'text',
                 'date'   => 'time|year',
                 'binary' => 'blob',
                 'enum'   => 'set',
             ] as $_ => $X) {
        if (preg_match("~$_|$X~", $U)) {
            return " class='$_'";
        }
    }
}

function edit_fields($o, $Xa, $U = "TABLE", $q = [], $cb = false)
{
    global $Yc;
    $o = array_values($o);
    echo '<thead><tr>
';
    if ($U == "PROCEDURE") {
        echo '<td>';
    }
    echo '<th id="label-name">', ($U == "TABLE" ? lang(97) : lang(98)), '<td id="label-type">', lang(45), '<textarea id="enum-edit" rows="4" cols="12" wrap="off" style="display: none;"></textarea>', script("qs('#enum-edit').onblur = editingLengthBlur;"), '<td id="label-length">', lang(99), '<td>', lang(100);
    if ($U == "TABLE") {
        echo '<td id="label-null">NULL
<td><input type="radio" name="auto_increment_col" value=""><acronym id="label-ai" title="', lang(47), '">AI</acronym>', doc_link([
            'sql'     => "example-auto-increment.html",
            'mariadb' => "auto_increment/",
            'sqlite'  => "autoinc.html",
            'pgsql'   => "datatype.html#DATATYPE-SERIAL",
            'mssql'   => "ms186775.aspx",
        ]), '<td id="label-default">', lang(48), (support("comment") ? "<td id='label-comment'" . ($cb ? "" : " class='hidden'") . ">" . lang(46) : "");
    }
    echo '<td>', "<input type='image' class='icon' name='add[" . (support("move_col") ? 0 : count($o)) . "]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=plus.gif&version=4.6.3") . "' alt='+' title='" . lang(101) . "'>" . script("row_count = " . count($o) . ";"), '</thead>
<tbody>
', script("mixin(qsl('tbody'), {onclick: editingClick, onkeydown: editingKeydown, oninput: editingInput});");
    foreach ($o as $u => $n) {
        $u++;
        $xe = $n[($_POST ? "orig" : "field")];
        $Eb = (isset($_POST["add"][$u - 1]) || (isset($n["field"]) && !$_POST["drop_col"][$u])) && (support("drop_col") || $xe == "");
        echo '<tr', ($Eb ? "" : " style='display: none;'"), '>
', ($U == "PROCEDURE" ? "<td>" . html_select("fields[$u][inout]", explode("|", $Yc), $n["inout"]) : ""), '<th>';
        if ($Eb) {
            echo '<input name="fields[', $u, '][field]" value="', h($n["field"]), '" maxlength="64" autocapitalize="off" aria-labelledby="label-name">', script("qsl('input').oninput = function () { editingNameChange.call(this);" . ($n["field"] != "" || count($o) > 1 ? "" : " editingAddRow.call(this);") . " };", "");
        }
        echo '<input type="hidden" name="fields[', $u, '][orig]" value="', h($xe), '">
';
        edit_type("fields[$u]", $n, $Xa, $q);
        if ($U == "TABLE") {
            echo '<td>', checkbox("fields[$u][null]", 1, $n["null"], "", "", "block", "label-null"), '<td><label class="block"><input type="radio" name="auto_increment_col" value="', $u, '"';
            if ($n["auto_increment"]) {
                echo ' checked';
            }
            echo ' aria-labelledby="label-ai"></label><td>', checkbox("fields[$u][has_default]", 1, $n["has_default"], "", "", "", "label-default"), '<input name="fields[', $u, '][default]" value="', h($n["default"]), '" aria-labelledby="label-default">', (support("comment") ? "<td" . ($cb ? "" : " class='hidden'") . "><input name='fields[$u][comment]' value='" . h($n["comment"]) . "' maxlength='" . (min_version(5.5) ? 1024 : 255) . "' aria-labelledby='label-comment'>" : "");
        }
        echo "<td>", (support("move_col") ? "<input type='image' class='icon' name='add[$u]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=plus.gif&version=4.6.3") . "' alt='+' title='" . lang(101) . "'> " . "<input type='image' class='icon' name='up[$u]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=up.gif&version=4.6.3") . "' alt='↑' title='" . lang(102) . "'> " . "<input type='image' class='icon' name='down[$u]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=down.gif&version=4.6.3") . "' alt='↓' title='" . lang(103) . "'> " : ""), ($xe == "" || support("drop_col") ? "<input type='image' class='icon' name='drop_col[$u]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=cross.gif&version=4.6.3") . "' alt='x' title='" . lang(104) . "'>" : "");
    }
}

function process_fields(&$o)
{
    $ee = 0;
    if ($_POST["up"]) {
        $rd = 0;
        foreach ($o as $_ => $n) {
            if (key($_POST["up"]) == $_) {
                unset($o[$_]);
                array_splice($o, $rd, 0, [$n]);
                break;
            }
            if (isset($n["field"])) {
                $rd = $ee;
            }
            $ee++;
        }
    } elseif ($_POST["down"]) {
        $_c = false;
        foreach ($o as $_ => $n) {
            if (isset($n["field"]) && $_c) {
                unset($o[key($_POST["down"])]);
                array_splice($o, $ee, 0, [$_c]);
                break;
            }
            if (key($_POST["down"]) == $_) {
                $_c = $n;
            }
            $ee++;
        }
    } elseif ($_POST["add"]) {
        $o = array_values($o);
        array_splice($o, key($_POST["add"]), 0, [[]]);
    } elseif (!$_POST["drop_col"]) {
        return false;
    }
    return true;
}

function normalize_enum($D)
{
    return "'" . str_replace("'", "''", addcslashes(stripcslashes(str_replace($D[0][0] . $D[0][0], $D[0][0], substr($D[0], 1, -1))), '\\')) . "'";
}

function grant($Dc, $df, $e, $ke)
{
    if (!$df) {
        return true;
    }
    if ($df == [
            "ALL PRIVILEGES",
            "GRANT OPTION",
        ]) {
        return ($Dc == "GRANT" ? queries("$Dc ALL PRIVILEGES$ke WITH GRANT OPTION") : queries("$Dc ALL PRIVILEGES$ke") && queries("$Dc GRANT OPTION$ke"));
    }
    return queries("$Dc " . preg_replace('~(GRANT OPTION)\([^)]*\)~', '\1', implode("$e, ", $df) . $e) . $ke);
}

function drop_create($Jb, $i, $Kb, $xg, $Lb, $C, $Od, $Md, $Nd, $he, $Xd)
{
    if ($_POST["drop"]) {
        query_redirect($Jb, $C, $Od);
    } elseif ($he == "") {
        query_redirect($i, $C, $Nd);
    } elseif ($he != $Xd) {
        $lb = queries($i);
        queries_redirect($C, $Md, $lb && queries($Jb));
        if ($lb) {
            queries($Kb);
        }
    } else {
        queries_redirect($C, $Md, queries($xg) && queries($Lb) && queries($Jb) && queries($i));
    }
}

function create_trigger($ke, $L)
{
    global $z;
    $Bg = " $L[Timing] $L[Event]" . ($L["Event"] == "UPDATE OF" ? " " . idf_escape($L["Of"]) : "");
    return "CREATE TRIGGER " . idf_escape($L["Trigger"]) . ($z == "mssql" ? $ke . $Bg : $Bg . $ke) . rtrim(" $L[Type]\n$L[Statement]", ";") . ";";
}

function create_routine($_f, $L)
{
    global $Yc, $z;
    $P = [];
    $o = (array) $L["fields"];
    ksort($o);
    foreach ($o as $n) {
        if ($n["field"] != "") {
            $P[] = (preg_match("~^($Yc)\$~", $n["inout"]) ? "$n[inout] " : "") . idf_escape($n["field"]) . process_type($n, "CHARACTER SET");
        }
    }
    $zb = rtrim("\n$L[definition]", ";");
    return "CREATE $_f " . idf_escape(trim($L["name"])) . " (" . implode(", ", $P) . ")" . (isset($_GET["function"]) ? " RETURNS" . process_type($L["returns"], "CHARACTER SET") : "") . ($L["language"] ? " LANGUAGE $L[language]" : "") . ($z == "pgsql" ? " AS " . q($zb) : "$zb;");
}

function remove_definer($I)
{
    return preg_replace('~^([A-Z =]+) DEFINER=`' . preg_replace('~@(.*)~', '`@`(%|\1)', logged_user()) . '`~', '\1', $I);
}

function format_foreign_key($p)
{
    global $le;
    return " FOREIGN KEY (" . implode(", ", array_map('idf_escape', $p["source"])) . ") REFERENCES " . table($p["table"]) . " (" . implode(", ", array_map('idf_escape', $p["target"])) . ")" . (preg_match("~^($le)\$~", $p["on_delete"]) ? " ON DELETE $p[on_delete]" : "") . (preg_match("~^($le)\$~", $p["on_update"]) ? " ON UPDATE $p[on_update]" : "");
}

function tar_file($sc, $Gg)
{
    $K = pack("a100a8a8a8a12a12", $sc, 644, 0, 0, decoct($Gg->size), decoct(time()));
    $Pa = 8 * 32;
    for ($u = 0; $u < strlen($K); $u++) {
        $Pa += ord($K[$u]);
    }
    $K .= sprintf("%06o", $Pa) . "\0 ";
    echo $K, str_repeat("\0", 512 - strlen($K));
    $Gg->send();
    echo str_repeat("\0", 511 - ($Gg->size + 511) % 512);
}

function ini_bytes($Xc)
{
    $X = ini_get($Xc);
    switch (strtolower(substr($X, -1))) {
        case'g':
            $X *= 1024;
        case'm':
            $X *= 1024;
        case'k':
            $X *= 1024;
    }
    return $X;
}

function doc_link($Oe, $yg = "<sup>?</sup>")
{
    global $z, $g;
    $Mf = $g->server_info;
    $mh = preg_replace('~^(\d\.?\d).*~s', '\1', $Mf);
    $eh = [
        'sql'    => "https://dev.mysql.com/doc/refman/$mh/en/",
        'sqlite' => "https://www.sqlite.org/",
        'pgsql'  => "https://www.postgresql.org/docs/$mh/static/",
        'mssql'  => "https://msdn.microsoft.com/library/",
        'oracle' => "https://download.oracle.com/docs/cd/B19306_01/server.102/b14200/",
    ];
    if (preg_match('~MariaDB~', $Mf)) {
        $eh['sql'] = "https://mariadb.com/kb/en/library/";
        $Oe['sql'] = (isset($Oe['mariadb']) ? $Oe['mariadb'] : str_replace(".html", "/", $Oe['sql']));
    }
    return ($Oe[$z] ? "<a href='$eh[$z]$Oe[$z]'" . target_blank() . ">$yg</a>" : "");
}

function ob_gzencode($eg)
{
    return gzencode($eg);
}

function db_size($k)
{
    global $g;
    if (!$g->select_db($k)) {
        return "?";
    }
    $K = 0;
    foreach (table_status() as $R) {
        $K += $R["Data_length"] + $R["Index_length"];
    }
    return format_number($K);
}

function set_utf8mb4($i)
{
    global $g;
    static $P = false;
    if (!$P && preg_match('~\butf8mb4~i', $i)) {
        $P = true;
        echo "SET NAMES " . charset($g) . ";\n\n";
    }
}

function connect_error()
{
    global $c, $g, $T, $m, $Ib;
    if (DB != "") {
        header("HTTP/1.1 404 Not Found");
        page_header(lang(33) . ": " . h(DB), lang(105), true);
    } else {
        if ($_POST["db"] && !$m) {
            queries_redirect(substr(ME, 0, -1), lang(106), drop_databases($_POST["db"]));
        }
        page_header(lang(107), $m, false);
        echo "<p class='links'>\n";
        foreach ([
                     'database'    => lang(108),
                     'privileges'  => lang(67),
                     'processlist' => lang(109),
                     'variables'   => lang(110),
                     'status'      => lang(111),
                 ] as $_ => $X) {
            if (support($_)) {
                echo "<a href='" . h(ME) . "$_='>$X</a>\n";
            }
        }
        echo "<p>" . lang(112, $Ib[DRIVER], "<b>" . h($g->server_info) . "</b>", "<b>$g->extension</b>") . "\n", "<p>" . lang(113, "<b>" . h(logged_user()) . "</b>") . "\n";
        $j = $c->databases();
        if ($j) {
            $Ff = support("scheme");
            $Xa = collations();
            echo "<form action='' method='post'>\n", "<table cellspacing='0' class='checkable'>\n", script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"), "<thead><tr>" . (support("database") ? "<td>" : "") . "<th>" . lang(33) . " - <a href='" . h(ME) . "refresh=1'>" . lang(114) . "</a>" . "<td>" . lang(115) . "<td>" . lang(116) . "<td>" . lang(117) . " - <a href='" . h(ME) . "dbsize=1'>" . lang(118) . "</a>" . script("qsl('a').onclick = partial(ajaxSetHtml, '" . js_escape(ME) . "script=connect');", "") . "</thead>\n";
            $j = ($_GET["dbsize"] ? count_tables($j) : array_flip($j));
            foreach ($j as $k => $S) {
                $zf = h(ME) . "db=" . urlencode($k);
                $v = h("Db-" . $k);
                echo "<tr" . odd() . ">" . (support("database") ? "<td>" . checkbox("db[]", $k, in_array($k, (array) $_POST["db"]), "", "", "", $v) : ""), "<th><a href='$zf' id='$v'>" . h($k) . "</a>";
                $Wa = h(db_collation($k, $Xa));
                echo "<td>" . (support("database") ? "<a href='$zf" . ($Ff ? "&amp;ns=" : "") . "&amp;database=' title='" . lang(63) . "'>$Wa</a>" : $Wa), "<td align='right'><a href='$zf&amp;schema=' id='tables-" . h($k) . "' title='" . lang(66) . "'>" . ($_GET["dbsize"] ? $S : "?") . "</a>", "<td align='right' id='size-" . h($k) . "'>" . ($_GET["dbsize"] ? db_size($k) : "?"), "\n";
            }
            echo "</table>\n", (support("database") ? "<div class='footer'><div>\n" . "<fieldset><legend>" . lang(119) . " <span id='selected'></span></legend><div>\n" . "<input type='hidden' name='all' value=''>" . script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^db/)); };") . "<input type='submit' name='drop' value='" . lang(120) . "'>" . confirm() . "\n" . "</div></fieldset>\n" . "</div></div>\n" : ""), "<input type='hidden' name='token' value='$T'>\n", "</form>\n", script("tableCheck();");
        }
    }
    page_footer("db");
}

if (isset($_GET["status"])) {
    $_GET["variables"] = $_GET["status"];
}
if (isset($_GET["import"])) {
    $_GET["sql"] = $_GET["import"];
}
if (!(DB != "" ? $g->select_db(DB) : isset($_GET["sql"]) || isset($_GET["dump"]) || isset($_GET["database"]) || isset($_GET["processlist"]) || isset($_GET["privileges"]) || isset($_GET["user"]) || isset($_GET["variables"]) || $_GET["script"] == "connect" || $_GET["script"] == "kill")) {
    if (DB != "" || $_GET["refresh"]) {
        restart_session();
        set_session("dbs", null);
    }
    connect_error();
    exit;
}
$le = "RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";

class
TmpFile
{
    var $handler;
    var $size;

    function __construct()
    {
        $this->handler = tmpfile();
    }

    function write($gb)
    {
        $this->size += strlen($gb);
        fwrite($this->handler, $gb);
    }

    function send()
    {
        fseek($this->handler, 0);
        fpassthru($this->handler);
        fclose($this->handler);
    }
}

$Zb = "'(?:''|[^'\\\\]|\\\\.)*'";
$Yc = "IN|OUT|INOUT";
if (isset($_GET["select"]) && ($_POST["edit"] || $_POST["clone"]) && !$_POST["save"]) {
    $_GET["edit"] = $_GET["select"];
}
if (isset($_GET["callf"])) {
    $_GET["call"] = $_GET["callf"];
}
if (isset($_GET["function"])) {
    $_GET["procedure"] = $_GET["function"];
}
if (isset($_GET["download"])) {
    $b = $_GET["download"];
    $o = fields($b);
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=" . friendly_url("$b-" . implode("_", $_GET["where"])) . "." . friendly_url($_GET["field"]));
    $N = [idf_escape($_GET["field"])];
    $J = $l->select($b, $N, [where($_GET, $o)], $N);
    $L = ($J ? $J->fetch_row() : []);
    echo $l->value($L[0], $o[$_GET["field"]]);
    exit;
} elseif (isset($_GET["table"])) {
    $b = $_GET["table"];
    $o = fields($b);
    if (!$o) {
        $m = error();
    }
    $R = table_status1($b, true);
    $F = $c->tableName($R);
    page_header(($o && is_view($R) ? $R['Engine'] == 'materialized view' ? lang(121) : lang(122) : lang(123)) . ": " . ($F != "" ? $F : h($b)), $m);
    $c->selectLinks($R);
    $bb = $R["Comment"];
    if ($bb != "") {
        echo "<p class='nowrap'>" . lang(46) . ": " . h($bb) . "\n";
    }
    if ($o) {
        $c->tableStructurePrint($o);
    }
    if (!is_view($R)) {
        if (support("indexes")) {
            echo "<h3 id='indexes'>" . lang(124) . "</h3>\n";
            $y = indexes($b);
            if ($y) {
                $c->tableIndexesPrint($y);
            }
            echo '<p class="links"><a href="' . h(ME) . 'indexes=' . urlencode($b) . '">' . lang(125) . "</a>\n";
        }
        if (fk_support($R)) {
            echo "<h3 id='foreign-keys'>" . lang(93) . "</h3>\n";
            $q = foreign_keys($b);
            if ($q) {
                echo "<table cellspacing='0'>\n", "<thead><tr><th>" . lang(126) . "<td>" . lang(127) . "<td>" . lang(96) . "<td>" . lang(95) . "<td></thead>\n";
                foreach ($q as $F => $p) {
                    echo "<tr title='" . h($F) . "'>", "<th><i>" . implode("</i>, <i>", array_map('h', $p["source"])) . "</i>", "<td><a href='" . h($p["db"] != "" ? preg_replace('~db=[^&]*~', "db=" . urlencode($p["db"]), ME) : ($p["ns"] != "" ? preg_replace('~ns=[^&]*~', "ns=" . urlencode($p["ns"]), ME) : ME)) . "table=" . urlencode($p["table"]) . "'>" . ($p["db"] != "" ? "<b>" . h($p["db"]) . "</b>." : "") . ($p["ns"] != "" ? "<b>" . h($p["ns"]) . "</b>." : "") . h($p["table"]) . "</a>", "(<i>" . implode("</i>, <i>", array_map('h', $p["target"])) . "</i>)", "<td>" . h($p["on_delete"]) . "\n", "<td>" . h($p["on_update"]) . "\n", '<td><a href="' . h(ME . 'foreign=' . urlencode($b) . '&name=' . urlencode($F)) . '">' . lang(128) . '</a>';
                }
                echo "</table>\n";
            }
            echo '<p class="links"><a href="' . h(ME) . 'foreign=' . urlencode($b) . '">' . lang(129) . "</a>\n";
        }
    }
    if (support(is_view($R) ? "view_trigger" : "trigger")) {
        echo "<h3 id='triggers'>" . lang(130) . "</h3>\n";
        $Qg = triggers($b);
        if ($Qg) {
            echo "<table cellspacing='0'>\n";
            foreach ($Qg as $_ => $X) {
                echo "<tr valign='top'><td>" . h($X[0]) . "<td>" . h($X[1]) . "<th>" . h($_) . "<td><a href='" . h(ME . 'trigger=' . urlencode($b) . '&name=' . urlencode($_)) . "'>" . lang(128) . "</a>\n";
            }
            echo "</table>\n";
        }
        echo '<p class="links"><a href="' . h(ME) . 'trigger=' . urlencode($b) . '">' . lang(131) . "</a>\n";
    }
} elseif (isset($_GET["schema"])) {
    page_header(lang(66), "", [], h(DB . ($_GET["ns"] ? ".$_GET[ns]" : "")));
    $og = [];
    $pg = [];
    $da = ($_GET["schema"] ? $_GET["schema"] : $_COOKIE["adminer_schema-" . str_replace(".", "_", DB)]);
    preg_match_all('~([^:]+):([-0-9.]+)x([-0-9.]+)(_|$)~', $da, $Ed, PREG_SET_ORDER);
    foreach ($Ed as $u => $D) {
        $og[$D[1]] = [
            $D[2],
            $D[3],
        ];
        $pg[] = "\n\t'" . js_escape($D[1]) . "': [ $D[2], $D[3] ]";
    }
    $Ig = 0;
    $Ca = -1;
    $Ef = [];
    $qf = [];
    $vd = [];
    foreach (table_status('', true) as $Q => $R) {
        if (is_view($R)) {
            continue;
        }
        $Te = 0;
        $Ef[$Q]["fields"] = [];
        foreach (fields($Q) as $F => $n) {
            $Te += 1.25;
            $n["pos"] = $Te;
            $Ef[$Q]["fields"][$F] = $n;
        }
        $Ef[$Q]["pos"] = ($og[$Q] ? $og[$Q] : [
            $Ig,
            0,
        ]);
        foreach ($c->foreignKeys($Q) as $X) {
            if (!$X["db"]) {
                $td = $Ca;
                if ($og[$Q][1] || $og[$X["table"]][1]) {
                    $td = min(floatval($og[$Q][1]), floatval($og[$X["table"]][1])) - 1;
                } else {
                    $Ca -= .1;
                }
                while ($vd[(string) $td]) {
                    $td -= .0001;
                }
                $Ef[$Q]["references"][$X["table"]][(string) $td] = [
                    $X["source"],
                    $X["target"],
                ];
                $qf[$X["table"]][$Q][(string) $td] = $X["target"];
                $vd[(string) $td] = true;
            }
        }
        $Ig = max($Ig, $Ef[$Q]["pos"][0] + 2.5 + $Te);
    }
    echo '<div id="schema" style="height: ', $Ig, 'em;">
<script', nonce(), '>
qs(\'#schema\').onselectstart = function () { return false; };
var tablePos = {', implode(",", $pg) . "\n", '};
var em = qs(\'#schema\').offsetHeight / ', $Ig, ';
document.onmousemove = schemaMousemove;
document.onmouseup = partialArg(schemaMouseup, \'', js_escape(DB), '\');
</script>
';
    foreach ($Ef as $F => $Q) {
        echo "<div class='table' style='top: " . $Q["pos"][0] . "em; left: " . $Q["pos"][1] . "em;'>", '<a href="' . h(ME) . 'table=' . urlencode($F) . '"><b>' . h($F) . "</b></a>", script("qsl('div').onmousedown = schemaMousedown;");
        foreach ($Q["fields"] as $n) {
            $X = '<span' . type_class($n["type"]) . ' title="' . h($n["full_type"] . ($n["null"] ? " NULL" : '')) . '">' . h($n["field"]) . '</span>';
            echo "<br>" . ($n["primary"] ? "<i>$X</i>" : $X);
        }
        foreach ((array) $Q["references"] as $vg => $rf) {
            foreach ($rf as $td => $nf) {
                $ud = $td - $og[$F][1];
                $u = 0;
                foreach ($nf[0] as $Uf) {
                    echo "\n<div class='references' title='" . h($vg) . "' id='refs$td-" . ($u++) . "' style='left: $ud" . "em; top: " . $Q["fields"][$Uf]["pos"] . "em; padding-top: .5em;'><div style='border-top: 1px solid Gray; width: " . (-$ud) . "em;'></div></div>";
                }
            }
        }
        foreach ((array) $qf[$F] as $vg => $rf) {
            foreach ($rf as $td => $e) {
                $ud = $td - $og[$F][1];
                $u = 0;
                foreach ($e as $ug) {
                    echo "\n<div class='references' title='" . h($vg) . "' id='refd$td-" . ($u++) . "' style='left: $ud" . "em; top: " . $Q["fields"][$ug]["pos"] . "em; height: 1.25em; background: url(" . h(preg_replace("~\\?.*~", "", ME) . "?file=arrow.gif) no-repeat right center;&version=4.6.3") . "'><div style='height: .5em; border-bottom: 1px solid Gray; width: " . (-$ud) . "em;'></div></div>";
                }
            }
        }
        echo "\n</div>\n";
    }
    foreach ($Ef as $F => $Q) {
        foreach ((array) $Q["references"] as $vg => $rf) {
            foreach ($rf as $td => $nf) {
                $Qd = $Ig;
                $Id = -10;
                foreach ($nf[0] as $_ => $Uf) {
                    $Ue = $Q["pos"][0] + $Q["fields"][$Uf]["pos"];
                    $Ve = $Ef[$vg]["pos"][0] + $Ef[$vg]["fields"][$nf[1][$_]]["pos"];
                    $Qd = min($Qd, $Ue, $Ve);
                    $Id = max($Id, $Ue, $Ve);
                }
                echo "<div class='references' id='refl$td' style='left: $td" . "em; top: $Qd" . "em; padding: .5em 0;'><div style='border-right: 1px solid Gray; margin-top: 1px; height: " . ($Id - $Qd) . "em;'></div></div>\n";
            }
        }
    }
    echo '</div>
<p class="links"><a href="', h(ME . "schema=" . urlencode($da)), '" id="schema-link">', lang(132), '</a>
';
} elseif (isset($_GET["dump"])) {
    $b = $_GET["dump"];
    if ($_POST && !$m) {
        $jb = "";
        foreach ([
                     "output",
                     "format",
                     "db_style",
                     "routines",
                     "events",
                     "table_style",
                     "auto_increment",
                     "triggers",
                     "data_style",
                 ] as $_) {
            $jb .= "&$_=" . urlencode($_POST[$_]);
        }
        cookie("adminer_export", substr($jb, 1));
        $S = array_flip((array) $_POST["tables"]) + array_flip((array) $_POST["data"]);
        $kc = dump_headers((count($S) == 1 ? key($S) : DB), (DB == "" || count($S) > 1));
        $gd = preg_match('~sql~', $_POST["format"]);
        if ($gd) {
            echo "-- Adminer $fa " . $Ib[DRIVER] . " dump\n\n";
            if ($z == "sql") {
                echo "SET NAMES utf8;
SET time_zone = '+00:00';
" . ($_POST["data_style"] ? "SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
" : "") . "
";
                $g->query("SET time_zone = '+00:00';");
            }
        }
        $gg = $_POST["db_style"];
        $j = [DB];
        if (DB == "") {
            $j = $_POST["databases"];
            if (is_string($j)) {
                $j = explode("\n", rtrim(str_replace("\r", "", $j), "\n"));
            }
        }
        foreach ((array) $j as $k) {
            $c->dumpDatabase($k);
            if ($g->select_db($k)) {
                if ($gd && preg_match('~CREATE~', $gg) && ($i = $g->result("SHOW CREATE DATABASE " . idf_escape($k), 1))) {
                    set_utf8mb4($i);
                    if ($gg == "DROP+CREATE") {
                        echo "DROP DATABASE IF EXISTS " . idf_escape($k) . ";\n";
                    }
                    echo "$i;\n";
                }
                if ($gd) {
                    if ($gg) {
                        echo use_sql($k) . ";\n\n";
                    }
                    $Be = "";
                    if ($_POST["routines"]) {
                        foreach ([
                                     "FUNCTION",
                                     "PROCEDURE",
                                 ] as $_f) {
                            foreach (get_rows("SHOW $_f STATUS WHERE Db = " . q($k), null, "-- ") as $L) {
                                $i = remove_definer($g->result("SHOW CREATE $_f " . idf_escape($L["Name"]), 2));
                                set_utf8mb4($i);
                                $Be .= ($gg != 'DROP+CREATE' ? "DROP $_f IF EXISTS " . idf_escape($L["Name"]) . ";;\n" : "") . "$i;;\n\n";
                            }
                        }
                    }
                    if ($_POST["events"]) {
                        foreach (get_rows("SHOW EVENTS", null, "-- ") as $L) {
                            $i = remove_definer($g->result("SHOW CREATE EVENT " . idf_escape($L["Name"]), 3));
                            set_utf8mb4($i);
                            $Be .= ($gg != 'DROP+CREATE' ? "DROP EVENT IF EXISTS " . idf_escape($L["Name"]) . ";;\n" : "") . "$i;;\n\n";
                        }
                    }
                    if ($Be) {
                        echo "DELIMITER ;;\n\n$Be" . "DELIMITER ;\n\n";
                    }
                }
                if ($_POST["table_style"] || $_POST["data_style"]) {
                    $oh = [];
                    foreach (table_status('', true) as $F => $R) {
                        $Q = (DB == "" || in_array($F, (array) $_POST["tables"]));
                        $rb = (DB == "" || in_array($F, (array) $_POST["data"]));
                        if ($Q || $rb) {
                            if ($kc == "tar") {
                                $Gg = new
                                TmpFile;
                                ob_start([
                                    $Gg,
                                    'write',
                                ], 1e5);
                            }
                            $c->dumpTable($F, ($Q ? $_POST["table_style"] : ""), (is_view($R) ? 2 : 0));
                            if (is_view($R)) {
                                $oh[] = $F;
                            } elseif ($rb) {
                                $o = fields($F);
                                $c->dumpData($F, $_POST["data_style"], "SELECT *" . convert_fields($o, $o) . " FROM " . table($F));
                            }
                            if ($gd && $_POST["triggers"] && $Q && ($Qg = trigger_sql($F))) {
                                echo "\nDELIMITER ;;\n$Qg\nDELIMITER ;\n";
                            }
                            if ($kc == "tar") {
                                ob_end_flush();
                                tar_file((DB != "" ? "" : "$k/") . "$F.csv", $Gg);
                            } elseif ($gd) {
                                echo "\n";
                            }
                        }
                    }
                    foreach ($oh as $nh) {
                        $c->dumpTable($nh, $_POST["table_style"], 1);
                    }
                    if ($kc == "tar") {
                        echo pack("x512");
                    }
                }
            }
        }
        if ($gd) {
            echo "-- " . $g->result("SELECT NOW()") . "\n";
        }
        exit;
    }
    page_header(lang(69), $m, ($_GET["export"] != "" ? ["table" => $_GET["export"]] : []), h(DB));
    echo '
<form action="" method="post">
<table cellspacing="0">
';
    $vb = [
        '',
        'USE',
        'DROP+CREATE',
        'CREATE',
    ];
    $qg = [
        '',
        'DROP+CREATE',
        'CREATE',
    ];
    $sb = [
        '',
        'TRUNCATE+INSERT',
        'INSERT',
    ];
    if ($z == "sql") {
        $sb[] = 'INSERT+UPDATE';
    }
    parse_str($_COOKIE["adminer_export"], $L);
    if (!$L) {
        $L = [
            "output"      => "text",
            "format"      => "sql",
            "db_style"    => (DB != "" ? "" : "CREATE"),
            "table_style" => "DROP+CREATE",
            "data_style"  => "INSERT",
        ];
    }
    if (!isset($L["events"])) {
        $L["routines"] = $L["events"] = ($_GET["dump"] == "");
        $L["triggers"] = $L["table_style"];
    }
    echo "<tr><th>" . lang(133) . "<td>" . html_select("output", $c->dumpOutput(), $L["output"], 0) . "\n";
    echo "<tr><th>" . lang(134) . "<td>" . html_select("format", $c->dumpFormat(), $L["format"], 0) . "\n";
    echo($z == "sqlite" ? "" : "<tr><th>" . lang(33) . "<td>" . html_select('db_style', $vb, $L["db_style"]) . (support("routine") ? checkbox("routines", 1, $L["routines"], lang(135)) : "") . (support("event") ? checkbox("events", 1, $L["events"], lang(136)) : "")), "<tr><th>" . lang(116) . "<td>" . html_select('table_style', $qg, $L["table_style"]) . checkbox("auto_increment", 1, $L["auto_increment"], lang(47)) . (support("trigger") ? checkbox("triggers", 1, $L["triggers"], lang(130)) : ""), "<tr><th>" . lang(137) . "<td>" . html_select('data_style', $sb, $L["data_style"]), '</table>
<p><input type="submit" value="', lang(69), '">
<input type="hidden" name="token" value="', $T, '">

<table cellspacing="0">
', script("qsl('table').onclick = dumpClick;");
    $Ye = [];
    if (DB != "") {
        $Na = ($b != "" ? "" : " checked");
        echo "<thead><tr>", "<th style='text-align: left;'><label class='block'><input type='checkbox' id='check-tables'$Na>" . lang(116) . "</label>" . script("qs('#check-tables').onclick = partial(formCheck, /^tables\\[/);", ""), "<th style='text-align: right;'><label class='block'>" . lang(137) . "<input type='checkbox' id='check-data'$Na></label>" . script("qs('#check-data').onclick = partial(formCheck, /^data\\[/);", ""), "</thead>\n";
        $oh = "";
        $rg = tables_list();
        foreach ($rg as $F => $U) {
            $Xe = preg_replace('~_.*~', '', $F);
            $Na = ($b == "" || $b == (substr($b, -1) == "%" ? "$Xe%" : $F));
            $af = "<tr><td>" . checkbox("tables[]", $F, $Na, $F, "", "block");
            if ($U !== null && !preg_match('~table~i', $U)) {
                $oh .= "$af\n";
            } else {
                echo "$af<td align='right'><label class='block'><span id='Rows-" . h($F) . "'></span>" . checkbox("data[]", $F, $Na) . "</label>\n";
            }
            $Ye[$Xe]++;
        }
        echo $oh;
        if ($rg) {
            echo script("ajaxSetHtml('" . js_escape(ME) . "script=db');");
        }
    } else {
        echo "<thead><tr><th style='text-align: left;'>", "<label class='block'><input type='checkbox' id='check-databases'" . ($b == "" ? " checked" : "") . ">" . lang(33) . "</label>", script("qs('#check-databases').onclick = partial(formCheck, /^databases\\[/);", ""), "</thead>\n";
        $j = $c->databases();
        if ($j) {
            foreach ($j as $k) {
                if (!information_schema($k)) {
                    $Xe = preg_replace('~_.*~', '', $k);
                    echo "<tr><td>" . checkbox("databases[]", $k, $b == "" || $b == "$Xe%", $k, "", "block") . "\n";
                    $Ye[$Xe]++;
                }
            }
        } else {
            echo "<tr><td><textarea name='databases' rows='10' cols='20'></textarea>";
        }
    }
    echo '</table>
</form>
';
    $uc = true;
    foreach ($Ye as $_ => $X) {
        if ($_ != "" && $X > 1) {
            echo ($uc ? "<p>" : " ") . "<a href='" . h(ME) . "dump=" . urlencode("$_%") . "'>" . h($_) . "</a>";
            $uc = false;
        }
    }
} elseif (isset($_GET["privileges"])) {
    page_header(lang(67));
    echo '<p class="links"><a href="' . h(ME) . 'user=">' . lang(138) . "</a>";
    $J = $g->query("SELECT User, Host FROM mysql." . (DB == "" ? "user" : "db WHERE " . q(DB) . " LIKE Db") . " ORDER BY Host, User");
    $Dc = $J;
    if (!$J) {
        $J = $g->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");
    }
    echo "<form action=''><p>\n";
    hidden_fields_get();
    echo "<input type='hidden' name='db' value='" . h(DB) . "'>\n", ($Dc ? "" : "<input type='hidden' name='grant' value=''>\n"), "<table cellspacing='0'>\n", "<thead><tr><th>" . lang(31) . "<th>" . lang(30) . "<th></thead>\n";
    while ($L = $J->fetch_assoc()) {
        echo '<tr' . odd() . '><td>' . h($L["User"]) . "<td>" . h($L["Host"]) . '<td><a href="' . h(ME . 'user=' . urlencode($L["User"]) . '&host=' . urlencode($L["Host"])) . '">' . lang(10) . "</a>\n";
    }
    if (!$Dc || DB != "") {
        echo "<tr" . odd() . "><td><input name='user' autocapitalize='off'><td><input name='host' value='localhost' autocapitalize='off'><td><input type='submit' value='" . lang(10) . "'>\n";
    }
    echo "</table>\n", "</form>\n";
} elseif (isset($_GET["sql"])) {
    if (!$m && $_POST["export"]) {
        dump_headers("sql");
        $c->dumpTable("", "");
        $c->dumpData("", "table", $_POST["query"]);
        exit;
    }
    restart_session();
    $Qc =& get_session("queries");
    $Pc =& $Qc[DB];
    if (!$m && $_POST["clear"]) {
        $Pc = [];
        redirect(remove_from_uri("history"));
    }
    page_header((isset($_GET["import"]) ? lang(68) : lang(60)), $m);
    if (!$m && $_POST) {
        $r = false;
        if (!isset($_GET["import"])) {
            $I = $_POST["query"];
        } elseif ($_POST["webfile"]) {
            $Xf = $c->importServerPath();
            $r = @fopen((file_exists($Xf) ? $Xf : "compress.zlib://$Xf.gz"), "rb");
            $I = ($r ? fread($r, 1e6) : false);
        } else {
            $I = get_file("sql_file", true);
        }
        if (is_string($I)) {
            if (function_exists('memory_get_usage')) {
                @ini_set("memory_limit", max(ini_bytes("memory_limit"), 2 * strlen($I) + memory_get_usage() + 8e6));
            }
            if ($I != "" && strlen($I) < 1e6) {
                $H = $I . (preg_match("~;[ \t\r\n]*\$~", $I) ? "" : ";");
                if (!$Pc || reset(end($Pc)) != $H) {
                    restart_session();
                    $Pc[] = [
                        $H,
                        time(),
                    ];
                    set_session("queries", $Qc);
                    stop_session();
                }
            }
            $Vf = "(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";
            $_b = ";";
            $ee = 0;
            $Wb = true;
            $h = connect();
            if (is_object($h) && DB != "") {
                $h->select_db(DB);
            }
            $ab = 0;
            $bc = [];
            $He = '[\'"' . ($z == "sql" ? '`#' : ($z == "sqlite" ? '`[' : ($z == "mssql" ? '[' : ''))) . ']|/\*|-- |$' . ($z == "pgsql" ? '|\$[^$]*\$' : '');
            $Jg = microtime(true);
            parse_str($_COOKIE["adminer_export"], $la);
            $Nb = $c->dumpFormat();
            unset($Nb["sql"]);
            while ($I != "") {
                if (!$ee && preg_match("~^$Vf*+DELIMITER\\s+(\\S+)~i", $I, $D)) {
                    $_b = $D[1];
                    $I = substr($I, strlen($D[0]));
                } else {
                    preg_match('(' . preg_quote($_b) . "\\s*|$He)", $I, $D, PREG_OFFSET_CAPTURE, $ee);
                    list($_c, $Te) = $D[0];
                    if (!$_c && $r && !feof($r)) {
                        $I .= fread($r, 1e5);
                    } else {
                        if (!$_c && rtrim($I) == "") {
                            break;
                        }
                        $ee = $Te + strlen($_c);
                        if ($_c && rtrim($_c) != $_b) {
                            while (preg_match('(' . ($_c == '/*' ? '\*/' : ($_c == '[' ? ']' : (preg_match('~^-- |^#~', $_c) ? "\n" : preg_quote($_c) . "|\\\\."))) . '|$)s', $I, $D, PREG_OFFSET_CAPTURE, $ee)) {
                                $Cf = $D[0][0];
                                if (!$Cf && $r && !feof($r)) {
                                    $I .= fread($r, 1e5);
                                } else {
                                    $ee = $D[0][1] + strlen($Cf);
                                    if ($Cf[0] != "\\") {
                                        break;
                                    }
                                }
                            }
                        } else {
                            $Wb = false;
                            $H = substr($I, 0, $Te);
                            $ab++;
                            $af = "<pre id='sql-$ab'><code class='jush-$z'>" . $c->sqlCommandQuery($H) . "</code></pre>\n";
                            if ($z == "sqlite" && preg_match("~^$Vf*+ATTACH\\b~i", $H, $D)) {
                                echo $af, "<p class='error'>" . lang(139) . "\n";
                                $bc[] = " <a href='#sql-$ab'>$ab</a>";
                                if ($_POST["error_stops"]) {
                                    break;
                                }
                            } else {
                                if (!$_POST["only_errors"]) {
                                    echo $af;
                                    ob_flush();
                                    flush();
                                }
                                $ag = microtime(true);
                                if ($g->multi_query($H) && is_object($h) && preg_match("~^$Vf*+USE\\b~i", $H)) {
                                    $h->query($H);
                                }
                                do {
                                    $J = $g->store_result();
                                    if ($g->error) {
                                        echo($_POST["only_errors"] ? $af : ""), "<p class='error'>" . lang(140) . ($g->errno ? " ($g->errno)" : "") . ": " . error() . "\n";
                                        $bc[] = " <a href='#sql-$ab'>$ab</a>";
                                        if ($_POST["error_stops"]) {
                                            break
                                            2;
                                        }
                                    } else {
                                        $_g = " <span class='time'>(" . format_time($ag) . ")</span>" . (strlen($H) < 1000 ? " <a href='" . h(ME) . "sql=" . urlencode(trim($H)) . "'>" . lang(10) . "</a>" : "");
                                        $na = $g->affected_rows;
                                        $rh = ($_POST["only_errors"] ? "" : $l->warnings());
                                        $sh = "warnings-$ab";
                                        if ($rh) {
                                            $_g .= ", <a href='#$sh'>" . lang(42) . "</a>" . script("qsl('a').onclick = partial(toggle, '$sh');", "");
                                        }
                                        $ic = null;
                                        $jc = "explain-$ab";
                                        if (is_object($J)) {
                                            $A = $_POST["limit"];
                                            $we = select($J, $h, [], $A);
                                            if (!$_POST["only_errors"]) {
                                                echo "<form action='' method='post'>\n";
                                                $be = $J->num_rows;
                                                echo "<p>" . ($be ? ($A && $be > $A ? lang(141, $A) : "") . lang(142, $be) : ""), $_g;
                                                if ($h && preg_match("~^($Vf|\\()*+SELECT\\b~i", $H) && ($ic = explain($h, $H))) {
                                                    echo ", <a href='#$jc'>Explain</a>" . script("qsl('a').onclick = partial(toggle, '$jc');", "");
                                                }
                                                $v = "export-$ab";
                                                echo ", <a href='#$v'>" . lang(69) . "</a>" . script("qsl('a').onclick = partial(toggle, '$v');", "") . "<span id='$v' class='hidden'>: " . html_select("output", $c->dumpOutput(), $la["output"]) . " " . html_select("format", $Nb, $la["format"]) . "<input type='hidden' name='query' value='" . h($H) . "'>" . " <input type='submit' name='export' value='" . lang(69) . "'><input type='hidden' name='token' value='$T'></span>\n" . "</form>\n";
                                            }
                                        } else {
                                            if (preg_match("~^$Vf*+(CREATE|DROP|ALTER)$Vf++(DATABASE|SCHEMA)\\b~i", $H)) {
                                                restart_session();
                                                set_session("dbs", null);
                                                stop_session();
                                            }
                                            if (!$_POST["only_errors"]) {
                                                echo "<p class='message' title='" . h($g->info) . "'>" . lang(143, $na) . "$_g\n";
                                            }
                                        }
                                        echo($rh ? "<div id='$sh' class='hidden'>\n$rh</div>\n" : "");
                                        if ($ic) {
                                            echo "<div id='$jc' class='hidden'>\n";
                                            select($ic, $h, $we);
                                            echo "</div>\n";
                                        }
                                    }
                                    $ag = microtime(true);
                                } while ($g->next_result());
                            }
                            $I = substr($I, $ee);
                            $ee = 0;
                        }
                    }
                }
            }
            if ($Wb) {
                echo "<p class='message'>" . lang(144) . "\n";
            } elseif ($_POST["only_errors"]) {
                echo "<p class='message'>" . lang(145, $ab - count($bc)), " <span class='time'>(" . format_time($Jg) . ")</span>\n";
            } elseif ($bc && $ab > 1) {
                echo "<p class='error'>" . lang(140) . ": " . implode("", $bc) . "\n";
            }
        } else {
            echo "<p class='error'>" . upload_error($I) . "\n";
        }
    }
    echo '
<form action="" method="post" enctype="multipart/form-data" id="form">
';
    $gc = "<input type='submit' value='" . lang(146) . "' title='Ctrl+Enter'>";
    if (!isset($_GET["import"])) {
        $H = $_GET["sql"];
        if ($_POST) {
            $H = $_POST["query"];
        } elseif ($_GET["history"] == "all") {
            $H = $Pc;
        } elseif ($_GET["history"] != "") {
            $H = $Pc[$_GET["history"]][0];
        }
        echo "<p>";
        textarea("query", $H, 20);
        echo($_POST ? "" : script("qs('textarea').focus();")), "<p>$gc\n", lang(147) . ": <input type='number' name='limit' class='size' value='" . h($_POST ? $_POST["limit"] : $_GET["limit"]) . "'>\n";
    } else {
        echo "<fieldset><legend>" . lang(148) . "</legend><div>";
        $Ic = (extension_loaded("zlib") ? "[.gz]" : "");
        echo(ini_bool("file_uploads") ? "SQL$Ic (&lt; " . ini_get("upload_max_filesize") . "B): <input type='file' name='sql_file[]' multiple>\n$gc" : lang(149)), "</div></fieldset>\n", "<fieldset><legend>" . lang(150) . "</legend><div>", lang(151, "<code>" . h($c->importServerPath()) . "$Ic</code>"), ' <input type="submit" name="webfile" value="' . lang(152) . '">', "</div></fieldset>\n", "<p>";
    }
    echo checkbox("error_stops", 1, ($_POST ? $_POST["error_stops"] : isset($_GET["import"])), lang(153)) . "\n", checkbox("only_errors", 1, ($_POST ? $_POST["only_errors"] : isset($_GET["import"])), lang(154)) . "\n", "<input type='hidden' name='token' value='$T'>\n";
    if (!isset($_GET["import"]) && $Pc) {
        print_fieldset("history", lang(155), $_GET["history"] != "");
        for ($X = end($Pc); $X; $X = prev($Pc)) {
            $_ = key($Pc);
            list($H, $_g, $Rb) = $X;
            echo '<a href="' . h(ME . "sql=&history=$_") . '">' . lang(10) . "</a>" . " <span class='time' title='" . @date('Y-m-d', $_g) . "'>" . @date("H:i:s", $_g) . "</span>" . " <code class='jush-$z'>" . shorten_utf8(ltrim(str_replace("\n", " ", str_replace("\r", "", preg_replace('~^(#|-- ).*~m', '', $H)))), 80, "</code>") . ($Rb ? " <span class='time'>($Rb)</span>" : "") . "<br>\n";
        }
        echo "<input type='submit' name='clear' value='" . lang(156) . "'>\n", "<a href='" . h(ME . "sql=&history=all") . "'>" . lang(157) . "</a>\n", "</div></fieldset>\n";
    }
    echo '</form>
';
} elseif (isset($_GET["edit"])) {
    $b = $_GET["edit"];
    $o = fields($b);
    $Z = (isset($_GET["select"]) ? ($_POST["check"] && count($_POST["check"]) == 1 ? where_check($_POST["check"][0], $o) : "") : where($_GET, $o));
    $bh = (isset($_GET["select"]) ? $_POST["edit"] : $Z);
    foreach ($o as $F => $n) {
        if (!isset($n["privileges"][$bh ? "update" : "insert"]) || $c->fieldName($n) == "") {
            unset($o[$F]);
        }
    }
    if ($_POST && !$m && !isset($_GET["select"])) {
        $C = $_POST["referer"];
        if ($_POST["insert"]) {
            $C = ($bh ? null : $_SERVER["REQUEST_URI"]);
        } elseif (!preg_match('~^.+&select=.+$~', $C)) {
            $C = ME . "select=" . urlencode($b);
        }
        $y = indexes($b);
        $Wg = unique_array($_GET["where"], $y);
        $jf = "\nWHERE $Z";
        if (isset($_POST["delete"])) {
            queries_redirect($C, lang(158), $l->delete($b, $jf, !$Wg));
        } else {
            $P = [];
            foreach ($o as $F => $n) {
                $X = process_input($n);
                if ($X !== false && $X !== null) {
                    $P[idf_escape($F)] = $X;
                }
            }
            if ($bh) {
                if (!$P) {
                    redirect($C);
                }
                queries_redirect($C, lang(159), $l->update($b, $P, $jf, !$Wg));
                if (is_ajax()) {
                    page_headers();
                    page_messages($m);
                    exit;
                }
            } else {
                $J = $l->insert($b, $P);
                $sd = ($J ? last_id() : 0);
                queries_redirect($C, lang(160, ($sd ? " $sd" : "")), $J);
            }
        }
    }
    $L = null;
    if ($_POST["save"]) {
        $L = (array) $_POST["fields"];
    } elseif ($Z) {
        $N = [];
        foreach ($o as $F => $n) {
            if (isset($n["privileges"]["select"])) {
                $ua = convert_field($n);
                if ($_POST["clone"] && $n["auto_increment"]) {
                    $ua = "''";
                }
                if ($z == "sql" && preg_match("~enum|set~", $n["type"])) {
                    $ua = "1*" . idf_escape($F);
                }
                $N[] = ($ua ? "$ua AS " : "") . idf_escape($F);
            }
        }
        $L = [];
        if (!support("table")) {
            $N = ["*"];
        }
        if ($N) {
            $J = $l->select($b, $N, [$Z], $N, [], (isset($_GET["select"]) ? 2 : 1));
            if (!$J) {
                $m = error();
            } else {
                $L = $J->fetch_assoc();
                if (!$L) {
                    $L = false;
                }
            }
            if (isset($_GET["select"]) && (!$L || $J->fetch_assoc())) {
                $L = null;
            }
        }
    }
    if (!support("table") && !$o) {
        if (!$Z) {
            $J = $l->select($b, ["*"], $Z, ["*"]);
            $L = ($J ? $J->fetch_assoc() : false);
            if (!$L) {
                $L = [$l->primary => ""];
            }
        }
        if ($L) {
            foreach ($L as $_ => $X) {
                if (!$Z) {
                    $L[$_] = null;
                }
                $o[$_] = [
                    "field"          => $_,
                    "null"           => ($_ != $l->primary),
                    "auto_increment" => ($_ == $l->primary),
                ];
            }
        }
    }
    edit_form($b, $o, $L, $bh);
} elseif (isset($_GET["create"])) {
    $b = $_GET["create"];
    $Ie = [];
    foreach ([
                 'HASH',
                 'LINEAR HASH',
                 'KEY',
                 'LINEAR KEY',
                 'RANGE',
                 'LIST',
             ] as $_) {
        $Ie[$_] = $_;
    }
    $pf = referencable_primary($b);
    $q = [];
    foreach ($pf as $ng => $n) {
        $q[str_replace("`", "``", $ng) . "`" . str_replace("`", "``", $n["field"])] = $ng;
    }
    $ze = [];
    $R = [];
    if ($b != "") {
        $ze = fields($b);
        $R = table_status($b);
        if (!$R) {
            $m = lang(9);
        }
    }
    $L = $_POST;
    $L["fields"] = (array) $L["fields"];
    if ($L["auto_increment_col"]) {
        $L["fields"][$L["auto_increment_col"]]["auto_increment"] = true;
    }
    if ($_POST && !process_fields($L["fields"]) && !$m) {
        if ($_POST["drop"]) {
            queries_redirect(substr(ME, 0, -1), lang(161), drop_tables([$b]));
        } else {
            $o = [];
            $ra = [];
            $fh = false;
            $xc = [];
            $ye = reset($ze);
            $pa = " FIRST";
            foreach ($L["fields"] as $_ => $n) {
                $p = $q[$n["type"]];
                $Rg = ($p !== null ? $pf[$p] : $n);
                if ($n["field"] != "") {
                    if (!$n["has_default"]) {
                        $n["default"] = null;
                    }
                    if ($_ == $L["auto_increment_col"]) {
                        $n["auto_increment"] = true;
                    }
                    $ff = process_field($n, $Rg);
                    $ra[] = [
                        $n["orig"],
                        $ff,
                        $pa,
                    ];
                    if ($ff != process_field($ye, $ye)) {
                        $o[] = [
                            $n["orig"],
                            $ff,
                            $pa,
                        ];
                        if ($n["orig"] != "" || $pa) {
                            $fh = true;
                        }
                    }
                    if ($p !== null) {
                        $xc[idf_escape($n["field"])] = ($b != "" && $z != "sqlite" ? "ADD" : " ") . format_foreign_key([
                                'table'     => $q[$n["type"]],
                                'source'    => [$n["field"]],
                                'target'    => [$Rg["field"]],
                                'on_delete' => $n["on_delete"],
                            ]);
                    }
                    $pa = " AFTER " . idf_escape($n["field"]);
                } elseif ($n["orig"] != "") {
                    $fh = true;
                    $o[] = [$n["orig"]];
                }
                if ($n["orig"] != "") {
                    $ye = next($ze);
                    if (!$ye) {
                        $pa = "";
                    }
                }
            }
            $Ke = "";
            if ($Ie[$L["partition_by"]]) {
                $Le = [];
                if ($L["partition_by"] == 'RANGE' || $L["partition_by"] == 'LIST') {
                    foreach (array_filter($L["partition_names"]) as $_ => $X) {
                        $Y = $L["partition_values"][$_];
                        $Le[] = "\n  PARTITION " . idf_escape($X) . " VALUES " . ($L["partition_by"] == 'RANGE' ? "LESS THAN" : "IN") . ($Y != "" ? " ($Y)" : " MAXVALUE");
                    }
                }
                $Ke .= "\nPARTITION BY $L[partition_by]($L[partition])" . ($Le ? " (" . implode(",", $Le) . "\n)" : ($L["partitions"] ? " PARTITIONS " . (+$L["partitions"]) : ""));
            } elseif (support("partitioning") && preg_match("~partitioned~", $R["Create_options"])) {
                $Ke .= "\nREMOVE PARTITIONING";
            }
            $E = lang(162);
            if ($b == "") {
                cookie("adminer_engine", $L["Engine"]);
                $E = lang(163);
            }
            $F = trim($L["name"]);
            queries_redirect(ME . (support("table") ? "table=" : "select=") . urlencode($F), $E, alter_table($b, $F, ($z == "sqlite" && ($fh || $xc) ? $ra : $o), $xc, ($L["Comment"] != $R["Comment"] ? $L["Comment"] : null), ($L["Engine"] && $L["Engine"] != $R["Engine"] ? $L["Engine"] : ""), ($L["Collation"] && $L["Collation"] != $R["Collation"] ? $L["Collation"] : ""), ($L["Auto_increment"] != "" ? number($L["Auto_increment"]) : ""), $Ke));
        }
    }
    page_header(($b != "" ? lang(40) : lang(70)), $m, ["table" => $b], h($b));
    if (!$_POST) {
        $L = [
            "Engine"          => $_COOKIE["adminer_engine"],
            "fields"          => [
                [
                    "field"     => "",
                    "type"      => (isset($Tg["int"]) ? "int" : (isset($Tg["integer"]) ? "integer" : "")),
                    "on_update" => "",
                ],
            ],
            "partition_names" => [""],
        ];
        if ($b != "") {
            $L = $R;
            $L["name"] = $b;
            $L["fields"] = [];
            if (!$_GET["auto_increment"]) {
                $L["Auto_increment"] = "";
            }
            foreach ($ze as $n) {
                $n["has_default"] = isset($n["default"]);
                $L["fields"][] = $n;
            }
            if (support("partitioning")) {
                $Bc = "FROM information_schema.PARTITIONS WHERE TABLE_SCHEMA = " . q(DB) . " AND TABLE_NAME = " . q($b);
                $J = $g->query("SELECT PARTITION_METHOD, PARTITION_ORDINAL_POSITION, PARTITION_EXPRESSION $Bc ORDER BY PARTITION_ORDINAL_POSITION DESC LIMIT 1");
                list($L["partition_by"], $L["partitions"], $L["partition"]) = $J->fetch_row();
                $Le = get_key_vals("SELECT PARTITION_NAME, PARTITION_DESCRIPTION $Bc AND PARTITION_NAME != '' ORDER BY PARTITION_ORDINAL_POSITION");
                $Le[""] = "";
                $L["partition_names"] = array_keys($Le);
                $L["partition_values"] = array_values($Le);
            }
        }
    }
    $Xa = collations();
    $Yb = engines();
    foreach ($Yb as $Xb) {
        if (!strcasecmp($Xb, $L["Engine"])) {
            $L["Engine"] = $Xb;
            break;
        }
    }
    echo '
<form action="" method="post" id="form">
<p>
';
    if (support("columns") || $b == "") {
        echo lang(164), ': <input name="name" maxlength="64" value="', h($L["name"]), '" autocapitalize="off">
';
        if ($b == "" && !$_POST) {
            echo script("focus(qs('#form')['name']);");
        }
        echo($Yb ? "<select name='Engine'>" . optionlist(["" => "(" . lang(165) . ")"] + $Yb, $L["Engine"]) . "</select>" . on_help("getTarget(event).value", 1) . script("qsl('select').onchange = helpClose;") : ""), ' ', ($Xa && !preg_match("~sqlite|mssql~", $z) ? html_select("Collation", ["" => "(" . lang(94) . ")"] + $Xa, $L["Collation"]) : ""), ' <input type="submit" value="', lang(14), '">
';
    }
    echo '
';
    if (support("columns")) {
        echo '<table cellspacing="0" id="edit-fields" class="nowrap">
';
        $cb = ($_POST ? $_POST["comments"] : $L["Comment"] != "");
        if (!$_POST && !$cb) {
            foreach ($L["fields"] as $n) {
                if ($n["comment"] != "") {
                    $cb = true;
                    break;
                }
            }
        }
        edit_fields($L["fields"], $Xa, "TABLE", $q, $cb);
        echo '</table>
<p>
', lang(47), ': <input type="number" name="Auto_increment" size="6" value="', h($L["Auto_increment"]), '">
', checkbox("defaults", 1, !$_POST || $_POST["defaults"], lang(166), "columnShow(this.checked, 5)", "jsonly"), ($_POST ? "" : script("editingHideDefaults();")), (support("comment") ? "<label><input type='checkbox' name='comments' value='1' class='jsonly'" . ($cb ? " checked" : "") . ">" . lang(46) . "</label>" . script("qsl('input').onclick = partial(editingCommentsClick, true);") . ' <input name="Comment" value="' . h($L["Comment"]) . '" maxlength="' . (min_version(5.5) ? 2048 : 60) . '"' . ($cb ? '' : ' class="hidden"') . '>' : ''), '<p>
<input type="submit" value="', lang(14), '">
';
    }
    echo '
';
    if ($b != "") {
        echo '<input type="submit" name="drop" value="', lang(120), '">', confirm(lang(167, $b));
    }
    if (support("partitioning")) {
        $Je = preg_match('~RANGE|LIST~', $L["partition_by"]);
        print_fieldset("partition", lang(168), $L["partition_by"]);
        echo '<p>
', "<select name='partition_by'>" . optionlist(["" => ""] + $Ie, $L["partition_by"]) . "</select>" . on_help("getTarget(event).value.replace(/./, 'PARTITION BY \$&')", 1) . script("qsl('select').onchange = partitionByChange;"), '(<input name="partition" value="', h($L["partition"]), '">)
', lang(169), ': <input type="number" name="partitions" class="size', ($Je || !$L["partition_by"] ? " hidden" : ""), '" value="', h($L["partitions"]), '">
<table cellspacing="0" id="partition-table"', ($Je ? "" : " class='hidden'"), '>
<thead><tr><th>', lang(170), '<th>', lang(171), '</thead>
';
        foreach ($L["partition_names"] as $_ => $X) {
            echo '<tr>', '<td><input name="partition_names[]" value="' . h($X) . '" autocapitalize="off">', ($_ == count($L["partition_names"]) - 1 ? script("qsl('input').oninput = partitionNameChange;") : ''), '<td><input name="partition_values[]" value="' . h($L["partition_values"][$_]) . '">';
        }
        echo '</table>
</div></fieldset>
';
    }
    echo '<input type="hidden" name="token" value="', $T, '">
</form>
', script("qs('#form')['defaults'].onclick();" . (support("comment") ? " editingCommentsClick.call(qs('#form')['comments']);" : ""));
} elseif (isset($_GET["indexes"])) {
    $b = $_GET["indexes"];
    $Wc = [
        "PRIMARY",
        "UNIQUE",
        "INDEX",
    ];
    $R = table_status($b, true);
    if (preg_match('~MyISAM|M?aria' . (min_version(5.6, '10.0.5') ? '|InnoDB' : '') . '~i', $R["Engine"])) {
        $Wc[] = "FULLTEXT";
    }
    if (preg_match('~MyISAM|M?aria' . (min_version(5.7, '10.2.2') ? '|InnoDB' : '') . '~i', $R["Engine"])) {
        $Wc[] = "SPATIAL";
    }
    $y = indexes($b);
    $Ze = [];
    if ($z == "mongo") {
        $Ze = $y["_id_"];
        unset($Wc[0]);
        unset($y["_id_"]);
    }
    $L = $_POST;
    if ($_POST && !$m && !$_POST["add"] && !$_POST["drop_col"]) {
        $sa = [];
        foreach ($L["indexes"] as $x) {
            $F = $x["name"];
            if (in_array($x["type"], $Wc)) {
                $e = [];
                $yd = [];
                $Bb = [];
                $P = [];
                ksort($x["columns"]);
                foreach ($x["columns"] as $_ => $d) {
                    if ($d != "") {
                        $xd = $x["lengths"][$_];
                        $Ab = $x["descs"][$_];
                        $P[] = idf_escape($d) . ($xd ? "(" . (+$xd) . ")" : "") . ($Ab ? " DESC" : "");
                        $e[] = $d;
                        $yd[] = ($xd ? $xd : null);
                        $Bb[] = $Ab;
                    }
                }
                if ($e) {
                    $hc = $y[$F];
                    if ($hc) {
                        ksort($hc["columns"]);
                        ksort($hc["lengths"]);
                        ksort($hc["descs"]);
                        if ($x["type"] == $hc["type"] && array_values($hc["columns"]) === $e && (!$hc["lengths"] || array_values($hc["lengths"]) === $yd) && array_values($hc["descs"]) === $Bb) {
                            unset($y[$F]);
                            continue;
                        }
                    }
                    $sa[] = [
                        $x["type"],
                        $F,
                        $P,
                    ];
                }
            }
        }
        foreach ($y as $F => $hc) {
            $sa[] = [
                $hc["type"],
                $F,
                "DROP",
            ];
        }
        if (!$sa) {
            redirect(ME . "table=" . urlencode($b));
        }
        queries_redirect(ME . "table=" . urlencode($b), lang(172), alter_indexes($b, $sa));
    }
    page_header(lang(124), $m, ["table" => $b], h($b));
    $o = array_keys(fields($b));
    if ($_POST["add"]) {
        foreach ($L["indexes"] as $_ => $x) {
            if ($x["columns"][count($x["columns"])] != "") {
                $L["indexes"][$_]["columns"][] = "";
            }
        }
        $x = end($L["indexes"]);
        if ($x["type"] || array_filter($x["columns"], 'strlen')) {
            $L["indexes"][] = ["columns" => [1 => ""]];
        }
    }
    if (!$L) {
        foreach ($y as $_ => $x) {
            $y[$_]["name"] = $_;
            $y[$_]["columns"][] = "";
        }
        $y[] = ["columns" => [1 => ""]];
        $L["indexes"] = $y;
    }
    echo '
<form action="" method="post">
<table cellspacing="0" class="nowrap">
<thead><tr>
<th id="label-type">', lang(173), '<th><input type="submit" class="wayoff">', lang(174), '<th id="label-name">', lang(175), '<th><noscript>', "<input type='image' class='icon' name='add[0]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=plus.gif&version=4.6.3") . "' alt='+' title='" . lang(101) . "'>", '</noscript>
</thead>
';
    if ($Ze) {
        echo "<tr><td>PRIMARY<td>";
        foreach ($Ze["columns"] as $_ => $d) {
            echo select_input(" disabled", $o, $d), "<label><input disabled type='checkbox'>" . lang(55) . "</label> ";
        }
        echo "<td><td>\n";
    }
    $id = 1;
    foreach ($L["indexes"] as $x) {
        if (!$_POST["drop_col"] || $id != key($_POST["drop_col"])) {
            echo "<tr><td>" . html_select("indexes[$id][type]", [-1 => ""] + $Wc, $x["type"], ($id == count($L["indexes"]) ? "indexesAddRow.call(this);" : 1), "label-type"), "<td>";
            ksort($x["columns"]);
            $u = 1;
            foreach ($x["columns"] as $_ => $d) {
                echo "<span>" . select_input(" name='indexes[$id][columns][$u]' title='" . lang(44) . "'", ($o ? array_combine($o, $o) : $o), $d, "partial(" . ($u == count($x["columns"]) ? "indexesAddColumn" : "indexesChangeColumn") . ", '" . js_escape($z == "sql" ? "" : $_GET["indexes"] . "_") . "')"), ($z == "sql" || $z == "mssql" ? "<input type='number' name='indexes[$id][lengths][$u]' class='size' value='" . h($x["lengths"][$_]) . "' title='" . lang(99) . "'>" : ""), ($z != "sql" ? checkbox("indexes[$id][descs][$u]", 1, $x["descs"][$_], lang(55)) : ""), " </span>";
                $u++;
            }
            echo "<td><input name='indexes[$id][name]' value='" . h($x["name"]) . "' autocapitalize='off' aria-labelledby='label-name'>\n", "<td><input type='image' class='icon' name='drop_col[$id]' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=cross.gif&version=4.6.3") . "' alt='x' title='" . lang(104) . "'>" . script("qsl('input').onclick = partial(editingRemoveRow, 'indexes\$1[type]');");
        }
        $id++;
    }
    echo '</table>
<p>
<input type="submit" value="', lang(14), '">
<input type="hidden" name="token" value="', $T, '">
</form>
';
} elseif (isset($_GET["database"])) {
    $L = $_POST;
    if ($_POST && !$m && !isset($_POST["add_x"])) {
        $F = trim($L["name"]);
        if ($_POST["drop"]) {
            $_GET["db"] = "";
            queries_redirect(remove_from_uri("db|database"), lang(176), drop_databases([DB]));
        } elseif (DB !== $F) {
            if (DB != "") {
                $_GET["db"] = $F;
                queries_redirect(preg_replace('~\bdb=[^&]*&~', '', ME) . "db=" . urlencode($F), lang(177), rename_database($F, $L["collation"]));
            } else {
                $j = explode("\n", str_replace("\r", "", $F));
                $hg = true;
                $rd = "";
                foreach ($j as $k) {
                    if (count($j) == 1 || $k != "") {
                        if (!create_database($k, $L["collation"])) {
                            $hg = false;
                        }
                        $rd = $k;
                    }
                }
                restart_session();
                set_session("dbs", null);
                queries_redirect(ME . "db=" . urlencode($rd), lang(178), $hg);
            }
        } else {
            if (!$L["collation"]) {
                redirect(substr(ME, 0, -1));
            }
            query_redirect("ALTER DATABASE " . idf_escape($F) . (preg_match('~^[a-z0-9_]+$~i', $L["collation"]) ? " COLLATE $L[collation]" : ""), substr(ME, 0, -1), lang(179));
        }
    }
    page_header(DB != "" ? lang(63) : lang(108), $m, [], h(DB));
    $Xa = collations();
    $F = DB;
    if ($_POST) {
        $F = $L["name"];
    } elseif (DB != "") {
        $L["collation"] = db_collation(DB, $Xa);
    } elseif ($z == "sql") {
        foreach (get_vals("SHOW GRANTS") as $Dc) {
            if (preg_match('~ ON (`(([^\\\\`]|``|\\\\.)*)%`\.\*)?~', $Dc, $D) && $D[1]) {
                $F = stripcslashes(idf_unescape("`$D[2]`"));
                break;
            }
        }
    }
    echo '
<form action="" method="post">
<p>
', ($_POST["add_x"] || strpos($F, "\n") ? '<textarea id="name" name="name" rows="10" cols="40">' . h($F) . '</textarea><br>' : '<input name="name" id="name" value="' . h($F) . '" maxlength="64" autocapitalize="off">') . "\n" . ($Xa ? html_select("collation", ["" => "(" . lang(94) . ")"] + $Xa, $L["collation"]) . doc_link([
                'sql'     => "charset-charsets.html",
                'mariadb' => "supported-character-sets-and-collations/",
                'mssql'   => "ms187963.aspx",
            ]) : ""), script("focus(qs('#name'));"), '<input type="submit" value="', lang(14), '">
';
    if (DB != "") {
        echo "<input type='submit' name='drop' value='" . lang(120) . "'>" . confirm(lang(167, DB)) . "\n";
    } elseif (!$_POST["add_x"] && $_GET["db"] == "") {
        echo "<input type='image' class='icon' name='add' src='" . h(preg_replace("~\\?.*~", "", ME) . "?file=plus.gif&version=4.6.3") . "' alt='+' title='" . lang(101) . "'>\n";
    }
    echo '<input type="hidden" name="token" value="', $T, '">
</form>
';
} elseif (isset($_GET["call"])) {
    $ca = ($_GET["name"] ? $_GET["name"] : $_GET["call"]);
    page_header(lang(180) . ": " . h($ca), $m);
    $_f = routine($_GET["call"], (isset($_GET["callf"]) ? "FUNCTION" : "PROCEDURE"));
    $Vc = [];
    $Be = [];
    foreach ($_f["fields"] as $u => $n) {
        if (substr($n["inout"], -3) == "OUT") {
            $Be[$u] = "@" . idf_escape($n["field"]) . " AS " . idf_escape($n["field"]);
        }
        if (!$n["inout"] || substr($n["inout"], 0, 2) == "IN") {
            $Vc[] = $u;
        }
    }
    if (!$m && $_POST) {
        $Ja = [];
        foreach ($_f["fields"] as $_ => $n) {
            if (in_array($_, $Vc)) {
                $X = process_input($n);
                if ($X === false) {
                    $X = "''";
                }
                if (isset($Be[$_])) {
                    $g->query("SET @" . idf_escape($n["field"]) . " = $X");
                }
            }
            $Ja[] = (isset($Be[$_]) ? "@" . idf_escape($n["field"]) : $X);
        }
        $I = (isset($_GET["callf"]) ? "SELECT" : "CALL") . " " . table($ca) . "(" . implode(", ", $Ja) . ")";
        $ag = microtime(true);
        $J = $g->multi_query($I);
        $na = $g->affected_rows;
        echo $c->selectQuery($I, $ag, !$J);
        if (!$J) {
            echo "<p class='error'>" . error() . "\n";
        } else {
            $h = connect();
            if (is_object($h)) {
                $h->select_db(DB);
            }
            do {
                $J = $g->store_result();
                if (is_object($J)) {
                    select($J, $h);
                } else {
                    echo "<p class='message'>" . lang(181, $na) . "\n";
                }
            } while ($g->next_result());
            if ($Be) {
                select($g->query("SELECT " . implode(", ", $Be)));
            }
        }
    }
    echo '
<form action="" method="post">
';
    if ($Vc) {
        echo "<table cellspacing='0'>\n";
        foreach ($Vc as $_) {
            $n = $_f["fields"][$_];
            $F = $n["field"];
            echo "<tr><th>" . $c->fieldName($n);
            $Y = $_POST["fields"][$F];
            if ($Y != "") {
                if ($n["type"] == "enum") {
                    $Y = +$Y;
                }
                if ($n["type"] == "set") {
                    $Y = array_sum($Y);
                }
            }
            input($n, $Y, (string) $_POST["function"][$F]);
            echo "\n";
        }
        echo "</table>\n";
    }
    echo '<p>
<input type="submit" value="', lang(180), '">
<input type="hidden" name="token" value="', $T, '">
</form>
';
} elseif (isset($_GET["foreign"])) {
    $b = $_GET["foreign"];
    $F = $_GET["name"];
    $L = $_POST;
    if ($_POST && !$m && !$_POST["add"] && !$_POST["change"] && !$_POST["change-js"]) {
        $E = ($_POST["drop"] ? lang(182) : ($F != "" ? lang(183) : lang(184)));
        $C = ME . "table=" . urlencode($b);
        if (!$_POST["drop"]) {
            $L["source"] = array_filter($L["source"], 'strlen');
            ksort($L["source"]);
            $ug = [];
            foreach ($L["source"] as $_ => $X) {
                $ug[$_] = $L["target"][$_];
            }
            $L["target"] = $ug;
        }
        if ($z == "sqlite") {
            queries_redirect($C, $E, recreate_table($b, $b, [], [], [" $F" => ($_POST["drop"] ? "" : " " . format_foreign_key($L))]));
        } else {
            $sa = "ALTER TABLE " . table($b);
            $Jb = "\nDROP " . ($z == "sql" ? "FOREIGN KEY " : "CONSTRAINT ") . idf_escape($F);
            if ($_POST["drop"]) {
                query_redirect($sa . $Jb, $C, $E);
            } else {
                query_redirect($sa . ($F != "" ? "$Jb," : "") . "\nADD" . format_foreign_key($L), $C, $E);
                $m = lang(185) . "<br>$m";
            }
        }
    }
    page_header(lang(186), $m, ["table" => $b], h($b));
    if ($_POST) {
        ksort($L["source"]);
        if ($_POST["add"]) {
            $L["source"][] = "";
        } elseif ($_POST["change"] || $_POST["change-js"]) {
            $L["target"] = [];
        }
    } elseif ($F != "") {
        $q = foreign_keys($b);
        $L = $q[$F];
        $L["source"][] = "";
    } else {
        $L["table"] = $b;
        $L["source"] = [""];
    }
    $Uf = array_keys(fields($b));
    $ug = ($b === $L["table"] ? $Uf : array_keys(fields($L["table"])));
    $of = array_keys(array_filter(table_status('', true), 'fk_support'));
    echo '
<form action="" method="post">
<p>
';
    if ($L["db"] == "" && $L["ns"] == "") {
        echo lang(187), ':
', html_select("table", $of, $L["table"], "this.form['change-js'].value = '1'; this.form.submit();"), '<input type="hidden" name="change-js" value="">
<noscript><p><input type="submit" name="change" value="', lang(188), '"></noscript>
<table cellspacing="0">
<thead><tr><th id="label-source">', lang(126), '<th id="label-target">', lang(127), '</thead>
';
        $id = 0;
        foreach ($L["source"] as $_ => $X) {
            echo "<tr>", "<td>" . html_select("source[" . (+$_) . "]", [-1 => ""] + $Uf, $X, ($id == count($L["source"]) - 1 ? "foreignAddRow.call(this);" : 1), "label-source"), "<td>" . html_select("target[" . (+$_) . "]", $ug, $L["target"][$_], 1, "label-target");
            $id++;
        }
        echo '</table>
<p>
', lang(96), ': ', html_select("on_delete", [-1 => ""] + explode("|", $le), $L["on_delete"]), ' ', lang(95), ': ', html_select("on_update", [-1 => ""] + explode("|", $le), $L["on_update"]), doc_link([
            'sql'     => "innodb-foreign-key-constraints.html",
            'mariadb' => "foreign-keys/",
            'pgsql'   => "sql-createtable.html#SQL-CREATETABLE-REFERENCES",
            'mssql'   => "ms174979.aspx",
            'oracle'  => "clauses002.htm#sthref2903",
        ]), '<p>
<input type="submit" value="', lang(14), '">
<noscript><p><input type="submit" name="add" value="', lang(189), '"></noscript>
';
    }
    if ($F != "") {
        echo '<input type="submit" name="drop" value="', lang(120), '">', confirm(lang(167, $F));
    }
    echo '<input type="hidden" name="token" value="', $T, '">
</form>
';
} elseif (isset($_GET["view"])) {
    $b = $_GET["view"];
    $L = $_POST;
    $_e = "VIEW";
    if ($z == "pgsql" && $b != "") {
        $bg = table_status($b);
        $_e = strtoupper($bg["Engine"]);
    }
    if ($_POST && !$m) {
        $F = trim($L["name"]);
        $ua = " AS\n$L[select]";
        $C = ME . "table=" . urlencode($F);
        $E = lang(190);
        $U = ($_POST["materialized"] ? "MATERIALIZED VIEW" : "VIEW");
        if (!$_POST["drop"] && $b == $F && $z != "sqlite" && $U == "VIEW" && $_e == "VIEW") {
            query_redirect(($z == "mssql" ? "ALTER" : "CREATE OR REPLACE") . " VIEW " . table($F) . $ua, $C, $E);
        } else {
            $wg = $F . "_adminer_" . uniqid();
            drop_create("DROP $_e " . table($b), "CREATE $U " . table($F) . $ua, "DROP $U " . table($F), "CREATE $U " . table($wg) . $ua, "DROP $U " . table($wg), ($_POST["drop"] ? substr(ME, 0, -1) : $C), lang(191), $E, lang(192), $b, $F);
        }
    }
    if (!$_POST && $b != "") {
        $L = view($b);
        $L["name"] = $b;
        $L["materialized"] = ($_e != "VIEW");
        if (!$m) {
            $m = error();
        }
    }
    page_header(($b != "" ? lang(39) : lang(193)), $m, ["table" => $b], h($b));
    echo '
<form action="" method="post">
<p>', lang(175), ': <input name="name" value="', h($L["name"]), '" maxlength="64" autocapitalize="off">
', (support("materializedview") ? " " . checkbox("materialized", 1, $L["materialized"], lang(121)) : ""), '<p>';
    textarea("select", $L["select"]);
    echo '<p>
<input type="submit" value="', lang(14), '">
';
    if ($b != "") {
        echo '<input type="submit" name="drop" value="', lang(120), '">', confirm(lang(167, $b));
    }
    echo '<input type="hidden" name="token" value="', $T, '">
</form>
';
} elseif (isset($_GET["event"])) {
    $aa = $_GET["event"];
    $bd = [
        "YEAR",
        "QUARTER",
        "MONTH",
        "DAY",
        "HOUR",
        "MINUTE",
        "WEEK",
        "SECOND",
        "YEAR_MONTH",
        "DAY_HOUR",
        "DAY_MINUTE",
        "DAY_SECOND",
        "HOUR_MINUTE",
        "HOUR_SECOND",
        "MINUTE_SECOND",
    ];
    $cg = [
        "ENABLED"            => "ENABLE",
        "DISABLED"           => "DISABLE",
        "SLAVESIDE_DISABLED" => "DISABLE ON SLAVE",
    ];
    $L = $_POST;
    if ($_POST && !$m) {
        if ($_POST["drop"]) {
            query_redirect("DROP EVENT " . idf_escape($aa), substr(ME, 0, -1), lang(194));
        } elseif (in_array($L["INTERVAL_FIELD"], $bd) && isset($cg[$L["STATUS"]])) {
            $Df = "\nON SCHEDULE " . ($L["INTERVAL_VALUE"] ? "EVERY " . q($L["INTERVAL_VALUE"]) . " $L[INTERVAL_FIELD]" . ($L["STARTS"] ? " STARTS " . q($L["STARTS"]) : "") . ($L["ENDS"] ? " ENDS " . q($L["ENDS"]) : "") : "AT " . q($L["STARTS"])) . " ON COMPLETION" . ($L["ON_COMPLETION"] ? "" : " NOT") . " PRESERVE";
            queries_redirect(substr(ME, 0, -1), ($aa != "" ? lang(195) : lang(196)), queries(($aa != "" ? "ALTER EVENT " . idf_escape($aa) . $Df . ($aa != $L["EVENT_NAME"] ? "\nRENAME TO " . idf_escape($L["EVENT_NAME"]) : "") : "CREATE EVENT " . idf_escape($L["EVENT_NAME"]) . $Df) . "\n" . $cg[$L["STATUS"]] . " COMMENT " . q($L["EVENT_COMMENT"]) . rtrim(" DO\n$L[EVENT_DEFINITION]", ";") . ";"));
        }
    }
    page_header(($aa != "" ? lang(197) . ": " . h($aa) : lang(198)), $m);
    if (!$L && $aa != "") {
        $M = get_rows("SELECT * FROM information_schema.EVENTS WHERE EVENT_SCHEMA = " . q(DB) . " AND EVENT_NAME = " . q($aa));
        $L = reset($M);
    }
    echo '
<form action="" method="post">
<table cellspacing="0">
<tr><th>', lang(175), '<td><input name="EVENT_NAME" value="', h($L["EVENT_NAME"]), '" maxlength="64" autocapitalize="off">
<tr><th title="datetime">', lang(199), '<td><input name="STARTS" value="', h("$L[EXECUTE_AT]$L[STARTS]"), '">
<tr><th title="datetime">', lang(200), '<td><input name="ENDS" value="', h($L["ENDS"]), '">
<tr><th>', lang(201), '<td><input type="number" name="INTERVAL_VALUE" value="', h($L["INTERVAL_VALUE"]), '" class="size"> ', html_select("INTERVAL_FIELD", $bd, $L["INTERVAL_FIELD"]), '<tr><th>', lang(111), '<td>', html_select("STATUS", $cg, $L["STATUS"]), '<tr><th>', lang(46), '<td><input name="EVENT_COMMENT" value="', h($L["EVENT_COMMENT"]), '" maxlength="64">
<tr><th><td>', checkbox("ON_COMPLETION", "PRESERVE", $L["ON_COMPLETION"] == "PRESERVE", lang(202)), '</table>
<p>';
    textarea("EVENT_DEFINITION", $L["EVENT_DEFINITION"]);
    echo '<p>
<input type="submit" value="', lang(14), '">
';
    if ($aa != "") {
        echo '<input type="submit" name="drop" value="', lang(120), '">', confirm(lang(167, $aa));
    }
    echo '<input type="hidden" name="token" value="', $T, '">
</form>
';
} elseif (isset($_GET["procedure"])) {
    $ca = ($_GET["name"] ? $_GET["name"] : $_GET["procedure"]);
    $_f = (isset($_GET["function"]) ? "FUNCTION" : "PROCEDURE");
    $L = $_POST;
    $L["fields"] = (array) $L["fields"];
    if ($_POST && !process_fields($L["fields"]) && !$m) {
        $xe = routine($_GET["procedure"], $_f);
        $wg = "$L[name]_adminer_" . uniqid();
        drop_create("DROP $_f " . routine_id($ca, $xe), create_routine($_f, $L), "DROP $_f " . routine_id($L["name"], $L), create_routine($_f, ["name" => $wg] + $L), "DROP $_f " . routine_id($wg, $L), substr(ME, 0, -1), lang(203), lang(204), lang(205), $ca, $L["name"]);
    }
    page_header(($ca != "" ? (isset($_GET["function"]) ? lang(206) : lang(207)) . ": " . h($ca) : (isset($_GET["function"]) ? lang(208) : lang(209))), $m);
    if (!$_POST && $ca != "") {
        $L = routine($_GET["procedure"], $_f);
        $L["name"] = $ca;
    }
    $Xa = get_vals("SHOW CHARACTER SET");
    sort($Xa);
    $Af = routine_languages();
    echo '
<form action="" method="post" id="form">
<p>', lang(175), ': <input name="name" value="', h($L["name"]), '" maxlength="64" autocapitalize="off">
', ($Af ? lang(19) . ": " . html_select("language", $Af, $L["language"]) . "\n" : ""), '<input type="submit" value="', lang(14), '">
<table cellspacing="0" class="nowrap">
';
    edit_fields($L["fields"], $Xa, $_f);
    if (isset($_GET["function"])) {
        echo "<tr><td>" . lang(210);
        edit_type("returns", $L["returns"], $Xa, [], ($z == "pgsql" ? [
            "void",
            "trigger",
        ] : []));
    }
    echo '</table>
<p>';
    textarea("definition", $L["definition"]);
    echo '<p>
<input type="submit" value="', lang(14), '">
';
    if ($ca != "") {
        echo '<input type="submit" name="drop" value="', lang(120), '">', confirm(lang(167, $ca));
    }
    echo '<input type="hidden" name="token" value="', $T, '">
</form>
';
} elseif (isset($_GET["trigger"])) {
    $b = $_GET["trigger"];
    $F = $_GET["name"];
    $Pg = trigger_options();
    $L = (array) trigger($F) + ["Trigger" => $b . "_bi"];
    if ($_POST) {
        if (!$m && in_array($_POST["Timing"], $Pg["Timing"]) && in_array($_POST["Event"], $Pg["Event"]) && in_array($_POST["Type"], $Pg["Type"])) {
            $ke = " ON " . table($b);
            $Jb = "DROP TRIGGER " . idf_escape($F) . ($z == "pgsql" ? $ke : "");
            $C = ME . "table=" . urlencode($b);
            if ($_POST["drop"]) {
                query_redirect($Jb, $C, lang(211));
            } else {
                if ($F != "") {
                    queries($Jb);
                }
                queries_redirect($C, ($F != "" ? lang(212) : lang(213)), queries(create_trigger($ke, $_POST)));
                if ($F != "") {
                    queries(create_trigger($ke, $L + ["Type" => reset($Pg["Type"])]));
                }
            }
        }
        $L = $_POST;
    }
    page_header(($F != "" ? lang(214) . ": " . h($F) : lang(215)), $m, ["table" => $b]);
    echo '
<form action="" method="post" id="form">
<table cellspacing="0">
<tr><th>', lang(216), '<td>', html_select("Timing", $Pg["Timing"], $L["Timing"], "triggerChange(/^" . preg_quote($b, "/") . "_[ba][iud]$/, '" . js_escape($b) . "', this.form);"), '<tr><th>', lang(217), '<td>', html_select("Event", $Pg["Event"], $L["Event"], "this.form['Timing'].onchange();"), (in_array("UPDATE OF", $Pg["Event"]) ? " <input name='Of' value='" . h($L["Of"]) . "' class='hidden'>" : ""), '<tr><th>', lang(45), '<td>', html_select("Type", $Pg["Type"], $L["Type"]), '</table>
<p>', lang(175), ': <input name="Trigger" value="', h($L["Trigger"]), '" maxlength="64" autocapitalize="off">
', script("qs('#form')['Timing'].onchange();"), '<p>';
    textarea("Statement", $L["Statement"]);
    echo '<p>
<input type="submit" value="', lang(14), '">
';
    if ($F != "") {
        echo '<input type="submit" name="drop" value="', lang(120), '">', confirm(lang(167, $F));
    }
    echo '<input type="hidden" name="token" value="', $T, '">
</form>
';
} elseif (isset($_GET["user"])) {
    $ea = $_GET["user"];
    $df = ["" => ["All privileges" => ""]];
    foreach (get_rows("SHOW PRIVILEGES") as $L) {
        foreach (explode(",", ($L["Privilege"] == "Grant option" ? "" : $L["Context"])) as $hb) {
            $df[$hb][$L["Privilege"]] = $L["Comment"];
        }
    }
    $df["Server Admin"] += $df["File access on server"];
    $df["Databases"]["Create routine"] = $df["Procedures"]["Create routine"];
    unset($df["Procedures"]["Create routine"]);
    $df["Columns"] = [];
    foreach ([
                 "Select",
                 "Insert",
                 "Update",
                 "References",
             ] as $X) {
        $df["Columns"][$X] = $df["Tables"][$X];
    }
    unset($df["Server Admin"]["Usage"]);
    foreach ($df["Tables"] as $_ => $X) {
        unset($df["Databases"][$_]);
    }
    $Wd = [];
    if ($_POST) {
        foreach ($_POST["objects"] as $_ => $X) {
            $Wd[$X] = (array) $Wd[$X] + (array) $_POST["grants"][$_];
        }
    }
    $Ec = [];
    $ie = "";
    if (isset($_GET["host"]) && ($J = $g->query("SHOW GRANTS FOR " . q($ea) . "@" . q($_GET["host"])))) {
        while ($L = $J->fetch_row()) {
            if (preg_match('~GRANT (.*) ON (.*) TO ~', $L[0], $D) && preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~', $D[1], $Ed, PREG_SET_ORDER)) {
                foreach ($Ed as $X) {
                    if ($X[1] != "USAGE") {
                        $Ec["$D[2]$X[2]"][$X[1]] = true;
                    }
                    if (preg_match('~ WITH GRANT OPTION~', $L[0])) {
                        $Ec["$D[2]$X[2]"]["GRANT OPTION"] = true;
                    }
                }
            }
            if (preg_match("~ IDENTIFIED BY PASSWORD '([^']+)~", $L[0], $D)) {
                $ie = $D[1];
            }
        }
    }
    if ($_POST && !$m) {
        $je = (isset($_GET["host"]) ? q($ea) . "@" . q($_GET["host"]) : "''");
        if ($_POST["drop"]) {
            query_redirect("DROP USER $je", ME . "privileges=", lang(218));
        } else {
            $Yd = q($_POST["user"]) . "@" . q($_POST["host"]);
            $Me = $_POST["pass"];
            if ($Me != '' && !$_POST["hashed"]) {
                $Me = $g->result("SELECT PASSWORD(" . q($Me) . ")");
                $m = !$Me;
            }
            $lb = false;
            if (!$m) {
                if ($je != $Yd) {
                    $lb = queries((min_version(5) ? "CREATE USER" : "GRANT USAGE ON *.* TO") . " $Yd IDENTIFIED BY PASSWORD " . q($Me));
                    $m = !$lb;
                } elseif ($Me != $ie) {
                    queries("SET PASSWORD FOR $Yd = " . q($Me));
                }
            }
            if (!$m) {
                $xf = [];
                foreach ($Wd as $de => $Dc) {
                    if (isset($_GET["grant"])) {
                        $Dc = array_filter($Dc);
                    }
                    $Dc = array_keys($Dc);
                    if (isset($_GET["grant"])) {
                        $xf = array_diff(array_keys(array_filter($Wd[$de], 'strlen')), $Dc);
                    } elseif ($je == $Yd) {
                        $ge = array_keys((array) $Ec[$de]);
                        $xf = array_diff($ge, $Dc);
                        $Dc = array_diff($Dc, $ge);
                        unset($Ec[$de]);
                    }
                    if (preg_match('~^(.+)\s*(\(.*\))?$~U', $de, $D) && (!grant("REVOKE", $xf, $D[2], " ON $D[1] FROM $Yd") || !grant("GRANT", $Dc, $D[2], " ON $D[1] TO $Yd"))) {
                        $m = true;
                        break;
                    }
                }
            }
            if (!$m && isset($_GET["host"])) {
                if ($je != $Yd) {
                    queries("DROP USER $je");
                } elseif (!isset($_GET["grant"])) {
                    foreach ($Ec as $de => $xf) {
                        if (preg_match('~^(.+)(\(.*\))?$~U', $de, $D)) {
                            grant("REVOKE", array_keys($xf), $D[2], " ON $D[1] FROM $Yd");
                        }
                    }
                }
            }
            queries_redirect(ME . "privileges=", (isset($_GET["host"]) ? lang(219) : lang(220)), !$m);
            if ($lb) {
                $g->query("DROP USER $Yd");
            }
        }
    }
    page_header((isset($_GET["host"]) ? lang(31) . ": " . h("$ea@$_GET[host]") : lang(138)), $m, [
        "privileges" => [
            '',
            lang(67),
        ],
    ]);
    if ($_POST) {
        $L = $_POST;
        $Ec = $Wd;
    } else {
        $L = $_GET + ["host" => $g->result("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', -1)")];
        $L["pass"] = $ie;
        if ($ie != "") {
            $L["hashed"] = true;
        }
        $Ec[(DB == "" || $Ec ? "" : idf_escape(addcslashes(DB, "%_\\"))) . ".*"] = [];
    }
    echo '<form action="" method="post">
<table cellspacing="0">
<tr><th>', lang(30), '<td><input name="host" maxlength="60" value="', h($L["host"]), '" autocapitalize="off">
<tr><th>', lang(31), '<td><input name="user" maxlength="16" value="', h($L["user"]), '" autocapitalize="off">
<tr><th>', lang(32), '<td><input name="pass" id="pass" value="', h($L["pass"]), '" autocomplete="new-password">
';
    if (!$L["hashed"]) {
        echo script("typePassword(qs('#pass'));");
    }
    echo checkbox("hashed", 1, $L["hashed"], lang(221), "typePassword(this.form['pass'], this.checked);"), '</table>

';
    echo "<table cellspacing='0'>\n", "<thead><tr><th colspan='2'>" . lang(67) . doc_link(['sql' => "grant.html#priv_level"]);
    $u = 0;
    foreach ($Ec as $de => $Dc) {
        echo '<th>' . ($de != "*.*" ? "<input name='objects[$u]' value='" . h($de) . "' size='10' autocapitalize='off'>" : "<input type='hidden' name='objects[$u]' value='*.*' size='10'>*.*");
        $u++;
    }
    echo "</thead>\n";
    foreach ([
                 ""             => "",
                 "Server Admin" => lang(30),
                 "Databases"    => lang(33),
                 "Tables"       => lang(123),
                 "Columns"      => lang(44),
                 "Procedures"   => lang(222),
             ] as $hb => $Ab) {
        foreach ((array) $df[$hb] as $cf => $bb) {
            echo "<tr" . odd() . "><td" . ($Ab ? ">$Ab<td" : " colspan='2'") . ' lang="en" title="' . h($bb) . '">' . h($cf);
            $u = 0;
            foreach ($Ec as $de => $Dc) {
                $F = "'grants[$u][" . h(strtoupper($cf)) . "]'";
                $Y = $Dc[strtoupper($cf)];
                if ($hb == "Server Admin" && $de != (isset($Ec["*.*"]) ? "*.*" : ".*")) {
                    echo "<td>";
                } elseif (isset($_GET["grant"])) {
                    echo "<td><select name=$F><option><option value='1'" . ($Y ? " selected" : "") . ">" . lang(223) . "<option value='0'" . ($Y == "0" ? " selected" : "") . ">" . lang(224) . "</select>";
                } else {
                    echo "<td align='center'><label class='block'>", "<input type='checkbox' name=$F value='1'" . ($Y ? " checked" : "") . ($cf == "All privileges" ? " id='grants-$u-all'>" : ">" . ($cf == "Grant option" ? "" : script("qsl('input').onclick = function () { if (this.checked) formUncheck('grants-$u-all'); };"))), "</label>";
                }
                $u++;
            }
        }
    }
    echo "</table>\n", '<p>
<input type="submit" value="', lang(14), '">
';
    if (isset($_GET["host"])) {
        echo '<input type="submit" name="drop" value="', lang(120), '">', confirm(lang(167, "$ea@$_GET[host]"));
    }
    echo '<input type="hidden" name="token" value="', $T, '">
</form>
';
} elseif (isset($_GET["processlist"])) {
    if (support("kill") && $_POST && !$m) {
        $md = 0;
        foreach ((array) $_POST["kill"] as $X) {
            if (kill_process($X)) {
                $md++;
            }
        }
        queries_redirect(ME . "processlist=", lang(225, $md), $md || !$_POST["kill"]);
    }
    page_header(lang(109), $m);
    echo '
<form action="" method="post">
<table cellspacing="0" class="nowrap checkable">
', script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});");
    $u = -1;
    foreach (process_list() as $u => $L) {
        if (!$u) {
            echo "<thead><tr lang='en'>" . (support("kill") ? "<th>" : "");
            foreach ($L as $_ => $X) {
                echo "<th>$_" . doc_link([
                        'sql'    => "show-processlist.html#processlist_" . strtolower($_),
                        'pgsql'  => "monitoring-stats.html#PG-STAT-ACTIVITY-VIEW",
                        'oracle' => "../b14237/dynviews_2088.htm",
                    ]);
            }
            echo "</thead>\n";
        }
        echo "<tr" . odd() . ">" . (support("kill") ? "<td>" . checkbox("kill[]", $L[$z == "sql" ? "Id" : "pid"], 0) : "");
        foreach ($L as $_ => $X) {
            echo "<td>" . (($z == "sql" && $_ == "Info" && preg_match("~Query|Killed~", $L["Command"]) && $X != "") || ($z == "pgsql" && $_ == "current_query" && $X != "<IDLE>") || ($z == "oracle" && $_ == "sql_text" && $X != "") ? "<code class='jush-$z'>" . shorten_utf8($X, 100, "</code>") . ' <a href="' . h(ME . ($L["db"] != "" ? "db=" . urlencode($L["db"]) . "&" : "") . "sql=" . urlencode($X)) . '">' . lang(226) . '</a>' : h($X));
        }
        echo "\n";
    }
    echo '</table>
<p>
';
    if (support("kill")) {
        echo ($u + 1) . "/" . lang(227, max_connections()), "<p><input type='submit' value='" . lang(228) . "'>\n";
    }
    echo '<input type="hidden" name="token" value="', $T, '">
</form>
', script("tableCheck();");
} elseif (isset($_GET["select"])) {
    $b = $_GET["select"];
    $R = table_status1($b);
    $y = indexes($b);
    $o = fields($b);
    $q = column_foreign_keys($b);
    $fe = $R["Oid"];
    parse_str($_COOKIE["adminer_import"], $ma);
    $yf = [];
    $e = [];
    $zg = null;
    foreach ($o as $_ => $n) {
        $F = $c->fieldName($n);
        if (isset($n["privileges"]["select"]) && $F != "") {
            $e[$_] = html_entity_decode(strip_tags($F), ENT_QUOTES);
            if (is_shortable($n)) {
                $zg = $c->selectLengthProcess();
            }
        }
        $yf += $n["privileges"];
    }
    list($N, $t) = $c->selectColumnsProcess($e, $y);
    $fd = count($t) < count($N);
    $Z = $c->selectSearchProcess($o, $y);
    $te = $c->selectOrderProcess($o, $y);
    $A = $c->selectLimitProcess();
    if ($_GET["val"] && is_ajax()) {
        header("Content-Type: text/plain; charset=utf-8");
        foreach ($_GET["val"] as $Xg => $L) {
            $ua = convert_field($o[key($L)]);
            $N = [$ua ? $ua : idf_escape(key($L))];
            $Z[] = where_check($Xg, $o);
            $K = $l->select($b, $N, $Z, $N);
            if ($K) {
                echo reset($K->fetch_row());
            }
        }
        exit;
    }
    $Ze = $Zg = null;
    foreach ($y as $x) {
        if ($x["type"] == "PRIMARY") {
            $Ze = array_flip($x["columns"]);
            $Zg = ($N ? $Ze : []);
            foreach ($Zg as $_ => $X) {
                if (in_array(idf_escape($_), $N)) {
                    unset($Zg[$_]);
                }
            }
            break;
        }
    }
    if ($fe && !$Ze) {
        $Ze = $Zg = [$fe => 0];
        $y[] = [
            "type"    => "PRIMARY",
            "columns" => [$fe],
        ];
    }
    if ($_POST && !$m) {
        $uh = $Z;
        if (!$_POST["all"] && is_array($_POST["check"])) {
            $Oa = [];
            foreach ($_POST["check"] as $Ma) {
                $Oa[] = where_check($Ma, $o);
            }
            $uh[] = "((" . implode(") OR (", $Oa) . "))";
        }
        $uh = ($uh ? "\nWHERE " . implode(" AND ", $uh) : "");
        if ($_POST["export"]) {
            cookie("adminer_import", "output=" . urlencode($_POST["output"]) . "&format=" . urlencode($_POST["format"]));
            dump_headers($b);
            $c->dumpTable($b, "");
            $Bc = ($N ? implode(", ", $N) : "*") . convert_fields($e, $o, $N) . "\nFROM " . table($b);
            $Gc = ($t && $fd ? "\nGROUP BY " . implode(", ", $t) : "") . ($te ? "\nORDER BY " . implode(", ", $te) : "");
            if (!is_array($_POST["check"]) || $Ze) {
                $I = "SELECT $Bc$uh$Gc";
            } else {
                $Vg = [];
                foreach ($_POST["check"] as $X) {
                    $Vg[] = "(SELECT" . limit($Bc, "\nWHERE " . ($Z ? implode(" AND ", $Z) . " AND " : "") . where_check($X, $o) . $Gc, 1) . ")";
                }
                $I = implode(" UNION ALL ", $Vg);
            }
            $c->dumpData($b, "table", $I);
            exit;
        }
        if (!$c->selectEmailProcess($Z, $q)) {
            if ($_POST["save"] || $_POST["delete"]) {
                $J = true;
                $na = 0;
                $P = [];
                if (!$_POST["delete"]) {
                    foreach ($e as $F => $X) {
                        $X = process_input($o[$F]);
                        if ($X !== null && ($_POST["clone"] || $X !== false)) {
                            $P[idf_escape($F)] = ($X !== false ? $X : idf_escape($F));
                        }
                    }
                }
                if ($_POST["delete"] || $P) {
                    if ($_POST["clone"]) {
                        $I = "INTO " . table($b) . " (" . implode(", ", array_keys($P)) . ")\nSELECT " . implode(", ", $P) . "\nFROM " . table($b);
                    }
                    if ($_POST["all"] || ($Ze && is_array($_POST["check"])) || $fd) {
                        $J = ($_POST["delete"] ? $l->delete($b, $uh) : ($_POST["clone"] ? queries("INSERT $I$uh") : $l->update($b, $P, $uh)));
                        $na = $g->affected_rows;
                    } else {
                        foreach ((array) $_POST["check"] as $X) {
                            $th = "\nWHERE " . ($Z ? implode(" AND ", $Z) . " AND " : "") . where_check($X, $o);
                            $J = ($_POST["delete"] ? $l->delete($b, $th, 1) : ($_POST["clone"] ? queries("INSERT" . limit1($b, $I, $th)) : $l->update($b, $P, $th, 1)));
                            if (!$J) {
                                break;
                            }
                            $na += $g->affected_rows;
                        }
                    }
                }
                $E = lang(229, $na);
                if ($_POST["clone"] && $J && $na == 1) {
                    $sd = last_id();
                    if ($sd) {
                        $E = lang(160, " $sd");
                    }
                }
                queries_redirect(remove_from_uri($_POST["all"] && $_POST["delete"] ? "page" : ""), $E, $J);
                if (!$_POST["delete"]) {
                    edit_form($b, $o, (array) $_POST["fields"], !$_POST["clone"]);
                    page_footer();
                    exit;
                }
            } elseif (!$_POST["import"]) {
                if (!$_POST["val"]) {
                    $m = lang(230);
                } else {
                    $J = true;
                    $na = 0;
                    foreach ($_POST["val"] as $Xg => $L) {
                        $P = [];
                        foreach ($L as $_ => $X) {
                            $_ = bracket_escape($_, 1);
                            $P[idf_escape($_)] = (preg_match('~char|text~', $o[$_]["type"]) || $X != "" ? $c->processInput($o[$_], $X) : "NULL");
                        }
                        $J = $l->update($b, $P, " WHERE " . ($Z ? implode(" AND ", $Z) . " AND " : "") . where_check($Xg, $o), !$fd && !$Ze, " ");
                        if (!$J) {
                            break;
                        }
                        $na += $g->affected_rows;
                    }
                    queries_redirect(remove_from_uri(), lang(229, $na), $J);
                }
            } elseif (!is_string($rc = get_file("csv_file", true))) {
                $m = upload_error($rc);
            } elseif (!preg_match('~~u', $rc)) {
                $m = lang(231);
            } else {
                cookie("adminer_import", "output=" . urlencode($ma["output"]) . "&format=" . urlencode($_POST["separator"]));
                $J = true;
                $Ya = array_keys($o);
                preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~', $rc, $Ed);
                $na = count($Ed[0]);
                $l->begin();
                $Lf = ($_POST["separator"] == "csv" ? "," : ($_POST["separator"] == "tsv" ? "\t" : ";"));
                $M = [];
                foreach ($Ed[0] as $_ => $X) {
                    preg_match_all("~((?>\"[^\"]*\")+|[^$Lf]*)$Lf~", $X . $Lf, $Fd);
                    if (!$_ && !array_diff($Fd[1], $Ya)) {
                        $Ya = $Fd[1];
                        $na--;
                    } else {
                        $P = [];
                        foreach ($Fd[1] as $u => $Ua) {
                            $P[idf_escape($Ya[$u])] = ($Ua == "" && $o[$Ya[$u]]["null"] ? "NULL" : q(str_replace('""', '"', preg_replace('~^"|"$~', '', $Ua))));
                        }
                        $M[] = $P;
                    }
                }
                $J = (!$M || $l->insertUpdate($b, $M, $Ze));
                if ($J) {
                    $J = $l->commit();
                }
                queries_redirect(remove_from_uri("page"), lang(232, $na), $J);
                $l->rollback();
            }
        }
    }
    $ng = $c->tableName($R);
    if (is_ajax()) {
        page_headers();
        ob_start();
    } else {
        page_header(lang(49) . ": $ng", $m);
    }
    $P = null;
    if (isset($yf["insert"]) || !support("table")) {
        $P = "";
        foreach ((array) $_GET["where"] as $X) {
            if ($q[$X["col"]] && count($q[$X["col"]]) == 1 && ($X["op"] == "=" || (!$X["op"] && !preg_match('~[_%]~', $X["val"])))) {
                $P .= "&set" . urlencode("[" . bracket_escape($X["col"]) . "]") . "=" . urlencode($X["val"]);
            }
        }
    }
    $c->selectLinks($R, $P);
    if (!$e && support("table")) {
        echo "<p class='error'>" . lang(233) . ($o ? "." : ": " . error()) . "\n";
    } else {
        echo "<form action='' id='form'>\n", "<div style='display: none;'>";
        hidden_fields_get();
        echo(DB != "" ? '<input type="hidden" name="db" value="' . h(DB) . '">' . (isset($_GET["ns"]) ? '<input type="hidden" name="ns" value="' . h($_GET["ns"]) . '">' : "") : "");
        echo '<input type="hidden" name="select" value="' . h($b) . '">', "</div>\n";
        $c->selectColumnsPrint($N, $e);
        $c->selectSearchPrint($Z, $e, $y);
        $c->selectOrderPrint($te, $e, $y);
        $c->selectLimitPrint($A);
        $c->selectLengthPrint($zg);
        $c->selectActionPrint($y);
        echo "</form>\n";
        $G = $_GET["page"];
        if ($G == "last") {
            $Ac = $g->result(count_rows($b, $Z, $fd, $t));
            $G = floor(max(0, $Ac - 1) / $A);
        }
        $Gf = $N;
        $Fc = $t;
        if (!$Gf) {
            $Gf[] = "*";
            $ib = convert_fields($e, $o, $N);
            if ($ib) {
                $Gf[] = substr($ib, 2);
            }
        }
        foreach ($N as $_ => $X) {
            $n = $o[idf_unescape($X)];
            if ($n && ($ua = convert_field($n))) {
                $Gf[$_] = "$ua AS $X";
            }
        }
        if (!$fd && $Zg) {
            foreach ($Zg as $_ => $X) {
                $Gf[] = idf_escape($_);
                if ($Fc) {
                    $Fc[] = idf_escape($_);
                }
            }
        }
        $J = $l->select($b, $Gf, $Z, $Fc, $te, $A, $G, true);
        if (!$J) {
            echo "<p class='error'>" . error() . "\n";
        } else {
            if ($z == "mssql" && $G) {
                $J->seek($A * $G);
            }
            $Vb = [];
            echo "<form action='' method='post' enctype='multipart/form-data'>\n";
            $M = [];
            while ($L = $J->fetch_assoc()) {
                if ($G && $z == "oracle") {
                    unset($L["RNUM"]);
                }
                $M[] = $L;
            }
            if ($_GET["page"] != "last" && $A != "" && $t && $fd && $z == "sql") {
                $Ac = $g->result(" SELECT FOUND_ROWS()");
            }
            if (!$M) {
                echo "<p class='message'>" . lang(12) . "\n";
            } else {
                $Ba = $c->backwardKeys($b, $ng);
                echo "<table id='table' cellspacing='0' class='nowrap checkable'>", script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"), "<thead><tr>" . (!$t && $N ? "" : "<td><input type='checkbox' id='all-page' class='jsonly'>" . script("qs('#all-page').onclick = partial(formCheck, /check/);", "") . " <a href='" . h($_GET["modify"] ? remove_from_uri("modify") : $_SERVER["REQUEST_URI"] . "&modify=1") . "'>" . lang(234) . "</a>");
                $Vd = [];
                $Cc = [];
                reset($N);
                $lf = 1;
                foreach ($M[0] as $_ => $X) {
                    if (!isset($Zg[$_])) {
                        $X = $_GET["columns"][key($N)];
                        $n = $o[$N ? ($X ? $X["col"] : current($N)) : $_];
                        $F = ($n ? $c->fieldName($n, $lf) : ($X["fun"] ? "*" : $_));
                        if ($F != "") {
                            $lf++;
                            $Vd[$_] = $F;
                            $d = idf_escape($_);
                            $Sc = remove_from_uri('(order|desc)[^=]*|page') . '&order%5B0%5D=' . urlencode($_);
                            $Ab = "&desc%5B0%5D=1";
                            echo "<th>" . script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});", ""), '<a href="' . h($Sc . ($te[0] == $d || $te[0] == $_ || (!$te && $fd && $t[0] == $d) ? $Ab : '')) . '">';
                            echo apply_sql_function($X["fun"], $F) . "</a>";
                            echo "<span class='column hidden'>", "<a href='" . h($Sc . $Ab) . "' title='" . lang(55) . "' class='text'> ↓</a>";
                            if (!$X["fun"]) {
                                echo '<a href="#fieldset-search" title="' . lang(52) . '" class="text jsonly"> =</a>', script("qsl('a').onclick = partial(selectSearch, '" . js_escape($_) . "');");
                            }
                            echo "</span>";
                        }
                        $Cc[$_] = $X["fun"];
                        next($N);
                    }
                }
                $yd = [];
                if ($_GET["modify"]) {
                    foreach ($M as $L) {
                        foreach ($L as $_ => $X) {
                            $yd[$_] = max($yd[$_], min(40, strlen(utf8_decode($X))));
                        }
                    }
                }
                echo ($Ba ? "<th>" . lang(235) : "") . "</thead>\n";
                if (is_ajax()) {
                    if ($A % 2 == 1 && $G % 2 == 1) {
                        odd();
                    }
                    ob_end_clean();
                }
                foreach ($c->rowDescriptions($M, $q) as $Ud => $L) {
                    $Wg = unique_array($M[$Ud], $y);
                    if (!$Wg) {
                        $Wg = [];
                        foreach ($M[$Ud] as $_ => $X) {
                            if (!preg_match('~^(COUNT\((\*|(DISTINCT )?`(?:[^`]|``)+`)\)|(AVG|GROUP_CONCAT|MAX|MIN|SUM)\(`(?:[^`]|``)+`\))$~', $_)) {
                                $Wg[$_] = $X;
                            }
                        }
                    }
                    $Xg = "";
                    foreach ($Wg as $_ => $X) {
                        if (($z == "sql" || $z == "pgsql") && preg_match('~char|text|enum|set~', $o[$_]["type"]) && strlen($X) > 64) {
                            $_ = (strpos($_, '(') ? $_ : idf_escape($_));
                            $_ = "MD5(" . ($z != 'sql' || preg_match("~^utf8~", $o[$_]["collation"]) ? $_ : "CONVERT($_ USING " . charset($g) . ")") . ")";
                            $X = md5($X);
                        }
                        $Xg .= "&" . ($X !== null ? urlencode("where[" . bracket_escape($_) . "]") . "=" . urlencode($X) : "null%5B%5D=" . urlencode($_));
                    }
                    echo "<tr" . odd() . ">" . (!$t && $N ? "" : "<td>" . checkbox("check[]", substr($Xg, 1), in_array(substr($Xg, 1), (array) $_POST["check"])) . ($fd || information_schema(DB) ? "" : " <a href='" . h(ME . "edit=" . urlencode($b) . $Xg) . "' class='edit'>" . lang(236) . "</a>"));
                    foreach ($L as $_ => $X) {
                        if (isset($Vd[$_])) {
                            $n = $o[$_];
                            $X = $l->value($X, $n);
                            if ($X != "" && (!isset($Vb[$_]) || $Vb[$_] != "")) {
                                $Vb[$_] = (is_mail($X) ? $Vd[$_] : "");
                            }
                            $B = "";
                            if (preg_match('~blob|bytea|raw|file~', $n["type"]) && $X != "") {
                                $B = ME . 'download=' . urlencode($b) . '&field=' . urlencode($_) . $Xg;
                            }
                            if (!$B && $X !== null) {
                                foreach ((array) $q[$_] as $p) {
                                    if (count($q[$_]) == 1 || end($p["source"]) == $_) {
                                        $B = "";
                                        foreach ($p["source"] as $u => $Uf) {
                                            $B .= where_link($u, $p["target"][$u], $M[$Ud][$Uf]);
                                        }
                                        $B = ($p["db"] != "" ? preg_replace('~([?&]db=)[^&]+~', '\1' . urlencode($p["db"]), ME) : ME) . 'select=' . urlencode($p["table"]) . $B;
                                        if ($p["ns"]) {
                                            $B = preg_replace('~([?&]ns=)[^&]+~', '\1' . urlencode($p["ns"]), $B);
                                        }
                                        if (count($p["source"]) == 1) {
                                            break;
                                        }
                                    }
                                }
                            }
                            if ($_ == "COUNT(*)") {
                                $B = ME . "select=" . urlencode($b);
                                $u = 0;
                                foreach ((array) $_GET["where"] as $W) {
                                    if (!array_key_exists($W["col"], $Wg)) {
                                        $B .= where_link($u++, $W["col"], $W["val"], $W["op"]);
                                    }
                                }
                                foreach ($Wg as $jd => $W) {
                                    $B .= where_link($u++, $jd, $W);
                                }
                            }
                            $X = select_value($X, $B, $n, $zg);
                            $v = h("val[$Xg][" . bracket_escape($_) . "]");
                            $Y = $_POST["val"][$Xg][bracket_escape($_)];
                            $Qb = !is_array($L[$_]) && is_utf8($X) && $M[$Ud][$_] == $L[$_] && !$Cc[$_];
                            $yg = preg_match('~text|lob~', $n["type"]);
                            if (($_GET["modify"] && $Qb) || $Y !== null) {
                                $Jc = h($Y !== null ? $Y : $L[$_]);
                                echo "<td>" . ($yg ? "<textarea name='$v' cols='30' rows='" . (substr_count($L[$_], "\n") + 1) . "'>$Jc</textarea>" : "<input name='$v' value='$Jc' size='$yd[$_]'>");
                            } else {
                                $Bd = strpos($X, "<i>...</i>");
                                echo "<td id='$v' data-text='" . ($Bd ? 2 : ($yg ? 1 : 0)) . "'" . ($Qb ? "" : " data-warning='" . h(lang(237)) . "'") . ">$X</td>";
                            }
                        }
                    }
                    if ($Ba) {
                        echo "<td>";
                    }
                    $c->backwardKeysPrint($Ba, $M[$Ud]);
                    echo "</tr>\n";
                }
                if (is_ajax()) {
                    exit;
                }
                echo "</table>\n";
            }
            if (!is_ajax()) {
                if ($M || $G) {
                    $fc = true;
                    if ($_GET["page"] != "last") {
                        if ($A == "" || (count($M) < $A && ($M || !$G))) {
                            $Ac = ($G ? $G * $A : 0) + count($M);
                        } elseif ($z != "sql" || !$fd) {
                            $Ac = ($fd ? false : found_rows($R, $Z));
                            if ($Ac < max(1e4, 2 * ($G + 1) * $A)) {
                                $Ac = reset(slow_query(count_rows($b, $Z, $fd, $t)));
                            } else {
                                $fc = false;
                            }
                        }
                    }
                    $Ee = ($A != "" && ($Ac === false || $Ac > $A || $G));
                    if ($Ee) {
                        echo(($Ac === false ? count($M) + 1 : $Ac - $G * $A) > $A ? '<p><a href="' . h(remove_from_uri("page") . "&page=" . ($G + 1)) . '" class="loadmore">' . lang(238) . '</a>' . script("qsl('a').onclick = partial(selectLoadMore, " . (+$A) . ", '" . lang(239) . "...');", "") : ''), "\n";
                    }
                }
                echo "<div class='footer'><div>\n";
                if ($M || $G) {
                    if ($Ee) {
                        $Hd = ($Ac === false ? $G + (count($M) >= $A ? 2 : 1) : floor(($Ac - 1) / $A));
                        echo "<fieldset>";
                        if ($z != "simpledb") {
                            echo "<legend><a href='" . h(remove_from_uri("page")) . "'>" . lang(240) . "</a></legend>", script("qsl('a').onclick = function () { pageClick(this.href, +prompt('" . lang(240) . "', '" . ($G + 1) . "')); return false; };"), pagination(0, $G) . ($G > 5 ? " ..." : "");
                            for ($u = max(1, $G - 4); $u < min($Hd, $G + 5); $u++) {
                                echo pagination($u, $G);
                            }
                            if ($Hd > 0) {
                                echo($G + 5 < $Hd ? " ..." : ""), ($fc && $Ac !== false ? pagination($Hd, $G) : " <a href='" . h(remove_from_uri("page") . "&page=last") . "' title='~$Hd'>" . lang(241) . "</a>");
                            }
                        } else {
                            echo "<legend>" . lang(240) . "</legend>", pagination(0, $G) . ($G > 1 ? " ..." : ""), ($G ? pagination($G, $G) : ""), ($Hd > $G ? pagination($G + 1, $G) . ($Hd > $G + 1 ? " ..." : "") : "");
                        }
                        echo "</fieldset>\n";
                    }
                    echo "<fieldset>", "<legend>" . lang(242) . "</legend>";
                    $Fb = ($fc ? "" : "~ ") . $Ac;
                    echo checkbox("all", 1, 0, ($Ac !== false ? ($fc ? "" : "~ ") . lang(142, $Ac) : ""), "var checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$Fb' : checked); selectCount('selected2', this.checked || !checked ? '$Fb' : checked);") . "\n", "</fieldset>\n";
                    if ($c->selectCommandPrint()) {
                        echo '<fieldset', ($_GET["modify"] ? '' : ' class="jsonly"'), '><legend>', lang(234), '</legend><div>
<input type="submit" value="', lang(14), '"', ($_GET["modify"] ? '' : ' title="' . lang(230) . '"'), '>
</div></fieldset>
<fieldset><legend>', lang(119), ' <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="', lang(10), '">
<input type="submit" name="clone" value="', lang(226), '">
<input type="submit" name="delete" value="', lang(18), '">', confirm(), '</div></fieldset>
';
                    }
                    $zc = $c->dumpFormat();
                    foreach ((array) $_GET["columns"] as $d) {
                        if ($d["fun"]) {
                            unset($zc['sql']);
                            break;
                        }
                    }
                    if ($zc) {
                        print_fieldset("export", lang(69) . " <span id='selected2'></span>");
                        $Ce = $c->dumpOutput();
                        echo($Ce ? html_select("output", $Ce, $ma["output"]) . " " : ""), html_select("format", $zc, $ma["format"]), " <input type='submit' name='export' value='" . lang(69) . "'>\n", "</div></fieldset>\n";
                    }
                    $c->selectEmailPrint(array_filter($Vb, 'strlen'), $e);
                }
                echo "</div></div>\n";
                if ($c->selectImportPrint()) {
                    echo "<div>", "<a href='#import'>" . lang(68) . "</a>", script("qsl('a').onclick = partial(toggle, 'import');", ""), "<span id='import' class='hidden'>: ", "<input type='file' name='csv_file'> ", html_select("separator", [
                        "csv"  => "CSV,",
                        "csv;" => "CSV;",
                        "tsv"  => "TSV",
                    ], $ma["format"], 1);
                    echo " <input type='submit' name='import' value='" . lang(68) . "'>", "</span>", "</div>";
                }
                echo "<input type='hidden' name='token' value='$T'>\n", "</form>\n", (!$t && $N ? "" : script("tableCheck();"));
            }
        }
    }
    if (is_ajax()) {
        ob_end_clean();
        exit;
    }
} elseif (isset($_GET["variables"])) {
    $bg = isset($_GET["status"]);
    page_header($bg ? lang(111) : lang(110));
    $kh = ($bg ? show_status() : show_variables());
    if (!$kh) {
        echo "<p class='message'>" . lang(12) . "\n";
    } else {
        echo "<table cellspacing='0'>\n";
        foreach ($kh as $_ => $X) {
            echo "<tr>", "<th><code class='jush-" . $z . ($bg ? "status" : "set") . "'>" . h($_) . "</code>", "<td>" . h($X);
        }
        echo "</table>\n";
    }
} elseif (isset($_GET["script"])) {
    header("Content-Type: text/javascript; charset=utf-8");
    if ($_GET["script"] == "db") {
        $kg = [
            "Data_length"  => 0,
            "Index_length" => 0,
            "Data_free"    => 0,
        ];
        foreach (table_status() as $F => $R) {
            json_row("Comment-$F", h($R["Comment"]));
            if (!is_view($R)) {
                foreach ([
                             "Engine",
                             "Collation",
                         ] as $_) {
                    json_row("$_-$F", h($R[$_]));
                }
                foreach ($kg + [
                    "Auto_increment" => 0,
                    "Rows"           => 0,
                ] as $_ => $X) {
                    if ($R[$_] != "") {
                        $X = format_number($R[$_]);
                        json_row("$_-$F", ($_ == "Rows" && $X && $R["Engine"] == ($Wf == "pgsql" ? "table" : "InnoDB") ? "~ $X" : $X));
                        if (isset($kg[$_])) {
                            $kg[$_] += ($R["Engine"] != "InnoDB" || $_ != "Data_free" ? $R[$_] : 0);
                        }
                    } elseif (array_key_exists($_, $R)) {
                        json_row("$_-$F");
                    }
                }
            }
        }
        foreach ($kg as $_ => $X) {
            json_row("sum-$_", format_number($X));
        }
        json_row("");
    } elseif ($_GET["script"] == "kill") {
        $g->query("KILL " . number($_POST["kill"]));
    } else {
        foreach (count_tables($c->databases()) as $k => $X) {
            json_row("tables-$k", $X);
            json_row("size-$k", db_size($k));
        }
        json_row("");
    }
    exit;
} else {
    $sg = array_merge((array) $_POST["tables"], (array) $_POST["views"]);
    if ($sg && !$m && !$_POST["search"]) {
        $J = true;
        $E = "";
        if ($z == "sql" && $_POST["tables"] && count($_POST["tables"]) > 1 && ($_POST["drop"] || $_POST["truncate"] || $_POST["copy"])) {
            queries("SET foreign_key_checks = 0");
        }
        if ($_POST["truncate"]) {
            if ($_POST["tables"]) {
                $J = truncate_tables($_POST["tables"]);
            }
            $E = lang(243);
        } elseif ($_POST["move"]) {
            $J = move_tables((array) $_POST["tables"], (array) $_POST["views"], $_POST["target"]);
            $E = lang(244);
        } elseif ($_POST["copy"]) {
            $J = copy_tables((array) $_POST["tables"], (array) $_POST["views"], $_POST["target"]);
            $E = lang(245);
        } elseif ($_POST["drop"]) {
            if ($_POST["views"]) {
                $J = drop_views($_POST["views"]);
            }
            if ($J && $_POST["tables"]) {
                $J = drop_tables($_POST["tables"]);
            }
            $E = lang(246);
        } elseif ($z != "sql") {
            $J = ($z == "sqlite" ? queries("VACUUM") : apply_queries("VACUUM" . ($_POST["optimize"] ? "" : " ANALYZE"), $_POST["tables"]));
            $E = lang(247);
        } elseif (!$_POST["tables"]) {
            $E = lang(9);
        } elseif ($J = queries(($_POST["optimize"] ? "OPTIMIZE" : ($_POST["check"] ? "CHECK" : ($_POST["repair"] ? "REPAIR" : "ANALYZE"))) . " TABLE " . implode(", ", array_map('idf_escape', $_POST["tables"])))) {
            while ($L = $J->fetch_assoc()) {
                $E .= "<b>" . h($L["Table"]) . "</b>: " . h($L["Msg_text"]) . "<br>";
            }
        }
        queries_redirect(substr(ME, 0, -1), $E, $J);
    }
    page_header(($_GET["ns"] == "" ? lang(33) . ": " . h(DB) : lang(248) . ": " . h($_GET["ns"])), $m, true);
    if ($c->homepage()) {
        if ($_GET["ns"] !== "") {
            echo "<h3 id='tables-views'>" . lang(249) . "</h3>\n";
            $rg = tables_list();
            if (!$rg) {
                echo "<p class='message'>" . lang(9) . "\n";
            } else {
                echo "<form action='' method='post'>\n";
                if (support("table")) {
                    echo "<fieldset><legend>" . lang(250) . " <span id='selected2'></span></legend><div>", "<input type='search' name='query' value='" . h($_POST["query"]) . "'>", script("qsl('input').onkeydown = partialArg(bodyKeydown, 'search');", ""), " <input type='submit' name='search' value='" . lang(52) . "'>\n", "</div></fieldset>\n";
                    if ($_POST["search"] && $_POST["query"] != "") {
                        $_GET["where"][0]["op"] = "LIKE %%";
                        search_tables();
                    }
                }
                $Gb = doc_link(['sql' => 'show-table-status.html']);
                echo "<table cellspacing='0' class='nowrap checkable'>\n", script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"), '<thead><tr class="wrap">', '<td><input id="check-all" type="checkbox" class="jsonly">' . script("qs('#check-all').onclick = partial(formCheck, /^(tables|views)\[/);", ""), '<th>' . lang(123), '<td>' . lang(251) . doc_link(['sql' => 'storage-engines.html']), '<td>' . lang(115) . doc_link([
                        'sql'     => 'charset-charsets.html',
                        'mariadb' => 'supported-character-sets-and-collations/',
                    ]), '<td>' . lang(252) . $Gb, '<td>' . lang(253) . $Gb, '<td>' . lang(254) . $Gb, '<td>' . lang(47) . doc_link([
                        'sql'     => 'example-auto-increment.html',
                        'mariadb' => 'auto_increment/',
                    ]), '<td>' . lang(255) . $Gb, (support("comment") ? '<td>' . lang(46) . $Gb : ''), "</thead>\n";
                $S = 0;
                foreach ($rg as $F => $U) {
                    $nh = ($U !== null && !preg_match('~table~i', $U));
                    $v = h("Table-" . $F);
                    echo '<tr' . odd() . '><td>' . checkbox(($nh ? "views[]" : "tables[]"), $F, in_array($F, $sg, true), "", "", "", $v), '<th>' . (support("table") || support("indexes") ? "<a href='" . h(ME) . "table=" . urlencode($F) . "' title='" . lang(38) . "' id='$v'>" . h($F) . '</a>' : h($F));
                    if ($nh) {
                        echo '<td colspan="6"><a href="' . h(ME) . "view=" . urlencode($F) . '" title="' . lang(39) . '">' . (preg_match('~materialized~i', $U) ? lang(121) : lang(122)) . '</a>', '<td align="right"><a href="' . h(ME) . "select=" . urlencode($F) . '" title="' . lang(37) . '">?</a>';
                    } else {
                        foreach ([
                                     "Engine"         => [],
                                     "Collation"      => [],
                                     "Data_length"    => [
                                         "create",
                                         lang(40),
                                     ],
                                     "Index_length"   => [
                                         "indexes",
                                         lang(125),
                                     ],
                                     "Data_free"      => [
                                         "edit",
                                         lang(41),
                                     ],
                                     "Auto_increment" => [
                                         "auto_increment=1&create",
                                         lang(40),
                                     ],
                                     "Rows"           => [
                                         "select",
                                         lang(37),
                                     ],
                                 ] as $_ => $B) {
                            $v = " id='$_-" . h($F) . "'";
                            echo($B ? "<td align='right'>" . (support("table") || $_ == "Rows" || (support("indexes") && $_ != "Data_length") ? "<a href='" . h(ME . "$B[0]=") . urlencode($F) . "'$v title='$B[1]'>?</a>" : "<span$v>?</span>") : "<td id='$_-" . h($F) . "'>");
                        }
                        $S++;
                    }
                    echo(support("comment") ? "<td id='Comment-" . h($F) . "'>" : "");
                }
                echo "<tr><td><th>" . lang(227, count($rg)), "<td>" . h($z == "sql" ? $g->result("SELECT @@storage_engine") : ""), "<td>" . h(db_collation(DB, collations()));
                foreach ([
                             "Data_length",
                             "Index_length",
                             "Data_free",
                         ] as $_) {
                    echo "<td align='right' id='sum-$_'>";
                }
                echo "</table>\n";
                if (!information_schema(DB)) {
                    echo "<div class='footer'><div>\n";
                    $ih = "<input type='submit' value='" . lang(256) . "'> " . on_help("'VACUUM'");
                    $qe = "<input type='submit' name='optimize' value='" . lang(257) . "'> " . on_help($z == "sql" ? "'OPTIMIZE TABLE'" : "'VACUUM OPTIMIZE'");
                    echo "<fieldset><legend>" . lang(119) . " <span id='selected'></span></legend><div>" . ($z == "sqlite" ? $ih : ($z == "pgsql" ? $ih . $qe : ($z == "sql" ? "<input type='submit' value='" . lang(258) . "'> " . on_help("'ANALYZE TABLE'") . $qe . "<input type='submit' name='check' value='" . lang(259) . "'> " . on_help("'CHECK TABLE'") . "<input type='submit' name='repair' value='" . lang(260) . "'> " . on_help("'REPAIR TABLE'") : ""))) . "<input type='submit' name='truncate' value='" . lang(261) . "'> " . on_help($z == "sqlite" ? "'DELETE'" : "'TRUNCATE" . ($z == "pgsql" ? "'" : " TABLE'")) . confirm() . "<input type='submit' name='drop' value='" . lang(120) . "'>" . on_help("'DROP TABLE'") . confirm() . "\n";
                    $j = (support("scheme") ? $c->schemas() : $c->databases());
                    if (count($j) != 1 && $z != "sqlite") {
                        $k = (isset($_POST["target"]) ? $_POST["target"] : (support("scheme") ? $_GET["ns"] : DB));
                        echo "<p>" . lang(262) . ": ", ($j ? html_select("target", $j, $k) : '<input name="target" value="' . h($k) . '" autocapitalize="off">'), " <input type='submit' name='move' value='" . lang(263) . "'>", (support("copy") ? " <input type='submit' name='copy' value='" . lang(264) . "'>" : ""), "\n";
                    }
                    echo "<input type='hidden' name='all' value=''>";
                    echo script("qsl('input').onclick = function () { selectCount('selected', formChecked(this, /^(tables|views)\[/));" . (support("table") ? " selectCount('selected2', formChecked(this, /^tables\[/) || $S);" : "") . " }"), "<input type='hidden' name='token' value='$T'>\n", "</div></fieldset>\n", "</div></div>\n";
                }
                echo "</form>\n", script("tableCheck();");
            }
            echo '<p class="links"><a href="' . h(ME) . 'create=">' . lang(70) . "</a>\n", (support("view") ? '<a href="' . h(ME) . 'view=">' . lang(193) . "</a>\n" : "");
            if (support("routine")) {
                echo "<h3 id='routines'>" . lang(135) . "</h3>\n";
                $Bf = routines();
                if ($Bf) {
                    echo "<table cellspacing='0'>\n", '<thead><tr><th>' . lang(175) . '<td>' . lang(45) . '<td>' . lang(210) . "<td></thead>\n";
                    odd('');
                    foreach ($Bf as $L) {
                        $F = ($L["SPECIFIC_NAME"] == $L["ROUTINE_NAME"] ? "" : "&name=" . urlencode($L["ROUTINE_NAME"]));
                        echo '<tr' . odd() . '>', '<th><a href="' . h(ME . ($L["ROUTINE_TYPE"] != "PROCEDURE" ? 'callf=' : 'call=') . urlencode($L["SPECIFIC_NAME"]) . $F) . '">' . h($L["ROUTINE_NAME"]) . '</a>', '<td>' . h($L["ROUTINE_TYPE"]), '<td>' . h($L["DTD_IDENTIFIER"]), '<td><a href="' . h(ME . ($L["ROUTINE_TYPE"] != "PROCEDURE" ? 'function=' : 'procedure=') . urlencode($L["SPECIFIC_NAME"]) . $F) . '">' . lang(128) . "</a>";
                    }
                    echo "</table>\n";
                }
                echo '<p class="links">' . (support("procedure") ? '<a href="' . h(ME) . 'procedure=">' . lang(209) . '</a>' : '') . '<a href="' . h(ME) . 'function=">' . lang(208) . "</a>\n";
            }
            if (support("event")) {
                echo "<h3 id='events'>" . lang(136) . "</h3>\n";
                $M = get_rows("SHOW EVENTS");
                if ($M) {
                    echo "<table cellspacing='0'>\n", "<thead><tr><th>" . lang(175) . "<td>" . lang(265) . "<td>" . lang(199) . "<td>" . lang(200) . "<td></thead>\n";
                    foreach ($M as $L) {
                        echo "<tr>", "<th>" . h($L["Name"]), "<td>" . ($L["Execute at"] ? lang(266) . "<td>" . $L["Execute at"] : lang(201) . " " . $L["Interval value"] . " " . $L["Interval field"] . "<td>$L[Starts]"), "<td>$L[Ends]", '<td><a href="' . h(ME) . 'event=' . urlencode($L["Name"]) . '">' . lang(128) . '</a>';
                    }
                    echo "</table>\n";
                    $dc = $g->result("SELECT @@event_scheduler");
                    if ($dc && $dc != "ON") {
                        echo "<p class='error'><code class='jush-sqlset'>event_scheduler</code>: " . h($dc) . "\n";
                    }
                }
                echo '<p class="links"><a href="' . h(ME) . 'event=">' . lang(198) . "</a>\n";
            }
            if ($rg) {
                echo script("ajaxSetHtml('" . js_escape(ME) . "script=db');");
            }
        }
    }
}
page_footer();
