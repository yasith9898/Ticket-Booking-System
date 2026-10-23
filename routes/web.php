<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\MyTicketController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketWebController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/client-submit', [ClientPortalController::class, 'submitForm'])->name('client-submit');
Route::post('/client-submit', [ClientPortalController::class, 'submitStore'])->name('client-submit.store');

Route::get('/client-portal', [ClientPortalController::class, 'portal'])->name('client-portal');
Route::get('/portal/tickets', [ClientPortalController::class, 'ticketsJson'])->name('portal.tickets');
Route::post('/portal/tickets/{ticketNumber}/reply', [ClientPortalController::class, 'reply'])->whereNumber('ticketNumber')->name('portal.reply');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('pipeline'));

    Route::get('/pipeline', [PipelineController::class, 'show'])->name('pipeline');
    Route::post('/pipeline/leads', [PipelineController::class, 'storeLead'])->name('pipeline.lead');
    Route::post('/pipeline/move', [PipelineController::class, 'move'])->name('pipeline.move');
    Route::post('/pipeline/columns', [PipelineController::class, 'addColumn'])->name('pipeline.column');
    Route::get('/pipeline/clients', [PipelineController::class, 'clientsJson'])->name('pipeline.clients');

    Route::get('/ticket-dashboard', [TicketWebController::class, 'dashboard'])->name('ticket-dashboard');
    Route::get('/create-ticket', [TicketWebController::class, 'create'])->name('create-ticket');
    Route::post('/create-ticket', [TicketWebController::class, 'store'])->name('create-ticket.store');

    Route::get('/my-ticket', [MyTicketController::class, 'show'])->name('my-ticket');
    Route::get('/tickets/{ticketNumber}/detail', [MyTicketController::class, 'detailJson'])->whereNumber('ticketNumber')->name('tickets.detail');
    Route::post('/tickets/{ticketNumber}/message', [MyTicketController::class, 'addMessage'])->whereNumber('ticketNumber')->name('tickets.message');
    Route::post('/tickets/{ticketNumber}/task', [MyTicketController::class, 'addTask'])->whereNumber('ticketNumber')->name('tickets.task');
    Route::patch('/tickets/{ticketNumber}', [MyTicketController::class, 'update'])->whereNumber('ticketNumber')->name('tickets.patch');

    Route::get('/crm-reports', [ReportController::class, 'crmReports'])->name('crm-reports');
    Route::get('/reports-by-employee', [ReportController::class, 'reportsByEmployee'])->name('reports-by-employee');
});
