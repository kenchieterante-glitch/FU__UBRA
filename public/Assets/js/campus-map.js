// Building markers placed on public/images/MAP.jpg (2048 x 1365 px).
// Fire extinguisher icons printed on MAP.jpg; each map draws its own symbol over them.
const ICON_POINTS = [
  [451, 140], [359, 199], [620, 191], [955, 413], [369, 494], [334, 821], [221, 790],
  [469, 1000], [638, 1115], [597, 917], [823, 791], [959, 554], [1068, 801], [1068, 1075],
  [1199, 470], [1254, 751], [1211, 913], [896, 1103], [274, 647],
];

// Coordinates are the numbers from the legend, scaled to the image's native size.
const MAP_BUILDINGS = [
  { n: 2,  name: 'University Cafeteria, Bookstore, Sewing', x: 617,  y: 1099 },
  { n: 3,  name: 'College of Law Building', x: 626,  y: 928 },
  { n: 4,  name: 'College of Agriculture and SIE', x: 488,  y: 1015 },
  { n: 5,  name: 'Museo de Vicente', x: 317,  y: 833 },
  { n: 6,  name: 'Bunk House', x: 204,  y: 863 },
  { n: 8,  name: 'University Library', x: 304,  y: 663 },
  { n: 10, name: 'Executive House', x: 343,  y: 477 },
  { n: 12, name: 'Guest House', x: 359,  y: 224 },
  { n: 13, name: 'HRM Kitchen', x: 434,  y: 119 },
  { n: 14, name: 'College of Education Building', x: 602,  y: 199 },
  { n: 16, name: 'Animation Lab / ROTC Office', x: 926,  y: 316 },
  { n: 17, name: 'LG Sinco Computer Center Building', x: 938,  y: 396 },
  { n: 18, name: 'Sofia Soller Sinco Hall', x: 940,  y: 544 },
  { n: 19, name: 'College of Art & Sciences Building', x: 833,  y: 751 },
  { n: 20, name: 'Art & Science Laboratories / Audio Visual Rooms', x: 1044, y: 788 },
  { n: 21, name: 'College of Business Economics and Accountancy', x: 1180, y: 459 },
  { n: 22, name: 'College of Nursing', x: 1253, y: 673 },
  { n: 23, name: 'Administration Building', x: 1226, y: 857 },
  { n: 25, name: "Registrar's Office", x: 1111, y: 1051 },
  { n: 26, name: 'Business and Finance Office', x: 1109, y: 1094 },
  { n: 27, name: 'Old College of Industrial Engineering and Technology', x: 923,  y: 1103 },
];

// opts: { imageUrl, statusByName, alertFloorsByName }
//   statusByName      – building name -> 'Passed' | 'Needs Attention' (monthly check)
//   hideFloorText – true to leave the floor names off the alert icons (hover title still lists them)
//   alertFloorsByName – building name -> [floor, ...] that have an upcoming or overdue aircon alert (blinks)
window.renderMapImage = function (svgId, opts) {
  const svg = document.getElementById(svgId);
  const ns = 'http://www.w3.org/2000/svg';
  svg.setAttribute('viewBox', '0 0 2048 1365');
  svg.innerHTML = '';

  const img = document.createElementNS(ns, 'image');
  img.setAttribute('href', opts.imageUrl);
  img.setAttribute('x', 0);
  img.setAttribute('y', 0);
  img.setAttribute('width', 2048);
  img.setAttribute('height', 1365);
  svg.appendChild(img);

  const heading = document.createElementNS(ns, 'rect');
  heading.setAttribute('x', 1400); heading.setAttribute('y', 196);
  heading.setAttribute('width', 640); heading.setAttribute('height', 62);
  heading.setAttribute('fill', '#ffffff');
  svg.appendChild(heading);

  const legendCover = document.createElementNS(ns, 'rect');
  legendCover.setAttribute('x', 958); legendCover.setAttribute('y', 74);
  legendCover.setAttribute('width', 424); legendCover.setAttribute('height', 220);
  legendCover.setAttribute('fill', '#ffffff');
  svg.appendChild(legendCover);

  if (opts.legend === 'aircon' || opts.legend === 'cleaning' || opts.legend === 'building') {
    const cover = document.createElementNS(ns, 'rect');
    cover.setAttribute('x', 958); cover.setAttribute('y', 74);
    cover.setAttribute('width', 424); cover.setAttribute('height', 220);
    cover.setAttribute('fill', '#ffffff');
    svg.appendChild(cover);

    ICON_POINTS.forEach(([x, y]) => {
      if (opts.legend === 'cleaning') { drawBroom(svg, ns, x, y); return; }
      if (opts.legend === 'building') { drawCheck(svg, ns, x, y); return; }
      const bg = document.createElementNS(ns, 'circle');
      bg.setAttribute('cx', x); bg.setAttribute('cy', y); bg.setAttribute('r', 22);
      bg.setAttribute('fill', '#1c6dd0');
      bg.setAttribute('stroke', '#ffffff'); bg.setAttribute('stroke-width', 3);
      svg.appendChild(bg);
      [0, 60, 120].forEach(deg => {
        const rad = deg * Math.PI / 180;
        const dx = Math.cos(rad) * 14, dy = Math.sin(rad) * 14;
        const arm = document.createElementNS(ns, 'line');
        arm.setAttribute('x1', x - dx); arm.setAttribute('y1', y - dy);
        arm.setAttribute('x2', x + dx); arm.setAttribute('y2', y + dy);
        arm.setAttribute('stroke', '#ffffff'); arm.setAttribute('stroke-width', 3);
        arm.setAttribute('stroke-linecap', 'round');
        svg.appendChild(arm);
      });
    });

  }

  const statusColors = { 'Passed': '#2e7d32', 'Completed': '#2e7d32', 'Needs Attention': '#b3261e' };
  const statusByName = opts.statusByName || {};
  const alertFloorsByName = opts.alertFloorsByName || {};
  const stateByName = opts.stateByName || {};
  const stateColor = { done: '#2e7d32', needs: '#ff1414', overdue: '#ff8c00' };

  MAP_BUILDINGS.forEach(b => {
    const st = stateByName[b.name];
    const alerts = [];
    if (st && (st.red || st.yellow)) {
      if (st.red) alerts.push({ kind: 'red', floors: st.red });
      if (st.yellow) alerts.push({ kind: 'yellow', floors: st.yellow });
    } else if (st) {
      if (st.state === 'overdue') alerts.push({ kind: 'red', floors: st.floors || [] });
      if (st.state === 'needs') alerts.push({ kind: 'yellow', floors: st.floors || [] });
    } else if ((alertFloorsByName[b.name] || []).length) {
      alerts.push({ kind: 'red', floors: alertFloorsByName[b.name] });
    }
    const floors = alerts.flatMap(x => x.floors);
    const doneState = st && (st.done === true || st.state === 'done');
    const color = doneState ? stateColor.done : null;
    const status = statusByName[b.name];
    let bx = b.x, by = b.y;
    if (opts.legend) {
      let near = null, nd = Infinity;
      ICON_POINTS.forEach(([ix, iy]) => { const d = Math.hypot(b.x - ix, b.y - iy); if (d < nd) { nd = d; near = [ix, iy]; } });
      if (near && nd < 70) {
        const dx = nd === 0 ? -1 : (b.x - near[0]) / nd, dy = nd === 0 ? -1 : (b.y - near[1]) / nd;
        bx = near[0] + dx * 72; by = near[1] + dy * 72;
      }
    }
    const group = document.createElementNS(ns, 'g');
    group.style.cursor = 'pointer';
    group.addEventListener('click', () => opts.onSelect && opts.onSelect(b.name));

    const title = document.createElementNS(ns, 'title');
    title.textContent = floors.length
      ? `${b.name} — alert on ${[...new Set(floors)].join(", ")}`
      : `${b.name}${status ? ': ' + status : ''}`;
    group.appendChild(title);

    const make = (tag, attrs, text) => {
      const el = document.createElementNS(ns, tag);
      Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
      if (text !== undefined) el.textContent = text;
      group.appendChild(el);
      return el;
    };

    if (alerts.length) {
      alerts.forEach((al, i) => {
        const cx = alerts.length === 2 ? bx + (i === 0 ? -42 : 42) : bx;
        const yellow = al.kind === 'yellow';
        const blink = al.kind === 'red' && !opts.noBlink;
        const ag = document.createElementNS(ns, 'g');
        if (blink) ag.setAttribute('class', 'fac-blink');
        group.appendChild(ag);
        const tri = document.createElementNS(ns, 'polygon');
        Object.entries({
          points: `${cx},${by - 36} ${cx + 36},${by + 26} ${cx - 36},${by + 26}`,
          fill: yellow ? '#ffc400' : '#ff1414', stroke: '#ffffff', 'stroke-width': 5, 'stroke-linejoin': 'round',
        }).forEach(([k, v]) => tri.setAttribute(k, v));
        ag.appendChild(tri);
        const bang = document.createElementNS(ns, 'text');
        Object.entries({ x: cx, y: by + 17, 'text-anchor': 'middle', 'font-size': 36, 'font-weight': 800, fill: yellow ? '#1a1a1a' : '#ffffff' }).forEach(([k, v]) => bang.setAttribute(k, v));
        bang.textContent = '!';
        ag.appendChild(bang);
        if (al.floors.length && !opts.hideFloorText) {
          make('text', {
            x: bx, y: by + 86 + i * 28, 'text-anchor': 'middle', 'font-size': 24, 'font-weight': 700,
            fill: yellow ? '#a87800' : '#d10000', stroke: '#ffffff', 'stroke-width': 6, 'paint-order': 'stroke',
          }, al.floors.join(', '));
        }
      });
      make('text', {
        x: bx, y: by + 58, 'text-anchor': 'middle', 'font-size': 24, 'font-weight': 700,
        fill: '#383838', stroke: '#ffffff', 'stroke-width': 6, 'paint-order': 'stroke',
      }, b.n);
    } else {
      make('circle', { cx: bx, cy: by, r: 26, fill: color || statusColors[status] || '#8a8a94', stroke: '#ffffff', 'stroke-width': 4 });
      make('text', { x: bx, y: by + 8, 'text-anchor': 'middle', 'font-size': 24, 'font-weight': 700, fill: '#ffffff' }, b.n);
    }

    svg.appendChild(group);
  });
};

function drawBroom(svg, ns, x, y) {
  const bg = document.createElementNS(ns, 'circle');
  bg.setAttribute('cx', x); bg.setAttribute('cy', y); bg.setAttribute('r', 22);
  bg.setAttribute('fill', '#2e7d32');
  bg.setAttribute('stroke', '#ffffff'); bg.setAttribute('stroke-width', 3);
  svg.appendChild(bg);
  const handle = document.createElementNS(ns, 'line');
  handle.setAttribute('x1', x - 7); handle.setAttribute('y1', y - 12);
  handle.setAttribute('x2', x + 3); handle.setAttribute('y2', y + 3);
  handle.setAttribute('stroke', '#ffffff'); handle.setAttribute('stroke-width', 4);
  handle.setAttribute('stroke-linecap', 'round');
  svg.appendChild(handle);
  const head = document.createElementNS(ns, 'polygon');
  head.setAttribute('points', `${x - 1},${y + 2} ${x + 7},${y + 2} ${x + 11},${y + 13} ${x - 5},${y + 13}`);
  head.setAttribute('fill', '#ffffff');
  svg.appendChild(head);
}

function drawCheck(svg, ns, x, y) {
  const bg = document.createElementNS(ns, 'circle');
  bg.setAttribute('cx', x); bg.setAttribute('cy', y); bg.setAttribute('r', 22);
  bg.setAttribute('fill', '#800000');
  bg.setAttribute('stroke', '#ffffff'); bg.setAttribute('stroke-width', 3);
  svg.appendChild(bg);
  const tick = document.createElementNS(ns, 'polyline');
  tick.setAttribute('points', `${x - 9},${y} ${x - 2},${y + 8} ${x + 10},${y - 8}`);
  tick.setAttribute('fill', 'none'); tick.setAttribute('stroke', '#ffffff');
  tick.setAttribute('stroke-width', 4); tick.setAttribute('stroke-linecap', 'round'); tick.setAttribute('stroke-linejoin', 'round');
  svg.appendChild(tick);
}

// Zoom a rendered map to one building (name) or back to the full map (empty name).
window.zoomMapTo = function (svgId, name) {
  const svg = document.getElementById(svgId);
  if (!svg) return;
  const full = [0, 0, 2048, 1365];
  const b = MAP_BUILDINGS.find(x => x.name === name);
  let target = full;
  if (b) {
    const vw = 560, vh = Math.round(560 * 1365 / 2048);
    target = [
      Math.min(Math.max(b.x - vw / 2, 0), 2048 - vw),
      Math.min(Math.max(b.y - vh / 2, 0), 1365 - vh),
      vw, vh,
    ];
  }
  const start = (svg.getAttribute('viewBox') || full.join(' ')).split(' ').map(Number);
  const t0 = performance.now();
  const dur = 350;
  (function step(now) {
    const k = Math.min(1, (now - t0) / dur);
    const e = k < 0.5 ? 2 * k * k : 1 - Math.pow(-2 * k + 2, 2) / 2;
    svg.setAttribute('viewBox', start.map((s, i) => s + (target[i] - s) * e).join(' '));
    if (k < 1) requestAnimationFrame(step);
  })(t0);
};
