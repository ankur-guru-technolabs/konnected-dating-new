<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\File;
use App\Http\Controllers\BaseController;
use App\Models\Age;
use App\Models\Bodytype;
use App\Models\Children;
use App\Models\Education;
use App\Models\Ethnicity;
use App\Models\Faith;
use App\Models\Gender;
use App\Models\Height;
use App\Models\Hobby;
use App\Models\Industry;
use App\Models\Icebreaker;
use App\Models\Question;
use App\Models\Salary;
use App\Models\Temp;
use App\Models\User;
use App\Models\UserIceBreaker;
use App\Models\UserPhoto;
use App\Models\UserQuestion;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Helper;
use Validator;

class AuthController extends BaseController
{
    // SEND OTP FOR REGISTRATION, LOGIN, RESEND OTP

    public function sendOtp(Request $request){
        try{
            
            $temp_number = array('+9111111','+9122222','+9133333','+9144444','+9155555','+9166666','+9177777','+9188888','+9199999','+9112345','+9167890','+9111223','+9122345','+9133456','+9144567','+9155678');
            $otp    = substr(number_format(time() * rand(),0,'',''),0,4);
            if(isset($request->phone_no) && in_array($request->phone_no,$temp_number)){
                $otp    = 4444;
            }
            $data   = [];
            $data['is_user_exist'] = 0;
            $data['otp'] = (int)$otp;
            
            if(isset($request->email)){
                $validateData = Validator::make($request->all(), [
                    // 'email' => 'required|email|unique:users,email',
                    'email' => 'required|email',
                ]);

                if ($validateData->fails()) {
                    return $this->error($validateData->errors(),'Validation error',403);
                } 
                
                $key          = $request->email;
                $email_data   = [
                    'email'   => $key,
                    'otp'     => $otp,
                    'subject' => 'Email OTP Verification - For Konnected dating',
                ];

                if (User::where('email', '=', $key)->count() > 0) {
                    if(User::where('email','=', $key)->where('status',0)->count() > 0){
                        return $this->error('You are inactive','You are inactive');
                    };
                    $data['is_user_exist'] = 1;
                }

                Helper::sendMail('emails.email_verify', $email_data, $key, '');

                $data['send_in'] = 'email';

            } else if(isset($request->phone_no)){

                $validateData = Validator::make($request->all(), [
                    // 'phone_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|unique:users,phone_no',
                    'phone_no' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                ]);

                if ($validateData->fails()) {
                    return $this->error($validateData->errors(),'Validation error',403);
                } 
               
                $key             = $request->phone_no;

                if ($user = User::where('phone_no','=', $key)->count() > 0) {
                    if(User::where('phone_no','=', $key)->where('status',0)->count() > 0){
                        return $this->error('You are inactive','You are inactive');
                    };
                    $data['is_user_exist'] = 1;
                }
                if(!in_array($request->phone_no,$temp_number)){
                    Helper::sendOtp($key,$otp);
                }
                $data['send_in'] = 'phone_no'; 
            } else {
                return $this->error('Please enter email or phone number','Required parameter');
            }
            
            $temp         = Temp::firstOrNew(['key' => $key]);
            $temp->key    = $key;
            $temp->value  = $otp;
            $temp->save();
            
            return $this->success($data,'OTP send successfully');

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // VERIFY OTP (IF USER EXISTS AND OTP VERIFIED THEN IT IS USED AS A LOGIN) 

    public function verifyOtp(Request $request){
        // $user = User::where('email', '=', $request->email_or_phone)
        // ->orWhere('phone_no','=', $request->email_or_phone)
        // ->select('id','email', 'phone_no')
        // ->first();
        // $data['token'] = $user->createToken('Auth token')->accessToken;
        // return $data;
        try{
            $validateData = Validator::make($request->all(), [
                'email_or_phone' => 'required',
                'otp' => 'required',
                'type' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 
            
            $temp         = Temp::where('key',$request->email_or_phone)->first();
            if($temp != null){
                $is_data_present = Temp::where('key',$request->email_or_phone)->where('value',$request->otp)->first();
                if($is_data_present != null){

                    $is_data_present->delete();
                    $data = [];
                    $data['user_id'] = 0;
                    $data['is_user_exist'] = 0;
                    $data['is_email_verified'] = 0;
                    $data['otp'] = (int)$request->otp;


                    // When user update email and come to verify screen at that time it is required to send id
                    if(isset($request->id)){
                        $user = User::where('id','=', $request->id)
                        ->select('id','email', 'phone_no','email_verified')
                        ->first();
                        if ($user && filter_var($request->email_or_phone, FILTER_VALIDATE_EMAIL)) {
                            $user->update(['email'=> $request->email_or_phone]);
                        }
                    }

                    $user = User::where('email', '=', $request->email_or_phone)
                            ->orWhere('phone_no','=', $request->email_or_phone)
                            ->select('id','email', 'phone_no','email_verified')
                            ->first();

                    if ($user) {
                        $data['is_user_exist'] = 1;
                        $data['user_id'] = $user->id;
                        $data['email'] = $user->email;
                        $data['phone_no'] = $user->phone_no;
                        
                        if ($user->email == $request->email_or_phone) {
                            $user->email_verified = 1;
                        }
                        $user->otp_verified = 1;
                        $user->save();
                        
                        // When user register and from the page where otp verifiy for email kill app and then try to do login so need to send email and verified 0.
                        // If user is exists and email not verifiy then show email send otp screens
                        $data['is_email_verified'] = $user->email_verified;

                        if($user->email_verified == 0){
                            $request1 = new Request();
                            $request1->merge(['email' => $user->email]);
                            $response = $this->sendOtp($request1);
                            $data11 = json_decode($response->getContent(), true);  
                            if ($data11 && isset($data11['data']['otp'])) {
                                $data['otp'] = (int)$data11['data']['otp'];  
                            } 
                        }
                        
                        $data['user_photo_exits'] = UserPhoto::where('user_id',$user->id)->count() > 0 ? true : false;
                        if($user->email_verified == 1 && $user->phone_verified = 1 && $user->otp_verified == 1 && $request->type != 'edit'){
                            $user->tokens()->delete();
                            $user->fcm_token = $request->fcm_token;
                            $user->save();
                            $data['token'] = $user->createToken('Auth token')->accessToken;
                        }

                        if($user->email_verified == 1 && $user->phone_verified = 1 && $user->otp_verified == 1 && $request->type == 'register'){
                            // Notification for welcome

                            $title = "Welcome to Konnected App";
                            $message = "Welcome to Konnected App"; 
                            Helper::send_notification('single', 0, $user->id, $title, 'welcome', $message, []);
                        }
                    } 
                    return $this->success($data,'OTP verified successfully');
                }
                return $this->error('OTP is wrong','OTP is wrong');
            } 
            $can_not_find = "Sorry we can not find data with this credentials";
            return $this->error($can_not_find,$can_not_find);

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // GOOGLE LOGIN OR SIGNUP 

    public function redirectToProvider(Request $request, $provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback(Request $request, $provider)
    {
        try{
            $user= Socialite::driver($provider)->stateless()->user();
            $findUser = User::where($provider.'_id', $user->id)->first();
            if($findUser){
                $findUser->tokens()->delete();
                $data['token'] = $findUser->createToken('Auth token')->accessToken;
                return $this->success($data,'Login successfully');
            }else{
                if($provider == 'google'){
                    $data['first_name']  = $user->user['given_name'];
                    $data['last_name']   = $user->user['family_name'];
                    $data['google_id']   = $user->id;
                    $data['email']       = $user->email;
                    return $this->success($data,'Signup successfully');
                }
                if($provider == 'facebook'){
                    dd($user);
                }
            }
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // CHECK USER EXIST OR NOT 

    public function checkSocailUser(Request $request)
    {
        try{
            $validateData = Validator::make($request->all(), [
                'social_id' => 'required',
                'fcm_token' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 

            $findUser = User::where('google_id', $request->social_id)->orWhere('facebook_id',$request->social_id)->orWhere('apple_id',$request->social_id)->first();
            if($findUser){
                $findUser->tokens()->delete();
                $findUser->fcm_token = $request->fcm_token;
                $findUser->save();
                $data['token'] = $findUser->createToken('Auth token')->accessToken;
                $data['user_id'] = $findUser->id;
                $data['user_photo_exits'] = UserPhoto::where('user_id',$findUser->id)->count() > 0 ? true : false;
                return $this->success($data,'Login successfully');
            }else{
                if($request->email && User::where('email', '=', $request->email)->count() > 0){
                    return $this->error('Email already exist','Email already exist');
                }
                $data['social_id']   = $request->social_id;
                $data['email_verified'] = 1 ;
                return $this->success($data,'Signup successfully');
            }
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // RETRIVE DATA WHICH ARE NEEDED FOR REGISTRATION FORM

    public function getRegistrationFormData(){
        try{
            $data               = [];
            $data['age']        = Age::all();
            $data['body_type']  = Bodytype::all();
            $data['children']   = Children::all();
            $data['education']  = Education::all();
            $data['ethnicity']  = Ethnicity::all();
            $data['faith']      = Faith::all();
            $data['gender']     = Gender::all();
            $data['height']     = Height::all();
            $data['hobby']      = Hobby::all();
            $data['industry']   = Industry::all();
            $data['icebreaker'] = Icebreaker::all();
            $data['question']   = Question::with('SubQuestions')->get();
            $data['salary']     = Salary::all();
            $data['min_height'] = 6;
            $data['max_height'] = 37;
            $data['min_age']    = 4;
            $data['max_age']    = 63;
            return $this->success($data,'Registration form data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // USER REGISTRATION

    public function register(Request $request){
        try{
            $messages = [
                'ice_breaker.required' => 'Ice breakers are required',
                'ice_breaker.array' => 'Ice breakers must be an array',
                'ice_breaker.min' => 'Ice breakers must have at least :min items',
                'ice_breaker.*.ice_breaker_id.required' => 'Ice breaker ID is required',
                'ice_breaker.*.answer.required' => 'Answer is required',
                'questions.required' => 'Questions are required',
                'questions.array' => 'Questions must be an array',
                'questions.min' => 'Questions must have at least :min items',
                'questions.*.question_id.required' => 'Question ID is required',
                'questions.*.answer_id.required' => 'Answer is required',
            ];
            $validateData = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name'  => 'required|string|max:255',
                'email'      => 'nullable|email|unique:users,email|max:255',
                'phone_no'   => 'nullable|string|unique:users,phone_no|max:20',
                'location'   => 'required|string|max:255',
                'latitude'   => 'required|numeric',
                'longitude'  => 'required|numeric',
                'job'        => 'required|string|max:255',
                'bio'        => 'required|string',
                'company'    => 'required|string|max:255',
                'gender'     => 'required',
                'age'        => 'required',
                'height'     => 'required',
                'education'  => 'required',
                'industry'   => 'required',
                'salary'     => 'required',
                // 'body_type'  => 'required',
                'children'   => 'required',
                'faith'      => 'required',
                'ethnticity' => 'required',
                'hobbies'    => 'required',
                // 'photos'     => 'required|array|min:3',
                // 'photos.*'   => 'required|file|mimes:jpeg,png,jpg,mp4,mov,avi|max:100000', 
                // 'profile_image'                => 'required|file|mimes:jpeg,png,jpg',
                // 'thumbnail_image'              => 'required|file|mimes:jpeg,png,jpg',
                'ice_breaker'                  => 'required|array|min:1|max:3',
                'ice_breaker.*.ice_breaker_id' => 'required',
                'ice_breaker.*.answer'         => 'required',
                'questions'                    => 'required|array|min:8',
                'questions.*.question_id'      => 'required',
                'questions.*.answer_id'        => 'required',
            ], $messages); 
            
            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 

            if(!isset($request->google_id) && !isset($request->facebook_id) && !isset($request->apple_id)){
                $this->sendOtp($request);
            }
            $input                   = $request->all();
            $input['user_type']      = 'user';
            $input['phone_verified'] = 1;
            $input['email_verified'] = 0;
             if(isset($request->google_id) || isset($request->facebook_id)){
                $input['email_verified'] = 1;
             }
            $input['google_id']      = isset($request->google_id) ? $request->google_id : null;

            $user_data  = User::create($input);

            if(isset($user_data->id)){
                
                $ice_breaker_data_new = [];
                foreach($input['ice_breaker'] as $ice_breaker_data){
                    $ice_breaker_data['user_id'] = $user_data->id;
                    $ice_breaker_data['created_at'] = now();
				    $ice_breaker_data['updated_at'] = now();
                    $ice_breaker_data_new[] = $ice_breaker_data;
                    // UserIceBreaker::create($ice_breaker_data);
                }
                UserIceBreaker::insert($ice_breaker_data_new);
            
                $question_new = [];
                foreach($input['questions'] as $question){
                    // $question['user_id']        = $user_data->id;
                    // $question['question_id']    = $question['question_id'];
                    // $question['answer_id']      = $question['answer_id'];
                    // $question['created_at'] = now();
				    // $question['updated_at'] = now();
                    // $question_new[] = $question;
                    $user_id = $user_data->id;
                    $question_id = $question['question_id'];

                    if (strpos($question['answer_id'], ',') !== false) {
                        // If it is, split the values and create separate entries
                        $answer_ids = explode(',', $question['answer_id']);
                        foreach ($answer_ids as $answer_id) {
                            $question_new[] = [
                                'user_id' => $user_id,
                                'question_id' => $question_id,
                                'answer_id' => $answer_id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    } else {
                        $question_new[] = [
                            'user_id' => $user_id,
                            'question_id' => $question_id,
                            'answer_id' => $question['answer_id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // UserQuestion::create($question);
                }
                UserQuestion::insert($question_new);

                // $folderPath = public_path().'/user_profile';
                // if (!is_dir($folderPath)) {
                //     mkdir($folderPath, 0777, true);
                // }
                
                
                // $mediaFiles = $request->file('photos');
                // $thumbnailImage = $request->file('thumbnail_image');
                // $profileImage = $request->file('profile_image');

                // $user_photo_data = [];

                // if (!empty($mediaFiles)) {
                //     $user_photo_data = $this->uploadMediaFiles($mediaFiles, $user_data->id);
                // }

                // if (!empty($thumbnailImage)) {
                //     $user_photo_data[] = $this->uploadImageFile($thumbnailImage, $user_data->id, 'thumbnail_image');
                // }
    
                // if (!empty($profileImage)) {
                //     $user_photo_data[] = $this->uploadImageFile($profileImage, $user_data->id, 'profile_image');
                // }

                // UserPhoto::insert($user_photo_data); 

                if(!isset($request->google_id) && !isset($request->facebook_id) && !isset($request->apple_id)){
                    $temp         = Temp::where('key',$request->email)->first();
                    if($temp != null){
                        $user_data['otp'] = (int)$temp->value; 
                    }
                }
                if(isset($request->google_id) || isset($request->facebook_id) || isset($request->apple_id)){
                   // Notification for welcome

                   $title = "Welcome to Konnected App";
                   $message = "Welcome to Konnected App"; 
                   Helper::send_notification('single', 0, $user_data->id, $title, 'welcome', $message, []);
                }
                return $this->success($user_data,'You are successfully registered');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            if(isset($user_data->id)){
                // $user_old_photo_name = UserPhoto::where('user_id',$user_data->id)->pluck('name')->toArray();

                // $deletedFiles = [];
                // if(!empty($user_old_photo_name)){
                //     foreach ($user_old_photo_name as $name) {
                //         $path = public_path('user_profile/' . $name);
                //         if (File::exists($path)) {
                //             if (!is_writable($path)) {
                //                 chmod($path, 0777);
                //             }
                //             File::delete($path);
                //             $deletedFiles[] = $path;
                //         }
                //     };
                // }
                UserIceBreaker::where('user_id',$user_data->id)->delete();
                UserPhoto::where('user_id',$user_data->id)->delete();
                UserQuestion::where('user_id',$user_data->id)->delete();
                User::where('id',$user_data->id)->delete();
            }
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // CHECK EMAIL EXISTS OR NOT DURING REGISTRATION AND EMAIL CHANGE FROM MODAL

    public function emailExist(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 
                
            $key  = $request->email;
            
            $data['is_email_exist'] = 0;
            if (User::where('email', '=', $key)->count() > 0) {
                $data['is_email_exist'] = 1;
            }
            return $this->success($data,'Email exists check');

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // CHECK DEVICE TOKEN EXISTS OR NOT

    public function checkDeviceToken(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'device_token' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 
                
            $key  = $request->device_token;
            
            $data['is_device_token'] = 0;
            $data['is_from_google'] = false;
            $data['is_from_facebook'] = false;
            if (User::where('fcm_token', '=', $key)->orWhere('device_token', '=', $key)->count() > 0) {
                $data['is_device_token'] = 1;
                $data['is_from_google'] = User::where(function ($query) use ($key) {
                    $query->where('fcm_token', '=', $key)
                        ->orWhere('device_token', '=', $key);
                })->whereNotNull('google_id')->count() > 0 ? true : false;
                $data['is_from_facebook'] = User::where(function ($query) use ($key) {
                    $query->where('fcm_token', '=', $key)
                        ->orWhere('device_token', '=', $key);
                })->whereNotNull('facebook_id')->count() > 0 ? true : false;
            }
            return $this->success($data,'Device token exists check');

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
}
