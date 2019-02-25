<?php

namespace App\Http\Requests;

use App\Repositories\Eloquent\Models\Attribute;
use App\Services\AbstractService;
use App\Services\ReviewTypeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewAttribute extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->hasPermission('create-reviewAttributes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->request->get('id')) {
            $uniqueIdPart = ',' . $this->request->get('id');
        } else {
            $uniqueIdPart = '';
        }
        $statusesString = $this->getAvailableStatusesString();
        $typesString = $this->getAvailableTypesString();

        /** @var ReviewTypeService $reviewTypeService */
        $reviewTypeService = AbstractService::getService(ReviewTypeService::class);
        $availableReviewTypes = $reviewTypeService->getAvailableReviewTypesAsString();

        return [
            'name' => 'required|max:125|unique:attributes,name' . $uniqueIdPart,
            'type' => 'required|in:' . $typesString,
            'status' => 'required|numeric|in:' . $statusesString,
            'review_type_attribute.*' => 'in:' . $availableReviewTypes,
        ];
    }

    /**
     * Get validation messages that apply to errors
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    private function getAvailableStatusesString(): string
    {
        return implode(',', array_keys(Attribute::getAllStatuses()));
    }

    private function getAvailableTypesString(): string
    {
        return implode(',', array_keys(Attribute::getAvailableTypes()));
    }

}
