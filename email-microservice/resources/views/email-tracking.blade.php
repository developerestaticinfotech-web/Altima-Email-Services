<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Tracking Dashboard - AltimaCRM</title>
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
            padding: 20px;
            color: #e0e0e0;
            position: relative;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
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
            color: white;
            margin-bottom: 30px;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(26, 26, 46, 0.8);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            border: 1px solid rgba(138, 43, 226, 0.2);
            backdrop-filter: blur(10px);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(138, 43, 226, 0.4);
            box-shadow: 0 15px 40px rgba(138, 43, 226, 0.2);
        }

        .stat-card h3 {
            color: #00d4ff;
            margin-bottom: 15px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 0 0 8px rgba(0, 212, 255, 0.3);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(45deg, #8a2be2, #00d4ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #b0b0b0;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .trend {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 10px;
            font-size: 0.9rem;
        }

        .trend.up { color: #20c997; }
        .trend.down { color: #ff6b6b; }
        .trend.neutral { color: #b0b0b0; }

        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: rgba(26, 26, 46, 0.8);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(138, 43, 226, 0.2);
            backdrop-filter: blur(10px);
        }

        .chart-card h3 {
            color: #8a2be2;
            margin-bottom: 20px;
            font-size: 1.3rem;
            text-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
        }

        .chart-container {
            height: 300px;
            position: relative;
            background: rgba(15, 15, 35, 0.5);
            border-radius: 10px;
            padding: 20px;
            border: 1px solid rgba(138, 43, 226, 0.1);
            overflow: hidden;
        }

        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #8a2be2, #00d4ff, #8a2be2);
            background-size: 200% 100%;
            animation: gradientMove 3s ease-in-out infinite;
        }

        @keyframes gradientMove {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .chart-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #b0b0b0;
            font-size: 1.1rem;
            text-align: center;
            background: linear-gradient(45deg, rgba(138, 43, 226, 0.1), rgba(0, 212, 255, 0.1));
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .chart-placeholder:hover {
            background: linear-gradient(45deg, rgba(138, 43, 226, 0.2), rgba(0, 212, 255, 0.2));
            transform: scale(1.02);
        }

        .chart-placeholder .chart-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 10px rgba(138, 43, 226, 0.5));
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .chart-placeholder .chart-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #e0e0e0;
        }

        .chart-placeholder .chart-stats {
            font-size: 0.9rem;
            opacity: 0.7;
            line-height: 1.4;
        }

        .chart-placeholder .stat-highlight {
            color: #00d4ff;
            font-weight: 600;
            cursor: help;
            position: relative;
        }

        .table-section {
            background: rgba(26, 26, 46, 0.8);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 30px;
            border: 1px solid rgba(138, 43, 226, 0.2);
            backdrop-filter: blur(10px);
        }

        .table-section h3 {
            color: #8a2be2;
            margin-bottom: 20px;
            font-size: 1.3rem;
            text-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(138, 43, 226, 0.1);
            color: #e0e0e0;
        }

        th {
            background: linear-gradient(45deg, #8a2be2, #9d4edd);
            font-weight: 600;
            color: white;
            border-bottom: 1px solid rgba(138, 43, 226, 0.3);
        }

        tr:hover {
            background-color: rgba(138, 43, 226, 0.1);
        }

        tr:nth-child(even) {
            background-color: rgba(26, 26, 46, 0.6);
        }

        tr:nth-child(odd) {
            background-color: rgba(15, 15, 35, 0.8);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-sent { background-color: #d4edda; color: #155724; }
        .status-failed { background-color: #f8d7da; color: #721c24; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-bounced { background-color: #f5c6cb; color: #721c24; }

        .source-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .source-queue { background-color: #cce5ff; color: #004085; }
        .source-api { background-color: #d1ecf1; color: #0c5460; }
        .source-direct { background-color: #d4edda; color: #155724; }

        .refresh-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .dashboard-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .last-updated {
            color: #b0b0b0;
            font-size: 0.9rem;
            padding: 8px 15px;
            background: rgba(26, 26, 46, 0.6);
            border-radius: 8px;
            border: 1px solid rgba(138, 43, 226, 0.2);
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #b0b0b0;
        }

        .error {
            background-color: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #20c997;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        @media (max-width: 768px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-container {
                height: 250px;
                padding: 15px;
            }
            
            .dashboard-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .last-updated {
                text-align: center;
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
            <a href="/outbox" class="sidebar-menu-item">
                <span>📤</span> Outbox
            </a>
            <a href="/replied-emails" class="sidebar-menu-item">
                <span>💬</span> Replied Emails
            </a>
            <a href="/email-tracking" class="sidebar-menu-item active">
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
            <h1>📊 Email Tracking Dashboard</h1>
            <p>Comprehensive monitoring of all email activities and performance metrics</p>
        </div>

        <div class="dashboard-controls">
            <button class="refresh-btn" onclick="refreshDashboard()">🔄 Refresh Dashboard</button>
            <div class="last-updated">
                Last Updated: <span id="lastUpdated">-</span>
            </div>
        </div>

        <!-- Key Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>📧 Total Emails Sent</h3>
                <div class="stat-number" id="totalEmails">-</div>
                <div class="stat-label">All Time</div>
            </div>

            <div class="stat-card">
                <h3>🚀 Queue Processed</h3>
                <div class="stat-number" id="queueEmails">-</div>
                <div class="stat-label">Via RabbitMQ</div>
            </div>

            <div class="stat-card">
                <h3>⚡ Direct Sent</h3>
                <div class="stat-number" id="directEmails">-</div>
                <div class="stat-label">API/Test Emails</div>
            </div>

            <div class="stat-card">
                <h3>✅ Success Rate</h3>
                <div class="stat-number" id="successRate">-</div>
                <div class="stat-label">Percentage</div>
            </div>

            <div class="stat-card">
                <h3>⏱️ Avg Processing Time</h3>
                <div class="stat-number" id="avgProcessingTime">-</div>
                <div class="stat-label">Milliseconds</div>
            </div>

            <div class="stat-card">
                <h3>🔄 Active Providers</h3>
                <div class="stat-number" id="activeProviders">-</div>
                <div class="stat-label">Email Services</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <h3>📈 Email Volume by Source</h3>
                <div class="chart-container" id="emailVolumeChart">
                    <div class="chart-placeholder">
                        <div>
                            <div class="chart-icon">📊</div>
                            <div class="chart-title">Email Volume Analytics</div>
                            <div class="chart-stats">
                                Queue: <span class="stat-highlight" id="queueVolume">-</span> | 
                                Direct: <span class="stat-highlight" id="directVolume">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <h3>🎯 Success vs Failure Rate</h3>
                <div class="chart-container" id="successRateChart">
                    <div class="chart-placeholder">
                        <div>
                            <div class="chart-icon">🎯</div>
                            <div class="chart-title">Success Rate Overview</div>
                            <div class="chart-stats">
                                Success: <span class="stat-highlight" id="successCount">-</span> | 
                                Failed: <span class="stat-highlight" id="failedCount">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chart-card">
                <h3>🚀 Provider Performance</h3>
                <div class="chart-container" id="providerPerformanceChart">
                    <div class="chart-placeholder">
                        <div>
                            <div class="chart-icon">🚀</div>
                            <div class="chart-title">Active Providers</div>
                            <div class="chart-stats">
                                Active: <span class="stat-highlight" id="activeProvidersCount">-</span> | 
                                Total: <span class="stat-highlight" id="totalProvidersCount">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Emails Table -->
        <div class="table-section">
            <h3>📋 Recent Email Activity</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Subject</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Processing Time</th>
                            <th>Provider</th>
                        </tr>
                    </thead>
                    <tbody id="recentEmailsTable">
                        <tr>
                            <td colspan="8" class="loading">Loading recent emails...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Dashboard refresh function
        function refreshDashboard() {
            loadDashboardData();
            showSuccess('Dashboard refreshed successfully! 📊');
        }

        // Load all dashboard data
        async function loadDashboardData() {
            try {
                console.log('🚀 Loading dashboard data...');
                
                await Promise.all([
                    loadStatistics(),
                    loadRecentEmails()
                ]);
                
                console.log('✅ Dashboard data loaded successfully');
            } catch (error) {
                console.error('❌ Error loading dashboard:', error);
                showError('Failed to load dashboard data');
            }
        }

        // Load key statistics
        async function loadStatistics() {
            try {
                console.log('📊 Loading statistics...');
                const response = await fetch('/api/email/tracking/stats');
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('📊 API Data:', data);
                
                if (data.success) {
                    console.log('✅ Statistics loaded successfully');
                    updateStatCard('totalEmails', data.data.total_emails || 0);
                    updateStatCard('queueEmails', data.data.queue_emails || 0);
                    updateStatCard('directEmails', data.data.direct_emails || 0);
                    updateStatCard('successRate', (data.data.success_rate || 0) + '%');
                    updateStatCard('avgProcessingTime', (data.data.avg_processing_time || 0) + 'ms');
                    updateStatCard('activeProviders', data.data.active_providers || 0);
                    
                    // Update chart data
                    updateChartData(data.data);
                } else {
                    console.error('❌ API returned error:', data.error || 'Unknown error');
                }
            } catch (error) {
                console.error('❌ Error loading statistics:', error);
                showError('Failed to load statistics');
            }
        }

        // Load recent emails
        async function loadRecentEmails() {
            try {
                console.log('📧 Loading recent emails...');
                const response = await fetch('/api/email/tracking/recent');
                const data = await response.json();
                
                if (data.success) {
                    updateRecentEmailsTable(data.data);
                } else {
                    console.error('❌ Failed to load recent emails:', data.error);
                }
            } catch (error) {
                console.error('❌ Error loading recent emails:', error);
                showError('Failed to load recent emails');
            }
        }

        // Update stat card
        function updateStatCard(id, value) {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        }

        // Update chart data
        function updateChartData(data) {
            console.log('📊 Updating chart data:', data);
            
            // Update email volume chart
            const queueVolume = document.getElementById('queueVolume');
            const directVolume = document.getElementById('directVolume');
            if (queueVolume) queueVolume.textContent = data.queue_emails || 0;
            if (directVolume) directVolume.textContent = data.direct_emails || 0;
            
            // Update success rate chart
            const successCount = document.getElementById('successCount');
            const failedCount = document.getElementById('failedCount');
            if (successCount) {
                const successEmails = Math.round((data.success_rate || 0) * (data.total_emails || 0) / 100);
                successCount.textContent = successEmails;
            }
            if (failedCount) {
                const failedEmails = (data.total_emails || 0) - Math.round((data.success_rate || 0) * (data.total_emails || 0) / 100);
                failedCount.textContent = failedEmails;
            }
            
            // Update provider performance chart
            const activeProvidersCount = document.getElementById('activeProvidersCount');
            const totalProvidersCount = document.getElementById('totalProvidersCount');
            if (activeProvidersCount) activeProvidersCount.textContent = data.active_providers || 0;
            if (totalProvidersCount) totalProvidersCount.textContent = data.total_providers || data.active_providers || 0;
            
            // Update last updated timestamp
            const lastUpdated = document.getElementById('lastUpdated');
            if (lastUpdated) {
                lastUpdated.textContent = new Date().toLocaleTimeString();
            }
        }

        // Update recent emails table
        function updateRecentEmailsTable(emails) {
            const tbody = document.getElementById('recentEmailsTable');
            if (!tbody) return;

            if (!emails || emails.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="loading">No recent emails found</td></tr>';
                return;
            }

            tbody.innerHTML = emails.map(email => `
                <tr>
                    <td>${formatDateTime(email.sent_at)}</td>
                    <td>${email.from || 'N/A'}</td>
                    <td>${Array.isArray(email.to) ? email.to.join(', ') : (email.to || 'N/A')}</td>
                    <td>${email.subject || 'N/A'}</td>
                    <td><span class="source-badge source-${email.source || 'unknown'}">${email.source || 'N/A'}</span></td>
                    <td><span class="status-badge status-${email.status || 'unknown'}">${email.status || 'N/A'}</span></td>
                    <td>${email.processing_time_ms ? email.processing_time_ms + 'ms' : 'N/A'}</td>
                    <td>${email.provider_name || 'N/A'}</td>
                </tr>
            `).join('');
        }

        // Format date time
        function formatDateTime(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString();
        }

        // Show error message
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            errorDiv.textContent = message;
            document.querySelector('.container').insertBefore(errorDiv, document.querySelector('.stats-grid'));
            
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }

        // Show success message
        function showSuccess(message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'success';
            successDiv.textContent = message;
            document.querySelector('.container').insertBefore(successDiv, document.querySelector('.stats-grid'));
            
            setTimeout(() => {
                successDiv.remove();
            }, 3000);
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Email Tracking Dashboard Initializing...');
            
            // Load initial data
            loadDashboardData();
            
            // Auto-refresh every 30 seconds
            setInterval(loadDashboardData, 30000);
            
            console.log('✅ Dashboard initialized successfully');
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
