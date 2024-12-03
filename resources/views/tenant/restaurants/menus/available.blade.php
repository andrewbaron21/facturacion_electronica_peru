<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Disponible</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            font-size: 1.5rem;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 10px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .menu-item {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .menu-item:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }

        .menu-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .menu-content {
            padding: 15px;
        }

        .menu-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #333;
        }

        .menu-price {
            font-size: 1.1rem;
            color: #4CAF50;
            margin: 10px 0;
        }

        .menu-description {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 15px;
        }

        .menu-button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .menu-button:hover {
            background-color: #45a049;
        }

        footer {
            text-align: center;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            margin-top: 20px;
        }

        /* Responsivo */
        @media (max-width: 768px) {
            .menu-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<header>
    Menú Disponible
</header>

<div class="container">
    <div class="menu-grid">
        @foreach($menus as $menu)
        <div class="menu-item">
            <img src="{{ asset('storage/' . $menu->image) }}" alt="{{ $menu->name }}">
            <div class="menu-content">
                <h2 class="menu-title">{{ $menu->name }}</h2>
                <p class="menu-price">S/ {{ number_format($menu->price, 2) }}</p>
                <p class="menu-description">{{ $menu->description }}</p>
                <!-- <a href="#" class="menu-button">Agregar al Pedido</a> -->
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- <footer>
    © {{ date('Y') }} Restaurante. Todos los derechos reservados.
</footer> -->

</body>
</html>
