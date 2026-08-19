<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Helper: Cek apakah user memiliki keanggotaan AKTIF di organisasi
     * dengan salah satu dari role yang diizinkan.
     */
    private function hasActiveMembership(User $user, Organization $organization, array $roles): bool
    {
        return OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', $roles)
            ->exists();
    }

    /**
     * Melihat detail organisasi.
     * Semua anggota aktif boleh melihat.
     */
    public function view(User $user, Organization $organization): bool
    {
        return $this->hasActiveMembership($user, $organization, [
            'admin', 'committee', 'ticketing', 'scanner', 'finance',
        ]);
    }

    /**
     * Mengubah data organisasi (nama, logo, deskripsi, dll).
     * Hanya admin organisasi.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $this->hasActiveMembership($user, $organization, ['admin']);
    }

    /**
     * Mengelola anggota organisasi (invite, ubah role, hapus anggota).
     * Hanya admin organisasi.
     */
    public function manageMembers(User $user, Organization $organization): bool
    {
        return $this->hasActiveMembership($user, $organization, ['admin']);
    }
}
