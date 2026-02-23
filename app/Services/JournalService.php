<?php

namespace App\Services;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Exception;

class JournalService
{
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $totalDebit = collect($data['lines'])->sum('debit');
            $totalCredit = collect($data['lines'])->sum('credit');

            if ($totalDebit != $totalCredit) {
                throw new Exception('Journal not balanced');
            }

            $entry = JournalEntry::create([
                'user_id' => $data['user_id'],
                'date' => $data['date'],
                'reference' => $data['reference'],
                'description' => $data['description']
            ]);

            foreach ($data['lines'] as $line) {
                $entry->lines()->create($line);
            }

            return $entry;
        });
    }
}
