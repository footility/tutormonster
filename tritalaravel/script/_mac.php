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

        logMessage("Pulizia Filesystem...", LOG_INFO, true, $student);
        CheckRepoStatus($student, true, true);

        logMessage("Pulizia database...", LOG_INFO, true, $student);
        executeQuery("DROP DATABASE IF EXISTS `" . DB_NAME . "`;", null, $student);
        executeQuery("CREATE DATABASE `" . DB_NAME . "`;", null, $student);

        killProjectProcesses($student);

        //rotte di test
        $routes = [];

        foreach ($REPO_LIST as $REPO_NAME) {


            if (!checkRepositoryExists($student, $REPO_NAME)) {
                $message = "Lo studente $student non ha caricato la repo $REPO_NAME";
                logMessage($message, LOG_WARNING, true, $student);
                readline($message . "\nContatta lo studente e premi invio per continuare!");
                continue; //prossimo repo o prossimo studente
            }

            logMessage("Mi sposto in " . REPO_DIR, LOG_WARNING, true, $student);
            chdir(REPO_DIR);

            $clone_url = "https://github.com/{$student}/{$REPO_NAME}.git";

            logMessage("Inizio clonazione e installazione ambiente", LOG_INFO, true, $student);
            $runResponse = run("git", "clone $clone_url", false, $student, false);
            if ($runResponse['code'] != 0) {
                readline("Non è possibile proseguire, premere invio per passare al prossimo studente.");
                continue;
            }

            $REPO_DIR = REPO_DIR . DIRECTORY_SEPARATOR . $REPO_NAME;
            $REPO_ENV = $REPO_DIR . DIRECTORY_SEPARATOR . ".env";

            logMessage("Mi sposto in " . $REPO_DIR, LOG_WARNING, true, $student);
            chdir($REPO_DIR);


            //gestisco l'installazione di laravel solo nei progetti laravel
            if (file_exists("artisan") && file_exists("composer.json")) {

                logMessage("trovato progetto laravel", LOG_INFO, true, $student);
                //questo comando serve a copiare il custom env per far funzionare laravel sulla macchina locale
                run("cp", TRITALARAVEL_ENV . " " . $REPO_ENV, false, $student, false);

                //sfilza di comandi

                run(PHP_COMMAND . " " . COMPOSER_COMMAND, "install", false, $student, false); // Installazione delle dipendenze Composer
                run(PHP_COMMAND, "artisan key:generate", false, $student, false);
                run(PHP_COMMAND, "artisan migrate:install", false, $student, false);
                run(PHP_COMMAND, "artisan migrate", false, $student, false);

                // Trova tutte le classi seeder nella directory database/seeders eccetto DatabaseSeeder.php
                $seeders = glob('database/seeders/*.php');
                foreach ($seeders as $seeder) {
                    $className = basename($seeder, '.php');
                    if ($className !== 'DatabaseSeeder') {
                        run(PHP_COMMAND, "artisan db:seed --class=$className", false, $student, false);
                    }
                }

                run(PHP_COMMAND, "artisan storage:link", false, $student, false);

                executeQuery($FAKE_USER_QUERY, DB_NAME, $student);

                $routes = array_merge($routes, getLaravelRoutes($student));

                logMessage("Avvio artisan...", LOG_INFO, true, $student);
                $artisanProcess = run(PHP_COMMAND, " artisan serve", true, $student, false); // Avvio Laravel in background

            } else {
                logMessage("Composer o laravel non presenti, installazione non possibile.", LOG_ERR, true, $student);
            }

            if (file_exists("package.json")) {

                logMessage("trovato progetto compatiile npm", LOG_INFO, true, $student);
                //per tutti gli altri progetti
                run("npm", "install", false, $student, false);
                logMessage("Avvio server (se presente)...", LOG_INFO, true, $student);
                run("npm", "run build", false, $student, false); // Avvio NPM in background
                $npmProcess = run("npm", "run dev", true, $student, false); // Avvio NPM in background

                if (!in_array(VITE_URL_5176, $routes) &&
                    !in_array(VITE_URL_5173, $routes) &&
                    !in_array(VITE_URL_5174, $routes)
                ) {
                    $routes[] = VITE_URL_5176;
                    $routes[] = VITE_URL_5173;
                    $routes[] = VITE_URL_5174;
                }

            } else {
                logMessage("package.json assente, imppssibile installare progetto.", LOG_ERR, true, $student);
            }
        }

        showRouteMenu($routes, $student);

        killProjectProcesses($student, [$artisanProcess, $npmProcess]);

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
