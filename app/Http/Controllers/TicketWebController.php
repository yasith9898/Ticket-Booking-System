<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketWebController extends Controller
{
    public function dashboard()
    {
        $tickets = Ticket::orderByDesc('opened_on')->get()->map(fn ($t) => [
            'id' => (string) $t->ticket_number,
            'date' => $t->opened_on->format('Y-m-d'),
            'subject' => $t->subject,
            'client' => $t->client_name ?: $t->company,
            'mobile' => $t->mobile ?? '',
            'employee' => $t->employee ?? 'Unassigned',
            'status' => $t->status,
            'due' => $t->due_on?->format('Y-m-d'),
        ]);

        return view('ticket-dashboard', ['ticketsJson' => $tickets]);
    }

    public function create()
    {
        $clients = Client::orderBy('person_name')->get(['id', 'person_name', 'company']);

        return view('create-ticket', compact('clients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mode' => 'required|in:exist,new',
            'exist_company' => 'nullable|string',
            'person_name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'new_mobile' => 'nullable|string|max:255',
            'new_category' => 'nullable|string|max:255',
            'new_city' => 'nullable|string|max:255',
            'new_address' => 'nullable|string|max:255',
            'new_salesrep' => 'nullable|string|max:255',
            'ticket-subject' => 'required|string|max:255',
            'ticket-priority' => 'required|string|max:50',
            'ticket-assign' => 'nullable|string|max:255',
            'ticket-comment' => 'nullable|string',
            'ticket-internal' => 'nullable|string',
        ]);

        $clientName = '';
        $company = '';
        $mobile = null;
        $salesRep = null;
        $clientId = null;

        if ($data['mode'] === 'exist') {
            $request->validate(['exist_company' => 'required|string']);
            $company = $data['exist_company'];
            $client = Client::where('company', $company)->first();
            if ($client) {
                $clientName = $client->person_name;
                $mobile = $client->mobile;
                $clientId = $client->id;
            } else {
                $parts = explode('(', $request->input('exist_label', ''));
                $clientName = trim($parts[0] ?? $company);
            }
        } else {
            $request->validate([
                'person_name' => 'required|string|max:255',
                'company' => 'required|string|max:255',
            ]);
            $clientName = $data['person_name'];
            $company = $data['company'];
            $mobile = $data['new_mobile'] ?? null;
            $salesRep = $data['new_salesrep'] ?? null;

            $client = Client::create([
                'person_name' => $clientName,
                'company' => $company,
                'mobile' => $mobile,
                'category' => $data['new_category'] ?? null,
                'city' => $data['new_city'] ?? null,
                'address' => $data['new_address'] ?? null,
                'sales_rep' => ($salesRep && $salesRep !== 'Unassigned') ? $salesRep : null,
            ]);
            $clientId = $client->id;
        }

        $assign = $data['ticket-assign'] ?? 'Unassigned';
        if ($assign === 'Unassigned') {
            $assign = null;
        }

        Ticket::create([
            'ticket_number' => Ticket::nextNumber(),
            'subject' => $data['ticket-subject'],
            'client_name' => $clientName,
            'company' => $company,
            'mobile' => $mobile,
            'employee' => $assign,
            'sales_rep' => $salesRep,
            'status' => 'Pending',
            'priority' => $data['ticket-priority'],
            'opened_on' => now()->toDateString(),
            'due_on' => now()->addDays(3)->toDateString(),
            'public_comment' => $data['ticket-comment'] ?? null,
            'internal_note' => $data['ticket-internal'] ?? null,
            'client_id' => $clientId,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['redirect' => route('ticket-dashboard')]);
        }

        return redirect()->route('ticket-dashboard');
    }
}
