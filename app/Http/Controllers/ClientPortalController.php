<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class ClientPortalController extends Controller
{
    public function submitForm()
    {
        return view('client-submit');
    }

    public function submitStore(Request $request)
    {
        $data = $request->validate([
            'client-name' => 'required|string|max:255',
            'client-company' => 'required|string|max:255',
            'client-contact' => 'nullable|string|max:255',
            'ticket-subject' => 'required|string|max:255',
            'ticket-desc' => 'nullable|string',
        ]);

        $contact = $data['client-contact'] ?? '';
        $email = filter_var($contact, FILTER_VALIDATE_EMAIL) ? $contact : null;

        $ticket = Ticket::create([
            'ticket_number' => Ticket::nextNumber(),
            'subject' => $data['ticket-subject'],
            'client_name' => $data['client-name'],
            'company' => $data['client-company'],
            'mobile' => $contact,
            'client_email' => $email,
            'employee' => null,
            'status' => 'Pending',
            'priority' => 'Normal',
            'opened_on' => now()->toDateString(),
            'due_on' => now()->addDays(2)->toDateString(),
            'public_comment' => $data['ticket-desc'] ?? null,
        ]);

        if (! empty($data['ticket-desc'])) {
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'kind' => 'client',
                'body' => $data['ticket-desc'],
                'meta_time' => 'Just now',
            ]);
        }

        return response()->json([
            'ticket_number' => $ticket->ticket_number,
        ]);
    }

    public function portal()
    {
        return view('client-portal');
    }

    public function ticketsJson(Request $request)
    {
        $email = $request->query('email');
        if (! $email) {
            return response()->json([]);
        }

        $tickets = Ticket::with(['messages' => fn ($q) => $q->orderBy('id')])
            ->where('client_email', $email)
            ->orderByDesc('opened_on')
            ->get();

        $out = [];
        foreach ($tickets as $t) {
            $status = $t->status;
            if ($status === 'In Process') {
                $status = 'In-Process';
            }

            $out[] = [
                'id' => (string) $t->ticket_number,
                'email' => $email,
                'subject' => $t->subject,
                'status' => str_replace(' ', '-', $status),
                'date' => $t->opened_on->format('Y-m-d'),
                'messages' => $t->messages->map(fn ($m) => [
                    'from' => $m->kind === 'client' ? 'client' : 'support',
                    'text' => $m->body,
                    'time' => $m->meta_time ?? $m->created_at->format('g:i A'),
                ])->values()->all(),
            ];
        }

        return response()->json($out);
    }

    public function reply(Request $request, int $ticketNumber)
    {
        $ticket = Ticket::where('ticket_number', $ticketNumber)->firstOrFail();

        $data = $request->validate([
            'body' => 'required|string',
            'email' => 'required|email',
        ]);

        if ($ticket->client_email !== $data['email']) {
            abort(403);
        }

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'kind' => 'client',
            'body' => $data['body'],
            'meta_time' => 'Just now',
        ]);

        return response()->json(['ok' => true]);
    }
}
