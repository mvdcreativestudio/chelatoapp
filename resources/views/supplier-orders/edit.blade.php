@extends('layouts/layoutMaster')

@section('title', 'Editar Orden a Proveedor')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Órdenes a Proveedores /</span> Editar Orden
</h4>

<div class="app-ecommerce">
  <form action="{{ route('supplier-orders.update', $supplierOrder->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
      <div class="col-12">
        <div class="card mb-4">
          <div class="card-header">
            <h5 class="card-title mb-0">Información de la Orden</h5>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label for="supplier_id" class="form-label">Proveedor</label>
              <select class="form-select" id="supplier_id" name="supplier_id" required>
                <option selected disabled>Seleccione un proveedor</option>
                @foreach($suppliers as $supplier)
                  <option value="{{ $supplier->id }}" {{ $supplierOrder->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label d-flex align-items-center mb-3">
                Materias Primas
                <i onclick="addRawMaterialField()" class="bx bx-plus-circle add-button" style="font-size: 1.5rem; cursor: pointer; margin-left: 5px;"></i>
              </label>
              <div id="rawMaterialsFields"></div>
            </div>

            <div class="mb-3">
              <label for="order_date" class="form-label">Fecha de Orden</label>
              <input type="date" class="form-control" id="order_date" name="order_date" required value="{{ $supplierOrder->order_date }}">
            </div>

            <div class="mb-3">
              <label for="shipping_status" class="form-label">Estado de Envío</label>
              <select class="form-select" id="shipping_status" name="shipping_status" required>
                <option value="pending" {{ $supplierOrder->shipping_status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                <option value="sending" {{ $supplierOrder->shipping_status == 'sending' ? 'selected' : '' }}>Enviando</option>
                <option value="completed" {{ $supplierOrder->shipping_status == 'completed' ? 'selected' : '' }}>Completado</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="payment_status" class="form-label">Estado del Pago</label>
              <select class="form-select" id="payment_status" name="payment_status" required>
                <option value="pending" {{ $supplierOrder->payment_status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                <option value="paid" {{ $supplierOrder->payment_status == 'paid' ? 'selected' : '' }}>Pagado</option>
                <option value="delayed" {{ $supplierOrder->payment_status == 'delayed' ? 'selected' : '' }}>Atrasado</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="payment" class="form-label">Pago</label>
              <input type="number" class="form-control" id="payment" name="payment" step="0.01" required value="{{ $supplierOrder->payment }}">
            </div>

            <div class="mb-3">
              <label for="notes" class="form-label">Notas</label>
              <textarea class="form-control" id="notes" name="notes" rows="3">{{ $supplierOrder->notes }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Orden</button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
let selectedRawMaterials = [];

function addRawMaterialField(rawMaterialId = null, rawMaterialName = '', quantity = 0, unitCost = 0) {
  const container = document.getElementById('rawMaterialsFields');
  const newRow = document.createElement('div');
  newRow.className = 'mb-3 row align-items-center';
  newRow.innerHTML = `
      <div class="col-4">
        <select class="form-select raw-material-select" name="raw_material_id[]" required onchange="updateOptions();">
          <option disabled value="" ${!rawMaterialId ? 'selected' : ''}>Seleccione una materia prima</option>
          @foreach($rawMaterials as $material)
            <option value="{{ $material->id }}" ` + (rawMaterialId == {{ $material->id }} ? 'selected' : '') + `>{{ $material->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-3">
        <input type="number" name="quantity[]" class="form-control" placeholder="Cantidad" required min="1" value="` + quantity + `">
      </div>
      <div class="col-3">
        <input type="number" name="unit_cost[]" class="form-control" placeholder="Costo por unidad" required step="0.01" value="` + unitCost + `">
      </div>
      <div class="col-auto">
        <i class="bx bx-minus-circle" style="font-size: 1.5rem; cursor: pointer;" onclick="removeRawMaterialField(this);"></i>
      </div>
  `;
  container.appendChild(newRow);

  updateOptions(); // Esta llamada asegura que las opciones se actualicen para respetar las selecciones existentes.
}

function updateOptions() {
    selectedRawMaterials = [];

    document.querySelectorAll('.raw-material-select').forEach((select) => {
      if (select.value) selectedRawMaterials.push(select.value);
    });

    document.querySelectorAll('.raw-material-select').forEach((select) => {
      const currentValue = select.value;
      const otherValues = selectedRawMaterials.filter(val => val !== currentValue);

      select.querySelectorAll('option').forEach((option) => {
        option.hidden = otherValues.includes(option.value);
      });
    });

    checkAddButtonVisibility();
}

function checkAddButtonVisibility() {
    const allSelected = [...document.querySelectorAll('.raw-material-select')].every(select => select.value);

    let selectableOptionsCount = 0;
    document.querySelectorAll('.raw-material-select').forEach((select) => {
      selectableOptionsCount += Array.from(select.options).filter(option => !option.hidden && option.value).length;
    });

    selectableOptionsCount -= document.querySelectorAll('.raw-material-select').length;

    const addButton = document.querySelector('.add-button');

    addButton.style.display = allSelected && selectableOptionsCount > 0 ? 'inline-block' : 'none';
}

function removeRawMaterialField(element) {
  const row = element.closest('.row');
  row.remove();
  updateOptions();
  checkAddButtonVisibility();
}

document.addEventListener('DOMContentLoaded', () => {
  // Añade campos de materia prima existentes basados en la orden cargada.
  @foreach($supplierOrder->rawMaterials as $rawMaterial)
    addRawMaterialField({{ $rawMaterial->id }}, "{{ $rawMaterial->name }}", {{ $rawMaterial->pivot->quantity }}, {{ $rawMaterial->pivot->unit_cost }});
  @endforeach

  updateOptions();
  checkAddButtonVisibility();

  // Verifica si hay campos de materias primas presentes después de cargar los existentes.
  // Si no hay ninguno, añade un nuevo campo automáticamente.
  const rawMaterialsCount = document.querySelectorAll('.raw-material-select').length;
  if (rawMaterialsCount === 0) {
    addRawMaterialField();
  }
});

</script>
@endsection
