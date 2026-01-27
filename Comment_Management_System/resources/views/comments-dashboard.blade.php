<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Comments Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        .row { display: flex; gap: 16px; margin-bottom: 16px; flex-wrap: wrap; }
        .card { border: 1px solid #ddd; padding: 12px; border-radius: 6px; width: 320px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
        label { display: block; font-weight: 600; margin: 8px 0 4px; }
        input, textarea { width: 100%; padding: 8px; }
        button { padding: 8px 12px; margin-top: 8px; cursor: pointer; }
        pre { background: #f7f7f7; padding: 12px; border-radius: 4px; max-height: 360px; overflow: auto; }
        table { border-collapse: collapse; width: 100%; margin-top: 12px; }
        th, td { border: 1px solid #eee; padding: 8px; text-align: left; }
        .small { font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <h2>Comments API Dashboard</h2>
    <p class="small">Set your base URL and IDs, then trigger API calls. Results appear in the log and table.</p>

    <div class="row">
        <div class="card" style="width: 100%">
            <label>API Base</label>
            <input id="baseUrl" value="{{ url('/api/cms') }}" />
        </div>
    </div>

    <div class="row">
        <div class="card">
            <h4>List Comments</h4>
            <label>Page ID</label>
            <input id="listPageId" type="number" value="1" min="1">
            <label style="font-weight:400;"><input type="checkbox" id="approvedOnly"> approved_only</label>
            <button onclick="listComments()">GET /pages/{page}/comments</button>
        </div>

        <div class="card">
            <h4>Create Comment</h4>
            <label>Page ID</label>
            <input id="createPageId" type="number" value="1" min="1">
            <label>Content</label>
            <textarea id="createContent">New comment from dashboard</textarea>
            <label>User ID (optional)</label>
            <input id="createUserId" type="number" min="1">
            <label>Parent ID (optional)</label>
            <input id="createParentId" type="number" min="1">
            <button onclick="createComment()">POST /pages/{page}/comments</button>
        </div>

        <div class="card">
            <h4>Update Comment</h4>
            <label>Comment ID</label>
            <input id="updateId" type="number" min="1">
            <label>New Content</label>
            <textarea id="updateContent">Updated content</textarea>
            <button onclick="updateComment()">PUT /comments/{id}</button>
        </div>

        <div class="card">
            <h4>Delete Comment</h4>
            <label>Comment ID</label>
            <input id="deleteId" type="number" min="1">
            <button onclick="deleteComment()">DELETE /comments/{id}</button>
        </div>

        <div class="card">
            <h4>Moderate (admin)</h4>
            <label>Comment ID</label>
            <input id="moderateId" type="number" min="1">
            <button onclick="moderate('approve')">POST /comments/{id}/approve</button>
            <button onclick="moderate('reject')">POST /comments/{id}/reject</button>
        </div>

        <div class="card">
            <h4>Pages</h4>
            <button onclick="listPages()">GET /pages</button>
            <label>Title</label>
            <input id="pageTitle" value="New Page">
            <button onclick="createPage()">POST /pages</button>
        </div>
    </div>

    <h4>Latest List Result</h4>
    <div id="tableContainer"></div>

    <h4>Latest Pages Result</h4>
    <div id="pagesContainer"></div>

    <h4>Response Log</h4>
    <pre id="log"></pre>

    <script>
        const logEl = document.getElementById('log');
        const tableEl = document.getElementById('tableContainer');
        const pagesEl = document.getElementById('pagesContainer');

        function base() { return document.getElementById('baseUrl').value.replace(/\/$/, ''); }

        function appendLog(title, obj) {
            const time = new Date().toISOString();
            logEl.textContent = `[${time}] ${title}\n` + JSON.stringify(obj, null, 2) + '\n\n' + logEl.textContent;
        }

        function renderTable(list) {
            if (!Array.isArray(list) || !list.length) {
                tableEl.innerHTML = '<p>No comments.</p>';
                return;
            }
            const rows = list.map(c => {
                return `<tr>
                    <td>${c.id}</td>
                    <td>${c.page_id}</td>
                    <td>${c.user_id ?? ''}</td>
                    <td>${c.content}</td>
                    <td>${c.status}</td>
                    <td>${c.parent_id ?? ''}</td>
                </tr>`;
            }).join('');
            tableEl.innerHTML = `<table>
                <thead><tr><th>ID</th><th>Page</th><th>User</th><th>Content</th><th>Status</th><th>Parent</th></tr></thead>
                <tbody>${rows}</tbody>
            </table>`;
        }

        function renderPages(list) {
            if (!Array.isArray(list) || !list.length) {
                pagesEl.innerHTML = '<p>No pages.</p>';
                return;
            }
            const rows = list.map(p => `<tr><td>${p.id}</td><td>${p.title}</td></tr>`).join('');
            pagesEl.innerHTML = `<table>
                <thead><tr><th>ID</th><th>Title</th></tr></thead>
                <tbody>${rows}</tbody>
            </table>`;
        }

        async function api(path, options = {}) {
            const res = await fetch(base() + path, {
                headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
                ...options
            });
            let body;
            try { body = await res.json(); } catch (e) { body = await res.text(); }
            return { ok: res.ok, status: res.status, body };
        }

        async function listComments() {
            const pageId = document.getElementById('listPageId').value || 1;
            const approvedOnly = document.getElementById('approvedOnly').checked;
            const query = approvedOnly ? '?approved_only=1' : '';
            const result = await api(`/pages/${pageId}/comments${query}`);
            appendLog(`GET /pages/${pageId}/comments${query}`, result);
            if (result.ok) renderTable(result.body);
        }

        async function createComment() {
            const pageId = document.getElementById('createPageId').value || 1;
            const content = document.getElementById('createContent').value;
            const parentId = document.getElementById('createParentId').value || null;
            const userId = document.getElementById('createUserId').value || null;
            const payload = { content, parent_id: parentId ? Number(parentId) : null };
            if (userId) payload.user_id = Number(userId);
            const result = await api(`/pages/${pageId}/comments`, {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            appendLog(`POST /pages/${pageId}/comments`, result);
            if (result.ok) listComments();
        }

        async function updateComment() {
            const id = document.getElementById('updateId').value;
            const content = document.getElementById('updateContent').value;
            if (!id) return alert('Provide comment ID');
            const result = await api(`/comments/${id}`, {
                method: 'PUT',
                body: JSON.stringify({ content })
            });
            appendLog(`PUT /comments/${id}`, result);
            if (result.ok) listComments();
        }

        async function deleteComment() {
            const id = document.getElementById('deleteId').value;
            if (!id) return alert('Provide comment ID');
            const result = await api(`/comments/${id}`, { method: 'DELETE' });
            appendLog(`DELETE /comments/${id}`, result);
            if (result.ok) listComments();
        }

        async function moderate(action) {
            const id = document.getElementById('moderateId').value;
            if (!id) return alert('Provide comment ID');
            const result = await api(`/comments/${id}/${action}`, { method: 'POST' });
            appendLog(`POST /comments/${id}/${action}`, result);
            if (result.ok) listComments();
        }

        async function listPages() {
            const result = await api('/pages');
            appendLog('GET /pages', result);
            if (result.ok) renderPages(result.body);
        }

        async function createPage() {
            const title = document.getElementById('pageTitle').value || 'Untitled';
            const result = await api('/pages', {
                method: 'POST',
                body: JSON.stringify({ title })
            });
            appendLog('POST /pages', result);
            if (result.ok) {
                listPages();
            }
        }

        // Auto-load on open
        listComments();
        listPages();
    </script>
</body>
</html>

