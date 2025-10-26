<?php
// Ajuste BASE_PATH conforme a pasta do projeto no htdocs
define('BASE_PATH', '/hackaton-novo/hackaton/sistemas-reservas-senac');
function url(string $relative): string { return rtrim(BASE_PATH, '/').$relative; }
