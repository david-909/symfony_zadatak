<?php

namespace App\My;

class Regex
{
    public $regexs = ["/(.*[A-Z].*)$/", "/(.*[a-z].*)$/", "/(.*[0-9].*)$/", "/(.*[.,!@#$%^&*()].*)$/"];
    public $regexErrors = ["Nedostaje 1 veliko slovo.", "Nedostaje 1 malo slovo.", "Nedostaje 1 cifra.", "Nedostaje jedan speciajlni karakter."];

    public function checkRegex($regex, $data)
    {
        if (!preg_match($regex, $data)) {
            return 0;
        } else return 1;
    }
}
