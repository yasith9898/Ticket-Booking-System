<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create New Ticket</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --bg-light: #f4f6f9;
            --white: #ffffff;
            --border: #e0e0e0;
            --success: #2ecc71;
        }

        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; height: 100vh; background-color: var(--bg-light); }

        /* Sidebar (Visual only) */
        .sidebar { width: 250px; background-color: var(--primary); color: var(--white); display: flex; flex-direction: column; padding: 20px 0; }
        .logo { font-size: 20px; font-weight: bold; text-align: center; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; }
        .menu-item { padding: 15px 25px; color: #bdc3c7; cursor: not-allowed; } 
        .menu-item.active { background-color: rgba(255,255,255,0.1); color: white; border-left: 4px solid var(--secondary); cursor: pointer;}

        /* Main Form Area */
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .form-container { background: white; max-width: 800px; margin: 0 auto; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        h2 { margin-top: 0; color: var(--primary); border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 25px; }
        h4 { margin-bottom: 15px; color: #2c3e50; margin-top: 0;}

        /* Form Elements */
        .form-section { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px dashed #eee; }
        .form-section:last-child { border-bottom: none; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #555; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 14px;
        }
        .form-group textarea { resize: vertical; }

        /* Grid for side-by-side inputs */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        /* Toggle Button Style */
        .client-toggle { display: flex; gap: 10px; margin-bottom: 20px; }
        .toggle-btn { 
            flex: 1; padding: 10px; text-align: center; border: 1px solid #ddd; cursor: pointer; border-radius: 4px; font-weight: 600; color: #777;
        }
        .toggle-btn.active { background-color: var(--secondary); color: white; border-color: var(--secondary); }

        /* Buttons */
        .btn-submit { background-color: var(--success); color: white; border: none; padding: 12px 30px; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; float: right; }
        .btn-submit:hover { background-color: #27ae60; }
        .btn-cancel { background-color: #eee; color: #333; border: none; padding: 12px 20px; border-radius: 4px; font-size: 16px; cursor: pointer; float: right; margin-right: 15px; }
        .btn-cancel:hover { background-color: #e0e0e0; }

        .internal-note { background-color: #fff8e1; border: 1px solid #ffe0b2; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="logo">Unified System</div>
        <div class="menu-item active">➕ New Ticket</div>
        <div class="menu-item" onclick="window.location.href='{{ route('ticket-dashboard') }}'">← Back to Dashboard</div>
    </nav>

    <main class="main-content">
        <div class="form-container">
            <h2>Create New Support Ticket</h2>

            <div class="form-section">
                <h4>1. Client Information</h4>
                
                <div class="client-toggle">
                    <div class="toggle-btn active" id="btn-exist" onclick="toggleClient('exist')">Select Existing Client</div>
                    <div class="toggle-btn" id="btn-new" onclick="toggleClient('new')">Add New Contact</div>
                </div>

                <div id="section-exist">
                    <div class="form-group">
                        <label>Search Client</label>
                        <select id="exist-client-select" name="exist_company">
                            <option value="">-- Select Client --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->company }}">{{ $c->person_name }} ({{ $c->company }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="section-new" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Contact Name</label>
                            <input type="text" id="new-name" placeholder="Full Name">
                        </div>
                        <div class="form-group">
                            <label>Company Name</label>
                            <input type="text" id="new-company" placeholder="Company Name">
                        </div>
                    </div>
                    
                    <div class="form-group" style="background:#f0f8ff; padding:10px; border-radius:4px; border:1px dashed #3498db;">
                        <label style="color:#2980b9;">Refer to Sales Person (Owner)</label>
                        <select id="new-salesrep">
                            <option value="Unassigned">-- Select Sales Rep --</option>
                            <option value="Ahmed Sales">Ahmed Sales</option>
                            <option value="Sara Rep">Sara Rep</option>
                            <option value="Mike Deal">Mike Deal</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="text" id="new-mobile" placeholder="+964...">
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <input type="text" id="new-category" placeholder="e.g. VIP, Retail...">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" id="new-city">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" id="new-address">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4>2. Ticket Details</h4>
                <div class="form-group">
                    <label>Reason / Subject</label>
                    <input type="text" id="ticket-subject" placeholder="e.g. Internet Disconnected">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Priority</label>
                        <select id="ticket-priority">
                            <option value="Normal">Normal</option>
                            <option value="High">High</option>
                            <option value="Low">Low</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign To (Support Agent)</label>
                        <select id="ticket-assign">
                            <option value="Unassigned">-- Unassigned --</option>
                            <option value="John Doe">John Doe</option>
                            <option value="Sarah Smith">Sarah Smith</option>
                            <option value="Level 1 Support">Level 1 Support Team</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4>3. Comments & Notes</h4>
                <div class="form-group">
                    <label>Initial Comment (Public / Visible to Client)</label>
                    <textarea id="ticket-comment" rows="3" placeholder="Description of the issue..."></textarea>
                </div>
                <div class="form-group">
                    <label>Internal Note (Private / Team Only)</label>
                    <textarea id="ticket-internal" rows="3" class="internal-note" placeholder="Notes for the team (hidden from client)..."></textarea>
                </div>
            </div>

            <div style="overflow: hidden;">
                <button class="btn-submit" onclick="createTicket()">Create Ticket</button>
                <button class="btn-cancel" onclick="window.location.href='{{ route('ticket-dashboard') }}'">Cancel</button>
            </div>

        </div>
    </main>

    <script>
        let clientMode = 'exist';

        function toggleClient(mode) {
            clientMode = mode;
            document.getElementById('btn-exist').className = mode === 'exist' ? 'toggle-btn active' : 'toggle-btn';
            document.getElementById('btn-new').className = mode === 'new' ? 'toggle-btn active' : 'toggle-btn';
            document.getElementById('section-exist').style.display = mode === 'exist' ? 'block' : 'none';
            document.getElementById('section-new').style.display = mode === 'new' ? 'block' : 'none';
        }

        async function createTicket() {
            const subject = document.getElementById('ticket-subject').value;
            if (!subject) { alert("Please enter a Subject/Reason."); return; }

            const payload = {
                mode: clientMode,
                'ticket-subject': subject,
                'ticket-priority': document.getElementById('ticket-priority').value,
                'ticket-assign': document.getElementById('ticket-assign').value,
                'ticket-comment': document.getElementById('ticket-comment').value,
                'ticket-internal': document.getElementById('ticket-internal').value,
            };

            if (clientMode === 'exist') {
                const select = document.getElementById('exist-client-select');
                if (select.value === "") { alert("Please select a client."); return; }
                payload.exist_company = select.value;
            } else {
                payload.person_name = document.getElementById('new-name').value;
                payload.company = document.getElementById('new-company').value;
                payload.new_salesrep = document.getElementById('new-salesrep').value;
                payload.new_mobile = document.getElementById('new-mobile').value;
                payload.new_category = document.getElementById('new-category').value;
                payload.new_city = document.getElementById('new-city').value;
                payload.new_address = document.getElementById('new-address').value;
                if (!payload.person_name || !payload.company) { alert("Please fill Name and Company."); return; }
            }

            try {
                const res = await fetch(@json(route('create-ticket.store')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.redirect) {
                    alert("Ticket Created Successfully!");
                    window.location.href = data.redirect;
                    return;
                }
                alert(data.message || 'Could not create ticket.');
            } catch (e) {
                alert('Network error');
                console.error(e);
            }
        }
    </script>
</body>
</html>