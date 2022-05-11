<?php

namespace App\Http\Controllers\API;

use App\Brand;
use App\Category;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryCollection;
use App\Product;
use App\ProductImage;
use App\Http\Resources\Product as ResourcesProduct;
use App\Http\Resources\ProductBidCollection;
use App\Http\Resources\ProductCollection;
use App\ProductBid;
use App\ProductFavourite;
use App\ProductSize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $products = Product::paginate(25);
        if ($request->featured == 1) {
            $products = Product::where('featured', 1)->paginate(25);
        }

        return new ProductCollection($products);
    }

    public function homeproducts(){
        $laybull_picks=Product::where('featured',1)->limit(10)->get();
        $latest=Product::orderBy('id','desc')->limit(10)->get();
        $release=Product::where('release',1)->limit(10)->get();
        $popular=Product::where('popular',1)->limit(10)->get();
        $categories=Category::all();

        $laybull_picks=new ProductCollection($laybull_picks);
        $latest=new ProductCollection($latest);
        $release=new ProductCollection($release);
        $popular=new ProductCollection($popular);


        $categories=new CategoryCollection($categories);


        return response()->json([
            'categories'=>$categories,
            'laybull_picks'=>$laybull_picks,
            'latest'=>$latest,
            'release'=>$release,
            'popular'=>$popular,

        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product = new Product();
        $product->category_id = $request->category_id ;
        $product->brand_id = $request->brand_id ;
        $product->name = $request->name ;
        $product->price = $request->price ;
        $product->feature_image = $request->feature_image ;
        $product->color = $request->color ;
        $product->feature_image = $request->feature_image ;
        $product->feature_image = $request->feature_image ;
        $product->save();

        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $productFeatureImg = Str::random(20). '.' . $file->getClientOriginalExtension();
            Storage::disk('public_product')->put($productFeatureImg, File::get($file));
            $imgeurl = url('media/product/'.$productFeatureImg);
        }

        if ($request->hasFile('images')) {
            ProductImage::where('product_id', $product->id)->delete();
            $filename = 'jewellery.jpg';
            foreach ($request->file('images') as $image) {
                $a = \Str::random(5);
                $destinationPath = 'file/'; // upload path
                $nameonly = preg_replace('/\..+$/', '', $image->getClientOriginalName());
                $filename = $nameonly . '_' . $a . '_' . '.' . $image->getClientOriginalExtension();
                $image->move('images', $filename);
                ProductImage::create(['product_id' => $product->id, 'image' => $filename]);
            }
        }

        return new ResourcesProduct($product);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::with('images')->find($id);
        return new ResourcesProduct($product);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        $product->update($request->all());
        $product = Product::find($id);
        return new ResourcesProduct($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        $product->delete();
        return response()->json([
            'success' => true
        ]);
    }
    public function favouriteproducts(){
        $favouriteproducts=ProductFavourite::where('user_id',auth()->user()->id)->pluck('product_id');
        $products=Product::whereIn('id',$favouriteproducts)->get();
        return new ProductCollection($products);
    }
    public function productsizes($id)
    {
        $sizes = ProductSize::pluck('text');
        if (isset($id)) {
            $sizes = ProductSize::where('category_id', $id)->pluck('text');
        }
        return response()->json([
            'data' => $sizes
        ]);
    }
    public function brandCategory(){
        $data['brand']=Brand::latest()->get();
        $data['category']=Category::latest()->get();
        return $this->formatResponse('success','data get successful',$data);
    }

}
