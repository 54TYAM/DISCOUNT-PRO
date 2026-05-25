<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, string $productId)
    {
        $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($productId);
        $user    = auth()->user();

        // Only customers who have ordered the product can review it
        $hasOrdered = Order::where('user_id', (string) $user->_id)
            ->where('items.product_id', (string) $product->_id)
            ->exists();

        if (! $hasOrdered) {
            return back()->with('error', 'You can only review products you have purchased.');
        }

        // One review per user per product — update if exists
        Review::updateOrCreate(
            ['user_id' => (string) $user->_id, 'product_id' => (string) $product->_id],
            [
                'user_name' => $user->name,
                'rating'    => (int) $request->rating,
                'comment'   => $request->comment,
            ]
        );

        return back()->with('success', 'Thanks for your review!');
    }

    public function destroy(string $reviewId)
    {
        $review = Review::findOrFail($reviewId);
        abort_unless((string) $review->user_id === (string) auth()->user()->_id, 403);
        $review->delete();
        return back()->with('success', 'Review removed.');
    }
}
