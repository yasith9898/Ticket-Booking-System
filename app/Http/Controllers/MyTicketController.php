<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyTicketController extends Controller
{
    public function show()
    {
        $user = Auth::user()->name;

        $activeTickets = Ticket::query()
            ->where(function ($q) use ($user) {
                $q->where('employee', $user)
                    ->orWhere('employee', 'Unassigned')
                    ->orWhereNull('employee');
            })
            ->whereNotIn('status', ['Closed'])
            ->orderByDesc('opened_on')
            ->limit(50)
            ->get();

        $completedTickets = Ticket::query()
            ->where('employee', $user)
            ->where('status', 'Closed')
            ->orderByDesc('opened_on')
            ->limit(50)
            ->get();

        $initials = collect(explode(' ', $user))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');

        return view('my-ticket', [
            'userName' => $user,
            'userInitials' => $initials ?: 'U',
            'activeTickets' => $activeTickets,
            'completedTickets' => $completedTickets,
            'statTotal' => $activeTickets->count(),
            'statHigh' => $activeTickets->filter(fn ($t) => strtolower((string) $t->priority) === 'high')->count(),
            'statBlocked' => $activeTickets->filter(fn ($t) => ! $t->payment_ok)->count(),
        ]);
    }

    public function detailJson(string $ticketNumber)
    {
        $t = Ticket::where('ticket_number', (int) $ticketNumber)
            ->with(['messages', 'tasks'])
            ->firstOrFail();

        return response()->json([
            'subject' => $t->subject,
            'client' => $t->client_name ?: $t->company,
            'product' => $t->product ?: 'Enterprise CRM License',
            'payment_ok' => $t->payment_ok,
            'workflow_step' => (int) $t->workflow_step,
            'status' => $t->status,
            'messages' => $t->messages->map(fn ($m) => [
                'from' => $m->kind === 'internal' ? 'internal' : ($m->kind === 'client' ? 'client' : 'agent'),
                'text' => $m->body,
                'time' => $m->meta_time ?? $m->created_at->format('g:i A'),
            ]),
            'tasks' => $t->tasks->map(fn ($x) => [
                'id' => $x->id,
                'title' => $x->title,
                'completed' => $x->completed,
            ]),
        ]);
    }

    public function addMessage(Request $request, string $ticketNumber)
    {
        $t = Ticket::where('ticket_number', (int) $ticketNumber)->firstOrFail();
        $data = $request->validate([
            'body' => 'required|string',
            'kind' => 'required|in:reply,internal',
        ]);

        TicketMessage::create([
            'ticket_id' => $t->id,
            'kind' => $data['kind'] === 'internal' ? 'internal' : 'agent',
            'body' => $data['body'],
            'meta_time' => 'Just now',
        ]);

        return response()->json(['ok' => true]);
    }

    public function addTask(Request $request, string $ticketNumber)
    {
        $t = Ticket::where('ticket_number', (int) $ticketNumber)->firstOrFail();
        $data = $request->validate(['title' => 'required|string|max:255']);

        $max = (int) $t->tasks()->max('sort_order');

        TicketTask::create([
            'ticket_id' => $t->id,
            'title' => $data['title'],
            'sort_order' => $max + 1,
        ]);

        return response()->json(['ok' => true]);
    }

    public function update(Request $request, string $ticketNumber)
    {
        $t = Ticket::where('ticket_number', (int) $ticketNumber)->firstOrFail();
        $data = $request->validate([
            'workflow_step' => 'sometimes|integer|min:0|max:3',
            'payment_ok' => 'sometimes|boolean',
            'status' => 'sometimes|string|max:50',
        ]);

        if (array_key_exists('payment_ok', $data)) {
            $t->payment_ok = $data['payment_ok'];
        }
        if (isset($data['workflow_step'])) {
            $t->workflow_step = $data['workflow_step'];
        }
        if (isset($data['status'])) {
            $t->status = $data['status'];
        }
        $t->save();

        return response()->json(['ok' => true]);
    }
}
