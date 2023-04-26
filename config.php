<?php

class config {

    /**
     * Static function for setting API Important configurations, Change according your server settings.
     * @author Sudhanshu Saxena <marjss21@gmail.com>
     */
    public static function params() {
        return[
            "db_host" => "localhost",
            "db_name" => "db_assignment",
            "db_username" => "root",
            "db_password" => "",
            "url" => "http://localhost/assignment/" //this is the rest api base url.
        ];
    }

}
