<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lotes utilizados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #f8f9fa;
            --text-color: #34495e;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .dashboard-header {
            background: linear-gradient(135deg, red, blue, white);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dashboard-title {
            font-weight: 300;
            letter-spacing: 1px;
        }

        .batch-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .batch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .batch-id {
            color: var(--primary-color);
            font-weight: 600;
        }

        .quantity-used {
            font-size: 1.2rem;
            color: var(--secondary-color);
        }

        .card-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .dashboard-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <header class="dashboard-header text-center">
        <div class="container">
            <h1 class="dashboard-title">Información de Lotes utilizados</h1>
        </div>
    </header>

    <main class="container">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($batches as $batch)
            <div class="col">
                <div class="card batch-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-box card-icon"></i>
                        <h5 class="card-title batch-id">Número de lote: {{ $batch->batch_name }}</h5>
                        <p class="card-text quantity-used">
                            Cantidad utilizada: {{ $batch->quantity_used }} {{ $batch->unit_of_measure }} 
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>