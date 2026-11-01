@extends('layouts.admin')

@section('title', 'Selecionar Pontos da Viagem')
@section('header_title', 'Pontos de Embarque e Desembarque')

@section('content')
<!-- Leaflet CSS carregado diretamente para garantir a estilização nativa -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    /* Força a anulação de resets globais do Tailwind que afetam as imagens e marcadores do Leaflet */
    .leaflet-container img {
        max-width: none !important;
        max-height: none !important;
    }
</style>

@php
$pontosAtivos = optional($viagem->rota)->pontosDeParada
    ? $viagem->rota->pontosDeParada
        ->filter(fn($p) => isset($p->pivot->ativo) ? (bool)$p->pivot->ativo : true)
        ->sortBy('pivot.ordem')
        ->map(fn($p, $key) => [
            'id' => $p->id,
            'descricao' => $p->descricao,
            'lat' => (float) str_replace(',', '.', $p->latitude),
            'lng' => (float) str_replace(',', '.', $p->longitude),
            'ordem' => isset($p->pivot->ordem) ? (int)$p->pivot->ordem : $key
        ])
        ->values()
    : collect([]);
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Card Topo / Cabeçalho da Rota -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                    &larr; Voltar
                </a>
                <h2 class="text-base font-bold text-slate-800">
                    Viagem: {{ $viagem->rota->descricao ?? 'Detalhes' }}
                </h2>
            </div>
            <p class="text-xs text-slate-500 mt-1">Defina os pontos de parada para o seu percurso nesta rota.</p>
        </div>

        @if(!empty($passageiro))
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 self-start sm:self-auto">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Inscrição Confirmada
            </span>
        @endif
    </div>

    <!-- Mensagens de Validação de Erros -->
    @if ($errors->any())
        <div class="p-4 rounded-xl text-xs font-semibold text-rose-800 bg-rose-50 border border-rose-200 space-y-1">
            <p class="font-bold text-sm">Atenção:</p>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- ÁREA DO MAPA (7 COLUNAS) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col justify-between">
            <div class="flex justify-between items-center mb-3 px-1">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Mapa do Trajeto e Embarque</h3>
                <div id="distancia-info" class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100 hidden"></div>
            </div>

            <div
                id="map"
                style="height: 520px; min-height: 520px; width: 100%;"
                class="rounded-xl border border-slate-200 z-10 overflow-hidden"
                data-pontos='@json($pontosAtivos)'>
            </div>
        </div>

        <!-- FORMULÁRIO DE SELEÇÃO DOS PONTOS (5 COLUNAS) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between space-y-6">
            <div class="space-y-5">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Selecione seus Pontos</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Escolha os locais diretamente no mapa ou pelos seletores abaixo.</p>
                </div>

                <!-- PAINEL DE RESUMO INTERATIVO -->
                <div class="space-y-2.5">
                    <div id="card-embarque" class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl transition-all">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">🟢 Ponto de Embarque</span>
                        <p id="texto-embarque" class="text-xs font-bold text-slate-700 mt-0.5">Nenhum selecionado</p>
                    </div>

                    <div id="card-desembarque" class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl transition-all">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 block">🔴 Ponto de Desembarque</span>
                        <p id="texto-desembarque" class="text-xs font-bold text-slate-700 mt-0.5">Nenhum selecionado</p>
                    </div>
                </div>

                <!-- FORMULÁRIO DE CONFIRMAÇÃO -->
                <form id="form-pontos" action="{{ route('passageiros.salvar-pontos', $viagem->id) }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Ponto de Embarque -->
                    <div>
                        <label for="ponto_de_parada_saida_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Embarque (Saída)</label>
                        <select name="ponto_de_parada_saida_id" id="ponto_de_parada_saida_id" required
                            onchange="aoMudarSelect()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 text-slate-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm px-3 py-2">
                            <option value="">Selecione o ponto de embarque...</option>
                            @foreach($pontosAtivos as $ponto)
                                <option value="{{ $ponto['id'] }}" data-ordem="{{ $ponto['ordem'] }}"
                                    {{ (old('ponto_de_parada_saida_id', $passageiro->ponto_de_parada_saida_id ?? '') == $ponto['id']) ? 'selected' : '' }}>
                                    {{ $ponto['ordem'] + 1 }}. {{ $ponto['descricao'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Ponto de Desembarque -->
                    <div>
                        <label for="ponto_de_parada_chegada_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Desembarque (Chegada)</label>
                        <select name="ponto_de_parada_chegada_id" id="ponto_de_parada_chegada_id" required
                            onchange="aoMudarSelect()"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 text-slate-800 text-sm focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-colors shadow-sm px-3 py-2">
                            <option value="">Selecione o ponto de desembarque...</option>
                            @foreach($pontosAtivos as $ponto)
                                <option value="{{ $ponto['id'] }}" data-ordem="{{ $ponto['ordem'] }}"
                                    {{ (old('ponto_de_parada_chegada_id', $passageiro->ponto_de_parada_chegada_id ?? '') == $ponto['id']) ? 'selected' : '' }}>
                                    {{ $ponto['ordem'] + 1 }}. {{ $ponto['descricao'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- BOTÃO DE SALVAR -->
                    <button type="submit"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl transition shadow-md shadow-indigo-500/20 flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>{{ !empty($passageiro) ? 'Atualizar Confirmação' : 'Confirmar Presença na Viagem' }}</span>
                    </button>
                </form>

                <!-- BOTÕES DE AÇÕES SECUNDÁRIAS -->
                @if(isset($passageiro) && $passageiro->exists)
                    <div class="space-y-2 pt-1 border-t border-slate-100">
                        @if($passageiro->data_hora_saida)
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Embarque confirmado em {{ $passageiro->data_hora_saida->format('d/m/Y H:i') }}</span>
                            </div>
                        @else
                            <a href="{{ route('passageiros.scanner') }}"
                                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl transition shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <span>Escanear QR Code para Embarcar</span>
                            </a>
                        @endif

                        <form action="{{ route('passageiros.cancelar', $viagem->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja cancelar sua participação nesta viagem?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs rounded-xl border border-rose-200 transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>Cancelar minha participação</span>
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('dashboard') }}"
                        class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 transition flex items-center justify-center text-center">
                        Cancelar / Voltar
                    </a>
                @endif
            </div>

            <!-- Dados Adicionais -->
            <div class="pt-4 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                <p><strong>Veículo:</strong> {{ $viagem->veiculo->descricao ?? 'N/A' }} ({{ $viagem->veiculo->placa ?? 'Sem placa' }})</p>
                <p><strong>Motorista:</strong> {{ $viagem->motorista->usuario->name ?? 'Não definido' }}</p>
            </div>
        </div>

    </div>
</div>

<!-- Leaflet JS carregado diretamente no final do conteúdo -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    let map;
    let marcadores = {};
    let linhaRotaGeral = null;
    let linhaTrechoPassageiro = null;
    let pontosData = [];

    document.addEventListener("DOMContentLoaded", function() {
        const mapElement = document.getElementById('map');
        if (!mapElement) return;

        // Redefinição de URLs para evitar marcadores quebrados/transparentes
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({
            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
        });

        pontosData = JSON.parse(mapElement.dataset.pontos || '[]');

        map = L.map('map').setView([-15.7801, -47.9292], 4);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        setTimeout(() => {
            map.invalidateSize();
        }, 300);

        if (pontosData.length === 0) return;

        // 1. Criar marcadores no mapa
        pontosData.forEach((ponto, index) => {
            const iconNumerado = criarIconeNumerado(index + 1, '#4f46e5');
            const marker = L.marker([ponto.lat, ponto.lng], {
                icon: iconNumerado
            }).addTo(map);

            const popupContent = `
                <div class="p-1 text-center space-y-2">
                    <strong class="block text-xs text-slate-800">Parada ${index + 1}: ${ponto.descricao}</strong>
                    <div class="flex flex-col gap-1.5">
                        <button type="button" onclick="definirPonto(${ponto.id}, 'saida')" 
                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                            🟢 Embarcar Aqui
                        </button>
                        <button type="button" onclick="definirPonto(${ponto.id}, 'chegada')" 
                                class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                            🔴 Desembarcar Aqui
                        </button>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent);
            marcadores[ponto.id] = {
                marker: marker,
                lat: ponto.lat,
                lng: ponto.lng,
                descricao: ponto.descricao,
                index: index,
                ordem: ponto.ordem
            };
        });

        // 2. Traçar rota via OSRM
        if (pontosData.length >= 2) {
            const coordinatesStr = pontosData.map(p => `${p.lng},${p.lat}`).join(';');
            const url = `https://router.project-osrm.org/route/v1/driving/${coordinatesStr}?overview=full&geometries=geojson`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.routes && data.routes.length > 0) {
                        const routeCoordinates = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);

                        linhaRotaGeral = L.polyline(routeCoordinates, {
                            color: '#94a3b8',
                            weight: 4,
                            opacity: 0.6,
                            dashArray: '6, 6'
                        }).addTo(map);

                        const saidaId = document.getElementById('ponto_de_parada_saida_id').value;
                        const chegadaId = document.getElementById('ponto_de_parada_chegada_id').value;

                        if (!saidaId || !chegadaId) {
                            map.fitBounds(linhaRotaGeral.getBounds(), {
                                padding: [40, 40]
                            });
                        }
                    }
                })
                .catch(err => console.error("Erro ao carregar rota OSRM:", err))
                .finally(() => {
                    aoMudarSelect();
                });
        } else {
            map.setView([pontosData[0].lat, pontosData[0].lng], 14);
            aoMudarSelect();
        }
    });

    function criarIconeNumerado(numero, corFundo) {
        return L.divIcon({
            className: 'custom-div-icon',
            html: `<div style="background-color: ${corFundo}; color: white; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">${numero}</div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });
    }

    function aoMudarSelect() {
        validarEFiltrarOpcoes();
        atualizarTrajetoEInterface();
    }

    function validarEFiltrarOpcoes() {
        const selectSaida = document.getElementById('ponto_de_parada_saida_id');
        const selectChegada = document.getElementById('ponto_de_parada_chegada_id');

        const selectedSaidaOpt = selectSaida.options[selectSaida.selectedIndex];
        const selectedChegadaOpt = selectChegada.options[selectChegada.selectedIndex];

        const ordemSaida = selectedSaidaOpt && selectedSaidaOpt.value ? parseInt(selectedSaidaOpt.dataset.ordem) : null;
        const ordemChegada = selectedChegadaOpt && selectedChegadaOpt.value ? parseInt(selectedChegadaOpt.dataset.ordem) : null;

        Array.from(selectChegada.options).forEach(opt => {
            if (!opt.value) return;
            const ordemOpt = parseInt(opt.dataset.ordem);
            if (ordemSaida !== null && ordemOpt <= ordemSaida) {
                opt.disabled = true;
            } else {
                opt.disabled = false;
            }
        });

        Array.from(selectSaida.options).forEach(opt => {
            if (!opt.value) return;
            const ordemOpt = parseInt(opt.dataset.ordem);
            if (ordemChegada !== null && ordemOpt >= ordemChegada) {
                opt.disabled = true;
            } else {
                opt.disabled = false;
            }
        });
    }

    function definirPonto(pontoId, tipo) {
        const selectSaida = document.getElementById('ponto_de_parada_saida_id');
        const selectChegada = document.getElementById('ponto_de_parada_chegada_id');

        const pontoClicado = marcadores[pontoId];
        if (!pontoClicado) return;

        const ordemClicada = pontoClicado.ordem;

        if (tipo === 'saida') {
            const chegadaOpt = selectChegada.options[selectChegada.selectedIndex];
            const ordemChegada = chegadaOpt && chegadaOpt.value ? parseInt(chegadaOpt.dataset.ordem) : null;

            if (ordemChegada !== null && ordemClicada >= ordemChegada) {
                alert('Erro: O ponto de embarque deve ser anterior ao ponto de desembarque.');
                return;
            }
            selectSaida.value = pontoId;
        } else if (tipo === 'chegada') {
            const saidaOpt = selectSaida.options[selectSaida.selectedIndex];
            const ordemSaida = saidaOpt && saidaOpt.value ? parseInt(saidaOpt.dataset.ordem) : null;

            if (ordemSaida !== null && ordemClicada <= ordemSaida) {
                alert('Erro: O ponto de desembarque não pode ser anterior ou igual ao de embarque.');
                return;
            }
            selectChegada.value = pontoId;
        }

        map.closePopup();
        aoMudarSelect();
    }

    function atualizarTrajetoEInterface() {
        const saidaId = document.getElementById('ponto_de_parada_saida_id').value;
        const chegadaId = document.getElementById('ponto_de_parada_chegada_id').value;

        const cardEmbarque = document.getElementById('card-embarque');
        const cardDesembarque = document.getElementById('card-desembarque');
        const textoEmbarque = document.getElementById('texto-embarque');
        const textoDesembarque = document.getElementById('texto-desembarque');
        const distanciaInfo = document.getElementById('distancia-info');

        Object.keys(marcadores).forEach(id => {
            const idx = marcadores[id].index;
            marcadores[id].marker.setIcon(criarIconeNumerado(idx + 1, '#4f46e5'));
        });

        if (linhaTrechoPassageiro) {
            map.removeLayer(linhaTrechoPassageiro);
            linhaTrechoPassageiro = null;
        }

        distanciaInfo.classList.add('hidden');

        if (saidaId && marcadores[saidaId]) {
            const idx = marcadores[saidaId].index;
            marcadores[saidaId].marker.setIcon(criarIconeNumerado(idx + 1, '#10b981'));
            textoEmbarque.innerText = `${idx + 1}. ${marcadores[saidaId].descricao}`;
            cardEmbarque.className = "p-3.5 bg-emerald-50 border border-emerald-300 rounded-xl transition-all shadow-sm";
        } else {
            textoEmbarque.innerText = "Nenhum selecionado";
            cardEmbarque.className = "p-3.5 bg-slate-50 border border-slate-200 rounded-xl transition-all";
        }

        if (chegadaId && marcadores[chegadaId]) {
            const idx = marcadores[chegadaId].index;
            marcadores[chegadaId].marker.setIcon(criarIconeNumerado(idx + 1, '#f43f5e'));
            textoDesembarque.innerText = `${idx + 1}. ${marcadores[chegadaId].descricao}`;
            cardDesembarque.className = "p-3.5 bg-rose-50 border border-rose-300 rounded-xl transition-all shadow-sm";
        } else {
            textoDesembarque.innerText = "Nenhum selecionado";
            cardDesembarque.className = "p-3.5 bg-slate-50 border border-slate-200 rounded-xl transition-all";
        }

        if (saidaId && chegadaId && marcadores[saidaId] && marcadores[chegadaId]) {
            const idxInicio = marcadores[saidaId].index;
            const idxFim = marcadores[chegadaId].index;

            if (idxInicio < idxFim) {
                const subPontos = pontosData.slice(idxInicio, idxFim + 1);

                if (subPontos.length >= 2) {
                    const coordsStr = subPontos.map(p => `${p.lng},${p.lat}`).join(';');
                    const url = `https://router.project-osrm.org/route/v1/driving/${coordsStr}?overview=full&geometries=geojson`;

                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            if (data.routes && data.routes.length > 0) {
                                const route = data.routes[0];
                                const coordsTrecho = route.geometry.coordinates.map(c => [c[1], c[0]]);

                                linhaTrechoPassageiro = L.polyline(coordsTrecho, {
                                    color: '#4f46e5',
                                    weight: 6,
                                    opacity: 0.95
                                }).addTo(map);

                                map.fitBounds(linhaTrechoPassageiro.getBounds(), {
                                    padding: [60, 60]
                                });

                                const distanciaKm = (route.distance / 1000).toFixed(1);
                                distanciaInfo.textContent = `Seu Trajeto: ${distanciaKm} km`;
                                distanciaInfo.classList.remove('hidden');
                            }
                        });
                }
            }
        }
    }
</script>
@endsection