<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AltimaCRM Email Microservice</title>
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
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            color: #ffffff;
        }
        
        .header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 0 0 20px rgba(138, 43, 226, 0.5);
            background: linear-gradient(45deg, #8a2be2, #00d4ff, #8a2be2);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradientShift 3s ease-in-out infinite;
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }
        
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .nav-menu {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .nav-link {
            color: #e0e0e0;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 15px;
            background: rgba(138, 43, 226, 0.2);
            transition: all 0.3s ease;
            font-size: 0.8rem;
            white-space: nowrap;
            border: 1px solid rgba(138, 43, 226, 0.3);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(138, 43, 226, 0.2);
        }
        
        .nav-link:hover {
            background: rgba(138, 43, 226, 0.4);
            transform: translateY(-2px);
            border-color: rgba(138, 43, 226, 0.6);
            box-shadow: 0 6px 20px rgba(138, 43, 226, 0.4);
            color: #ffffff;
        }
        
        .nav-link.active {
            background: rgba(138, 43, 226, 0.6);
            font-weight: 500;
            border-color: rgba(138, 43, 226, 0.8);
            color: #ffffff;
            box-shadow: 0 0 20px rgba(138, 43, 226, 0.5);
        }
        
        .nav-separator {
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(138, 43, 226, 0.6), rgba(0, 212, 255, 0.6), rgba(138, 43, 226, 0.6), transparent);
            margin: 20px auto 30px auto;
            max-width: 800px;
            border-radius: 1px;
            box-shadow: 0 0 20px rgba(138, 43, 226, 0.3);
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .card {
            background: rgba(26, 26, 46, 0.8);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            border: 1px solid rgba(138, 43, 226, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .card:hover {
            transform: translateY(-5px);
            border-color: rgba(138, 43, 226, 0.4);
            box-shadow: 0 15px 40px rgba(138, 43, 226, 0.2);
        }
        
        .card h2 {
            color: #8a2be2;
            margin-bottom: 20px;
            font-size: 1.5rem;
            text-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
        }
        
        .card h3 {
            color: #00d4ff;
            margin-bottom: 15px;
            font-size: 1.2rem;
            text-shadow: 0 0 8px rgba(0, 212, 255, 0.3);
        }
        
        .card p {
            color: #b0b0b0;
            line-height: 1.6;
        }
        
        .endpoint {
            background: rgba(15, 15, 35, 0.6);
            border-left: 4px solid #8a2be2;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid rgba(138, 43, 226, 0.1);
            color: #e0e0e0;
        }
        
        .endpoint .method {
            display: inline-block;
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-right: 10px;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }
        
        .endpoint .method.post { 
            background: linear-gradient(45deg, #007bff, #0056b3);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }
        .endpoint .method.get { 
            background: linear-gradient(45deg, #28a745, #20c997);
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }
        
        .stats {
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
        
        .btn {
            display: inline-block;
            background: linear-gradient(45deg, #8a2be2, #9d4edd);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            margin: 5px;
            border: 1px solid rgba(138, 43, 226, 0.3);
            box-shadow: 0 4px 15px rgba(138, 43, 226, 0.2);
            font-weight: 500;
        }
        
        .btn:hover {
            background: linear-gradient(45deg, #9d4edd, #8a2be2);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(138, 43, 226, 0.4);
            border-color: rgba(138, 43, 226, 0.6);
        }
        
        .btn.secondary {
            background: linear-gradient(45deg, #495057, #6c757d);
            border-color: rgba(108, 117, 125, 0.3);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.2);
        }
        
        .btn.secondary:hover {
            background: linear-gradient(45deg, #6c757d, #495057);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
            border-color: rgba(108, 117, 125, 0.6);
        }
        
        .btn-info {
            background: linear-gradient(45deg, #17a2b8, #20c997);
            border-color: rgba(23, 162, 184, 0.3);
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.2);
        }
        
        .btn-info:hover {
            background: linear-gradient(45deg, #20c997, #17a2b8);
            box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);
            border-color: rgba(23, 162, 184, 0.6);
        }
        
        .btn-warning {
            background: linear-gradient(45deg, #ffc107, #ffca2c);
            color: #212529;
            border-color: rgba(255, 193, 7, 0.3);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.2);
        }
        
        .btn-warning:hover {
            background: linear-gradient(45deg, #ffca2c, #ffc107);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
            border-color: rgba(255, 193, 7, 0.6);
        }
        
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border-color: rgba(40, 167, 69, 0.3);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
        }
        
        .btn-success:hover {
            background: linear-gradient(45deg, #20c997, #28a745);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            border-color: rgba(40, 167, 69, 0.6);
        }
        
        /* Main Actions Full Width Styling */
        .main-actions-card {
            grid-column: 1 / -1;
            margin-top: 30px;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 25px;
        }
        
        .action-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: center;
            padding: 20px;
            background: rgba(15, 15, 35, 0.3);
            border-radius: 12px;
            border: 1px solid rgba(138, 43, 226, 0.1);
            transition: all 0.3s ease;
        }
        
        .action-group:hover {
            background: rgba(15, 15, 35, 0.5);
            border-color: rgba(138, 43, 226, 0.3);
            transform: translateY(-2px);
        }
        
        .action-group .btn {
            width: 100%;
            text-align: center;
            justify-content: center;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .nav-menu {
                gap: 6px;
                max-width: 100%;
            }
            
            .nav-link {
                padding: 5px 10px;
                font-size: 0.75rem;
            }
            
            .card {
                padding: 20px;
            }
            
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .action-group {
                padding: 15px;
            }
        }
        
        /* Dark theme scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(15, 15, 35, 0.3);
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #8a2be2, #00d4ff);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(45deg, #9d4edd, #00d4ff);
        }
        
        /* Selection color */
        ::selection {
            background: rgba(138, 43, 226, 0.3);
            color: #ffffff;
        }
        
        /* Focus styles for accessibility */
        .nav-link:focus,
        .btn:focus {
            outline: 2px solid rgba(0, 212, 255, 0.6);
            outline-offset: 2px;
        }

        .user-info {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 27px !important;
            margin: 0 auto;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 340px;
            justify-content: center;
            text-align: center;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #8a2be2, #00d4ff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 1.2rem;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: #ffffff;
            font-size: 0.9rem;
        }

        .user-tenant {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .logout-btn {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 AltimaCRM Email Microservice</h1>
            <p>Multi-tenant, provider-agnostic email service with RabbitMQ integration</p>
            
            <!-- Navigation Menu -->
            <div class="nav-menu">
                <a href="/" class="nav-link active">🏠 Home</a>
                <a href="/providers" class="nav-link">📧 Providers</a>
                <a href="/email-logs" class="nav-link">📊 Email Logs</a>
                <a href="/inbound-emails" class="nav-link">📥 Inbound</a>
                <a href="/outbox" class="nav-link">📤 Outbox</a>
                <a href="/replied-emails" class="nav-link">💬 Replied</a>
                <a href="/email-tracking" class="nav-link">📈 Tracking</a>
                <a href="/rabbitmq-test" class="nav-link">🐰 RabbitMQ</a>
                <a href="/api/health" class="nav-link">💚 Health</a>
                <a href="/api/email/templates" class="nav-link">📝 Templates</a>
                <a href="/api/email/stats" class="nav-link">📊 Stats</a>
                <a href="/api/email/processing/stats" class="nav-link">⚡ Processing</a>
                <a href="/api/email/storage/stats" class="nav-link">💾 Storage</a>
                <a href="/api/" class="nav-link">📚 API Docs</a>
             </div> 
                <!-- User Info and Logout -->
                <div class="user-info">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="user-details">
                        <div class="user-name">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="user-tenant">{{ auth()->user()->tenant->tenant_name ?? 'Tenant' }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="logout-btn">🚪 Logout</button>
                    </form>
                </div>
           
        </div>
        
        <!-- Navigation Separator -->
        <div class="nav-separator"></div>
        
        <div class="stats">
            <div class="stat-item">
                <div class="stat-number">3</div>
                <div class="stat-label">Email Providers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">3</div>
                <div class="stat-label">Tenants</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">3</div>
                <div class="stat-label">Templates</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">Active</div>
                <div class="stat-label">Status</div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="card">
                <h2>📧 Email API Endpoints</h2>
                <div class="endpoint">
                    <span class="method post">POST</span>
                    <strong>/api/email/send</strong> - Send email using template
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <strong>/api/email/templates</strong> - Get available templates
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <strong>/api/email/stats</strong> - Get email statistics
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <strong>/api/email/logs</strong> - Get email logs with filters
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <strong>/api/email/replies</strong> - Get replied emails with filters
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <strong>/api/email/storage/stats</strong> - Get storage statistics
                </div>
            </div>
            
            <div class="card">
                <h2>🐰 RabbitMQ Service Endpoints</h2>
                <div class="endpoint">
                    <span class="method post">POST</span>
                    <strong>/api/rabbitmq/send-email</strong> - Send email via RabbitMQ queue
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <strong>/api/rabbitmq/queue-status</strong> - Check queue status
                </div>
                <div class="endpoint">
                    <span class="method get">GET</span>
                    <strong>/api/rabbitmq/queue-stats</strong> - Get queue statistics
                </div>
                <div class="endpoint">
                    <span class="method post">POST</span>
                    <strong>/api/rabbitmq/process-queue</strong> - Manually process queue
                </div>
            </div>
        </div>
        
        <!-- Main Actions - Full Width -->
        <div class="card main-actions-card">
            <h2>🔗 Main Actions</h2>
            <div class="actions-grid">
                <div class="action-group">
                    <a href="/providers" class="btn btn-success">📧 Manage Email Providers</a>
                    <a href="/email-logs" class="btn btn-info">📊 View Email Logs (Table)</a>
                    <a href="/inbound-emails" class="btn btn-info">📥 Inbound Emails</a>
                </div>
                <div class="action-group">
                    <a href="/outbox" class="btn btn-info">📤 Outbox Management</a>
                    <a href="/replied-emails" class="btn btn-info">💬 Replied Emails</a>
                    <a href="/email-tracking" class="btn btn-info">📈 Email Tracking Dashboard</a>
                    <a href="/rabbitmq-test" class="btn btn-warning">🐰 Test RabbitMQ Service</a>
                </div>
                <div class="action-group">
                    <a href="/api/email/logs" class="btn btn-secondary">📋 API Email Logs</a>
                    <a href="/api/health" class="btn">💚 Health Check</a>
                </div>
                <div class="action-group">
                    <a href="/api/" class="btn">📚 Full API Documentation</a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>⚡ Phase 1 Features (Complete!)</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div>
                    <h3>🔐 Multi-Tenant</h3>
                    <p>Support for multiple organizations with isolated email configurations</p>
                </div>
                <div>
                    <h3>📨 Provider Agnostic</h3>
                    <p>Support for AWS SES, Postmark, Gmail, and generic SMTP</p>
                </div>
                <div>
                    <h3>🐰 RabbitMQ Integration</h3>
                    <p>Asynchronous email processing with message queuing</p>
                </div>
                <div>
                    <h3>📊 Full Audit Logging</h3>
                    <p>Complete tracking of all email activities and statuses</p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>🚀 New Phase 1 Capabilities</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div>
                    <h3>📝 MIME Parser</h3>
                    <p>Complete EML/MIME email parsing with support for all encodings (Base64, quoted-printable)</p>
                </div>
                <div>
                    <h3>📎 Attachment Handler</h3>
                    <p>Extract, store, and manage email attachments with proper file organization</p>
                </div>
                <div>
                    <h3>🖼️ Inline Image Support</h3>
                    <p>Handle embedded images and replace CID references with accessible URLs</p>
                </div>
                <div>
                    <h3>💾 File Storage</h3>
                    <p>Local file storage system with proper organization and cleanup capabilities</p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2>🧪 Testing Endpoints</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div>
                    <h3>📧 MIME Parsing Test</h3>
                    <p><strong>POST /api/email/test/mime-parsing</strong><br>Test MIME parsing with sample emails</p>
                </div>
                <div>
                    <h3>💾 File Storage Test</h3>
                    <p><strong>POST /api/email/test/file-storage</strong><br>Test file storage and retrieval</p>
                </div>
                <div>
                    <h3>📊 Processing Stats</h3>
                    <p><strong>GET /api/email/processing/stats</strong><br>View email processing statistics</p>
                </div>
                <div>
                    <h3>💽 Storage Stats</h3>
                    <p><strong>GET /api/email/storage/stats</strong><br>View file storage statistics</p>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Version 1.0.0 | Built with Laravel | AltimaCRM Email Microservice</p>
        </div>
    </div>
</body>
</html> 