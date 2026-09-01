<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tickets' => ['required', 'array', 'min:1'],
            'tickets.*.ticket_type_id' => ['required', 'uuid', 'exists:ticket_types,id'],
            'tickets.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ];
    }
    
    public function bodyParameters(): array
    {
        return [
            'tickets' => [
                'description' => 'Daftar tiket yang dibeli.',
                'example' => [
                    [
                        'ticket_type_id' => '0190bd05-95a7-7b24-9b21-432d64a02be0',
                        'quantity' => 2
                    ]
                ]
            ],
            'payment_method' => [
                'description' => 'Metode pembayaran pilihan user (opsional).',
                'example' => 'qris'
            ]
        ];
    }
}
