// ===== MAIN JS =====

// Banner Slider
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.banner-slide');
    const prevBtn = document.querySelector('.banner-nav.prev');
    const nextBtn = document.querySelector('.banner-nav.next');
    let currentSlide = 0;

    if (slides.length > 0) {
        function showSlide(index) {
            slides.forEach(s => s.classList.remove('active'));
            slides[index].classList.add('active');
        }

        if (prevBtn && nextBtn) {
            nextBtn.addEventListener('click', () => {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            });

            prevBtn.addEventListener('click', () => {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(currentSlide);
            });
        }

        // Auto-slide
        setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 5000);
    }

    // Countdown Timer
    function updateTimer() {
        const hours = document.getElementById('hours');
        const minutes = document.getElementById('minutes');
        const seconds = document.getElementById('seconds');
        
        if (!hours || !minutes || !seconds) return;

        let h = parseInt(hours.textContent);
        let m = parseInt(minutes.textContent);
        let s = parseInt(seconds.textContent);

        s--;
        if (s < 0) { s = 59; m--; }
        if (m < 0) { m = 59; h--; }
        if (h < 0) { h = 23; m = 59; s = 59; }

        hours.textContent = h.toString().padStart(2, '0');
        minutes.textContent = m.toString().padStart(2, '0');
        seconds.textContent = s.toString().padStart(2, '0');
    }
    setInterval(updateTimer, 1000);

    // Load Homepage Products
    loadFlashSale();
    loadFeaturedCovers();
    loadFeaturedDresses();
});

function createProductCard(product) {
    const detailPage = product.category === 'phone-cover' 
        ? `pages/product-detail.html?id=${product.id}` 
        : `pages/product-detail.html?id=${product.id}`;
    
    // For pages inside /pages/ folder, adjust path
    const basePath = window.location.pathname.includes('/pages/') ? '' : 'pages/';
    const link = `${basePath}product-detail.html?id=${product.id}`;
    
    return `
        <div class="product-card">
            ${product.discount ? `<span class="sale-badge">-${product.discount}%</span>` : ''}
            <a href="${link}">
                <div class="product-image">
                    <img src="${product.images[0]}" alt="${product.title}" loading="lazy">
                </div>
            </a>
            <div class="product-info">
                <a href="${link}">
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
    `;
}

function loadFlashSale() {
    const container = document.getElementById('flashSaleProducts');
    if (!container) return;
    
    const allProducts = getAllProducts();
    const saleProducts = allProducts.sort((a, b) => b.discount - a.discount).slice(0, 4);
    container.innerHTML = saleProducts.map(p => createProductCard(p)).join('');
}

function loadFeaturedCovers() {
    const container = document.getElementById('featuredCovers');
    if (!container) return;
    
    const featured = phoneCovers.slice(0, 4);
    container.innerHTML = featured.map(p => createProductCard(p)).join('');
}

function loadFeaturedDresses() {
    const container = document.getElementById('featuredDresses');
    if (!container) return;
    
    const featured = ladiesDresses.slice(0, 4);
    container.innerHTML = featured.map(p => createProductCard(p)).join('');
}

function quickAddToCart(productId) {
    const allProducts = getAllProducts();
    const product = allProducts.find(p => p.id === productId);
    if (product) {
        const options = {};
        if (product.models) options.model = product.models[0];
        if (product.sizes) options.size = product.sizes[0];
        if (product.colors) {
            options.color = product.colors[0];
            options.colorName = product.colorNames[0];
        }
        cart.addItem(product, options);
    }
}

// Search functionality
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim().toLowerCase();
            if (query) {
                if (query.includes('phone') || query.includes('cover') || query.includes('case')) {
                    window.location.href = window.location.pathname.includes('/pages/') 
                        ? 'phone-covers.html' 
                        : 'pages/phone-covers.html';
                } else if (query.includes('dress') || query.includes('ladies') || query.includes('fashion')) {
                    window.location.href = window.location.pathname.includes('/pages/') 
                        ? 'ladies-dress.html' 
                        : 'pages/ladies-dress.html';
                }
            }
        }
    });
}
