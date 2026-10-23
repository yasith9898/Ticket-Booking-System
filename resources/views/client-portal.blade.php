<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Client Portal | My Tickets</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --bg-light: #f4f6f9;
            --chat-bg: #f8f9fa;
        }

        body { font-family: 'Segoe UI', sans-serif; margin: 0; background-color: var(--bg-light); height: 100vh; display: flex; flex-direction: column;}

        /* Navbar */
        .navbar { background: white; padding: 15px 30px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
        .brand { font-weight: bold; font-size: 20px; color: var(--primary); }
        .user-info { font-size: 14px; color: #666; }

        /* Container */
        .container { max-width: 900px; margin: 30px auto; width: 90%; flex: 1; display: flex; flex-direction: column; }

        /* LOGIN SCREEN */
        .login-card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 400px; margin: auto; text-align: center; }
        .login-input { width: 100%; padding: 12px; margin: 15px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-primary { background: var(--secondary); color: white; border: none; padding: 12px 25px; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; }
        .btn-primary:hover { background: #2980b9; }

        /* TICKET LIST */
        .ticket-card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; border-left: 5px solid #ddd; cursor: pointer; transition: 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .ticket-card:hover { transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .ticket-card.status-New { border-left-color: #2ecc71; }
        .ticket-card.status-In-Process { border-left-color: #3498db; }
        .ticket-card.status-Closed { border-left-color: #95a5a6; }

        .t-header { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .t-subject { font-weight: bold; font-size: 16px; color: #2c3e50; }
        .t-id { color: #999; font-size: 13px; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 11px; color: white; background: #999; }
        .badge-New { background: #2ecc71; }
        .badge-Process { background: #3498db; }

        /* TICKET DETAIL & CHAT */
        .detail-view { background: white; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column; height: 600px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .detail-header { padding: 20px; border-bottom: 1px solid #eee; background: #fff; }
        .chat-area { flex: 1; padding: 20px; overflow-y: auto; background: var(--chat-bg); }
        
        .message { margin-bottom: 20px; max-width: 80%; }
        .message.client { margin-left: auto; text-align: right; }
        .msg-bubble { padding: 10px 15px; border-radius: 8px; display: inline-block; font-size: 14px; }
        .client .msg-bubble { background: #d6eaf8; color: #2c3e50; border-bottom-right-radius: 0; }
        .support .msg-bubble { background: white; border: 1px solid #ddd; border-bottom-left-radius: 0; }
        .msg-meta { font-size: 11px; color: #999; margin-top: 4px; display: block; }

        .reply-box { padding: 20px; border-top: 1px solid #eee; display: flex; gap: 10px; background: white; }
        .reply-input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }

        /* Utility */
        .hidden { display: none !important; }
        .btn-back { background: none; border: none; color: #666; cursor: pointer; margin-bottom: 10px; display: flex; align-items: center; gap: 5px;}
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="brand">Unified Support</div>
        <div class="user-info" id="nav-user" style="display:none;"></div>
    </nav>

    <div class="container">
        
        <div id="view-login" class="login-card">
            <h3>Client Login</h3>
            <p style="color:#777; font-size:14px;">Enter your email to view your ticket history.</p>
            <input type="email" id="email-input" class="login-input" placeholder="e.g. john@acme.com" value="john@acme.com">
            <button class="btn-primary" onclick="login()">Check Tickets</button>
        </div>

        <div id="view-list" class="hidden">
            <h2 style="margin-top:0;">My Tickets</h2>
            <div id="ticket-list-container">
                </div>
        </div>

        <div id="view-detail" class="hidden">
            <button class="btn-back" onclick="showList()">← Back to List</button>
            <div class="detail-view">
                <div class="detail-header">
                    <h3 id="dt-subject" style="margin:0;">Subject</h3>
                    <div style="margin-top:5px;">
                        <span id="dt-id" style="color:#888; font-size:13px;">#1001</span> • 
                        <span id="dt-status" class="badge">New</span>
                    </div>
                </div>
                
                <div class="chat-area" id="chat-box">
                    </div>

                <div class="reply-box">
                    <input type="text" id="reply-input" class="reply-input" placeholder="Type a reply...">
                    <button class="btn-primary" style="width: auto;" onclick="sendReply()">Send</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        let serverTickets = [];
        let currentUser = '';
        let currentTicketId = '';

        function csrfToken() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        async function login() {
            const email = document.getElementById('email-input').value;
            if(!email) return alert("Please enter email");

            currentUser = email;
            document.getElementById('nav-user').innerText = "Logged in as: " + email;
            document.getElementById('nav-user').style.display = 'block';

            try {
                const res = await fetch('/portal/tickets?email=' + encodeURIComponent(email), {
                    headers: { 'Accept': 'application/json' }
                });
                serverTickets = await res.json();
            } catch (e) {
                serverTickets = [];
                console.error(e);
            }

            renderTicketList();

            document.getElementById('view-login').classList.add('hidden');
            document.getElementById('view-list').classList.remove('hidden');
        }

        function renderTicketList() {
            const container = document.getElementById('ticket-list-container');
            container.innerHTML = '';

            if(serverTickets.length === 0) {
                container.innerHTML = '<p style="text-align:center; color:#999;">No tickets found for this email.</p>';
                return;
            }

            serverTickets.forEach(t => {
                const st = (t.status || '').replace(/-/g, '');
                let badgeClass = '';
                if(st.indexOf('New') >= 0 || st === 'Pending') badgeClass = 'badge-New';
                else if(st.indexOf('Process') >= 0 || st.indexOf('In') >= 0) badgeClass = 'badge-Process';

                const html = `
                    <div class="ticket-card status-${(t.status || '').replace(/ /g,'-')}" onclick="openTicket('${t.id}')">
                        <div class="t-header">
                            <span class="t-subject">${t.subject}</span>
                            <span class="badge ${badgeClass}">${(t.status || '').replace(/-/g,' ')}</span>
                        </div>
                        <div class="t-id">Ticket #${t.id} • ${t.date}</div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
            });
        }

        function openTicket(id) {
            currentTicketId = id;
            const ticket = serverTickets.find(t => String(t.id) === String(id));
            if (!ticket) return;

            document.getElementById('dt-subject').innerText = ticket.subject;
            document.getElementById('dt-id').innerText = "#" + ticket.id;
            document.getElementById('dt-status').innerText = (ticket.status || '').replace(/-/g,' ');

            renderChat(ticket.messages || []);

            document.getElementById('view-list').classList.add('hidden');
            document.getElementById('view-detail').classList.remove('hidden');
        }

        function renderChat(messages) {
            const chatBox = document.getElementById('chat-box');
            chatBox.innerHTML = '';

            messages.forEach(msg => {
                const isClient = msg.from === 'client';
                const div = document.createElement('div');
                div.className = `message ${isClient ? 'client' : 'support'}`;
                div.innerHTML = `
                    <div class="msg-bubble">${msg.text}</div>
                    <span class="msg-meta">${isClient ? 'You' : 'Support Team'} • ${msg.time}</span>
                `;
                chatBox.appendChild(div);
            });

            chatBox.scrollTop = chatBox.scrollHeight;
        }

        async function sendReply() {
            const input = document.getElementById('reply-input');
            const text = input.value;
            if(!text) return;

            try {
                await fetch('/portal/tickets/' + currentTicketId + '/reply', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ body: text, email: currentUser })
                });
                const ticket = serverTickets.find(t => String(t.id) === String(currentTicketId));
                if (ticket) {
                    ticket.messages.push({ from: 'client', text: text, time: 'Just now' });
                    renderChat(ticket.messages);
                }
                input.value = '';
            } catch (e) { console.error(e); }
        }

        function showList() {
            document.getElementById('view-detail').classList.add('hidden');
            document.getElementById('view-list').classList.remove('hidden');
        }

    </script>
</body>
</html>