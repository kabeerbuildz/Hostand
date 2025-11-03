<?php

namespace App\Http\Controllers;

use App\Models\Maintainer;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MaintainerController extends Controller
{

  public function index()
{
    if (\Auth::user()->can('manage maintainer')) {

        // Check if user is super admin
        if (\Auth::user()->type === 'super admin') {
            // Get all maintainers
            $maintainers = Maintainer::all();
        } else {
            // Get only maintainers with specific parent_id
            $maintainers = Maintainer::where('parent_id', parentId())
                ->orWhere('parent_id', 339)
                ->get();
        }

        return view('maintainer.index', compact('maintainers'));

    } else {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }
}



    // public function create()
    // {
    //     if (\Auth::user()->can('create maintainer')) {
    //         $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
    //         // $property = Property::pluck('name', 'id');


    //         $types = Type::where('parent_id', 339)->where('type', 'maintainer_type')->get()->pluck('title', 'id');
    //         $types->prepend(__('Select Type'), '');
           
    //         return view('maintainer.create', compact('property', 'types'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission Denied!'));
    //     }
    // }
public function create()
{
    if (\Auth::user()->can('create maintainer')) {

        if (\Auth::user()->type == 'super admin') {
            // Super admin sees all properties
            $property = Property::pluck('name', 'id');
        } else {
            // Others see only their own properties
            $property = Property::where('parent_id', parentId())->pluck('name', 'id');
        }
            if (\Auth::user()->type == 'super admin') {
                $types = Type::where('type', 'maintainer_type')
                        ->pluck('title', 'id');
            }else{
            $types = Type::where('parent_id', 339)
                        ->where('type', 'maintainer_type')
                        ->pluck('title', 'id');

            }
       
        $types->prepend(__('Select Type'), '');

        return view('maintainer.create', compact('property', 'types'));
    } else {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }
}


    public function store(Request $request)
    {

        // dd($request->all());

        if (\Auth::user()->can('create maintainer')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|unique:users',
                    'password' => 'required',
                    'phone_number' => 'required',
                    // 'property_id' => 'required',
                    'type_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $userRole = Role::where('parent_id', parentId())->where('name', 'maintainer')->first();
            if(!$userRole){
                $userRole = Role::create(['name' => 'maintainer', 'parent_id' => parentId()]);
            }
            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->email_verified_at = now();
            $user->password = \Hash::make($request->password);
            $user->phone_number = $request->phone_number;
            $user->type = $userRole->name;
            $user->profile = 'avatar.png';
            $user->lang = 'english';
            $user->parent_id = parentId();
            $user->save();
            $user->assignRole($userRole);

            if (!empty($request->profile)) {
                $maintainerFilenameWithExt = $request->file('profile')->getClientOriginalName();
                $maintainerFilename = pathinfo($maintainerFilenameWithExt, PATHINFO_FILENAME);
                $maintainerExtension = $request->file('profile')->getClientOriginalExtension();
                $maintainerFileName = $maintainerFilename . '_' . time() . '.' . $maintainerExtension;
                $dir = storage_path('upload/profile');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('profile')->storeAs('upload/profile/', $maintainerFileName);
                $user->profile = $maintainerFileName;
                $user->save();
            }


            $maintainer = new Maintainer();
            $maintainer->user_id = $user->id;
            // $maintainer->property_id = !empty($request->property_id) ? implode(',', $request->property_id) : '';
            $maintainer->type_id = $request->type_id;
            $maintainer->parent_id = parentId();

            // dd($maintainer);
            $maintainer->save();

            $module = 'maintainer_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            
            // Only try to assign password if notification exists
            if ($notification) {
                $notification->password = $request->password;
            }
            
            $setting=settings();
            $errorMessage = '';
            if (!empty($notification) && $notification->enabled_email == 1) {
                $notification_responce = MessageReplace($notification, $user->id);
                $datas['subject'] = $notification_responce['subject'];
                $datas['message'] = $notification_responce['message'];
                $datas['module'] = $module;
                $datas['logo']=  $setting['company_logo'];
                $to = $user->email;
                $response = commonEmailSend($to, $datas);
                    if ($response['status'] == 'error') {
                        $errorMessage=$response['message'];
                    }
            }


            return redirect()->back()->with('success', __('Maintainer successfully created.'). '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show(Maintainer $maintainer)
    {
        //
    }


public function edit(Maintainer $maintainer)
{
    if (\Auth::user()->can('edit maintainer')) {

        // Property selection based on user type
        if (\Auth::user()->type == 'super admin') {
            $property = Property::pluck('name', 'id');
        } else {
            $property = Property::where('parent_id', parentId())->pluck('name', 'id');
        }

        // Maintainer types based on user type
        if (\Auth::user()->type == 'super admin') {
            $types = Type::where('type', 'maintainer_type')
                        ->pluck('title', 'id');
        } else {
            $types = Type::where('parent_id', 339)
                        ->where('type', 'maintainer_type')
                        ->pluck('title', 'id');
        }

        $types->prepend(__('Select Type'), '');

        $user = User::find($maintainer->user_id);

        return view('maintainer.edit', compact('property', 'maintainer', 'types', 'user'));

    } else {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }
}


    public function update(Request $request, Maintainer $maintainer)
    {
        if (\Auth::user()->can('edit maintainer')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                     'email' => 'required|email|unique:users,email,' . $maintainer->user_id,
                    'phone_number' => 'required',
                    // 'property_id' => 'required',
                    'type_id' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $user = User::find($maintainer->user_id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone_number = $request->phone_number;
            $user->save();

            if (!empty($request->profile)) {
                $maintainerFilenameWithExt = $request->file('profile')->getClientOriginalName();
                $maintainerFilename = pathinfo($maintainerFilenameWithExt, PATHINFO_FILENAME);
                $maintainerExtension = $request->file('profile')->getClientOriginalExtension();
                $maintainerFileName = $maintainerFilename . '_' . time() . '.' . $maintainerExtension;
                $dir = storage_path('upload/profile');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('profile')->storeAs('upload/profile/', $maintainerFileName);
                $user->profile = $maintainerFileName;
                $user->save();
            }

            $maintainer->property_id = !empty($request->property_id) ? implode(',', $request->property_id) : 0;
            $maintainer->type_id = $request->type_id;
            $maintainer->save();



            return redirect()->back()->with('success', __('Maintainer successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(Maintainer $maintainer)
    {
        if (\Auth::user()->can('delete maintainer')) {
            User::where('id',$maintainer->user_id)->delete();
            $maintainer->delete();
            return redirect()->back()->with('success', __('Maintainer successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
}
