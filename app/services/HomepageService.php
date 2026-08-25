<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Organization;
use App\Models\TalentProfile;
use Illuminate\Support\Facades\Cache;

class HomepageService
{
    /**
     * Ambil maks 3 event published terdekat.
     * Cache 5 menit.
     */
    public function featuredEvents()
    {
        return Cache::remember('homepage:featured-events', 300, function () {
            return Event::with('organization')
                ->where('status', 'published')
                ->where('start_date', '>=', now())
                ->orderBy('start_date', 'asc')
                ->limit(3)
                ->get();
        });
    }

    /**
     * Ambil semua sub-organisasi yang aktif.
     * Cache 5 menit.
     */
    public function subOrganizations()
    {
        return Cache::remember('homepage:sub-organizations', 300, function () {
            return Organization::where('status', 'active')
                ->select(['id', 'name', 'slug', 'logo_url', 'description'])
                ->orderBy('name', 'asc')
                ->get();
        });
    }

    /**
     * Ambil talent highlights (yang punya bio, max 6).
     * Cache 5 menit.
     */
    public function talentHighlights()
    {
        return Cache::remember('homepage:talent-highlights', 300, function () {
            return TalentProfile::with('user:id,name')
                ->whereNotNull('bio')
                ->where('bio', '!=', '')
                ->limit(6)
                ->get();
        });
    }
}
