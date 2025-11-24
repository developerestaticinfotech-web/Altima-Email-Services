<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outbox - AltimaCRM Email Microservice</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
            position: relative;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        @media (min-width: 769px) {
            .container {
                margin-left: 0;
            }
        }

        /* Sidebar Navigation */
        .sidebar {
            position: fixed;
            left: -300px;
            top: 0;
            height: 100vh;
            width: 280px;
            background: rgba(26, 26, 46, 0.98);
            backdrop-filter: blur(10px);
            transition: left 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
            overflow-y: auto;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(138, 43, 226, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-header h3 {
            color: #ffffff;
            font-size: 1.2rem;
            margin: 0;
        }

        .sidebar-close {
            background: none;
            border: none;
            color: #ffffff;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .sidebar-close:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu-item {
            display: block;
            padding: 15px 20px;
            color: #e0e0e0;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-size: 1rem;
        }

        .sidebar-menu-item:hover {
            background: rgba(138, 43, 226, 0.2);
            border-left-color: #8a2be2;
            color: #ffffff;
            padding-left: 25px;
        }

        .sidebar-menu-item.active {
            background: rgba(138, 43, 226, 0.3);
            border-left-color: #8a2be2;
            color: #ffffff;
        }

        .sidebar-menu-item i {
            margin-right: 10px;
            width: 20px;
            display: inline-block;
        }

        .sidebar-toggle {
            position: fixed;
            left: 0px;
            top: 0px;
            z-index: 999;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 0 0 10px 0;
            cursor: pointer;
            font-size: 1.2rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            backdrop-filter: blur(2px);
        }

        .sidebar-overlay.active {
            display: block;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                left: -100%;
            }

            .sidebar-toggle {
                left: 0px;
                top: 0px;
                padding: 10px 12px;
                font-size: 1rem;
            }
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .stat-card.pending h3 { color: #f39c12; }
        .stat-card.sent h3 { color: #27ae60; }
        .stat-card.failed h3 { color: #e74c3c; }
        .stat-card.bounced h3 { color: #9b59b6; }

        .stat-card p {
            color: #7f8c8d;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .controls {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .controls-row {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        select, input {
            padding: 12px 15px;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            background: white;
        }

        select:focus, input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
        }

        .emails-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .email-item {
            border: 2px solid #e0e6ed;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .email-item:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .email-subject {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .email-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .email-meta span {
            background: #f8f9fa;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .email-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-sent {
            background: #d4edda;
            color: #155724;
        }

        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }

        .status-bounced {
            background: #e2e3e5;
            color: #383d41;
        }

        .email-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 0.85rem;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .no-emails {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .no-emails h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 30px;
            padding: 20px;
            flex-wrap: wrap;
        }

        .pagination button {
            padding: 10px 15px;
            border: 2px solid #e0e6ed;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            min-width: 40px;
            font-weight: 500;
        }

        .pagination button:hover:not(:disabled) {
            border-color: #667eea;
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
        }

        .pagination button:disabled:not([style*="cursor: default"]) {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination button[disabled][style*="cursor: default"] {
            background: transparent;
            border: none;
            opacity: 0.7;
            cursor: default;
        }

        .pagination-info {
            color: #7f8c8d;
            font-size: 0.9rem;
            font-weight: 500;
            margin-left: 15px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e6ed;
        }

        .modal-header h2 {
            color: #2c3e50;
            font-size: 1.8rem;
        }

        .close {
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            color: #7f8c8d;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #e74c3c;
        }

        .email-details {
            display: grid;
            gap: 20px;
        }

        .detail-group {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }

        .detail-group h4 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .detail-group p {
            color: #6c757d;
            line-height: 1.6;
        }

        .email-body {
            background: white;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            padding: 20px;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .controls-row {
                flex-direction: column;
                align-items: stretch;
            }

            .email-meta {
                flex-direction: column;
                gap: 10px;
            }

            .email-actions {
                flex-direction: column;
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Navigation">
        ☰
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>🚀 Navigation</h3>
            <button class="sidebar-close" onclick="toggleSidebar()">×</button>
        </div>
        <nav class="sidebar-menu">
            <a href="/" class="sidebar-menu-item">
                <span>🏠</span> Home
            </a>
            <a href="/inbound-emails" class="sidebar-menu-item">
                <span>📥</span> Inbound Emails
            </a>
            <a href="/outbox" class="sidebar-menu-item active">
                <span>📤</span> Outbox
            </a>
            <a href="/replied-emails" class="sidebar-menu-item">
                <span>💬</span> Replied Emails
            </a>
            <a href="/email-tracking" class="sidebar-menu-item">
                <span>📈</span> Email Tracking
            </a>
            <a href="/email-logs" class="sidebar-menu-item">
                <span>📊</span> Email Logs
            </a>
            <a href="/providers" class="sidebar-menu-item">
                <span>📧</span> Providers
            </a>
            <a href="/rabbitmq-test" class="sidebar-menu-item">
                <span>🐰</span> RabbitMQ Test
            </a>
            <a href="/api/health" class="sidebar-menu-item">
                <span>💚</span> Health Check
            </a>
            <a href="/api/email/templates" class="sidebar-menu-item">
                <span>📝</span> Templates
            </a>
        </nav>
    </div>

    <div class="container">
        <div class="header">
            <h1>📤 Outbox</h1>
            <p>Manage and monitor your outgoing emails</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card pending">
                <h3 id="pending-count">-</h3>
                <p>Pending</p>
            </div>
            <div class="stat-card sent">
                <h3 id="sent-count">-</h3>
                <p>Sent</p>
            </div>
            <div class="stat-card failed">
                <h3 id="failed-count">-</h3>
                <p>Failed</p>
            </div>
            <div class="stat-card bounced">
                <h3 id="bounced-count">-</h3>
                <p>Bounced</p>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls">
            <h3 style="margin-bottom: 20px; color: #2c3e50;">🔍 Filters</h3>
            <div class="controls-row">
                <div class="form-group">
                    <label for="tenant_id">Tenant</label>
                    <select id="tenant_id" required>
                        <option value="">Select Tenant</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status_filter">Status</label>
                    <select id="status_filter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="sent">Sent</option>
                        <option value="failed">Failed</option>
                        <option value="bounced">Bounced</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="from_email">From Email</label>
                    <input type="email" id="from_email" placeholder="Filter by sender email">
                </div>
                <div class="form-group">
                    <label for="to_email">To Email</label>
                    <input type="email" id="to_email" placeholder="Filter by recipient email">
                </div>
            </div>
            <div class="controls-row" style="margin-top: 15px;">
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" placeholder="Filter by subject">
                </div>
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" placeholder="Search emails...">
                </div>
                <div class="form-group">
                    <label for="date_from">Date From</label>
                    <input type="date" id="date_from">
                </div>
                <div class="form-group">
                    <label for="date_to">Date To</label>
                    <input type="date" id="date_to">
                </div>
            </div>
            <div class="controls-row" style="margin-top: 15px;">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button class="btn btn-warning" onclick="clearFilters()" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">Clear Filters</button>
                </div>
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button class="btn btn-success" onclick="loadEmails(1)">🔄 Refresh</button>
                </div>
            </div>
        </div>

        <!-- Emails Container -->
        <div class="emails-container">
            <div id="emails-list">
                <div class="loading">
                    <p>Select a tenant to view outbox emails...</p>
                </div>
            </div>
            <div id="pagination" class="pagination" style="display: none;"></div>
        </div>
    </div>

    <!-- Email Details Modal -->
    <div id="emailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Email Details</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div id="modal-body">
                <!-- Email details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentTenantId = '';
        let currentFilters = {};

        // Load tenants on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTenants();
            loadStats();
        });

        // Load tenants
        async function loadTenants() {
            try {
                const response = await fetch('/api/email/tenants');
                if (response.ok) {
                    const result = await response.json();
                    const tenants = result.data || [];
                    
                    const select = document.getElementById('tenant_id');
                    select.innerHTML = '<option value="">Select Tenant</option>' +
                        tenants.map(tenant => `<option value="${tenant.tenant_id}">${tenant.tenant_name}</option>`).join('');
                }
            } catch (error) {
                console.error('Error loading tenants:', error);
            }
        }

        // Apply filters
        function applyFilters() {
            currentPage = 1;
            loadEmails(1);
        }

        // Clear filters
        function clearFilters() {
            document.getElementById('status_filter').value = '';
            document.getElementById('from_email').value = '';
            document.getElementById('to_email').value = '';
            document.getElementById('subject').value = '';
            document.getElementById('search').value = '';
            document.getElementById('date_from').value = '';
            document.getElementById('date_to').value = '';
            currentFilters = {};
            currentPage = 1;
            loadEmails(1);
        }

        // Load emails
        async function loadEmails(page = 1) {
            const tenantId = document.getElementById('tenant_id').value;

            if (!tenantId) {
                document.getElementById('emails-list').innerHTML = 
                    '<div class="loading"><p>Select a tenant to view outbox emails...</p></div>';
                return;
            }

            currentTenantId = tenantId;
            currentPage = page;

            try {
                const params = new URLSearchParams({
                    tenant_id: tenantId,
                    page: page,
                    per_page: 10
                });

                // Add all filters
                const status = document.getElementById('status_filter').value;
                const fromEmail = document.getElementById('from_email').value;
                const toEmail = document.getElementById('to_email').value;
                const subject = document.getElementById('subject').value;
                const search = document.getElementById('search').value;
                const dateFrom = document.getElementById('date_from').value;
                const dateTo = document.getElementById('date_to').value;

                if (status) params.append('status', status);
                if (fromEmail) params.append('from_email', fromEmail);
                if (toEmail) params.append('to_email', toEmail);
                if (subject) params.append('subject', subject);
                if (search) params.append('search', search);
                if (dateFrom) params.append('date_from', dateFrom);
                if (dateTo) params.append('date_to', dateTo);

                const response = await fetch(`/api/outbox/emails?${params}`);
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        displayEmails(result.data);
                        // Always update pagination if it exists
                        if (result.pagination) {
                            updatePagination(result.pagination);
                        } else {
                            // Hide pagination if no pagination data
                            document.getElementById('pagination').style.display = 'none';
                        }
                        loadStats(tenantId);
                    } else {
                        document.getElementById('emails-list').innerHTML = 
                            '<div class="loading"><p>Error: ' + (result.message || 'Failed to load emails') + '</p></div>';
                        document.getElementById('pagination').style.display = 'none';
                    }
                } else {
                    const errorData = await response.json().catch(() => ({ message: 'Failed to load emails' }));
                    document.getElementById('emails-list').innerHTML = 
                        '<div class="loading"><p>Error: ' + (errorData.message || 'Failed to load emails') + '</p></div>';
                    document.getElementById('pagination').style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading emails:', error);
                document.getElementById('emails-list').innerHTML = 
                    '<div class="loading"><p>Error loading emails. Please try again.</p></div>';
            }
        }

        // Display emails
        function displayEmails(emails) {
            const container = document.getElementById('emails-list');
            
            if (emails.length === 0) {
                container.innerHTML = `
                    <div class="no-emails">
                        <h3>No emails found</h3>
                        <p>No outbox emails match your current filters.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = emails.map(email => {
                const toEmails = Array.isArray(email.to) ? email.to.join(', ') : (email.to || 'N/A');
                const sentDate = email.sent_at ? new Date(email.sent_at).toLocaleString() : 'Not sent yet';
                const createdDate = email.created_at ? new Date(email.created_at).toLocaleString() : 'N/A';
                
                return `
                <div class="email-item" onclick="showEmailDetails('${email.id}')">
                    <div class="email-header">
                        <div>
                            <div class="email-subject">${email.subject || 'No Subject'}</div>
                            <div class="email-meta">
                                <span><strong>To:</strong> ${toEmails}</span>
                                <span><strong>From:</strong> ${email.from || 'N/A'}</span>
                                <span><strong>Created:</strong> ${createdDate}</span>
                                ${email.sent_at ? `<span><strong>Sent:</strong> ${sentDate}</span>` : ''}
                            </div>
                        </div>
                        <div class="email-status status-${email.status}">${email.status}</div>
                    </div>
                    <div class="email-actions">
                        ${email.status === 'failed' ? `<button class="btn btn-success btn-sm" onclick="event.stopPropagation(); resendEmail('${email.id}')">Resend</button>` : ''}
                        ${email.status === 'pending' ? `<button class="btn btn-danger btn-sm" onclick="event.stopPropagation(); deleteEmail('${email.id}')">Delete</button>` : ''}
                        <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); showEmailDetails('${email.id}')">View Details</button>
                    </div>
                </div>
            `;
            }).join('');
        }

        // Update pagination
        function updatePagination(pagination) {
            const container = document.getElementById('pagination');
            
            // Check if pagination data exists
            if (!pagination) {
                console.warn('No pagination data received');
                container.style.display = 'none';
                return;
            }

            // Show pagination info even if only one page (but hide buttons)
            if (pagination.total === 0 || !pagination.total) {
                container.style.display = 'none';
                return;
            }

            // Always show pagination if there are results
            container.style.display = 'flex';
            
            let paginationHtml = '';
            
            // Previous button
            paginationHtml += `<button ${pagination.current_page <= 1 ? 'disabled' : ''} onclick="loadEmails(${pagination.current_page - 1})">Previous</button>`;
            
            // Page numbers - show all pages if 10 or less, otherwise show smart pagination
            if (pagination.last_page <= 10) {
                // Show all pages
                for (let i = 1; i <= pagination.last_page; i++) {
                    if (i === pagination.current_page) {
                        paginationHtml += `<button style="background: #667eea; color: white; border-color: #667eea;" disabled>${i}</button>`;
                    } else {
                        paginationHtml += `<button onclick="loadEmails(${i})">${i}</button>`;
                    }
                }
            } else {
                // Smart pagination - show first, last, current, and nearby pages
                // Always show first page
                if (pagination.current_page > 3) {
                    paginationHtml += `<button onclick="loadEmails(1)">1</button>`;
                    if (pagination.current_page > 4) {
                        paginationHtml += `<button disabled style="cursor: default; background: transparent; border: none;">...</button>`;
                    }
                }
                
                // Show pages around current
                const start = Math.max(1, pagination.current_page - 2);
                const end = Math.min(pagination.last_page, pagination.current_page + 2);
                
                for (let i = start; i <= end; i++) {
                    if (i === pagination.current_page) {
                        paginationHtml += `<button style="background: #667eea; color: white; border-color: #667eea;" disabled>${i}</button>`;
                    } else {
                        paginationHtml += `<button onclick="loadEmails(${i})">${i}</button>`;
                    }
                }
                
                // Always show last page
                if (pagination.current_page < pagination.last_page - 2) {
                    if (pagination.current_page < pagination.last_page - 3) {
                        paginationHtml += `<button disabled style="cursor: default; background: transparent; border: none;">...</button>`;
                    }
                    paginationHtml += `<button onclick="loadEmails(${pagination.last_page})">${pagination.last_page}</button>`;
                }
            }
            
            // Next button
            paginationHtml += `<button ${pagination.current_page >= pagination.last_page ? 'disabled' : ''} onclick="loadEmails(${pagination.current_page + 1})">Next</button>`;
            
            // Pagination info - always show
            paginationHtml += `<span class="pagination-info" style="margin-left: 15px;">Page ${pagination.current_page} of ${pagination.last_page} (${pagination.from || 0}-${pagination.to || 0} of ${pagination.total})</span>`;
            
            container.innerHTML = paginationHtml;
            
            // Debug log
            console.log('Pagination updated:', pagination);
        }

        // Load statistics
        async function loadStats(tenantId = null) {
            const targetTenantId = tenantId || document.getElementById('tenant_id').value;
            
            if (!targetTenantId) {
                // Reset stats if no tenant selected
                document.getElementById('pending-count').textContent = '-';
                document.getElementById('sent-count').textContent = '-';
                document.getElementById('failed-count').textContent = '-';
                document.getElementById('bounced-count').textContent = '-';
                return;
            }
            
            try {
                const response = await fetch(`/api/outbox/stats?tenant_id=${targetTenantId}`);
                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.data) {
                        const stats = result.data;
                        document.getElementById('pending-count').textContent = stats.pending || 0;
                        document.getElementById('sent-count').textContent = stats.sent || 0;
                        document.getElementById('failed-count').textContent = stats.failed || 0;
                        document.getElementById('bounced-count').textContent = stats.bounced || 0;
                    }
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Show email details
        async function showEmailDetails(emailId) {
            try {
                const response = await fetch(`http://localhost:8000/api/outbox/emails/${emailId}`);
                if (response.ok) {
                    const result = await response.json();
                    const email = result.data;
                    
                    document.getElementById('modal-title').textContent = email.subject || 'Email Details';
                    const toEmails = Array.isArray(email.to) ? email.to.join(', ') : (email.to || 'N/A');
                    const ccEmails = Array.isArray(email.cc) ? email.cc.join(', ') : (email.cc || '');
                    const bccEmails = Array.isArray(email.bcc) ? email.bcc.join(', ') : (email.bcc || '');
                    
                    document.getElementById('modal-body').innerHTML = `
                        <div class="email-details">
                            <div class="detail-group">
                                <h4>Basic Information</h4>
                                <p><strong>Subject:</strong> ${email.subject || 'No Subject'}</p>
                                <p><strong>From:</strong> ${email.from || 'N/A'}</p>
                                <p><strong>To:</strong> ${toEmails}</p>
                                ${ccEmails ? `<p><strong>CC:</strong> ${ccEmails}</p>` : ''}
                                ${bccEmails ? `<p><strong>BCC:</strong> ${bccEmails}</p>` : ''}
                                <p><strong>Status:</strong> <span class="email-status status-${email.status}">${email.status}</span></p>
                                <p><strong>Created:</strong> ${email.created_at ? new Date(email.created_at).toLocaleString() : 'N/A'}</p>
                                ${email.sent_at ? `<p><strong>Sent:</strong> ${new Date(email.sent_at).toLocaleString()}</p>` : ''}
                                ${email.delivered_at ? `<p><strong>Delivered:</strong> ${new Date(email.delivered_at).toLocaleString()}</p>` : ''}
                            </div>
                            
                            ${email.body_content ? `
                            <div class="detail-group">
                                <h4>Email Body</h4>
                                <div class="email-body">
                                    ${email.body_format === 'HTML' ? email.body_content : `<pre>${email.body_content}</pre>`}
                                </div>
                            </div>
                            ` : ''}
                            
                            ${email.error_message ? `
                            <div class="detail-group">
                                <h4>Error Details</h4>
                                <p style="color: #e74c3c;">${email.error_message}</p>
                            </div>
                            ` : ''}
                            
                            ${email.tracking_data ? `
                            <div class="detail-group">
                                <h4>Tracking Information</h4>
                                <p><strong>Opens:</strong> ${email.tracking_data.open_count || 0}</p>
                                <p><strong>Clicks:</strong> ${email.tracking_data.click_count || 0}</p>
                                <p><strong>Last Opened:</strong> ${email.tracking_data.last_opened_at ? new Date(email.tracking_data.last_opened_at).toLocaleString() : 'Never'}</p>
                            </div>
                            ` : ''}
                        </div>
                    `;
                    
                    document.getElementById('emailModal').style.display = 'block';
                }
            } catch (error) {
                console.error('Error loading email details:', error);
            }
        }

        // Close modal
        function closeModal() {
            document.getElementById('emailModal').style.display = 'none';
        }

        // Resend email
        async function resendEmail(emailId) {
            if (!confirm('Are you sure you want to resend this email?')) return;
            
            try {
                const response = await fetch(`/api/outbox/emails/${emailId}/resend`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                if (response.ok) {
                    alert('Email queued for resending successfully!');
                    loadEmails(currentPage);
                    loadStats(currentTenantId);
                } else {
                    const result = await response.json();
                    alert('Error: ' + (result.message || 'Failed to resend email'));
                }
            } catch (error) {
                console.error('Error resending email:', error);
                alert('Error resending email');
            }
        }

        // Delete email
        async function deleteEmail(emailId) {
            if (!confirm('Are you sure you want to delete this email? This action cannot be undone.')) return;
            
            try {
                const response = await fetch(`/api/outbox/emails/${emailId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                if (response.ok) {
                    alert('Email deleted successfully!');
                    loadEmails(currentPage);
                    loadStats(currentTenantId);
                } else {
                    const result = await response.json();
                    alert('Error: ' + (result.message || 'Failed to delete email'));
                }
            } catch (error) {
                console.error('Error deleting email:', error);
                alert('Error deleting email');
            }
        }

        // Event listeners
        document.getElementById('tenant_id').addEventListener('change', function() {
            loadEmails(1);
            loadStats(this.value);
        });

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('emailModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            toggleSidebar();
        });

        // Close sidebar on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const sidebar = document.getElementById('sidebar');
                if (sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            }
        });
    </script>
</body>
</html>
