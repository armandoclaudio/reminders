<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ReminderRepeats implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $data = explode(' ', $value);

        if(sizeof($data) != 2) return false;

        $qty = $data[0];
        $type = $data[1];

        if(! is_numeric($qty)) return false;
        if(intval($qty) <= 0) return false;
        if(strpos($qty, '.') !== false) return false;
        if(! in_array($type, ['days', 'weeks', 'months', 'years'])) return false;

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid format';
    }
}
