<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Support Portal | Submit a Ticket</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .portal-card {
            background-color: white;
            width: 500px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 0 0 10px 0; color: #2c3e50; }
        .header p { color: #7f8c8d; margin: 0; font-size: 14px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #34495e; font-size: 14px; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
            transition: 0.3s;
        }
        .form-group input:focus, .form-group textarea:focus { border-color: #3498db; outline: none; }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-submit:hover { background-color: #2980b9; }

        .success-message {
            display: none;
            text-align: center;
            color: #27ae60;
            background: #eafaf1;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
        }

        .logo { font-size: 24px; font-weight: bold; color: #2c3e50; margin-bottom: 5px; display: block;}
    </style>
</head>
<body>

    <div class="portal-card">
        <div class="header">
            <span class="logo">Unified System</span>
            <h2>Submit a Support Request</h2>
            <p>Please provide details of your issue below.</p>
        </div>

        <div id="ticketForm">
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>Your Name</label>
                    <input type="text" id="client-name" placeholder="John Doe">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Company</label>
                    <input type="text" id="client-company" placeholder="Acme Corp">
                </div>
            </div>

            <div class="form-group">
                <label>Email / Phone</label>
                <input type="text" id="client-contact" placeholder="contact@example.com">
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <div class="form-group">
                <label>Subject / Issue</label>
                <input type="text" id="ticket-subject" placeholder="e.g. Cannot login to dashboard">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea id="ticket-desc" rows="4" placeholder="Please describe the issue in detail..."></textarea>
            </div>

            <button class="btn-submit" onclick="submitTicket()">Submit Ticket</button>
        </div>

        <div id="successMsg" class="success-message">
            <strong>Ticket Submitted Successfully!</strong><br>
            <small>Ticket ID: <span id="new-id"></span></small><br><br>
            <button onclick="location.reload()" style="background:none; border:none; color:#27ae60; text-decoration:underline; cursor:pointer;">Submit another</button>
        </div>
    </div>

    <script>
        async function submitTicket() {
            const name = document.getElementById('client-name').value;
            const company = document.getElementById('client-company').value;
            const contact = document.getElementById('client-contact').value;
            const subject = document.getElementById('ticket-subject').value;
            const desc = document.getElementById('ticket-desc').value;

            if(!name || !company || !subject) {
                alert("Please fill in Name, Company, and Subject.");
                return;
            }

            try {
                const res = await fetch(@json(route('client-submit.store')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        'client-name': name,
                        'client-company': company,
                        'client-contact': contact,
                        'ticket-subject': subject,
                        'ticket-desc': desc
                    })
                });
                const data = await res.json();
                if (!res.ok) {
                    alert('Could not submit ticket.');
                    return;
                }
                document.getElementById('new-id').innerText = '#' + data.ticket_number;
                document.getElementById('ticketForm').style.display = 'none';
                document.getElementById('successMsg').style.display = 'block';
            } catch (e) {
                alert('Network error');
                console.error(e);
            }
        }
    </script>
</body>
</html>