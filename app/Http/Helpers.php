<?php

use App\DeviceToken;
use App\Language;
use App\PostMeToo;
use App\PushLog;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;

if (!function_exists('send_response')) {

    function send_response($Status, $Message = "", $ResponseData = NULL, $extraData = NULL, $null_remove = null)
    {
        $data = [];
        $valid_status = [412, 200, 401];
        if (is_array($ResponseData)) {
            $data["status"] = $Status;
            $data["message"] = $Message;
            $data["data"] = $ResponseData;
        } else if (!empty($ResponseData)) {
            $data["status"] = $Status;
            $data["message"] = $Message;
            $data["data"] = $ResponseData;
        } else {
            $data["status"] = $Status;
            $data["message"] = $Message;
            $data["data"] = new stdClass();
        }
        if (!empty($extraData) && is_array($extraData)) {
            foreach ($extraData as $key => $val) {
                $data[$key] = $val;
            }
        }
//        if ($null_remove) {
//            null_remover($data['data']);
//        }
        $header_status = in_array($data['status'], $valid_status) ? $data['status'] : 412;
        response()->json($data, $header_status)->header('Content-Type', 'application/json')->send();
        die(0);
    }
}


//function null_remover($response, $ignore = [])
//{
//    array_walk_recursive($response, function (&$item) {
//        if (is_null($item)) {
//            $item = strval($item);
//        }
//    });
//    return $response;
//}

function token_generator()
{
    return genUniqueStr('', 100, 'device_tokens', 'token', true);
}

function get_header_auth_token()
{
    $full_token = request()->header('vAuthorization');
    
    return (substr($full_token, 0, 7) === 'Bearer ') ? substr($full_token, 7) : null;
}

if (!function_exists('un_link_file')) {
    function un_link_file($image_name = "")
    {
        $pass = true;
        if (!empty($image_name)) {
            try {
                $default_url = URL::to('/');
                $get_default_images = config('constants.default');
                $file_name = str_replace($default_url, '', $image_name);
                $default_image_list = is_array($get_default_images) ? str_replace($default_url, '', array_values($get_default_images)) : [];
                if (!in_array($file_name, $default_image_list)) {
                    Storage::disk(get_constants('upload_type'))->delete($file_name);
                }
            } catch (Exception $exception) {
                $pass = $exception;
            }
        } else {
            $pass = 'Empty Field Name';
        }
        return $pass;
    }
}


function get_asset($val = "", $file_exits_check = true, $no_image_available = null)
{
    $no_image_available = ($no_image_available ?? asset(get_constants('default.no_image_available')));
    if ($val) {
        if ($file_exits_check) {
            return (file_exists(public_path($val))) ? asset($val) : $no_image_available;
        } else {
            return asset($val);
        }
    } else {
        return asset($no_image_available);
    }
} 

function print_title($title)
{
    return ucfirst($title);
}

function get_constants($name)
{
    return config('constants.' . $name);
}

function calculate_percentage($amount = 0, $discount = 0)
{
    return ($amount && $discount) ? (($amount * $discount) / 100) : 0;
}

function flash_session($name = "", $value = "")
{
    session()->flash($name, $value);
}

function success_session($value = "")
{
    session()->flash('success', ucfirst($value));
}

function error_session($value = "")
{
    session()->flash('error', ucfirst($value));
}

function getDashboardRouteName()
{
    $name = 'front.dashboard';
    $user_data = Auth::user();
    if ($user_data) {
        if (in_array($user_data->type, ["admin", "local_admin"])) {
            $name = 'admin.dashboard';
        }
    }
    return $name;
}

function admin_modules()
{
    return [
        [
            'route' => route('admin.dashboard'),
            'name' => __('Dashboard'),
            'icon' => 'kt-menu__link-icon fa fa-home',
            'child' => [],
            'all_routes' => [
                'admin.dashboard',
            ]
        ],
        [
            'route' => route('admin.user.index'),
            'name' => __('User'),
            'icon' => 'kt-menu__link-icon fas fa-users',
            'child' => [],
            'all_routes' => [
                'admin.user.index',
                'admin.user.show',
                'admin.user.add',
            ]
        ],
        [
            'route' => route('admin.category.index'),
            'name' => __('Category'),
            'icon' => 'kt-menu__link-icon fas fa-users',
            'child' => [],
            'all_routes' => [
                'admin.category.index',
                'admin.category.show',
                'admin.category.add',
                'admin.category.create',
            ]
        ],
        [
            'route' => route('admin.category.user_categroy'),
            'name' => __('Suggested Category'),
            'icon' => 'kt-menu__link-icon fas fa-users',
            'child' => [],
            'all_routes' => [
                'admin.category.category.user_categroy',
                
            ]
        ],
        [
            'route' => route('admin.post.index'),
            'name' => __('Problum Management'),
            'icon' => 'kt-menu__link-icon fas fa-users',
            'child' => [],
            'all_routes' => [
                'admin.post.index',
                'admin.post.show',
                'admin.post.add',
            ]
        ],
      
        [
            'route' => 'javascript:;',
            'name' => __('General Settings'),
            'icon' => 'kt-menu__link-icon fa fa-home',
            'all_routes' => [
                'admin.get_update_password',
                'admin.get_site_settings',
            ],
            'child' => [
                [
                    'route' => route('admin.get_update_password'),
                    'name' => 'Change Password',
                    'icon' => '',
                    'all_routes' => [
                        'admin.get_update_password',
                    ],
                ],
                [
                    'route' => route('admin.get_site_settings'),
                    'name' => 'Site Settings',
                    'icon' => '',
                    'all_routes' => [
                        'admin.get_site_settings',
                    ],
                ]
            ],
        ],
        [
            'route' => route('front.logout'),
            'name' => __('logout'),
            'icon' => 'kt-menu__link-icon fas fa-sign-out-alt',
            'child' => [],
            'all_routes' => [],
        ],
    ];
}


function get_error_html($error)
{
    $content = "";
    if ($error->any() !== null && $error->any()) {
        foreach ($error->all() as $value) {
            $content .= '<div class="alert alert-danger alert-dismissible mb-1" role="alert">';
            $content .= '<div class="alert-text">' . $value . '</div>';
            $content .= '<div class="alert-close"><i class="flaticon2-cross kt-icon-sm" data-dismiss="alert"></i></div></div>';
        }
    }
    return $content;
}


function breadcrumb($aBradcrumb = array())
{
    $i = 0;
    $content = '';
    $is_login = Auth::user();
    if ($is_login) {
        if ($is_login->type == "admin") {
            $aBradcrumb = array_merge(['Home' => route('admin.dashboard')], $aBradcrumb);
        } elseif ($is_login->type == "vendor") {
            $aBradcrumb = array_merge(['Home' => route('vendor.dashboard')], $aBradcrumb);
        }
    }
    if (is_array($aBradcrumb) && count($aBradcrumb) > 0) {
        $total_bread_crumbs = count($aBradcrumb);
        foreach ($aBradcrumb as $key => $link) {
            $i += 1;
            $link = (!empty($link)) ? $link : 'javascript:void(0)';

            $content .=  '<li class="breadcrumb-item"> <a href="'.$link.'">'. ucfirst($key).'</a>';

           
            // $content .= "<a href='" . $link . "' class='kt-subheader__breadcrumbs-link'>" . ucfirst($key) . "</a>";
            // if ($total_bread_crumbs != $i) {
            //     $content .= "<span class='kt-subheader__breadcrumbs-separator'></span>";
            // }
        }
    }
    return $content;
}

function success_error_view_generator()
{
    $content = "";
    if (session()->has('error')) {
        $content = '<div class="alert alert-danger alert-dismissible" role="alert">
                                        <div class="alert-text">' . session('error') . '</div>
                                        <div class="alert-close"><i class="flaticon2-cross kt-icon-sm"
                                                                    data-dismiss="alert"></i></div></div>';
    } elseif (session()->has('success')) {
        $content = '<div class="alert alert-success alert-dismissible" role="alert">
                                        <div class="alert-text">' . session('success') . '</div>
                                        <div class="alert-close"><i class="flaticon2-cross kt-icon-sm"
                                                                    data-dismiss="alert"></i></div></div>';
    }
    return $content;
}

function datatable_filters()
{
    $post = request()->all();
    return array(
        'offset' => isset($post['start']) ? intval($post['start']) : 0,
        'limit' => isset($post['length']) ? intval($post['length']) : 25,
        'sort' => isset($post['columns'][(isset($post["order"][0]['column'])) ? $post["order"][0]['column'] : 0]['data']) ? $post['columns'][(isset($post["order"][0]['column'])) ? $post["order"][0]['column'] : 0]['data'] : 'created_at',
        'order' => isset($post["order"][0]['dir']) ? $post["order"][0]['dir'] : 'DESC',
        'search' => isset($post["search"]['value']) ? $post["search"]['value'] : '',
        'sEcho' => isset($post['sEcho']) ? $post['sEcho'] : 1,
    );
}

function send_push($user_id = 0, $data = [], $notification_entry = false, $is_driver = false,$isadmin=0)
{
    
    $language = App::getLocale();
    if($isadmin == 1 ){
        $language = 'en';
    }
    

    $main_name = 'Problum';
    if (\Illuminate\Support\Facades\Auth::check()) {
        $user_data = \Illuminate\Support\Facades\Auth::user();
        if (!empty($user_data) && $user_data['user_type'] !== "admin") {
            $main_name = isset($data['username']) ? $data['username'] : (isset($user_data->username) ? $user_data->username : $main_name);
        }
    }
    $push_data = [
        'user_id' => $user_id,
        'from_user_id' => isset($data['from_user_id']) ? $data['from_user_id'] : 1,
        'sound' => 'defualt',
        'badge' => '1',
        'push_type' => isset($data['push_type']) ? $data['push_type'] : 0,
        'push_title' => isset($data['push_title']) ? $data['push_title'] : $main_name,
        'push_message' => isset($data['push_message']) ? $data['push_message'] : "",
        //        'push_from' => isset($data['push_from']) ? $data['push_from'] : $main_name,
        'object_id' => isset($data['object_id']) ? $data['object_id'] : 0,
        'main_object_id' => isset($data['main_object_id']) ? $data['main_object_id'] : 0,
        'country_id' => isset($data['country_id']) ? $data['country_id'] : 0,
        'title' => isset($data['push_title']) ? $data['push_title'] : 0,
        'body' => isset($data['body']) ? $data['body'] : 0,
    ];
   
    //    if (!empty($user_data)) {
    $push_data['push_message'] = (strpos($push_data['push_message'], ':user') !== false) ? __($push_data['push_message'], ['user' => $main_name]) : $push_data['push_message'];
    if (isset($data['extra']) && !empty($data['extra'])) {
        $push_data = array_merge($push_data, $data['extra']);
    }
    if ($push_data['user_id'] !== $push_data['from_user_id']) {
        $to_user_data = \App\User::find($user_id);

        if ($to_user_data) {
            if (1) {
                $push_status = "Sent";
                
                $get_user_tokens = \App\DeviceToken::get_user_tokens($user_id);
                    
                $headers = array("Authorization: key=" . config('constants.firebase_server_key'), "Content-Type: application/json");
                if (count($get_user_tokens)) {
                    foreach ($get_user_tokens as $key => $value) {
                        $result = '';
                        $device_type = strtolower($value['type']);

                        if ($device_type == "ios") {
                            $token = $value['push_token'];
                            $pem_secret = "";
                            $bundle_id = ($is_driver) ? 'com.app.MotoTPDriver' : 'com.app.MotoTPCustomer';
                            $pem_file = ($is_driver) ? storage_path('push_cert/MotoTPDeriver.pem') : storage_path('push_cert/MotoTPCustomer.pem');
                            $url = "https://api.sandbox.push.apple.com/3/device/" . $token;
                            $ch = curl_init($url);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                                'aps' => [
                                    'badge' => +1,
                                    'alert' => $push_data['push_message'],
                                    'sound' => 'default',
                                    'push_type' => $push_data['push_type'],
                                    "mutable-content" => "1",
                                ],
                                'payload' => [
                                    'to' => $token,
                                    'notification' => ['title' => $push_data['push_title'], 'body' => $push_data['push_message'], "sound" => "default", "badge" => 1],
                                    'data' => $push_data,
                                    'priority' => 'high'
                                ]
                            ]));
                            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array("apns-topic: $bundle_id"));
                            curl_setopt($ch, CURLOPT_SSLCERT, $pem_file);
                            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $pem_secret);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                            $response = curl_exec($ch);

                            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                            if ($httpcode == 200) {
                                if (config('constants.push_log')) {
                                    PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "Push Sent", json_encode($push_data), $response);
                                }
                            } else {
                                if (config('constants.push_log')) {
                                    PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], $response, json_encode($push_data), $response);
                                }
                            }
                        } else {
                            
                            $ch = curl_init();
                            $payload_data = [
                                'to' => $value['push_token'],
                                'data' => $push_data,
                                'notification' => $push_data,
                                'priority' => 'high'
                            ];
                            curl_setopt_array($ch, array(
                                CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
                                CURLOPT_RETURNTRANSFER => 1,
                                CURLOPT_POSTFIELDS => json_encode($payload_data),
                                CURLOPT_POST => 1,
                                CURLOPT_HTTPHEADER => $headers,
                            ));
                            $result = curl_exec($ch);
                            if (curl_errno($ch)) {
                                $push_status = 'Curl Error:' . curl_error($ch);
                            }

                            curl_close($ch);
                            if (config('constants.push_log')) {
                                \App\PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], $push_status, json_encode($push_data), $result);
                            }
                        }
                    }
                } else {
                    if (config('constants.push_log')) {
                        \App\PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "Token Is Empty");
                    }
                }
            } else {
                if (config('constants.push_log')) {
                    \App\PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "notification is off or country is diff");
                }
            }
         
            if ($notification_entry) {
                \App\Notification::create([
                    'push_type' => $push_data['push_type'],
                    'user_id' => $push_data['user_id'],
                    'language_unique_name_main' => $language,
                    'from_user_id' => $push_data['from_user_id'],
                    'push_title' => $push_data['push_title'],
                    'push_message' => $push_data['push_message'],
                    'object_id' => $push_data['object_id'],
                    'main_object_id' => isset($push_data['main_object_id']) ? $push_data['main_object_id'] : 0,
                    'extra' => (isset($data['extra']) && !empty($data['extra'])) ? json_encode($data['extra']) : json_encode([]),
                    'country_id' => $push_data['country_id'],
                ]);
            }
        }
    } else {
        if (config('constants.push_log')) {
            \App\PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "User Cant Sent Push To Own Profile.");
        }
    }
}

function number_to_dec($number = "", $show_number = 2, $separated = '.', $thousand_separator = "")
{
    return number_format($number, $show_number, $separated, $thousand_separator);
}

function genUniqueStr($prefix = '', $length = 10, $table, $field, $isAlphaNum = false)
{
    $arr = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
    if ($isAlphaNum) {
        $arr = array_merge($arr, array(
            'a', 'b', 'c', 'd', 'e', 'f',
            'g', 'h', 'i', 'j', 'k', 'l',
            'm', 'n', 'o', 'p', 'r', 's',
            't', 'u', 'v', 'x', 'y', 'z',
            'A', 'B', 'C', 'D', 'E', 'F',
            'G', 'H', 'I', 'J', 'K', 'L',
            'M', 'N', 'O', 'P', 'R', 'S',
            'T', 'U', 'V', 'X', 'Y', 'Z'));
    }
    $token = $prefix;
    $maxLen = max(($length - strlen($prefix)), 0);
    for ($i = 0; $i < $maxLen; $i++) {
        $index = rand(0, count($arr) - 1);
        $token .= $arr[$index];
    }
    if (isTokenExist($token, $table, $field)) {
        return genUniqueStr($prefix, $length, $table, $field, $isAlphaNum);
    } else {
        return $token;
    }
}

function isTokenExist($token, $table, $field)
{
    if ($token != '') {
        $isExist = DB::table($table)->where($field, $token)->count();
        if ($isExist > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function get_fancy_box_html($path = "", $class = "img_75")
{
    return '<a data-fancybox="gallery" href="' . $path . '"><img class="' . $class . '" src="' . $path . '" alt="image" width=40 height=40></a>';
}

function general_date($date)
{
    return date('Y-m-d', strtotime($date));
}

function current_route_is_same($name = "")
{
    return $name == request()->route()->getName();
}

function is_selected_blade($id = 0, $id2 = "")
{
    return ($id == $id2) ? "selected" : "";
}

function clean_number($number)
{
    return preg_replace('/[^0-9]/', '', $number);
}

function print_query($builder)
{
    $addSlashes = str_replace('?', "'?'", $builder->toSql());
    return vsprintf(str_replace('?', '%s', $addSlashes), $builder->getBindings());
}

function user_status($status = "", $is_delete_at = false)
{
    if ($is_delete_at) {
        $status = "<span class='badge badge-danger'>Deleted</span>";
    } elseif ($status == "inactive") {
        $status = "<span class='badge badge-warning'>" . ucfirst($status) . "</span>";
    } else {
        $status = "<span class='badge badge-success'>" . ucfirst($status) . "</span>";
    }
    return $status;
}


function is_active_module($names = [])
{
    $current_route = request()->route()->getName();
    return in_array($current_route, $names) ? "mm-active" : "";
}

function echo_extra_for_site_setting($extra = "")
{
    $string = "";
    $extra = json_decode($extra);
    if (!empty($extra) && (is_array($extra) || is_object($extra))) {
        foreach ($extra as $key => $item) {
            $string .= $key . '="' . $item . '" ';
        }
    }
    return $string;
}

function upload_file($file_name = "", $path = null)
{
    $file = "";
    $request = \request();
    if ($request->hasFile($file_name) && $path) {
        $path = config('constants.upload_paths.' . $path);
        $file = $request->file($file_name)->store($path, config('constants.upload_type'));
    } else {
        echo 'Provide Valid Const from web controller';
        die();
    }
    return $file;
}

function upload_base_64_img($base64 = "", $path = "uploads/product/")
{
    $file = null;
    if (preg_match('/^data:image\/(\w+);base64,/', $base64)) {
        $data = substr($base64, strpos($base64, ',') + 1);
        $up_file = rtrim($path, '/') . '/' . md5(uniqid()) . '.png';
        $img = Storage::disk('local')->put($up_file, base64_decode($data));
        if ($img) {
            $file = $up_file;
        }
    }
    return $file;
}
function checkMeToo($user,$postid){
    $metToo = PostMeToo::where('user_id',$user)->where('post_id',$postid)->first();
    if($metToo){
        return 1;
    }
    return 0;
}


function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function get_all_languages()
{
    return Language::all();
}