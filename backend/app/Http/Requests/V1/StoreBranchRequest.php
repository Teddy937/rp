<?php
namespace App\Http\Requests\V1;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class StoreBranchRequest extends FormRequest
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
            'name'      => ['required', 'string', 'max:100'],
            'code'      => ['required', 'string', 'max:20', 'unique:branches,code'],
            'location'  => ['nullable', 'string', 'max:200'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
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
