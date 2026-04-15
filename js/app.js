<script>
// Double click row to add 1 unit to cart
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.table tbody tr');
    rows.forEach(row => {
        row.addEventListener('dblclick', function() {
            const select = this.querySelector('select');
            const form = this.querySelector('form');
            
            if (select && form) {
                // Find first numeric option
                const options = select.options;
                for (let i = 1; i < options.length; i++) {
                    if (!isNaN(options[i].value) && options[i].value > 0) {
                        select.value = options[i].value;
                        break;
                    }
                }
                form.submit();
            }
        });
        
        // Show hint
        row.title = 'Double-click to quick add 1 unit';
    });
});
</script>