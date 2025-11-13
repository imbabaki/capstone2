<script>
// Hide URL in address bar and prevent URL preview completely
(function() {
    // Replace URL on page load to hide query parameters and specific routes
    if (window.location.pathname !== '/') {
        // Replace the URL with just '/' to hide the actual route
        window.history.replaceState({}, document.title, '/');
    }

    // Convert all links to use data attributes instead of href on page load
    window.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('a[href]');
        links.forEach(function(link) {
            if (link.hostname === window.location.hostname) {
                link.setAttribute('data-url', link.href);
                link.removeAttribute('href');
                link.style.cursor = 'pointer';
            }
        });
    });

    // Also convert dynamically added links
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) {
                    const links = node.querySelectorAll ? node.querySelectorAll('a[href]') : [];
                    links.forEach(function(link) {
                        if (link.hostname === window.location.hostname) {
                            link.setAttribute('data-url', link.href);
                            link.removeAttribute('href');
                            link.style.cursor = 'pointer';
                        }
                    });
                    // If the node itself is a link
                    if (node.tagName === 'A' && node.href && node.hostname === window.location.hostname) {
                        node.setAttribute('data-url', node.href);
                        node.removeAttribute('href');
                        node.style.cursor = 'pointer';
                    }
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Handle link clicks
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (link && link.getAttribute('data-url')) {
            e.preventDefault();
            window.location.href = link.getAttribute('data-url');
        }
    });

    // Handle form submissions - store action in data attribute
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form[action]');
        forms.forEach(function(form) {
            form.setAttribute('data-action', form.action);
            form.removeAttribute('action');
        });
    });

    // Intercept form submissions
    document.addEventListener('submit', function(e) {
        const form = e.target;
        const action = form.getAttribute('data-action') || form.action;

        if (!action) return;

        e.preventDefault();

        if (form.method.toLowerCase() === 'post') {
            // For POST forms, create a temporary form and submit
            const tempForm = document.createElement('form');
            tempForm.method = 'POST';
            tempForm.action = action;
            tempForm.style.display = 'none';

            // Copy all form data
            const formData = new FormData(form);
            formData.forEach(function(value, key) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                tempForm.appendChild(input);
            });

            document.body.appendChild(tempForm);
            tempForm.submit();
        } else {
            // For GET forms
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            const url = action + (params.toString() ? '?' + params.toString() : '');
            window.location.href = url;
        }
    });
})();
</script>
