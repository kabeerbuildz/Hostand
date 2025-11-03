<?php

namespace App\Http\Controllers;

use App\Models\LoggedHistory;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage user')) {
            if (\Auth::user()->type == 'super admin') {
                $users = User::where('type','!=', 'super admin')->get();
                return view('user.index', compact('users'));
            } else {
                $users = User::where('parent_id', '=', parentId())->whereNotIn('type', ['tenant', 'maintainer'])->get();
                return view('user.index', compact('users'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        $userRoles = Role::where('parent_id', parentId())->whereNotIn('name', ['tenant', 'maintainer'])->get()->pluck('name', 'id');
        return view('user.create', compact('userRoles'));
    }


    // public function store(Request $request)
    // {
    //     if (\Auth::user()->can('create user')) {
    //         if (\Auth::user()->type == 'super admin') {
    //             $validator = \Validator::make(
    //                 $request->all(),
    //                 [
    //                     'name' => 'required',
    //                     'email' => 'required|email|unique:users',
    //                     'password' => 'required|min:6',
    //                 ]
    //             );
    //             if ($validator->fails()) {
    //                 $messages = $validator->getMessageBag();

    //                 return redirect()->back()->with('error', $messages->first());
    //             }

    //             $user = new User();
    //             $user->first_name = $request->name;
    //             $user->email = $request->email;
    //             $user->email_verified_at = now();
    //             $user->password = \Hash::make($request->password);
    //             $user->phone_number = $request->phone_number;
    //             $user->type = 'owner';
    //             $user->lang = 'english';
    //             $user->subscription = 1;
    //             $user->email_verified_at = now();
    //             $user->parent_id = parentId();
    //             $user->save();
    //             $userRole = Role::findByName('owner');
    //             $user->assignRole($userRole);

    //             defaultTenantCreate($user->id);
    //             defaultMaintainerCreate($user->id);
    //             defultTemplate($user->id);

    //             $module = 'owner_create';
    //             $setting = settings();
    //             $errorMessage='';
    //             if (!empty($user)) {
    //                 $data['subject'] = 'New User Created';
    //                 $data['module'] = $module;
    //                 $data['password'] = $request->password;
    //                 $data['name'] = $request->name;
    //                 $data['email'] = $request->email;
    //                 $data['url'] = env('APP_URL');
    //                 $data['logo'] = $setting['company_logo'];
    //                 $to = $user->email;
    //                 $response = commonEmailSend($to, $data);
    //                 if ($response['status'] == 'error') {
    //                     $errorMessage=$response['message'];
    //                 }
    //             }

    //             return redirect()->route('users.index')->with('success', __('User successfully created.').'</br>'.$errorMessage);
    //         } else {

    //             $validator = \Validator::make(
    //                 $request->all(),
    //                 [
    //                     'first_name' => 'required',
    //                     'last_name' => 'required',
    //                     'email' => 'required|email|unique:users',
    //                     'password' => 'required|min:6',
    //                     'role' => 'required',
    //                 ]
    //             );
    //             if ($validator->fails()) {
    //                 $messages = $validator->getMessageBag();

    //                 return redirect()->back()->with('error', $messages->first());
    //             }

    //             $ids = parentId();
    //             $authUser = \App\Models\User::find($ids);
    //             $totalUser = $authUser->totalUser();
    //             $subscription = Subscription::find($authUser->subscription);
    //             if ($totalUser >= $subscription->user_limit && $subscription->user_limit != 0) {
    //                 return redirect()->back()->with('error', __('Your user limit is over, please upgrade your subscription.'));
    //             }

    //             $userRole = Role::findById($request->role);
    //             $user = new User();
    //             $user->first_name = $request->first_name;
    //             $user->last_name = $request->last_name;
    //             $user->email = $request->email;
    //             $user->phone_number = $request->phone_number;
    //             $user->password = \Hash::make($request->password);
    //             $user->email_verified_at = now();
    //             $user->type = $userRole->name;
    //             $user->profile = 'avatar.png';
    //             $user->lang = 'english';
    //             $user->parent_id = parentId();
    //             $user->save();
    //             $user->assignRole($userRole);

    //             $module = 'user_create';
    //             $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
    //             $notification->password=$request->password;
    //             $setting = settings();
    //             $errorMessage = '';
    //             if (!empty($notification) && $notification->enabled_email == 1) {
    //                 $notification_responce = MessageReplace($notification, $user->id);
    //                 $data['subject'] = $notification_responce['subject'];
    //                 $data['message'] = $notification_responce['message'];
    //                 $data['module'] = $module;
    //                 $data['password'] = $request->password;
    //                 $data['logo'] = $setting['company_logo'];
    //                 $to = $user->email;

    //                 $response = commonEmailSend($to, $data);
    //                 if ($response['status'] == 'error') {
    //                     $errorMessage=$response['message'];
    //                 }
    //             }

    //             return redirect()->route('users.index')->with('success', __('User successfully created.'). '</br>'.$errorMessage);
    //         }
    //     } else {
    //         return redirect()->back()->with('error', __('Permission Denied.'));
    //     }
    // }
public function store(Request $request)
{
    // Check permission
    if (!\Auth::user()->can('create user')) {
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    // If logged-in user is SUPER ADMIN
    if (\Auth::user()->type == 'super admin') {

        // ✅ Validation
        $validator = \Validator::make($request->all(), [
            'name'                => 'required_without_all:first_name,last_name|string|max:191',
            'first_name'          => 'nullable|string|max:191',
            'last_name'           => 'nullable|string|max:191',
            'email'               => 'required|email|unique:users',
            'password'            => 'required|min:6',
            'company_name'        => 'nullable|string|max:255',
            'vat_tax_code'        => 'nullable|string|max:255',
            'residential_address' => 'nullable|string|max:255',
            'certified_email'     => 'nullable|email|max:255',
            'telephone_number'    => 'nullable|string|max:255',
            'sdi'                 => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        // ✅ Normalize name
        $firstName = null;
        $lastName  = null;
        if ($request->filled('first_name') && $request->filled('last_name')) {
            $firstName = $request->first_name;
            $lastName  = $request->last_name;
        } else {
            $full = trim($request->input('name', ''));
            if ($full !== '') {
                $parts = preg_split('/\s+/', $full, 2);
                $firstName = $parts[0];
                $lastName  = $parts[1] ?? null;
            }
        }

        // ✅ Create User
        $user = new User();
        $user->first_name          = $firstName;
        $user->last_name           = $lastName;
        $user->email               = $request->email;
        $user->password            = \Hash::make($request->password);
        $user->phone_number        = $request->phone_number;
        $user->telephone_number    = $request->telephone_number;
        $user->company_name        = $request->company_name;
        $user->vat_tax_code        = $request->vat_tax_code;
        $user->residential_address = $request->residential_address;
        $user->certified_email     = $request->certified_email;
        $user->sdi                 = $request->sdi;
        $user->type                = 'owner';
        $user->lang                = 'english';
        $user->subscription        = 1;
        $user->email_verified_at   = now();
        $user->parent_id           = parentId();
        $user->save();

        // ✅ Assign Role
        $userRole = Role::findByName('owner');
        $user->assignRole($userRole);

        // ✅ Default setup
        defaultTenantCreate($user->id);
        defaultMaintainerCreate($user->id);
        defultTemplate($user->id);

        // ✅ Send Notification Email
        $module = 'owner_create';
        $setting = settings();
        $errorMessage = '';

        $data = [
            'subject'  => 'New User Created',
            'module'   => $module,
            'password' => $request->password,
            'name'     => $firstName . ' ' . $lastName,
            'email'    => $request->email,
            'url'      => env('APP_URL'),
            'logo'     => $setting['company_logo'],
        ];

        $response = commonEmailSend($user->email, $data);
        if ($response['status'] == 'error') {
            $errorMessage = $response['message'];
        }

        return redirect()->route('users.index')
            ->with('success', __('User successfully created.') . '</br>' . $errorMessage);
    }

    // If logged-in user is NOT SUPER ADMIN
    else {

        // ✅ Validation
        $validator = \Validator::make($request->all(), [
            'first_name'          => 'required|string|max:191',
            'last_name'           => 'required|string|max:191',
            'email'               => 'required|email|unique:users',
            'password'            => 'required|min:6',
            'role'                => 'required',
            'company_name'        => 'nullable|string|max:255',
            'vat_tax_code'        => 'nullable|string|max:255',
            'residential_address' => 'nullable|string|max:255',
            'certified_email'     => 'nullable|email|max:255',
            'telephone_number'    => 'nullable|string|max:255',
            'sdi'                 => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        // ✅ Subscription limits
        $authUser = \App\Models\User::find(parentId());
        $totalUser = $authUser->totalUser();
        $subscription = Subscription::find($authUser->subscription);

        if ($totalUser >= $subscription->user_limit && $subscription->user_limit != 0) {
            return redirect()->back()->with('error', __('Your user limit is over, please upgrade your subscription.'));
        }

        // ✅ Create User
        $userRole = Role::findById($request->role);

        $user = new User();
        $user->first_name          = $request->first_name;
        $user->last_name           = $request->last_name;
        $user->email               = $request->email;
        $user->phone_number        = $request->phone_number;
        $user->telephone_number    = $request->telephone_number;
        $user->password            = \Hash::make($request->password);
        $user->company_name        = $request->company_name;
        $user->vat_tax_code        = $request->vat_tax_code;
        $user->residential_address = $request->residential_address;
        $user->certified_email     = $request->certified_email;
        $user->sdi                 = $request->sdi;
        $user->email_verified_at   = now();
        $user->type                = $userRole->name;
        $user->profile             = 'avatar.png';
        $user->lang                = 'english';
        $user->parent_id           = parentId();
        $user->save();

        $user->assignRole($userRole);

        // ✅ Notification
        $module = 'user_create';
        $notification = Notification::where('parent_id', parentId())
            ->where('module', $module)
            ->first();

        $errorMessage = '';
        if (!empty($notification) && $notification->enabled_email == 1) {
            $notification->password = $request->password;
            $notification_response = MessageReplace($notification, $user->id);
            $setting = settings();

            $data = [
                'subject'  => $notification_response['subject'],
                'message'  => $notification_response['message'],
                'module'   => $module,
                'password' => $request->password,
                'logo'     => $setting['company_logo'],
            ];

            $response = commonEmailSend($user->email, $data);
            if ($response['status'] == 'error') {
                $errorMessage = $response['message'];
            }
        }

        return redirect()->route('users.index')
            ->with('success', __('User successfully created.') . '</br>' . $errorMessage);
    }
}



   public function show($id)
{
    $user = User::with('roles')->findOrFail($id);
    $userRoles = Role::pluck('name', 'id'); // in case you reuse this view structure
    return view('user.show', compact('user', 'userRoles'));
}



    public function edit($id)
    {
        $user = User::findOrFail($id);
        $userRoles = Role::where('parent_id', '=', parentId())->whereNotIn('name', ['tenant', 'maintainer'])->get()->pluck('name', 'id');

        return view('user.edit', compact('user', 'userRoles'));
    }


public function update(Request $request, $id)
{
    if (!\Auth::user()->can('edit user')) {
        return redirect()->back()->with('error', __('Permission Denied.'));
    }

    $user = User::findOrFail($id);

    if (\Auth::user()->type == 'super admin') {
        // ✅ Validation
        $validator = \Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $id,
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        // ✅ Split name into first and last name if possible
        $nameParts = explode(' ', $request->name, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        // ✅ Update user
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->telephone_number = $request->telephone_number;
        $user->company_name = $request->company_name;
        $user->vat_tax_code = $request->vat_tax_code;
        $user->residential_address = $request->residential_address;
        $user->certified_email = $request->certified_email;
        $user->sdi = $request->sdi;

        $user->save();

        return redirect()->route('users.index')->with('success', __('User successfully updated.'));
    }

    // ✅ For normal users (not super admin)
    $validator = \Validator::make(
        $request->all(),
        [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required',
        ]
    );

    if ($validator->fails()) {
        $messages = $validator->getMessageBag();
        return redirect()->back()->with('error', $messages->first());
    }

    $userRole = Role::findById($request->role);

    $user->first_name = $request->first_name;
    $user->last_name = $request->last_name;
    $user->email = $request->email;
    $user->phone_number = $request->phone_number;
    $user->telephone_number = $request->telephone_number;
    $user->company_name = $request->company_name;
    $user->vat_tax_code = $request->vat_tax_code;
    $user->residential_address = $request->residential_address;
    $user->certified_email = $request->certified_email;
    $user->sdi = $request->sdi;
    $user->type = $userRole->name;
    $user->save();

    // ✅ Sync role safely
    if ($userRole) {
        $user->roles()->sync([$userRole->id]);
    }

    return redirect()->route('users.index')->with('success', __('User successfully updated.'));
}


    public function destroy($id)
    {

        if (\Auth::user()->can('delete user')) {
            $user = User::find($id);
            $user->delete();

            return redirect()->route('users.index')->with('success', __('User successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function loggedHistory()
    {
        $ids = parentId();
        $authUser = \App\Models\User::find($ids);
        $subscription = \App\Models\Subscription::find($authUser->subscription);

        if (\Auth::user()->can('manage logged history') && $subscription->enabled_logged_history == 1) {
            $histories = LoggedHistory::where('parent_id', parentId())->get();
            return view('logged_history.index', compact('histories'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function loggedHistoryShow($id)
    {
        if (\Auth::user()->can('manage logged history')) {
            $histories = LoggedHistory::find($id);
            return view('logged_history.show', compact('histories'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function loggedHistoryDestroy($id)
    {
        if (\Auth::user()->can('delete logged history')) {
            $histories = LoggedHistory::find($id);
            $histories->delete();
            return redirect()->back()->with('success', 'Logged history succefully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
