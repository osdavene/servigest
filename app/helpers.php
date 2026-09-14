<?php

// Helpers globales para ServiGest

if (!function_exists('formato_moneda')) {
    function formato_moneda($monto) {
        return '$' . number_format((float)$monto, 0, ',', '.');
    }
}
