<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;

class EventPolicy
{
    /**
     * Helper: Cek apakah user memiliki keanggotaan AKTIF di organisasi
     * pemilik event, dengan salah satu role yang diizinkan.
     */
    private function hasActiveMembership(User $user, string $organizationId, array $roles): bool
    {
        return OrganizationMember::where('organization_id', $organizationId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', $roles)
            ->exists();
    }

    /**
     * Melihat detail event (dari sisi panitia/manajemen).
     * Semua anggota aktif di organisasi pemilik event boleh melihat.
     */
    public function view(User $user, Event $event): bool
    {
        return $this->hasActiveMembership($user, $event->organization_id, [
            'admin', 'committee', 'ticketing', 'scanner', 'finance',
        ]);
    }

    /**
     * Membuat event baru di organisasi tertentu.
     * Hanya admin dan committee.
     * Catatan: Event belum ada, jadi menerima Organization ID sebagai parameter.
     */
    public function create(User $user, string $organizationId): bool
    {
        return $this->hasActiveMembership($user, $organizationId, [
            'admin', 'committee',
        ]);
    }

    /**
     * Mengubah data event (judul, tanggal, deskripsi, dll).
     * Hanya admin dan committee di organisasi pemilik event.
     */
    public function update(User $user, Event $event): bool
    {
        return $this->hasActiveMembership($user, $event->organization_id, [
            'admin', 'committee',
        ]);
    }

    /**
     * Menghapus event.
     * Hanya admin organisasi pemilik event.
     */
    public function delete(User $user, Event $event): bool
    {
        return $this->hasActiveMembership($user, $event->organization_id, ['admin']);
    }

    /**
     * Melakukan check-in / scan tiket peserta di event.
     * Admin, committee, dan scanner.
     */
    public function checkIn(User $user, Event $event): bool
    {
        return $this->hasActiveMembership($user, $event->organization_id, [
            'admin', 'committee', 'scanner',
        ]);
    }

    /**
     * Mengelola tiket event (membuat tipe tiket, mengubah harga, kuota, dll).
     * Admin, committee, dan ticketing.
     */
    public function manageTickets(User $user, Event $event): bool
    {
        return $this->hasActiveMembership($user, $event->organization_id, [
            'admin', 'committee', 'ticketing',
        ]);
    }
}
