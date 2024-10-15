@extends('layouts/layoutMaster')

@section('title', 'Agregar Orden a Proveedor')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Órdenes a Proveedores /</span> Crear Orden
</h4>



<div class="app-ecommerce">
  <form action="{{ route('supplier-orders.store') }}" method="POST">
  @csrf
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
                  <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
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
              <input type="date" class="form-control" id="order_date" name="order_date" required>
            </div>

            <div class="mb-3">
              <label for="shipping_status" class="form-label">Estado de Envío</label>
              <select class="form-select" id="shipping_status" name="shipping_status" required>
                <option value="completed">Completado</option>
                <option value="pending">Pendiente</option>
                <option value="sending">Enviando</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="payment_status" class="form-label">Estado del Pago</label>
              <select class="form-select" id="payment_status" name="payment_status" required>
                <option value="pending">Pendiente</option>
                <option value="paid">Pagado</option>
                <option value="delayed">Atrasado</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label" for="payment_method">Método de pago</label>
              <select class="form-select" id="payment_method" name="payment_method" required>
                <option value="">Seleccione un método de pago</option>
                <option value="cash">Efectivo</option>
                <option value="credit">Crédito</option>
                <option value="debit">Débito</option>
                <option value="check">Cheque</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="payment" class="form-label">Pago</label>
              <input type="number" class="form-control" id="payment" name="payment" step="0.01" required>
            </div>

            <div class="mb-3">
              <label for="notes" class="form-label">Notas</label>
              <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>

            @if ($errors->any())
              @foreach ($errors->all() as $error)
                <div class="alert alert-danger">
                  {{ $error }}
                </div>
              @endforeach
            @endif

            <button type="submit" class="btn btn-primary">Crear Orden</button>
        </div>
        </div>
      </div>
    </div>
  </form>
</div>

<style>
  div#rawMaterialsFields div:first-child i {
    display: none;
  }
</style>

<script>
  let selectedRawMaterials = [];

  function addRawMaterialField() {
    const container = document.getElementById('rawMaterialsFields');
    const newRow = document.createElement('div');
    newRow.className = 'mb-3 row align-items-center';
    newRow.innerHTML = `
        <div class="col-4">
          <select class="form-select raw-material-select" name="raw_material_id[]" required onchange="updateOptions();">
            <option disabled selected value="">Seleccione una materia prima</option>
            @foreach($rawMaterials as $material)
                <option value="{{ $material->id }}">{{ $material->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-3">
          <input type="number" name="quantity[]" class="form-control" placeholder="Cantidad" required min="1">
        </div>
        <div class="col-3">
          <input type="number" name="unit_cost[]" class="form-control" placeholder="Costo por unidad" required step="0.01" min="0">
        </div>
        <div class="col-auto">
          <i class="bx bx-minus-circle" style="font-size: 1.5rem; cursor: pointer;" onclick="removeRawMaterialField(this);"></i>
        </div>
    `;
    container.appendChild(newRow);
    updateOptions();
    checkAddButtonVisibility();
  }


  function updateOptions() {
    selectedRawMaterials = [];

    document.querySelectorAll('.raw-material-select').forEach((select) => {
      console.log(select.value)
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
    const container = document.getElementById('rawMaterialsFields');
    const row = element.closest('.row');
    row.remove();
    updateOptions();

    checkAddButtonVisibility();
  }

  document.addEventListener('DOMContentLoaded', () => {
    addRawMaterialField();
  });
</script>




@endsection
