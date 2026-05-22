<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>内部搜索系统</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 600px; margin: 0 auto; padding: 40px 20px; }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { font-size: 48px; font-weight: bold; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .logo p { color: rgba(255,255,255,0.8); margin-top: 10px; }
        .search-box { background: white; border-radius: 50px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 10px; display: flex; gap: 10px; }
        .search-input { flex: 1; padding: 15px 25px; border: none; outline: none; font-size: 16px; background: transparent; }
        .search-btn { padding: 15px 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 50px; font-size: 16px; cursor: pointer; transition: transform 0.2s; }
        .search-btn:hover { transform: translateY(-2px); }
        .engines { display: flex; justify-content: center; gap: 10px; margin-top: 25px; }
        .engine-btn { padding: 10px 20px; border: 2px solid rgba(255,255,255,0.5); background: rgba(255,255,255,0.1); color: white; border-radius: 25px; cursor: pointer; transition: all 0.3s; font-size: 14px; }
        .engine-btn:hover, .engine-btn.active { background: white; color: #667eea; border-color: white; }
        .admin-link { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: rgba(255,255,255,0.2); color: white; border-radius: 25px; text-decoration: none; font-size: 14px; }
        .admin-link:hover { background: rgba(255,255,255,0.3); }
        .stats { display: flex; justify-content: center; gap: 30px; margin-top: 40px; color: rgba(255,255,255,0.8); font-size: 14px; }
    </style>
</head>
<body>
    <a href="goolehome.php" class="admin-link">管理面板</a>
    
    <div class="container">
        <div class="logo">
            <h1>🔍</h1>
            <p>内部搜索系统</p>
        </div>
        
        <div class="engines">
            <button class="engine-btn active" data-engine="google">Google</button>
            <button class="engine-btn" data-engine="duckduckgo">DuckDuckGo</button>
            <button class="engine-btn" data-engine="startpage">Startpage</button>
            <button class="engine-btn" data-engine="bing">Bing</button>
            <button class="engine-btn" data-engine="baidu">百度</button>
        </div>
        
        <form action="search.php" method="GET" class="search-box">
            <input type="hidden" name="engine" id="engineInput" value="google">
            <input type="text" name="q" class="search-input" placeholder="输入搜索关键词..." autofocus>
            <button type="submit" class="search-btn">搜索</button>
        </form>
        
        <div class="stats">
            <span>支持多引擎搜索</span>
            <span>安全加密</span>
        </div>
    </div>
    
    <script>
        const engines = document.querySelectorAll('.engine-btn');
        const engineInput = document.getElementById('engineInput');
        
        engines.forEach(btn => {
            btn.addEventListener('click', function() {
                engines.forEach(e => e.classList.remove('active'));
                this.classList.add('active');
                engineInput.value = this.dataset.engine;
            });
        });
    </script>
</body>
</html>