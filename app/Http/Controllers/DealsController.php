<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Promotion;
use Illuminate\Http\Request;

class DealsController extends Controller
{
    public function index(Request $request)
    {
        $query = Discount::active()
            ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()));

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . strtoupper($search) . '%');
            });
        }

        $discounts = $query->orderBy('created_at', 'desc')->get();

        $promotions = Promotion::active()
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()))
            ->orderBy('created_at', 'desc')
            ->get();

        // Track impressions — bulk increment in one query to avoid N writes
        if ($promotions->isNotEmpty()) {
            Promotion::whereIn('_id', $promotions->pluck('_id')->all())->increment('view_count');
        }

        return view('deals', compact('discounts', 'promotions'));
    }
}
