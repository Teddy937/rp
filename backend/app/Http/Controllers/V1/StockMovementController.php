<?php
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\InitiateTransferRequest;
use App\Http\Requests\V1\RecordAdjustmentRequest;
use App\Http\Requests\V1\RecordSaleRequest;
use App\Http\Resources\V1\StockMovementResource;
use App\Repositories\StockMovementRepository;
use App\Services\StockMovementService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockMovementController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly StockMovementService $movementService,
        private readonly StockMovementRepository $movementRepo
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user    = Auth::user();
            $filters = $request->only([
                'type', 'status', 'sku_id', 'date_from', 'date_to', 'reference_no',
            ]);
            $movements = $this->movementRepo->paginateFiltered($filters, 15);
            return $this->paginated($movements);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $movement = $this->movementRepo->findByIdOrFail($id, [
                'sku', 'fromStore.branch', 'toStore.branch', 'createdBy', 'approvedBy',
            ]);

            return $this->created(new StockMovementResource($movement));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Record a sale.
     */
    public function sale(RecordSaleRequest $request): JsonResponse
    {
        try {
            $movement = $this->movementService->recordSale(
                $request->validated(),
                Auth::user()
            );

            return $this->created(
                new StockMovementResource($movement->load(['sku', 'fromStore', 'createdBy'])),
                'Sale recorded successfully'
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Initiate a transfer request.
     */
    public function transfer(InitiateTransferRequest $request): JsonResponse
    {
        try {
            $movement = $this->movementService->initiateTransfer(
                $request->validated(),
                Auth::user()
            );

            return $this->created(
                new StockMovementResource($movement->load(['sku', 'fromStore', 'toStore', 'createdBy'])),
                'Transfer initiated and pending approval'
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Approve a pending transfer.
     */
    public function approveTransfer(int $id): JsonResponse
    {
        try {
            $movement = $this->movementRepo->findByIdOrFail($id);
            $movement = $this->movementService->approveTransfer($movement, Auth::user());

            return $this->success(
                new StockMovementResource($movement->load(['sku', 'fromStore', 'toStore', 'approvedBy'])),
                'Transfer approved successfully'
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Reject a pending transfer.
     */
    public function rejectTransfer(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate(['reason' => ['required', 'string', 'max:500']]);

            $movement = $this->movementRepo->findByIdOrFail($id);

            $movement = $this->movementService->rejectTransfer(
                $movement,
                Auth::user(),
                $request->input('reason')
            );

            return $this->success(
                new StockMovementResource($movement),
                'Transfer rejected'
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Record a stock adjustment.
     */
    public function adjustment(RecordAdjustmentRequest $request): JsonResponse
    {
        try {
            $movement = $this->movementService->recordAdjustment(
                $request->validated(),
                Auth::user(),
            );

            return $this->created(
                new StockMovementResource($movement->load(['sku', 'createdBy'])),
                'Adjustment recorded successfully'
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Record a procurement (stock in from central purchasing).
     */
    public function procurement(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'store_id'  => ['required', 'exists:stores,id'],
                'branch_id' => ['required', 'exists:branches,id'],
                'sku_id'    => ['required', 'exists:skus,id'],
                'quantity'  => ['required', 'numeric', 'min:0.0001'],
                'unit_cost' => ['nullable', 'numeric', 'min:0'],
                'notes'     => ['nullable', 'string', 'max:500'],
            ]);

            $movement = $this->movementService->recordProcurement(
                $request->all(),
                Auth::user()
            );

            return $this->created(
                new StockMovementResource($movement->load(['sku', 'toStore', 'createdBy'])),
                'Procurement recorded successfully'
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Pending transfers list.
     */
    public function pendingTransfers(Request $request): JsonResponse
    {
        try {
            $user    = Auth::user();
            $filters = [];

            if (user_can('Can view branches')) {
                $filters['branch_id'] = $user->branch_id;
            }

            $pending = $this->movementRepo->getPendingTransfers($filters);

            return $this->success(StockMovementResource::collection($pending));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
