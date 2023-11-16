<?php

$random = isset($_GET['r']);
$files = glob(__DIR__ . "/data/*");
$faqs = [];

echo "<pre>";

foreach ($files as $file) {
    $data = json_decode(file_get_contents($file));

    echo "$file<br>";
    echo json_last_error_msg();
    var_dump($data[0]->faqs);
    echo "<br>";
    echo "<br>";
    foreach ($data[0]->faqs as $faq) {
        $faqs[] = $faq;
    }
}

if ($random)
    shuffle($faqs);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TRITASTUDENTI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <style>

        .question-block {
            background-color: #80d5c1;
        }

        .answer-block {
            background-color: lightgrey;
        }

        .block {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 1000px;
            flex-direction: column;
            padding: 15%
        }

        a {
            position: absolute;
            right: 20%;
            bottom: 20%;
        }
    </style>
</head>
<body>


<div class="block">
    <img class="d-block mx-auto mb-4" src="boologo.png" alt="">
    <h1 class="display-5 fw-bold text-body-emphasis text-center">Simulazioni colloqui tecnico (?)</h1>
    <div class="col-lg-6 mx-auto">
        <br>
        <h3 class="text-center">Verifichiamo le nostre conoscenze tecniche e cerchiamo di esprimerci in modo semplice e
            conciso</h3>
    </div>
</div>
<hr/>
<div class="container-fluid mx-0 px-0 ">

    <?php foreach ($faqs as $faqNumber => $faq): ?>
        <div id="q-<?= $faqNumber ?>" class="block question-block">
            <h1><?= $faq->question ?></h1>
            <a href="#a-<?= $faqNumber ?>" class="btn btn-primary" >Risposta</a>
        </div>
        <div id="a-<?= $faqNumber ?>" class="block answer-block">
            <h3><?= $faq->answer ?></h3>
            <a href="#q-<?= $faqNumber + 1 ?>" class="btn btn-primary">Prossima</a>
        </div>
    <?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>
</html>
