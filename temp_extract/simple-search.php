<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>快速搜索</title>
    <link rel="stylesheet" href="/assets/style.css?v=2026051401">
    <style>
        .simple-body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .simple-shell {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 90%;
        }
        .simple-title {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .simple-subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .simple-search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .simple-search-input {
            flex: 1;
            padding: 15px 20px;
            font-size: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 50px;
            outline: none;
            transition: border-color 0.3s;
        }
        .simple-search-input:focus {
            border-color: #667eea;
        }
        .simple-search-btn {
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .simple-search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .search-engines {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }
        .engine-btn {
            padding: 10px 20px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        .engine-btn:hover, .engine-btn.active {
            border-color: #667eea;
            background: #f0f4ff;
        }
        .results-section {
            margin-top: 30px;
            text-align: left;
            display: none;
        }
        .result-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .result-title {
            color: #1a0dab;
            font-size: 18px;
            text-decoration: none;
            display: block;
            margin-bottom: 5px;
        }
        .result-title:hover {
            text-decoration: underline;
        }
        .result-url {
            color: #006621;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .result-snippet {
            color: #545454;
            font-size: 14px;
            line-height: 1.5;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="simple-body">
    <div class="simple-shell">
        <h1 class="simple-title">🔍 快速搜索</h1>
        <p class="simple-subtitle">选择搜索引擎，输入关键词开始搜索</p>
        
        <div class="search-engines">
            <button class="engine-btn active" data-engine="google">Google</button>
            <button class="engine-btn" data-engine="duckduckgo">DuckDuckGo</button>
            <button class="engine-btn" data-engine="bing">Bing</button>
            <button class="engine-btn" data-engine="baidu">百度</button>
        </div>

        <form class="simple-search-form" id="searchForm">
            <input type="text" class="simple-search-input" id="searchInput" placeholder="输入搜索关键词..." autofocus>
            <button type="submit" class="simple-search-btn">搜索</button>
        </form>

        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>正在搜索...</p>
        </div>

        <div class="results-section" id="resultsSection">
            <h3 style="margin-bottom: 15px; color: #333;">搜索结果</h3>
            <div id="resultsContainer"></div>
        </div>
    </div>

    <script>
        let currentEngine = 'google';
        
        const searchUrls = {
            google: 'https://www.google.com/search?q=',
            duckduckgo: 'https://duckduckgo.com/?q=',
            bing: 'https://www.bing.com/search?q=',
            baidu: 'https://www.baidu.com/s?wd='
        };

        document.querySelectorAll('.engine-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.engine-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentEngine = this.dataset.engine;
            });
        });

        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const query = document.getElementById('searchInput').value.trim();
            if (query) {
                window.open(searchUrls[currentEngine] + encodeURIComponent(query), '_blank');
            }
        });

        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && this.value.trim()) {
                document.getElementById('searchForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>
