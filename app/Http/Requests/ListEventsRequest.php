<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListEventsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Public endpoint, semua boleh mengakses
        return true; 
    }

    public function rules(): array
    {
        return [
            'q'        => 'nullable|string|max:100',
            'category' => 'nullable|string', // Nanti bisa ditambah exists:categories,slug jika tabel category terpisah
            'subOrg'   => 'nullable|exists:organizations,id',
            'scope'    => 'nullable|in:internal,external',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateTo'   => 'nullable|date_format:Y-m-d|after_or_equal:dateFrom',
            'sort'     => 'nullable|in:newest,nearest,price',
            'page'     => 'nullable|integer|min:1',
            'perPage'  => 'nullable|integer|min:1|max:100',
        ];
    }
}
