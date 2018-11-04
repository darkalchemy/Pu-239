<?php

require_once 'function_menu.php';
require_once 'functions.php';

$pager = '';
$name = (isset($_GET['sub_name']) ? $_GET['sub_name'] : '');
if ($name) {
    $pager = 'sub_name=' . $name . '&amp;';
}
$searchby = (isset($_GET['searchby']) ? $_GET['searchby'] : '');
if ($searchby) {
    $pager .= 'searchby=' . $searchby . '&amp;';
}
$lang = (isset($_GET['lang']) ? $_GET['lang'] : 'all');
if ($lang) {
    $pager .= 'lang=' . $lang . '&amp;';
}
$fps = (isset($_GET['fps']) ? $_GET['fps'] : '');
if ($fps) {
    $pager .= 'fps=' . $fls . '&amp;';
}
$format = (isset($_GET['format']) ? $_GET['format'] : '');
if ($format) {
    $pager .= 'format=' . $format . '&amp;';
}
$cds = (isset($_GET['cds']) ? (int) $_GET['cds'] : '');
if ($cds) {
    $pager .= 'cds=' . $cds . '&amp;';
}
$offset = (isset($_GET['offset']) ? (int) $_GET['offset'] : '');

if ($searchby === 'name') {
    $name = str_replace([
        '.',
        '/',
        '"',
        '!',
        '-',
        '+',
        '_',
        '@',
        '#',
        '$',
        '%',
        '&',
        '^',
        '(',
        ')',
        '*',
    ], ' ', $name);
}

?>
<!doctype html>
<html>
<head>
    <!--[if lt IE 9]><script src='//html5shim.googlecode.com/svn/trunk/html5.js'></script><![endif]-->
    <meta charset='{$site_config['char_set']}'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta property='og:url' content='{$site_config['baseurl']}'>
    <meta property='og:type' content='website'>
    <meta property='og:title' content='{$title}'>
    <meta property='og:description' content='{$site_config['domain']} - {$site_config['site_name']}'>
    <title>Subtitle Search</title>
    <style>
        body {
            font-family: "tahoma", "arial", "helvetica", "sans-serif";
            font-size: 9pt;
            background-color: #333333;
            color: #fff;
            font-weight: bold;
            padding-top: 15px;
            padding-bottom: 20px;
        }

        fieldset {
            border: 1px solid #666666;
            width: 40%;
        }

        legend {
            border: none;
            padding: 5px 5px 5px 5px
        }

        input, select, textarea {
            font-size: 8pt;
            background: #CCCCCC;
            color: #333333;
            border: solid 1px #666666;
        }

        input:hover, select:hover, textarea:hover {
            border: solid 1px #999999;
        }

        input[type=button], input[type=submit], input[type=reset] {
            font-weight: bold;
            margin: 5px;
        }

        a:link, a:hover, a:visited {
            color: #fff;
        }

        .releasename {
            font-size: 8pt;
            color: #cccccc;
        }

        .sublink {
            border: 1px solid #222222;
            text-decoration: none;
            background-color: #999999;
            color: #333333;
            margin: 4px
            font-weight: bold;
            padding: 0 5px;
            width: 15px;
            height: 15px;
        }

        .sublink:hover, a.sublink:active {
            border: 1px solid #cccccc;
            color: #333333;
        }

        .sublink-active {
            border: 1px solid #cccccc;
            background-color: #999999;
            margin: 4px
            font-weight: bold;
            padding: 0 5px;
        }
    </style>
</head>
<body>
<div>
    <fieldset>
        <legend>Search for subtitle</legend>
        <form action=" " method="get">
            <table width="40%" border="1" style="border-collapse:collapse"
            >
                <tr>
                    <td nowrap="nowrap">Search</td>
                    <td colspan="7" nowrap="nowrap"><input type="text" name="sub_name"
                                                           value="<?php echo $name ? $name : ''; ?>"
                                                           size="80"/>
                        &#160;by&#160;
                        <select name="searchby">
                            <option value="name" <?php echo $searchby === 'name' ? 'selected' : ''; ?>>Name
                            </option>
                            <option value="imdb" <?php echo $searchby === 'imdb' ? 'selected' : ''; ?>>IMDb
                                id
                            </option>
                        </select></td>
                </tr>
                <tr>
                    <td nowrap="nowrap">Subtitle format</td>
                    <td><?php echo build_menu('format', $format_menu, $format); ?></td>
                    <td nowrap="nowrap">Cds</td>
                    <td><?php echo build_menu('cds', $cds_menu, $cds); ?></td>
                    <td nowrap="nowrap">Language</td>
                    <td><?php echo build_menu('lang', $lang_menu, $lang); ?></td>
                    <td nowrap="nowrap">FPS</td>
                    <td><?php echo build_menu('fps', $fps_menu, $fps); ?></td>
                </tr>
                <tr>
                    <td colspan="8"><input type="submit" value="Search"/></td>
                </tr>
            </table>
        </form>
    </fieldset>
    <br>

    <?php

    if (!empty($name)) {
        $search = xmlconvert(requestXML($name, $searchby, $lang, $cds, $format, $fps, $offset));
        echo build_result($search, '?' . $pager);
    }

    ?>
</div>
</body>
</html>
