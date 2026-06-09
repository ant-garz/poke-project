<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\PokemonImportBatchStatus;

class PokemonImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_filename',
        'file_path',
        'status',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'status' => PokemonImportBatchStatus::class,
        ];
    }

    public function getProgressAttribute(): float
    {
        if (!$this->total_rows) {
            return 0;
        }

        return round(
            (($this->processed_rows + $this->failed_rows) / $this->total_rows) * 100,
            2
        );
    }

    /*
    |-----------------------------
    | State helpers
    |-----------------------------
    */

    public function markProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    /*
    |-----------------------------
    | Progress tracking
    |-----------------------------
    */

    public function incrementProcessed(): void
    {
        $this->increment('processed_rows');
    }

    public function incrementFailed(): void
    {
        $this->increment('failed_rows');
    }

    public function progress(): float
    {
        if (!$this->total_rows) {
            return 0;
        }

        return round(
            (($this->processed_rows + $this->failed_rows) / $this->total_rows) * 100,
            2
        );
    }
}