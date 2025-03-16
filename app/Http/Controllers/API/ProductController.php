<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\API\ProductResource;
use App\Models\Product;
use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *   title="API Documentation",
 *   version="1.0.0",
 * 
 *   @OA\Tag(
 *     name="Products",
 *     description="API endpoints for products"
 *   ),
 *   @OA\Contact(
 *     email="manishtechniz@gmail.com",
 *     name="Manish Techniz",
 *   )
 * )
 * @OA\Server(
 *   url="http://localhost:8000",
 *   description="Localhost"
 * )
 */
class ProductController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="List of products",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully fetched products",
     *         @OA\JsonContent(
     *             example={
     *                       "data": {
     *                           {
     *                               "id": 8,
     *                               "name": "Karl Lind",
     *                               "price": "953.47",
     *                               "discount": "78.0400",
     *                               "stock": 492,
     *                               "status": 1,
     *                               "images": {
     *                                   {
     *                                       "id": 22,
     *                                       "image_url": "https://example.com/image.jpg"
     *                                   }
     *                               },
     *                               "description": "Voluptas veritatis omnis eius quae iste porro tempora."
     *                           }
     *                       },
     *                       "per_page": 2,
     *                       "next_page_url": "https://example.com/api/products?cursor=eyJpZCI6NywiX3BvaW50c1RvTmV4dEl0ZW1zIjp0cnVlfQ",
     *                       "prev_page_url": null 
     *             }
     *         )
     *     )
     * )
     */
    public function index()
    {
        return Cache::flexible('product_lists_' . request('cursor'), [120, 1800], function(){
            /**
             * Use cursor pagination for best performance
             */
            $paginate = Product::where('status', 1)
                ->orderBy('id', 'desc')
                ->cursorPaginate(perPage: 10);

            return response()->json([
                'data'          => ProductResource::collection($paginate->items()),
                'per_page'      => $paginate->perPage(),
                'next_page_url' => $paginate->nextPageUrl(),
                'prev_page_url' => $paginate->previousPageUrl(),
            ]);
        });
    }

    /**
     * @OA\Post(
     *     path="/api/products/{id}/update",
     *     summary="Update a product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product id"
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             example={
     *                  "name": "Laptop",
     *                  "price": 1299.99,
     *                  "discount": 100.50,
     *                  "stock": 100,
     *                  "status": "0 or 1",
     *                  "images": {
     *                      "file - jpeg, png, jpg",
     *                      "id - Provide image id to delete image",
     *                  },
     *                  "description": "This is a description",
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             example={
     *                      "message": "Product updated successfully",
     *                      "data": {
     *                               "id": 8,
     *                               "name": "Karl Lind",
     *                               "price": "953.47",
     *                               "discount": "78.0400",
     *                               "stock": 492,
     *                               "status": "0 or 1",
     *                               "images": {
     *                                   {
     *                                       "id": 22,
     *                                       "image_url": "https://example.com/image.jpg"
     *                                   }
     *                               },
     *                               "description": "Voluptas veritatis omnis eius quae iste porro tempora."
     *                           }
     *             }
     *         )
     *     ),
     *      @OA\Response(
     *        response="422-A",
     *        description="Failed",
     *        @OA\JsonContent(
     *            example={
     *                  "message": "Encountered an error while updating the product",
     *             }
     *        )
     *     ),
     *      @OA\Response(
     *        response="422-B",
     *        description="Failed",
     *        @OA\JsonContent(
     *            example={
     *                  "message": "The images field must be an array",
     *                  "errors": {
     *                          "images": {
     *                              "The images field must be an array."
     *                          },
     *                          "other attributes": {
     *                              "other attributes message"
     *                          }
     *                  }
     *             }
     *        )
     *     )
     * )
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

        DB::beginTransaction();

        try {
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
        } catch(\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Encountered an error while updating the product',
            ], 422);
        }

        DB::commit();

        return response()->json([
            'message' => 'Product updated successfully',
            'data'    => new ProductResource($product),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             example={
     *                  "name": "Laptop",
     *                  "price": 1299.99,
     *                  "discount": 100.50,
     *                  "stock": 100,
     *                  "status": "0 or 1",
     *                  "images": {
     *                      "file - jpeg, png, jpg",
     *                  },
     *                  "description": "This is a description",
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Success",
     *         @OA\JsonContent(
     *             example={
     *                      "message": "Product created successfully",
     *                      "data": {
     *                               "id": 8,
     *                               "name": "Karl Lind",
     *                               "price": "953.47",
     *                               "discount": "78.0400",
     *                               "stock": 492,
     *                               "status": "0 or 1",
     *                               "images": {
     *                                   {
     *                                       "id": 22,
     *                                       "image_url": "https://example.com/image.jpg"
     *                                   }
     *                               },
     *                               "description": "Voluptas veritatis omnis eius quae iste porro tempora."
     *                           }
     *             }
     *         )
     *     ),
     *      @OA\Response(
     *          response="422-A",
     *          description="Failed",
     *          @OA\JsonContent(
     *              example={
     *                  "message": "The name field is required. (and 6 more errors)",
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *        response="422-B",
     *        description="Failed",
     *        @OA\JsonContent(
     *            example={
     *                  "message": "The name field is required. (and 6 more errors)",
     *                  "errors": {
     *                          "name": {
     *                              "The name field is required."
     *                          },
     *                          "description": {
     *                              "The description field is required."
     *                          },
     *                          "price": {
     *                              "The price field is required."
     *                          },
     *                          "discount": {
     *                              "The discount field is required."
     *                          },
     *                          "stock": {
     *                              "The stock field is required."
     *                          },
     *                          "status": {
     *                              "The status field is required."
     *                          },
     *                          "images": {
     *                              "The images field is required."
     *                          }
     *                  }
     *             }    
     *        )
     *     )
     * )
     */
    public function store(ProductRequest $productRequest) 
    {
        DB::beginTransaction();

        try {
            $product = Product::create(Arr::except($productRequest->validated(), ['images']));

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
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get specific product by id",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product id"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             example={
     *                      "message": "Product fetched successfully",
     *                      "data": {
     *                               "id": 8,
     *                               "name": "Karl Lind",
     *                               "price": "953.47",
     *                               "discount": "78.0400",
     *                               "stock": 492,
     *                               "status": "0 or 1",
     *                               "images": {
     *                                   {
     *                                       "id": 22,
     *                                       "image_url": "https://example.com/image.jpg"
     *                                   }
     *                               },
     *                               "description": "Voluptas veritatis omnis eius quae iste porro tempora."
     *                     }
     *             }
     *         )
     *     ),
     *      @OA\Response(
     *        response=404,
     *        description="Failed",
     *        @OA\JsonContent(
     *            example={
     *                "message": "Product not found"
     *            }
     *        )
     *     )
     * )
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

        return response()->json([
            'message' => 'Product fetched successfully',
            'data'    => new ProductResource($cachedProduct),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete a product",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product id"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             example={
     *                      "message": "Product deleted successfullysss",
     *             }
     *         )
     *     ),
     *      @OA\Response(
     *        response=422,
     *        description="Failed",
     *        @OA\JsonContent(
     *            example={
     *                "message": "Encountered an error while deleting the product"
     *            }
     *        )
     *     )
     * )
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