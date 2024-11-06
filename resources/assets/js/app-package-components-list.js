$(document).ready(function () {
    const form = $('#addTapLabelForm');

    form.on('submit', function (e) {
        e.preventDefault(); 

        $.ajax({
            url: 'package-components',
            type: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': window.csrfToken 
            },
            success: function(data) {
                console.log('Success:', data);
                $('#offcanvasTapLabel').offcanvas('hide');
                            
                form[0].reset();
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
            }
        });
    });

    // Add event listener for delete buttons
    $(document).on('click', '.btn-delete-package', function() {
        const componentId = $(this).data('id');
        deletePackageComponent(componentId);
        
    });

    function deletePackageComponent(id) {
        $.ajax({
            url: `package-components/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr, status, error) {
                console.error('Error deleting:', error);
                alert('Ocurrió un error al eliminar el componente. Por favor, inténtalo de nuevo.');
            }
        });
    }


    const editStockModal = document.getElementById('editStockModal');
    const editStockForm = document.getElementById('editStockForm');
    const addStockButton = document.getElementById('addStockButton');

    editStockModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const componentId = button.getAttribute('data-id');
        document.getElementById('componentId').value = componentId;
    });

    addStockButton.addEventListener('click', function() {
        const componentId = document.getElementById('componentId').value;
        const stockToAdd = document.getElementById('stockToAdd').value;

        fetch(`package-components/${componentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({ stockToAdd: stockToAdd })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(editStockModal).hide();
                location.reload();
            } else {
                alert('Error updating stock');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating stock');
        });
    });

    const isSellableSelect = document.getElementById('is_sellable');
    const priceContainer = document.getElementById('price').closest('.mb-3'); // Get the entire price div container

    updatePriceVisibility(isSellableSelect.value);

    isSellableSelect.addEventListener('change', function() {
        updatePriceVisibility(this.value);
    });

    function updatePriceVisibility(value) {
        if (value === "0") { 
            priceContainer.style.display = 'none'; 
            document.getElementById('price').value = '';
            document.getElementById('price').required = false; 
        } else {
            priceContainer.style.display = 'block';
            document.getElementById('price').required = true; 
        }
    }
});