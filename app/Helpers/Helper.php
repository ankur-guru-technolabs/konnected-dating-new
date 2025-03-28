<?php
  
namespace App\Helpers;
use Mail;
use App\Mail\EmailVerificationMail;
use App\Mail\SubscriptionExpireMail;
use App\Models\Notification;
use App\Models\OtpCount;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\GooglePlayService;
use Twilio\Rest\Client;
use Google\Client as GoogleClient;
use Auth; 

class Helper {

    /**
     * Write code on Method
     *
     * @return response()
     */
    public static function sendMail($view = '', $data = [], $to = '', $from = '', $attechMent = '')
    {
        if(empty($view) || empty($to)) {
            return false;
        }

        $subject = isset($data['subject']) ? $data['subject'] : '';
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <info@konnected-dating.com>' . "\r\n";

        // For sending otp to mail
        
        if(isset($data['otp'])){
            $otp = $data['otp'] ;
            Mail::to($data['email'])->send(new EmailVerificationMail($otp));
        }elseif (isset($data['subscription_expire'])) {
            Mail::to($data['email'])->send(new SubscriptionExpireMail($data));
        }
        return true;

    }

    public static function sendOtp($number,$otp)
    {
        if ($number == '') {
            return false;
        }
        $account_sid = env('TWILIO_SID', 'AC5ba9320b675dc1701dd32ef717784cb6');
        $auth_token = env('TWILIO_AUTH_TOKEN', '48b6d7d9ad34b2ebb9d55e846812e6b5');
        $twilio_number = env('TWILIO_NUMBER', '+18667904843');
        $message = "Your konnected otp is ". $otp;
        $client = new Client($account_sid, $auth_token);
        $client->messages->create($number,['from' => $twilio_number, 'body' => $message] );

        $otpRecord = OtpCount::where('phone_number', $number)->where('date', date('Y-m-d'))->first();
         
        if ($otpRecord) {
            $otpRecord->update([
                'otp' => $otp,
                'count' => $otpRecord->count + 1,
                'date' => date('Y-m-d'),
            ]);
        } else {
            OtpCount::create([
                'phone_number' => $number,
                'otp' => $otp,
                'count' => 1,
                'date' => date('Y-m-d'),
            ]);
        }
        return true;
    }

    public static function getAccessToken($serviceAccountPath) {
        $client = new GoogleClient();
        $client->setAuthConfig($serviceAccountPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();
        $token = $client->fetchAccessTokenWithAssertion();
        return $token['access_token'];
    }

    public static function sendMessage($accessToken, $projectId, $message) {
        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
        $headers = [
         'Authorization: Bearer ' . $accessToken,
         'Content-Type: application/json',
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['message' => $message]));
        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);
        return json_decode($response, true);
    }

    public static function send_notification($notification_id, $sender_id = '', $receiver_id = '', $title = '', $type = '', $message = '', $custom = [])
    {
        $receiver_data = User::where('id', $receiver_id)->first();

        if ($notification_id == 'single') {
            $notification_id = [$receiver_data->fcm_token];
        }
       
        // This will give old badges count which is already stored...

        $badge = Notification::where('receiver_id', $receiver_id)->where(function ($query) {
            $query->where('type', 'message');
            $query->orWhere('type', 'video_call');
        })->where('status', 0)->count();

        // If new arriving notification is also for message,video_call then need to add +1 in old count

        if ($type == 'message' || $type == 'video_call') {
            $badge = $badge + 1;
        }

        if (isset($custom['image'])) {
            $image = $custom['image'];
        } else {
            $image = asset('images/konnected-dating.png');
        }

        $messages = $message;
        if (!empty($receiver_data) && $receiver_data->is_notification_mute == 0 && $receiver_data->fcm_token != '') {
            $accesstoken = env('FCM_KEY');
 
            $message = [
                "token" => $notification_id[0],
                'notification' => [
                    "title" => $title,
                   // "body" => $messages,  
                    "image" => $image,
                ],
                'data' => [
                    "title" => $title,
                   // "body" => $messages,  
                    "type" => $type,
                    "sender_id" => (string)$sender_id,
                    "receiver_id" => (string)$receiver_id,
                    "custom" => !empty($custom) ? json_encode($custom) : null,
                    "image" => $image,
                    "badge" => (string)$badge,
                ],
            ];

            $serviceAccountPath = '../konnected-35755-firebase-adminsdk-ait1g-eced09525e.json';
            $projectId = 'konnected-35755';

            $accessToken = Helper::getAccessToken($serviceAccountPath);
            if(!empty($accessToken) && $accessToken !== ''){
                $response = Helper::sendMessage($accessToken, $projectId, $message);
            };
        }
        if ($type !== 'message' && $type !== 'video_call') {
            $input['sender_id']     = $sender_id;
            $input['receiver_id']   = $receiver_id;
            $input['title']         = $title;
            $input['type']          = $type;
            $input['message']       = $messages;
            $input['status']        = 0;
            $input['data']          = json_encode($custom);
    
            $notification_data      = Notification::create($input);
        }
        return true;
    }

     public static function send_notification_by_admin($title = '',  $message = '', $custom = [])
    {
        $receivers = User::where('is_notification_mute', 0)
                    ->where('status',1)
                    ->whereNotNull('fcm_token')
                    ->get();

        $registration_ids = $receivers->pluck('fcm_token')->toArray();
        $receiverIds = $receivers->pluck('id')->toArray();
        
        if (empty($registration_ids)) {
            return false;  
        }

        if (isset($custom['image'])) {
            $image = $custom['image'];
        } else {
            $image = asset('images/konnected-dating.png');
        }

        $messages = $message;

        $accesstoken = env('FCM_KEY');
        $serviceAccountPath = '../konnected-35755-firebase-adminsdk-ait1g-eced09525e.json';
        $projectId = 'konnected-35755';

        $accessToken = Helper::getAccessToken($serviceAccountPath);

        if(!empty($accessToken) && $accessToken !== ''){ 
            foreach ($registration_ids as $token) {
                $message = [
                    "token" => $token,
                    'notification' => [
                        "title" => $title,
                        "body" => $messages,
                        "image" => $image
                    ],
                    'data' => [
                        "title" => $title,
                        "body" => $messages,
                        "type" => 'admin_notificaion',
                        "sender_id" => '1', // Convert to string
                        "receiver_id" => '0', // Convert to string
                        "custom" => !empty($custom) ? json_encode($custom) : null,
                        "image" => $image,
                        "badge" => (string)0, 
                    ],
                ];
    
                $response = Helper::sendMessage($accessToken, $projectId, $message);
            }
        };
        $commonNotificationData = [
            'sender_id' => 1,
            'title' => $title,
            'type' => 'admin_notificaion',
            'message' => $messages,
            'status' => 0,
            'data' => json_encode($custom),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $notificationData = array_map(function ($receiverId) use ($commonNotificationData) {
            return array_merge(['receiver_id' => $receiverId], $commonNotificationData);
        }, $receiverIds);

        Notification::insert($notificationData);
        
        return true;
    }

    public static function send_notificationOld($notification_id, $sender_id = '', $receiver_id = '', $title = '', $type = '', $message = '', $custom = [])
    {
        $receiver_data = User::where('id', $receiver_id)->first();

        if ($notification_id == 'single') {
            $notification_id = [$receiver_data->fcm_token];
        }
        // This will give old badges count which is already stored...

        $badge = Notification::where('receiver_id', $receiver_id)->where(function ($query) {
            $query->where('type', 'message');
            $query->orWhere('type', 'video_call');
        })->where('status', 0)->count();

        // If new arriving notification is also for message,video_call then need to add +1 in old count

        if ($type == 'message' || $type == 'video_call') {
            $badge = $badge + 1;
        }

        if (isset($custom['image'])) {
            $image = $custom['image'];
        } else {
            $image = asset('images/konnected-dating.png');
        }

        if (!empty($receiver_data) && $receiver_data->is_notification_mute == 0 && $receiver_data->fcm_token != '') {
            $accesstoken = env('FCM_KEY');

            $data = [
                "registration_ids" => $notification_id,
                "notification" => [
                    "title" => $title,
                    // "body" => $message,  
                    "type" => $type,
                    "sender_id" => $sender_id,
                    "receiver_id" => $receiver_id,
                    "custom" => !empty($custom) ? json_encode($custom) : null,
                    "image" => $image,
                    "badge" => $badge,
                ],
                "data" => [
                    "title" => $title,
                    // "body" => $message,  
                    "type" => $type,
                    "sender_id" => $sender_id,
                    "receiver_id" => $receiver_id,
                    "custom" => !empty($custom) ? json_encode($custom) : null,
                    "image" => $image,
                    "badge" => $badge,
                ],
            ];
            $dataString = json_encode($data);

            $headers = [
                'Authorization:key=' . $accesstoken,
                'Content-Type: application/json',
            ];

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);
        }

        $input['sender_id']     = $sender_id;
        $input['receiver_id']   = $receiver_id;
        $input['title']         = $title;
        $input['type']          = $type;
        $input['message']       = $message;
        $input['status']        = 0;
        $input['data']          = json_encode($custom);
        $notification_data      = Notification::create($input);
        return true;
    }

    public static function send_notification_by_adminOld($title = '',  $message = '', $custom = [])
    {
        // Fetch all users in chunks
        User::chunk(1000, function ($users) use ($title, $message,$custom) {
            $accesstoken = env('FCM_KEY');
            $image = isset($custom['image']) ? $custom['image'] : asset('images/meet-now.png');

            $data = [
                "registration_ids" => [],
                "notification" => [
                    "title" => $title,
                    "type" => 'admin_notificaion',
                    "sender_id" => 1,
                    "custom" => json_encode($custom),
                    "image" => $image,
                    "badge" => 0, // Initialize the badge count as 0
                ],
                "data" => [
                    "title" => $title,
                    "type" => 'admin_notificaion',
                    "sender_id" => 1,
                    "custom" => json_encode($custom),
                    "image" => $image,
                    "badge" => 0, // Initialize the badge count as 0
                ],
            ];

            foreach ($users as $user) {
                $receiver_id = $user->id;

                // Update the receiver_id and other notification data dynamically for each user
                $badge = Notification::where('receiver_id', $receiver_id)
                    ->whereIn('type', ['message', 'video_call'])
                    ->where('status', 0)
                    ->count();

                if (!empty($user->is_notification_mute) && $user->is_notification_mute == 0 && !empty($user->fcm_token)) {
                    $data['registration_ids'][] = $user->fcm_token;
                }

                // Update the badge count dynamically for each user
                $data['notification']['badge'] += $badge;
                $data['data']['badge'] += $badge;

                // Save the notification data for each user
                $input = [
                    'sender_id' => 1,
                    'receiver_id' => $receiver_id,
                    'title' => $title,
                    'type' => 'admin_notificaion',
                    'message' => $message,
                    'status' => 0,
                    'data' => json_encode($custom),
                ];
                $notification_data = Notification::create($input);
            }

            // Send the notifications in batches to FCM
            if (!empty($data['registration_ids'])) {
                $dataString = json_encode($data);
                $headers = [
                    'Authorization: key=' . $accesstoken,
                    'Content-Type: application/json',
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
                $response = curl_exec($ch);
            }
        });

        return true;
    }

    public static function googlePlanStatusCheck($productId,$purchaseToken){
        $googlePlayService = new GooglePlayService();
         
        $result = $googlePlayService->verifyPurchase($productId, $purchaseToken);
        if($result->orderId){ 
            $time = $result->expiryTimeMillis/1000;
            $exp = new \DateTime("@$time"); 
            $date = $exp->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s') ?? Date('Y-m-d H:i:s', strtotime('+'.$plan_data->plan_duration. 'days')); 
            $is_purchased = UserSubscription::where('user_id',Auth::id())->update(['expire_date' => $date]);
        }
        return true;
    }

    public static function applePlanStatusCheck($apple_order_id){
        $fileFullPath = base_path('AuthKey_U6GN885678.p8'); // DOWNLOADED FROM APP STORE CONNECT API https://appstoreconnect.apple.com/access/api
        $privateKey = '';
        if (file_exists($fileFullPath)) {
            $privateKey = file_get_contents($fileFullPath);
        } 
        $header = [
            'alg' => 'ES256',
            'kid' => env('APPLE_PLAY_KEY', 'U6GN885678'), // GET FROM APP STORE CONNECT API https://appstoreconnect.apple.com/access/api
            'typ' => 'JWT',
        ];
        $payload = [
            'iss' => env('APPLE_ISSUER_ID', '1ad6c991-e1c9-4403-b921-134292957166'), // GET FROM APP STORE CONNECT API https://appstoreconnect.apple.com/access/api
            'iat' => (int)time(),
            'exp' => (int)(time() + 3600),  
            "aud"=> "appstoreconnect-v1", // STATIC VALUE 
            "bid"=> "com.konnectedlives.app", // APP BUNDLE ID
        ];

        $jwtToken = \Firebase\JWT\JWT::encode($payload, $privateKey, 'ES256', null, $header);
        $response = \Http::withHeaders([
            'Authorization' => 'Bearer ' . $jwtToken,
        ])
        ->get('https://api.storekit-sandbox.itunes.apple.com/inApps/v1/subscriptions/'.$apple_order_id);
        $statusData = $response->json();
        
        if ($status = $statusData['data'][0]['lastTransactions'][0]['status'] ?? null) {
            $user_id = Auth::id();
            if($status == 1 || $status == 2 || $status == 3 || $status == 4 || $status == 5){
                $encodedTransactionInfo = $statusData['data'][0]['lastTransactions'][0]['signedTransactionInfo'];
                list($header, $payload, $signature) = explode (".", $encodedTransactionInfo);
                $decode_data = json_decode(base64_decode($payload), true);
                $time = $decode_data['expiresDate']/1000;
                $exp = new \DateTime("@$time"); 
                $expiryDate = $exp->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');

                $is_purchased = UserSubscription::where('user_id',$user_id)->update(['expire_date' => $expiryDate]);
            }
        } 
        return true;
    }
}