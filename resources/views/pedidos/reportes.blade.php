<script>
    document.addEventListener('DOMContentLoaded', function() {
        const generateButtons = document.querySelectorAll('.btn-generate');
        
        generateButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const spinner = this.querySelector('.loading-spinner');
                spinner.style.display = 'inline-block';
                
                const url = this.getAttribute('href');
                
                // Redirigir directamente para descargar el PDF
                window.location.href = url;
                
                // Ocultar el spinner después de un tiempo razonable
                setTimeout(() => {
                    spinner.style.display = 'none';
                }, 3000);
            });
        });
    });
</script>