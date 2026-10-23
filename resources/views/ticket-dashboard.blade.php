<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ticket Dashboard System</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --bg-light: #f4f6f9;
            --white: #ffffff;
            --border: #e0e0e0;
            
            /* Status Colors */
            --s-new: #2ecc71;      /* Green */
            --s-process: #3498db;  /* Blue */
            --s-overdue: #e74c3c;  /* Red */
            --s-closed: #95a5a6;   /* Gray */
            --s-pending: #f39c12;  /* Orange */
            --s-paid: #16a085;     /* Teal */
            --s-unpaid: #8e44ad;   /* Purple */
        }

        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; height: 100vh; background-color: var(--bg-light); overflow: hidden; }

        /* Sidebar */
        .sidebar { width: 250px; background-color: var(--primary); color: var(--white); display: flex; flex-direction: column; padding: 20px 0; flex-shrink: 0; }
        .logo { font-size: 20px; font-weight: bold; text-align: center; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; }
        .menu-item { padding: 15px 25px; cursor: pointer; display: flex; align-items: center; color: var(--white); text-decoration: none;}
        .menu-item:hover, .menu-item.active { background-color: rgba(255,255,255,0.1); border-left: 4px solid var(--secondary); }

        /* Main Content */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .top-bar { background-color: var(--white); height: 60px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; padding: 0 30px; flex-shrink: 0; }
        
        .scroll-area { padding: 30px; overflow-y: auto; flex: 1; }

        /* STATS ROW (7 Items) */
        .stats-row { 
            display: grid; grid-template-columns: repeat(7, 1fr); gap: 15px; margin-bottom: 30px; 
        }
        .stat-card { 
            background: white; padding: 15px; border-radius: 8px; border: 1px solid var(--border); 
            box-shadow: 0 2px 4px rgba(0,0,0,0.03); cursor: pointer; transition: 0.2s; position: relative; overflow: hidden;
            display: flex; flex-direction: column; justify-content: center;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .stat-card::after { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; }
        
        /* Stat Colors */
        .st-total::after { background: #333; }
        .st-closed::after { background: var(--s-closed); }
        .st-process::after { background: var(--s-process); }
        .st-overdue::after { background: var(--s-overdue); }
        .st-pending::after { background: var(--s-pending); }
        .st-paid::after { background: var(--s-paid); }
        .st-unpaid::after { background: var(--s-unpaid); }

        .stat-value { font-size: 22px; font-weight: bold; color: #333; margin-top: 5px; }
        .stat-label { color: #7f8c8d; font-size: 11px; font-weight: 700; text-transform: uppercase; }

        /* HEADER & SEARCH */
        .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 20px;}
        
        .search-container { flex: 1; position: relative; max-width: 400px; }
        .search-input { 
            width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 20px; 
            font-size: 14px; outline: none; padding-left: 35px;
        }
        .search-input:focus { border-color: var(--secondary); box-shadow: 0 0 5px rgba(52, 152, 219, 0.2); }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; }

        .btn-new { background: var(--secondary); color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; white-space: nowrap; }
        .btn-new:hover { background: #2980b9; }

        /* GRID VIEW (Cards) */
        .grid-view { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        
        .ticket-card { 
            background: white; border-radius: 8px; padding: 20px; border: 1px solid #eee; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.02); transition: transform 0.2s; cursor: pointer; position: relative;
        }
        .ticket-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-color: var(--secondary); }

        .t-top { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .t-id { font-weight: bold; color: #555; font-size: 13px; }
        .t-date { font-size: 12px; color: #999; }
        
        .t-subject { font-size: 16px; font-weight: bold; color: var(--primary); margin-bottom: 5px; }
        .t-client { font-size: 13px; color: #666; margin-bottom: 15px; }

        .t-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #eee; padding-top: 10px; }
        .t-badge { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; color: white; }

        /* TABLE VIEW (Hidden by default) */
        table { width: 100%; border-collapse: collapse; background: var(--white); box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background-color: #f8f9fa; color: #555; }
        tr:hover { background-color: #f9f9f9; cursor: pointer; }

        /* Status Badges */
        .bg-new { background: var(--s-new); }
        .bg-process { background: var(--s-process); }
        .bg-overdue { background: var(--s-overdue); }
        .bg-closed { background: var(--s-closed); }
        .bg-pending { background: var(--s-pending); }
        .bg-paid { background: var(--s-paid); }
        .bg-unpaid { background: var(--s-unpaid); }

    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="logo">Unified System</div>
        <div class="menu-item active">🎫 Ticket Dashboard</div>
        <div class="menu-item" onclick="window.location.href='{{ route('reports-by-employee') }}'">📊 Reports</div>
    </nav>

    <main class="main-content">
        <div class="top-bar">
            <h2 id="page-title">Ticket Dashboard</h2>
            <div>👤 {{ Auth::user()->name }}</div>
        </div>

        <div class="scroll-area">
            
            <div class="stats-row">
                <div class="stat-card st-total" onclick="renderCards('All')">
                    <div class="stat-value" id="count-total">0</div>
                    <div class="stat-label">Total Tickets</div>
                </div>
                <div class="stat-card st-closed" onclick="showTable('Closed')">
                    <div class="stat-value" id="count-closed">0</div>
                    <div class="stat-label">Closed</div>
                </div>
                <div class="stat-card st-process" onclick="showTable('In Process')">
                    <div class="stat-value" id="count-process">0</div>
                    <div class="stat-label">In Process</div>
                </div>
                <div class="stat-card st-overdue" onclick="showTable('Overdue')">
                    <div class="stat-value" id="count-overdue">0</div>
                    <div class="stat-label">Overdue</div>
                </div>
                <div class="stat-card st-pending" onclick="showTable('Pending')">
                    <div class="stat-value" id="count-pending">0</div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card st-paid" onclick="showTable('Paid')">
                    <div class="stat-value" id="count-paid">0</div>
                    <div class="stat-label">Paid</div>
                </div>
                <div class="stat-card st-unpaid" onclick="showTable('Unpaid')">
                    <div class="stat-value" id="count-unpaid">0</div>
                    <div class="stat-label">Unpaid</div>
                </div>
            </div>

            <div class="header-row">
                <h3 id="view-title" style="margin:0; color:#444; min-width:150px;">All Tickets</h3>
                
                <div class="search-container">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search by Name, Mobile, or Ticket #..." onkeyup="runSearch()">
                </div>

                <button class="btn-new" onclick="window.location.href='{{ route('create-ticket') }}'">+ New Ticket</button>
            </div>

            <div id="content-area" class="grid-view">
                </div>

        </div>
    </main>

    <script>
        const tickets = @json($ticketsJson);

        window.onload = function() {
            calculateStats();
            renderCards('All');
        };

        function calculateStats() {
            const counts = { Total: 0, Closed: 0, 'In Process': 0, Overdue: 0, Pending: 0, Paid: 0, Unpaid: 0 };
            
            tickets.forEach(t => {
                counts.Total++;
                if(counts[t.status] !== undefined) counts[t.status]++;
            });

            document.getElementById('count-total').innerText = counts.Total;
            document.getElementById('count-closed').innerText = counts.Closed;
            document.getElementById('count-process').innerText = counts['In Process'];
            document.getElementById('count-overdue').innerText = counts.Overdue;
            document.getElementById('count-pending').innerText = counts.Pending;
            document.getElementById('count-paid').innerText = counts.Paid;
            document.getElementById('count-unpaid').innerText = counts.Unpaid;
        }

        // --- SEARCH FUNCTION ---
        function runSearch() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            
            if(!query) {
                renderCards('All'); // Reset if empty
                return;
            }

            // Filter logic: ID, Client, Mobile
            const filtered = tickets.filter(t => 
                t.id.toString().includes(query) || 
                t.client.toLowerCase().includes(query) || 
                t.mobile.includes(query)
            );

            renderCards(null, filtered); // Render specific data
            document.getElementById('view-title').innerText = `Search Results (${filtered.length})`;
        }

        // VIEW 1: GRID OF CARDS (Modified to accept raw data)
        function renderCards(filter, customData = null) {
            const container = document.getElementById('content-area');
            container.innerHTML = '';
            container.className = 'grid-view';
            
            if(filter) {
                document.getElementById('view-title').innerText = filter === 'All' ? 'All Tickets' : filter + ' Tickets';
            }

            // Decide which data to use: Custom (Search) or Filtered Status
            let dataToRender;
            if (customData) {
                dataToRender = customData;
            } else {
                dataToRender = filter === 'All' ? tickets : tickets.filter(t => t.status === filter);
            }

            if(dataToRender.length === 0) {
                container.innerHTML = '<div style="color:#999; padding:20px; font-style:italic;">No tickets found.</div>';
                return;
            }

            dataToRender.forEach(t => {
                const badgeColor = getStatusColor(t.status);
                const html = `
                    <div class="ticket-card" onclick="goToMyTicket('${t.id}')">
                        <div class="t-top">
                            <span class="t-id">#${t.id}</span>
                            <span class="t-date">${t.date}</span>
                        </div>
                        <div class="t-subject">${t.subject}</div>
                        <div class="t-client">👤 ${t.client}</div>
                        <div class="t-footer">
                            <span style="font-size:12px; color:#777;">📱 ${t.mobile}</span>
                            <span class="t-badge ${badgeColor}">${t.status}</span>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
            });
        }

        // VIEW 2: TABLE
        function showTable(status) {
            const container = document.getElementById('content-area');
            container.innerHTML = '';
            container.className = ''; // Remove grid class
            document.getElementById('view-title').innerText = `Table View: ${status} Tickets`;

            const filteredData = tickets.filter(t => t.status === status);

            if(filteredData.length === 0) {
                container.innerHTML = '<div style="color:#999; padding:20px;">No tickets found.</div>';
                return;
            }

            let tableHTML = `
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Client</th>
                            <th>Mobile</th>
                            <th>Assigned</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            filteredData.forEach(t => {
                const badgeColor = getStatusColor(t.status);
                tableHTML += `
                    <tr onclick="goToMyTicket('${t.id}')">
                        <td>#${t.id}</td>
                        <td>${t.date}</td>
                        <td><strong>${t.subject}</strong></td>
                        <td>${t.client}</td>
                        <td>${t.mobile}</td>
                        <td>${t.employee}</td>
                        <td><span class="t-badge ${badgeColor}">${t.status}</span></td>
                    </tr>
                `;
            });

            tableHTML += '</tbody></table>';
            container.innerHTML = tableHTML;
        }

        function getStatusColor(status) {
            if(status === 'New') return 'bg-new';
            if(status === 'In Process') return 'bg-process';
            if(status === 'Closed') return 'bg-closed';
            if(status === 'Overdue') return 'bg-overdue';
            if(status === 'Pending') return 'bg-pending';
            if(status === 'Paid') return 'bg-paid';
            if(status === 'Unpaid') return 'bg-unpaid';
            return 'bg-process';
        }

        function goToMyTicket(id) {
            localStorage.setItem('currentTicketId', id);
            window.location.href = @json(route('my-ticket'));
        }
    </script>
</body>
</html>