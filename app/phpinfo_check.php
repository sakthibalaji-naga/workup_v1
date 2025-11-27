<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Extensions Check - WorkUp</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        .extension-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }
        .extension-card {
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #10b981;
            background: #f0fdf4;
        }
        .extension-card.missing {
            border-left-color: #ef4444;
            background: #fef2f2;
        }
        .extension-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.2em;
        }
        .status {
            font-weight: bold;
            font-size: 1.1em;
        }
        .status.enabled {
            color: #10b981;
        }
        .status.disabled {
            color: #ef4444;
        }
        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .info-section h3 {
            color: #333;
            margin-bottom: 15px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: 600;
            color: #666;
        }
        .value {
            color: #333;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 PHP Extensions Status</h1>
        <p class="subtitle">CodeIgniter Application - WorkUp Docker Environment</p>

        <div class="info-section">
            <h3>📋 PHP Information</h3>
            <div class="info-item">
                <span class="label">PHP Version:</span>
                <span class="value"><?php echo phpversion(); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Server Software:</span>
                <span class="value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></span>
            </div>
            <div class="info-item">
                <span class="label">Document Root:</span>
                <span class="value"><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'N/A'; ?></span>
            </div>
        </div>

        <h3 style="margin-top: 30px; color: #333;">📦 Required Extensions Status</h3>
        
        <div class="extension-grid">
            <?php
            $required_extensions = [
                'imap' => 'IMAP Extension',
                'gd' => 'GD Extension',
                'zip' => 'Zip Extension',
                'pdo_mysql' => 'PDO MySQL',
                'mysqli' => 'MySQLi',
                'mbstring' => 'Multibyte String',
                'curl' => 'cURL',
                'openssl' => 'OpenSSL'
            ];

            $loaded_extensions = get_loaded_extensions();
            
            foreach ($required_extensions as $ext => $name) {
                $is_loaded = in_array($ext, $loaded_extensions);
                $card_class = $is_loaded ? '' : ' missing';
                $status_class = $is_loaded ? 'enabled' : 'disabled';
                $status_text = $is_loaded ? '✓ Enabled' : '✗ Not Enabled';
                
                echo "<div class='extension-card{$card_class}'>";
                echo "<h3>{$name}</h3>";
                echo "<p class='status {$status_class}'>{$status_text}</p>";
                echo "</div>";
            }
            ?>
        </div>

        <div class="info-section">
            <h3>🗄️ Database Connection Test</h3>
            <?php
            $host = 'mysql';
            $db = 'workup_db';
            $user = 'workup_user';
            $pass = 'workup_pass';
            
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
                echo '<div class="info-item">';
                echo '<span class="label">MySQL Connection:</span>';
                echo '<span class="value" style="color: #10b981;">✓ Connected</span>';
                echo '</div>';
                echo '<div class="info-item">';
                echo '<span class="label">Database:</span>';
                echo '<span class="value">' . $db . '</span>';
                echo '</div>';
            } catch (PDOException $e) {
                echo '<div class="info-item">';
                echo '<span class="label">MySQL Connection:</span>';
                echo '<span class="value" style="color: #ef4444;">✗ Failed</span>';
                echo '</div>';
                echo '<div class="info-item">';
                echo '<span class="label">Error:</span>';
                echo '<span class="value" style="color: #ef4444;">' . $e->getMessage() . '</span>';
                echo '</div>';
            }
            ?>
        </div>

        <div class="info-section">
            <h3>ℹ️ IMAP Extension Note</h3>
            <p style="color: #666; line-height: 1.6;">
                The IMAP extension is not available in PHP 8.1 on Debian Trixie due to deprecated dependencies.
                <br><br>
                <strong>Recommended Solution:</strong> Use the <code>php-imap/php-imap</code> Composer package as a pure PHP alternative.
                <br><br>
                <strong>Installation:</strong><br>
                <code style="background: #2d3748; color: #68d391; padding: 5px 10px; border-radius: 4px; display: inline-block; margin-top: 10px;">
                composer require php-imap/php-imap
                </code>
            </p>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #dbeafe; border-left: 4px solid #3b82f6; border-radius: 8px;">
            <strong>📁 Application Folder:</strong> /var/www/html<br>
            <strong>🌐 Application URL:</strong> <a href="http://localhost:8080" style="color: #3b82f6;">http://localhost:8080</a><br>
            <strong>🗃️ phpMyAdmin:</strong> <a href="http://localhost:8081" style="color: #3b82f6;">http://localhost:8081</a>
        </div>
    </div>
</body>
</html>
