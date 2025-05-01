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


// Fetch monthly revenue data
$monthly_revenue_sql = "
    SELECT 
        DATE_FORMAT(booking_date, '%Y-%m') AS month, 
        SUM(total_cost) AS revenue 
    FROM bookings 
    GROUP BY DATE_FORMAT(booking_date, '%Y-%m') 
    ORDER BY DATE_FORMAT(booking_date, '%Y-%m') ASC";
$monthly_revenue_result = $conn->query($monthly_revenue_sql);

$months = [];
$revenues = [];

if ($monthly_revenue_result->num_rows > 0) {
    while ($row = $monthly_revenue_result->fetch_assoc()) {
        $months[] = $row['month'];
        $revenues[] = $row['revenue'];
    }
}
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
                <p>$<?= number_format($total_earnings, 2) ?></p>
            </div>
            <div class="card">
                <h3>Maintenance Requests</h3>
                <h6>(Last Months)</h6>

                <p><?= htmlspecialchars($maintenance_last_month) ?></p>
            </div>
            <div class="card">
                <h3>Earnings</h3>
                <h6>(Last Months)</h6>

                <p>$<?= number_format($earnings_last_month, 2) ?></p>
            </div>
            <div class="card">
                <h3>Users Joined</h3>
                <h6>(Last Months)</h6>

                <p><?= htmlspecialchars($users_last_month) ?></p>
            </div>
        </div>
    </div>

    <div class="chart-container">
        <h1>Revenue Graph</h1>
        <canvas id="revenueChart"></canvas>
        <div id="chartError" style="color: red; display: none;">Error loading chart. Please check console for details.</div>
    </div>

    <!-- Load Chart.js before your script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Your custom script must be placed after the Chart.js library and after PHP variables are available -->
<script>
    console.log('Script executed'); // ✅ This should show

    document.addEventListener('DOMContentLoaded', function() {
        console.log('Script executed 2'); // ✅ This should now show

        try {
            const months = <?= json_encode($months) ?>;
            const revenues = <?= json_encode($revenues) ?>.map(Number);
            
            console.log('Chart Data:', {months, revenues});
            
            if (!months || !revenues || months.length === 0 || revenues.length === 0) {
                document.getElementById('chartError').style.display = 'block';
                document.getElementById('chartError').textContent = 'No revenue data available to display chart.';
                return;
            }

            const formattedMonths = months.map(month => {
                const [year, monthNum] = month.split('-');
                const date = new Date(year, monthNum - 1);
                return date.toLocaleString('default', { month: 'short' }) + ' ' + year;
            });

            const ctx = document.getElementById('revenueChart').getContext('2d');
            if (!ctx) {
                throw new Error('Could not get canvas context');
            }

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: formattedMonths,
                    datasets: [{
                        label: 'Monthly Revenue ($)',
                        data: revenues,
                        backgroundColor: 'rgba(67, 97, 238, 0.2)',
                        borderColor: 'rgba(67, 97, 238, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
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
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Revenue ($)',
                                font: {
                                    weight: 'bold'
                                }
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
            console.error('Chart error:', error);
            document.getElementById('chartError').style.display = 'block';
            document.getElementById('chartError').textContent = 'Error loading chart: ' + error.message;
        }
    });
</script>


</body>
</html>