<?php

namespace App\Policies;

use App\Models\PreAlert;
use App\Models\User;

class PreAlertPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isCustomer();
    }

    public function view(User $user, PreAlert $preAlert): bool
    {
        return $user->isCustomer() && $preAlert->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isCustomer();
    }

    public function update(User $user, PreAlert $preAlert): bool
    {
        return $user->isCustomer()
            && $preAlert->user_id === $user->id
            && $preAlert->isEditable();
    }

    public function cancel(User $user, PreAlert $preAlert): bool
    {
        return $user->isCustomer()
            && $preAlert->user_id === $user->id
            && $preAlert->isCancellable();
    }

    public function downloadInvoice(User $user, PreAlert $preAlert): bool
    {
        if ($user->isAdmin()) {
            return $preAlert->invoice_path !== null;
        }

        return $this->view($user, $preAlert) && $preAlert->invoice_path !== null;
    }
}
