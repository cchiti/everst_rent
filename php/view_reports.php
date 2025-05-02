<?php
include 'db_connect.php';

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Total Earnings (All Months)
$total_earnings_sql = "SELECT SUM(total_cost) AS total_earnings FROM bookings";
$total_earnings_result = $conn->query($total_earnings_sql);
if (!$total_earnings_result) {
    die("Error fetching total earnings: " . $conn->error);
}
$total_earnings = $total_earnings_result->fetch_assoc()['total_earnings'] ?? 0;

// Total Maintenance Cost (All Months)
$total_maintenance_cost_sql = "SELECT SUM(cost) AS total_maintenance_cost FROM maintenance_requests";
$total_maintenance_cost_result = $conn->query($total_maintenance_cost_sql);
if (!$total_maintenance_cost_result) {
    die("Error fetching total maintenance cost: " . $conn->error);
}
$total_maintenance_cost = $total_maintenance_cost_result->fetch_assoc()['total_maintenance_cost'] ?? 0;

// Total Earnings Net (after maintenance cost)
$total_earnings_net = $total_earnings - $total_maintenance_cost;

// Maintenance Requests (Last Month)
$last_month_maintenance_sql = "
    SELECT COUNT(*) AS maintenance_last_month 
    FROM maintenance_requests 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
$last_month_maintenance_result = $conn->query($last_month_maintenance_sql);
if (!$last_month_maintenance_result) {
    die("Error fetching maintenance requests: " . $conn->error);
}
$maintenance_last_month = $last_month_maintenance_result->fetch_assoc()['maintenance_last_month'] ?? 0;

// Earnings (Last Month)
$last_month_earnings_sql = "
    SELECT SUM(total_cost) AS earnings_last_month 
    FROM bookings 
    WHERE MONTH(booking_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
    AND YEAR(booking_date) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
$last_month_earnings_result = $conn->query($last_month_earnings_sql);
if (!$last_month_earnings_result) {
    die("Error fetching last month's earnings: " . $conn->error);
}
$earnings_last_month = $last_month_earnings_result->fetch_assoc()['earnings_last_month'] ?? 0;

// Maintenance Cost (Last Month)
$last_month_maintenance_sql = "
    SELECT SUM(cost) AS maintenance_last_month 
    FROM maintenance_requests 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
$last_month_maintenance_result = $conn->query($last_month_maintenance_sql);
if (!$last_month_maintenance_result) {
    die("Error fetching last month's maintenance cost: " . $conn->error);
}
$maintenance_last_month = $last_month_maintenance_result->fetch_assoc()['maintenance_last_month'] ?? 0;

// Net Earnings (Last Month)
$net_earnings_last_month = $earnings_last_month - $maintenance_last_month;

// Users Joined (Last Month)
$last_month_users_sql = "
    SELECT COUNT(*) AS users_last_month 
    FROM users 
    WHERE MONTH(joinAt) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) 
    AND YEAR(joinAt) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)";
$last_month_users_result = $conn->query($last_month_users_sql);
if (!$last_month_users_result) {
    die("Error fetching users joined last month: " . $conn->error);
}
$users_last_month = $last_month_users_result->fetch_assoc()['users_last_month'] ?? 0;


// Generate a list of all months for the current year
$currentYear = date('Y');
$allMonths = [];
for ($i = 1; $i <= 12; $i++) {
    $allMonths[] = sprintf('%s-%02d', $currentYear, $i);
}

// Fetch revenue data
$monthly_revenue_sql = "
    SELECT 
        DATE_FORMAT(start_date, '%Y-%m') AS month, 
        SUM(total_cost) AS revenue 
    FROM bookings 
    WHERE status = 'Confirmed'
    GROUP BY DATE_FORMAT(start_date, '%Y-%m') 
    ORDER BY DATE_FORMAT(start_date, '%Y-%m') ASC";

$monthly_revenue_result = $conn->query($monthly_revenue_sql);

$months = [];
$revenues = [];

// Initialize revenue data with 0 for all months
$revenueData = array_fill_keys($allMonths, 0);

if ($monthly_revenue_result->num_rows > 0) {
    while ($row = $monthly_revenue_result->fetch_assoc()) {
        $revenueData[$row['month']] = $row['revenue'];
    }
}

// Extract months and revenues for the chart
$months = array_keys($revenueData);
$revenues = array_values($revenueData);

// Total Earnings (Current Month)
$current_month_earnings_sql = "
    SELECT SUM(total_cost) AS current_month_earnings 
    FROM bookings 
    WHERE MONTH(booking_date) = MONTH(CURRENT_DATE) 
    AND YEAR(booking_date) = YEAR(CURRENT_DATE)";
$current_month_earnings_result = $conn->query($current_month_earnings_sql);
if (!$current_month_earnings_result) {
    die("Error fetching current month's earnings: " . $conn->error);
}
$current_month_earnings = $current_month_earnings_result->fetch_assoc()['current_month_earnings'] ?? 0;

// Maintenance Requests (Current Month)
$current_month_maintenance_sql = "
    SELECT COUNT(*) AS maintenance_current_month 
    FROM maintenance_requests 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE)";
$current_month_maintenance_result = $conn->query($current_month_maintenance_sql);
if (!$current_month_maintenance_result) {
    die("Error fetching current month's maintenance requests: " . $conn->error);
}
$maintenance_current_month = $current_month_maintenance_result->fetch_assoc()['maintenance_current_month'] ?? 0;

// Users Joined (Current Month)
$current_month_users_sql = "
    SELECT COUNT(*) AS users_current_month 
    FROM users 
    WHERE MONTH(joinAt) = MONTH(CURRENT_DATE) 
    AND YEAR(joinAt) = YEAR(CURRENT_DATE)";
$current_month_users_result = $conn->query($current_month_users_sql);
if (!$current_month_users_result) {
    die("Error fetching current month's users joined: " . $conn->error);
}
$users_current_month = $current_month_users_result->fetch_assoc()['users_current_month'] ?? 0;

// Total Maintenance Cost (Current Month) based on created_at
$current_month_maintenance_cost_sql = "
    SELECT SUM(cost) AS maintenance_cost_current_month 
    FROM maintenance_requests 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE)";
$current_month_maintenance_cost_result = $conn->query($current_month_maintenance_cost_sql);
if (!$current_month_maintenance_cost_result) {
    die("Error fetching current month's maintenance cost: " . $conn->error);
}
$maintenance_cost_current_month = $current_month_maintenance_cost_result->fetch_assoc()['maintenance_cost_current_month'] ?? 0;

$total_earning_current_month = $current_month_earnings - $maintenance_cost_current_month;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4cc9f0;
            --warning-color: #f8961e;
            --danger-color: #f72585;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 30px;
            text-align: center;
            font-size: 2.2rem;
        }

        .reports-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            transition: var(--transition);
            border-top: 4px solid var(--primary-color);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card:nth-child(1) {
            border-top-color: var(--success-color);
        }

        .card:nth-child(2) {
            border-top-color: var(--warning-color);
        }

        .card:nth-child(3) {
            border-top-color: var(--accent-color);
        }

        .card:nth-child(4) {
            border-top-color: var(--danger-color);
        }

        .card h3 {
            color: var(--dark-color);
            font-size: 1.1rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .card p {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .card:nth-child(1) p {
            color: var(--success-color);
        }

        .card:nth-child(2) p {
            color: var(--warning-color);
        }

        .card:nth-child(3) p {
            color: var(--accent-color);
        }

        .card:nth-child(4) p {
            color: var(--danger-color);
        }
        .chart-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-top: 40px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }

        #revenueChart {
            width: 100% !important;
            height: 400px !important;
        }

        @media (max-width: 768px) {
            .summary-cards {
                grid-template-columns: 1fr;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="reports-content">
        <h1>View Reports</h1>
        <div class="summary-cards">
            <div class="card">
                <h3>Total Earnings </h3>
                <h6>(All Months)</h6>
                <p>$<?= number_format($total_earnings_net, 2) ?></p>
            </div>
            <div class="card">
                <h3>Maintenance Cost</h3>
                <h6>(Last Months)</h6>

                <p><?= htmlspecialchars($maintenance_last_month) ?></p>
            </div>
            <div class="card">
                <h3>Earnings</h3>
                <h6>(Last Months)</h6>

                <p>$<?= number_format($net_earnings_last_month, 2) ?></p>
            </div>
            <div class="card">
                <h3>Users Joined</h3>
                <h6>(Last Months)</h6>

                <p><?= htmlspecialchars($users_last_month) ?></p>
            </div>
            <div class="card">
    <h3>Total Earnings</h3>
    <h6>(Current Month)</h6>
    <p>$<?= number_format($total_earning_current_month, 2) ?></p>
</div>
<div class="card">
    <h3>Maintenance Cost</h3>
    <h6>(Current Month)</h6>
    <p style="color:red">$<?= number_format($maintenance_cost_current_month, 2) ?></p>
</div>
<div class="card">
    <h3>Maintenance Requests</h3>
    <h6>(Current Month)</h6>
    <p><?= htmlspecialchars($maintenance_current_month) ?></p>
</div>

<div class="card">
    <h3>Users Joined</h3>
    <h6>(Current Month)</h6>
    <p><?= htmlspecialchars($users_current_month) ?></p>
</div>
        </div>
    </div>

    <div class="chart-container">
        <h1>Revenue Graph</h1>
        <canvas id="revenueChart"></canvas>
        <div id="chartError" style="color: red; display: none;">Error loading chart. Please check console for details.</div>
    </div>


    <!-- Load jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Then load Chart.js with integrity check -->
<script src="https://cdn.jsdelivr.net/npm/chart.js" 
        integrity="sha384-6eD7W4Z5+0yD7Q9RZJXZ5Q5Z5Y5Q5Z5Y5Q5Z5Y5Q5Z5Y5Q5Z5Y5Q5Z5Y5Q5Z5Y5Q==" 
        crossorigin="anonymous"></script>

<script>
// Wait for everything to be ready
$(document).ready(function() {
    // First check if Chart.js loaded properly
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded!');
        document.getElementById('chartError').style.display = 'block';
        document.getElementById('chartError').textContent = 'Chart library failed to load. Please refresh the page.';
        return;
    }

    try {
        const months = <?= json_encode($months ?? []) ?>;
        const revenues = <?= json_encode($revenues ?? []) ?>;
        
        console.log('Chart Data:', { months, revenues });

        if (!months || !revenues || months.length === 0 || revenues.length === 0) {
            document.getElementById('chartError').style.display = 'block';
            document.getElementById('chartError').textContent = 'No revenue data available to display chart.';
            return;
        }

        const formattedMonths = months.map(month => {
            const [year, monthNum] = month.split('-');
            return new Date(year, monthNum - 1).toLocaleString('default', { month: 'short' }) + ' ' + year;
        });

        const numericRevenues = revenues.map(rev => Number(rev));

        const ctx = document.getElementById('revenueChart');
        if (!ctx) {
            console.error("Canvas element not found");
            return;
        }
        
        // Destroy existing chart if it exists
        const existingChart = Chart.getChart(ctx);
        if (existingChart) {
            existingChart.destroy();
        }

        // Create new chart
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: formattedMonths,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Monthly Revenue ($)',
                        data: numericRevenues,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        order: 2
                    },
                    {
                        type: 'line',
                        label: 'Revenue Trend',
                        data: numericRevenues,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '$' + context.raw.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Month',
                            font: { weight: 'bold' }
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Revenue ($)',
                            font: { weight: 'bold' }
                        },
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
        
    } catch (error) {
        console.error('Chart creation error:', error);
        document.getElementById('chartError').style.display = 'block';
        document.getElementById('chartError').textContent = 'Error loading chart: ' + error.message;
    }
});
</script>


  
</body>
</html>