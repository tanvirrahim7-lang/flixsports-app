// ===== CART PAGE JS =====

document.addEventListener('DOMContentLoaded', function() {
    renderCart();
});

function renderCart() {
    const container = document.getElementById('cartItems');
    const summary = document.getElementById('cartSummary');
    
    if (cart.items.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added anything to your cart yet</p>
                <a href="../index.html"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
            </div>
        `;
        summary.style.display = 'none';
        return;
    }
    
    container.innerHTML = cart.items.map((item, index) => `
        <div class="cart-item">
            <div class="cart-item-image">
                <img src="${item.image}" alt="${item.title}">
            </div>
            <div class="cart-item-info">
                <h3>${item.title}</h3>
                <div class="item-variant">
                    ${item.selectedModel ? `<span>Model: ${item.selectedModel}</span>` : ''}
                    ${item.selectedSize ? `<span>Size: ${item.selectedSize}</span>` : ''}
                    ${item.selectedColorName ? `<span>Color: ${item.selectedColorName}</span>` : ''}
                </div>
            </div>
            <div class="cart-item-qty">
                <div class="qty-controls">
                    <button class="qty-btn" onclick="updateItemQty(${index}, -1)">-</button>
                    <input type="number" class="qty-input" value="${item.quantity}" min="1" max="10" onchange="setItemQty(${index}, this.value)">
                    <button class="qty-btn" onclick="updateItemQty(${index}, 1)">+</button>
                </div>
            </div>
            <div class="cart-item-price">৳${item.price * item.quantity}</div>
            <button class="cart-item-remove" onclick="removeItem(${index})">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    `).join('');
    
    summary.style.display = 'flex';
    document.getElementById('cartTotal').textContent = `৳${cart.getTotal()}`;
}

function updateItemQty(index, delta) {
    const newQty = cart.items[index].quantity + delta;
    if (newQty >= 1 && newQty <= 10) {
        cart.updateQuantity(index, newQty);
        renderCart();
    }
}

function setItemQty(index, value) {
    const qty = parseInt(value);
    if (qty >= 1 && qty <= 10) {
        cart.updateQuantity(index, qty);
        renderCart();
    }
}

function removeItem(index) {
    cart.removeItem(index);
    renderCart();
    showToast('Item removed from cart');
}

function clearCart() {
    if (confirm('Are you sure you want to clear the cart?')) {
        cart.clear();
        renderCart();
        showToast('Cart cleared');
    }
}

function checkout() {
    showToast('Order placed successfully! Thank you for shopping.', 'success');
    cart.clear();
    setTimeout(() => {
        renderCart();
    }, 1500);
}
