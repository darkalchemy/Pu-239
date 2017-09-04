<?php
function pager_new($count, $perpage, $page, $url, $page_link = false)
{
    $pages = floor($count / $perpage);
    if ($pages * $perpage < $count) {
        ++$pages;
    }
    //=== lets make php happy
    $page_num = '';
    $page = ($page < 1 ? 1 : $page);
    $page = ($page > $pages ? $pages : $page);
    //=== lets add the ... if too many pages
    switch (true) {
        case $pages < 11:
            for ($i = 1; $i <= $pages; ++$i) {
                $page_num .= ($i == $page ? ' ' . $i . ' ' : ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ');
            }
            break;

        case $page < 5 || $page > ($pages - 3):
            for ($i = 1; $i < 5; ++$i) {
                $page_num .= ($i == $page ? ' ' . $i . ' ' : ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ');
            }
            $page_num .= ' ... ';
            $math = round($pages / 2);
            for ($i = ($math - 1); $i <= ($math + 1); ++$i) {
                $page_num .= ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ';
            }
            $page_num .= ' ... ';
            for ($i = ($pages - 2); $i <= $pages; ++$i) {
                $page_num .= ($i == $page ? ' ' . $i . ' ' : ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ');
            }
            break;

        case $page > 4 && $page < ($pages - 2):
            for ($i = 1; $i < 5; ++$i) {
                $page_num .= ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ';
            }
            $page_num .= ' ... ';
            for ($i = ($page - 1); $i <= ($page + 1); ++$i) {
                $page_num .= ($i == $page ? ' ' . $i . ' ' : ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ');
            }
            $page_num .= ' ... ';
            for ($i = ($pages - 2); $i <= $pages; ++$i) {
                $page_num .= ' <a class="altlink" href="' . $url . '&amp;page=' . $i . $page_link . '">' . $i . '</a> ';
            }
            break;
    }
    $menu = ($page == 1 ? ' <div style="text-align: center; font-weight: bold;"><img src="./images/forums/arrow_prev.gif" alt="&lt;&lt;" /> Prev' : '<div style="text-align: center; font-weight: bold;"><a class="altlink" href="' . $url . '&amp;page=' . ($page - 1) . $page_link . '"><img src="./images/forums/arrow_prev.gif" alt="&lt;&lt;" /> Prev</a>') . '&#160;&#160;&#160;' . $page_num . '&#160;&#160;&#160;' . ($page == $pages ? 'Next <img src="./images/forums/arrow_next.gif" alt="&gt;&gt;" /></div> ' : ' <a class="altlink" href="' . $url . '&amp;page=' . ($page + 1) . $page_link . '">Next <img src="./images/forums/arrow_next.gif" alt="&gt;&gt;" /></a></div>');
    $offset = ($page * $perpage) - $perpage;
    $LIMIT = ($count > 0 ? "LIMIT $offset,$perpage" : '');

    return [
        $menu,
        $LIMIT,
    ];
} //=== end pager function
