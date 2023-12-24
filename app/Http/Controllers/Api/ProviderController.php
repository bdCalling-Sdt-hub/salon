<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderRequest;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function postProvider(ProviderRequest $request)
    {
        $cover_photo = time() . '.' . $request->coverPhoto->extension();
        $request->coverPhoto->move(public_path('images'), $cover_photo);

        $photo_gallary = time() . '.' . $request->photoGallary->extension();
        $request->photoGallary->move(public_path('images'), $photo_gallary);

        $post_provider = Provider::create([
            'category_id' => $request->input('catId'),
            'business_name' => $request->input('businessName'),
            'address' => $request->input('address'),
            'description' => $request->input('description'),
            'available_service_our' => $request->input('serviceOur'),
            'cover_photo' => $cover_photo,
            'gallary_photo' => $photo_gallary,
        ]);
    }
}
