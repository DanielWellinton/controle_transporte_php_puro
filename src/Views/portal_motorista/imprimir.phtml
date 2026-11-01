<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Embarque - Viagem #{{ $viagem->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
                padding: 0 !important;
                color: black !important;
            }

            .print-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body class="bg-slate-100 text-slate-900 font-sans p-4 sm:p-8">

    @php
        $totalAgendados = $viagem->passageiros->count();
        $totalEmbarcados = $viagem->passageiros->filter(fn($p) => !is_null($p->pivot->data_hora_saida))->count();
        $diferenca = $totalAgendados - $totalEmbarcados;
    @endphp

    <!-- Ações superiores (Oculto na impressão) -->
    <div class="max-w-4xl mx-auto mb-6 flex justify-between items-center no-print">
        <a href="{{ route('portal_motorista.show', $viagem->id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-slate-700 hover:text-black transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Voltar para a viagem</span>
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-black text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <span>Imprimir Ficha</span>
        </button>
    </div>

    <!-- Folha Imprimível -->
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-2xl border border-slate-300 shadow-sm print-card space-y-5">

        <!-- Cabeçalho -->
        <div class="border-b-2 border-slate-900 pb-3 flex justify-between items-start">
            <div>
                <h1 class="text-base font-bold text-black uppercase tracking-wider">Relatório de Embarque de Passageiros</h1>
                <p class="text-[11px] text-slate-600 mt-0.5">Emissão: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold uppercase tracking-wider px-3 py-1 border-2 border-slate-800 rounded-md text-black">
                    Viagem #{{ $viagem->id }}
                </span>
            </div>
        </div>

        <!-- Informações da Viagem -->
        <div class="grid grid-cols-3 gap-4 text-xs border-b border-slate-300 pb-3">
            <div>
                <span class="text-slate-600 block text-[10px] uppercase font-bold">Rota:</span>
                <strong class="text-black font-bold">{{ $viagem->rota->descricao ?? $viagem->rota->nome ?? 'Não informada' }}</strong>
            </div>
            <div>
                <span class="text-slate-600 block text-[10px] uppercase font-bold">Saída Programada:</span>
                <strong class="text-black font-bold">
                    @if($viagem->data_hora_saida)
                        {{ $viagem->data_hora_saida instanceof \Carbon\Carbon ? $viagem->data_hora_saida->format('d/m/Y H:i') : \Carbon\Carbon::parse($viagem->data_hora_saida)->format('d/m/Y H:i') }}
                    @else
                        --/--/---- --:--
                    @endif
                </strong>
            </div>
            <div>
                <span class="text-slate-600 block text-[10px] uppercase font-bold">Veículo / Placa:</span>
                <strong class="text-black font-bold">{{ $viagem->veiculo->placa ?? '-' }}</strong>
            </div>
        </div>

        <!-- Resumo do Embarque Monocromático -->
        <div class="grid grid-cols-3 border-2 border-slate-800 rounded-lg text-center divide-x-2 divide-slate-800 py-2">
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-700 block">Agendados</span>
                <span class="text-base font-black text-black">{{ $totalAgendados }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-700 block">Embarcados</span>
                <span class="text-base font-black text-black">{{ $totalEmbarcados }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold uppercase text-slate-700 block">Pendentes / Ausentes</span>
                <span class="text-base font-black text-black">{{ $diferenca }}</span>
            </div>
        </div>

        <!-- Tabela da Chamada com Horários e Flag de Presença P&B -->
        <div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-slate-900 text-[10px] font-bold text-black uppercase tracking-wider">
                        <th class="py-2 px-1 w-6">#</th>
                        <th class="py-2 px-2">Passageiro</th>
                        <th class="py-2 px-2">Trajeto (Subida → Descida)</th>
                        <th class="py-2 px-2 text-center w-20">Hora Subida</th>
                        <th class="py-2 px-2 text-center w-20">Hora Descida</th>
                        <th class="py-2 px-2 text-center w-16">Presença</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-300 text-xs text-black">
                    @forelse($viagem->passageiros as $index => $passageiro)
                    <tr>
                        <td class="py-2.5 px-1 font-bold text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-2.5 px-2">
                            <div class="font-bold text-black">{{ $passageiro->name }}</div>
                            <div class="text-[10px] text-slate-600">{{ $passageiro->telefone ?? 'Sem tel' }}</div>
                        </td>
                        <td class="py-2.5 px-2 text-[11px]">
                            <span class="font-semibold">{{ \App\Models\PontoDeParada::find($passageiro->pivot->ponto_de_parada_saida_id)?->descricao ?? '-' }}</span>
                            <span class="text-slate-400 mx-1">→</span>
                            <span class="text-slate-700">{{ \App\Models\PontoDeParada::find($passageiro->pivot->ponto_de_parada_chegada_id)?->descricao ?? '-' }}</span>
                        </td>
                        
                        <!-- Horário de Subida -->
                        <td class="py-2.5 px-2 text-center font-mono font-bold text-[11px]">
                            @if(!empty($passageiro->pivot->data_hora_saida))
                                {{ \Carbon\Carbon::parse($passageiro->pivot->data_hora_saida)->format('H:i') }}
                            @else
                                <span class="text-slate-400 font-normal">--:--</span>
                            @endif
                        </td>

                        <!-- Horário de Descida -->
                        <td class="py-2.5 px-2 text-center font-mono font-bold text-[11px]">
                            @if(!empty($passageiro->pivot->data_hora_chegada))
                                {{ \Carbon\Carbon::parse($passageiro->pivot->data_hora_chegada)->format('H:i') }}
                            @else
                                <span class="text-slate-400 font-normal">--:--</span>
                            @endif
                        </td>

                        <!-- Checkbox / Presença P&B -->
                        <td class="py-2.5 px-2 text-center align-middle">
                            @if(!empty($passageiro->pivot->data_hora_saida))
                                <!-- Check Marcado em Preto (Digitalmente confirmado) -->
                                <div class="w-5 h-5 border-2 border-black bg-black text-white rounded mx-auto flex items-center justify-center font-bold text-xs">
                                    ✓
                                </div>
                            @else
                                <!-- Caixa Vazia (Para marcação manual em papel) -->
                                <div class="w-5 h-5 border-2 border-slate-800 rounded mx-auto bg-white"></div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500 italic">
                            Nenhum passageiro cadastrado nesta viagem.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Seção de Assinaturas -->
        <div class="pt-10 grid grid-cols-2 gap-12 text-center text-xs text-black">
            <div>
                <div class="border-t-2 border-slate-800 pt-1 font-bold">Assinatura do Motorista</div>
            </div>
            <div>
                <div class="border-t-2 border-slate-800 pt-1 font-bold">Visto / Fiscalização</div>
            </div>
        </div>

    </div>

</body>

</html>