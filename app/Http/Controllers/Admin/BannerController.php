<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Banner;
use App\Model\Category;
use App\Model\Product;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class BannerController extends Controller
{
    public function __construct(
       private Banner $banner,
       private Product $product,
       private Category $category
    ){}

    /**
     * @param Request $request
     * @return Factory|View|Application
     */
    function index(Request $request): View|Factory|Application
    {
        $query_param = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $banners = $this->banner->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('title', 'like', "%{$value}%");
                    $q->orWhere('id', 'like', "%{$value}%");
                }
            })->orderBy('id', 'desc');
            $query_param = ['search' => $request['search']];
        }else{
            $banners = $this->banner->orderBy('id', 'desc');
        }
        if ($request->is('admin/banner/home/*')) {
            $banners = $banners->whereNotNull('type')->paginate(Helpers::getPagination())->appends($query_param);
            
        } else {
            $banners = $banners->whereNull('type')->paginate(Helpers::getPagination())->appends($query_param);
        }
        


        // $products = $this->product->orderBy('name')->get();
        // $categories = $this->category->where(['parent_id'=>0])->orderBy('name')->get();
        return view('admin-views.banner.index', compact('banners','search'));
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     */
    function list(Request $request): View|Factory|Application
    {
        $query_param = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $banners = $this->banner->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('title', 'like', "%{$value}%");
                    $q->orWhere('id', 'like', "%{$value}%");
                }
            })->orderBy('id', 'desc');
            $query_param = ['search' => $request['search']];
        }else{
            $banners = $this->banner->orderBy('id', 'desc');
        }
        $banners = $banners->paginate(Helpers::getPagination())->appends($query_param);
        return view('admin-views.banner.list', compact('banners','search'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|max:255',
            'image' => 'required',
        ],[
            'title.required'=>translate('Title is required'),
            'image.required'=>translate('Image is required'),
        ]);

        $isHomeBanner = !empty($request['is_home_banner']) ?  $request['is_home_banner'] : 0;
        $banner = $this->banner;
        $banner->title = $request->title;
        $banner->title_ja = $request->title_ja;
        $banner->link = !empty($request->link) ? $request->link : null;
        if(!$isHomeBanner) {
            if ($request['item_type'] == 'product') {
                $banner->product_id = $request->product_id;
            } elseif ($request['item_type'] == 'category') {
                $banner->category_id = $request->category_id;
            }
        } else {
            $banner->type = (!empty($request->banner_type)) ? $request->banner_type : null;
            $banner->banner_order = (!empty($request->order)) ? $request->order : null;
        }  
        $banner->description = $request->description;
        $banner->description_ja = $request->description_ja;
        $banner->ad_section = !empty($request->ad_section)  ? $request->ad_section : null;
        $banner->banner_logo = Helpers::upload('banner/logo/', 'png', $request->file('banner_logo'));
        $banner->image = Helpers::upload('banner/', 'png', $request->file('image'));      
        $banner->save();        
        Toastr::success(translate('Banner added successfully!'));
        return back();
        
    }

    /**
     * @param $id
     * @return Factory|View|Application
     */
    public function edit($id): View|Factory|Application
    {
        $products = $this->product->orderBy('name')->get();
        $banner = $this->banner->find($id);
        $categories = $this->category->where(['parent_id'=>0])->orderBy('name')->get();
        return view('admin-views.banner.edit', compact('banner', 'products', 'categories'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $banner = $this->banner->find($request->id);
        $banner->status = $request->status;
        $banner->save();
        Toastr::success(translate('Banner status updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'title' => 'required|max:255',
        ], [
            'title.required' => 'Title is required!',
        ]);

        $isHomeBanner = !empty($request['is_home_banner']) ?  $request['is_home_banner'] : 0;

        $banner = $this->banner->find($id);
        $banner->title = $request->title;
        $banner->title_ja = $request->title_ja;
        $banner->ad_section = !empty($request->ad_section) ? $request->ad_section : null;
        $banner->link = !empty($request->link) ? $request->link : null;
        if(!$isHomeBanner) { 
            if ($request['item_type'] == 'product') {
                $banner->product_id = $request->product_id;
                $banner->category_id = null;
            } elseif ($request['item_type'] == 'category') {
                $banner->product_id = null;
                $banner->category_id = $request->category_id;
            }
        } else {
            $banner->type = (!empty($request->banner_type)) ? $request->banner_type : null;
            $banner->banner_order = (!empty($request->order)) ? $request->order : null;
        }
        $banner->description = $request->description;
        $banner->description_ja = $request->description_ja;
        $banner->image = $request->has('image') ? Helpers::update('banner/', $banner->image, 'png', $request->file('image')) : $banner->image;
        $banner->banner_logo = $request->has('banner_logo') ? Helpers::update('banner/logo/', $banner->banner_logo, 'png', $request->file('banner_logo')) : $banner->banner_logo;
        $banner->save();
        Toastr::success(translate('Banner updated successfully!'));
        return back();
        // return redirect()->route('admin.banner.home.add-new');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $banner = $this->banner->find($request->id);
        if (Storage::disk('public')->exists('banner/' . $banner['image'])) {
            Storage::disk('public')->delete('banner/' . $banner['image']);
        }
        $banner->delete();
        Toastr::success(translate('Banner removed!'));
        return back();
    }
}
