<?php
namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,

            // Identity
            'name'                => $this->name,
            'email'               => $this->email,
            'phone'               => $this->phone,
            'avatar'              => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'address'             => $this->address,
            'date_of_birth'       => $this->date_of_birth?->toDateString(),
            'gender'              => $this->gender,
            'national_id'         => $this->national_id,

            // Account
            'account_status'      => $this->account_status,
            'is_locked_out'       => $this->isLockedOut(),
            'locked_until'        => $this->locked_until?->toDateTimeString(),

            // Password state
            'password_expires_at' => $this->password_expires_at?->toDateTimeString(),
            'is_password_expired' => $this->isPasswordExpired(),
            'password_changed_at' => $this->password_changed_at?->toDateTimeString(),

            // Session
            'last_login_at'       => $this->last_login_at?->toDateTimeString(),
            'last_login_ip'       => $this->last_login_ip,

            // Roles & Permissions
            'roles'               => $this->getRoleNames(),
            'permissions'         => $this->getAllPermissions()->pluck('name'),

            // Relations
            'branch'              => new BranchResource($this->whenLoaded('branch')),
            'store'               => new StoreResource($this->whenLoaded('store')),

            'created_at'          => $this->created_at?->toDateTimeString(),
        ];
    }
}
