// Right-hand details card used beside the campus maps (same markup as the Fire Safety building panel).
// facDrill(panelId, { title, sub, banner:{cls,head,text}, floors:[…], tabsLabel, count(f), detail(f), alerts:[{cls,html}], alertsLabel, planLabel, close, footer })
// Needs a global esc(s) on the page.
window.fdState = window.fdState || {};

window.facDrill = function (panelId, o) {
  window.fdState[panelId] = Object.assign({ sel: o.floors[0] || '' }, o);
  window.facDrillRender(panelId);
};

window.facDrillRender = function (panelId) {
  const o = window.fdState[panelId], box = document.getElementById(panelId);
  if (!o || !box) return;
  const st = o.banner.cls === 'fd-ok' ? 'st-new' : o.banner.cls === 'fd-bad' ? 'st-warning' : 'st-expires';
  const tabs = o.floors.map(f => '<button type="button" class="floor-tab' + (f === o.sel ? ' active' : '') + '" data-f="' + esc(f) + '" onclick="facDrillPick(&quot;' + panelId + '&quot;, this.dataset.f)">' + esc(f) + ' <span class="floor-count">' + (o.count ? o.count(f) : '') + '</span></button>').join('');
  const alerts = (o.alerts || []).map(a => '<div class="dp-alert-item ' + a.cls + '"><i class="bi bi-exclamation-triangle-fill"></i> ' + a.html + '</div>').join('') || '<div class="no-alert">No alerts for this building.</div>';
  box.innerHTML = '<div class="drill-panel ' + st + '">' +
    '<div class="dp-header"><div><div class="dp-title">' + esc(o.title) + '</div><div class="dp-sub">' + esc(o.sub) + '</div></div>' +
    '<button type="button" class="dp-close" onclick="' + o.close + '" aria-label="Close"><i class="bi bi-x-lg"></i></button></div>' +
    '<div class="dp-status-box"><strong>' + esc(o.banner.head) + '</strong>' + esc(o.banner.text) + '</div>' +
    '<div class="dp-section-title"><i class="bi bi-' + (o.tabsIcon || 'building') + '"></i> ' + esc(o.tabsLabel || 'Floor') + '</div>' +
    '<div class="floor-tabs">' + tabs + '</div>' +
    '<div class="floor-diagram"><div class="floor-plan"><div class="dp-section-title" style="margin-top:0">' + esc(o.sel) + ' ' + esc(o.planLabel || 'details') + '</div>' + o.detail(o.sel) + '</div></div>' +
    '<div class="dp-section-title"><i class="bi bi-clock-history"></i> ' + esc(o.alertsLabel || 'Alerts') + '</div>' +
    '<div class="dp-alerts">' + alerts + '</div>' +
    (o.footer ? '<div class="fd-footer">' + o.footer + '</div>' : '') + '</div>';
  box.style.display = '';
};

window.facDrillPick = function (panelId, floor) { window.fdState[panelId].sel = floor; window.facDrillRender(panelId); };
