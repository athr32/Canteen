function toggleQuantityControls(checkbox) {
    const quantityControls = checkbox.nextElementSibling;
    quantityControls.style.display = checkbox.checked ? 'flex' : 'none';
}

function adjustQuantity(button, increment) {
    const input = button.parentElement.querySelector('input[type="number"]');
    let currentValue = parseInt(input.value);
    if (increment) {
        currentValue += 1;
    } else {
        currentValue = Math.max(1, currentValue - 1);
    }
    input.value = currentValue;
}

document.getElementById('checkout-button').addEventListener('click', function () {
    const foodItems = document.querySelectorAll('.food-item');
    const orderSummary = document.getElementById('order-summary');
    orderSummary.innerHTML = '<h2>Order Summary</h2>';
    let total = 0;

    foodItems.forEach(item => {
        const checkbox = item.querySelector('input[type="checkbox"]');
        if (checkbox.checked) {
            const quantity = parseInt(item.querySelector('input[type="number"]').value);
            const price = parseInt(item.querySelector('span').textContent.split('₹')[1]);
            const name = item.querySelector('span').textContent.split(' - ₹')[0];
            total += price * quantity;
            orderSummary.insertAdjacentHTML('beforeend', `<p>${name} x ${quantity} = ₹${price * quantity}</p>`);
        }
    });

    orderSummary.insertAdjacentHTML('beforeend', `<h3>Total: ₹${total}</h3>`);
});
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