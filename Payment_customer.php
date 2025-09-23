<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HaulPro — Payment Center</title>
  <link rel="stylesheet" href="dashboad_style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
  <style>
    :root{
      --primary:#2563eb; --primary-hover:#1d4ed8;
      --bg:#f5f7fb; --surface:#fff; --border:#e5e7eb;
      --text:#0f172a; --muted:#64748b; --subtle:#334155;
      --radius:12px; --shadow:0 4px 12px rgba(0,0,0,0.08);
      --ok:#16a34a; --warn:#f59e0b; --bad:#ef4444;
      --chip:#eef2ff; --chiptext:#3730a3;
    }
    *{box-sizing:border-box}
    body{margin:0; font-family:Inter, "Segoe UI", system-ui, -apple-system, sans-serif; background:var(--bg); color:var(--text)}
    .container{display:flex; min-height:100vh}
    main{flex:1; padding:24px; max-width:1700px; margin:0 auto}

    /* Sidebar (inline, not centralized) */
    

    /* Header */
    .header{display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px}
    .header h1{margin:0; font-size:26px; color:var(--primary)}
    .btn-row{display:flex; gap:8px; flex-wrap:wrap}

    /* Tabs */
    .tabs{display:flex; gap:10px; flex-wrap:wrap; margin-top: 14px;margin-bottom:14px}
    .tab-btn{padding:10px 14px; border:1px solid var(--border); background:#fff; border-radius:999px; font-weight:600; cursor:pointer}
    .tab-btn.active{background:var(--primary); color:#fff; border-color:var(--primary); box-shadow:0 6px 16px rgba(37,99,235,.25)}
    .tab{display:none} .tab.active{display:block; animation:fade .25s ease}
    @keyframes fade{from{opacity:0; transform:translateY(8px)} to{opacity:1; transform:translateY(0)}}

    /* Layout & Cards */
    .grid{display:grid; gap:14px}
    .cols-2{grid-template-columns:1fr 1fr}
    .cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}
    .cols-4{grid-template-columns:repeat(4,minmax(0,1fr))}
    @media (max-width: 1050px){.cols-4{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width: 860px){.cols-3,.cols-2,.cols-4{grid-template-columns:1fr}}

    .card{background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); padding:16px}
    h2{font-size:20px; margin:0 0 10px}
    h3{font-size:16px; margin:8px 0}

    label{display:block; font-weight:600; margin:6px 0 4px}
    input, select, textarea{width:100%; padding:12px; border:1px solid var(--border); border-radius:10px; background:#fff; font-size:15px}
    .help{font-size:12px; color:var(--muted); margin-top:4px}

    .btn{background:var(--primary); color:#fff; padding:10px 16px; border:none; border-radius:10px; font-weight:600; cursor:pointer}
    .btn:hover{background:var(--primary-hover)}
    .btn.secondary{background:#f1f5f9; color:#0f172a; border:1px solid var(--border)}
    .btn.ghost{background:transparent; border:1px dashed var(--border); color:#0f172a}
    .btn.danger{background:var(--bad)}

    .seg{display:flex; gap:6px; background:#f1f5f9; padding:6px; border-radius:999px; width:max-content; margin-bottom:8px}
    .seg button{background:transparent; border:none; padding:8px 14px; border-radius:999px; cursor:pointer; font-weight:600}
    .seg button.active{background:#fff; box-shadow:var(--shadow); border:1px solid var(--border)}

    /* Stat cards */
    .stat{display:flex; align-items:center; justify-content:space-between}
    .stat .big{font-size:28px; font-weight:800}
    .chip{display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; background:var(--chip); color:var(--chiptext)}

    /* Tables */
    table{width:100%; border-collapse:separate; border-spacing:0; border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff}
    th,td{padding:10px 12px; border-bottom:1px solid var(--border); font-size:14px; text-align:left}
    thead th{background:#f8fafc; font-weight:700; color:#334155}
    tbody tr:hover{background:#f9fafb}
    .tag{display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; background:#eef2ff; color:#3730a3}
    .tag.green{background:#ecfdf5; color:#065f46}
    .tag.yellow{background:#fffbeb; color:#92400e}
    .tag.red{background:#fef2f2; color:#991b1b}
    .tag.gray{background:#f3f4f6; color:#374151}

    /* Toast */
    .toast{position:fixed; right:24px; bottom:24px; padding:12px 16px; background:#111827; color:#fff; border-radius:10px; box-shadow:var(--shadow); opacity:0; transform:translateY(10px); transition:.2s}
    .toast.show{opacity:1; transform:translateY(0)}
    .kbd{font-family:ui-monospace,Consolas,monospace; font-size:12px; background:#111827; color:#fff; padding:2px 6px; border-radius:6px}
  </style>
</head>
<body>
<div class="container">
  <!-- Inline Sidebar (keep active on Payment Method) -->
  <aside class="sidebar" id="sidebar">
    <img src="Image/Logo.png" alt="HaulPro Logo" width="160" />
    <h3>HaulPro</h3>
    <ul class="menu">
      <li>
        <a href="dashboard.html">
          <img src="Image/dashboard.png" alt="" />Dashboard
        </a>
      </li>
      <li class="has-submenu">
        <a href="#"> <img src="Image/chart.png" alt="" />Analysis </a>
        <ul class="submenu">
          <li><a href="delivery_performance.php"><img src="Image/continuous-improvement.png" alt=""/>Delivery Performance</a></li>
          <li><a href="Revenue_analysis.php"><img src="Image/profit-margin.png" alt=""/>Revenue Analysis</a></li>
          <li><a href="fleet_analysis.php"><img src="Image/delivery-truck.png" alt=""/>Fleet Efficiency</a></li>
        </ul>
      </li>
      <li><a href="#"><img src="Image/car.png" alt="" style="width: 40px" />Vehicle</a></li>
      <li><a href="#"><img src="Image/plus.png" alt="" style="width: 40px" />Add Trips</a></li>
      <li><a href="Payment_method.php" class="active"><img src="Image/wallet.png" alt="" style="width: 40px" />Payment Method</a></li>
      <li><a href="Lorry_owner.php"><img src="Image/businessman.png" alt="" style="width: 40px" />Lorry Owner List</a></li>
      <li><a href="lorrylist.php"><img src="Image/truck.png" alt="" style="width: 40px" />Lorry List</a></li>
      <li><a href="Admin_settings.php"><img src="Image/settings.png" alt="" style="width: 40px" />Settings</a></li>
      <li><a href="faq.html"><img src="Image/faq.png" alt="" style="width: 40px" />FAQ</a></li>
    </ul>
    <div class="help-card">
      <img src="https://cdn-icons-png.flaticon.com/512/4712/4712002.png" alt="Help"/>
      <p>Need Help?</p>
      <button>Contact Now</button>
    </div>
  </aside>

  <main>
    <div class="header">
      <h1>💳 Payment Center</h1>
      <div class="btn-row">
        <button class="btn secondary" id="exportTx">⬇️ Export Transactions CSV</button>
      </div>
    </div>

    <!-- Summary cards -->
    <div class="grid cols-4" id="summary">
      <div class="card stat">
        <div>
          <div class="subtle">Total Due</div>
          <div class="big" id="sum_due">—</div>
        </div>
        <button class="btn" id="quickPay">Pay Now</button>
      </div>
      <div class="card stat">
        <div>
          <div class="subtle">Overdue</div>
          <div class="big" id="sum_overdue">—</div>
        </div>
        <span class="chip" id="sum_next">Next due: —</span>
      </div>
      <div class="card stat">
        <div>
          <div class="subtle">Default Method</div>
          <div class="big" id="sum_default">—</div>
        </div>
        <span class="chip" id="sum_auto">Auto-charge: Off</span>
      </div>
      <div class="card stat">
        <div>
          <div class="subtle">Receipts</div>
          <div class="big" id="sum_receipts">0</div>
        </div>
        <button class="btn secondary" id="printLast">🧾 Last Receipt</button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab-btn active" data-tab="methods">Methods</button>
      <button class="tab-btn" data-tab="dues">Dues & Pay</button>
      <button class="tab-btn" data-tab="activity">Activity & Receipts</button>
    </div>

    <!-- METHODS TAB -->
    <section id="methods" class="tab active">
      <div class="grid cols-2">
        <!-- Left: add new method -->
        <div class="card">
          <h2>Add New Method</h2>
          <div class="seg" id="paySeg">
            <button data-kind="card" class="active">Card</button>
            <button data-kind="wallet">Mobile Wallet</button>
            <button data-kind="bank">Bank Account</button>
          </div>

          <div id="form_card">
            <div class="grid cols-2">
              <div><label>Name on Card</label><input id="c_name" placeholder="Jane Customer"/></div>
              <div><label>Label (optional)</label><input id="c_label" placeholder="Personal Visa"/></div>
            </div>
            <div class="grid cols-3">
              <div><label>Card Number</label><input id="c_number" placeholder="4242 4242 4242 4242" inputmode="numeric" maxlength="19"/><div class="help">Demo: only last 4 is stored</div></div>
              <div><label>Expiry (MM/YY)</label><input id="c_exp" placeholder="12/27" maxlength="5"/></div>
              <div><label>CVC</label><input id="c_cvc" placeholder="123" inputmode="numeric" maxlength="4"/></div>
            </div>
            <div class="btn-row"><button class="btn" id="addCard">➕ Add Card</button></div>
          </div>

          <div id="form_wallet" style="display:none">
            <div class="grid cols-2">
              <div><label>Provider</label>
                <select id="w_provider"><option>bKash</option><option>Nagad</option><option>Rocket</option><option>Upay</option></select>
              </div>
              <div><label>Wallet Number</label><input id="w_number" placeholder="+8801XXXXXXXXX"/></div>
            </div>
            <div class="btn-row"><button class="btn" id="addWallet">➕ Link Wallet</button></div>
            <div class="help">We’ll simulate verification in demo mode.</div>
          </div>

          <div id="form_bank" style="display:none">
            <div class="grid cols-2">
              <div><label>Account Name</label><input id="b_name" placeholder="Your Name or Company"/></div>
              <div><label>Account Number</label><input id="b_ac" placeholder="XXXXXXXXXXXX"/></div>
            </div>
            <div class="grid cols-2">
              <div><label>Bank & Branch</label><input id="b_branch" placeholder="Bank name, branch"/></div>
              <div><label>Routing / SWIFT (optional)</label><input id="b_swift" placeholder="XXXXXX"/></div>
            </div>
            <div class="btn-row"><button class="btn" id="addBank">➕ Add Bank</button></div>
          </div>
        </div>

        <!-- Right: saved methods -->
        <div class="card">
          <h2>Saved Methods</h2>
          <table id="pmTable">
            <thead><tr><th>Type</th><th>Label / Provider</th><th>Details</th><th>Default</th><th style="width:240px">Actions</th></tr></thead>
            <tbody></tbody>
          </table>
          <div class="help">Set one default method. Rename or remove anytime.</div>
        </div>
      </div>

      <div class="card" style="margin-top:14px">
        <h2>Billing Preferences</h2>
        <div class="grid cols-3">
          <div><label>Default Currency</label><select id="p_currency"><option>BDT</option><option selected>USD</option></select></div>
          <div><label>Auto-Charge on Due Date</label><select id="p_auto"><option>No</option><option>Yes</option></select><div class="help">Auto-charge the default method on invoice due date</div></div>
          <div><label>Receipt Email</label><input id="p_email" type="email" placeholder="billing@company.com"/></div>
        </div>
        <div class="btn-row" style="margin-top:8px"><button class="btn" id="savePrefs">💾 Save Preferences</button></div>
      </div>
    </section>

    <!-- DUES & PAY TAB -->
    <section id="dues" class="tab">
      <div class="grid cols-3">
        <div class="card stat" style="grid-column:1/-1">
          <div>
            <div class="subtle">Your Current Due</div>
            <div class="big" id="due_total">—</div>
            <div class="help">Includes pending rentals/self-use of trucks and invoices</div>
          </div>
          <div class="btn-row">
            <button class="btn" id="payFull">Pay Full Due</button>
            <button class="btn secondary" id="payPartialBtn">Pay Partial</button>
          </div>
        </div>

        <!-- Left: line items -->
        <div class="card" style="grid-column: span 2">
          <h2>Open Items</h2>
          <table id="dueTable">
            <thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Status</th><th style="width:160px">Action</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>

        <!-- Right: pay panel -->
        <div class="card">
          <h2>Pay Due</h2>
          <label>Amount</label>
          <input id="pay_amount" placeholder="e.g., 5000" inputmode="decimal"/>
          <label>Pay with</label>
          <select id="pay_method"></select>
          <div class="help">Default method is preselected when available</div>
          <div class="btn-row" style="margin-top:8px">
            <button class="btn" id="btnPay">💸 Pay Now</button>
            <button class="btn ghost" id="btnAddCharge">+ Add Test Charge</button>
          </div>
        </div>
      </div>
    </section>

    <!-- ACTIVITY TAB -->
    <section id="activity" class="tab">
      <div class="grid cols-3">
        <div class="card" style="grid-column:1/-1">
          <h2>Transactions</h2>
          <div class="grid cols-3" style="align-items:end">
            <div><label>Filter Type</label>
              <select id="f_type"><option value="">All</option><option value="invoice.pay">Payments</option><option value="card.add">Added Card</option><option value="wallet.link">Linked Wallet</option><option value="bank.add">Added Bank</option></select>
            </div>
            <div><label>Date Contains</label><input id="f_date" placeholder="e.g., 2025-09"/></div>
            <div class="btn-row"><button class="btn secondary" id="applyTxFilter">Apply</button><button class="btn" id="clearTxFilter">Clear</button></div>
          </div>
          <table id="txTable"><thead><tr><th>Date</th><th>Action</th><th>Amount</th><th>Status</th><th style="width:160px">Receipt</th></tr></thead><tbody></tbody></table>
        </div>
      </div>
    </section>

    <div class="toast" id="toast">Saved.</div>
  </main>
</div>

<script>
  // ===== Utilities =====
  const toast=(m='Saved.')=>{const t=document.getElementById('toast'); t.textContent=m; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),1600)};
  const nowStr = () => new Date().toLocaleString();
  const fmtLast4 = s => String(s).replace(/\D/g,'').slice(-4).padStart(4,'•');
  const fmtExp = s => s.replace(/\s+/g,'').replace(/^(\d{2})(\d{0,2}).*/, (m,a,b)=> b? a+'/'+b : a);
  const C = { BDT: '৳', USD: '$' };
  const money = (n,cur) => (C[cur]||'') + Number(n||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});

  // ===== Storage Keys =====
  const K_METHODS='cust_pay_methods';
  const K_PREFS='cust_pay_prefs';
  const K_TX='cust_pay_tx';
  const K_DUES='cust_dues';

  const get=(k,def)=>{ try{ return JSON.parse(localStorage.getItem(k)) ?? def }catch{ return def } };
  const set=(k,v)=>localStorage.setItem(k, JSON.stringify(v));

  // ===== Tabs =====
  document.querySelectorAll('.tab-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
      document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
      document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
      btn.classList.add('active'); document.getElementById(btn.dataset.tab).classList.add('active');
    });
  });

  // ===== Seed Demo Data =====
  (function seed(){
    if(!get(K_METHODS)){
      set(K_METHODS,[
        {id:'m1', type:'card', label:'Corporate Visa', details:{brand:'Visa', last4:'4242', exp:'12/27'}, isDefault:true, created:nowStr()},
        {id:'m2', type:'wallet', label:'bKash', details:{provider:'bKash', last4:'1234'}, isDefault:false, created:nowStr()},
        {id:'m3', type:'bank', label:'DBBL — Main', details:{bank:'Dutch-Bangla', last4:'6789'}, isDefault:false, created:nowStr()},
      ]);
    }
    if(!get(K_PREFS)){
      set(K_PREFS,{currency:'USD', auto:'No', email:'billing@haulpro.com'});
    }
    if(!get(K_TX)){
      set(K_TX,[
        {t:nowStr(), act:'card.add', amt:'—', status:'ok'},
        {t:nowStr(), act:'wallet.link', amt:'—', status:'ok'},
        {t:nowStr(), act:'invoice.pay #HP-INV-1001', amt:'$120.00', status:'ok'},
      ]);
    }
    if(!get(K_DUES)){
      const today = new Date();
      const next = new Date(today.getFullYear(), today.getMonth(), today.getDate()+7);
      set(K_DUES,{
        currency:'USD',
        items:[
          {id:'d1', date:new Date(today-5*864e5).toLocaleDateString(), desc:'Self-use Truck Rental — 1 day', amount:120.00, paid:false},
          {id:'d2', date:new Date(today-2*864e5).toLocaleDateString(), desc:'Fuel & Toll Adjustments', amount:35.50, paid:false},
          {id:'d3', date:new Date(today-1*864e5).toLocaleDateString(), desc:'Trip Assistance Fee', amount:9.99, paid:false},
        ],
        nextDue: next.toLocaleDateString()
      });
    }
  })();

  // ===== Methods UI =====
  const seg=document.getElementById('paySeg');
  const forms={card:form_card, wallet:form_wallet, bank:form_bank};
  seg.addEventListener('click', (e)=>{
    if(e.target.tagName!=='BUTTON') return;
    seg.querySelectorAll('button').forEach(b=>b.classList.remove('active'));
    e.target.classList.add('active');
    const kind=e.target.dataset.kind; Object.keys(forms).forEach(k=>forms[k].style.display=(k===kind?'block':'none'));
  });

  function renderMethods(){
    const tb=document.querySelector('#pmTable tbody'); tb.innerHTML='';
    const methods=get(K_METHODS,[]);
    methods.forEach((m,idx)=>{
      const tr=document.createElement('tr');
      const det = m.type==='card' ? `${m.details.brand||'Card'} •••• ${m.details.last4} (${m.details.exp||'—'})`
                : m.type==='wallet' ? `${m.details.provider} •••• ${m.details.last4}`
                : `${m.details.bank||'Bank'} •••• ${m.details.last4}`;
      tr.innerHTML = `
        <td>${m.type[0].toUpperCase()+m.type.slice(1)}</td>
        <td><input data-idx="${idx}" class="pmLabel" value="${m.label||''}" style="width:220px"/></td>
        <td>${det}</td>
        <td>${m.isDefault?'<span class="tag green">Default</span>':'<span class="tag gray">—</span>'}</td>
        <td>
          ${m.isDefault?'':`<button class="btn secondary setDef" data-idx="${idx}">Set Default</button>`}
          <button class="btn danger del" data-idx="${idx}">Remove</button>
        </td>`;
      tb.appendChild(tr);
    });

    tb.querySelectorAll('.pmLabel').forEach(inp=>{
      inp.addEventListener('change', e=>{ const i=+e.target.dataset.idx; const methods=get(K_METHODS,[]); methods[i].label=e.target.value; set(K_METHODS,methods); toast('Label updated') });
    });
    tb.querySelectorAll('.setDef').forEach(btn=>{
      btn.addEventListener('click', e=>{ const i=+e.target.dataset.idx; const methods=get(K_METHODS,[]); methods.forEach((m,ix)=> m.isDefault = (ix===i)); set(K_METHODS,methods); renderMethods(); renderSummary(); toast('Default method set') });
    });
    tb.querySelectorAll('.del').forEach(btn=>{
      btn.addEventListener('click', e=>{ const i=+e.target.dataset.idx; let methods=get(K_METHODS,[]); if(!confirm('Remove this payment method?')) return; const wasDefault=methods[i].isDefault; methods.splice(i,1); if(wasDefault && methods[0]) methods[0].isDefault=true; set(K_METHODS,methods); renderMethods(); renderSummary(); toast('Method removed') });
    });

    // fill pay dropdown
    const sel=document.getElementById('pay_method'); sel.innerHTML='';
    methods.forEach(m=>{ const opt=document.createElement('option'); opt.value=m.id; opt.textContent=(m.label||m.type)+' — '+(m.type==='card'? (m.details.brand+' •••• '+m.details.last4) : m.type==='wallet'? (m.details.provider+' •••• '+m.details.last4) : (m.details.bank+' •••• '+m.details.last4)); sel.appendChild(opt) });
    const def=methods.find(m=>m.isDefault); if(def) sel.value=def.id;
  }

  // Add methods
  addCard.addEventListener('click', ()=>{
    const name=c_name.value.trim(), num=c_number.value.replace(/\s+/g,''); const exp=c_exp.value.trim(), cvc=c_cvc.value.trim();
    if(!name || num.length<12 || !exp || cvc.length<3){ alert('Please fill valid card details.'); return; }
    const last4 = fmtLast4(num); const brand = (/^4/.test(num)?'Visa': /^5[1-5]/.test(num)?'Mastercard': 'Card');
    const m=get(K_METHODS,[]); m.push({id:'m'+Math.random().toString(36).slice(2,8), type:'card', label:c_label.value||`${brand} •••• ${last4}`, details:{brand, last4, exp:fmtExp(exp)}, isDefault:m.length===0, created:nowStr()}); set(K_METHODS,m);
    const tx=get(K_TX,[]); tx.unshift({t:nowStr(), act:'card.add', amt:'—', status:'ok'}); set(K_TX,tx);
    c_name.value=c_label.value=c_number.value=c_exp.value=c_cvc.value=''; renderMethods(); renderTx(); renderSummary(); toast('Card added (demo)');
  });
  addWallet.addEventListener('click', ()=>{
    const prov=w_provider.value, num=w_number.value.trim(); if(!num){ alert('Enter wallet number'); return; }
    const last4=fmtLast4(num); const m=get(K_METHODS,[]); m.push({id:'m'+Math.random().toString(36).slice(2,8), type:'wallet', label:prov, details:{provider:prov, last4}, isDefault:m.length===0, created:nowStr()}); set(K_METHODS,m);
    const tx=get(K_TX,[]); tx.unshift({t:nowStr(), act:'wallet.link', amt:'—', status:'ok'}); set(K_TX,tx);
    w_number.value=''; renderMethods(); renderTx(); renderSummary(); toast('Wallet linked (demo)');
  });
  addBank.addEventListener('click', ()=>{
    const nm=b_name.value.trim(), ac=b_ac.value.trim(), bank=b_branch.value.trim(); if(!nm || !ac){ alert('Enter account name & number'); return; }
    const last4=fmtLast4(ac); const label=(nm||'Bank') + (bank? ' — '+bank : '');
    const m=get(K_METHODS,[]); m.push({id:'m'+Math.random().toString(36).slice(2,8), type:'bank', label, details:{bank:bank||'Bank', last4}, isDefault:m.length===0, created:nowStr()}); set(K_METHODS,m);
    const tx=get(K_TX,[]); tx.unshift({t:nowStr(), act:'bank.add', amt:'—', status:'ok'}); set(K_TX,tx);
    b_name.value=b_ac.value=b_branch.value=b_swift.value=''; renderMethods(); renderTx(); renderSummary(); toast('Bank added (demo)');
  });

  // Preferences
  function renderPrefs(){ const p=get(K_PREFS,{currency:'USD',auto:'No',email:''}); p_currency.value=p.currency||'USD'; p_auto.value=p.auto||'No'; p_email.value=p.email||'' }
  savePrefs.addEventListener('click', ()=>{ set(K_PREFS,{currency:p_currency.value, auto:p_auto.value, email:p_email.value.trim()}); renderSummary(); toast('Preferences saved') });

  // ===== Dues =====
  function dueState(){ const d=get(K_DUES,{currency:'USD',items:[],nextDue:'—'}); const cur=(get(K_PREFS)||{}).currency||d.currency||'USD'; let balance=0, overdue=0; const today=new Date();
    d.items.forEach(it=>{ if(!it.paid){ balance+=Number(it.amount)||0; const dt=new Date(it.date); if(dt<today) overdue+=Number(it.amount)||0; }}); return {d,cur,balance,overdue}; }

  function renderDues(){
    const {d,cur,balance,overdue}=dueState();
    due_total.textContent = money(balance, cur);
    const tb=document.querySelector('#dueTable tbody'); tb.innerHTML='';
    d.items.forEach((it,idx)=>{
      const tr=document.createElement('tr');
      tr.innerHTML=`<td>${it.date}</td><td>${it.desc}</td><td>${money(it.amount, cur)}</td><td>${it.paid?'<span class="tag green">Paid</span>':'<span class="tag yellow">Open</span>'}</td><td>${it.paid?'—':`<button class="btn secondary payItem" data-idx="${idx}">Pay Item</button>`}</td>`;
      tb.appendChild(tr);
    });
    // actions
    tb.querySelectorAll('.payItem').forEach(btn=>{
      btn.addEventListener('click',()=>{ const idx=+btn.dataset.idx; const {d}=dueState(); const it=d.items[idx]; pay_amount.value = Number(it.amount).toFixed(2); toast('Amount set from item'); });
    });
  }

  function applyPayment(amount, methodId){
    if(!(amount>0)) return false;
    const state=get(K_DUES); const methods=get(K_METHODS,[]); const method=methods.find(m=>m.id===methodId)||methods.find(m=>m.isDefault);
    if(!method){ alert('Please add/select a payment method first.'); return false; }
    // allocate oldest-first
    let remain=amount; for(const it of state.items){ if(it.paid) continue; const a=Number(it.amount)||0; if(remain>=a){ it.paid=true; remain-=a; } else { it.amount=(a-remain).toFixed(2); remain=0; break; } if(remain<=0) break; }
    set(K_DUES,state);
    // record tx
    const prefs=get(K_PREFS,{currency:'USD'}); const cur=prefs.currency||state.currency||'USD';
    const tx=get(K_TX,[]); tx.unshift({t:nowStr(), act:'invoice.pay', amt:money(amount,cur), status:'ok', method:method.label||method.type}); set(K_TX,tx);
    return true;
  }

  payFull.addEventListener('click',()=>{ const {balance}=dueState(); if(balance<=0){ toast('No outstanding due'); return; } pay_amount.value=balance.toFixed(2); document.querySelector('.tab-btn[data-tab="dues"]').click(); });
  payPartialBtn.addEventListener('click',()=>{ const {balance}=dueState(); const half=(balance/2).toFixed(2); pay_amount.value=half; toast('Partial amount filled'); });
  quickPay.addEventListener('click',()=>{ document.querySelector('.tab-btn[data-tab="dues"]').click(); setTimeout(()=> payFull.click(), 0) });

  btnPay.addEventListener('click',()=>{
    const amt=parseFloat((pay_amount.value||'0').replace(/,/g,'')); const methodId=pay_method.value; if(!(amt>0)){ alert('Enter a valid amount'); return; }
    const ok=applyPayment(amt, methodId); if(!ok) return; renderDues(); renderTx(); renderSummary(); pay_amount.value=''; toast('Payment successful (demo)');
  });

  btnAddCharge.addEventListener('click',()=>{ const st=get(K_DUES); const n={id:'d'+Math.random().toString(36).slice(2,6), date:new Date().toLocaleDateString(), desc:'Additional Usage Fee', amount:25.00, paid:false}; st.items.push(n); set(K_DUES,st); renderDues(); renderSummary(); toast('Test charge added'); });

  // ===== Activity =====
  function renderTx(filter={}){
    const tb=document.querySelector('#txTable tbody'); tb.innerHTML='';
    const txs=get(K_TX,[]).filter(r=>{
      if(filter.type && !(r.act||'').startsWith(filter.type)) return false;
      if(filter.date && !(r.t||'').includes(filter.date)) return false; return true;
    });
    txs.forEach((r,i)=>{
      const tr=document.createElement('tr');
      const isPay=(r.act||'').startsWith('invoice.pay');
      tr.innerHTML=`<td>${r.t}</td><td>${r.act}</td><td>${r.amt||'—'}</td><td><span class="tag ${r.status==='ok'?'green':'gray'}">${r.status}</span></td><td>${isPay?`<button class="btn secondary rcpt" data-idx="${i}">Download</button>`:'—'}</td>`;
      tb.appendChild(tr);
    });
    // receipts
    tb.querySelectorAll('.rcpt').forEach(btn=> btn.addEventListener('click',()=> downloadReceipt(parseInt(btn.dataset.idx,10), txs)) );
    sum_receipts.textContent = txs.filter(r=> (r.act||'').startsWith('invoice.pay')).length;
  }

  function receiptHTML(entry){
    const prefs=get(K_PREFS,{currency:'USD', email:'billing@example.com'});
    return `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Receipt</title>
      <style>body{font-family:Inter,Segoe UI,sans-serif;padding:24px;color:#0f172a}
      .box{border:1px solid #e5e7eb; border-radius:12px; padding:16px; max-width:720px}
      h1{margin:0 0 8px; font-size:22px; color:#2563eb}
      .row{display:flex; gap:20px; flex-wrap:wrap}
      .row>div{flex:1 1 240px}
      .muted{color:#64748b}
      table{width:100%; border-collapse:collapse; margin-top:12px}
      th,td{padding:8px; border-bottom:1px solid #e5e7eb; text-align:left}
      th{background:#f8fafc}
      </style></head><body>
      <div class="box">
        <h1>Payment Receipt</h1>
        <div class="row">
          <div><div class="muted">Date</div><div>${entry.t}</div></div>
          <div><div class="muted">Amount</div><div>${entry.amt}</div></div>
          <div><div class="muted">Method</div><div>${entry.method||'—'}</div></div>
          <div><div class="muted">Email</div><div>${prefs.email||'—'}</div></div>
        </div>
        <table><thead><tr><th>Description</th><th>Status</th></tr></thead>
          <tbody><tr><td>${entry.act}</td><td>${entry.status}</td></tr></tbody>
        </table>
      </div>
    </body></html>`;
  }

  function downloadReceipt(idx, list){ const entry=list[idx]; const blob=new Blob([receiptHTML(entry)],{type:'text/html'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=`receipt_${Date.now()}.html`; a.click(); }
  printLast.addEventListener('click',()=>{
    const tx=get(K_TX,[]); const last=tx.find(r=> (r.act||'').startsWith('invoice.pay')); if(!last){ toast('No payment receipt found'); return; }
    const blob=new Blob([receiptHTML(last)],{type:'text/html'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=`receipt_${Date.now()}.html`; a.click();
  });

  // ===== Summary =====
  function renderSummary(){
    const prefs=get(K_PREFS,{currency:'USD',auto:'No'});
    const {d,cur,balance,overdue}=dueState();
    sum_due.textContent = money(balance, prefs.currency||cur);
    sum_overdue.textContent = money(overdue, prefs.currency||cur);
    sum_next.textContent = 'Next due: ' + (d.nextDue||'—');
    const methods=get(K_METHODS,[]); const def=methods.find(m=>m.isDefault);
    sum_default.textContent = def? (def.label || (def.type==='card'? def.details.brand+' •••• '+def.details.last4 : def.type)) : '—';
    sum_auto.textContent = 'Auto-charge: ' + (prefs.auto==='Yes'?'On':'Off');

    // prepare pay panel dropdown
    const sel=document.getElementById('pay_method'); if(def) sel.value=def.id;
  }

  // Export CSV
  exportTx.addEventListener('click', ()=>{
    const rows=get(K_TX,[]); const csv=['time,action,amount,status,method'].concat(rows.map(r=>`"${r.t}","${(r.act||'').replace(/"/g,'""')}","${r.amt||''}","${r.status||''}","${r.method||''}"`)).join('\n');
    const blob=new Blob([csv],{type:'text/csv'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='payment_activity.csv'; a.click();
  });

  // Inputs formatting
  c_exp && c_exp.addEventListener('input', ()=> c_exp.value = fmtExp(c_exp.value));
  c_number && c_number.addEventListener('input', ()=>{ let v=c_number.value.replace(/\D/g,'').slice(0,16); c_number.value = v.replace(/(\d{4})(?=\d)/g,'$1 ').trim(); });

  // Filters
  applyTxFilter.addEventListener('click',()=>{ renderTx({type:f_type.value, date:f_date.value.trim()}) });
  clearTxFilter.addEventListener('click',()=>{ f_type.value=''; f_date.value=''; renderTx({}) });

  // Init
  (function init(){ renderMethods(); renderPrefs(); renderDues(); renderTx({}); renderSummary(); })();
</script>
</body>
</html>