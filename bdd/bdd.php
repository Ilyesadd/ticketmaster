<?php


    try{
        $user = "root";
        $pass = "rot";
        $bdd = new PDO('mysql:host=localhost;dbname=ticketmaster', $user, $pass);

    }catch(PDOException $e){
        print "Erreur! :" . $e->getMessage() .
        "<br/>";
        die();
    }




?>