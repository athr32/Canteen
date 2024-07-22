document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.food-item input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', showOrderSummary);
    });

    document.querySelectorAll('.quantity-controls input').forEach(input => {
        input.addEventListener('change', showOrderSummary);
    });
});
