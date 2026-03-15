<?php
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateBranchRequest;
use App\Http\Resources\V1\BranchResource;
use App\Repositories\BranchRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly BranchRepository $branchRepo
    ) {
        $this->middleware('permission:Can create branches')->only('store');
        $this->middleware('permission:Can update branches')->only('update');
        $this->middleware('permission:Can delete branches')->only('delete');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $branches = $this->branchRepo->paginateWithStores();
            // return $branches;

            return $this->paginated(
                $branches
            );
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
            $branch = $this->branchRepo->create($request->validated());

            return $this->created(new BranchResource($branch), 'Branch created successfully');
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
            $branch = $this->branchRepo->findByIdOrFail($id, ['stores']);

            return $this->created(new BranchResource($branch));
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
    public function update(UpdateBranchRequest $request, string $id)
    {
        try {
            $branch = $this->branchRepo->findByIdOrFail($id);

            $branch = $this->branchRepo->update($branch, $request->validated());

            return $this->created(new BranchResource($branch), 'Branch updated successfully');
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
            $branch = $this->branchRepo->findByIdOrFail($id);
            $this->branchRepo->delete($branch);

            return $this->success(null, 'Branch deleted successfully');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
