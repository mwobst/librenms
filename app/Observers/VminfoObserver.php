<?php

namespace App\Observers;

use App\Models\Eventlog;
use App\Models\Vminfo;
use LibreNMS\Enum\Severity;

class VminfoObserver
{
    /**
     * Handle the Vminfo "created" event.
     *
     * @param  Vminfo  $vminfo
     * @return void
     */
    public function created(Vminfo $vminfo): void
    {
        Eventlog::log('Virtual Machine added: ' . $vminfo->vmwVmDisplayName . " ($vminfo->vmwVmMemSize GB / $vminfo->vmwVmCpus vCPU)", $vminfo->device_id, 'vm', Severity::Notice, $vminfo->vmwVmVMID);
    }

    /**
     * Handle the Vminfo "updated" event.
     *
     * @param  Vminfo  $vminfo
     * @return void
     */
    public function updating(Vminfo $vminfo)
    {
        foreach ($vminfo->getDirty() as $field => $value) {
            Eventlog::log($vminfo->vmwVmDisplayName . ' (' . preg_replace('/^vmwVm/', '', $field) . ') -> ' . $value, $vminfo->device_id, 'vm');
        }
    }

    /**
     * Handle the Vminfo "deleted" event.
     *
     * @param  Vminfo  $vminfo
     * @return void
     */
    public function deleted(Vminfo $vminfo): void
    {
        Eventlog::log('Virtual Machine removed: ' . $vminfo->vmwVmDisplayName, $vminfo->device_id, 'vm', Severity::Warning, $vminfo->vmwVmVMID);
    }

    /**
     * Handle the Vminfo "restored" event.
     *
     * @param  Vminfo  $vminfo
     * @return void
     */
    public function restored(Vminfo $vminfo): void
    {
        //
    }

    /**
     * Handle the Vminfo "force deleted" event.
     *
     * @param  Vminfo  $vminfo
     * @return void
     */
    public function forceDeleted(Vminfo $vminfo): void
    {
        //
    }
}
