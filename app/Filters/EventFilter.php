<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class EventFilter
{
    protected Builder $query;
    protected array $filters;

    public function __construct(Builder $query, array $filters)
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    public static function apply(Builder $query, array $filters): Builder
    {
        $instance = new static($query, $filters);
        
        // Aturan dasar: HANYA event yang published
        $instance->query->where('status', 'published');

        return $instance->applySearch()
                        ->applyCategory()
                        ->applySubOrganization()
                        ->applyScope()
                        ->applyDateRange()
                        ->applySort()
                        ->query;
    }

    protected function applySearch(): self
    {
        if (!empty($this->filters['q'])) {
            $search = $this->filters['q'];
            // Menggunakan title karena kolom nama event di database kita adalah title
            $this->query->where('title', 'ilike', '%' . $search . '%');
        }
        return $this;
    }

    protected function applyCategory(): self
    {
        if (!empty($this->filters['category'])) {
            $this->query->where('category', $this->filters['category']);
        }
        return $this;
    }

    protected function applySubOrganization(): self
    {
        if (!empty($this->filters['subOrg'])) {
            $this->query->where('organization_id', $this->filters['subOrg']);
        }
        return $this;
    }

    protected function applyScope(): self
    {
        if (!empty($this->filters['scope'])) {
            $this->query->where('scope', $this->filters['scope']);
        }
        return $this;
    }

    protected function applyDateRange(): self
    {
        if (!empty($this->filters['dateFrom'])) {
            $this->query->whereDate('start_date', '>=', $this->filters['dateFrom']);
        }
        
        if (!empty($this->filters['dateTo'])) {
            $this->query->whereDate('start_date', '<=', $this->filters['dateTo']);
        }
        
        return $this;
    }

    protected function applySort(): self
    {
        $sort = $this->filters['sort'] ?? 'newest';

        if ($sort === 'nearest') {
            $this->query->whereDate('start_date', '>=', now())
                        ->orderBy('start_date', 'asc');
        } elseif ($sort === 'price') {
            // Karena tabel tiket belum ada, sementara kita urutkan berdasarkan yang terbaru dulu
            $this->query->orderBy('created_at', 'desc');
        } else {
            // newest (default)
            $this->query->orderBy('created_at', 'desc');
        }

        return $this;
    }
}
