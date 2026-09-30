<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f7f6f0">
  <title>Admin Vlyhady</title>
  <link rel="icon" type="image/svg+xml" href="../favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <style>
    :root { --paper:#f7f6f0;--ink:#1c2621;--muted:#737a72;--line:#e2e3da;--green:#234c3a;--lime:#d7ed79;--orange:#e36f4c;--white:#fffefa;--sans:"DM Sans",sans-serif;--serif:"Playfair Display",Georgia,serif; }
    * { box-sizing:border-box; }
    body { min-width:320px;margin:0;background:var(--paper);color:var(--ink);font-family:var(--sans);font-size:13px;-webkit-font-smoothing:antialiased; }
    button,input,select,textarea { font:inherit; }
    button,a,input,select,textarea { outline-color:var(--orange);outline-offset:2px; }
    button { cursor:pointer; }
    a { color:inherit;text-decoration:none; }
    .topbar { height:66px;padding:0 5%;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--line);background:rgba(247,246,240,.96); }
    .brand { font-size:17px;font-weight:700;letter-spacing:.13em; }
    .brand span { color:var(--orange); }
    .topbar-actions { display:flex;align-items:center;gap:18px;color:var(--muted);font-size:11px; }
    .topbar-actions a:hover { color:var(--green); }
    main { max-width:1260px;margin:0 auto;padding:40px 5% 72px; }
    .eyebrow { color:var(--orange);font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase; }
    h1,h2,h3,p { margin-top:0; }
    h1 { margin:7px 0 9px;font:500 34px/1.12 var(--serif); }
    h2 { margin:0;font:500 25px/1.2 var(--serif); }
    .muted { color:var(--muted);font-size:11px;line-height:1.7; }
    .panel { border-top:1px solid var(--line);padding-top:24px;margin-top:27px; }
    .button { min-height:39px;padding:0 15px;border:1px solid var(--green);background:var(--green);color:#fff;font-size:10px;font-weight:700;letter-spacing:.03em; }
    .button:hover { background:#19392b; }
    .button.secondary { background:transparent;color:var(--green); }
    .button.secondary:hover { background:#edf0e7; }
    .button.danger { border-color:#b94f3c;background:#b94f3c; }
    .button.small { min-height:31px;padding:0 10px;font-size:9px; }
    .button:disabled { opacity:.55;cursor:wait; }
    .auth-wrap { width:min(470px,100%);margin:8vh auto 0;padding:30px;background:var(--white);border:1px solid var(--line); }
    .auth-wrap p { margin:10px 0 22px; }
    .form-grid { display:grid;grid-template-columns:1fr 1fr;gap:13px; }
    .field { display:flex;flex-direction:column;gap:6px;min-width:0; }
    .field.full { grid-column:1/-1; }
    .field label { color:#566056;font-size:9px;font-weight:700;letter-spacing:.07em;text-transform:uppercase; }
    .field input,.field select,.field textarea { width:100%;min-height:40px;padding:9px 10px;border:1px solid var(--line);background:#fff;color:var(--ink);font-size:11px; }
    .field textarea { min-height:82px;resize:vertical; }
    .auth-wrap .button { width:100%;margin-top:17px; }
    .inline-error { min-height:16px;margin:12px 0 0;color:#aa4436;font-size:10px;line-height:1.6; }
    .notice { padding:11px 13px;border-left:3px solid var(--orange);background:#f1eee4;color:#596057;font-size:10px;line-height:1.7; }
    .dashboard-head { display:flex;justify-content:space-between;align-items:flex-end;gap:20px; }
    .dashboard-head .muted { max-width:520px;margin:0; }
    .stats { margin-top:25px;display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--line);border-bottom:1px solid var(--line); }
    .stat { padding:17px 20px 17px 0;border-right:1px solid var(--line); }
    .stat:not(:first-child) { padding-left:20px; }
    .stat:last-child { border-right:0; }
    .stat-label { color:var(--muted);font-size:9px;letter-spacing:.1em;text-transform:uppercase; }
    .stat-value { margin-top:7px;font:500 27px var(--serif); }
    .tabs { display:flex;gap:8px;overflow:auto;border-bottom:1px solid var(--line); }
    .tab { min-height:40px;padding:0 13px;border:0;border-bottom:2px solid transparent;background:none;color:var(--muted);font-size:10px;white-space:nowrap; }
    .tab.active { border-color:var(--green);color:var(--green);font-weight:700; }
    .view-head { display:flex;justify-content:space-between;align-items:center;gap:15px;margin:20px 0 14px; }
    .view-head h2 { font-size:23px; }
    .table-wrap { overflow-x:auto;border-top:1px solid var(--line); }
    .pos-layout { display:grid;grid-template-columns:minmax(0,1fr) 350px;gap:22px;align-items:start; }
    .pos-search { margin:0 0 12px; }
    .pos-search input { width:100%;min-height:39px;padding:9px 11px;border:1px solid var(--line);background:#fff;font-size:11px; }
    .pos-products { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px; }
    .pos-product { min-width:0;padding:13px;border:1px solid var(--line);background:var(--white); }
    .pos-product h3 { margin:0 0 6px;font:500 16px/1.2 var(--serif); }
    .pos-product-meta { display:flex;justify-content:space-between;gap:8px;margin-bottom:11px;color:var(--muted);font-size:9px; }
    .pos-product .button { width:100%; }
    .pos-checkout { position:sticky;top:16px;padding:18px;border:1px solid var(--line);background:var(--white); }
    .pos-checkout h3 { margin:0 0 13px;font:500 20px var(--serif); }
    .pos-cart-list { max-height:270px;overflow:auto;border-top:1px solid var(--line); }
    .pos-cart-line { display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px;padding:11px 0;border-bottom:1px solid var(--line); }
    .pos-cart-line strong { display:block;font-size:10px; }
    .pos-cart-line .cell-note { margin-top:3px; }
    .pos-qty { display:flex;align-items:center;gap:5px; }
    .pos-qty button { width:27px;height:27px;border:1px solid var(--line);background:transparent; }
    .pos-qty span { min-width:19px;text-align:center;font-size:10px; }
    .pos-total { display:flex;justify-content:space-between;gap:12px;margin:14px 0;padding:13px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line);font-weight:700; }
    .pos-change { margin:9px 0 0;color:var(--green);font-size:10px;font-weight:700; }
    .pos-confirm { display:flex;align-items:flex-start;gap:8px;margin-top:11px;color:#586157;font-size:9px;line-height:1.55; }
    .pos-confirm input { margin:2px 0 0;accent-color:var(--green); }
    .pos-message { min-height:18px;margin:8px 0;color:#a84334;font-size:10px;line-height:1.5; }
    .pos-result { margin-top:12px;padding:12px;border-left:3px solid var(--green);background:#edf0e7;font-size:10px;line-height:1.7; }
    .pos-result strong { display:block;font:500 17px var(--serif); }
    table { width:100%;border-collapse:collapse;text-align:left; }
    th { padding:11px 10px;color:var(--muted);font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap; }
    td { padding:12px 10px;border-top:1px solid var(--line);font-size:10px;vertical-align:top; }
    td strong { font-size:11px; }
    .cell-note { display:block;margin-top:4px;color:var(--muted);font-size:9px;line-height:1.55; }
    .status { display:inline-block;padding:5px 7px;background:#eceee5;color:#445244;font-size:9px;white-space:nowrap; }
    .status.demo { background:#f8e8d7;color:#815327; }
    .status.active { background:#e3ecd6;color:#385638; }
    .status.paid { background:#e3ecd6;color:#385638; }
    .status.pending { background:#f8e8d7;color:#815327; }
    .status.failed,.status.expired { background:#f0e4df;color:#855244; }
    .status.unsubscribed { background:#f0e4df;color:#855244; }
    .row-actions { display:flex;gap:6px;flex-wrap:wrap; }
    .row-actions select { max-width:148px;min-height:31px;padding:0 7px;border:1px solid var(--line);background:#fff;font-size:9px; }
    .empty-row { padding:35px 12px;color:var(--muted);text-align:center;font-size:11px; }
    .dialog { width:min(700px,calc(100% - 24px));max-height:calc(100dvh - 24px);padding:0;border:0;background:var(--paper);box-shadow:0 24px 70px rgba(28,38,33,.22); }
    .dialog::backdrop { background:rgba(18,26,21,.55); }
    .dialog-inner { padding:25px;overflow:auto;max-height:calc(100dvh - 24px); }
    .dialog-head { display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:20px; }
    .dialog-head h2 { font-size:24px; }
    .dialog-close { width:34px;height:34px;border:1px solid var(--line);background:transparent;font-size:18px; }
    .checkbox-field { grid-column:1/-1;display:flex;align-items:flex-start;gap:9px;color:#505a50;font-size:10px;line-height:1.55; }
    .checkbox-field input { margin:1px 0 0;accent-color:var(--green); }
    .package-inputs { width:100%;min-width:0;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:5px; }
    .package-inputs input { min-width:0;padding-right:5px;padding-left:5px; }
    .dialog-actions { display:flex;justify-content:flex-end;gap:8px;margin-top:20px; }
    .toast { position:fixed;right:18px;bottom:18px;z-index:10;max-width:min(390px,calc(100vw - 36px));padding:13px 16px;background:var(--green);color:#fff;box-shadow:0 10px 30px rgba(0,0,0,.13);font-size:10px;opacity:0;transform:translateY(8px);pointer-events:none;transition:opacity .2s,transform .2s; }
    .toast.show { opacity:1;transform:translateY(0); }
    @media (max-width:680px) {
      .topbar { height:59px;padding:0 4%; }
      .topbar-actions { gap:11px;font-size:9px; }
      main { padding:28px 4% 55px; }
      h1 { font-size:30px; }
      .dashboard-head { align-items:flex-start;flex-direction:column; }
      .stats { grid-template-columns:repeat(3,1fr); }
      .stat { padding:14px 8px 14px 0; }
      .stat:not(:first-child) { padding-left:8px; }
      .stat-value { font-size:23px; }
      .view-head { align-items:flex-start; }
      .view-head h2 { font-size:21px; }
      .form-grid { grid-template-columns:1fr; }
      .field.full,.checkbox-field { grid-column:auto; }
      .auth-wrap { margin-top:5vh;padding:22px 18px; }
      .dialog-inner { padding:21px 16px; }
      .dialog-head h2 { font-size:21px; }
      .pos-layout { grid-template-columns:1fr; }
      .pos-checkout { position:static; }
      .pos-products { grid-template-columns:1fr 1fr; }
    }
    @media (prefers-reduced-motion:reduce) { *,*::before,*::after { transition:none!important; } }
  </style>
</head>
<body>
  <header class="topbar"><a class="brand" href="../">Vlyhady<span>.</span> / ADMIN</a><nav class="topbar-actions"><a href="../">Lihat toko</a><button class="button secondary small" id="logoutButton" hidden>Keluar</button></nav></header>
  <main id="app"><p class="muted">Memeriksa sesi admin...</p></main>
  <dialog class="dialog" id="productDialog"><div class="dialog-inner"><div class="dialog-head"><div><div class="eyebrow">Katalog</div><h2 id="productDialogTitle">Tambah produk</h2></div><button class="dialog-close" type="button" data-close-dialog aria-label="Tutup">×</button></div><form id="productForm"><input type="hidden" name="id"><div class="form-grid"><div class="field"><label for="productName">Nama produk</label><input id="productName" name="name" maxlength="160" required></div><div class="field"><label for="productCategory">Kategori</label><select id="productCategory" name="category" required><option>Rumah</option><option>Dapur</option><option>Ritual</option><option>Aksesori</option></select></div><div class="field"><label for="productPrice">Harga (rupiah)</label><input id="productPrice" name="price" type="number" min="1" step="1" required></div><div class="field"><label for="productStock">Stok</label><input id="productStock" name="stock" type="number" min="0" step="1" required></div><div class="field"><label for="productWeight">Berat paket (gram)</label><input id="productWeight" name="weight_grams" type="number" min="0" step="1" required></div><div class="field"><label>Ukuran paket (cm)</label><div class="package-inputs"><input aria-label="Panjang paket, sentimeter" name="length_cm" type="number" min="0" step="1" placeholder="Panjang" required><input aria-label="Lebar paket, sentimeter" name="width_cm" type="number" min="0" step="1" placeholder="Lebar" required><input aria-label="Tinggi paket, sentimeter" name="height_cm" type="number" min="0" step="1" placeholder="Tinggi" required></div></div><div class="field full"><label for="productImage">URL gambar HTTPS atau ID Unsplash</label><input id="productImage" name="image" maxlength="512" placeholder="https://... atau photo-... "></div><div class="field"><label for="productBadge">Label singkat</label><input id="productBadge" name="badge" maxlength="60" placeholder="Contoh: Buatan lokal"></div><div class="field"><label for="productAlt">Deskripsi gambar</label><input id="productAlt" name="alt" maxlength="240" required></div><div class="field full"><label for="productDescription">Deskripsi</label><textarea id="productDescription" name="description" maxlength="4000" required></textarea></div><div class="field"><label for="productMaterial">Bahan</label><input id="productMaterial" name="material" maxlength="180"></div><div class="field"><label for="productDimensions">Ukuran / isi benda</label><input id="productDimensions" name="dimensions" maxlength="120"></div><label class="checkbox-field"><input name="is_demo" type="checkbox"><span>Produk contoh. Pesanan dari produk ini akan berstatus DEMO dan stok tidak dikurangi.</span></label><label class="checkbox-field"><input name="is_active" type="checkbox" checked><span>Tampilkan produk di toko.</span></label></div><p class="inline-error" id="productFormError" aria-live="polite"></p><div class="dialog-actions"><button class="button secondary" type="button" data-close-dialog>Batal</button><button class="button" type="submit">Simpan produk</button></div></form></div></dialog>
  <div class="toast" id="toast" role="status" aria-live="polite"></div>
  <script>
    const app = document.getElementById('app');
    const productDialog = document.getElementById('productDialog');
    const productForm = document.getElementById('productForm');
    let csrfToken = '';
    let products = [];
    let currentView = 'products';
    let posCart = [];
    let posPaymentMethod = 'cash';
    let posAmountReceived = 0;
    let toastTimer;

    const escapeHTML = value => String(value ?? '').replace(/[&<>"']/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[character]);
    const formatPrice = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value));
    const formatDate = value => new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value.replace(' ', 'T') + 'Z'));

    async function api(action, options = {}) {
      const response = await fetch(`../api/admin.php?action=${encodeURIComponent(action)}`, {
        method: options.method || 'GET',
        headers: options.method === 'POST' ? { 'Content-Type': 'application/json', Accept: 'application/json' } : { Accept: 'application/json' },
        body: options.method === 'POST' ? JSON.stringify({ ...options.body, csrf: csrfToken }) : undefined,
        credentials: 'same-origin'
      });
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'Permintaan gagal.');
      if (result.csrf) csrfToken = result.csrf;
      return result;
    }

    function showToast(message) {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.classList.add('show');
      clearTimeout(toastTimer);
      toastTimer = setTimeout(() => toast.classList.remove('show'), 2800);
    }

    function renderAuth(isSetup) {
      document.getElementById('logoutButton').hidden = true;
      app.innerHTML = `<section class="auth-wrap"><div class="eyebrow">Vlyhady · Area privat</div><h1>${isSetup ? 'Buat akun admin' : 'Masuk ke admin'}</h1><p class="muted">${isSetup ? 'Akun pertama hanya dapat dibuat dari komputer ini. Gunakan kata sandi unik minimal 12 karakter.' : 'Masuk untuk mengelola katalog, melihat pesanan, dan mengatur newsletter.'}</p><form id="authForm"><div class="form-grid"><div class="field full"><label for="authUsername">Nama admin</label><input id="authUsername" name="username" autocomplete="username" minlength="3" maxlength="50" required></div><div class="field full"><label for="authPassword">Kata sandi${isSetup ? ' (minimal 12 karakter)' : ''}</label><input id="authPassword" name="password" type="password" autocomplete="${isSetup ? 'new-password' : 'current-password'}" minlength="${isSetup ? '12' : '1'}" maxlength="200" required></div>${isSetup ? '<div class="field full"><label for="authPasswordConfirm">Ulangi kata sandi</label><input id="authPasswordConfirm" name="passwordConfirmation" type="password" autocomplete="new-password" minlength="12" maxlength="200" required></div>' : ''}</div><p class="inline-error" id="authError" aria-live="polite"></p><button class="button" type="submit">${isSetup ? 'Buat akun dan masuk' : 'Masuk'}</button></form>${isSetup ? '<p class="muted" style="margin:15px 0 0">Kata sandi tidak dikirim melalui chat dan disimpan sebagai hash di database.</p>' : ''}</section>`;
      document.getElementById('authForm').addEventListener('submit', event => handleAuth(event, isSetup));
    }

    async function handleAuth(event, isSetup) {
      event.preventDefault();
      const form = event.currentTarget;
      const submit = form.querySelector('button[type=submit]');
      const data = Object.fromEntries(new FormData(form));
      const error = document.getElementById('authError');
      submit.disabled = true;
      try {
        await api(isSetup ? 'setup' : 'login', { method: 'POST', body: data });
        await loadDashboard();
      } catch (reason) {
        error.textContent = reason.message;
      } finally {
        submit.disabled = false;
      }
    }

    function dashboardShell() {
      document.getElementById('logoutButton').hidden = false;
      document.getElementById('logoutButton').onclick = async () => {
        try { await api('logout', { method: 'POST' }); location.reload(); }
        catch (error) { showToast(error.message); }
      };
      app.innerHTML = `<div class="dashboard-head"><div><div class="eyebrow">Ruang kerja</div><h1>Selamat datang, ${escapeHTML(window.adminUsername)}</h1><p class="muted">Kelola katalog, transaksi kasir, pesanan, dan daftar kabar.</p></div><div class="notice">Simpan kata sandi dengan aman. Data pesanan berisi informasi pribadi, jangan dibagikan.</div></div><div class="stats"><div class="stat"><div class="stat-label">Produk aktif</div><div class="stat-value" id="statProducts">—</div></div><div class="stat"><div class="stat-label">Pesanan tercatat</div><div class="stat-value" id="statOrders">—</div></div><div class="stat"><div class="stat-label">Pelanggan newsletter</div><div class="stat-value" id="statSubscribers">—</div></div></div><section class="panel"><nav class="tabs" aria-label="Bagian admin"><button class="tab" data-view="products">Produk</button><button class="tab" data-view="pos">Kasir</button><button class="tab" data-view="orders">Pesanan</button><button class="tab" data-view="newsletter">Newsletter</button></nav><div id="viewContent"></div></section>`;
      app.querySelectorAll('[data-view]').forEach(button => button.addEventListener('click', () => loadView(button.dataset.view)));
    }

    async function loadDashboard() {
      const status = await api('status');
      if (!status.authenticated) { renderAuth(status.setupRequired); return; }
      window.adminUsername = status.username;
      dashboardShell();
      const [productResult, orderResult, subscriberResult] = await Promise.all([api('products'), api('orders'), api('newsletter')]);
      document.getElementById('statProducts').textContent = productResult.data.filter(item => Number(item.is_active) === 1).length;
      document.getElementById('statOrders').textContent = orderResult.data.length;
      document.getElementById('statSubscribers').textContent = subscriberResult.data.filter(item => item.status === 'active').length;
      await loadView(currentView);
    }

    function setActiveTab(view) {
      currentView = view;
      app.querySelectorAll('[data-view]').forEach(button => button.classList.toggle('active', button.dataset.view === view));
    }

    async function loadView(view) {
      setActiveTab(view);
      const content = document.getElementById('viewContent');
      if (!content) return;
      content.innerHTML = '<p class="muted" style="padding:18px 0">Memuat data...</p>';
      try {
        const result = await api(view === 'pos' ? 'products' : view);
        if (view === 'products') { products = result.data; renderProductsView(content); }
        if (view === 'pos') { products = result.data; renderPosView(content); }
        if (view === 'orders') renderOrdersView(content, result.data);
        if (view === 'newsletter') renderNewsletterView(content, result.data);
      } catch (error) {
        content.innerHTML = `<p class="inline-error">${escapeHTML(error.message)}</p>`;
        if (error.message.includes('masuk')) renderAuth(false);
      }
    }

    function renderProductsView(content) {
      content.innerHTML = `<div class="view-head"><div><h2>Katalog produk</h2><span class="cell-note">Produk bertanda DEMO tidak akan mengurangi stok saat checkout.</span></div><button class="button" id="newProduct">Tambah produk</button></div><div class="table-wrap"><table><thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead><tbody>${products.length ? products.map(product => `<tr><td><strong>${escapeHTML(product.name)}</strong><span class="cell-note">${escapeHTML(product.badge || product.description.slice(0, 90))}</span></td><td>${escapeHTML(product.category)}</td><td>${formatPrice(product.price)}</td><td>${product.stock}</td><td>${Number(product.is_demo) ? '<span class="status demo">DEMO</span>' : '<span class="status active">Asli</span>'} ${Number(product.is_active) ? '' : '<span class="status">Nonaktif</span>'}</td><td><div class="row-actions"><button class="button secondary small" data-edit-product="${product.id}">Ubah</button>${Number(product.is_active) ? `<button class="button danger small" data-archive-product="${product.id}">Nonaktifkan</button>` : ''}</div></td></tr>`).join('') : '<tr><td class="empty-row" colspan="6">Belum ada produk.</td></tr>'}</tbody></table></div>`;
      content.querySelector('#newProduct').addEventListener('click', () => openProductForm());
      content.querySelectorAll('[data-edit-product]').forEach(button => button.addEventListener('click', () => openProductForm(Number(button.dataset.editProduct))));
      content.querySelectorAll('[data-archive-product]').forEach(button => button.addEventListener('click', () => archiveProduct(Number(button.dataset.archiveProduct))));
    }

    function renderPosView(content) {
      posCart = [];
      posPaymentMethod = 'cash';
      posAmountReceived = 0;
      content.innerHTML = `<div class="view-head"><div><h2>Kasir langsung</h2><span class="cell-note">Hanya produk aktif dan noncontoh yang dapat dijual. Stok dikurangi saat transaksi disimpan.</span></div></div><div class="pos-layout"><section><label class="pos-search"><input id="posSearch" type="search" placeholder="Cari produk untuk dijual" aria-label="Cari produk kasir"></label><div class="pos-products" id="posProducts"></div></section><section class="pos-checkout"><h3>Transaksi</h3><div class="pos-cart-list" id="posCartItems"></div><div class="pos-total"><span>Total</span><span id="posTotal">Rp 0</span></div><div class="field"><label for="posPayment">Metode pembayaran</label><select id="posPayment"><option value="cash">Tunai</option><option value="qris_manual">QRIS (sudah diterima)</option><option value="card_manual">Kartu / non-tunai (sudah diterima)</option></select></div><div class="field" id="posCashField" style="margin-top:11px"><label for="posAmountReceived">Uang diterima (rupiah)</label><input id="posAmountReceived" type="number" min="0" step="1" inputmode="numeric" placeholder="Masukkan jumlah uang" value="0"></div><label class="pos-confirm" id="posPaymentConfirm" hidden><input id="posPaymentConfirmed" type="checkbox"><span>Saya sudah menerima dan mengonfirmasi pembayaran non-tunai pelanggan.</span></label><div class="pos-change" id="posChange"></div><p class="pos-message" id="posMessage" aria-live="polite"></p><button class="button" id="completePosSale" type="button" disabled>Simpan penjualan</button><div id="posResult" aria-live="polite"></div></section></div>`;
      renderPosProducts(content);
      renderPosCart(content);
      content.querySelector('#posSearch').addEventListener('input', () => renderPosProducts(content));
      content.querySelector('#posPayment').addEventListener('change', event => {
        posPaymentMethod = event.currentTarget.value;
        const isCash = posPaymentMethod === 'cash';
        content.querySelector('#posCashField').hidden = !isCash;
        content.querySelector('#posPaymentConfirm').hidden = isCash;
        renderPosCart(content);
      });
      content.querySelector('#posAmountReceived').addEventListener('input', event => {
        posAmountReceived = Number(event.currentTarget.value) || 0;
        updatePosSummary(content);
      });
      content.querySelector('#posPaymentConfirmed').addEventListener('change', () => updatePosSummary(content));
      content.addEventListener('click', event => {
        const add = event.target.closest('[data-pos-add]');
        const change = event.target.closest('[data-pos-change]');
        if (add) updatePosQuantity(Number(add.dataset.posAdd), 1, content);
        if (change) updatePosQuantity(Number(change.dataset.posChange), Number(change.dataset.delta), content);
        if (event.target.closest('#completePosSale')) submitPosSale(content);
      });
    }

    function renderPosProducts(content) {
      const target = content.querySelector('#posProducts');
      if (!target) return;
      const query = content.querySelector('#posSearch').value.trim().toLocaleLowerCase('id');
      const sellable = products.filter(product => Number(product.is_active) === 1 && Number(product.is_demo) === 0 && Number(product.stock) > 0 && `${product.name} ${product.category}`.toLocaleLowerCase('id').includes(query));
      if (!sellable.length) {
        target.innerHTML = `<div class="empty-row" style="grid-column:1/-1">${products.some(product => Number(product.is_active) && !Number(product.is_demo)) ? 'Produk tidak ditemukan atau stok habis.' : 'Belum ada produk asli yang aktif. Ubah produk contoh dan isi data stok di tab Produk.'}</div>`;
        return;
      }
      target.innerHTML = sellable.map(product => `<article class="pos-product"><h3>${escapeHTML(product.name)}</h3><div class="pos-product-meta"><span>${formatPrice(product.price)}</span><span>Stok ${product.stock}</span></div><button class="button secondary small" type="button" data-pos-add="${product.id}">Tambah</button></article>`).join('');
    }

    function updatePosQuantity(id, delta, content) {
      const product = products.find(item => Number(item.id) === id);
      if (!product) return;
      const line = posCart.find(item => item.id === id);
      const nextQuantity = (line?.quantity || 0) + delta;
      if (nextQuantity > Number(product.stock)) {
        content.querySelector('#posMessage').textContent = `Stok ${product.name} tidak mencukupi.`;
        return;
      }
      if (nextQuantity <= 0) posCart = posCart.filter(item => item.id !== id);
      else if (line) line.quantity = nextQuantity;
      else posCart.push({ id, quantity: nextQuantity });
      content.querySelector('#posMessage').textContent = '';
      renderPosProducts(content);
      renderPosCart(content);
    }

    function posTotal() {
      return posCart.reduce((total, line) => total + Number(products.find(product => Number(product.id) === line.id)?.price || 0) * line.quantity, 0);
    }

    function renderPosCart(content) {
      const list = content.querySelector('#posCartItems');
      if (!list) return;
      list.innerHTML = posCart.length ? posCart.map(line => {
        const product = products.find(item => Number(item.id) === line.id);
        return `<div class="pos-cart-line"><div><strong>${escapeHTML(product.name)}</strong><span class="cell-note">${formatPrice(product.price)} · ${formatPrice(product.price * line.quantity)}</span></div><div class="pos-qty"><button type="button" data-pos-change="${line.id}" data-delta="-1" aria-label="Kurangi ${escapeHTML(product.name)}">−</button><span>${line.quantity}</span><button type="button" data-pos-change="${line.id}" data-delta="1" aria-label="Tambah ${escapeHTML(product.name)}" ${line.quantity >= Number(product.stock) ? 'disabled' : ''}>+</button></div></div>`;
      }).join('') : '<div class="empty-row">Pilih produk untuk memulai transaksi.</div>';
      updatePosSummary(content);
    }

    function updatePosSummary(content) {
      const total = posTotal();
      const isCash = posPaymentMethod === 'cash';
      const amountField = content.querySelector('#posAmountReceived');
      if (isCash && amountField && document.activeElement !== amountField) {
        amountField.value = posAmountReceived ? String(posAmountReceived) : '';
        amountField.placeholder = formatPrice(total);
      }
      const change = isCash ? posAmountReceived - total : 0;
      content.querySelector('#posTotal').textContent = formatPrice(total);
      content.querySelector('#posChange').textContent = isCash && posAmountReceived >= total && total > 0 ? `Kembalian ${formatPrice(change)}` : '';
      const paymentConfirmed = isCash || content.querySelector('#posPaymentConfirmed').checked;
      content.querySelector('#completePosSale').disabled = posCart.length === 0 || (isCash ? posAmountReceived < total : !paymentConfirmed);
    }

    async function submitPosSale(content) {
      const button = content.querySelector('#completePosSale');
      const message = content.querySelector('#posMessage');
      const confirmed = posPaymentMethod === 'cash' || content.querySelector('#posPaymentConfirmed').checked;
      if (!posCart.length || !confirmed) return;
      button.disabled = true;
      button.textContent = 'Menyimpan penjualan...';
      message.textContent = '';
      try {
        const result = await api('pos-sale', {
          method: 'POST',
          body: {
            items: posCart,
            payment_method: posPaymentMethod,
            amount_received: posAmountReceived,
            payment_confirmed: posPaymentMethod === 'cash' || content.querySelector('#posPaymentConfirmed').checked,
          }
        });
        const methodName = posPaymentMethod === 'cash' ? 'Tunai' : posPaymentMethod === 'qris_manual' ? 'QRIS' : 'Non-tunai';
        content.querySelector('#posResult').innerHTML = `<div class="pos-result"><strong>Penjualan tersimpan</strong>No. ${escapeHTML(result.orderCode)}<br>${methodName} · ${formatPrice(result.subtotal)}${posPaymentMethod === 'cash' ? `<br>Diterima ${formatPrice(result.amountReceived)} · kembalian ${formatPrice(result.changeAmount)}` : ''}</div>`;
        posCart = [];
        posAmountReceived = 0;
        await loadDashboard();
        showToast(`Penjualan ${result.orderCode} tersimpan · kembalian ${formatPrice(result.changeAmount)}.`);
      } catch (error) {
        message.textContent = error.message;
        button.disabled = false;
        button.textContent = 'Simpan penjualan';
      }
    }

    function openProductForm(id = null) {
      const product = products.find(item => Number(item.id) === id);
      productForm.reset();
      productForm.elements.id.value = product?.id ?? '';
      productForm.elements.name.value = product?.name ?? '';
      productForm.elements.category.value = product?.category ?? 'Rumah';
      productForm.elements.price.value = product?.price ?? '';
      productForm.elements.stock.value = product?.stock ?? '';
      productForm.elements.image.value = product?.image ?? '';
      productForm.elements.badge.value = product?.badge ?? '';
      productForm.elements.alt.value = product?.alt ?? product?.name ?? '';
      productForm.elements.description.value = product?.description ?? '';
      productForm.elements.material.value = product?.material ?? '';
      productForm.elements.dimensions.value = product?.dimensions ?? '';
      productForm.elements.weight_grams.value = product?.weight_grams ?? 0;
      productForm.elements.length_cm.value = product?.length_cm ?? 0;
      productForm.elements.width_cm.value = product?.width_cm ?? 0;
      productForm.elements.height_cm.value = product?.height_cm ?? 0;
      productForm.elements.is_demo.checked = product ? Boolean(Number(product.is_demo)) : false;
      productForm.elements.is_active.checked = product ? Boolean(Number(product.is_active)) : true;
      document.getElementById('productDialogTitle').textContent = product ? 'Ubah produk' : 'Tambah produk';
      document.getElementById('productFormError').textContent = '';
      productDialog.showModal();
      productForm.elements.name.focus();
    }

    async function saveProduct(event) {
      event.preventDefault();
      const form = event.currentTarget;
      const button = form.querySelector('button[type=submit]');
      const data = Object.fromEntries(new FormData(form));
      data.id = data.id ? Number(data.id) : null;
      data.price = Number(data.price);
      data.stock = Number(data.stock);
      data.weight_grams = Number(data.weight_grams);
      data.length_cm = Number(data.length_cm);
      data.width_cm = Number(data.width_cm);
      data.height_cm = Number(data.height_cm);
      data.is_demo = form.elements.is_demo.checked;
      data.is_active = form.elements.is_active.checked;
      button.disabled = true;
      try {
        await api('product-save', { method: 'POST', body: data });
        productDialog.close();
        showToast('Produk tersimpan di MySQL.');
        await loadDashboard();
      } catch (error) {
        document.getElementById('productFormError').textContent = error.message;
      } finally {
        button.disabled = false;
      }
    }

    async function archiveProduct(id) {
      if (!confirm('Nonaktifkan produk ini dari toko? Riwayat pesanan tetap disimpan.')) return;
      try {
        await api('product-archive', { method: 'POST', body: { id } });
        showToast('Produk dinonaktifkan.');
        await loadDashboard();
      } catch (error) { showToast(error.message); }
    }

    function renderOrdersView(content, orders) {
      const statuses = ['menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];
      const paymentLabels = { manual:'Manual', pending:'Menunggu bayar', paid:'Lunas', failed:'Gagal', expired:'Kedaluwarsa' };
      content.innerHTML = `<div class="view-head"><div><h2>Pesanan</h2><span class="cell-note">Menampilkan paling banyak 100 pesanan terbaru.</span></div><button class="button secondary small" id="refreshOrders">Muat ulang</button></div><div class="table-wrap"><table><thead><tr><th>Pesanan / waktu</th><th>Pelanggan</th><th>Rincian</th><th>Total</th><th>Pembayaran</th><th>Status pesanan</th><th>Perbarui</th></tr></thead><tbody>${orders.length ? orders.map(order => `<tr><td><strong>${escapeHTML(order.order_code)}</strong><span class="cell-note">${formatDate(order.created_at)}</span></td><td><strong>${escapeHTML(order.customer_name)}</strong><span class="cell-note">${escapeHTML(order.customer_phone)} · ${escapeHTML(order.customer_email)}</span><span class="cell-note">${escapeHTML(order.shipping_address)}, ${escapeHTML(order.city)} ${escapeHTML(order.postal_code)}</span></td><td>${order.items.map(item => `${escapeHTML(item.product_name)} × ${item.quantity}`).join('<br>')}<span class="cell-note">${escapeHTML(order.payment_method)}</span>${order.courier_code ? `<span class="cell-note">${escapeHTML(order.courier_code)} ${escapeHTML(order.courier_service)}</span>` : ''}</td><td><strong>${formatPrice(order.total_amount || order.subtotal)}</strong><span class="cell-note">Produk ${formatPrice(order.subtotal)} · ongkir ${order.shipping_fee === null ? 'belum ditentukan' : formatPrice(order.shipping_fee)}</span></td><td><span class="status ${escapeHTML(order.payment_status)}">${escapeHTML(paymentLabels[order.payment_status] || order.payment_status)}</span></td><td><span class="status ${order.status === 'demo' ? 'demo' : ''}">${escapeHTML(order.status.replaceAll('_', ' '))}</span></td><td>${order.status === 'demo' ? '<span class="cell-note">Data contoh</span>' : `<div class="row-actions"><select data-order-status="${order.id}" aria-label="Status pesanan ${escapeHTML(order.order_code)}">${statuses.map(status => `<option value="${status}" ${order.status === status ? 'selected' : ''}>${status.replaceAll('_', ' ')}</option>`).join('')}</select><button class="button secondary small" data-save-order="${order.id}">Simpan</button></div>`}</td></tr>`).join('') : '<tr><td class="empty-row" colspan="7">Belum ada pesanan.</td></tr>'}</tbody></table></div>`;
      orders.forEach((order, index) => {
        if (order.sales_channel !== 'pos') return;
        const row = content.querySelectorAll('tbody tr')[index];
        if (!row) return;
        const channel = document.createElement('span');
        channel.className = 'cell-note';
        channel.textContent = 'Penjualan kasir';
        row.cells[0].append(channel);
        const tender = document.createElement('span');
        tender.className = 'cell-note';
        tender.textContent = `Diterima ${formatPrice(order.amount_received)} · kembalian ${formatPrice(order.change_amount)}`;
        row.cells[4].append(tender);
      });
      content.querySelector('#refreshOrders').addEventListener('click', () => loadView('orders'));
      content.querySelectorAll('[data-save-order]').forEach(button => button.addEventListener('click', () => updateOrderStatus(Number(button.dataset.saveOrder))));
    }

    async function updateOrderStatus(id) {
      const status = document.querySelector(`[data-order-status="${id}"]`).value;
      try {
        await api('order-status', { method: 'POST', body: { id, status } });
        showToast('Status pesanan diperbarui.');
        await loadView('orders');
      } catch (error) { showToast(error.message); }
    }

    function renderNewsletterView(content, subscribers) {
      content.innerHTML = `<div class="view-head"><div><h2>Daftar newsletter</h2><span class="cell-note">Alamat email tersimpan di database; pengiriman kampanye belum terhubung.</span></div><button class="button secondary small" id="refreshSubscribers">Muat ulang</button></div><div class="table-wrap"><table><thead><tr><th>Email</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody>${subscribers.length ? subscribers.map(person => `<tr><td>${escapeHTML(person.email)}</td><td><span class="status ${person.status}">${escapeHTML(person.status)}</span></td><td>${formatDate(person.subscribed_at)}</td><td>${person.status === 'active' ? `<button class="button secondary small" data-unsubscribe="${person.id}">Berhenti langganan</button>` : '—'}</td></tr>`).join('') : '<tr><td class="empty-row" colspan="4">Belum ada pelanggan newsletter.</td></tr>'}</tbody></table></div>`;
      content.querySelector('#refreshSubscribers').addEventListener('click', () => loadView('newsletter'));
      content.querySelectorAll('[data-unsubscribe]').forEach(button => button.addEventListener('click', () => unsubscribe(Number(button.dataset.unsubscribe))));
    }

    async function unsubscribe(id) {
      try {
        await api('newsletter-unsubscribe', { method: 'POST', body: { id } });
        showToast('Status langganan diperbarui.');
        await loadDashboard();
        currentView = 'newsletter';
        await loadView('newsletter');
      } catch (error) { showToast(error.message); }
    }

    productForm.addEventListener('submit', saveProduct);
    document.querySelectorAll('[data-close-dialog]').forEach(button => button.addEventListener('click', () => productDialog.close()));
    productDialog.addEventListener('click', event => { if (event.target === productDialog) productDialog.close(); });

    loadDashboard().catch(error => {
      app.innerHTML = `<section class="auth-wrap"><div class="eyebrow">Koneksi database</div><h1>Admin belum dapat dibuka.</h1><p class="muted">${escapeHTML(error.message)} Pastikan MySQL aktif, lalu muat ulang.</p></section>`;
    });
  </script>
</body>
</html>