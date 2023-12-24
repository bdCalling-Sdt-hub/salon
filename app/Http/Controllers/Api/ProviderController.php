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

        $image = array();
        if ($files = $request->file('photoGellary')) {
            foreach ($files as $file) {
                $image_name = md5(rand(1000, 10000));
                $ext = strtolower($file->getClientOriginalExtension());
                $image_full_name = $image_name . '.' . $ext;
                $upload_path = 'public/images/';
                $image_url = $upload_path . $image_full_name;
                $file->move($upload_path, $image_full_name);
                $image[] = $image_url;
            }
        }
        $post_provider = Provider::create([
            'category_id' => $request->input('catId'),
            'business_name' => $request->input('businessName'),
            'address' => $request->input('address'),
            'description' => $request->input('description'),
            'available_service_our' => $request->input('serviceOur'),
            'cover_photo' => $cover_photo,
            'gallary_photo' => implode('|', $image),
        ]);
        return response()->json([
            'success'
        ]);
    }
}
