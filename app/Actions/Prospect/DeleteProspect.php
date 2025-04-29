<?php

namespace App\Actions\Prospect;

use App\Models\Prospect;

class DeleteProspect
{
    public function handle(Prospect $prospect): void
    {
        $prospect->delete();
    }
}