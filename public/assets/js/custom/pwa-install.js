(function () {
  const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  const isMobile = window.matchMedia('(max-width: 767px)').matches || /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
  let deferredPrompt = null;

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
  }

  if (isStandalone || !isMobile || localStorage.getItem('pwaInstallDismissed') === '1') {
    return;
  }

  function ensureSheet() {
    if (document.getElementById('pwa-install-sheet')) {
      return document.getElementById('pwa-install-sheet');
    }

    const sheet = document.createElement('div');
    sheet.id = 'pwa-install-sheet';
    sheet.innerHTML = `
      <div class="pwa-install-backdrop"></div>
      <div class="pwa-install-panel" role="dialog" aria-modal="true" aria-label="Install aplikasi">
        <div class="pwa-install-handle"></div>
        <h3>Install Aplikasi</h3>
        <p>Tambahkan aplikasi ke layar utama agar guru lebih cepat membuka scan pelanggaran.</p>
        <div class="pwa-install-actions">
          <button type="button" class="pwa-install-later">Nanti</button>
          <button type="button" class="pwa-install-now">Install Aplikasi</button>
        </div>
      </div>
    `;

    const style = document.createElement('style');
    style.textContent = `
      #pwa-install-sheet{position:fixed;inset:0;z-index:2147483000;display:none}
      #pwa-install-sheet.show{display:block}
      .pwa-install-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.38)}
      .pwa-install-panel{position:absolute;left:0;right:0;bottom:0;background:#fff;border-radius:8px 8px 0 0;padding:18px 18px 20px;box-shadow:0 -18px 40px rgba(15,23,42,.18);font-family:Inter,Arial,sans-serif}
      .pwa-install-handle{width:44px;height:4px;border-radius:999px;background:#cbd5e1;margin:0 auto 14px}
      .pwa-install-panel h3{font-size:18px;line-height:1.25;margin:0 0 8px;color:#111827;font-weight:800}
      .pwa-install-panel p{font-size:14px;line-height:1.5;margin:0 0 16px;color:#64748b}
      .pwa-install-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px}
      .pwa-install-actions button{min-height:46px;border-radius:8px;font-weight:700;border:0}
      .pwa-install-later{background:#f1f5f9;color:#334155}
      .pwa-install-now{background:#0b57d0;color:#fff}
    `;
    document.head.appendChild(style);
    document.body.appendChild(sheet);

    sheet.querySelector('.pwa-install-later').addEventListener('click', () => {
      localStorage.setItem('pwaInstallDismissed', '1');
      sheet.classList.remove('show');
    });

    sheet.querySelector('.pwa-install-now').addEventListener('click', async () => {
      sheet.classList.remove('show');
      if (!deferredPrompt) return;
      deferredPrompt.prompt();
      await deferredPrompt.userChoice;
      deferredPrompt = null;
    });

    return sheet;
  }

  window.addEventListener('beforeinstallprompt', event => {
    event.preventDefault();
    deferredPrompt = event;
    setTimeout(() => ensureSheet().classList.add('show'), 900);
  });
})();
