<?php
require __DIR__ . '/app/bootstrap.php';
require_install();
$settings = get_settings();
if (!has_front_access($settings)) { http_response_code(403); exit('未授权访问'); }
try { $u = validate_external_http_url((string)($_GET['u'] ?? '')); }
catch (Throwable $e) { http_response_code(400); exit($e->getMessage()); }
$q = trim((string)($_GET['q'] ?? ''));
$seconds = int_setting($settings, 'redirect_countdown_seconds', 1, 300, 5);
?>
<!doctype html><html lang="zh-CN"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>即将访问 - <?= e($settings['site_name']) ?></title><link rel="stylesheet" href="/assets/style.css?v=2026051401"></head><body class="home-body"><div class="shell search-shell"><header class="topbar"><div class="brand"><?= brand_text($settings['site_name']) ?></div></header><main class="redirect-main"><div class="redirect-card"><div class="redirect-badge">即将离开本站</div><h1>即将跳转至外部网站</h1><p class="redirect-dest">目标链接：<code><?= e($u) ?></code></p><?php if ($q !== ''): ?><p class="redirect-src">搜索关键词：<strong><?= e($q) ?></strong></p><?php endif; ?><div class="redirect-countdown" id="redirectCountdown">
  <span class="cd-circle" id="cdCircle"><?= $seconds ?></span>
  <span class="cd-label" id="cdLabel">秒后可继续访问</span>
</div><div class="redirect-actions">
  <a id="continueBtn" class="continue-btn disabled" href="<?= e($u) ?>" rel="noopener noreferrer">继续访问</a>
  <a class="back-btn secondary" href="/">返回首页</a>
</div><?= render_ad_pool('redirect', '中转页广告') ?></div></main></div><script>
(function(){
  var remain = <?= $seconds ?>;
  var circle = document.getElementById('cdCircle');
  var label = document.getElementById('cdLabel');
  var btn   = document.getElementById('continueBtn');
  var timer = setInterval(function(){
    remain--;
    circle.textContent = remain;
    if (remain <= 0) {
      clearInterval(timer);
      circle.textContent = '✓';
      label.textContent = '可以继续访问';
      btn.classList.remove('disabled');
      btn.removeAttribute('href'); btn.setAttribute('href', <?= json_encode($u, JSON_UNESCAPED_SLASHES) ?>);
      btn.style.pointerEvents = 'auto';
    }
  }, 1000);
  btn.addEventListener('click', function(e){ if (remain > 0) e.preventDefault(); });
})();
</script></body></html>
