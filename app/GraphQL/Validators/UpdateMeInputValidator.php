<?php

namespace App\GraphQL\Validators;

use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

final class UpdateMeInputValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [

            'email' => [
                'bail',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->arg('id')),
            ],
        ];
    }
}
