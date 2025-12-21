// En resources/js/barcode-scanner.js
document.addEventListener('DOMContentLoaded', function() {
    let barcode = '';
    let reading = false;
    
    document.addEventListener('keydown', function(e) {
        // Ignorar teclas especiales
        if (e.key === 'Shift' || e.key === 'Control' || e.key === 'Alt') {
            return;
        }
        
        // Enter indica fin de lectura
        if (e.key === 'Enter') {
            if (barcode.length >= 8) { // Mínimo 8 caracteres para código
                Livewire.dispatch('barcodeScanned', { code: barcode });
            }
            barcode = '';
            reading = false;
            return;
        }
        
        // Acumular caracteres
        if (!reading) {
            reading = true;
            barcode = '';
        }
        
        barcode += e.key;
        
        // Resetear después de 100ms sin teclas
        clearTimeout(window.barcodeTimeout);
        window.barcodeTimeout = setTimeout(() => {
            barcode = '';
            reading = false;
        }, 100);
    });
});