<?php
//gestione funzioni comuni
require_once "functions.php";
//ATTENZIONE: gestione dell'avvio dello scipt
require_once "config.php";


/**
 * Luncher di tritalaravel per innescare il sistema operativo corretto
 */
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "Esecuzione su Windows\n";
    include "script/_windows.php";
} elseif (PHP_OS === 'Linux') {
    echo "Esecuzione su Linux\n";
    include "script/_linux.php";
} elseif (PHP_OS === 'Darwin') {
    echo "Esecuzione su macOS\n";
    include "script/_mac.php";
} else {
    echo "Sistema Operativo non riconosciuto, script non gestito: PHP_OS\n";
}
