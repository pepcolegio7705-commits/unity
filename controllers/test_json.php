<?php
$_POST = [
    'action' => 'listar',
    'start' => 0,
    'length' => 10,
    'search' => ['value' => ''],
    'draw' => 1,
    'curso_id' => '',
    'fecha' => '',
    'tipo_id' => '',
    'alumno' => ''
];
$_SESSION = ['usuario_id' => 1]; // Simulate logged in
require 'asistencias_ajax.php';
