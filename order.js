document.addEventListener('DOMContentLoaded', () => {
    const orderDetails = document.getElementById('order_details');
    const orderStatus = document.getElementById('order_status');

    // Simulate placing an order
    function placeOrder() {
        orderDetails.classList.add('show');
        orderStatus.textContent = 'Your order is being prepared...';

        // Simulate canteen owner actions
        setTimeout(() => {
            orderStatus.textContent = 'Your order is ready for pickup!';
        }, 5000); // Order ready after 5 seconds

        setTimeout(() => {
            orderDetails.classList.remove('show');
            orderStatus.textContent = 'You don\'t have any pending order.';
        }, 10000); // Order complete after another 5 seconds
    }

    // Simulate clicking the checkout button
    placeOrder();
});