<?php

namespace App\Http\Controllers\App\Account;

use Carbon\Carbon;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\CompanyProfile;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\CompanyProfileRequest;
use App\Http\Resources\CompanyProfileResource;
use App\Http\Resources\OrderResourceCollection;

class ProfileController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user=auth()->user();
        $success=[
            'user' =>  new UserResource($user),
            // 'wallet' => $user->wallet ?? 0,
        ];

        // if (auth()->user()->hasRole('endUser')) {
                
        //     $success['orders'] =new OrderResourceCollection($user->orders);
        //     $success['addresses'] = $user->addresses;
        //     $success['setting'] = [
        //         'allow_notification' => $user->allow_notification ?? true,
        //         'lang' => $user->lang ?? 'en',
        //     ];
        //     $success['payment_methods'] = $user->payment_methods;
        // }
        
        return $this->sendResponse($success, 'User Profile.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return [];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return [];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return [];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return [];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProfileRequest $request)
    {
        /* Storage::put('test.txt', json_encode($request->all()));
        dd(123); */
        $user = User::find(auth()->user()->id);
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        // $user->phone = $request->phone;
        if($request->hasFile('profile_image')){
            $user->photo_path = custom_file_upload($request->profile_image,USER_IMAGE_PATH_PUBLIC);
            // $user->provider = "";
        }
        if($user->email != $request->email){
            $user->email_verified_at = null;
            $user->otp_verified_at=null;
            $this->sendOtp($user);
        }
        // dd($user->email);
        $user->email = $request->email;
        
        $user->save();

        return $this->sendResponse(new UserResource($user), 'Profile Updated.');
        
    }

    public function passUpdate(Request $request)
    {
        $validator = validator($request->all(), [
            'old_password' => ['required',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, auth()->user()->password)) {
                        return $fail('The old password is incorrect.');
                    }
                }
            ],
            'password' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation error',$validator->errors(), 400);
        }

        $user = User::find(auth()->user()->id);
        $user->password = Hash::make($request->password);
        $user->save();

        return $this->sendResponse(new UserResource($user), 'Profile Updated.');
        
    }

    public function sendOtp($user)
    {
        $user->otp=unique_serial('users','otp',null);
        $user->otp_expiry=Carbon::now()->addMinutes('5');
        $user->otp_verified_at=null;
        $user->save();

        $data=[
            'otp' =>  $user->otp,
            'otp_expiry' =>  Carbon::parse($user->otp_expiry)->format('Y-m-d H:i:s'),
        ];

        try {
            Mail::to($user->email)->send(new EmailOtp($data));
        } catch (\Throwable $th) {
             
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function companyProfile(CompanyProfileRequest $request)
    {
        $user = auth()->user();

        if ( $request->isMethod('post')) {
            $answers1=[
                'user_id' => $user->id,
            ];

            $answers2=[
                'user_id' => $user->id,
                'shop_name' => $request->shop_name ?? 'null',
                'shop_contact' => $request->shop_contact ?? 'null',
                'shop_location' => $request->shop_location ?? 'null',
                'additional_info' => $request->additional_info ?? 'null',
            ];
            
            if($request->hasFile('shop_logo')){
                $answers2['shop_logo'] = custom_file_upload($request->shop_logo,PROFILE_IMAGE_PATH);
            }
            
            if($request->hasFile('reg_certificate')){
                $answers2['reg_certificate'] = custom_file_upload($request->reg_certificate,PROFILE_IMAGE_PATH);
            }
        
            $res = CompanyProfile::updateOrCreate($answers1,$answers2);
        }else{
            $res = $user->company_profile;
        }
        

        return $this->sendResponse(new CompanyProfileResource($res), 'Company Profile.');
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return [];
    }
}
