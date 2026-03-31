<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Theme Sample</title>
    <style>
        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #e2e8f0;
            min-height: 100vh;
            display: grid;
            place-items: center;
        }
        .card {
            width: min(760px, 92vw);
            border: 1px solid #334155;
            background: rgba(15, 23, 42, 0.86);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 20px 60px rgba(2, 6, 23, 0.45);
        }
        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            color: #93c5fd;
            background: rgba(30, 64, 175, 0.25);
            border: 1px solid rgba(59, 130, 246, 0.5);
        }
        h1 {
            margin: 14px 0 10px;
            font-size: clamp(26px, 4.2vw, 42px);
            line-height: 1.15;
        }
        p {
            margin: 0;
            color: #cbd5e1;
            line-height: 1.7;
        }
        code {
            color: #f8fafc;
        }
    </style>
</head>
<body>
<main class="card">
    <span class="badge">Theme Active</span>
    <h1>Theme Sample đang hoạt động</h1>
    <p>
        Nếu bạn nhìn thấy giao diện này ở route <code>/</code>, nghĩa là cơ chế ưu tiên view
        theo theme active đã hoạt động đúng.
    </p>
</main>
</body>
</html>
