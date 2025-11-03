<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TypeController extends Controller
{

   public function index()
{
    // Check if the user has 'manage types' permission
    if (!\Auth::user()->can('manage types')) {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }

    // If user is admin, get all types, else get types by parent_id
    if (\Auth::user()->type === 'super admin') {
        $types = Type::all();
    } else {
        $types = Type::where('parent_id', parentId())->get();
    }

    return view('type.index', compact('types'));
}



    public function create()
    {
        $types = Type::$types;

        return view('type.create', compact('types'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create types')) {
            $validator = \Validator::make(
                $request->all(), [
                'title' => 'required',
                'type' => 'required',

            ],
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $type = new Type();
            $type->title = $request->title;
            $type->type = $request->type;
            $type->parent_id =parentId();
            $type->save();

            return redirect()->back()->with('success', __('Type successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show(Type $type)
    {
        //
    }


    public function edit(Type $type)
    {

        $types = Type::$types;
        return view('type.edit', compact('types', 'type'));
    }


    public function update(Request $request, Type $type)
    {
        if (\Auth::user()->can('edit types')) {
            $validator = \Validator::make(
                $request->all(), [
                'title' => 'required',
                'type' => 'required',

            ],
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            
            $type->title = $request->title;
            $type->type = $request->type;
            $type->parent_id =parentId();
            $type->save();

            return redirect()->back()->with('success', __('Type successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(Type $type)
    {
        if (\Auth::user()->can('delete types')) {
            $type->delete();
            return redirect()->back()->with('success', 'Type successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }
}
