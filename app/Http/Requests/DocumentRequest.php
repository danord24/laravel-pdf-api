<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'name' => filter_var($this->name, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES),
            'description' => ($this->description ? filter_var($this->description, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES): NULL)
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['string', 'required'],
            'description' => ['string', 'nullable'],
            'visibility' => [
                'required',
                Rule::in(['public', 'private'])
            ],
            'html' => [
                'required_without:url',
                function ($attribute, $value, $fail) {
                    if ($value != '' && $this->url != '') {
                        $fail('You can only pass in HTML or URL, not both.');
                    }
                },
                'string'
            ],
            'url' => ['required_without:html', 'url']
        ];
    }
}
