<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Regras Gerais de Validação
    |--------------------------------------------------------------------------
    | Regras que se aplicam a todos os elementos da análise.
    */
    '_regras_gerais' => [
        'nao_negativo' => true, // Não pode haver valores negativos
        'nao_zero' => true,     // Não pode haver valores iguais a zero
        'Mg' > "Ca"
    ],


    /*
    |--------------------------------------------------------------------------
    | Intervalos de Referência Simples
    |--------------------------------------------------------------------------
    */
    'intervalos' => [
        'MO' => ['min' => 0.50, 'max' => 6.00],     // Matéria Orgânica
        'Prem' => ['min' => 4.00, 'max' => 55.00],  // Fósforo Remanescente
        'B' => ['min' => 0.20, 'max' => 2.00],      // Boro
        'S' => ['min' => 8.00, 'max' => 90.00],      // Enxofre
    ],


    /*
    |--------------------------------------------------------------------------
    | Relações Condicionais
    |--------------------------------------------------------------------------
    | Regras onde o valor ideal de um elemento depende de outros.
    */
    '_relacoes' => [
        // Relação entre pH e V% (Saturação de Bases)
        'ph_por_v' => [
            ['v_min' => 0, 'v_max' => 10, 'ph_min' => 4.00, 'ph_max' => 4.58],
            ['v_min' => 10, 'v_max' => 30, 'ph_min' => 4.58, 'ph_max' => 5.17],
            ['v_min' => 30, 'v_max' => 50, 'ph_min' => 5.17, 'ph_max' => 5.67],
            ['v_min' => 50, 'v_max' => 80, 'ph_min' => 5.67, 'ph_max' => 6.42],
            ['v_min' => 80, 'v_max' => 100, 'ph_min' => 6.42, 'ph_max' => 6.99],
        ],
    ],
];