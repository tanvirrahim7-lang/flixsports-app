// ===== CART MANAGEMENT =====

class Cart {
    constructor() {
        this.items = JSON.parse(localStorage.getItem('shopzone_cart')) || [];
        this.updateCartCount();
    }

    addItem(product, options = {}) {
        const existingIndex = this.items.findIndex(item => 
            item.id === product.id && 
            item.selectedColor === options.color &&
            item.selectedModel === options.model &&
            item.selectedSize === options.size
        );

        if (existingIndex > -1) {
            this.items[existingIndex].quantity += (options.quantity || 1);
        } else {
            this.items.push({
                id: product.id,
                title: product.title,
                price: product.price,
                image: product.images[0],
                selectedColor: options.color || '',
                selectedColorName: options.colorName || '',
                selectedModel: options.model || '',
                selectedSize: options.size || '',
                quantity: options.quantity || 1,
                category: product.category
            });
        }

        this.save();
        this.updateCartCount();
        showToast('Added to cart!', 'success');
    }

    removeItem(index) {
        this.items.splice(index, 1);
        this.save();
        this.updateCartCount();
    }

    updateQuantity(index, quantity) {
        if (quantity <= 0) {
            this.removeItem(index);
        } else {
            this.items[index].quantity = quantity;
            this.save();
        }
    }

    getTotal() {
        return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    getItemCount() {
        return this.items.reduce((count, item) => count + item.quantity, 0);
    }

    clear() {
        this.items = [];
        this.save();
        this.updateCartCount();
    }

    save() {
        localStorage.setItem('shopzone_cart', JSON.stringify(this.items));
    }

    updateCartCount() {
        const countElements = document.querySelectorAll('#cartCount, .cart-count');
        countElements.forEach(el => {
            el.textContent = this.getItemCount();
        });
    }
}

// Global cart instance
const cart = new Cart();

// Toast notification
function showToast(message, type = 'success') {
    const existing = document.querySelector('.toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => toast.remove(), 3000);
}
