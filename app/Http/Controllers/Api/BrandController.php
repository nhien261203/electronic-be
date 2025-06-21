<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\BrandRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BrandController extends Controller
{
    protected $brandRepository;

    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $brand = $this->brandRepository->store($request->all());

            return response()->json([
                'message' => 'Brand created successfully',
                'data' => $brand
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi tạo thương hiệu.'], 500);
        }
    }
    public function index()
    {
        $brands = $this->brandRepository->getAll();
        return response()->json(['data' => $brands]);
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'country' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $brand = $this->brandRepository->update($id, $request->all());

        return response()->json([
            'message' => 'Brand updated successfully',
            'data' => $brand
        ], 200);
    }

    public function destroy($id)
    {
        $this->brandRepository->delete($id);

        return response()->json([
            'message' => 'Brand deleted successfully'
        ], 200);
    }
    public function show($id)
    {
        try {
            $brand = $this->brandRepository->findById($id);

            return response()->json([
                'message' => 'Brand retrieved successfully',
                'data' => $brand
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Brand not found',
            ], 404);
        }
    }
}
