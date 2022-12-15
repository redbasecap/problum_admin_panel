<?php

namespace App\Http\Controllers\Api\V1;

use App\Content;
use App\Http\Controllers\Api\ResponseController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GuestController extends ResponseController
{

    public function login(Request $request)
    {
        $rules = [
            'username' => ['required'],
            'password' => ['required'],
            'push_token' => ['nullable'],
            'device_type' => ['required', 'in:android,ios'],
            'device_id' => ['required', 'max:255'],
        ];
        $messages = ['email.exists' => __('api.err_email_not_register')];
        $this->directValidation($rules, $messages);

        $field = 'username';
        if (filter_var($request->input('username'), FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } else {
            $field = 'username';
        }

        $user = User::where($field, $request->username)->first();
        if($user){
            if($user->status == 'inactive'){
                $this->sendError(__('api.err_account_ban'), false);
            }
        }
        else{
            $this->sendError(__('api.err_login_valid'), false);
        }

        $attempt = [$field => $request->username, 'password' => $request->password, 'type' => 'user', 'status' => 'active'];
        //$attempt = ['username' => $request->username, 'password' => $request->password, 'type' => 'user', 'status' => 'active'];
        if (Auth::attempt($attempt)) {
            $token = User::AddTokenToUser();
            $this->sendResponse(200, __('api.suc_user_login'), $this->get_user_data($token));
        } else {
            $this->sendError(__('api.err_fail_to_auth'), false);
        }
    }


    public function signup(Request $request)
    {

        $valid = $this->directValidation([
            'first_name' => ['required'],
            'last_name' => ['required'],
            'country_code' => ['required'],
            'mobile' => ['required'],
            'mobile' => ['required', 'integer', Rule::unique('users')->where('country_code', $request->country_code)->whereNull('deleted_at')],
            'password' => ['required'],
            'age' => ['required'],
            'dob' => ['required'],
            

            'push_token' => ['nullable'],
            'email' => ['required', 'email', Rule::unique('users')->whereNull('deleted_at')],
            'username' => ['required', Rule::unique('users')->whereNull('deleted_at')],
            'device_id' => ['required', 'max:255'],
            'device_type' => ['required', 'in:android,ios'],
        ], [
            'mobile.unique' => __('api.err_mobile_is_exits'),
            'email.unique' => __('api.err_email_is_exits'),
        ]);
        $user = User::create([
            'password' => $request->password,
            'email' => $request->email,
            'name' => $request->first_name.' '.$request->last_name,
            'first_name' => $request->first_name ?? '',
            'last_name' => $request->last_name ?? '',
            'country_code' => $request->country_code ?? '',
            'mobile' => $request->mobile  ?? '',
            'age' => $request->age  ?? '',
            'username' => $request->username,
            'dob' => $request->dob ?? '',
            
            'profile_image' => config('constants.default.user_image'),
        ]);
        if ($user) {
            Auth::loginUsingId($user->id);
            $token = User::AddTokenToUser();
            $this->sendResponse(200, __('api.suc_user_register'), $this->get_user_data($token));
        } else {
            $this->sendError(__('api.err_something_went_wrong'), false);
        }

    }

    public function forgot_password(Request $request)
    {

        $data = User::password_reset($request->email, false);
        $status = $data['status'] ? 200 : 412;
        $this->sendResponse($status, $data['message']);
    }

    public function content(Request $request, $type)
    {
        $data = Content::where('slug', $type)->first();
        return ($data) ? $data->content : "Invalid Content type passed";
    }

    public function check_ability(Request $request)
    {
        $otp = "";
        $type = $request->type;
        $is_sms_need = $request->is_sms_need;
        $rules = [
            'type' => ['required', 'in:username,email'],
            'value' => ['required'],
            'country_code' => ['required_if:type,mobile']
        ];
        $user_id = $request->user_id;
        if ($type == "email") {
            $rules['value'][] = 'email';
            $rules['value'][] = Rule::unique('users', 'email')->ignore($user_id)->whereNull('deleted_at');
        } elseif ($type == "username") {
            $rules['value'][] = 'regex:/^\S*$/u';
            $rules['value'][] = Rule::unique('users', 'username')->ignore($user_id)->whereNull('deleted_at');
        } else {
            $rules['value'][] = 'integer';
            $rules['value'][] = Rule::unique('users', 'mobile')->ignore($user_id)->where('country_code', $request->country_code)->whereNull('deleted_at');
        }
        $this->directValidation($rules, ['regex' => __('api.err_space_not_allowed'), 'unique' => __('api.err_field_is_taken', ['attribute' => str_replace('_', ' ', $type)])]);
        $this->sendResponse(200, __('api.succ'));
    }


    public function version_checker(Request $request)
    {
        $type = $request->type;
        $version = $request->version;
        $this->directValidation([
            'type' => ['required', 'in:android,ios'],
            'version' => 'required',
            'device_id' => ['required', 'max:255'],
            'push_token' => ['required'],
        ]);
        $data = [
            'is_force_update' => ($type == "ios") ? IOS_Force_Update : Android_Force_Update,
        ];
        DeviceToken::updateOrCreate(
            ['device_id' => $request->device_id, 'type' => $request->device_type],
            ['device_id' => $request->device_id, 'type' => $request->device_type, 'push_token' => $request->push_token, 'badge' => 0]
        );
        $check = ($type == "ios") ? (IOS_Version <= $version) : (Android_Version <= $version);
        if ($check) {
            $this->sendResponse(200, __('api.succ'), $data);
        } else {
            $this->sendResponse(412, __('api.err_new_version_is_available'), $data);
        }


    }

    public function forgot_password_new(Request $request)
    {
        $otp =   $otp = genUniqueStr('', 6,'users','email');
        $status =  412;
        $dataarr = [];
        $user = User::where('email', $request->email)->first();

        if ($user) {

            $data = User::Api_password_reset($request->email, false, $otp);

            $status = $data['status'] ? 200 : 412;
            if ($data['status']) {
                $dataarr['otp'] = $otp;
                $dataarr['user_id'] = $user->id;
            }
        } else {

            $data = ['status' => 412, 'message' => __('api.err_email_not_exits')];
        }

        $this->sendResponse($status, $data['message'],$dataarr);
    }


}
