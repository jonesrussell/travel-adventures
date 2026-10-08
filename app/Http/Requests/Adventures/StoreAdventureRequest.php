<?php

namespace App\Http\Requests\Adventures;

use App\Rules\StoryDocument;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAdventureRequest extends FormRequest
{
    /**
     * Validate only the JSON body so query parameters cannot supply creation fields.
     *
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return $this->json()
            ->all();
    }

    /**
     * Normalize metadata explicitly because global trimming is disabled to preserve story text.
     * Blank titles become null so creation can generate the default title.
     */
    protected function prepareForValidation(): void
    {
        $input = $this->json();
        foreach (['title', 'location'] as $field) {
            if (is_string($input->get($field))) {
                $value = trim($input->get($field));
                $input->set($field, $value === '' ? null : $value);
            }
        }
        if (is_string($input->get('creation_key'))) {
            // UUID casing must not give the same retry key a different database identity.
            $input->set('creation_key', strtolower($input->get('creation_key')));
        }
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return [
            'creation_key' => ['bail', 'required', 'string', 'uuid'],
            'title' => ['nullable', 'string', 'max:160'],
            'story_format_version' => ['sometimes', 'required', 'integer', 'in:1', function (string $attribute, mixed $value, Closure $fail): void {
                // Laravel's integer rule also accepts numeric strings; the contract requires a JSON integer.
                if ($value !== 1) {
                    $fail('The story format version must be the integer 1.');
                }
            }],
            'story' => ['sometimes', 'required', function (string $attribute, mixed $value, Closure $fail): void {
                // Laravel's input arrays erase JSON object/list distinctions required by the story schema.
                $document = json_decode($this->getContent(), flags: JSON_THROW_ON_ERROR)
                    ->story;
                (new StoryDocument)
                    ->validate($attribute, $document, $fail);
            }],
            'location' => ['nullable', 'string', 'max:200'],
            'travel_start_date' => ['nullable', 'date_format:Y-m-d', $this->calendarYearRule()],
            'travel_end_date' => ['nullable', 'date_format:Y-m-d', $this->calendarYearRule()],
        ];
    }

    /**
     * Reject undeclared fields and check date relationships after individual date validation.
     *
     * @return array<Closure(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach (array_diff(array_keys($this->json()
                ->all()), array_keys($this->rules())) as $field) {
                $validator->errors()
                    ->add((string) $field, 'This field is not allowed.');
            }
            if ($validator->errors()
                ->hasAny(['travel_start_date', 'travel_end_date'])) {
                return;
            }
            $start = $this->json('travel_start_date');
            $end = $this->json('travel_end_date');
            if ($end !== null && $start === null) {
                $validator->errors()
                    ->add('travel_end_date', 'An end date requires a start date.');
            } elseif (is_string($end) && is_string($start) && $end < $start) {
                // Validated YYYY-MM-DD strings sort in the same order as their calendar dates.
                $validator->errors()
                    ->add('travel_end_date', 'The end date must be on or after the start date.');
            }
        }];
    }

    /**
     * PHP accepts year zero, but the contract restricts travel dates to years 0001 through 9999.
     */
    private function calendarYearRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (is_string($value) && str_starts_with($value, '0000-')) {
                $fail('Travel dates must use a year from 0001 to 9999.');
            }
        };
    }
}
