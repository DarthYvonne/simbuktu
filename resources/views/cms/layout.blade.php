<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CMS') | Simbuktu</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #f4f6f8; color: #2c3e50; }
        .cms-header {
            background: #2c3e50; color: #fff; padding: 16px 24px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
        }
        .cms-header a { color: #fff; text-decoration: none; }
        .cms-header h1 { font-size: 18px; font-weight: 600; letter-spacing: 0.5px; }
        .cms-header nav { display: flex; gap: 16px; font-size: 14px; }
        .cms-main { max-width: 1000px; margin: 24px auto; padding: 0 16px; }
        .card { background: #fff; border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px; margin-bottom: 16px; }
        .btn {
            display: inline-block; background: #3498db; color: #fff;
            padding: 8px 16px; border: 0; border-radius: 4px;
            text-decoration: none; font-size: 14px; cursor: pointer;
        }
        .btn:hover { background: #2980b9; }
        .btn--secondary { background: #95a5a6; }
        .btn--danger { background: #e74c3c; }
        .btn--danger:hover { background: #c0392b; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #eee; font-size: 14px; vertical-align: middle; }
        th { background: #fafafa; font-weight: 600; }
        td.actions { text-align: right; white-space: nowrap; }
        td.actions form { display: inline; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #555; }
        input[type=text], input[type=number], textarea {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px;
        }
        textarea { min-height: 240px; font-family: ui-monospace, monospace; }
        .form-row { margin-bottom: 16px; }
        .form-row--inline { display: flex; gap: 16px; flex-wrap: wrap; }
        .form-row--inline > div { flex: 1; min-width: 160px; }
        .checkbox { display: flex; align-items: center; gap: 8px; }
        .checkbox input { width: auto; }
        .status { background: #d4edda; color: #155724; padding: 10px 16px; border-radius: 4px; margin-bottom: 16px; }
        .pill { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; }
        .pill--on { background: #d4edda; color: #155724; }
        .pill--off { background: #f8d7da; color: #721c24; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-size: 13px; }
        @media (max-width: 600px) {
            th, td { font-size: 13px; padding: 8px 4px; }
            td.actions { text-align: left; padding-top: 0; }
            .pill { font-size: 11px; }
        }
    </style>
</head>
<body>
    <div class="cms-header">
        <h1><a href="/cms">Simbuktu CMS</a></h1>
        <nav>
            <a href="/cms">Sider</a>
            <a href="/cms/settings">Forside</a>
            <a href="/" target="_blank">Vis website ↗</a>
        </nav>
    </div>
    <div class="cms-main">
        @if(session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif
        @yield('content')
    </div>
</body>
</html>
