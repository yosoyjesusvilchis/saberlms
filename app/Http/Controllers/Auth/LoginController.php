<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordMail;
use App\Models\Student;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Traits\General;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use IvanoMatteo\LaravelDeviceTracking\Models\Device;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Cookie;

class LoginController extends Controller
{
    use General;
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function showLoginForm()
    {
        Cookie::queue(Cookie::forget('_uuid_d'));
        $data['pageTitle'] = __('Login');
        $data['title'] = __('Login');
        return view('auth.login', $data);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $field = 'email';
        if (filter_var($request->input('email'), FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } elseif (is_numeric($request->input('email'))) {
            $field = 'mobile_number';
        }

        $request->merge([$field => $request->input('email')]);

        $credentials = $request->only($field, 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->role == USER_ROLE_STUDENT && $user->student->status == STATUS_REJECTED) {
                Auth::logout();
                $this->showToastrMessage('error', __('Your account has been blocked!'));
                return redirect("login");
            }

            if ($user->role == USER_ROLE_STUDENT && $user->student->status == STATUS_PENDING) {
                Auth::logout();
                $this->showToastrMessage('warning', 'Your account has been in pending status. Please wait until approval.');
                return redirect("login");
            }

            if ($user->role == USER_ROLE_INSTRUCTOR && $user->student->status == STATUS_REJECTED && $user->instructor->status == STATUS_REJECTED) {
                Auth::logout();
                $this->showToastrMessage('error', __('Your account has been blocked!'));
                return redirect("login");
            }

            if (get_option('registration_email_verification') == 1) {
                if (!$user->hasVerifiedEmail()) {
                    Auth::logout();
                    $this->showToastrMessage('error', __('Your email is not verified!'));
                    return redirect("login");
                }
            }

            // ✅ Redirección según el rol
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            } elseif ($user->hasRole('organization')) {
                return redirect()->route('organization.dashboard');
            } elseif ($user->hasRole('instructor')) {
                return redirect()->route('instructor.dashboard');
            } elseif ($user->hasRole('student')) {
                return redirect()->route('student.dashboard');
            } elseif ($user->hasRole('affiliate')) {
                return redirect()->route('affiliate.dashboard');
            }

            return redirect()->route('home');
        }

        $this->showToastrMessage('error', __('Ops! You have entered invalid credentials'));
        return redirect("login");
    }

    // Google login
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->user();
        $this->_registerOrLoginUser($user);
        return redirect()->route('main.index');
    }

    // Facebook login
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        $user = Socialite::driver('facebook')->user();
        $this->_registerOrLoginUser($user);
        return redirect()->route('main.index');
    }

    // Twitter login
    public function redirectToTwitter()
    {
        return Socialite::driver('twitter')->redirect();
    }

    public function handleTwitterCallback()
    {
        $user = Socialite::driver('twitter')->user();
        $this->_registerOrLoginUser($user);
        return redirect()->route('main.index');
    }

    protected function _registerOrLoginUser($data)
    {
        $user = User::where('email', '=', $data->email)->first();

        if (!$user) {
            $user = new User();
            $user->name = $data->name;
            $user->email = $data->email;
            $user->provider_id = $data->id;
            $user->avatar = $data->avatar;
            $user->role = 3;
            $user->email_verified_at = now();
            $user->save();

            $full = $data->name;
            $full1 = explode(' ', $full);
            $first = $full1[0];
            $rest = ltrim($full, $first . ' ');

            $student  = new Student();
            $student->user_id = $user->id;
            $student->first_name = $first;
            $student->last_name = $rest;
            $student->status = get_option('private_mode') ? STATUS_PENDING : STATUS_ACCEPTED;
            $student->save();
        } else {
            $student = $user->student;
        }

        if ($student->status != STATUS_PENDING) {
            Auth::login($user);
        }
    }

    public function forgetPassword()
    {
        $data = array();
        $data['title'] = __("Forget Password");
        return view('auth.forgot', $data);
    }

    public function forgetPasswordEmail(Request $request)
    {
        $email = $request->email;
        $user = User::whereEmail($email)->first();

        if ($user) {
            $verification_code = rand(10000, 99999);
            if ($verification_code) {
                $user->forgot_token = $verification_code;
                $user->save();
            }

            try {
                Mail::to($user->email)->send(new ForgotPasswordMail($user, $verification_code));
            } catch (\Exception $exception) {
                toastrMessage('error', 'Something is wrong. Try after few minutes!');
                return redirect()->back();
            }

            Session::put('email', $email);
            Session::put('verification_code', $verification_code);

            $this->showToastrMessage('success', __('Verification code sent your email. Please check.'));
            return redirect()->route('reset-password');
        }

        $this->showToastrMessage('error', __('Your Email is incorrect!'));
        return redirect()->back();
    }

    public function resetPassword()
    {
        $data['title'] = __("Reset Password");
        return view('auth.reset-password', $data);
    }

    public function resetPasswordCheck(Request $request)
    {
        $request->validate([
            'verification_code' => 'required',
        ]);

        $email = Session::get('email');
        $verification_code = Session::get('verification_code');

        if ($request->verification_code == $verification_code) {
            $user = User::whereEmail($email)->whereForgotToken($verification_code)->first();

            if (!$user) {
                $this->showToastrMessage('error', __('Your verification code is incorrect!'));
            } else {
                $request->validate([
                    'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
                    'password_confirmation' => 'min:6'
                ]);

                $user->password = Hash::make($request->password);
                $user->email_verified_at = now();
                $user->forgot_token = null;
                $user->save();
                Session::put('email', '');
                Session::put('verification_code', '');
                $this->showToastrMessage('success', 'Successfully changed your password.');
                return redirect()->route('login');
            }
        } else {
            $this->showToastrMessage('error', __('Your verification code is incorrect!'));
        }

        return redirect()->back();
    }

    public function logout(Request $request)
    {
        if (get_option('device_control')) {
            $device_uuid = $request->cookie('_uuid_d');
            Cookie::queue(Cookie::forget('_uuid_d'));
            Device::join('device_user', 'devices.id', '=', 'device_user.device_id')
                ->where('devices.device_uuid', $device_uuid)
                ->update(['deleted_at' => now()]);
        }

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new \Illuminate\Http\JsonResponse([], 204)
            : redirect('/');
    }
}

