<?php

namespace App\Actions\Sales;

use App\Models\Opportunity;
use App\Models\OpportunityNote;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AddOpportunityNoteAction
{
    public function execute(Opportunity $opportunity, string $content, ?User $user = null): OpportunityNote
    {
        $content = trim($content);

        if ($content === '') {
            throw ValidationException::withMessages([
                'note' => 'La nota no puede estar vacía.',
            ]);
        }

        return $opportunity->notes()->create([
            'content' => $content,
            'by_user_id' => $user?->id,
        ]);
    }
}
