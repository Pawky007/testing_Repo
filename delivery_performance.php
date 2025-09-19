<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HaulPro — Analysis</title>
    <link rel="stylesheet" href="dashboad_style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="analysis_css.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
    />
    <style>
      
    </style>
  </head>
  <body>
    <div class="container">
      <!-- Sidebar -->
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
            <a href="#">
              <img src="Image/chart.png" alt="" />Analysis
            </a>
            <ul class="submenu">
              <li><a href="delivery_performance.php"><img src="Image/continuous-improvement.png" alt="">Delivery Performance</a></li>
              <li><a href="revenue_analysis.html"><img src="Image/profit-margin.png" alt="">Revenue Analysis</a></li>
              <li><a href="fleet_efficiency.html"><img src="Image/delivery-truck.png" alt="">Fleet Efficiency</a></li>
            </ul>
          </li>

          <li>
            <a href="#"><img src="Image/car.png" alt="" style="width: 40px" />Vehicle</a>
          </li>
          <li>
            <a href="#"><img src="Image/plus.png" alt="" style="width: 40px" />Add Trips</a>
          </li>
          <li>
            <a href="#"><img src="Image/wallet.png" alt="" style="width: 40px" />Payment Method</a>
          </li>
          <li>
            <a href="Lorry_owner.php"><img src="Image/businessman.png" alt="" style="width: 40px" />Lorry Owner List</a>
          </li>
          <li>
            <a href="lorrylist.php"><img src="Image/truck.png" alt="" style="width: 40px" />Lorry List</a>
          </li>
          <li>
            <a href="#"><img src="Image/settings.png" alt="" style="width: 40px" />Settings</a>
          </li>
          <li>
            <a href="faq.html"><img src="Image/faq.png" alt="" style="width: 40px" />FAQ</a>
          </li>
        </ul>

        <div class="help-card">
          <img src="https://cdn-icons-png.flaticon.com/512/4712/4712002.png" alt="Help" />
          <p>Need Help?</p>
          <button>Contact Now</button>
        </div>
      </aside>

      <!-- Main content -->
      <main class="dashboard" id="dashboard">
        <div class="header">
          <h1>Welcome to Analysis Section</h1>
        </div>
      </main>
    </div>

    <footer>
      <div class="footer-content">
        <p>&copy; 2025 HaulPro. All Rights Reserved.</p>
        <div class="footer-links">
          <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
        </div>
      </div>
    </footer>
  </body>
</html>
