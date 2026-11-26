<?php

$seeds = [
    'UsuarioSeed.php',
    'MotoristaSeed.php',
    'VeiculoSeed.php',
    'PontoDeParadaSeed.php',
    'RotaSeed.php',
    'ViagemSeed.php',
];

foreach ($seeds as $seed) {
    echo "Executando {$seed}...\n";
    require_once __DIR__ . '/' . $seed;
}

echo "Todas as seeds foram executadas com sucesso!\n";