<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Auth\Access\Response;

class EnrollmentPolicy
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
    public function view(User $user, Enrollment $enrollment): bool
    {
        return $user->hasPermissionTo('enrollment.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user,Course $course): bool
    {
        if(!$course->is_active){
            return false;
        }
        $alreadyEnroll = Enrollment::where('user_id',$user->id)
                                    ->where('course_id',$course->id)
                                    ->whereIN('status',['pending','cancelled'])
                                    ->exists();
        return $user->can('enrollment.create') && !$alreadyEnroll;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Enrollment $enrollment): bool
    {
        if($user->hasRole('admin')){
            return true;
        }

        if($user->id === $enrollment->user_id){
            return $enrollment->status === 'pending';
        }

        return $user->id === $enrollment->course->created_by && $user->can('enrollment.update');
        
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Enrollment $enrollment,Course $course): bool
    {
        if($user->hasRole('admin')){
            return true;
        }

        $canDelete = Enrollment::where('user_id',$user->id) 
                                ->where('course_id',$course->id)
                                ->whereIn('status',['pending','cancelled'])
                                ->exists();

        // if($user->id !== $enrollment->user_id){
        //     return false;
        // }
        // if(! in_array($enrollment->status,['pending','cancelled'])){
        //     return false;
        // }
        
        return $user->can('enrollment.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Enrollment $enrollment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Enrollment $enrollment): bool
    {
        return false;
    }
}
