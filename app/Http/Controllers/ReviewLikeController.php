<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ReviewLikeController extends Controller
{
    public function toggle(Review $review)
    {
        auth()->user()->likedReviews()->toggle($review->id);

        return back();
    }
}
