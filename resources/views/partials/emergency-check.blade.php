<script>
// Real-time emergency shutdown check for customer pages
(function() {
    let checkInterval;

    function checkEmergencyShutdown() {
        fetch('/api/emergency-status')
            .then(response => response.json())
            .then(data => {
                if (data.emergency_shutdown) {
                    // Emergency shutdown activated - redirect to maintenance page
                    console.log('Emergency shutdown detected! Redirecting to maintenance page...');
                    clearInterval(checkInterval);
                    location.reload(); // Reload will show maintenance page via middleware
                }
            })
            .catch(error => {
                console.error('Error checking emergency status:', error);
            });
    }

    // Check every 3 seconds
    checkInterval = setInterval(checkEmergencyShutdown, 3000);

    // Also check immediately
    checkEmergencyShutdown();
})();
</script>
