@extends('layouts.app')

@section('title', 'VerSolo | Início')

@section('content')
    <div class="container mt-4">
        @if (!empty($allAnalyses))
            <form action="/report/save" method="post">
                @csrf
                <h3>Resultados:</h3>
                <div class="accordion" id="accordionTodosProtocolos">
                    @php $protocolCounter = 0; @endphp
                    @foreach ($allAnalyses as $filename => $analise)
                        @if (isset($analise['erro']))
                            <div class="alert alert-danger">
                                <strong>Ocorreu um erro na extração dos dados do arquivo '{{ $filename }}':</strong>
                                {{ $analise['erro'] }}
                            </div>
                        @else
                            @foreach ($analise as $protocolo => $dadosAmostra)
                                @php
                                    $protocoloComErro = false;
                                    foreach ($dadosAmostra as $resultado) {
                                        if (isset($resultado['diagnostico'])) {
                                            if (str_contains($resultado['diagnostico'], 'inválido') || str_contains($resultado['diagnostico'], 'Abaixo') || str_contains($resultado['diagnostico'], 'Acima') || str_contains($resultado['diagnostico'], 'Irregular')) {
                                                $protocoloComErro = true;
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-{{ $protocolCounter }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ $protocolCounter }}" aria-expanded="false"
                                            aria-controls="collapse-{{ $protocolCounter }}">
                                            <span class="fw-bold me-3">Protocolo: {{ $protocolo }}</span>
                                            @if ($protocoloComErro)
                                                <span class="badge bg-danger">Contém Irregularidades</span>
                                            @else
                                                <span class="badge bg-success">Dentro dos Padrões</span>
                                            @endif
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $protocolCounter }}" class="accordion-collapse collapse"
                                        aria-labelledby="heading-{{ $protocolCounter }}" data-bs-parent="#accordionTodosProtocolos">
                                        <div class="accordion-body">
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
                                                                if (str_contains($resultado['diagnostico'], 'inválido') || str_contains($resultado['diagnostico'], 'Abaixo') || str_contains($resultado['diagnostico'], 'Acima')) {
                                                                    $classeDiagnostico = 'text-danger';
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
                                                                {{-- LÓGICA DO BOTÃO CORRIGIDA --}}
                                                                @if (isset($resultado['diagnostico']) && $resultado['diagnostico'] != 'N/A' && $classeDiagnostico === 'text-danger')
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" value="1"
                                                                            id="check-{{ $protocolCounter }}-{{ Str::slug($chave) }}"
                                                                            name="ignored_errors[{{ $filename }}][{{ $protocolo }}][{{ $chave }}]">
                                                                        <label class="form-check-label"
                                                                            for="check-{{ $protocolCounter }}-{{ Str::slug($chave) }}">
                                                                            Ignorar
                                                                        </label>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @php $protocolCounter++; @endphp
                            @endforeach
                        @endif
                    @endforeach
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                </div>
            </form>
        @else
            <h2>Realizar Verificação</h2>
            <p>Envie um ou mais arquivos para serem verificados de acordo com o padrão estabelecido.</p>
            <form action="/report" method="post" enctype="multipart/form-data">
                @csrf
                <div class="input-group mb-3">
                    <input type="file" class="form-control" name="file[]" id="input-file" multiple="multiple"
                        aria-label="Upload">
                    <button class="btn btn-outline-success" type="submit" id="button-addon2">Verificar</button>
                </div>
            </form>
        @endif
    </div>
@endsection