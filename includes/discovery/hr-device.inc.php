<?php

use LibreNMS\OS;

if (empty($os) || ! $os instanceof OS) {
    $os = OS::make($device);
}

(new \LibreNMS\Modules\HrDevice())->discover($os);
