<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
        return Cache::flexible('products_cursor_' . request('cursor'), [1, 1], function(){
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
     * Create Product
     */
    public function store(ProductRequest $productRequest) 
    {
        DB::beginTransaction();

        try {
            $product = Product::create($productRequest->except(['images']));

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

        return response()->json(new ProductResource($product));
    }

    /**
     * Delete a product by id
     */
    public function destroy($id)
    {
       $isDeleted = Product::where('id', $id)->delete();

       if ($isDeleted) {
            return response()->json([
                'message' => 'Product deleted successfully',
            ]);
       }

        return response()->json([
            'message' => 'Encountered an error while deleting the product',
        ], 422);
    }
}