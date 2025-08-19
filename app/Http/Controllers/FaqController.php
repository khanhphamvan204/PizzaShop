<?php
namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::all();
        return response()->json([
            'status' => 'success',
            'data' => $faqs
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'answer' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $faq = Faq::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $faq
        ], 201);
    }

    public function show(Faq $faq)
    {
        return response()->json([
            'status' => 'success',
            'data' => $faq
        ], 200);
    }

    public function update(Request $request, Faq $faq)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'answer' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $faq->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $faq
        ], 200);
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'FAQ deleted successfully'
        ], 200);
    }
}