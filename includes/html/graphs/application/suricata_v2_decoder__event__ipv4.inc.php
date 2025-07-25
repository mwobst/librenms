<?php

$name = 'suricata';
$unit_text = 'IPv4 pkt/s';
$colours = 'psychedelic';
$descr_len = 15;

if (isset($vars['sinstance'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__frag_ignored']);
    $decoder__event__ipv4__frag_overlap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__frag_overlap']);
    $decoder__event__ipv4__frag_pkt_too_large_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__frag_pkt_too_large']);
    $decoder__event__ipv4__hlen_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__hlen_too_small']);
    $decoder__event__ipv4__icmpv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__icmpv6']);
    $decoder__event__ipv4__iplen_smaller_than_hlen_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__iplen_smaller_than_hlen']);
    $decoder__event__ipv4__opt_duplicate_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__opt_duplicate']);
    $decoder__event__ipv4__opt_eol_required_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__opt_eol_required']);
    $decoder__event__ipv4__opt_invalid_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__opt_invalid']);
    $decoder__event__ipv4__opt_invalid_len_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__opt_invalid_len']);
    $decoder__event__ipv4__opt_malformed_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__opt_malformed']);
    $decoder__event__ipv4__opt_pad_required_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__opt_pad_required']);
    $decoder__event__ipv4__opt_unknown_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__opt_unknown']);
    $decoder__event__ipv4__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__pkt_too_small']);
    $decoder__event__ipv4__trunc_pkt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'instance_' . $vars['sinstance'] . '___decoder__event__ipv4__trunc_pkt']);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__frag_ignored']);
    $decoder__event__ipv4__frag_overlap_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__frag_overlap']);
    $decoder__event__ipv4__frag_pkt_too_large_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__frag_pkt_too_large']);
    $decoder__event__ipv4__hlen_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__hlen_too_small']);
    $decoder__event__ipv4__icmpv6_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__icmpv6']);
    $decoder__event__ipv4__iplen_smaller_than_hlen_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__iplen_smaller_than_hlen']);
    $decoder__event__ipv4__opt_duplicate_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__opt_duplicate']);
    $decoder__event__ipv4__opt_eol_required_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__opt_eol_required']);
    $decoder__event__ipv4__opt_invalid_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__opt_invalid']);
    $decoder__event__ipv4__opt_invalid_len_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__opt_invalid_len']);
    $decoder__event__ipv4__opt_malformed_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__opt_malformed']);
    $decoder__event__ipv4__opt_pad_required_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__opt_pad_required']);
    $decoder__event__ipv4__opt_unknown_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__opt_unknown']);
    $decoder__event__ipv4__pkt_too_small_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__pkt_too_small']);
    $decoder__event__ipv4__trunc_pkt_rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'totals___decoder__event__ipv4__trunc_pkt']);
}

$rrd_list = [
    [
        'filename' => $rrd_filename,
        'descr' => 'Frag Ignored',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__frag_overlap_rrd_filename,
        'descr' => 'Frag Overlap',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__frag_pkt_too_large_rrd_filename,
        'descr' => 'Frag Pkt Too Lrg',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__hlen_too_small_rrd_filename,
        'descr' => 'Hlen Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__icmpv6_rrd_filename,
        'descr' => 'ICMPv6',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__iplen_smaller_than_hlen_rrd_filename,
        'descr' => 'IPlen < Hlen',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__opt_duplicate_rrd_filename,
        'descr' => 'Opt Dup',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__opt_eol_required_rrd_filename,
        'descr' => 'Opt EOL Required',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__opt_invalid_rrd_filename,
        'descr' => 'Opt Invalid',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__opt_invalid_len_rrd_filename,
        'descr' => 'Opt Invalid Len',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__opt_malformed_rrd_filename,
        'descr' => 'Opt Malformed',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__opt_pad_required_rrd_filename,
        'descr' => 'Opt Pad Required',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__opt_unknown_rrd_filename,
        'descr' => 'Opt Unkown',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__pkt_too_small_rrd_filename,
        'descr' => 'Pkt Too Small',
        'ds' => 'data',
    ],
    [
        'filename' => $decoder__event__ipv4__trunc_pkt_rrd_filename,
        'descr' => 'Trunc Pkt',
        'ds' => 'data',
    ],
];

require 'includes/html/graphs/generic_multi_line.inc.php';
