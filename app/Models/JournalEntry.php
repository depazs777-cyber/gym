<?php

namespace App\Models;

use App\Core\Model;

class JournalEntry extends Model {
    protected $table = 'journal_entries';
    protected $fillable = ['gym_id', 'entry_date', 'description', 'reference', 'source_module', 'source_id', 'status', 'created_by', 'posted_at', 'voided_at'];

    // Relación con JournalLines
    public function lines($entryId) {
        $stmt = $this->db->prepare("SELECT * FROM journal_lines WHERE entry_id = :entry_id");
        $stmt->execute(['entry_id' => $entryId]);
        return $stmt->fetchAll();
    }
}
