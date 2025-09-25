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