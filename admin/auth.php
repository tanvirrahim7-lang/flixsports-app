<?php
require_once __DIR__ . '/../config.php';

if (!isAdmin()) {
    redirect(ADMIN_URL);
}
