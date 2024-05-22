<?php
//gestione funzioni comuni
require_once "functions.php";
//ATTENZIONE: gestione dell'avvio dello scipt
require_once "config.php";

/*
 * Sei invitato a contribuire allo sviluppo di questo strumento. Ogni suggerimento o miglioramento è ben accetto!
 * Al momento lo script si lancia attraverso il singolo file `run.php`, che seleziona lo script effettivo in base al sistema operativo:

        1. `_linux.php`
        2. `_mac.php`
        3. `_windows.php`
 * Attualmente, soltanto il file `_mac.php` contiene "l'algoritmo" dello script.
 * Se vuoi contribuire per Windows o per Linux, copia tutto il contenuto dello script per macOS nel file corrispondente al tuo sistema operativo.
 * Apporta le modifiche necessarie per risolvere eventuali errori specifici del tuo sistema operativo.

 */

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
