<?php

namespace App\Livewire\Reportes;

use App\Models\CajaCierre as CajaCierreModel;
use App\Services\ReporteCajaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class CierreCaja extends Component
{
    public ?CajaCierreModel $ultimoCierre = null;
    public bool $showConfirmationModal = false;

    public function askForConfirmation()
    {
        $this->showConfirmationModal = true;
    }

    public function realizarCierre(ReporteCajaService $reporteCajaService)
    {
        $user = Auth::user();
        $this->ultimoCierre = $reporteCajaService->generarReporte($user->id, $user->company_id);
        
        $this->showConfirmationModal = false;

        $this->dispatch('cierre-realizado', '¡Cierre de caja completado con éxito!');
    }

    public function descargarPdf($cierreId)
    {
        $cierre = CajaCierreModel::with('user')->findOrFail($cierreId);
        
        $pdf = Pdf::loadView('pdfs.reporte-cierre-caja', ['cierre' => $cierre]);
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'reporte-cierre-'.$cierre->fecha_cierre->format('Ymd-His').'.pdf');
    }

    public function render()
    {
        return view('livewire.reportes.cierre-caja');
    }
}

