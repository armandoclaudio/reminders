<?php

namespace App\Http\Requests;

use App\Reminder;
use App\Rules\ReminderRepeats;
use Illuminate\Foundation\Http\FormRequest;

class RemindersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return request()->isMethod('post') ||
            request()->user()->id == $this->route('reminder')->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(request()->isMethod('delete')) return [];

        return [
            'title' => 'required|string|max:255',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'repeats' => ['nullable', new ReminderRepeats],
        ];
    }
}
