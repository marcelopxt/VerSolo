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

const dadosAnalisados = processarDadosDeAnalise(texto1);
console.log(JSON.stringify(dadosAnalisados, null, 2));