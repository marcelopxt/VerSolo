@extends('layouts.app')


@section('title', 'VerSolo | Início')

@section('content')
    <!-- Status de salvamento -->
    @if (!empty($allAnalyses))
        <div class="alert alert-info d-flex align-items-center mb-3" id="save-status">
            <i class="bi bi-info-circle me-2"></i>
            <span id="save-message">
                @if (isset($autoSaved) && $autoSaved)
                    ✅ Análise salva automaticamente no histórico!
                @else
                    ⚠️ Análise ainda não foi salva. Clique em "Atualizar Histórico" para garantir que não seja perdida.
                @endif
            </span>
        </div>
    @endif

    <h3>Realizar uma nova leitura</h3>
    <span>A leitura atual ficará salva no histórico</span>
    <form action="/report" method="post" enctype="multipart/form-data" id="analysis-form">
        @csrf
        <div class="mb-3">
            <div class="input-group">
                <input type="file" class="form-control" name="file[]" id="input-file" multiple="multiple" required />
                <button type="submit" class="btn btn-outline-dark">
                    <i class="bi bi-upload"></i> Enviar
                </button>
            </div>
        </div>
    </form>

    @if (!empty($filenames) && !empty($allAnalyses))
        <div class="container mt-4">
            <!-- Botão de salvamento único -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Resultados da Análise</h2>
                <button type="button" class="btn btn-success" id="save-analysis-btn">
                    <i class="bi bi-arrow-clockwise"></i> Atualizar Histórico
                </button>
            </div>

            {{-- Loop principal para iterar sobre cada arquivo analisado --}}
            @forelse ($allAnalyses as $filename => $analise)
                <h2 class="mb-4">Arquivo: {{ $filename }}</h2>

                @if (isset($analise['erro']))
                    <div class="alert alert-danger">
                        <strong>Ocorreu um erro na extração dos dados:</strong> {{ $analise['erro'] }}
                    </div>
                @else
                    {{-- Acordeon para os protocolos --}}
                    <div class="accordion" id="accordion-{{ $loop->index }}">
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
                                $accordionId = 'accordion-' . $loop->parent->index . '-' . $loop->index;
                                $collapseId = 'collapse-' . $loop->parent->index . '-' . $loop->index;
                            @endphp

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-{{ $accordionId }}">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                        aria-controls="{{ $collapseId }}">
                                        <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                            <span class="fw-bold">Protocolo: {{ $protocolo }}</span>
                                            @if ($protocoloComErro)
                                                <span class="badge bg-danger">Contém Irregularidades</span>
                                            @else
                                                <span class="badge bg-success">Dentro dos Padrões</span>
                                            @endif
                                        </div>
                                    </button>
                                </h2>
                                <div id="{{ $collapseId }}"
                                    class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                    aria-labelledby="heading-{{ $accordionId }}"
                                    data-bs-parent="#accordion-{{ $loop->parent->index }}">
                                    <div class="accordion-body">
                                        <table class="table table-hover table-sm table-bordered">
                                            <thead class="table-light">
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
                                                        $classeDiagnostico = '';
                                                        if (isset($resultado['diagnostico'])) {
                                                            if (
                                                                str_contains($resultado['diagnostico'], 'inválido') ||
                                                                str_contains($resultado['diagnostico'], 'Abaixo') ||
                                                                str_contains($resultado['diagnostico'], 'Acima')
                                                            ) {
                                                                $classeDiagnostico = 'table-danger';
                                                            } elseif (
                                                                str_contains(
                                                                    $resultado['diagnostico'],
                                                                    'Dentro do ideal',
                                                                )
                                                            ) {
                                                                $classeDiagnostico = 'table-success';
                                                            }
                                                        }
                                                    @endphp

                                                    <tr class="{{ $classeDiagnostico }}">
                                                        <td class="fw-medium">{{ $chave }}</td>
                                                        <td class="text-center">{{ $resultado['valor'] ?? 'N/A' }}</td>
                                                        <td>{{ $resultado['unidade'] ?? '' }}</td>
                                                        <td>{{ $resultado['diagnostico'] ?? 'N/A' }}</td>
                                                        <td>
                                                            @if (isset($resultado['diagnostico']) && $resultado['diagnostico'] != 'N/A')
                                                                <div class="form-check">
                                                                    <input class="form-check-input ignore-checkbox"
                                                                        type="checkbox"
                                                                        name="ignored_errors[{{ $filename }}][{{ $protocolo }}][{{ $chave }}]"
                                                                        value="1"
                                                                        id="ignore-{{ $accordionId }}-{{ $loop->index }}">
                                                                    <label class="form-check-label"
                                                                        for="ignore-{{ $accordionId }}-{{ $loop->index }}">
                                                                        Ignorar Erro
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
                        @endforeach
                    </div>
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

    <style>
        .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .accordion-item {
            border: 1px solid #dee2e6;
            margin-bottom: 0.5rem;
            border-radius: 0.375rem;
        }

        .accordion-item:not(:first-child) {
            border-top: 1px solid #dee2e6;
        }

        .accordion-button {
            border-radius: 0.375rem;
        }

        .accordion-button:not(.collapsed) {
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }

        .table-danger {
            --bs-table-bg: #f8d7da;
            --bs-table-border-color: #f1aeb5;
        }

        .table-success {
            --bs-table-bg: #d1e7dd;
            --bs-table-border-color: #a3cfbb;
        }

        .badge {
            font-size: 0.75em;
        }

        .input-group .form-control {
            border-right: 0;
        }

        .input-group .btn {
            border-left: 0;
            border-color: #ced4da;
        }

        .input-group .btn:hover {
            border-color: #86b7fe;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainForm = document.getElementById('analysis-form');
            const saveBtn = document.getElementById('save-analysis-btn');
            const saveStatus = document.getElementById('save-status');
            const saveMessage = document.getElementById('save-message');

            // Dados da análise atual
            const currentAnalysisData = @json($allAnalyses ?? []);
            let hasUnsavedChanges = false; // Iniciar como false porque dados já foram salvos automaticamente
            let consultationId = @json($consultationId ?? null); // ID da consulta criada automaticamente

            // Auto-save no localStorage como backup
            if (Object.keys(currentAnalysisData).length > 0) {
                localStorage.setItem('versolo_analysis_backup', JSON.stringify({
                    data: currentAnalysisData,
                    timestamp: Date.now(),
                    saved: true // Marcar como salvo porque foi salvamento automático
                }));
            }

            // Função para coletar erros ignorados
            function getIgnoredErrors() {
                const ignoredErrors = {};
                const checkedCheckboxes = document.querySelectorAll('.ignore-checkbox:checked');

                checkedCheckboxes.forEach(checkbox => {
                    const matches = checkbox.name.match(
                        /ignored_errors\[([^\]]+)\]\[([^\]]+)\]\[([^\]]+)\]/);
                    if (matches) {
                        const [, filename, protocol, key] = matches;
                        if (!ignoredErrors[filename]) ignoredErrors[filename] = {};
                        if (!ignoredErrors[filename][protocol]) ignoredErrors[filename][protocol] = {};
                        ignoredErrors[filename][protocol][key] = 1;
                    }
                });

                return ignoredErrors;
            }

            // Função para salvar via AJAX
            function saveAnalysis() {
                if (!hasUnsavedChanges) {
                    showMessage('✅ Análise já foi salva!', 'success');
                    return;
                }

                if (!consultationId) {
                    showMessage('❌ Erro: ID da consulta não encontrado.', 'danger');
                    return;
                }

                const ignoredErrors = getIgnoredErrors();

                // Mostrar loading
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Atualizando...';
                }

                fetch('{{ route('analysis.save') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            analysis_data: JSON.stringify(currentAnalysisData),
                            ignored_errors: ignoredErrors,
                            consultation_id: consultationId // Enviar ID da consulta para atualização
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            hasUnsavedChanges = false;
                            showMessage('✅ ' + data.message, 'success');

                            // Limpar backup do localStorage
                            localStorage.removeItem('versolo_analysis_backup');
                        } else {
                            throw new Error(data.message || 'Erro desconhecido');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao salvar:', error);
                        showMessage('❌ Erro ao salvar: ' + error.message, 'danger');
                    })
                    .finally(() => {
                        if (saveBtn) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Atualizar Histórico';
                        }
                    });
            }

            // Função para mostrar mensagens
            function showMessage(message, type) {
                if (saveMessage) {
                    saveMessage.textContent = message;
                }
                if (saveStatus) {
                    saveStatus.className = `alert alert-${type} d-flex align-items-center mb-3`;
                }
            }

            // Event listeners
            if (saveBtn) {
                saveBtn.addEventListener('click', saveAnalysis);
            }

            // Detectar mudanças nos checkboxes
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('ignore-checkbox')) {
                    hasUnsavedChanges = true;
                    showMessage('⚠️ Você fez alterações. Clique em "Atualizar Histórico" para salvá-las.',
                        'warning');
                }
            });

            // Aviso ao tentar sair da página com alterações não salvas
            window.addEventListener('beforeunload', function(e) {
                if (hasUnsavedChanges) {
                    const message = 'Você tem alterações não salvas. Tem certeza que deseja sair?';
                    e.returnValue = message;
                    return message;
                }
            });

            // Submissão do formulário principal
            if (mainForm) {
                mainForm.addEventListener('submit', function(event) {
                    event.preventDefault();

                    // Se há dados não salvos, perguntar se quer salvar antes
                    if (hasUnsavedChanges && Object.keys(currentAnalysisData).length > 0) {
                        const shouldSave = confirm(
                            'Você tem alterações não salvas. Deseja salvá-las antes de fazer uma nova análise?'
                            );
                        if (shouldSave) {
                            // Salvar primeiro, depois submeter
                            saveAnalysis();
                            setTimeout(() => {
                                hasUnsavedChanges = false; // Evitar aviso do beforeunload
                                mainForm.submit();
                            }, 1000);
                            return;
                        }
                    }

                    hasUnsavedChanges = false; // Evitar aviso do beforeunload
                    mainForm.submit();
                });
            }
        });
    </script>
@endsection
