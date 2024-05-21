<?php

/**
 * Ci permette di controllare se la repo esiste
 * @param $student string utente dello studente per github
 * @param $repo string nome delle repo
 * @return bool se la repo esiste vero altrimenti falso
 */
function checkRepositoryExists($student, $repo)
{
    $url = "https://github.com/$student/$repo.git";
    logMessage("controllo se la $repo esiste.", LOG_INFO, true, $student);
    $check = shell_exec("git ls-remote $url");
    return !empty($check);
}


function logMessage($message, $level, $consoleOut, $actor)
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [" . logLevelString($level) . "] $message";

    $logMessage = $actor . " > " . $logMessage;

    file_put_contents(LOG_FILE, $logMessage . PHP_EOL, FILE_APPEND);

    if ($consoleOut) {
        echo getColoredString($logMessage, $level) . PHP_EOL;
    }
}

function logLevelString($level)
{
    switch ($level) {
        case LOG_INFO:
            return "INF";
        case LOG_ERR:
            return "ERR";
        case LOG_WARNING:
            return "WARN";
    }
}

function getColoredString($text, $status)
{
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Su Windows, restituisci il testo senza colori
        return $text;
    }

    // Codici colori ANSI per Unix/Linux e macOS
    $out = "";
    switch ($status) {
        case LOG_ERR:
            $out = "\033[31m"; // Red color
            break;
        case LOG_WARNING:
            $out = "\033[33m"; // Yellow color
            break;
        case LOG_INFO:
            $out = "\033[32m"; // Green color
            break;
        default:
            $out = "\033[0m"; // No color
            break;
    }

    return $out . $text . "\033[0m";
}

/**
 * @param $command string comando da eseguire
 * @param $params string paramteri del comando separati da spazio
 * @param $background boolean è un comando eseguito in background
 * @param $actor string stringa del prompt
 * @param $terminateOnError boolean se $background è false allora si può usare per bloccare il programma in caso di errore
 * @return int|string
 */
function run($command, $params, $background, $actor, $terminateOnError)
{
    if ($background) {
        // Esegui il comando in background e ottieni il PID
        $pidCommand = "$command $params > /dev/null 2>&1 & echo $!";
        logMessage("$pidCommand", LOG_WARNING, true, $actor);
        $pid = shell_exec($pidCommand);
        return trim($pid);

    } else {
        // Esegui il comando in foreground e ottieni l'output e il codice di uscita
        $output = [];
        $returnCode = 0;

        logMessage("$command $params", LOG_WARNING, true, $actor);
        exec("$command $params", $output, $returnCode);

        if ($returnCode != 0) {
            logMessage("Errore nell'esecuzione del comando con codice $returnCode.", LOG_ERR, true, $actor);
            $logLevel = LOG_ERR;
            if ($terminateOnError) {
                ExitProgram();
            }
        } else {
            $logLevel = LOG_INFO;
        }

        logMessage(implode("\n", $output), $logLevel, false, $actor);

        return ['code' => $returnCode, 'output' => $output];
    }
}


function executeQuery($query, $database, $actor)
{
    // Parametri di connessione al database
    $host = 'localhost';
    $student = 'root'; // Sostituisci con il tuo username
    $password = 'root'; // Sostituisci con la tua password

    // Apertura connessione
    $conn = new mysqli($host, $student, $password, $database);

    // Controlla la connessione
    if ($conn->connect_error) {
        logMessage("Errore connessione db!", LOG_ERR, true, $actor);
        logMessage($conn->connect_error, LOG_ERR, true, $actor);
        ExitProgram();;
    }

    logMessage($query, LOG_INFO, true, $actor);

    // Esecuzione della query
    if ($conn->query($query) !== TRUE) {
        logMessage("Query fallita: " . $query, LOG_ERR, true, $actor);
        // Chiusura della connessione
        $conn->close();
        return false;
    }

    // Chiusura della connessione
    $conn->close();
    return true;
}


/**
 * @return void
 */
function ExitProgram()
{
    echo "Programma terminato!";
    exit(0);
}

/**
 * Crea la directory di appoggio per scaricare la repo dello studente
 * se non esiste questa directory non ha senso continuare
 * @return void
 */
function CheckRepoStatus($actor, $destory, $create)
{

    if (file_exists(REPO_DIR) && $destory) {

        logMessage("La directory " . REPO_DIR . " deve essere ripulita.", LOG_INFO, true, $actor);
        run("rm", "-rf " . REPO_DIR, false, $actor, true);

    }

    if ($create) {

        logMessage("Creazione directory " . REPO_DIR, LOG_INFO, true, $actor);

        if (mkdir(REPO_DIR, 0777, true))
            logMessage("Directory creata correttamente.", LOG_INFO, true, $actor);
        else {
            logMessage("Impossibile creare directory. Il programma non può proseguire", LOG_ERR, true, $actor);
            ExitProgram();
        }
    }

}

function killProjectProcesses($actor, $pids = array())
{

    $output = run("ps -a | grep", CURRENT_STUDENT_REPO_DIR_NAME, false, $actor, false);
    $output = $output['output'];

    $newPid = null;
    foreach ($output as $line) {
        if (preg_match('/^\s*(\d+).*npm\srun\sdev/', $line, $matches)) {
            // Aggiungi il PID specifico per "npm run dev"
            $newPid = $matches[1];
        } elseif (preg_match('/^\s*(\d+)/', $line, $matches)) {
            // Aggiungi altri PID relativi al progetto
            $newPid = $matches[1];
        }

        if (!in_array($newPid, $pids)) {
            $pids[] = $newPid;
        }
    }


    if (count($pids) > 0) {
        logMessage("Eliminazione processi precedenti appesi...", LOG_WARNING, true, $actor);
        foreach ($pids as $pid) {
            run("kill", $pid, false, $actor, false);
        }
    } else {
        logMessage("Nessun processo appeso travato", LOG_INFO, true, $actor);
    }

}

function getLaravelRoutes($actor)
{
    logMessage("Recupero delle rotte Laravel in corso...", LOG_INFO, true, $actor);
    $routesOutput = run(PHP_COMMAND, "artisan route:list", false, $actor, false);
    $lines = $routesOutput['output'];

    $routes = [];
    foreach ($lines as $line) {
        if (preg_match('/(GET|HEAD|POST|PUT|PATCH|DELETE)\s+([^\s]+)/', $line, $matches)) {
            // $matches[1] è il metodo HTTP, $matches[2] è il nome della rotta
            $routes[] = LARAVEL_URL_DEFAULT . "/$matches[2] [$matches[1]]";
        }
    }

    return $routes;
}

function showRouteMenu($routes, $actor)
{
    do {
        echo "**** Valutazione dello studente ****\n";
        echo "\nScegli una rotta:\n";

        // Stampa le rotte con un indice
        foreach ($routes as $index => $route) {
            echo ($index + 1) . ". $route\n";
        }

        echo "0. Esci\n";

        $selectedIndex = readline('Seleziona una rotta (o 0 per uscire): ');

        if ($selectedIndex == 0) {
            logMessage("Uscita dal menu delle rotte", LOG_INFO, false, $actor);
            break; // Esce dal menu
        }

        if (isset($routes[$selectedIndex - 1])) {
            $selectedRoute = $routes[$selectedIndex - 1];

            // Apri la rotta selezionata in Chrome (modifica l'URL base come necessario)
            $url = explode(' ', $selectedRoute)[0];
            run("open -a 'Google Chrome'", "'$url'", false, $actor, false);
        } else {
            logMessage("Selezione non valida.", LOG_WARNING, true, $actor);

        }

    } while (true);
}


// Funzione per determinare il percorso dei comandi PHP e Composer
function findCommandPath($command)
{
    $output = [];
    $returnCode = 0;
    exec("which " . $command, $output, $returnCode);

    if ($returnCode == 0 && !empty($output)) {
        $path = trim($output[0]);
        // Rimuove eventuali comandi aggiuntivi come 'install'
        $path = str_replace(' ', '\\ ', $path);
        // Controlla se il path è valido ed eseguibile
        return $path;
    }

    return null;
}

// Funzione per richiedere conferma dall'utente
function askForConfirmation($message)
{
    echo $message . " (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));

    if (strtolower($response) == 'yes') {
        return true;
    }

    return false;
}

// Modifica la funzione per salvare la configurazione locale
function saveLocalConfig($config)
{
    file_put_contents('configs/local_config.json', json_encode($config));
}

// Modifica la funzione per caricare la configurazione locale
function loadLocalConfig()
{
    if (file_exists('configs/local_config.json')) {
        return json_decode(file_get_contents('configs/local_config.json'), true);
    }

    return [];
}


// Funzione per scaricare composer.phar
function downloadComposerPhar()
{
    $composerPharUrl = 'https://getcomposer.org/composer.phar';
    $composerPharPath = __DIR__ . '/composer.phar';

    if (!file_exists($composerPharPath)) {
        echo "Downloading composer.phar...\n";
        file_put_contents($composerPharPath, file_get_contents($composerPharUrl));
    } else {
        echo "composer.phar already exists at: $composerPharPath\n";
    }

    return $composerPharPath;
}


function getPhpCommand()
{
// Configurazioni iniziali
    $config = loadLocalConfig();

// Controlla se PHP è configurato, altrimenti cerca il comando PHP
    if (empty($config['PHP_COMMAND'])) {
        $phpCommand = findCommandPath('php');
        if ($phpCommand) {
            echo "PHP command found at: $phpCommand\n";
            if (askForConfirmation("Do you want to use this PHP command?")) {
                $config['PHP_COMMAND'] = $phpCommand;
                saveLocalConfig($config);
            } else {
                die("Configuration aborted by user.\n");
            }
        } else {
            die("PHP command not found. Please install PHP or specify its path manually.\n");
        }
    }

    return $config['PHP_COMMAND'];
}

function getComposerCommand(){

    $config = loadLocalConfig();

    // Controlla se Composer è configurato, altrimenti cerca il comando Composer
    if (empty($config['COMPOSER_COMMAND'])) {
        $composerCommand = findCommandPath('composer');
        if ($composerCommand) {
            echo "Composer command found at: $composerCommand\n";
            if (askForConfirmation("Do you want to use this Composer command?")) {
                $config['COMPOSER_COMMAND'] = $composerCommand;
                saveLocalConfig($config);
            } else {
                die("Configuration aborted by user.\n");
            }
        } else {
            echo "Composer command not found. Downloading composer.phar locally...\n";
            $composerPharPath = downloadComposerPhar();
            if ($composerPharPath) {
                $config['COMPOSER_COMMAND'] = PHP_COMMAND . " " . $composerPharPath;
                saveLocalConfig($config);
                echo "Composer downloaded at: $composerPharPath\n";
            } else {
                die("Failed to download composer.phar. Please try again.\n");
            }
        }
    }

    return $config['COMPOSER_COMMAND'];
}


function clearLog() {
    if (file_exists(LOG_FILE)) {
        unlink(LOG_FILE);  // Elimina il file di log
    }
}

function setupLoggingEnvironment() {
    if (!file_exists(LOG_DIR)) {
        mkdir(LOG_DIR, 0777, true); // Crea la directory con permessi di lettura/scrittura/esecuzione
        logMessage("Directory dei log creata.", LOG_INFO, true, "System");
    }

    if (file_exists(LOG_FILE)) {
        unlink(LOG_FILE);  // Cancella il file di log esistente
        logMessage("File di log precedente cancellato.", LOG_INFO, true, "System");
    } else {
        logMessage("File di log creato.", LOG_INFO, true, "System");
    }
}
