<?php

function checkRepositoryExists($user, $repo)
{
    $url = "https://github.com/$user/$repo.git";
    logMessage("controllo se la $repo esiste.", LOG_INFO, true, $user);
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

        return $returnCode;
    }
}


function executeQuery($query, $database, $actor)
{
    // Parametri di connessione al database
    $host = 'localhost';
    $user = 'root'; // Sostituisci con il tuo username
    $password = 'root'; // Sostituisci con la tua password

    // Apertura connessione
    $conn = new mysqli($host, $user, $password, $database);

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
    }

    // Chiusura della connessione
    $conn->close();
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
