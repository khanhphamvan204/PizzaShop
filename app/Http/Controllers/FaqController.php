<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Mail\FaqAnswerMail;
use App\Mail\FaqConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class FaqController extends Controller
{
    /**
     * Display a listing of FAQs, with optional search and filtering by status.
     */
    public function index(Request $request)
    {
        try {
            $query = Faq::query();

            // Search by question, answer, or name
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('question', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            }

            // Filter by status (pending/answered)
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $faqs = $query->orderBy('created_at', 'desc')->get();
            return response()->json([
                'success' => true,
                'data' => $faqs
            ]);
        } catch (Exception $e) {
            Log::error("FAQ index error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch FAQs'
            ], 500);
        }
    }

    /**
     * Store a new FAQ question from user input and send confirmation email.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:100',
                'question' => 'required|string|max:255',
            ]);

            $faq = Faq::create($request->only(['name', 'email', 'question']));

            // Send confirmation email to user
            Mail::to($faq->email)->send(new FaqConfirmationMail($faq));

            return response()->json([
                'success' => true,
                'message' => 'FAQ created successfully',
                'data' => $faq
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error("FAQ store error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create FAQ'
            ], 500);
        }
    }

    /**
     * Display a specific FAQ by ID.
     */
    public function show($id)
    {
        try {
            $faq = Faq::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $faq
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'FAQ not found'
            ], 404);
        } catch (Exception $e) {
            Log::error("FAQ show error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch FAQ'
            ], 500);
        }
    }

    /**
     * Update an existing FAQ (only answer for admin, auto-set status to answered).
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'answer' => 'required|string', // answer is required when updating
            ]);

            $faq = Faq::findOrFail($id);
            $faq->update([
                'answer' => $request->answer,
                'status' => 'answered' // Auto-set status to answered
            ]);

            // Send answer email to user
            Mail::to($faq->email)->send(new FaqAnswerMail($faq));

            return response()->json([
                'success' => true,
                'message' => 'FAQ updated successfully',
                'data' => $faq
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'FAQ not found'
            ], 404);
        } catch (Exception $e) {
            Log::error("FAQ update error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update FAQ'
            ], 500);
        }
    }

    /**
     * Delete a specific FAQ.
     */
    public function destroy($id)
    {
        try {
            $faq = Faq::findOrFail($id);
            $faq->delete();
            return response()->json([
                'success' => true,
                'message' => 'FAQ deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'FAQ not found'
            ], 404);
        } catch (Exception $e) {
            Log::error("FAQ destroy error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete FAQ'
            ], 500);
        }
    }
}
?>