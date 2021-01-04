<?php namespace Waka\Agg\Classes\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Waka\Agg\Models\AggeableLog;

class AggeableLogsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $aggeableLog = new AggeableLog();
            $aggeableLog->id = $row['id'] ?? null;
            $aggeableLog->taken_at = $row['taken_at'] ?? null;
            $aggeableLog->ended_at = $row['ended_at'] ?? null;
            $aggeableLog->data_source = $row['data_source'] ?? null;
            $aggeableLog->parts = $row['parts'] ?? null;
            $aggeableLog->log = $row['log'] ?? null;
            $aggeableLog->save();
        }
    }
}
