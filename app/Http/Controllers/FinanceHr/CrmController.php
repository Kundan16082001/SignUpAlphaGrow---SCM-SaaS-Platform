<?php

namespace App\Http\Controllers\FinanceHr;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Dealer;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $tab = $request->get('tab', 'leads');

        $leads = Lead::where('tenant_id', $tenantId)->latest()->paginate(10, ['*'], 'leads_page');
        $dealers = Dealer::where('tenant_id', $tenantId)->latest()->paginate(10, ['*'], 'dealers_page');
        $tickets = Ticket::where('tenant_id', $tenantId)->latest()->paginate(10, ['*'], 'tickets_page');

        // Lead Pipeline Stats
        $leadStats = [
            'total' => Lead::where('tenant_id', $tenantId)->count(),
            'new' => Lead::where('tenant_id', $tenantId)->where('status', 'new')->count(),
            'contacted' => Lead::where('tenant_id', $tenantId)->where('status', 'contacted')->count(),
            'quoted' => Lead::where('tenant_id', $tenantId)->where('status', 'quoted')->count(),
            'won' => Lead::where('tenant_id', $tenantId)->where('status', 'won')->count(),
            'lost' => Lead::where('tenant_id', $tenantId)->where('status', 'lost')->count(),
            'pipeline_value' => Lead::where('tenant_id', $tenantId)->whereIn('status', ['new', 'contacted', 'quoted'])->sum('value'),
            'won_value' => Lead::where('tenant_id', $tenantId)->where('status', 'won')->sum('value'),
            'overdue_followups' => Lead::where('tenant_id', $tenantId)->where('follow_up_date', '<', now())->whereIn('status', ['new', 'contacted', 'quoted'])->count(),
        ];

        // Dealer Stats
        $dealerStats = [
            'total' => Dealer::where('tenant_id', $tenantId)->count(),
            'active' => Dealer::where('tenant_id', $tenantId)->active()->count(),
            'total_business' => Dealer::where('tenant_id', $tenantId)->sum('total_business'),
            'total_outstanding' => Dealer::where('tenant_id', $tenantId)->sum('outstanding'),
        ];

        // Ticket Stats
        $ticketStats = [
            'total' => Ticket::where('tenant_id', $tenantId)->count(),
            'open' => Ticket::where('tenant_id', $tenantId)->where('status', 'open')->count(),
            'in_progress' => Ticket::where('tenant_id', $tenantId)->where('status', 'in_progress')->count(),
            'urgent' => Ticket::where('tenant_id', $tenantId)->where('priority', 'urgent')->open()->count(),
            'resolved_this_month' => Ticket::where('tenant_id', $tenantId)->resolved()->whereMonth('updated_at', now()->month)->count(),
        ];

        return view('finance-hr.crm.index', compact(
            'leads', 'dealers', 'tickets',
            'leadStats', 'dealerStats', 'ticketStats',
            'tab'
        ));
    }

    // ==================== LEADS ====================
    public function storeLead(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:100',
            'value' => 'nullable|numeric|min:0',
            'follow_up_date' => 'nullable|date|after_or_equal:today',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $leadCount = Lead::where('tenant_id', $tenantId)->count() + 1;
        $leadCode = 'LEAD-' . str_pad($leadCount, 3, '0', STR_PAD_LEFT);

        Lead::create([
            'tenant_id' => $tenantId,
            'lead_code' => $leadCode,
            'company_name' => $request->company_name,
            'contact_person' => $request->contact_person,
            'source' => $request->source,
            'value' => $request->value ?? 0,
            'status' => 'new',
            'follow_up_date' => $request->follow_up_date,
        ]);

        return redirect()->route('finance-hr.crm.index', ['tab' => 'leads'])->with('success', 'Lead added successfully!');
    }

    public function updateLeadStatus(Request $request, Lead $lead)
    {
        if ($lead->tenant_id !== Auth::user()->tenant_id) abort(403);
        $request->validate(['status' => 'required|in:new,contacted,quoted,won,lost']);
        $lead->update(['status' => $request->status]);
        return redirect()->back()->with('success', 'Lead status updated!');
    }

    // ==================== DEALERS ====================
    public function storeDealer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $dealerCount = Dealer::where('tenant_id', $tenantId)->count() + 1;
        $dealerCode = 'DLR-' . str_pad($dealerCount, 3, '0', STR_PAD_LEFT);

        Dealer::create([
            'tenant_id' => $tenantId,
            'dealer_code' => $dealerCode,
            'name' => $request->name,
            'region' => $request->region,
            'credit_limit' => $request->credit_limit ?? 0,
            'status' => 'active',
        ]);

        return redirect()->route('finance-hr.crm.index', ['tab' => 'dealers'])->with('success', 'Dealer added successfully!');
    }

    public function updateDealer(Request $request, Dealer $dealer)
    {
        if ($dealer->tenant_id !== Auth::user()->tenant_id) abort(403);

        $request->validate([
            'total_orders' => 'nullable|integer|min:0',
            'total_business' => 'nullable|numeric|min:0',
            'outstanding' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $dealer->update($request->only(['total_orders', 'total_business', 'outstanding', 'status']));
        return redirect()->back()->with('success', 'Dealer updated!');
    }

    // ==================== TICKETS ====================
    public function storeTicket(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'nullable|string|max:1000',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $ticketCount = Ticket::where('tenant_id', $tenantId)->count() + 1;
        $ticketNo = 'TKT-' . str_pad($ticketCount, 3, '0', STR_PAD_LEFT);

        Ticket::create([
            'tenant_id' => $tenantId,
            'ticket_no' => $ticketNo,
            'customer_name' => $request->customer_name,
            'subject' => $request->subject,
            'priority' => $request->priority,
            'status' => 'open',
            'description' => $request->description,
        ]);

        return redirect()->route('finance-hr.crm.index', ['tab' => 'tickets'])->with('success', 'Ticket created!');
    }

    public function updateTicketStatus(Request $request, Ticket $ticket)
    {
        if ($ticket->tenant_id !== Auth::user()->tenant_id) abort(403);
        $request->validate(['status' => 'required|in:open,in_progress,resolved,closed']);
        $ticket->update(['status' => $request->status]);
        return redirect()->back()->with('success', 'Ticket status updated!');
    }
}