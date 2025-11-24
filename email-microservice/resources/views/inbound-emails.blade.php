<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbound Emails - AltimaCRM Email Microservice</title>
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
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .filters {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .filters h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3rem;
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
        }

        .filter-group label {
            color: #34495e;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .filter-group select,
        .filter-group input {
            padding: 10px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #3498db;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #229954;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #e67e22;
        }

        .emails-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .emails-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .emails-header h3 {
            color: #2c3e50;
            font-size: 1.5rem;
        }

        .email-item {
            border: 1px solid #b8eaf7;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .email-item:hover {
            border-color: #3498db;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.1);
            transform: translateY(-2px);
        }

        .email-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .email-sender {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sender-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }

        .sender-info h4 {
            color: #2c3e50;
            font-size: 1.1rem;
            margin-bottom: 2px;
        }

        .sender-info p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .email-meta {
            text-align: right;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .email-subject {
            color: #2c3e50;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .email-preview {
            color: #7f8c8d;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .email-tags {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .tag {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .tag-new {
            background: #e8f5e8;
            color: #27ae60;
        }

        .tag-processed {
            background: #e3f2fd;
            color: #2196f3;
        }

        .tag-queued {
            background: #fff3e0;
            color: #ff9800;
        }

        .tag-delivered {
            background: #e8f5e8;
            color: #4caf50;
        }

        .tag-failed {
            background: #ffebee;
            color: #f44336;
        }

        .tag-reply {
            background: #f3e5f5;
            color: #9c27b0;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination button {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination button:hover:not(:disabled) {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #95a5a6;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .filter-row {
                grid-template-columns: 1fr;
            }

            .email-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .email-meta {
                text-align: left;
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
            <a href="/inbound-emails" class="sidebar-menu-item active">
                <span>📥</span> Inbound Emails
            </a>
            <a href="/outbox" class="sidebar-menu-item">
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
            <h1>
                📥 Inbound Emails
                <span style="font-size: 1rem; color: #7f8c8d; font-weight: normal;">Track incoming emails and replies</span>
            </h1>
            <p>Monitor and manage all incoming emails, replies, and conversations for your tenants.</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="stat-number" id="totalEmails">-</div>
                <div class="stat-label">Total Emails</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="newEmails">-</div>
                <div class="stat-label">New</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="replyEmails">-</div>
                <div class="stat-label">Replies</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="deliveredEmails">-</div>
                <div class="stat-label">Delivered</div>
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
                    <label for="replyFilter">Type</label>
                    <select id="replyFilter">
                        <option value="">All Types</option>
                        <option value="true">Replies Only</option>
                        <option value="false">Non-Replies</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="fromEmailFilter">From Email</label>
                    <input type="email" id="fromEmailFilter" placeholder="Filter by sender email">
                </div>
            </div>
            <div class="filter-row">
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
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button class="btn btn-warning" onclick="clearFilters()">Clear Filters</button>
                </div>
            </div>
        </div>

        <!-- Emails List -->
        <div class="emails-container">
            <div class="emails-header">
                <h3>📧 Inbound Emails</h3>
                <div>
                    <button class="btn btn-success" onclick="refreshEmails()">🔄 Refresh</button>
                    <button class="btn btn-primary" onclick="testInboundEmail()">🧪 Test Inbound</button>
                </div>
            </div>
            
            <div id="emailsList">
                <div class="loading">Loading inbound emails...</div>
            </div>
            
            <div id="pagination" class="pagination" style="display: none;">
                <!-- Pagination will be generated here -->
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
                const result = await response.json();
                
                if (result.success) {
                    const select = document.getElementById('tenantSelect');
                    select.innerHTML = '<option value="">Select Tenant</option>';
                    
                    result.data.forEach(tenant => {
                        const option = document.createElement('option');
                        option.value = tenant.tenant_id;
                        option.textContent = tenant.tenant_name;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading tenants:', error);
            }
        }

        async function loadStats() {
            const tenantId = document.getElementById('tenantSelect').value;
            if (!tenantId) return;

            try {
                const response = await fetch(`/api/email/inbound/stats?tenant_id=${tenantId}`);
                const result = await response.json();
                
                if (result.success) {
                    const stats = result.data;
                    document.getElementById('totalEmails').textContent = stats.total_emails || 0;
                    document.getElementById('newEmails').textContent = stats.new_emails || 0;
                    document.getElementById('replyEmails').textContent = stats.reply_emails || 0;
                    document.getElementById('deliveredEmails').textContent = stats.delivered_emails || 0;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        async function loadEmails() {
            const tenantId = document.getElementById('tenantSelect').value;
            if (!tenantId) {
                document.getElementById('emailsList').innerHTML = '<div class="empty-state"><h3>Please select a tenant</h3><p>Choose a tenant to view inbound emails</p></div>';
                return;
            }

            try {
                const params = new URLSearchParams({
                    tenant_id: tenantId,
                    page: currentPage,
                    ...currentFilters
                });

                const response = await fetch(`/api/email/inbound?${params}`);
                const result = await response.json();
                
                if (result.success) {
                    displayEmails(result.data);
                    displayPagination(result.pagination);
                } else {
                    document.getElementById('emailsList').innerHTML = '<div class="empty-state"><h3>Error loading emails</h3><p>' + result.message + '</p></div>';
                }
            } catch (error) {
                console.error('Error loading emails:', error);
                document.getElementById('emailsList').innerHTML = '<div class="empty-state"><h3>Error loading emails</h3><p>Please try again</p></div>';
            }
        }

        function displayEmails(emails) {
            const container = document.getElementById('emailsList');
            
            if (emails.length === 0) {
                container.innerHTML = '<div class="empty-state"><h3>No inbound emails found</h3><p>No emails match your current filters</p></div>';
                return;
            }

            const emailsHtml = emails.map(email => {
                const receivedDate = new Date(email.received_at).toLocaleString();
                const senderInitial = email.from_name ? email.from_name.charAt(0).toUpperCase() : email.from_email.charAt(0).toUpperCase();
                const preview = email.body_content ? email.body_content.substring(0, 150) + '...' : 'No content';
                
                return `
                    <div class="email-item" onclick="viewEmail('${email.id}')">
                        <div class="email-header">
                            <div class="email-sender">
                                <div class="sender-avatar">${senderInitial}</div>
                                <div class="sender-info">
                                    <h4>${email.from_name || email.from_email}</h4>
                                    <p>${email.from_email}</p>
                                </div>
                            </div>
                            <div class="email-meta">
                                <div>${receivedDate}</div>
                                <div>Status: ${email.status}</div>
                            </div>
                        </div>
                        <div class="email-subject">${email.subject}</div>
                        <div class="email-preview">${preview}</div>
                        <div class="email-tags">
                            <span class="tag tag-${email.status}">${email.status}</span>
                            ${email.is_reply ? '<span class="tag tag-reply">Reply</span>' : ''}
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = emailsHtml;
        }

        function displayPagination(pagination) {
            const container = document.getElementById('pagination');
            
            if (pagination.last_page <= 1) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'flex';
            
            let paginationHtml = '';
            
            // Previous button
            paginationHtml += `<button ${pagination.current_page <= 1 ? 'disabled' : ''} onclick="changePage(${pagination.current_page - 1})">Previous</button>`;
            
            // Page numbers
            for (let i = 1; i <= pagination.last_page; i++) {
                if (i === pagination.current_page) {
                    paginationHtml += `<button style="background: #3498db; color: white;" disabled>${i}</button>`;
                } else {
                    paginationHtml += `<button onclick="changePage(${i})">${i}</button>`;
                }
            }
            
            // Next button
            paginationHtml += `<button ${pagination.current_page >= pagination.last_page ? 'disabled' : ''} onclick="changePage(${pagination.current_page + 1})">Next</button>`;
            
            container.innerHTML = paginationHtml;
        }

        function changePage(page) {
            currentPage = page;
            loadEmails();
        }

        function applyFilters() {
            currentFilters = {
                status: document.getElementById('statusSelect').value,
                is_reply: document.getElementById('replyFilter').value,
                from_email: document.getElementById('fromEmailFilter').value,
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
            document.getElementById('replyFilter').value = '';
            document.getElementById('fromEmailFilter').value = '';
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

        function viewEmail(emailId) {
            alert('View email details for: ' + emailId + '\n\nThis would open a detailed view of the email.');
        }

        async function testInboundEmail() {
            const tenantId = document.getElementById('tenantSelect').value;
            if (!tenantId) {
                alert('Please select a tenant first');
                return;
            }

            const testData = {
                tenant_id: tenantId,
                provider_id: '0198a819-e5d3-703a-a39a-1b77e3ece687', // Use existing provider
                message_id: 'test-inbound-' + Date.now(),
                subject: 'Test Inbound Email - ' + new Date().toLocaleString(),
                from_email: 'test@example.com',
                from_name: 'Test Sender',
                to_emails: ['nishant.joshi@estatic-infotech.com'],
                body_format: 'Text',
                body_content: 'This is a test inbound email to verify the system is working correctly.',
                is_reply: true,
                in_reply_to: 'test-outbound-message-id'
            };

            try {
                const response = await fetch('/api/email/inbound', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(testData)
                });

                const result = await response.json();
                
                if (result.success) {
                    alert('✅ Test inbound email created successfully!\n\nEmail ID: ' + result.data.id);
                    loadEmails();
                    loadStats();
                } else {
                    alert('❌ Failed to create test email: ' + result.message);
                }
            } catch (error) {
                console.error('Error creating test email:', error);
                alert('❌ Error creating test email: ' + error.message);
            }
        }

        // Auto-refresh when tenant changes
        document.getElementById('tenantSelect').addEventListener('change', function() {
            currentTenant = this.value;
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
