<?php

namespace App\Services\Company;

use App\Models\Company;

class CompanyService
{
    public function createCompany(array $data): Company
    {
        return Company::create($data);
    }

    public function updateCompany(Company $company, array $data): Company
    {
        $company->update($data);
        return $company;
    }

    public function deleteCompany(Company $company): void
    {
        $company->delete();
    }
}
