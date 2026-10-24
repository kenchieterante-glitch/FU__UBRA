// Code 128 (set B) barcode as inline SVG — readable by any ordinary 1D or 2D wired barcode scanner.
// code128Svg('KEY-8E4BF210', { module: 2, height: 64 }) -> '<svg …>…</svg>'
(function () {
  const P = ['212222','222122','222221','121223','121322','131222','122213','122312','132212','221213','221312','231212','112232','122132','122231','113222','123122','123221','223211','221132','221231','213212','223112','312131','311222','321122','321221','312212','322112','322211','212123','212321','232121','111323','131123','131321','112313','132113','132311','211313','231113','231311','112133','112331','132131','113123','113321','133121','313121','211331','231131','213113','213311','213131','311123','311321','331121','312113','312311','332111','314111','221411','431111','111224','111422','121124','121421','141122','141221','112214','112412','122114','122411','142112','142211','241211','221114','413111','241112','134111','111242','121142','121241','114212','124112','124211','411212','421112','421211','212141','214121','412121','111143','111341','131141','114113','114311','411113','411311','113141','114131','311141','411131','211412','211214','211232','2331112'];
  const START_B = 104, STOP = 106;

  window.code128Svg = function (text, opts) {
    opts = opts || {};
    const m = opts.module || 2, h = opts.height || 64, quiet = 10 * m;
    const codes = [START_B];
    let sum = START_B;
    String(text).split('').forEach((ch, i) => {
      let v = ch.charCodeAt(0) - 32;
      if (v < 0 || v > 94) v = 0; // outside printable ASCII -> space
      codes.push(v);
      sum += v * (i + 1);
    });
    codes.push(sum % 103, STOP);

    let x = quiet, bars = '';
    codes.forEach(c => {
      const pat = P[c];
      for (let i = 0; i < pat.length; i++) {
        const w = Number(pat[i]) * m;
        if (i % 2 === 0) bars += '<rect x="' + x + '" y="0" width="' + w + '" height="' + h + '" fill="#000"/>';
        x += w;
      }
    });
    const total = x + quiet;
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + total + ' ' + h + '" width="' + total + '" height="' + h + '" preserveAspectRatio="none" style="max-width:100%;background:#fff">' +
      '<rect width="' + total + '" height="' + h + '" fill="#fff"/>' + bars + '</svg>';
  };
})();
