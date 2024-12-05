<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\File;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Api\AuthController;
use App\Models\Age;
use App\Models\Bodytype;
use App\Models\Category;
use App\Models\Chat;
use App\Models\Children;
use App\Models\ContactSupport;
use App\Models\Coin;
use App\Models\Education;
use App\Models\Ethnicity;
use App\Models\Faith;
use App\Models\Faq;
use App\Models\Gender;
use App\Models\Gift;
use App\Models\Height;
use App\Models\Hobby;
use App\Models\Industry;
use App\Models\Icebreaker;
use App\Models\Notification;
use App\Models\Question;
use App\Models\Salary; 
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Temp;
use App\Models\User;
use App\Models\UserCoin;
use App\Models\UserIceBreaker;
use App\Models\UserLikes;
use App\Models\UserPhoto;
use App\Models\UserQuestion;
use App\Models\UserReviewLater;
use App\Models\UserView;
use App\Models\UserReport;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DateTime;
use Exception;
use Helper;
use Validator;
use DB;
use Laravel\Ui\Presets\React;
use App\Lib\RtcTokenBuilder;
use App\Services\GooglePlayService;
use Carbon\Carbon;

use GuzzleHttp\Client;

class CustomerController extends BaseController
{
    //

    // UPLOAD USER PHOTO
    
    public function uploadUserMedia(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'photos.*.name' => 'required',
                'photos.*.type' => 'required',
                'user_id'       => 'required'
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }   
            
            $photos = $request->photos;
            $user_id = $request->user_id;
            $photos = array_map(function ($photo) use ($user_id) {
                return array_merge($photo, [
                    'user_id' => $user_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }, $photos);

            \DB::table('user_photos')->insert($photos);

            if(isset($request->old_images)){
                UserPhoto::whereIn('id',$request->old_images)->delete();
            }

            $data = User::find($user_id);
            if(isset($request->google_id) || isset($request->facebook_id) || isset($request->apple_id)){
                $data['token'] = $data->createToken('Auth token')->accessToken;
            }
            $temp_data = Temp::where('key',$data->email)->first();
            if(!empty($temp_data)){
                $data['otp'] = (int)$temp_data->value;
            }
            return $this->success($data,'Photos uploaded successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // GET LOGGED IN USER PROFILE

    public function getProfile(Request $request){
        try{
            $id = isset($request->id) ? $request->id : Auth::id();
            $answeredIceBreakerIds = DB::table('user_ice_breakers')
                            ->where('user_id', $id)
                            ->pluck('ice_breaker_id');

            $iceBreakers = DB::table('icebreakers')
                 ->whereNotIn('id', $answeredIceBreakerIds)
                ->select('id', 'question')
                ->get();

            foreach ($iceBreakers as $iceBreaker) {
                $iceBreaker->id = (int)$iceBreaker->id;
                $iceBreaker->user_id = (int)$id;
                $iceBreaker->ice_breaker_id = (int)$iceBreaker->id;
                $iceBreaker->answer =  null;
                $iceBreaker->question =  $iceBreaker->question;
            }
 
            $data['user'] = User::with(['iceBreakers' => function ($query) {
                $query->leftJoin('icebreakers', 'icebreakers.id', '=', 'user_ice_breakers.ice_breaker_id')
                      ->select('user_ice_breakers.id', 'icebreakers.question','user_ice_breakers.user_id', 'user_ice_breakers.ice_breaker_id', 'user_ice_breakers.answer', );
            }, 'photos', 'userQuestions' => function ($query) {
                $query->leftJoin('questions', 'questions.id', '=', 'user_questions.question_id')
                      ->leftJoin('sub_questions', 'sub_questions.id', '=', 'user_questions.answer_id')
                      ->select('user_questions.id', 'user_questions.user_id', 'user_questions.question_id', 'user_questions.answer_id', 'questions.question', 'sub_questions.option');
            }])->find($id);
            
            if(isset($request->id)){
                $data['user']->makeHidden(['google_id','facebook_id','apple_id']);
            } 

            if(!empty($data['user']) && !empty($data['user']['iceBreakers'])){
                $data['user']['ice_breakers_new'] = $data['user']['iceBreakers']->concat($iceBreakers);
            }

            $data['user']->photos->map(function ($photo) {
                $photo->append('profile_photo');
            });
           
            $hobbies_id                       = $data['user']['hobbies'];
            $hobbies_array                    = explode(",", $hobbies_id); 
            $data['user']['hobbies_array']    = array_map('intval', $hobbies_array);
            $hobbyNames                       = Hobby::whereRaw("FIND_IN_SET(id, '$hobbies_id') > 0")->pluck('name');
            
            $ethnticity_id                    = $data['user']['ethnticity'];
            $ethnticity_array                 = explode(",", $ethnticity_id); 
            $data['user']['ethnticity_array'] = array_map('intval', $ethnticity_array);
            $ethnticityNames                  = Ethnicity::whereRaw("FIND_IN_SET(id, '$ethnticity_id') > 0")->pluck('name');

        
            $data['user']['age_new']        = Age::where('id',$data['user']['age'])->pluck('year')->first();
            $data['user']['body_type_new']  = Bodytype::where('id',$data['user']['body_type'])->pluck('name')->first();
            $data['user']['children_new']   = Children::where('id',$data['user']['children'])->pluck('children')->first();
            $data['user']['education_new']  = Education::where('id',$data['user']['education'])->pluck('name')->first();
            $data['user']['ethnticity_new'] = implode(", ", $ethnticityNames->toArray());
            $data['user']['faith_new']      = Faith::where('id',$data['user']['faith'])->pluck('name')->first();
            $data['user']['gender_new']     = Gender::where('id',$data['user']['gender'])->pluck('gender')->first();
            $data['user']['height_new']     = Height::where('id',$data['user']['height'])->pluck('height')->first();
            $data['user']['hobbies_new']    = implode(", ", $hobbyNames->toArray());
            $data['user']['industry_new']   = Industry::where('id',$data['user']['industry'])->pluck('name')->first();
            $data['user']['salary_new']     = Salary::where('id',$data['user']['salary'])->pluck('range')->first();
            $data['user']['formatted_height']= Height::find($data['user']['height'])->formatted_height;
            
            if($id != Auth::id()){
                
                // Check user is already liked and then after view profile ? in that scnario no data will inserted

                $user_likes = UserLikes::where('like_from',Auth::id())->where('like_to',$id)->first();
                $user_view = UserView::where('view_from',Auth::id())->where('view_to',$id)->first();
                if(empty($user_likes) && empty($user_view)){
                    UserView::create(['view_from'=>Auth::id(),'view_to'=> $id]);

                    // Notification for profile view

                    $title = Auth::user()->full_name ." has viewed your profile";
                    $message = Auth::user()->full_name ." has viewed your profile"; 
                    Helper::send_notification('single', Auth::id(), $id, $title, 'user_view', $message, []);
                };
            }

            return $this->success($data,'User profile data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
   
    // GET LOGGED IN USER PLAN

    public function getUserPlan(Request $request){
        try{
            $today_date = date('Y-m-d');

            $plan_data           = UserSubscription::where('user_id',Auth::user()->id)->whereDate('expire_date','>=',$today_date)->orderby('id','asc')->first();
            if($plan_data == null){
                $plan_data         = Subscription::where('plan_type',"free")->first();
                $data['plan_id']   = (int)$plan_data->id;
                $data['plan_type'] = 'free';
                $undoCount = $plan_data->undo_profile;
            }else{
                $data['plan_id']     = (int)$plan_data->subscription_id;
                $data['plan_type']   = 'paid';
                $undoCount = $plan_data->undo_profile;
            }
            $remainingUndoCount     = $this->remainingUndoCount(Auth::id(), $undoCount);
            $today_like_count       = UserLikes::where('like_from',Auth::user()->id)->whereDate('created_at', date('Y-m-d'))->count();
            $last_undo_date         = Auth::user()->last_undo_date;
            // $undo_remaining_count   = Auth::user()->undo_remaining_count;

            // if(date('Y-m-d') == $last_undo_date){
            //     $data['undo_remaining_count'] = (int)$undo_remaining_count;
            // }else{
            //     $data['undo_remaining_count'] = (int)$plan_data->undo_profile;
            // }
            $data['remaining_likes'] = (int)$plan_data->like_per_day - $today_like_count;
            if($remainingUndoCount < $undoCount){
                $data['remaining_likes'] = (int)$plan_data->like_per_day - $today_like_count - ($undoCount-(int)$remainingUndoCount);
            }
            $data['remaining_undo_count'] = $remainingUndoCount;
            $data['search_filters'] = explode(',',$plan_data->search_filters);
            $data['like_per_day']   = $plan_data->like_per_day;
            $data['video_call']     = $plan_data->video_call;
            $data['who_like_me']    = $plan_data->who_like_me;
            $data['who_view_me']    = $plan_data->who_view_me;
            $data['undo_profile']   = (int)$plan_data->undo_profile;
            $data['read_receipt']   = $plan_data->read_receipt;
            $data['travel_mode']    = $plan_data->travel_mode;
            $data['profile_badge']  = $plan_data->profile_badge;
            return $this->success($data,'User profile plan data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    public function remainingUndoCount($userId, $undoCount)
    {    
        $undoRemainingCount = Auth::user()->undo_remaining_count;
        $lastUndoDate = Auth::user()->last_undo_date;
        $currentDate = Carbon::now()->format('Y-m-d'); 
    
        // Check if it's a new day
        if ($lastUndoDate < $currentDate){
            $user = User::find($userId);
            $user->last_undo_date = $currentDate;
            $user->undo_remaining_count = $undoCount;
            $user->save();
            $remainingUndoCount = $user->undo_remaining_count;
        }else{     
            $remainingUndoCount = Auth::user()->undo_remaining_count;
        }
        return (int)$remainingUndoCount;
    }

    // UPDATE USER PROFILE

    public function updateProfile(Request $request){
        try{
            $messages = [
                'ice_breaker.required' => 'Ice breakers are required',
                'ice_breaker.array' => 'Ice breakers must be an array',
                'ice_breaker.min' => 'Ice breakers must have at least :min items',
                'ice_breaker.*.ice_breaker_id.required' => 'Ice breaker ID is required',
                'ice_breaker.*.answer.required' => 'Answer is required',
                'questions.required' => 'Questions are required',
                'questions.array' => 'Questions must be an array',
                'questions.*.question_id.required' => 'Question ID is required',
                'questions.*.answer_id.required' => 'Answer is required',
            ];
            $validateData = Validator::make($request->all(), [
                'user_id'    => 'required',
                'first_name' => 'required|string|max:255',
                'last_name'  => 'required|string|max:255',
                'email'      => 'nullable|email|max:255|unique:users,email,'.$request->user_id,
                // 'phone_no'   => 'required|string|unique:users,phone_no|max:20',
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
                // 'photos'     => 'sometimes|required',
                // 'photos.*'   => 'sometimes|required|file|mimes:jpeg,png,jpg,mp4,mov,avi|max:100000',
                // 'profile_image'                => 'sometimes|file|mimes:jpeg,png,jpg',
                // 'thumbnail_image'              => 'sometimes|file|mimes:jpeg,png,jpg',
                'ice_breaker'                  => 'required|array|min:1|max:3',
                'ice_breaker.*.ice_breaker_id' => 'required',
                'ice_breaker.*.answer'         => 'required',
                'questions'                    => 'required|array',
                'questions.*.question_id'      => 'required',
                'questions.*.answer_id'        => 'required',
            ], $messages);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            } 

            $user_data = User::where('id',$request->user_id)->first();

            $otp = 0;
            if($user_data){
                if(!empty(($user_data->email)) && ($user_data->email != $request->email)){
                    // $user_data->email_verified = 0;
                    // $user_data->otp_verified = 0;
                    // $user_data->save();
                    $response = (new AuthController)->sendOtp($request);
                    $data11 = json_decode($response->getContent(), true);  
                    if ($data11 && isset($data11['data']['otp'])) {
                        $otp = (int)$data11['data']['otp'];  
                    } 
                }
                $user_data->update($request->except(['phone_no','email']));

                // Check ice_breaker id which is present in old data but not in new 

                $new_ice_breaker_id = collect($request['ice_breaker'])->pluck('ice_breaker_id');
                $old_ice_breaker_id = UserIceBreaker::where('user_id',$request->user_id)->pluck('ice_breaker_id');
                $ids_to_delete = $old_ice_breaker_id->diff($new_ice_breaker_id);
                UserIceBreaker::whereIn('ice_breaker_id', $ids_to_delete)->where('user_id',$request->user_id)->delete();

                foreach($request['ice_breaker'] as $ice_breaker_data){
                    $user_ice_breaker_data = UserIceBreaker::where('user_id',$request->user_id)->where('ice_breaker_id', $ice_breaker_data['ice_breaker_id'])->first();
                    if($user_ice_breaker_data){
                        $user_ice_breaker_data->update(['answer'=> $ice_breaker_data['answer']]);
                    }else{
                        $ice_breaker_data['user_id'] = $user_data->id;
                        UserIceBreaker::create($ice_breaker_data);
                    }
                }

                foreach($request['questions'] as $question){
                    $user_question_data = UserQuestion::where('user_id',$request->user_id)->where('question_id', $question['question_id'])->first();
                    if($user_question_data){
                        if (strpos($question['answer_id'], ',') !== false) {
                            $user_question_data = UserQuestion::where('user_id',$request->user_id)->where('question_id', $question['question_id'])->delete();
                            $answer_ids = explode(',', $question['answer_id']);
                            $question_new = [];
                            foreach ($answer_ids as $answer_id) {
                                $question_new[] = [
                                    'user_id' => Auth::id(),
                                    'question_id' => $question['question_id'],
                                    'answer_id' => $answer_id,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                            UserQuestion::insert($question_new);
                        }else{
                            $user_question_data->update(['answer_id'=> $question['answer_id']]);
                        }
                    }
                }
                
                // $folderPath = public_path().'/user_profile';

                // if (!is_dir($folderPath)) {
                //     mkdir($folderPath, 0777, true);
                // }

                // $mediaFiles = $request->file('photos');
                // $thumbnailImage = $request->file('thumbnail_image');
                // $profileImage = $request->file('profile_image');
                // $user_photo_data = [];

                // if (isset($request->image) && $request->hasFile('photos')) {

                //     $userPhotos = UserPhoto::whereIn('id', $request->image)->where('user_id',$request->user_id)->where('type','!=','thumbnail_image');
                //     $user_old_photo_name = $userPhotos->pluck('name')->toArray();
                    
                //     $deletedFiles = [];
                //     if(!empty($user_old_photo_name)){
                //         foreach ($user_old_photo_name as $name) {
                //             $path = public_path('user_profile/' . $name);
                //             if (File::exists($path)) {
                //                 if (!is_writable($path)) {
                //                     chmod($path, 0777);
                //                 }
                //                 File::delete($path);
                //                 $deletedFiles[] = $path;
                //             }
                //         };
                //     }
                //     $userPhotos->delete();
                //     $user_photo_data = $this->uploadMediaFiles($mediaFiles, $user_data->id);
                // }

                // if (!empty($thumbnailImage)) {
                //     $this->deleteUserPhotos(null, $request->user_id, 'thumbnail_image');
                //     $user_photo_data[] = $this->uploadImageFile($thumbnailImage, $user_data->id, 'thumbnail_image');
                // }

                // if (!empty($profileImage)) {
                //     $this->deleteUserPhotos(null, $request->user_id, 'profile_image');
                //     $user_photo_data[] = $this->uploadImageFile($profileImage, $user_data->id, 'profile_image');
                // }

                // UserPhoto::insert($user_photo_data);

                $user_data->new_email = null;
                if(!empty(($user_data->email)) && ($user_data->email != $request->email)){
                    $user_data->new_email = $request->email;
                    if($otp > 0){
                        $user_data->otp = $otp;
                    }
                }
                return $this->success($user_data,'Your profile successfully updated');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // SWIPE PROFILE

    public function swipeProfile(Request $request){ 
        try{
            $validateData = Validator::make($request->all(), [
                'like_to' => 'required',
                'status' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $user_id          = Auth::id();
            $today_date       = date('Y-m-d');
            $plan_data        = UserSubscription::where('user_id',$user_id)->whereDate('expire_date','>=',$today_date)->orderby('id','asc')->first();
            if($plan_data == null){
                $plan_data         = Subscription::where('plan_type',"free")->first();
                $undoCount = $plan_data->undo_profile;
            }else{
                $undoCount = $plan_data->undo_profile;
            }
            $today_like_count = UserLikes::where('like_from',$user_id)->whereDate('created_at', date('Y-m-d'))->count();
          
            $remainingUndoCount     = $this->remainingUndoCount(Auth::id(), $undoCount);
            $data['remaining_likes'] = (int)$plan_data->like_per_day - $today_like_count - 1;
            if($remainingUndoCount < $undoCount){
                $data['remaining_likes'] = (int)$plan_data->like_per_day - $today_like_count - 1 - ($undoCount-(int)$remainingUndoCount);
            }

            $input              = $request->all();
            $input['like_from'] = Auth::id();
            $input['status']    = (strtolower($input['status']) == 'like') ? 1 : 0;
            $input['match_id']  = 0;

            // Check user already liked or disliked opposite user if yes then not insert else create
            
            $same_request = UserLikes::where('like_from',$input['like_from'])->where('like_to',$input['like_to'])->where('status',$input['status'])->exists();
          
            // Check opposite user is already liked or disliked if yes then set match_id,match_status,matched_at else set to default

            $opposite_request = UserLikes::where('like_from',$input['like_to'])->where('like_to',$input['like_from'])->where('status',$input['status'])->exists();
           
            $input['can_chat'] = 1;
            $oppsite_user_gender = User::where('id',$input['like_to'])->select('gender')->first();
            $check_female_gender = '';
            if(Auth::user()->gender != $oppsite_user_gender->gender){
                $input['can_chat'] = 0;
                if($oppsite_user_gender->gender == 2){
                    $check_female_gender = 'oppsite_user_gender';
                }
                if(Auth::user()->gender == 2){
                    $check_female_gender = 'auth_user_gender';
                }
            } 
            
            if($opposite_request && $input['status'] == 1){
                $maxId = UserLikes::where('match_id', '>', 0)->max('match_id');
             
                $input['match_id']      = ($maxId > 10000 || $maxId == 10000) ? $maxId + 1 : 10000;
                $input['match_status']  = 1;
                $input['matched_at']    = now();
                
                UserLikes::where('like_from',$input['like_to'])->where('like_to',$input['like_from'])->where('status',$input['status'])->update(
                    ['match_id' => $input['match_id'],'match_status' => $input['match_status'],'matched_at' => $input['matched_at'], 'can_chat' => $input['can_chat']]);

                // Notification for match profile both side
                $receiver_data = User::where('id',$input['like_to'])->first();

                $sender_image =  asset('images/konnected-dating.png');
                $login_user_image_data = UserPhoto::where('user_id',Auth::id())->where('type','profile_image')->first();
    
                if(!empty($login_user_image_data)){
                    $sender_image = $login_user_image_data->profile_photo;
                }
              
                $receiver_image =  asset('images/konnected-dating.png');
                $login_user_image_data = UserPhoto::where('user_id',$input['like_to'])->where('type','profile_image')->first();
    
                if(!empty($login_user_image_data)){
                    $receiver_image = $login_user_image_data->profile_photo;
                }

                $title = "Congrats! You have a match with ".Auth::user()->full_name;
                $message = "Congrats! You have a match with ".Auth::user()->full_name; 
                $data_for_receiver = array('match_id' => $input['match_id'],'sender_id'=> (int)$input['like_to'],'sender_name' => $receiver_data->full_name,'sender_image'=> $receiver_image,'receiver_id'=> Auth::id(),'receiver_name' => Auth::user()->full_name,'receiver_image'=> $sender_image,'can_chat' => 0);
                Helper::send_notification('single', Auth::id(), $input['like_to'], $title, 'match', $message, $data_for_receiver);
              
                // Notification for match profile both side
                
                $title = "Congrats! You have a match with ". $receiver_data->full_name;
                $message = "Congrats! You have a match with ". $receiver_data->full_name; 
                $data_for_sender = array('match_id' => $input['match_id'],'sender_id'=> Auth::id(),'sender_name' => Auth::user()->full_name,'sender_image'=> $sender_image,'receiver_id'=> (int)$input['like_to'],'receiver_name' => $receiver_data->full_name,'receiver_image'=> $receiver_image,'can_chat' => 0);
                Helper::send_notification('single', $input['like_to'], Auth::id(), $title, 'match', $message, $data_for_sender);
               
                $custom = [
                    'sender_id'     =>  Auth::id(),
                    'match_id'      =>  $input['match_id'],
                    'sender_name'   =>  Auth::user()->full_name,
                    'sender_image'  =>  $sender_image,
                    'image'         =>  $sender_image,
                ]; 

                if($check_female_gender != ''){
                    $title = "You've got a chat request to approve.";
                    $message = "You've got a chat request to approve."; 
                    if($check_female_gender == 'auth_user_gender'){
                        Helper::send_notification('single', 0, Auth::id(), $title, 'reminder_allow_chat', $message, $custom);
                    }else{
                        Helper::send_notification('single', 0, $input['like_to'], $title, 'reminder_allow_chat', $message, $custom);
                    }
                }
            }

            if(!$same_request){
                // Check logged in user viewd opposite user profile and now liking that user profile then delete

                if($input['status'] == 1 || $input['status'] == 0){
                    UserView::where('view_from',Auth::id())->where('view_to',$input['like_to'])->delete();
                }

                // Check logged in user viewd opposite user profile and now liking or disliking that user profile then delete
                //UserReviewLater::where('user_review_from',Auth::id())->where('user_review_to',$input['like_to'])->delete();
                 
                UserReviewLater::where(function ($query) use ($input) {
                    $query->where('user_review_from', Auth::id())
                          ->where('user_review_to', $input['like_to']);
                })->orWhere(function ($query) use ($input) {
                    $query->where('user_review_to', Auth::id())
                          ->where('user_review_from', $input['like_to']);
                })->delete();
                
                
                // Check logged in user's profile viewd by opposite user profile and now logged in user liking or disliking that user profile then delete
                
                UserView::where('view_from',$input['like_to'])->where('view_to',Auth::id())->delete();
                
                UserLikes::create($input);

                if($input['status'] == 1){
                    // Notification for profile like

                    $title = Auth::user()->full_name ." has liked your profile";
                    $message = Auth::user()->full_name ." has liked your profile"; 
                    Helper::send_notification('single', Auth::id(), $input['like_to'], $title, 'like', $message, []);
                }
            }
            return $this->success($data,'Profile liked successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // ALLOW CHAT

    public function allowChat(Request $request){ 
        try{
            $validateData = Validator::make($request->all(), [
                'match_id' => 'required', 
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }   

            UserLikes::where('match_id',$request->match_id)->update(['can_chat' => 1]);

            $user_allow_notification = UserLikes::where('match_id',$request->match_id)->where('like_from',Auth::id())->select('like_to')->first();

            
            $sender_image =  asset('images/konnected-dating.png');
            $login_user_image_data = UserPhoto::where('user_id',Auth::id())->where('type','profile_image')->first();

            if(!empty($login_user_image_data)){
                $sender_image = $login_user_image_data->profile_photo;
            }
            $custom = [
                'sender_id'     =>  Auth::id(),
                'match_id'      =>  $request->match_id,
                'sender_name'   =>  Auth::user()->full_name,
                'sender_image'  =>  $sender_image,
                'image'         =>  $sender_image,
            ]; 

            // Notification for profile allow for chat

            $title = Auth::user()->full_name . " has accepted your chat invitation";
            $message = Auth::user()->full_name . " has accepted your chat invitation";
            Helper::send_notification('single', Auth::id(), $user_allow_notification->like_to, $title, 'allow_chat', $message, $custom);
            return $this->success([],'Allow chat');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // DISCOVER PROFILE

    public function discoverProfile(Request $request){ 
        try{
            $validateData = Validator::make($request->all(), [
                'gender' => 'required',
                'min_age' => 'required',
                'max_age' => 'required|gte:min_age',
                'min_height' => 'required',
                'max_height' => 'required|gte:min_height',
                'latitude'    => 'required',
                'longitude'   => 'required'
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $auth_lat1 = $request->input('latitude');
            $auth_lon1 = $request->input('longitude');

            $query = User::where('users.id', '!=', Auth::id())
                            ->where('user_type', 'user')
                            ->where('users.status', 1)
                            ->where('email_verified', 1)
                            ->where('gender', $request->gender)
                            ->whereRaw("CAST(age AS UNSIGNED) BETWEEN $request->min_age AND $request->max_age")
                            ->whereRaw("CAST(height AS UNSIGNED) BETWEEN $request->min_height AND $request->max_height");

                            if($request->has('location')){                                        
                                // $query->where('location', $request->location);
                            } 

                            if ($request->has('education')) {
                                $query->where('education', $request->education);
                            }

                            if ($request->has('industry')) {
                                $query->where('industry', $request->industry);
                            }

                            if ($request->has('salary')) {
                                $query->where('salary', $request->salary);
                            }

                            if ($request->has('body_type')) {
                                $query->where('body_type', $request->body_type);
                            }

                            if ($request->has('children')) {
                                $query->where('children', $request->children);
                            }
                          
                            if ($request->has('faith')) {
                                $query->where('faith', $request->faith);
                            }
                            
                            if ($request->has('hobbies')) {
                                $query->where(function($query) use ($request) {
                                    if($request->hobbies) {
                                        $hobby_ids = explode(',', $request->hobbies);
                                        foreach($hobby_ids as $id) {
                                            $query->orWhereRaw("FIND_IN_SET($id, hobbies)");
                                        }
                                    }
                                });
                            }
                          
                            if ($request->has('ethnticity')) {
                                $query->where(function($query) use ($request) {
                                    if($request->ethnticity) {
                                        $hobby_ids = explode(',', $request->ethnticity);
                                        foreach($hobby_ids as $id) {
                                            $query->orWhereRaw("FIND_IN_SET($id, ethnticity)");
                                        }
                                    }
                                });
                            }

                            $query->leftJoin('user_likes as ul1', function ($join) {
                                $join->on('users.id', '=', 'ul1.like_from')
                                     ->where('ul1.like_to', '=', Auth::id());
                            })->leftJoin('user_likes as ul2', function ($join) {
                                $join->on('users.id', '=', 'ul2.like_to')
                                     ->where('ul2.like_from', '=', Auth::id());
                            })->leftJoin('user_review_laters as ur1', function ($join) {
                                $join->on('users.id', '=', 'ur1.user_review_to')
                                     ->where('ur1.user_review_from', '=', Auth::id());
                            })->leftJoin('user_reports as ur2', function ($join) {
                                $join->on('users.id', '=', 'ur2.reported_user_id')
                                     ->where('ur2.reporter_id', '=', Auth::id());
                            })->leftJoin('user_reports as ur3', function ($join) {
                                $join->on('users.id', '=', 'ur3.reporter_id')
                                     ->where('ur3.reported_user_id', '=', Auth::id());
                            })->whereNull('ul1.id')->whereNull('ul2.id')->whereNull('ur1.id')->whereNull('ur2.id')->whereNull('ur3.id');
                            
            // $user_list = $query->select('users.id', 'first_name', 'last_name', 'location', 'job', 'age','live_latitude','live_longitude')
            $user_list  = $query->select(\DB::raw("users.id,first_name,last_name,location,job,age,latitude,longitude,(3959 * 
                            acos(
                                cos(radians(" . $auth_lat1 . ")) * 
                                cos(radians(latitude)) * 
                                cos(radians(longitude) - radians(" . $auth_lon1 . ")) + 
                                sin(radians(" . $auth_lat1 . ")) * 
                                sin(radians(latitude))
                            )
                        ) AS distance"))
                        ->having('distance', '<=', 50)
                        ->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));


            $data['user_list']  =   $user_list->map(function ($user){
                                        $profile_photo_media = $user->photos->firstWhere('type', 'profile_image');
                                        $user->name = $user->first_name.' '.$user->last_name;
                                        $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                        $user->age_new  = Age::where('id',$user->age)->pluck('year')->first();
                                        $user->location  = $user->location;
                                        $user->job  = $user->job;
                                        unset($user->photos);
                                        return $user;
                                    });

            $data['current_page'] = $user_list->currentPage();
            $data['per_page']     = $user_list->perPage();
            $data['total']        = $user_list->total();
            $data['last_page']    = $user_list->lastPage();

            // if (empty($data['user_list'][0])) {
            //     $data['user_list'] = [];
            // }

            return $this->success($data,'Discovery list');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // MATCHED USER LISTING

    public function matchedUserList(Request $request){

        try{
            $matched_user_listing = UserLikes::where('user_likes.like_to',Auth::id())
                                        ->where('user_likes.status',1)
                                        ->where('user_likes.match_status',1)
                                        ->where('user_likes.match_id','>',0)
                                        ->leftJoin('chats as c', function ($join) {
                                            $join->on('user_likes.match_id', '=', 'c.match_id');
                                        }) 
                                        ->whereNull('c.id') 
                                        ->select('user_likes.id', 'user_likes.like_from','user_likes.like_to','user_likes.match_id','user_likes.can_chat')
                                        ->get();

            $data['matched_user_listing'] = $matched_user_listing->map(function ($user){
                                            if($user->users->isNotEmpty()){
                                                $profile_photo_media = $user->users->first()->photos->firstWhere('type', 'profile_image'); 
                                                $user->user_id = $user->users->first()->id;
                                                $user->name = $user->users->first()->first_name.' '.$user->users->first()->last_name;
                                                $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                                unset($user->users);
                                            }
                                            return $user;
                                        })->filter(function ($user){
                                            return isset($user->user_id);
                                        })
                                        ->values();


            return $this->success($data,'Matched user listing');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // GET CHAT LIST

    public function chatList(Request $request){
        try{
            $chat_list          =   Chat::where(function ($query) {
                                        $query->where('receiver_id', Auth::id())
                                            ->orWhere('sender_id', Auth::id());
                                    })
                                    ->join(DB::raw('(SELECT MAX(id) AS latest_chat_id FROM chats GROUP BY match_id) AS latest_chats'), 'chats.id', '=', 'latest_chats.latest_chat_id')
                                    ->select('chats.id', 'chats.match_id','chats.sender_id','chats.receiver_id','chats.read_status','chats.type','chats.created_at')
                                    ->selectRaw('MAX(chats.message) as last_message')
                                    ->selectRaw('(SELECT COUNT(*) FROM chats AS sub_chats WHERE sub_chats.match_id = chats.match_id AND sub_chats.read_status = 0 AND sub_chats.receiver_id = '.Auth::id().') as unread_message_count')
                                    ->leftJoin('user_likes as ul', function ($join) {
                                        $join->on('chats.match_id', '=', 'ul.match_id');
                                    })
                                    ->where('ul.match_status',1) 
                                    ->groupBy('chats.match_id')
                                    ->orderBy('chats.created_at', 'desc')
                                    ->orderBy('chats.id', 'desc')
                                    ->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));

            $data['chat_list']  =   $chat_list->map(function ($user){
                                            if($user->sender_id == Auth::id() && $user->userReceiver->isNotEmpty()){
                                                $profile_photo_media = $user->userReceiver->first()->photos->firstWhere('type', 'profile_image'); 
                                                $user->user_id = $user->userReceiver->first()->id;
                                                $user->name = $user->userReceiver->first()->first_name.' '.$user->userReceiver->first()->last_name;
                                                $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                                $user->unread_message_count = (int)$user->unread_message_count;
                                                $user->last_message = $user->last_message;
                                                unset($user->userReceiver);
                                            }
                                            if($user->sender_id != Auth::id() && $user->users->isNotEmpty()){
                                                $profile_photo_media = $user->users->first()->photos->firstWhere('type', 'profile_image'); 
                                                $user->user_id = $user->users->first()->id;
                                                $user->name = $user->users->first()->first_name.' '.$user->users->first()->last_name;
                                                $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                                $user->unread_message_count = (int)$user->unread_message_count;
                                                $user->last_message = $user->last_message;
                                                unset($user->users);
                                            }
                                            return $user;
                                        })->filter(function ($user){
                                            return isset($user->user_id);
                                        })
                                        ->values();

            $data['current_page'] = $chat_list->currentPage();
            $data['per_page']     = $chat_list->perPage();
            $data['total']        = $chat_list->total();
            $data['last_page']    = $chat_list->lastPage();
            return $this->success($data,'Chat list');

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // CHANGE MESSAGE READ STATUS 

    public function changeReadStatus(Request $request){
        try{
            $chat_read_status   =   Chat::where('receiver_id',Auth::id())
                                    ->where('match_id',$request->match_id)
                                    ->update(['read_status' => 1]);

            return $this->success([],'Chat read successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // SEND MESSAGE 

    public function sendMessage(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'match_id' => 'required',
                'receiver_id' => 'required',
                'message' => 'required',
                'type' => 'required',
                'coins_number' => 'required_if:type,gift',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $chats = new Chat();
            $chats->match_id    = $request->match_id;
            $chats->sender_id   = Auth::id();
            $chats->receiver_id = $request->receiver_id;
            $chats->message     = $request->message;
            $chats->type        = $request->type;
            $chats->save();

            $sender_image =  asset('images/konnected-dating.png');
            $login_user_image_data = UserPhoto::where('user_id',Auth::id())->where('type','profile_image')->first();

            if(!empty($login_user_image_data)){
                $sender_image = $login_user_image_data->profile_photo;
            }
            $custom = [
                'sender_id'     =>  Auth::id(),
                'match_id'      =>  $request->match_id,
                'sender_name'   =>  Auth::user()->full_name,
                'sender_image'  =>  $sender_image,
                'image'         =>  $sender_image,
            ]; 
               
            $can_chat = UserLikes::where('match_id',$request->match_id)->first();
            if($can_chat->can_chat == 0){
                UserLikes::where('match_id',$request->match_id)->update(['can_chat' => 1]);
                $user_allow_notification = UserLikes::where('match_id',$request->match_id)->where('like_from',Auth::id())->select('like_to')->first();
    
                // Notification for profile allow for chat
    
                $title = Auth::user()->full_name . " has accepted your chat invitation";
                $message = Auth::user()->full_name . " has accepted your chat invitation";
                Helper::send_notification('single', Auth::id(), $user_allow_notification->like_to, $title, 'allow_chat', $message, $custom);
            }

            // Notification for message send
            $data = [];
            if($request->type != 'gift'){
                $title = Auth::user()->full_name." has sent you a message";
                $message = Auth::user()->full_name." has sent you a message"; 
                Helper::send_notification('single', Auth::id(), $request->receiver_id, $title, 'message', $message, $custom);
            }else{

                $user_coin = new UserCoin();
                
                $user_coin->sender_id       = Auth::id();
                $user_coin->receiver_id     = $request->receiver_id;
                $user_coin->coins_number    = $request->coins_number;
                $user_coin->message         = "Received Gift From ". Auth::user()->full_name;
                $user_coin->type            = 'gift_card_receive';
                $user_coin->action          = '+'.$request->coins_number;
                $user_coin->save();
                
                $receiver_user = User::select('*', DB::raw('CONCAT(first_name, " ", last_name) AS full_name1'))->where('id',$request->receiver_id)->first();
                $user_coin = new UserCoin();
                $user_coin->sender_id       = Auth::id();
                $user_coin->receiver_id     = $request->receiver_id;
                $user_coin->coins_number    = $request->coins_number;
                $user_coin->message         = "Sent Gift To ". $receiver_user->full_name1;
                $user_coin->type            = 'gift_card_sent';
                $user_coin->action          = '-'.$request->coins_number;
                $user_coin->save();
    
               
                $data['total_balance'] = UserCoin::where('receiver_id', Auth::id())
                                        ->orWhere('sender_id', Auth::id())
                                        ->whereIn('type', ['purchase_coin', 'gift_card_receive', 'purchase_plan', 'gift_card_sent'])
                                        ->selectRaw('SUM(CASE WHEN receiver_id = ? AND type IN ("purchase_coin", "gift_card_receive") THEN coins_number ELSE 0 END) 
                                                    - SUM(CASE WHEN sender_id = ? AND type IN ("purchase_plan", "gift_card_sent") THEN coins_number ELSE 0 END) 
                                                    AS total_balance', [Auth::id(), Auth::id()])
                                        ->value('total_balance') ?? 0;
    
                $title =  Auth::user()->full_name ." has sent you a gift";
                $message =  Auth::user()->full_name ." has sent you a gift"; 
                Helper::send_notification('single', Auth::id(), $request->receiver_id, $title, 'gift_card_receive', $message, $custom);
            }
       
            return $this->success($data,'Message send successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // UNMATCH 

    public function unmatch(Request $request){
        try{

            $validateData = Validator::make($request->all(), [
                'match_id' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            UserLikes::where('user_likes.match_id',$request->match_id)->update(['user_likes.match_status' => 0]);
            
            $user_data =  UserLikes::where('user_likes.match_id',$request->match_id)->first();

            $notification_receiver_id = 0;
            if($user_data->like_from != Auth::id()){
                $notification_receiver_id = $user_data->like_from;
            }

            if($user_data->like_to != Auth::id()){
                $notification_receiver_id = $user_data->like_to;
            } 
            
            // Notification for unmatch profile both side

            $title = "You have unmatched with ".Auth::user()->full_name;
            $message = "You have unmatched with ".Auth::user()->full_name; 
            Helper::send_notification('single', Auth::id(), $notification_receiver_id, $title, 'unmatch', $message, []);

            return $this->success([],'Unmatch done successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // REPORT 

    public function report(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                // 'match_id' => 'required',
                'message' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            UserLikes::where('user_likes.match_id',$request->match_id)->update(['user_likes.match_status' => 0]);

            $user_report = new UserReport();
            $user_report->match_id          = $request->match_id ?? null;
            $user_report->reporter_id       = Auth::id();
            $user_report->reported_user_id  = $request->reported_user_id;
            $user_report->message           = $request->message;
            $user_report->save();

            // Notification for report

            $title = Auth::user()->full_name ." has reported your profile";
            $message = Auth::user()->full_name ." has reported your profile"; 
            Helper::send_notification('single', Auth::id(), $request->reported_user_id, $title, 'report', $message, []);

            return $this->success([],'Report done successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // CONTACTSUPPORT 

    public function contactSupport(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required',
                'description' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $support                = new ContactSupport();
            $support->name          = $request->name;
            $support->email         = $request->email;
            $support->description   = $request->description;
            $support->save();

            return $this->success([],'Request added successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // REVIEW LATER

    public function reviewLater(Request $request){ 
        try{
            $validateData = Validator::make($request->all(), [
                'user_id' => 'required', 
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }
            
            $user_likes = UserLikes::where('like_from',Auth::id())->where('like_to',$request->user_id)->first();
            $user_view = UserReviewLater::where('user_review_from',Auth::id())->where('user_review_to',$request->user_id)->first();
            if(empty($user_likes) && empty($user_view)){
                UserReviewLater::create(['user_review_from'=>Auth::id(),'user_review_to'=> $request->user_id]);
            };
           
            return $this->success([],'Profile added to review later successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // UNDO 

    public function undoProfile (Request $request){
        try{
            $undo_profile_listing = [UserLikes::where('user_likes.like_from',Auth::id())
                                            ->where('user_likes.match_status',2)
                                            ->where('user_likes.status',0)
                                            ->select('user_likes.id', 'user_likes.like_from','user_likes.like_to')
                                            ->latest()
                                            ->first()];

            $data['user_list'] = [];

            if ($undo_profile_listing[0]) {
                $data['user_list']  =   collect($undo_profile_listing)->map(function ($user){
                    $profile_photo_media = $user->usersLikesTo->first()->photos->firstWhere('type', 'profile_image');
                    $user->id = $user->usersLikesTo->first()->id;
                    $user->name = $user->usersLikesTo->first()->first_name . ' ' . $user->usersLikesTo->first()->last_name;
                    $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                    $user->age_new  = Age::where('id',$user->usersLikesTo->first()->age)->pluck('year')->first();
                    $user->location  = $user->usersLikesTo->first()->location;
                    $user->job  = $user->usersLikesTo->first()->job;
                    unset($user->usersLikesTo);
                    return $user;
                });
                
                UserLikes::where('user_likes.like_from',Auth::id())->where('user_likes.match_status',2)->latest()->first()->delete();

                $user_id          = Auth::id();
                $today_date       = date('Y-m-d');
                $plan_data        = UserSubscription::where('user_id',$user_id)->whereDate('expire_date','>=',$today_date)->orderby('id','asc')->first();
                if($plan_data == null){
                    $plan_data         = Subscription::where('plan_type',"free")->first();
                } 
                $user = User::where('id',Auth::id())->first();
                if($user->undo_remaining_count !== null && $user->last_undo_date == date('Y-m-d')){
                    $data['remaining_undo'] = $user->undo_remaining_count - 1;
                }else{
                    $data['remaining_undo'] = (int)$plan_data->undo_profile - 1;
                }
                $user->undo_remaining_count = $data['remaining_undo'] ;
                $user->last_undo_date = date('Y-m-d');
                $user->save();
            }
            return $this->success($data,'Undo profile data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // WHO LIKES ME LISTING

    public function whoLikesMe(Request $request){
        try{
            $user_likes_listing = UserLikes::where('user_likes.like_to',Auth::id())
                                        ->where('user_likes.status',1)
                                        ->where('user_likes.match_status',2)
                                        ->select('user_likes.id', 'user_likes.like_from','user_likes.like_to')
                                        ->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
                                        
            $data['user_likes_listing'] = $user_likes_listing->map(function ($user){
                                            if($user->users->isNotEmpty()){
                                                $profile_photo_media = $user->users->first()->photos->firstWhere('type', 'profile_image');
                                                $user->user_id = $user->users->first()->id;
                                                $user->name = $user->users->first()->first_name.' '.$user->users->first()->last_name;
                                                $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                                unset($user->users);
                                            }
                                            return $user;
                                        })->filter(function ($user){
                                            return isset($user->user_id);
                                        })
                                        ->values();

            $data['current_page'] = $user_likes_listing->currentPage();
            $data['per_page']     = $user_likes_listing->perPage();
            $data['total']        = $user_likes_listing->total();
            $data['last_page']    = $user_likes_listing->lastPage();

            return $this->success($data,'Who likes me listing');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // STATIC PAGE DATA

    public function staticPage(Request $request){
        try{
            $data['static_page_data']  = Setting::all();
            return $this->success($data,'Static page data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // CATEGORY LIST

    public function categoryList(Request $request){
        try{
            $data['category_data']  = Category::all();
            return $this->success($data,'Category list');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // FAQ LIST

    public function faqList(Request $request,$cat_id){
        try{
            $data['faq_data']  = Faq::where('category_id',$cat_id)->get();
            return $this->success($data,'Faq data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // WHO VIEWED ME LISTING

    public function whoViewedMe(Request $request){
        try{
            $user_view_listing  = UserView::where('user_views.view_to',Auth::id())
                                        ->select('user_views.id', 'user_views.view_from','user_views.view_to')
                                        ->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
                                        
            $data['user_view_listing'] = $user_view_listing->map(function ($user){
                                            if($user->users->isNotEmpty()){
                                                $profile_photo_media = $user->users->first()->photos->firstWhere('type', 'profile_image');
                                                $user->user_id = $user->users->first()->id;
                                                $user->name = $user->users->first()->first_name.' '.$user->users->first()->last_name;
                                                $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                                unset($user->users);
                                            }
                                            return $user;
                                        })->filter(function ($user){
                                            return isset($user->user_id);
                                        })
                                        ->values();

            $data['current_page'] = $user_view_listing->currentPage();
            $data['per_page']     = $user_view_listing->perPage();
            $data['total']        = $user_view_listing->total();
            $data['last_page']    = $user_view_listing->lastPage();

            return $this->success($data,'Who viewd me listing');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // REVIEW LATER LISTING

    public function reviewLaterList(Request $request){
        try{
            $user_review_later_listing = UserReviewLater::where('user_review_laters.user_review_from',Auth::id())
                                                ->select('user_review_laters.id', 'user_review_laters.user_review_from','user_review_laters.user_review_to')
                                                ->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));

            $data['user_review_later_listing'] = $user_review_later_listing->map(function ($user){
                                                    if($user->users->isNotEmpty()){
                                                        $profile_photo_media = $user->users->first()->photos->firstWhere('type', 'profile_image');
                                                        $user->user_id = $user->users->first()->id;
                                                        $user->name = $user->users->first()->first_name.' '.$user->users->first()->last_name;
                                                        $user->profile_photo = $profile_photo_media->profile_photo ?? null;
                                                        unset($user->users);
                                                    }
                                                    return $user;
                                                })->filter(function ($user){
                                                    return isset($user->user_id);
                                                })
                                                ->values();
                                                 
            $data['current_page'] = $user_review_later_listing->currentPage();
            $data['per_page']     = $user_review_later_listing->perPage();
            $data['total']        = $user_review_later_listing->total();
            $data['last_page']    = $user_review_later_listing->lastPage();
            return $this->success($data,'User review later me listing');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // USER LIVE LOCATION UPDATE
    
    public function updateLocation(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'latitude'  => 'required',
                'longitude' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            if (Auth::user()) {
                $user_id   = Auth::user()->id;
                $user_data = User::where('id',$user_id)->update(['live_latitude' =>  $request->latitude, 'live_longitude' => $request->longitude]);
                return $this->success([],'Location updated successfullly');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // VIDEO CALL 

    public function singleVideoCall(Request $request){
        try{
            $validateData = Validator::make($request->all(),[
                'receiver_id'  => 'required|int',
            ]);
    
            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            if (Auth::user()) { 
                $appID =  env("AGORA_APP_ID", "4f6f13fdda8c4d039249274d1b8ac229");
                $appCertificate = env("AGORA_APP_CERTIFICATE", "a05000ab3f024995b468bbec55fbb7b4");

                $channelName = $this->generateRandomChannel(8);
                $userId = $this->generateRandomUid();
                $role = RtcTokenBuilder::RolePublisher;

                $expireTimeInSeconds = 3600;
                $currentTimestamp = now()->getTimestamp();
                $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

                $rtcToken1 = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, $userId, $role, $privilegeExpiredTs);
                $sender_image =  asset('images/konnected-dating.png');
                $login_user_image_data = UserPhoto::where('user_id',Auth::id())->where('type','profile_image')->first();

                if(!empty($login_user_image_data)){
                    $sender_image = $login_user_image_data->profile_photo;
                }

                $userIdReceiver = $this->generateRandomUid();
                $roleReceiver = RtcTokenBuilder::RoleSubscriber;
                $rtcTokenReceiver = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, $userIdReceiver, $roleReceiver, $privilegeExpiredTs);

                $data = [
                    'sender_id'     =>  Auth::id(),
                    'receiver_id'   =>  $request->receiver_id,
                    'receiver_u_id' =>  $userId,
                    'channel_name'  =>  $channelName,
                    'receiver_token'=>  $rtcToken1,
                    'sender_name'   =>  Auth::user()->full_name,
                    'sender_image'  =>  $sender_image,
                    'userIdReceiver'  =>  $userIdReceiver,
                    'roleReceiver'  =>  $roleReceiver,
                    'rtcTokenReceiver'  =>  $rtcTokenReceiver,
                    'image'         =>  $sender_image,
                ];    

                // Notification for video call

                $title = "You have a video call request from ". Auth::user()->full_name;
                $message = "You have a video call request from ". Auth::user()->full_name; 
                Helper::send_notification('single', Auth::id(), $request->receiver_id, $title, 'video_call', $message, $data);

                return $this->success($data,'Video call done');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    public function generateRandomChannel($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function generateRandomUid($length = 9) {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function declineVideoCall(Request $request)
    {
        try
        {
            $validateData = Validator::make($request->all(),[
                'receiver_id'  => 'required',
            ]);
    
            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $user = User::where('id',Auth::id())->first();

            $receiver_image =   asset('images/konnected-dating.png');
            $login_user_image_data = UserPhoto::where('user_id',Auth::id())->where('type','profile_image')->first();

            if(!empty($login_user_image_data)){
                $receiver_image = $login_user_image_data->profile_photo;
            }
            $data = [
                'sender_id'     =>  Auth::id(), 
                'receiver_id'   =>  $request->receiver_id, 
                'receiver_image'=>  $receiver_image,           
                'image'         =>  $receiver_image,           
            ];    
            
            $title = $user->full_name ." has declined your video call";
            $message = $user->full_name ." has declined your video call"; 

            Helper::send_notification('single', Auth::id(), $request->receiver_id, $title, 'decline_call', $message, $data);
            return $this->success([],'Video call declined');
        }
        catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur!');
        }
    }

    // FCM TOKEN SET

    public function updateFcmToken(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'fcm_token' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            User::where('id',Auth::id())->update(['fcm_token' => $request->fcm_token]);
           
            return $this->success([],'Token updated successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // NOTIFICATION LIST

    public function notificationList(Request $request){
        try{
            // $notification_id  = Notification::where('receiver_id',Auth::id())->orderBy('id','desc')->take(30)->pluck('id')->toArray();
            // Notification::whereNotIn('id', $notification_id)->where('receiver_id',Auth::id())->delete();

            $notification_data  = Notification::where('receiver_id',Auth::id())->orderBy('id','desc')->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));
            $data['notification_data'] = $notification_data->map(function ($notification){
                $date = date('d/m/Y', strtotime($notification->created_at));

                if($date == date('d/m/Y')) {
                    $notification->date = 'Today';
                }else if($date == date('d/m/Y', strtotime('-1 day'))) {
                    $notification->date = 'Yesterday';
                }else{
                    $notification->date = date('d M', strtotime($notification->created_at));
                }
                
                $profile_photo_media =  !empty($notification->notificationSender->first()) ? $notification->notificationSender->first()->photos->firstWhere('type', 'profile_image') : null;
                $notification->name = !empty($notification->notificationSender->first()) ? $notification->notificationSender->first()->first_name . ' ' . $notification->notificationSender->first()->last_name : 'Admin';
                $notification->profile_photo = $profile_photo_media->profile_photo ?? null;
                unset($notification->notificationSender);
                
                return $notification;
            })->values();

            return $this->success($data,'Notification data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // NOTIFICATION READ
    
    public function notificationRead(){
        try{
            Notification::where('receiver_id',Auth::id())->update(['status'=>1]);
            return $this->success([],'Notification read successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // NOTIFICATION SETTING

    public function notificationSetting(){
        try{
            $user_data = User::where('id',Auth::id())->first();
            if($user_data['is_notification_mute'] == '0'){
                $user_data['is_notification_mute'] = '1';
                $user_data->save();
                return $this->success([],'Notification disable successfully');
            }

            if($user_data['is_notification_mute'] == 1){
                $user_data['is_notification_mute'] = 0;
                $user_data->save();
                return $this->success([],'Notification enable successfully');
            }
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // SUBSCRIPTION LISTING
    
    public function subscriptionList(Request $request){
        try{
            $data['subscription_list'] = Subscription::all();
            $data['total_balance']     = UserCoin::where('receiver_id', Auth::id())
                                        ->orWhere('sender_id', Auth::id())
                                        ->whereIn('type', ['purchase_coin', 'gift_card_receive', 'purchase_plan', 'gift_card_sent'])
                                        ->selectRaw('SUM(CASE WHEN receiver_id = ? AND type IN ("purchase_coin", "gift_card_receive") THEN coins_number ELSE 0 END) 
                                                    - SUM(CASE WHEN sender_id = ? AND type IN ("purchase_plan", "gift_card_sent") THEN coins_number ELSE 0 END) 
                                                    AS total_balance', [Auth::id(), Auth::id()])
                                        ->value('total_balance') ?? 0;
            return $this->success($data,'Subscription listing');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // PURCHASE SUBSCRIPTION
    
    public function purchaseSubscription(Request $request){
        try{
            $user_id = Auth::id();
            $today_date = date('Y-m-d');
            $is_purchased = UserSubscription::where('user_id',$user_id)->whereDate('expire_date','>=',$today_date)->latest()->first();
            // $total_balance = UserCoin::where('receiver_id', Auth::id())
            // ->orWhere('sender_id', Auth::id())
            // ->whereIn('type', ['purchase_coin', 'gift_card_receive', 'purchase_plan', 'gift_card_sent'])
            // ->selectRaw('SUM(CASE WHEN receiver_id = ? AND type IN ("purchase_coin", "gift_card_receive") THEN coins_number ELSE 0 END) 
            //             - SUM(CASE WHEN sender_id = ? AND type IN ("purchase_plan", "gift_card_sent") THEN coins_number ELSE 0 END) 
            //             AS total_balance', [Auth::id(), Auth::id()])
            //             ->value('total_balance') ?? 0;
                        
            
            $plan_data = Subscription::where('id',$request->subscription_id)->first();
            
            if($is_purchased !== null){
                // IT IS CHECK IF PLAN EXIST THEN TAKE EXPIRE DATE OF THAT PLAN AND ADD ONE DAY IN THAT DATE AND CONSIDER AS START DATE OF NEW PLAN 
                $plan_start_date = date('Y-m-d H:i:s', strtotime($is_purchased->expire_date. ' +1 days'));
                $expire_date = date('Y-m-d H:i:s', strtotime($is_purchased->expire_date. ' +'.$plan_data->plan_duration.' days'));
            } 

            // if($total_balance < $plan_data->coin){
            //     return $this->error("Sorry, but you don't have enough coins to purchase this plan.","Sorry, but you don't have enough coins to purchase this plan.");
            // };
                        
            $user_subscription                  =  new UserSubscription();
            $user_subscription->user_id         =  $user_id; 
            $user_subscription->subscription_id =  $plan_data->id; 
            $user_subscription->start_date      =  $plan_start_date ?? Date('Y-m-d H:i:s'); 
            $user_subscription->expire_date     =  $expire_date ?? Date('Y-m-d H:i:s', strtotime('+'.$plan_data->plan_duration. 'days')); 
            $user_subscription->title           =  $plan_data->title; 
            $user_subscription->description     =  $plan_data->description;
            $user_subscription->search_filters  =  $plan_data->search_filters;
            $user_subscription->like_per_day    =  $plan_data->like_per_day;
            $user_subscription->video_call      =  $plan_data->video_call;
            $user_subscription->who_like_me     =  $plan_data->who_like_me;
            $user_subscription->who_view_me     =  $plan_data->who_view_me;
            $user_subscription->undo_profile    =  $plan_data->undo_profile;
            $user_subscription->read_receipt    =  $plan_data->read_receipt;
            $user_subscription->travel_mode     =  $plan_data->travel_mode;
            $user_subscription->profile_badge   =  $plan_data->profile_badge;
            $user_subscription->price           =  $plan_data->price;   
            $user_subscription->month           =  $plan_data->month; 
            $user_subscription->plan_duration   =  $plan_data->plan_duration; 
            $user_subscription->plan_type       =  $plan_data->plan_type; 
            $user_subscription->google_plan_id  =  $plan_data->google_plan_id; 
            $user_subscription->apple_plan_id   =  $plan_data->apple_plan_id; 
            $user_subscription->save(); 


            // $user_coin = new UserCoin();
            // $user_coin->sender_id       = Auth::id();
            // $user_coin->receiver_id     = 0;
            // $user_coin->coins_number    = $plan_data->coin;
            // $user_coin->message         = "Purchased ".$plan_data->title;
            // $user_coin->type            = "purchase_plan";
            // $user_coin->action          = '-'. $plan_data->coin;
            // $user_coin->save();

            // Arpita mem

            $currentDate = Carbon::now()->format('Y-m-d'); 
            User::where("id",$user_id)->first()->update(array('undo_remaining_count' => (int)$plan_data->undo_profile, 'last_undo_date' => $currentDate));

            // Arpita mem

            // Notification for subscription purchase

            $title = $plan_data->title." plan purchased successfully";
            $message = $plan_data->title." plan purchased successfully"; 
            Helper::send_notification('single', 0, Auth::id(), $title, 'subscription_purchase', $message, []);

            $data['plan_id']         = $plan_data->id;
            $data['plan_type']       = $plan_data->plan_type;
            $data['search_filters'] = explode(',',$plan_data->search_filters);
            $data['like_per_day']   = $plan_data->like_per_day;
            $data['video_call']     = $plan_data->video_call;
            $data['who_like_me']    = $plan_data->who_like_me;
            $data['who_view_me']    = $plan_data->who_view_me;
            $data['undo_profile']   = (int)$plan_data->undo_profile;
            $data['read_receipt']   = $plan_data->read_receipt;
            $data['travel_mode']    = $plan_data->travel_mode;
            $data['profile_badge']  = $plan_data->profile_badge;
            return $this->success($data,'Subscription purchased successfully');
            // return $this->error('You have already purchased plan','You have already purchased plan');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // PURCHASE FROM GOOGLE SUBSCRIPTION

    public function purchaseFromGoogle(Request $request){
        try{

            $validateData = Validator::make($request->all(), [
                'product_id' => 'required',
                'purchase_token' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $productId = $request->product_id;            
            $purchaseToken = $request->purchase_token;            
            $googlePlayService = new GooglePlayService();

            $user_id = Auth::id();
            $today_date = date('Y-m-d H:i:s');

            // if(UserSubscription::where('user_id',$user_id)->where('expire_date','>',$today_date)->count() > 0){
            //     return $this->error('You have already purchased plan','You have already purchased plan');
            // }

            try{
                $result = $googlePlayService->verifyPurchase($productId, $purchaseToken); 
            }catch(\Exception $e){
                return $this->error('Unable to proceed payment','Unable to proceed payment');
            } 

            if($result->orderId){ 
                $time = $result->expiryTimeMillis/1000;
                $exp = new DateTime("@$time"); 

                $plan_data = Subscription::where('google_plan_id',$request->product_id)->first();
                $is_purchased = UserSubscription::where('user_id',$user_id)->whereDate('expire_date','>=',$today_date)->latest()->first();
                if($is_purchased !== null){
                    // IT IS CHECK IF PLAN EXIST THEN TAKE EXPIRE DATE OF THAT PLAN AND ADD ONE DAY IN THAT DATE AND CONSIDER AS START DATE OF NEW PLAN 
                    $plan_start_date = date('Y-m-d H:i:s', strtotime($is_purchased->expire_date. ' +1 days'));
                    $expire_date = date('Y-m-d H:i:s', strtotime($is_purchased->expire_date. ' +'.$plan_data->plan_duration.' days'));
                }else{
                    $expire_date = $exp->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s') ?? Date('Y-m-d H:i:s', strtotime('+'.$plan_data->plan_duration. 'days')); 
                }   

                $user_subscription                  = new UserSubscription();
                $user_subscription->user_id         =  $user_id; 
                $user_subscription->subscription_id =  $plan_data->id; 
                $user_subscription->start_date      =  $plan_start_date ?? Date('Y-m-d H:i:s'); 
                $user_subscription->expire_date     =  $expire_date; 
                $user_subscription->title           =  $plan_data->title; 
                $user_subscription->description     =  $plan_data->description;
                $user_subscription->search_filters  =  $plan_data->search_filters;
                $user_subscription->like_per_day    =  $plan_data->like_per_day;
                $user_subscription->video_call      =  $plan_data->video_call;
                $user_subscription->who_like_me     =  $plan_data->who_like_me;
                $user_subscription->who_view_me     =  $plan_data->who_view_me;
                $user_subscription->undo_profile    =  $plan_data->undo_profile;
                $user_subscription->read_receipt    =  $plan_data->read_receipt;
                $user_subscription->travel_mode     =  $plan_data->travel_mode;
                $user_subscription->profile_badge   =  $plan_data->profile_badge;
                $user_subscription->price           =  $plan_data->price;   
                $user_subscription->month           =  $plan_data->month; 
                $user_subscription->plan_duration   =  $plan_data->plan_duration; 
                $user_subscription->plan_type       =  $request->plan_type ?? $plan_data->plan_type; 
                $user_subscription->google_plan_id  =  $plan_data->google_plan_id; 
                $user_subscription->order_id        =  $purchaseToken; 
                $user_subscription->save(); 

                // Notification for subscription purchase

                $title = $plan_data->title." purchased successfully";
                $message = $plan_data->title." purchased successfully"; 
                Helper::send_notification('single', 0, Auth::id(), $title, 'subscription_purchase', $message, []);
                return $this->success([],'Subscription purchased successfully');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // PURCHASE FROM APPLE 

    public function purchaseFromApple(Request $request){
        try{

            $validateData = Validator::make($request->all(), [
                'product_id' => 'required',
                'receipt_data' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $user_id = Auth::id();
            $today_date = date('Y-m-d H:i:s');
            
            // if(UserSubscription::where('user_id',$user_id)->where('expire_date','>',$today_date)->count() > 0){
            //     return $this->error('You have already purchased plan','You have already purchased plan');
            // }
            try{
                $response = \Http::post('https://sandbox.itunes.apple.com/verifyReceipt', [
                    'receipt-data' => $request->receipt_data,
                    'password' => env('APPLE_PLAY_SECRET', '85daa2e3306e4ec0902b46b1882cf2dd'),
                    'exclude-old-transactions' => true,
                ]);
                $verificationResult = $response->json();
            }catch(\Exception $e){
                return $this->error('Unable to proceed payment','Unable to proceed payment');
            } 

            if(isset($verificationResult['latest_receipt_info'])){
                $time = $verificationResult['latest_receipt_info'][0]['expires_date_ms']/1000;
                $exp = new DateTime("@$time"); 
                $plan_data = Subscription::where('apple_plan_id',$request->product_id)->first();

                $is_purchased = UserSubscription::where('user_id',$user_id)->whereDate('expire_date','>=',$today_date)->latest()->first();
                if($is_purchased !== null){
                    // IT IS CHECK IF PLAN EXIST THEN TAKE EXPIRE DATE OF THAT PLAN AND ADD ONE DAY IN THAT DATE AND CONSIDER AS START DATE OF NEW PLAN 
                    $plan_start_date = date('Y-m-d H:i:s', strtotime($is_purchased->expire_date. ' +1 days'));
                    $expire_date = date('Y-m-d H:i:s', strtotime($is_purchased->expire_date. ' +'.$plan_data->plan_duration.' days'));
                }else{
                    $expire_date = $exp->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s') ?? Date('Y-m-d H:i:s', strtotime('+'.$plan_data->plan_duration. 'days')); 
                } 

                $user_subscription                  = new UserSubscription();
                $user_subscription->user_id         =  $user_id; 
                $user_subscription->subscription_id =  $plan_data->id; 
                $user_subscription->start_date      =  $plan_start_date ?? Date('Y-m-d H:i:s'); 
                $user_subscription->expire_date     =  $expire_date; 
                $user_subscription->title           =  $plan_data->title; 
                $user_subscription->description     =  $plan_data->description;
                $user_subscription->search_filters  =  $plan_data->search_filters;
                $user_subscription->like_per_day    =  $plan_data->like_per_day;
                $user_subscription->video_call      =  $plan_data->video_call;
                $user_subscription->who_like_me     =  $plan_data->who_like_me;
                $user_subscription->who_view_me     =  $plan_data->who_view_me;
                $user_subscription->undo_profile    =  $plan_data->undo_profile;
                $user_subscription->read_receipt    =  $plan_data->read_receipt;
                $user_subscription->travel_mode     =  $plan_data->travel_mode;
                $user_subscription->profile_badge   =  $plan_data->profile_badge;
                $user_subscription->price           =  $plan_data->price;   
                $user_subscription->month           =  $plan_data->month; 
                $user_subscription->plan_duration   =  $plan_data->plan_duration; 
                $user_subscription->plan_type       =  $request->plan_type ?? $plan_data->plan_type; 
                $user_subscription->apple_plan_id   =  $plan_data->apple_plan_id; 
                $user_subscription->order_id        =  $verificationResult['latest_receipt_info'][0]['original_transaction_id']; 
                $user_subscription->save(); 
 
                // Notification for subscription purchase

                $title = $plan_data->title." purchased successfully";
                $message = $plan_data->title." purchased successfully"; 
                Helper::send_notification('single', 0, Auth::id(), $title, 'subscription_purchase', $message, []);
                return $this->success([],'Subscription purchased successfully');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // ACTIVE SUBSCRIPTION LISTING
    
    public function activeSubscriptionList(Request $request){
        try{
            $user_id = Auth::id();
            $today_date = date('Y-m-d');
            $all_purchased = UserSubscription::where('user_id',$user_id)->whereDate('expire_date','>=',$today_date)->get();
            foreach($all_purchased as $purchased){
                if($purchased->google_plan_id && $purchased->order_id){
                    Helper::googlePlanStatusCheck($purchased->google_plan_id,$purchased->order_id);
                }
                if($purchased->apple_plan_id && $purchased->order_id){
                    Helper::applePlanStatusCheck($purchased->order_id);
                }
            }
            $is_purchased = UserSubscription::where('user_id',$user_id)->whereDate('expire_date','>=',$today_date)->orderby('id','asc')->first();
            if($is_purchased != null){
                $data= $is_purchased;
            }else{
                $data= Subscription::where('plan_type','free')->first();
            }
            return $this->success($data,'Active subscription successfully');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // SUBSCRIPTION HISTORY
    
    public function subscriptionHistory(Request $request){
        try{
            $user_id = Auth::id();
            $today_date = date('Y-m-d');
            $data['active_suvscription'] = UserSubscription::where('user_id',$user_id)->whereDate('expire_date','>=',$today_date)->orderby('id','asc')->first();
            $data['subscription_list'] = UserSubscription::where('user_id',$user_id)->latest()->get();
            return $this->success($data,'Subscription history');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }


    // COIN LIST

    public function coinList(Request $request){
        try{
            $data['coin_list']  = Coin::all();
            $data['total_balance'] = UserCoin::where('receiver_id', Auth::id())
                                    ->orWhere('sender_id', Auth::id())
                                    ->whereIn('type', ['purchase_coin', 'gift_card_receive', 'purchase_plan', 'gift_card_sent'])
                                    ->selectRaw('SUM(CASE WHEN receiver_id = ? AND type IN ("purchase_coin", "gift_card_receive") THEN coins_number ELSE 0 END) 
                                                - SUM(CASE WHEN sender_id = ? AND type IN ("purchase_plan", "gift_card_sent") THEN coins_number ELSE 0 END) 
                                                AS total_balance', [Auth::id(), Auth::id()])
                                    ->value('total_balance') ?? 0;
                                    
            return $this->success($data,'Notification data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
    // COIN PURCHASE

    public function coinPurchase(Request $request){
        try{
            $validateData = Validator::make($request->all(), [
                'coin_id' => 'required',
            ]);

            if ($validateData->fails()) {
                return $this->error($validateData->errors(),'Validation error',403);
            }

            $coin_data = Coin::where('id',$request->coin_id)->first();

            $user_coin = new UserCoin();

            $user_coin->sender_id       = 0;
            $user_coin->receiver_id     = Auth::id();
            $user_coin->coin_id         = $request->coin_id;
            $user_coin->price           = $coin_data->price;
            $user_coin->coins_number    = $coin_data->coins;
            $user_coin->message         = $coin_data->coins .' Coins Purchased';
            $user_coin->type            = 'purchase_coin';
            $user_coin->action          = '+'.$coin_data->coins;
            $user_coin->save();

           
            $data['total_balance'] = UserCoin::where('receiver_id', Auth::id())
                                    ->orWhere('sender_id', Auth::id())
                                    ->whereIn('type', ['purchase_coin', 'gift_card_receive', 'purchase_plan', 'gift_card_sent'])
                                    ->selectRaw('SUM(CASE WHEN receiver_id = ? AND type IN ("purchase_coin", "gift_card_receive") THEN coins_number ELSE 0 END) 
                                                - SUM(CASE WHEN sender_id = ? AND type IN ("purchase_plan", "gift_card_sent") THEN coins_number ELSE 0 END) 
                                                AS total_balance', [Auth::id(), Auth::id()])
                                    ->value('total_balance') ?? 0;

            $title =  $coin_data->coins. " coins purchased successfully";
            $message =  $coin_data->coins. " coins purchased successfully"; 
            Helper::send_notification('single', 0, Auth::id(), $title, 'coin_purchase', $message, []);

            return $this->success($data,'Coin purchased successfully');

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // WALLET HISTORY

    public function walletHistory(Request $request){
        try{
            $authId = Auth::id();
            $walletHistory = UserCoin::where(function ($query) use ($authId) {
                                $query->where(function ($query) use ($authId) {
                                    $query->where('receiver_id', $authId)
                                        ->where('type', '!=', 'gift_card_sent');
                                })
                                ->orWhere(function ($query) use ($authId) {
                                    $query->where('sender_id', $authId)
                                        ->where('type', '!=', 'gift_card_receive');
                                });
                            })
                            ->orderBy('user_coins.id', 'desc')
                            ->paginate($request->input('perPage'), ['*'], 'page', $request->input('page'));

            $data['wallet_history'] = $walletHistory->items();
            $data['current_page'] = $walletHistory->currentPage();
            $data['per_page'] = $walletHistory->perPage();
            $data['total'] = $walletHistory->total();
            $data['last_page'] = $walletHistory->lastPage();

            $data['total_balance'] = UserCoin::where('receiver_id', Auth::id())
                                    ->orWhere('sender_id', Auth::id())
                                    ->whereIn('type', ['purchase_coin', 'gift_card_receive', 'purchase_plan', 'gift_card_sent'])
                                    ->selectRaw('SUM(CASE WHEN receiver_id = ? AND type IN ("purchase_coin", "gift_card_receive") THEN coins_number ELSE 0 END) 
                                                - SUM(CASE WHEN sender_id = ? AND type IN ("purchase_plan", "gift_card_sent") THEN coins_number ELSE 0 END) 
                                                AS total_balance', [Auth::id(), Auth::id()])
                                    ->value('total_balance') ?? 0;
                                    
            return $this->success($data,'Wallet list');

        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
    
     
    // GIFT LIST

    public function giftList(Request $request){
        try{
            $data['gift_list']     = Gift::all();
            $data['total_balance'] = UserCoin::where('receiver_id', Auth::id())
                                    ->orWhere('sender_id', Auth::id())
                                    ->whereIn('type', ['purchase_coin', 'gift_card_receive', 'purchase_plan', 'gift_card_sent'])
                                    ->selectRaw('SUM(CASE WHEN receiver_id = ? AND type IN ("purchase_coin", "gift_card_receive") THEN coins_number ELSE 0 END) 
                                                - SUM(CASE WHEN sender_id = ? AND type IN ("purchase_plan", "gift_card_sent") THEN coins_number ELSE 0 END) 
                                                AS total_balance', [Auth::id(), Auth::id()])
                                    ->value('total_balance') ?? 0;
                                    
            return $this->success($data,'Gift data');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // USER DELETE ACCOUNT
    public function deleteAccount(){
        try{
            if (Auth::user()) {
                $user_data = Auth::user();
                $user = Auth::user()->token();
                $user->revoke();

                $userPhotos = UserPhoto::where('user_id',$user_data);
                $user_old_photo_name = $userPhotos->pluck('name')->toArray();
                
                $deletedFiles = [];
                if(!empty($user_old_photo_name)){
                    foreach ($user_old_photo_name as $name) {
                        $path = public_path('user_profile/' . $name);
                        if (File::exists($path)) {
                            if (!is_writable($path)) {
                                chmod($path, 0777);
                            }
                            File::delete($path);
                            $deletedFiles[] = $path;
                        }
                    };
                }
                $userPhotos->delete();
                
                Chat::where('sender_id',$user_data->id)->orWhere('receiver_id',$user_data->id)->delete();
                Notification::where('sender_id',$user_data->id)->orWhere('receiver_id',$user_data->id)->delete();
                UserIceBreaker::where('user_id',$user_data->id)->delete();
                UserLikes::where('like_from',$user_data->id)->orWhere('like_to',$user_data->id)->delete();
                UserPhoto::where('user_id',$user_data->id)->delete();
                UserQuestion::where('user_id',$user_data->id)->delete();
                UserReport::where('reporter_id',$user_data->id)->orWhere('reported_user_id',$user_data->id)->delete();
                UserReviewLater::where('user_review_from',$user_data->id)->orWhere('user_review_to',$user_data->id)->delete();
                UserSubscription::where('user_id',$user_data->id)->delete();
                UserView::where('view_from',$user_data->id)->orWhere('view_to',$user_data->id)->delete();
                User::where('id',$user_data->id)->delete();
                return $this->success([],'Account delete successfully');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }

    // USER LOGOUT
    public function logout(){
        try{
            if (Auth::user()) {
                User::where('id',Auth::id())->update(['device_token' =>  User::find(Auth::id())->fcm_token]);
                User::where('id',Auth::id())->update(['fcm_token' => null]);
                $user = Auth::user()->token();
                $user->revoke();
                return $this->success([],'You are successfully logout');
            }
            return $this->error('Something went wrong','Something went wrong');
        }catch(Exception $e){
            return $this->error($e->getMessage(),'Exception occur');
        }
        return $this->error('Something went wrong','Something went wrong');
    }
}
