<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\PipelineCard;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketTask;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->delete();
        TicketTask::query()->delete();
        TicketMessage::query()->delete();
        Ticket::query()->delete();
        PipelineCard::query()->delete();
        Client::query()->delete();

        User::create([
            'name' => 'John Doe',
            'email' => 'admin@crm.local',
            'password' => bcrypt('password'),
        ]);

        $john = Client::create([
            'person_name' => 'John Doe',
            'company' => 'Acme Corp',
            'mobile' => '07701234567',
            'category' => 'Tech',
            'city' => 'Erbil',
            'address' => 'Main St',
            'sales_rep' => 'Ahmed Sales',
            'email' => 'john@acme.com',
        ]);

        $sarah = Client::create([
            'person_name' => 'Sarah Smith',
            'company' => 'Tech Global',
            'mobile' => '07509876543',
            'category' => 'Medical',
            'city' => 'Basra',
            'address' => 'Port Rd',
            'sales_rep' => 'Sara Rep',
        ]);

        Client::create([
            'person_name' => 'Mike Ross',
            'company' => 'Finance LLC',
            'mobile' => '07901112222',
            'category' => 'Finance',
            'city' => 'Baghdad',
            'address' => 'River Rd',
            'sales_rep' => 'Mike Deal',
        ]);

        PipelineCard::create([
            'client_id' => $john->id,
            'column_key' => 'col-new',
            'sales_rep' => 'Ahmed Sales',
        ]);

        PipelineCard::create([
            'client_id' => $sarah->id,
            'column_key' => 'col-negotiation',
            'sales_rep' => 'Sara Rep',
        ]);

        $rows = [
            [1001, '2026-10-24', 'Server Down', 'Acme Corp', '07701234567', 'John Doe', 'Paid', '2026-10-25'],
            [1002, '2026-10-23', 'Login Issue', 'Tech Global', '07509876543', 'Sarah Smith', 'In Process', '2026-10-26'],
            [1003, '2026-10-20', 'Billing Error', 'Finance LLC', '07901112222', 'Mike Ross', 'Overdue', '2026-10-21'],
            [1004, '2026-10-24', 'User Setup', 'StartUp Inc', '07803334444', 'Unassigned', 'Pending', '2026-10-25'],
            [1005, '2026-10-18', 'VPN Config', 'Logistics Co', '07705556666', 'John Doe', 'Closed', '2026-10-19'],
            [10024, '2026-10-24', 'Onboarding - Global Tech', 'Global Tech Solutions', '07801231234', 'John Doe', 'In Process', '2026-10-27'],
            [99283, '2026-10-22', 'Server Connection Issue', 'Acme Corp', '07701234567', 'John Doe', 'In Process', '2026-10-24'],
            [10045, '2026-10-21', 'Database Migration', 'StartUp Inc', '07505554444', 'John Doe', 'Pending', '2026-10-22'],
        ];

        foreach ($rows as $r) {
            Ticket::create([
                'ticket_number' => $r[0],
                'opened_on' => $r[1],
                'subject' => $r[2],
                'company' => $r[3],
                'mobile' => $r[4],
                'employee' => $r[5],
                'status' => $r[6],
                'due_on' => $r[7],
                'client_name' => match ($r[3]) {
                    'Acme Corp' => 'John Doe',
                    'Tech Global' => 'Sarah Smith',
                    'Finance LLC' => 'Mike Ross',
                    'Global Tech Solutions' => 'Global Tech Solutions',
                    'StartUp Inc' => 'StartUp Inc',
                    default => $r[3],
                },
                'priority' => 'Normal',
                'payment_ok' => $r[0] !== 10045,
                'product' => $r[0] === 10024 ? 'Enterprise CRM License' : ($r[0] === 99283 ? 'Cloud Hosting Plan (Tier 2)' : null),
                'workflow_step' => 1,
            ]);
        }

        $t10024 = Ticket::where('ticket_number', 10024)->first();
        if ($t10024) {
            foreach (['Verify Contract', 'Setup Server Env', 'Create Admin User', 'Send Welcome Email'] as $i => $title) {
                TicketTask::create(['ticket_id' => $t10024->id, 'title' => $title, 'sort_order' => $i]);
            }
            TicketMessage::create(['ticket_id' => $t10024->id, 'kind' => 'client', 'body' => 'Hi, we need onboarding assistance.', 'meta_time' => '10:00 AM']);
            TicketMessage::create(['ticket_id' => $t10024->id, 'kind' => 'agent', 'body' => 'Hello, we have started the onboarding checklist.', 'meta_time' => '10:15 AM']);
        }

        $t99283 = Ticket::where('ticket_number', 99283)->first();
        if ($t99283) {
            foreach (['Check Logs', 'Restart Service', 'Reply to Client'] as $i => $title) {
                TicketTask::create(['ticket_id' => $t99283->id, 'title' => $title, 'sort_order' => $i]);
            }
        }

        $t1005 = Ticket::where('ticket_number', 1005)->first();
        if ($t1005) {
            TicketMessage::create(['ticket_id' => $t1005->id, 'kind' => 'client', 'body' => 'VPN issue resolved earlier.', 'meta_time' => '09:00 AM']);
        }

        $portal = Ticket::where('ticket_number', 1001)->first();
        if ($portal) {
            $portal->client_email = 'john@acme.com';
            $portal->save();
            TicketMessage::create(['ticket_id' => $portal->id, 'kind' => 'client', 'body' => 'Hi, I cannot connect to the server.', 'meta_time' => '10:00 AM']);
            TicketMessage::create(['ticket_id' => $portal->id, 'kind' => 'agent', 'body' => 'Hello John, we are looking into it.', 'meta_time' => '10:15 AM']);
        }

        Ticket::where('ticket_number', 10024)->update(['priority' => 'High']);
        Ticket::where('ticket_number', 99283)->update(['priority' => 'Normal']);
        Ticket::where('ticket_number', 10045)->update(['priority' => 'High']);
    }
}
