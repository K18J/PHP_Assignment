<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Comments - Comment Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        h1 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .controls {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .filter-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        button:hover {
            background: #2980b9;
        }
        
        button:active {
            transform: scale(0.98);
        }
        
        .refresh-btn {
            background: #27ae60;
        }
        
        .refresh-btn:hover {
            background: #229954;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 5px;
            border-left: 4px solid #3498db;
        }
        
        .stat-card .label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .stat-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        thead {
            background: #34495e;
            color: white;
        }
        
        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 14px;
        }
        
        tbody tr:hover {
            background: #f8f9fa;
        }
        
        tbody tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .content-cell {
            max-width: 300px;
            word-wrap: break-word;
        }
        
        .page-link {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        
        .page-link:hover {
            text-decoration: underline;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .timestamp {
            font-size: 12px;
            color: #95a5a6;
        }
        
        .no-comments {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>All Comments</h1>
        <p class="subtitle">View and manage all comments across all pages</p>
        
        <div class="controls">
            <button class="refresh-btn" onclick="loadComments()">🔄 Refresh</button>
            <div class="filter-group">
                <label>
                    <input type="checkbox" id="approvedOnly" onchange="loadComments()">
                    <span>Show approved only</span>
                </label>
            </div>
        </div>
        
        <div id="stats" class="stats" style="display: none;"></div>
        
        <div id="error" class="error" style="display: none;"></div>
        
        <div id="loading" class="loading">Loading comments...</div>
        
        <div id="commentsTable" style="display: none;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Page</th>
                        <th>Content</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Parent</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody id="commentsBody">
                </tbody>
            </table>
        </div>
        
        <div id="emptyState" class="empty-state" style="display: none;">
            <p>No comments found.</p>
        </div>
    </div>

    <script>
        const API_BASE = '{{ url("/api/cms") }}';
        
        async function loadComments() {
            const loadingEl = document.getElementById('loading');
            const tableEl = document.getElementById('commentsTable');
            const emptyEl = document.getElementById('emptyState');
            const errorEl = document.getElementById('error');
            const statsEl = document.getElementById('stats');
            const bodyEl = document.getElementById('commentsBody');
            
            // Show loading, hide others
            loadingEl.style.display = 'block';
            tableEl.style.display = 'none';
            emptyEl.style.display = 'none';
            errorEl.style.display = 'none';
            statsEl.style.display = 'none';
            
            try {
                const approvedOnly = document.getElementById('approvedOnly').checked;
                const query = approvedOnly ? '?approved_only=1' : '';
                const response = await fetch(`${API_BASE}/comments${query}`);
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to load comments');
                }
                
                loadingEl.style.display = 'none';
                
                if (!Array.isArray(data) || data.length === 0) {
                    emptyEl.style.display = 'block';
                    return;
                }
                
                // Render stats
                const total = data.length;
                const pending = data.filter(c => c.status === 'pending').length;
                const approved = data.filter(c => c.status === 'approved').length;
                const rejected = data.filter(c => c.status === 'rejected').length;
                
                statsEl.innerHTML = `
                    <div class="stat-card">
                        <div class="label">Total Comments</div>
                        <div class="value">${total}</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Pending</div>
                        <div class="value">${pending}</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Approved</div>
                        <div class="value">${approved}</div>
                    </div>
                    <div class="stat-card">
                        <div class="label">Rejected</div>
                        <div class="value">${rejected}</div>
                    </div>
                `;
                statsEl.style.display = 'flex';
                
                // Render table
                bodyEl.innerHTML = data.map(comment => {
                    const createdDate = new Date(comment.created_at);
                    const formattedDate = createdDate.toLocaleDateString() + ' ' + 
                                         createdDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    
                    return `
                        <tr>
                            <td>${comment.id}</td>
                            <td>
                                ${comment.page ? `<a href="#" class="page-link" title="${comment.page.title}">Page ${comment.page_id}</a>` : `Page ${comment.page_id}`}
                            </td>
                            <td class="content-cell">${escapeHtml(comment.content)}</td>
                            <td>${comment.author ? escapeHtml(comment.author.name || comment.author.email || 'User') : 'Guest'}</td>
                            <td><span class="status-badge status-${comment.status}">${comment.status}</span></td>
                            <td>${comment.parent_id ? comment.parent_id : '-'}</td>
                            <td class="timestamp">${formattedDate}</td>
                        </tr>
                    `;
                }).join('');
                
                tableEl.style.display = 'block';
                
            } catch (error) {
                loadingEl.style.display = 'none';
                errorEl.textContent = `Error: ${error.message}`;
                errorEl.style.display = 'block';
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Load comments on page load
        loadComments();
        
        // Auto-refresh every 30 seconds
        setInterval(loadComments, 30000);
    </script>
</body>
</html>
