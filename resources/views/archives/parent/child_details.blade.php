@extends('layouts.parent')

@section('title', $student->full_name . ' – Archives ' . $year->name)
@section('page-title', $student->full_name . ' – ' . $year->name)

@section('content')
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('archives.parent.show', $year->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

{{-- Résumé élève --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap gap-4 align-items-center">
        <div>
            <h2 class="h5 fw-bold mb-1">{{ $record->last_name }} {{ $record->first_name }}</h2>
            <p class="text-muted small mb-0">
                Classe : <strong>{{ $class->name }}</strong>
                · Année : <strong>{{ $year->name }}</strong>
                · {{ ucfirst($class->entity->name ?? '--') }}
            </p>
        </div>
        @if($record->moy_annuelle !== null)
            <div class="ms-auto text-center">
                <div class="display-6 fw-bold {{ $record->moy_annuelle >= 10 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($record->moy_annuelle, 2, ',', '') }}/20
                </div>
                <div class="text-muted small">Moyenne annuelle</div>
                @if($record->rang_annuel)
                    <span class="badge bg-secondary mt-1">{{ $record->rang_annuel }}ᵉ rang</span>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Statut délibération --}}
@if($record->statut_deliberation === 'passed')
    <div class="alert alert-success mb-4">
        <i class="fas fa-graduation-cap me-2"></i>
        <strong>Admis(e)</strong> pour l'année {{ $year->name }}.
        @if($record->nextClass && $record->nextAcademicYear)
            Passage en <strong>{{ $record->nextClass->name }}</strong> ({{ $record->nextAcademicYear->name }}).
        @endif
    </div>
@elseif($record->statut_deliberation === 'repeated')
    <div class="alert alert-danger mb-4">
        <i class="fas fa-redo me-2"></i>
        <strong>Redoublant(e)</strong> pour l'année {{ $year->name }}.
    </div>
@endif

{{-- Tabs navigation --}}
<ul class="nav nav-tabs mb-4" id="childTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#notesTab">
            <i class="fas fa-chart-bar me-1 text-success"></i> Notes & Bulletins
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#edtTab">
            <i class="fas fa-calendar-alt me-1 text-indigo-600"></i> Emploi du temps
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#payTab">
            <i class="fas fa-money-bill-wave me-1 text-warning"></i> Paiements
        </button>
    </li>
</ul>

<div class="tab-content">

    {{-- ===== ONGLET NOTES ===================================================== --}}
    <div class="tab-pane fade show active" id="notesTab">

        {{-- Résumé trimestriel depuis le snapshot --------------------------------}}
        <div class="row g-3 mb-4">
            @foreach([1, 2, 3] as $t)
                @php
                    $moy = $record->{'moy_trimestre_' . $t};
                    $td  = $trimestresData[$t] ?? null;
                @endphp
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h3 class="h6 text-muted mb-2">Trimestre {{ $t }}</h3>
                            <div class="h3 fw-bold mb-1 {{ $moy !== null && $moy >= 10 ? 'text-success' : 'text-danger' }}">
                                {{ $moy !== null ? number_format($moy, 2, ',', '') : '–' }}/20
                            </div>
                            @if($td)
                                <span class="badge {{ $td['moyenneGenerale'] !== null && $td['moyenneGenerale'] >= 10 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $td['moyenneGenerale'] !== null && $td['moyenneGenerale'] >= 10 ? 'text-success' : 'text-danger' }}">
                                    {{ $td['appreciation'] }}
                                </span>
                                <div class="text-muted small mt-2">Conduite : {{ number_format($td['conduite'], 2, ',', '') }}/20</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Moyenne annuelle -----------------------------------------------------}}
        @php $moyAnn = $record->moy_annuelle ?? $moyAnnuelle; @endphp
        @if($moyAnn !== null)
            <div class="alert {{ $moyAnn >= 10 ? 'alert-success' : 'alert-danger' }} d-flex align-items-center gap-3 mb-4">
                <i class="fas fa-graduation-cap fa-2x"></i>
                <div>
                    <strong>Moyenne annuelle : {{ number_format($moyAnn, 2, ',', '') }}/20</strong>
                    — {{ $moyAnn >= 10 ? '✅ Admis(e)' : '❌ Non admis(e)' }}
                </div>
            </div>
        @endif

        {{-- Bulletins par trimestre (accordéon) ----------------------------------}}
        <div class="accordion" id="bulletinAccordion">
            @foreach([1, 2, 3] as $t)
                @php $td = $trimestresData[$t] ?? null; @endphp
                @if($td)
                <div class="accordion-item border rounded-3 mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $t != 1 ? 'collapsed' : '' }} rounded-3"
                                type="button" data-bs-toggle="collapse"
                                data-bs-target="#bulletin{{ $t }}">
                            <i class="fas fa-file-alt me-2 text-success"></i>
                            Trimestre {{ $t }}
                            @if($td['moyenneGenerale'] !== null)
                                <span class="ms-3 badge {{ $td['moyenneGenerale'] >= 10 ? 'bg-success' : 'bg-danger' }}">
                                    {{ number_format($td['moyenneGenerale'], 2, ',', '') }}/20
                                </span>
                            @endif
                        </button>
                    </h2>
                    <div id="bulletin{{ $t }}" class="accordion-collapse collapse {{ $t == 1 ? 'show' : '' }}"
                         data-bs-parent="#bulletinAccordion">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-dark text-center" style="font-size:0.8rem">
                                        <tr>
                                            <th class="text-start">Matière</th>
                                            <th>Coef.</th>
                                            <th>Interros</th>
                                            <th>Devoir 1</th>
                                            <th>Devoir 2</th>
                                            <th>Moy./Interros</th>
                                            <th>Moy./20</th>
                                            <th>Appréciation</th>
                                        </tr>
                                    </thead>
                                    <tbody style="font-size:0.85rem">
                                        @foreach($td['bulletin'] as $row)
                                            <tr>
                                                <td class="fw-medium">{{ $row['subject'] }}</td>
                                                <td class="text-center">{{ $row['coef'] }}</td>
                                                <td class="text-center text-muted small">
                                                    {{ !empty($row['interros']) ? implode(' / ', array_map(fn($v) => number_format($v, 2, ',', ''), $row['interros'])) : '–' }}
                                                </td>
                                                <td class="text-center">{{ $row['devoir1'] !== null ? number_format($row['devoir1'], 2, ',', '') : '–' }}</td>
                                                <td class="text-center">{{ $row['devoir2'] !== null ? number_format($row['devoir2'], 2, ',', '') : '–' }}</td>
                                                <td class="text-center">{{ $row['moyInterro'] !== null ? number_format($row['moyInterro'], 2, ',', '') : '–' }}</td>
                                                <td class="text-center fw-bold {{ $row['moyenne'] !== null && $row['moyenne'] >= 10 ? 'text-success' : ($row['moyenne'] !== null ? 'text-danger' : 'text-muted') }}">
                                                    {{ $row['moyenne'] !== null ? number_format($row['moyenne'], 2, ',', '') : '–' }}
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $row['moyenne'] !== null && $row['moyenne'] >= 10 ? 'success' : ($row['moyenne'] !== null ? 'danger' : 'secondary') }} bg-opacity-10 text-{{ $row['moyenne'] !== null && $row['moyenne'] >= 10 ? 'success' : ($row['moyenne'] !== null ? 'danger' : 'secondary') }}">
                                                        {{ $row['appreciation'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-success">
                                        <tr>
                                            <td colspan="6" class="fw-bold text-end">Moyenne générale :</td>
                                            <td class="fw-bold text-center fs-6">
                                                {{ $td['moyenneGenerale'] !== null ? number_format($td['moyenneGenerale'], 2, ',', '') : '–' }}/20
                                            </td>
                                            <td class="fw-bold text-center">{{ $td['appreciation'] }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ===== ONGLET EMPLOI DU TEMPS ========================================= --}}
    <div class="tab-pane fade" id="edtTab">
        @if($timetables->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                <p>Aucun emploi du temps disponible pour cette année.</p>
            </div>
        @else
            <div class="table-responsive border rounded-3 shadow-sm">
                <table class="table table-bordered table-sm mb-0 text-center" style="font-size:0.85rem; min-width:700px">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:80px">Heure</th>
                            @foreach($days as $day)
                                <th>{{ $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hours as $hourSlot)
                            @php
                                [$startH] = explode('-', $hourSlot);
                                $startFmt = str_replace('h', ':00', $startH);
                            @endphp
                            <tr>
                                <td class="fw-semibold bg-light small">{{ $hourSlot }}</td>
                                @foreach($days as $day)
                                    @php
                                        $course = $timetables->first(fn($t) =>
                                            $t->day === $day &&
                                            date('H:i', strtotime($t->start_time)) == $startFmt
                                        );
                                        $overlap = $timetables->first(fn($t) =>
                                            $t->day === $day &&
                                            strtotime($t->start_time) < strtotime($startFmt) &&
                                            strtotime($t->end_time) > strtotime($startFmt)
                                        );
                                    @endphp
                                    @if($course)
                                        @php $dur = max(1, round((strtotime($course->end_time) - strtotime($course->start_time)) / 3600)); @endphp
                                        <td rowspan="{{ $dur }}" class="bg-success bg-opacity-10 align-middle p-1">
                                            <div class="fw-bold text-success small">{{ $course->subject->name ?? '–' }}</div>
                                            <div class="text-muted" style="font-size:0.75rem">{{ $course->teacher->name ?? '–' }}</div>
                                            <div class="badge bg-success bg-opacity-25 text-success" style="font-size:0.7rem">
                                                {{ date('H:i', strtotime($course->start_time)) }} – {{ date('H:i', strtotime($course->end_time)) }}
                                            </div>
                                        </td>
                                    @elseif($overlap)
                                    @else
                                        <td></td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ===== ONGLET PAIEMENTS =============================================== --}}
    <div class="tab-pane fade" id="payTab">
        @php
            $pRate     = $totalFees > 0 ? round($totalPaid / $totalFees * 100) : 0;
            $remaining = max(0, $totalFees - $totalPaid);
        @endphp

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body">
                        <div class="h4 fw-bold text-success">{{ number_format($totalPaid, 0, ',', ' ') }}</div>
                        <div class="text-muted small">FCFA payés</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body">
                        <div class="h4 fw-bold {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($remaining, 0, ',', ' ') }}</div>
                        <div class="text-muted small">FCFA restants</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body">
                        <div class="h4 fw-bold text-primary">{{ number_format($totalFees, 0, ',', ' ') }}</div>
                        <div class="text-muted small">Total dû</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center border-0 shadow-sm">
                    <div class="card-body">
                        <div class="h4 fw-bold">{{ $pRate }}%</div>
                        <div class="text-muted small">Payé</div>
                        <div class="progress mt-2" style="height:5px">
                            <div class="progress-bar bg-success" style="width:{{ $pRate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($payments->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-receipt fa-3x mb-3"></i>
                <p>Aucun paiement enregistré pour cette année.</p>
            </div>
        @else
            <div class="table-responsive border rounded-3 shadow-sm">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>N°</th>
                            <th>Date</th>
                            <th>Tranche</th>
                            <th class="text-end">Montant (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $idx => $payment)
                            <tr>
                                <td class="text-muted">{{ $idx + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                                <td>{{ $payment->tranche ?? '–' }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format($payment->amount, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">Total</td>
                            <td class="text-end text-success">{{ number_format($totalPaid, 0, ',', ' ') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection