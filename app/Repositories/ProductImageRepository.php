<?php 

namespace App\Repositories;

use App\Interfaces\ProductImageRepositoryInterface;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProductImageRepository implements ProductImageRepositoryInterface
{
    public function getByProduct($productId)
    {
        return ProductImage::where('product_id', $productId)->get();
    }

    private function handleImageUpload($file)
    {
        $image = Image::make($file)
            ->resize(600, 600, function ($c) {
                $c->aspectRatio();
                $c->upsize();
            })
            ->encode('webp', 80);

        $filename = 'products/' . uniqid('image_') . '.webp';
        Storage::disk('public')->put($filename, $image);

        return Storage::url($filename);
    }

    public function storeImages($productId, array $images)
    {
        $created = [];

        foreach ($images as $index => $imageFile) {
            $imageUrl = $this->handleImageUpload($imageFile);

            $created[] = ProductImage::create([
                'product_id' => $productId,
                'image_url' => $imageUrl,
                'is_thumbnail' => $index === 0 && !ProductImage::where('product_id', $productId)->where('is_thumbnail', true)->exists(),
            ]);
        }

        return $created;
    }

    public function setThumbnail($imageId)
    {
        $image = ProductImage::findOrFail($imageId);

        // Unset thumbnail của các ảnh khác cùng sản phẩm
        ProductImage::where('product_id', $image->product_id)
            ->update(['is_thumbnail' => false]);

        $image->is_thumbnail = true;
        $image->save();

        return $image;
    }

    public function delete($id)
    {
        $image = ProductImage::findOrFail($id);
        $relativePath = str_replace('/storage/', '', $image->image_url);

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }

        return $image->delete();
    }
}
