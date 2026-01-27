        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.getElementById('wrapper').classList.toggle('sidebar-collapsed');
        });
    }

    // Persist sidebar state
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        document.getElementById('wrapper').classList.add('sidebar-collapsed');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            localStorage.setItem('sidebarCollapsed', document.getElementById('wrapper').classList.contains('sidebar-collapsed'));
        });
    }
</script>
<?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>
