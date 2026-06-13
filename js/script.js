const CART_STORAGE_KEY = "quickbite-cart";
const LEGACY_CART_STORAGE_KEY = "cart";

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

function getCart() {
    try {
        const storedCart = JSON.parse(localStorage.getItem(CART_STORAGE_KEY));
        if (Array.isArray(storedCart)) {
            const normalizedCart = normalizeCart(storedCart);
            if (normalizedCart.some((item, index) => item.price !== storedCart[index]?.price || item.quantity !== storedCart[index]?.quantity)) {
                setCart(normalizedCart);
            }
            return normalizedCart;
        }

        const legacyCart = JSON.parse(localStorage.getItem(LEGACY_CART_STORAGE_KEY));
        if (Array.isArray(legacyCart)) {
            const normalizedCart = normalizeCart(legacyCart);
            setCart(normalizedCart);
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
    localStorage.setItem(CART_STORAGE_KEY, cartJson);
    localStorage.setItem(LEGACY_CART_STORAGE_KEY, cartJson);
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

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initCart);
} else {
    initCart();
}