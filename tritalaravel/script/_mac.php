<?php

/**
 * @return void
 */


logMessage("Studenti totali $STUDENT_COUNT", LOG_INFO, true, "tritalaravel");

CheckRepoStatus("tritalaravel", true, false);

while (true) {

    echo "****\tMenu\t****" . PHP_EOL;

    foreach (STUDENTS_GITHUB as $index => $student) {
        echo "\t$index - $student" . PHP_EOL;
    }

    // Chiedi quale/i utente/i verificare
    $response = readline("Quale utente vuoi verificare?\n(Inserisci un numero, 'da [numero]', 'prima metà', 'seconda metà', 'exit' per uscire): ");
    logMessage("l'utente ha scelto $response", LOG_INFO, false, $student);

    // Gestisci il comando di uscita
    if (strtolower($response) === 'exit') {
        ExitProgram();
    }

    // Gestisci la risposta
    if (is_numeric($response) && $response >= 0 && $response < $STUDENT_COUNT) {
        // Un solo utente specificato
        $selectedStudents = array_slice(STUDENTS_GITHUB, $response, 1);
    } elseif (strpos($response, 'da ') === 0) {
        // Intervallo specificato
        $start = intval(substr($response, 3));
        $selectedStudents = array_slice(STUDENTS_GITHUB, $start);

    } elseif ($response === 'prima metà') {
        // Prima metà degli utenti
        $selectedStudents = array_slice(STUDENTS_GITHUB, 0, $CLASS_PART_NUMBER);
    } elseif ($response === 'seconda metà') {
        // Seconda metà degli utenti
        $selectedStudents = array_slice(STUDENTS_GITHUB, $CLASS_PART_NUMBER);
    } else {
        $selectedStudents = array(); // questo impedisce errori nel ciclo dopo
        readline("Nessuna scelta valida, premi invio per continuare!");

    }

    foreach ($selectedStudents as $student) {

        $clone_url = "https://github.com/{$student}/{$REPO_NAME}.git";

        if (!checkRepositoryExists($student, $REPO_NAME)) {
            $message = "Lo studente $student non ha caricato la repo $REPO_NAME";
            logMessage($message, LOG_WARNING, true, $student);
            readline($message . "\nContatta lo studente e premi invio per continuare!");
            continue; //prossimo studente
        }

        logMessage("Pulizia Filesystem...", LOG_INFO, true, $student);
        CheckRepoStatus($student, true, true);

        logMessage("Pulizia database...", LOG_INFO, true, $student);
        executeQuery("DROP DATABASE IF EXISTS `" . DB_NAME . "`;", null, $student);
        executeQuery("CREATE DATABASE `" . DB_NAME . "`;", null, $student);

        logMessage("Mi sposto in " . REPO_DIR, LOG_WARNING, true, $student);
        chdir(REPO_DIR);

        logMessage("Inizio clonazione e installazione LARAVEL", LOG_INFO, true, $student);
        $returnCode = run("git", "clone $clone_url", false, $student, false);
        if ($returnCode != 0) {
            readline("Non è possibile proseguire, premere invio per passare al prossimo studente.");
            continue;
        }

        logMessage("Mi sposto in " . $REPO_DIR, LOG_WARNING, true, $student);
        chdir($REPO_DIR);

        //questo comando serve a copiare il custom env per far funzionare laravel sulla macchina locale
        run("cp", TRITALARAVEL_ENV . " " . $REPO_ENV, false, $student, false);

        //sfilza di comandi
        run("npm", "install", false, $student, false);
        run(PHP_COMMAND . " " . COMPOSER_COMMAND, "install", false, $student, false); // Installazione delle dipendenze Composer
        run(PHP_COMMAND, "artisan key:generate", false, $student, false);
        run(PHP_COMMAND, "artisan migrate:install", false, $student, false);
        run(PHP_COMMAND, "artisan migrate", false, $student, false);
        run(PHP_COMMAND, "artisan db:seed", false, $student, false);
        run(PHP_COMMAND, "artisan storage:link", false, $student, false);

        executeQuery($FAKE_USER_QUERY, DB_NAME, $student);

        logMessage("Avvio vite...", LOG_INFO, true, $student);
        $npmProcess = run("npm", "run dev", true, $student, false); // Avvio NPM in background
        logMessage("Avvio artisan...", LOG_INFO, true, $student);
        $laravelProcess = run(PHP_COMMAND, " artisan serve", true, $student, false); // Avvio Laravel in background

        logMessage("...attendi " . STATIC_AIWAIT_SERVER_SECOND . " secondi.", LOG_INFO, true, $student);
        sleep(STATIC_AIWAIT_SERVER_SECOND);

        run("open", "-a 'Google Chrome' " . CHROME_URL_DEFAULT, false, $student, false); // Sostituire con il comando appropriato per il tuo sistema operativo
        // Attesa della chiusura di Chrome
        logMessage("Ora valuta lo studente...", LOG_INFO, true, $student);
        readline("Premi [Enter] dopo aver chiuso Chrome...");

        logMessage("Chiusura laravel...", LOG_INFO, true, $student);
        run("kill", $laravelProcess, false, $student, false);
        logMessage("Chiusura VITE...", LOG_INFO, true, $student);
        run("kill", $npmProcess, false, $student, false);

        logMessage("Pulizia database...", LOG_INFO, true, $student);
        executeQuery("DROP DATABASE IF EXISTS `" . DB_NAME . "`;", null, $student);

        logMessage("Mi sposto in " . ROOT_DIR, LOG_WARNING, true, $student);
        chdir(ROOT_DIR);

        //se chiedo un solo utente oppure esco devo per forza cancellare senza ricreare
        //questo lascia il filesystem pulito
        CheckRepoStatus($student, true, false);

    }
}


?>
