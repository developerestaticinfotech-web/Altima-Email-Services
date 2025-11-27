<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RabbitMQ Service Test - AltimaCRM Email Microservice</title>
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #e0e0e0;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid rgba(138, 43, 226, 0.3);
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(15, 15, 35, 0.6);
            color: #e0e0e0;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #8a2be2;
            background: rgba(15, 15, 35, 0.8);
            box-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
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
        
        .status-display {
            background: rgba(15, 15, 35, 0.8);
            border: 1px solid rgba(138, 43, 226, 0.3);
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
            color: #e0e0e0;
        }
        
        .queue-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .queue-item {
            background: rgba(26, 26, 46, 0.8);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border: 2px solid rgba(138, 43, 226, 0.2);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .queue-item.active {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.2);
        }
        
        .queue-item.inactive {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
            box-shadow: 0 0 20px rgba(220, 53, 69, 0.2);
        }
        
        .queue-number {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(45deg, #8a2be2, #00d4ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
        }
        
        .queue-label {
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
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: rgba(26, 26, 46, 0.98);
            border: 2px solid rgba(138, 43, 226, 0.5);
            border-radius: 15px;
            padding: 30px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(138, 43, 226, 0.5);
            color: #e0e0e0;
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(138, 43, 226, 0.3);
        }

        .modal-header h2 {
            color: #8a2be2;
            margin: 0;
            font-size: 1.8rem;
        }

        .close-modal {
            background: none;
            border: none;
            color: #e0e0e0;
            font-size: 2rem;
            cursor: pointer;
            padding: 0;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .template-preview {
            background: rgba(15, 15, 35, 0.6);
            border: 1px solid rgba(138, 43, 226, 0.3);
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            max-height: 500px;
            overflow-y: auto;
        }

        .template-preview-html {
            background: white;
            color: #333;
            padding: 20px;
            border-radius: 5px;
            min-height: 200px;
        }

        .template-preview-text {
            white-space: pre-wrap;
            font-family: monospace;
            font-size: 0.9rem;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .template-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
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
            <a href="/providers" class="sidebar-menu-item">
                <span>📧</span> Providers
            </a>
            <a href="/rabbitmq-test" class="sidebar-menu-item active">
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
            <h1>🐰 RabbitMQ Service Test</h1>
            <p>Test email queuing and queue management functionality</p>
        </div>
        
        <div class="nav-links">
            <a href="/">🏠 Home</a>
            <a href="/providers">📧 Providers</a>
            <a href="/email-logs">📊 Email Logs</a>
        </div>
        
        <div class="card">
            <h2>📊 Queue Status</h2>
            <div class="queue-info" id="queueInfo">
                <div class="queue-item">
                    <div class="queue-number">-</div>
                    <div class="queue-label">Email Send Queue</div>
                </div>
                <div class="queue-item">
                    <div class="queue-number">-</div>
                    <div class="queue-label">Email Sync Queue</div>
                </div>
                <div class="queue-item">
                    <div class="queue-number">-</div>
                    <div class="queue-label">Connection Status</div>
                </div>
            </div>
            
            <div style="text-align: center; margin-bottom: 20px;">
                <button class="btn btn-info" onclick="refreshQueueStatus()">🔄 Refresh Status</button>
                <button id="processQueueBtn" class="btn btn-warning" onclick="processQueue(); return false;">⚡ Process Queue</button>
            </div>
        </div>
        
        <div class="card">
            <h2>📧 Send Email via RabbitMQ</h2>
            <form id="rabbitmqForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tenant_id">Tenant ID *</label>
                        <select id="tenant_id" name="tenant_id" required>
                            <option value="">Select Tenant</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="provider_id">Provider ID *</label>
                        <select id="provider_id" name="provider_id" required>
                            <option value="">Select Provider</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="from">From Email *</label>
                        <input type="email" id="from" name="from" required placeholder="sender@domain.com">
                    </div>
                    <div class="form-group">
                        <label for="to">To Email(s) *</label>
                        <input type="text" id="to" name="to" required placeholder="recipient@domain.com, cc@domain.com">
                        <small>Separate multiple emails with commas</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="template_id">Template *</label>
                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            <select id="template_id" name="template_id" required style="flex: 1;">
                                <option value="">Loading templates...</option>
                            </select>
                            <button type="button" class="btn btn-info btn-sm" onclick="previewSelectedTemplate()" title="Preview Template" style="padding: 10px 15px;">👁️ Preview</button>
                            <button type="button" class="btn btn-success btn-sm" onclick="openCreateTemplateModal()" title="Add New Template" style="padding: 10px 15px;">➕ Add</button>
                        </div>
                        <small id="templateInfo" style="display: none; color: #00d4ff; margin-top: 5px;"></small>
                        <div class="template-actions">
                            <button type="button" class="btn btn-info btn-sm" onclick="loadTemplates()" style="padding: 5px 10px; font-size: 0.85rem;">🔄 Refresh Templates</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject (Optional)</label>
                        <input type="text" id="subject" name="subject" placeholder="Leave blank to use template subject">
                        <small>Optional: Override template subject</small>
                    </div>
                </div>
                
                <!-- <div class="form-group">
                    <label for="template_data">Template Data (JSON) *</label>
                    <textarea id="template_data" name="template_data" rows="6" required placeholder='{"name": "John Doe", "company": "Example Corp"}'>{
  "name": "Test User",
  "company": "AltimaCRM",
  "message": "This is a test email sent via RabbitMQ with template support."
}</textarea>
                    <small>JSON object with template variables. Example: {"name": "John", "email": "john@example.com"}</small>
                </div> -->
                
                <!-- Attachments Section -->
                <div class="form-group">
                    <label>📎 Attachments (File URLs)</label>
                    <div id="attachmentsContainer">
                        <div class="attachment-item" style="margin-bottom: 10px; padding: 10px; background: rgba(26, 26, 46, 0.5); border-radius: 5px;">
                            <div class="form-row">
                                <div class="form-group" style="flex: 2;">
                                    <input type="url" class="attachment-url" placeholder="https://example.com/files/document.pdf" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid rgba(138, 43, 226, 0.3); background: rgba(15, 15, 35, 0.8); color: #e0e0e0;">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    <input type="text" class="attachment-filename" placeholder="document.pdf" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid rgba(138, 43, 226, 0.3); background: rgba(15, 15, 35, 0.8); color: #e0e0e0;">
                                </div>
                                <div class="form-group" style="flex: 1;">
                                    <input type="text" class="attachment-mime" placeholder="application/pdf" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid rgba(138, 43, 226, 0.3); background: rgba(15, 15, 35, 0.8); color: #e0e0e0;">
                                </div>
                                <div class="form-group" style="flex: 0 0 auto;">
                                    <button type="button" class="btn btn-warning btn-sm" onclick="removeAttachment(this)" style="padding: 8px 12px; font-size: 0.9rem;">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-info btn-sm" onclick="addAttachment()" style="margin-top: 10px;">+ Add Attachment</button>
                    <small style="display: block; margin-top: 5px; color: #ffc107;">
                        ⚠️ <strong>Important:</strong> Add file URLs that will be fetched and attached to the email. 
                        Files must be publicly accessible via <strong>HTTP/HTTPS URLs</strong> (not local file paths like <code>file://</code>). 
                        Upload files to a web server, cloud storage (S3, Google Drive, etc.), or use a file sharing service.
                    </small>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" class="btn btn-primary">🚀 Queue Email</button>
                    <button type="button" class="btn btn-success" onclick="loadSampleData()">📝 Load Sample Data</button>
                </div>
            </form>
            
            <div id="statusDisplay" class="status-display" style="display: none;"></div>
        </div>
        
        <div class="footer">
            <p>AltimaCRM Email Microservice - RabbitMQ Service Test</p>
        </div>
    </div>

    <!-- Template Preview Modal -->
    <div id="previewModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2>👁️ Template Preview</h2>
                <button class="close-modal" onclick="closePreviewModal()">&times;</button>
            </div>
            <div id="previewContent">
                <p>Loading preview...</p>
            </div>
        </div>
    </div>

    <!-- Create Template Modal -->
    <div id="createTemplateModal" class="modal">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <h2>➕ Create New Template</h2>
                <button class="close-modal" onclick="closeCreateTemplateModal()">&times;</button>
            </div>
            <form id="createTemplateForm">
                <div class="form-group">
                    <label for="new_template_id">Template ID *</label>
                    <input type="text" id="new_template_id" name="template_id" required placeholder="e.g., welcome-email" pattern="[a-z0-9-]+" title="Lowercase letters, numbers, and hyphens only">
                    <small>Unique identifier (lowercase, numbers, hyphens only)</small>
                </div>
                <div class="form-group">
                    <label for="new_template_name">Template Name *</label>
                    <input type="text" id="new_template_name" name="name" required placeholder="e.g., Welcome Email Template">
                </div>
                <div class="form-group">
                    <label for="new_template_subject">Subject *</label>
                    <input type="text" id="new_template_subject" name="subject" required placeholder="e.g., Welcome @{{name}}!">
                    <small>You can use variables like @{{name}}, @{{company}}, etc.</small>
                </div>
                <div class="form-group">
                    <label for="new_template_html">HTML Content *</label>
                    <textarea id="new_template_html" name="html_content" rows="10" required placeholder='<html><body><h1>Hello @{{name}}!</h1><p>@{{message}}</p></body></html>'></textarea>
                    <small>Use Blade syntax: @{{variable}} for variables</small>
                </div>
                <div class="form-group">
                    <label for="new_template_text">Text Content (Optional)</label>
                    <textarea id="new_template_text" name="text_content" rows="6" placeholder="Plain text version of the email"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="new_template_category">Category</label>
                        <input type="text" id="new_template_category" name="category" placeholder="e.g., system, marketing, transactional">
                    </div>
                    <div class="form-group">
                        <label for="new_template_language">Language</label>
                        <input type="text" id="new_template_language" name="language" value="en" placeholder="e.g., en, es, fr">
                    </div>
                </div>
                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">💾 Create Template</button>
                    <button type="button" class="btn btn-warning" onclick="closeCreateTemplateModal()">Cancel</button>
                </div>
            </form>
            <div id="createTemplateStatus" style="display: none; margin-top: 15px; padding: 10px; border-radius: 5px;"></div>
        </div>
    </div>

    <script>
        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, initializing RabbitMQ test page...');
            loadTenants();
            loadProviders();
            loadTemplates();
            refreshQueueStatus();
            
            // Verify processQueue function is available
            if (typeof processQueue === 'function') {
                console.log('✓ processQueue function is available');
            } else {
                console.error('✗ processQueue function is NOT available!');
            }
            
            // Also verify button exists and add event listener as backup
            const processBtn = document.getElementById('processQueueBtn');
            if (processBtn) {
                console.log('✓ Process Queue button found');
                // Add event listener as backup (in addition to onclick)
                processBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Button clicked via event listener');
                    if (typeof processQueue === 'function') {
                        processQueue();
                    } else {
                        alert('Error: processQueue function not found. Check browser console.');
                    }
                });
            } else {
                console.error('✗ Process Queue button NOT found!');
            }
        });
        
        // Get base URL dynamically
        const baseUrl = window.location.origin;
        
        // Load email templates
        async function loadTemplates() {
            const templateSelect = document.getElementById('template_id');
            const templateInfo = document.getElementById('templateInfo');
            
            try {
                templateSelect.innerHTML = '<option value="">Loading templates...</option>';
                
                const response = await fetch(`${baseUrl}/api/email/templates?active=true&per_page=100`, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (result.success && result.data && result.data.length > 0) {
                    templateSelect.innerHTML = '<option value="">Select a Template</option>';
                    
                    result.data.forEach(template => {
                        const option = document.createElement('option');
                        option.value = template.template_id;
                        option.textContent = `${template.name} (${template.template_id})`;
                        option.dataset.subject = template.subject || '';
                        option.dataset.variables = JSON.stringify(template.variables || {});
                        // Store full template data for preview
                        option.dataset.templateData = JSON.stringify(template);
                        templateSelect.appendChild(option);
                    });
                    
                    // Show template info when selected
                    templateSelect.addEventListener('change', function() {
                        const selectedOption = this.options[this.selectedIndex];
                        const templateDataField = document.getElementById('template_data');
                        const templateDataLabel = templateDataField?.previousElementSibling;
                        const templateDataContainer = templateDataField?.parentElement;
                        
                        if (selectedOption.value) {
                            const subject = selectedOption.dataset.subject;
                            const variables = JSON.parse(selectedOption.dataset.variables || '{}');
                            const varKeys = Object.keys(variables);
                            
                            let info = `Subject: ${subject || 'N/A'}`;
                            if (varKeys.length > 0) {
                                info += ` | Variables: ${varKeys.join(', ')}`;
                            }
                            templateInfo.textContent = info;
                            templateInfo.style.display = 'block';
                            
                            // Update subject field with template subject
                            if (subject && !document.getElementById('subject').value) {
                                document.getElementById('subject').value = subject;
                            }
                            
                            // Show/hide template_data field based on whether template has variables
                            if (varKeys.length > 0) {
                                // Template has variables - show the field
                                if (templateDataContainer) {
                                    templateDataContainer.style.display = 'block';
                                }
                                templateDataField.required = true;
                                
                                // Update template_data with example based on variables
                                if (!templateDataField.value.trim()) {
                                    const exampleData = {};
                                    varKeys.forEach(key => {
                                        exampleData[key] = `Your ${key} value here`;
                                    });
                                    templateDataField.value = JSON.stringify(exampleData, null, 2);
                                }
                            } else {
                                // Template has no variables - hide the field
                                if (templateDataContainer) {
                                    templateDataContainer.style.display = 'none';
                                }
                                templateDataField.required = false;
                                templateDataField.value = '{}'; // Set empty JSON
                            }
                        } else {
                            templateInfo.style.display = 'none';
                            // Show field by default when no template selected
                            if (templateDataContainer) {
                                templateDataContainer.style.display = 'block';
                            }
                            templateDataField.required = true;
                        }
                    });
                } else {
                    templateSelect.innerHTML = '<option value="">No templates found. Please create templates first.</option>';
                    templateInfo.textContent = '⚠️ No active templates available. Create templates in the database first.';
                    templateInfo.style.display = 'block';
                    templateInfo.style.color = '#ffc107';
                }
            } catch (error) {
                console.error('Error loading templates:', error);
                templateSelect.innerHTML = '<option value="">Error loading templates</option>';
                templateInfo.textContent = '❌ Error loading templates: ' + error.message;
                templateInfo.style.display = 'block';
                templateInfo.style.color = '#f44336';
            }
        }
        
        // Load tenants
        async function loadTenants() {
            try {
                const response = await fetch(`${baseUrl}/api/email/tenants`);
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
        
        // Load providers
        async function loadProviders() {
            try {
                const response = await fetch(`${baseUrl}/api/email/providers`);
                if (response.ok) {
                    const result = await response.json();
                    const providers = result.data || [];
                    
                    const select = document.getElementById('provider_id');
                    select.innerHTML = '<option value="">Select Provider</option>' +
                        providers.map(provider => `<option value="${provider.provider_id}">${provider.provider_name}</option>`).join('');
                }
            } catch (error) {
                console.error('Error loading providers:', error);
            }
        }
        
        // Refresh queue status
        async function refreshQueueStatus() {
            try {
                const response = await fetch(`${baseUrl}/api/rabbitmq/queue-status`);
                if (response.ok) {
                    const result = await response.json();
                    const status = result.data;
                    
                    // Debug: Log the actual response
                    console.log('Queue Status Response:', result);
                    console.log('Status Data:', status);
                    
                    // Update queue info display
                    const queueInfo = document.getElementById('queueInfo');
                    const items = queueInfo.querySelectorAll('.queue-item');
                    
                    // Update Email Send Queue
                    if (status.email_send_queue) {
                        items[0].querySelector('.queue-number').textContent = status.email_send_queue.message_count || 0;
                        items[0].className = 'queue-item active';
                    }
                    
                    // Update Email Sync Queue
                    if (status.email_sync_user_queue) {
                        items[1].querySelector('.queue-number').textContent = status.email_sync_user_queue.message_count || 0;
                        items[1].className = 'queue-item active';
                    }
                    
                    // Update Connection Status
                    if (status.connection_status) {
                        const isConnected = status.connection_status === 'connected';
                        items[2].querySelector('.queue-number').textContent = isConnected ? '✅' : '❌';
                        items[2].className = isConnected ? 'queue-item active' : 'queue-item inactive';
                    }
                }
            } catch (error) {
                console.error('Error refreshing queue status:', error);
            }
        }
        
        // Process queue manually
        async function processQueue() {
            console.log('processQueue function called');
            
            const btn = document.getElementById('processQueueBtn');
            if (!btn) {
                console.error('Process Queue button not found!');
                alert('Error: Process Queue button not found on page');
                return;
            }
            
            const originalText = btn.innerHTML;
            
            // Disable button and show loading state
            btn.disabled = true;
            btn.innerHTML = '⏳ Processing...';
            btn.style.opacity = '0.6';
            btn.style.cursor = 'not-allowed';
            
            try {
                console.log('Starting queue processing...');
                
                const response = await fetch(`${baseUrl}/api/rabbitmq/process-queue`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        queue_name: 'email.send',
                        max_messages: 5
                    })
                });
                
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Response error:', errorText);
                    throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 100)}`);
                }
                
                const result = await response.json();
                console.log('Processing result:', result);
                
                if (result.success) {
                    const data = result.data;
                    let message = '✅ Queue processed successfully!\n\n';
                    
                    if (typeof data === 'object') {
                        message += `📊 Processing Results:\n`;
                        message += `✅ Processed: ${data.processed || 0} messages\n`;
                        message += `✅ Success: ${data.success || 0} emails sent\n`;
                        message += `❌ Failed: ${data.failed || 0} emails\n`;
                        message += `📧 Queue: ${data.queue || 'unknown'}\n`;
                        
                        if (data.note) {
                            message += `\nℹ️ Note: ${data.note}\n`;
                        }
                        
                        if (data.error) {
                            message += `\n⚠️ Error: ${data.error}\n`;
                        }
                        
                        // Show additional info if emails failed
                        if (data.failed > 0) {
                            message += `\n💡 Tip: Check failed emails at http://localhost:8000/outbox (filter by "failed" status)`;
                        }
                    } else {
                        message += `Processed: ${data} messages`;
                    }
                    
                    alert(message);
                    // Refresh queue status immediately after processing (multiple times to ensure update)
                    refreshQueueStatus();
                    setTimeout(() => {
                        refreshQueueStatus();
                    }, 1000);
                    setTimeout(() => {
                        refreshQueueStatus();
                    }, 2000);
                } else {
                    alert('❌ Failed to process queue:\n\n' + (result.message || 'Unknown error') + 
                          (result.error ? '\n\nError: ' + result.error : ''));
                }
            } catch (error) {
                console.error('Error processing queue:', error);
                let errorMessage = 'Error processing queue:\n\n';
                
                if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                    errorMessage += '❌ Network error: Could not connect to server.\n\n';
                    errorMessage += 'Please check:\n';
                    errorMessage += '1. Server is running (http://localhost:8000)\n';
                    errorMessage += '2. No CORS issues\n';
                    errorMessage += '3. Browser console for details';
                } else {
                    errorMessage += error.message;
                }
                
                alert(errorMessage);
            } finally {
                // Re-enable button
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                }
            }
        }
        
        // Make function globally accessible (in case of scope issues)
        window.processQueue = processQueue;
        
        // Add attachment field
        function addAttachment() {
            const container = document.getElementById('attachmentsContainer');
            const newItem = document.createElement('div');
            newItem.className = 'attachment-item';
            newItem.style.cssText = 'margin-bottom: 10px; padding: 10px; background: rgba(26, 26, 46, 0.5); border-radius: 5px;';
            newItem.innerHTML = `
                <div class="form-row">
                    <div class="form-group" style="flex: 2;">
                        <input type="url" class="attachment-url" placeholder="https://example.com/files/document.pdf" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid rgba(138, 43, 226, 0.3); background: rgba(15, 15, 35, 0.8); color: #e0e0e0;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <input type="text" class="attachment-filename" placeholder="document.pdf" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid rgba(138, 43, 226, 0.3); background: rgba(15, 15, 35, 0.8); color: #e0e0e0;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <input type="text" class="attachment-mime" placeholder="application/pdf" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid rgba(138, 43, 226, 0.3); background: rgba(15, 15, 35, 0.8); color: #e0e0e0;">
                    </div>
                    <div class="form-group" style="flex: 0 0 auto;">
                        <button type="button" class="btn btn-warning btn-sm" onclick="removeAttachment(this)" style="padding: 8px 12px; font-size: 0.9rem;">Remove</button>
                    </div>
                </div>
            `;
            container.appendChild(newItem);
        }
        
        // Remove attachment field
        function removeAttachment(button) {
            button.closest('.attachment-item').remove();
        }
        
        // Collect attachments from form
        function collectAttachments() {
            const attachments = [];
            const items = document.querySelectorAll('.attachment-item');
            const errors = [];
            
            items.forEach((item, index) => {
                const url = item.querySelector('.attachment-url').value.trim();
                const filename = item.querySelector('.attachment-filename').value.trim();
                const mimeType = item.querySelector('.attachment-mime').value.trim();
                
                if (url && filename) {
                    // Validate URL - must be HTTP/HTTPS, not file://
                    if (url.startsWith('file://')) {
                        errors.push(`Attachment ${index + 1}: Local file paths (file://) are not supported. Please upload the file to a web server and use an HTTP/HTTPS URL.`);
                        return;
                    }
                    
                    if (!url.startsWith('http://') && !url.startsWith('https://')) {
                        errors.push(`Attachment ${index + 1}: URL must start with http:// or https://`);
                        return;
                    }
                    
                    attachments.push({
                        url: url,
                        filename: filename,
                        mime_type: mimeType || 'application/octet-stream'
                    });
                }
            });
            
            if (errors.length > 0) {
                alert('❌ Attachment URL Errors:\n\n' + errors.join('\n') + '\n\nPlease fix these errors before submitting.');
                return null;
            }
            
            return attachments;
        }
        
        // Load sample data
        function loadSampleData() {
            document.getElementById('from').value = 'test@altimacrm.com';
            document.getElementById('to').value = 'nishant.joshi@estatic-infotech.com';
            document.getElementById('subject').value = 'Test Email via RabbitMQ with Attachments';
            
            // Try to select first available template
            const templateSelect = document.getElementById('template_id');
            if (templateSelect.options.length > 1) {
                templateSelect.selectedIndex = 1; // Select first template (skip "Select a Template" option)
                templateSelect.dispatchEvent(new Event('change')); // Trigger change event to show info
            }
            document.getElementById('template_data').value = JSON.stringify({
                "name": "John Doe",
                "company": "Example Corp",
                "message": "This is a test email with template support and file attachments."
            }, null, 2);
            
            // Clear existing attachments and add sample
            document.getElementById('attachmentsContainer').innerHTML = '';
            addAttachment();
            const firstItem = document.querySelector('.attachment-item');
            if (firstItem) {
                firstItem.querySelector('.attachment-url').value = 'https://example.com/files/sample-document.pdf';
                firstItem.querySelector('.attachment-filename').value = 'sample-document.pdf';
                firstItem.querySelector('.attachment-mime').value = 'application/pdf';
            }
        }
        
        // Handle form submission
        document.getElementById('rabbitmqForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Parse template_data JSON
            let templateData = {};
            try {
                templateData = JSON.parse(formData.get('template_data') || '{}');
            } catch (error) {
                alert('❌ Invalid JSON in Template Data field. Please check your JSON syntax.');
                return;
            }
            
            // Collect attachments
            const attachments = collectAttachments();
            
            // If attachments collection failed (validation errors), stop submission
            if (attachments === null) {
                return;
            }
            
            // Ensure attachments is always an array (even if empty)
            const attachmentsArray = Array.isArray(attachments) ? attachments : [];
            
            console.log('Sending email with attachments:', {
                count: attachmentsArray.length,
                attachments: attachmentsArray
            });
            
            const emailData = {
                tenant_id: formData.get('tenant_id'),
                provider_id: formData.get('provider_id'),
                from: formData.get('from'),
                to: formData.get('to').split(',').map(email => email.trim()),
                subject: formData.get('subject') || null, // Optional
                template_id: formData.get('template_id'),
                template_data: templateData,
                attachments: attachmentsArray.length > 0 ? attachmentsArray : [] // Always send as array, even if empty
            };
            
            try {
                const response = await fetch(`${baseUrl}/api/rabbitmq/send-email`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(emailData)
                });
                
                const result = await response.json();
                
                const statusDisplay = document.getElementById('statusDisplay');
                statusDisplay.style.display = 'block';
                
                if (result.success) {
                    const attachmentsInfo = attachments.length > 0 ? 
                        `\n📎 Attachments: ${attachments.length} file(s) will be fetched and attached` : 
                        '\n📎 Attachments: None';
                    
                    statusDisplay.innerHTML = `✅ Email Queued Successfully!

📧 Message ID: ${result.data.message_id}
📦 Outbox ID: ${result.data.outbox_id}
📋 Status: ${result.data.status}
⏰ Queued At: ${result.data.queued_at}
⏱️ Estimated Processing: ${result.data.estimated_processing_time}${attachmentsInfo}

The email has been queued and will be processed asynchronously.
Files will be fetched from URLs and attached during processing.`;
                    statusDisplay.style.backgroundColor = '#d4edda';
                    statusDisplay.style.borderColor = '#c3e6cb';
                    statusDisplay.style.color = '#155724';
                } else {
                    statusDisplay.innerHTML = `❌ Failed to Queue Email

Error: ${result.message}
Details: ${result.error || 'No additional details'}`;
                    statusDisplay.style.backgroundColor = '#f8d7da';
                    statusDisplay.style.borderColor = '#f5c6cb';
                    statusDisplay.style.color = '#721c24';
                }
                
                // Refresh queue status
                refreshQueueStatus();
                
            } catch (error) {
                console.error('Error:', error);
                const statusDisplay = document.getElementById('statusDisplay');
                statusDisplay.style.display = 'block';
                statusDisplay.innerHTML = `❌ Error: ${error.message}`;
                statusDisplay.style.backgroundColor = '#f8d7da';
                statusDisplay.style.borderColor = '#f5c6cb';
                statusDisplay.style.color = '#721c24';
            }
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
                closePreviewModal();
                closeCreateTemplateModal();
            }
        });

        // Template Preview Functions
        function previewSelectedTemplate() {
            const templateSelect = document.getElementById('template_id');
            const selectedOption = templateSelect.options[templateSelect.selectedIndex];
            
            if (!selectedOption || !selectedOption.value) {
                alert('Please select a template first');
                return;
            }

            const templateData = JSON.parse(selectedOption.dataset.templateData || '{}');
            const templateDataInput = document.getElementById('template_data').value;
            
            let sampleData = {};
            try {
                if (templateDataInput.trim()) {
                    sampleData = JSON.parse(templateDataInput);
                }
            } catch (e) {
                // Use default sample data if JSON is invalid
            }

            // If no data provided, use template variables to create sample
            if (Object.keys(sampleData).length === 0 && templateData.variables) {
                Object.keys(templateData.variables).forEach(key => {
                    sampleData[key] = `Sample ${key}`;
                });
            }

            showTemplatePreview(templateData, sampleData);
        }

        function showTemplatePreview(template, sampleData) {
            const modal = document.getElementById('previewModal');
            const content = document.getElementById('previewContent');
            
            let htmlPreview = '';
            let textPreview = '';

            // Render HTML content if available
            if (template.html_content) {
                htmlPreview = renderTemplate(template.html_content, sampleData);
            }

            // Render text content if available
            if (template.text_content) {
                textPreview = renderTemplate(template.text_content, sampleData);
            }

            content.innerHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #8a2be2; margin-bottom: 10px;">${template.name || 'Template'}</h3>
                    <p><strong>Template ID:</strong> <code>${template.template_id}</code></p>
                    <p><strong>Subject:</strong> ${renderTemplate(template.subject || '', sampleData)}</p>
                    ${template.variables ? `<p><strong>Variables:</strong> ${Object.keys(template.variables).join(', ')}</p>` : ''}
                </div>
                ${htmlPreview ? `
                <div style="margin-bottom: 20px;">
                    <h4 style="color: #00d4ff; margin-bottom: 10px;">📧 HTML Preview</h4>
                    <div class="template-preview">
                        <div class="template-preview-html">${htmlPreview}</div>
                    </div>
                </div>
                ` : ''}
                ${textPreview ? `
                <div>
                    <h4 style="color: #00d4ff; margin-bottom: 10px;">📝 Text Preview</h4>
                    <div class="template-preview">
                        <div class="template-preview-text">${escapeHtml(textPreview)}</div>
                    </div>
                </div>
                ` : ''}
                ${!htmlPreview && !textPreview ? '<p style="color: #ffc107;">⚠️ No content available for preview</p>' : ''}
            `;

            modal.classList.add('active');
        }

        function renderTemplate(content, data) {
            if (!content) return '';
            
            let rendered = content;
            // Simple variable replacement (Blade-like syntax)
            Object.keys(data).forEach(key => {
                const regex = new RegExp(`\\{\\{\\s*${key}\\s*\\}\\}`, 'g');
                rendered = rendered.replace(regex, data[key]);
            });
            
            return rendered;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function closePreviewModal() {
            document.getElementById('previewModal').classList.remove('active');
        }

        // Create Template Functions
        function openCreateTemplateModal() {
            document.getElementById('createTemplateModal').classList.add('active');
            document.getElementById('createTemplateForm').reset();
            document.getElementById('createTemplateStatus').style.display = 'none';
        }

        function closeCreateTemplateModal() {
            document.getElementById('createTemplateModal').classList.remove('active');
        }

        // Handle create template form submission
        document.getElementById('createTemplateForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const statusDiv = document.getElementById('createTemplateStatus');
            
            const templateData = {
                template_id: formData.get('template_id'),
                name: formData.get('name'),
                subject: formData.get('subject'),
                html_content: formData.get('html_content'),
                text_content: formData.get('text_content') || null,
                category: formData.get('category') || 'system',
                language: formData.get('language') || 'en',
                is_active: true
            };

            statusDiv.style.display = 'block';
            statusDiv.innerHTML = '<p style="color: #00d4ff;">⏳ Creating template...</p>';

            try {
                const response = await fetch(`${baseUrl}/api/email/templates`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(templateData)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    statusDiv.innerHTML = `
                        <div style="background: rgba(40, 167, 69, 0.2); border: 1px solid #28a745; padding: 15px; border-radius: 5px; color: #28a745;">
                            ✅ Template created successfully!<br>
                            <strong>Template ID:</strong> ${result.data.template_id || templateData.template_id}<br>
                            <button class="btn btn-primary btn-sm" onclick="closeCreateTemplateModal(); loadTemplates();" style="margin-top: 10px;">Refresh Templates</button>
                        </div>
                    `;
                    
                    // Auto-refresh templates after 2 seconds
                    setTimeout(() => {
                        closeCreateTemplateModal();
                        loadTemplates();
                    }, 2000);
                } else {
                    statusDiv.innerHTML = `
                        <div style="background: rgba(220, 53, 69, 0.2); border: 1px solid #dc3545; padding: 15px; border-radius: 5px; color: #dc3545;">
                            ❌ Failed to create template<br>
                            <strong>Error:</strong> ${result.message || result.error || 'Unknown error'}<br>
                            ${result.errors ? `<pre style="margin-top: 10px; font-size: 0.85rem;">${JSON.stringify(result.errors, null, 2)}</pre>` : ''}
                        </div>
                    `;
                }
            } catch (error) {
                statusDiv.innerHTML = `
                    <div style="background: rgba(220, 53, 69, 0.2); border: 1px solid #dc3545; padding: 15px; border-radius: 5px; color: #dc3545;">
                        ❌ Error: ${error.message}<br>
                        <small>Note: Make sure the API endpoint POST /api/email/templates exists</small>
                    </div>
                `;
            }
        });

        // Close modals when clicking outside
        document.getElementById('previewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePreviewModal();
            }
        });

        document.getElementById('createTemplateModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateTemplateModal();
            }
        });
    </script>
</body>
</html> 