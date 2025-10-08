@extends('layouts.app')

@section('title', 'VerSolo | Início')

@section('content')
    <h3>Realizar uma nova leitura</h3>
    <span>A leitura atual ficará salva no histórico</span>
    <form action="/report" method="post" enctype="multipart/form-data">
        @csrf
        @if (!empty($allAnalyses))
            <input type="hidden" name="previous_analysis_data" value="{{ json_encode($allAnalyses) }}">
        @endif
        <div class="mb-3">
            <input type="file" class="form-control" name="file[]" id="input-file" multiple="multiple" />
            <br>
            <button type="submit" class="btn btn-outline-dark">Enviar</button>
        </div>
    </form>
    @if (!empty($filenames) && !empty($allAnalyses))
        <div class="container mt-4">
            {{-- Loop principal para iterar sobre cada arquivo analisado --}}
            @forelse ($allAnalyses as $filename => $analise)
                <h2 class="mb-4">Arquivo: {{ $filename }}</h2>

                @if (isset($analise['erro']))
                    <div class="alert alert-danger">
                        <strong>Ocorreu um erro na extração dos dados:</strong> {{ $analise['erro'] }}
                    </div>
                @else
                    @foreach ($analise as $protocolo => $dadosAmostra)
                        @php
                            $protocoloComErro = false;
                            foreach ($dadosAmostra as $resultado) {
                                if (isset($resultado['diagnostico'])) {
                                    if (
                                        str_contains($resultado['diagnostico'], 'inválido') ||
                                        str_contains($resultado['diagnostico'], 'Abaixo') ||
                                        str_contains($resultado['diagnostico'], 'Acima') ||
                                        str_contains($resultado['diagnostico'], 'Irregular')
                                    ) {
                                        $protocoloComErro = true;
                                        break;
                                    }
                                }
                            }
                        @endphp

                        <div class="d-flex align-items-center mt-5">
                            <h4 class="mb-0">Protocolo: {{ $protocolo }}</h4>
                            @if ($protocoloComErro)
                                <span class="badge bg-danger ml-3">Contém Irregularidades</span>
                            @else
                                <span class="badge bg-success ml-3">Dentro dos Padrões</span>
                            @endif
                        </div>

                        <table class="table table-hover table-sm mt-2 table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Análise</th>
                                    <th class="text-center">Valor</th>
                                    <th>Unidade</th>
                                    <th>Diagnóstico</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dadosAmostra as $chave => $resultado)
                                    @php
                                        $classeDiagnostico = 'text-body';
                                        if (isset($resultado['diagnostico'])) {
                                            if (
                                                str_contains($resultado['diagnostico'], 'inválido') ||
                                                str_contains($resultado['diagnostico'], 'Abaixo') ||
                                                str_contains($resultado['diagnostico'], 'Acima')
                                            ) {
                                                $classeDiagnostico = 'text-danger font-weight-bold';
                                            } elseif (str_contains($resultado['diagnostico'], 'Dentro do ideal')) {
                                                $classeDiagnostico = 'text-success';
                                            }
                                        }
                                    @endphp

                                    <tr class="{{ $classeDiagnostico }}">
                                        <td>{{ $chave }}</td>
                                        <td class="text-center">{{ $resultado['valor'] ?? 'N/A' }}</td>
                                        <td>{{ $resultado['unidade'] ?? '' }}</td>
                                        <td>{{ $resultado['diagnostico'] ?? 'N/A' }}</td>
                                        <td>
                                            @if (isset($resultado['diagnostico']) && $resultado['diagnostico'] != 'N/A')
                                                <div class="btn-group" role="group" data-bs-toggle="buttons">
                                                    <label class="btn btn-primary active">
                                                        <input type="checkbox" class="me-2"
                                                            name="ignored_errors[{{ $filename }}][{{ $protocolo }}][{{ $chave }}]"
                                                            value="1" autocomplete="off" />
                                                        Ignorar Erro
                                                    </label>
                                                </div>
                                            @endif

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endforeach
                @endif

                {{-- Adiciona uma linha divisória se não for o último arquivo --}}
                @if (!$loop->last)
                    <hr class="my-5">
                @endif

            @empty
                <div class="alert alert-info mt-4">
                    Nenhum dado de análise para exibir. Verifique os arquivos enviados.
                </div>
            @endforelse
        </div>
    @endif
@endsection
