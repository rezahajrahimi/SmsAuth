<?php

namespace App\Http\Controllers;
use App\Models\User;
use Carbon\Carbon;
use Amirbagh75\SMSIR\SmsIRClient;
use Throwable;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function authFailed()
    {
        return response('unauthenticated', 401);
    }
    private function getResponse(User $user)
    {

        $tokenResult =   $user->createToken("Personal Access Token");
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(2);
        $token->save();


        return  response([
            'accessToken' => $tokenResult->accessToken,
            'tokenType' => "Bearer",
            'expiresAt' => Carbon::parse($token->expires_at)->toDateTimeString()
        ], 200);
    }
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'min:11', 'max:11', 'regex:/^09[0-9]{9}$/', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $verification_code = rand(1000001, 9999999);


        $user = User::create([
            'name' => $request['name'],
            'phone' => $request['phone'],
            'password' => Hash::make($request['password']),
            'verification_code' => $verification_code,
        ]);
        if ($user) {

            try {
                $this->sendSms($verification_code, $request->phone);
                return response('حساب کاربری ایجاد شد - نیاز به فعال سازی دارد', 200);
            } catch (Throwable $e) {
                return response("خطا در ارسال پین کد", 422);
            }
        } else return response(['errors' => $user->errors()], 422);
    }
    public function getVerify()
    {
        $phone = \request()->phone;
        $user = User::where('phone', $phone)->first();
        if ($user)
            if ($user->status == false)
                return response('حساب کاربری فعال نشده است', 200);
            else
                return response("حساب کاربری شما قبلا فعال شده است", 422);
        else
            return response("خطایی به وجود آمده است - چنین کاربری در سیستم وجود ندارد", 422);
    }

    public function doVerify(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|integer'
        ], [
            'code.required' => 'فیلد کد ارسالی الزامی می باشد',
            'code.integer' => 'فرمت فیلد کد ارسالی نادرست می باشد'
        ]);

        $user = User::where('verification_code', $request->code)->first();
        if ($user) {
            $user->status = true;
            $user->verification_code = null;
            $user->save();
            return $this->getResponse($user);
        } else
            return response("کد وارد شده نادرست می باشد", 422);
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'min:11', 'max:11', 'regex:/^09[0-9]{9}$/'],
            'password' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $credentials = \request(['phone', 'password']);

        if (Auth::attempt($credentials)) {
            $user = $request->user();
            return  $this->getResponse($user);
        }
    }
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response('Successfully logged out', 200);
    }

    public function user(Request $request)
    {
        return $request->user();
    }
    public function sendResetPasswordPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:11|max:11|regex:/^09[0-9]{9}$/'
        ]);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $phone = $request->input('phone');
        $user = User::where('phone', $phone)->first();
        if ($user) {
            if ($user->status == true) {
                $newPassword = rand(1000001, 9999999);
                $this->sendSms($newPassword, $request->phone);
                $user->password = $newPassword;
                $user->update();
            } else {
                return response('حساب شما فعال نمی باشد', 422);

            }
            return response("لینک فعال سازی به تلفن همراه ارسال شد",200);
        } else {
            return response('چنین کاربری با شماره وارد شده یافت نشد', 422);
        }
    }

    public function sendSms(String $code, String $phone)
    {
        $apiKey = 'Your Api Key ...';
        $secretKey = 'Your Secret Key ...';
        $lineNumber = 'Service line Number';
        $smsir = new SmsIRClient($apiKey, $secretKey, $lineNumber);
        return $smsir->ultraFastSend(['token name' => $code], "templete ID", $phone);
    }
}
