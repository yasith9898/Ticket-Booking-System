<?php

namespace App\Http\Controllers;

use App\Models\PipelineCard;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function crmReports()
    {
        $rows = PipelineCard::query()
            ->with(['client', 'histories' => fn ($q) => $q->orderByDesc('happened_on')])
            ->get()
            ->map(function ($card) {
                $status = 'Open';
                if ($card->column_key === 'col-won') {
                    $status = 'Won';
                } elseif ($card->column_key === 'col-lost') {
                    $status = 'Lost';
                }

                $last = $card->histories->first();
                $cl = $card->client;

                return [
                    'id' => $card->id,
                    'name' => $cl->person_name,
                    'company' => $cl->company,
                    'category' => $cl->category ?: 'General',
                    'ticketDate' => $last?->happened_on?->format('Y-m-d') ?? $card->created_at->format('Y-m-d'),
                    'ticketSubject' => $last && $last->note ? mb_substr($last->note, 0, 80) : 'Pipeline Activity',
                    'status' => $status,
                ];
            });

        if ($rows->isEmpty()) {
            $rows = collect([
                [
                    'id' => 1,
                    'name' => 'Ali Ahmed',
                    'company' => 'Baghdad Tech',
                    'category' => 'Tech',
                    'ticketDate' => now()->format('Y-m-d'),
                    'ticketSubject' => 'Sample entry — add pipeline cards to see live data.',
                    'status' => 'Open',
                ],
            ]);
        }

        return view('crm-reports', ['reportJson' => $rows->values()]);
    }

    public function reportsByEmployee()
    {
        $raw = Ticket::query()
            ->select([
                'ticket_number as id',
                DB::raw('COALESCE(employee, "Unassigned") as employee'),
                DB::raw('DATE(opened_on) as date'),
                'status',
                'subject',
                'client_name as client',
                'mobile',
            ])
            ->orderByDesc('opened_on')
            ->get();

        return view('reports-by-employee', ['ticketDataJson' => $raw->values()]);
    }
}
