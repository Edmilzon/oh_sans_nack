<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('evaluacion.{id_competencia}', function ($user, $id_competencia) {
    // For now, allow any authenticated user to listen.
    // In a real application, you'd check if the user is an evaluator
    // for this specific competition.
    return $user !== null;
});
