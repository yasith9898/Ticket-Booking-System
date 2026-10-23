<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reports & Analytics</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --bg-light: #f4f6f9;
            --white: #ffffff;
            --border: #e0e0e0;
            --success: #2ecc71; /* Won */
            --danger: #e74c3c;  /* Lost */
            --warning: #f1c40f; /* Open */
        }

        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; height: 100vh; background-color: var(--bg-light); overflow: hidden; }

        /* SIDEBAR */
        .sidebar { width: 250px; background-color: var(--primary); color: var(--white); display: flex; flex-direction: column; padding: 20px 0; flex-shrink: 0; }
        .logo { font-size: 20px; font-weight: bold; text-align: center; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; }
        .menu-item { padding: 15px 25px; cursor: pointer; display: flex; align-items: center; color: var(--white); text-decoration: none; opacity: 0.8; }
        .menu-item:hover { background-color: rgba(255,255,255,0.1); opacity: 1; }
        .menu-item.active { background-color: rgba(255,255,255,0.1); border-left: 4px solid var(--secondary); opacity: 1; }

        /* MAIN CONTENT */
        .main-content { flex: 1; display: flex; flex-direction: column; padding: 30px; overflow-y: auto; }
        
        .header { margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 15px; }
        .header h2 { margin: 0; color: #2c3e50; }

        /* FILTER BAR */
        .filter-bar { 
            background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
            display: flex; gap: 20px; align-items: flex-end; margin-bottom: 20px; flex-wrap: wrap;
        }
        
        .filter-group { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 150px; }
        .filter-group label { font-size: 13px; font-weight: bold; color: #7f8c8d; }
        .filter-group input, .filter-group select { 
            padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; 
        }

        .btn-search { 
            background-color: var(--secondary); color: white; border: none; padding: 10px 25px; 
            border-radius: 4px; font-weight: bold; cursor: pointer; height: 40px; 
        }
        .btn-search:hover { background-color: #2980b9; }

        /* STATS BOXES (Clickable) */
        .stats-container { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-box { 
            flex: 1; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex; flex-direction: column; border-left: 5px solid #ccc; cursor: pointer; transition: transform 0.2s;
        }
        .stat-box:hover { transform: translateY(-3px); background-color: #fafafa; }
        
        .stat-box.total { border-left-color: var(--secondary); }
        .stat-box.won { border-left-color: var(--success); }
        .stat-box.lost { border-left-color: var(--danger); }
        .stat-box.open { border-left-color: var(--warning); }

        .stat-number { font-size: 24px; font-weight: bold; color: #333; }
        .stat-label { font-size: 12px; color: #777; text-transform: uppercase; font-weight: bold; }

        /* RESULTS AREA */
        .results-container { min-height: 200px; }
        
        /* GRID VIEW (Default) */
        .grid-view { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }

        /* CLIENT CARD */
        .client-card { 
            background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
            border-top: 4px solid var(--secondary); padding: 20px; transition: transform 0.2s; position: relative;
        }
        .client-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

        .card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .client-name { font-size: 18px; font-weight: bold; color: #2c3e50; margin: 0; }
        .client-company { font-size: 14px; color: #7f8c8d; margin-top: 2px; }
        
        .category-badge { 
            background: #eee; color: #555; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase;
        }

        .status-badge {
            position: absolute; top: 10px; right: 10px; padding: 4px 8px; border-radius: 4px; 
            font-size: 10px; color: white; font-weight: bold;
        }
        .bg-won { background-color: var(--success); }
        .bg-lost { background-color: var(--danger); }
        .bg-open { background-color: var(--warning); color: #333; }

        .card-body { margin-top: 25px; padding-top: 15px; border-top: 1px dashed #eee; }
        .ticket-info { font-size: 13px; color: #555; margin-bottom: 5px; }
        .ticket-date { font-size: 12px; color: #999; }
        
        /* TABLE VIEW (Hidden by default) */
        .report-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .report-table th, .report-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        .report-table th { background-color: #f8f9fa; color: #555; font-weight: bold; text-transform: uppercase; font-size: 12px; }
        .report-table tr:hover { background-color: #f1f1f1; }

        .empty-state { width: 100%; text-align: center; color: #999; padding: 40px; font-size: 16px; }

    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="logo">Unified System</div>
        <div class="menu-item" onclick="window.location.href='{{ route('ticket-dashboard') }}'">🎫 Ticket Dashboard</div>
        <div class="menu-item active">📊 Reports</div>
    </nav>

    <main class="main-content">
        <div class="header">
            <h2>Client & Pipeline Reports</h2>
        </div>

        <div class="filter-bar">
            <div class="filter-group">
                <label>Date From</label>
                <input type="date" id="date-from">
            </div>
            <div class="filter-group">
                <label>Date To</label>
                <input type="date" id="date-to">
            </div>
            <div class="filter-group">
                <label>Client Category</label>
                <select id="category-select">
                    <option value="All">All Categories</option>
                    <option value="Tech">Tech / IT</option>
                    <option value="Medical">Medical</option>
                    <option value="Retail">Retail</option>
                    <option value="Finance">Finance</option>
                    <option value="Education">Education</option>
                </select>
            </div>
            <button class="btn-search" onclick="runReport()">Generate Report</button>
        </div>

        <div class="stats-container" id="stats-display" style="display:none;">
            <div class="stat-box total" onclick="showTable('All')">
                <span class="stat-number" id="count-total">0</span>
                <span class="stat-label">Total Clients</span>
            </div>
            <div class="stat-box won" onclick="showTable('Won')">
                <span class="stat-number" id="count-won">0</span>
                <span class="stat-label">Won (Click to View)</span>
            </div>
            <div class="stat-box lost" onclick="showTable('Lost')">
                <span class="stat-number" id="count-lost">0</span>
                <span class="stat-label">Lost (Click to View)</span>
            </div>
            <div class="stat-box open" onclick="showTable('Open')">
                <span class="stat-number" id="count-open">0</span>
                <span class="stat-label">Open (Click to View)</span>
            </div>
        </div>

        <div id="results-area" class="results-container">
            <div class="empty-state">Select filters and click "Generate Report" to see results.</div>
        </div>

    </main>

    <script>
        const reportData = @json($reportJson);

        // Store current filtered results globally so we can switch views without re-filtering
        let currentResults = [];

        // Init Date Inputs
        document.getElementById('date-from').value = "2025-01-01";
        document.getElementById('date-to').value = "2025-12-31";

        function runReport() {
            // 1. Get Values
            const fromDate = document.getElementById('date-from').value;
            const toDate = document.getElementById('date-to').value;
            const category = document.getElementById('category-select').value;
            const statsBar = document.getElementById('stats-display');

            // 2. Filter Logic
            currentResults = reportData.filter(item => {
                if (category !== 'All' && item.category !== category) return false;
                if (fromDate && item.ticketDate < fromDate) return false;
                if (toDate && item.ticketDate > toDate) return false;
                return true;
            });

            // 3. Stats Calculation
            let won = 0, lost = 0, open = 0;
            currentResults.forEach(item => {
                if(item.status === 'Won') won++;
                else if(item.status === 'Lost') lost++;
                else open++;
            });

            // 4. Update Stats UI
            document.getElementById('count-total').innerText = currentResults.length;
            document.getElementById('count-won').innerText = won;
            document.getElementById('count-lost').innerText = lost;
            document.getElementById('count-open').innerText = open;
            
            if(currentResults.length > 0) {
                statsBar.style.display = 'flex';
                // Render Default View (Cards)
                renderCards(currentResults);
            } else {
                statsBar.style.display = 'none';
                document.getElementById('results-area').innerHTML = '<div class="empty-state">No records found.</div>';
            }
        }

        function renderCards(data) {
            const container = document.getElementById('results-area');
            container.innerHTML = '';
            container.className = 'results-container grid-view'; // Set to Grid

            data.forEach(item => {
                let statusClass = 'bg-open';
                if(item.status === 'Won') statusClass = 'bg-won';
                if(item.status === 'Lost') statusClass = 'bg-lost';

                const card = document.createElement('div');
                card.className = 'client-card';
                card.innerHTML = `
                    <div class="status-badge ${statusClass}">${item.status}</div>
                    <div class="card-header">
                        <div style="margin-right:30px;">
                            <h3 class="client-name">${item.name}</h3>
                            <div class="client-company">${item.company}</div>
                        </div>
                        <span class="category-badge">${item.category}</span>
                    </div>
                    <div class="card-body">
                        <div class="ticket-info"><strong>Subject:</strong> ${item.ticketSubject}</div>
                        <div class="ticket-date">📅 ${item.ticketDate}</div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function showTable(filterType) {
            // 1. Check if we have data
            if(currentResults.length === 0) return;

            // 2. Filter current results based on selection
            let tableData = currentResults;
            if(filterType !== 'All') {
                tableData = currentResults.filter(item => item.status === filterType);
            }

            // 3. Setup Table HTML
            const container = document.getElementById('results-area');
            container.innerHTML = '';
            container.className = 'results-container'; // Remove Grid class

            // Add Header Title
            const title = document.createElement('h3');
            title.innerText = filterType === 'All' ? 'All Clients' : `Details: ${filterType} Clients`;
            title.style.marginTop = '0';
            title.style.color = '#2c3e50';
            container.appendChild(title);

            if(tableData.length === 0) {
                container.innerHTML += '<div class="empty-state">No clients found with this status.</div>';
                return;
            }

            // Generate Table
            const table = document.createElement('table');
            table.className = 'report-table';
            
            let tableHTML = `
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Company</th>
                        <th>Category</th>
                        <th>Last Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
            `;

            tableData.forEach(item => {
                let color = '#333';
                if(item.status === 'Won') color = '#2ecc71';
                if(item.status === 'Lost') color = '#e74c3c';

                tableHTML += `
                    <tr>
                        <td><strong>${item.name}</strong></td>
                        <td>${item.company}</td>
                        <td><span style="background:#eee; padding:2px 6px; border-radius:4px; font-size:11px;">${item.category}</span></td>
                        <td>${item.ticketSubject}</td>
                        <td>${item.ticketDate}</td>
                        <td style="font-weight:bold; color:${color}">${item.status}</td>
                    </tr>
                `;
            });

            tableHTML += '</tbody>';
            table.innerHTML = tableHTML;
            container.appendChild(table);
        }
    </script>
</body>
</html>