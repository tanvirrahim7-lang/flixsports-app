// ===== PRODUCT DETAIL PAGE JS =====

let currentProduct = null;
let selectedModel = '';
let selectedSize = '';
let selectedColor = '';
let selectedColorName = '';

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    
    if (!productId) {
        window.location.href = '../index.html';
        return;
    }
    
    // Find product in all products
    const allProducts = getAllProducts();
    currentProduct = allProducts.find(p => p.id === productId);
    
    if (!currentProduct) {
        window.location.href = '../index.html';
        return;
    }
    
    renderProductDetail();
});

function renderProductDetail() {
    const product = currentProduct;
    
    // Update page title
    document.title = `${product.title} - ShopZone`;
    
    // Breadcrumb
    const categoryLink = document.getElementById('categoryLink');
    const productName = document.getElementById('productName');
    if (product.category === 'phone-cover') {
        categoryLink.href = 'phone-covers.html';
        categoryLink.textContent = 'Phone Covers';
    } else {
        categoryLink.href = 'ladies-dress.html';
        categoryLink.textContent = 'Ladies Dress';
    }
    productName.textContent = product.title.substring(0, 40) + '...';
    
    // Main Image
    document.getElementById('mainImage').src = product.images[0];
    
    // Thumbnails
    const thumbnailsContainer = document.getElementById('thumbnails');
    thumbnailsContainer.innerHTML = product.images.map((img, index) => `
        <div class="thumbnail ${index === 0 ? 'active' : ''}" onclick="changeImage('${img}', this)">
            <img src="${img}" alt="Thumbnail ${index + 1}">
        </div>
    `).join('');
    
    // Title
    document.getElementById('productTitle').textContent = product.title;
    
    // Rating
    document.getElementById('productStars').innerHTML = '<i class="fas fa-star"></i>'.repeat(Math.floor(product.rating));
    document.getElementById('productRating').textContent = `${product.rating} rating`;
    document.getElementById('productSold').textContent = `${product.sold}+ sold`;
    
    // Price
    document.getElementById('productPrice').textContent = `৳${product.price}`;
    document.getElementById('originalPrice').textContent = `৳${product.originalPrice}`;
    document.getElementById('discountBadge').textContent = `-${product.discount}%`;
    
    // Phone Model Options (for phone covers)
    if (product.models && product.models.length > 0) {
        document.getElementById('modelSection').style.display = 'block';
        const modelContainer = document.getElementById('modelOptions');
        modelContainer.innerHTML = product.models.map(model => `
            <div class="option-item" onclick="selectModel('${model}', this)">${model}</div>
        `).join('');
    }
    
    // Size Options (for dresses)
    if (product.sizes && product.sizes.length > 0) {
        document.getElementById('sizeSection').style.display = 'block';
        const sizeContainer = document.getElementById('sizeOptions');
        sizeContainer.innerHTML = product.sizes.map(size => `
            <div class="option-item" onclick="selectSize('${size}', this)">${size}</div>
        `).join('');
    }
    
    // Color Options
    if (product.colors && product.colors.length > 0) {
        const colorContainer = document.getElementById('colorOptions');
        colorContainer.innerHTML = product.colors.map((color, index) => `
            <div class="color-option" 
                 style="background: ${color}; ${color === '#ffffff' || color === '#fff8e1' || color === '#f5f5f5' || color === '#ffe0e0' || color === '#e0f0ff' || color === '#e0ffe0' || color === '#e8f5e9' || color === '#fce4ec' || color === '#e3f2fd' || color === '#f3e5f5' || color === '#fff3e0' || color === '#e0f7fa' || color === '#f1f8e9' || color === '#ede7f6' || color === '#fff9c4' || color === '#c8e6c9' || color === '#bbdefb' || color === '#ffcdd2' || color === '#e1bee7' || color === '#ffd700' || color === '#c0c0c0' || color === '#90caf9' ? 'border-color: #ccc;' : ''}"
                 onclick="selectColor('${color}', '${product.colorNames[index]}', this)"
                 title="${product.colorNames[index]}">
            </div>
        `).join('');
    }
    
    // Features
    const featuresContainer = document.getElementById('productFeatures');
    featuresContainer.innerHTML = product.features.map(f => `<li style="margin-bottom: 5px;">${f}</li>`).join('');
    
    // Description
    document.getElementById('productDescription').textContent = product.description;
}

function changeImage(src, thumbnail) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
    thumbnail.classList.add('active');
}

function selectModel(model, element) {
    selectedModel = model;
    document.querySelectorAll('#modelOptions .option-item').forEach(item => item.classList.remove('selected'));
    element.classList.add('selected');
}

function selectSize(size, element) {
    selectedSize = size;
    document.querySelectorAll('#sizeOptions .option-item').forEach(item => item.classList.remove('selected'));
    element.classList.add('selected');
}

function selectColor(color, colorName, element) {
    selectedColor = color;
    selectedColorName = colorName;
    document.getElementById('selectedColorName').textContent = colorName;
    document.querySelectorAll('.color-option').forEach(item => item.classList.remove('selected'));
    element.classList.add('selected');
    
    // Change main image based on color index
    const colorIndex = currentProduct.colors.indexOf(color);
    if (colorIndex >= 0 && colorIndex < currentProduct.images.length) {
        document.getElementById('mainImage').src = currentProduct.images[colorIndex];
    }
}

function changeQty(delta) {
    const input = document.getElementById('qtyInput');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > 10) val = 10;
    input.value = val;
}

function addToCartFromDetail() {
    if (!currentProduct) return;
    
    // Validation
    if (currentProduct.models && currentProduct.models.length > 0 && !selectedModel) {
        showToast('Please select a phone model', 'error');
        return;
    }
    if (currentProduct.sizes && currentProduct.sizes.length > 0 && !selectedSize) {
        showToast('Please select a size', 'error');
        return;
    }
    if (!selectedColor) {
        showToast('Please select a color', 'error');
        return;
    }
    
    const quantity = parseInt(document.getElementById('qtyInput').value);
    
    cart.addItem(currentProduct, {
        model: selectedModel,
        size: selectedSize,
        color: selectedColor,
        colorName: selectedColorName,
        quantity: quantity
    });
}

function buyNow() {
    addToCartFromDetail();
    // Small delay to let the toast show
    setTimeout(() => {
        window.location.href = 'cart.html';
    }, 500);
}
