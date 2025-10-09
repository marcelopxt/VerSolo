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

        'P' => ['min' => 2.0, 'max' => 80.0],
        'K +' => ['min' => 10.0, 'max' => 220.0],
        'Ca 2+' => ['max' => 10.0],
        'Zn' => ['min' => 1.00, 'max' => 12.00],
        'Cu' => ['min' => 0.10, 'max' => 5.00],
        'Mn' => ['min' => 1.00, 'max' => 20.00],
        'Fe' => ['min' => 20.00, 'max' => 150.00],
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

    // Regra de comparação entre dois elementos (Mg > Ca)
    'comparacoes' => [
        ['elemento1' => 'Mg 2+', 'operador' => '<', 'elemento2' => 'Ca 2+', 'mensagem' => 'Valor de Mg deve ser menor que o de Ca'],
    ],

    'regras_por_faixa_ca' => [
        [
            'ca_faixa' => ['min' => 0.20, 'max' => 1.30],
            'regras' => [
                'Al 3+' => ['min' => 0.50, 'max' => 1.90],
            ]
        ],
        [
            'ca_faixa' => ['min' => 1.30, 'max' => 3.40],
            'regras' => [
                'Al 3+' => ['min' => 0.30, 'max' => 1.00],
            ]
        ],
        [
            'ca_faixa' => ['min' => 3.40, 'max' => 4.50],
            'regras' => [
                'Al 3+' => ['max' => 1.00],
            ]
        ],
        [
            'ca_faixa' => ['min' => 5.00],
            'regras' => [
                'Al 3+' => ['max' => 0.80],
            ]
        ],

    ]
];