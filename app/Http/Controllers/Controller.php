<?php

namespace App\Http\Controllers;
//on importe le trait qui contient la méthode authorize() pour les politiques d'accès
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
abstract class Controller
{
    //on utilise le trait AuthorizesRequests pour pouvoir utiliser la méthode authorize() dans tous les contrôleurs qui héritent de ce Controller
    use AuthorizesRequests;
}
