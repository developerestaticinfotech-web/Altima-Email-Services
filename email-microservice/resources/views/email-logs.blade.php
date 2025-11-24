<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Email Logs - AltimaCRM Email Microservice</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 25%, #16213e 50%, #0f3460 75%, #533483 100%);
            min-height: 100vh;
            color: #e0e0e0;
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
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 0 0 20px rgba(138, 43, 226, 0.5);
            background: linear-gradient(45deg, #8a2be2, #00d4ff, #8a2be2);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradientShift 3s ease-in-out infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .nav-links {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .nav-links a {
            display: inline-block;
            background: rgba(138, 43, 226, 0.2);
            color: #e0e0e0;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 25px;
            margin: 0 10px;
            transition: all 0.3s ease;
            border: 1px solid rgba(138, 43, 226, 0.3);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(138, 43, 226, 0.2);
        }
        
        .nav-links a:hover {
            background: rgba(138, 43, 226, 0.4);
            transform: translateY(-2px);
            border-color: rgba(138, 43, 226, 0.6);
            box-shadow: 0 6px 20px rgba(138, 43, 226, 0.4);
            color: #ffffff;
        }
        
        .card {
            background: rgba(26, 26, 46, 0.8);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(138, 43, 226, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .card h2 {
            color: #8a2be2;
            margin-bottom: 20px;
            font-size: 1.5rem;
            text-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-item {
            background: rgba(26, 26, 46, 0.9);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 1px solid rgba(138, 43, 226, 0.2);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-3px);
            border-color: rgba(138, 43, 226, 0.4);
            box-shadow: 0 8px 25px rgba(138, 43, 226, 0.2);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(45deg, #8a2be2, #00d4ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #b0b0b0;
            font-size: 0.9rem;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background 0.3s ease;
            margin: 5px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #8a2be2, #9d4edd);
            color: white;
            border: 1px solid rgba(138, 43, 226, 0.3);
            box-shadow: 0 4px 15px rgba(138, 43, 226, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #9d4edd, #8a2be2);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(138, 43, 226, 0.4);
            border-color: rgba(138, 43, 226, 0.6);
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #495057, #6c757d);
            color: white;
            border: 1px solid rgba(108, 117, 125, 0.3);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(45deg, #6c757d, #495057);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
            border-color: rgba(108, 117, 125, 0.6);
        }
        
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: 1px solid rgba(40, 167, 69, 0.3);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-success:hover {
            background: linear-gradient(45deg, #20c997, #28a745);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            border-color: rgba(40, 167, 69, 0.6);
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
            border: 1px solid rgba(220, 53, 69, 0.3);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            background: linear-gradient(45deg, #c82333, #dc3545);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
            border-color: rgba(220, 53, 69, 0.6);
        }
        
        .btn-warning {
            background: linear-gradient(45deg, #ffc107, #ffca2c);
            color: #212529;
            border: 1px solid rgba(255, 193, 7, 0.3);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-warning:hover {
            background: linear-gradient(45deg, #ffca2c, #ffc107);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
            border-color: rgba(255, 193, 7, 0.6);
        }
        
        .btn-info {
            background: linear-gradient(45deg, #17a2b8, #20c997);
            color: white;
            border: 1px solid rgba(23, 162, 184, 0.3);
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.2);
            transition: all 0.3s ease;
        }
        
        .btn-info:hover {
            background: linear-gradient(45deg, #20c997, #17a2b8);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);
            border-color: rgba(23, 162, 184, 0.6);
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }
        
        /* Table Styles */
        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 1px solid rgba(138, 43, 226, 0.2);
        }
        
        .email-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(15, 15, 35, 0.8);
            font-size: 0.9rem;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .email-table th {
            background: linear-gradient(45deg, #8a2be2, #9d4edd);
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 1px solid rgba(138, 43, 226, 0.3);
        }
        
        .email-table td {
            padding: 12px;
            border-bottom: 1px solid rgba(138, 43, 226, 0.1);
            vertical-align: top;
            color: #e0e0e0;
        }
        
        .email-table tr:hover {
            background-color: rgba(138, 43, 226, 0.1);
        }
        
        .email-table tr:nth-child(even) {
            background-color: rgba(26, 26, 46, 0.6);
        }
        
        .email-table tr:nth-child(odd) {
            background-color: rgba(15, 15, 35, 0.8);
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-sent {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-bounced {
            background: #f5c6cb;
            color: #721c24;
        }
        
        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #b0b0b0;
        }
        
        .error {
            text-align: center;
            padding: 40px;
            color: #ff6b6b;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }
        
        .pagination button {
            padding: 8px 12px;
            border: 1px solid rgba(138, 43, 226, 0.3);
            background: rgba(26, 26, 46, 0.8);
            color: #e0e0e0;
            cursor: pointer;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .pagination button:hover {
            background: rgba(138, 43, 226, 0.4);
            color: white;
            border-color: rgba(138, 43, 226, 0.6);
        }
        
        .pagination button:disabled {
            background: rgba(15, 15, 35, 0.6);
            color: #666;
            cursor: not-allowed;
            border-color: rgba(138, 43, 226, 0.1);
        }
        
        .pagination .current-page {
            background: linear-gradient(45deg, #8a2be2, #9d4edd);
            color: white;
            border-color: rgba(138, 43, 226, 0.6);
        }
        
        .footer {
            text-align: center;
            color: #b0b0b0;
            opacity: 0.9;
            margin-top: 40px;
            padding: 20px;
            background: rgba(15, 15, 35, 0.5);
            border-radius: 15px;
            border: 1px solid rgba(138, 43, 226, 0.1);
            backdrop-filter: blur(10px);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .email-table {
                font-size: 0.8rem;
            }
            
            .email-table th,
            .email-table td {
                padding: 8px 6px;
            }
        }

        .source-queue { background-color: #cce5ff; color: #004085; }
        .source-api { background-color: #d1ecf1; color: #0c5460; }
        .source-direct { background-color: #d4edda; color: #155724; }

        /* Editable Email Styles */
        .editable-email {
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s ease;
            position: relative;
        }

        .editable-email:hover {
            background-color: rgba(138, 43, 226, 0.1);
            border: 1px dashed rgba(138, 43, 226, 0.3);
        }

        .email-input {
            background: rgba(26, 26, 46, 0.9);
            border: 1px solid rgba(138, 43, 226, 0.5);
            color: #e0e0e0;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
            width: 200px;
        }

        .email-input:focus {
            outline: none;
            border-color: rgba(138, 43, 226, 0.8);
            box-shadow: 0 0 0 2px rgba(138, 43, 226, 0.2);
        }

        /* Success and Error Message Styles */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }

        .message.success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #20c997;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .message.error {
            background-color: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Pagination Styles */
        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination button {
            background: rgba(138, 43, 226, 0.2);
            color: #e0e0e0;
            border: 1px solid rgba(138, 43, 226, 0.3);
            padding: 8px 12px;
            margin: 0 5px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination button:hover:not(:disabled) {
            background: rgba(138, 43, 226, 0.4);
            border-color: rgba(138, 43, 226, 0.6);
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination button.current-page {
            background: rgba(138, 43, 226, 0.6);
            border-color: rgba(138, 43, 226, 0.8);
        }

        .pagination span {
            color: #b0b0b0;
            margin: 0 10px;
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
            <a href="/replied-emails" class="sidebar-menu-item">
                <span>💬</span> Replied Emails
            </a>
            <a href="/email-tracking" class="sidebar-menu-item">
                <span>📈</span> Email Tracking
            </a>
            <a href="/email-logs" class="sidebar-menu-item active">
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
            <h1>📊 Email Logs</h1>
            <p>Complete record of all sent emails with detailed status tracking</p>
        </div>
        
        <div class="nav-links">
            <a href="/">🏠 Home</a>
            <a href="/providers">📧 Providers</a>
            <a href="/api/email/stats">📈 Stats</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number" id="total-emails">-</div>
                <div class="stat-label">Total Emails</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="sent-emails">-</div>
                <div class="stat-label">Successfully Sent</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="failed-emails">-</div>
                <div class="stat-label">Failed</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="pending-emails">-</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="bounced-emails">-</div>
                <div class="stat-label">Bounced</div>
            </div>
        </div>
        
        <!-- Bounced Emails Table -->
        <div class="card">
            <h2>🚫 Bounced Emails - Correct & Re-queue</h2>
            <div style="margin-bottom: 20px; text-align: right;">
                <button class="btn btn-primary" onclick="refreshBouncedEmails()">
                    🔄 Refresh Bounced Emails
                </button>
            </div>
            <div class="table-container">
                <table class="email-table" id="bouncedEmailsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>From Email</th>
                            <th>To Email (Editable)</th>
                            <th>Subject</th>
                            <th>Bounce Reason</th>
                            <th>Status</th>
                            <th>Provider</th>
                            <th>Sent At</th>
                            <th>Retry Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bouncedEmailsBody">
                        <tr>
                            <td colspan="10" class="loading">Loading bounced emails...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="pagination" id="bouncedPagination">
                <!-- Pagination will be populated by JavaScript -->
            </div>
        </div>
        
        <div class="card">
            <h2>📋 Email Logs Table</h2>
            <div style="margin-bottom: 20px; text-align: right;">
                <button class="btn btn-primary" onclick="refreshData()">
                    🔄 Refresh Data
                </button>
            </div>
            <div class="table-container">
                <table class="email-table" id="emailLogsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Message ID</th>
                            <th>From Email</th>
                            <th>To Email</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Provider</th>
                            <th>Tenant</th>
                            <th>Sent At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="emailLogsBody">
                        <tr>
                            <td colspan="10" class="loading">Loading email logs...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="pagination" id="pagination">
                <!-- Pagination will be populated by JavaScript -->
            </div>
        </div>
        
        <div class="footer">
            <p>AltimaCRM Email Microservice - Email Logs</p>
        </div>
    </div>

    <script>
        let currentPage = 1;
        const itemsPerPage = 20;
        let allEmailLogs = [];
        
        // Bounced emails variables
        let currentBouncedPage = 1;
        let allBouncedEmails = [];
        
        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadEmailLogs();
            loadEmailStats();
            loadBouncedEmails();
        });
        
        // Load email logs
        async function loadEmailLogs() {
            try {
                const response = await fetch('/api/email/logs');
                if (response.ok) {
                    const result = await response.json();
                    allEmailLogs = result.data || [];
                    
                    // Sort by sent_at in descending order (newest first)
                    allEmailLogs.sort((a, b) => {
                        const dateA = new Date(a.sent_at || a.created_at);
                        const dateB = new Date(b.sent_at || b.created_at);
                        return dateB - dateA;
                    });
                    
                    displayEmailLogs();
                    updatePagination();
                } else {
                    document.getElementById('emailLogsBody').innerHTML = 
                        '<tr><td colspan="10" class="error">Error loading email logs</td></tr>';
                }
            } catch (error) {
                console.error('Error loading email logs:', error);
                document.getElementById('emailLogsBody').innerHTML = 
                    '<tr><td colspan="10" class="error">Error loading email logs</td></tr>';
            }
        }
        
        // Load email statistics
        async function loadEmailStats() {
            try {
                const response = await fetch('/api/email/stats');
                if (response.ok) {
                    const stats = await response.json();
                    document.getElementById('total-emails').textContent = stats.data.total_emails || 0;
                    document.getElementById('sent-emails').textContent = stats.data.sent_emails || 0;
                    document.getElementById('failed-emails').textContent = stats.data.failed_emails || 0;
                    document.getElementById('pending-emails').textContent = stats.data.pending_emails || 0;
                    document.getElementById('bounced-emails').textContent = stats.data.bounced_emails || 0;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }
        
        // Load bounced emails
        async function loadBouncedEmails() {
            try {
                const response = await fetch('/api/email/bounced');
                if (response.ok) {
                    const result = await response.json();
                    allBouncedEmails = result.data || [];
                    
                    // Sort by sent_at in descending order (newest first)
                    allBouncedEmails.sort((a, b) => {
                        const dateA = new Date(a.sent_at || a.created_at);
                        const dateB = new Date(b.sent_at || b.created_at);
                        return dateB - dateA;
                    });
                    
                    displayBouncedEmails();
                    updateBouncedPagination();
                } else {
                    document.getElementById('bouncedEmailsBody').innerHTML = 
                        '<tr><td colspan="10" class="error">Error loading bounced emails</td></tr>';
                }
            } catch (error) {
                console.error('Error loading bounced emails:', error);
                document.getElementById('bouncedEmailsBody').innerHTML = 
                    '<tr><td colspan="10" class="error">Error loading bounced emails</td></tr>';
            }
        }
        
        // Display email logs with pagination
        function displayEmailLogs() {
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageLogs = allEmailLogs.slice(startIndex, endIndex);
            
            const tbody = document.getElementById('emailLogsBody');
            
            if (pageLogs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="loading">No email logs found</td></tr>';
                return;
            }
            
            tbody.innerHTML = pageLogs.map((log, index) => {
                const rowNumber = startIndex + index + 1;
                const sentDate = log.sent_at ? new Date(log.sent_at).toLocaleString() : 'N/A';
                const statusClass = `status-${log.status.toLowerCase()}`;
                
                return `
                    <tr>
                        <td><strong>${rowNumber}</strong></td>
                        <td><code>${log.message_id || log.id || 'N/A'}</code></td>
                        <td>${getFromEmail(log)}</td>
                        <td>${getToEmail(log)}</td>
                        <td>
                            <div class="message-preview" title="${log.subject || 'N/A'}">
                                ${log.subject || 'N/A'}
                            </div>
                        </td>
                        <td>
                            <span class="status-badge ${statusClass}">
                                ${log.status || 'Unknown'}
                            </span>
                        </td>
                        <td>${getProviderName(log)}</td>
                        <td>${getTenantName(log)}</td>
                        <td>${sentDate}</td>
                        <td>
                            <button class="btn btn-info btn-sm" onclick="viewEmailDetails('${log.id}')">
                                👁️ View
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        
        // Display bounced emails with pagination
        function displayBouncedEmails() {
            const startIndex = (currentBouncedPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageEmails = allBouncedEmails.slice(startIndex, endIndex);
            
            const tbody = document.getElementById('bouncedEmailsBody');
            
            if (pageEmails.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="loading">No bounced emails found</td></tr>';
                return;
            }
            
            tbody.innerHTML = pageEmails.map((email, index) => {
                const rowNumber = startIndex + index + 1;
                const sentDate = email.sent_at ? new Date(email.sent_at).toLocaleString() : 'N/A';
                const statusClass = `status-${email.status.toLowerCase()}`;
                const toEmail = Array.isArray(email.to) ? email.to[0] : email.to;
                
                return `
                    <tr data-email-id="${email.id}">
                        <td><strong>${rowNumber}</strong></td>
                        <td>${email.from || 'N/A'}</td>
                        <td>
                            <div class="editable-email" onclick="makeEmailEditable(this, '${email.id}', '${toEmail}')">
                                <span class="email-text">${toEmail || 'N/A'}</span>
                                <input type="email" class="email-input" value="${toEmail || ''}" style="display: none;" />
                            </div>
                        </td>
                        <td>
                            <div class="message-preview" title="${email.subject || 'N/A'}">
                                ${email.subject || 'N/A'}
                            </div>
                        </td>
                        <td>${email.bounce_reason || email.error_message || 'N/A'}</td>
                        <td>
                            <span class="status-badge ${statusClass}">
                                ${email.status || 'Unknown'}
                            </span>
                        </td>
                        <td>${getProviderName(email)}</td>
                        <td>${sentDate}</td>
                        <td>${email.retry_count || 0}</td>
                        <td>
                            <button class="btn btn-success btn-sm" onclick="requeueEmail('${email.id}')" title="Re-queue email">
                                🔄 Re-queue
                            </button>
                            <button class="btn btn-info btn-sm" onclick="viewEmailDetails('${email.id}')" title="View details">
                                👁️ View
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        
        // Get from email from outbox data
        function getFromEmail(log) {
            return log.from || 'N/A';
        }
        
        // Get to email from outbox data (first recipient)
        function getToEmail(log) {
            if (log.to && Array.isArray(log.to) && log.to.length > 0) {
                return log.to[0];
            }
            return 'N/A';
        }
        
        // Get provider name from outbox data
        function getProviderName(log) {
            if (log.provider && log.provider.provider_name) {
                return log.provider.provider_name;
            }
            return log.provider_id || 'N/A';
        }

        // Get tenant name from outbox data
        function getTenantName(log) {
            if (log.tenant && log.tenant.tenant_name) {
                return log.tenant.tenant_name;
            }
            return log.tenant_id || 'N/A';
        }
        
        // Update pagination
        function updatePagination() {
            const totalPages = Math.ceil(allEmailLogs.length / itemsPerPage);
            const pagination = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }
            
            let paginationHTML = '';
            
            // Previous button
            paginationHTML += `
                <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                    ← Previous
                </button>
            `;
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    const pageClass = i === currentPage ? 'current-page' : '';
                    paginationHTML += `
                        <button onclick="changePage(${i})" class="${pageClass}">
                            ${i}
                        </button>
                    `;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    paginationHTML += '<span>...</span>';
                }
            }
            
            // Next button
            paginationHTML += `
                <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                    Next →
                </button>
            `;
            
            pagination.innerHTML = paginationHTML;
        }
        
        // Update bounced emails pagination
        function updateBouncedPagination() {
            const totalPages = Math.ceil(allBouncedEmails.length / itemsPerPage);
            const pagination = document.getElementById('bouncedPagination');
            
            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }
            
            let paginationHTML = '';
            
            // Previous button
            paginationHTML += `
                <button onclick="changeBouncedPage(${currentBouncedPage - 1})" ${currentBouncedPage === 1 ? 'disabled' : ''}>
                    ← Previous
                </button>
            `;
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentBouncedPage - 2 && i <= currentBouncedPage + 2)) {
                    const pageClass = i === currentBouncedPage ? 'current-page' : '';
                    paginationHTML += `
                        <button onclick="changeBouncedPage(${i})" class="${pageClass}">
                            ${i}
                        </button>
                    `;
                } else if (i === currentBouncedPage - 3 || i === currentBouncedPage + 3) {
                    paginationHTML += '<span>...</span>';
                }
            }
            
            // Next button
            paginationHTML += `
                <button onclick="changeBouncedPage(${currentBouncedPage + 1})" ${currentBouncedPage === totalPages ? 'disabled' : ''}>
                    Next →
                </button>
            `;
            
            pagination.innerHTML = paginationHTML;
        }
        
        // Change page
        function changePage(page) {
            if (page >= 1 && page <= Math.ceil(allEmailLogs.length / itemsPerPage)) {
                currentPage = page;
                displayEmailLogs();
                updatePagination();
                // Scroll to top of table
                document.querySelector('.table-container').scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        // Change bounced emails page
        function changeBouncedPage(page) {
            if (page >= 1 && page <= Math.ceil(allBouncedEmails.length / itemsPerPage)) {
                currentBouncedPage = page;
                displayBouncedEmails();
                updateBouncedPagination();
                // Scroll to top of table
                document.querySelector('#bouncedEmailsTable').scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        // View email details (placeholder for future enhancement)
        function viewEmailDetails(messageId) {
            const log = allEmailLogs.find(l => l.id === messageId);
            if (log) {
                const details = `
Email Details for: ${messageId}

📧 Subject: ${log.subject || 'N/A'}
📤 From: ${log.from || 'N/A'}
📥 To: ${Array.isArray(log.to) ? log.to.join(', ') : 'N/A'}
📋 Status: ${log.status || 'N/A'}
🏢 Provider: ${getProviderName(log)}
🏢 Tenant: ${getTenantName(log)}
📅 Sent At: ${log.sent_at ? new Date(log.sent_at).toLocaleString() : 'N/A'}
📅 Delivered At: ${log.delivered_at ? new Date(log.delivered_at).toLocaleString() : 'N/A'}
📝 Body Format: ${log.body_format || 'N/A'}
📎 Attachments: ${log.attachments ? log.attachments.length : 0}

This feature will be enhanced in future updates to show:
- Full email content
- Headers
- Provider response details
- Tracking information
- Attachment details
                `;
                alert(details);
            } else {
                alert(`Email with ID ${messageId} not found`);
            }
        }
        
        // Make email address editable
        function makeEmailEditable(element, emailId, currentEmail) {
            const emailText = element.querySelector('.email-text');
            const emailInput = element.querySelector('.email-input');
            
            if (emailText.style.display !== 'none') {
                // Switch to edit mode
                emailText.style.display = 'none';
                emailInput.style.display = 'inline-block';
                emailInput.focus();
                emailInput.select();
                
                // Add save button
                const saveBtn = document.createElement('button');
                saveBtn.className = 'btn btn-success btn-sm';
                saveBtn.innerHTML = '💾 Save';
                saveBtn.onclick = () => saveEmailAddress(emailId, emailInput.value, element);
                element.appendChild(saveBtn);
                
                // Add cancel button
                const cancelBtn = document.createElement('button');
                cancelBtn.className = 'btn btn-secondary btn-sm';
                cancelBtn.innerHTML = '❌ Cancel';
                cancelBtn.onclick = () => cancelEmailEdit(element, currentEmail);
                element.appendChild(cancelBtn);
            }
        }
        
        // Save email address
        async function saveEmailAddress(emailId, newEmail, element) {
            try {
                const response = await fetch(`/api/email/bounced/${emailId}/update-email`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        new_email: newEmail,
                        reason: 'Manual correction by admin'
                    })
                });
                
                if (response.ok) {
                    const result = await response.json();
                    showSuccess('Email address updated successfully!');
                    
                    // Update the display
                    const emailText = element.querySelector('.email-text');
                    emailText.textContent = newEmail;
                    emailText.style.display = 'inline-block';
                    
                    // Remove input and buttons
                    element.querySelector('.email-input').style.display = 'none';
                    element.querySelectorAll('button').forEach(btn => btn.remove());
                    
                    // Refresh bounced emails to show updated status
                    loadBouncedEmails();
                } else {
                    const error = await response.json();
                    showError('Failed to update email address: ' + (error.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error updating email address:', error);
                showError('Failed to update email address');
            }
        }
        
        // Cancel email edit
        function cancelEmailEdit(element, originalEmail) {
            const emailText = element.querySelector('.email-text');
            const emailInput = element.querySelector('.email-input');
            
            emailText.style.display = 'inline-block';
            emailInput.style.display = 'none';
            emailInput.value = originalEmail;
            
            // Remove buttons
            element.querySelectorAll('button').forEach(btn => btn.remove());
        }
        
        // Re-queue bounced email
        async function requeueEmail(emailId) {
            try {
                const response = await fetch(`/api/email/bounced/${emailId}/requeue`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    showSuccess('Email re-queued successfully!');
                    
                    // Refresh bounced emails to show updated status
                    loadBouncedEmails();
                } else {
                    const error = await response.json();
                    showError('Failed to re-queue email: ' + (error.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error re-queuing email:', error);
                showError('Failed to re-queue email');
            }
        }
        
        // Refresh data
        function refreshData() {
            currentPage = 1;
            loadEmailLogs();
            loadEmailStats();
        }
        
        // Show success message
        function showSuccess(message) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message success';
            messageDiv.textContent = message;
            document.querySelector('.container').insertBefore(messageDiv, document.querySelector('.stats-grid'));
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
        
        // Show error message
        function showError(message) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message error';
            messageDiv.textContent = message;
            document.querySelector('.container').insertBefore(messageDiv, document.querySelector('.stats-grid'));
            
            setTimeout(() => {
                messageDiv.remove();
            }, 8000);
        }
        
        // Refresh bounced emails
        function refreshBouncedEmails() {
            currentBouncedPage = 1;
            loadBouncedEmails();
        }
        
        // Auto-refresh every 30 seconds
        setInterval(refreshData, 30000);
    </script>
</body>
</html> 