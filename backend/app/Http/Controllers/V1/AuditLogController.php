<?php
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Audit_log;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $logs = Audit_log::with('user')
                ->when($request->user_id, fn($q, $v) => $q->where('user_id', $v))
                ->when($request->action, fn($q, $v) => $q->where('action', $v))
                ->when($request->model_type, fn($q, $v) => $q->where('model_type', 'like', "%{$v}%"))
                ->when($request->date_from, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
                ->when($request->date_to, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
                ->when($request->search, fn($q, $v) =>
                    $q->where(fn($q2) =>
                        $q2->where('description', 'like', "%{$v}%")
                            ->orWhere('ip_address', 'like', "%{$v}%")
                            ->orWhereHas('user', fn($q3) => $q3->where('name', 'like', "%{$v}%"))
                    )
                )
                ->latest()
                ->paginate(paginate_limit());

            return $this->paginated(
                $logs->through(fn($log) => [
                    'id'          => $log->id,
                    'action'      => $log->action,
                    'model_type'  => class_basename($log->model_type),
                    'model_id'    => $log->model_id,
                    'description' => $log->description,
                    'old_values'  => $log->old_values,
                    'new_values'  => $log->new_values,
                    'ip_address'  => $log->ip_address,
                    'user'        => $log->user
                        ? ['id' => $log->user->id, 'name' => $log->user->name, 'email' => $log->user->email]
                        : null,
                    'created_at'  => $log->created_at?->toDateTimeString(),
                ])
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
