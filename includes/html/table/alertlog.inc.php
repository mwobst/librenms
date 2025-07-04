<?php

/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage graphs
 * @link       https://www.librenms.org
 * @copyright  2018 LibreNMS
 * @author     LibreNMS Contributors
*/

$alert_severities = [
    // alert_rules.status is enum('ok','warning','critical')
    'ok' => 1,
    'warning' => 2,
    'critical' => 3,
    'ok only' => 4,
    'warning only' => 5,
    'critical only' => 6,
];

$where = 1;
$param = [];
$alert_log_id = null;

if (is_numeric($vars['device_id'])) {
    $where .= ' AND E.device_id = ?';
    $param[] = $vars['device_id'];
}

if ($vars['state'] >= 0) {
    $where .= ' AND `E`.`state` = ?';
    $param[] = $vars['state'];
}

if (isset($vars['min_severity'])) {
    $where .= get_sql_filter_min_severity($vars['min_severity'], 'R');
}

if (isset($vars['device_group']) && is_numeric($vars['device_group'])) {
    $where .= ' AND D.device_id IN (SELECT `device_id` FROM `device_group_device` WHERE `device_group_id` = ?)';
    $param[] = $vars['device_group'];
}

if (! Auth::user()->hasGlobalRead()) {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $where .= ' AND `E`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $param = array_merge($param, $device_ids);
}

if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $where .= ' AND (`D`.`hostname` LIKE ? OR `D`.`sysName` LIKE ? OR `E`.`time_logged` LIKE ? OR `name` LIKE ?)';
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
}

$sql = " FROM `alert_log` AS E LEFT JOIN devices AS D ON E.device_id=D.device_id LEFT JOIN alert_rules AS R ON E.rule_id=R.id WHERE $where";

$count_sql = "SELECT COUNT(`E`.`id`) $sql";
$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (! isset($sort) || empty($sort)) {
    $sort = 'time_logged DESC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - $rowCount);
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

if (session('preferences.timezone')) {
    $sql = "SELECT E.id AS alert_log_id, E.details AS alert_log_details, R.severity, D.device_id,name AS alert,rule_id,state,time_logged,DATE_FORMAT(IFNULL(CONVERT_TZ(time_logged, @@global.time_zone, ?),time_logged), '" . \App\Facades\LibrenmsConfig::get('dateformat.mysql.compact') . "') as humandate,details $sql";
    $param = array_merge([session('preferences.timezone')], $param);
} else {
    $sql = "SELECT E.id AS alert_log_id, E.details AS alert_log_details, R.severity, D.device_id,name AS alert,rule_id,state,time_logged,DATE_FORMAT(time_logged, '" . \App\Facades\LibrenmsConfig::get('dateformat.mysql.compact') . "') as humandate,details $sql";
}

$rulei = 0;
foreach (dbFetchRows($sql, $param) as $alertlog) {
    $dev = device_by_id_cache($alertlog['device_id']);
    $alert_state = $alertlog['state'];
    // If it's a new rule created, or a clear/RECOVERED
    if ($alert_state == '0') {
        // Get the latest active that is not a clear/RECOVERED
        // @phpstan-ignore-next-line
        $last_active_state = dbFetchRows('SELECT id, details FROM alert_log WHERE device_id = ? AND id < ? AND rule_id = ? AND `state` != 0 ORDER BY id DESC LIMIT 1', [$alertlog['device_id'], $alertlog['alert_log_id'], $alertlog['rule_id']]);
        // It's a real alarm, we can then used it for the details
        if ($last_active_state) {
            $alert_log_id = $last_active_state[0]['id'];
            [$fault_detail, $max_row_length] = alert_details($last_active_state[0]['details']);
            // It's a rule created log
        } else {
            $fault_detail = 'Rule created, no faults found';
        }
        // We will display the details of the log in question
    } else {
        $alert_log_id = $alertlog['alert_log_id'];
        [$fault_detail, $max_row_length] = alert_details($alertlog['alert_log_details']);
    }

    if ($alert_state == '0') {
        $status = 'label-success';
    } elseif ($alert_state == '1') {
        $status = 'label-danger';
    } elseif ($alert_state == '2') {
        $status = 'label-info';
    } elseif ($alert_state == '3') {
        $status = 'label-warning';
    } elseif ($alert_state == '4') {
        $status = 'label-primary';
    } elseif ($alert_state == '5') {
        $status = 'label-warning';
    }//end if

    $response[] = [
        'id' => $rulei++,
        'time_logged' => $alertlog['humandate'],
        'details' => '<a class="fa fa-plus incident-toggle" style="display:none" data-toggle="collapse" data-target="#incident' . $rulei . '" data-parent="#alerts"></a>',
        'verbose_details' => "<button type='button' class='btn btn-alert-details verbose-alert-details' style='display:none' aria-label='Details' id='alert-details' data-alert_log_id='{$alert_log_id}'><i class='fa-solid fa-circle-info'></i></button>",
        'hostname' => '<div class="incident">' . generate_device_link($dev) . '<div id="incident' . $rulei . '" class="collapse">' . $fault_detail . '</div></div>',
        'alert' => htmlspecialchars($alertlog['alert']),
        'status' => "<i class='alert-status " . $status . "' title='" . ($alert_state ? 'active' : 'recovered') . "'></i>",
        'severity' => $alertlog['severity'],
    ];
}//end foreach

$output = [
    'current' => $current,
    'rowCount' => $rowCount,
    'rows' => $response,
    'total' => $total,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
