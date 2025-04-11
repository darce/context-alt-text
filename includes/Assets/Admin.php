<?php

declare(strict_types=1);

namespace WordPressPluginBoilerplate\Assets;

use WordPressPluginBoilerplate\Core\Template;
use WordPressPluginBoilerplate\Traits\Base;

class Admin
{
    use Base;

    const HANDLE = 'context-alt-text-admin';
    const OBJ_NAME = 'contextAltTextAdmin';
    const DEV_SCRIPT = 'src/admin/main.tsx';

    private $allowed_screens = array(
        'toplevel_page_wordpress-plugin-boilerplate',
    );

    public function bootstrap(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_footer', [$this, 'render_admin_mount_point']);
    }

    public function enqueue_admin_assets(): void
    {
        $screen = get_current_screen();
        if (! $screen || ! in_array($screen->base, $this->allowed_screens, true)) {
            return;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            // DEV mode: load from Vite server
            wp_enqueue_script(
                self::HANDLE,
                'http://localhost:5174/' . self::DEV_SCRIPT,
                [],
                time(),
                true
            );
        } else {
            // PROD mode: load built asset (adjust path to match build)
            wp_enqueue_script(
                self::HANDLE,
                plugin_dir_url(__FILE__) . '../../build/assets/admin.js',
                [],
                '1.0.0',
                true
            );
        }

        wp_localize_script(self::HANDLE, self::OBJ_NAME, $this->get_data());
    }

    public function render_admin_mount_point(): void
    {
        $screen = get_current_screen();
        if ($screen && in_array($screen->base, $this->allowed_screens, true)) {
            echo '<div id="context-alt-text-admin"></div>';
        }
    }

    private function get_data(): array
    {
        return [
            'developer' => 'prappo',
            'isAdmin'   => is_admin(),
            'apiUrl'    => rest_url(),
            'userInfo'  => $this->get_user_data(),
        ];
    }

    private function get_user_data(): array
    {
        $username   = '';
        $avatar_url = '';

        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $username     = $current_user->user_login;
            $avatar_url   = get_avatar_url($current_user->ID);
        }

        return [
            'username' => $username,
            'avatar'   => $avatar_url,
        ];
    }
}
