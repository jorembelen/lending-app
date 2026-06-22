<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeesImport implements ToModel, WithHeadingRow
{   
    public function model(array $row)
    {
        return Employee::updateOrCreate(['badge' => $row['badge']],
        [
            'badge'     => $row['badge'],
            'name'     => $row['name'],
            'nationality'     => $row['nationality'],
            'designation'     => $row['designation'],
            'mobile'     => $row['mobile'] ?? null,
        ]);
    }

    
}
