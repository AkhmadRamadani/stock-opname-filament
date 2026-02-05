<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\TransaksiKeluar;
use App\Models\User;

class TransaksiKeluarPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any TransaksiKeluar');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TransaksiKeluar $transaksikeluar): bool
    {
        return $user->checkPermissionTo('view TransaksiKeluar');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create TransaksiKeluar');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TransaksiKeluar $transaksikeluar): bool
    {
        return $user->checkPermissionTo('update TransaksiKeluar');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TransaksiKeluar $transaksikeluar): bool
    {
        return $user->checkPermissionTo('delete TransaksiKeluar');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any TransaksiKeluar');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TransaksiKeluar $transaksikeluar): bool
    {
        return $user->checkPermissionTo('restore TransaksiKeluar');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any TransaksiKeluar');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, TransaksiKeluar $transaksikeluar): bool
    {
        return $user->checkPermissionTo('replicate TransaksiKeluar');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder TransaksiKeluar');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TransaksiKeluar $transaksikeluar): bool
    {
        return $user->checkPermissionTo('force-delete TransaksiKeluar');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any TransaksiKeluar');
    }
}
