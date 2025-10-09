@extends('layouts.app')

@section('title', 'VerSolo | Histórico')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Histórico de Consultas</h2>
        <span class="badge bg-primary">{{ $consultations->count() }} consulta(s)</span>
    </div>

    @if ($consultations->count() > 0)
        <div class="accordion" id="consultationsAccordion">
            @foreach ($consultations as $consultation)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading-{{ $consultation->id }}">
                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapse-{{ $consultation->id }}"
                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                            aria-controls="collapse-{{ $consultation->id }}">
                            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                <div>
                                    <span class="fw-bold">Consulta #{{ $consultation->id }}</span>
                                    <span class="text-muted ms-2">
                                        {{ $consultation->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-info me-2">{{ $consultation->qt_analysis }} protocolo(s)</span>
                                    @php
                                        $approvedCount = $consultation->analyses->where('status', 'approved')->count();
                                        $rejectedCount = $consultation->analyses->where('status', 'rejected')->count();
                                    @endphp
                                    @if ($rejectedCount > 0)
                                        <span class="badge bg-danger">{{ $rejectedCount }} com irregularidade(s)</span>
                                    @else
                                        <span class="badge bg-success">Todos aprovados</span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-{{ $consultation->id }}"
                        class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                        aria-labelledby="heading-{{ $consultation->id }}" data-bs-parent="#consultationsAccordion">
                        <div class="accordion-body">
                            @if ($consultation->analyses->count() > 0)
                                <div class="row">
                                    @foreach ($consultation->analyses as $analysis)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title">{{ $analysis->protocol }}</h6>
                                                        @if ($analysis->status === 'approved')
                                                            <span class="badge bg-success">Aprovado</span>
                                                        @else
                                                            <span class="badge bg-danger">Rejeitado</span>
                                                        @endif
                                                    </div>
                                                    <p class="card-text text-muted small">
                                                        Análise realizada em {{ $analysis->created_at->format('d/m/Y H:i') }}
                                                    </p>
                                                    <button type="button" class="btn btn-outline-primary btn-sm w-100"
                                                        data-bs-toggle="modal" data-bs-target="#analysisModal"
                                                        onclick="showAnalysisReport('{{ $analysis->protocol }}', {{ json_encode($analysis->report) }})">
                                                        <i class="bi bi-eye"></i> Visualizar Dados
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    Nenhuma análise encontrada para esta consulta.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-clock-history" style="font-size: 4rem; color: #6c757d;"></i>
            <h4 class="text-muted mt-3">Nenhuma consulta encontrada</h4>
            <p class="text-muted">Realize uma análise de solo para começar a visualizar seu histórico.</p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nova Análise
            </a>
        </div>
    @endif

    <!-- Modal para visualizar dados da análise -->
    <div class="modal fade" id="analysisModal" tabindex="-1" aria-labelledby="analysisModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="analysisModalLabel">
                        <i class="bi bi-bar-chart"></i> Dados da Análise - <span id="modalProtocolName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="analysisTableContainer">
                        <!-- Tabela será inserida aqui via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

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
        
        .card {
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .badge {
            font-size: 0.75em;
        }
        
        .table-danger {
            --bs-table-bg: #f8d7da;
            --bs-table-border-color: #f1aeb5;
        }
        
        .table-success {
            --bs-table-bg: #d1e7dd;
            --bs-table-border-color: #a3cfbb;
        }
        
        .table-warning {
            --bs-table-bg: #fff3cd;
            --bs-table-border-color: #ffdf7e;
        }
    </style>

    <script>
        function showAnalysisReport(protocol, reportData) {
            document.getElementById('modalProtocolName').textContent = protocol;
            
            try {
                const report = typeof reportData === 'string' ? JSON.parse(reportData) : reportData;
                const tableContainer = document.getElementById('analysisTableContainer');
                
                let tableHTML = `
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Parâmetro</th>
                                    <th class="text-center">Valor</th>
                                    <th>Unidade</th>
                                    <th>Diagnóstico</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                for (const [key, data] of Object.entries(report)) {
                    if (key === 'status') continue; // Pular campo status
                    
                    if (data && typeof data === 'object' && data.valor !== undefined) {
                        const valor = data.valor ?? 'N/A';
                        const unidade = data.unidade ?? '';
                        const diagnostico = data.diagnostico ?? 'N/A';
                        
                        // Determinar classe CSS baseada no diagnóstico
                        let rowClass = '';
                        let statusBadge = '';
                        
                        if (diagnostico.includes('inválido') || 
                            diagnostico.includes('Abaixo') || 
                            diagnostico.includes('Acima') ||
                            diagnostico.includes('Irregular')) {
                            rowClass = 'table-danger';
                            statusBadge = '<span class="badge bg-danger">Problema</span>';
                        } else if (diagnostico.includes('Dentro do ideal') || diagnostico.includes('Normal')) {
                            rowClass = 'table-success';
                            statusBadge = '<span class="badge bg-success">OK</span>';
                        } else if (diagnostico === 'N/A') {
                            rowClass = 'table-warning';
                            statusBadge = '<span class="badge bg-warning">N/A</span>';
                        } else {
                            statusBadge = '<span class="badge bg-secondary">-</span>';
                        }
                        
                        tableHTML += `
                            <tr class="${rowClass}">
                                <td class="fw-medium">${key}</td>
                                <td class="text-center">${valor}</td>
                                <td>${unidade}</td>
                                <td>${diagnostico}</td>
                                <td class="text-center">${statusBadge}</td>
                            </tr>
                        `;
                    }
                }
                
                tableHTML += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                tableContainer.innerHTML = tableHTML;
                
            } catch (error) {
                console.error('Erro ao processar dados do report:', error);
                document.getElementById('analysisTableContainer').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Erro ao carregar os dados da análise. Dados podem estar corrompidos.
                    </div>
                `;
            }
        }
    </script>
@endsection