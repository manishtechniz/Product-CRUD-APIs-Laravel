<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\API\ProductResource;
use App\Models\Product;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * List all products
     */
    public function index()
    {
        return Cache::flexible('product_lists_' . request('cursor'), [5, 1800], function(){
            /**
             * Use cursor pagination for best performance
             */
            $paginate = Product::where('status', 1)
                ->orderBy('id', 'desc')
                ->cursorPaginate(perPage: 2);

            return response()->json([
                'data'          => ProductResource::collection($paginate->items()),
                'per_page'      => $paginate->perPage(),
                'next_page_url' => $paginate->nextPageUrl(),
                'prev_page_url' => $paginate->previousPageUrl(),
            ]);
        });
    }

    /**
     * Update a product by id
     */
    public function update(ProductRequest $productRequest, $id)
    {
        /**
         * Convert POST request to PUT request for preventing violation in case of update resource
         */
        if (request()->isMethod('post')) {
            request()->setMethod('put');
        }

        $validatedData = $productRequest->validated();

        $product = Product::find($id);

        if (request()->has('name')) {
            $product->name = $validatedData['name'];
        }

        if (request()->has('description')) {
            $product->description = $validatedData['description'];
        }

        if (request()->has('price')) {
            $product->price = $validatedData['price'];
        }

        if (request()->has('discount')) {
            $product->discount = $validatedData['discount'];
        }

        if (request()->has('stock')) {
            $product->stock = $validatedData['stock'];
        }

        if (request()->has('status')) {
            $product->status = $validatedData['status'];
        }

        $product->save();

        /**
         * Insert new product images and delete existing image if image id provided
         */
        if (! empty($validatedData['images'])) {
            foreach($validatedData['images'] as $fileOrImageId) {
                if ($fileOrImageId instanceof \Illuminate\Http\UploadedFile) {
                    $product->images()->create([
                        'path' => $fileOrImageId->store("product/{$product->id}", 'public'),
                    ]);
                }
    
                if (filter_var($fileOrImageId, FILTER_VALIDATE_INT)) {
                    if (! empty($productImage = $product->images()->find($fileOrImageId))) {
                        Storage::delete("{$productImage?->path}");
    
                        $productImage->delete();
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * Create Product
     */
    public function store(ProductRequest $productRequest) 
    {
        DB::beginTransaction();

        try {
            $product = Product::create($productRequest->validated()->except(['images']));

            /**
             * Insert product images
             */
            foreach(request()->file('images') as $file) {
                $product->images()->create([
                    'path' => $file->store("product/{$product->id}", 'public'),
                ]);
            }
        } catch(\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Encountered an error while creating the product',
            ], 422);
        }

        DB::commit();

        return response()->json([
            'message' => 'Product created successfully',
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * Get a product by id
     */
    public function product($id)
    {
        $product = Product::find($id);

        if (empty($product)) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        /**
         * Product cached
         */
        $cachedProduct = Cache::remember('product_id_'. $id, 900, function() use ($product) {
            return $product;
        });

        return response()->json(new ProductResource($cachedProduct));
    }

    /**
     * Delete a product by id
     */
    public function destroy($id)
    {
        $product = Product::where('id', $id)->find($id);

        foreach($product?->images ?? [] as $image) {
            Storage::disk('public')->delete("{$image->path}");
        }

        if (! empty($product) 
            && $product->delete()
        ) {
            /**
             * Delete cached data
             */
            Cache::forget('product_lists_*');
            Cache::forget('product_id_'. $id);

            return response()->json([
                'message' => 'Product deleted successfully',
            ]);
        }

        return response()->json([
            'message' => 'Encountered an error while deleting the product',
        ], 422);
    }
}