<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=800, height=480, initial-scale=1.0">
    <title>Machine Temporarily Disabled</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .maintenance-container {
            text-align: center;
            padding: 4vh 4vw;
            max-width: 90vw;
        }

        .icon-container {
            margin-bottom: 4vh;
            animation: float 3s ease-in-out infinite;
        }

        .icon {
            font-size: 15vh;
            color: #ef4444;
            text-shadow: 0 0 3vh rgba(239, 68, 68, 0.5);
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-2vh);
            }
        }

        h1 {
            font-size: 6vh;
            font-weight: 900;
            color: #ef4444;
            margin-bottom: 3vh;
            text-shadow: 0 0 2vh rgba(239, 68, 68, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.2vw;
        }

        .message {
            font-size: 3.5vh;
            color: #cbd5e1;
            margin-bottom: 2vh;
            font-weight: 600;
            line-height: 1.5;
        }

        .sub-message {
            font-size: 2.5vh;
            color: #94a3b8;
            margin-bottom: 4vh;
            line-height: 1.6;
        }

        .info-box {
            background: rgba(239, 68, 68, 0.1);
            border: 3px solid #ef4444;
            border-radius: 2vh;
            padding: 3vh 3vw;
            margin-top: 3vh;
            box-shadow: 0 0 3vh rgba(239, 68, 68, 0.3);
        }

        .info-box p {
            font-size: 2.2vh;
            color: #fecaca;
            margin-bottom: 1vh;
        }

        .info-box p:last-child {
            margin-bottom: 0;
        }

        .pulse {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="icon-container">
            <div class="icon">⛔</div>
        </div>

        <h1 class="pulse">Machine Temporarily Disabled</h1>

        <p class="message">
            This printing vendo machine is currently unavailable.
        </p>

        <p class="sub-message">
            We apologize for any inconvenience.<br>
            The system has been temporarily shut down for maintenance or emergency purposes.
        </p>

        <div class="info-box">
            <p><strong>⚠️ EMERGENCY SHUTDOWN ACTIVE</strong></p>
            <p>Please contact the administrator for more information.</p>
            <p>Thank you for your patience and understanding.</p>
        </div>
    </div>

    <script>
        // Real-time check every 2 seconds if maintenance is over
        function checkMaintenanceStatus() {
            fetch('/api/emergency-status')
                .then(response => response.json())
                .then(data => {
                    if (!data.emergency_shutdown) {
                        // Machine is back online - reload to homepage
                        console.log('Machine is back online! Redirecting...');
                        location.href = '/';
                    }
                })
                .catch(error => {
                    console.error('Error checking maintenance status:', error);
                });
        }

        // Check every 2 seconds
        setInterval(checkMaintenanceStatus, 2000);

        // Also check immediately
        checkMaintenanceStatus();
    </script>
</body>
</html>
