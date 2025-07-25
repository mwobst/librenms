<?php

use LibreNMS\Enum\Sensor as SensorEnum;
use LibreNMS\Util\Html;

$sensors = DeviceCache::getPrimary()->sensors->where('sensor_class', $sensor_class)->where('group', '!=', 'transceiver')->sortBy([
    ['group', 'asc'],
    ['sensor_descr', 'asc'],
]); // cache all sensors on device and exclude transceivers

if ($sensors->isNotEmpty()) {
    $sensor_fa_icon = 'fa-' . SensorEnum::from($sensor_class)->icon();

    echo '
        <div class="row">
        <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
        <div class="panel-heading">';
    echo '<a href="device/device=' . $device['device_id'] . '/tab=health/metric=' . strtolower($sensor_type) . '/"><i class="fa ' . $sensor_fa_icon . ' fa-lg icon-theme" aria-hidden="true"></i><strong> ' . \LibreNMS\Util\StringHelpers::niceCase($sensor_type) . '</strong></a>';
    echo '      </div>
        <table class="table table-hover table-condensed table-striped">';
    $group = '';
    foreach ($sensors as $sensor) {
        if ($group != $sensor->group) {
            $group = $sensor->group;
            echo "<tr><td colspan='3'><strong>$group</strong></td></tr>";
        }

        // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
        // FIXME - So now we need to clean this up and move it into a function. Isn't it just "print-graphrow"?
        // FIXME - DUPLICATED IN health/sensors
        $graph_array = [];
        $graph_array['height'] = '100';
        $graph_array['width'] = '210';
        $graph_array['to'] = \App\Facades\LibrenmsConfig::get('time.now');
        $graph_array['id'] = $sensor->sensor_id;
        $graph_array['type'] = $graph_type;
        $graph_array['from'] = \App\Facades\LibrenmsConfig::get('time.day');
        $graph_array['legend'] = 'no';

        $link_array = $graph_array;
        $link_array['page'] = 'graphs';
        unset($link_array['height'], $link_array['width'], $link_array['legend']);
        $link = \LibreNMS\Util\Url::generate($link_array);

        if ($sensor->poller_type == 'ipmi') {
            $sensor->sensor_descr = substr(ipmiSensorName($device['hardware'], $sensor->sensor_descr), 0, 48);
        } else {
            $sensor->sensor_descr = substr($sensor->sensor_descr, 0, 48);
        }

        $overlib_content = '<div class=overlib><span class=overlib-text>' . $device['hostname'] . ' - ' . $sensor->sensor_descr . '</span><br />';
        foreach (['day', 'week', 'month', 'year'] as $period) {
            $graph_array['from'] = \App\Facades\LibrenmsConfig::get("time.$period");
            $overlib_content .= str_replace('"', "\'", \LibreNMS\Util\Url::graphTag($graph_array));
        }

        $overlib_content .= '</div>';

        $graph_array['width'] = 80;
        $graph_array['height'] = 20;
        $graph_array['bg'] = 'ffffff00';
        // the 00 at the end makes the area transparent.
        $graph_array['from'] = \App\Facades\LibrenmsConfig::get('time.day');
        $sensor_minigraph = \LibreNMS\Util\Url::lazyGraphTag($graph_array);

        $sensor_current = Html::severityToLabel($sensor->currentStatus(), $sensor->formatValue());

        echo '<tr><td><div style="display: grid; grid-gap: 10px; grid-template-columns: 3fr 1fr 1fr;">
            <div>' . \LibreNMS\Util\Url::overlibLink($link, \LibreNMS\Util\Rewrite::shortenIfName($sensor->sensor_descr), $overlib_content, $sensor_class) . '</div>
            <div>' . \LibreNMS\Util\Url::overlibLink($link, $sensor_minigraph, $overlib_content, $sensor_class) . '</div>
            <div>' . \LibreNMS\Util\Url::overlibLink($link, $sensor_current, $overlib_content, $sensor_class) . '</div>
            </div></td></tr>';
    }//end foreach

    echo '</table>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}//end if
