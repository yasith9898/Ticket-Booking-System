<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Unified CRM & Helpdesk System</title>
    <style>
        /* --- GLOBAL STYLES --- */
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --bg-light: #f4f6f9;
            --white: #ffffff;
            --border: #e0e0e0;
            --text-dark: #333;
        }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; height: 100vh; background-color: var(--bg-light); overflow: hidden; }
        
        /* --- SIDEBAR --- */
        .sidebar { width: 250px; background-color: var(--primary); color: var(--white); display: flex; flex-direction: column; padding: 20px 0; flex-shrink: 0; }
        .logo { font-size: 20px; font-weight: bold; text-align: center; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; }
        .menu-category { font-size: 11px; text-transform: uppercase; color: #95a5a6; padding: 0 25px; margin-bottom: 5px; margin-top: 20px;}
        .menu-item { padding: 12px 25px; cursor: pointer; display: flex; align-items: center; color: #bdc3c7; transition: 0.2s; border-left: 4px solid transparent;}
        .menu-item:hover { background-color: rgba(255,255,255,0.05); color: white; }
        .menu-item.active { background-color: rgba(255,255,255,0.1); border-left-color: var(--secondary); color: white; }

        /* --- MAIN LAYOUT --- */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; position: relative; }
        .top-bar { background-color: var(--white); height: 60px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 30px; }
        .user-profile { display: flex; align-items: center; gap: 10px; font-weight: bold; color: var(--primary); }

        /* --- PAGE MANAGEMENT --- */
        .page-view { display: none; height: 100%; animation: fadeIn 0.3s; overflow-y: auto; }
        .page-view.active-page { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        /* --- DASHBOARD STYLES --- */
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; padding: 30px; }
        .stat-card { 
            background: white; padding: 20px; border-radius: 8px; border: 1px solid var(--border); 
            box-shadow: 0 2px 4px rgba(0,0,0,0.03); cursor: pointer; transition: 0.2s; position: relative;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .stat-card.active-filter { border: 2px solid var(--secondary); background: #f0f8ff; }
        .stat-value { font-size: 24px; font-weight: bold; color: var(--primary); margin-top: 5px; }
        .stat-label { color: #7f8c8d; font-size: 13px; font-weight: 500; }
        
        .table-card { background: white; margin: 0 30px 30px; border-radius: 8px; border: 1px solid var(--border); overflow: hidden; }
        .table-header { padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background-color: #f8f9fa; color: #666; font-weight: 600; text-transform: uppercase; font-size: 12px; }
        tr:hover { background-color: #f9f9f9; }

        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .bg-high { background: #ffebee; color: #c0392b; }
        .bg-normal { background: #e3f2fd; color: #2980b9; }
        .bg-done { background: #e8f5e9; color: #2ecc71; }
        .btn-continue { background-color: var(--secondary); color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500; }
        .btn-continue:hover { background-color: #2980b9; }

        /* --- TICKET DETAIL STYLES --- */
        .detail-layout { display: flex; height: calc(100% - 60px); }
        .left-panel { flex: 1; display: flex; flex-direction: column; border-right: 1px solid var(--border); background: #fdfdfd; }
        .right-sidebar { width: 320px; background: white; padding: 20px; border-left: 1px solid var(--border); overflow-y: auto; display: flex; flex-direction: column; gap: 20px;}
        
        .stepper-container { padding: 20px; background: white; border-bottom: 1px solid var(--border); }
        .stepper { display: flex; justify-content: space-between; position: relative; }
        .stepper::before { content: ''; position: absolute; top: 15px; left: 0; width: 100%; height: 2px; background: #eee; z-index: 0; }
        .step { position: relative; z-index: 1; text-align: center; width: 25%; cursor: pointer; }
        .step-circle { width: 30px; height: 30px; background: #eee; color: #666; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 5px; border: 2px solid white; font-weight: bold; }
        .step.active .step-circle { background: var(--secondary); color: white; }
        .step.completed .step-circle { background: var(--success); color: white; }
        .step-label { font-size: 11px; color: #999; font-weight: bold; }
        
        .chat-area { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; }
        .msg { max-width: 80%; padding: 15px; border-radius: 8px; font-size: 14px; line-height: 1.5; }
        .msg-client { align-self: flex-start; background: white; border: 1px solid var(--border); border-top-left-radius: 0; }
        .msg-agent { align-self: flex-end; background: #e3f2fd; border-top-right-radius: 0; }
        .msg-internal { align-self: flex-end; background: #fff3cd; border: 1px solid #ffeeba; border-top-right-radius: 0; width: 80%; }
        .msg-meta { font-size: 11px; color: #888; margin-bottom: 4px; display: block; }
        
        .reply-section { padding: 20px; background: white; border-top: 1px solid var(--border); }
        .reply-tabs { display: flex; gap: 10px; margin-bottom: 10px; }
        .tab-btn { padding: 5px 12px; border-radius: 12px; font-size: 12px; cursor: pointer; border: 1px solid transparent; }
        .tab-btn.active-reply { background: var(--secondary); color: white; }
        .tab-btn.active-note { background: #f39c12; color: white; }
        .reply-input { width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 10px; min-height: 60px; box-sizing: border-box; }
        .internal-mode .reply-input { background-color: #fffaf0; border-color: #f39c12; }

        .sidebar-panel { border: 1px solid #eee; border-radius: 6px; padding: 15px; background: #fdfdfd; margin-bottom: 20px;}
        .sidebar-header { margin: 0 0 10px 0; font-size: 12px; color: #666; text-transform: uppercase; font-weight: bold; }
        .sidebar-select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        
        .payment-badge { display: inline-block; padding: 5px; width: 100%; text-align: center; border-radius: 4px; font-size: 12px; font-weight: bold; margin-top: 5px; box-sizing: border-box;}
        .paid { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .unpaid { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .timer-display { background: #2c3e50; color: #ecf0f1; padding: 8px 15px; border-radius: 20px; font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .timer-dot { width: 8px; height: 8px; background: #e74c3c; border-radius: 50%; animation: pulse 1s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }
        
        .upload-box { border: 2px dashed #bdc3c7; border-radius: 6px; padding: 15px; text-align: center; background: #fafafa; cursor: pointer; transition: 0.3s; }
        .upload-box:hover { background: #fff; border-color: var(--secondary); }
        .upload-icon { font-size: 24px; color: #95a5a6; margin-bottom: 5px; display: block; }
        .upload-preview { max-width: 100%; border-radius: 4px; margin-top: 10px; display: none; border: 1px solid #ddd; }
        input[type="file"] { display: none; }

        /* Checklist & Progress */
        .task-progress-container { background: #e0e0e0; height: 6px; border-radius: 3px; margin-bottom: 10px; overflow: hidden; }
        .task-progress-fill { background: var(--success); height: 100%; width: 0%; transition: width 0.3s; }
        .task-list { list-style: none; padding: 0; margin: 0; }
        .task-item { display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px solid #eee; font-size: 13px; }
        .task-item.completed span { text-decoration: line-through; color: #999; }
        .add-task-row { display: flex; gap: 5px; margin-top: 10px; }
        .add-task-input { flex: 1; padding: 5px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; }
        .btn-add-task { padding: 5px 10px; background: var(--secondary); color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }

    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="logo">Unified System</div>
        <div class="menu-category">Employee Workspace</div>
        <div class="menu-item active" id="nav-dashboard" onclick="navigateTo('page-employee')">📂 My Tickets</div>
        <div class="menu-item" id="nav-completed" onclick="navigateTo('page-completed')">✅ My Completed Tasks</div>
        
        <div class="menu-category">CRM & Sales</div>
        <div class="menu-item" onclick="window.location.href='{{ route('pipeline') }}'">📊 Pipeline</div>
    </nav>

    <main class="main-content">
        
        <div id="page-employee" class="page-view active-page">
            <div class="top-bar">
                <h2 style="font-size: 18px; margin: 0;">My Task List</h2>
                <div class="user-profile">
                    <div style="background: #e3f2fd; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--secondary); margin-right: 10px;">{{ $userInitials }}</div>
                    <span>{{ $userName }}</span>
                </div>
            </div>

            <div class="stats-row">
                <div class="stat-card active-filter" id="filter-all" onclick="filterTable('all')">
                    <div class="stat-label">Assigned to Me</div>
                    <div class="stat-value">{{ $statTotal }}</div>
                </div>
                <div class="stat-card" id="filter-high" style="border-left: 4px solid #e74c3c;" onclick="filterTable('high')">
                    <div class="stat-label">High Priority</div>
                    <div class="stat-value" style="color: #e74c3c">{{ $statHigh }}</div>
                </div>
                <div class="stat-card" id="filter-blocked" style="border-left: 4px solid #f39c12;" onclick="filterTable('blocked')">
                    <div class="stat-label">Blocked (Unpaid)</div>
                    <div class="stat-value" style="color: #f39c12">{{ $statBlocked }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Avg Work Time</div>
                    <div class="stat-value" style="color: #27ae60">42m</div>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h3 style="margin: 0; font-size: 16px;" id="table-title">All Active Tasks</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th width="80">ID</th>
                            <th>Subject</th>
                            <th>Client</th>
                            <th>Priority</th>
                            <th>Payment</th>
                            <th width="100">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeTickets as $t)
                            @php
                                $pr = strtolower((string) $t->priority) === 'high' ? 'high' : 'normal';
                                $paid = $t->payment_ok;
                                $badgeClass = $pr === 'high' ? 'bg-high' : 'bg-normal';
                            @endphp
                            <tr class="ticket-row" data-priority="{{ $pr }}" data-status="{{ $paid ? 'paid' : 'unpaid' }}" @if(!$paid) style="background-color: #fffaf0;" @endif>
                                <td><strong>#{{ $t->ticket_number }}</strong></td>
                                <td>{{ $t->subject }}</td>
                                <td>{{ $t->company }}</td>
                                <td><span class="badge {{ $badgeClass }}">{{ $t->priority }}</span></td>
                                <td>
                                    @if($paid)
                                        <span style="color: #27ae60; font-weight: bold; font-size: 12px;">✔ Paid</span>
                                    @else
                                        <span style="color: #e74c3c; font-weight: bold; font-size: 12px;">✖ Unpaid</span>
                                    @endif
                                </td>
                                <td>
                                    @if($paid)
                                        <button type="button" class="btn-continue" onclick="loadTicket('{{ $t->ticket_number }}')">Continue</button>
                                    @else
                                        <button type="button" class="btn-continue" style="background:#ccc; cursor:not-allowed;" disabled>Locked</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="page-completed" class="page-view">
            <div class="top-bar">
                <h2 style="font-size: 18px; margin: 0;">Completed Tasks</h2>
                <div class="user-profile"><span>{{ $userName }}</span></div>
            </div>
            
            <div style="padding: 30px;">
                <div class="table-card" style="margin:0;">
                    <div class="table-header">
                        <h3 style="margin: 0; font-size: 16px;">History Log</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Client</th>
                                <th>Completed Date</th>
                                <th>Total Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($completedTickets as $t)
                                <tr>
                                    <td><strong>#{{ $t->ticket_number }}</strong></td>
                                    <td>{{ $t->subject }}</td>
                                    <td>{{ $t->company }}</td>
                                    <td>{{ $t->updated_at->format('M j, Y') }}</td>
                                    <td>—</td>
                                    <td><span class="badge bg-done">Closed</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" style="text-align:center;color:#999;">No completed tickets.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="page-ticket-detail" class="page-view">
            <div class="top-bar">
                <h3 id="detail-subject" style="margin:0;">Loading...</h3>
                <div class="timer-display">
                    <div class="timer-dot"></div>
                    <span id="live-timer">00:00:00</span>
                </div>
                <div>
                    <button style="background:transparent; border:none; color:#666; margin-right:15px; cursor:pointer" onclick="navigateTo('page-employee')">Cancel</button>
                    <button class="btn-continue" style="background: var(--success);" onclick="attemptClose()">✅ Close Ticket</button>
                </div>
            </div>

            <div class="detail-layout">
                <div class="left-panel">
                    
                    <div class="stepper-container">
                        <div class="stepper">
                            <div class="step completed" onclick="setStep(0)"><div class="step-circle">✔</div><div class="step-label">Open</div></div>
                            <div class="step active" id="s1" onclick="setStep(1)"><div class="step-circle">2</div><div class="step-label">In Process</div></div>
                            <div class="step" id="s2" onclick="setStep(2)"><div class="step-circle">3</div><div class="step-label">Delivered</div></div>
                            <div class="step" id="s3" onclick="setStep(3)"><div class="step-circle">4</div><div class="step-label">Done</div></div>
                        </div>
                    </div>

                    <div class="chat-area" id="chat-feed"></div>

                    <div class="reply-section" id="reply-section">
                        <div class="reply-tabs">
                            <div class="tab-btn active-reply" id="tab-reply" onclick="toggleMode('reply')">📧 Reply to Client</div>
                            <div class="tab-btn" id="tab-note" onclick="toggleMode('note')">🔒 Internal Note</div>
                        </div>
                        <textarea class="reply-input" id="msg-input" placeholder="Type your reply..."></textarea>
                        <div style="text-align: right; margin-top:10px;">
                            <button class="btn-continue" onclick="sendMessage()">Send Update</button>
                        </div>
                    </div>
                </div>

                <div class="right-sidebar">
                    
                    <div>
                        <h4 class="sidebar-header">Client Details</h4>
                        <div id="detail-client" style="font-weight:bold; font-size:16px;">Loading...</div>
                        
                        <div style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px; border: 1px solid #eee;">
                            <span style="font-size:11px; color:#999; text-transform:uppercase; font-weight:bold;">Product Sold:</span>
                            <div id="detail-product" style="font-weight:600; color:var(--primary);">Loading...</div>
                        </div>

                        <div style="border-top:1px solid #eee; margin-top:10px; padding-top:10px;">
                            <span style="font-size:12px; color:#666">Payment Status:</span>
                            <div id="payment-badge" class="payment-badge paid" onclick="togglePayment()">PAID - VERIFIED</div>
                        </div>
                    </div>

                    <div class="sidebar-panel">
                        <h4 class="sidebar-header">📌 Current Status</h4>
                        <select id="status-select" class="sidebar-select" onchange="changeStatusFromDropdown()">
                            <option value="0">Open</option>
                            <option value="1" selected>In Process</option>
                            <option value="2">Delivered</option>
                            <option value="3">Done</option>
                        </select>
                    </div>

                    <div class="sidebar-panel">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                            <h4 class="sidebar-header" style="margin:0;">✅ Required Tasks</h4>
                            <span id="task-percent" style="font-size:11px; font-weight:bold; color:var(--success);">0%</span>
                        </div>
                        <div class="task-progress-container">
                            <div class="task-progress-fill" id="task-progress-fill"></div>
                        </div>
                        <ul class="task-list" id="task-list"></ul>
                        <div class="add-task-row">
                            <input type="text" id="new-task-text" class="add-task-input" placeholder="+ Add task...">
                            <button class="btn-add-task" onclick="addTask()">Add</button>
                        </div>
                    </div>

                    <div class="sidebar-panel">
                        <h4 class="sidebar-header">📄 Delivery Note / Proof</h4>
                        <label for="dn-upload" class="upload-box">
                            <span class="upload-icon">☁️</span>
                            <div style="font-size:12px; color:#666">Click to Upload</div>
                        </label>
                        <input type="file" id="dn-upload" accept="image/*" onchange="previewImage(event)">
                        <img id="dn-preview" class="upload-preview" src="" alt="Delivery Note">
                        <div id="dn-status" style="display:none; text-align:center; font-size:11px; color:green; margin-top:5px;">✔ Image Attached</div>
                    </div>

                    <div class="sidebar-panel" style="border-left: 4px solid #34495e;">
                        <h4 class="sidebar-header">🔄 Re-Assign Ticket</h4>
                        <select id="assign-select" class="sidebar-select" style="margin-bottom: 5px;">
                            <option value="me">Me (John Doe)</option>
                            <option value="Finance">Finance Dept</option>
                            <option value="Support2">Tier 2 Support</option>
                        </select>
                        <button class="btn-continue" style="width:100%; background:#34495e;" onclick="reassignTicket()">Transfer</button>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <script>
        let currentTicketNumber = null;
        let timerInterval, secondsElapsed = 0, isPaid = true, msgMode = 'reply';

        function csrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s == null ? '' : String(s);
            return d.innerHTML;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const id = localStorage.getItem('currentTicketId');
            if (id) {
                localStorage.removeItem('currentTicketId');
                loadTicket(id);
            }
        });

        function filterTable(criteria) {
            document.querySelectorAll('.stat-card').forEach(c => c.classList.remove('active-filter'));
            let title = "";
            if(criteria === 'all') { document.getElementById('filter-all').classList.add('active-filter'); title = "All Active Tasks"; } 
            else if (criteria === 'high') { document.getElementById('filter-high').classList.add('active-filter'); title = "High Priority Tasks"; } 
            else if (criteria === 'blocked') { document.getElementById('filter-blocked').classList.add('active-filter'); title = "Blocked / Unpaid Tasks"; }
            
            document.getElementById('table-title').innerText = title;
            document.querySelectorAll('.ticket-row').forEach(row => {
                const p = row.getAttribute('data-priority'), s = row.getAttribute('data-status');
                if(criteria === 'all') row.style.display = '';
                else if (criteria === 'high') row.style.display = (p === 'high') ? '' : 'none';
                else if (criteria === 'blocked') row.style.display = (s === 'unpaid') ? '' : 'none';
            });
        }

        function navigateTo(pageId) {
            document.querySelectorAll('.page-view').forEach(p => p.classList.remove('active-page'));
            document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
            
            document.getElementById(pageId).classList.add('active-page');
            
            if(pageId === 'page-employee') document.getElementById('nav-dashboard').classList.add('active');
            if(pageId === 'page-completed') document.getElementById('nav-completed').classList.add('active');

            if(pageId !== 'page-ticket-detail') stopTimer();
        }

        async function loadTicket(id) {
            try {
                const res = await fetch(`/tickets/${id}/detail`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (!res.ok) return;

                currentTicketNumber = String(id);
                document.getElementById('detail-subject').innerText = data.subject + " (#" + id + ")";
                document.getElementById('detail-client').innerText = data.client;
                document.getElementById('detail-product').innerText = data.product;

                isPaid = !!data.payment_ok;
                const b = document.getElementById('payment-badge');
                b.className = isPaid ? "payment-badge paid" : "payment-badge unpaid";
                b.innerText = isPaid ? "PAID - VERIFIED" : "UNPAID - PENDING";

                renderServerMessages(data.messages || []);

                const list = document.getElementById('task-list');
                list.innerHTML = '';
                (data.tasks || []).forEach(function(t) {
                    renderTaskRow(t.title, !!t.completed);
                });
                updateTaskProgress();

                const step = typeof data.workflow_step === 'number' ? data.workflow_step : 1;
                setStep(step);

                startTimer();
                navigateTo('page-ticket-detail');
            } catch (e) {
                console.error(e);
            }
        }

        function renderServerMessages(messages) {
            const feed = document.getElementById('chat-feed');
            feed.innerHTML = '';
            messages.forEach(function(m) {
                const div = document.createElement('div');
                const time = m.time || '';
                if (m.from === 'client') {
                    div.className = 'msg msg-client';
                    div.innerHTML = '<span class="msg-meta">Client • ' + escapeHtml(time) + '</span><div class="msg-bubble">' + escapeHtml(m.text) + '</div>';
                } else if (m.from === 'internal') {
                    div.className = 'msg msg-internal';
                    div.innerHTML = '<span class="msg-meta">Internal • ' + escapeHtml(time) + '</span>' + escapeHtml(m.text);
                } else {
                    div.className = 'msg msg-agent';
                    div.innerHTML = '<span class="msg-meta">Support • ' + escapeHtml(time) + '</span><div class="msg-bubble">' + escapeHtml(m.text) + '</div>';
                }
                feed.appendChild(div);
            });
            feed.scrollTop = feed.scrollHeight;
        }

        // --- NEW STATUS SELECTOR LOGIC ---
        function changeStatusFromDropdown() {
            const val = parseInt(document.getElementById('status-select').value);
            setStep(val); // Reuse existing stepper logic
        }

        function setStep(idx) {
            // Update Visual Stepper
            document.querySelectorAll('.step').forEach((s, i) => {
                s.className = i <= idx ? (i === idx ? 'step active' : 'step completed') : 'step';
                s.querySelector('.step-circle').innerHTML = i < idx ? '✔' : i+1;
            });
            // Update Dropdown to match (if clicked via stepper)
            document.getElementById('status-select').value = idx;
        }

        // --- REASSIGN FUNCTION (RESTORED) ---
        function reassignTicket() {
            const sel = document.getElementById('assign-select');
            if(sel.value === 'me') return alert("Select a different user.");
            if(prompt("Reason for transfer?")) {
                alert("Ticket Transferred Successfully.");
                navigateTo('page-employee');
            }
        }

        // --- EXISTING LOGIC ---
        function renderTaskRow(text, completed) {
            const list = document.getElementById('task-list');
            const li = document.createElement('li');
            li.className = 'task-item' + (completed ? ' completed' : '');
            li.innerHTML = '<input type="checkbox" ' + (completed ? 'checked ' : '') + 'onchange="toggleTask(this)"><span>' + escapeHtml(text) + '</span>';
            list.appendChild(li);
        }
        async function addTask() {
            const input = document.getElementById('new-task-text');
            const val = input.value.trim();
            if (!val || !currentTicketNumber) return;
            try {
                await fetch(`/tickets/${currentTicketNumber}/task`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ title: val })
                });
                renderTaskRow(val, false);
                input.value = "";
                updateTaskProgress();
            } catch (e) { console.error(e); }
        }
        function toggleTask(checkbox) {
            if(checkbox.checked) checkbox.parentElement.classList.add('completed');
            else checkbox.parentElement.classList.remove('completed');
            updateTaskProgress();
        }
        function updateTaskProgress() {
            const total = document.querySelectorAll('.task-item input').length;
            const checked = document.querySelectorAll('.task-item input:checked').length;
            const percent = total === 0 ? 0 : Math.round((checked / total) * 100);
            document.getElementById('task-progress-fill').style.width = percent + "%";
            document.getElementById('task-percent').innerText = percent + "%";
            return percent;
        }
        function startTimer() {
            secondsElapsed = 0; document.getElementById('live-timer').innerText = "00:00:00";
            clearInterval(timerInterval);
            timerInterval = setInterval(() => {
                secondsElapsed++;
                const h = Math.floor(secondsElapsed/3600).toString().padStart(2,'0'), m = Math.floor((secondsElapsed%3600)/60).toString().padStart(2,'0'), s = (secondsElapsed%60).toString().padStart(2,'0');
                document.getElementById('live-timer').innerText = `${h}:${m}:${s}`;
            }, 1000);
        }
        function stopTimer() { clearInterval(timerInterval); }
        function attemptClose() {
            if(!isPaid) { alert("⛔ Payment Unpaid."); return; }
            const percent = updateTaskProgress();
            if(percent < 100) { if(!confirm(`⚠ Tasks are only ${percent}% complete.\nAre you sure?`)) return; }
            stopTimer();
            if(confirm(`Complete Ticket?\nTime: ${document.getElementById('live-timer').innerText}`)) { alert("Ticket Closed!"); navigateTo('page-employee'); } else startTimer();
        }
        function previewImage(event) {
            const file = event.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('dn-preview'); img.src = e.target.result; img.style.display = "block";
                    document.getElementById('dn-status').style.display = "block";
                    addLog("attached a Delivery Note.");
                }
                reader.readAsDataURL(file);
            }
        }
        function togglePayment() {
            isPaid = !isPaid;
            const b = document.getElementById('payment-badge');
            b.className = isPaid ? "payment-badge paid" : "payment-badge unpaid";
            b.innerText = isPaid ? "PAID - VERIFIED" : "UNPAID - PENDING";
        }
        function toggleMode(mode) {
            msgMode = mode;
            const sec = document.getElementById('reply-section');
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active-reply', 'active-note'));
            if(mode === 'note') { document.getElementById('tab-note').classList.add('active-note'); sec.classList.add('internal-mode'); }
            else { document.getElementById('tab-reply').classList.add('active-reply'); sec.classList.remove('internal-mode'); }
        }
        async function sendMessage() {
            const txt = document.getElementById('msg-input').value;
            if (!txt || !currentTicketNumber) return;
            try {
                await fetch(`/tickets/${currentTicketNumber}/message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ body: txt, kind: msgMode === 'note' ? 'internal' : 'reply' })
                });
                addLog(txt, msgMode === 'note' ? 'internal' : 'agent');
                document.getElementById('msg-input').value = "";
            } catch (e) { console.error(e); }
        }
        function addLog(text, type='agent') {
            const feed = document.getElementById('chat-feed');
            const div = document.createElement('div');
            div.className = type === 'internal' ? 'msg msg-internal' : 'msg msg-agent';
            div.innerHTML = `<span class="msg-meta">${type === 'internal' ? 'Internal Note' : 'You'}</span>${text}`;
            feed.appendChild(div);
            feed.scrollTop = feed.scrollHeight;
        }
    </script>
</body>
</html>