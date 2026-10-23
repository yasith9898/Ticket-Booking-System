<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CRM Pipeline | Sales & Leads</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #2ecc71;
            --danger: #e74c3c;
            --bg-light: #f4f6f9;
            --white: #ffffff;
            --border: #e0e0e0;
            --column-bg: #ebecf0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            display: flex;
            height: 100vh;
            background-color: var(--bg-light);
            overflow: hidden;
        }

        /* Sidebar */
        .sidebar { width: 250px; background-color: var(--primary); color: var(--white); display: flex; flex-direction: column; padding: 20px 0; flex-shrink: 0; }
        .logo { font-size: 20px; font-weight: bold; text-align: center; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; }
        .menu-item { padding: 15px 25px; cursor: pointer; display: flex; align-items: center; color: var(--white); text-decoration: none;}
        .menu-item:hover, .menu-item.active { background-color: rgba(255,255,255,0.1); border-left: 4px solid var(--secondary); }
        .menu-category { font-size: 12px; text-transform: uppercase; color: #bdc3c7; padding: 20px 25px 5px; }

        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .top-bar { background-color: var(--white); height: 60px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 30px; flex-shrink: 0; }

        /* VIEWS */
        .view-section { display: none; height: 100%; animation: fadeIn 0.3s; }
        .active-view { display: block; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* PIPELINE (KANBAN) STYLES */
        .board-container { padding: 20px; overflow-x: auto; height: 100%; display: flex; align-items: flex-start; gap: 20px; box-sizing: border-box; }
        .column { background-color: var(--column-bg); min-width: 280px; width: 280px; border-radius: 8px; padding: 10px; display: flex; flex-direction: column; max-height: 90%; }
        
        .column-header { font-weight: bold; padding: 10px; display: flex; justify-content: space-between; align-items: center; color: #172b4d; }
        
        .card-list { flex: 1; overflow-y: auto; min-height: 100px; padding-bottom: 10px; }
        
        .client-card { 
            background-color: var(--white); 
            padding: 15px; 
            margin: 5px 0; 
            border-radius: 4px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.12); 
            cursor: pointer; 
            border-left: 4px solid var(--secondary); 
            transition: transform 0.1s;
        }
        .client-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .client-card:active { cursor: grabbing; }

        .add-column-btn { min-width: 280px; height: 50px; background: rgba(0,0,0,0.05); border: 2px dashed #ccc; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #666; font-weight: bold; }
        .add-column-btn:hover { background: rgba(0,0,0,0.1); }

        /* MODAL STYLES */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 25px; border-radius: 8px; width: 450px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto; }
        .modal-large { width: 600px; }

        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #34495e; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;}

        /* Grid for the Add Form */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        /* Buttons */
        .btn-save { background: var(--secondary); color: white; padding: 8px 20px; border: none; border-radius: 4px; cursor: pointer; float: right; }
        .btn-cancel { background: #ddd; color: #333; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; float: right; margin-right: 10px; }
        .btn-add-mini { background:none; border:1px solid #bdc3c7; color: #2c3e50; border-radius:4px; cursor:pointer; font-weight:bold; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; }
        .btn-add-mini:hover { background-color: #ecf0f1; }

        /* Toggle Buttons */
        .toggle-container { display: flex; gap: 10px; margin-bottom: 15px; }
        .toggle-btn { flex: 1; padding: 8px; text-align: center; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .toggle-btn.active { background: var(--secondary); color: white; border-color: var(--secondary); }

        /* Details Section in History Modal */
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .detail-item strong { display: block; font-size: 11px; color: #7f8c8d; text-transform: uppercase; }
        
        .history-table { width: 100%; font-size: 13px; margin-top: 10px; border: 1px solid #eee; border-collapse: collapse; }
        .history-table th { background: #f1f1f1; padding: 8px; border-bottom: 2px solid #ddd; text-align: left;}
        .history-table td { padding: 8px; border-bottom: 1px solid #eee; vertical-align: top;}
        .empty-history { text-align: center; color: #888; padding: 20px; font-style: italic; }

    </style>
</head>
<body>

     <nav class="sidebar">
        <div class="logo">Unified System</div>
        
        <div class="menu-category">CRM Module</div>
        <div class="menu-item active" id="menu-pipeline"> Pipeline Board</div>
        <div class="menu-item" onclick="window.location.href='{{ route('crm-reports') }}'"> 
   CRM Report 
</div>
        <div class="menu-category">Support Module</div>
       <div class="menu-item" onclick="window.location.href='{{ route('ticket-dashboard') }}'"> 
   Ticket System
</div>
   <div class="menu-item" onclick="window.location.href='{{ route('create-ticket') }}'"> 
   Create Ticket
</div>

   <div class="menu-item" onclick="window.location.href='{{ route('my-ticket') }}'"> 
   My Teckit
</div>
    <div class="menu-item" onclick="window.location.href='{{ route('reports-by-employee') }}'"> 
   Ticket By Employee
   
</div>

        </nav>

    <main class="main-content">
        <div class="top-bar">
            <h2 id="page-title">Client Pipeline</h2>
            <div style="display:flex;align-items:center;gap:14px;">
                <span>{{ Auth::user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" style="margin:0;display:inline;">
                    @csrf
                    <button type="submit" style="background:none;border:none;color:#3498db;cursor:pointer;font-size:13px;padding:0;">Logout</button>
                </form>
            </div>
        </div>

        <div id="view-pipeline" class="view-section active-view">
            <div class="board-container" id="board">
                
                <div class="column">
                    <div class="column-header">
                        New Lead / Client
                        <button class="btn-add-mini" onclick="openAddModal()" title="Add New Lead">+</button>
                    </div>
                    <div class="card-list" id="col-new" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
                </div>

                <div class="column">
                    <div class="column-header">Prospect / Potential</div>
                    <div class="card-list" id="col-prospect" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
                </div>

                <div class="column">
                    <div class="column-header">Interested</div>
                    <div class="card-list" id="col-interested" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
                </div>

                <div class="column">
                    <div class="column-header">Negotiation / Progress</div>
                    <div class="card-list" id="col-negotiation" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
                </div>

                <div class="column">
                    <div class="column-header">醇 Closed Won</div>
                    <div class="card-list" id="col-won" ondrop="drop(event)" ondragover="allowDrop(event)" style="background: rgba(46, 204, 113, 0.1);"></div>
                </div>

                <div class="column">
                    <div class="column-header">笶Closed Lost</div>
                    <div class="card-list" id="col-lost" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
                </div>

                <div class="add-column-btn" onclick="addCategory()">+ Add Category</div>

            </div>
        </div>

    </main>

    <div id="addClientModal" class="modal-overlay">
        <div class="modal-content">
            <h3 style="margin-top:0;">Add To Pipeline</h3>
            
            <div class="toggle-container">
                <div class="toggle-btn active" id="btn-new" onclick="toggleAddMode('new')">Add New Client</div>
                <div class="toggle-btn" id="btn-exist" onclick="toggleAddMode('exist')">Select Existing</div>
            </div>

            <div id="form-existing" style="display:none;">
                <div class="form-group">
                    <label>Select Client from Database</label>
                    <select id="exist-select" onchange="fillExistingData()">
                        <option value="">-- Choose Client --</option>
                    </select>
                </div>
            </div>

            <div id="form-new">
                <div class="form-group">
                    <label>Person Name</label>
                    <input type="text" id="new-name" placeholder="e.g. Ali Ahmed">
                </div>
                
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" id="new-company" placeholder="e.g. Sunshine Ltd">
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" id="new-mobile" placeholder="+964...">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" id="new-category" placeholder="e.g. Retail, Tech">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" id="new-city" placeholder="e.g. Baghdad">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" id="new-address" placeholder="e.g. 14th St">
                    </div>
                </div>
            </div>

            <hr style="border:0; border-top:1px dashed #ddd; margin:15px 0;">
            <div class="form-group">
                <label>Assign to Sales Employee</label>
                <select id="new-salesrep">
                    <option value="Unassigned">-- Unassigned --</option>
                    <option value="Ahmed Sales">Ahmed Sales</option>
                    <option value="Sara Rep">Sara Rep</option>
                    <option value="Mike Deal">Mike Deal</option>
                    <option value="Team Lead">Team Lead</option>
                </select>
            </div>

            <div style="overflow: hidden; margin-top:10px;">
                <button class="btn-save" onclick="saveNewClient()">Add Lead</button>
                <button class="btn-cancel" onclick="closeModal('addClientModal')">Cancel</button>
            </div>
        </div>
    </div>

    <div id="moveModal" class="modal-overlay">
        <div class="modal-content">
            <h3 id="modal-title">Update Stage</h3>
            <p id="modal-desc">Please record the details of this move.</p>
            
            <div class="form-group">
                <label>Date</label>
                <input type="date" id="moveDate">
            </div>

            <div class="form-group">
                <label>Reason / Notes</label>
                <textarea id="moveReason" rows="3" placeholder="Enter reason..."></textarea>
            </div>

            <div style="overflow: hidden;">
                <button class="btn-save" onclick="confirmMove()">Save Update</button>
                <button class="btn-cancel" onclick="closeModal('moveModal')">Cancel</button>
            </div>
        </div>
    </div>

    <div id="historyModal" class="modal-overlay">
        <div class="modal-content modal-large">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px;">
                <div>
                    <h3 style="margin:0;" id="history-name">Client Name</h3>
                    <small style="color:#666;" id="history-company">Company Name</small>
                </div>
                <button onclick="closeModal('historyModal')" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">&times;</button>
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <strong>Sales Representative</strong>
                    <span id="detail-salesrep" style="color:#2980b9; font-weight:bold;">-</span>
                </div>
                <div class="detail-item">
                    <strong>Mobile</strong>
                    <span id="detail-mobile">-</span>
                </div>
                <div class="detail-item">
                    <strong>Category</strong>
                    <span id="detail-category">-</span>
                </div>
                <div class="detail-item">
                    <strong>City / Address</strong>
                    <span id="detail-location">-</span>
                </div>
            </div>
            
            <label style="font-weight:bold; font-size:14px; color:#2c3e50;">Statement of Progress / Notes:</label>
            <div id="history-content">
                </div>

            <div style="margin-top:20px; overflow:hidden;">
                <button class="btn-save" onclick="closeModal('historyModal')">Close</button>
            </div>
        </div>
    </div>

    <script>
        const ROUTES = {
            lead: @json(route('pipeline.lead')),
            move: @json(route('pipeline.move')),
            column: @json(route('pipeline.column')),
            clients: @json(route('pipeline.clients')),
            ticketDashboard: @json(route('ticket-dashboard')),
        };
        const SERVER_BOOT = @json($bootstrap);

        let draggedCardId = null;
        let sourceColId = null;
        let targetColId = null;
        let isWonMove = false;
        let addMode = 'new';
        let clientHistory = {};
        let clientDB = {};

        function csrfHeaders() {
            const t = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': t,
                'X-Requested-With': 'XMLHttpRequest'
            };
        }

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s == null ? '' : String(s);
            return d.innerHTML;
        }

        function hydrateFromServer() {
            clientDB = {};
            const cards = SERVER_BOOT.cards || {};
            for (const [cid, row] of Object.entries(cards)) {
                clientDB[cid] = {
                    name: row.name,
                    company: row.company,
                    mobile: row.mobile || '',
                    category: row.category || '',
                    city: row.city || '',
                    address: row.address || '',
                    salesRep: row.salesRep || 'Unassigned',
                    column_key: row.column_key
                };
            }
            clientHistory = SERVER_BOOT.history || {};
        }

        function renderExtraColumns() {
            const board = document.getElementById('board');
            const btn = document.querySelector('.add-column-btn');
            (SERVER_BOOT.extraColumns || []).forEach(col => {
                const colId = col.column_key;
                if (document.getElementById(colId)) return;
                const newCol = document.createElement('div');
                newCol.className = 'column';
                newCol.innerHTML = '<div class="column-header">' + escapeHtml(col.title) + '</div><div class="card-list" id="' + colId + '" ondrop="drop(event)" ondragover="allowDrop(event)"></div>';
                board.insertBefore(newCol, btn);
            });
        }

        function renderBoard() {
            document.querySelectorAll('#board .card-list').forEach(list => { list.innerHTML = ''; });
            for (const [cid, data] of Object.entries(clientDB)) {
                const col = document.getElementById(data.column_key);
                if (!col) continue;
                const el = document.createElement('div');
                el.className = 'client-card';
                el.id = cid;
                el.draggable = true;
                el.setAttribute('data-id', cid);
                el.setAttribute('ondragstart', 'drag(event)');
                el.setAttribute('onclick', 'openHistory(this)');
                el.innerHTML = '<strong>' + escapeHtml(data.name) + '</strong><br><small>' + escapeHtml(data.company) + '</small>';
                col.appendChild(el);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('moveDate').valueAsDate = new Date();
            hydrateFromServer();
            renderExtraColumns();
            renderBoard();
        });

        async function openAddModal() {
            document.getElementById('new-name').value = '';
            document.getElementById('new-company').value = '';
            document.getElementById('new-mobile').value = '';
            document.getElementById('new-category').value = '';
            document.getElementById('new-city').value = '';
            document.getElementById('new-address').value = '';
            document.getElementById('new-salesrep').value = 'Unassigned';

            const select = document.getElementById('exist-select');
            select.innerHTML = '<option value="">-- Choose Client --</option>';
            try {
                const res = await fetch(ROUTES.clients, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } });
                const rows = await res.json();
                rows.forEach(function(r) {
                    const opt = document.createElement('option');
                    opt.value = r.id;
                    opt.textContent = r.label;
                    select.appendChild(opt);
                });
            } catch (e) { console.error(e); }

            toggleAddMode('new');
            document.getElementById('addClientModal').style.display = 'flex';
        }

        function toggleAddMode(mode) {
            addMode = mode;
            document.getElementById('btn-new').className = mode === 'new' ? 'toggle-btn active' : 'toggle-btn';
            document.getElementById('btn-exist').className = mode === 'exist' ? 'toggle-btn active' : 'toggle-btn';
            document.getElementById('form-new').style.display = mode === 'new' ? 'block' : 'none';
            document.getElementById('form-existing').style.display = mode === 'exist' ? 'block' : 'none';
        }

        function fillExistingData() {}

        async function saveNewClient() {
            const salesRep = document.getElementById('new-salesrep').value;
            let payload = { mode: addMode, sales_rep: salesRep };

            if (addMode === 'new') {
                payload.person_name = document.getElementById('new-name').value;
                payload.company = document.getElementById('new-company').value;
                payload.mobile = document.getElementById('new-mobile').value;
                payload.category = document.getElementById('new-category').value;
                payload.city = document.getElementById('new-city').value;
                payload.address = document.getElementById('new-address').value;
                if (!payload.person_name || !payload.company) { alert("Name and Company are required!"); return; }
            } else {
                const id = document.getElementById('exist-select').value;
                if (!id) { alert("Please select a client."); return; }
                payload.mode = 'exist';
                payload.existing_client_id = parseInt(id, 10);
            }

            try {
                const res = await fetch(ROUTES.lead, { method: 'POST', headers: csrfHeaders(), body: JSON.stringify(payload) });
                const data = await res.json();
                if (!res.ok) {
                    alert((data && data.message) ? data.message : 'Could not save lead.');
                    return;
                }
                const cid = data.card_id;
                const row = data.card_db[cid];
                row.column_key = 'col-new';
                clientDB[cid] = row;
                clientHistory[cid] = [data.history_entry];
                renderBoard();
                closeModal('addClientModal');
            } catch (e) {
                alert('Network error');
                console.error(e);
            }
        }

        function allowDrop(ev) { ev.preventDefault(); }
        function drag(ev) {
            draggedCardId = ev.target.id;
            sourceColId = ev.target.parentElement.id;
            ev.dataTransfer.setData("text", ev.target.id);
        }
        function drop(ev) {
            ev.preventDefault();
            const targetList = ev.target.closest('.card-list');
            if (!targetList) return;
            targetColId = targetList.id;
            if (targetColId === sourceColId) return;

            if (targetColId === 'col-won') {
                isWonMove = true;
                document.getElementById('modal-title').innerText = "Deal Won!";
                document.getElementById('modal-desc').innerText = "Record details. This will auto-create a ticket.";
            } else {
                isWonMove = false;
                document.getElementById('modal-title').innerText = "Update Stage";
                document.getElementById('modal-desc').innerText = "Please record the details of this move.";
            }
            document.getElementById('moveModal').style.display = 'flex';
        }

        async function confirmMove() {
            const stageName = document.getElementById(targetColId).previousElementSibling.innerText.replace('+', '').trim();
            const reason = document.getElementById('moveReason').value;
            const date = document.getElementById('moveDate').value;

            const payload = {
                card_id: draggedCardId,
                target_column_key: targetColId,
                happened_on: date,
                note: reason,
                stage_name: stageName,
                create_ticket: isWonMove
            };

            try {
                const res = await fetch(ROUTES.move, { method: 'POST', headers: csrfHeaders(), body: JSON.stringify(payload) });
                const data = await res.json();
                if (!res.ok) {
                    alert('Could not save this move.');
                    return;
                }
                const card = document.getElementById(draggedCardId);
                document.getElementById(targetColId).appendChild(card);
                if (!clientHistory[draggedCardId]) clientHistory[draggedCardId] = [];
                clientHistory[draggedCardId].unshift({ date: date, stage: stageName, note: reason || "No notes added." });
                if (clientDB[draggedCardId]) clientDB[draggedCardId].column_key = targetColId;

                if (isWonMove) {
                    alert("Deal Closed! \nRedirecting to Ticket Dashboard...");
                    window.location.href = ROUTES.ticketDashboard;
                    return;
                }
                closeModal('moveModal');
            } catch (e) {
                alert('Network error');
                console.error(e);
            }
        }

        function openHistory(cardElement) {
            const cardId = cardElement.id;
            const data = clientDB[cardId] || { name: 'Unknown', company: 'Unknown' };
            const historyData = clientHistory[cardId] || [];

            document.getElementById('history-name').innerText = data.name;
            document.getElementById('history-company').innerText = data.company;
            document.getElementById('detail-salesrep').innerText = data.salesRep || "Unassigned";
            document.getElementById('detail-mobile').innerText = data.mobile || '-';
            document.getElementById('detail-category').innerText = data.category || '-';
            document.getElementById('detail-location').innerText = (data.city || '') + ' ' + (data.address || '');

            const contentDiv = document.getElementById('history-content');
            if (historyData.length === 0) {
                contentDiv.innerHTML = '<div class="empty-history">No records found.</div>';
            } else {
                let html = '<table class="history-table"><thead><tr><th width="20%">Date</th><th width="30%">Stage</th><th width="50%">Notes</th></tr></thead><tbody>';
                historyData.forEach(function(item) {
                    html += '<tr><td>' + escapeHtml(item.date) + '</td><td><strong>' + escapeHtml(item.stage) + '</strong></td><td>' + escapeHtml(item.note) + '</td></tr>';
                });
                html += '</tbody></table>';
                contentDiv.innerHTML = html;
            }

            document.getElementById('historyModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            if (modalId === 'moveModal') {
                document.getElementById('moveReason').value = "";
                draggedCardId = null;
                targetColId = null;
            }
        }

        async function addCategory() {
            const name = prompt("Enter new Category Name:");
            if (!name) return;
            try {
                const res = await fetch(ROUTES.column, { method: 'POST', headers: csrfHeaders(), body: JSON.stringify({ title: name }) });
                const data = await res.json();
                if (!res.ok) return;
                const board = document.getElementById('board');
                const btn = document.querySelector('.add-column-btn');
                const colId = data.column_key;
                const newCol = document.createElement('div');
                newCol.className = 'column';
                newCol.innerHTML = '<div class="column-header">' + escapeHtml(data.title) + '</div><div class="card-list" id="' + colId + '" ondrop="drop(event)" ondragover="allowDrop(event)"></div>';
                board.insertBefore(newCol, btn);
            } catch (e) {
                console.error(e);
            }
        }
    </script>
</body>
</html>