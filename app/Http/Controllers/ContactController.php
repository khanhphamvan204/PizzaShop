<?php
namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::with('user')->get();
        return response()->json([
            'status' => 'success',
            'data' => $contacts
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'name' => 'nullable|string|max:100',
            'email' => 'required|email|max:100',
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $contact = Contact::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $contact->load('user')
        ], 201);
    }

    public function show(Contact $contact)
    {
        return response()->json([
            'status' => 'success',
            'data' => $contact->load('user')
        ], 200);
    }

    public function update(Request $request, Contact $contact)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'name' => 'nullable|string|max:100',
            'email' => 'required|email|max:100',
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $contact->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $contact->load('user')
        ], 200);
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Contact deleted successfully'
        ], 200);
    }
}