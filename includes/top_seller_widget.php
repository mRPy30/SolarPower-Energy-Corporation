<?php
/**
 * Top Selling Product Widget
 * 
 * Requires: $best_seller array with keys: product_name, total_qty, order_frequency, image_path (optional), product_id
 * Include this file wherever you want to display the top selling product card.
 */

// Determine the base path to project root relative to the current script
$_widget_base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
$_widget_root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$_widget_rel  = '';
$_depth = 0;
$_tmp = $_widget_base;
while ($_tmp !== $_widget_root && $_depth < 10) {
    $_tmp = dirname($_tmp);
    $_widget_rel .= '../';
    $_depth++;
}

// Try to find the actual product image file on disk
$_widget_img_src = '';
if (!empty($best_seller['image_path'])) {
    $_widget_img_full = realpath(__DIR__ . '/../' . $best_seller['image_path']);
    if ($_widget_img_full && file_exists($_widget_img_full)) {
        $_widget_img_src = $_widget_rel . $best_seller['image_path'];
    }
}
// Fallback: scan the product's upload folder for the first image
if (empty($_widget_img_src) && !empty($best_seller['product_id']) && $best_seller['product_id'] > 0) {
    $_widget_folder = realpath(__DIR__ . '/../uploads/products/' . $best_seller['product_id']);
    if ($_widget_folder && is_dir($_widget_folder)) {
        $_widget_files = glob($_widget_folder . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        if (empty($_widget_files)) {
            // Also check for double extensions like .jpg.png
            $_widget_files = glob($_widget_folder . '/*.*');
            $_widget_files = array_filter($_widget_files, function($f) {
                return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f);
            });
        }
        if (!empty($_widget_files)) {
            $_widget_img_src = $_widget_rel . 'uploads/products/' . $best_seller['product_id'] . '/' . basename(reset($_widget_files));
        }
    }
}
?>
<div class="details-card" style="flex: 1; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; min-width: 300px;">
    <h3 style="margin-bottom: 20px; color: #333;">Top Selling Product</h3>
    <?php if ($best_seller): ?>
        <div style="padding: 20px; border: 2px dashed #f1f1f1; border-radius: 10px;">
            <div style="width: 120px; height: 120px; margin: 0 auto 15px; border-radius: 10px; overflow: hidden;">
                <?php if (!empty($_widget_img_src)): ?>
                    <img src="<?php echo htmlspecialchars($_widget_img_src); ?>" 
                         alt="<?php echo htmlspecialchars($best_seller['product_name']); ?>" 
                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 10px;">
                <?php else: ?>
                    <div style="background: #fff9db; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; border-radius: 10px;">
                        <i class="fas fa-box" style="font-size: 40px; color: #f1c40f;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <h2 style="font-size: 22px; margin-bottom: 10px; color: #2c3e50;"><?php echo htmlspecialchars($best_seller['product_name']); ?></h2>
            <div style="display: flex; justify-content: space-around; margin-top: 20px;">
                <div>
                    <p style="font-size: 20px; font-weight: bold; color: #27ae60;"><?php echo $best_seller['total_qty']; ?></p>
                    <p style="font-size: 12px; color: #999; text-transform: uppercase;">Sold</p>
                </div>
                <div>
                    <p style="font-size: 20px; font-weight: bold; color: #3498db;"><?php echo $best_seller['order_frequency']; ?></p>
                    <p style="font-size: 12px; color: #999; text-transform: uppercase;">Orders</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p style="color: #999; margin-top: 30px;">No sales data available.</p>
    <?php endif; ?>
</div>
