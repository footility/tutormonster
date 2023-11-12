Script TRITALARAVEL
Ho sviluppato uno script automatico, “TritaLaravel” versione beta, per migliorare l’analisi degli esercizi di Laravel.
Questo ha rallentato un pò la gestione delle valutazioni ma ci tengo a precisare che non ha influenzato la gestione del lavoro pomeridiano da tutor!
Mi ero ripromesso da tempo di farlo per ottimizzare il lavoro pomeridiano e migliorare il feedback.

Cosa fa in linea generale:
Scarica la repo dello studente
Riconfigura .env per poter cerare un db con il pattern [utente]_[esercizio]
installa npm
installa laravel (composer)
poi effettua: generazione chiave, migrazione, seed, storage link.
innietta un utente con una query diretta sulla tabella user
esegue in background vite e

Problemi noti dello script:
Rimozione della repo scaricata: Il log impedisce la completa cancellazione delle directory con rm -rf, probabilmente per un problema di lock dei file durante la rimozione.
Esecuzione del seeder: Ci sono stati problemi frequenti, forse legati ai seeder degli studenti o a permessi di scrittura sul mio mac, magari proprio causati dal fatto che sto eseguendo uno script con shell_exec.
!Non so che problemi può creare su WINDOWS! :joy:

Ovviamente è necessario migliorarlo, l’ho fatto a best effort!
A domani!
