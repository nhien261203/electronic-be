<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\ProductImageRepositoryInterface;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    protected $repo;

    public function __construct(ProductImageRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function getByProduct($productId)
    {
        return response()->json($this->repo->getByProduct($productId));
    }

    public function store(Request $request, $productId)
    {
        $request->validate([
            'images' => 'required',
            'images.*' => 'image|mimes:jpeg,png,webp,jpg|max:2048',
        ]);

        $images = $request->file('images');

        if (!is_array($images)) {
            $images = [$images]; // convert 1 file sang mảng
        }

        $saved = $this->repo->storeImages($productId, $images);

        return response()->json(['message' => 'Images uploaded successfully', 'data' => $saved]);
    }


    public function setThumbnail($id)
    {
        $image = $this->repo->setThumbnail($id);

        return response()->json(['message' => 'Thumbnail updated successfully', 'data' => $image]);
    }

    public function destroy($id)
    {
        $this->repo->delete($id);

        return response()->json(['message' => 'Image deleted successfully']);
    }
}
