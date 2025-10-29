<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVerlofaanvraagRequest extends FormRequest {


    /**
     * Determine if the user is authorized to make this request.
     */

    public function authorize(): bool {
         return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'verlof_type_id' => ['required','exists:verloftype,verlof_type_id'],
            'start_datum'    => ['required','date','after_or_equal:today'],
            'eind_datum'     => ['required','date','after_or_equal:start_datum'],
            'reden'          => ['required','string','min:5','max:500'],
        ];
    }
}
