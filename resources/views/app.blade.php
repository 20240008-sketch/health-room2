<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', '健康管理システム') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Vite CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'EUDC', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .fallback-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
            text-align: center;
        }
        .spinner {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            border: 2px solid #E5E7EB;
            border-top: 2px solid #3B82F6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="fallback-content">
            <h1 style="color: #374151; margin-bottom: 1rem;">健康管理システム</h1>
            <div style="color: #6B7280; margin-bottom: 1rem;">アプリケーションを読み込み中...</div>
            <div class="spinner"></div>
        </div>
    </div>
    
    <script>
        // Vue.jsマウント確認
        setTimeout(function() {
            const app = document.getElementById('app');
            if (app && app.innerHTML.includes('アプリケーションを読み込み中')) {
                console.error('Vue.js application failed to mount');
                app.innerHTML = '<div class="fallback-content"><h2 style="color: #DC2626;">アプリケーションの読み込みに失敗しました</h2><p>ページを再読み込みしてください</p><button onclick="location.reload()" style="margin-top: 1rem; padding: 0.5rem 1rem; background: #3B82F6; color: white; border: none; border-radius: 0.25rem; cursor: pointer;">再読み込み</button></div>';
            }
        }, 5000);
    </script>
</body>
</html>