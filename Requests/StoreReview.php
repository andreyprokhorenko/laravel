<?php

namespace App\Http\Requests;

use App\Repositories\Eloquent\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreReview extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->hasPermission('create-reviews');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $maxFileSize = config('filesystems.images.review.max_file_size');
        $minWidth = config('filesystems.images.blog.min_width');
        $minHeight = config('filesystems.images.blog.min_height');
        $maxWidth = config('filesystems.images.blog.max_width');
        $maxHeight = config('filesystems.images.blog.max_height');

        $maxPosition = $this->getMaxPosition();
        $highlightedTypesString = $this->getHighlightedTypesString();
        $statusesString = $this->getAvailableStatusesString();

        return [
            'title' => 'required|min:3|unique:reviews,title,' . $this->request->get('id') . ',id,language_id,' . $this->request->get('language_id') . ',type_id,' . $this->request->get('type_id'),
            'slug' => 'required|min:3|unique:reviews,slug,' . $this->request->get('id') . ',id,language_id,' . $this->request->get('language_id') . ',type_id,' . $this->request->get('type_id'),
            'icon' => 'mimes:jpg,jpeg,png,gif|max:' . $maxFileSize . '|dimensions:min_width=' . $minWidth . ',min_height=' . $minHeight . ',max_width=' . $maxWidth . ',max_height=' . $maxHeight,
            'content' => 'required|min:3',
            'type_id' => 'required|numeric|exists:review_types,id',
            'language_id' => 'required|numeric|exists:languages,id',
            'position' => 'int|min:1|max:' . $maxPosition,
            'is_highlighted' => 'in:' . $highlightedTypesString,
            'status' => 'in:' . $statusesString
        ];
    }

    public function messages()
    {
        $minWidth = config('filesystems.images.blog.min_width');
        $minHeight = config('filesystems.images.blog.min_height');
        $maxWidth = config('filesystems.images.blog.max_width');
        $maxHeight = config('filesystems.images.blog.max_height');

        return [
            'title.requred' => 'Review title is required!',
            'icon.dimensions' => 'The Image has invalid dimensions, it should be: min-' . $minWidth . 'x' . $minHeight . ', max-' . $maxWidth . 'x' . $maxHeight,
            'content.required' => 'Review text is required',
            'type_id.required' => 'Review type is required',
            'type_id.numeric' => 'Invalid review type',
            'type_id.exists' => 'Invalid review type',
            'language_id.required' => 'Review language is required',
            'language_id.numeric' => 'Invalid review language',
            'language_id.exists' => 'Invalid review language',
        ];
    }

    private function getMaxPosition()
    {
        $existingReviewsCount = Review::where('language_id', $this->request->get('language_id'))
                                        ->where('type_id', $this->request->get('type_id'))
                                        ->count();
        if ($this->request->has('id')) {
            $review = Review::find($this->request->get('id'));
        }
        if(!isset($review) || !$review || $review->language_id != $this->request->get('language_id') || $review->type_id != $this->request->get('type_id')) {
            $existingReviewsCount ++;
        }

        return $existingReviewsCount;
    }

    private function getHighlightedTypesString()
    {
        $typesArr = Review::getHighlightedTypes();
        $typesStr = '';
        foreach ($typesArr as $type => $text) {
            $typesStr .= $type . ',';
        }

        return substr($typesStr, 0, -1);
    }

    private function getAvailableStatusesString()
    {
        $statusesArr = Review::getChangableStatuses();
        $statusesStr = '';
        foreach ($statusesArr as $status => $text) {
            $statusesStr .= $status . ',';
        }

        return substr($statusesStr, 0, -1);
    }
}
