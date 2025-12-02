document.addEventListener('DOMContentLoaded', function() {

    const productBtn = document.getElementById('product-btn');
    const productDropdown = document.getElementById('product-dropdown');

    if (productBtn && productDropdown) {
        
        productBtn.addEventListener('click', function(event) {
            event.stopPropagation(); 
            productDropdown.classList.toggle('show');
        });

        window.addEventListener('click', function(event) {
            if (productDropdown.classList.contains('show')) {
                productDropdown.classList.remove('show');
            }
        });
    }

});