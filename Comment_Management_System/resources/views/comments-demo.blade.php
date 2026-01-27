<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Comments Demo</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; }
        .comment { border: 1px solid #ddd; padding: 12px; margin-bottom: 8px; }
        .reply { margin-left: 24px; border-left: 2px solid #eee; padding-left: 12px; }
        form { margin-top: 16px; }
        label { display: block; margin: 8px 0 4px; }
        input, textarea { width: 100%; padding: 8px; }
        button { padding: 8px 14px; }
    </style>
</head>
<body>
    <h2>Comments Demo</h2>
    <p>Page ID: <input id="pageId" type="number" value="1" min="1" style="width:120px"></p>

    <div id="comments"></div>

    <h3>Add Comment</h3>
    <form id="createForm">
        <label for="content">Content</label>
        <textarea id="content" required></textarea>
        <label for="parentId">Parent ID (optional for replies)</label>
        <input id="parentId" type="number" min="1" placeholder="e.g., 1">
        <button type="submit">Post</button>
    </form>

    <script>
        const apiBase = '{{ url('/api/cms') }}';

        async function fetchComments() {
            const pageId = document.getElementById('pageId').value || 1;
            const res = await fetch(`${apiBase}/pages/${pageId}/comments`);
            const data = await res.json();
            renderComments(data, document.getElementById('comments'));
        }

        function renderComments(list, container) {
            container.innerHTML = '';
            list.forEach(c => {
                const el = document.createElement('div');
                el.className = 'comment';
                el.innerHTML = `
                    <div><strong>#${c.id}</strong> by ${c.author?.name ?? 'Unknown'} (${c.status})</div>
                    <div>${c.content}</div>
                `;
                container.appendChild(el);
                if (c.replies && c.replies.length) {
                    const child = document.createElement('div');
                    child.className = 'reply';
                    renderComments(c.replies, child);
                    container.appendChild(child);
                }
            });
        }

        document.getElementById('createForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const pageId = document.getElementById('pageId').value || 1;
            const content = document.getElementById('content').value;
            const parentId = document.getElementById('parentId').value || null;

            const res = await fetch(`${apiBase}/pages/${pageId}/comments`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content, parent_id: parentId ? Number(parentId) : null })
            });

            if (!res.ok) {
                const err = await res.json();
                alert('Error: ' + (err.message || 'Unable to post'));
                return;
            }

            document.getElementById('content').value = '';
            document.getElementById('parentId').value = '';
            fetchComments();
        });

        fetchComments();
    </script>
</body>
</html>

