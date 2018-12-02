<?php

/**
 * @return array
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_styles()
{
    global $fluent;

    $query = $fluent->from('stylesheets')
        ->select(null)
        ->select('id')
        ->select('uri');

    $styles = [];
    foreach ($query as $style) {
        $styles[] = $style['id'];
    }

    return $styles;
}

/**
 * @param array $styles
 * @param bool  $create
 *
 * @return array
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_classes(array $styles, bool $create)
{
    global $fluent;

    $all_classes = [];
    foreach ($styles as $style) {
        $classes = $fluent->from('class_config')
            ->select(null)
            ->select('name')
            ->select('value')
            ->select('classname')
            ->select('classcolor')
            ->select('classpic')
            ->orderBy('value')
            ->where('template = ?', $style)
            ->fetchAll();

        if (empty($classes)) {
            if (!$create) {
                die("You do have not classes for template {$style}\n\nto create them rerun this script\nphp bin/uglify.php classes\n");
            } else {
                foreach ($all_classes[0] as $values) {
                    $values['template'] = $style;
                    $fluent->insertInto('class_config')
                        ->values($values)
                        ->execute();
                }
                die("Classes added for template {$style}\n");
            }
        }
        $all_classes[] = $classes;
    }

    return $all_classes;
}
