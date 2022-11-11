<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveCourse extends FormRequest
{
    public function withValidator(Validator $validator): void
    {
        $validator->stopOnFirstFailure();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $all = request()->all();

        // Helper meta function
        $createArrayCheck = function ($name) use ($all) {
            return [
                'bail',
                'required',
                'array',
                function ($attribute, $value, $fail) use ($all, $name) {
                    // Attribute's array must be a regular array with indices 0..count.
                    // Count depends on the context: either session_count or times (see $name).
                    if (array_keys($value) !== range(0, $all[$name] - 1)) {
                        $fail($attribute);
                    }
                },
            ];
        };

        // Dynamically create validation functions
        $arraySessionCheck = $createArrayCheck('session_count');
        $arrayGroupCheck = $createArrayCheck('times');

        return [
            // Step 1
            'course_name' => 'bail|required|string|max:100',
            'last_date' => 'bail|required|date_format:Y-m-d',
            'times' => 'bail|required|integer|min:1|max:10',
            'session_count' => 'bail|required|integer|min:1|max:10',
            'description' => 'bail|required|string',

            // Step 2
            'desc' => $arraySessionCheck,
            'desc.*' => 'bail|required|string',

            // Step 3
            'group_max_ppl' => $arrayGroupCheck,
            'group_max_ppl.*' => 'bail|required|integer|min:2|max:65535',
            'session_location' => $arrayGroupCheck,
            'session_location.*' => $arraySessionCheck,
            'session_location.*.*' => 'bail|required|string|max:150',
            'session_starttime' => $arrayGroupCheck,
            'session_starttime.*' => $arraySessionCheck,
            'session_endtime' => $arrayGroupCheck,
            'session_endtime.*' => $arraySessionCheck,
            'session_endtime.*.*' => 'bail|required|date_format:H:i',
            'session_starttime.*.*' => 'bail|required|date_format:H:i|before:session_endtime.*.*',
            'session_date' => $arrayGroupCheck,
            'session_date.*' => $arraySessionCheck,
            'session_date.*.*' => [
                'bail',
                'required',
                'date_format:Y-m-d',
                'after_or_equal:last_date',
                function ($attribute, $value, $fail) use ($all) {
                    // Safe to use values here because they have been validated
                    $sessionCount = (int) $all['session_count'];
                    $split = explode('.', $attribute, 3);
                    $group = (int) $split[1];
                    $session = (int) $split[2];

                    // By testing the "smaller than", we automatically test for overlaps as well
                    if ($session < $sessionCount - 1) {
                        // We get dates in Y-m-d format, and times in H:i format
                        // This has the funny consequence that we can simply do an ASCII string comparison
                        // instead of converting it to actual time and doing a shitton of parsing and validation
                        $myFormat = $value.' '.$all['session_endtime'][$group][$session];
                        $otherFormat = $all['session_date'][$group][$session + 1].' '.$all['session_endtime'][$group][$session + 1];

                        if ($myFormat > $otherFormat) {
                            $fail($attribute);
                        }
                    }
                },
            ],
        ];
    }
}
