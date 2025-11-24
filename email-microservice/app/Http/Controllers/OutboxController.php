<?php

namespace App\Http\Controllers;

use App\Models\Outbox;
use App\Models\Tenant;
use App\Models\EmailProvider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OutboxController extends Controller
{
    /**
     * Display the outbox page
     */
    public function index(): View
    {
        return view('outbox');
    }

    /**
     * Get outbox emails for a specific tenant
     */
    public function getEmails(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => 'required|string|exists:tenants,tenant_id',
            'status' => 'sometimes|string|in:pending,sent,failed,bounced,delivered',
            'search' => 'sometimes|string',
            'from_email' => 'sometimes|email',
            'to_email' => 'sometimes|email',
            'subject' => 'sometimes|string',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'provider_id' => 'sometimes|string|exists:email_providers,provider_id',
            'user_id' => 'sometimes|string',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100'
        ]);

        $query = Outbox::where('tenant_id', $request->tenant_id)
            ->with(['tenant', 'provider', 'user']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by from email
        if ($request->has('from_email')) {
            $query->where('from', $request->from_email);
        }

        // Filter by to email
        if ($request->has('to_email')) {
            $query->whereJsonContains('to', $request->to_email);
        }

        // Filter by subject
        if ($request->has('subject')) {
            $query->where('subject', 'like', "%{$request->subject}%");
        }

        // Filter by date range
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('sent_at', [$request->date_from, $request->date_to]);
        } elseif ($request->has('date_from')) {
            $query->where('sent_at', '>=', $request->date_from);
        } elseif ($request->has('date_to')) {
            $query->where('sent_at', '<=', $request->date_to);
        }

        // Filter by provider
        if ($request->has('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('from', 'like', "%{$search}%")
                  ->orWhereJsonContains('to', $search)
                  ->orWhereJsonContains('cc', $search)
                  ->orWhereJsonContains('bcc', $search);
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $emails = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $emails->items(),
            'pagination' => [
                'current_page' => $emails->currentPage(),
                'last_page' => $emails->lastPage(),
                'per_page' => $emails->perPage(),
                'total' => $emails->total(),
                'from' => $emails->firstItem(),
                'to' => $emails->lastItem()
            ]
        ]);
    }

    /**
     * Get email details
     */
    public function show(string $id): JsonResponse
    {
        $email = Outbox::with(['tenant', 'provider', 'user', 'attachments'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $email
        ]);
    }

    /**
     * Update email status
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,sent,failed,bounced'
        ]);

        $email = Outbox::findOrFail($id);
        $email->update([
            'status' => $request->status,
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email status updated successfully',
            'data' => $email
        ]);
    }

    /**
     * Delete email from outbox
     */
    public function destroy(string $id): JsonResponse
    {
        $email = Outbox::findOrFail($id);
        
        // Only allow deletion of pending emails
        if ($email->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending emails can be deleted'
            ], 400);
        }

        $email->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email deleted successfully'
        ]);
    }

    /**
     * Resend failed email
     */
    public function resend(string $id): JsonResponse
    {
        $email = Outbox::findOrFail($id);
        
        if ($email->status !== 'failed') {
            return response()->json([
                'success' => false,
                'message' => 'Only failed emails can be resent'
            ], 400);
        }

        // Reset status to pending for resending
        $email->update([
            'status' => 'pending',
            'retry_count' => $email->retry_count + 1,
            'last_retry_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Email queued for resending',
            'data' => $email
        ]);
    }

    /**
     * Get outbox statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => 'required|string'
        ]);

        $stats = Outbox::where('tenant_id', $request->tenant_id)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = "bounced" THEN 1 ELSE 0 END) as bounced
            ')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
