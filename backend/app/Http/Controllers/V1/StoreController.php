<?php
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreStoreRequest;
use App\Http\Resources\V1\StoreResource;
use App\Models\User;
use App\Repositories\StoreRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly StoreRepository $storeRepo
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user    = User::where('id', Auth::id())->first();
            $filters = $request->only(['search', 'is_active', 'branch_id']);

            // Scope branch managers to their own branch

            $stores = $this->storeRepo->paginateFiltered($filters, 15);

            return $this->paginated($stores);
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
    public function store(StoreStoreRequest $request)
    {
        try {
            $store = $this->storeRepo->create($request->validated());

            return $this->created(
                new StoreResource($store->load('branch')),
                'Store created successfully'
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $store = $this->storeRepo->findByIdOrFail($id, ['branch']);

            return $this->created(new StoreResource($store));
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
        try {
            $store = $this->storeRepo->findByIdOrFail($id);

            $store = $this->storeRepo->update($store, $request->only([
                'name', 'location', 'phone', 'is_active',
            ]));

            return $this->success(new StoreResource($store), 'Store updated successfully');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $store = $this->storeRepo->findByIdOrFail($id);

            $this->storeRepo->delete($store);

            return $this->success(null, 'Store deleted successfully');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
