<?php
if (!function_exists('app_base_path')) {
    function app_base_path(): string
    {
        $configuredBase = trim((string) (getenv('APP_BASE_PATH') ?: ''));
        if ($configuredBase !== '') {
            return '/' . trim($configuredBase, '/');
        }

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $localFolder = '/SolarPower-Energy-Corporation/';

        if (stripos($scriptName, $localFolder) !== false) {
            return rtrim(substr($scriptName, 0, stripos($scriptName, $localFolder) + strlen($localFolder) - 1), '/');
        }

        return '';
    }
}

if (!function_exists('clean_url')) {
    function clean_url(string $path = ''): string
    {
        $path = trim($path);

        if ($path === '' || $path === '/' || $path === 'index.php') {
            return app_base_path() . '/';
        }

        if (preg_match('#^(https?:)?//#i', $path) || preg_match('#^(mailto:|tel:|javascript:)#i', $path) || substr($path, 0, 1) === '#') {
            return $path;
        }

        $fragment = '';
        $fragmentPos = strpos($path, '#');
        if ($fragmentPos !== false) {
            $fragment = substr($path, $fragmentPos);
            $path = substr($path, 0, $fragmentPos);
        }

        $query = '';
        $queryPos = strpos($path, '?');
        if ($queryPos !== false) {
            $query = substr($path, $queryPos);
            $path = substr($path, 0, $queryPos);
        }

        $path = preg_replace('#^(\./|\.\./)+#', '', $path);
        $path = ltrim($path, '/');

        $routeMap = [
            'index.php' => '',
            'about.php' => 'about',
            'cart.php' => 'cart',
            'checkout.php' => 'checkout',
            'contact.php' => 'contact',
            'product.php' => 'product',
            'services.php' => 'services',
            'projects.php' => 'projects',
            'loans.php' => 'loans',
            'faq.php' => 'faq',
            'newsletter.php' => 'newsletter',
            'track-order.php' => 'track-order',
            'package.php' => 'package',
            'brand.php' => 'brand',
            'category.php' => 'category',
            'privacy-policy.php' => 'privacy-policy',
            'terms-of-service.php' => 'terms-of-service',
            'refund-policy.php' => 'refund-policy',
            'terms-of-payment.php' => 'terms-of-payment',
            'process-payment.php' => 'process-payment',
            'views/login.php' => 'login',
            'login.php' => 'login',
            'views/signup.php' => 'signup',
            'signup.php' => 'signup',
            'views/forgot_password.php' => 'forgot-password',
            'forgot_password.php' => 'forgot-password',
            'views/verify_reset_code.php' => 'verify-reset-code',
            'verify_reset_code.php' => 'verify-reset-code',
            'views/staff/dashboard.php' => 'dashboard',
            'staff/dashboard.php' => 'dashboard',
            'dashboard.php' => 'dashboard',
            'controllers/logout.php' => 'logout',
            'logout.php' => 'logout',
        ];

        if (isset($routeMap[$path])) {
            $route = $routeMap[$path];
        } elseif (substr($path, 0, strlen('product-details.php/')) === 'product-details.php/') {
            $route = 'product-details/' . trim(substr($path, strlen('product-details.php/')), '/');
        } elseif (substr($path, -4) === '.php') {
            $route = substr($path, 0, -4);
        } else {
            $route = $path;
        }

        return app_base_path() . '/' . trim($route, '/') . $query . $fragment;
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        return app_base_path() . '/' . ltrim($path, '/');
    }
}
