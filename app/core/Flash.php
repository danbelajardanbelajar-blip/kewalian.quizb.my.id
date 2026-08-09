<?php
/**
 * Flash.php — Flash Message Helper
 * Pesan sementara yang muncul sekali setelah redirect
 */
class Flash
{
    private static string $key = '_flash_messages';

    /**
     * Set flash message
     * 
     * @param string $type  'success' | 'error' | 'warning' | 'info'
     * @param string $message
     */
    public static function set(string $type, string $message): void
    {
        Session::start();
        $_SESSION[self::$key][] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    /**
     * Get semua flash messages dan hapus dari session
     */
    public static function getAll(): array
    {
        Session::start();
        $messages = $_SESSION[self::$key] ?? [];
        unset($_SESSION[self::$key]);
        return $messages;
    }

    /**
     * Cek apakah ada flash message
     */
    public static function has(): bool
    {
        Session::start();
        return !empty($_SESSION[self::$key]);
    }

    /**
     * Render flash messages sebagai HTML Bootstrap alerts
     */
    public static function render(): string
    {
        $messages = self::getAll();
        if (empty($messages)) {
            return '';
        }

        $typeMap = [
            'success' => 'success',
            'error'   => 'danger',
            'warning' => 'warning',
            'info'    => 'info',
        ];

        $iconMap = [
            'success' => 'bi-check-circle-fill',
            'error'   => 'bi-x-circle-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            'info'    => 'bi-info-circle-fill',
        ];

        $html = '<div id="flash-messages">';
        foreach ($messages as $msg) {
            $bsType = $typeMap[$msg['type']] ?? 'secondary';
            $icon   = $iconMap[$msg['type']] ?? 'bi-bell-fill';
            $html  .= sprintf(
                '<div class="alert alert--%s alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm mb-2" role="alert">'
                . '<i class="bi %s"></i>'
                . '<div>%s</div>'
                . '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>'
                . '</div>',
                $bsType,
                $icon,
                htmlspecialchars($msg['message'])
            );
        }
        $html .= '</div>';
        return $html;
    }
}
