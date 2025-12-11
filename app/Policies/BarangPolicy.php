<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Barang;
use App\Models\User;

class BarangPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Barang');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Barang $barang): bool
    {
        return $user->checkPermissionTo('view Barang');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Barang');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Barang $barang): bool
    {
        return $user->checkPermissionTo('update Barang');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Barang $barang): bool
    {
        return $user->checkPermissionTo('delete Barang');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Barang');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Barang $barang): bool
    {
        return $user->checkPermissionTo('restore Barang');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Barang');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Barang $barang): bool
    {
        return $user->checkPermissionTo('replicate Barang');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Barang');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Barang $barang): bool
    {
        return $user->checkPermissionTo('force-delete Barang');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Barang');
    }
}
