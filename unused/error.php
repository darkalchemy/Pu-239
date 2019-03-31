<?php

require_once __DIR__ . '/include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
global $site_config;

$string = $_SERVER['QUERY_STRING'];
if ($string === '404') {
    $page = 'Page Not Found - 404';
    $text = 'Sorry, The page you are looking for cannot be found.';
}
if ($string === '401') {
    $page = 'Authorization Required - 401';
    $text = 'You need to be Authorized to access this page. You do not have the correct credentials.';
}
if ($string === '403') {
    $page = 'Forbidden - 403';
    $text = 'You do not have full permission to access this page.';
}
if ($string === '500') {
    $page = 'Internal Server Error - 500';
    $text = 'There seems to have been an error on this server. Please notify the webmaster of the site.';
}
if ($string === '400') {
    $page = 'Bad Request - 400';
    $text = 'There has been an error with the page you are trying to view. Please try again later.';
}
$domain = htmlsafechars($_SERVER['HTTP_HOST']);
$htmlout = doc_head() . "
    <meta property='og:title' content='{$page}'>
    <title>{$page}</title>
    <style>
    <!--
    body     {
    margin: 4px;
    background-color: rgb(255,255,255);
    }
    p
    {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    color: #000000;
    font-size: 14px;
    }
    .style1 {    color: #666666;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 12px;
    }
    -->
    </style>
    </head>
    <body>
    <div>
    <table width='300'>
    <!--Error pic layout-->
    <tr>
    <td width='300' height='50'><!--Error 404 EmptyCell-->&#160;</td>
    </tr>
    <tr>
    <td height='520'><img src='{$site_config['pic_baseurl']}error404.png' alt='Error Not Found' width='300' height='520' usemap='#Map'></td>
    </tr>
    <tr>
    <td height='14'><div><span class='style1'></span></div></td>
    </tr>
    </table></div><map name='Map' id='map'>
    <area shape='rect' coords='99,425,203,481' alt='Error Not Found'>
    </map>
    <p><b>{$page}</b></p>
    <p>{$text}</p><br>
    <p>You will be redirected back to {$domain} in 5 seconds</p>
    </body>
    </html>";
echo $htmlout;
