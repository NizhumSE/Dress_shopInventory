@extends('layouts.admin')

@section('content')
<div class="bg-white p-6 rounded-lg shadow">
    <div id="barcode-container" class="text-center">
        <svg id="barcode"></svg>
        <p class="mt-4">{{ $product->barcode }}</p>
    </div>
    <button onclick="printBarcode()" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">
        Print Barcode
    </button>
</div>

<script>
    JsBarcode("#barcode", "{{ $product->barcode }}", {
        format: "CODE128",
        lineColor: "#000",
        width: 2,
        height: 100,
        displayValue: true
    });

    function printBarcode() {
        const printContent = document.getElementById('barcode-container').innerHTML;
        const originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
    }
</script>
@endsection
