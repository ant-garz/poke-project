<?php

namespace App\Enums;

enum PokemonImportBatchStatus: string
{
    case Uploaded = 'uploaded';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}