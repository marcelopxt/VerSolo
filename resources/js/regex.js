function extrairTabelaDeElementos(textoCompleto) {
    const regexCabecalho = /AMOSTRA\s+((?:S\d+\s*)+)/;
    const matchCabecalho = textoCompleto.match(regexCabecalho);

    if (!matchCabecalho) {
        console.error("Não foi possível encontrar o cabeçalho da tabela de resultados (Ex: AMOSTRA S...).");
        return { protocolos: [], tabela: "" };
    }
    const protocolos = matchCabecalho[1].trim().split(/\s+/);

    const indiceInicioTabela = matchCabecalho.index + matchCabecalho[0].length;
    const marcadorDeFim = 'PH em água, KCl e CaCl 2 - Relação';
    const indiceFimTabela = textoCompleto.indexOf(marcadorDeFim);

    if (indiceFimTabela === -1) {
        console.error("Não foi possível encontrar o marcador de fim da tabela de elementos.");
        return { protocolos: [], tabela: "" };
    }

    const tabelaExtraida = textoCompleto.substring(indiceInicioTabela, indiceFimTabela).trim();

    return { protocolos, tabela: tabelaExtraida };
}

function processarDadosDeAnalise(textoCompleto) {
    const { protocolos, tabela } = extrairTabelaDeElementos(textoCompleto);

    if (protocolos.length === 0) {
        return {};
    }

    const numeroDeAmostras = protocolos.length;
    const resultado = {};
    protocolos.forEach(p => { resultado[p] = {}; });

    const chavesDaAnalise = [
        'PH em água', 'PH em CaCl 2', 'PH em KCl', 'P', 'K +', 'Ca 2+', 'Mg 2+', 'Al 3+',
        'H', 'H+Al', 'SB', 't', 'T', 'V', '%m', 'MO', 'Prem', 'Zn', 'Fe', 'Mn', 'Cu', 'B', 'S',
        'K', 'Ca', 'Mg', 'H', 'Al',
        'Ca/Mg', 'Ca/K', 'Mg/K'
    ];

    let searchFromIndex = 0;

    chavesDaAnalise.forEach((chave) => {
        const indiceInicial = tabela.indexOf(chave, searchFromIndex);
        if (indiceInicial === -1) return;

        let indiceFinal = tabela.length;
        for (const outraChave of chavesDaAnalise) {
            const tempIndex = tabela.indexOf(outraChave, indiceInicial + chave.length);
            if (tempIndex !== -1 && tempIndex < indiceFinal) {
                indiceFinal = tempIndex;
            }
        }

        searchFromIndex = indiceInicial + 1;

        const linha = tabela.substring(indiceInicial, indiceFinal).trim();
        const parteDosValores = linha.substring(chave.length).trim();

        const valorRegex = '\\s+([\\d,.-]+)';
        const regex = new RegExp(`^(.*?)${valorRegex.repeat(numeroDeAmostras)}$`);
        const match = parteDosValores.match(regex);

        if (!match) return;

        const [, unidade, ...valoresStr] = match;

        const parseValor = (str) => {
            if (str === '-') return null;
            return parseFloat(str.replace(',', '.'));
        };

        protocolos.forEach((protocolo, index) => {
            if (!resultado[protocolo][chave]) {
                resultado[protocolo][chave] = {
                    valor: parseValor(valoresStr[index]),
                    unidade: unidade.trim()
                };
            }
        });
    });

    return resultado;
}


const texto1 = `LABORATÓRIO DE ANÁLISE DE SOLOS E MATERIAIS VEGETAIS SINDICATO DOS PRODUTORES RURAIS DE MANHUAÇU E-mail: labsolosindicato@hotmail.com Telefone: (33) 3331-1667 (33) 98444-0890 Rua Cel. José Pedro, 209 - Centro - Manhuaçu/MG Cliente: RITCHELLY DUTRA CPF: Emissão: 18/09/2025 Matrícula: - Propriedade: FAZENDA CATADUPA Município: MATIPÓ Estado: MG Área: - RESULTADO DE ANÁLISE DE SOLO Protocolo Identificação Entrada Cultura Valor S202514643 BROTAÇÃO 08/09/2025 CAFÉ R$0,00 S202514644 PLANTIO 08/09/2025 CAFÉ R$0,00 DETERMINAÇÕES AMOSTRA S202514643 S202514644 PH em água - 5,02 5,12 PH em CaCl 2 - - - PH em KCl - - - P mg/dm 3 12,17 38,04 K + mg/dm 3 221 191 Ca 2+ cmol c/dm 3 2,28 2,89 Mg 2+ cmol c/dm 3 0,83 0,79 Al 3+ cmol c/dm 3 0,70 0,30 H cmol c/dm 3 7,2 6,1 H+Al cmol c/dm 3 7,92 6,41 SB cmol c/dm 3 3,7 4,2 t cmol c/dm 3 4,4 4,5 T cmol c/dm 3 11,6 10,6 V % 31,7 39,4 %m % 16,0 6,7 MO dag/kg 2,61 2,86 Prem mg/L 18,1 24,9 Zn mg/dm 3 2,10 4,50 Fe mg/dm 3 38,7 30,4 Mn mg/dm 3 14,9 43,9 Cu mg/dm 3 0,80 1,00 B mg/dm 3 0,46 0,35 S mg/dm 3 28,6 18,8 K % de T 4,9 4,6 Ca % de T 19,6 27,3 Mg % de T 7,2 7,5 H % de T 62,3 57,8 Al % de T 6,0 2,8 Ca/Mg - 2,7 3,7 Ca/K - 4,0 5,9 Mg/K - 1,5 1,6 PH em água, KCl e CaCl 2 - Relação 1:2,5 P-Remanescente - Solução CaCl 2 - 0,01 mol/L com 60 mg/L de P P-K-Fe-Zn-Mn e Cu - Extrator Mehlich 1 SB - Soma de Bases Trocáveis Ca-Mg-Al - Extrator: KCl - 1 mol/L CTC (t) - Capacidade de Troca Catiônica Efetiva H + Al - Método SMP CTC (T) - Capacidade de Troca Catiônica Total B - Extrator: CaCl 2 - 5 mmol/L em Microondas V - Índice Saturação de Bases S - Extrator: Fosfato monocálcico em ácido acético m - Índice Saturação de Alumínio MO - Matéria orgânica - Colorimetria Responsável Técnico`;


const texto2 = `CACHOEIRA CHATA Município: MANHUAÇU Estado: MG Área: - RESULTADO DE ANÁLISE DE SOLO Protocolo Identificação Entrada Cultura Valor S20222596 BRAÚNA 01 09/06/2022 CAFÉ R$40,00 S20222597 IPÊ 01 09/06/2022 ÁREA PLANTIO R$40,00 S20222598 BÚGIO 01 09/06/2022 NI R$40,00 S20222599 BÚGIO 02 09/06/2022 CAFÉ R$40,00 S20222600 BÚGIO 03 09/06/2022 CAFÉ R$40,00 S20222601 BÚGIO 04 09/06/2022 CAFÉ R$40,00 S20223733 PEROBA - 01 30/06/2022 NI R$40,00 S20224197 GAVIÃO 01 07/07/2022 NI R$40,00 DETERMINAÇÕES AMOSTRA S20222596 S20222597 S20222598 S20222599 S20222600 S20222601 S20223733 S20224197 PH em água - 5,16 5,98 5,61 5,16 5,45 5,83 4,98 4,83 PH em CaCl 2 - - - - - - - - - PH em KCl - - - - - - - - - P mg/dm 3 7,56 4,01 7,75 6,34 5,96 17,86 2,85 2,89 K + mg/dm 3 34 52 62 50 31 75 118 78 Ca 2+ cmol c/dm 3 2,14 3,33 4,04 2,41 2,19 5,12 1,08 0,60 Mg 2+ cmol c/dm 3 0,57 1,47 1,36 0,71 0,87 1,74 0,34 0,27 Al 3+ cmol c/dm 3 0,10 0,00 0,00 0,20 0,10 0,00 0,40 0,70 H cmol c/dm 3 6,5 3,0 6,5 5,4 5,3 4,4 5,3 6,7 H+Al cmol c/dm 3 6,63 3,05 6,48 5,61 5,43 4,44 5,67 7,41 SB cmol c/dm 3 2,8 4,9 5,6 3,2 3,1 7,0 1,7 1,1 t cmol c/dm 3 2,9 4,9 5,6 3,4 3,2 7,0 2,1 1,8 T cmol c/dm 3 9,4 8,0 12,0 8,9 8,6 11,5 7,4 8,5 V % 29,7 61,8 46,1 36,6 36,6 61,3 23,3 12,6 %m % 3,4 0,0 0,0 5,8 3,1 0,0 18,8 39,6 MO dag/kg 4,02 4,73 4,68 3,26 3,76 5,31 4,62 5,07 Prem mg/L 12,4 19,3 12,3 16,4 15,5 12,8 7,6 12,5 Zn mg/dm 3 2,50 4,50 3,70 3,80 1,70 3,80 1,30 2,00 Fe mg/dm 3 23,6 20,4 15,2 22,8 22,2 17,3 32,3 59,7 Mn mg/dm 3 6,0 7,1 7,8 4,1 4,0 11,2 4,6 8,4 Cu mg/dm 3 0,40 0,30 0,40 0,70 0,40 0,30 1,80 0,80 B mg/dm 3 0,41 0,46 0,41 0,33 0,27 0,47 0,21 0,24 S mg/dm 3 8,2 4,6 16,9 9,5 5,9 4,6 6,9 6,2 K % de T 0,9 1,7 1,3 1,4 0,9 1,7 4,1 2,4 Ca % de T 22,7 41,7 33,5 27,2 25,5 44,5 14,6 7,1 Mg % de T 6,1 18,4 11,3 8,0 10,1 15,1 4,6 3,1 H % de T 69,2 38,2 53,9 61,1 62,3 38,7 71,3 79,2 Al % de T 1,1 0,0 0,0 2,3 1,2 0,0 5,4 8,3 Ca/Mg - 3,7 2,3 3,0 3,4 2,5 2,9 3,2 2,3 Ca/K - 24,6 25,0 25,4 18,8 27,6 26,7 3,6 3,0 Mg/K - 6,6 11,0 8,6 5,5 10,9 9,1 1,1 1,3 PH em água, KCl e CaCl 2 - Relação 1:2,5 P-Remanescente - Solução CaCl 2 - 0,01 mol/L com 60 mg/L de P P-K-Fe-Zn-Mn e Cu - Extrator Mehlich 1 SB - Soma de Bases Trocávei`;

const dadosAnalisados = processarDadosDeAnalise(texto2);
console.log(JSON.stringify(dadosAnalisados, null, 2));