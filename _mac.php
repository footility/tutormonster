<?php



include "lib/functions.php";

//config
$dbName = "tritalaravel"; //quest
$config_dir = __DIR__ . "/config";
$repos_dir = "tritalaravel"; //se cambiata da mettere in .gitignore
$repo_name = "laravel-api";
//fine config

echo "*** Inizio verifica compiti ***\n";

// lista degli utenti da controllare
$users = array(
    "Claudio-Bernardinetti",
    "ElisaBoscani",
    "giacomobranchi",
    "samuelebruni",
    "EliaBuratti",
    "FedericaCampo97",
    "marcocentonze",
    "PietroCipolla95",
    "FabioCostantino84",
    "AntoninoCucuzza",
    "GiuseppeDalessio7",
    "EsterDahri1",
    "Valentina-De-Mite",
    "valeriodipaolo1997",
    "CarloFanelli",
    'lucafranzoi98',
    "LiciaLicari",
    "francescomascellino",
    "Francesco-Munafo",
    "matteoNapoli42",
    "lorenzo-neri",
    "MatteoNocera",
    "danielplebani",
    "Pietro907",
    "davide-rullo",
    "alessandrosaladino-tech",
    "DaniloSalerno",
    "RiccardoSansone",
    "rpsobolewski",
    "vgianluca96",
    "FabioVasi",
    "RiccardoVoltolina",
    "MarcoZellini"
);


// Flag per scegliere quale metà degli utenti processare
$processFirstHalf = 2; // Imposta su false per processare la seconda metà

// Determina la lunghezza dell'array e calcola il punto di divisione
$userCount = count($users);
$halfPoint = ceil($userCount / 2);


echo "Studenti $userCount\n";

if (!file_exists($repos_dir)) {
    echo "Creazione directory delle reopository necessaria\n";
    mkdir($repos_dir, 0777, true);
}

echo "Directory repository OK\n";
// Memorizza la directory corrente iniziale
$initial_dir = getcwd();

while (true) {

    echo "Elenco degli utenti:\n";
    foreach ($users as $index => $user) {
        echo ($index + 1) . ". $user\n";
    }

    // Chiedi quale/i utente/i verificare
    $response = readline("Quale utente vuoi verificare? (Inserisci un numero, 'da [numero]', 'prima metà', 'seconda metà', 'exit' per uscire): ");

    // Gestisci il comando di uscita
    if (strtolower($response) === 'exit') {
        $continueScript = false;
        break;
    }

    // Gestisci la risposta
    if (is_numeric($response) && $response > 0 && $response <= $userCount) {
        // Un solo utente specificato
        $selectedUsers = array_slice($users, $response - 1, 1);
    } elseif (strpos($response, 'da ') === 0) {
        // Intervallo specificato
        $start = intval(substr($response, 3)) - 1;
        $selectedUsers = array_slice($users, $start);
    } elseif ($response === 'prima metà') {
        // Prima metà degli utenti
        $selectedUsers = array_slice($users, 0, $halfPoint);
    } elseif ($response === 'seconda metà') {
        // Seconda metà degli utenti
        $selectedUsers = array_slice($users, $halfPoint);
    }

    foreach ($selectedUsers as $user) {

        $user_dir = "$initial_dir/$repos_dir/$user";
        $repo_dir = "$user_dir/$repo_name";

        echo "Eliminazione vecchio db $dbName\n";
        executeQuery("DROP DATABASE `$dbName`;");

        echo "Controllo sporcizia $user\n";
        if (file_exists($user_dir)) {

            echo "Cancello vecchia directory $user_dir\n";
            run("rm -rf $user_dir");

        }

        echo "Creazione nuovo db $dbName\n";
        executeQuery("CREATE DATABASE `$dbName`;");

        echo "Creazione nuova directory $dbName $user_dir\n";
        mkdir($user_dir, 0777, true);

        echo "Cambio directory in $user_dir\n";
        chdir($user_dir);

        echo "Verifica directory: " . getcwd() . "\n";

        $clone_url = "https://github.com/{$user}/{$repo_name}.git";
        run("git clone " . $clone_url);

        echo "Cambio directory in $repo_dir\n";
        chdir($repo_dir);

        echo "Verifica directory: " . getcwd() . "\n";

        echo "Lettura .env_example per $user\n";
        // Leggi il contenuto del file .env
        $envContent = file_get_contents($config_dir . "/.env", true);
        echo "Salvataggio .env per $user\n";
        // Scrivi il contenuto modificato nel file .env
        file_put_contents($repo_dir . "/.env", $envContent);

        // Installazione di npm
        echo "Installazione di NPM per \n";
        run("npm install");

        echo "/usr/local/bin/php /Users/mistre/Library/Application\ Support/Herd/bin/composer install\n";
        run("/usr/local/bin/php /Users/mistre/Library/Application\ Support/Herd/bin/composer install"); // Installazione delle dipendenze Composer
        echo "/usr/local/bin/php artisan key:generate \n";
        run("/usr/local/bin/php artisan key:generate");
        echo "/usr/local/bin/php artisan migrate:install \n";
        run("/usr/local/bin/php artisan migrate:install");
        echo "/usr/local/bin/php artisan migrate \n";
        run("/usr/local/bin/php artisan migrate");
        echo "/usr/local/bin/php artisan db:seed \n";
        run("/usr/local/bin/php artisan db:seed");
        echo "/usr/local/bin/php artisan storage:link \n";
        run("/usr/local/bin/php artisan storage:link");

        echo "Creazione utente di test user:tritalaravel@boolean.it pass:tritalaravel\n";
        $email = 'tritalaravel@boolean.it';
        $password = password_hash('tritalaravel', PASSWORD_DEFAULT); // Hash della password
        $emailVerifiedAt = date('Y-m-d H:i:s'); // Timestamp corrente per email verificata

        $query = "INSERT INTO 
              users (name, email, password, email_verified_at) 
              VALUES ('Paolo', '$email', '$password', '$emailVerifiedAt');
             ";

        executeQuery($query, $dbName);

        echo "npm run dev & $\n";
        $npmProcess = run("npm run dev > /dev/null 2>&1 & echo $!"); // Avvio NPM in background
        echo "/usr/local/bin/php artisan serve &\n";
        $laravelProcess = run("/usr/local/bin/php artisan serve > /dev/null 2>&1 & echo $!"); // Avvio Laravel in background

        // Avvio di Chrome
        echo "open -a 'Google Chrome' http://localhost:8000 \n";
        run("open -a 'Google Chrome' http://localhost:8000"); // Sostituire con il comando appropriato per il tuo sistema operativo

        // Attesa della chiusura di Chrome
        echo "Valutazione umana di $user\n";
        readline("Premi [Enter] dopo aver chiuso Chrome...");

        // Terminazione dei processi Laravel e NPM
        echo "Terminazione dei processi per $user\n";
        run("kill $laravelProcess");
        run("kill $npmProcess");

        echo "Eliminazione db $dbName\n";
        executeQuery("DROP DATABASE `$dbName`;");

        echo "Cancellazione directory $repo_dir\n";
        run("rm -rf $repo_dir");

        // Torna alla directory corrente iniziale dopo ogni operazione
        chdir($initial_dir);
    }
}


?>
