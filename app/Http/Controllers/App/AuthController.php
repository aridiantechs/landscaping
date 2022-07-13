<?php

namespace App\Http\Controllers\App;

use Carbon\Carbon;
use App\Models\User;
use App\Mail\EmailOtp;
use App\Models\UserDevice;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\CompanyProfile;
use App\Mail\EmailVerification;
use App\Rules\MatchOldPassword;
use App\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\RequestResourceCollection;
// use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class AuthController extends Controller
{
    // use SendsPasswordResetEmails;
	public $successStatus = 200;
    
    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'email' => 'required|string|regex:/[a-z0-9]+@[a-z]+\.[a-z]{2,3}/i',
            'password' => 'required|string',
        ]);
        
    	if ($validator->fails()) {
            
            $fields1=new User;
            $fields=$fields1->getFillable();
            $errors= $this->formatErrors($fields, $validator->errors());
            return $this->validationError('Fields are Missing', $errors, 400);
        }

        $user= User::where('email', request('email'))->first();
        if ( Auth::attempt($request->only('email', 'password'))) {
            
            Auth::login($user);
            if(auth()->user()->hasRole('superadmin')) {
                $user->tokens()->delete();
                return $this->validationError('Unauthorised.', [['title'=>'signin','message'=>'Unauthorised']],400);
            }
            // $user->otp_verified_at=null;
            
            $device = UserDevice::where('user_id',$user->id)->where('device_id',$request->device_id)->first();
            if(!$device && $request->device_id){
                UserDevice::create([
                    'user_id' => $user->id,
                    'device_id' => $request->device_id,
                ]);
            }

            $success=[
                'token' =>  $user->createToken('API Token')->plainTextToken,
                'user' =>  new UserResource($user),
            ];

            // $this->sendOtp($user);
            
            return $this->sendResponse($success, 'User login successfully.');
        }

        return $this->validationError('Unauthorised.', [['title'=>'signin','message'=>'Unauthorised']],400);
         
    }

    public function createUser(Request $request)
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'photo_path' => custom_file_upload($request->profile_image,USER_IMAGE_PATH_PUBLIC),
            'password' => Hash::make($request->password),
        ]);
        $user->save();

        return $user;

    }

    public function signup(Request $request)
    {
    	$validator =  Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'numeric','unique:users,phone'],
            'email' => ['required', 'string', 'regex:/[a-z0-9]+@[a-z]+\.[a-z]{2,3}/i', 'max:255', 'unique:users'],
            'profile_image' => 'nullable|image',
            'role' => ['required', 'string', 'in:endUser,worker'],
            'password' => [
                'required',
                'min:8',
                'confirmed'
            ],
        ]);

    	if ($validator->fails()) {
        

            $fields1=new User;
            $fields=$fields1->getFillable();
            $errors=$this->formatErrors(array_merge($fields,['role']), $validator->errors());
            return $this->validationError('Fields are Missing', $errors, 400);
        }

        $user = $this->createUser($request);
        $user->assignRole($request->role);
        
        if (Auth::attempt(['email' => $request->email,'password' => $request->password])) {
        
            //  $this->sendEmailVerification($user);
            UserDevice::create([
                'user_id' => $user->id,
                'device_id' => $request->device_id,
            ]);

            // store square customer
            $this->storeSquareCustomer($user);
            
            $success['token'] =  $user->createToken('API Token')->plainTextToken;
            $success['user'] =  new UserResource($user);

            $this->sendOtp($user);
    
            return $this->sendResponse($success, 'User register successfully.');
        }

    }

    public function storeSquareCustomer($user)
    {
        $ps=new PaymentService;
        $ps_res=$ps->create_customer([
            'email' => $user->email,
            'phone' => $user->phone,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ]);
        
        if (!is_null($ps_res) && isset($ps_res['customer_id'])) {
            $user->square_customer_id =$ps_res['customer_id'];
            $user->save();
        }
    }

    public function sendOtp($user)
    {
        $user->otp=otp_unique_serial('users','otp',null);
        $user->otp_expiry=Carbon::now()->addMinutes('5');
        $user->otp_verified_at=null;
        $user->save();

        $data=[
            'otp' =>  $user->otp,
            'otp_expiry' =>  Carbon::parse($user->otp_expiry)->format('Y-m-d H:i:s'),
            'body_text'=>'Hi, here is your verification code'
        ];

        try {
            Mail::to($user->email)->send(new EmailOtp($data));
        } catch (\Throwable $th) {
             
        }

    }

    // resend otp
    public function resendOtp(Request $request)
    {
        $user=User::where('email',auth()->user()->email)->first();
        if(!$user) {
            return $this->validationError('User not found.', [['title'=>'signin','message'=>'User not found']],400);
        }
        $this->sendOtp($user);
        return $this->sendResponse([], 'OTP sent successfully.');
    }

    public function sendResetOtp($user)
    {
        $user->otp=otp_unique_serial('users','otp',null);
        $user->otp_expiry=Carbon::now()->addMinutes('60');
        $user->otp_verified_at=null;
        $user->save();

        $data=[
            'otp' =>  $user->otp,
            'otp_expiry' =>  Carbon::parse($user->otp_expiry)->format('Y-m-d H:i:s'),
            'body_text'=>'Hi, here is your verification code to reset your password'
        ];

        try {
            Mail::to($user->email)->send(new EmailOtp($data));
        } catch (\Throwable $th) {
            // dd($th);
             
        }

    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return $this->sendResponse(new UserResource(Auth::user()),'Personal Info');
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        if ($request->device_id) {
            $device = UserDevice::where('user_id',auth()->user()->id)->where('device_id',$request->device_id)->first();
            if($device){
                $device->delete();
            }
        }

        $user = Auth::user();
    	$user->tokens()->delete();
        $user->otp_verified_at=null;
        $user->otp=null;
        $user->save();

        return $this->sendResponse(null,'Successfully logged out');

    }

    // function to refresh token
    public function refreshToken(Request $request)
    {
        $user = Auth::user();
        $user->tokens()->delete();
        $user->save();

        $success['token'] =  $user->createToken('API Token')->plainTextToken;
        $success['user'] =  new UserResource($user);

        return $this->sendResponse($success, 'Token refreshed successfully.');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmailUsingOtp(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'otp' => ['required', 'string', 'max:10'],
        ]);

    	if ($validator->fails()) {
            $valid_errors=$validator->getMessageBag()->toArray();
            
            $errors[] = ['key' => 'otp','message' => $valid_errors['otp'] ?? ''];
            return $this->validationError('Unauthorised.', $errors, 400);
        }
        
        $user= auth()->user();
        
        // if (is_null($user->otp_verified_at)) {
            if ($user->otp !=$request->otp) {
                return $this->validationError('Unauthorised.',[array('key'=>'otp','message'=>'Otp is Invalid')],400);
            }
            else if(Carbon::parse($user->otp_expiry)->lt(Carbon::now())){
                return $this->validationError('Unauthorised.','Otp is Expired',400);
            }else{
                $user->otp_verified_at=Carbon::now();
                $user->email_verified_at=Carbon::now();
                $user->save();

            }
        // }
        $success['token'] =  $user->createToken('API Token')->plainTextToken;
        $success['user'] =  new UserResource($user);
        // $success['profile_completed'] = $user->email ? true : false;

        return $this->sendResponse( $success, 'Otp verified.');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $user = Auth::user(); 
        $success['token'] =  $user->createToken('API Token')->plainTextToken;
        $success['user'] =  $user;

        return $this->sendResponse($success, 'Token Refreshed.');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard($guard)
    {
        return Auth::guard($guard);
    }

    // Overriding the Trait Method
    public function sendResetLinkEmail(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'email' => ['required', 'string', 'regex:/[a-z0-9]+@[a-z]+\.[a-z]{2,3}/i', 'exists:users'],
        ]);

        if ($validator->fails()) {
            return $this->validationError('Validation error',$validator->errors(), 400);
        }
        $user = User::where('email',$request->only('email'))->first();
        if(!$user){
            return $this->validationError("Email doesn't exists",[], 400);
        }


        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        // $response = $this->broker()->sendResetLink(
        //     $request->only('email')
        // );

        $this->sendResetOtp($user);

        return $this->sendResponse([], 'You will get recovery e-mail shortly.');
    }

    /**
     * Reset Password
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function reset_password(Request $request)
    {

        $validator =  Validator::make($request->all(), [
            'email' => ['required', 'regex:/[a-z0-9]+@[a-z]+\.[a-z]{2,3}/i' , 'exists:users'],
            // 'current_password' => ['required', new MatchOldPassword],
            'otp' => ['required', 'string', 'max:10'],
            'new_password' => [
                'required',
                'min:8', 
                'regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/'
                ],
            'new_confirm_password' => ['same:new_password'],
        ],[
            'new_password.regex' => 'The password must be at least 8 characters and must be combination of uppercase, lowercase, special character and a digit.'
        ]);
        
        if ($validator->fails()) {
            $errors = [];
            foreach($validator->errors()->toArray() as $key => $error ){
                $errors[] = $error[0];
            }
            return api_response("",400,implode("\n ", $errors),$errors);
        }
        
        $user = User::where('email',$request->email)->first();
        // if(!$user){
        //     return $this->validationError("Email doesn't exists",[], 400);
        // }
        // dd($user, $request->email);

        if ($user->otp !=$request->otp) {
            return $this->validationError('Unauthorised.',[array('key'=>'otp','message'=>'Otp is Invalid')],400);
        }
        else if(Carbon::parse($user->otp_expiry)->lt(Carbon::now())){
            return $this->validationError('Unauthorised.','Otp is Expired',400);
        }

        Auth::logoutOtherDevices($user->password);
        $user->update(['password'=> Hash::make($request->new_password)]);
        
        $user->otp = null;
        $user->save();
        return api_response("Password Changed Successfully.",200);
       
    }


    /**
     * Update Password
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    // public function change_password(Request $request)
    // {

    //     $validator =  Validator::make($request->all(), [
    //         'current_password' => ['required', new MatchOldPassword],
    //         'new_password' => [
    //             'required',
    //             'min:8', 
    //             'regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/'
    //             ],
    //         'new_confirm_password' => ['same:new_password'],
    //     ],[
    //         'new_password.regex' => 'The password must be at least 8 characters and must be combination of uppercase, lowercase, special character and a digit.'
    //     ]);

    //     if ($validator->fails()) {
    //         $errors = [];
    //         foreach($validator->errors()->toArray() as $key => $error ){
    //              $errors[] = $error[0];
    //         }
    //         return api_response("",400,implode("\n ", $errors),$errors);
    //     }

    //     // Auth::logoutOtherDevices(auth()->user()->password);
    //     User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);
        
    //     return api_response("Password Updated.",200);
       
    // }

    public function sendEmailVerification($user){

        /* $user->email_verify_token = Hash::make(md5( rand(0,1000) ));
        $user->email_token_expire = \Carbon\Carbon::now()->addHour()->format('Y-m-d H:i:s');
        $user->save(); */

        try {
           Mail::to($user->email)->send(new EmailVerification($user));
        } catch (\Throwable $th) {
            
        }
    }

    public function verified(Request $request){
       
          return view('auth.verified');

    }

    public function socialAuthenticate(Request $request){

        $validator = Validator::make($request->all(), [
            'provider'           => 'required|string',
            'access_token'   => 'required|string',
            'device_id'   => 'required|string',
            'role' => ['required', 'string', 'in:endUser,worker'],
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->getMessages() as $key => $value){
                $errors[$key] = $value[0];
            }
            return $this->validationError('Fields are Missing',$errors,400);
        }

        $res=$this->getUserInfo($request->access_token,$request->provider);
        
        if (isset($res['data']) && isset($res['data']['email']) ) {
            $user_data = $this->createNewUser($res['data'],$request->provider);
            
            $device = UserDevice::where('user_id',$user_data['user']->id)->where('device_id',$request->device_id)->first();
            if(!$device && $request->device_id){
                UserDevice::create([
                    'user_id' => $user_data['user']->id,
                    'device_id' => $request->device_id,
                ]);
            }
            $user_data['user']->assignRole('endUser');
            return $this->sendResponse((object)[
                'user' => new UserResource($user_data['user']),
                'token' =>  $user_data['token'],
            ], 'User Signup successfully.', 200,[]);
        } else {
            return $this->sendResponse((object)[], 'Something went wrong!', 400,[]);
        }

    }
    
    public function createNewUser($social_user,$provider){
        $user = User::where(['email' => $social_user['email']])->first();
        if(!$user){
            $user = User::forceCreate([
                'first_name'        => $social_user['first_name'] ?? '',
                'last_name'         => $social_user['last_name'] ?? '',
                'email'             => $social_user['email'] ?? '',
                'photo_path'        => $social_user['image'] ?? '',
                'password'          => Str::random(40),
                'email_verified_at' => now(),
                'otp_verified_at'   => now(),
                'provider'          => $provider,
            ]);
        }

        Auth::login($user);
        $success['token'] =  $user->createToken('API Token')->plainTextToken; 
        $success['user'] =  $user;
        $this->storeSquareCustomer($user);
        
        return $success;
    }   
    
    public function getUserInfo($access_token,$provider)
    {
        $curl = curl_init();

        if ($provider == 'google') {
            $curl_url="https://oauth2.googleapis.com/tokeninfo?id_token=".$access_token;
            $curl_header=[
                "Accept: application/json",
                "Content-Type: application/json",
            ];
        } else if($provider == 'facebook') {
            $curl_url="https://graph.facebook.com/me?access_token=".$access_token;
            $curl_header=[
                "Accept: application/json",
                "Content-Type: application/json",
            ];
        }
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $curl_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $curl_header,
        ]);

        $social_user = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $res=array(
                "success"=>false,
                "error"=>"cURL Error #:" . $err
            );
        } else {
            if ($provider == 'google') {
                $social_user =json_decode($social_user);
                $response=[
                    'first_name'        => $social_user->given_name ?? '',
                    'last_name'         => $social_user->family_name ?? '',
                    'email'             => $social_user->email ?? '',
                    'image'             => $social_user->picture ?? '',
                ];
            } else if($provider == 'facebook') {
                $social_user =json_decode($social_user);
                $response=[
                    'first_name'        => $social_user->first_name ?? '',
                    'last_name'         => $social_user->last_name ?? '',
                    'email'             => $social_user->email ?? '',
                    'image'             => $social_user->picture->data->url ?? '',
                ];
            }
            
            $res=array(
                "success"=>true,
                "data"=>$response
            );
        }
        
        return $res;
    }

} 
