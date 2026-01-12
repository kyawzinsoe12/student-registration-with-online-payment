<?php

namespace App\Policies;

use App\Models\Major;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MajorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Major $major): bool
    {
        return $user->hasPermissionTo('major.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('major.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Major $major): bool
    {
        return $user->hasPermissionTo('major.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Major $major): bool
    {
        // return $user->hasPermissionTo('major.delete');
        $canDelete =  $user->hasRole('admin') ||
                ($user->id === $major->created_by && $user->can('major.delete'));
        
        if($canDelete && $major->courses()->where('is_active',true)->exists()){
            return false;
        }

        return $canDelete;

    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Major $major): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Major $major): bool
    {
        return false;
    }
}
