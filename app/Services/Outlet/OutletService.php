<?php

namespace App\Services\Outlet;

use App\Models\Outlet;

class OutletService
{
    public function createOutlet(array $data)
    {
        return Outlet::create($data);
    }

    public function updateOutlet(Outlet $outlet, array $data)
    {
        return $outlet->update($data);
    }

    public function deleteOutlet(Outlet $outlet)
    {
        return $outlet->delete();
    }
}
