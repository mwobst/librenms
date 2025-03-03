<?php
/**
 * Module for polling fdb-table information regularly. This basically only
 * includes the fdb-table discovery module. Reason: Information about VLANs
 * and especially MAC addresses need to be "fresh".
 */

use LibreNMS\Config;

require Config::get('install_dir') . "/includes/discovery/fdb-table.inc.php";
