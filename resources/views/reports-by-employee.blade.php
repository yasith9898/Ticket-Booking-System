<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ticket Reports & Analytics</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --bg-light: #f4f6f9;
            --white: #ffffff;
            --border: #e0e0e0;
            --success: #2ecc71;
            --danger: #e74c3c;
            --warning: #f1c40f;
            --info: #1abc9c;
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
        
        .header { margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .header h2 { margin: 0; color: #2c3e50; }

        /* FILTER BAR */
        .filter-bar { 
            background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
            display: flex; gap: 20px; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap;
        }
        
        .filter-group { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 150px; }
        .filter-group label { font-size: 13px; font-weight: bold; color: #7f8c8d; }
        .filter-group input, .filter-group select { 
            padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 100%; box-sizing: border-box;
        }

        .btn-filter { 
            background-color: var(--secondary); color: white; border: none; padding: 10px 25px; 
            border-radius: 4px; font-weight: bold; cursor: pointer; height: 40px; 
        }
        .btn-filter:hover { background-color: #2980b9; }

        /* GLOBAL STATUS STATS */
        .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .mini-stat { 
            background: white; padding: 15px; border-radius: 8px; border-left: 5px solid #ccc; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; flex-direction: column;
        }
        .mini-stat.total { border-color: var(--secondary); }
        .mini-stat.closed { border-color: var(--success); }
        .mini-stat.pending { border-color: var(--warning); }
        .mini-stat.overdue { border-color: var(--danger); }

        .ms-val { font-size: 24px; font-weight: bold; color: #333; }
        .ms-lbl { font-size: 11px; text-transform: uppercase; color: #999; font-weight: bold; }

        /* EMPLOYEE CARDS GRID */
        .section-title { font-size: 16px; font-weight: bold; color: #2c3e50; margin-bottom: 15px; border-left: 4px solid var(--primary); padding-left: 10px; }
        
        .cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px;}

        .emp-card { 
            background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.2s; cursor: pointer; border: 1px solid transparent;
        }
        .emp-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); border-color: var(--secondary); }
        .emp-card.selected { border: 2px solid var(--secondary); background: #fdfdfd; }

        .emp-header { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .emp-avatar { 
            width: 40px; height: 40px; background: var(--bg-light); border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; font-weight: bold; color: var(--secondary); border: 1px solid #ddd;
        }
        .emp-info h3 { margin: 0; font-size: 16px; color: #333; }
        .emp-info span { font-size: 12px; color: #999; }

        .emp-stats { display: flex; justify-content: space-between; text-align: center; background: #f9f9f9; padding: 10px; border-radius: 6px; }
        .es-item strong { display: block; font-size: 16px; color: #333; }
        .es-item small { font-size: 10px; color: #777; text-transform: uppercase; }

        /* Progress Bar inside Card */
        .progress-container { margin-top: 15px; }
        .progress-lbl { display: flex; justify-content: space-between; font-size: 11px; color: #666; margin-bottom: 3px; }
        .progress-bar { height: 6px; background: #eee; border-radius: 3px; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--success); width: 0%; }

        /* DRILL DOWN TABLE */
        .drilldown-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 -2px 10px rgba(0,0,0,0.05); display: none; animation: slideUp 0.3s; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 13px; }
        th { background: #f8f9fa; color: #555; }
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 11px; color: white; font-weight: bold; }
        
        .b-closed { background: var(--success); }
        .b-process { background: var(--secondary); }
        .b-pending { background: var(--warning); }
        .b-overdue { background: var(--danger); }

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
            <h2>Ticket Analytics & Performance</h2>
            <div style="font-size:12px; color:#777;">Data Analysis Module</div>
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
                <label>Team Member / Agent</label>
                <select id="emp-select">
                    <option value="All">All Team Members</option>
                    <option value="John Doe">John Doe</option>
                    <option value="Sarah Smith">Sarah Smith</option>
                    <option value="Mike Ross">Mike Ross</option>
                    <option value="Unassigned">Unassigned</option>
                </select>
            </div>

            <button class="btn-filter" onclick="generateReport()">Apply Filters</button>
        </div>

        <div class="section-title">Overall Ticket Status</div>
        <div class="stats-row">
            <div class="mini-stat total">
                <span class="ms-val" id="g-total">0</span>
                <span class="ms-lbl">Total Tickets</span>
            </div>
            <div class="mini-stat closed">
                <span class="ms-val" id="g-closed">0</span>
                <span class="ms-lbl">Closed / Resolved</span>
            </div>
            <div class="mini-stat pending">
                <span class="ms-val" id="g-process">0</span>
                <span class="ms-lbl">Active (Process/New)</span>
            </div>
            <div class="mini-stat overdue">
                <span class="ms-val" id="g-overdue">0</span>
                <span class="ms-lbl">Overdue</span>
            </div>
        </div>

        <div class="section-title">Performance by Agent (Click Card for Details)</div>
        <div id="cards-area" class="cards-grid">
            </div>

        <div id="drill-down" class="drilldown-container">
            <div style="display:flex; justify-content:space-between; margin-bottom:15px;">
                <h3 style="margin:0; color:#2c3e50;">Detailed Ticket List: <span id="dd-name"></span></h3>
                <button onclick="closeDrillDown()" style="border:none; background:none; color:red; cursor:pointer;">&times; Close</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Client</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="dd-body"></tbody>
            </table>
        </div>

    </main>

    <script>
        const ticketData = @json($ticketDataJson);

        // Init Default Date (Year 2025)
        document.getElementById('date-from').value = '2025-01-01';
        document.getElementById('date-to').value = '2025-12-31';

        // Run on Load
        window.onload = generateReport;

        function generateReport() {
            // 1. Get Inputs
            const dFrom = document.getElementById('date-from').value;
            const dTo = document.getElementById('date-to').value;
            const empSelect = document.getElementById('emp-select').value;

            // 2. Filter Data
            const filtered = ticketData.filter(t => {
                // Check Date
                if (dFrom && t.date < dFrom) return false;
                if (dTo && t.date > dTo) return false;
                
                // Check Employee
                if (empSelect !== 'All' && t.employee !== empSelect) return false;

                return true;
            });

            // 3. Calculate Global Stats
            let total=0, closed=0, active=0, overdue=0;
            
            // 4. Group by Employee
            const empMap = {}; 

            filtered.forEach(t => {
                total++;
                if(t.status === 'Closed') closed++;
                else if(t.status === 'Overdue') overdue++;
                else active++; // New, In Process, Pending

                // Employee Grouping
                if(!empMap[t.employee]) {
                    empMap[t.employee] = { name: t.employee, total: 0, closed: 0, open: 0, tickets: [] };
                }
                
                empMap[t.employee].total++;
                empMap[t.employee].tickets.push(t);
                
                if(t.status === 'Closed') empMap[t.employee].closed++;
                else empMap[t.employee].open++;
            });

            // Update Global UI
            document.getElementById('g-total').innerText = total;
            document.getElementById('g-closed').innerText = closed;
            document.getElementById('g-process').innerText = active;
            document.getElementById('g-overdue').innerText = overdue;

            // Render Cards
            renderCards(empMap);
            
            // Hide drill down on regen
            closeDrillDown();
        }

        function renderCards(empMap) {
            const container = document.getElementById('cards-area');
            container.innerHTML = '';

            const employees = Object.values(empMap);

            if(employees.length === 0) {
                container.innerHTML = '<div style="color:#999; grid-column:1/-1; text-align:center; padding:30px;">No records found for the selected criteria.</div>';
                return;
            }

            employees.forEach(emp => {
                // Calculate Success Rate %
                const rate = emp.total === 0 ? 0 : Math.round((emp.closed / emp.total) * 100);

                const card = document.createElement('div');
                card.className = 'emp-card';
                card.onclick = () => showDrillDown(emp, card);

                // Initials
                const initials = emp.name === 'Unassigned' ? '?' : emp.name.match(/\b(\w)/g).join('');

                card.innerHTML = `
                    <div class="emp-header">
                        <div class="emp-avatar">${initials}</div>
                        <div class="emp-info">
                            <h3>${emp.name}</h3>
                            <span>Support Agent</span>
                        </div>
                    </div>
                    
                    <div class="emp-stats">
                        <div class="es-item">
                            <strong>${emp.total}</strong>
                            <small>Total</small>
                        </div>
                        <div class="es-item">
                            <strong style="color:var(--success)">${emp.closed}</strong>
                            <small>Closed</small>
                        </div>
                        <div class="es-item">
                            <strong style="color:var(--warning)">${emp.open}</strong>
                            <small>Open</small>
                        </div>
                    </div>

                    <div class="progress-container">
                        <div class="progress-lbl">
                            <span>Completion Rate</span>
                            <span>${rate}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${rate}%;"></div>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function showDrillDown(empData, cardElem) {
            // Highlight Card
            document.querySelectorAll('.emp-card').forEach(c => c.classList.remove('selected'));
            cardElem.classList.add('selected');

            // Populate Table
            const tbody = document.getElementById('dd-body');
            tbody.innerHTML = '';
            document.getElementById('dd-name').innerText = empData.name;

            empData.tickets.forEach(t => {
                let badgeClass = 'b-process';
                if(t.status === 'Closed') badgeClass = 'b-closed';
                if(t.status === 'Overdue') badgeClass = 'b-overdue';
                if(t.status === 'Pending') badgeClass = 'b-pending';

                const row = `
                    <tr>
                        <td>#${t.id}</td>
                        <td>${t.date}</td>
                        <td>${t.subject}</td>
                        <td>${t.client}</td>
                        <td><span class="badge ${badgeClass}">${t.status}</span></td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });

            // Show Area
            document.getElementById('drill-down').style.display = 'block';
            document.getElementById('drill-down').scrollIntoView({ behavior: 'smooth' });
        }

        function closeDrillDown() {
            document.getElementById('drill-down').style.display = 'none';
            document.querySelectorAll('.emp-card').forEach(c => c.classList.remove('selected'));
        }
    </script>
</body>
</html>