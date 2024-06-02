<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Helpers\Cart;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::getCartItems();
        $ids = Arr::pluck($cartItems,'product_id');
        $products = Product::query()->whereIn('id',$ids)->get();
        $cartItems = Arr::keyBy($cartItems,'product_id');
        $total = 0;

        foreach ($products as $product) {
            $total+=$product->price* $cartItems[$product->id]['quantity'];

        }

        return response()->json(
            [
                'total'=>$total,
                'products'=>$products,
                'cartItems'=>$cartItems

            ]
        );

    }


    public function add(Request $request, Product $product)
    {
        $quantity = $request->post('quantity',1);

        $user = $request->user();
        if($user){
            $cartItem = CartItem::where([
                'user_id'=>$user->id,
                "product_id"=>$product->id])->first();

            if($cartItem){
                $cartItem->quantity += $quantity;
                $cartItem->update();
            }else{
                $data =[
                     'user_id'=>$request->user()->id,
                     'product_id'=>$product->id,
                     'quantity'=>$quantity,

                ];

                CartItem::create($data);
            }

            return response()->json([
                'count'=>Cart::getCartItemsCount()
            ],200);
        }else{
            $cartItems = json_decode($request->cookie('cart_items','[]'),true);
            $productFound =false;

            foreach ($cartItems as &$item){
                if($item['product_id']=== $product->id){
                   $item[$quantity]+=$quantity;
                   $productFound=true;

                   break;
                }
            }
        }

        if(!$productFound){
            $cartItems[]=[
                'user_id'=>null,
                'product_id'=>$product->id,
                'quantity'=>$quantity,
                'price'=>$product->price
            ];
        }

        Cookie::queue('cart_items',json_decode($cartItems),60*24*30);

        return response()->json([
            'count'=>Cart::getCookieCartItems($cartItems)
        ]);
      }

      public function remove(Request $request, Product $product)
      {
          $user= $request->user();
          if($user){
              $cartItem = CartItem::query()->where([
                  'user_id'=>$user->id,
                  'product_id'=>$product->id,

              ])->first();

              if($cartItem){
                  $cartItem->delete();

              }
              return response()->json([
                  'count'=>Cart::getCartItemsCount(),
              ],401);
          }else{
        $cartItems = json_decode($request->cookie('cart_items','[]'),true);
              foreach ($cartItems as $i=>&$cartItem) {
                  if($cartItem['product_id']===$product->id){
                  array_splice($cartItems,$i,1);
                  break;
                  }
              }
        }
          Cookie::queue('cart_items',json_decode($cartItems),60*24*30);


         return  response()->json([
             'count'=>Cart::getCartItemsCount($cartItems)
         ]);

      }


    public function updateQuantity(Request $request, Product $product)
    {
        $quantity = (int)$request->post('quantity');

        $user = $request->user();
        if ($user) {
            $cartItem = CartItem::query()->where([
                'user_id' => $user->id,
                'product_id' => $product->id,

            ])->update(['quantity' => $quantity]);


            return response()->json([
                'count' => Cart::getCartItemsCount(),
            ], 200);
        } else {
            $cartItems = json_decode($request->cookie('cart_items', '[]'), true);
            foreach ($cartItems as &$cartItem) {
                if ($cartItem['product_id'] === $product->id) {
                    $cartItem['quantity'] = $quantity;
                    break;
                }
            }

            Cookie::queue('cart_items',json_decode($cartItems),60*24*30);


            return  response()->json([
                'count'=>Cart::getCartItemsCount($cartItems)
            ]);
        }

    }

}
