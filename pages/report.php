<?php
require_once __DIR__ . '/../configs/db_functions.php';
require_once __DIR__ . '/../configs/functions.php';
session_start(); // MUST be first line

// Fake login credentials (for demo)
$validUser = "admin@gmail.com";
$validPass = "admin@gmail.com";

// Handle login
if (isset($_POST['login'])) {
    if ($_POST['username'] === $validUser && $_POST['password'] === $validPass) {
        session_regenerate_id(true);
        $_SESSION['user'] = $validUser;
    } else {
        $error = "Invalid login!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Reports</title>
</head>

<body>

    <?php if (!isset($_SESSION['user'])): ?>

        <style>
            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                background: #f4f6f8;
                font-family: Arial, sans-serif;
            }

            .login-box {
                width: 300px;
                padding: 25px;
                background: #fff;
                border-radius: 6px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                text-align: center;
            }

            .login-box h2 {
                margin-bottom: 20px;
            }

            .login-box input {
                width: 100%;
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }

            .login-box button {
                width: 100%;
                padding: 10px;
                background: #007bff;
                border: none;
                color: #fff;
                font-size: 16px;
                border-radius: 4px;
                cursor: pointer;
            }

            .login-box button:hover {
                background: #0056b3;
            }

            .error {
                color: red;
                margin-bottom: 15px;
            }

            input:focus {
                outline: none;
                border-color: #007bff;
                box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            }

            .login-box {
                animation: fadeIn 0.3s ease-in-out;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: scale(0.95);
                }

                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }
        </style>
        <div class="login-box">
            <h2>Login</h2>

            <?php if (isset($error)): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <div>
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
        </div>

    <?php else: ?>
        <style>
            body {
                font-family: Arial;
                background: #f4f6f8;
            }

            h1 {
                margin-bottom: 15px
            }

            table {
                width: 100%;
                border-collapse: collapse;
                background: #fff
            }

            th,
            td {
                padding: 10px;
                border: 1px solid #ddd;
                font-size: 13px
            }

            th {
                background: #222;
                color: #fff
            }

            tr:nth-child(even) {
                background: #f9f9f9
            }
            .filter-form {
                display: flex;
                align-items: flex-end;
                gap: 12px;
                padding: 12px 0;
                background: #f8f9fa;
                border-radius: 6px;
                margin-bottom: 15px;
            }

            .filter-item {
                display: flex;
                flex-direction: column;
            }

            .filter-item label {
                font-size: 13px;
                margin-bottom: 4px;
                color: #555;
            }

            .filter-item input {
                padding: 6px 8px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }

            .filter-btn button {
                padding: 7px 16px;
                background: #0d6efd;
                color: #fff;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }

            .filter-btn button:hover {
                background: #0b5ed7;
            }

        </style>
        <?php
        $where = [];
        $uuids = "";
        $startDate = "";
        $endDate = "";

        // UUID filter (comma-separated)
        if (!empty($_GET['uuid'])) {
            $uuids = $_GET['uuid'];
            $uuidArray = array_filter(array_map('trim', explode(',', $_GET['uuid'])));
            if ($uuidArray) {
                $where['uuid'] = $uuidArray;
            }
        }
        // Visit_time filter
        $startDate = $_GET['start_date'] ?? '';
        $endDate   = $_GET['end_date'] ?? '';

        if ($startDate && $endDate) {
            // Both start and end
            $where['visit_time'] = [
                'start' => $startDate . ' 00:00:00',
                'end'   => $endDate . ' 23:59:59'
            ];
            $startDate = $startDate . ' 00:00:00';
            $endDate = $endDate . ' 23:59:59';
        } elseif ($startDate) {
            // Only start date
            $where['visit_time>='] = $startDate . ' 00:00:00';
            $startDate = $startDate . ' 00:00:00';
        } elseif ($endDate) {
            // Only end date
            $where['visit_time<='] = $endDate . ' 23:59:59';
            $endDate = $endDate . ' 23:59:59';
        }
        // echo '<pre>';print_r($where);die;
        $visitors = $getAllVisitors($where);


        $visitors_events = getAllVisitorsEventsLog($uuids, $startDate, $endDate);
        // echo '<pre>';print_r($visitors);die;
        ?>


        <div style="display: flex;justify-content: start;">
            <a href="<?= base_url("report") ?>" style="margin-right: 10px;">Report</a>
            <a href="<?= base_url("logout") ?>">Logout</a>
        </div>

        <form method="get" class="filter-form">
            <!-- Start Date -->
            <div class="filter-item">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date"
                    value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
            </div>

            <!-- End Date -->
            <div class="filter-item">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date"
                    value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
            </div>

            <!-- UUID -->
            <div class="filter-item">
                <label for="uuid">UUID(s) <small style="color:#555; font-size:12px;">(Comma separated)</small></label>
                <input type="text" id="uuid" name="uuid" placeholder="abc,def,ghi"
                    value="<?= htmlspecialchars($_GET['uuid'] ?? '') ?>">
                
            </div>

            <!-- Filter button -->
            <div class="filter-item filter-btn">
                <button type="submit">Filter</button>
            </div>
            <!-- Reset button -->
            <div class="filter-item filter-btn">
                <a href="<?= base_url("report") ?>" style="text-decoration: none; color: #fff;"><button type="button">Clear</button></a>
            </div>
        </form>




        <h3 style="margin-bottom: 5px;">User Traking details</h3>
        <table>
            <tr>
                <th>Time</th>
                <th>UUID</th>
                <th>IP</th>
                <th>City</th>
                <th>Country</th>
                <th>Page URL</th>
                <th>Device</th>
                <th>Browser</th>
            </tr>

            <?php
            if (count($visitors) > 0) {
                foreach ($visitors as $key => $value) {
            ?>
                    <tr>
                        <td><?= $value['visit_time'] ?></td>
                        <td><?= $value['uuid'] ?></td>
                        <td><?= $value['ip_address'] ?></td>
                        <td><?= $value['city'] ?></td>
                        <td><?= $value['country'] ?></td>
                        <td><?= $value['page_url'] ?></td>
                        <td><?= $value['device_type'] ?></td>
                        <td><?= $value['browser_type'] ?></td>
                    </tr>
                <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No visitors to show!</td>
                </tr>
            <?php
            }
            ?>

        </table>

        <h4 style="margin-bottom: 5px;">User Activity Details</h4>
        <table>
            <tr>
                <th>Logged At</th>
                <th>UUID</th>
                <th>Event Type</th>
                <th>Event Name</th>
                <th>Source</th>
            </tr>

            <?php
            if (count($visitors_events) > 0) {
                foreach ($visitors_events as $key => $value) {
            ?>
                    <tr>
                        <td><?= $value['logged_at'] ?></td>
                        <td><?= $value['uuid'] ?? 'NA' ?></td>
                        <td><?= $value['event_type'] ?></td>
                        <td><?= $value['event'] ?></td>
                        <td><?= $value['source'] ?? 'NA' ?></td>
                    </tr>
                <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No visitors to show!</td>
                </tr>
            <?php
            }
            ?>

        </table>


    <?php endif; ?>

</body>

</html>