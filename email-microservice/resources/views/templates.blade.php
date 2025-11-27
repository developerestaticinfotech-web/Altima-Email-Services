<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Templates Management - AltimaCRM Email Microservice</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            transition: all 0.3s ease;
            margin: 5px;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #8a2be2, #9d4edd);
            color: white;
            border: 1px solid rgba(138, 43, 226, 0.3);
            box-shadow: 0 4px 15px rgba(138, 43, 226, 0.2);
        }
        
        .btn-primary:hover {
            background: linear-gradient(45deg, #9d4edd, #8a2be2);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(138, 43, 226, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: 1px solid rgba(40, 167, 69, 0.3);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);
        }
        
        .btn-success:hover {
            background: linear-gradient(45deg, #20c997, #28a745);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
        
        .btn-info {
            background: linear-gradient(45deg, #17a2b8, #20c997);
            color: white;
            border: 1px solid rgba(23, 162, 184, 0.3);
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.2);
        }
        
        .btn-info:hover {
            background: linear-gradient(45deg, #20c997, #17a2b8);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);
        }
        
        .btn-warning {
            background: linear-gradient(45deg, #ffc107, #ffca2c);
            color: #212529;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .template-item {
            background: rgba(15, 15, 35, 0.6);
            border: 2px solid rgba(138, 43, 226, 0.2);
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .template-item:hover {
            border-color: #8a2be2;
            box-shadow: 0 5px 15px rgba(138, 43, 226, 0.2);
            transform: translateY(-2px);
        }
        
        .template-item.active {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }
        
        .template-item.inactive {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
            opacity: 0.7;
        }
        
        .template-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .template-name {
            font-weight: bold;
            font-size: 1.1rem;
            color: #e0e0e0;
        }
        
        .template-id {
            font-size: 0.85rem;
            color: #b0b0b0;
            font-family: monospace;
            margin-top: 5px;
        }
        
        .template-subject {
            color: #b0b0b0;
            font-size: 0.9rem;
            margin: 10px 0;
            padding: 10px;
            background: rgba(15, 15, 35, 0.5);
            border-radius: 5px;
        }
        
        .template-meta {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .template-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .badge-active {
            background: rgba(40, 167, 69, 0.2);
            color: #20c997;
        }
        
        .badge-inactive {
            background: rgba(220, 53, 69, 0.2);
            color: #f44336;
        }
        
        .badge-category {
            background: rgba(138, 43, 226, 0.2);
            color: #9d4edd;
        }
        
        .template-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
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

        .loading {
            text-align: center;
            padding: 40px;
            color: #b0b0b0;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #b0b0b0;
        }

        .empty-state h3 {
            color: #e0e0e0;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .template-grid {
                grid-template-columns: 1fr;
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
            <a href="/providers" class="sidebar-menu-item">
                <span>📧</span> Providers
            </a>
            <a href="/rabbitmq-test" class="sidebar-menu-item">
                <span>🐰</span> RabbitMQ Test
            </a>
            <a href="/templates" class="sidebar-menu-item active">
                <span>📝</span> Templates
            </a>
            <a href="/api/health" class="sidebar-menu-item">
                <span>💚</span> Health Check
            </a>
        </nav>
    </div>

    <div class="container">
        <div class="header">
            <h1>📝 Email Templates Management</h1>
            <p>Create, preview, and manage email templates</p>
        </div>
        
        <div class="card">
            <h2>
                <span>Templates</span>
                <button class="btn btn-success" onclick="openCreateTemplateModal()">➕ Add New Template</button>
            </h2>
            
            <div id="templatesList" class="template-grid">
                <div class="loading">Loading templates...</div>
            </div>
            
            <div id="pagination" style="text-align: center; margin-top: 20px; display: none;"></div>
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
        let currentPage = 1;
        let templates = [];

        // Load templates on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTemplates();
        });

        // Get base URL dynamically
        const baseUrl = window.location.origin;
        
        // Load templates
        async function loadTemplates(page = 1) {
            try {
                const response = await fetch(`${baseUrl}/api/email/templates?per_page=20&page=${page}`, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (result.success && result.data && result.data.length > 0) {
                    templates = result.data;
                    displayTemplates(result.data);
                    
                    if (result.pagination) {
                        displayPagination(result.pagination);
                    }
                } else {
                    document.getElementById('templatesList').innerHTML = `
                        <div class="empty-state">
                            <h3>No templates found</h3>
                            <p>Click "Add New Template" to create your first template</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading templates:', error);
                document.getElementById('templatesList').innerHTML = `
                    <div class="empty-state">
                        <h3>Error loading templates</h3>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }

        function displayTemplates(templates) {
            const container = document.getElementById('templatesList');
            
            if (templates.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <h3>No templates found</h3>
                        <p>Click "Add New Template" to create your first template</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = templates.map(template => `
                <div class="template-item ${template.is_active ? 'active' : 'inactive'}">
                    <div class="template-header">
                        <div>
                            <div class="template-name">${template.name || 'Unnamed Template'}</div>
                            <div class="template-id">ID: ${template.template_id}</div>
                        </div>
                        <span class="template-badge ${template.is_active ? 'badge-active' : 'badge-inactive'}">
                            ${template.is_active ? '✓ Active' : '✗ Inactive'}
                        </span>
                    </div>
                    <div class="template-subject">
                        <strong>Subject:</strong> ${template.subject || 'N/A'}
                    </div>
                    <div class="template-meta">
                        ${template.category ? `<span class="template-badge badge-category">${template.category}</span>` : ''}
                        ${template.language ? `<span class="template-badge badge-category">${template.language}</span>` : ''}
                        ${template.variables && Object.keys(template.variables).length > 0 ? 
                            `<span class="template-badge badge-category">${Object.keys(template.variables).length} variables</span>` : ''}
                    </div>
                    <div class="template-actions">
                        <button class="btn btn-info btn-sm" onclick="previewTemplate('${template.template_id}')">👁️ Preview</button>
                        <button class="btn btn-primary btn-sm" onclick="viewTemplateDetails('${template.template_id}')">📄 Details</button>
                    </div>
                </div>
            `).join('');
        }

        function displayPagination(pagination) {
            const paginationDiv = document.getElementById('pagination');
            if (pagination.last_page <= 1) {
                paginationDiv.style.display = 'none';
                return;
            }

            paginationDiv.style.display = 'block';
            let html = '';
            
            if (pagination.current_page > 1) {
                html += `<button class="btn btn-info btn-sm" onclick="loadTemplates(${pagination.current_page - 1})">← Previous</button>`;
            }
            
            html += ` <span style="margin: 0 15px;">Page ${pagination.current_page} of ${pagination.last_page}</span> `;
            
            if (pagination.current_page < pagination.last_page) {
                html += `<button class="btn btn-info btn-sm" onclick="loadTemplates(${pagination.current_page + 1})">Next →</button>`;
            }
            
            paginationDiv.innerHTML = html;
        }

        async function previewTemplate(templateId) {
            try {
                const response = await fetch(`${baseUrl}/api/email/templates/${templateId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (result.success && result.data) {
                    const template = result.data;
                    
                    // Create sample data from template variables
                    let sampleData = {};
                    if (template.variables) {
                        Object.keys(template.variables).forEach(key => {
                            sampleData[key] = `Sample ${key}`;
                        });
                    }
                    
                    showTemplatePreview(template, sampleData);
                } else {
                    alert('Template not found');
                }
            } catch (error) {
                console.error('Error loading template:', error);
                alert('Error loading template: ' + error.message);
            }
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

        function viewTemplateDetails(templateId) {
            window.location.href = `${baseUrl}/api/email/templates/${templateId}`;
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
                        ❌ Error: ${error.message}
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

        // Close modals on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePreviewModal();
                closeCreateTemplateModal();
                const sidebar = document.getElementById('sidebar');
                if (sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            }
        });
    </script>
</body>
</html>

