<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\KategoriBarang;
use App\Models\User;

class KategoriBarangPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any KategoriBarang');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, KategoriBarang $kategoribarang): bool
    {
        return $user->checkPermissionTo('view KategoriBarang');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create KategoriBarang');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, KategoriBarang $kategoribarang): bool
    {
        return $user->checkPermissionTo('update KategoriBarang');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, KategoriBarang $kategoribarang): bool
    {
        return $user->checkPermissionTo('delete KategoriBarang');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any KategoriBarang');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, KategoriBarang $kategoribarang): bool
    {
        return $user->checkPermissionTo('restore KategoriBarang');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any KategoriBarang');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, KategoriBarang $kategoribarang): bool
    {
        return $user->checkPermissionTo('replicate KategoriBarang');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder KategoriBarang');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, KategoriBarang $kategoribarang): bool
    {
        return $user->checkPermissionTo('force-delete KategoriBarang');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any KategoriBarang');
    }
}
