<?php

$name = 'nextcloud';
$unit_text = 'apps';
$descr = 'disabled apps';

if (isset($vars['nextcloud_user'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'users___' . $vars['nextcloud_user'] . '___disabled_apps']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'disabled_apps']);
}

if (Rrd::checkRrdExists($uptime_rrd_filename)) {
    $ds = 'data';
    $filename = $rrd_filename;
} else {
    d_echo('RRD "' . $rrd_filename . '" not found');
}

require 'includes/html/graphs/generic_stats.inc.php';
