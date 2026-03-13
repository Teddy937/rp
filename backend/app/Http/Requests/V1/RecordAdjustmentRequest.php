<?php
namespace App\Http\Requests\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class RecordAdjustmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_id'  => ['required', 'exists:stores,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'sku_id'    => ['required', 'exists:skus,id'],
            'quantity'  => ['required', 'numeric', 'min:0.0001'],
            'direction' => ['required', 'in:in,out'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'notes'     => ['required', 'string', 'max:500'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        // Get the validation errors
        $errors = $validator->errors();

        // Create a custom response structure
        $response = response()->json([
            'status'  => Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
            'code'    => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => 'Validation failed',
            'data'    => null,
            'errors'  => $errors,
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
