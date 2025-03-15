<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ImageOrId implements ValidationRule
{
    protected const MAX_IMAGE_SIZE_IN_BYTES = 2 * 1024 * 1024;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {   
        /**
         * Check if the value is an integer or an image
         */
        if (! filter_var($value, FILTER_VALIDATE_INT)
            && ! $value instanceof \Illuminate\Http\UploadedFile
        ) {
            $fail('The :attribute must be an integer or an image');
        }

        /**
         * Check image mime type
         */
        if ($value instanceof \Illuminate\Http\UploadedFile
            && ! in_array($value->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg'])
        ) {
             $fail('Only JPEG, PNG and JPG images are allowed.');
        }

        /**
         * File size validation
         */
        if ($value instanceof \Illuminate\Http\UploadedFile
            && $value->getSize() > self::MAX_IMAGE_SIZE_IN_BYTES
        ) {
             $fail('Image size must be less than 2MB.');
        }
    }
}
