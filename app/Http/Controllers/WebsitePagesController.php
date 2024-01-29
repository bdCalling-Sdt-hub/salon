<?php

namespace App\Http\Controllers;

use App\Models\WebsitePage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebsitePagesController extends Controller
{
    public function showWebsitePages()
    {
        $website_pages = WebsitePage::all();
        return ResponseMethod('Website pages list', $website_pages);
    }

    public function showSinglePages($id)
    {
        $website_page = WebsitePage::where('id', $id)->first();
        if ($website_page) {
            return ResponseMethod('Singe Page details', $website_page);
        } else {
            return ResponseMessage('Page Not Exist');
        }
    }

    public function addWebsitePage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_title' => 'required|unique:website_pages|max:20',
            'page_description' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $website_page = new WebsitePage();
        $website_page->page_title = $request->page_title;
        $website_page->page_description = $request->page_description;
        $website_page->save();
        return ResponseMethod('Page added Successfully', $website_page);
    }

    public function updateWebsitePage(Request $request, $id)
    {
        $website_page = WebsitePage::where('id', $id)->first();
        if ($website_page) {
            $validator = Validator::make($request->all(), [
                'page_title' => 'required|max:20',
                'page_description' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $website_page->page_title = $request->page_title;
            $website_page->page_description = $request->page_description;
            $website_page->update();
            return responseMethod('Website page update successfully', $website_page);
        } else {
            return responseMessage('Website page not found');
        }
    }

    public function deleteWebsitePage($id)
    {
        $website_page = WebsitePage::where('id', $id)->first();
        if ($website_page) {
            $website_page->delete();
            return ResponseMessage('Page deleted successfully');
        } else {
            return ResponseMessage('Page not found');
        }
    }
}
