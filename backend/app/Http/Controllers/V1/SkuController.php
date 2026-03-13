<?php
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SkuResource;
use App\Repositories\SkuRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SkuController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SkuRepository $skuRepo
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'is_active']);
            $skus    = $this->skuRepo->paginateFiltered($filters, paginate_limit());

            return $this->paginated($skus);
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
        try {
            $data = $request->validate([
                'name'          => ['required', 'string', 'max:150'],
                'code'          => ['nullable', 'string', 'max:50', 'unique:skus,code'],
                'unit'          => ['required', 'string', 'max:30'],
                'description'   => ['nullable', 'string'],
                'unit_cost'     => ['required', 'numeric', 'min:0'],
                'unit_price'    => ['required', 'numeric', 'min:0'],
                'reorder_level' => ['nullable', 'integer', 'min:0'],
                'is_active'     => ['boolean'],
                'metadata'      => ['nullable', 'array'],
            ]);

            $sku = $this->skuRepo->create($data);

            return $this->created(new SkuResource($sku), 'SKU created successfully');
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
            $sku = $this->skuRepo->findByIdOrFail($id);

            return $this->success(new SkuResource($sku));
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
            $sku  = $this->skuRepo->findByIdOrFail($id);
            $data = $request->validate([
                'name'          => ['sometimes', 'string', 'max:150'],
                'code'          => ['sometimes', 'string', 'max:50', 'unique:skus,code,' . $id],
                'unit'          => ['sometimes', 'string', 'max:30'],
                'description'   => ['nullable', 'string'],
                'unit_cost'     => ['sometimes', 'numeric', 'min:0'],
                'unit_price'    => ['sometimes', 'numeric', 'min:0'],
                'reorder_level' => ['nullable', 'integer', 'min:0'],
                'is_active'     => ['boolean'],
                'metadata'      => ['nullable', 'array'],
            ]);

            $sku = $this->skuRepo->update($sku, $data);

            return $this->created(new SkuResource($sku), 'SKU updated successfully');
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
            $sku = $this->skuRepo->findByIdOrFail($id);
            $this->skuRepo->delete($sku);

            return $this->success(null, 'SKU deleted successfully');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
