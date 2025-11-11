<?php
class AdminUtilitiesController {
    public static function audit(): void {
        require_admin();
        $rows = AuditLog::latest(200);
        render('admin/audit', compact('rows'));
    }
}