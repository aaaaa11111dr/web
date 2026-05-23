const form = document.querySelector('#searchForm');
const q = document.querySelector('#q');
const state = document.querySelector('#state');
const results = document.querySelector('#results');
const pager = document.querySelector('#pager');
const topAd = document.querySelector('#topAd');
const bottomAd = document.querySelector('#bottomAd');
let currentPage = 1;
let currentKeyword = '';
let pagerCooldown = 0;
let pagerTimer = null;
let cooldown = 0;

function escapeHtml(text) {
  return String(text || '').replace(/[&<>'"]/g, function(s) {
    var m = {'&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#039;', '"':'&quot;'};
    return m[s];
  });
}

function mergeAds(results, ads) {
  if (!results || !results.length) return results;
  var merged = [];
  var mid = Math.floor(results.length / 2);
  for (var i = 0; i < results.length; i++) {
    if (i === 0 && ads.top && ads.top !== '') {
      merged.push({_ad: true, html: ads.top, label: '结果顶部广告'});
    }
    merged.push(results[i]);
    if (i === mid && ads.middle && ads.middle !== '') {
      merged.push({_ad: true, html: ads.middle, label: '结果中部广告'});
    }
  }
  if (ads.bottom && ads.bottom !== '') {
    merged.push({_ad: true, html: ads.bottom, label: '结果底部广告'});
  }
  return merged;
}

function renderItems(items) {
  return items.map(function(item) {
    if (item._ad) {
      return '<div class="result-ad-slot ad-inline" aria-label="' + escapeHtml(item.label) + '">' + item.html + '</div>';
    }
    return '<article class="result-card">' +
      '<a class="result-title" href="' + escapeHtml(item.open_url) + '">' + escapeHtml(item.title) + '</a>' +
      '<div class="result-url">' + escapeHtml(item.display_url) + '</div>' +
      '<p>' + escapeHtml(item.snippet) + '</p>' +
    '</article>';
  }).join('');
}

function renderBlocked(word, adHtml) {
  var html = '<div class="blocked-page"><div class="blocked-icon">⛔</div>';
  html += '<h2>搜索被拦截</h2>';
  html += '<p class="blocked-msg">' + escapeHtml(word) + '词为非法关键词，请合规使用</p>';
  if (adHtml && adHtml !== '') {
    html += '<section class="ad-slot" aria-label="拦截页广告">' + adHtml + '</section>';
  }
  html += '</div>';
  return html;
}

function renderExhausted(msg, adHtml) {
  var html = '<div class="blocked-page"><div class="blocked-icon">🕐</div>';
  html += '<h2>资源繁忙</h2>';
  html += '<p class="blocked-msg">' + escapeHtml(msg || '因资源耗尽，请稍等再访问') + '</p>';
  if (adHtml && adHtml !== '') {
    html += '<section class="ad-slot" aria-label="资源耗尽页广告">' + adHtml + '</section>';
  }
  html += '</div>';
  return html;
}

function startPagerCooldown(seconds) {
  cooldown = seconds;
  if (pagerTimer) clearInterval(pagerTimer);
  var prevBtn = pager.querySelector('[data-page="prev"]');
  var nextBtn = pager.querySelector('[data-page="next"]');
  function update() {
    prevBtn.disabled = true;
    nextBtn.disabled = true;
    nextBtn.textContent = cooldown + 's';
    if (cooldown <= 0) {
      clearInterval(pagerTimer);
      pagerTimer = null;
      nextBtn.textContent = '下一页';
      nextBtn.disabled = false;
      prevBtn.disabled = currentPage <= 1;
    }
  }
  update();
  pagerTimer = setInterval(function() { cooldown--; update(); }, 1000);
}

async function doSearch(page) {
  if (page === undefined) page = 1;
  var keyword = q.value.trim();
  if (!keyword) { state.textContent = '请输入关键词。'; results.innerHTML = ''; if (pager) pager.hidden = true; if (topAd) topAd.innerHTML = ''; if (bottomAd) bottomAd.innerHTML = ''; return; }
  currentKeyword = keyword; currentPage = page; state.textContent = '搜索中…'; results.innerHTML = ''; if (pager) pager.hidden = true; if (topAd) topAd.innerHTML = ''; if (bottomAd) bottomAd.innerHTML = '';
  if (pagerTimer) { clearInterval(pagerTimer); pagerTimer = null; }
  try {
    var res = await fetch('/api/search.php?q=' + encodeURIComponent(keyword) + '&page=' + page, {headers: {'Accept': 'application/json'}});
    var data = await res.json();
    if (!data.ok) throw new Error(data.message || '搜索失败');

    // Blocked keyword
    if (data.blocked) {
      state.innerHTML = '';
      results.innerHTML = renderBlocked(data.blocked_word || '', (data.ads && data.ads.blocked) || '');
      if (pager) pager.hidden = true;
      if (topAd) topAd.innerHTML = '';
      if (bottomAd) bottomAd.innerHTML = '';
      return;
    }

    // Resource exhausted
    if (data.resource_exhausted) {
      state.innerHTML = '';
      results.innerHTML = renderExhausted(data.message, (data.ads && data.ads.resource) || '');
      if (pager) pager.hidden = true;
      if (topAd) topAd.innerHTML = '';
      if (bottomAd) bottomAd.innerHTML = '';
      return;
    }

    // No results
    if (!data.results || !data.results.length) {
      state.textContent = '没有解析到结果，可能需要调整代理或稍后再试。';
      if (pager) pager.hidden = true;
      return;
    }

    // Merge ads into results
    var items = mergeAds(data.results, data.ads || {});
    state.textContent = '第 ' + data.page + ' 页，找到 ' + data.results.length + ' 条结果'
      + (data.via_proxy ? ' (通过代理 ' + (data.proxy_slot || '') + ')' : ' (直连)');
    results.innerHTML = renderItems(items);

    // Pager with cooldown
    if (pager) {
      pager.hidden = false;
      var cd = parseInt(data.pager_countdown) || 20;
      if (cd > 0) {
        startPagerCooldown(cd);
      } else {
        pager.querySelector('[data-page="prev"]').disabled = data.page <= 1;
        pager.querySelector('[data-page="next"]').disabled = false;
        pager.querySelector('[data-page="next"]').textContent = '下一页';
      }
    }
  } catch (err) {
    state.textContent = err.message || '搜索失败';
    if (pager) pager.hidden = true;
  }
}

if (form) form.addEventListener('submit', function(e) { e.preventDefault(); doSearch(1); });

if (pager) pager.addEventListener('click', function(e) {
  var btn = e.target.closest('button');
  if (!btn) return;
  if (cooldown > 0) return;
  if (btn.dataset.page === 'prev' && currentPage > 1) doSearch(currentPage - 1);
  if (btn.dataset.page === 'next') doSearch(currentPage + 1);
});
