<?php

/*
 * da eseguire in montessori nella home page con tutti gli studenti, dove si vedono anche gli esercizi
 *
Object.keys(obj.students).forEach(item => console.log(obj.students[item].github_user)) per trovare gli studenti
const lista = [];
    Object.keys(obj.students).forEach(item => {
        lista.push(`"${obj.students[item].github_user}"`);
    });
    console.log(lista.join(",\n    "));
 *
 */


/**
 * CONFIGURAZIONI DELLE COSTANTI "statiche"
 */
const DB_NAME = 'tritalaravel'; //questo deve essere configurato manualmente e deve corrispondere ad .env todo: fare automatismo

const FAKE_USER_NAME = "tritalaravel";
const FAKE_USER_EMAIL = "tutormonster@boolean.it";
const FAKE_USER_PASS = "tritalaravel";
const ROOT_DIR = __DIR__;
const CONFIG_DIR = ROOT_DIR . DIRECTORY_SEPARATOR . "configs";
/*
 * questa directory viene rimossa e creata dallo script,
 * non serve avere la repo di ogni studente.
 * aiuta a mantenere più pulito il filesystem
 */

const CURRENT_STUDENT_REPO_DIR_NAME = "CURRENT_STUDENT_REPO";

const REPO_DIR = ROOT_DIR . DIRECTORY_SEPARATOR . CURRENT_STUDENT_REPO_DIR_NAME;
const LOG_DIR = ROOT_DIR . DIRECTORY_SEPARATOR . "logs";
const LOG_FILE = LOG_DIR . DIRECTORY_SEPARATOR . "tritalaravel.log";
const TRITALARAVEL_ENV = CONFIG_DIR . DIRECTORY_SEPARATOR . ".env";
const CLASS_PARTS = 2;
const LARAVEL_URL_DEFAULT = "http://localhost:8000";
const VITE_URL_5176 = "http://localhost:5176";
const VITE_URL_5173 = "http://localhost:5173";
const VITE_URL_5174 = "http://localhost:5174";


//per non far arrabbiare laravel aspettiamo sia vite che laravel si avviino prima di aprire chrome
const STATIC_AIWAIT_SERVER_SECOND = 4;


/**
 * CONFIGURAZIONI DELLE COSTANTI "DINAMICHE"
 * pre gestire valori usati in comune con i vari script
 * le scriviamo in maiuscole
 */

// Controlla se il file students.php esiste
if (!file_exists('configs/students.php')) {
    logMessage("Il file 'configs/students.php' non esiste. Crealo e aggiungi l'elenco degli studenti con lo script apposito o manualmente.", LOG_ERR, true, "tritalaravel");
    die();
}

$students = include 'configs/students.php';

// Controlla se l'array degli studenti è vuoto
if (empty($students)) {

    logMessage("Errore: L'elenco degli studenti è vuoto. Aggiungi gli studenti nel file 'configs/students.php", LOG_ERR, true, "tritalaravel");
    die();
}

define("STUDENTS_GITHUB", $students);
// Utilizza i comandi confermati o configurati
define('PHP_COMMAND', getPhpCommand());
define('COMPOSER_COMMAND', getComposerCommand());

// Determina la lunghezza dell'array e calcola il punto di divisione
$STUDENT_COUNT = count(STUDENTS_GITHUB);
//gestisce il numero di studenti presenti in ogni parte della classe, di solito è solo la metà
$CLASS_PART_NUMBER = ceil($STUDENT_COUNT / CLASS_PARTS);


/**
 * GESTIONE AVVIO SCRIPT
 */

// Verifica se il nome della repository è stato fornito come parametro
if ($argc < 2 || $argc > 3) {
    die("Errore: Devi specificare il nome delle repository separate da spazio (max 2).\n");
}


/**
 * DEFINIZIONE DELLE "COSTANTI" dinamiche
 */
//faccio una variabile scritta come costante per renderla una "conf"
$REPO_LIST = [];

if (isset($argv[1]) && $argv[1] != "") {
    $REPO_LIST[] = $argv[1];
}

if (isset($argv[2]) && $argv[2] != "") {
    $REPO_LIST[] = $argv[2];
}


/**
 * DATABASE
 */
$FAKE_USER_PASS_HASHED = password_hash(FAKE_USER_PASS, PASSWORD_DEFAULT); // Hash della password

$EMAIL_VERIFIED_AT = date('Y-m-d H:i:s'); // Timestamp corrente per email verificata

$FAKE_USER_QUERY = "INSERT INTO users (name, email,  password,  email_verified_at )   VALUES ('" . FAKE_USER_NAME . "',  '" . FAKE_USER_EMAIL . "',  '" . $FAKE_USER_PASS_HASHED . "',  '" . $EMAIL_VERIFIED_AT . "');";
