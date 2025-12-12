<?php
require_once '../../admin/api/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Checkout - Embun Slowbar</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{
      --accent:#6fbf8f;
      --accent-600:#52a66f;
      --muted:#6f7b76;
      --card:#ffffff;
      --bg:#f4f7f3;
      --radius:12px;
      --pill-bg:#eef7ef;
      --pill-active:#d7efe0;
      --shadow: 0 8px 24px rgba(20,20,20,0.06);
      --input-border:#e6e9e6;
      --input-focus:rgba(111,191,143,0.25);
    }

    *{box-sizing:border-box}
    body{
      font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;
      margin:0;
      background:var(--bg);
      color:#142018;
      line-height:1.5;
    }
    .container{max-width:1200px;margin:28px auto;padding:0 18px}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:12px}
    .top-left{display:flex;align-items:center;gap:12px}
    .top-right{display:flex;align-items:center;gap:8px}
    .btn-back{
      display:inline-block;
      background:#fff;
      color:var(--accent);
      padding:8px 12px;
      border-radius:8px;
      text-decoration:none;
      font-weight:700;
      border:1px solid rgba(20,20,20,0.06);
    }
    h1{font-size:18px;margin:0}
    .layout{display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start}
    .main-card{
      background:var(--card);
      padding:18px;
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      
    }
    .sidebar .box{
      background:var(--card);
      padding:16px;
      border-radius:10px;
      margin-bottom:12px;
      box-shadow:0 6px 18px rgba(0,0,0,0.04);
      
    }
    .muted{color:var(--muted);font-size:13px}

    /* items grid – fix kolom supaya tidak berubah antara light/dark */
    .items-grid{
      display:grid;
      grid-template-columns:repeat(3,minmax(0,1fr)); /* desktop: 3 kolom */
      gap:14px;
      margin-top:12px;
    }

    /* tablet */
    @media (max-width:980px){
      .items-grid{
        grid-template-columns:repeat(2,minmax(0,1fr)); /* 2 kolom */
      }
    }

    /* hp */
    @media (max-width:640px){
      .items-grid{
        grid-template-columns:repeat(1,minmax(0,1fr)); /* 1 kolom */
      }
    }

    .card-item{
      background:linear-gradient(180deg,#fff,#fbfff9);
      border-radius:12px;
      padding:12px;
      border:1px solid #eef7ec;
      box-shadow:0 4px 12px rgba(0,0,0,0.03);
      display:flex;
      flex-direction:column;
      gap:8px;
      overflow:hidden;
      
    }
    .card-item .title{font-weight:700}
    .card-item .desc{color:var(--muted);font-size:13px}
    .card-item .price{color:var(--accent-600);font-weight:700;margin-top:6px}
    .card-actions{display:flex;gap:8px;align-items:center;margin-top:auto}
    .card-item .option-panel{margin-top:10px}

    /* inputs nicer */
    input[type="text"], input[type="email"], input[type="number"], select, textarea {
      width:100%;
      padding:10px 12px;
      border-radius:10px;
      border:1px solid var(--input-border);
      background:#fff;
      box-shadow:0 1px 0 rgba(0,0,0,0.02);
      
      font-size:14px;
    }
    input[type="text"]:focus, input[type="email"]:focus, input[type="number"]:focus, select:focus, textarea:focus {
      outline: none;
      border-color: var(--accent-600);
      box-shadow: 0 6px 18px var(--input-focus);
    }
    input::placeholder, textarea::placeholder { color: #9aa6a0; }

    .btn{
      display:inline-block;
      padding:8px 12px;
      border-radius:10px;
      border:0;
      cursor:pointer;
      
    }
    .btn.primary{background:var(--accent);color:#fff;font-weight:700}
    .btn.ghost{background:#fff;border:1px solid #e7efe8;color:#333}
    .qty-input{width:70px;padding:8px;border-radius:8px;border:1px solid var(--input-border);text-align:center}
    .option-panel{
      background:var(--pill-bg);
      padding:12px;
      border-radius:10px;
      margin-top:10px;
      border:1px dashed rgba(111,191,143,0.22);
      box-sizing:border-box;
      
    }
    .opt-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(80px,1fr));gap:8px;margin-top:8px}
    .pill{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      padding:8px 10px;
      border-radius:999px;
      background:transparent;
      border:1px solid transparent;
      cursor:pointer;
      font-weight:700;
      min-height:44px;
      
    }
    .pill:hover{background:var(--pill-active)}
    .pill.selected{background:var(--accent);color:#fff;border-color:var(--accent-600)}
    .inline-row{display:flex;gap:12px;align-items:center;margin-top:8px;flex-wrap:wrap}
    .small{font-size:13px;color:var(--muted)}

    /* cart / left */
    .cart-table{width:100%;border-collapse:collapse;margin-top:14px}
    .cart-table th, .cart-table td{padding:8px;border-bottom:1px solid #f1f4f2;text-align:left}
    .cart-empty{color:var(--muted);padding:12px 0}

    /* sidebar form tweaks to fit nicely */
    .sidebar .box form > label { display:block; margin-top:8px; font-weight:700; font-size:13px; }
    .sidebar .box input[type="text"], .sidebar .box input[type="email"], .sidebar .box input[type="number"], .sidebar .box textarea { margin-top:6px; }

    /* theme switch */
    .theme-switch{
      position:relative;
      display:inline-flex;
      align-items:center;
      justify-content:space-between;
      gap:8px;
      padding:4px 8px;
      border-radius:999px;
      background:#ffffff;
      border:1px solid #e5e7eb;
      min-width:140px;
      cursor:pointer;
      font-size:13px;
      user-select:none;
    }
      .theme-switch-thumb{
      position:absolute;
      top:3px;
      bottom:3px;
      left:3px; /* mulai dari kiri */
      width:calc(50% - 3px); /* biar pas, tidak keluar */
      border-radius:999px;
      background:var(--accent);
      transition:left 0.2s ease;
      z-index:0;
    }

    .theme-switch-label{
      position:relative;
      z-index:1;
      flex:1;
      text-align:center;
      font-weight:600;
      opacity:.6;
      transition:opacity 0.2s,color 0.2s;
      color:#111827;
    }
    .theme-switch.light .theme-switch-label.ts-light{opacity:1;}
    .theme-switch.dark  .theme-switch-label.ts-dark {opacity:1;color:#0f172a;}

    /* fab */
    .fab{position:fixed;right:20px;bottom:22px;background:var(--accent);color:#fff;padding:12px 16px;border-radius:999px;box-shadow:0 10px 28px rgba(43,96,73,0.18);display:none;z-index:999;text-decoration:none;font-weight:700}
    .fab.show{display:inline-block}

    /* === DARK MODE === */
    body.dark{
      --bg:#020617;
      --card:#020617;
      --muted:#9ca3af;
      --pill-bg:#020617;
      --pill-active:#111827;
      --input-border:#1f2933;
      --shadow:0 12px 30px rgba(0,0,0,0.6);
      background:#020617;
      color:#e5f1ea;
    }
    body.dark .btn-back{
      background:#020617;
      color:var(--accent);
      border-color:#1f2933;
    }
    body.dark .main-card,
    body.dark .sidebar .box{
      background:#020617;
      box-shadow:var(--shadow);
      border:1px solid #1f2933;
    }
    body.dark input[type="text"],
    body.dark input[type="email"],
    body.dark input[type="number"],
    body.dark select,
    body.dark textarea{
      background:#020617;
      color:#e5f1ea;
      border-color:var(--input-border);
      box-shadow:none;
    }
    body.dark input::placeholder,
    body.dark textarea::placeholder{
      color:#6b7280;
    }
    body.dark .card-item{
      background:linear-gradient(180deg,#020617,#020617);
      border-color:#1f2933;
      box-shadow:var(--shadow);
    }
    body.dark .card-item .title,
    body.dark .card-item .desc{
      color:#e5f1ea;
    }
    body.dark .cart-table th,
    body.dark .cart-table td{
      border-bottom:1px solid #111827;
    }
    body.dark .btn.ghost{
      background:#020617;
      border-color:#1f2933;
      color:#e5f1ea;
    }
    body.dark .btn.primary{
      box-shadow:0 8px 18px rgba(34,197,94,0.35);
    }
    body.dark .muted{
      color:#9ca3af;
    }
    body.dark .option-panel{
      border-color:rgba(148,163,184,0.4);
      color:#e5f1ea;
    }
    /* teks di dalam panel pilihan jadi putih */
    body.dark .option-panel label,
    body.dark .option-panel .small{
      color:#e5f1ea;
    }
    body.dark .pill{
      color:#e5f1ea;
      border-color:#374151;
      background:#020617;
    }
    body.dark .pill:hover{
      background:#111827;
    }
    body.dark .pill.selected{
      background:var(--accent);
      border-color:var(--accent-600);
      color:#fff;
    }

    body.dark .theme-switch{
      background:#020617;
      border-color:#1f2933;
    }
    body.dark .theme-switch-thumb{
      left:calc(50% + 0px); /* geser ke “kolom” kanan */
    }

    body.dark .theme-switch-label{
      color:#e5f1ea;
    }

    @media(max-width:980px){
      .layout{grid-template-columns:1fr}
      .fab{right:14px;bottom:14px}
      .card-item{min-height:220px}
    }
  </style>
</head>
<body>
<div class="container">
  <div class="topbar">
    <div class="top-left">
      <a class="btn-back" href="../panels/Index.php">← Beranda</a>
      <h1>Checkout — Embun Slowbar</h1>
    </div>
    <div class="top-right">
      <!-- switch Light / Dark -->
      <div id="themeSwitch" class="theme-switch light" role="button" aria-label="Ganti tema" aria-pressed="false">
        <div class="theme-switch-thumb"></div>
        <span class="theme-switch-label ts-light">Light</span>
        <span class="theme-switch-label ts-dark">Dark</span>
      </div>
    </div>
  </div>

  <div class="layout">
    <!-- LEFT column: items + cart table -->
    <div class="main-card">
      <h3 style="margin-top:0">Pilih Menu</h3>

      <div style="display:flex;gap:12px;align-items:center;margin-bottom:8px">
        <label style="min-width:120px;margin:0">Kategori</label>
        <select id="categorySelect" aria-label="Kategori">
          <option value="all">Memuat...</option>
        </select>
      </div>

      <div class="muted">Klik <b>Pilih</b> pada kartu menu — opsi muncul langsung di bawah kartu.</div>

      <div id="itemsGrid" class="items-grid" aria-live="polite">
        <!-- rendered by JS -->
      </div>

      <!-- left cart area -->
      <div id="leftCartArea"></div>
    </div>

    <!-- RIGHT column: summary + checkout form -->
    <aside class="sidebar">
      <div class="box">
        <h4 style="margin:0 0 8px 0">RINGKASAN</h4>
        <div style="display:flex;justify-content:space-between"><div class="small">Subtotal</div><div id="sumSubtotal">Rp 0</div></div>
        <div style="display:flex;justify-content:space-between"><div class="small">Shipping</div><div class="small">FREE</div></div>
        <hr>
        <div style="display:flex;justify-content:space-between;font-weight:700;font-size:16px"><div>Total</div><div id="sumTotal">Rp 0</div></div>
      </div>

      <div class="box">
        <h4 style="margin:0 0 8px 0">Detail Pembeli & Pembayaran</h4>
        <form id="checkoutForm">
          <label>Nama</label>
          <input type="text" name="customer_name" placeholder="Nama lengkap" required>

          <label>Email</label>
          <input type="email" name="email" placeholder="email@domain.com" required>

          <label>Nomor WhatsApp (WA)</label>
          <input type="text" name="wa_number" id="wa_number" placeholder="+62..." />

          <input type="hidden" name="cart_json" id="cart_json">
          <input type="hidden" name="item_name" id="item_name" value="Keranjang Embun">

          <label>Total (Rp)</label>
          <input type="number" name="amount" id="amount" value="0" readonly required style="font-weight:700">

          <div style="display:flex;gap:8px;margin-top:12px">
            <button type="submit" class="btn primary">Bayar Sekarang</button>
            <button type="button" id="resetCart" class="btn ghost">Kosongkan Keranjang</button>
          </div>
        </form>
      </div>

      <div class="box">
        <h4 style="margin:0 0 8px 0">DI KERANJANG</h4>
        <div id="cartPreview"><div class="cart-empty">Belum ada item.</div></div>
      </div>

      <div class="box"><h4 style="margin:0 0 8px 0">INFO</h4><div class="muted">Orders on business days. Hubungi WA(isiNomer) jika perlu.</div></div>
    </aside>
  </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="<?php echo htmlspecialchars($MIDTRANS_CLIENT_KEY, ENT_QUOTES); ?>"></script>

<script>
/* THEME TOGGLE – switch Light / Dark */
(function(){
  const sw = document.getElementById('themeSwitch');
  if (!sw) return;

  function applyTheme(theme){
    const isDark = theme === 'dark';
    document.body.classList.toggle('dark', isDark);
    sw.classList.toggle('dark', isDark);
    sw.classList.toggle('light', !isDark);
    sw.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    localStorage.setItem('embunTheme', theme);
  }

  const saved = localStorage.getItem('embunTheme') || 'light';
  applyTheme(saved);

  sw.addEventListener('click', () => {
    const current = document.body.classList.contains('dark') ? 'dark' : 'light';
    applyTheme(current === 'dark' ? 'light' : 'dark');
  });
})();

/* Utilities */
const fmtRp = n => new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n||0);
function normalizePrice(v){ if(typeof v==='number') return Math.round(v); if(typeof v==='string'){ const s=v.trim(); if(/[.,]/.test(s)){ const f=parseFloat(s.replace(',','.')); if(!isNaN(f)) return Math.round(f);} const only = s.replace(/[^\d]/g,''); return only ? parseInt(only,10) : 0; } return 0; }
function el(q){ return document.querySelector(q); }
function create(tag, cls=''){ const e = document.createElement(tag); if(cls) e.className = cls; return e; }
function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

/* State */
const itemsGrid = el('#itemsGrid');
const categorySelect = el('#categorySelect');
const cartPreview = el('#cartPreview');
const leftCartArea = el('#leftCartArea');
const sumSubtotal = el('#sumSubtotal'), sumTotal = el('#sumTotal');
const amountInput = el('#amount'), cartJsonInput = el('#cart_json');

const ADD_SUGAR_PRICE = 500;
let allMenuItems = [];
let allCategories = [];
let cart = []; // rows: {id, orig_id, name, price, quantity, options, extra_price}

/* Cart helpers */
function computeExtra(opts){ return (opts && opts.add_sugar) ? ADD_SUGAR_PRICE : 0; }
function addToCartRow(row){ cart.push(row); renderCart(); }
function addToCartFromItem(item, qty, options){
  const price = normalizePrice(item.price ?? 0);
  const extra = computeExtra(options);
  const uid = String(item.id) + '-' + Math.random().toString(36).slice(2,8);
  const name = item.name ?? item.title ?? ('Item ' + item.id);
  const row = { id: uid, orig_id: item.id, name, price, quantity: qty, options, extra_price: extra };
  addToCartRow(row);
}
function updateQty(id, qty){ const i = cart.findIndex(x=>String(x.id)===String(id)); if(i===-1) return; cart[i].quantity = Math.max(1, parseInt(qty||1,10)); renderCart(); }
function removeFromCart(id){ cart = cart.filter(x=> String(x.id)!==String(id)); renderCart(); }
function totals(){ const subtotal = cart.reduce((s,i)=> s + (i.price * i.quantity), 0); const extra = cart.reduce((s,i)=> s + ((i.extra_price||0) * i.quantity), 0); const total = subtotal + extra; return { subtotal, extra, total }; }

/* Render cart (left area + sidebar preview) */
function renderCart(){
  // left cart table
  let leftBody = document.getElementById('leftCartBody');
  if (!leftBody){
    leftCartArea.innerHTML = `<h4>Keranjang</h4>
      <table class="cart-table" style="width:100%;border-collapse:collapse">
        <thead><tr><th>Item</th><th>Harga</th><th>Qty</th><th>Subtotal</th><th>Aksi</th></tr></thead>
        <tbody id="leftCartBody"></tbody>
      </table>`;
    leftBody = document.getElementById('leftCartBody');
  }
  leftBody.innerHTML = '';
  if (!cart.length){
    leftBody.innerHTML = '<tr><td colspan="5" class="cart-empty">Belum ada item. Tambahkan dari grid.</td></tr>';
  } else {
    cart.forEach(r=>{
      const tr = create('tr');
      const tdName = create('td'); tdName.innerHTML = `<strong>${escapeHtml(r.name)}</strong>`;
      const opts = [];
      if (r.options) {
        if (r.options.sugar !== undefined) opts.push('Sugar: '+r.options.sugar);
        if (r.options.ice !== undefined) opts.push('Ice: '+r.options.ice);
        if (r.options.add_sugar) opts.push('Add sugar: +Rp' + ADD_SUGAR_PRICE);
        if (r.options.notes) opts.push('Notes: ' + escapeHtml(r.options.notes));
      }
      if (opts.length) tdName.innerHTML += `<div class="small" style="margin-top:6px">${opts.join(' • ')}</div>`;
      const tdP = create('td'); tdP.textContent = fmtRp(r.price + (r.extra_price||0)); tdP.className='right';
      const tdQ = create('td'); tdQ.className='right'; const qInp = create('input'); qInp.type='number'; qInp.min='1'; qInp.value = r.quantity; qInp.className='qty-input'; qInp.addEventListener('change', e=> updateQty(r.id, e.target.value)); tdQ.appendChild(qInp);
      const tdS = create('td'); tdS.className='right'; tdS.textContent = fmtRp( (r.price + (r.extra_price||0)) * r.quantity );
      const tdA = create('td'); tdA.className='right'; const del = create('button'); del.className='btn ghost'; del.textContent='Hapus'; del.addEventListener('click', ()=> removeFromCart(r.id)); tdA.appendChild(del);
      [tdName,tdP,tdQ,tdS,tdA].forEach(td=>tr.appendChild(td));
      leftBody.appendChild(tr);
    });
  }

  // sidebar preview
  cartPreview.innerHTML = '';
  if (!cart.length) cartPreview.innerHTML = '<div class="cart-empty">Belum ada item.</div>';
  else {
    cart.forEach(r=>{
      const w = create('div'); w.style.marginBottom='10px';
      const t = create('div'); t.style.fontWeight='700'; t.textContent = r.name;
      const m = create('div'); m.className='small'; m.textContent = 'Qty: ' + r.quantity + ' • ' + fmtRp(r.price + (r.extra_price||0));
      w.appendChild(t); w.appendChild(m); cartPreview.appendChild(w);
    });
  }

  // totals (no tax)
  const t = totals();
  sumSubtotal.textContent = fmtRp(t.subtotal + t.extra);
  sumTotal.textContent = fmtRp(t.total);
  amountInput.value = t.total || 0;
  cartJsonInput.value = JSON.stringify(cart.map(x=> ({ id:String(x.orig_id||x.id), name:x.name, price:x.price, quantity:x.quantity, options:x.options, extra_price:x.extra_price||0 })));
}

/* Build pill group */
function buildPillGroup(values, defaultValue){
  const cont = create('div','opt-grid');
  values.forEach(v=>{
    const btn = create('button','pill'); btn.type='button'; btn.textContent = v; btn.dataset.value = v;
    if (v === defaultValue) btn.classList.add('selected');
    btn.addEventListener('click', ()=> {
      cont.querySelectorAll('.pill').forEach(p=>p.classList.remove('selected'));
      btn.classList.add('selected');
    });
    cont.appendChild(btn);
  });
  return cont;
}

/* Build option panel for a card */
function buildOptionPanel(item){
  const panel = create('div','option-panel');
  const cat = item.category_id ?? item.category ?? 0;

  let sugarGroup = null, iceGroup = null, addSugarChk = null, notes = null;
  if (cat >=1 && cat <=6){
    const lblS = create('label'); lblS.textContent = 'Sugar %';
    sugarGroup = buildPillGroup(['100%','75%','50%','No Sugar'],'100%');

    const lblI = create('label'); lblI.textContent = 'Ice';
    iceGroup = buildPillGroup(['100%','75%','50%','No Ice (Hot)'],'100%');

    addSugarChk = create('input'); addSugarChk.type='checkbox';
    const addLbl = create('label'); addLbl.style.display='block'; addLbl.appendChild(addSugarChk); addLbl.append(' Add sugar (+Rp' + ADD_SUGAR_PRICE + ')');

    const lblN = create('label'); lblN.textContent = 'Catatan (optional)';
    notes = create('textarea'); notes.rows=2;

    panel.appendChild(lblS); panel.appendChild(sugarGroup);
    panel.appendChild(lblI); panel.appendChild(iceGroup);
    panel.appendChild(addLbl);
    panel.appendChild(lblN); panel.appendChild(notes);
  } else {
    const lblN = create('label'); lblN.textContent = 'Catatan (optional)';
    notes = create('textarea'); notes.rows=2;
    panel.appendChild(lblN); panel.appendChild(notes);
  }

  const actions = create('div'); actions.className='inline-row';
  const qty = create('input'); qty.type='number'; qty.min='1'; qty.value='1'; qty.className='qty-input';
  const btnAdd = create('button','btn primary'); btnAdd.type='button'; btnAdd.textContent='Tambah ke Keranjang';
  const btnGoto = create('button','btn ghost'); btnGoto.type='button'; btnGoto.textContent='Lihat / Checkout';
  const btnCancel = create('button','btn ghost'); btnCancel.type='button'; btnCancel.textContent='Batal';
  actions.appendChild(qty); actions.appendChild(btnAdd); actions.appendChild(btnGoto); actions.appendChild(btnCancel);
  panel.appendChild(actions);

  const get = ()=> {
    const options = {};
    if (sugarGroup){ const s = sugarGroup.querySelector('.pill.selected'); options.sugar = s ? s.dataset.value : '100%'; }
    if (iceGroup){ const i = iceGroup.querySelector('.pill.selected'); options.ice = i ? i.dataset.value : '100%'; }
    if (addSugarChk) options.add_sugar = !!addSugarChk.checked;
    if (notes) options.notes = notes.value.trim();
    return { options, qty: Math.max(1, parseInt(qty.value||'1',10)) };
  };

  return { panel, get, btnAdd, btnGoto, btnCancel };
}

/* Render items as cards */
function renderItemsGrid(items){
  itemsGrid.innerHTML = '';
  if (!items || !items.length){ itemsGrid.innerHTML = '<div class="cart-empty">Belum ada menu.</div>'; return; }
  items.forEach(item=>{
    const card = create('div','card-item');
    const title = create('div','title'); title.textContent = item.name ?? item.title ?? ('Item ' + item.id);
    const desc = create('div','desc'); desc.textContent = item.description ?? item.summary ?? '';
    const price = create('div','price'); price.textContent = fmtRp(normalizePrice(item.price ?? item.price_int ?? item.amount ?? 0));
    const actions = create('div','card-actions');

    const qty = create('input'); qty.type='number'; qty.min='1'; qty.value='1'; qty.className='qty-input';
    const pilih = create('button','btn ghost'); pilih.type='button'; pilih.textContent='Pilih';

    actions.appendChild(qty); actions.appendChild(pilih);

    card.appendChild(title);
    if (desc.textContent) card.appendChild(desc);
    card.appendChild(price);
    card.appendChild(actions);

    const { panel, get, btnAdd, btnGoto, btnCancel } = buildOptionPanel(item);
    panel.style.display = 'none';
    panel.classList.add('option-panel');
    card.appendChild(panel);

    pilih.addEventListener('click', ()=>{
      panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
      if (panel.style.display === 'block') panel.scrollIntoView({ behavior:'smooth', block:'nearest' });
    });

    btnCancel.addEventListener('click', ()=> panel.style.display = 'none');

    btnAdd.addEventListener('click', ()=> {
      const d = get();
      addToCartFromItem(item, d.qty, d.options);
      panel.style.display = 'none';
      qty.value = '1';
    });

    btnGoto.addEventListener('click', ()=> {
      const d = get();
      addToCartFromItem(item, d.qty, d.options);
      const form = document.getElementById('checkoutForm');
      if (form) form.scrollIntoView({ behavior:'smooth', block:'center' });
    });

    itemsGrid.appendChild(card);
  });
}

/* Load menu once */
async function loadInitial(){
  itemsGrid.innerHTML = '<div class="cart-empty">Memuat menu...</div>';
  try {
    const res = await fetch('../api/get_menu.php');
    const data = await res.json();
    allMenuItems = data.menu_items || data.items || data.products || [];
    allCategories = data.categories || [];
    if (!Array.isArray(allMenuItems)) allMenuItems = [];
    categorySelect.innerHTML = '<option value="all">Semua</option>';
    if (!Array.isArray(allCategories) || !allCategories.length){
      const map = {};
      allMenuItems.forEach(it=>{ const cid = String(it.category_id ?? it.category ?? '0'); if(!map[cid]) map[cid] = { id:cid, name: it.category_name ?? it.category_label ?? ('Kategori '+cid) }; });
      allCategories = Object.values(map);
    }
    allCategories.forEach(c=> {
      const o = document.createElement('option'); o.value = String(c.id); o.textContent = c.name;
      categorySelect.appendChild(o);
    });
    renderItemsGrid(allMenuItems);
  } catch(err){
    console.error('loadInitial error', err);
    itemsGrid.innerHTML = '<div class="cart-empty">Gagal memuat menu.</div>';
  }
}

categorySelect.addEventListener('change', function(){
  const v = this.value || 'all';
  if (v === 'all') return renderItemsGrid(allMenuItems);
  const filtered = allMenuItems.filter(it =>
    String(it.category_id ?? it.category ?? '') === String(v) ||
    String(it.category_slug ?? '').toLowerCase() === String(v).toLowerCase() ||
    String(it.category_name ?? '').toLowerCase() === String(v).toLowerCase()
  );
  renderItemsGrid(filtered);
});

el('#resetCart').addEventListener('click', ()=> { cart = []; renderCart(); });

/* Submit handler to payment.php */
document.getElementById('checkoutForm').addEventListener('submit', async function(e){
  e.preventDefault();
  if (!cart.length){ alert('Keranjang masih kosong. Tambahkan item terlebih dahulu.'); return; }
  document.getElementById('item_name').value = cart.map(i => i.name + ' x' + i.quantity).join(', ');
  cartJsonInput.value = JSON.stringify(cart.map(x => ({ id:String(x.orig_id||x.id), name:x.name, price:x.price, quantity:x.quantity, options:x.options, extra_price:x.extra_price||0 })));
  amountInput.value = String(totals().total || 0);

  const fd = new FormData(this);
  try {
    const resp = await fetch('payment.php', { method:'POST', body: fd });
    const data = await resp.json();
    if (!data.success){ alert('Gagal membuat transaksi: ' + (data.message || 'unknown')); return; }
    snap.pay(data.snapToken, {
      onSuccess: function(){ window.location.href = 'thankyou.php?order_id=' + encodeURIComponent(data.order_id); },
      onPending: function(){ alert('Menunggu pembayaran...'); },
      onError: function(){ alert('Pembayaran gagal.'); }
    });
  } catch(err){
    console.error('submit error', err);
    alert('Kesalahan saat membuat transaksi');
  }
});

/* init */
loadInitial();
renderCart();
</script>
</body>
</html>
