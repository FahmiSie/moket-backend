<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Pagination\LengthAwarePaginator;

class EventService
{
    /**
     * Cari, filter, urutkan, dan paginasi data Event.
     * Hanya event yang "published".
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function searchAndFilter(array $filters): LengthAwarePaginator
    {
        $query = Event::with('organization')->where('status', 'published');

        // Search (Keyword title)
        $query->when($filters['q'] ?? null, function ($q, $search) {
            $q->where('title', 'ilike', '%' . $search . '%');
        });

        // Filter Category
        $query->when($filters['category'] ?? null, function ($q, $category) {
            $q->where('category', $category);
        });

        // Filter Sub Organization
        $query->when($filters['subOrg'] ?? null, function ($q, $subOrg) {
            $q->where('organization_id', $subOrg);
        });

        // Filter Scope (Internal/External)
        $query->when($filters['scope'] ?? null, function ($q, $scope) {
            $q->where('scope', $scope);
        });

        // Rentang Tanggal (Date From & Date To)
        $query->when($filters['dateFrom'] ?? null, function ($q, $dateFrom) {
            $q->whereDate('start_date', '>=', $dateFrom);
        });
        
        $query->when($filters['dateTo'] ?? null, function ($q, $dateTo) {
            $q->whereDate('start_date', '<=', $dateTo);
        });

        // Sorting
        $sort = $filters['sort'] ?? 'newest';
        if ($sort === 'nearest') {
            // Yang terdekat dengan hari ini dan di masa depan
            $query->where('start_date', '>=', now())
                  ->orderBy('start_date', 'asc');
        } elseif ($sort === 'newest') {
            $query->orderBy('created_at', 'desc');
        } else {
            // Fallback (misal 'price' tapi harga belum ada)
            $query->orderBy('created_at', 'desc');
        }

        // Paginasi (Default: 12 item per halaman)
        return $query->paginate(12);
    }
}
