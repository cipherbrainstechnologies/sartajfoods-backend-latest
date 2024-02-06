<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\DeliveryMan;
use App\Model\DMReview;
use Box\Spout\Common\Exception\InvalidArgumentException;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliveryManController extends Controller
{
    public function __construct(
        private DeliveryMan $delivery_man,
        private DMReview $dm_review
    ){}

    public function index(): Factory|View|Application
    {
        return view('admin-views.delivery-man.index');
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     */
    public function list(Request $request): View|Factory|Application
    {
        $query_param = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $delivery_men = $this->delivery_man->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }else{
            $delivery_men = $this->delivery_man;
        }
        $delivery_men = $delivery_men->latest()->where('application_status', 'approved')->paginate(Helpers::getPagination())->appends($query_param);

        return view('admin-views.delivery-man.list', compact('delivery_men','search'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $key = explode(' ', $request['search']);
        $delivery_men = $this->delivery_man->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('f_name', 'like', "%{$value}%")
                    ->orWhere('l_name', 'like', "%{$value}%")
                    ->orWhere('email', 'like', "%{$value}%")
                    ->orWhere('phone', 'like', "%{$value}%")
                    ->orWhere('identity_number', 'like', "%{$value}%");
            }
        })->get();
        return response()->json([
            'view' => view('admin-views.delivery-man.partials._table', compact('delivery_men'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     */
    public function reviews_list(Request $request): View|Factory|Application
    {
        $query_param = [];
        $search = $request['search'];
        if($request->has('search'))
        {
             $key = explode(' ', $request['search']);
             $delivery_men = $this->delivery_man->where(function ($q) use ($key) {
                        foreach ($key as $value) {
                            $q->orWhere('f_name', 'like', "%{$value}%")
                                ->orWhere('l_name', 'like', "%{$value}%");
                        }
            })->pluck('id')->toArray();
            $reviews = $this->dm_review->with(['delivery_man', 'customer'])->whereIn('delivery_man_id',$delivery_men);
            $query_param = ['search' => $request['search']];
        }else
        {
            $reviews = $this->dm_review->with(['delivery_man', 'customer']);
        }
        $reviews = $reviews->latest()->paginate(Helpers::getPagination())->appends($query_param);
        return view('admin-views.delivery-man.reviews-list', compact('reviews','search'));
    }

    /**
     * @param $id
     * @return Factory|View|Application
     */
    public function preview($id): View|Factory|Application
    {
        $dm = $this->delivery_man->with(['reviews'])->where(['id' => $id])->first();
        $reviews = $this->dm_review->where(['delivery_man_id' => $id])->latest()->paginate(Helpers::getPagination());
        return view('admin-views.delivery-man.view', compact('dm', 'reviews'));
    }

    /**
     * @param Request $request
     * @return Redirector|Application|RedirectResponse
     */
    public function store(Request $request): Redirector|RedirectResponse|Application
    {
        $request->validate([
            'f_name' => 'required|max:100',
            'email' => 'required|regex:/(.+)@(.+)\.(.+)/i|unique:delivery_men',
            'phone' => 'required|unique:delivery_men',
            'password' => 'required|min:8',
            'password_confirmation' => 'required_with:password|same:password|min:8'
        ], [
            'f_name.required' => translate('First name is required!'),
            'email.required' => translate('Email is required!'),
            'email.unique' => translate('Email must be unique!'),
            'phone.required' => translate('Phone is required!'),
            'phone.unique' => translate('Phone must be unique!'),
        ]);

        if ($request->has('image')) {
            $image_name = Helpers::upload('delivery-man/', 'png', $request->file('image'));
        } else {
            $image_name = 'def.png';
        }

        $id_img_names = [];
        if (!empty($request->file('identity_image'))) {
            foreach ($request->identity_image as $img) {
                $identity_image = Helpers::upload('delivery-man/', 'png', $img);
                $id_img_names[] = $identity_image;
            }
            $identity_image = json_encode($id_img_names);
        } else {
            $identity_image = json_encode([]);
        }

        $dm = $this->delivery_man;
        $dm->f_name = $request->f_name;
        $dm->l_name = $request->l_name;
        $dm->email = $request->email;
        $dm->phone = $request->phone;
        $dm->identity_number = $request->identity_number;
        $dm->identity_type = $request->identity_type;
        $dm->branch_id = $request->branch_id;
        $dm->identity_image = $identity_image;
        $dm->image = $image_name;
        $dm->is_active = 1;
        $dm->password = bcrypt($request->password);
        $dm->application_status= 'approved';
        $dm->save();

        Toastr::success('Delivery man added successfully!');
        return redirect('admin/delivery-man/list');
    }

    /**
     * @param $id
     * @return Factory|View|Application
     */
    public function edit($id): View|Factory|Application
    {
        $delivery_man = $this->delivery_man->find($id);
        return view('admin-views.delivery-man.edit', compact('delivery_man'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function status(Request $request): RedirectResponse
    {
        $delivery_man = $this->delivery_man->find($request->id);
        $delivery_man->is_active = $request->status;
        $delivery_man->save();
        Toastr::success('Delivery man status updated!');
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return Redirector|Application|RedirectResponse
     */
    public function update(Request $request, $id): Redirector|RedirectResponse|Application
    {
        $request->validate([
            'f_name' => 'required|max:100',
            'email' => 'required|regex:/(.+)@(.+)\.(.+)/i',
            'password_confirmation' => 'required_with:password|same:password'
        ]);

        $delivery_man = $this->delivery_man->find($id);

        if ($delivery_man['email'] != $request['email']) {
            $request->validate([
                'email' => 'required|unique:delivery_men',
            ]);
        }

        if ($delivery_man['phone'] != $request['phone']) {
            $request->validate([
                'phone' => 'required|unique:delivery_men',
            ]);
        }

        if ($request->has('image')) {
            $image_name = Helpers::update('delivery-man/', $delivery_man->image, 'png', $request->file('image'));
        } else {
            $image_name = $delivery_man['image'];
        }

        if ($request->has('identity_image')){
            foreach (json_decode($delivery_man['identity_image'], true) as $img) {
                if (Storage::disk('public')->exists('delivery-man/' . $img)) {
                    Storage::disk('public')->delete('delivery-man/' . $img);
                }
            }
            $img_keeper = [];
            foreach ($request->identity_image as $img) {
                $identity_image = Helpers::upload('delivery-man/', 'png', $img);
                $img_keeper[] = $identity_image;
            }
            $identity_image = json_encode($img_keeper);
        } else {
            $identity_image = $delivery_man['identity_image'];
        }
        $delivery_man->f_name = $request->f_name;
        $delivery_man->l_name = $request->l_name;
        $delivery_man->email = $request->email;
        $delivery_man->phone = $request->phone;
        $delivery_man->identity_number = $request->identity_number;
        $delivery_man->identity_type = $request->identity_type;
        $delivery_man->branch_id = $request->branch_id;
        $delivery_man->identity_image = $identity_image;
        $delivery_man->image = $image_name;
        $delivery_man->password = strlen($request->password) > 1 ? bcrypt($request->password) : $delivery_man['password'];
        $delivery_man->save();
        Toastr::success(translate('Delivery man updated successfully'));
        return redirect('admin/delivery-man/list');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function delete(Request $request): RedirectResponse
    {
        $delivery_man = $this->delivery_man->find($request->id);
        if (Storage::disk('public')->exists('delivery-man/' . $delivery_man['image'])) {
            Storage::disk('public')->delete('delivery-man/' . $delivery_man['image']);
        }

        foreach (json_decode($delivery_man['identity_image'], true) as $img) {
            if (Storage::disk('public')->exists('delivery-man/' . $img)) {
                Storage::disk('public')->delete('delivery-man/' . $img);
            }
        }

        $delivery_man->delete();
        Toastr::success(translate('Delivery man removed!'));
        return back();
    }

    /**
     * @return StreamedResponse|string
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function export(Request $request): StreamedResponse|string
    {

        $query_param = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $delivery_man = $this->delivery_man->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }else{
            $delivery_man = $this->delivery_man;
        }
        $delivery_man = $delivery_man->latest()->where('application_status', 'approved')->get();

        $storage = [];

        foreach($delivery_man as $dm){

            if ($dm['branch_id'] == 0){
                $branch = 'All Branch';
            }else{
                $branch = $dm->branch ? $dm->branch->name : '';
            }

            $storage[] = [
                'first_name' => $dm['f_name'],
                'last_name' => $dm['l_name'],
                'phone' => $dm['phone'],
                'email' => $dm['email'],
                'identity_type' => $dm['identity_type'],
                'identity_number' => $dm['identity_number'],
                'branch' => $branch,
            ];
        }
        return (new FastExcel($storage))->download('delivery-man.xlsx');
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function pending_list(Request $request): Factory|View|Application
    {
        $query_param = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $delivery_men = $this->delivery_man->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }else{
            $delivery_men = $this->delivery_man;
        }
        $delivery_men = $delivery_men->with('branch')
            ->where('application_status', 'pending')
            ->latest()->paginate(Helpers::getPagination())
            ->appends($query_param);

        return view('admin-views.delivery-man.pending-list', compact('delivery_men','search'));
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function denied_list(Request $request): Factory|View|Application
    {
        $query_param = [];
        $search = $request['search'];
        if($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $delivery_men = $this->delivery_man->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%")
                        ->orWhere('email', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }else{
            $delivery_men = $this->delivery_man;
        }
        $delivery_men = $delivery_men->with('branch')
            ->where('application_status', 'denied')
            ->latest()
            ->paginate(Helpers::getPagination())
            ->appends($query_param);

        return view('admin-views.delivery-man.denied-list', compact('delivery_men','search'));
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function update_application(Request $request): RedirectResponse
    {
        $delivery_man = $this->delivery_man->findOrFail($request->id);
        $delivery_man->application_status = $request->status;
        if($request->status == 'approved') $delivery_man->is_active = 1;
        $delivery_man->save();

        try{
            $emailServices = Helpers::get_business_settings('mail_config');
            if (isset($emailServices['status']) && $emailServices['status'] == 1) {
                Mail::to($delivery_man->email)->send(new \App\Mail\DMSelfRegistration($request->status, $delivery_man->f_name.' '.$delivery_man->l_name));
            }

        }catch(\Exception $ex){
            info($ex);
        }

        Toastr::success(translate('application_status_updated_successfully'));
        return back();
    }
}
