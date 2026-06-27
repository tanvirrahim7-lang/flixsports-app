// ===== LADIES DRESS PAGE JS =====

let filteredProducts = [...ladiesDresses];

document.addEventListener('DOMContentLoaded', function() {
    renderProducts(filteredProducts);
    
    // Color swatch click handlers
    document.querySelectorAll('.color-swatch').forEach(swatch => {
        swatch.addEventListener('click', function() {
            this.classList.toggle('active');
            applyFilters();
        });
    });

    // Size and style checkbox change handlers
    document.querySelectorAll('input[name="size"], input[name="style"]').forEach(cb => {
        cb.addEventListener('change', applyFilters);
    });
});

function renderProducts(products) {
    const grid = document.getElementById('productGrid');
    const count = document.getElementById('resultsCount');
    
    if (!grid) return;
    
    count.textContent = `${products.length} products found`;
    
    if (products.length === 0) {
        grid.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: #999;">
                <i class="fas fa-search" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                <h3>No products found</h3>
                <p>Try adjusting your filters</p>
            </div>
        `;
        return;
    }
    
    grid.innerHTML = products.map(product => `
        <div class="product-card">
            ${product.discount ? `<span class="sale-badge">-${product.discount}%</span>` : ''}
            <a href="product-detail.html?id=${product.id}">
                <div class="product-image">
                    <img src="${product.images[0]}" alt="${product.title}" loading="lazy">
                </div>
            </a>
            <div class="product-info">
                <a href="product-detail.html?id=${product.id}">
                    <div class="product-title">${product.title}</div>
                </a>
                <div class="product-price">
                    ৳${product.price}
                    ${product.originalPrice ? `<span class="original-price">৳${product.originalPrice}</span>` : ''}
                </div>
                <div class="product-meta">
                    <span class="product-rating">
                        ${'<i class="fas fa-star"></i>'.repeat(Math.floor(product.rating))} ${product.rating}
                    </span>
                    <span>${product.sold} sold</span>
                </div>
            </div>
            <button class="add-cart-btn" onclick="quickAddToCart('${product.id}')">
                <i class="fas fa-cart-plus"></i> Add to Cart
            </button>
        </div>
    `).join('');
}

function applyFilters() {
    let results = [...ladiesDresses];
    
    // Filter by size
    const selectedSizes = Array.from(document.querySelectorAll('input[name="size"]:checked')).map(cb => cb.value);
    if (selectedSizes.length > 0) {
        results = results.filter(p => p.sizes.some(s => selectedSizes.includes(s)));
    }
    
    // Filter by style
    const selectedStyles = Array.from(document.querySelectorAll('input[name="style"]:checked')).map(cb => cb.value);
    if (selectedStyles.length > 0) {
        results = results.filter(p => selectedStyles.includes(p.style));
    }
    
    // Filter by color
    const selectedColors = Array.from(document.querySelectorAll('.color-swatch.active')).map(s => s.dataset.color.toLowerCase());
    if (selectedColors.length > 0) {
        results = results.filter(p => 
            p.colorNames.some(cn => selectedColors.some(sc => cn.toLowerCase().includes(sc)))
        );
    }
    
    // Filter by price
    const minPrice = parseInt(document.getElementById('minPrice').value) || 0;
    const maxPrice = parseInt(document.getElementById('maxPrice').value) || Infinity;
    results = results.filter(p => p.price >= minPrice && p.price <= maxPrice);
    
    filteredProducts = results;
    sortProducts();
}

function sortProducts() {
    const sortBy = document.getElementById('sortSelect').value;
    
    switch(sortBy) {
        case 'price-low':
            filteredProducts.sort((a, b) => a.price - b.price);
            break;
        case 'price-high':
            filteredProducts.sort((a, b) => b.price - a.price);
            break;
        case 'rating':
            filteredProducts.sort((a, b) => b.rating - a.rating);
            break;
        case 'popular':
        default:
            filteredProducts.sort((a, b) => b.sold - a.sold);
            break;
    }
    
    renderProducts(filteredProducts);
}

function clearFilters() {
    document.querySelectorAll('input[name="size"]:checked').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[name="style"]:checked').forEach(cb => cb.checked = false);
    document.querySelectorAll('.color-swatch.active').forEach(s => s.classList.remove('active'));
    document.getElementById('minPrice').value = '';
    document.getElementById('maxPrice').value = '';
    
    filteredProducts = [...ladiesDresses];
    renderProducts(filteredProducts);
}

function quickAddToCart(productId) {
    const product = ladiesDresses.find(p => p.id === productId);
    if (product) {
        cart.addItem(product, {
            size: product.sizes[0],
            color: product.colors[0],
            colorName: product.colorNames[0]
        });
    }
}
