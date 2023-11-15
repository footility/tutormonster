<?php

function checkRepositoryExists($user, $repo)
{
    $url = "https://github.com/$user/$repo.git";
    $check = shell_exec("git ls-remote $url");
    return !empty($check);
}

function logMesssage($message, $iseEcho)
{

    echo "$message\n";
    file_put_contents($logFile, $message . "\n", FILE_APPEND);
}


function run($command)
{
    $output = shell_exec($command);
    echo $output . "\n";
}

function executeQuery($query, $database = null)
{
    // Parametri di connessione al database
    $host = 'localhost';
    $user = 'root'; // Sostituisci con il tuo username
    $password = 'root'; // Sostituisci con la tua password

    // Apertura connessione
    $conn = new mysqli($host, $user, $password, $database);

    // Controlla la connessione
    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    echo "Esecuzione query $query\n";

    // Esecuzione della query
    if ($conn->query($query) === TRUE) {
        echo "Query eseguita con successo: $query\N";
    } else {
        echo "Errore nell'esecuzione della query: " . $conn->error . "\n";
    }

    // Chiusura della connessione
    $conn->close();
}
