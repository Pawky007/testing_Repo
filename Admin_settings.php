<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Settings — HaulPro (Polished)</title>
  <link rel="stylesheet" href="dashboad_style.css" />
  <style>
    :root{
      --primary:#2563eb; --primary-hover:#1d4ed8;
      --bg:#f5f7fb; --surface:#fff; --border:#e5e7eb;
      --text:#0f172a; --muted:#64748b;
      --radius:12px; --shadow:0 4px 12px rgba(0,0,0,0.08);
      --ok:#16a34a; --warn:#f59e0b; --bad:#dc2626;
      --tag:#eef2ff; --tagtext:#3730a3;
      --green:#10b981; --yellow:#f59e0b; --red:#ef4444; --gray:#6b7280;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0; font-family:'Segoe UI',system-ui,-apple-system,sans-serif; background:var(--bg); color:var(--text);}
    .container{display:flex; min-height:100vh; gap:0}
    main{flex:1; padding:28px; max-width:1700px; margin:0 auto}
    .header h1{margin:0 0 20px; font-size:28px; color:var(--primary); display:flex; align-items:center; gap:10px}

    /* Utility */
    .grid{display:grid; gap:14px}
    .grid.cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}
    .grid.cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}
    .grid.cols-4{grid-template-columns:repeat(4,minmax(0,1fr))}
    @media (max-width: 1100px){.grid.cols-4{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width: 860px){.grid.cols-3,.grid.cols-2,.grid.cols-4{grid-template-columns:1fr}}

    .section-title{display:flex; align-items:center; gap:10px; font-size:14px; font-weight:700; color:#334155; letter-spacing:.02em; text-transform:uppercase}
    .subtle{color:var(--muted)}

    /* Tabs */
    .tabs{display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap}
    .tab-btn{padding:10px 16px; border:1px solid var(--border); background:#fff; color:#0f172a; font-weight:600; border-radius:999px; cursor:pointer; transition:.25s}
    .tab-btn:hover{transform:translateY(-1px)}
    .tab-btn.active{background:var(--primary); color:#fff; border-color:var(--primary); box-shadow:0 6px 16px rgba(37,99,235,.25)}
    .tab{display:none;} .tab.active{display:block; animation:fade .25s ease}
    @keyframes fade{from{opacity:0; transform:translateY(8px)} to{opacity:1; transform:translateY(0)}}

    .card{background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); padding:20px}
    .card + .card{margin-top:14px}
    h2{font-size:20px; margin:0 0 16px}
    h3{margin:12px 0 8px}

    label{display:block; font-weight:600; margin-bottom:6px; color:#0f172a}
    .help{margin-top:-6px; margin-bottom:10px; font-size:12px; color:var(--muted)}
    input,select,textarea{width:100%; padding:12px; font-size:15px; border:1px solid var(--border); border-radius:10px; margin-bottom:12px; background:#fff}
    textarea{min-height:110px; resize:vertical}

    .btn{background:var(--primary); color:#fff; padding:10px 16px; border:none; border-radius:10px; font-weight:600; cursor:pointer; transition:.2s; position:relative; white-space:nowrap}
    .btn:hover{background:var(--primary-hover)}
    .btn.secondary{background:#f1f5f9; color:#0f172a; border:1px solid var(--border)}
    .btn.ghost{background:transparent; border:1px dashed var(--border); color:#0f172a}
    .btn.danger{background:#ef4444}
    .btn-row{display:flex; gap:10px; align-items:center; flex-wrap:wrap}

    .btn.unsaved::after{content:"•"; position:absolute; top:-6px; right:-8px; width:16px; height:16px; background:#ef4444; color:#fff; border-radius:999px; display:flex; align-items:center; justify-content:center; font-weight:700}

    /* Inputs layout */
    .row{display:flex; gap:12px; flex-wrap:wrap}
    .col{flex:1 1 240px}
    .col-2{flex:2 1 520px}

    /* Toggles */
    .toggle-row{display:flex; align-items:center; justify-content:space-between; gap:16px; padding:6px 0}
    .toggle-row .label{font-weight:600; color:#0f172a}
    .toggle{display:inline-flex; align-items:center; cursor:pointer; user-select:none}
    .toggle input{position:absolute; opacity:0; width:0; height:0}
    .switch{position:relative; width:54px; height:30px; border-radius:999px; background:#e5e7eb; border:1px solid var(--border); transition:.2s; display:inline-block}
    .switch::after{content:""; position:absolute; top:50%; left:3px; transform:translateY(-50%); width:24px; height:24px; border-radius:999px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.2); transition:.2s}
    .toggle input:checked + .switch{background:var(--primary); border-color:var(--primary)}
    .toggle input:checked + .switch::after{left:27px}

    /* Password meter */
    .meter{height:8px; background:#e5e7eb; border-radius:999px; overflow:hidden; margin:-4px 0 10px}
    .meter > span{display:block; height:100%; width:0%; background:var(--bad); transition:width .25s, background .25s; border-radius:999px}
    .meter.ok > span{background:var(--ok)} .meter.warn > span{background:var(--warn)}
    .hint{font-size:12px; color:var(--muted); margin:-6px 0 12px}

    /* Tables */
    table{width:100%; border-collapse:separate; border-spacing:0; border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff}
    th,td{padding:10px 12px; border-bottom:1px solid var(--border); text-align:left; font-size:14px}
    thead th{background:#f8fafc; font-weight:700; color:#334155}
    tbody tr:hover{background:#f9fafb}
    tbody tr:last-child td{border-bottom:0}

    .tag{display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; background:var(--tag); color:var(--tagtext)}
    .tag.green{background:#ecfdf5; color:#065f46}
    .tag.yellow{background:#fffbeb; color:#92400e}
    .tag.red{background:#fef2f2; color:#991b1b}
    .tag.gray{background:#f3f4f6; color:#374151}

    /* Inline kbd */
    .kbd{font-family:ui-monospace,Consolas,monospace; font-size:12px; background:#111827; color:#fff; padding:2px 6px; border-radius:6px}

    /* Toast */
    .toast{position:fixed; right:24px; bottom:24px; padding:12px 16px; background:#111827; color:#fff; border-radius:10px; box-shadow:var(--shadow); opacity:0; transform:translateY(10px); transition:.2s}
    .toast.show{opacity:1; transform:translateY(0)}

    /* POLISHED: Organization layout */
    .fieldset{border:1px solid var(--border); border-radius:10px; padding:14px; background:#fff}
    .fieldset + .fieldset{margin-top:12px}
    .fieldset .legend{font-weight:700; color:#0f172a; margin-bottom:10px; display:flex; align-items:center; gap:8px}

    /* POLISHED: Users & Roles split layout */
    .split{display:grid; grid-template-columns:360px 1fr; gap:14px}
    @media (max-width: 1000px){.split{grid-template-columns:1fr}}

    .panel{background:#fff; border:1px solid var(--border); border-radius:12px; box-shadow:var(--shadow); padding:16px}
    .panel h3{margin:4px 0 12px}
    .panel .row + .row{margin-top:8px}

    .callout{padding:10px 12px; border:1px solid var(--border); background:#f8fafc; border-radius:10px; color:#334155}

    /* Sidebar minimal to not break your layout */
    
</style>
</head>
<body>
<div class="container">
  <!-- Sidebar copied EXACTLY from your snippet (minor safe styles above) -->
  <aside class="sidebar" id="sidebar">
        <img src="Image/Logo.png" alt="HaulPro Logo" width="160" />
        <h3>HaulPro</h3>
        <ul class="menu">
          <li>
            <a href="dashboard.html">
              <img src="Image/dashboard.png" alt="" />Dashboard
            </a>
          </li>

          <!-- Analysis dropdown (hover to expand, no arrow) -->
          <li class="has-submenu">
            <a href="#"> <img src="Image/chart.png" alt="" />Analysis </a>
            <ul class="submenu">
              <li>
                <a href="delivery_performance.php"
                  ><img src="Image/continuous-improvement.png" alt="" />Delivery
                  Performance</a
                >
              </li>
              <li>
                <a href="Revenue_analysis.php"
                  ><img src="Image/profit-margin.png" alt="" />Revenue
                  Analysis</a
                >
              </li>
              <li>
                <a href="fleet_analysis.php"
                  ><img src="Image/delivery-truck.png" alt="" />Fleet
                  Efficiency</a
                >
              </li>
            </ul>
          </li>

          <li>
            <a href="#"
              ><img src="Image/car.png" alt="" style="width: 40px" />Vehicle</a
            >
          </li>
          <li>
            <a href="#"
              ><img src="Image/plus.png" alt="" style="width: 40px" />Add
              Trips</a
            >
          </li>
          <li>
            <a href="#"
              ><img src="Image/wallet.png" alt="" style="width: 40px" />Payment
              Method</a
            >
          </li>
          <li>
            <a href="Lorry_owner.php"
              ><img
                src="Image/businessman.png"
                alt=""
                style="width: 40px"
              />Lorry Owner List</a
            >
          </li>
          <li>
            <a href="lorrylist.php"
              ><img src="Image/truck.png" alt="" style="width: 40px" />Lorry
              List</a
            >
          </li>
          <li>
            <a href="Admin_settings.php"
              ><img
                src="Image/settings.png"
                alt=""
                style="width: 40px"
              />Settings</a
            >
          </li>
          <li>
            <a href="faq.html"
              ><img src="Image/faq.png" alt="" style="width: 40px" />FAQ</a
            >
          </li>
        </ul>

        <div class="help-card">
          <img
            src="https://cdn-icons-png.flaticon.com/512/4712/4712002.png"
            alt="Help"
          />
          <p>Need Help?</p>
          <button>Contact Now</button>
        </div>
      </aside>

  <main>
    <div class="header"><h1>🛠️ Admin Settings</h1></div>

    <div class="tabs">
      <button class="tab-btn active" data-tab="adminProfile">Profile</button>
      <button class="tab-btn" data-tab="org">Organization</button>
      <button class="tab-btn" data-tab="roles">Users & Roles</button>
      <button class="tab-btn" data-tab="security">Security</button>
      <button class="tab-btn" data-tab="notifs">Notifications</button>
      <button class="tab-btn" data-tab="api">API & Webhooks</button>
      <button class="tab-btn" data-tab="audit">Audit & Logs</button>
    </div>

    <!-- Profile (unchanged functionality) -->
    <section id="adminProfile" class="tab active">
      <div class="card">
        <h2>Admin Profile</h2>
        <div class="grid cols-3">
          <div class="col"><label>Full Name</label><input id="a_fullname" type="text" value="Jane Admin"></div>
          <div class="col"><label>Email</label><input id="a_email" type="email" value="admin@haulpro.com"></div>
          <div class="col"><label>Phone</label><input id="a_phone" type="text" value="+8801700000000"></div>
        </div>
        <div class="grid cols-2">
          <div class="col">
            <label>Password</label><input type="password" id="a_password" placeholder="Enter new password">
            <div class="meter" id="a_meter"><span></span></div>
          </div>
          <div class="col">
            <label>Confirm Password</label><input type="password" id="a_password2" placeholder="Re-enter password">
            <div class="hint" id="a_pwHint">Use at least 8 chars, number & symbol.</div>
          </div>
        </div>
        <div class="btn-row">
          <button class="btn" id="saveAdminProfile">💾 Save Profile</button>
          <button class="btn secondary" id="exportAdmin">⬇️ Export Profile</button>
          <label class="btn ghost" for="importAdmin" style="cursor:pointer;">⬆️ Import
            <input type="file" id="importAdmin" accept="application/json" style="display:none;">
          </label>
        </div>
      </div>
    </section>

    <!-- Organization (POLISHED layout only) -->
    <section id="org" class="tab">
      <div class="card">
        <h2>Organization</h2>

        <div class="fieldset">
          <div class="legend section-title">🏷️ <span>Identity</span></div>
          <div class="help">Legal details shown on invoices and contracts.</div>
          <div class="grid cols-4">
            <div><label>Company Legal Name</label><input id="o_name" type="text" value="HaulPro Logistics Ltd."></div>
            <div><label>Registration / Business No.</label><input id="o_reg" type="text" placeholder="e.g., RJSC No."></div>
            <div><label>Primary Domain</label><input id="o_domain" type="text" value="haulpro.com"></div>
            <div><label>Default Time Zone</label><input id="o_tz" type="text" value="Asia/Dhaka"></div>
          </div>
        </div>

        <div class="fieldset">
          <div class="legend section-title">🏠 <span>Billing Address & Support</span></div>
          <div class="grid cols-3">
            <div class="col-2"><label>Billing Address</label><textarea id="o_bill_addr" placeholder="Street, City, State/Division, Postal Code, Country">52 Kawran Bazar, Dhaka 1215, Bangladesh</textarea></div>
            <div>
              <label>Support Email</label><input id="o_support_email" type="email" value="support@haulpro.com">
              <label>Support Phone</label><input id="o_support_phone" type="text" value="+8801612345678">
            </div>
          </div>
        </div>

        <div class="fieldset">
          <div class="legend section-title">💳 <span>Financials</span></div>
          <div class="help">Defaults used for invoices and payments.</div>
          <div class="grid cols-4">
            <div><label>Tax / VAT ID</label><input id="o_vat" type="text" value="BD-123456789"></div>
            <div><label>Default Tax Rate (%)</label><input id="o_taxrate" type="number" value="7.5" min="0" step="0.1"></div>
            <div><label>Currency</label>
              <select id="o_currency"><option>BDT</option><option selected>USD</option></select>
            </div>
            <div><label>Invoice Prefix</label><input id="o_inv_prefix" type="text" value="HP-INV-"></div>
          </div>
          <div class="grid cols-3">
            <div><label>Next Invoice Number</label><input id="o_next_inv" type="number" value="1001" min="1"></div>
            <div><label>Payment Terms</label>
              <select id="o_terms"><option>Due on Receipt</option><option selected>Net 15</option><option>Net 30</option><option>Net 45</option></select>
            </div>
            <div><label>Late Fee (% per month)</label><input id="o_late_fee" type="number" value="0" min="0" step="0.1"></div>
          </div>
        </div>

        <div class="fieldset">
          <div class="legend section-title">🏦 <span>Bank / Payout</span></div>
          <div class="grid cols-4">
            <div><label>Account Name</label><input id="o_bank_name" type="text" placeholder="HaulPro Logistics Ltd."></div>
            <div><label>Account Number / IBAN</label><input id="o_bank_ac" type="text" placeholder="XXXXXXXXXXXX"></div>
            <div><label>Bank & Branch</label><input id="o_bank_branch" type="text" placeholder="Bank name, branch"></div>
            <div><label>SWIFT / BIC</label><input id="o_bank_swift" type="text" placeholder="XXXXXX"></div>
          </div>
        </div>

        <div class="fieldset">
          <div class="legend section-title">🗺️ <span>Service Regions</span></div>
          <div class="help">Comma-separated list used for pricing, availability and reporting.</div>
          <input id="o_regions" type="text" value="Dhaka, Chittagong, Sylhet, Rajshahi">
        </div>

        <div class="btn-row" style="margin-top:12px">
          <button class="btn" id="saveOrg">💾 Save Organization</button>
        </div>
      </div>
    </section>

    <!-- Users & Roles (POLISHED layout only) -->
    <section id="roles" class="tab">
      <div class="card">
        <h2>Users & Roles</h2>

        <!-- Invite strip -->
        <div class="panel" style="margin-bottom:12px">
          <div class="section-title">✉️ <span>Invite User</span></div>
          <div class="grid cols-3" style="align-items:end">
            <div><label>Invite User (email)</label><input id="u_invite" type="email" placeholder="user@company.com"></div>
            <div><label>Role</label><select id="u_role"></select></div>
            <div><button class="btn" id="inviteUser" style="margin-top:2px; width:100%">➕ Send Invite</button></div>
          </div>
          <div class="help">We’ll send an invite email. The user appears below as <span class="tag yellow">Invited</span>.</div>
        </div>

        <div class="split">
          <!-- Left: Role management -->
          <div class="panel">
            <div class="section-title">🛡️ <span>Roles</span></div>
            <div class="row">
              <div class="col"><label>Select Role to Edit</label><select id="roleSelect"></select></div>
            </div>

            <div class="callout" style="margin:10px 0">Tip: Use <span class="kbd">Clone</span> to start from an existing role, then toggle permissions.</div>

            <div class="grid cols-2">
              <div>
                <label>Create Role</label>
                <input id="newRoleName" placeholder="e.g., Dispatcher">
                <div class="btn-row"><button class="btn secondary" id="createRole">Create</button></div>
              </div>
              <div>
                <label>Clone From</label>
                <select id="cloneFrom"></select>
                <div class="btn-row"><button class="btn secondary" id="cloneRole">Clone</button></div>
              </div>
            </div>

            <div class="btn-row" style="margin-top:10px">
              <button class="btn danger" id="deleteRole">🗑️ Delete Role</button>
            </div>
          </div>

          <!-- Right: Permissions -->
          <div class="panel" id="permGrid" style="padding:16px;">
            <div class="section-title">✅ <span>Permissions</span></div>
            <div id="permList" class="grid cols-2" style="gap:12px"></div>
            <div class="btn-row" style="margin-top:10px; justify-content:flex-end">
              <button class="btn" id="saveRolePerms">💾 Save Permissions</button>
            </div>
          </div>
        </div>

        <!-- Users table -->
        <div class="panel" style="margin-top:12px">
          <div class="section-title">👥 <span>Users</span></div>
          <div class="grid cols-3" style="align-items:end; margin-bottom:8px">
            <div class="col-2"><label>Search</label><input id="userSearch" placeholder="Search by name, email, or role"></div>
            <div><button class="btn secondary" id="resetUsers" style="width:100%">↺ Reset Demo Data</button></div>
          </div>
          <table id="usersTable">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>2FA</th><th>Last Active</th><th style="width:220px">Actions</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Security (unchanged) -->
    <section id="security" class="tab">
      <div class="card">
        <h2>Security</h2>
        <div class="toggle-row"><span class="label">Enforce 2FA for all admins</span><label class="toggle"><input type="checkbox" id="s_enforce2fa" checked><span class="switch"></span></label></div>
        <div class="help">Admins must enroll a second factor (Authenticator app or SMS). Blocks password-only logins.</div>
        <div class="toggle-row"><span class="label">SSO (SAML/OIDC) Required</span><label class="toggle"><input type="checkbox" id="s_sso"><span class="switch"></span></label></div>
        <div class="help">Force sign-in via your identity provider (e.g., Google Workspace, Azure AD). Password logins disabled when ON.</div>
        <div class="grid cols-2">
          <div>
            <label>Allowed IPs (CIDR, comma separated)</label>
            <input id="s_ips" type="text" placeholder="203.0.113.0/24, 198.51.100.10">
            <div class="help">Only these networks can access high-privilege actions. Leave empty to allow all.</div>
          </div>
          <div>
            <label>Session Timeout (minutes)</label>
            <input id="s_timeout" type="number" min="5" value="30">
            <div class="help">After this inactivity period, users must re-authenticate.</div>
          </div>
        </div>
        <div class="btn-row"><button class="btn" id="saveSecurity">💾 Save Security</button><button class="btn danger" id="terminateSessions">🗝️ Terminate All Other Sessions</button><span class="hint">Hold <span class="kbd">Shift</span> while clicking to confirm.</span></div>
      </div>
    </section>

    <!-- Notifications (unchanged) -->
    <section id="notifs" class="tab">
      <div class="card">
        <h2>Notifications</h2>
        <div class="toggle-row"><span class="label">Login Alerts (new device)</span><label class="toggle"><input type="checkbox" id="n_login" checked><span class="switch"></span></label></div>
        <div class="toggle-row"><span class="label">Weekly Summary to Admins</span><label class="toggle"><input type="checkbox" id="n_weekly"><span class="switch"></span></label></div>
        <div class="grid cols-3">
          <div><label>Quiet Hours (start)</label><input id="n_qh_start" type="time" value="22:00"></div>
          <div><label>Quiet Hours (end)</label><input id="n_qh_end" type="time" value="07:00"></div>
          <div>
            <label>Weekly Summary Day</label>
            <select id="n_weekday"><option>Sunday</option><option>Monday</option><option selected>Tuesday</option><option>Wednesday</option><option>Thursday</option><option>Friday</option><option>Saturday</option></select>
          </div>
        </div>
        <div class="grid cols-2">
          <div><label>Slack Webhook (optional)</label><input id="n_slack" placeholder="https://hooks.slack.com/services/..."></div>
          <div><label>SMS Sender ID (optional)</label><input id="n_sms_id" placeholder="HAULPRO"></div>
        </div>
        <div class="btn-row"><button class="btn" id="testNotif">▶️ Send Test Notification</button><button class="btn" id="saveNotifs">💾 Save Notifications</button></div>
      </div>
    </section>

    <!-- API & Webhooks (unchanged) -->
    <section id="api" class="tab">
      <div class="card">
        <h2>API & Webhooks</h2>
        <div class="grid cols-2">
          <div>
            <label>API Keys</label>
            <table id="apiTable"><thead><tr><th>Label</th><th>Key</th><th>Created</th><th>Actions</th></tr></thead><tbody></tbody></table>
            <div class="btn-row" style="margin-top:10px"><input id="apiLabel" placeholder="Key label (e.g., BI Dashboard)"><button class="btn" id="genKey">🔑 Generate Key</button></div>
          </div>
          <div>
            <label>Webhook URL</label><input id="w_url" placeholder="https://example.com/webhooks/haulpro">
            <label>Secret (used for signatures)</label><input id="w_secret" placeholder="auto-generated if empty">
            <div class="btn-row"><button class="btn" id="saveWebhook">💾 Save Webhook</button><button class="btn secondary" id="testWebhook">▶️ Send Test Event</button></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Audit & Logs (unchanged) -->
    <section id="audit" class="tab">
      <div class="card">
        <h2>Audit & Logs</h2>
        <div class="grid cols-3" style="align-items:end">
          <div><label>Filter by Actor</label><input id="f_actor" placeholder="email or name"></div>
          <div><label>Action Contains</label><input id="f_action" placeholder="e.g., login, key.create"></div>
          <div class="btn-row"><button class="btn secondary" id="filterAudit">Filter</button><button class="btn" id="exportAudit">⬇️ Export CSV</button></div>
        </div>
        <table id="auditTable"><thead><tr><th>Time</th><th>Actor</th><th>Action</th><th>Metadata</th></tr></thead><tbody></tbody></table>
      </div>
    </section>

    <div class="toast" id="toast">Saved.</div>
  </main>
</div>

<script>
  // ---------- Utilities ----------
  const toast=(m='Saved.')=>{const t=document.getElementById('toast'); t.textContent=m; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),1600)};
  const unsaved=(btn)=>btn.classList.add('unsaved'); const saved=(btn)=>btn.classList.remove('unsaved');
  const pwScore=(pw)=>{let s=0; if(pw.length>=8)s++; if(/[A-Z]/.test(pw))s++; if(/[a-z]/.test(pw))s++; if(/\d/.test(pw))s++; if(/[^A-Za-z0-9]/.test(pw))s++; return s};
  const renderMeter=(id,score)=>{const m=document.getElementById(id); const bar=m.querySelector('span'); bar.style.width=Math.min(100,score*20)+'%'; m.classList.remove('ok','warn'); if(score>=4)m.classList.add('ok'); else if(score>=2)m.classList.add('warn')};

  // ---------- Tabs ----------
  document.addEventListener('DOMContentLoaded', ()=>{
    document.querySelectorAll('.tab-btn').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
        document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
        btn.classList.add('active'); document.getElementById(btn.dataset.tab).classList.add('active');
      });
    });

    // ---------- Profile ----------
    const pw=document.getElementById('a_password'), cpw=document.getElementById('a_password2'), pwh=document.getElementById('a_pwHint');
    pw.addEventListener('input', ()=>{renderMeter('a_meter', pwScore(pw.value)); unsaved(document.getElementById('saveAdminProfile'))});
    cpw.addEventListener('input', ()=>{pwh.textContent=(cpw.value && pw.value!==cpw.value)?'Passwords do not match.':''; unsaved(document.getElementById('saveAdminProfile'))});
    document.getElementById('saveAdminProfile').addEventListener('click', ()=>{
      if(pw.value && (pw.value.length<8 || pw.value!==cpw.value)){ alert('Check password requirements / match.'); return; }
      const data={name:a_fullname.value, email:a_email.value, phone:a_phone.value};
      localStorage.setItem('adm_profile', JSON.stringify(data));
      saved(document.getElementById('saveAdminProfile')); toast('Admin profile saved');
    });
    document.getElementById('exportAdmin').addEventListener('click', ()=>{
      const data=localStorage.getItem('adm_profile') || JSON.stringify({name:a_fullname.value,email:a_email.value,phone:a_phone.value});
      const blob=new Blob([data],{type:'application/json'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='admin-profile.json'; a.click();
    });
    importAdmin.addEventListener('change', (e)=>{const f=e.target.files[0]; if(!f)return; f.text().then(t=>{try{const d=JSON.parse(t); if(d.name)a_fullname.value=d.name; if(d.email)a_email.value=d.email; if(d.phone)a_phone.value=d.phone; unsaved(saveAdminProfile)}catch{alert('Invalid JSON')}})});

    // ---------- Organization ----------
    ['o_name','o_reg','o_domain','o_tz','o_bill_addr','o_support_email','o_support_phone','o_vat','o_taxrate','o_currency','o_inv_prefix','o_next_inv','o_terms','o_late_fee','o_bank_name','o_bank_ac','o_bank_branch','o_bank_swift','o_regions'].forEach(id=>{
      const el=document.getElementById(id); el.addEventListener('input', ()=>unsaved(document.getElementById('saveOrg'))); el.addEventListener('change', ()=>unsaved(document.getElementById('saveOrg')));
    });
    document.getElementById('saveOrg').addEventListener('click', ()=>{
      const d={
        name:o_name.value, reg:o_reg.value, domain:o_domain.value, tz:o_tz.value,
        bill_addr:o_bill_addr.value, support_email:o_support_email.value, support_phone:o_support_phone.value,
        vat:o_vat.value, taxrate:o_taxrate.value, currency:o_currency.value, inv_prefix:o_inv_prefix.value,
        next_inv:parseInt(o_next_inv.value,10)||1, terms:o_terms.value, late_fee:parseFloat(o_late_fee.value)||0,
        bank:{name:o_bank_name.value, ac:o_bank_ac.value, branch:o_bank_branch.value, swift:o_bank_swift.value},
        regions:o_regions.value
      };
      localStorage.setItem('adm_org', JSON.stringify(d)); saved(saveOrg); toast('Organization saved');
    });

    // ---------- Users & Roles ----------
    const DEFAULT_PERMS=[
      {key:'view_analytics', label:'View Analytics'},
      {key:'edit_fleet', label:'Edit Fleet'},
      {key:'manage_billing', label:'Manage Billing'},
      {key:'manage_users', label:'Manage Users'},
      {key:'export_data', label:'Export Data'},
      {key:'admin_console', label:'Admin Console'}
    ];
    function getRoles(){
      let roles=JSON.parse(localStorage.getItem('adm_roles')||'null');
      if(!roles){
        roles={
          'Viewer': {view_analytics:true},
          'Editor': {view_analytics:true, edit_fleet:true},
          'Manager': {view_analytics:true, edit_fleet:true, manage_billing:true, export_data:true},
          'Admin': {view_analytics:true, edit_fleet:true, manage_billing:true, manage_users:true, export_data:true, admin_console:true}
        };
        localStorage.setItem('adm_roles', JSON.stringify(roles));
      }
      return roles;
    }
    function setRoles(r){ localStorage.setItem('adm_roles', JSON.stringify(r)) }
    function permCatalog(){ let c=JSON.parse(localStorage.getItem('adm_perm_catalog')||'null'); if(!c){c=DEFAULT_PERMS; localStorage.setItem('adm_perm_catalog', JSON.stringify(c))} return c }

    function fillRoleDropdowns(){
      const roles=getRoles(); const opts=Object.keys(roles).map(r=>`<option>${r}</option>`).join('');
      roleSelect.innerHTML=opts; cloneFrom.innerHTML=opts; u_role.innerHTML=opts;
    }
    function renderPermList(roleName){
      const roles=getRoles(); const role=roles[roleName]||{}; const catalog=permCatalog();
      const list=document.getElementById('permList'); list.innerHTML='';
      catalog.forEach(p=>{
        const wrap=document.createElement('div');
        wrap.className='panel';
        wrap.style.padding='10px';
        wrap.innerHTML=`<div class="toggle-row"><span class="label">${p.label}</span><label class="toggle"><input type="checkbox" data-perm="${p.key}" ${role[p.key]?'checked':''}><span class="switch"></span></label></div>`;
        list.appendChild(wrap);
      });
    }
    function renderUsers(filter=''){
      const tbody=document.querySelector('#usersTable tbody');
      const roles=getRoles();
      let users=JSON.parse(localStorage.getItem('adm_users')||'null');
      if(!users){
        users=[
          {name:'Jane Admin', email:'admin@haulpro.com', role:'Admin', status:'Active', twofa:true, last:'2025-09-15 10:12'},
          {name:'Omar Ops', email:'ops@haulpro.com', role:'Manager', status:'Active', twofa:false, last:'2025-09-20 18:41'},
          {name:'Lina Logistics', email:'lina@haulpro.com', role:'Editor', status:'Invited', twofa:false, last:'—'},
          {name:'Vik Viewer', email:'vik@haulpro.com', role:'Viewer', status:'Active', twofa:false, last:'2025-09-17 09:05'}
        ];
        localStorage.setItem('adm_users', JSON.stringify(users));
      }
      if(filter){
        const f=filter.toLowerCase();
        users=users.filter(u=>[u.name,u.email,u.role].some(x=>String(x).toLowerCase().includes(f)));
      }
      tbody.innerHTML='';
      users.forEach((u,i)=>{
        const tr=document.createElement('tr');
        const statusTag = u.status==='Active' ? 'green' : (u.status==='Invited' ? 'yellow' : (u.status==='Disabled'?'red':'gray'));
        tr.innerHTML=`
          <td>${u.name}</td>
          <td>${u.email}</td>
          <td>
            <select data-idx="${i}" class="roleSel">
              ${Object.keys(roles).map(r=>`<option ${u.role===r?'selected':''}>${r}</option>`).join('')}
            </select>
          </td>
          <td><span class="tag ${statusTag}">${u.status}</span></td>
          <td>${u.twofa?'<span class="tag green">Enabled</span>':'<span class="tag gray">Off</span>'}</td>
          <td>${u.last||'—'}</td>
          <td>
            ${u.status==='Disabled'
              ? `<button class="btn secondary actBtn" data-idx="${i}" data-act="activate">Activate</button>`
              : `<button class="btn secondary actBtn" data-idx="${i}" data-act="deactivate">Deactivate</button>`
            }
            <button class="btn danger delBtn" data-idx="${i}">Remove</button>
          </td>`;
        tbody.appendChild(tr);
      });
      tbody.querySelectorAll('.roleSel').forEach(sel=>{
        sel.addEventListener('change', (e)=>{
          const idx=+e.target.dataset.idx; const users=JSON.parse(localStorage.getItem('adm_users')); users[idx].role=e.target.value; localStorage.setItem('adm_users', JSON.stringify(users)); toast('Role updated');
        });
      });
      tbody.querySelectorAll('.actBtn').forEach(btn=>{
        btn.addEventListener('click', ()=>{
          const idx=+btn.dataset.idx; const act=btn.dataset.act; const users=JSON.parse(localStorage.getItem('adm_users'));
          users[idx].status = (act==='activate')?'Active':'Disabled';
          localStorage.setItem('adm_users', JSON.stringify(users)); renderUsers(userSearch.value);
        });
      });
      tbody.querySelectorAll('.delBtn').forEach(btn=>{
        btn.addEventListener('click', ()=>{
          const idx=+btn.dataset.idx; const users=JSON.parse(localStorage.getItem('adm_users'));
          if(confirm('Remove user?')){ users.splice(idx,1); localStorage.setItem('adm_users', JSON.stringify(users)); renderUsers(userSearch.value); toast('User removed'); }
        });
      });
    }

    // Init roles & users
    fillRoleDropdowns();
    renderPermList(roleSelect.value = Object.keys(getRoles())[0]);
    renderUsers();

    // Role events
    roleSelect.addEventListener('change', ()=>renderPermList(roleSelect.value));
    createRole.addEventListener('click', ()=>{
      const name=newRoleName.value.trim(); if(!name){alert('Enter role name'); return;}
      const roles=getRoles(); if(roles[name]){alert('Role exists'); return;}
      roles[name]={}; setRoles(roles); fillRoleDropdowns(); roleSelect.value=name; renderPermList(name); newRoleName.value=''; toast('Role created');
    });
    cloneRole.addEventListener('click', ()=>{
      const src=cloneFrom.value, name=newRoleName.value.trim(); if(!name){alert('Enter new role name'); return;}
      const roles=getRoles(); if(roles[name]){alert('Role exists'); return;}
      roles[name]={...roles[src]}; setRoles(roles); fillRoleDropdowns(); roleSelect.value=name; renderPermList(name); newRoleName.value=''; toast(`Cloned from ${src}`);
    });
    deleteRole.addEventListener('click', ()=>{
      const name=roleSelect.value; if(['Admin','Manager','Editor','Viewer'].includes(name)){alert('Cannot delete a default role'); return;}
      const roles=getRoles(); delete roles[name]; setRoles(roles); fillRoleDropdowns(); roleSelect.value='Viewer'; renderPermList('Viewer'); toast('Role deleted');
    });
    saveRolePerms.addEventListener('click', ()=>{
      const name=roleSelect.value; const roles=getRoles(); const newPerms={};
      document.querySelectorAll('#permList input[type="checkbox"]').forEach(cb=>{newPerms[cb.dataset.perm]=cb.checked});
      roles[name]=newPerms; setRoles(roles); toast('Permissions saved');
    });

    // Invite user
    inviteUser.addEventListener('click', ()=>{
      const email=u_invite.value.trim(); if(!email){alert('Enter email'); return;}
      const name=email.split('@')[0].replace(/\./g,' ').replace(/\b\w/g,m=>m.toUpperCase());
      const role=u_role.value;
      const users=JSON.parse(localStorage.getItem('adm_users')||'[]');
      users.push({name, email, role, status:'Invited', twofa:false, last:'—'});
      localStorage.setItem('adm_users', JSON.stringify(users));
      u_invite.value=''; renderUsers(userSearch.value); toast('Invite queued (demo)');
    });
    userSearch.addEventListener('input', ()=>renderUsers(userSearch.value));
    resetUsers.addEventListener('click', ()=>{localStorage.removeItem('adm_users'); renderUsers(userSearch.value); toast('Demo users reset')});

    // ---------- Security ----------
    ['s_enforce2fa','s_sso','s_ips','s_timeout'].forEach(id=>{
      const el=document.getElementById(id);
      el.addEventListener('input', ()=>unsaved(document.getElementById('saveSecurity')));
      el.addEventListener('change', ()=>unsaved(document.getElementById('saveSecurity')));
    });
    saveSecurity.addEventListener('click', ()=>{
      const d={enforce2fa:s_enforce2fa.checked, sso:s_sso.checked, ips:s_ips.value, timeout:parseInt(s_timeout.value,10)||30};
      localStorage.setItem('adm_security', JSON.stringify(d)); saved(saveSecurity); toast('Security saved');
    });
    terminateSessions.addEventListener('click', (e)=>{ if(!e.shiftKey){ alert('Hold Shift while clicking to confirm.'); return; } toast('All other sessions terminated (demo)') });

    // ---------- Notifications ----------
    ['n_login','n_weekly','n_qh_start','n_qh_end','n_weekday','n_slack','n_sms_id'].forEach(id=>{
      const el=document.getElementById(id); el.addEventListener('input', ()=>unsaved(document.getElementById('saveNotifs'))); el.addEventListener('change', ()=>unsaved(document.getElementById('saveNotifs')));
    });
    saveNotifs.addEventListener('click', ()=>{
      const d={login:n_login.checked, weekly:n_weekly.checked, qh_start:n_qh_start.value, qh_end:n_qh_end.value, weekday:n_weekday.value, slack:n_slack.value, sms_id:n_sms_id.value};
      localStorage.setItem('adm_notifs', JSON.stringify(d)); saved(saveNotifs); toast('Notification settings saved');
    });
    testNotif.addEventListener('click', ()=> toast('Test notification sent (demo)'));

    // ---------- API & Webhooks (demo) ----------
    const apiTBody=document.querySelector('#apiTable tbody');
    function renderKeys(){
      apiTBody.innerHTML='';
      const keys=JSON.parse(localStorage.getItem('adm_api_keys')||'[]');
      keys.forEach((k,idx)=>{
        const tr=document.createElement('tr');
        tr.innerHTML=`<td>${k.label}</td><td><span class="kbd">${k.mask}</span></td><td>${k.created}</td>
        <td><button class="btn secondary" data-view="${idx}">View</button> <button class="btn danger" data-del="${idx}">Revoke</button></td>`;
        apiTBody.appendChild(tr);
      });
    }
    function genMask(full){return full.slice(0,6)+'…'+full.slice(-4)}
    function randomKey(){return 'hp_'+Math.random().toString(36).slice(2,10)+Math.random().toString(36).slice(2,10)+Math.random().toString(36).slice(2,6)}
    genKey.addEventListener('click', ()=>{
      const label=apiLabel.value.trim()||'Unnamed Key';
      const full=randomKey(); const mask=genMask(full);
      const keys=JSON.parse(localStorage.getItem('adm_api_keys')||'[]');
      keys.push({label, mask, full, created:new Date().toLocaleString()});
      localStorage.setItem('adm_api_keys', JSON.stringify(keys)); renderKeys();
      alert(`Copy your API key now:\n\n${full}\n\n(It will only be shown once)`); apiLabel.value='';
    });
    apiTBody.addEventListener('click', (e)=>{
      const d=e.target.dataset;
      if(d.view){ const idx=parseInt(d.view,10); const keys=JSON.parse(localStorage.getItem('adm_api_keys')||'[]'); alert('Stored mask: '+keys[idx].mask); }
      if(d.del){ const idx=parseInt(d.del,10); const keys=JSON.parse(localStorage.getItem('adm_api_keys')||'[]'); if(confirm('Revoke this key?')){ keys.splice(idx,1); localStorage.setItem('adm_api_keys', JSON.stringify(keys)); renderKeys(); toast('Key revoked')}}
    });
    saveWebhook.addEventListener('click', ()=>{
      const secret=w_secret.value || ('wh_'+Math.random().toString(36).slice(2,12));
      localStorage.setItem('adm_webhook', JSON.stringify({url:w_url.value, secret})); w_secret.value=secret; toast('Webhook saved');
    });
    testWebhook.addEventListener('click', ()=>{
      const cfg=JSON.parse(localStorage.getItem('adm_webhook')||'{}'); if(!cfg.url){ alert('Save a webhook URL first.'); return; } toast('Test event queued (demo)');
    });
    renderKeys();

    // ---------- Audit (demo) ----------
    const audit=JSON.parse(localStorage.getItem('adm_audit')||'[]');
    if(audit.length===0){
      const seed=[
        {t:new Date(Date.now()-3600e3).toLocaleString(), a:'admin@haulpro.com', act:'login.success', meta:'IP 203.0.113.10'},
        {t:new Date(Date.now()-1800e3).toLocaleString(), a:'admin@haulpro.com', act:'api.key.create', meta:'label=BI Dashboard'},
        {t:new Date(Date.now()-1200e3).toLocaleString(), a:'ops@haulpro.com', act:'webhook.test', meta:'200 OK'}
      ];
      localStorage.setItem('adm_audit', JSON.stringify(seed));
    }
    function renderAudit(rows){
      const tb=document.querySelector('#auditTable tbody'); tb.innerHTML='';
      rows.forEach(r=>{const tr=document.createElement('tr'); tr.innerHTML=`<td>${r.t}</td><td>${r.a}</td><td><span class="tag">${r.act}</span></td><td>${r.meta}</td>`; tb.appendChild(tr)});
    }
    function loadAudit(){ renderAudit(JSON.parse(localStorage.getItem('adm_audit')||'[]')) }
    loadAudit();
    filterAudit.addEventListener('click', ()=>{
      const all=JSON.parse(localStorage.getItem('adm_audit')||'[]');
      const a=f_actor.value.trim().toLowerCase(), k=f_action.value.trim().toLowerCase();
      const out=all.filter(r=>(!a || r.a.toLowerCase().includes(a)) && (!k || r.act.toLowerCase().includes(k)));
      renderAudit(out);
    });
    exportAudit.addEventListener('click', ()=>{
      const rows=JSON.parse(localStorage.getItem('adm_audit')||'[]');
      const csv=['time,actor,action,metadata'].concat(rows.map(r=>`"${r.t}","${r.a}","${r.act}","${(r.meta||'').replace(/"/g,'""')}"`)).join('\n');
      const blob=new Blob([csv],{type:'text/csv'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='audit.csv'; a.click();
    });

    // ---------- Restore saved states ----------
    (function restore(){
      const ap=localStorage.getItem('adm_profile'); if(ap){const d=JSON.parse(ap); a_fullname.value=d.name||''; a_email.value=d.email||''; a_phone.value=d.phone||''}
      const og=localStorage.getItem('adm_org'); if(og){const d=JSON.parse(og);
        o_name.value=d.name||''; o_reg.value=d.reg||''; o_domain.value=d.domain||''; o_tz.value=d.tz||'';
        o_bill_addr.value=d.bill_addr||''; o_support_email.value=d.support_email||''; o_support_phone.value=d.support_phone||'';
        o_vat.value=d.vat||''; o_taxrate.value=d.taxrate||''; o_currency.value=d.currency||'USD';
        o_inv_prefix.value=d.inv_prefix||''; o_next_inv.value=d.next_inv||1; o_terms.value=d.terms||'Net 15'; o_late_fee.value=d.late_fee||0;
        if(d.bank){ o_bank_name.value=d.bank.name||''; o_bank_ac.value=d.bank.ac||''; o_bank_branch.value=d.bank.branch||''; o_bank_swift.value=d.bank.swift||'' }
        o_regions.value=d.regions||'';
      }
      const nf=localStorage.getItem('adm_notifs'); if(nf){const d=JSON.parse(nf);
        n_login.checked=!!d.login; n_weekly.checked=!!d.weekly; n_qh_start.value=d.qh_start||'22:00'; n_qh_end.value=d.qh_end||'07:00';
        n_weekday.value=d.weekday||'Tuesday'; n_slack.value=d.slack||''; n_sms_id.value=d.sms_id||'';
      }
      fillRoleDropdowns(); renderUsers();
    })();
  });
</script>
</body>
</html>
