<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\TransaksiMasuk;
use App\Models\User;

class TransaksiMasukPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any TransaksiMasuk');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TransaksiMasuk $transaksimasuk): bool
    {
        return $user->checkPermissionTo('view TransaksiMasuk');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create TransaksiMasuk');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TransaksiMasuk $transaksimasuk): bool
    {
        return $user->checkPermissionTo('update TransaksiMasuk');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TransaksiMasuk $transaksimasuk): bool
    {
        return $user->checkPermissionTo('delete TransaksiMasuk');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any TransaksiMasuk');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TransaksiMasuk $transaksimasuk): bool
    {
        return $user->checkPermissionTo('restore TransaksiMasuk');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any TransaksiMasuk');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, TransaksiMasuk $transaksimasuk): bool
    {
        return $user->checkPermissionTo('replicate TransaksiMasuk');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder TransaksiMasuk');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TransaksiMasuk $transaksimasuk): bool
    {
        return $user->checkPermissionTo('force-delete TransaksiMasuk');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any TransaksiMasuk');
    }
}
