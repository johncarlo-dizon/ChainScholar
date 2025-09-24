<?php

namespace App\Policies;

use App\Models\ResearchPaper;
use App\Models\User;

class ResearchPaperPolicy
{
    public function view(User $user, ResearchPaper $paper): bool
    {
        return $user->role === 'ADMIN' || $user->id === $paper->user_id;
    }

    public function update(User $user, ResearchPaper $paper): bool
    {
        return $this->view($user, $paper); // owner or ADMIN
    }

    public function delete(User $user, ResearchPaper $paper): bool
    {
        return $this->view($user, $paper);
    }
}
