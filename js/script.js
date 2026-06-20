const CART_STORAGE_KEY = "quickbite-cart";
const LEGACY_CART_STORAGE_KEY = "cart";
const ORDERS_STORAGE_KEY = "quickbite-orders";
const TOKEN_COUNTER_KEY = "quickbite-token-counter";
const TOKEN_START = 1000;

function normalizePrice(price) {
    const numericPrice = Number(price) || 0;

    if (numericPrice > 0 && numericPrice < 1) {
        return Math.round(numericPrice * 1000);
    }

    if (numericPrice >= 1 && numericPrice < 100 && Number.isInteger(numericPrice)) {
        return numericPrice * 10;
    }

    return numericPrice;
}

function normalizeCart(cart) {
    if (!Array.isArray(cart)) {
        return [];
    }

    return cart.map((item) => ({
        ...item,
        price: normalizePrice(item.price),
        quantity: Number(item.quantity) || 0
    }));
}

function getScopedCartKey() {
    const userId = document.body.dataset.userId;
    if (userId) {
        return `${CART_STORAGE_KEY}-${userId}`;
    }
    return CART_STORAGE_KEY;
}

function getCart() {
    try {
        const scopedKey = getScopedCartKey();
        const storedCart = JSON.parse(localStorage.getItem(scopedKey));
        if (Array.isArray(storedCart)) {
            const normalizedCart = normalizeCart(storedCart);
            if (normalizedCart.some((item, index) => item.price !== storedCart[index]?.price || item.quantity !== storedCart[index]?.quantity)) {
                setCart(normalizedCart);
            }
            return normalizedCart;
        }

        // Migrate guest cart to logged-in user cart
        const legacyCart = JSON.parse(localStorage.getItem(LEGACY_CART_STORAGE_KEY)) || JSON.parse(localStorage.getItem(CART_STORAGE_KEY));
        if (Array.isArray(legacyCart)) {
            const normalizedCart = normalizeCart(legacyCart);
            setCart(normalizedCart);
            if (scopedKey !== CART_STORAGE_KEY) {
                localStorage.removeItem(CART_STORAGE_KEY);
                localStorage.removeItem(LEGACY_CART_STORAGE_KEY);
            }
            return normalizedCart;
        }

        return [];
    } catch (error) {
        return [];
    }
}

function setCart(cart) {
    const normalizedCart = normalizeCart(cart);
    const cartJson = JSON.stringify(normalizedCart);
    const scopedKey = getScopedCartKey();
    localStorage.setItem(scopedKey, cartJson);
    if (scopedKey === CART_STORAGE_KEY) {
        localStorage.setItem(LEGACY_CART_STORAGE_KEY, cartJson);
    }
}

function formatMoney(value) {
    const numericValue = Number(value) || 0;
    return Number.isInteger(numericValue) ? `Rs.${numericValue}` : `Rs.${numericValue.toFixed(2)}`;
}

function parsePrice(text) {
    const parsedValue = String(text || "").replace(/[^0-9.]/g, "");
    return Number.parseFloat(parsedValue) || 0;
}

function getItemKey(item) {
    return `${item.name}|${item.price}`;
}

function updateCartCount() {
    const cart = getCart();
    const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);

    document.querySelectorAll(".cart-btn").forEach((cartButton) => {
        cartButton.style.position = "relative";
        cartButton.style.overflow = "visible";

        let badge = cartButton.querySelector(".cart-count-badge");

        if (!badge) {
            badge = document.createElement("span");
            badge.className = "cart-count-badge";
            badge.style.cssText = [
                "position:absolute",
                "top:-8px",
                "right:-8px",
                "min-width:22px",
                "height:22px",
                "padding:0 6px",
                "border-radius:999px",
                "background:#ef4444",
                "color:#fff",
                "font-size:12px",
                "font-weight:700",
                "display:inline-flex",
                "align-items:center",
                "justify-content:center",
                "box-shadow:0 4px 10px rgba(0,0,0,0.2)"
            ].join(";");
            cartButton.appendChild(badge);
        }

        badge.textContent = String(itemCount);
        badge.style.display = itemCount > 0 ? "inline-flex" : "none";
    });
}

function addToCart(item) {
    if (!item || !item.name) {
        return;
    }

    item.price = normalizePrice(item.price);

    const cart = getCart();
    const existingItem = cart.find((cartItem) => getItemKey(cartItem) === getItemKey(item));

    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            name: item.name,
            price: item.price,
            quantity: 1,
            image: item.image || "",
            description: item.description || ""
        });
    }

    setCart(cart);
    updateCartCount();
    renderCartPage();
}

function readCardItem(button) {
    const card = button.closest(".dish-card, .card");

    if (!card) {
        return null;
    }

    const titleElement = card.querySelector("h3");
    const descriptionElement = card.querySelector("p");
    const imageElement = card.querySelector("img");
    const priceElement = card.querySelector(".dish-price, .price-badge");

    return {
        name: button.dataset.name || titleElement?.textContent.trim() || "Item",
        price: parsePrice(button.dataset.price || priceElement?.textContent || 0),
        description: button.dataset.description || descriptionElement?.textContent.trim() || "",
        image: button.dataset.image || imageElement?.getAttribute("src") || ""
    };
}

function bindAddToCartButtons() {
    document.querySelectorAll(".add-to-cart-btn").forEach((button) => {
        button.addEventListener("click", () => {
            const item = readCardItem(button);
            addToCart(item);
        });
    });
}

function getSummaryValues(cart) {
    const subtotal = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

    return {
        subtotal,
        total: subtotal
    };
}

function getOrders() {
    try {
        const storedOrders = JSON.parse(localStorage.getItem(ORDERS_STORAGE_KEY));
        return Array.isArray(storedOrders) ? storedOrders : [];
    } catch (error) {
        return [];
    }
}

function setOrders(orders) {
    localStorage.setItem(ORDERS_STORAGE_KEY, JSON.stringify(Array.isArray(orders) ? orders : []));
}

function getNextTokenNumber() {
    const currentValue = Number.parseInt(localStorage.getItem(TOKEN_COUNTER_KEY) || TOKEN_START, 10);
    const nextValue = Number.isNaN(currentValue) ? TOKEN_START + 1 : currentValue + 1;
    localStorage.setItem(TOKEN_COUNTER_KEY, String(nextValue));
    return `QB${nextValue}`;
}

function getOrderStatusBadgeClass(status) {
    const normalizedStatus = String(status || "").toLowerCase();

    if (normalizedStatus === "ready") {
        return "status-ready";
    }

    if (normalizedStatus === "completed") {
        return "status-completed";
    }

    return "status-preparing";
}

function formatOrderDate(dateString) {
    if (!dateString) {
        return "Just now";
    }

    const date = new Date(dateString);

    if (Number.isNaN(date.getTime())) {
        return dateString;
    }

    return date.toLocaleString([], {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit"
    });
}

function renderCartPage() {
    const cartList = document.getElementById("cart-list");

    if (!cartList) {
        return;
    }

    const cart = getCart();
    const emptyState = document.getElementById("cart-empty-state");
    const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    const summary = getSummaryValues(cart);

    const itemsLabel = document.getElementById("cart-total-items");
    const subtotalLabel = document.getElementById("summary-subtotal");
    const totalLabel = document.getElementById("summary-total");

    if (itemsLabel) {
        itemsLabel.textContent = `${itemCount} ${itemCount === 1 ? "item" : "items"}`;
    }

    if (subtotalLabel) {
        subtotalLabel.textContent = formatMoney(summary.subtotal);
    }

    if (totalLabel) {
        totalLabel.textContent = formatMoney(summary.total);
    }

    if (!cart.length) {
        cartList.innerHTML = "";

        if (emptyState) {
            emptyState.hidden = false;
        }

        updateCartCount();
        return;
    }

    if (emptyState) {
        emptyState.hidden = true;
    }

    cartList.innerHTML = cart
        .map((item, index) => {
            const lineTotal = item.price * item.quantity;
            return `
                <article class="cart-item">
                    <div class="cart-item-main">
                        <div class="cart-item-image">
                            ${item.image ? `<img src="${item.image}" alt="${item.name}">` : ""}
                        </div>
                        <div class="cart-item-details">
                            <h3>${item.name}</h3>
                            <p>${item.description || "Added from the menu"}</p>
                            <div class="cart-item-meta">
                                <span>${formatMoney(item.price)} each</span>
                                <span>${formatMoney(lineTotal)} total</span>
                            </div>
                        </div>
                    </div>
                    <div class="cart-item-actions">
                        <div class="quantity-controls">
                            <button type="button" data-cart-action="decrease" data-index="${index}">-</button>
                            <span>${item.quantity}</span>
                            <button type="button" data-cart-action="increase" data-index="${index}">+</button>
                        </div>
                        <button type="button" class="remove-item-btn" data-cart-action="remove" data-index="${index}">Remove</button>
                    </div>
                </article>
            `;
        })
        .join("");

    updateCartCount();
}

function updateQuantity(index, delta) {
    const cart = getCart();

    if (!cart[index]) {
        return;
    }

    cart[index].quantity += delta;

    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }

    setCart(cart);
    renderCartPage();
}

function removeItem(index) {
    const cart = getCart();

    if (!cart[index]) {
        return;
    }

    cart.splice(index, 1);
    setCart(cart);
    renderCartPage();
}

function bindCartControls() {
    const cartList = document.getElementById("cart-list");

    if (!cartList || cartList.dataset.bound === "true") {
        return;
    }

    cartList.dataset.bound = "true";

    cartList.addEventListener("click", (event) => {
        const control = event.target.closest("[data-cart-action]");

        if (!control) {
            return;
        }

        const action = control.dataset.cartAction;
        const index = Number(control.dataset.index);

        if (Number.isNaN(index)) {
            return;
        }

        if (action === "increase") {
            updateQuantity(index, 1);
        } else if (action === "decrease") {
            updateQuantity(index, -1);
        } else if (action === "remove") {
            removeItem(index);
        }
    });
}

function initCart() {
    bindAddToCartButtons();
    bindCartControls();
    updateCartCount();
    renderCartPage();
}

function renderCheckoutPage() {
    const checkoutSummary = document.getElementById("checkout-summary");
    if (!checkoutSummary) {
        return;
    }

    const cart = getCart();
    const summary = getSummaryValues(cart);
    const itemCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    const itemsHtml = cart.length
        ? cart.map((item) => `<li><span>${item.name}</span><strong>${formatMoney(item.price * item.quantity)}</strong></li>`).join("")
        : '<li><span>Your cart is empty</span><strong>Rs.0</strong></li>';

    checkoutSummary.innerHTML = `
        <div class="checkout-summary-block">
            <p class="checkout-summary-label">Items</p>
            <h3>${itemCount} ${itemCount === 1 ? "item" : "items"}</h3>
        </div>
        <ul class="checkout-items-list">${itemsHtml}</ul>
        <div class="checkout-summary-row"><span>Subtotal</span><strong>${formatMoney(summary.subtotal)}</strong></div>
        <div class="checkout-summary-row checkout-summary-total"><span>Total</span><strong>${formatMoney(summary.total)}</strong></div>
    `;
}

function bindCheckoutForm() {
    const checkoutForm = document.getElementById("checkout-form");
    const successBox = document.getElementById("checkout-success");

    if (!checkoutForm) {
        return;
    }

    checkoutForm.addEventListener("submit", (event) => {
        const cart = getCart();
        if (!cart.length) {
            event.preventDefault();
            if (successBox) {
                successBox.innerHTML = "Please add items to your cart before placing an order.";
                successBox.hidden = false;
                successBox.className = "success-box error-box";
            } else {
                alert("Please add items to your cart before placing an order.");
            }
            return;
        }

        // Set hidden input with cart JSON data
        const cartInput = document.getElementById("checkout-cart-data");
        if (cartInput) {
            cartInput.value = JSON.stringify(cart);
        }
    });
}

function renderMyOrdersPage() {
    const ordersList = document.getElementById("orders-list");

    if (!ordersList) {
        return;
    }

    // If we are on myorders.php, let the server handle order rendering
    if (window.location.pathname.includes("myorders.php")) {
        return;
    }

    const storedOrders = getOrders();
    const demoOrders = storedOrders.length ? storedOrders : [
        { token: "QB1001", status: "Preparing", name: "Demo Customer", createdAt: new Date().toISOString(), total: 0 },
        { token: "QB1002", status: "Ready", name: "Demo Customer", createdAt: new Date().toISOString(), total: 0 }
    ];

    ordersList.innerHTML = demoOrders.map((order) => `
        <div class="order-row">
            <span class="order-token">${order.token}</span>
            <span class="order-status ${getOrderStatusBadgeClass(order.status)}">${order.status}</span>
            <span class="order-name">${order.name || "Guest"}</span>
            <span class="order-date">${formatOrderDate(order.createdAt)}</span>
        </div>
    `).join("");
}

function initCheckoutPage() {
    renderCheckoutPage();
    bindCheckoutForm();
    updateCartCount();
}

function initMyOrdersPage() {
    renderMyOrdersPage();
    updateCartCount();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initCart);
} else {
    initCart();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
        initCheckoutPage();
        initMyOrdersPage();
    });
} else {
    initCheckoutPage();
    initMyOrdersPage();
}

// --- LOGIN & REGISTER FORM VALIDATION ---

function validateLoginForm() {
    const loginForm = document.querySelector('form[action="login.php"]');
    if (!loginForm) return;

    loginForm.addEventListener("submit", (event) => {
        const emailInput = document.getElementById("email");
        const passwordInput = document.getElementById("password");
        
        // පැරණි Error Messages තියෙනවා නම් ඉවත් කිරීම
        removeExistingErrors(loginForm);

        let hasError = false;

        // 1. Email පරික්ෂාව
        if (!emailInput.value.trim()) {
            showInputError(emailInput, "Email address is required.");
            hasError = true;
        } else if (!validateEmailPattern(emailInput.value.trim())) {
            showInputError(emailInput, "Please enter a valid email address.");
            hasError = true;
        }

        // 2. Password පරික්ෂාව
        if (!passwordInput.value.trim()) {
            showInputError(passwordInput, "Password is required.");
            hasError = true;
        }

        // වැරැද්දක් තිබේ නම් Form එක PHP එකට යෑම වළක්වයි
        if (hasError) {
            event.preventDefault();
        }
    });
}

function validateRegisterForm() {
    const registerForm = document.querySelector('form[action="register.php"]');
    if (!registerForm) return;

    registerForm.addEventListener("submit", (event) => {
        const nameInput = document.getElementById("full_name");
        const emailInput = document.getElementById("email") || document.getElementById("register_email");
        const passwordInput = document.getElementById("password") || document.getElementById("register_password");
        const confirmInput = document.getElementById("confirm_password");

        removeExistingErrors(registerForm);

        let hasError = false;

        // 1. නම පරික්ෂාව
        if (!nameInput.value.trim()) {
            showInputError(nameInput, "Full name is required.");
            hasError = true;
        }

        // 2. Email පරික්ෂාව
        if (!emailInput.value.trim()) {
            showInputError(emailInput, "Email address is required.");
            hasError = true;
        } else if (!validateEmailPattern(emailInput.value.trim())) {
            showInputError(emailInput, "Please enter a valid email address.");
            hasError = true;
        }

        // 3. Password දිග පරික්ෂාව (අවම අකුරු 6ක්)
        if (!passwordInput.value.trim()) {
            showInputError(passwordInput, "Password is required.");
            hasError = true;
        } else if (passwordInput.value.length < 6) {
            showInputError(passwordInput, "Password must be at least 6 characters long.");
            hasError = true;
        }

        // 4. Confirm Password ගැලපේදැයි බැලීම
        if (passwordInput.value !== confirmInput.value) {
            showInputError(confirmInput, "Passwords do not match.");
            hasError = true;
        }

        if (hasError) {
            event.preventDefault();
        }
    });
}

// Email එක සැබෑ එකක්දැයි බලන පොදු ශ්‍රිතය (Regex)
function validateEmailPattern(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Input එක පල්ලෙහායින් රතු පාටින් Error එක පෙන්වීම
function showInputError(inputElement, message) {
    inputElement.style.borderColor = "#ef4444"; // රතු පාට Border එකක් දීම
    
    const errorBox = document.createElement("p");
    errorBox.className = "js-input-error";
    errorBox.style.color = "#ef4444";
    errorBox.style.fontSize = "13px";
    errorBox.style.marginTop = "4px";
    errorBox.style.marginBottom = "12px";
    errorBox.textContent = message;
    
    inputElement.insertAdjacentElement("afterend", errorBox);
}

// කලින් දාපු Errors මැකීම
function removeExistingErrors(formElement) {
    formElement.querySelectorAll(".js-input-error").forEach(el => el.remove());
    formElement.querySelectorAll("input").forEach(input => input.style.borderColor = "");
}

// පිටුව Load වන විට මේවා ක්‍රියාත්මක කරවීම
document.addEventListener("DOMContentLoaded", () => {
    validateLoginForm();
    validateRegisterForm();
});