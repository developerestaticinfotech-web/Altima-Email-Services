<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Replied Emails - AltimaCRM Email Microservice</title>
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

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .filters {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .filters h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.5rem;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .filter-group input,
        .filter-group select {
            padding: 12px 15px;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            background: white;
        }

        .filter-group input:focus,
        .filter-group select:focus {
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
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4);
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

        .emails-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .emails-header h3 {
            color: #2c3e50;
            font-size: 1.5rem;
        }

        .email-thread-container {
            margin-bottom: 30px;
            border: 2px solid #e0e6ed;
            border-radius: 12px;
            overflow: hidden;
            background: #f8f9fa;
        }

        .email-item {
            border: none;
            border-radius: 0;
            padding: 20px;
            transition: all 0.3s ease;
            background: white;
            border-bottom: 1px solid #e0e6ed;
        }

        .email-item:last-child {
            border-bottom: none;
        }

        .email-reply {
            background: white;
            border-bottom: 2px solid #667eea;
        }

        .thread-original-email {
            background: #f8f9fa;
            padding: 20px;
            border-top: 1px solid #e0e6ed;
        }

        .email-item:hover {
            background: #f8f9fa;
        }

        .email-reply:hover {
            background: #f0f4ff;
        }

        .email-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .email-sender {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sender-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .sender-info h4 {
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 1rem;
            font-weight: 600;
        }

        .sender-info p {
            color: #7f8c8d;
            font-size: 0.85rem;
            margin: 0;
        }

        .email-meta {
            text-align: right;
            color: #7f8c8d;
            font-size: 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 5px;
            align-items: flex-end;
        }

        .email-subject {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .email-preview {
            color: #555;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 10px;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 100px;
            overflow: hidden;
        }

        .email-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .tag {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .tag-reply {
            background: #fce4ec;
            color: #c2185b;
            font-weight: 600;
        }

        .tag-new {
            background: #d4edda;
            color: #155724;
        }

        .tag-processed {
            background: #cce5ff;
            color: #004085;
        }

        .tag-queued {
            background: #fff3cd;
            color: #856404;
        }

        .tag-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }

        .tag-failed {
            background: #f8d7da;
            color: #721c24;
        }

        .tag-processed {
            background: #cce5ff;
            color: #004085;
        }

        .tag-new {
            background: #fff3cd;
            color: #856404;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .thread-info {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            margin: 5px 0;
            font-size: 0.85rem;
            color: #6c757d;
            border-left: 3px solid #667eea;
        }

        .thread-info strong {
            color: #2c3e50;
            margin-right: 8px;
        }

        .email-preview {
            color: #555;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
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
                left: 10px;
                top: 10px;
                padding: 10px 12px;
                font-size: 1rem;
            }
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
            max-width: 900px;
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
            margin: 0;
        }

        .close {
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            color: #7f8c8d;
            transition: color 0.3s ease;
            line-height: 1;
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
            margin: 5px 0;
        }

        .email-body-content {
            background: white;
            border: 2px solid #e0e6ed;
            border-radius: 10px;
            padding: 20px;
            margin-top: 10px;
            white-space: pre-wrap;
            word-wrap: break-word;
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
            <a href="/outbox" class="sidebar-menu-item">
                <span>📤</span> Outbox
            </a>
            <a href="/replied-emails" class="sidebar-menu-item active">
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
            <h1>💬 Replied Emails</h1>
            <p>View and manage customer replies to your emails</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number" id="totalReplies">-</div>
                <div class="stat-label">Total Replies</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="todayReplies">-</div>
                <div class="stat-label">Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="thisWeekReplies">-</div>
                <div class="stat-label">This Week</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="thisMonthReplies">-</div>
                <div class="stat-label">This Month</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <h3>🔍 Filters</h3>
            <div class="filter-row">
                <div class="filter-group">
                    <label for="tenantSelect">Tenant</label>
                    <select id="tenantSelect">
                        <option value="">Select Tenant</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="statusSelect">Status</label>
                    <select id="statusSelect">
                        <option value="">All Statuses</option>
                        <option value="new">New</option>
                        <option value="processed">Processed</option>
                        <option value="queued">Queued</option>
                        <option value="delivered">Delivered</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="fromEmailFilter">From Email</label>
                    <input type="email" id="fromEmailFilter" placeholder="Filter by sender email">
                </div>
                <div class="filter-group">
                    <label for="threadIdFilter">Thread ID</label>
                    <input type="text" id="threadIdFilter" placeholder="Filter by thread ID">
                </div>
            </div>
            <div class="filter-row">
                <div class="filter-group">
                    <label for="inReplyToFilter">In Reply To</label>
                    <input type="text" id="inReplyToFilter" placeholder="Filter by message ID">
                </div>
                <div class="filter-group">
                    <label for="dateFrom">Date From</label>
                    <input type="date" id="dateFrom">
                </div>
                <div class="filter-group">
                    <label for="dateTo">Date To</label>
                    <input type="date" id="dateTo">
                </div>
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                </div>
            </div>
            <div class="filter-row">
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button class="btn btn-warning" onclick="clearFilters()">Clear Filters</button>
                </div>
            </div>
        </div>

        <!-- Emails List -->
        <div class="emails-container">
            <div class="emails-header">
                <h3>📧 Replied Emails</h3>
                <div>
                    <button class="btn btn-success" onclick="refreshEmails()">🔄 Refresh</button>
                </div>
            </div>
            
            <div id="emailsList">
                <div class="loading">Loading replied emails...</div>
            </div>
            
            <div id="pagination" class="pagination" style="display: none;">
                <!-- Pagination will be generated here -->
            </div>
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
        let currentTenant = '';
        let currentPage = 1;
        let currentFilters = {};

        // Load tenants on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTenants();
            loadStats();
        });

        async function loadTenants() {
            try {
                const response = await fetch('/api/email/tenants');
                if (response.ok) {
                    const result = await response.json();
                    const tenants = result.data || [];
                    
                    const select = document.getElementById('tenantSelect');
                    select.innerHTML = '<option value="">Select Tenant</option>' +
                        tenants.map(tenant => `<option value="${tenant.tenant_id}">${tenant.tenant_name}</option>`).join('');
                }
            } catch (error) {
                console.error('Error loading tenants:', error);
            }
        }

        async function loadStats() {
            const tenantId = document.getElementById('tenantSelect').value;
            if (!tenantId) {
                document.getElementById('totalReplies').textContent = '-';
                document.getElementById('todayReplies').textContent = '-';
                document.getElementById('thisWeekReplies').textContent = '-';
                document.getElementById('thisMonthReplies').textContent = '-';
                return;
            }

            try {
                const today = new Date().toISOString().split('T')[0];
                const weekAgo = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
                const monthAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

                // Total replies
                const totalResponse = await fetch(`/api/email/replies?tenant_id=${tenantId}&per_page=1`);
                if (totalResponse.ok) {
                    const totalResult = await totalResponse.json();
                    if (totalResult.success) {
                        document.getElementById('totalReplies').textContent = totalResult.pagination?.total || 0;
                    }
                }

                // Today's replies
                const todayResponse = await fetch(`/api/email/replies?tenant_id=${tenantId}&date_from=${today}&per_page=1`);
                if (todayResponse.ok) {
                    const todayResult = await todayResponse.json();
                    if (todayResult.success) {
                        document.getElementById('todayReplies').textContent = todayResult.pagination?.total || 0;
                    }
                }

                // This week's replies
                const weekResponse = await fetch(`/api/email/replies?tenant_id=${tenantId}&date_from=${weekAgo}&per_page=1`);
                if (weekResponse.ok) {
                    const weekResult = await weekResponse.json();
                    if (weekResult.success) {
                        document.getElementById('thisWeekReplies').textContent = weekResult.pagination?.total || 0;
                    }
                }

                // This month's replies
                const monthResponse = await fetch(`/api/email/replies?tenant_id=${tenantId}&date_from=${monthAgo}&per_page=1`);
                if (monthResponse.ok) {
                    const monthResult = await monthResponse.json();
                    if (monthResult.success) {
                        document.getElementById('thisMonthReplies').textContent = monthResult.pagination?.total || 0;
                    }
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        async function loadEmails() {
            const tenantId = document.getElementById('tenantSelect').value;
            if (!tenantId) {
                document.getElementById('emailsList').innerHTML = '<div class="empty-state"><h3>Please select a tenant</h3><p>Choose a tenant to view replied emails</p></div>';
                return;
            }

            try {
                const params = new URLSearchParams({
                    tenant_id: tenantId,
                    page: currentPage,
                    per_page: 20
                });

                // Add filters
                if (currentFilters.status) params.append('status', currentFilters.status);
                if (currentFilters.from_email) params.append('from_email', currentFilters.from_email);
                if (currentFilters.thread_id) params.append('thread_id', currentFilters.thread_id);
                if (currentFilters.in_reply_to) params.append('in_reply_to', currentFilters.in_reply_to);
                if (currentFilters.date_from) params.append('date_from', currentFilters.date_from);
                if (currentFilters.date_to) params.append('date_to', currentFilters.date_to);

                const response = await fetch(`/api/email/replies?${params}`);
                const result = await response.json();
                
                if (result.success) {
                    displayEmails(result.data);
                    // Always update pagination if it exists
                    if (result.pagination) {
                        displayPagination(result.pagination);
                    } else {
                        // Hide pagination if no pagination data
                        document.getElementById('pagination').style.display = 'none';
                    }
                } else {
                    document.getElementById('emailsList').innerHTML = '<div class="empty-state"><h3>Error loading emails</h3><p>' + result.message + '</p></div>';
                    document.getElementById('pagination').style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading emails:', error);
                document.getElementById('emailsList').innerHTML = '<div class="empty-state"><h3>Error loading emails</h3><p>Please try again</p></div>';
            }
        }

        function displayEmails(emails) {
            const container = document.getElementById('emailsList');
            
            if (emails.length === 0) {
                container.innerHTML = '<div class="empty-state"><h3>No replied emails found</h3><p>No replies match your current filters</p></div>';
                return;
            }

            const emailsHtml = emails.map(email => {
                const receivedDate = new Date(email.received_at).toLocaleString();
                const senderInitial = email.from_name ? email.from_name.charAt(0).toUpperCase() : email.from_email.charAt(0).toUpperCase();
                
                // Clean email content - remove MIME boundaries, headers, and technical content
                let cleanedContent = 'No content';
                if (email.body_content) {
                    let content = email.body_content;
                    
                    // Remove MIME boundaries (lines starting with --)
                    content = content.replace(/^--[a-zA-Z0-9]+.*$/gm, '');
                    
                    // Remove Content-Type headers
                    content = content.replace(/Content-Type:\s*[^\n]+/gi, '');
                    content = content.replace(/Content-Transfer-Encoding:\s*[^\n]+/gi, '');
                    content = content.replace(/charset="?[^"\n]+"?/gi, '');
                    content = content.replace(/format=[^\n]+/gi, '');
                    content = content.replace(/delsp=[^\n]+/gi, '');
                    
                    // Remove HTML tags if present
                    content = content.replace(/<[^>]+>/g, '');
                    
                    // Remove multiple newlines and whitespace
                    content = content.replace(/\n{3,}/g, '\n\n');
                    content = content.replace(/[ \t]{2,}/g, ' ');
                    
                    // Trim
                    content = content.trim();
                    if (content.length > 0) {
                        cleanedContent = content;
                    }
                }
                
                // Format subject
                let subject = email.subject || 'No Subject';
                
                // Get original email info - check both repliedToOutbound and thread_emails
                let originalEmail = null;
                let originalEmailHtml = '';
                
                // First try to get from repliedToOutbound (if it's a reply to an outbound email)
                if (email.repliedToOutbound) {
                    originalEmail = email.repliedToOutbound;
                } 
                // Otherwise, try to get from thread_emails (if it's a reply to another inbound email)
                else if (email.thread_emails && email.thread_emails.length > 0) {
                    // Find the original email (not a reply) in the thread
                    originalEmail = email.thread_emails.find(e => !e.is_reply) || email.thread_emails[0];
                }
                
                if (originalEmail) {
                    const originalSenderInitial = (originalEmail.from_name || originalEmail.from || originalEmail.from_email || 'O').charAt(0).toUpperCase();
                    const originalDate = originalEmail.sent_at ? new Date(originalEmail.sent_at).toLocaleString() : 
                                       (originalEmail.received_at ? new Date(originalEmail.received_at).toLocaleString() : 'N/A');
                    let originalContent = originalEmail.body_content || 'No content';
                    
                    // Clean original email content
                    originalContent = originalContent.replace(/^--[a-zA-Z0-9]+.*$/gm, '');
                    originalContent = originalContent.replace(/Content-Type:\s*[^\n]+/gi, '');
                    originalContent = originalContent.replace(/<[^>]+>/g, '');
                    originalContent = originalContent.replace(/\n{3,}/g, '\n\n').trim();
                    
                    const originalStatus = originalEmail.status || 'new';
                    
                    originalEmailHtml = `
                        <div class="thread-original-email">
                            <div class="email-header">
                                <div class="email-sender">
                                    <div class="sender-avatar" style="background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);">${originalSenderInitial}</div>
                                    <div class="sender-info">
                                        <h4>${originalEmail.from_name || originalEmail.from || originalEmail.from_email || 'Original Sender'}</h4>
                                        <p style="font-size: 0.85em; color: #6c757d;">${originalEmail.from || originalEmail.from_email || 'N/A'}</p>
                                    </div>
                                </div>
                                <div class="email-meta">
                                    <div style="font-size: 0.9em; color: #6c757d;">${originalDate}</div>
                                    <div style="font-size: 0.85em; color: #6c757d; margin-top: 5px;">Status: ${originalStatus}</div>
                                </div>
                            </div>
                            <div class="email-subject">${originalEmail.subject || 'No Subject'}</div>
                            <div class="email-preview" style="color: #555; line-height: 1.6; margin: 10px 0;">${originalContent.length > 300 ? originalContent.substring(0, 300) + '...' : originalContent}</div>
                            <div class="email-tags" style="margin-top: 15px;">
                                <span class="tag tag-${originalStatus}">${originalStatus.toUpperCase()}</span>
                            </div>
                        </div>
                    `;
                }
                
                return `
                    <div class="email-thread-container">
                        <!-- Reply Email (Top) -->
                        <div class="email-item email-reply">
                            <div class="email-header">
                                <div class="email-sender">
                                    <div class="sender-avatar">${senderInitial}</div>
                                    <div class="sender-info">
                                        <h4>${email.from_name || email.from_email}</h4>
                                        <p style="font-size: 0.85em; color: #6c757d;">${email.from_email}</p>
                                    </div>
                                </div>
                                <div class="email-meta">
                                    <div style="font-size: 0.9em; color: #6c757d;">${receivedDate}</div>
                                    <div style="font-size: 0.85em; color: #6c757d; margin-top: 5px;">Status: ${email.status}</div>
                                </div>
                            </div>
                            <div class="email-subject">${subject}</div>
                            <div class="email-preview" style="color: #555; line-height: 1.6; margin: 10px 0;">${cleanedContent.length > 300 ? cleanedContent.substring(0, 300) + '...' : cleanedContent}</div>
                            <div class="email-tags" style="margin-top: 15px; display: flex; gap: 10px; align-items: center;">
                                <span class="tag tag-${email.status}">${email.status.toUpperCase()}</span>
                                <span class="tag tag-reply">REPLY</span>
                            </div>
                        </div>
                        
                        <!-- Original Email (Bottom) -->
                        ${originalEmailHtml}
                    </div>
                `;
            }).join('');

            container.innerHTML = emailsHtml;
        }

        function displayPagination(pagination) {
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
            paginationHtml += `<button ${pagination.current_page <= 1 ? 'disabled' : ''} onclick="changePage(${pagination.current_page - 1})">Previous</button>`;
            
            // Page numbers - show all pages if 10 or less, otherwise show smart pagination
            if (pagination.last_page <= 10) {
                // Show all pages
                for (let i = 1; i <= pagination.last_page; i++) {
                    if (i === pagination.current_page) {
                        paginationHtml += `<button style="background: #667eea; color: white; border-color: #667eea;" disabled>${i}</button>`;
                    } else {
                        paginationHtml += `<button onclick="changePage(${i})">${i}</button>`;
                    }
                }
            } else {
                // Smart pagination - show first, last, current, and nearby pages
                // Always show first page
                if (pagination.current_page > 3) {
                    paginationHtml += `<button onclick="changePage(1)">1</button>`;
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
                        paginationHtml += `<button onclick="changePage(${i})">${i}</button>`;
                    }
                }
                
                // Always show last page
                if (pagination.current_page < pagination.last_page - 2) {
                    if (pagination.current_page < pagination.last_page - 3) {
                        paginationHtml += `<button disabled style="cursor: default; background: transparent; border: none;">...</button>`;
                    }
                    paginationHtml += `<button onclick="changePage(${pagination.last_page})">${pagination.last_page}</button>`;
                }
            }
            
            // Next button
            paginationHtml += `<button ${pagination.current_page >= pagination.last_page ? 'disabled' : ''} onclick="changePage(${pagination.current_page + 1})">Next</button>`;
            
            // Pagination info - always show
            paginationHtml += `<span class="pagination-info" style="margin-left: 15px;">Page ${pagination.current_page} of ${pagination.last_page} (${pagination.from || 0}-${pagination.to || 0} of ${pagination.total})</span>`;
            
            container.innerHTML = paginationHtml;
            
            // Debug log
            console.log('Pagination updated:', pagination);
        }

        function changePage(page) {
            currentPage = page;
            loadEmails();
        }

        function applyFilters() {
            currentFilters = {
                status: document.getElementById('statusSelect').value,
                from_email: document.getElementById('fromEmailFilter').value,
                thread_id: document.getElementById('threadIdFilter').value,
                in_reply_to: document.getElementById('inReplyToFilter').value,
                date_from: document.getElementById('dateFrom').value,
                date_to: document.getElementById('dateTo').value
            };
            
            // Remove empty filters
            Object.keys(currentFilters).forEach(key => {
                if (!currentFilters[key]) {
                    delete currentFilters[key];
                }
            });
            
            currentPage = 1;
            loadEmails();
            loadStats();
        }

        function clearFilters() {
            document.getElementById('statusSelect').value = '';
            document.getElementById('fromEmailFilter').value = '';
            document.getElementById('threadIdFilter').value = '';
            document.getElementById('inReplyToFilter').value = '';
            document.getElementById('dateFrom').value = '';
            document.getElementById('dateTo').value = '';
            currentFilters = {};
            currentPage = 1;
            loadEmails();
            loadStats();
        }

        function refreshEmails() {
            loadEmails();
            loadStats();
        }

        async function viewEmail(emailId) {
            try {
                const response = await fetch(`/api/email/inbound/${emailId}`);
                if (response.ok) {
                    const result = await response.json();
                    if (result.success && result.data) {
                        const email = result.data;
                        
                        // Clean email body for display
                        let bodyContent = email.body_content || 'No content';
                        if (email.body_format === 'HTML') {
                            // For HTML, create a safe preview
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = bodyContent;
                            bodyContent = tempDiv.textContent || tempDiv.innerText || '';
                        }
                        
                        // Remove MIME boundaries and headers
                        bodyContent = bodyContent.replace(/^--[a-zA-Z0-9]+.*$/gm, '');
                        bodyContent = bodyContent.replace(/Content-Type:\s*[^\n]+/gi, '');
                        bodyContent = bodyContent.replace(/Content-Transfer-Encoding:\s*[^\n]+/gi, '');
                        bodyContent = bodyContent.replace(/charset="?[^"\n]+"?/gi, '');
                        bodyContent = bodyContent.replace(/\n{3,}/g, '\n\n').trim();
                        
                        const toEmails = Array.isArray(email.to_emails) ? email.to_emails.join(', ') : (email.to_emails || 'N/A');
                        const ccEmails = Array.isArray(email.cc_emails) ? email.cc_emails.join(', ') : (email.cc_emails || '');
                        const bccEmails = Array.isArray(email.bcc_emails) ? email.bcc_emails.join(', ') : (email.bcc_emails || '');
                        
                        document.getElementById('modal-title').textContent = email.subject || 'Email Details';
                        document.getElementById('modal-body').innerHTML = `
                            <div class="email-details">
                                <div class="detail-group">
                                    <h4>From</h4>
                                    <p><strong>Name:</strong> ${email.from_name || 'N/A'}</p>
                                    <p><strong>Email:</strong> ${email.from_email || 'N/A'}</p>
                                </div>
                                
                                <div class="detail-group">
                                    <h4>To</h4>
                                    <p>${toEmails}</p>
                                    ${ccEmails !== 'N/A' && ccEmails ? `<p><strong>CC:</strong> ${ccEmails}</p>` : ''}
                                    ${bccEmails !== 'N/A' && bccEmails ? `<p><strong>BCC:</strong> ${bccEmails}</p>` : ''}
                                </div>
                                
                                <div class="detail-group">
                                    <h4>Details</h4>
                                    <p><strong>Subject:</strong> ${email.subject || 'No Subject'}</p>
                                    <p><strong>Status:</strong> <span class="tag tag-${email.status}">${email.status.toUpperCase()}</span></p>
                                    <p><strong>Received:</strong> ${new Date(email.received_at).toLocaleString()}</p>
                                    ${email.processed_at ? `<p><strong>Processed:</strong> ${new Date(email.processed_at).toLocaleString()}</p>` : ''}
                                </div>
                                
                                ${email.thread_id ? `
                                <div class="detail-group">
                                    <h4>Thread Information</h4>
                                    <p><strong>Thread ID:</strong> <code style="font-size: 0.85em;">${email.thread_id}</code></p>
                                    ${email.in_reply_to && email.in_reply_to !== email.thread_id ? `<p><strong>In Reply To:</strong> <code style="font-size: 0.85em;">${email.in_reply_to}</code></p>` : ''}
                                    ${email.repliedToOutbound ? `<p><strong>Original Email:</strong> "${email.repliedToOutbound.subject || 'N/A'}"</p>` : ''}
                                </div>
                                ` : ''}
                                
                                <div class="detail-group">
                                    <h4>Email Content</h4>
                                    <div class="email-body-content">${bodyContent || 'No content available'}</div>
                                </div>
                                
                                ${email.attachments && email.attachments.length > 0 ? `
                                <div class="detail-group">
                                    <h4>Attachments (${email.attachments.length})</h4>
                                    ${email.attachments.map(att => `<p>📎 ${att.filename || att.name || 'Attachment'}</p>`).join('')}
                                </div>
                                ` : ''}
                            </div>
                        `;
                        
                        document.getElementById('emailModal').style.display = 'block';
                    } else {
                        alert('Failed to load email details: ' + (result.message || 'Unknown error'));
                    }
                } else {
                    const errorData = await response.json().catch(() => ({ message: 'Failed to load email' }));
                    alert('Error: ' + (errorData.message || 'Failed to load email details'));
                }
            } catch (error) {
                console.error('Error loading email details:', error);
                alert('Error loading email details. Please try again.');
            }
        }

        function closeModal() {
            document.getElementById('emailModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('emailModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Event listeners
        document.getElementById('tenantSelect').addEventListener('change', function() {
            currentPage = 1;
            loadEmails();
            loadStats();
        });

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

