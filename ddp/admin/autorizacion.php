<?php
declare(strict_types=1);

function esEditor(): bool
{
    return strtolower(trim((string) ($_SESSION['rol'] ?? ''))) === 'editor';
}

function exigirGestionUsuarios(): void
{
    if (esEditor()) {
        http_response_code(403);
        exit('No tienes permisos para gestionar usuarios.');
    }
}
