<?php

namespace App\Http\Controllers;

use App\Models\BoardColumn;
use App\Models\Client;
use App\Models\PipelineCard;
use App\Models\PipelineHistory;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PipelineController extends Controller
{
    public function show()
    {
        return view('pipeline', [
            'bootstrap' => $this->buildBootstrap(),
        ]);
    }

    protected function buildBootstrap(): array
    {
        $columnsOut = BoardColumn::orderBy('sort_order')->orderBy('id')->get(['column_key', 'title'])->map(fn ($c) => [
            'column_key' => $c->column_key,
            'title' => $c->title,
        ])->values()->all();

        $cardsOut = [];
        $historyOut = [];

        $cards = PipelineCard::with(['client', 'histories'])->orderBy('sort_order')->orderBy('id')->get();

        foreach ($cards as $card) {
            $cid = 'card-'.$card->id;
            $cl = $card->client;
            $rep = $card->sales_rep ?: ($cl->sales_rep ?: 'Unassigned');
            $cardsOut[$cid] = [
                'name' => $cl->person_name,
                'company' => $cl->company,
                'mobile' => $cl->mobile ?? '',
                'category' => $cl->category ?? '',
                'city' => $cl->city ?? '',
                'address' => $cl->address ?? '',
                'salesRep' => $rep,
                'column_key' => $card->column_key,
            ];
            $historyOut[$cid] = $card->histories->map(fn ($h) => [
                'date' => $h->happened_on->format('Y-m-d'),
                'stage' => $h->stage_name,
                'note' => $h->note ?? '',
            ])->values()->all();
        }

        return [
            'cards' => $cardsOut,
            'history' => $historyOut,
            'extraColumns' => $columnsOut,
        ];
    }

    public function storeLead(Request $request)
    {
        $mode = $request->input('mode');

        if ($mode === 'new') {
            $data = $request->validate([
                'mode' => 'required|in:new',
                'sales_rep' => 'nullable|string',
                'person_name' => 'required|string|max:255',
                'company' => 'required|string|max:255',
                'mobile' => 'nullable|string|max:255',
                'category' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
            ]);
        } elseif ($mode === 'exist') {
            $data = $request->validate([
                'mode' => 'required|in:exist',
                'sales_rep' => 'nullable|string',
                'existing_client_id' => 'required|integer|exists:clients,id',
            ]);
        } else {
            abort(422, 'Invalid mode');
        }

        $salesRep = ($data['sales_rep'] ?? '') ?: 'Unassigned';

        return DB::transaction(function () use ($data, $salesRep, $mode) {
            if ($mode === 'exist') {
                $client = Client::findOrFail($data['existing_client_id']);
            } else {
                $client = Client::create([
                    'person_name' => $data['person_name'],
                    'company' => $data['company'],
                    'mobile' => $data['mobile'] ?? null,
                    'category' => $data['category'] ?? null,
                    'city' => $data['city'] ?? null,
                    'address' => $data['address'] ?? null,
                    'sales_rep' => $salesRep === 'Unassigned' ? null : $salesRep,
                ]);
            }

            $card = PipelineCard::create([
                'client_id' => $client->id,
                'column_key' => 'col-new',
                'sales_rep' => $salesRep === 'Unassigned' ? null : $salesRep,
            ]);

            PipelineHistory::create([
                'pipeline_card_id' => $card->id,
                'stage_name' => 'New Lead',
                'note' => $mode === 'new' ? 'New Client Added' : 'Existing Client added to Pipeline',
                'happened_on' => now()->toDateString(),
            ]);

            $cid = 'card-'.$card->id;

            return response()->json([
                'card_id' => $cid,
                'card_db' => [
                    $cid => [
                        'name' => $client->person_name,
                        'company' => $client->company,
                        'mobile' => $client->mobile ?? '',
                        'category' => $client->category ?? '',
                        'city' => $client->city ?? '',
                        'address' => $client->address ?? '',
                        'salesRep' => $salesRep,
                    ],
                ],
                'history_entry' => [
                    'date' => now()->format('Y-m-d'),
                    'stage' => 'New Lead',
                    'note' => $mode === 'new' ? 'New Client Added' : 'Existing Client added to Pipeline',
                ],
            ]);
        });
    }

    public function move(Request $request)
    {
        $data = $request->validate([
            'card_id' => 'required|string',
            'target_column_key' => 'required|string',
            'happened_on' => 'required|date',
            'note' => 'nullable|string',
            'stage_name' => 'required|string',
            'create_ticket' => 'boolean',
        ]);

        $id = (int) str_replace('card-', '', $data['card_id']);

        return DB::transaction(function () use ($data, $id) {
            $card = PipelineCard::with('client')->findOrFail($id);
            $card->column_key = $data['target_column_key'];
            $card->save();

            PipelineHistory::create([
                'pipeline_card_id' => $card->id,
                'stage_name' => $data['stage_name'],
                'note' => ($data['note'] ?? '') ?: 'No notes added.',
                'happened_on' => $data['happened_on'],
            ]);

            $payload = ['ok' => true];

            if (! empty($data['create_ticket'])) {
                $client = $card->client;
                $ticket = Ticket::create([
                    'ticket_number' => Ticket::nextNumber(),
                    'subject' => 'Onboarding - '.(($data['note'] ?? '') ?: 'Deal closed'),
                    'client_name' => $client->person_name,
                    'company' => $client->company,
                    'mobile' => $client->mobile,
                    'employee' => 'Unassigned',
                    'status' => 'Pending',
                    'priority' => 'Normal',
                    'opened_on' => $data['happened_on'],
                    'due_on' => $data['happened_on'],
                    'public_comment' => $data['note'],
                    'sales_rep' => $card->sales_rep,
                    'client_id' => $client->id,
                ]);
                $payload['ticket'] = [
                    'id' => (string) $ticket->ticket_number,
                    'date' => $ticket->opened_on->format('Y-m-d'),
                    'subject' => $ticket->subject,
                    'client' => $ticket->client_name,
                    'mobile' => $ticket->mobile ?? 'Unknown',
                    'employee' => $ticket->employee ?? 'Unassigned',
                    'status' => $ticket->status,
                    'due' => $ticket->due_on?->format('Y-m-d'),
                ];
            }

            return response()->json($payload);
        });
    }

    public function addColumn(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $key = 'col-'.uniqid();

        $max = (int) BoardColumn::max('sort_order');

        BoardColumn::create([
            'column_key' => $key,
            'title' => $data['title'],
            'sort_order' => $max + 1,
        ]);

        return response()->json([
            'column_key' => $key,
            'title' => $data['title'],
        ]);
    }

    public function clientsJson()
    {
        $rows = Client::orderBy('person_name')->get(['id', 'person_name', 'company']);

        return response()->json($rows->map(fn ($c) => [
            'id' => $c->id,
            'label' => $c->person_name.' ('.$c->company.')',
        ]));
    }
}
