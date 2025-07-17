<?php

namespace App\Config;
use PDO;
use PDOException;


class Database{

    //Propriétés statiques privées pour stocker l'instance unique de PDO
    private static ?PDO $instance = null;

    //le constructeur est privé pour empecher la création d'objet  via new database
    private function __construct(){}

    //la méthode de clonage est privée pour empecher de cloner l'instance
    private function __clone(){}

    //méthode qui permet d'avoir le point d'acces à la BDD
    public static function getInstance():PDO{

        //SI l'instance n'a pas été crée
        if(self::$instance === null){

            //On construit le DSN (Data source Name) avec les infos du fichier .env
            $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4", Config::get('DB_HOST'), Config::get('DB_PORT', '3306'), Config::get('DB_NAME'));

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //lance les exceptions en cas erreur SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC //récupère les résultats sous forme de tableau associatif
            ];

            try{

                //On crée l'instance de PDO et on la stocke
                self::$instance = new PDO($dsn, Config::get('DB_USER'), Config::get('DB_PASSWORD'), $options);
            }catch(PDOException $e){
                die("Erreur de connexion à la base de donées : ". $e->getMessage());
            }
        }
        return self::$instance;
    }
}