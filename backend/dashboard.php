<?php
// dashboard.php - Admin analytics panel for KAWAL
require_once 'config.php';

// Authentication Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Logout handling
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Fetch Statistics
try {
    $totalQueries = $pdo->query("SELECT COUNT(*) FROM wacana_logs")->fetchColumn();
    $hoaxQueries = $pdo->query("SELECT COUNT(*) FROM wacana_logs WHERE status = 'hoaks'")->fetchColumn();
    $factQueries = $pdo->query("SELECT COUNT(*) FROM wacana_logs WHERE status = 'fakta'")->fetchColumn();
    $doubtQueries = $pdo->query("SELECT COUNT(*) FROM wacana_logs WHERE status = 'meragukan'")->fetchColumn();

    // Fetch wacana logs with associated users
    $stmt = $pdo->query("
        SELECT w.id, w.text_input, w.ai_analysis, w.status, w.created_at, u.whatsapp_number 
        FROM wacana_logs w
        LEFT JOIN users u ON w.user_id = u.id
        ORDER BY w.created_at DESC 
        LIMIT 50
    ");
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal memuat data dashboard: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin KAWAL</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <header>
        <div class="logo">
            <i class="fa-solid fa-shield-halved" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
            KAWAL <span>Dashboard</span>
        </div>
        <ul class="nav-links">
            <li><a href="index.php"><i class="fa-solid fa-mobile-screen-button"></i> Simulator</a></li>
            <li><a href="dashboard.php" style="color: var(--primary);"><i class="fa-solid fa-chart-pie"></i> Statistik</a></li>
            <li><a href="dashboard.php?logout=1" style="color: var(--danger);"><i class="fa-solid fa-sign-out-alt"></i> Keluar</a></li>
        </ul>
    </header>

    <main class="dashboard-container">
        
        <div class="dashboard-header">
            <div>
                <h1 style="font-size: 2rem; font-weight: 700;">Statistik Pemantauan Hoaks</h1>
                <p style="color: var(--text-muted);">Data real-time wacana dan berita lokal yang telah dikawal oleh AI.</p>
            </div>
            <div>
                <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: var(--primary); border: 1px solid rgba(99, 102, 241, 0.2); padding: 8px 16px; border-radius: 10px;">
                    <i class="fa-solid fa-circle" style="color: var(--accent); font-size: 0.7rem; margin-right: 6px; animation: pulse 2s infinite;"></i>
                    Sistem Aktif
                </span>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="stats-grid">
            <div class="glass stat-card">
                <span class="stat-label">Total Info Dikawal</span>
                <span class="stat-value" style="color: #fff;"><i class="fa-solid fa-comments" style="color: var(--primary); margin-right: 8px; font-size: 1.5rem;"></i><?php echo $totalQueries; ?></span>
            </div>
            <div class="glass stat-card" style="border-left: 4px solid var(--danger);">
                <span class="stat-label">Laporan Hoaks</span>
                <span class="stat-value" style="color: var(--danger);"><i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px; font-size: 1.5rem;"></i><?php echo $hoaxQueries; ?></span>
            </div>
            <div class="glass stat-card" style="border-left: 4px solid var(--accent);">
                <span class="stat-label">Klarifikasi Fakta</span>
                <span class="stat-value" style="color: var(--accent);"><i class="fa-solid fa-circle-check" style="margin-right: 8px; font-size: 1.5rem;"></i><?php echo $factQueries; ?></span>
            </div>
            <div class="glass stat-card" style="border-left: 4px solid var(--warning);">
                <span class="stat-label">Meragukan/Abu-abu</span>
                <span class="stat-value" style="color: var(--warning);"><i class="fa-solid fa-circle-question" style="margin-right: 8px; font-size: 1.5rem;"></i><?php echo $doubtQueries; ?></span>
            </div>
        </div>

        <!-- Chart and Details Section -->
        <div class="charts-section">
            
            <!-- Real-time Wacana Log Table -->
            <div class="glass chart-card" style="flex: 1.5; min-height: 450px;">
                <h3><i class="fa-solid fa-list-check" style="color: var(--primary); margin-right: 8px;"></i> Log Aktivitas Terkini</h3>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Pengguna (WA)</th>
                                <th>Teks Masukan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 40px 0;">
                                        Belum ada wacana yang masuk. Coba kirim sesuatu di simulator!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td style="white-space: nowrap; color: var(--text-muted); font-size: 0.8rem;">
                                            <?php echo date('d M H:i', strtotime($log['created_at'])); ?>
                                        </td>
                                        <td style="font-weight: 600; color: #fff;">
                                            <?php echo esc($log['whatsapp_number']); ?>
                                        </td>
                                        <td style="max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?php echo esc($log['text_input']); ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo esc($log['status']); ?>">
                                                <?php echo esc($log['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pie Chart Card -->
            <div class="glass chart-card" style="flex: 1; align-items: center; justify-content: space-between;">
                <h3 style="align-self: flex-start;"><i class="fa-solid fa-chart-pie" style="color: var(--secondary); margin-right: 8px;"></i> Distribusi Kategori</h3>
                <div class="chart-container" style="width: 100%; height: 100%;">
                    <?php if ($totalQueries == 0): ?>
                        <div style="color: var(--text-muted); text-align: center; margin-top: 50px;">Tidak ada data untuk diagram</div>
                    <?php else: ?>
                        <canvas id="distributionChart"></canvas>
                    <?php endif; ?>
                </div>
                <div style="width: 100%; display: flex; justify-content: space-around; font-size: 0.75rem; color: var(--text-muted); margin-top: 15px; border-top: 1px solid var(--card-border); padding-top: 15px;">
                    <div><i class="fa-solid fa-circle" style="color: var(--danger); margin-right: 4px;"></i> Hoaks: <?php echo $totalQueries > 0 ? round(($hoaxQueries/$totalQueries)*100) : 0; ?>%</div>
                    <div><i class="fa-solid fa-circle" style="color: var(--accent); margin-right: 4px;"></i> Fakta: <?php echo $totalQueries > 0 ? round(($factQueries/$totalQueries)*100) : 0; ?>%</div>
                    <div><i class="fa-solid fa-circle" style="color: var(--warning); margin-right: 4px;"></i> Meragukan: <?php echo $totalQueries > 0 ? round(($doubtQueries/$totalQueries)*100) : 0; ?>%</div>
                </div>
            </div>

        </div>

    </main>

    <script>
        <?php if ($totalQueries > 0): ?>
        // Initialize Chart.js Pie Chart
        const ctx = document.getElementById('distributionChart').getContext('2d');
        const distributionChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Hoaks', 'Fakta', 'Meragukan'],
                datasets: [{
                    data: [<?php echo $hoaxQueries; ?>, <?php echo $factQueries; ?>, <?php echo $doubtQueries; ?>],
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.75)',  // Red for Hoax
                        'rgba(34, 197, 94, 0.75)',  // Green for Fact
                        'rgba(234, 179, 8, 0.75)'   // Yellow for Doubtful
                    ],
                    borderColor: [
                        '#ef4444',
                        '#22c55e',
                        '#eab308'
                    ],
                    borderWidth: 1.5,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false // We use our own legend below chart
                    }
                },
                cutout: '65%' // Make it a nice ring chart
            }
        });
        <?php endif; ?>
    </script>

    <style>
        @keyframes pulse {
            0% { opacity: 0.4; }
            50% { opacity: 1; }
            100% { opacity: 0.4; }
        }
    </style>

</body>
</html>
