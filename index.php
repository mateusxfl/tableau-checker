<?php

    include "classes/no.php";
    include "classes/ramo.php";

    $ramoInicial = new Ramo();

    $ramoInicial->adicionarHipoteses("T", "(p > q)"); 
    $ramoInicial->adicionarHipoteses("T", "(r > s)"); 
    $ramoInicial->adicionarHipoteses("F", "((p # r) > (q # s))"); 

    // echo "<pre> Ramo inicial: "; var_dump($ramoInicial); echo "</pre>";

    $ramoInicial->expandirRamo($ramoInicial);

?>