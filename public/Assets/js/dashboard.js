function performTopbarSearch(term) {
  const query = term.trim().toLowerCase();
  if (!query) return;

  const routes = [
    { url: '/vehicles', keywords: ['vehicle', 'vehicles', 'fleet', 'plate', 'gps'] },
    { url: '/tools', keywords: ['tool', 'tools', 'asset', 'assets', 'inventory'] },
    { url: '/personnel', keywords: ['personnel', 'employee', 'employees', 'driver', 'drivers', 'janitor', 'janitors', 'carpentry', 'carpentries', 'maintenance'] },
    { url: '/travel', keywords: ['travel', 'trip', 'ticket', 'journey', 'route', 'destination'] },
    { url: '/notifications', keywords: ['notification', 'notifications', 'alert', 'alerts'] },
    { url: '/reports', keywords: ['report', 'reports', 'archive', 'archiving'] },
    { url: '/safety', keywords: ['safety', 'janitorial', 'guard', 'key', 'work order', 'maintenance'] },
    { url: '/dashboard', keywords: ['dashboard', 'home', 'overview'] }
  ];

  for (const route of routes) {
    if (route.keywords.some(keyword => query.includes(keyword))) {
      window.location.href = window.location.origin + route.url;
      return;
    }
  }

  const input = document.querySelector('.topbar .search-box');
  if (input) {
    input.value = '';
    input.placeholder = 'No matches. Try: vehicle, personnel, travel';
  }
}

const topbarSearch = document.querySelector('.topbar .search-box');
if (topbarSearch) {
  topbarSearch.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      e.preventDefault();
      performTopbarSearch(topbarSearch.value);
    }
  });

  topbarSearch.addEventListener('input', e => {
    const value = e.target.value.trim().toLowerCase();
    if (!value) {
      topbarSearch.setAttribute('placeholder', 'Search operational resources...');
    }
  });
}
