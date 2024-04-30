<?php

namespace App\Imports;

use App\Models\School; // Import School model

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class SchoolImport implements ToModel, WithHeadingRow
{
    use Importable;

    public function model(mixed $row)
    {
        $data = [
            'name'        => $row["name"] ?? '',
            'email'       => $row["email"]  ?? '',
            'classrooms'  => $row["classrooms"] ?? 0,
            'province_id' => $row["id_province"] ?? 0,
        ];

        return new School($data);
    }

    
}
