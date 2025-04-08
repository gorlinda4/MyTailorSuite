// Real-time updates with SSE
function setupRealTimeUpdates() {
    const eventSource = new EventSource('sse.php');
    
    eventSource.addEventListener('notification', function(e) {
        const notification = JSON.parse(e.data);
        showNotification(notification.title, notification.message);
        
        // Update notification badge
        updateNotificationCount();
    });
    
    eventSource.addEventListener('order_update', function(e) {
        const update = JSON.parse(e.data);
        
        // Update order status in the UI
        updateOrderStatus(update.order_id, update.status);
        
        // Show notification
        showNotification('Order Update', `Order #${update.order_id} status changed to ${update.status}`);
    });
    
    eventSource.addEventListener('payment_update', function(e) {
        const payment = JSON.parse(e.data);
        
        // Update payment status in the UI
        updatePaymentStatus(payment.payment_id, payment.status);
        
        if(payment.status === 'completed') {
            showNotification('Payment Completed', `Payment for order #${payment.order_id} has been completed`);
        }
    });
    
    eventSource.onerror = function() {
        console.error('SSE connection error');
        // Attempt to reconnect after 5 seconds
        setTimeout(setupRealTimeUpdates, 5000);
    };
}

// Update order status in the UI
function updateOrderStatus(orderId, newStatus) {
    // Find all elements showing this order's status and update them
    document.querySelectorAll(`[data-order-id="${orderId}"] .order-status`).forEach(el => {
        el.textContent = newStatus;
        el.className = 'order-status status-' + newStatus.toLowerCase().replace(' ', '-');
    });
    
    // If on the order details page, refresh the data
    if(document.getElementById('order-details')?.dataset.orderId === orderId) {
        loadOrderDetails(orderId);
    }
}

// Update payment status in the UI
function updatePaymentStatus(paymentId, newStatus) {
    // Find all elements showing this payment's status and update them
    document.querySelectorAll(`[data-payment-id="${paymentId}"] .payment-status`).forEach(el => {
        el.textContent = newStatus;
        el.className = 'payment-status status-' + newStatus.toLowerCase();
    });
}

// Update notification count
function updateNotificationCount() {
    fetch('/api/notifications?unread=true')
        .then(response => response.json())
        .then(data => {
            const count = data.notifications.length;
            document.getElementById('notification-badge').textContent = count;
        });
}

// M-Pesa payment handler
function processMpesaPayment(orderId) {
    const phoneNumber = document.getElementById('mpesa-phone').value;
    
    if(!phoneNumber) {
        alert('Please enter your M-Pesa phone number');
        return;
    }
    
    fetch(`/api/orders/${orderId}/payments`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            payment_method: 'mpesa',
            phone_number: phoneNumber
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            if(data.result.status === 'pending') {
                showPaymentPending(data.result.message);
            } else {
                showPaymentSuccess();
            }
        } else {
            showPaymentError(data.error);
        }
    })
    .catch(error => {
        showPaymentError(error.message);
    });
}

// Show payment pending message
function showPaymentPending(message) {
    document.getElementById('payment-status').innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> ${message}
        </div>
    `;
    
    // Poll for payment status update
    const pollInterval = setInterval(() => {
        fetch(`/api/payments/${paymentId}`)
            .then(response => response.json())
            .then(data => {
                if(data.payment.status === 'completed') {
                    clearInterval(pollInterval);
                    showPaymentSuccess();
                } else if(data.payment.status === 'failed') {
                    clearInterval(pollInterval);
                    showPaymentError('Payment failed');
                }
            });
    }, 3000);
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Setup real-time updates
    setupRealTimeUpdates();
    
    // Load initial data based on user role
    loadDashboardData();
    
    // Setup event listeners for payment methods
    document.getElementById('mpesa-pay-btn').addEventListener('click', function() {
        const orderId = this.dataset.orderId;
        processMpesaPayment(orderId);
    });
    
    // Other initialization code...
});

// Load dashboard data based on user role
function loadDashboardData() {
    fetch('/api/user')
        .then(response => response.json())
        .then(data => {
            const user = data.user;
            
            // Update UI with user info
            document.getElementById('user-name').textContent = 
                `${user.first_name} ${user.last_name}`;
            document.getElementById('user-role').textContent = 
                user.role.charAt(0).toUpperCase() + user.role.slice(1);
            
            // Load role-specific data
            if(user.role === 'customer') {
                loadCustomerDashboard();
            } else if(user.role === 'tailor') {
                loadTailorDashboard();
            } else if(user.role === 'manager') {
                loadManagerDashboard();
            }
        });
}

// Customer dashboard
function loadCustomerDashboard() {
    fetch('/api/orders')
        .then(response => response.json())
        .then(data => {
            const orders = data.orders;
            
            // Update stats
            document.getElementById('active-orders-count').textContent = 
                orders.filter(o => o.status !== 'completed' && o.status !== 'cancelled').length;
            document.getElementById('pending-payments-count').textContent = 
                orders.filter(o => !o.is_paid).length;
            document.getElementById('delivered-orders-count').textContent = 
                orders.filter(o => o.status === 'delivered').length;
            
            // Render orders table
            renderOrdersTable(orders);
        });
}

// Manager dashboard
function loadManagerDashboard() {
    fetch('/api/orders')
        .then(response => response.json())
        .then(data => {
            const orders = data.orders;
            
            // Update stats
            document.getElementById('pending-approval-count').textContent = 
                orders.filter(o => o.status === 'pending').length;
            document.getElementById('in-progress-count').textContent = 
                orders.filter(o => o.status === 'in_progress').length;
            document.getElementById('tailors-available-count').textContent = 
                // This would require an additional API endpoint to get available tailors
                4; // Placeholder
            
            // Render orders tables
            renderPendingApprovalTable(orders.filter(o => o.status === 'pending'));
            renderInProgressTable(orders.filter(o => o.status === 'in_progress'));
        });
}

// Helper function to show notifications
function showNotification(title, message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <strong>${title}</strong>
        <p>${message}</p>
    `;
    
    document.getElementById('notifications-container').appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}