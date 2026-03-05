@extends('adminlte::page')

@section('title', 'Neraca Saldo (Trial Balance)')

@section('content_header')
    <h1>Neraca Saldo</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filter Tanggal</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.trial') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label class="mr-2">Per Tanggal:</label>
                    <input type="date" name="date" class="form-control" value="{{ $asOfDate }}">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
            </form>
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Laporan Neraca Saldo per {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}</h3>
            <div class="card-tools">
                <a href="{{ route('reports.trial.pdf', ['date' => $asOfDate]) }}" class="btn btn-danger btn-sm mr-2">
                    <i class="fas fa-file-pdf mr-1"></i> Download PDF
                </a>
                <button type="button" class="btn btn-tool" onclick="window.print()"><i class="fas fa-print"></i>
                    Cetak</button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th width="15%">Kode Akun</th>
                        <th>Nama Akun</th>
                        <th width="18%" class="text-right">Debit</th>
                        <th width="18%" class="text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $row)
                        <tr data-id="{{ $row['id'] }}"
                            @if ($row['parent_id']) data-parent="{{ $row['parent_id'] }}" @endif
                            class="
                            {{ !$row['is_postable'] ? 'font-weight-bold' : '' }}
                            {{ $row['level'] === 0 ? 'bg-light' : '' }}
                            {{ $row['level'] > 0 ? 'd-none child-row' : '' }}
                        ">
                            <td>
                                <span style="padding-left: {{ $row['level'] * 20 }}px;">
                                    @if ($row['has_children'])
                                        <i class="fas fa-caret-right toggle-children text-secondary mr-1"
                                            data-target="{{ $row['id'] }}" style="cursor:pointer; width:14px;"></i>
                                    @else
                                        <span style="display:inline-block; width:20px;"></span>
                                    @endif
                                    <code>{{ $row['code'] }}</code>
                                </span>
                            </td>
                            <td>
                                <span style="padding-left: {{ $row['level'] * 20 }}px;">
                                    {{ $row['name'] }}
                                </span>
                            </td>
                            <td class="text-right">
                                {{ $row['debit'] > 0 ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-right">
                                {{ $row['credit'] > 0 ? 'Rp ' . number_format($row['credit'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">Tidak ada data transaksi hingga tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-light font-weight-bold" style="font-size: 1.1rem;">
                    <tr>
                        <td colspan="2" class="text-right">TOTAL</td>
                        <td class="text-right text-primary">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                        <td class="text-right text-primary">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if (abs($totalDebit - $totalCredit) > 0.01)
            <div class="card-footer bg-danger text-white text-center">
                <i class="fas fa-exclamation-triangle"></i> PERINGATAN: Neraca tidak seimbang! Selisih: Rp
                {{ number_format(abs($totalDebit - $totalCredit), 0, ',', '.') }}
            </div>
        @endif
    </div>
@stop

@section('css')
    <style>
        .child-row {
            transition: all 0.2s ease;
        }

        table td {
            vertical-align: middle !important;
        }

        code {
            font-size: 0.95rem;
            color: #e83e8c;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.toggle-children').on('click', function() {
                const icon = $(this);
                const targetId = icon.data('target');

                if (icon.hasClass('fa-caret-right')) {
                    icon.removeClass('fa-caret-right').addClass('fa-caret-down');
                    $('tr[data-parent="' + targetId + '"]').removeClass('d-none');
                } else {
                    icon.removeClass('fa-caret-down').addClass('fa-caret-right');
                    hideDescendants(targetId);
                }
            });

            function hideDescendants(parentId) {
                let children = $('tr[data-parent="' + parentId + '"]');
                children.addClass('d-none');
                children.each(function() {
                    let childIcon = $(this).find('.toggle-children');
                    if (childIcon.length > 0) {
                        childIcon.removeClass('fa-caret-down').addClass('fa-caret-right');
                    }
                    let childId = $(this).data('id');
                    if (childId) hideDescendants(childId);
                });
            }
        });
    </script>
@stop
