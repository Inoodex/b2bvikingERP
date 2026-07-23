<?php

namespace App\Services\Department;

use App\Models\Department;

class DepartmentService
{
    public function createDepartment(array $data): Department
    {
        return Department::create($data);
    }

    public function updateDepartment(Department $department, array $data): Department
    {
        $department->update($data);
        return $department;
    }

    public function deleteDepartment(Department $department): void
    {
        $department->delete();
    }
}
