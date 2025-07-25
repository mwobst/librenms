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

$where = ' `devices`.`disabled` = 0';
$param = [];
$alert_states = [
    // divined from librenms/alerts.php
    'recovered' => 0,
    'alerted' => 1,
    'acknowledged' => 2,
    'worse' => 3,
    'better' => 4,
    'changed' => 5,
];

$show_recovered = false;

if (isset($vars['rule_id']) && is_numeric($vars['rule_id']) && $vars['rule_id'] > 0) {
    $where .= ' AND `alerts`.`rule_id` = ?';
    $param[] = $vars['rule_id'];
}

if (isset($vars['alert_id']) && is_numeric($vars['alert_id']) && $vars['alert_id'] > 0) {
    $where .= ' AND `alerts`.`id` = ?';
    $param[] = $vars['alert_id'];
}

if (isset($vars['device_id']) && is_numeric($vars['device_id']) && $vars['device_id'] > 0) {
    $where .= ' AND `alerts`.`device_id`=' . $vars['device_id'];
}

if (isset($vars['acknowledged']) && is_numeric($vars['acknowledged'])) {
    // I assume that if we are searching for acknowledged/not, we aren't interested in recovered
    $where .= ' AND `alerts`.`state`' . ($vars['acknowledged'] ? '=' : '!=') . $alert_states['acknowledged'];
}

if (isset($vars['fired']) && is_numeric($vars['fired'])) {
    $where .= ' AND `alerts`.`alerted`=' . $alert_states['alerted'];
}

if (isset($vars['unreachable']) && is_numeric($vars['unreachable'])) {
    // Sub-select to flag if at least one parent is set, and all parents are offline
    $where .= ' AND (SELECT IF(COUNT(`dr`.`parent_device_id`) > 0 AND COUNT(`dr`.`parent_device_id`)=count(`d`.`device_id`),1,0) FROM `device_relationships` `dr` LEFT JOIN `devices` `d` ON `dr`.`parent_device_id`=`d`.`device_id` AND `d`.`status`=0 WHERE `dr`.`child_device_id`=`devices`.`device_id`)=' . $vars['unreachable'];
}

if (isset($vars['state']) && is_numeric($vars['state'])) {
    $where .= ' AND `alerts`.`state`=' . $vars['state'];
    if ($vars['state'] == $alert_states['recovered']) {
        $show_recovered = true;
    }
}

if (isset($vars['min_severity'])) {
    $where .= get_sql_filter_min_severity($vars['min_severity'], 'alert_rules');
}

if (isset($vars['group']) && is_numeric($vars['group'])) {
    $where .= ' AND devices.device_id IN (SELECT `device_id` FROM `device_group_device` WHERE `device_group_id` = ?)';
    $param[] = $vars['group'];
}

if (! $show_recovered) {
    $where .= ' AND `alerts`.`state`!=' . $alert_states['recovered'];
}

if (! empty($searchPhrase)) {
    $where .= ' AND (`alerts`.`timestamp` LIKE ? OR `builder` LIKE ? OR `name` LIKE ? OR `hostname` LIKE ? OR `sysName` LIKE ?)';
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
    $param[] = "%$searchPhrase%";
}

$sql = ' FROM `alerts` LEFT JOIN `devices` ON `alerts`.`device_id`=`devices`.`device_id`';

if (! Auth::user()->hasGlobalRead()) {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $where .= ' AND `devices`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $param = array_merge($param, $device_ids);
}

$sql .= ' LEFT JOIN `locations` ON `devices`.`location_id` = `locations`.`id`';

$sql .= "  RIGHT JOIN `alert_rules` ON `alerts`.`rule_id`=`alert_rules`.`id` WHERE $where";

$count_sql = "SELECT COUNT(`alerts`.`id`) $sql";
$total = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (empty($vars['sort'])) {
    $sort = 'timestamp DESC';
} else {
    $sort = '`alert_rules`.`severity` DESC, timestamp DESC';
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
    $sql = "SELECT `alerts`.*, IFNULL(CONVERT_TZ(`alerts`.`timestamp`, @@global.time_zone, ?),`alerts`.`timestamp`) AS timestamp_display, `devices`.`hostname`, `devices`.`sysName`, `devices`.`display`, `devices`.`os`, `devices`.`hardware`, `locations`.`location`, `alert_rules`.`name`, `alert_rules`.`severity`, `alert_rules`.`builder` $sql";
    $param = array_merge([session('preferences.timezone')], $param);
} else {
    $sql = "SELECT `alerts`.*, `alerts`.`timestamp` AS timestamp_display, `devices`.`hostname`, `devices`.`sysName`, `devices`.`display`, `devices`.`os`, `devices`.`hardware`, `locations`.`location`, `alert_rules`.`name`, `alert_rules`.`severity`, `alert_rules`.`builder` $sql";
}

$rulei = 0;
foreach (dbFetchRows($sql, $param) as $alert) {
    $log = dbFetchCell('SELECT details FROM alert_log WHERE rule_id = ? AND device_id = ? ORDER BY id DESC LIMIT 1', [$alert['rule_id'], $alert['device_id']]);
    $alert_log_id = dbFetchCell('SELECT id FROM alert_log WHERE rule_id = ? AND device_id = ? ORDER BY id DESC LIMIT 1', [$alert['rule_id'], $alert['device_id']]);
    [$fault_detail, $max_row_length] = alert_details($log);
    $info = json_decode($alert['info'], true);

    $alert_to_ack = '<button type="button" class="btn btn-danger command-ack-alert fa fa-eye" aria-hidden="true" title="Mark as acknowledged" data-target="ack-alert" data-state="' . $alert['state'] . '" data-alert_id="' . $alert['id'] . '" data-alert_state="' . $alert['state'] . '" name="ack-alert"></button>';
    $alert_to_nack = '<button type="button" class="btn btn-primary command-ack-alert fa fa-eye-slash" aria-hidden="true" title="Mark as not acknowledged" data-target="ack-alert" data-state="' . $alert['state'] . '" data-alert_id="' . $alert['id'] . '" data-alert_state="' . $alert['state'] . '" name="ack-alert"></button>';
    $alert_to_unack = '<button type="button" class="btn btn-primary command-ack-alert fa fa-eye" aria-hidden="true" title="Mark as not acknowledged" data-target="ack-alert" data-state="' . $alert['state'] . '" data-alert_id="' . $alert['id'] . '" data-alert_state="' . $alert['state'] . '" name="ack-alert"></button>';

    $ack_ico = $alert_to_ack;

    if ((int) $alert['state'] === 0) {
        $msg = '';
    } elseif ((int) $alert['state'] === 1 || (int) $alert['state'] === 3 || (int) $alert['state'] === 4 || (int) $alert['state'] === 5) {
        if ((int) $alert['state'] === 3) {
            $msg = '<i class="fa fa-angle-double-down" style="font-size:20px;" aria-hidden="true" title="Status got worse"></i>';
        } elseif ((int) $alert['state'] === 4) {
            $msg = '<i class="fa fa-angle-double-up" style="font-size:20px;" aria-hidden="true" title="Status got better"></i>';
        } elseif ((int) $alert['state'] === 5) {
            $msg = '<i class="fa fa-angle-double-up" style="font-size:20px;" aria-hidden="true" title="Status changed"></i>';
        }
    } elseif ((int) $alert['state'] === 2) {
        if ($info['until_clear'] === false) {
            $ack_ico = $alert_to_unack;
        } else {
            $ack_ico = $alert_to_nack;
        }
    }

    $hostname = '<div class="incident">' . generate_device_link($alert, shorthost(format_hostname($alert))) . '<div id="incident' . $alert['id'] . '"';
    if (isset($vars['uncollapse_key_count']) && is_numeric($vars['uncollapse_key_count'])) {
        $hostname .= $max_row_length < (int) $vars['uncollapse_key_count'] ? '' : ' class="collapse"';
    } else {
        $hostname .= ' class="collapse"';
    }
    $hostname .= '>' . $fault_detail . '</div></div>';

    $severity = $alert['severity'];
    $severity_ico = '<span class="alert-status label-' . alert_layout($severity)['background_color'] . '">&nbsp;</span>';

    if ($alert['state'] == 3) {
        $severity .= ' <strong>+</strong>';
    } elseif ($alert['state'] == 4) {
        $severity .= ' <strong>-</strong>';
    }

    if ((int) $alert['state'] === 2) {
        $severity_ico = '<span class="alert-status label-primary">&nbsp;</span>';
    }

    $proc = dbFetchCell('SELECT proc FROM alerts,alert_rules WHERE alert_rules.id = alerts.rule_id AND alerts.id = ?', [$alert['id']]);
    if (($proc == '') || ($proc == 'NULL')) {
        $has_proc = '';
    } else {
        if (! preg_match('#^https?://#', $proc)) {
            $has_proc = '';
        } else {
            $has_proc = '<a href="' . $proc . '" target="_blank"><button type="button" class="btn btn-info fa fa-external-link" aria-hidden="true"></button></a>';
        }
    }

    if (empty($alert['note'])) {
        $note_class = 'default';
    } else {
        $note_class = 'warning';
    }

    $response[] = [
        'id' => $rulei++,
        'rule' => '<i title="' . htmlentities($alert['builder']) . '"><a href="' . \LibreNMS\Util\Url::generate(['page' => 'alert-rules']) . '">' . htmlentities($alert['name']) . '</a></i>',
        'details' => '<a class="fa-solid fa-plus incident-toggle" style="display:none" data-toggle="collapse" data-target="#incident' . $alert['id'] . '" data-parent="#alerts"></a>',
        'verbose_details' => "<button type='button' class='btn btn-alert-details command-alert-details' aria-label='Details' id='alert-details' data-alert_log_id='{$alert_log_id}'><i class='fa-solid fa-circle-info'></i></button>",
        'hostname' => $hostname,
        'location' => generate_link(htmlspecialchars($alert['location'] ?? 'N/A'), ['page' => 'devices', 'location' => $alert['location'] ?? '']),
        'timestamp' => ($alert['timestamp_display'] ? $alert['timestamp_display'] : 'N/A'),
        'severity' => $severity_ico,
        'state' => $alert['state'],
        'alert_id' => $alert['id'],
        'ack_ico' => $ack_ico,
        'proc' => $has_proc,
        'notes' => "<button type='button' class='btn btn-$note_class fa fa-sticky-note-o command-alert-note' aria-label='Notes' id='alert-notes' data-alert_id='{$alert['id']}'></button>",
    ];
}

$output = [
    'current' => $current,
    'rowCount' => $rowCount,
    'rows' => $response,
    'total' => $total,
];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
