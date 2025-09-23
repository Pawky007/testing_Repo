<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HaulPro — Admin Payment</title>
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
      --purple:#7c3aed;
    }
    *{box-sizing:border-box}
    body{margin:0; font-family:Inter, "Segoe UI", system-ui, -apple-system, sans-serif; background:var(--bg); color:var(--text)}
    .container{display:flex; min-height:100vh}
    main{flex:1; padding:24px; max-width:1500px; margin:0 auto}

    /* Sidebar (inline) */
    aside.sidebar{width:260px; padding:20px; background:#fff; border-right:1px solid var(--border)}
    aside.sidebar h3{margin:8px 0 16px}
    aside.sidebar .menu{list-style:none; padding:0; margin:0; display:grid; gap:6px}
    aside.sidebar .menu a{display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:10px; text-decoration:none; color:#0f172a}
    aside.sidebar .menu a.active, aside.sidebar .menu a:hover{background:#eef2ff}
    aside.sidebar .menu img{width:24px; height:auto}
    aside.sidebar .submenu{list-style:none; padding-left:45px; margin:6px 0; display:grid; gap:6px}
    aside.sidebar .help-card{margin-top:16px; padding:12px; border:1px solid var(--border); border-radius:12px; background:#f8fafc; text-align:center; display:grid; gap:8px}
    aside.sidebar .help-card img{width:48px; margin:0 auto}
    aside.sidebar .help-card button{border:none; background:var(--primary); color:#fff; padding:8px 12px; border-radius:8px; cursor:pointer; font-weight:600}

    .header{display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px}
    .header h1{margin:0; font-size:26px; color:var(--primary)}
    .btn-row{display:flex; gap:8px; flex-wrap:wrap}

    .subtle{color:var(--subtle); font-weight:600}

    /* Tabs */
    .tabs{display:flex; gap:10px; flex-wrap:wrap; margin:12px 0 16px}
    .tab-btn{padding:10px 14px; border:1px solid var(--border); background:#fff; border-radius:999px; font-weight:600; cursor:pointer}
    .tab-btn.active{background:var(--primary); color:#fff; border-color:var(--primary); box-shadow:0 6px 16px rgba(37,99,235,.25)}
    .tab{display:none} .tab.active{display:block; animation:fade .25s ease}
    @keyframes fade{from{opacity:0; transform:translateY(8px)} to{opacity:1; transform:translateY(0)}}

    /* Layout & Cards */
    .grid{display:grid; gap:14px}
    .cols-2{grid-template-columns:1fr 1fr}
    .cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}
    .cols-4{grid-template-columns:repeat(4,minmax(0,1fr))}
    .cols-5{grid-template-columns:repeat(5,minmax(0,1fr))}
    @media (max-width: 1200px){.cols-5{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width: 900px){.cols-4,.cols-3,.cols-2,.cols-5{grid-template-columns:1fr}}

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

    .stat{display:flex; align-items:center; justify-content:space-between}
    .stat .big{font-size:28px; font-weight:800}
    .chip{display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; background:var(--chip); color:var(--chiptext)}
    .badge{display:inline-block; padding:4px 8px; border-radius:8px; font-size:12px}
    .b-ok{background:#ecfdf5; color:#065f46}
    .b-warn{background:#fffbeb; color:#92400e}
    .b-bad{background:#fef2f2; color:#991b1b}

    table{width:100%; border-collapse:separate; border-spacing:0; border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff}
    th,td{padding:10px 12px; border-bottom:1px solid var(--border); font-size:14px; text-align:left}
    thead th{background:#f8fafc; font-weight:700; color:#334155}
    tbody tr:hover{background:#f9fafb}
    .tag{display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; background:#eef2ff; color:#3730a3}
    .tag.green{background:#ecfdf5; color:#065f46}
    .tag.yellow{background:#fffbeb; color:#92400e}
    .tag.red{background:#fef2f2; color:#991b1b}
    .tag.gray{background:#f3f4f6; color:#374151}

    .toast{position:fixed; right:24px; bottom:24px; padding:12px 16px; background:#111827; color:#fff; border-radius:10px; box-shadow:var(--shadow); opacity:0; transform:translateY(10px); transition:.2s}
    .toast.show{opacity:1; transform:translateY(0)}
  </style>
</head>
<body>
<div class="container">
  <!-- Inline Sidebar with Payment active linking to Admin_payment.php -->
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
      <li><a href="Admin_payment.php" class="active"><img src="Image/wallet.png" alt="" style="width: 40px" />Payment Method</a></li>
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
      <h1>🏦 Admin Payments</h1>
      <div class="btn-row">
        <button class="btn secondary" id="exportAll">⬇️ Export All CSV</button>
        <button class="btn" id="newInvoiceBtn">➕ New Invoice</button>
      </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid cols-4" id="overview">
      <div class="card stat">
        <div>
          <div class="subtle">Processing Volume (MTD)</div>
          <div class="big" id="ov_mtd">—</div>
        </div>
        <span class="chip" id="ov_paid_count">0 payments</span>
      </div>
      <div class="card stat">
        <div>
          <div class="subtle">Accounts Receivable (Open)</div>
          <div class="big" id="ov_ar">—</div>
        </div>
        <span class="chip" id="ov_overdue">Overdue: —</span>
      </div>
      <div class="card stat">
        <div>
          <div class="subtle">Pending Payouts</div>
          <div class="big" id="ov_payouts">—</div>
        </div>
        <button class="btn" id="markAllPayouts">Mark All Sent</button>
      </div>
      <div class="card stat">
        <div>
          <div class="subtle">Failed Charges (30d)</div>
          <div class="big" id="ov_failed">0</div>
        </div>
        <button class="btn ghost" id="simulateFail">Simulate Fail</button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab-btn active" data-tab="invoices">Invoices & Charges</button>
      <button class="tab-btn" data-tab="payouts">Payouts</button>
      <button class="tab-btn" data-tab="customers">Customers & Methods</button>
    </div>

    <!-- INVOICES TAB -->
    <section id="invoices" class="tab active">
      <div class="grid cols-2">
        <div class="card">
          <h2>Invoices</h2>
          <div class="grid cols-3" style="align-items:end">
            <div><label>Status</label>
              <select id="f_inv_status"><option value="">All</option><option>open</option><option>paid</option><option>overdue</option><option>refunded</option><option>void</option></select>
            </div>
            <div><label>Customer</label><select id="f_inv_customer"></select></div>
            <div class="btn-row"><button class="btn secondary" id="applyInvFilter">Apply</button><button class="btn" id="clearInvFilter">Clear</button></div>
          </div>
          <table id="invTable"><thead><tr><th>Code</th><th>Date</th><th>Due</th><th>Customer</th><th>Amount</th><th>Status</th><th style="width:260px">Actions</th></tr></thead><tbody></tbody></table>
          <div class="btn-row" style="margin-top:8px"><button class="btn secondary" id="exportInv">Export CSV</button></div>
        </div>

        <div class="card">
          <h2>Manual Charge / Refund</h2>
          <label>Customer</label><select id="mc_customer"></select>
          <label>Amount</label><input id="mc_amount" placeholder="e.g., 200" inputmode="decimal" />
          <label>Description</label><input id="mc_desc" placeholder="Adjustment or quick charge" />
          <div class="btn-row" style="margin-top:8px">
            <button class="btn" id="mc_charge">💳 Charge</button>
            <button class="btn danger" id="mc_refund">↩️ Refund</button>
          </div>
          <div class="help">Charges allocate to oldest open invoices for that customer (demo).</div>
        </div>
      </div>
    </section>

    <!-- PAYOUTS TAB -->
    <section id="payouts" class="tab">
      <div class="grid cols-2">
        <div class="card">
          <h2>Payouts</h2>
          <table id="poTable"><thead><tr><th>Date</th><th>Destination</th><th>Amount</th><th>Status</th><th style="width:220px">Actions</th></tr></thead><tbody></tbody></table>
          <div class="btn-row" style="margin-top:8px"><button class="btn secondary" id="exportPayouts">Export CSV</button></div>
        </div>
        <div class="card">
          <h2>Create Payout</h2>
          <label>Destination (Bank/Wallet)</label><input id="po_to" placeholder="DBBL — Main"/>
          <label>Amount</label><input id="po_amount" placeholder="e.g., 1500" inputmode="decimal"/>
          <label>Note</label><input id="po_note" placeholder="Weekly settlement"/>
          <div class="btn-row" style="margin-top:8px"><button class="btn" id="po_create">➕ Create</button></div>
          <div class="help">Demo only — no external transfers are made.</div>
        </div>
      </div>
    </section>

    <!-- CUSTOMERS TAB -->
    <section id="customers" class="tab">
      <div class="grid cols-2">
        <!-- Directory -->
        <div class="card">
          <h2>Customers</h2>
          <table id="custTable"><thead><tr><th>Name</th><th>Email</th><th>Due</th><th>Default Method</th><th style="width:180px">Actions</th></tr></thead><tbody></tbody></table>
        </div>
        <!-- Methods & actions for selected customer -->
        <div class="card">
          <h2 id="cm_title">Methods</h2>
          <div class="seg" id="cm_seg">
            <button data-kind="card" class="active">Card</button>
            <button data-kind="wallet">Wallet</button>
            <button data-kind="bank">Bank</button>
          </div>
          <div id="cm_form_card">
            <label>Label</label><input id="cm_c_label" placeholder="Corporate Visa"/>
            <div class="grid cols-3">
              <div><label>Card Number</label><input id="cm_c_number" placeholder="4242 4242 4242 4242" maxlength="19"/></div>
              <div><label>Expiry (MM/YY)</label><input id="cm_c_exp" placeholder="12/27" maxlength="5"/></div>
              <div><label>CVC</label><input id="cm_c_cvc" placeholder="123" maxlength="4"/></div>
            </div>
            <div class="btn-row" style="margin-top:8px"><button class="btn" id="cm_add_card">➕ Add Card</button></div>
          </div>
          <div id="cm_form_wallet" style="display:none">
            <div class="grid cols-2"><div><label>Provider</label><select id="cm_w_provider"><option>bKash</option><option>Nagad</option><option>Rocket</option><option>Upay</option></select></div><div><label>Number</label><input id="cm_w_number" placeholder="+8801XXXXXXXXX"/></div></div>
            <div class="btn-row" style="margin-top:8px"><button class="btn" id="cm_add_wallet">➕ Link Wallet</button></div>
          </div>
          <div id="cm_form_bank" style="display:none">
            <div class="grid cols-2"><div><label>Account Name</label><input id="cm_b_name" placeholder="Acme Ltd."/></div><div><label>Account Number</label><input id="cm_b_ac" placeholder="XXXXXXXXXXXX"/></div></div>
            <div class="grid cols-2"><div><label>Bank & Branch</label><input id="cm_b_branch" placeholder="DBBL, Dhanmondi"/></div><div><label>SWIFT (optional)</label><input id="cm_b_swift" placeholder="XXXXXX"/></div></div>
            <div class="btn-row" style="margin-top:8px"><button class="btn" id="cm_add_bank">➕ Add Bank</button></div>
          </div>

          <h3 style="margin-top:14px">Saved Methods</h3>
          <table id="cm_methods"><thead><tr><th>Type</th><th>Label</th><th>Details</th><th>Default</th><th style="width:200px">Actions</th></tr></thead><tbody></tbody></table>
          <div class="btn-row" style="margin-top:8px">
            <button class="btn" id="cm_charge_due">💸 Charge Full Due</button>
            <button class="btn secondary" id="cm_new_invoice">➕ New Invoice</button>
          </div>
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
  const K_CUST='ap_customers';
  const K_INV='ap_invoices';
  const K_PAYOUT='ap_payouts';
  const K_TX='ap_tx';

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

  // ===== Seed Data =====
  (function seed(){
    if(!get(K_CUST)){
      set(K_CUST,[
        {id:'c1', name:'Acme Logistics', email:'billing@acme.com', currency:'USD', methods:[{id:'c1m1', type:'card', label:'Acme Visa', details:{brand:'Visa', last4:'4242', exp:'12/27'}, isDefault:true}]},
        {id:'c2', name:'Rajshahi Traders', email:'finance@rajtraders.bd', currency:'USD', methods:[{id:'c2m1', type:'wallet', label:'bKash', details:{provider:'bKash', last4:'1122'}, isDefault:true}]},
        {id:'c3', name:'Sylhet Foods', email:'ap@sylhetfoods.com', currency:'USD', methods:[{id:'c3m1', type:'bank', label:'DBBL — Main', details:{bank:'DBBL', last4:'7788'}, isDefault:true}]},
      ]);
    }
    if(!get(K_INV)){
      const today=new Date();
      const fmt=(d)=> new Date(d).toISOString().slice(0,10);
      set(K_INV,[
        {id:'i1', code:'HP-INV-2001', customerId:'c1', date:fmt(today), dueDate:fmt(new Date(today.getTime()+6*864e5)), amount:250.00, currency:'USD', status:'open', desc:'Self-use Truck Rental — 2 days'},
        {id:'i2', code:'HP-INV-2002', customerId:'c1', date:fmt(new Date(today-10*864e5)), dueDate:fmt(new Date(today-3*864e5)), amount:120.00, currency:'USD', status:'overdue', desc:'Fuel & Toll Adjustments'},
        {id:'i3', code:'HP-INV-2003', customerId:'c2', date:fmt(new Date(today-2*864e5)), dueDate:fmt(new Date(today+10*864e5)), amount:89.99, currency:'USD', status:'open', desc:'Trip Assistance Fee'},
        {id:'i4', code:'HP-INV-2004', customerId:'c3', date:fmt(new Date(today-20*864e5)), dueDate:fmt(new Date(today-15*864e5)), amount:600.00, currency:'USD', status:'paid', paidAt:nowStr(), method:'DBBL — Main', desc:'Monthly Subscription'},
      ]);
    }
    if(!get(K_PAYOUT)){
      set(K_PAYOUT,[
        {id:'p1', date:new Date().toISOString().slice(0,10), to:'DBBL — Ops', amount:1500, currency:'USD', status:'pending', note:'Weekly settlement'},
      ]);
    }
    if(!get(K_TX)){
      set(K_TX,[
        {t:nowStr(), type:'charge', ref:'HP-INV-2004', amount:money(600,'USD'), status:'ok', method:'DBBL — Main'},
      ]);
    }
  })();

  // ===== Helpers (lookup) =====
  const byId = (arr,id)=> arr.find(x=>x.id===id);
  const custById = (id)=> byId(get(K_CUST,[]), id);
  const invForCust = (cid)=> get(K_INV,[]).filter(i=> i.customerId===cid);

  // ===== Overview =====
  function calcOverview(){
    const inv=get(K_INV,[]);
    const tx=get(K_TX,[]);
    const payouts=get(K_PAYOUT,[]);
    const month=(new Date()).toISOString().slice(0,7);
    const paidThisMonth = tx
      .filter(r=> r.type==='charge' && r.status==='ok' && (r.t||'').includes(month))
      .reduce((s,r)=> s + Number(String(r.amount).replace(/[^0-9.\-]/g,'')), 0);
    const openAR = inv.filter(i=> i.status==='open' || i.status==='overdue')
      .reduce((s,i)=> s + Number(i.amount||0), 0);
    const overdue = inv.filter(i=> i.status==='overdue')
      .reduce((s,i)=> s + Number(i.amount||0), 0);
    const pendingPayouts = payouts.filter(p=> p.status==='pending')
      .reduce((s,p)=> s + Number(p.amount||0), 0);
    const failed30 = tx.filter(r=> r.status==='failed').length;
    return {paidThisMonth, openAR, overdue, pendingPayouts, paidCount: tx.filter(r=> r.type==='charge' && r.status==='ok').length, failed30};
  };
  }

  function renderOverview(){
    const {paidThisMonth, openAR, overdue, pendingPayouts, paidCount, failed30} = calcOverview();
    ov_mtd.textContent = money(paidThisMonth, 'USD');
    ov_paid_count.textContent = paidCount + ' payments';
    ov_ar.textContent = money(openAR,'USD');
    ov_overdue.textContent = 'Overdue: ' + money(overdue,'USD');
    ov_payouts.textContent = money(pendingPayouts,'USD');
    ov_failed.textContent = failed30;
  }

  // Auto-mark overdue invoices based on dueDate
  function refreshOverdue(){
    const inv=get(K_INV,[]); const today=new Date().toISOString().slice(0,10);
    let changed=false; inv.forEach(i=>{ if((i.status==='open'||i.status==='overdue') && i.dueDate && i.dueDate<today){ if(i.status!=='overdue'){ i.status='overdue'; changed=true; } } });
    if(changed) set(K_INV,inv);
  }

  // ===== Invoices =====
  function nextInvCode(){
    const inv=get(K_INV,[]); const nums=inv.map(i=> parseInt(String(i.code).split('-').pop()||'0',10)).filter(n=>!isNaN(n));
    const next = (nums.length? Math.max(...nums)+1 : 1001); return 'HP-INV-' + next;
  }

  function renderInvFilters(){
    const sel=f_inv_customer; sel.innerHTML='<option value="">All</option>';
    get(K_CUST,[]).forEach(c=>{ const o=document.createElement('option'); o.value=c.id; o.textContent=c.name; sel.appendChild(o); });

    const sel2=mc_customer; sel2.innerHTML=''; get(K_CUST,[]).forEach(c=>{ const o=document.createElement('option'); o.value=c.id; o.textContent=c.name; sel2.appendChild(o); });
  }

  function renderInvoices(filter={}){
    const tb=document.querySelector('#invTable tbody'); tb.innerHTML='';
    let rows=[...get(K_INV,[])];
    if(filter.status) rows=rows.filter(i=> i.status===filter.status);
    if(filter.customerId) rows=rows.filter(i=> i.customerId===filter.customerId);

    rows.sort((a,b)=> (a.date>b.date?-1:1));

    rows.forEach(i=>{
      const c=custById(i.customerId);
      const tr=document.createElement('tr');
      tr.innerHTML=`<td>${i.code}</td><td>${i.date}</td><td>${i.dueDate}</td><td>${c?c.name:'—'}</td><td>${money(i.amount,i.currency||'USD')}</td>
        <td>${i.status==='open'?'<span class="tag yellow">open</span>': i.status==='overdue'?'<span class="tag red">overdue</span>': i.status==='paid'?'<span class="tag green">paid</span>':'<span class="tag gray">'+i.status+'</span>'}</td>
        <td>
          ${i.status==='open'||i.status==='overdue'?`<button class="btn secondary invCharge" data-id="${i.id}">Charge</button> <button class="btn" data-id="${i.id}" data-act="markPaid">Mark Paid</button> <button class="btn ghost" data-id="${i.id}" data-act="void">Void</button>`:''}
          ${i.status==='paid'?`<button class="btn danger invRefund" data-id="${i.id}">Refund</button> <button class="btn secondary invReceipt" data-id="${i.id}">Receipt</button>`:''}
        </td>`;
      tb.appendChild(tr);
    });

    // Actions
    tb.querySelectorAll('.invCharge').forEach(btn=> btn.addEventListener('click',()=> chargeInvoice(btn.dataset.id)) );
    tb.querySelectorAll('[data-act="markPaid"]').forEach(btn=> btn.addEventListener('click',()=> markInvoicePaid(btn.dataset.id)) );
    tb.querySelectorAll('[data-act="void"]').forEach(btn=> btn.addEventListener('click',()=> voidInvoice(btn.dataset.id)) );
    tb.querySelectorAll('.invRefund').forEach(btn=> btn.addEventListener('click',()=> refundInvoice(btn.dataset.id)) );
    tb.querySelectorAll('.invReceipt').forEach(btn=> btn.addEventListener('click',()=> downloadReceiptForInvoice(btn.dataset.id)) );
  }

  function addInvoice(custId, amount, desc){
    const inv=get(K_INV,[]);
    const today=new Date().toISOString().slice(0,10);
    const due=new Date(Date.now()+7*864e5).toISOString().slice(0,10);
    const code=nextInvCode();
    inv.push({id:'i'+Math.random().toString(36).slice(2,8), code, customerId:custId, date:today, dueDate:due, amount:Number(amount||0), currency:'USD', status:'open', desc:desc||''});
    set(K_INV,inv); renderInvoices(); renderOverview(); renderCustomers(); toast('Invoice created');
  }

  function markInvoicePaid(id, methodLabel){
    const inv=get(K_INV,[]); const i=byId(inv,id); if(!i) return;
    i.status='paid'; i.paidAt=nowStr(); if(methodLabel) i.method=methodLabel;
    set(K_INV,inv);
    const tx=get(K_TX,[]); tx.unshift({t:nowStr(), type:'charge', ref:i.code, amount:money(i.amount,i.currency||'USD'), status:'ok', method:methodLabel||'manual'}); set(K_TX,tx);
    renderInvoices(); renderOverview(); renderCustomers(); toast('Invoice marked paid');
  }

  function voidInvoice(id){
    const inv=get(K_INV,[]); const i=byId(inv,id); if(!i) return; if(!confirm('Void this invoice?')) return; i.status='void'; set(K_INV,inv); renderInvoices(); renderOverview(); renderCustomers(); toast('Invoice voided');
  }

  function chargeInvoice(id){
    const inv=get(K_INV,[]); const i=byId(inv,id); if(!i) return;
    const cust=custById(i.customerId); if(!cust){ alert('Customer not found'); return; }
    const def=(cust.methods||[]).find(m=>m.isDefault) || (cust.methods||[])[0];
    if(!def){ alert('Customer has no payment method'); return; }
    i.status='paid'; i.paidAt=nowStr(); i.method=def.label||def.type;
    set(K_INV,inv);
    const tx=get(K_TX,[]); tx.unshift({t:nowStr(), type:'charge', ref:i.code, amount:money(i.amount,i.currency||'USD'), status:'ok', method:i.method}); set(K_TX,tx);
    renderInvoices(); renderOverview(); renderCustomers(); toast('Charged via '+(i.method));
  }

  function refundInvoice(id){
    const inv=get(K_INV,[]); const i=byId(inv,id); if(!i) return; if(i.status!=='paid'){ alert('Only paid invoices can be refunded'); return; }
    const amt = prompt('Refund amount', String(i.amount)); if(amt===null) return; const n=parseFloat(amt); if(!(n>0)){ alert('Invalid amount'); return; }
    const tx=get(K_TX,[]); tx.unshift({t:nowStr(), type:'refund', ref:i.code, amount:'-'+money(n,i.currency||'USD'), status:'ok', method:i.method||'—'}); set(K_TX,tx);
    i.status = (n>=i.amount)? 'refunded' : 'paid'; set(K_INV,inv);
    renderInvoices(); renderOverview(); renderCustomers(); toast('Refund issued');
  }

  // Manual charge/refund panel
  mc_charge.addEventListener('click',()=>{
    const cid=mc_customer.value; const amt=parseFloat(mc_amount.value||'0'); const desc=mc_desc.value.trim();
    if(!cid||!(amt>0)){ alert('Select customer and enter amount'); return; }
    // Allocate oldest-first across that customer's open/overdue invoices
    const rows=[...get(K_INV,[])].sort((a,b)=> (a.date<b.date?-1:1));
    let remain=amt; for(const row of rows){
      if(row.customerId!==cid) continue;
      if(row.status==='open' || row.status==='overdue'){
        const a=Number(row.amount)||0;
        if(remain>=a){ row.status='paid'; row.paidAt=nowStr(); row.method='manual'; remain-=a; }
        else if(remain>0){ row.amount=(a-remain).toFixed(2); row.paidAt=nowStr(); row.method='manual (partial)'; row.status='open'; remain=0; break; }
      }
    }
    set(K_INV,rows);
    const tx=get(K_TX,[]); tx.unshift({t:nowStr(), type:'charge', ref:desc||'manual', amount:money(amt,'USD'), status:'ok', method:'manual'}); set(K_TX,tx);
    renderInvoices(); renderOverview(); renderCustomers(); mc_amount.value=''; mc_desc.value=''; toast('Manual charge applied (demo)');
  }); const desc=mc_desc.value.trim(); if(!cid||!(amt>0)){ alert('Select customer and enter amount'); return; }
    // Allocate to oldest open/overdue invoices
    let inv=get(K_INV,[]).sort((a,b)=> (a.date<b.date?-1:1));
    for(const row of inv){ if(row.customerId!==cid) continue; if(row.status==='open' || row.status==='overdue'){ if(amt>=row.amount){ row.status='paid'; row.paidAt=nowStr(); row.method='manual'; } } }
    set(K_INV,inv);
    const tx=get(K_TX,[]); tx.unshift({t:nowStr(), type:'charge', ref:desc||'manual', amount:money(amt,'USD'), status:'ok', method:'manual'}); set(K_TX,tx);
    renderInvoices(); renderOverview(); renderCustomers(); mc_amount.value=''; mc_desc.value=''; toast('Manual charge applied (demo)');
  });
  mc_refund.addEventListener('click',()=>{
    const cid=mc_customer.value; const amt=parseFloat(mc_amount.value||'0'); const desc=mc_desc.value.trim(); if(!cid||!(amt>0)){ alert('Select customer and enter amount'); return; }
    const tx=get(K_TX,[]); tx.unshift({t:nowStr(), type:'refund', ref:desc||'manual', amount:'-'+money(amt,'USD'), status:'ok', method:'manual'}); set(K_TX,tx);
    renderOverview(); toast('Refund recorded (demo)'); mc_amount.value=''; mc_desc.value='';
  });

  // Export invoices
  exportInv.addEventListener('click',()=>{
    const rows=get(K_INV,[]); const csv=['code,date,due,customer,amount,currency,status,paidAt,method,desc']
      .concat(rows.map(i=>`"${i.code}","${i.date}","${i.dueDate}","${(custById(i.customerId)||{}).name||''}","${i.amount}","${i.currency}","${i.status}","${i.paidAt||''}","${i.method||''}","${(i.desc||'').replace(/"/g,'""')}"`)).join('\n');
    const blob=new Blob([csv],{type:'text/csv'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='invoices.csv'; a.click();
  });

  // ===== Payouts =====
  function renderPayouts(){
    const tb=document.querySelector('#poTable tbody'); tb.innerHTML='';
    get(K_PAYOUT,[]).forEach(p=>{
      const tr=document.createElement('tr');
      tr.innerHTML = `<td>${p.date}</td><td>${p.to}</td><td>${money(p.amount,p.currency||'USD')}</td><td>${p.status==='pending'?'<span class="tag yellow">pending</span>':p.status==='sent'?'<span class="tag green">sent</span>':'<span class="tag red">failed</span>'}</td>
        <td>
          ${p.status!=='sent'?`<button class="btn" data-id="${p.id}" data-act="sent">Mark Sent</button>`:''}
          ${p.status!=='failed'?`<button class="btn danger" data-id="${p.id}" data-act="failed">Mark Failed</button>`:''}
          <button class="btn secondary" data-id="${p.id}" data-act="del">Delete</button>
        </td>`;
      tb.appendChild(tr);
    });
    tb.querySelectorAll('[data-act]').forEach(btn=>{
      btn.addEventListener('click',()=>{
        const id=btn.dataset.id; const act=btn.dataset.act; const rows=get(K_PAYOUT,[]); const p=byId(rows,id); if(!p) return;
        if(act==='del'){ if(!confirm('Delete payout?')) return; const idx=rows.findIndex(x=>x.id===id); rows.splice(idx,1); }
        else if(act==='sent'){ p.status='sent'; }
        else if(act==='failed'){ p.status='failed'; }
        set(K_PAYOUT,rows); renderPayouts(); renderOverview(); toast('Payout updated');
      });
    });
  }

  po_create.addEventListener('click',()=>{
    const to=po_to.value.trim(); const amt=parseFloat(po_amount.value||'0'); const note=po_note.value.trim(); if(!to||!(amt>0)){ alert('Enter destination and amount'); return; }
    const rows=get(K_PAYOUT,[]); rows.push({id:'p'+Math.random().toString(36).slice(2,8), date:new Date().toISOString().slice(0,10), to, amount:amt, currency:'USD', status:'pending', note}); set(K_PAYOUT,rows);
    po_to.value=po_amount.value=po_note.value=''; renderPayouts(); renderOverview(); toast('Payout created');
  });

  exportPayouts.addEventListener('click',()=>{
    const rows=get(K_PAYOUT,[]); const csv=['date,to,amount,currency,status,note']
      .concat(rows.map(p=>`"${p.date}","${p.to}","${p.amount}","${p.currency}","${p.status}","${(p.note||'').replace(/"/g,'""')}"`)).join('\n');
    const blob=new Blob([csv],{type:'text/csv'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='payouts.csv'; a.click();
  });

  markAllPayouts.addEventListener('click',()=>{ const rows=get(K_PAYOUT,[]).map(p=>({...p, status:'sent'})); set(K_PAYOUT,rows); renderPayouts(); renderOverview(); toast('All payouts marked sent'); });
  simulateFail.addEventListener('click',()=>{ const tx=get(K_TX,[]); tx.unshift({t:nowStr(), type:'charge', ref:'simulated', amount:money(1,'USD'), status:'failed'}); set(K_TX,tx); renderOverview(); toast('Simulated failed charge'); });

  // ===== Customers & Methods =====
  let selectedCustomer=null;

  function renderCustomers(){
    const tb=document.querySelector('#custTable tbody'); tb.innerHTML='';
    get(K_CUST,[]).forEach(c=>{
      const inv=invForCust(c.id);
      const due=inv.filter(i=> i.status==='open' || i.status==='overdue').reduce((s,i)=> s + Number(i.amount||0), 0);
      const def=(c.methods||[]).find(m=>m.isDefault) || (c.methods||[])[0];
      const det = def? (def.label || (def.type==='card'? def.details.brand+' •••• '+def.details.last4 : def.type)) : '—';
      const tr=document.createElement('tr');
      tr.innerHTML=`<td>${c.name}</td><td>${c.email}</td><td>${money(due,c.currency||'USD')}</td><td>${det}</td>
        <td><button class="btn secondary selCust" data-id="${c.id}">Manage</button> <button class="btn" data-id="${c.id}" data-act="invoice">New Invoice</button></td>`;
      tb.appendChild(tr);
    });

    tb.querySelectorAll('.selCust').forEach(btn=> btn.addEventListener('click',()=> selectCustomer(btn.dataset.id)) );
    tb.querySelectorAll('[data-act="invoice"]').forEach(btn=> btn.addEventListener('click',()=>{ selectCustomer(btn.dataset.id); document.querySelector('.tab-btn[data-tab="invoices"]').click(); newInvoiceDialog(btn.dataset.id); }) );

    // Fill filters selects too
    renderInvFilters();
  }

  function selectCustomer(id){ selectedCustomer=id; const c=custById(id); cm_title.textContent='Methods — '+(c?c.name:''); renderCustomerMethods(); }

  function renderCustomerMethods(){
    const c=custById(selectedCustomer); if(!c){ document.querySelector('#cm_methods tbody').innerHTML=''; return; }
    const tb=document.querySelector('#cm_methods tbody'); tb.innerHTML='';
    (c.methods||[]).forEach((m,idx)=>{
      const det = m.type==='card'? `${m.details.brand} •••• ${m.details.last4} (${m.details.exp||'—'})` : m.type==='wallet'? `${m.details.provider} •••• ${m.details.last4}` : `${m.details.bank||'Bank'} •••• ${m.details.last4}`;
      const tr=document.createElement('tr');
      tr.innerHTML=`<td>${m.type}</td><td><input class="cmLabel" data-idx="${idx}" value="${m.label||''}" style="width:220px"/></td><td>${det}</td><td>${m.isDefault?'<span class="tag green">Default</span>':'<span class="tag gray">—</span>'}</td>
        <td>${m.isDefault?'':`<button class="btn secondary cmSetDef" data-idx="${idx}">Set Default</button>`} <button class="btn danger cmDel" data-idx="${idx}">Remove</button></td>`;
      tb.appendChild(tr);
    });
    tb.querySelectorAll('.cmLabel').forEach(inp=> inp.addEventListener('change',e=>{ const i=+e.target.dataset.idx; const c=custById(selectedCustomer); c.methods[i].label=e.target.value; commitCustomer(c); renderCustomerMethods(); toast('Label updated'); }));
    tb.querySelectorAll('.cmSetDef').forEach(btn=> btn.addEventListener('click',()=>{ const i=+btn.dataset.idx; const c=custById(selectedCustomer); (c.methods||[]).forEach((m,ix)=> m.isDefault=(ix===i)); commitCustomer(c); renderCustomerMethods(); toast('Default set'); }));
    tb.querySelectorAll('.cmDel').forEach(btn=> btn.addEventListener('click',()=>{ const i=+btn.dataset.idx; const c=custById(selectedCustomer); if(!confirm('Remove method?')) return; c.methods.splice(i,1); commitCustomer(c); renderCustomerMethods(); toast('Method removed'); }));
  }

  function commitCustomer(updated){ const rows=get(K_CUST,[]); const idx=rows.findIndex(x=>x.id===updated.id); rows[idx]=updated; set(K_CUST,rows); renderCustomers(); }

  // Method form switching
  const cmForms={card:cm_form_card, wallet:cm_form_wallet, bank:cm_form_bank};
  cm_seg.addEventListener('click', (e)=>{ if(e.target.tagName!=='BUTTON') return; cm_seg.querySelectorAll('button').forEach(b=>b.classList.remove('active')); e.target.classList.add('active'); const k=e.target.dataset.kind; Object.keys(cmForms).forEach(x=> cmForms[x].style.display=(x===k?'block':'none')); });

  // Add methods
  cm_add_card.addEventListener('click',()=>{ const c=custById(selectedCustomer); if(!c){ alert('Select a customer'); return; } const num=cm_c_number.value.replace(/\D/g,''); if(num.length<12){ alert('Enter full card number'); return; } const last4=fmtLast4(num); const brand=(/^4/.test(num)?'Visa':/^5[1-5]/.test(num)?'Mastercard':'Card'); const m={id:'m'+Math.random().toString(36).slice(2,8), type:'card', label:cm_c_label.value||brand+' •••• '+last4, details:{brand,last4,exp:fmtExp(cm_c_exp.value)}, isDefault:!(c.methods&&c.methods.length)}; c.methods=c.methods||[]; c.methods.push(m); commitCustomer(c); cm_c_label.value=cm_c_number.value=cm_c_exp.value=cm_c_cvc.value=''; renderCustomerMethods(); toast('Card added'); });
  cm_add_wallet.addEventListener('click',()=>{ const c=custById(selectedCustomer); if(!c){ alert('Select a customer'); return; } const prov=cm_w_provider.value; const num=cm_w_number.value.trim(); if(!num){ alert('Enter wallet number'); return; } const last4=fmtLast4(num); const m={id:'m'+Math.random().toString(36).slice(2,8), type:'wallet', label:prov, details:{provider:prov, last4}, isDefault:!(c.methods&&c.methods.length)}; c.methods=c.methods||[]; c.methods.push(m); commitCustomer(c); cm_w_number.value=''; renderCustomerMethods(); toast('Wallet linked'); });
  cm_add_bank.addEventListener('click',()=>{ const c=custById(selectedCustomer); if(!c){ alert('Select a customer'); return; } const name=cm_b_name.value.trim(), ac=cm_b_ac.value.trim(); if(!name||!ac){ alert('Enter account name & number'); return; } const last4=fmtLast4(ac); const m={id:'m'+Math.random().toString(36).slice(2,8), type:'bank', label:(name+' — '+(cm_b_branch.value||'Bank')), details:{bank:(cm_b_branch.value||'Bank'), last4}, isDefault:!(c.methods&&c.methods.length)}; c.methods=c.methods||[]; c.methods.push(m); commitCustomer(c); cm_b_name.value=cm_b_ac.value=cm_b_branch.value=cm_b_swift.value=''; renderCustomerMethods(); toast('Bank added'); });

  cm_charge_due.addEventListener('click',()=>{
    if(!selectedCustomer){ alert('Select a customer'); return; }
    const c=custById(selectedCustomer); const def=(c.methods||[]).find(m=>m.isDefault) || (c.methods||[])[0]; if(!def){ alert('No method for customer'); return; }
    let all=get(K_INV,[]);
    const targets=all.filter(i=> i.customerId===selectedCustomer && (i.status==='open'||i.status==='overdue'));
    const total=targets.reduce((s,i)=> s + Number(i.amount||0), 0);
    if(total<=0){ toast('No due for this customer'); return; }
    targets.forEach(i=>{ i.status='paid'; i.paidAt=nowStr(); i.method=def.label||def.type; });
    set(K_INV,all);
    const tx=get(K_TX,[]); tx.unshift({t:nowStr(), type:'charge', ref:'bulk due ('+c.name+')', amount:money(total,'USD'), status:'ok', method:def.label||def.type}); set(K_TX,tx);
    renderInvoices(); renderOverview(); renderCustomers(); toast('Charged '+c.name+' '+money(total,'USD'));
  }); return; } const inv=invForCust(selectedCustomer).filter(i=> i.status==='open'||i.status==='overdue'); const total=inv.reduce((s,i)=> s + Number(i.amount||0), 0); if(total<=0){ toast('No due for this customer'); return; } // pick default method
    const c=custById(selectedCustomer); const def=(c.methods||[]).find(m=>m.isDefault) || (c.methods||[])[0]; if(!def){ alert('No method for customer'); return; }
    inv.forEach(i=>{ i.status='paid'; i.paidAt=nowStr(); i.method=def.label||def.type; }); set(K_INV,get(K_INV,[])); const tx=get(K_TX,[]); tx.unshift({t:nowStr(), type:'charge', ref:'bulk due ('+c.name+')', amount:money(total,'USD'), status:'ok', method:def.label||def.type}); set(K_TX,tx); renderInvoices(); renderOverview(); renderCustomers(); toast('Charged '+c.name+' '+money(total,'USD')); });

  cm_new_invoice.addEventListener('click',()=>{ if(!selectedCustomer){ alert('Select a customer'); return; } document.querySelector('.tab-btn[data-tab="invoices"]').click(); newInvoiceDialog(selectedCustomer); });

  // ===== New Invoice Dialog (simple prompt-based for demo) =====
  function newInvoiceDialog(prefId){ const cid = prefId || prompt('Customer ID (e.g., c1)'); if(!cid) return; const c=custById(cid); if(!c){ alert('Customer not found'); return; } const amt=prompt('Amount', '100'); if(amt===null) return; const desc=prompt('Description', 'Rental/Service'); addInvoice(cid, parseFloat(amt||'0'), desc); }
  newInvoiceBtn.addEventListener('click',()=> newInvoiceDialog() );

  // ===== Receipts =====
  function receiptHTML(i){ const c=custById(i.customerId)||{}; return `<!DOCTYPE html><html><head><meta charset="utf-8"><title>${i.code} Receipt</title>
    <style>body{font-family:Inter,Segoe UI,sans-serif;padding:24px;color:#0f172a}.box{border:1px solid #e5e7eb;border-radius:12px;padding:16px;max-width:720px}
      h1{margin:0 0 8px;font-size:22px;color:#2563eb}.row{display:flex;gap:20px;flex-wrap:wrap}.row>div{flex:1 1 240px}.muted{color:#64748b}
      table{width:100%;border-collapse:collapse;margin-top:12px}th,td{padding:8px;border-bottom:1px solid #e5e7eb;text-align:left}th{background:#f8fafc}</style>
    </head><body><div class="box"><h1>Payment Receipt — ${i.code}</h1>
      <div class="row"><div><div class="muted">Customer</div><div>${c.name||'—'}</div></div>
        <div><div class="muted">Date</div><div>${i.paidAt||'—'}</div></div>
        <div><div class="muted">Amount</div><div>${money(i.amount,i.currency||'USD')}</div></div>
        <div><div class="muted">Method</div><div>${i.method||'—'}</div></div></div>
      <table><thead><tr><th>Description</th><th>Status</th></tr></thead><tbody><tr><td>${(i.desc||'—')}</td><td>paid</td></tr></tbody></table>
    </div></body></html>`; }

  function downloadReceiptForInvoice(id){ const i=byId(get(K_INV,[]), id); if(!i){ alert('Invoice not found'); return; } const blob=new Blob([receiptHTML(i)],{type:'text/html'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=`receipt_${i.code}.html`; a.click(); }

  // ===== Filters for Invoices =====
  applyInvFilter.addEventListener('click',()=> renderInvoices({status:f_inv_status.value||'', customerId:f_inv_customer.value||''}) );
  clearInvFilter.addEventListener('click',()=>{ f_inv_status.value=''; f_inv_customer.value=''; renderInvoices({}); });

  // ===== Export all =====
  exportAll.addEventListener('click',()=>{
    const inv=get(K_INV,[]); const payouts=get(K_PAYOUT,[]); const tx=get(K_TX,[]);
    const csv1=['INVOICES','code,date,due,customer,amount,currency,status,paidAt,method,desc']
      .concat(inv.map(i=>`"${i.code}","${i.date}","${i.dueDate}","${(custById(i.customerId)||{}).name||''}","${i.amount}","${i.currency}","${i.status}","${i.paidAt||''}","${i.method||''}","${(i.desc||'').replace(/"/g,'""')}"`))
      .join('\n');
    const csv2=['','PAYOUTS','date,to,amount,currency,status,note']
      .concat(payouts.map(p=>`"${p.date}","${p.to}","${p.amount}","${p.currency}","${p.status}","${(p.note||'').replace(/"/g,'""')}"`))
      .join('\n');
    const csv3=['','TRANSACTIONS','time,type,ref,amount,status,method']
      .concat(tx.map(r=>`"${r.t}","${r.type}","${r.ref}","${r.amount}","${r.status}","${r.method||''}"`))
      .join('\n');
    const blob=new Blob([csv1+'\n'+csv2+'\n'+csv3],{type:'text/csv'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='admin_payments_export.csv'; a.click();
  });

  // ===== Init =====
  function init(){ refreshOverdue(); renderOverview(); renderInvFilters(); renderInvoices(); renderPayouts(); renderCustomers(); selectCustomer('c1'); }
  init();
</script>
</body>
</html>
