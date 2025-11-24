<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Providers Management - AltimaCRM Email Microservice</title>
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
            max-width: 1200px;
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
            margin-bottom: 40px;
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
            color: #ffffff;
            margin-bottom: 20px;
            font-size: 1.5rem;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        .provider-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .provider-item {
            background: rgba(26, 26, 46, 0.8);
            border: 2px solid rgba(138, 43, 226, 0.2);
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .provider-item:hover {
            border-color: #8a2be2;
            box-shadow: 0 5px 15px rgba(138, 43, 226, 0.2);
            transform: translateY(-2px);
        }
        
        .provider-item.active {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.2);
        }
        
        .provider-item.inactive {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
            opacity: 0.7;
        }
        
        .provider-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .provider-name {
            font-weight: bold;
            font-size: 1.1rem;
            color: #e0e0e0;
        }
        
        .provider-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .status-active {
            background: rgba(40, 167, 69, 0.2);
            color: #20c997;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .status-inactive {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .provider-details {
            font-size: 0.9rem;
            color: #b0b0b0;
        }
        
        .provider-details div {
            margin-bottom: 5px;
        }
        
        .provider-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
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
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #ffffff;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .footer {
            text-align: center;
            color: white;
            opacity: 0.8;
            margin-top: 40px;
        }
        
        /* Modal Styles */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow-y: auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            margin: 0;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            overflow-y: auto;
        }
        
        .modal-header {
            background: #667eea;
            color: white;
            padding: 20px 30px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 1.3rem;
        }
        
        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            margin: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s;
        }
        
        .close:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
        .modal form {
            padding: 30px;
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            position: sticky;
            bottom: 0;
            background: white;
            padding-bottom: 10px;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        /* Ensure modal appears above everything */
        .modal {
            z-index: 9999;
        }
        
        /* Prevent body scroll when modal is open */
        body.modal-open {
            overflow: hidden;
        }
        
        /* Modal backdrop styling */
        .modal::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: -1;
        }
        
        /* Ensure modal content is properly contained */
        .modal-content {
            transform: translateZ(0);
            will-change: transform;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .provider-grid {
                grid-template-columns: 1fr;
            }
            .modal-content {
                width: 95%;
                max-height: 95vh;
                margin: 10px;
            }
            
            .modal form {
                padding: 20px;
            }
            
            .modal-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .modal-actions .btn {
                width: 100%;
            }
        }
        
        /* Animation for modal appearance */
        .modal {
            animation: fadeIn 0.3s ease-out;
        }
        
        .modal-content {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { 
                transform: translateY(-50px);
                opacity: 0;
            }
            to { 
                transform: translateY(0);
                opacity: 1;
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
            <a href="/email-tracking" class="sidebar-menu-item">
                <span>📈</span> Email Tracking
            </a>
            <a href="/email-logs" class="sidebar-menu-item">
                <span>📊</span> Email Logs
            </a>
            <a href="/providers" class="sidebar-menu-item active">
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
            <h1>📧 Email Providers Management</h1>
            <p>Configure and manage multiple email providers with SMTP settings</p>
        </div>
        
        <div class="nav-links">
            <a href="/">🏠 Home</a>
            <a href="/api/email/providers">📊 API View</a>
            <a href="/api/email/stats">📈 Statistics</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number" id="total-providers">-</div>
                <div class="stat-label">Total Providers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="active-providers">-</div>
                <div class="stat-label">Active Providers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="total-tenants">-</div>
                <div class="stat-label">Tenants</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="total-emails">-</div>
                <div class="stat-label">Total Emails</div>
            </div>
        </div>
        
        <div class="card">
            <h2>➕ Add New Email Provider</h2>
            <form id="addProviderForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="provider_name">Provider Name *</label>
                        <input type="text" id="provider_name" name="provider_name" required placeholder="e.g., Gmail SMTP, Postmark, Custom SMTP">
                    </div>
                    <div class="form-group">
                        <label for="tenant_id">Tenant *</label>
                        <select id="tenant_id" name="tenant_id" required>
                            <option value="">Select Tenant</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="smtp_host">SMTP Host *</label>
                        <input type="text" id="smtp_host" name="smtp_host" required placeholder="e.g., smtp.gmail.com">
                    </div>
                    <div class="form-group">
                        <label for="smtp_port">SMTP Port *</label>
                        <input type="number" id="smtp_port" name="smtp_port" required placeholder="587" value="587">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="smtp_username">SMTP Username *</label>
                        <input type="text" id="smtp_username" name="smtp_username" required placeholder="e.g., your-email@gmail.com">
                    </div>
                    <div class="form-group">
                        <label for="smtp_password">SMTP Password *</label>
                        <input type="password" id="smtp_password" name="smtp_password" required placeholder="Your SMTP password or API key">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="encryption">Encryption</label>
                        <select id="encryption" name="encryption">
                            <option value="tls">TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="from_address">From Email Address *</label>
                        <input type="email" id="from_address" name="from_address" required placeholder="noreply@yourdomain.com">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="from_name">From Name</label>
                        <input type="text" id="from_name" name="from_name" placeholder="Your Company Name">
                    </div>
                    <div class="form-group">
                        <label for="bounce_email">Bounce Email</label>
                        <input type="email" id="bounce_email" name="bounce_email" placeholder="bounce@yourdomain.com">
                    </div>
                </div>
                
                <!-- IMAP/POP3 Configuration Section -->
                <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e9ecef;">
                    <h3 style="color: #ffffff; margin-bottom: 20px;">📥 Incoming Email Configuration (IMAP/POP3)</h3>
                    <p style="color: #6c757d; margin-bottom: 20px; font-size: 0.9rem;">
                        Configure these settings to enable fetching incoming emails (replies) from this provider.
                    </p>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="protocol">Email Protocol *</label>
                        <select id="protocol" name="protocol" required>
                            <option value="imap">IMAP (Recommended)</option>
                            <option value="pop3">POP3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="incoming_host">Incoming Mail Host *</label>
                        <input type="text" id="incoming_host" name="incoming_host" required 
                               placeholder="e.g., imap.gmail.com or pop.gmail.com">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="incoming_port">Incoming Mail Port *</label>
                        <input type="number" id="incoming_port" name="incoming_port" required 
                               placeholder="993 for IMAP SSL, 995 for POP3 SSL" value="993">
                    </div>
                    <div class="form-group">
                        <label for="incoming_encryption">Incoming Encryption</label>
                        <select id="incoming_encryption" name="incoming_encryption">
                            <option value="ssl">SSL (Recommended)</option>
                            <option value="tls">TLS</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="incoming_username">Incoming Username *</label>
                        <input type="text" id="incoming_username" name="incoming_username" required 
                               placeholder="e.g., your-email@gmail.com">
                    </div>
                    <div class="form-group">
                        <label for="incoming_password">Incoming Password *</label>
                        <input type="password" id="incoming_password" name="incoming_password" required 
                               placeholder="Your email password or app password">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="incoming_folder">Mailbox Folder</label>
                    <input type="text" id="incoming_folder" name="incoming_folder" 
                           placeholder="INBOX (default)" value="INBOX">
                    <small class="form-text text-muted">Leave as 'INBOX' for most providers</small>
                </div>
                
                <div class="form-group">
                    <label for="custom_headers">Custom Headers (JSON)</label>
                    <textarea id="custom_headers" name="custom_headers" rows="3" placeholder='{"X-Mailer": "AltimaCRM", "X-Priority": "3"}'></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">➕ Add Provider</button>
                <button type="button" class="btn btn-success" onclick="addTestProvider()">🧪 Add Test Gmail Provider</button>
            </form>
        </div>
        
        <div class="card">
            <h2>📋 Current Email Providers</h2>
            <div id="providersList" class="provider-grid">
                <div style="text-align: center; grid-column: 1 / -1; padding: 40px; color: #6c757d;">
                    Loading providers...
                </div>
            </div>
        </div>
        
        <!-- Test Email Sending Section -->
        <div class="card">
            <h2>📧 Send Test Email</h2>
            <form id="testEmailForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="test_provider_id">Select Email Provider *</label>
                        <select id="test_provider_id" name="provider_id" required>
                            <option value="">Select Provider</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="test_to_email">To Email *</label>
                        <input type="email" id="test_to_email" name="to_email" required 
                               placeholder="nishant.joshi@estatic-infotech.com" 
                               value="nishant.joshi@estatic-infotech.com">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="test_from_email">From Email (Optional)</label>
                        <input type="email" id="test_from_email" name="from_email" 
                               placeholder="Leave blank to use provider's default from address">
                        <small class="form-text text-muted">If left blank, will use the provider's configured from address</small>
                    </div>
                    <div class="form-group">
                        <label for="test_subject">Subject *</label>
                        <input type="text" id="test_subject" name="subject" required 
                               placeholder="Test Email from AltimaCRM" 
                               value="Test Email from AltimaCRM Email Microservice">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="test_message">Message *</label>
                        <textarea id="test_message" name="message" rows="4" required 
                                  placeholder="This is a test email sent from the AltimaCRM Email Microservice...">Hello Nishant,

This is a test email sent from the AltimaCRM Email Microservice using the configured email provider.

Features tested:
✅ Email provider configuration
✅ SMTP connection
✅ Email sending functionality
✅ Email logging

Best regards,
AltimaCRM Team</textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">📤 Send Test Email</button>
                <button type="button" class="btn btn-success" onclick="sendQuickTestEmail()">🚀 Quick Test (Outlook)</button>
                <button type="button" class="btn btn-info" onclick="testTenantsAPI()">🔍 Debug Tenants API</button>
            </form>
        </div>
        
        <!-- Edit Provider Modal -->
        <div id="editModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>✏️ Edit Email Provider</h3>
                    <span class="close" onclick="closeEditModal()">&times;</span>
                </div>
                <form id="editProviderForm">
                    <input type="hidden" id="edit_provider_id" name="provider_id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_provider_name">Provider Name *</label>
                            <input type="text" id="edit_provider_name" name="provider_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_tenant_id">Tenant *</label>
                            <select id="edit_tenant_id" name="tenant_id" required>
                                <option value="">Select Tenant</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_smtp_host">SMTP Host *</label>
                            <input type="text" id="edit_smtp_host" name="smtp_host" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_smtp_port">SMTP Port *</label>
                            <input type="number" id="edit_smtp_port" name="smtp_port" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_smtp_username">SMTP Username *</label>
                            <input type="text" id="edit_smtp_username" name="smtp_username" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_smtp_password">SMTP Password *</label>
                            <input type="password" id="edit_smtp_password" name="smtp_password" placeholder="Leave blank to keep current">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_encryption">Encryption</label>
                            <select id="edit_encryption" name="encryption">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_from_address">From Email Address *</label>
                            <input type="email" id="edit_from_address" name="from_address" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_from_name">From Name</label>
                            <input type="text" id="edit_from_name" name="from_name">
                        </div>
                        <div class="form-group">
                            <label for="edit_bounce_email">Bounce Email</label>
                            <input type="email" id="edit_bounce_email" name="bounce_email">
                        </div>
                    </div>
                    
                    <!-- IMAP/POP3 Configuration Section -->
                    <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e9ecef;">
                        <h3 style="color: #ffffff; margin-bottom: 20px;">📥 Incoming Email Configuration (IMAP/POP3)</h3>
                        <p style="color: #6c757d; margin-bottom: 20px; font-size: 0.9rem;">
                            Configure these settings to enable fetching incoming emails (replies) from this provider.
                        </p>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_protocol">Email Protocol *</label>
                            <select id="edit_protocol" name="protocol" required>
                                <option value="imap">IMAP (Recommended)</option>
                                <option value="pop3">POP3</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_incoming_host">Incoming Mail Host *</label>
                            <input type="text" id="edit_incoming_host" name="incoming_host" required 
                                   placeholder="e.g., imap.gmail.com or pop.gmail.com">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_incoming_port">Incoming Mail Port *</label>
                            <input type="number" id="edit_incoming_port" name="incoming_port" required 
                                   placeholder="993 for IMAP SSL, 995 for POP3 SSL">
                        </div>
                        <div class="form-group">
                            <label for="edit_incoming_encryption">Incoming Encryption</label>
                            <select id="edit_incoming_encryption" name="incoming_encryption">
                                <option value="ssl">SSL (Recommended)</option>
                                <option value="tls">TLS</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_incoming_username">Incoming Username *</label>
                            <input type="text" id="edit_incoming_username" name="incoming_username" required 
                                   placeholder="e.g., your-email@gmail.com">
                        </div>
                        <div class="form-group">
                            <label for="edit_incoming_password">Incoming Password *</label>
                            <input type="password" id="edit_incoming_password" name="incoming_password" 
                                   placeholder="Leave blank to keep current">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_incoming_folder">Mailbox Folder</label>
                        <input type="text" id="edit_incoming_folder" name="incoming_folder" 
                               placeholder="INBOX (default)" value="INBOX">
                        <small class="form-text text-muted">Leave as 'INBOX' for most providers</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_custom_headers">Custom Headers (JSON)</label>
                        <textarea id="edit_custom_headers" name="custom_headers" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="edit_is_active" name="is_active" value="1">
                            Active Provider
                        </label>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">💾 Update Provider</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="footer">
            <p>AltimaCRM Email Microservice - Provider Management</p>
        </div>
    </div>

    <script>
        // Load initial data
        async function loadInitialData() {
            await Promise.all([
                loadTenants(),
                loadProviders(),
                loadStats()
            ]);
        }
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', loadInitialData);
        
        // Load statistics
        async function loadStats() {
            try {
                const [statsResponse, processingResponse] = await Promise.all([
                    fetch('/api/email/stats'),
                    fetch('/api/email/processing/stats')
                ]);
                
                if (statsResponse.ok) {
                    const stats = await statsResponse.json();
                    document.getElementById('total-emails').textContent = stats.data.total_emails || 0;
                }
                
                if (processingResponse.ok) {
                    const processing = await processingResponse.json();
                    // We'll get provider count from the providers API
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }
        
        // Test function to debug tenants API
        async function testTenantsAPI() {
            try {
                console.log('Testing tenants API...');
                const response = await fetch('/api/email/tenants');
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const result = await response.json();
                console.log('Raw API response:', result);
                console.log('Response type:', typeof result);
                console.log('Is array:', Array.isArray(result));
                console.log('Has data property:', result && result.hasOwnProperty('data'));
                console.log('Data type:', result && typeof result.data);
                console.log('Data is array:', result && Array.isArray(result.data));
                
                if (result && result.data) {
                    console.log('Tenants count:', result.data.length);
                    console.log('First tenant:', result.data[0]);
                }
            } catch (error) {
                console.error('Error testing tenants API:', error);
            }
        }
        
        // Load tenants for dropdowns
        async function loadTenants() {
            try {
                console.log('Loading tenants...');
                const response = await fetch('/api/email/tenants');
                if (response.ok) {
                    const result = await response.json();
                    console.log('Tenants API response:', result);
                    
                    // Handle different response structures
                    let tenants = [];
                    if (Array.isArray(result)) {
                        tenants = result;
                    } else if (result && Array.isArray(result.data)) {
                        tenants = result.data;
                    } else if (result && result.data) {
                        tenants = [result.data];
                    } else {
                        console.warn('Unexpected tenants response structure:', result);
                        tenants = [];
                    }
                    
                    console.log('Processed tenants:', tenants);
                    
                    // Populate add provider form tenant dropdown
                    const addTenantSelect = document.getElementById('tenant_id');
                    if (addTenantSelect) {
                        addTenantSelect.innerHTML = '<option value="">Select Tenant</option>' +
                            tenants.map(tenant => `<option value="${tenant.tenant_id}">${tenant.tenant_name}</option>`).join('');
                        console.log('Add provider tenant dropdown populated');
                    }
                    
                    // Populate edit provider form tenant dropdown
                    const editTenantSelect = document.getElementById('edit_tenant_id');
                    if (editTenantSelect) {
                        editTenantSelect.innerHTML = '<option value="">Select Tenant</option>' +
                            tenants.map(tenant => `<option value="${tenant.tenant_id}">${tenant.tenant_name}</option>`).join('');
                        console.log('Edit provider tenant dropdown populated with', tenants.length, 'tenants');
                    } else {
                        console.warn('Edit tenant dropdown not found');
                    }
                } else {
                    console.error('Failed to load tenants:', response.status, response.statusText);
                }
            } catch (error) {
                console.error('Error loading tenants:', error);
            }
        }
        
        // Load providers
        async function loadProviders() {
            try {
                const response = await fetch('/api/email/providers');
                if (response.ok) {
                    const data = await response.json();
                    displayProviders(data.data || []);
                    updateProviderStats(data.data || []);
                    
                    // Also populate test email provider dropdown
                    populateTestEmailProviders(data.data || []);
                }
            } catch (error) {
                console.error('Error loading providers:', error);
                document.getElementById('providersList').innerHTML = 
                    '<div style="text-align: center; grid-column: 1 / -1; padding: 40px; color: #dc3545;">Error loading providers</div>';
            }
        }
        
        // Populate test email provider dropdown
        function populateTestEmailProviders(providers) {
            const select = document.getElementById('test_provider_id');
            if (select) {
                select.innerHTML = '<option value="">Select Provider</option>' +
                    providers.filter(p => p.is_active).map(provider => 
                        `<option value="${provider.provider_id}">${provider.provider_name}</option>`
                    ).join('');
            }
        }
        
        // Display providers
        function displayProviders(providers) {
            const container = document.getElementById('providersList');
            
            if (providers.length === 0) {
                container.innerHTML = '<div style="text-align: center; grid-column: 1 / -1; padding: 40px; color: #6c757d;">No providers found</div>';
                return;
            }
            
            container.innerHTML = providers.map(provider => {
                const config = provider.config_json || {};
                const headers = provider.header_overrides || {};
                
                return `
                    <div class="provider-item ${provider.is_active ? 'active' : 'inactive'}">
                        <div class="provider-header">
                            <div class="provider-name">${provider.provider_name || 'Unnamed Provider'}</div>
                            <div class="provider-status ${provider.is_active ? 'status-active' : 'status-inactive'}">
                                ${provider.is_active ? 'Active' : 'Inactive'}
                            </div>
                        </div>
                        <div class="provider-details">
                            <div><strong>SMTP Host:</strong> ${config.host || 'Not set'}:${config.port || 'Not set'}</div>
                            <div><strong>SMTP Username:</strong> ${config.username || 'Not set'}</div>
                            <div><strong>From:</strong> ${config.from_address || 'Not set'}</div>
                            <div><strong>SMTP Encryption:</strong> ${config.encryption || 'TLS'}</div>
                            <div><strong>IMAP/POP3:</strong> ${config.protocol ? config.protocol.toUpperCase() : 'Not configured'}</div>
                            <div><strong>Incoming Host:</strong> ${config.imap_host || 'Not set'}:${config.imap_port || 'Not set'}</div>
                            <div><strong>Incoming Encryption:</strong> ${config.imap_encryption || 'Not set'}</div>
                            <div><strong>Tenant:</strong> ${provider.tenant ? provider.tenant.tenant_name : 'Unknown'}</div>
                        </div>
                        <div class="provider-actions">
                            <button class="btn btn-warning" onclick="toggleProvider('${provider.provider_id}', ${!provider.is_active})">
                                ${provider.is_active ? 'Deactivate' : 'Activate'}
                            </button>
                            <button class="btn btn-primary" onclick="editProvider('${provider.provider_id}')">
                                Edit
                            </button>
                            <button class="btn btn-danger" onclick="deleteProvider('${provider.provider_id}')">
                                Delete
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Update provider statistics
        function updateProviderStats(providers) {
            document.getElementById('total-providers').textContent = providers.length;
            document.getElementById('active-providers').textContent = providers.filter(p => p.is_active).length;
            document.getElementById('total-tenants').textContent = new Set(providers.map(p => p.tenant_id)).size;
        }
        
        // Handle form submission
        document.getElementById('addProviderForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const providerData = {
                tenant_id: formData.get('tenant_id'),
                provider_name: formData.get('provider_name'),
                config_json: {
                    // SMTP Configuration (for sending emails)
                    driver: 'smtp',
                    host: formData.get('smtp_host'),
                    port: parseInt(formData.get('smtp_port')),
                    encryption: formData.get('encryption'),
                    username: formData.get('smtp_username'),
                    password: formData.get('smtp_password'),
                    from_address: formData.get('from_address'),
                    from_name: formData.get('from_name'),
                    
                    // IMAP/POP3 Configuration (for receiving emails)
                    protocol: formData.get('protocol'),
                    imap_host: formData.get('incoming_host'),
                    imap_port: parseInt(formData.get('incoming_port')),
                    imap_encryption: formData.get('incoming_encryption'),
                    imap_username: formData.get('incoming_username'),
                    imap_password: formData.get('incoming_password'),
                    folder: formData.get('incoming_folder') || 'INBOX',
                },
                bounce_email: formData.get('bounce_email'),
                header_overrides: formData.get('custom_headers') ? JSON.parse(formData.get('custom_headers')) : {},
                is_active: true
            };
            
            try {
                const response = await fetch('/api/email/providers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(providerData)
                });
                
                if (response.ok) {
                    alert('Provider added successfully!');
                    this.reset();
                    loadProviders();
                    loadStats();
                } else {
                    const error = await response.json();
                    alert('Error adding provider: ' + (error.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error adding provider: ' + error.message);
            }
        });
        
        // Toggle provider status
        async function toggleProvider(providerId, isActive) {
            try {
                const response = await fetch(`/api/email/providers/${providerId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ is_active: isActive })
                });
                
                if (response.ok) {
                    loadProviders();
                    loadStats();
                } else {
                    alert('Error updating provider');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating provider');
            }
        }
        
        // Edit provider
        async function editProvider(providerId) {
            try {
                const response = await fetch(`/api/email/providers/${providerId}`);
                if (response.ok) {
                    const provider = await response.json();
                    
                    // Show modal first
                    const modal = document.getElementById('editModal');
                    modal.style.display = 'flex';
                    
                    // Ensure tenant dropdown is populated
                    await loadTenants();
                    
                    // Double-check if tenants are loaded, if not, try again
                    const editTenantSelect = document.getElementById('edit_tenant_id');
                    if (!editTenantSelect || editTenantSelect.options.length <= 1) {
                        console.log('Tenants not loaded properly, retrying...');
                        await new Promise(resolve => setTimeout(resolve, 200)); // Wait a bit
                        await loadTenants();
                    }
                    
                    // Now populate the form with provider data
                    populateEditForm(provider.data);
                    
                    // Scroll to top of modal content
                    const modalContent = modal.querySelector('.modal-content');
                    modalContent.scrollTop = 0;
                    
                    // Prevent body scrolling when modal is open
                    document.body.classList.add('modal-open');
                    document.body.style.overflow = 'hidden';
                    
                    // Debug: Check if tenant dropdown is populated
                    setTimeout(() => {
                        const editTenantSelect = document.getElementById('edit_tenant_id');
                        console.log('Tenant dropdown options:', editTenantSelect ? editTenantSelect.innerHTML : 'No dropdown found');
                        console.log('Current tenant ID:', provider.data.tenant_id);
                        console.log('Selected value:', editTenantSelect ? editTenantSelect.value : 'No value');
                    }, 100);
                    
                } else {
                    alert('Error loading provider details');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error loading provider details');
            }
        }
        
        // Populate edit form
        function populateEditForm(provider) {
            const config = provider.config_json || {};
            const headers = provider.header_overrides || {};
            
            console.log('Populating edit form with provider:', provider);
            console.log('Provider tenant ID:', provider.tenant_id);
            
            // Set form values
            document.getElementById('edit_provider_id').value = provider.provider_id;
            document.getElementById('edit_provider_name').value = provider.provider_name || '';
            
            // Set tenant dropdown value
            const editTenantSelect = document.getElementById('edit_tenant_id');
            if (editTenantSelect) {
                editTenantSelect.value = provider.tenant_id || '';
                console.log('Tenant dropdown value set to:', editTenantSelect.value);
                
                // Verify the option exists
                const optionExists = Array.from(editTenantSelect.options).some(option => option.value === provider.tenant_id);
                console.log('Tenant option exists:', optionExists);
            }
            
            // SMTP Configuration
            document.getElementById('edit_smtp_host').value = config.host || '';
            document.getElementById('edit_smtp_port').value = config.port || '';
            document.getElementById('edit_smtp_username').value = config.username || '';
            document.getElementById('edit_smtp_password').value = ''; // Don't populate password
            document.getElementById('edit_encryption').value = config.encryption || 'tls';
            document.getElementById('edit_from_address').value = config.from_address || '';
            document.getElementById('edit_from_name').value = config.from_name || '';
            document.getElementById('edit_bounce_email').value = provider.bounce_email || '';
            document.getElementById('edit_custom_headers').value = JSON.stringify(headers, null, 2);
            document.getElementById('edit_is_active').checked = provider.is_active;
            
            // IMAP/POP3 Configuration
            document.getElementById('edit_protocol').value = config.protocol || 'imap';
            document.getElementById('edit_incoming_host').value = config.imap_host || '';
            document.getElementById('edit_incoming_port').value = config.imap_port || '';
            document.getElementById('edit_incoming_encryption').value = config.imap_encryption || 'ssl';
            document.getElementById('edit_incoming_username').value = config.imap_username || '';
            document.getElementById('edit_incoming_password').value = ''; // Don't populate password
            document.getElementById('edit_incoming_folder').value = config.folder || 'INBOX';
            
            // Final verification
            console.log('Final tenant dropdown value:', editTenantSelect ? editTenantSelect.value : 'No dropdown');
        }
        
        // Close edit modal
        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.style.display = 'none';
            document.getElementById('editProviderForm').reset();
            
            // Restore body scrolling
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        }
        
        // Handle edit form submission
        document.getElementById('editProviderForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const providerId = formData.get('provider_id');
            const tenantId = formData.get('tenant_id');
            
            // Validate required fields
            if (!tenantId) {
                alert('Please select a tenant');
                return;
            }
            
            const updateData = {
                provider_name: formData.get('provider_name'),
                tenant_id: tenantId,
                config_json: {
                    // SMTP Configuration (for sending emails)
                    driver: 'smtp',
                    host: formData.get('smtp_host'),
                    port: parseInt(formData.get('smtp_port')),
                    encryption: formData.get('encryption'),
                    username: formData.get('smtp_username'),
                    from_address: formData.get('from_address'),
                    from_name: formData.get('from_name'),
                    
                    // IMAP/POP3 Configuration (for receiving emails)
                    protocol: formData.get('protocol'),
                    imap_host: formData.get('incoming_host'),
                    imap_port: parseInt(formData.get('incoming_port')),
                    imap_encryption: formData.get('incoming_encryption'),
                    imap_username: formData.get('incoming_username'),
                    folder: formData.get('incoming_folder') || 'INBOX',
                },
                bounce_email: formData.get('bounce_email'),
                header_overrides: formData.get('custom_headers') ? JSON.parse(formData.get('custom_headers')) : {},
                is_active: formData.get('is_active') === '1'
            };
            
            // Only include passwords if they were changed
            const smtpPassword = formData.get('smtp_password');
            if (smtpPassword && smtpPassword.trim() !== '') {
                updateData.config_json.password = smtpPassword;
            }
            
            const imapPassword = formData.get('incoming_password');
            if (imapPassword && imapPassword.trim() !== '') {
                updateData.config_json.imap_password = imapPassword;
            }
            
            try {
                const response = await fetch(`/api/email/providers/${providerId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(updateData)
                });
                
                if (response.ok) {
                    alert('Provider updated successfully!');
                    closeEditModal();
                    loadProviders();
                    loadStats();
                } else {
                    const error = await response.json();
                    alert('Error updating provider: ' + (error.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating provider: ' + error.message);
            }
        });
        
        // Delete provider
        async function deleteProvider(providerId) {
            if (!confirm('Are you sure you want to delete this provider?')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/email/providers/${providerId}`, {
                    method: 'DELETE'
                });
                
                if (response.ok) {
                    loadProviders();
                    loadStats();
                } else {
                    alert('Error deleting provider');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error deleting provider');
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }

        // Add a test provider (Gmail)
        async function addTestProvider() {
            const testProviderData = {
                tenant_id: document.getElementById('tenant_id').value, // Use selected tenant
                provider_name: "Test Gmail Provider",
                config_json: {
                    driver: "smtp",
                    host: "smtp.gmail.com",
                    port: 587,
                    encryption: "tls",
                    username: "your.test.email@gmail.com", // Replace with a valid Gmail address
                    password: "your_test_password", // Replace with a valid Gmail password
                    from_address: "noreply@yourdomain.com",
                    from_name: "Your Company Name",
                },
                bounce_email: "bounce@yourdomain.com",
                header_overrides: {},
                is_active: true
            };

            try {
                const response = await fetch('/api/email/providers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(testProviderData)
                });

                if (response.ok) {
                    alert('Test Gmail Provider added successfully!');
                    loadProviders();
                    loadStats();
                } else {
                    const error = await response.json();
                    alert('Error adding test provider: ' + (error.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error adding test provider: ' + error.message);
            }
        }
        
        // Handle test email form submission
        document.getElementById('testEmailForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const emailData = {
                provider_id: formData.get('provider_id'),
                to_email: formData.get('to_email'),
                from_email: formData.get('from_email'), // Add custom from email
                subject: formData.get('subject'),
                message: formData.get('message')
            };
            
            if (!emailData.provider_id) {
                alert('Please select an email provider');
                return;
            }
            
            try {
                const response = await fetch('/api/email/send-test-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(emailData)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    const fromInfo = result.data.used_custom_from ? 
                        `\nFrom: ${result.data.from_email} (Custom)` : 
                        `\nFrom: ${result.data.from_email} (Provider Default)`;
                    
                    alert('✅ Test email sent successfully!\n\n' +
                          'Provider: ' + result.data.provider_name + 
                          fromInfo +
                          '\nTo: ' + result.data.to_email + 
                          '\nSubject: ' + result.data.subject);
                    // Don't reset form, allow multiple tests
                } else {
                    alert('❌ Failed to send test email: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ Error sending test email: ' + error.message);
            }
        });
        
        // Quick test function for Outlook
        async function sendQuickTestEmail() {
            // Find Outlook provider
            const providers = await fetch('/api/email/providers').then(r => r.json()).then(d => d.data || []);
            const outlookProvider = providers.find(p => 
                p.provider_name.toLowerCase().includes('outlook') || 
                p.provider_name.toLowerCase().includes('hotmail')
            );
            
            if (!outlookProvider) {
                alert('❌ No Outlook/Hotmail provider found. Please add one first.');
                return;
            }
            
            // Set form values (preserve existing to_email if already set)
            document.getElementById('test_provider_id').value = outlookProvider.provider_id;
            // Only set to_email if it's empty, otherwise keep the user's input
            if (!document.getElementById('test_to_email').value) {
                document.getElementById('test_to_email').value = 'nishant.joshi@estatic-infotech.com';
            }
            document.getElementById('test_from_email').value = outlookProvider.config_json.from_address || 'noreply@altimacrm.com';
            document.getElementById('test_subject').value = 'Quick Test - Outlook Provider Working! 🚀';
            document.getElementById('test_message').value = `Hello Nishant!

This is a quick test email sent from the AltimaCRM Email Microservice using the ${outlookProvider.provider_name}.

✅ Provider: ${outlookProvider.provider_name}
✅ SMTP: ${outlookProvider.config_json.host}:${outlookProvider.config_json.port}
✅ Encryption: ${outlookProvider.config_json.encryption}
✅ From: ${outlookProvider.config_json.from_address}

If you receive this email, the Outlook provider is working perfectly!

Best regards,
AltimaCRM Email Microservice Team`;

            // Submit the form
            document.getElementById('testEmailForm').dispatchEvent(new Event('submit'));
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