<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{

    protected function logChanges($attributes, $action)
    {
        $msg = "Record " .$action .' : ' .json_encode($attributes);
        activity()->withProperties(['attributes' => ['name' => 'User was ' .$action]])->log($msg);
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->logChanges($user->getAttributes(), 'created');
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $changes = $user->getDirty(); // Get the changed attributes

        // Log the changes
        foreach ($changes as $attribute => $newValue) {
            $originalValue = $user->getOriginal($attribute);
            $msg = "Attribute '$attribute' changed from '$originalValue' to '$newValue'";
            activity()->withProperties(['attributes' => ['name' => $user->name .' was updated']])->log($msg);
        }

    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->logChanges($user->getAttributes(), 'deleted');
    }

   
}
