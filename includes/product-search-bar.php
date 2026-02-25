<!-- Product Search Bar Include -->
<div class="product-search-wrap" style="margin:0;padding:0;">
    <div class="product-search-box">
        <i class="fas fa-search product-search-icon"></i>
        <input
            type="text"
            id="productSearchInput"
            class="product-search-input"
            placeholder="Search by product name or brand…"
            autocomplete="off"
            spellcheck="false"
        >
        <button class="product-search-clear" id="searchClearBtn" title="Clear search" style="display:none;">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="product-search-meta" id="searchMeta"></div>
</div>
<style>
.product-search-wrap {
    margin: 0 !important;
    padding: 0 !important;
}
.product-search-box {
    position: relative;
    display: flex;
    align-items: center;
    background: #fff;
    border: 2px solid #e5e5e5;
    border-radius: 50px;
    padding: 0 18px;
    transition: border-color 0.25s, box-shadow 0.25s;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.product-search-box:focus-within {
    border-color: #ffc107;
    box-shadow: 0 4px 20px rgba(255,193,7,0.2);
}
.product-search-icon {
    color: #aaa;
    font-size: 16px;
    margin-right: 12px;
    flex-shrink: 0;
    transition: color 0.2s;
}
.product-search-box:focus-within .product-search-icon {
    color: #ffc107;
}
.product-search-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 15px;
    padding: 14px 0;
    background: transparent;
    color: #333;
    font-family: inherit;
}
.product-search-input::placeholder {
    color: #bbb;
}
.product-search-clear {
    background: none;
    border: none;
    color: #aaa;
    font-size: 15px;
    cursor: pointer;
    padding: 6px 4px;
    line-height: 1;
    flex-shrink: 0;
    transition: color 0.2s;
}
.product-search-clear:hover { color: #e74c3c; }
.product-search-meta {
    margin-top: 8px;
    font-size: 13px;
    color: #888;
    padding-left: 8px;
    min-height: 18px;
}
.product-search-meta .highlight-count {
    color: #0a5c3d;
    font-weight: 700;
}
.search-highlight {
    background: rgba(255,193,7,0.35);
    border-radius: 3px;
    padding: 0 2px;
}
</style>