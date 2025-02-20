<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding-top: 80px; /* Prevent header from covering content */
        }
        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background-color: #343a40;
            color: white;
            padding: 15px;
            z-index: 1000;
        }
        .header .navbar-nav {
            margin-left: auto;
        }
        .header .nav-item {
            margin-left: 20px;
        }
        .header .nav-item i {
            margin-right: 8px;
        }
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            transition: transform 0.3s ease;
        }
        .sidebar.hide {
            transform: translateX(-100%);
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 10px 15px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: #495057;
            border-radius: 5px;
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: #007bff;
            border-radius: 5px;
        }
        .sidebar .nav-item {
            margin-bottom: 10px;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        /* Main Content */
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        /* Fixed Header for User Info */
        .header-user {
            position: fixed;
            top: 0;
            right: 0px;
            padding: 0px;
            padding-right: 20px;
            background-color: white;
            width: calc(100% - 250px); /* Adjust width if sidebar is open */
            z-index: 1001;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: right;

        }
        .header-user .user-name {
            font-weight: bold;
            font-size: 18px;
        }
        .header-user .user-username {
            color:rgb(222, 223, 224);
            font-size: 14px;
        }
        /* Button for Sidebar Toggle */
        .toggle-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #343a40;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
        }
        .toggle-btn i {
            font-size: 20px;
        }
        /* Dropdown menu */
        .dropdown-menu {
            min-width: 150px;
            
        }
        /* Custom button styling */
        .custom-btn {
            background-color: #007bff; /* Change to any color */
            border-color:rgb(125, 157, 192);     /* Adjust border color */
            color: white;              /* Text color */
            padding: 10px 25px;        /* Button padding */
            border-radius: 0px;        /* Rounded corners */
            font-size: 16px;           /* Font size */
            text-align: left;
        }

        .custom-btn:hover {
            background-color: #0056b3; /* Darken button on hover */
            border-color: #004085;
        }

        /* Media Query for Mobile */
        @media (max-width: 767px) {
            .sidebar {
                width: 200px;
            }
            .content {
                margin-left: 0;
                padding-top: 80px; /* Prevent content from being covered by fixed header */
            }
            .header-user {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Toggle Button for Sidebar -->
    <button class="toggle-btn" id="sidebarToggle"><i class="fa fa-bars"></i></button>

    <!-- Sidebar Menu -->
    <div class="sidebar" id="sidebar">
        <h4 class="text-white mb-4 text-center">SITUSAJA</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#"><i class="fa fa-tachometer"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fa fa-users"></i> Anggota</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fa fa-credit-card"></i> Deposit</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fa fa-arrow-down"></i> Withdraw</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fa fa-gamepad"></i> Games</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fa fa-bullhorn"></i> Promo</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fa fa-bank"></i> Rekening</a>
            </li>
        </ul>
    </div>

    

    <!-- Fixed Header for User Info -->
<div class="header-user">
    <!-- Dropdown for Settings and Logout -->
    <div class="dropdown ms-auto">
        <button class="btn custom-btn" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="user-name">Adminmaster</div>
            <div class="user-username">Admin</div>
        </button>

        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <li><a class="dropdown-item" href="#">Change Password</a></li>
            <li><a class="dropdown-item" href="#">Manage Users</a></li>
            <li><a class="dropdown-item" href="#">Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Logout</a></li>
        </ul>
    </div>
</div>


    <!-- Main Content Area -->
    <div class="content">
        <div class="row mt-5">
            <div class="col-md-4">
                <div class="card p-3 mb-4">
                    <div class="card-title">Total Deposit</div>
                    <div class="card-body">
                        <p>Rp 75,000,-</p>
                    </div>
                    <div class="card-footer">
                        <a href="#">Lihat detail data</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 mb-4">
                    <div class="card-title">Total Withdraw</div>
                    <div class="card-body">
                        <p>Rp 50,000,-</p>
                    </div>
                    <div class="card-footer">
                        <a href="#">Lihat detail data</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 mb-4">
                    <div class="card-title">Anggota</div>
                    <div class="card-body">
                        <p>1</p>
                    </div>
                    <div class="card-footer">
                        <a href="#">Lihat detail data</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle Sidebar visibility and change icon
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('hide');
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times'); // Toggle between hamburger and close icon
        });
    </script>
</body>
</html>
