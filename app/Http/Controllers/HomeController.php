<?php

namespace App\Http\Controllers;

use App\Models\UserPackage;
use App\Traits\General;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use IvanoMatteo\LaravelDeviceTracking\Models\Device;

class HomeController extends Controller
{
    use General;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();

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

        // fallback por si no tiene ningún rol válido
        return redirect()->route('login');
    }

    /**
     * Show all the login device of current user.
     */
    public function allLoginDevice()
    {
        $data['devices'] = auth()->user()->device;
        $userPackage = UserPackage::join('packages', 'packages.id', '=', 'user_packages.package_id')
            ->where('package_type', PACKAGE_TYPE_SUBSCRIPTION)
            ->where('user_packages.user_id', auth()->id())
            ->where('user_packages.status', PACKAGE_STATUS_ACTIVE)
            ->whereDate('enroll_date', '<=', now())
            ->whereDate('expired_date', '>=', now())
            ->with('enrollments')
            ->select('user_packages.device')
            ->first();

        $data['limit'] = $userPackage ? $userPackage->device : get_option('device_limit');

        return view('frontend.logout_devices', $data);
    }

    /**
     * Logout a specific device or all devices.
     */
    public function logoutDevice($device_id = null)
    {
        $userPackage = UserPackage::join('packages', 'packages.id', '=', 'user_packages.package_id')
            ->where('package_type', PACKAGE_TYPE_SUBSCRIPTION)
            ->where('user_packages.user_id', auth()->id())
            ->where('user_packages.status', PACKAGE_STATUS_ACTIVE)
            ->whereDate('enroll_date', '<=', now())
            ->whereDate('expired_date', '>=', now())
            ->with('enrollments')
            ->select('user_packages.device')
            ->first();

        $limit = $userPackage ? $userPackage->device : get_option('device_limit');

        if ($device_id) {
            Device::join('device_user', 'devices.id', '=', 'device_user.device_id')
                ->where('devices.id', $device_id)
                ->update(['deleted_at' => now()]);
            Cookie::queue(Cookie::forget('_uuid_d'));
            $this->showToastrMessage('success', 'Logout device successfully.');

            $device_count = auth()->user()->device->count();
            if ($device_count < $limit) {
                \DeviceTracker::detectFindAndUpdate();
            }
        } else {
            Device::join('device_user', 'devices.id', '=', 'device_user.device_id')
                ->where('user_id', auth()->id())
                ->update(['deleted_at' => now()]);
            $this->showToastrMessage('success', 'Logout from all devices successfully. Please login to continue.');
            Cookie::queue(Cookie::forget('_uuid_d'));
            Auth::logout();
        }

        return redirect('/');
    }
}
