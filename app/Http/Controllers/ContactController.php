<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contact;
use App\Models\Subscription;
use Illuminate\Http\Request;

class ContactController extends Controller
{

public function index()
{
    $userId = \Auth::id();

    if (\Auth::user()->can('manage contact')) {
        // Inbox = messages where I am the receiver
        $inbox = Contact::where('parent_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // Sent = messages where I am the sender
        $sent = Contact::where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('contact.index', compact('inbox', 'sent'));
    }

    return redirect()->back()->with('error', __('Permission denied.'));
}




    public function create()
    {
        return view('contact.create');
    }

public function store(Request $request)
{
    if (\Auth::user()->can('create contact')) {
        $validator = \Validator::make(
            $request->all(),
            [
                // 'name'    => 'required',
                'email'   => 'required|email', // receiver email
                // 'subject' => 'required',
                'message' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        // Find the user who will receive the message based on the email
        $receiver = User::where('email', $request->email)->first();

        if (!$receiver) {
            return redirect()->back()->with('error', __('Receiver not found.'));
        }

        $contact = new Contact();
        $contact->name           = $request->name;
        $contact->email          = $request->email;
        $contact->contact_number = $request->contact_number;
        $contact->subject        = $request->subject;
        $contact->message        = $request->message;

        // Set sender and receiver
        $contact->created_by = \Auth::id();   // sender (logged-in user)
        $contact->parent_id  = $receiver->id; // receiver ID

        $contact->save();

        return redirect()->back()->with('success', __('Contact successfully created.'));
    } else {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}




    public function show(Contact $contact)
    {
        //
    }


    public function edit(Contact $contact)
    {
        return view('contact.edit', compact('contact'));
    }


    public function update(Request $request, Contact $contact)
    {
        if (\Auth::user()->can('edit contact') ) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'subject' => 'required',
                    'message' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }


            $contact->name = $request->name;
            $contact->email = $request->email;
            $contact->contact_number = $request->contact_number;
            $contact->subject = $request->subject;
            $contact->message = $request->message;
            $contact->save();

            return redirect()->back()->with('success', __('Contact successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function destroy(Contact $contact)
    {
        if (\Auth::user()->can('edit contact') ) {
            $contact->delete();

            return redirect()->back()->with('success', 'Contact successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
}
